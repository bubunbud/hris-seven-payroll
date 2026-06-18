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
        Schema::create('t_perjalanan_dinas_karyawan', function (Blueprint $table) {
            $table->string('vcCounterKaryawan', 50)->primary()->comment('Counter untuk detail karyawan');
            $table->string('vcNoRpd', 50)->comment('No RPD (Foreign Key)');
            $table->string('vcNik', 10)->comment('NIK Karyawan');
            $table->string('vcNamaKaryawan', 100)->comment('Nama Karyawan');
            $table->string('vcKodeDept', 10)->nullable()->comment('Kode Departemen');
            $table->string('vcKodeJabatan', 20)->nullable()->comment('Kode Jabatan');
            $table->string('vcKlasifikasiGrade', 50)->nullable()->comment('Klasifikasi Grade: Senior Management, Middle Management, Junior Management, Staff, Operator/Driver');
            // Audit
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
            
            // Foreign key
            $table->foreign('vcNoRpd')->references('vcNoRpd')->on('t_perjalanan_dinas_header')->onDelete('cascade');
            $table->index('vcNoRpd');
            $table->index('vcNik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_perjalanan_dinas_karyawan');
    }
};
