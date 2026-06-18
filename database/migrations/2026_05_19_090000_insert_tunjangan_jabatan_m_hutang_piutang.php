<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master jenis 5 — Tunjangan Jabatan — agar bisa dipilih di menu Hutang Piutang (t_hutang_piutang.vcJenis).
     * vcHutangPiutang 'P': penanda umum Piutang / pihak dapat manfaat (karyawan); sesuaikan jika konvensi DB Anda lain.
     */
    public function up(): void
    {
        if (! Schema::hasTable('m_hutang_piutang')) {
            return;
        }

        if (DB::table('m_hutang_piutang')->where('vcJenis', '5')->exists()) {
            return;
        }

        $now = now();

        DB::table('m_hutang_piutang')->insert([
            'vcJenis' => '5',
            'vcKeterangan' => 'Tunjangan Jabatan',
            'vcHutangPiutang' => 'P',
            'dtCreate' => $now,
            'dtChange' => $now,
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('m_hutang_piutang')) {
            return;
        }

        DB::table('m_hutang_piutang')->where('vcJenis', '5')->delete();
    }
};
