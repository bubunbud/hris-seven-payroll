<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jabatan + nama file dokumen SK (Surat Keputusan) untuk riwayat mutasi (tabel existing).
     */
    public function up(): void
    {
        if (! Schema::hasTable('t_mutasi')) {
            return;
        }

        Schema::table('t_mutasi', function (Blueprint $table) {
            if (! Schema::hasColumn('t_mutasi', 'vcJabatan')) {
                $table->string('vcJabatan', 150)->nullable()->after('vcSeksi');
            }
            if (! Schema::hasColumn('t_mutasi', 'vcFileSK')) {
                $table->string('vcFileSK', 255)->nullable()->after('vcJabatan');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('t_mutasi')) {
            return;
        }

        Schema::table('t_mutasi', function (Blueprint $table) {
            if (Schema::hasColumn('t_mutasi', 'vcFileSK')) {
                $table->dropColumn('vcFileSK');
            }
            if (Schema::hasColumn('t_mutasi', 'vcJabatan')) {
                $table->dropColumn('vcJabatan');
            }
        });
    }
};
