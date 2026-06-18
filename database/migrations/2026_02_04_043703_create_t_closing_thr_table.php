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
        Schema::create('t_closing_thr', function (Blueprint $table) {
            // Primary key: composite key (dtTanggalTHR, vcNik, vcAgama)
            $table->date('dtTanggalTHR')->comment('Tanggal THR (dari t_periode_thr.dtCutoffTHR)');
            $table->string('vcNik', 10)->comment('NIK Karyawan');
            $table->string('vcAgama', 20)->comment('Agama Karyawan');
            
            // Field tracking dan informasi dasar
            $table->string('vcKodeDivisi', 10)->comment('Kode Divisi untuk tracking');
            $table->string('vcGroupPegawai', 20)->comment('Group Pegawai (Operator/Staff)');
            $table->string('vcGolongan', 10)->nullable()->comment('Golongan dari m_karyawan.Gol');
            
            // Gaji Pokok (nullable untuk Staff)
            $table->decimal('decGajiPokok', 15, 2)->nullable()->comment('Gaji Pokok = upah + tunj_keluarga + tunj_masa_kerja + tunj_jabatan1 + tunj_jabatan2 (null untuk Staff)');
            
            // Tanggal Masuk
            $table->date('dtTanggalMasuk')->comment('Tanggal Masuk Karyawan');
            
            // Masa Kerja (format lengkap)
            $table->string('vcMasaKerja', 50)->comment('Masa Kerja format: X Tahun, Y Bulan, Z Hari');
            
            // Masa Kerja (dalam satuan)
            $table->integer('intMasaKerjaHari')->default(0)->comment('Masa Kerja dalam Hari');
            $table->decimal('decMasaKerjaBulan', 10, 2)->default(0)->comment('Masa Kerja dalam Bulan (desimal)');
            $table->decimal('decMasaKerjaTahun', 10, 2)->default(0)->comment('Masa Kerja dalam Tahun (desimal)');
            
            // Perhitungan THR
            $table->decimal('decXGaji', 5, 2)->default(0)->comment('Multiplier (x Gaji), contoh: 1.0, 0.9, 0.3');
            $table->decimal('decNilaiTHR', 15, 2)->nullable()->comment('Nominal Uang THR yang diterima (null untuk Staff)');
            
            // Timestamps
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
            
            // Primary key composite
            $table->primary(['dtTanggalTHR', 'vcNik', 'vcAgama']);
            
            // Indexes
            $table->index('dtTanggalTHR');
            $table->index('vcNik');
            $table->index('vcAgama');
            $table->index('vcKodeDivisi');
            $table->index('vcGroupPegawai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_closing_thr');
    }
};
