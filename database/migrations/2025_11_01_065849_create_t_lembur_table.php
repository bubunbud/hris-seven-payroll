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
        Schema::create('t_lembur', function (Blueprint $table) {
            $table->string('vcCounter', 9)->primary();
            $table->date('dtTanggal');
            $table->string('vcNik', 8);
            $table->string('dtJamMulai', 8); // format HH:MM:SS
            $table->string('dtJamSelesai', 8); // format HH:MM:SS
            $table->decimal('decTotalJam', 5, 2)->default(0); // total jam lembur
            $table->string('vcKeterangan', 100)->nullable();
            $table->string('vcStatus', 1)->default('P'); // P=Pending, A=Approved, R=Rejected
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
            
            $table->index(['dtTanggal', 'vcNik']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_lembur');
    }
};
