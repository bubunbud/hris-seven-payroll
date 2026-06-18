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
        Schema::create('t_jadwal_shift_security', function (Blueprint $table) {
            $table->id();
            $table->string('vcNik', 8)->comment('NIK Satpam');
            $table->date('dtTanggal')->comment('Tanggal jadwal');
            $table->tinyInteger('intShift')->comment('1=Shift 1, 2=Shift 2, 3=Shift 3');
            $table->string('vcKeterangan', 50)->nullable()->comment('OFF, Libur Nasional, Penggantian, dll');
            $table->boolean('isOverride')->default(false)->comment('True jika jadwal di-override karena urgent');
            $table->string('vcOverrideBy', 100)->nullable()->comment('User yang override');
            $table->dateTime('dtOverrideAt')->nullable();
            $table->dateTime('dtCreate')->nullable();
            $table->dateTime('dtChange')->nullable();

            $table->index(['vcNik', 'dtTanggal'], 'idx_nik_tanggal');
            $table->index('dtTanggal', 'idx_tanggal');
            // Foreign key constraint dihapus sementara untuk menghindari error
            // Pastikan vcNik sesuai dengan format NIK di m_karyawan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_jadwal_shift_security');
    }
};
