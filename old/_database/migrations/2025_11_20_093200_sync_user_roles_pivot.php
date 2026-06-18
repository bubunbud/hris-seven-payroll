<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('user_role')) {
            return;
        }

        $defaultRole = Role::where('slug', 'user')->first();

        User::with('roles')->chunk(100, function ($users) use ($defaultRole) {
            foreach ($users as $user) {
                if ($user->roles()->exists()) {
                    continue;
                }

                $legacySlug = $user->role ?: 'user';
                $role = Role::where('slug', $legacySlug)->first() ?? $defaultRole;

                if ($role) {
                    $user->roles()->syncWithoutDetaching([$role->id]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for data sync
    }
};
