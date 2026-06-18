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
        Schema::create('m_saldo_cuti', function (Blueprint $table) {
            $table->string('vcNik', 8); // FK ke m_karyawan
            $table->integer('intTahun'); // Tahun cuti (misal: 2024, 2025)
            $table->decimal('decSaldoAwal', 5, 2)->default(0); // Saldo awal cuti tahunan
            $table->decimal('decSaldoTambahan', 5, 2)->default(0); // Tambahan cuti jika ada
            $table->decimal('decSaldoDigunakan', 5, 2)->default(0); // Total yang sudah digunakan (auto calculate)
            $table->decimal('decSaldoSisa', 5, 2)->default(0); // Sisa saldo (auto calculate)
            $table->text('vcKeterangan')->nullable(); // Catatan
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
            
            $table->primary(['vcNik', 'intTahun']);
            $table->index('intTahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_saldo_cuti');
    }
};
