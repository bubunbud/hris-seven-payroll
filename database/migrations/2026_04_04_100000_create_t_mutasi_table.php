<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat mutasi karyawan (Master Karyawan — tab Mutasi).
     * Skema mengikuti tabel existing: PK (nik, NoSK).
     */
    public function up(): void
    {
        if (Schema::hasTable('t_mutasi')) {
            return;
        }

        Schema::create('t_mutasi', function (Blueprint $table) {
            $table->string('nik', 24);
            $table->string('NoSK', 20);
            $table->date('vcTglSK')->nullable();
            $table->string('vcDivisi', 100)->nullable();
            $table->string('vcDept', 100)->nullable();
            $table->string('vcbagian', 100)->nullable();
            $table->string('vcSeksi', 100)->nullable();
            $table->string('vcJabatan', 150)->nullable();
            $table->string('vcFileSK', 255)->nullable();

            $table->primary(['nik', 'NoSK']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_mutasi');
    }
};
