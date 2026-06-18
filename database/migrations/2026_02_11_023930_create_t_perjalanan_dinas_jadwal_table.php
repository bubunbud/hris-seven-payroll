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
        Schema::create('t_perjalanan_dinas_jadwal', function (Blueprint $table) {
            $table->string('vcCounterJadwal', 50)->primary()->comment('Counter untuk detail jadwal');
            $table->string('vcNoRpd', 50)->comment('No RPD (Foreign Key)');
            $table->string('vcModaPerjalanan', 50)->comment('Moda Perjalanan: Kendaraan Dinas, Kendaraan Pribadi, Kendaraan Umum');
            // Berangkat
            $table->string('vcHariBerangkat', 20)->nullable()->comment('Hari Berangkat');
            $table->date('dtTanggalBerangkat')->nullable()->comment('Tanggal Berangkat');
            $table->time('dtJamBerangkat')->nullable()->comment('Jam Berangkat');
            $table->string('vcKeteranganBerangkat', 200)->nullable()->comment('Keterangan Berangkat');
            // Kembali
            $table->string('vcHariKembali', 20)->nullable()->comment('Hari Kembali');
            $table->date('dtTanggalKembali')->nullable()->comment('Tanggal Kembali');
            $table->time('dtJamKembali')->nullable()->comment('Jam Kembali');
            $table->string('vcKeteranganKembali', 200)->nullable()->comment('Keterangan Kembali');
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
        Schema::dropIfExists('t_perjalanan_dinas_jadwal');
    }
};
