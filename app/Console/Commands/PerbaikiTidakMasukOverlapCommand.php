<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PerbaikiTidakMasukOverlapCommand extends Command
{
    protected $signature = 'tidak-masuk:perbaiki-overlap
                            {--nik= : Batasi ke satu NIK}
                            {--selesai= : Tanggal selesai yang diduga salah (Y-m-d), default deteksi otomatis}
                            {--min-records=5 : Minimal jumlah record dengan selesai sama per NIK}
                            {--grace-days=14 : Record mulai >= (selesai - grace days) tidak diubah}
                            {--execute : Jalankan update (tanpa flag = dry-run)}';

    protected $description = 'Perbaiki dtTanggalSelesai riwayat lama yang overlap massal (set selesai = mulai untuk rentang tidak aktif)';

    public function handle(): int
    {
        $nikFilter = $this->option('nik');
        $selesaiFilter = $this->option('selesai');
        $minRecords = (int) $this->option('min-records');
        $graceDays = (int) $this->option('grace-days');
        $execute = (bool) $this->option('execute');

        $groupsQuery = DB::table('t_tidak_masuk')
            ->select('vcNik', 'dtTanggalSelesai', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('dtTanggalMulai')
            ->whereNotNull('dtTanggalSelesai')
            ->whereColumn('dtTanggalMulai', '<', 'dtTanggalSelesai')
            ->groupBy('vcNik', 'dtTanggalSelesai')
            ->having('cnt', '>=', $minRecords);

        if ($nikFilter) {
            $groupsQuery->where('vcNik', $nikFilter);
        }
        if ($selesaiFilter) {
            $groupsQuery->where('dtTanggalSelesai', $selesaiFilter);
        }

        $groups = $groupsQuery->orderByDesc('cnt')->get();

        if ($groups->isEmpty()) {
            $this->info('Tidak ada pola overlap massal yang memenuhi kriteria.');

            return self::SUCCESS;
        }

        $this->info(($execute ? 'EXECUTE' : 'DRY-RUN') . ' — pola selesai massal per NIK:');
        $totalWouldFix = 0;

        foreach ($groups as $group) {
            $cutoff = date('Y-m-d', strtotime($group->dtTanggalSelesai . " -{$graceDays} days"));

            $fixQuery = DB::table('t_tidak_masuk')
                ->where('vcNik', $group->vcNik)
                ->where('dtTanggalSelesai', $group->dtTanggalSelesai)
                ->where('dtTanggalMulai', '<', $cutoff)
                ->whereColumn('dtTanggalMulai', '<', 'dtTanggalSelesai');

            $count = (clone $fixQuery)->count();
            if ($count === 0) {
                continue;
            }

            $this->line("  NIK {$group->vcNik} | selesai={$group->dtTanggalSelesai} | grup={$group->cnt} | akan diperbaiki={$count}");
            $totalWouldFix += $count;

            if ($execute) {
                $updated = $fixQuery->update([
                    'dtTanggalSelesai' => DB::raw('dtTanggalMulai'),
                ]);
                $this->line("    -> updated {$updated} baris");
            }
        }

        $this->newLine();
        if (!$execute) {
            $this->warn("Dry-run: {$totalWouldFix} baris akan di-set dtTanggalSelesai = dtTanggalMulai.");
            $this->warn('Jalankan ulang dengan --execute untuk menerapkan.');
        } else {
            $this->info("Selesai. {$totalWouldFix} baris diperbaiki.");
        }

        return self::SUCCESS;
    }
}
