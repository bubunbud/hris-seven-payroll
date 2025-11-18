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
        Schema::table('t_closing', function (Blueprint $table) {
            $table->decimal('decPotonganAbsen', 12, 2)->default(0)->after('decPotonganKoperasi')->comment('Potongan Absen (Ijin Pribadi)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_closing', function (Blueprint $table) {
            $table->dropColumn('decPotonganAbsen');
        });
    }
};
