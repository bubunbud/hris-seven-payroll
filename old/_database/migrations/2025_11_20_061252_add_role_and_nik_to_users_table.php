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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 8)->nullable()->after('email')->comment('NIK karyawan yang terhubung dengan user');
            $table->enum('role', ['admin', 'hr', 'manager', 'user'])->default('user')->after('nik')->comment('Role user: admin, hr, manager, user');
            $table->boolean('is_active')->default(true)->after('role')->comment('Status aktif user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nik', 'role', 'is_active']);
        });
    }
};
