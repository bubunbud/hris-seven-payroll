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
        Schema::table('t_jadwal_shift_security', function (Blueprint $table) {
            // Ubah intShift menjadi nullable untuk support "OFF"
            $table->tinyInteger('intShift')->nullable()->change()->comment('1=Shift 1, 2=Shift 2, 3=Shift 3, NULL=OFF');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_jadwal_shift_security', function (Blueprint $table) {
            // Kembalikan ke not null (perlu hapus NULL dulu)
            DB::statement('UPDATE t_jadwal_shift_security SET intShift = 1 WHERE intShift IS NULL');
            $table->tinyInteger('intShift')->nullable(false)->change();
        });
    }
};
