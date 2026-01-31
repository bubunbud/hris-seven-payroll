<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        User::with('roles')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $primaryRole = $user->roles->first();
                if ($primaryRole && $user->role !== $primaryRole->slug) {
                    $user->forceFill(['role' => $primaryRole->slug])->save();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback necessary
    }
};
