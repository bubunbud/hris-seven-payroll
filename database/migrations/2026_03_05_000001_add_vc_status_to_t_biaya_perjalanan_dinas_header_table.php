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
        Schema::table('t_biaya_perjalanan_dinas_header', function (Blueprint $table) {
            $table->string('vcStatus', 20)->default('draft')->after('vcMengetahuiFinance')
                ->comment('Status: draft = hanya bagian 1&2, complete = lengkap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_biaya_perjalanan_dinas_header', function (Blueprint $table) {
            $table->dropColumn('vcStatus');
        });
    }
};
