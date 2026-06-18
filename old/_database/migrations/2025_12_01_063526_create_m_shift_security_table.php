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
        Schema::create('m_shift_security', function (Blueprint $table) {
            $table->tinyInteger('vcKodeShift')->primary()->comment('1=Shift 1, 2=Shift 2, 3=Shift 3');
            $table->string('vcNamaShift', 20)->comment('Shift 1, Shift 2, Shift 3');
            $table->time('dtJamMasuk')->comment('Jam masuk shift');
            $table->time('dtJamPulang')->comment('Jam pulang shift');
            $table->boolean('isCrossDay')->default(false)->comment('True jika shift melewati tengah malam');
            $table->decimal('intDurasiJam', 4, 2)->default(8.00)->comment('Durasi shift dalam jam');
            $table->integer('intToleransiMasuk')->default(30)->comment('Toleransi terlambat dalam menit');
            $table->integer('intToleransiPulang')->default(30)->comment('Toleransi pulang cepat dalam menit');
            $table->string('vcKeterangan', 100)->nullable();
            $table->dateTime('dtCreate')->nullable();
            $table->dateTime('dtChange')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_shift_security');
    }
};
