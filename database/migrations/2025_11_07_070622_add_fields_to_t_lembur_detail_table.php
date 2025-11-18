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
        Schema::table('t_lembur_detail', function (Blueprint $table) {
            $table->string('dtJamMulaiLembur', 8)->nullable()->after('vcKodeJabatan')->comment('Jam mulai lembur (HH:MM:SS)');
            $table->string('dtJamSelesaiLembur', 8)->nullable()->after('dtJamMulaiLembur')->comment('Jam selesai lembur (HH:MM:SS)');
            $table->decimal('decDurasiLembur', 5, 2)->nullable()->after('dtJamSelesaiLembur')->comment('Durasi lembur dalam jam');
            $table->integer('intDurasiIstirahat')->nullable()->default(0)->after('decDurasiLembur')->comment('Durasi istirahat dalam menit');
            $table->string('vcDeskripsiLembur', 200)->nullable()->after('intDurasiIstirahat')->comment('Deskripsi lembur');
            $table->string('vcPenanggungBebanLembur', 20)->nullable()->after('vcDeskripsiLembur')->comment('Penanggung beban lembur (TGI, SIA-EXP, dll)');
            $table->string('vcPenanggungBebanLainnya', 100)->nullable()->after('vcPenanggungBebanLembur')->comment('Jika penanggung beban = Lainnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_lembur_detail', function (Blueprint $table) {
            $table->dropColumn([
                'dtJamMulaiLembur',
                'dtJamSelesaiLembur',
                'decDurasiLembur',
                'intDurasiIstirahat',
                'vcDeskripsiLembur',
                'vcPenanggungBebanLembur',
                'vcPenanggungBebanLainnya'
            ]);
        });
    }
};
