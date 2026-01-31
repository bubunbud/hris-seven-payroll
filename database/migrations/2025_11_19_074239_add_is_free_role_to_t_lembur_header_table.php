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
            if (!Schema::hasColumn('t_lembur_header', 'is_free_role')) {
                $table->boolean('is_free_role')
                    ->default(false)
                    ->after('vcKepalaDept')
                    ->comment('Flag untuk menandai apakah instruksi kerja lembur menggunakan mode free role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_lembur_header', function (Blueprint $table) {
            if (Schema::hasColumn('t_lembur_header', 'is_free_role')) {
                $table->dropColumn('is_free_role');
            }
        });
    }
};
