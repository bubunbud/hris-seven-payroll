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
        Schema::table('m_divisi', function (Blueprint $table) {
            if (!Schema::hasColumn('m_divisi', 'vcManagerFinAcc')) {
                $table->string('vcManagerFinAcc', 100)
                    ->nullable()
                    ->after('vcKabag')
                    ->comment('Manager Fin-Acc');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_divisi', function (Blueprint $table) {
            if (Schema::hasColumn('m_divisi', 'vcManagerFinAcc')) {
                $table->dropColumn('vcManagerFinAcc');
            }
        });
    }
};
