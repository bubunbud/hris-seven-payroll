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
        Schema::table('t_izin', function (Blueprint $table) {
            // Ubah kolom dtDari menjadi nullable untuk mendukung fitur "Pulang Cepat"
            $table->time('dtDari')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_izin', function (Blueprint $table) {
            // Kembalikan kolom dtDari menjadi NOT NULL (jika perlu rollback)
            // Catatan: Rollback ini akan gagal jika ada data dengan dtDari = null
            $table->time('dtDari')->nullable(false)->change();
        });
    }
};
