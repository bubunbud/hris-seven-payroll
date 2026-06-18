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
        Schema::create('t_periode_thr', function (Blueprint $table) {
            // Primary key: composite key (dtPeriode, dtKategori, vcKodeDivisi)
            $table->string('dtPeriode', 4)->comment('Tahun periode THR (contoh: 2025)');
            $table->string('dtKategori', 50)->comment('Hari Keagamaan: Islam (Idul Fitri), Kristen (Natal), Hindu (Nyepi), Budha (Waisak), Lainnya');
            $table->string('vcKodeDivisi', 10)->comment('Kode Divisi/Bisnis Unit');
            
            // Field lainnya
            $table->date('dtCutoffTHR')->comment('Tanggal Patokan Perhitungan THR');
            $table->string('vcKeterangan', 255)->nullable()->comment('Keterangan free text');
            $table->string('vcStatus', 1)->default('0')->comment('0=Belum proses, 1=Sudah diproses');
            $table->datetime('dtCreate')->nullable()->comment('Tanggal buat');
            
            // Primary key composite
            $table->primary(['dtPeriode', 'dtKategori', 'vcKodeDivisi']);
            
            // Indexes
            $table->index('dtPeriode');
            $table->index('dtKategori');
            $table->index('vcKodeDivisi');
            $table->index('vcStatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_periode_thr');
    }
};
