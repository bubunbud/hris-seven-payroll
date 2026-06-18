<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Memastikan kolom closing yang dibutuhkan ClosingController ada.
 * Tanpa ->after() agar tidak gagal diam-diam jika urutan kolom DB berbeda.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_closing')) {
            return;
        }

        Schema::table('t_closing', function (Blueprint $table) {
            if (! Schema::hasColumn('t_closing', 'decTunjanganJabatan')) {
                $table->decimal('decTunjanganJabatan', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('t_closing', 'decJamLemburKerja4')) {
                $table->decimal('decJamLemburKerja4', 12, 2)->nullable()->default(0);
            }
            if (! Schema::hasColumn('t_closing', 'decLemburKerja4')) {
                $table->decimal('decLemburKerja4', 15, 2)->nullable()->default(0);
            }
            if (! Schema::hasColumn('t_closing', 'decTotallembur4')) {
                $table->decimal('decTotallembur4', 15, 2)->nullable()->default(0);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('t_closing')) {
            return;
        }

        Schema::table('t_closing', function (Blueprint $table) {
            $drops = [];
            foreach (['decTotallembur4', 'decLemburKerja4', 'decJamLemburKerja4', 'decTunjanganJabatan'] as $col) {
                if (Schema::hasColumn('t_closing', $col)) {
                    $drops[] = $col;
                }
            }
            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
