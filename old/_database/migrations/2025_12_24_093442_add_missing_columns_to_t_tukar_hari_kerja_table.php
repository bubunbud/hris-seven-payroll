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
        Schema::table('t_tukar_hari_kerja', function (Blueprint $table) {
            // Cek apakah kolom sudah ada sebelum menambahkan
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'vcKeterangan')) {
                $table->string('vcKeterangan', 255)->nullable()->comment('Keterangan/alasan tukar hari kerja');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'vcTipeTukar')) {
                $table->enum('vcTipeTukar', ['LIBUR_KE_KERJA', 'KERJA_KE_LIBUR'])->default('LIBUR_KE_KERJA')->comment('Tipe tukar hari kerja');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'vcScope')) {
                $table->enum('vcScope', ['PERORANGAN', 'GROUP', 'SEMUA_BU'])->default('PERORANGAN')->comment('Scope penetapan');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'vcKodeDivisi')) {
                $table->string('vcKodeDivisi', 10)->nullable()->comment('Kode divisi (jika scope GROUP atau SEMUA_BU)');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'vcKodeDept')) {
                $table->string('vcKodeDept', 10)->nullable()->comment('Kode departemen (jika scope GROUP)');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'vcKodeBagian')) {
                $table->string('vcKodeBagian', 10)->nullable()->comment('Kode bagian (jika scope GROUP)');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'dtTanggalMulai')) {
                $table->date('dtTanggalMulai')->nullable()->comment('Tanggal mulai efektif');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'dtTanggalSelesai')) {
                $table->date('dtTanggalSelesai')->nullable()->comment('Tanggal selesai efektif');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'vcCreatedBy')) {
                $table->string('vcCreatedBy', 50)->nullable()->comment('User yang membuat');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'dtCreatedAt')) {
                $table->dateTime('dtCreatedAt')->nullable()->comment('Waktu dibuat');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'vcUpdatedBy')) {
                $table->string('vcUpdatedBy', 50)->nullable()->comment('User yang mengubah');
            }
            
            if (!Schema::hasColumn('t_tukar_hari_kerja', 'dtUpdatedAt')) {
                $table->dateTime('dtUpdatedAt')->nullable()->comment('Waktu diubah');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_tukar_hari_kerja', function (Blueprint $table) {
            $columns = ['vcKeterangan', 'vcTipeTukar', 'vcScope', 'vcKodeDivisi', 'vcKodeDept', 'vcKodeBagian', 'dtTanggalMulai', 'dtTanggalSelesai', 'vcCreatedBy', 'dtCreatedAt', 'vcUpdatedBy', 'dtUpdatedAt'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('t_tukar_hari_kerja', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
