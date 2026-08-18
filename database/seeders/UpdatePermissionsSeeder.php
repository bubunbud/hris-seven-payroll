<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class UpdatePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini untuk menambahkan permission yang belum ada
     * tanpa membuat duplikat (menggunakan firstOrCreate)
     */
    public function run(): void
    {
        // Permission yang perlu ditambahkan (jika belum ada)
        $permissions = [
            // Absensi - Permission Baru
            ['name' => 'View Perjalanan Dinas', 'slug' => 'view-perjalanan-dinas', 'module' => 'absensi', 'description' => 'Melihat Form Perjalanan Dinas'],
            ['name' => 'View Biaya Perjalanan Dinas', 'slug' => 'view-biaya-perjalanan-dinas', 'module' => 'absensi', 'description' => 'Melihat Form Biaya Perjalanan Dinas (BPD)'],
            ['name' => 'View Rekap Keterlambatan', 'slug' => 'view-rekap-keterlambatan', 'module' => 'absensi', 'description' => 'Melihat Rekap Absensi Keterlambatan'],

            // Proses Payroll - Permission THR
            ['name' => 'View Periode THR', 'slug' => 'view-periode-thr', 'module' => 'proses-gaji', 'description' => 'Melihat Periode Closing THR'],
            ['name' => 'View Closing THR', 'slug' => 'view-closing-thr', 'module' => 'proses-gaji', 'description' => 'Melihat Closing THR'],
            ['name' => 'View List THR', 'slug' => 'view-list-thr', 'module' => 'proses-gaji', 'description' => 'Melihat List THR'],

            // Laporan - Permission Baru
            ['name' => 'View Laporan THR', 'slug' => 'view-laporan-thr', 'module' => 'laporan', 'description' => 'Melihat Laporan THR'],
            ['name' => 'View Rekap Upah Finance Ver', 'slug' => 'view-rekap-upah-finance-ver', 'module' => 'laporan', 'description' => 'Melihat Rekap Upah Finance Ver'],

            // Settings - List Pengajuan Cuti API
            ['name' => 'View List Pengajuan Cuti API', 'slug' => 'view-list-pengajuan-cuti-api', 'module' => 'settings', 'description' => 'Melihat List Pengajuan Cuti dari API (feeder cuti)'],
            ['name' => 'View List Pengajuan Izin API', 'slug' => 'view-list-pengajuan-izin-api', 'module' => 'settings', 'description' => 'Melihat List Pengajuan Izin dari API (feeder izin tidak masuk)'],
            ['name' => 'View Tarik Data Absensi API', 'slug' => 'view-tarik-data-absensi-api', 'module' => 'settings', 'description' => 'Melihat Tarik Data Absensi dari API HRIS eksternal'],
            ['name' => 'View Master Mesin Fingerprint', 'slug' => 'view-mesin-fingerprint', 'module' => 'settings', 'description' => 'Melihat dan mengelola master mesin fingerprint'],
            ['name' => 'View Tarik Data Fingerprint', 'slug' => 'view-tarik-data-fingerprint', 'module' => 'settings', 'description' => 'Melihat dan menarik data absensi dari mesin fingerprint'],
            ['name' => 'View Tarik Data Absensi Supabase', 'slug' => 'view-tarik-data-absensi-supabase', 'module' => 'settings', 'description' => 'Melihat dan menarik data absensi dari Supabase REST API'],
            ['name' => 'View Tarik Data Izin/Sakit/Cuti Supabase', 'slug' => 'view-tarik-data-leave-supabase', 'module' => 'settings', 'description' => 'Melihat dan menarik data izin, sakit, cuti dari Supabase leave_requests'],
            // Settings - Login aktif & riwayat
            ['name' => 'View Login Activity', 'slug' => 'view-login-activity', 'module' => 'settings', 'description' => 'Melihat user yang sedang login dan riwayat login/logout'],
        ];

        $added = 0;
        $skipped = 0;

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(
                ['slug' => $perm['slug']],
                $perm
            );

            if ($permission->wasRecentlyCreated) {
                $added++;
                $this->command->info("✓ Permission '{$perm['name']}' berhasil ditambahkan");
            } else {
                $skipped++;
                $this->command->line("⊘ Permission '{$perm['name']}' sudah ada, dilewati");
            }
        }

        $this->command->info("\n=== Summary ===");
        $this->command->info("Permission baru ditambahkan: {$added}");
        $this->command->info("Permission sudah ada (dilewati): {$skipped}");
        $this->command->info("Total permission di database: " . Permission::count());

        $adminRole = Role::where('slug', 'admin')->first();
        $loginPerm = Permission::where('slug', 'view-login-activity')->first();
        if ($adminRole && $loginPerm) {
            $adminRole->permissions()->syncWithoutDetaching([$loginPerm->id]);
            $this->command->info("Role Admin: permission view-login-activity dilampirkan (jika belum).");
        }

        $absensiApiPerm = Permission::where('slug', 'view-tarik-data-absensi-api')->first();
        if ($adminRole && $absensiApiPerm) {
            $adminRole->permissions()->syncWithoutDetaching([$absensiApiPerm->id]);
            $this->command->info("Role Admin: permission view-tarik-data-absensi-api dilampirkan (jika belum).");
        }

        foreach (['view-mesin-fingerprint', 'view-tarik-data-fingerprint', 'view-tarik-data-absensi-supabase', 'view-tarik-data-leave-supabase'] as $slug) {
            $perm = Permission::where('slug', $slug)->first();
            if ($adminRole && $perm) {
                $adminRole->permissions()->syncWithoutDetaching([$perm->id]);
                $this->command->info("Role Admin: permission {$slug} dilampirkan (jika belum).");
            }
        }
    }
}





