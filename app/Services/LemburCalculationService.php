<?php

namespace App\Services;

use Carbon\Carbon;

class LemburCalculationService
{
    /**
     * Calculate lembur nominal based on standard formula
     * 
     * @param float $gapokPerBulan Gaji pokok per bulan (Upah + Tunjangan)
     * @param float $totalJamLembur Total jam lembur (dalam jam, bisa desimal)
     * @param bool $isHariLibur Apakah hari libur
     * @return array ['nominal' => float, 'jam_kerja_1' => float, 'jam_kerja_2' => float, 'rupiah_kerja_1' => float, 'rupiah_kerja_2' => float, 'jam_libur_2' => float, 'jam_libur_3' => float, 'rupiah_libur_2' => float, 'rupiah_libur_3' => float]
     */
    public static function calculateLemburNominal($gapokPerBulan, $totalJamLembur, $isHariLibur = false)
    {
        $ratePerJam = $gapokPerBulan / 173;

        $jamKerja1 = 0;
        $rupiahKerja1 = 0;
        $jamKerja2 = 0;
        $rupiahKerja2 = 0;
        $jamKerja3 = 0;
        $rupiahKerja3 = 0;
        $jamKerja4 = 0;
        $rupiahKerja4 = 0;
        $jamLibur2 = 0;
        $rupiahLibur2 = 0;
        $jamLibur3 = 0;
        $rupiahLibur3 = 0;
        $rupiahLibur3x = 0;
        $rupiahLibur4x = 0;
        $totalNominal = 0;

        if ($isHariLibur) {
            // Hari libur: 2x (8 jam pertama), 3x (jam ke-9), 4x (jam ke-10 sampai 12)
            if ($totalJamLembur > 0) {
                $jam1 = min(8, $totalJamLembur);
                $jam2 = $totalJamLembur > 8 ? min(1, $totalJamLembur - 8) : 0;
                $jam3 = $totalJamLembur > 9 ? min(3, $totalJamLembur - 9) : 0;

                $rupiah2 = $jam1 * 2 * $ratePerJam;
                $rupiahLibur3x = $jam2 * 3 * $ratePerJam;
                $rupiahLibur4x = $jam3 * 4 * $ratePerJam;
                $rupiah3 = $rupiahLibur3x + $rupiahLibur4x;

                $jamLibur2 = $jam1;
                $jamLibur3 = $jam2 + $jam3;
                $rupiahLibur2 = $rupiah2;
                $rupiahLibur3 = $rupiah3;

                $totalNominal = $rupiahLibur2 + $rupiahLibur3;
            }
        } else {
            // Hari kerja normal (HKN): J1 1.5× (max 1 jam), J2 2× (max 8 jam), J3 3× (max 1 jam), J4 4× (sisa)
            if ($totalJamLembur > 0) {
                $jam1 = min(1, $totalJamLembur);
                $jam2 = min(8, max(0, $totalJamLembur - 1));
                $jam3 = min(1, max(0, $totalJamLembur - 9));
                $jam4 = max(0, $totalJamLembur - 10);

                $rupiah1 = $jam1 * 1.5 * $ratePerJam;
                $rupiah2 = $jam2 * 2 * $ratePerJam;
                $rupiah3 = $jam3 * 3 * $ratePerJam;
                $rupiah4 = $jam4 * 4 * $ratePerJam;

                $jamKerja1 = $jam1;
                $jamKerja2 = $jam2;
                $jamKerja3 = $jam3;
                $jamKerja4 = $jam4;
                $rupiahKerja1 = $rupiah1;
                $rupiahKerja2 = $rupiah2;
                $rupiahKerja3 = $rupiah3;
                $rupiahKerja4 = $rupiah4;

                $totalNominal = $rupiahKerja1 + $rupiahKerja2 + $rupiahKerja3 + $rupiahKerja4;
            }
        }

        return [
            'nominal' => round($totalNominal, 2),
            'jam_kerja_1' => $jamKerja1,
            'jam_kerja_2' => $jamKerja2,
            'jam_kerja_3' => $jamKerja3,
            'jam_kerja_4' => $jamKerja4,
            'rupiah_kerja_1' => round($rupiahKerja1, 2),
            'rupiah_kerja_2' => round($rupiahKerja2, 2),
            'rupiah_kerja_3' => round($rupiahKerja3, 2),
            'rupiah_kerja_4' => round($rupiahKerja4, 2),
            'jam_libur_2' => $jamLibur2,
            'jam_libur_3' => $jamLibur3,
            'rupiah_libur_2' => round($rupiahLibur2, 2),
            'rupiah_libur_3' => round($rupiahLibur3, 2),
            'rupiah_libur_3x' => round($rupiahLibur3x, 2),
            'rupiah_libur_4x' => round($rupiahLibur4x, 2),
        ];
    }

    /**
     * Calculate total jam lembur from jam mulai and jam selesai
     * 
     * @param string $jamMulai Format: HH:MM
     * @param string $jamSelesai Format: HH:MM
     * @param string $tanggal Format: Y-m-d
     * @param int $durasiIstirahat Durasi istirahat dalam menit
     * @return float Total jam lembur (dibulatkan 2 desimal)
     */
    public static function calculateTotalJamLembur($jamMulai, $jamSelesai, $tanggal, $durasiIstirahat = 0)
    {
        $tanggalObj = Carbon::parse($tanggal);
        $masukLembur = $tanggalObj->copy()->setTimeFromTimeString($jamMulai);
        $keluarLembur = $tanggalObj->copy()->setTimeFromTimeString($jamSelesai);

        if ($keluarLembur->lessThan($masukLembur)) {
            $keluarLembur->addDay();
        }

        // Hitung total menit lembur
        $totalMenitLembur = $masukLembur->diffInMinutes($keluarLembur, true);

        // Kurangi waktu istirahat (dalam menit) jika ada
        if ($durasiIstirahat > 0) {
            $totalMenitLembur = max(0, $totalMenitLembur - $durasiIstirahat);
        }

        // Konversi ke jam (dibulatkan 2 desimal)
        return round($totalMenitLembur / 60, 2);
    }

    /**
     * Check if date is holiday
     * 
     * @param string $tanggal Format: Y-m-d
     * @param array $hariLiburList Array of dates (Y-m-d format)
     * @return bool
     */
    public static function isHariLibur($tanggal, $hariLiburList = [])
    {
        $tanggalObj = Carbon::parse($tanggal);
        $tanggalStr = $tanggalObj->format('Y-m-d');

        // Check if in hari libur list
        if (in_array($tanggalStr, $hariLiburList)) {
            return true;
        }

        // Check if weekend (Sabtu = 6, Minggu = 0)
        $dayOfWeek = $tanggalObj->dayOfWeek;
        if (in_array($dayOfWeek, [0, 6])) {
            return true;
        }

        return false;
    }
}


