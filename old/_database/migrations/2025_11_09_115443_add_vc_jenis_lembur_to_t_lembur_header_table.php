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
        Schema::table('t_lembur_header', function (Blueprint $table) {
            $table->string('vcJenisLembur', 20)->nullable()->after('dtTanggalLembur')->comment('Jenis Lembur: Hari Kerja atau Hari Libur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_lembur_header', function (Blueprint $table) {
            $table->dropColumn('vcJenisLembur');
        });
    }
};
