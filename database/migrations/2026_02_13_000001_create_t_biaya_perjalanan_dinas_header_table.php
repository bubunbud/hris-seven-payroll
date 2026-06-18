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
        Schema::create('t_biaya_perjalanan_dinas_header', function (Blueprint $table) {
            $table->string('vcNoBpd', 50)->primary()->comment('No BPD (Biaya Perjalanan Dinas)');
            $table->string('vcNoRpd', 50)->comment('No RPD (Foreign Key ke t_perjalanan_dinas_header)');
            $table->string('vcPemberiTugas', 100)->comment('Nama Pemberi Tugas');
            // Kasbon
            $table->decimal('decKasbonNilai', 15, 2)->nullable()->default(0)->comment('Nilai Kasbon Perjalanan Dinas');
            $table->string('vcKasbonTerbilang', 500)->nullable()->comment('Kasbon Terbilang (dalam kata-kata)');
            // Summary
            $table->decimal('decTotalPengeluaran', 15, 2)->nullable()->default(0)->comment('Total Pengeluaran');
            $table->decimal('decKekuranganKelebihan', 15, 2)->nullable()->default(0)->comment('Kekurangan/Kelebihan');
            // Laporan
            $table->text('vcLaporanSingkat')->nullable()->comment('Laporan Singkat');
            // Otorisasi
            $table->string('vcMelaporkan', 100)->nullable()->comment('Melaporkan - Penerima Tugas');
            $table->string('vcMenyetujui', 100)->nullable()->comment('Menyetujui - Pemberi Tugas');
            $table->string('vcMengetahuiHrd', 100)->nullable()->comment('Mengetahui - HRD');
            $table->string('vcMengetahuiFinance', 100)->nullable()->comment('Mengetahui - Finance & Accounting');
            // Audit
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
            
            // Foreign key
            $table->foreign('vcNoRpd')->references('vcNoRpd')->on('t_perjalanan_dinas_header')->onDelete('restrict');
            $table->index('vcNoRpd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_biaya_perjalanan_dinas_header');
    }
};







