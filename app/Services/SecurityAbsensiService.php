<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SecurityAbsensiService
{
    /**
     * Tentukan shift dari jam masuk/pulang
     * 
     * @param string $jamMasuk Format: HH:mm:ss
     * @param string $jamPulang Format: HH:mm:ss
     * @param string $tanggal Format: Y-m-d
     * @return int|null 1, 2, 3, atau null jika tidak terdeteksi
     */
    public static function determineShiftFromTime($jamMasuk, $jamPulang, $tanggal)
    {
        if (!$jamMasuk || !$jamPulang) {
            return null;
        }

        $masuk = Carbon::parse($tanggal . ' ' . $jamMasuk);
        $pulang = Carbon::parse($tanggal . ' ' . $jamPulang);

        // Handle cross-day (Shift 3)
        if ($pulang->lessThan($masuk)) {
            $pulang->addDay();
            // Validasi: masuk >= 22:00 atau pulang <= 07:00
            $jamMasukStr = $masuk->format('H:i');
            $jamPulangStr = $pulang->format('H:i');
            if ($jamMasukStr >= '22:00' || $jamPulangStr <= '07:00') {
                return 3;
            }
        }

        $jamMasukStr = $masuk->format('H:i');
        $jamPulangStr = $pulang->format('H:i');

        // Shift 1: 06:30 - 14:30 (toleransi: masuk 06:00-08:00, pulang 14:00-15:00)
        if (
            $jamMasukStr >= '06:00' && $jamMasukStr <= '08:00' &&
            $jamPulangStr >= '14:00' && $jamPulangStr <= '15:00'
        ) {
            return 1;
        }

        // Shift 2: 14:30 - 22:30 (toleransi: masuk 14:00-15:00, pulang 22:00-23:00)
        if (
            $jamMasukStr >= '14:00' && $jamMasukStr <= '15:00' &&
            $jamPulangStr >= '22:00' && $jamPulangStr <= '23:00'
        ) {
            return 2;
        }

        // Shift 3: 22:30 - 06:30 (sudah di-handle di atas, tapi double check)
        if ($jamMasukStr >= '22:00' || $jamPulangStr <= '07:00') {
            return 3;
        }

        return null; // Tidak terdeteksi
    }

    /**
     * Validasi absensi sesuai jadwal
     * 
     * @param string $vcNik
     * @param string $tanggal Format: Y-m-d
     * @param int|null $shiftAktual Shift yang terdeteksi dari jam absensi
     * @return array ['status' => 'sesuai|tidak_sesuai|tidak_masuk|tidak_ada_jadwal', 'message' => ...]
     */
    public static function validateAbsensiVsJadwal($vcNik, $tanggal, $shiftAktual)
    {
        // Ambil jadwal untuk tanggal tersebut
        $jadwal = DB::table('t_jadwal_shift_security')
            ->where('vcNik', $vcNik)
            ->where('dtTanggal', $tanggal)
            ->pluck('intShift')
            ->toArray();

        // Jika tidak ada jadwal
        if (empty($jadwal)) {
            return [
                'status' => 'tidak_ada_jadwal',
                'message' => 'Tidak ada jadwal untuk tanggal ini'
            ];
        }

        // Jika tidak ada absensi
        if (!$shiftAktual) {
            return [
                'status' => 'tidak_masuk',
                'message' => 'Tidak ada absensi'
            ];
        }

        // Cek apakah shift aktual ada di jadwal
        if (in_array($shiftAktual, $jadwal)) {
            return [
                'status' => 'sesuai',
                'message' => 'Absensi sesuai jadwal'
            ];
        }

        // Jika tidak sesuai
        return [
            'status' => 'tidak_sesuai',
            'message' => 'Jadwal: ' . implode(',', $jadwal) . ', Aktual: ' . $shiftAktual
        ];
    }

    /**
     * Get jadwal shift untuk satpam pada tanggal tertentu
     * 
     * @param string $vcNik
     * @param string $tanggal Format: Y-m-d
     * @return array Array of shift numbers [1, 2, 3]
     */
    public static function getJadwalShift($vcNik, $tanggal)
    {
        return DB::table('t_jadwal_shift_security')
            ->where('vcNik', $vcNik)
            ->where('dtTanggal', $tanggal)
            ->pluck('intShift')
            ->toArray();
    }
}

