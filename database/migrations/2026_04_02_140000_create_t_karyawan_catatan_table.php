<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat catatan HR: SP, teguran, penghargaan, dll. (Master Karyawan — tab Catatan Karyawan).
     */
    public function up(): void
    {
        if (Schema::hasTable('t_karyawan_catatan')) {
            return;
        }

        Schema::create('t_karyawan_catatan', function (Blueprint $table) {
            $table->id();
            $table->string('karyawan_nik', 24);
            $table->date('tanggal')->nullable();
            $table->enum('jenis', ['SP', 'Teguran', 'Peringatan Lisan', 'Penghargaan', 'Catatan', 'Pelanggaran']);
            $table->enum('kategori', ['Disiplin', 'Penghargaan', 'Informasi']);
            $table->string('judul', 255);
            $table->text('deskripsi')->nullable();
            $table->enum('level', ['SP1', 'SP2', 'SP3', 'Non-SP'])->default('Non-SP');
            $table->enum('status', ['Aktif', 'Selesai', 'Dibatalkan'])->default('Aktif');
            $table->string('no_dokumen', 100)->nullable();
            $table->string('file_lampiran', 255)->nullable();
            $table->date('tanggal_berlaku')->nullable();
            $table->date('tanggal_berakhir')->nullable();
            $table->timestamps();

            $table->index('karyawan_nik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_karyawan_catatan');
    }
};
