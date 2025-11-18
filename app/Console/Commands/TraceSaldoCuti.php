<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TidakMasuk;
use App\Models\SaldoCuti;
use App\Models\HariLibur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TraceSaldoCuti extends Command
{
    protected $signature = 'saldo-cuti:trace {nik} {tahun}';
    protected $description = 'Melacak perbedaan antara saldo cuti dan data tidak masuk untuk NIK dan tahun tertentu';

    public function handle()
    {
        $nik = $this->argument('nik');
        $tahun = (int) $this->argument('tahun');

        $this->info("=== TRACE SALDO CUTI ===");
        $this->info("NIK: {$nik}");
        $this->info("Tahun: {$tahun}");
        $this->newLine();

        // 1. Ambil saldo cuti
        $saldoCuti = SaldoCuti::where('vcNik', $nik)
            ->where('intTahun', $tahun)
            ->first();

        if ($saldoCuti) {
            $this->info("=== SALDO CUTI ===");
            $this->table(
                ['Field', 'Nilai'],
                [
                    ['Tahun Lalu', $saldoCuti->decTahunLalu ?? 0],
                    ['Tahun Ini', $saldoCuti->decTahunIni ?? 0],
                    ['Total Saldo', ($saldoCuti->decTahunLalu ?? 0) + ($saldoCuti->decTahunIni ?? 0)],
                ]
            );
        } else {
            $this->warn("Tidak ada data saldo cuti untuk NIK {$nik} tahun {$tahun}");
        }

        $this->newLine();

        // 2. Ambil semua data tidak masuk C010 untuk tahun tersebut
        $kodeCuti = ['C010', 'C012'];
        $tanggalAwalTahun = Carbon::create($tahun, 1, 1);
        $tanggalAkhirTahun = Carbon::create($tahun, 12, 31);

        $this->info("=== DATA TIDAK MASUK (C010 & C012) ===");
        $this->info("Filter: dtTanggalMulai tahun {$tahun}");
        $this->newLine();

        $tidakMasukRecords = TidakMasuk::where('vcNik', $nik)
            ->whereIn('vcKodeAbsen', $kodeCuti)
            ->whereYear('dtTanggalMulai', $tahun)
            ->get();

        if ($tidakMasukRecords->isEmpty()) {
            $this->warn("Tidak ada data tidak masuk dengan filter whereYear('dtTanggalMulai', {$tahun})");
        } else {
            $totalHari = 0;
            $detail = [];

            foreach ($tidakMasukRecords as $record) {
                $jumlahHari = $record->jumlah_hari;
                $totalHari += $jumlahHari;

                // Hitung hari yang benar-benar di tahun tersebut
                $mulai = Carbon::parse($record->dtTanggalMulai);
                $selesai = Carbon::parse($record->dtTanggalSelesai);

                $overlapMulai = max($mulai, $tanggalAwalTahun);
                $overlapSelesai = min($selesai, $tanggalAkhirTahun);

                $hariDiTahun = 0;
                if ($overlapMulai <= $overlapSelesai) {
                    $hariDiTahun = $overlapMulai->diffInDays($overlapSelesai) + 1;
                }

                $detail[] = [
                    'Kode' => $record->vcKodeAbsen,
                    'Tanggal Mulai' => $mulai->format('d/m/Y'),
                    'Tanggal Selesai' => $selesai->format('d/m/Y'),
                    'Jumlah Hari (Total)' => $jumlahHari,
                    'Hari di Tahun ' . $tahun => $hariDiTahun,
                    'Keterangan' => $record->vcKeterangan ?? '-',
                ];
            }

            $this->table(
                ['Kode', 'Tanggal Mulai', 'Tanggal Selesai', 'Jumlah Hari (Total)', 'Hari di Tahun ' . $tahun, 'Keterangan'],
                $detail
            );

            $this->newLine();
            $this->info("Total Jumlah Hari (dari jumlah_hari): {$totalHari}");
        }

        $this->newLine();

        // 3. Cek data yang mungkin terlewat (cuti yang dimulai tahun sebelumnya tapi berakhir di tahun ini)
        $this->info("=== CEK DATA YANG MUNGKIN TERLEWAT ===");
        $this->info("Cuti yang dimulai sebelum tahun {$tahun} tapi berakhir di tahun {$tahun}:");
        $this->newLine();

        $tidakMasukLintasTahun = TidakMasuk::where('vcNik', $nik)
            ->whereIn('vcKodeAbsen', $kodeCuti)
            ->where('dtTanggalMulai', '<', $tanggalAwalTahun->format('Y-m-d'))
            ->where('dtTanggalSelesai', '>=', $tanggalAwalTahun->format('Y-m-d'))
            ->where('dtTanggalSelesai', '<=', $tanggalAkhirTahun->format('Y-m-d'))
            ->get();

        if ($tidakMasukLintasTahun->isEmpty()) {
            $this->info("Tidak ada data cuti lintas tahun yang terlewat");
        } else {
            $detailLintas = [];
            foreach ($tidakMasukLintasTahun as $record) {
                $mulai = Carbon::parse($record->dtTanggalMulai);
                $selesai = Carbon::parse($record->dtTanggalSelesai);

                $overlapMulai = max($mulai, $tanggalAwalTahun);
                $overlapSelesai = min($selesai, $tanggalAkhirTahun);

                $hariDiTahun = 0;
                if ($overlapMulai <= $overlapSelesai) {
                    $hariDiTahun = $overlapMulai->diffInDays($overlapSelesai) + 1;
                }

                $detailLintas[] = [
                    'Kode' => $record->vcKodeAbsen,
                    'Tanggal Mulai' => $mulai->format('d/m/Y'),
                    'Tanggal Selesai' => $selesai->format('d/m/Y'),
                    'Jumlah Hari (Total)' => $record->jumlah_hari,
                    'Hari di Tahun ' . $tahun => $hariDiTahun,
                ];
            }

            $this->table(
                ['Kode', 'Tanggal Mulai', 'Tanggal Selesai', 'Jumlah Hari (Total)', 'Hari di Tahun ' . $tahun],
                $detailLintas
            );
        }

        $this->newLine();

        // 4. Cek cuti bersama
        $cutiBersama = HariLibur::where('vcTipeHariLibur', 'Cuti Bersama')
            ->whereYear('dtTanggal', $tahun)
            ->count();

        $this->info("=== CUTI BERSAMA ===");
        $this->info("Jumlah hari cuti bersama tahun {$tahun}: {$cutiBersama}");

        $this->newLine();

        // 5. Perhitungan yang digunakan di SaldoCutiController
        $this->info("=== PERHITUNGAN SAAT INI (SaldoCutiController) ===");
        $penggunaanCuti = TidakMasuk::where('vcNik', $nik)
            ->whereIn('vcKodeAbsen', $kodeCuti)
            ->whereYear('dtTanggalMulai', $tahun)
            ->get()
            ->sum(function ($item) {
                return $item->jumlah_hari;
            });

        $this->info("Penggunaan Cuti (whereYear dtTanggalMulai): {$penggunaanCuti}");
        $this->info("Cuti Bersama: {$cutiBersama}");
        $this->info("Total Penggunaan: " . ($penggunaanCuti + $cutiBersama));

        $this->newLine();

        // 6. Perhitungan yang seharusnya (hitung hari yang benar-benar di tahun tersebut)
        $this->info("=== PERHITUNGAN YANG SEHARUSNYA ===");
        $penggunaanCutiBenar = $this->calculateHariCutiDiTahun($nik, $kodeCuti, $tahun);
        $this->info("Penggunaan Cuti (hitung hari di tahun): {$penggunaanCutiBenar}");
        $this->info("Cuti Bersama: {$cutiBersama}");
        $this->info("Total Penggunaan: " . ($penggunaanCutiBenar + $cutiBersama));

        $this->newLine();
        $this->info("=== SELISIH ===");
        $selisih = $penggunaanCuti - $penggunaanCutiBenar;
        if ($selisih > 0) {
            $this->warn("Perhitungan saat ini LEBIH BESAR {$selisih} hari");
            $this->warn("Kemungkinan: Ada cuti yang dimulai di tahun {$tahun} tapi berakhir di tahun berikutnya");
        } elseif ($selisih < 0) {
            $this->warn("Perhitungan saat ini LEBIH KECIL " . abs($selisih) . " hari");
            $this->warn("Kemungkinan: Ada cuti yang dimulai sebelum tahun {$tahun} tapi berakhir di tahun {$tahun}");
        } else {
            $this->info("Tidak ada selisih");
        }

        return 0;
    }

    private function calculateHariCutiDiTahun($nik, $kodeCuti, $tahun)
    {
        $tanggalAwalTahun = Carbon::create($tahun, 1, 1);
        $tanggalAkhirTahun = Carbon::create($tahun, 12, 31);

        // Ambil semua cuti yang overlap dengan tahun tersebut
        $cutiRecords = TidakMasuk::where('vcNik', $nik)
            ->whereIn('vcKodeAbsen', $kodeCuti)
            ->where(function ($q) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                $q->where(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    // Cuti yang dimulai di tahun ini
                    $qq->whereBetween('dtTanggalMulai', [
                        $tanggalAwalTahun->format('Y-m-d'),
                        $tanggalAkhirTahun->format('Y-m-d')
                    ]);
                })->orWhere(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    // Cuti yang berakhir di tahun ini
                    $qq->whereBetween('dtTanggalSelesai', [
                        $tanggalAwalTahun->format('Y-m-d'),
                        $tanggalAkhirTahun->format('Y-m-d')
                    ]);
                })->orWhere(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    // Cuti yang mencakup seluruh tahun
                    $qq->where('dtTanggalMulai', '<=', $tanggalAwalTahun->format('Y-m-d'))
                        ->where('dtTanggalSelesai', '>=', $tanggalAkhirTahun->format('Y-m-d'));
                });
            })
            ->get();

        $totalHari = 0;
        foreach ($cutiRecords as $record) {
            $mulai = Carbon::parse($record->dtTanggalMulai);
            $selesai = Carbon::parse($record->dtTanggalSelesai);

            // Hitung overlap dengan tahun
            $overlapMulai = max($mulai, $tanggalAwalTahun);
            $overlapSelesai = min($selesai, $tanggalAkhirTahun);

            if ($overlapMulai <= $overlapSelesai) {
                $hariOverlap = $overlapMulai->diffInDays($overlapSelesai) + 1;
                $totalHari += $hariOverlap;
            }
        }

        return $totalHari;
    }
}





