<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_mesin_fingerprint', function (Blueprint $table) {
            $table->id();
            $table->string('vcNama', 100);
            $table->string('vcMerk', 50)->nullable()->default('Solution');
            $table->string('vcTipe', 50)->nullable();
            $table->string('vcIp', 45);
            $table->unsignedSmallInteger('intPort')->default(4370);
            $table->unsignedInteger('intCommKey')->default(0);
            $table->char('vcAktif', 1)->default('1');
            $table->text('vcKeterangan')->nullable();
            $table->timestamp('dtLastPull')->nullable();
            $table->timestamp('dtCreate')->nullable();
            $table->timestamp('dtChange')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_mesin_fingerprint');
    }
};
