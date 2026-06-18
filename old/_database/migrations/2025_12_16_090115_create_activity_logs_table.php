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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // User Information
            $table->unsignedBigInteger('user_id')->nullable()->comment('FK ke users.id (nullable untuk system log)');
            $table->string('user_name', 255)->nullable()->comment('Nama user (untuk backup jika user dihapus)');
            $table->string('user_email', 255)->nullable()->comment('Email user (untuk backup)');
            
            // Action Information
            $table->string('action', 50)->comment('create, update, delete, view, login, logout, export, import');
            $table->string('model', 100)->nullable()->comment('Nama model/table (contoh: Karyawan, Absen, Closing)');
            $table->string('model_id', 100)->nullable()->comment('ID record yang diubah (bisa string untuk composite key)');
            
            // Change Information
            $table->text('description')->nullable()->comment('Deskripsi aksi');
            $table->json('old_values')->nullable()->comment('Data sebelum perubahan (untuk update/delete)');
            $table->json('new_values')->nullable()->comment('Data setelah perubahan (untuk create/update)');
            $table->text('changed_fields')->nullable()->comment('Field yang berubah (untuk update)');
            
            // Context Information
            $table->string('ip_address', 45)->nullable()->comment('IP address user');
            $table->text('user_agent')->nullable()->comment('Browser/device info');
            $table->string('route', 255)->nullable()->comment('Route yang diakses');
            $table->string('method', 10)->nullable()->comment('HTTP method (GET, POST, PUT, DELETE)');
            $table->text('url')->nullable()->comment('Full URL');
            
            // Module Information
            $table->string('module', 50)->nullable()->comment('Modul (master-data, absensi, proses-gaji, laporan, settings)');
            $table->string('submodule', 100)->nullable()->comment('Submodul (karyawan, absen, closing, dll)');
            
            // Additional Metadata
            $table->json('metadata')->nullable()->comment('Data tambahan (bisa untuk custom info)');
            $table->string('severity', 20)->default('info')->comment('info, warning, error, critical');
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('user_id', 'idx_activity_logs_user_id');
            $table->index('action', 'idx_activity_logs_action');
            $table->index('model', 'idx_activity_logs_model');
            $table->index('module', 'idx_activity_logs_module');
            $table->index('created_at', 'idx_activity_logs_created_at');
            $table->index(['user_id', 'action'], 'idx_activity_logs_user_action');
            $table->index(['model', 'action'], 'idx_activity_logs_model_action');
            
            // Foreign Key (optional, bisa di-comment jika user bisa dihapus)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
