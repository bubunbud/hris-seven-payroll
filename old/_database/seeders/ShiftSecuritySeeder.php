<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftSecuritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $shifts = [
            [
                'vcKodeShift' => 1,
                'vcNamaShift' => 'Shift 1',
                'dtJamMasuk' => '06:30:00',
                'dtJamPulang' => '14:30:00',
                'isCrossDay' => false,
                'intDurasiJam' => 8.00,
                'intToleransiMasuk' => 30,
                'intToleransiPulang' => 30,
                'vcKeterangan' => 'Pagi',
                'dtCreate' => $now,
                'dtChange' => $now,
            ],
            [
                'vcKodeShift' => 2,
                'vcNamaShift' => 'Shift 2',
                'dtJamMasuk' => '14:30:00',
                'dtJamPulang' => '22:30:00',
                'isCrossDay' => false,
                'intDurasiJam' => 8.00,
                'intToleransiMasuk' => 30,
                'intToleransiPulang' => 30,
                'vcKeterangan' => 'Siang',
                'dtCreate' => $now,
                'dtChange' => $now,
            ],
            [
                'vcKodeShift' => 3,
                'vcNamaShift' => 'Shift 3',
                'dtJamMasuk' => '22:30:00',
                'dtJamPulang' => '06:30:00',
                'isCrossDay' => true,
                'intDurasiJam' => 8.00,
                'intToleransiMasuk' => 30,
                'intToleransiPulang' => 30,
                'vcKeterangan' => 'Malam (Cross-Day)',
                'dtCreate' => $now,
                'dtChange' => $now,
            ],
        ];

        foreach ($shifts as $shift) {
            DB::table('m_shift_security')->updateOrInsert(
                ['vcKodeShift' => $shift['vcKodeShift']],
                $shift
            );
        }
    }
}
