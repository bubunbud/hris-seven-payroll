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
        Schema::create('t_perjalanan_dinas_hotel', function (Blueprint $table) {
            $table->string('vcCounterHotel', 50)->primary()->comment('Counter untuk detail hotel');
            $table->string('vcNoRpd', 50)->comment('No RPD (Foreign Key)');
            $table->boolean('isMenginap')->default(false)->comment('Apakah Menginap');
            $table->date('dtTanggalMenginap')->nullable()->comment('Tanggal Menginap');
            $table->string('vcKotaProvinsiNegara', 200)->nullable()->comment('Kota/Provinsi/Negara');
            $table->string('vcNamaHotel', 200)->nullable()->comment('Nama Hotel/Penginapan');
            $table->text('vcKeteranganHotel')->nullable()->comment('Keterangan Hotel');
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
        Schema::dropIfExists('t_perjalanan_dinas_hotel');
    }
};
