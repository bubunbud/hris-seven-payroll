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
        Schema::create('t_tukar_hari_kerja_detail', function (Blueprint $table) {
            $table->id();
            $table->string('vcKodeTukar', 20)->comment('FK ke t_tukar_hari_kerja');
            $table->string('vcNik', 20)->comment('NIK karyawan');
            $table->date('dtTanggalLibur')->comment('Tanggal hari libur yang ditukar');
            $table->date('dtTanggalKerja')->comment('Tanggal hari kerja pengganti');
            $table->char('vcStatus', 1)->default('1')->comment('1=Aktif, 0=Nonaktif');
            $table->dateTime('dtCreatedAt')->nullable()->comment('Waktu dibuat');
            $table->dateTime('dtUpdatedAt')->nullable()->comment('Waktu diubah');
            
            $table->unique(['vcKodeTukar', 'vcNik', 'dtTanggalLibur'], 'uk_tukar_nik_tanggal');
            $table->index('vcKodeTukar', 'idx_kode_tukar');
            $table->index('vcNik', 'idx_nik');
            $table->index('dtTanggalLibur', 'idx_tanggal_libur');
            $table->index('dtTanggalKerja', 'idx_tanggal_kerja');
            
            // Foreign key constraints - dibuat setelah tabel dibuat
            // Note: Foreign key akan dibuat di migration terpisah jika diperlukan
            // Untuk sementara, foreign key tidak dibuat karena bisa menyebabkan error jika tabel sudah ada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_tukar_hari_kerja_detail');
    }
};
