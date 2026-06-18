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
        if (Schema::hasTable('t_pelatihan')) {
            return;
        }

        Schema::create('t_pelatihan', function (Blueprint $table) {
            $table->string('Nik', 8);
            $table->string('nm_pelatihan', 150);
            $table->string('penyelenggara', 150)->nullable();
            $table->string('lokasi', 150)->nullable();
            $table->date('tg_pelatihan')->nullable();
            $table->date('tg_selesai')->nullable();
            $table->integer('lama')->nullable();
            $table->boolean('Sertifikasi')->default(false);
            $table->text('Keterangan')->nullable();

            $table->primary(['Nik', 'nm_pelatihan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_pelatihan');
    }
};
