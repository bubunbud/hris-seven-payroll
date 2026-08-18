<?php

namespace Database\Seeders;

use App\Models\MesinFingerprint;
use Illuminate\Database\Seeder;

class MesinFingerprintSeeder extends Seeder
{
    public function run(): void
    {
        $machines = [
            [
                'vcNama' => 'Gedung Utama',
                'vcMerk' => 'Solution',
                'vcTipe' => 'X302-S',
                'vcIp' => '192.168.29.9',
                'intPort' => 4370,
                'intCommKey' => 0,
                'vcAktif' => '1',
                'vcKeterangan' => 'Data awal — Gedung Utama',
            ],
            [
                'vcNama' => 'Prod1.1',
                'vcMerk' => 'Solution',
                'vcTipe' => 'X100-C',
                'vcIp' => '192.168.30.10',
                'intPort' => 4370,
                'intCommKey' => 0,
                'vcAktif' => '1',
                'vcKeterangan' => 'Data awal — Produksi 1.1',
            ],
        ];

        foreach ($machines as $row) {
            MesinFingerprint::firstOrCreate(
                ['vcIp' => $row['vcIp']],
                array_merge($row, [
                    'dtCreate' => now(),
                    'dtChange' => now(),
                ])
            );
        }
    }
}
