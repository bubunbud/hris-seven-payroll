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
        Schema::table('m_bagian', function (Blueprint $table) {
            $table->string('vcPICBagian', 50)->nullable()->after('vcNamaBagian')->comment('PIC Bagian');
            $table->string('vcKodeJabatan', 10)->nullable()->after('vcPICBagian')->comment('Kode Jabatan PIC Bagian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_bagian', function (Blueprint $table) {
            $table->dropColumn(['vcPICBagian', 'vcKodeJabatan']);
        });
    }
};
