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
            // Tambahkan field tanda tangan jika belum ada
            if (!Schema::hasColumn('m_divisi', 'vcStaff')) {
                $table->string('vcStaff', 100)->nullable()->after('vcKeterangan')->comment('Staff Payroll');
            }
            if (!Schema::hasColumn('m_divisi', 'vcKeuangan')) {
                $table->string('vcKeuangan', 100)->nullable()->after('vcStaff')->comment('Kabag HR/Keuangan');
            }
            if (!Schema::hasColumn('m_divisi', 'vcKabag')) {
                $table->string('vcKabag', 100)->nullable()->after('vcKeuangan')->comment('Manager / Ka. Dept');
            }
            if (!Schema::hasColumn('m_divisi', 'vPPIC')) {
                $table->string('vPPIC', 100)->nullable()->after('vcKabag')->comment('Direktur / General Manager');
            }
            if (!Schema::hasColumn('m_divisi', 'vcPlantManager')) {
                $table->string('vcPlantManager', 100)->nullable()->after('vPPIC')->comment('General Manager / Direktur');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_divisi', function (Blueprint $table) {
            $table->dropColumn(['vcStaff', 'vcKeuangan', 'vcKabag', 'vPPIC', 'vcPlantManager']);
        });
    }
};
