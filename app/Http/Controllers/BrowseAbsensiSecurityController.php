<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\TidakMasuk;
use App\Services\SecurityAbsensiService;
use App\Traits\HariKerjaHelper;
use App\Traits\TidakMasukOverlapHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BrowseAbsensiSecurityController extends Controller
{
    use HariKerjaHelper, TidakMasukOverlapHelper;

    /**
     * Browse Absensi Security/Satpam - khusus untuk Group Security
     * Dengan parameter: shift terjadwal, shift aktual, telat (menit), pulang cepat (menit), kepatuhan
     */
    public function index(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $search = $request->get('search');
        $filterStatus = $request->get('filter_status', ''); // sesuai, tidak_sesuai, tidak_masuk, telat, pulang_cepat

        // Karyawan Security untuk autocomplete
        $karyawans = Karyawan::where('Group_pegawai', 'Security')
            ->where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->orderBy('Nama')
            ->get(['Nik', 'Nama']);

        $karyawanList = $karyawans->map(function ($k) {
            return [
                'nik' => $k->Nik ?: '',
                'nama' => $k->Nama ?: '',
                'search' => strtolower(($k->Nik ?: '') . ' ' . ($k->Nama ?: '')),
            ];
        })->values();

        $hariLiburList = $this->getHariLiburWithTukar($startDate, $endDate);

        // Build search filter
        $searchTerms = $search ? preg_split('/,\s*/', trim($search)) : [];

        // Query absen - HANYA Security
        $absenQuery = DB::table('t_absen')
            ->join('m_karyawan', 't_absen.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->where('m_karyawan.Group_pegawai', 'Security')
            ->whereBetween('t_absen.dtTanggal', [$startDate, $endDate])
            ->select(
                't_absen.dtTanggal',
                't_absen.vcNik',
                't_absen.dtJamMasuk',
                't_absen.dtJamKeluar',
                't_absen.vcketerangan',
                'm_karyawan.Nama',
                'm_karyawan.Divisi',
                'm_karyawan.vcKodeBagian',
                'm_divisi.vcNamaDivisi',
                'm_bagian.vcNamaBagian'
            );

        if (!empty($searchTerms)) {
            $absenQuery->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if ($term === '') continue;
                    if (strpos($term, ' - ') !== false) {
                        $term = explode(' - ', $term)[0];
                    }
                    $q->orWhere('m_karyawan.Nik', 'like', '%' . $term . '%')
                        ->orWhere('m_karyawan.Nama', 'like', '%' . $term . '%');
                }
            });
        }

        // Jadwal security untuk periode
        $jadwalSecurity = DB::table('t_jadwal_shift_security')
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($item) {
                return $item->vcNik . '_' . $item->dtTanggal;
            })
            ->map(function ($group) {
                return $group->pluck('intShift')->filter()->values()->toArray();
            });

        // Tidak masuk untuk Security
        $tidakMasukQuery = DB::table('t_tidak_masuk')
            ->join('m_karyawan', 't_tidak_masuk.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_jenis_absen', 't_tidak_masuk.vcKodeAbsen', '=', 'm_jenis_absen.vcKodeAbsen')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->where('m_karyawan.Group_pegawai', 'Security')
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

        if (!empty($searchTerms)) {
            $tidakMasukQuery->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if ($term === '') continue;
                    if (strpos($term, ' - ') !== false) {
                        $term = explode(' - ', $term)[0];
                    }
                    $q->orWhere('m_karyawan.Nik', 'like', '%' . $term . '%')
                        ->orWhere('m_karyawan.Nama', 'like', '%' . $term . '%');
                }
            });
        }

        $tidakMasukRecords = $tidakMasukQuery->get();

        // Absen exists untuk exclude dari tidak masuk
        $absenExists = collect();
        DB::table('t_absen')
            ->join('m_karyawan', 't_absen.vcNik', '=', 'm_karyawan.Nik')
            ->where('m_karyawan.Group_pegawai', 'Security')
            ->whereBetween('t_absen.dtTanggal', [$startDate, $endDate])
            ->select('t_absen.dtTanggal', 't_absen.vcNik')
            ->orderBy('t_absen.dtTanggal')
            ->orderBy('t_absen.vcNik')
            ->chunk(2000, function ($chunk) use (&$absenExists) {
                foreach ($chunk as $item) {
                    $absenExists->put($item->dtTanggal . '_' . $item->vcNik, true);
                }
            });

        $tidakMasukExpanded = $this->expandTidakMasukUniquePerDay(
            $tidakMasukRecords,
            Carbon::parse($startDate),
            Carbon::parse($endDate),
            $absenExists
        );

        // Proses data absen
        $combinedData = collect();
        $absenQuery->orderBy('t_absen.dtTanggal', 'desc')->orderBy('t_absen.vcNik')
            ->chunk(500, function ($absensData) use (&$combinedData, $jadwalSecurity) {
                foreach ($absensData as $absen) {
                    $key = $absen->vcNik . '_' . $absen->dtTanggal;
                    $shiftTerjadwal = $jadwalSecurity->get($key, []);
                    $shiftAktual = SecurityAbsensiService::determineShiftFromTime(
                        $absen->dtJamMasuk,
                        $absen->dtJamKeluar,
                        $absen->dtTanggal
                    );
                    $validasi = SecurityAbsensiService::validateAbsensiVsJadwal(
                        $absen->vcNik,
                        $absen->dtTanggal,
                        $shiftAktual
                    );

                    // Hitung telat & pulang cepat (gunakan shift aktual, atau shift pertama di jadwal)
                    $shiftUntukHitung = $shiftAktual ?? (isset($shiftTerjadwal[0]) ? $shiftTerjadwal[0] : null);
                    $telatMenit = 0;
                    $pulangCepatMenit = 0;
                    if ($shiftUntukHitung && $absen->dtJamMasuk && $absen->dtJamKeluar) {
                        $telatMenit = SecurityAbsensiService::calculateTelatMenit(
                            $absen->dtJamMasuk,
                            $shiftUntukHitung,
                            $absen->dtTanggal
                        );
                        $pulangCepatMenit = SecurityAbsensiService::calculatePulangCepatMenit(
                            $absen->dtJamKeluar,
                            $shiftUntukHitung,
                            $absen->dtTanggal
                        );
                    }

                    // Kepatuhan: sesuai jika status_validasi = sesuai DAN tidak telat DAN tidak pulang cepat
                    $kepatuhan = 'Tidak Masuk';
                    if ($absen->dtJamMasuk && $absen->dtJamKeluar) {
                        if ($validasi['status'] === 'sesuai' && $telatMenit === 0 && $pulangCepatMenit === 0) {
                            $kepatuhan = 'Sesuai';
                        } elseif ($validasi['status'] === 'tidak_sesuai') {
                            $kepatuhan = 'Tidak Sesuai Jadwal';
                        } elseif ($telatMenit > 0 && $pulangCepatMenit > 0) {
                            $kepatuhan = 'Telat & Pulang Cepat';
                        } elseif ($telatMenit > 0) {
                            $kepatuhan = 'Telat';
                        } elseif ($pulangCepatMenit > 0) {
                            $kepatuhan = 'Pulang Cepat';
                        } else {
                            $kepatuhan = 'Sesuai';
                        }
                    }

                    $totalJam = $this->calculateTotalJam($absen->dtJamMasuk, $absen->dtJamKeluar, $absen->dtTanggal);

                    $combinedData->push([
                        'dtTanggal' => $absen->dtTanggal,
                        'vcNik' => $absen->vcNik,
                        'Nama' => $absen->Nama,
                        'vcNamaDivisi' => $absen->vcNamaDivisi,
                        'vcNamaBagian' => $absen->vcNamaBagian,
                        'dtJamMasuk' => $absen->dtJamMasuk,
                        'dtJamKeluar' => $absen->dtJamKeluar,
                        'total_jam' => $totalJam,
                        'vcketerangan' => $absen->vcketerangan,
                        'shift_terjadwal' => $shiftTerjadwal,
                        'shift_aktual' => $shiftAktual,
                        'status_validasi' => $validasi['status'],
                        'telat_menit' => $telatMenit,
                        'pulang_cepat_menit' => $pulangCepatMenit,
                        'kepatuhan' => $kepatuhan,
                        'source' => 'absen',
                    ]);
                }
            });

        // Tambah data tidak masuk
        foreach ($tidakMasukExpanded as $tm) {
            $combinedData->push([
                'dtTanggal' => $tm['dtTanggal'],
                'vcNik' => $tm['vcNik'],
                'Nama' => $tm['Nama'],
                'vcNamaDivisi' => $tm['vcNamaDivisi'],
                'vcNamaBagian' => $tm['vcNamaBagian'],
                'dtJamMasuk' => null,
                'dtJamKeluar' => null,
                'total_jam' => 0,
                'vcketerangan' => $tm['vcKeterangan'] ?? null,
                'shift_terjadwal' => [],
                'shift_aktual' => null,
                'status_validasi' => null,
                'telat_menit' => 0,
                'pulang_cepat_menit' => 0,
                'kepatuhan' => $tm['jenis_absen_keterangan'] ?? $tm['vcKodeAbsen'] ?? 'Tidak Masuk',
                'source' => 'tidak_masuk',
            ]);
        }

        // Filter status
        if ($filterStatus) {
            $combinedData = $combinedData->filter(function ($item) use ($filterStatus) {
                if ($filterStatus === 'sesuai') return ($item['kepatuhan'] ?? '') === 'Sesuai';
                if ($filterStatus === 'tidak_sesuai') return ($item['kepatuhan'] ?? '') === 'Tidak Sesuai Jadwal';
                if ($filterStatus === 'tidak_masuk') return ($item['source'] ?? '') === 'tidak_masuk';
                if ($filterStatus === 'telat') return ($item['telat_menit'] ?? 0) > 0;
                if ($filterStatus === 'pulang_cepat') return ($item['pulang_cepat_menit'] ?? 0) > 0;
                return true;
            })->values();
        }

        // Sort
        $combinedData = $combinedData->sort(function ($a, $b) {
            $c = strcmp($b['dtTanggal'], $a['dtTanggal']);
            return $c !== 0 ? $c : strcmp($a['vcNik'], $b['vcNik']);
        })->values();

        // Summary
        $summary = [
            'total' => $combinedData->count(),
            'sesuai' => $combinedData->where('kepatuhan', 'Sesuai')->count(),
            'telat' => $combinedData->filter(fn($x) => ($x['telat_menit'] ?? 0) > 0)->count(),
            'pulang_cepat' => $combinedData->filter(fn($x) => ($x['pulang_cepat_menit'] ?? 0) > 0)->count(),
            'tidak_sesuai' => $combinedData->where('kepatuhan', 'Tidak Sesuai Jadwal')->count(),
            'tidak_masuk' => $combinedData->where('source', 'tidak_masuk')->count(),
            'total_telat_menit' => $combinedData->sum('telat_menit'),
            'total_pulang_cepat_menit' => $combinedData->sum('pulang_cepat_menit'),
        ];

        // Pagination
        $perPage = 50;
        $currentPage = $request->get('page', 1);
        $total = $combinedData->count();
        $items = $combinedData->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $absens = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('browse-absensi-security.index', compact(
            'absens',
            'startDate',
            'endDate',
            'search',
            'filterStatus',
            'karyawanList',
            'summary',
            'hariLiburList'
        ));
    }

    /**
     * Hitung durasi jam kerja (handle cross-day untuk Shift 3)
     */
    private function calculateTotalJam($dtJamMasuk, $dtJamKeluar, $dtTanggal)
    {
        if (!$dtJamMasuk || !$dtJamKeluar) {
            return 0;
        }

        $tanggal = Carbon::parse($dtTanggal);
        $jamMasukStr = substr((string) $dtJamMasuk, 0, 5);
        $jamKeluarStr = substr((string) $dtJamKeluar, 0, 5);

        $masuk = $tanggal->copy()->setTimeFromTimeString($jamMasukStr);
        $keluar = $tanggal->copy()->setTimeFromTimeString($jamKeluarStr);

        if ($keluar->lessThan($masuk)) {
            $keluar->addDay();
        }

        return round($masuk->diffInHours($keluar, true), 1);
    }
}
