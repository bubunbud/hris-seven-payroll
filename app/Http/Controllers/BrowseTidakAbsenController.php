<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HariLibur;
use App\Models\TukarHariKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BrowseTidakAbsenController extends Controller
{
    public function index(Request $request)
    {
        // Tingkatkan memory limit dan timeout untuk handle data besar
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 menit
        
        // Default date range (current month)
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get filter parameters
        $search = $request->get('search'); // NIK / Nama (gabungan)
        // Backward compatibility: jika masih ada parameter nik/nama, gunakan itu
        $nik = $request->get('nik');
        $nama = $request->get('nama');
        if (!$search && ($nik || $nama)) {
            // Jika ada parameter lama, gabungkan menjadi search
            $searchParts = [];
            if ($nik) $searchParts[] = $nik;
            if ($nama) $searchParts[] = $nama;
            $search = implode(', ', $searchParts);
        }
        $group = $request->get('group', 'Semua Group');

        // Get hari libur list untuk periode ini (global: weekend + master)
        $hariLiburList = $this->getHariLiburList($startDate, $endDate);

        // Data tukar hari kerja untuk periode ini (per NIK)
        // - KERJA_KE_LIBUR: tanggal_libur = tanggal yang jadi libur untuk NIK tersebut → NIK tidak wajib absen
        // - LIBUR_KE_KERJA: tanggal_libur = tanggal yang jadi kerja untuk NIK tersebut → NIK wajib absen
        $tukarKerjaKeLiburSet = $this->getTukarHariKerjaSet($startDate, $endDate, 'KERJA_KE_LIBUR');
        $tukarLiburKeKerjaSet = $this->getTukarHariKerjaSet($startDate, $endDate, 'LIBUR_KE_KERJA');

        // Load karyawan aktif untuk autocomplete lokal (exclude Management)
        $karyawansForAutocomplete = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Group_pegawai', '!=', 'Management')
            ->with(['divisi', 'bagian'])
            ->orderBy('Nama')
            ->get(['Nik', 'Nama', 'Divisi', 'vcKodeBagian', 'Group_pegawai']);

        // Siapkan data sederhana untuk frontend (hindari logic berat di Blade)
        $karyawanList = $karyawansForAutocomplete->map(function ($k) {
            $divisiNama = '-';
            if ($k->divisi && isset($k->divisi->vcNamaDivisi)) {
                $divisiNama = $k->divisi->vcNamaDivisi;
            } elseif ($k->Divisi) {
                $divisiNama = $k->Divisi;
            }

            $bagianNama = '-';
            if ($k->bagian && isset($k->bagian->vcNamaBagian)) {
                $bagianNama = $k->bagian->vcNamaBagian;
            }

            return [
                'nik' => $k->Nik ?: '',
                'nama' => $k->Nama ?: '',
                'divisi' => $divisiNama,
                'bagian' => $bagianNama,
                'search' => strtolower(($k->Nik ?: '') . ' ' . ($k->Nama ?: '')),
            ];
        })->values();

        // Filter karyawan aktif untuk query data (exclude Management)
        $karyawanQuery = Karyawan::with(['departemen', 'bagian', 'divisi'])
            ->where('vcAktif', '1')
            ->where('Group_pegawai', '!=', 'Management'); // Exclude Management

        // Apply search filter (multi-term support)
        if ($search) {
            // Split by comma untuk multi pencarian
            $searchTerms = preg_split('/,\s*/', trim($search));

            $karyawanQuery->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty(trim($term))) {
                        $term = trim($term);
                        // Jika format "NIK - Nama", ambil NIK saja
                        if (strpos($term, ' - ') !== false) {
                            $term = explode(' - ', $term)[0];
                        }
                        $q->orWhere('Nik', 'like', '%' . $term . '%')
                            ->orWhere('Nama', 'like', '%' . $term . '%');
                    }
                }
            });
        }

        if ($group !== 'Semua Group') {
            $karyawanQuery->where('Group_pegawai', $group);
        }

        $karyawans = $karyawanQuery->orderBy('Nama')->get();

        // Ambil semua data absensi untuk periode ini (dengan chunking)
        $absenExists = collect();
        DB::table('t_absen')
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->where(function ($q) {
                $q->whereNotNull('dtJamMasuk')->orWhereNotNull('dtJamKeluar');
            })
            ->select('dtTanggal', 'vcNik')
            ->orderBy('dtTanggal')
            ->orderBy('vcNik')
            ->chunk(5000, function ($chunk) use (&$absenExists) {
                foreach ($chunk as $item) {
                    $absenExists->put($item->dtTanggal . '_' . $item->vcNik, true);
                }
            });

        // Ambil semua data tidak masuk untuk periode ini (dengan chunking)
        $tidakMasukExists = collect();
        $tidakMasukRecords = DB::table('t_tidak_masuk')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('dtTanggalMulai', [$startDate, $endDate])
                  ->orWhereBetween('dtTanggalSelesai', [$startDate, $endDate])
                  ->orWhere(function ($qq) use ($startDate, $endDate) {
                      $qq->where('dtTanggalMulai', '<=', $startDate)
                         ->where('dtTanggalSelesai', '>=', $endDate);
                  });
            })
            ->whereNotNull('dtTanggalMulai')
            ->whereNotNull('dtTanggalSelesai')
            ->select('vcNik', 'dtTanggalMulai', 'dtTanggalSelesai')
            ->orderBy('dtTanggalMulai')
            ->orderBy('vcNik')
            ->get();

        // Expand tanggal tidak masuk
        $filterStart = Carbon::parse($startDate);
        $filterEnd = Carbon::parse($endDate);
        foreach ($tidakMasukRecords as $tm) {
            $current = Carbon::parse($tm->dtTanggalMulai);
            $end = Carbon::parse($tm->dtTanggalSelesai);
            $maxDays = 365;
            $dayCount = 0;

            while ($current->lte($end) && $dayCount < $maxDays) {
                if ($current->lt($filterStart) || $current->gt($filterEnd)) {
                    $current->addDay();
                    $dayCount++;
                    continue;
                }

                $tanggalStr = $current->format('Y-m-d');
                $key = $tanggalStr . '_' . $tm->vcNik;
                $tidakMasukExists->put($key, true);

                $current->addDay();
                $dayCount++;
            }
        }

        // Generate data tidak absen (Alpha)
        $combinedData = collect();
        $cursor = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($cursor->lte($end)) {
            $dow = (int) $cursor->format('w'); // 0=Min,6=Sabtu
            $isWeekend = ($dow === 0 || $dow === 6);
            $isHoliday = in_array($cursor->format('Y-m-d'), $hariLiburList, true);
            $tanggalStr = $cursor->format('Y-m-d');

            foreach ($karyawans as $karyawan) {
                // Apakah tanggal ini hari kerja untuk NIK ini? (mempertimbangkan tukar hari kerja)
                // Hari kerja jika: (bukan libur global) ATAU (ada LIBUR_KE_KERJA untuk NIK ini)
                // Dan BUKAN: (ada KERJA_KE_LIBUR untuk NIK ini)
                $keyTukar = $tanggalStr . '_' . $karyawan->Nik;
                $isLiburKeKerja = $tukarLiburKeKerjaSet->has($keyTukar);
                $isKerjaKeLibur = $tukarKerjaKeLiburSet->has($keyTukar);

                $isHariKerjaUntukNik = ($isLiburKeKerja)
                    || ((!$isWeekend && !$isHoliday) && !$isKerjaKeLibur);

                // Hanya proses jika tanggal ini hari kerja untuk NIK ini
                if (!$isHariKerjaUntukNik) {
                    continue;
                }

                $key = $tanggalStr . '_' . $karyawan->Nik;

                // Cek apakah ada data absensi
                $adaAbsen = $absenExists->has($key);

                // Cek apakah ada data tidak masuk
                $adaTidakMasuk = $tidakMasukExists->has($key);

                // Jika tidak ada absensi DAN tidak ada tidak masuk, maka Alpha
                if (!$adaAbsen && !$adaTidakMasuk) {
                    $combinedData->push([
                            'dtTanggal' => $tanggalStr,
                            'vcNik' => $karyawan->Nik,
                            'Nama' => $karyawan->Nama,
                            'Divisi' => $karyawan->Divisi,
                            'vcKodeBagian' => $karyawan->vcKodeBagian,
                            'vcNamaDivisi' => $karyawan->divisi ? $karyawan->divisi->vcNamaDivisi : 'N/A',
                            'vcNamaDepartemen' => $karyawan->departemen ? $karyawan->departemen->vcNamaDept : 'N/A',
                            'vcNamaBagian' => $karyawan->bagian ? $karyawan->bagian->vcNamaBagian : 'N/A',
                            'Group_pegawai' => $karyawan->Group_pegawai ?? null,
                            'dtJamMasuk' => null,
                            'dtJamKeluar' => null,
                            'dtJamMasukLembur' => null,
                            'dtJamKeluarLembur' => null,
                            'total_jam' => 0,
                            'status' => 'Alpha',
                            'source' => 'tidak_absen',
                        ]);
                }
            }

            $cursor->addDay();
        }

        // Sort by tanggal desc, then nik
        $combinedData = $combinedData->sort(function ($a, $b) {
            $dateCompare = strcmp($b['dtTanggal'], $a['dtTanggal']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            return strcmp($a['vcNik'], $b['vcNik']);
        })->values();

        // Pagination manual
        $perPage = 50;
        $currentPage = $request->get('page', 1);
        $total = $combinedData->count();
        $items = $combinedData->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Create paginator manually
        $absens = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get summary data
        $totalData = $total;

        // Get unique groups for filter (exclude Management)
        $groups = Karyawan::select('Group_pegawai')
            ->distinct()
            ->whereNotNull('Group_pegawai')
            ->where('vcAktif', '1')
            ->where('Group_pegawai', '!=', 'Management')
            ->orderBy('Group_pegawai')
            ->pluck('Group_pegawai');

        return view('absen.tidak-absen.index', compact(
            'absens',
            'startDate',
            'endDate',
            'search',
            'group',
            'totalData',
            'groups',
            'hariLiburList',
            'karyawanList'
        ));
    }

    /**
     * Get list of holidays (including weekends)
     */
    private function getHariLiburList($startDate, $endDate)
    {
        $hariLibur = HariLibur::whereBetween('dtTanggal', [$startDate, $endDate])
            ->pluck('dtTanggal')
            ->map(function ($tanggal) {
                return $tanggal instanceof Carbon ? $tanggal->format('Y-m-d') : Carbon::parse($tanggal)->format('Y-m-d');
            })
            ->toArray();

        // Tambahkan Sabtu dan Minggu
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        while ($current->lte($end)) {
            if (in_array($current->dayOfWeek, [0, 6])) { // 0 = Minggu, 6 = Sabtu
                $tanggalStr = $current->format('Y-m-d');
                if (!in_array($tanggalStr, $hariLibur)) {
                    $hariLibur[] = $tanggalStr;
                }
            }
            $current->addDay();
        }

        return $hariLibur;
    }

    /**
     * Get set of (tanggal_nik) untuk tukar hari kerja dalam periode.
     * Key format: "Y-m-d_NIK" (e.g. "2026-02-24_12345")
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $tipe 'KERJA_KE_LIBUR' atau 'LIBUR_KE_KERJA'
     * @return \Illuminate\Support\Collection Set of keys (tanggal_nik)
     */
    private function getTukarHariKerjaSet($startDate, $endDate, $tipe)
    {
        $rows = TukarHariKerja::whereBetween('tanggal_libur', [$startDate, $endDate])
            ->where('vcTipeTukar', $tipe)
            ->get(['tanggal_libur', 'nik']);

        $set = collect();
        foreach ($rows as $row) {
            $tanggalStr = $row->tanggal_libur instanceof Carbon
                ? $row->tanggal_libur->format('Y-m-d')
                : Carbon::parse($row->tanggal_libur)->format('Y-m-d');
            $set->put($tanggalStr . '_' . $row->nik, true);
        }

        return $set;
    }
}

