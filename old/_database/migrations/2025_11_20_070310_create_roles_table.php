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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Nama role (contoh: Administrator, HR Manager)');
            $table->string('slug', 100)->unique()->comment('Slug untuk role (contoh: admin, hr-manager)');
            $table->text('description')->nullable()->comment('Deskripsi role');
            $table->boolean('is_active')->default(true)->comment('Status aktif role');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
