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
        Schema::create('t_biaya_perjalanan_dinas_detail', function (Blueprint $table) {
            $table->string('vcCounterDetail', 50)->primary()->comment('Counter untuk detail biaya');
            $table->string('vcNoBpd', 50)->comment('No BPD (Foreign Key)');
            // Kategori Biaya
            $table->string('vcKategoriBiaya', 50)->comment('Kategori: Penginapan, Kendaraan Umum, Kendaraan Dinas/Pribadi, Makan/Minum, Lain-lain');
            $table->string('vcSubKategori', 100)->nullable()->comment('Sub Kategori: Bensin, Tol, Parkir, Lokal Rumah, Antar Kota (PP), Lokal Lokasi, Makan/minum (lump sum), Makan/minum (on bill), Uang saku');
            // Tanggal (untuk Penginapan)
            $table->date('dtTanggalDari')->nullable()->comment('Tanggal Dari (untuk Penginapan)');
            $table->date('dtTanggalSampai')->nullable()->comment('Tanggal Sampai (untuk Penginapan)');
            // Nilai
            $table->decimal('decNilai', 15, 2)->nullable()->default(0)->comment('Nilai per item');
            $table->decimal('decTotal', 15, 2)->nullable()->default(0)->comment('Total (bisa sama dengan nilai atau dihitung)');
            $table->text('vcKeterangan')->nullable()->comment('Keterangan');
            // Audit
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
            
            // Foreign key
            $table->foreign('vcNoBpd')->references('vcNoBpd')->on('t_biaya_perjalanan_dinas_header')->onDelete('cascade');
            $table->index('vcNoBpd');
            $table->index('vcKategoriBiaya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_biaya_perjalanan_dinas_detail');
    }
};







