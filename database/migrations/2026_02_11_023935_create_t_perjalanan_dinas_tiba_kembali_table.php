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
        Schema::create('t_perjalanan_dinas_tiba_kembali', function (Blueprint $table) {
            $table->string('vcCounterTibaKembali', 50)->primary()->comment('Counter untuk detail tiba/kembali');
            $table->string('vcNoRpd', 50)->comment('No RPD (Foreign Key)');
            // Tiba
            $table->string('vcHariTiba', 20)->nullable()->comment('Hari Tiba');
            $table->date('dtTanggalTiba')->nullable()->comment('Tanggal Tiba');
            $table->time('dtJamTiba')->nullable()->comment('Jam Tiba');
            // Kembali
            $table->string('vcHariKembali', 20)->nullable()->comment('Hari Kembali');
            $table->date('dtTanggalKembali')->nullable()->comment('Tanggal Kembali');
            $table->time('dtJamKembali')->nullable()->comment('Jam Kembali');
            // Keterangan & Tanda Tangan
            $table->text('vcKeteranganKedatangan')->nullable()->comment('Keterangan/Uraian Kedatangan');
            $table->string('vcTandaTanganPihakBerwenang', 100)->nullable()->comment('Tanda Tangan Pihak Berwenang di Tempat Tujuan');
            // Audit
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
            
            // Foreign key
            $table->foreign('vcNoRpd')->references('vcNoRpd')->on('t_perjalanan_dinas_header')->onDelete('cascade');
            $table->index('vcNoRpd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_perjalanan_dinas_tiba_kembali');
    }
};
