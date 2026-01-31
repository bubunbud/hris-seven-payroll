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
            $table->decimal('decLemburExternal', 15, 2)->nullable()->after('vcPenanggungBebanLainnya')->comment('Nominal lembur untuk penanggung beban (dihitung saat input)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_lembur_detail', function (Blueprint $table) {
            $table->dropColumn('decLemburExternal');
        });
    }
};


