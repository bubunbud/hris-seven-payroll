<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_pelatihan') || Schema::hasColumn('t_pelatihan', 'lokasi')) {
            return;
        }

        Schema::table('t_pelatihan', function (Blueprint $table) {
            $table->string('lokasi', 150)->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('t_pelatihan') || ! Schema::hasColumn('t_pelatihan', 'lokasi')) {
            return;
        }

        Schema::table('t_pelatihan', function (Blueprint $table) {
            $table->dropColumn('lokasi');
        });
    }
};
