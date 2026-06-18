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
        Schema::create('t_tukar_hari_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('vcKodeTukar', 20)->unique()->comment('Kode unik tukar hari kerja');
            $table->date('dtTanggalLibur')->comment('Tanggal hari libur yang ditukar');
            $table->date('dtTanggalKerja')->comment('Tanggal hari kerja pengganti');
            $table->string('vcKeterangan', 255)->nullable()->comment('Keterangan/alasan tukar hari kerja');
            $table->enum('vcTipeTukar', ['LIBUR_KE_KERJA', 'KERJA_KE_LIBUR'])->comment('Tipe tukar hari kerja');
            $table->enum('vcScope', ['PERORANGAN', 'GROUP', 'SEMUA_BU'])->comment('Scope penetapan');
            $table->string('vcKodeDivisi', 10)->nullable()->comment('Kode divisi (jika scope GROUP atau SEMUA_BU)');
            $table->string('vcKodeDept', 10)->nullable()->comment('Kode departemen (jika scope GROUP)');
            $table->string('vcKodeBagian', 10)->nullable()->comment('Kode bagian (jika scope GROUP)');
            $table->date('dtTanggalMulai')->nullable()->comment('Tanggal mulai efektif');
            $table->date('dtTanggalSelesai')->nullable()->comment('Tanggal selesai efektif');
            $table->char('vcStatus', 1)->default('1')->comment('1=Aktif, 0=Nonaktif');
            $table->string('vcCreatedBy', 50)->nullable()->comment('User yang membuat');
            $table->dateTime('dtCreatedAt')->nullable()->comment('Waktu dibuat');
            $table->string('vcUpdatedBy', 50)->nullable()->comment('User yang mengubah');
            $table->dateTime('dtUpdatedAt')->nullable()->comment('Waktu diubah');
            
            $table->index('dtTanggalLibur', 'idx_tanggal_libur');
            $table->index('dtTanggalKerja', 'idx_tanggal_kerja');
            $table->index(['vcScope', 'vcKodeDivisi', 'vcKodeDept', 'vcKodeBagian'], 'idx_scope');
            $table->index('vcStatus', 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_tukar_hari_kerja');
    }
};
