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
        Schema::create('t_lembur_detail', function (Blueprint $table) {
            $table->string('vcCounterDetail', 15)->primary(); // Counter untuk detail
            $table->string('vcCounterHeader', 12); // FK ke t_lembur_header
            $table->string('vcNik', 8); // NIK Karyawan
            $table->string('vcNamaKaryawan', 150)->nullable(); // Nama (denormalisasi untuk display)
            $table->string('vcKodeJabatan', 10)->nullable(); // Jabatan
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
            
            $table->index('vcCounterHeader');
            $table->index('vcNik');
            
            // Foreign key constraint (optional, bisa di-comment jika ada masalah)
            // $table->foreign('vcCounterHeader')->references('vcCounter')->on('t_lembur_header')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_lembur_detail');
    }
};
