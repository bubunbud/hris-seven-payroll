<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Absen;
use App\Models\Karyawan;
use App\Models\Gapok;
use App\Models\HariLibur;
use App\Models\TukarHariKerja;
use App\Traits\HariKerjaHelper;
use Carbon\Carbon;

class DiagnoseClosingMakanTransport extends Command
{
    use HariKerjaHelper;

    protected $signature = 'closing:diagnose-makan-transport {nik} {periode_awal} {periode_akhir}';
    protected $description = 'Diagnosa kenapa intMakanKerja dan intTransportKerja = 0 untuk NIK tertentu';

    public function handle()
    {
        $nik = (string) $this->argument('nik');
        $periodeAwal = Carbon::parse($this->argument('periode_awal'))->format('Y-m-d');
        $periodeAkhir = Carbon::parse($this->argument('periode_akhir'))->format('Y-m-d');

        $this->info("=== DIAGNOSA INTMAKANKERJA / INTTRANSPORTKERJA = 0 ===");
        $this->info("NIK: {$nik}");
        $this->info("Periode: {$periodeAwal} s/d {$periodeAkhir}");
        $this->newLine();

        // 1. Cek karyawan
        $karyawan = Karyawan::where('Nik', $nik)->first();
        if (!$karyawan) {
            $this->error("Karyawan NIK {$nik} tidak ditemukan di m_karyawan.");
            return 1;
        }
        $this->info("Karyawan: {$karyawan->Nama} | Gol: {$karyawan->Gol}");

        // 2. Cek golongan & uang makan/transport
        $gapok = Gapok::find($karyawan->Gol);
        if ($gapok) {
            $this->info("Gapok: uang_makan=" . ($gapok->uang_makan ?? 0) . ", uang_transport=" . ($gapok->uang_transport ?? 0));
        }
        $this->newLine();

        // 3. Ambil absensi
        $absensi = Absen::where('vcNik', $nik)
            ->whereBetween('dtTanggal', [$periodeAwal, $periodeAkhir])
            ->orderBy('dtTanggal')
            ->get();

        if ($absensi->isEmpty()) {
            $this->warn("TIDAK ADA DATA ABSENSI di t_absen untuk periode ini.");
            $this->warn("Ini penyebab utama intMakanKerja = 0 dan intTransportKerja = 0.");
            return 0;
        }

        $this->info("Jumlah record t_absen: " . $absensi->count());
        $this->newLine();

        // 4. Hari libur dalam periode
        $hariLiburList = HariLibur::whereBetween('dtTanggal', [$periodeAwal, $periodeAkhir])
            ->pluck('dtTanggal')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();
        $this->info("Hari libur (m_hari_libur): " . implode(', ', $hariLiburList ?: ['-']));

        // 5. Tukar hari kerja KERJA_KE_LIBUR untuk NIK ini
        $kerjaKeLibur = TukarHariKerja::where('nik', $nik)
            ->where('vcTipeTukar', 'KERJA_KE_LIBUR')
            ->whereBetween('tanggal_libur', [$periodeAwal, $periodeAkhir])
            ->pluck('tanggal_libur')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();
        $this->info("Tukar KERJA_KE_LIBUR (tanggal jadi libur): " . implode(', ', $kerjaKeLibur ?: ['-']));

        // 6. Tukar hari kerja LIBUR_KE_KERJA untuk NIK ini
        $liburKeKerja = TukarHariKerja::where('nik', $nik)
            ->where('vcTipeTukar', 'LIBUR_KE_KERJA')
            ->whereBetween('tanggal_libur', [$periodeAwal, $periodeAkhir])
            ->pluck('tanggal_libur')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();
        $this->info("Tukar LIBUR_KE_KERJA (tanggal jadi kerja): " . implode(', ', $liburKeKerja ?: ['-']));
        $this->newLine();

        // 7. Loop tiap absensi - simulasi calculateTunjanganMakanTransport
        $rows = [];
        $makanKerja = 0;
        $transportKerja = 0;

        foreach ($absensi as $absen) {
            $tanggalStr = $absen->dtTanggal instanceof Carbon
                ? $absen->dtTanggal->format('Y-m-d')
                : Carbon::parse($absen->dtTanggal)->format('Y-m-d');

            $dow = Carbon::parse($tanggalStr)->dayOfWeek;
            $dayName = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][$dow];

            $jamMasuk = $absen->dtJamMasuk ?? '-';
            $jamKeluar = $absen->dtJamKeluar ?? '-';

            $adaJamMasukAtauKeluar = !empty($absen->dtJamMasuk) || !empty($absen->dtJamKeluar);
            $isHariKerjaNormal = $this->isHariKerjaNormal($tanggalStr, $nik);

            $hitungMakanTransport = $adaJamMasukAtauKeluar && $isHariKerjaNormal;
            if ($hitungMakanTransport) {
                $makanKerja++;
                $transportKerja++;
            }

            $alasan = '-';
            if (!$adaJamMasukAtauKeluar) {
                $alasan = 'dtJamMasuk dan dtJamKeluar kosong';
            } elseif (!$isHariKerjaNormal) {
                $alasan = 'Hari libur (weekend/tukar KERJA_KE_LIBUR)';
            } else {
                $alasan = 'OK → dihitung';
            }

            $rows[] = [
                $tanggalStr,
                $dayName,
                $jamMasuk,
                $jamKeluar,
                $adaJamMasukAtauKeluar ? 'Ya' : 'Tidak',
                $isHariKerjaNormal ? 'Ya' : 'Tidak',
                $alasan,
            ];
        }

        $this->table(
            ['Tanggal', 'Hari', 'JamMasuk', 'JamKeluar', 'Ada Jam?', 'Hari Kerja?', 'Alasan'],
            $rows
        );

        $this->newLine();
        $this->info("=== HASIL PERHITUNGAN ===");
        $this->info("intMakanKerja  : {$makanKerja}");
        $this->info("intTransportKerja: {$transportKerja}");

        if ($makanKerja == 0 && $transportKerja == 0) {
            $this->newLine();
            $this->warn("PENYEBAB NILAI 0:");
            $kosongSemua = $absensi->every(fn($a) => empty($a->dtJamMasuk) && empty($a->dtJamKeluar));
            if ($kosongSemua) {
                $this->warn("- Semua record t_absen memiliki dtJamMasuk dan dtJamKeluar KOSONG.");
            } else {
                $this->warn("- Semua tanggal yang punya jam masuk/keluar dianggap HARI LIBUR (weekend atau tukar KERJA_KE_LIBUR).");
            }
        }

        return 0;
    }
}
