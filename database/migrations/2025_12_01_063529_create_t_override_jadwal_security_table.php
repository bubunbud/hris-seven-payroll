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
        Schema::create('t_override_jadwal_security', function (Blueprint $table) {
            $table->id();
            $table->string('vcNik', 8)->comment('NIK Satpam');
            $table->date('dtTanggal')->comment('Tanggal yang di-override');
            $table->tinyInteger('intShiftLama')->nullable()->comment('Shift yang di-override (bisa null jika tambah shift baru)');
            $table->tinyInteger('intShiftBaru')->comment('Shift baru');
            $table->text('vcAlasan')->comment('Alasan override');
            $table->string('vcOverrideBy', 100)->comment('User yang override');
            $table->dateTime('dtOverrideAt');
            $table->dateTime('dtCreate')->nullable();

            $table->index(['vcNik', 'dtTanggal'], 'idx_nik_tanggal');
            // Foreign key constraint dihapus sementara untuk menghindari error
            // Pastikan vcNik sesuai dengan format NIK di m_karyawan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_override_jadwal_security');
    }
};
