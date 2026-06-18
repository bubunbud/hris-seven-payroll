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
        Schema::table('t_periode_thr', function (Blueprint $table) {
            $table->string('vcNamaHariRaya', 100)->nullable()->after('dtKategori')->comment('Nama Hari Raya (contoh: Idul Fitri, Natal, Nyepi, Waisak)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_periode_thr', function (Blueprint $table) {
            $table->dropColumn('vcNamaHariRaya');
        });
    }
};
