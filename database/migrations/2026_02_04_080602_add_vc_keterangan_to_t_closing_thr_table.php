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
        Schema::table('t_closing_thr', function (Blueprint $table) {
            $table->string('vcKeterangan', 255)->nullable()->after('decNilaiTHR')->comment('Keterangan dari t_periode_thr.vcKeterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_closing_thr', function (Blueprint $table) {
            $table->dropColumn('vcKeterangan');
        });
    }
};
