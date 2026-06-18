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
        Schema::create('t_user_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 191)->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('last_activity_at')->index();
            $table->timestamps();
        });

        Schema::create('t_login_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('event', 16)->index()->comment('login atau logout');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->index();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_login_history');
        Schema::dropIfExists('t_user_sessions');
    }
};
