<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tunjangan jabatan (sumber: t_hutang_piutang vcJenis = 5).
     */
    public function up(): void
    {
        if (! Schema::hasTable('t_closing')) {
            return;
        }

        Schema::table('t_closing', function (Blueprint $table) {
            if (! Schema::hasColumn('t_closing', 'decTunjanganJabatan')) {
                $table->decimal('decTunjanganJabatan', 12, 2)
                    ->default(0)
                    ->after('decRapel')
                    ->comment('Tunjangan jabatan (hutang piutang jenis 5)');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('t_closing')) {
            return;
        }

        Schema::table('t_closing', function (Blueprint $table) {
            if (Schema::hasColumn('t_closing', 'decTunjanganJabatan')) {
                $table->dropColumn('decTunjanganJabatan');
            }
        });
    }
};
