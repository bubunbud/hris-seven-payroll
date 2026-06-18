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
        Schema::create('t_lembur_header', function (Blueprint $table) {
            $table->string('vcCounter', 12)->primary(); // Counter untuk header
            $table->string('vcBusinessUnit', 50)->nullable(); // Business Unit / Divisi
            $table->string('vcKodeDept', 10)->nullable(); // Departemen
            $table->string('vcKodeBagian', 10)->nullable(); // Bagian / Seksi
            $table->date('dtTanggalLembur');
            $table->string('vcAlasanDasarLembur', 200)->nullable(); // Alasan / Dasar Lembur
            $table->decimal('decRencanaDurasiJam', 5, 2)->nullable(); // Rencana Durasi (Jam)
            $table->string('dtRencanaDariPukul', 8)->nullable(); // Dari Pukul (HH:MM:SS)
            $table->string('dtRencanaSampaiPukul', 8)->nullable(); // Sampai Pukul (HH:MM:SS)
            $table->string('vcDiajukanOleh', 100)->nullable(); // Diajukan Oleh
            $table->string('vcPenanggungBiaya', 20)->nullable(); // Penanggung Biaya (Produksi, Proyek, Maintenance, Lainnya)
            $table->string('vcPenanggungBiayaLainnya', 100)->nullable(); // Jika Lainnya
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
            
            $table->index('dtTanggalLembur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_lembur_header');
    }
};
