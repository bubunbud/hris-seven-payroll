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
        // MariaDB/MySQL tidak support renameColumn langsung, gunakan raw SQL
        DB::statement('ALTER TABLE `m_saldo_cuti` CHANGE `decSaldoAwal` `decTahunLalu` DECIMAL(5,2) DEFAULT 0');
        DB::statement('ALTER TABLE `m_saldo_cuti` CHANGE `decSaldoTambahan` `decTahunIni` DECIMAL(5,2) DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `m_saldo_cuti` CHANGE `decTahunLalu` `decSaldoAwal` DECIMAL(5,2) DEFAULT 0');
        DB::statement('ALTER TABLE `m_saldo_cuti` CHANGE `decTahunIni` `decSaldoTambahan` DECIMAL(5,2) DEFAULT 0');
    }
};
