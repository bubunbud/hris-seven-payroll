<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AdminUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('Role admin tidak ditemukan. Jalankan RolePermissionSeeder terlebih dahulu.');
            return;
        }

        $adminEmails = [
            'admin@hris.com',
            'bubun@abnmedical.com',
        ];

        foreach ($adminEmails as $email) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->command->warn("User dengan email {$email} tidak ditemukan.");
                continue;
            }

            $user->roles()->syncWithoutDetaching([$adminRole->id]);
            if ($user->role !== $adminRole->slug) {
                $user->forceFill(['role' => $adminRole->slug])->save();
            }

            $this->command->info("Role admin disematkan ke {$email}.");
        }
    }
}
