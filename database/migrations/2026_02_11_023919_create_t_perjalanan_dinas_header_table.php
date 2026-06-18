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
        Schema::create('t_perjalanan_dinas_header', function (Blueprint $table) {
            $table->string('vcNoRpd', 50)->primary()->comment('No RPD (Rencana Perjalanan Dinas)');
            $table->date('dtTanggalForm')->comment('Tanggal Form Dinas');
            $table->string('vcPemberiTugas', 100)->comment('Nama Pemberi Tugas');
            $table->string('vcJabatanPemberiTugas', 100)->nullable()->comment('Jabatan Pemberi Tugas');
            $table->string('vcTujuanDinas', 200)->comment('Tujuan Dinas (Tempat/Instansi/Negara)');
            $table->text('vcMaksudPerjalananDinas')->nullable()->comment('Maksud/Uraian Perjalanan Dinas');
            // Otorisasi
            $table->string('vcMengajukan', 100)->nullable()->comment('Mengajukan - Penerima Tugas');
            $table->string('vcMenyetujui', 100)->nullable()->comment('Menyetujui - Pemberi Tugas');
            $table->string('vcMengetahui', 100)->nullable()->comment('Mengetahui - HRD');
            // Audit
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_perjalanan_dinas_header');
    }
};
