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
        if (! Schema::hasTable('t_izin')) {
            return;
        }

        if (Schema::hasColumn('t_izin', 'vcTipeIzin')) {
            return;
        }

        Schema::table('t_izin', function (Blueprint $table) {
            $table->string('vcTipeIzin', 50)->nullable()->comment('Tipe izin / kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('t_izin') || ! Schema::hasColumn('t_izin', 'vcTipeIzin')) {
            return;
        }

        Schema::table('t_izin', function (Blueprint $table) {
            $table->dropColumn('vcTipeIzin');
        });
    }
};
