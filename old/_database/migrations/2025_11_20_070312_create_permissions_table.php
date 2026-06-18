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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Nama permission (contoh: View Master Data, Create User)');
            $table->string('slug', 100)->unique()->comment('Slug untuk permission (contoh: view-master-data, create-user)');
            $table->text('description')->nullable()->comment('Deskripsi permission');
            $table->string('module', 50)->nullable()->comment('Module/group permission (contoh: master-data, settings, absensi)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
