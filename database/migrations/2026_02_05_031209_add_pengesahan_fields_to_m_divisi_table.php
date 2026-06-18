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
            $table->string('vcHrGaManager', 100)->nullable()->after('vcPlantManager')->comment('HR&GA Manager');
            $table->string('vcSeniorFinanceManager', 100)->nullable()->after('vcHrGaManager')->comment('Senior Finance Manager');
            $table->string('vcGmBackOffice', 100)->nullable()->after('vcSeniorFinanceManager')->comment('GM Back Office');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_divisi', function (Blueprint $table) {
            $table->dropColumn(['vcHrGaManager', 'vcSeniorFinanceManager', 'vcGmBackOffice']);
        });
    }
};
