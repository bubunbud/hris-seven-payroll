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
        Schema::table('t_absen', function (Blueprint $table) {
            $table->integer('intDurasiIstirahat')->default(0)->after('dtJamKeluarLembur')->comment('Durasi istirahat dalam menit dari Instruksi Kerja Lembur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_absen', function (Blueprint $table) {
            $table->dropColumn('intDurasiIstirahat');
        });
    }
};
