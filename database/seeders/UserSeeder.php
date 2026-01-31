<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user admin pertama
        $user = User::firstOrCreate(
            ['email' => 'admin@hris.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@hris.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'nik' => null,
                'is_active' => true,
            ]
        );

        if ($user) {
            $adminRole = Role::where('slug', 'admin')->first();
            if ($adminRole) {
                $user->roles()->syncWithoutDetaching([$adminRole->id]);
            }
        }

        $this->command->info('User admin berhasil dibuat!');
        $this->command->info('Email: admin@hris.com');
        $this->command->info('Password: admin123');
        $this->command->warn('⚠️  PENTING: Segera ubah password setelah login pertama kali!');
    }
}
