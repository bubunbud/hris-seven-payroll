<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lembur HKN J4 (jam ke-10 dst, 4×) + agregat nominal decTotallembur4
     */
    public function up(): void
    {
        Schema::table('t_closing', function (Blueprint $table) {
            $table->decimal('decJamLemburKerja4', 12, 2)->nullable()->default(0)->after('decLemburKerja3');
            $table->decimal('decLemburKerja4', 15, 2)->nullable()->default(0)->after('decJamLemburKerja4');
            $table->decimal('decTotallembur4', 15, 2)->nullable()->default(0)->after('decTotallembur3');
        });
    }

    public function down(): void
    {
        Schema::table('t_closing', function (Blueprint $table) {
            $table->dropColumn(['decJamLemburKerja4', 'decLemburKerja4', 'decTotallembur4']);
        });
    }
};
