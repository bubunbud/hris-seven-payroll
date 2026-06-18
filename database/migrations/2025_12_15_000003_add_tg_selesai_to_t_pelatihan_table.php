<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_pelatihan') || Schema::hasColumn('t_pelatihan', 'tg_selesai')) {
            return;
        }

        Schema::table('t_pelatihan', function (Blueprint $table) {
            $table->date('tg_selesai')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('t_pelatihan') || ! Schema::hasColumn('t_pelatihan', 'tg_selesai')) {
            return;
        }

        Schema::table('t_pelatihan', function (Blueprint $table) {
            $table->dropColumn('tg_selesai');
        });
    }
};
