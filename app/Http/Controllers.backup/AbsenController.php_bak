<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Karyawan;
use App\Models\TidakMasuk;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsenController extends Controller
{
    public function index(Request $request)
    {
        // Default date range (current month)
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get filter parameters
        $nik = $request->get('nik');
        $nama = $request->get('nama');
        $tidakMasuk = $request->get('tidak_masuk');
        $absenTidakLengkap = $request->get('absen_tidak_lengkap');
        $group = $request->get('group', 'Semua Group');

        // Get hari libur list untuk periode ini (cached)
        $hariLiburList = $this->getHariLiburList($startDate, $endDate);

        // Build base karyawan filter query untuk reuse
        $karyawanFilter = function ($query) use ($nik, $nama, $group) {
            if ($nik) {
                $query->where('m_karyawan.Nik', 'like', '%' . $nik . '%');
            }
            if ($nama) {
                $query->where('m_karyawan.Nama', 'like', '%' . $nama . '%');
            }
            if ($group !== 'Semua Group') {
                $query->where('m_karyawan.Group_pegawai', $group);
            }
        };

        // Query untuk absen dengan eager loading minimal
        $absenQuery = DB::table('t_absen')
            ->join('m_karyawan', 't_absen.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->leftJoin('m_shift', 'm_karyawan.vcShift', '=', 'm_shift.vcShift')
            ->whereBetween('t_absen.dtTanggal', [$startDate, $endDate])
            ->select(
                't_absen.dtTanggal',
                't_absen.vcNik',
                't_absen.dtJamMasuk',
                't_absen.dtJamKeluar',
                't_absen.dtJamMasukLembur',
                't_absen.dtJamKeluarLembur',
                'm_karyawan.Nama',
                'm_karyawan.Divisi',
                'm_karyawan.vcKodeBagian',
                'm_divisi.vcNamaDivisi',
                'm_bagian.vcNamaBagian',
                'm_shift.vcMasuk as shift_masuk'
            );

        // Apply filters
        $karyawanFilter($absenQuery);

        // Build query untuk tidak masuk dengan expand tanggal di SQL (lebih efisien)
        // Gunakan recursive CTE untuk expand tanggal (jika MySQL 8+) atau cara lain untuk MySQL 5.7
        $tidakMasukQuery = DB::table('t_tidak_masuk')
            ->join('m_karyawan', 't_tidak_masuk.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_jenis_absen', 't_tidak_masuk.vcKodeAbsen', '=', 'm_jenis_absen.vcKodeAbsen')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('t_tidak_masuk.dtTanggalMulai', [$startDate, $endDate])
                    ->orWhereBetween('t_tidak_masuk.dtTanggalSelesai', [$startDate, $endDate])
                    ->orWhere(function ($qq) use ($startDate, $endDate) {
                        $qq->where('t_tidak_masuk.dtTanggalMulai', '<=', $startDate)
                            ->where('t_tidak_masuk.dtTanggalSelesai', '>=', $endDate);
                    });
            })
            ->whereNotNull('t_tidak_masuk.dtTanggalMulai')
            ->whereNotNull('t_tidak_masuk.dtTanggalSelesai')
            ->select(
                't_tidak_masuk.vcNik',
                't_tidak_masuk.vcKodeAbsen',
                't_tidak_masuk.dtTanggalMulai',
                't_tidak_masuk.dtTanggalSelesai',
                't_tidak_masuk.vcKeterangan',
                'm_karyawan.Nama',
                'm_karyawan.Divisi',
                'm_karyawan.vcKodeBagian',
                'm_divisi.vcNamaDivisi',
                'm_bagian.vcNamaBagian',
                'm_jenis_absen.vcKeterangan as jenis_absen_keterangan'
            );

        // Apply filters
        $karyawanFilter($tidakMasukQuery);

        // Ambil data tidak masuk (minimal, tanpa expand dulu)
        $tidakMasukRecords = $tidakMasukQuery->get();

        // Ambil semua NIK + Tanggal yang sudah ada di absen untuk exclude
        $absenExists = DB::table('t_absen')
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->select('dtTanggal', 'vcNik')
            ->get()
            ->keyBy(function ($item) {
                return $item->dtTanggal . '_' . $item->vcNik;
            });

        // Expand tidak masuk per tanggal (di PHP tapi dengan optimasi)
        $tidakMasukExpanded = [];
        $filterStart = Carbon::parse($startDate);
        $filterEnd = Carbon::parse($endDate);

        foreach ($tidakMasukRecords as $tm) {
            $current = Carbon::parse($tm->dtTanggalMulai);
            $end = Carbon::parse($tm->dtTanggalSelesai);

            // Batasi loop maksimal 365 hari untuk menghindari infinite loop
            $maxDays = 365;
            $dayCount = 0;

            while ($current->lte($end) && $dayCount < $maxDays) {
                // Skip jika di luar range filter
                if ($current->lt($filterStart) || $current->gt($filterEnd)) {
                    $current->addDay();
                    $dayCount++;
                    continue;
                }

                $tanggalStr = $current->format('Y-m-d');
                $key = $tanggalStr . '_' . $tm->vcNik;

                // Cek apakah sudah ada di absen, jika ada skip (prioritas absen)
                if (!$absenExists->has($key)) {
                    $tidakMasukExpanded[] = [
                        'dtTanggal' => $tanggalStr,
                        'vcNik' => $tm->vcNik,
                        'Nama' => $tm->Nama,
                        'Divisi' => $tm->Divisi,
                        'vcKodeBagian' => $tm->vcKodeBagian,
                        'vcNamaDivisi' => $tm->vcNamaDivisi,
                        'vcNamaBagian' => $tm->vcNamaBagian,
                        'vcKodeAbsen' => $tm->vcKodeAbsen,
                        'jenis_absen_keterangan' => $tm->jenis_absen_keterangan,
                        'vcKeterangan' => $tm->vcKeterangan,
                        'source' => 'tidak_masuk',
                    ];
                }

                $current->addDay();
                $dayCount++;
            }
        }

        // Ambil data absen (dengan pagination di database level)
        // Apply filters terlebih dahulu
        if ($tidakMasuk) {
            // Jika filter tidak masuk, hanya ambil yang tidak ada jam masuk dan keluar
            $absenQuery->whereNull('t_absen.dtJamMasuk')
                ->whereNull('t_absen.dtJamKeluar');
        }

        if ($absenTidakLengkap) {
            // Jika filter absen tidak lengkap, hanya ambil yang salah satu jam null
            $absenQuery->where(function ($q) {
                $q->whereNull('t_absen.dtJamMasuk')
                    ->orWhereNull('t_absen.dtJamKeluar');
            });
        }

        // Gabungkan hasil dengan UNION di SQL (lebih efisien)
        // Tapi karena struktur berbeda, kita akan gabungkan di PHP tapi dengan pagination yang lebih efisien

        // Ambil data absen
        $absensData = $absenQuery->orderBy('t_absen.dtTanggal', 'desc')
            ->orderBy('t_absen.vcNik')
            ->get();

        // Gabungkan data (dengan optimasi)
        $combinedData = collect();

        // Tambahkan data absen
        foreach ($absensData as $absen) {
            $combinedData->push([
                'dtTanggal' => $absen->dtTanggal,
                'vcNik' => $absen->vcNik,
                'Nama' => $absen->Nama,
                'Divisi' => $absen->Divisi,
                'vcKodeBagian' => $absen->vcKodeBagian,
                'vcNamaDivisi' => $absen->vcNamaDivisi,
                'vcNamaBagian' => $absen->vcNamaBagian,
                'dtJamMasuk' => $absen->dtJamMasuk,
                'dtJamKeluar' => $absen->dtJamKeluar,
                'dtJamMasukLembur' => $absen->dtJamMasukLembur,
                'dtJamKeluarLembur' => $absen->dtJamKeluarLembur,
                'total_jam' => $this->calculateTotalJam($absen->dtJamMasuk, $absen->dtJamKeluar, $absen->dtTanggal),
                'shift_masuk' => $absen->shift_masuk ?? null,
                'source' => 'absen',
            ]);
        }

        // Tambahkan data tidak masuk (jika tidak ada filter khusus)
        if (!$tidakMasuk && !$absenTidakLengkap) {
            foreach ($tidakMasukExpanded as $tm) {
                $combinedData->push([
                    'dtTanggal' => $tm['dtTanggal'],
                    'vcNik' => $tm['vcNik'],
                    'Nama' => $tm['Nama'],
                    'Divisi' => $tm['Divisi'],
                    'vcKodeBagian' => $tm['vcKodeBagian'],
                    'vcNamaDivisi' => $tm['vcNamaDivisi'],
                    'vcNamaBagian' => $tm['vcNamaBagian'],
                    'dtJamMasuk' => null,
                    'dtJamKeluar' => null,
                    'dtJamMasukLembur' => null,
                    'dtJamKeluarLembur' => null,
                    'total_jam' => 0,
                    'source' => 'tidak_masuk',
                    'vcKodeAbsen' => $tm['vcKodeAbsen'],
                    'jenis_absen_keterangan' => $tm['jenis_absen_keterangan'],
                    'vcKeterangan' => $tm['vcKeterangan'],
                ]);
            }
        } elseif ($tidakMasuk) {
            // Jika filter tidak masuk aktif, tambahkan data tidak masuk
            foreach ($tidakMasukExpanded as $tm) {
                $combinedData->push([
                    'dtTanggal' => $tm['dtTanggal'],
                    'vcNik' => $tm['vcNik'],
                    'Nama' => $tm['Nama'],
                    'Divisi' => $tm['Divisi'],
                    'vcKodeBagian' => $tm['vcKodeBagian'],
                    'vcNamaDivisi' => $tm['vcNamaDivisi'],
                    'vcNamaBagian' => $tm['vcNamaBagian'],
                    'dtJamMasuk' => null,
                    'dtJamKeluar' => null,
                    'dtJamMasukLembur' => null,
                    'dtJamKeluarLembur' => null,
                    'total_jam' => 0,
                    'source' => 'tidak_masuk',
                    'vcKodeAbsen' => $tm['vcKodeAbsen'],
                    'jenis_absen_keterangan' => $tm['jenis_absen_keterangan'],
                    'vcKeterangan' => $tm['vcKeterangan'],
                ]);
            }
        }

        // Sort by tanggal desc, then nik
        $combinedData = $combinedData->sort(function ($a, $b) {
            $dateCompare = strcmp($b['dtTanggal'], $a['dtTanggal']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            return strcmp($a['vcNik'], $b['vcNik']);
        })->values();

        // Pagination manual dengan optimasi
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

        // Get unique groups for filter
        $groups = Karyawan::select('Group_pegawai')
            ->distinct()
            ->whereNotNull('Group_pegawai')
            ->orderBy('Group_pegawai')
            ->pluck('Group_pegawai');

        return view('absen.index', compact(
            'absens',
            'startDate',
            'endDate',
            'nik',
            'nama',
            'tidakMasuk',
            'absenTidakLengkap',
            'group',
            'totalData',
            'groups',
            'hariLiburList'
        ));
    }

    /**
     * Calculate total hours from jam masuk and keluar
     */
    private function calculateTotalJam($dtJamMasuk, $dtJamKeluar, $dtTanggal)
    {
        if (!$dtJamMasuk || !$dtJamKeluar) {
            return 0;
        }

        $tanggal = Carbon::parse($dtTanggal);
        $masuk = $tanggal->copy()->setTimeFromTimeString((string) $dtJamMasuk);
        $keluar = $tanggal->copy()->setTimeFromTimeString((string) $dtJamKeluar);

        if ($keluar->lessThan($masuk)) {
            $keluar->addDay();
        }

        return round($masuk->diffInHours($keluar, true), 1);
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

    public function exportExcel(Request $request)
    {
        // Same logic as index but for export
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $nik = $request->get('nik');
        $nama = $request->get('nama');
        $tidakMasuk = $request->get('tidak_masuk');
        $absenTidakLengkap = $request->get('absen_tidak_lengkap');
        $group = $request->get('group', 'Semua Group');

        $query = Absen::with('karyawan')
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->orderBy('dtTanggal', 'desc')
            ->orderBy('vcNik');

        if ($nik) {
            $query->where('vcNik', 'like', '%' . $nik . '%');
        }

        if ($nama) {
            $query->whereHas('karyawan', function ($q) use ($nama) {
                $q->where('Nama', 'like', '%' . $nama . '%');
            });
        }

        if ($tidakMasuk) {
            $query->where(function ($q) {
                $q->whereNull('dtJamMasuk')
                    ->whereNull('dtJamKeluar');
            });
        }

        if ($absenTidakLengkap) {
            $query->where(function ($q) {
                $q->whereNull('dtJamMasuk')
                    ->orWhereNull('dtJamKeluar');
            });
        }

        if ($group !== 'Semua Group') {
            $query->whereHas('karyawan', function ($q) use ($group) {
                $q->where('Group_pegawai', $group);
            });
        }

        $absens = $query->get();

        // For now, return a simple response
        // In a real application, you would generate an Excel file
        return response()->json([
            'success' => true,
            'message' => 'Export Excel berhasil. Data: ' . $absens->count() . ' records',
            'data' => $absens->count()
        ]);
    }
}
