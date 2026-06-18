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
            $table->string('vcCounter', 12)->nullable()->after('dtJamKeluarLembur')->comment('Kode Lembur dari t_lembur_header');
            $table->index('vcCounter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_absen', function (Blueprint $table) {
            $table->dropIndex(['vcCounter']);
            $table->dropColumn('vcCounter');
        });
    }
};
