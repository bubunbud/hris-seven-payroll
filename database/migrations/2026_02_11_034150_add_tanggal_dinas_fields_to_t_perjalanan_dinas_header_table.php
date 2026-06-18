<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_perjalanan_dinas_header', function (Blueprint $table) {
            if (!Schema::hasColumn('t_perjalanan_dinas_header', 'dtTanggalDinasDari')) {
                $table->date('dtTanggalDinasDari')->nullable()->after('dtTanggalForm')->comment('Tanggal Dinas Dari');
            }
            if (!Schema::hasColumn('t_perjalanan_dinas_header', 'dtTanggalDinasSampai')) {
                $table->date('dtTanggalDinasSampai')->nullable()->after('dtTanggalDinasDari')->comment('Tanggal Dinas Sampai');
            }
            if (!Schema::hasColumn('t_perjalanan_dinas_header', 'intDurasiHari')) {
                $table->integer('intDurasiHari')->nullable()->after('dtTanggalDinasSampai')->comment('Durasi Perjalanan Dinas (dalam hari)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_perjalanan_dinas_header', function (Blueprint $table) {
            if (Schema::hasColumn('t_perjalanan_dinas_header', 'intDurasiHari')) {
                $table->dropColumn('intDurasiHari');
            }
            if (Schema::hasColumn('t_perjalanan_dinas_header', 'dtTanggalDinasSampai')) {
                $table->dropColumn('dtTanggalDinasSampai');
            }
            if (Schema::hasColumn('t_perjalanan_dinas_header', 'dtTanggalDinasDari')) {
                $table->dropColumn('dtTanggalDinasDari');
            }
        });
    }
};
