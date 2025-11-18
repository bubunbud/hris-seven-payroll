<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah kolom dari DECIMAL(5,2) ke DECIMAL(5,0) untuk menghitung hari yang pasti bulat
        // DECIMAL(5,0) = integer dengan maksimal 5 digit
        DB::statement('ALTER TABLE `m_saldo_cuti` MODIFY `decTahunLalu` DECIMAL(5,0) DEFAULT 0');
        DB::statement('ALTER TABLE `m_saldo_cuti` MODIFY `decTahunIni` DECIMAL(5,0) DEFAULT 0');
        DB::statement('ALTER TABLE `m_saldo_cuti` MODIFY `decSaldoDigunakan` DECIMAL(5,0) DEFAULT 0');
        DB::statement('ALTER TABLE `m_saldo_cuti` MODIFY `decSaldoSisa` DECIMAL(5,0) DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke DECIMAL(5,2)
        DB::statement('ALTER TABLE `m_saldo_cuti` MODIFY `decTahunLalu` DECIMAL(5,2) DEFAULT 0');
        DB::statement('ALTER TABLE `m_saldo_cuti` MODIFY `decTahunIni` DECIMAL(5,2) DEFAULT 0');
        DB::statement('ALTER TABLE `m_saldo_cuti` MODIFY `decSaldoDigunakan` DECIMAL(5,2) DEFAULT 0');
        DB::statement('ALTER TABLE `m_saldo_cuti` MODIFY `decSaldoSisa` DECIMAL(5,2) DEFAULT 0');
    }
};





