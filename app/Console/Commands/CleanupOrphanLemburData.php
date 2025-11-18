<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupOrphanLemburData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lembur:cleanup-orphan 
                            {--dry-run : Menampilkan data yang akan dihapus tanpa menghapus}
                            {--force : Menghapus data tanpa konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan data t_absen yang memiliki vcCounter tapi tidak ada di t_lembur_header (orphan data)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Memulai proses cleanup data orphan...');
        $this->newLine();

        // Cari data t_absen yang memiliki vcCounter tapi tidak ada di t_lembur_header
        $orphanData = DB::table('t_absen')
            ->whereNotNull('vcCounter')
            ->whereNotIn('vcCounter', function ($query) {
                $query->select('vcCounter')
                    ->from('t_lembur_header');
            })
            ->get();

        $count = $orphanData->count();

        if ($count === 0) {
            $this->info('✓ Tidak ada data orphan yang ditemukan.');
            return 0;
        }

        $this->warn("Ditemukan {$count} record dengan vcCounter yang tidak valid:");
        $this->newLine();

        // Tampilkan summary
        $this->table(
            ['Tanggal', 'NIK', 'vcCounter', 'Jam Masuk', 'Jam Keluar'],
            $orphanData->map(function ($item) {
                return [
                    $item->dtTanggal ?? '-',
                    $item->vcNik ?? '-',
                    $item->vcCounter ?? '-',
                    $item->dtJamMasuk ?? '-',
                    $item->dtJamKeluar ?? '-',
                ];
            })->toArray()
        );

        if ($dryRun) {
            $this->newLine();
            $this->info('Mode DRY-RUN: Data tidak akan dihapus.');
            $this->info("Total {$count} record akan dihapus jika command dijalankan tanpa --dry-run.");
            return 0;
        }

        if (!$force) {
            if (!$this->confirm("Apakah Anda yakin ingin menghapus {$count} record ini? (vcCounter akan di-set ke null)")) {
                $this->info('Operasi dibatalkan.');
                return 0;
            }
        }

        $this->newLine();
        $this->info('Memproses cleanup...');

        // Update: set vcCounter, dtJamMasukLembur, dtJamKeluarLembur ke null
        // dan intDurasiIstirahat ke 0, vcCfmLembur ke '0'
        $updated = DB::table('t_absen')
            ->whereNotNull('vcCounter')
            ->whereNotIn('vcCounter', function ($query) {
                $query->select('vcCounter')
                    ->from('t_lembur_header');
            })
            ->update([
                'vcCounter' => null,
                'dtJamMasukLembur' => null,
                'dtJamKeluarLembur' => null,
                'intDurasiIstirahat' => 0,
                'vcCfmLembur' => '0',
                'dtChange' => now(),
            ]);

        $this->info("✓ Berhasil membersihkan {$updated} record.");
        $this->newLine();

        // Log untuk audit
        Log::info("Cleanup orphan lembur data: {$updated} records cleaned", [
            'command' => 'lembur:cleanup-orphan',
            'count' => $updated,
        ]);

        return 0;
    }
}





