<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\TidakMasuk;
use App\Models\HariLibur;
use App\Models\SaldoCuti;
use App\Models\Divisi;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekapitulasiCutiController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $divisiId = $request->get('divisi');
        $departemenId = $request->get('departemen');
        $groupPegawai = $request->get('group_pegawai');

        // Get divisi dan departemen untuk dropdown
        $divisis = Divisi::orderBy('vcNamaDivisi')->get();
        $departemens = Departemen::orderBy('vcNamaDept')->get();

        // Get group pegawai untuk dropdown
        $groups = Karyawan::select('Group_pegawai')
            ->whereNotNull('Group_pegawai')
            ->where('vcAktif', '1')
            ->distinct()
            ->orderBy('Group_pegawai')
            ->pluck('Group_pegawai');

        // Filter karyawan aktif
        $karyawanQuery = Karyawan::with(['departemen', 'bagian', 'divisi'])
            ->where('vcAktif', '1');

        if ($divisiId) {
            $karyawanQuery->where('Divisi', $divisiId);
        }
        if ($departemenId) {
            $karyawanQuery->where('Dept', $departemenId);
        }
        if ($groupPegawai) {
            $karyawanQuery->where('Group_pegawai', $groupPegawai);
        }

        $karyawans = $karyawanQuery->orderBy('Nama')->get();

        // Tentukan tahun dari range tanggal (gunakan tahun akhir jika range lintas tahun)
        $tahun = (int) Carbon::parse($endDate)->format('Y');
        
        // Hitung cuti bersama untuk TAHUN tersebut (bukan range tanggal)
        // Sama seperti di halaman Saldo Cuti, cuti bersama dihitung per tahun
        // Semua karyawan mendapat jumlah cuti bersama yang sama untuk tahun tersebut
        $cutiBersamaCount = HariLibur::where('vcTipeHariLibur', 'Cuti Bersama')
            ->whereYear('dtTanggal', $tahun)
            ->count();

        // Hitung rekapitulasi untuk setiap karyawan
        $rekapitulasiData = [];
        foreach ($karyawans as $index => $karyawan) {
            $rekapitulasiData[] = $this->calculateRekapitulasiCutiKaryawan(
                $karyawan,
                $startDate,
                $endDate,
                $cutiBersamaCount,
                $index + 1
            );
        }

        return view('cuti.rekapitulasi.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'divisiId' => $divisiId,
            'departemenId' => $departemenId,
            'groupPegawai' => $groupPegawai,
            'divisis' => $divisis,
            'departemens' => $departemens,
            'groups' => $groups,
            'cutiBersamaCount' => $cutiBersamaCount,
            'rekapitulasiData' => $rekapitulasiData,
        ]);
    }

    /**
     * Export rekapitulasi cuti ke Excel
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $divisiId = $request->get('divisi');
        $departemenId = $request->get('departemen');
        $groupPegawai = $request->get('group_pegawai');

        // Filter karyawan aktif
        $karyawanQuery = Karyawan::with(['departemen', 'bagian', 'divisi'])
            ->where('vcAktif', '1');

        if ($divisiId) {
            $karyawanQuery->where('Divisi', $divisiId);
        }
        if ($departemenId) {
            $karyawanQuery->where('Dept', $departemenId);
        }
        if ($groupPegawai) {
            $karyawanQuery->where('Group_pegawai', $groupPegawai);
        }

        $karyawans = $karyawanQuery->orderBy('Nama')->get();

        // Tentukan tahun dari range tanggal (gunakan tahun akhir jika range lintas tahun)
        $tahun = (int) Carbon::parse($endDate)->format('Y');
        
        // Hitung cuti bersama untuk TAHUN tersebut (bukan range tanggal)
        // Sama seperti di halaman Saldo Cuti, cuti bersama dihitung per tahun
        // Semua karyawan mendapat jumlah cuti bersama yang sama untuk tahun tersebut
        $cutiBersamaCount = HariLibur::where('vcTipeHariLibur', 'Cuti Bersama')
            ->whereYear('dtTanggal', $tahun)
            ->count();

        // Hitung rekapitulasi untuk setiap karyawan
        $rekapitulasiData = [];
        foreach ($karyawans as $index => $karyawan) {
            $rekapitulasiData[] = $this->calculateRekapitulasiCutiKaryawan(
                $karyawan,
                $startDate,
                $endDate,
                $cutiBersamaCount,
                $index + 1
            );
        }

        // Generate Excel content menggunakan format TSV (Tab Separated Values) untuk Excel
        $filename = 'Rekapitulasi_Cuti_' . Carbon::parse($startDate)->format('Ymd') . '_' . Carbon::parse($endDate)->format('Ymd') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rekapitulasiData, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // BOM untuk Excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            $this->putCsvLine($file, [
                'REKAPITULASI CUTI KARYAWAN',
                'ABN GROUP',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);
            
            $this->putCsvLine($file, [
                'Periode: ' . Carbon::parse($startDate)->format('d F Y') . ' s/d ' . Carbon::parse($endDate)->format('d F Y'),
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);
            
            $this->putCsvLine($file, []); // Baris kosong
            
            // Header tabel
            $this->putCsvLine($file, [
                'No.',
                'NIK',
                'Nama',
                'Bisnis Unit/Divisi',
                'Departemen',
                'Bagian',
                'Cuti Tahun Lalu',
                'Cuti Tahun Ini',
                'Cuti Pribadi',
                'Cuti Bersama',
                'Saldo Cuti'
            ]);
            
            // Data
            foreach ($rekapitulasiData as $data) {
                $this->putCsvLine($file, [
                    $data['no'],
                    $data['nik'],
                    $data['nama'],
                    $data['divisi'],
                    $data['departemen'],
                    $data['bagian'],
                    $data['cuti_tahun_lalu'],
                    $data['cuti_tahun_ini'],
                    $data['cuti_pribadi'],
                    $data['cuti_bersama'],
                    $data['saldo_cuti']
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper untuk menulis baris CSV dengan tab separator
     */
    private function putCsvLine($file, $data)
    {
        // Gunakan tab sebagai separator untuk Excel (lebih universal)
        $line = [];
        foreach ($data as $field) {
            // Convert ke string dan escape tab/newline
            $field = (string) $field;
            // Replace tab dengan space, newline dengan space
            $field = str_replace(["\t", "\n", "\r"], [' ', ' ', ' '], $field);
            // Jika mengandung tab atau newline, wrap dengan quotes
            if (strpos($field, "\t") !== false || strpos($field, "\n") !== false || strpos($field, '"') !== false) {
                $field = '"' . str_replace('"', '""', $field) . '"';
            }
            $line[] = $field;
        }
        fwrite($file, implode("\t", $line) . "\n");
    }

    /**
     * Hitung rekapitulasi cuti untuk satu karyawan
     */
    private function calculateRekapitulasiCutiKaryawan($karyawan, $startDate, $endDate, $cutiBersamaCount, $no)
    {
        $nik = $karyawan->Nik;
        $tanggalAwal = Carbon::parse($startDate);
        $tanggalAkhir = Carbon::parse($endDate);

        // 1. Hitung Cuti Pribadi (C010 dan C012) untuk TAHUN tersebut
        // Sama seperti di halaman Saldo Cuti, hitung untuk seluruh tahun (bukan range tanggal)
        // Tentukan tahun dari range tanggal (gunakan tahun akhir jika range lintas tahun)
        $tahun = (int) $tanggalAkhir->format('Y');
        $tanggalAwalTahun = Carbon::create($tahun, 1, 1);
        $tanggalAkhirTahun = Carbon::create($tahun, 12, 31);
        
        // Ambil semua cuti individu (C010 dan C012) yang overlap dengan tahun tersebut
        // Sama seperti perhitungan di SaldoCutiController
        $cutiPribadiRecords = TidakMasuk::where('vcNik', $nik)
            ->whereIn('vcKodeAbsen', ['C010', 'C012']) // Cuti Tahunan dan Cuti Bersama (individu)
            ->where(function ($q) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                // Query overlap: ambil semua cuti yang overlap dengan tahun tersebut
                $q->where(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    // Cuti yang dimulai di tahun ini
                    $qq->whereBetween('dtTanggalMulai', [
                        $tanggalAwalTahun->format('Y-m-d'),
                        $tanggalAkhirTahun->format('Y-m-d')
                    ]);
                })->orWhere(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    // Cuti yang berakhir di tahun ini (dimulai sebelum tahun ini)
                    $qq->where('dtTanggalMulai', '<', $tanggalAwalTahun->format('Y-m-d'))
                        ->whereBetween('dtTanggalSelesai', [
                            $tanggalAwalTahun->format('Y-m-d'),
                            $tanggalAkhirTahun->format('Y-m-d')
                        ]);
                })->orWhere(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    // Cuti yang mencakup seluruh tahun (dimulai sebelum dan berakhir sesudah)
                    $qq->where('dtTanggalMulai', '<=', $tanggalAwalTahun->format('Y-m-d'))
                        ->where('dtTanggalSelesai', '>=', $tanggalAkhirTahun->format('Y-m-d'));
                });
            })
            ->whereNotNull('dtTanggalMulai')
            ->whereNotNull('dtTanggalSelesai')
            ->get();

        // Hitung hari yang benar-benar di tahun tersebut
        $cutiPribadi = 0;
        foreach ($cutiPribadiRecords as $record) {
            $mulai = Carbon::parse($record->dtTanggalMulai);
            $selesai = Carbon::parse($record->dtTanggalSelesai);
            
            // Hitung overlap dengan tahun
            $overlapMulai = max($mulai, $tanggalAwalTahun);
            $overlapSelesai = min($selesai, $tanggalAkhirTahun);
            
            if ($overlapMulai <= $overlapSelesai) {
                $hariOverlap = $overlapMulai->diffInDays($overlapSelesai) + 1;
                $cutiPribadi += $hariOverlap;
            }
        }

        // 2. Cuti Bersama sudah dihitung di method index() dan dikirim sebagai parameter
        // Semua karyawan mendapat jumlah cuti bersama yang sama dalam periode yang sama
        $cutiBersama = $cutiBersamaCount;

        // 3. Saldo Cuti - ambil saldo sisa untuk tahun yang sesuai
        // Tahun sudah ditentukan di atas
        
        $saldoCuti = SaldoCuti::where('vcNik', $nik)
            ->where('intTahun', $tahun)
            ->first();
        
        $saldoCutiSisa = 0;
        $cutiTahunLalu = 0;
        $cutiTahunIni = 0;
        
        if ($saldoCuti) {
            // Ambil saldo awal (bukan sisa)
            $cutiTahunLalu = (int) round($saldoCuti->decTahunLalu ?? 0);
            $cutiTahunIni = (int) round($saldoCuti->decTahunIni ?? 0);
            
            // Hitung saldo sisa berdasarkan penggunaan
            $tahunLalu = $cutiTahunLalu;
            $tahunIni = $cutiTahunIni;
            
            // Hitung penggunaan cuti (C010 dan C012) untuk tahun tersebut
            $tanggalAwalTahun = Carbon::create($tahun, 1, 1);
            $tanggalAkhirTahun = Carbon::create($tahun, 12, 31);
            
            $penggunaanCuti = TidakMasuk::where('vcNik', $nik)
                ->whereIn('vcKodeAbsen', ['C010', 'C012']) // Cuti Tahunan dan Cuti Bersama
                ->where(function ($q) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    $q->where(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                        $qq->whereBetween('dtTanggalMulai', [
                            $tanggalAwalTahun->format('Y-m-d'),
                            $tanggalAkhirTahun->format('Y-m-d')
                        ]);
                    })->orWhere(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                        $qq->where('dtTanggalMulai', '<', $tanggalAwalTahun->format('Y-m-d'))
                            ->whereBetween('dtTanggalSelesai', [
                                $tanggalAwalTahun->format('Y-m-d'),
                                $tanggalAkhirTahun->format('Y-m-d')
                            ]);
                    })->orWhere(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                        $qq->where('dtTanggalMulai', '<=', $tanggalAwalTahun->format('Y-m-d'))
                            ->where('dtTanggalSelesai', '>=', $tanggalAkhirTahun->format('Y-m-d'));
                    });
                })
                ->whereNotNull('dtTanggalMulai')
                ->whereNotNull('dtTanggalSelesai')
                ->get()
                ->sum(function ($item) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    $mulai = Carbon::parse($item->dtTanggalMulai);
                    $selesai = Carbon::parse($item->dtTanggalSelesai);
                    
                    $overlapMulai = max($mulai, $tanggalAwalTahun);
                    $overlapSelesai = min($selesai, $tanggalAkhirTahun);
                    
                    if ($overlapMulai <= $overlapSelesai) {
                        return $overlapMulai->diffInDays($overlapSelesai) + 1;
                    }
                    return 0;
                });
            
            // Tambahkan cuti bersama untuk tahun tersebut
            $cutiBersamaTahun = HariLibur::where('vcTipeHariLibur', 'Cuti Bersama')
                ->whereYear('dtTanggal', $tahun)
                ->count();
            
            $totalPenggunaan = $penggunaanCuti + $cutiBersamaTahun;
            
            // Hitung saldo sisa dengan prioritas: kurangi tahun lalu dulu, baru tahun ini
            $sisaPenggunaan = $totalPenggunaan;
            $tahunLaluTerpakai = min($tahunLalu, $sisaPenggunaan);
            $sisaPenggunaan -= $tahunLaluTerpakai;
            
            $tahunIniTerpakai = min($tahunIni, $sisaPenggunaan);
            
            $saldoTahunLaluSisa = $tahunLalu - $tahunLaluTerpakai;
            $saldoTahunIniSisa = $tahunIni - $tahunIniTerpakai;
            $saldoCutiSisa = $saldoTahunLaluSisa + $saldoTahunIniSisa;
        }

        return [
            'no' => $no,
            'nik' => $karyawan->Nik,
            'nama' => $karyawan->Nama,
            'divisi' => $karyawan->divisi->vcNamaDivisi ?? '-',
            'departemen' => $karyawan->departemen->vcNamaDept ?? '-',
            'bagian' => $karyawan->bagian->vcNamaBagian ?? '-',
            'cuti_tahun_lalu' => $cutiTahunLalu,
            'cuti_tahun_ini' => $cutiTahunIni,
            'cuti_pribadi' => $cutiPribadi,
            'cuti_bersama' => $cutiBersama,
            'saldo_cuti' => $saldoCutiSisa,
        ];
    }
}


