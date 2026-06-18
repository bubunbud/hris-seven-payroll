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
        Schema::table('t_lembur_header', function (Blueprint $table) {
            $table->string('vcJabatanPengaju', 10)->nullable()->after('vcDiajukanOleh')->comment('Kode Jabatan dari yang mengajukan');
            $table->string('vcKepalaDept', 100)->nullable()->after('vcJabatanPengaju')->comment('NIK atau Nama Kepala Departemen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_lembur_header', function (Blueprint $table) {
            $table->dropColumn(['vcJabatanPengaju', 'vcKepalaDept']);
        });
    }
};
