<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Update primary key dari 2 field (nik, hubKeluarga) menjadi 3 field (nik, hubKeluarga, NamaKeluarga)
     * Ini memungkinkan beberapa anggota dengan hubungan yang sama (misalnya 3 anak dengan nama berbeda)
     */
    public function up(): void
    {
        // Drop existing primary key jika ada
        Schema::table('t_keluarga', function (Blueprint $table) {
            // Hapus primary key yang lama (jika ada)
            // Note: MySQL tidak support drop primary key langsung, jadi kita gunakan raw SQL
        });

        // Gunakan raw SQL untuk drop dan create primary key baru
        DB::statement('ALTER TABLE t_keluarga DROP PRIMARY KEY');

        // Create primary key baru dengan 3 field
        DB::statement('ALTER TABLE t_keluarga ADD PRIMARY KEY (nik, hubKeluarga, NamaKeluarga)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke primary key lama (2 field)
        DB::statement('ALTER TABLE t_keluarga DROP PRIMARY KEY');
        DB::statement('ALTER TABLE t_keluarga ADD PRIMARY KEY (nik, hubKeluarga)');
    }
};
