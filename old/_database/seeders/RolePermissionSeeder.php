<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // Master Data Permissions
            ['name' => 'View Master Data', 'slug' => 'view-master-data', 'module' => 'master-data', 'description' => 'Melihat menu dan data master'],
            ['name' => 'Create Master Data', 'slug' => 'create-master-data', 'module' => 'master-data', 'description' => 'Membuat data master baru'],
            ['name' => 'Edit Master Data', 'slug' => 'edit-master-data', 'module' => 'master-data', 'description' => 'Mengedit data master'],
            ['name' => 'Delete Master Data', 'slug' => 'delete-master-data', 'module' => 'master-data', 'description' => 'Menghapus data master'],

            // Absensi Permissions
            ['name' => 'View Absensi', 'slug' => 'view-absensi', 'module' => 'absensi', 'description' => 'Melihat data absensi'],
            ['name' => 'Create Absensi', 'slug' => 'create-absensi', 'module' => 'absensi', 'description' => 'Membuat data absensi'],
            ['name' => 'Edit Absensi', 'slug' => 'edit-absensi', 'module' => 'absensi', 'description' => 'Mengedit data absensi'],
            ['name' => 'Delete Absensi', 'slug' => 'delete-absensi', 'module' => 'absensi', 'description' => 'Menghapus data absensi'],

            // Proses Gaji Permissions
            ['name' => 'View Proses Gaji', 'slug' => 'view-proses-gaji', 'module' => 'proses-gaji', 'description' => 'Melihat proses gaji'],
            ['name' => 'Create Proses Gaji', 'slug' => 'create-proses-gaji', 'module' => 'proses-gaji', 'description' => 'Membuat proses gaji'],
            ['name' => 'Edit Proses Gaji', 'slug' => 'edit-proses-gaji', 'module' => 'proses-gaji', 'description' => 'Mengedit proses gaji'],
            ['name' => 'Delete Proses Gaji', 'slug' => 'delete-proses-gaji', 'module' => 'proses-gaji', 'description' => 'Menghapus proses gaji'],

            // Laporan Permissions
            ['name' => 'View Laporan', 'slug' => 'view-laporan', 'module' => 'laporan', 'description' => 'Melihat laporan'],
            ['name' => 'Print Laporan', 'slug' => 'print-laporan', 'module' => 'laporan', 'description' => 'Mencetak laporan'],
            ['name' => 'Export Laporan', 'slug' => 'export-laporan', 'module' => 'laporan', 'description' => 'Export laporan'],

            // Settings Permissions
            ['name' => 'View Settings', 'slug' => 'view-settings', 'module' => 'settings', 'description' => 'Melihat menu settings'],
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'module' => 'settings', 'description' => 'Mengelola user'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'module' => 'settings', 'description' => 'Mengelola role'],
            ['name' => 'Manage Permissions', 'slug' => 'manage-permissions', 'module' => 'settings', 'description' => 'Mengelola permission'],
            ['name' => 'View Logs', 'slug' => 'view-logs', 'module' => 'settings', 'description' => 'Melihat activity logs'],

            // Dashboard Permissions
            ['name' => 'View Dashboard Group', 'slug' => 'view-dashboard-group', 'module' => 'dashboard', 'description' => 'Melihat Dashboard Level Group (Holding View)'],
            ['name' => 'View Dashboard BU', 'slug' => 'view-dashboard-bu', 'module' => 'dashboard', 'description' => 'Melihat Dashboard Level Business Unit (BU View)'],
            ['name' => 'View Dashboard Employee', 'slug' => 'view-dashboard-employee', 'module' => 'dashboard', 'description' => 'Melihat Dashboard Employee Self Service'],

            // Tukar Hari Kerja Permission
            ['name' => 'View Tukar Hari Kerja', 'slug' => 'view-tukar-hari-kerja', 'module' => 'absensi', 'description' => 'Melihat dan mengelola Tukar Hari Kerja'],

            // ========== PERMISSION GRANULAR PER SUBMENU ==========
            
            // Master Data - Granular Permissions
            ['name' => 'View Master Divisi', 'slug' => 'view-master-divisi', 'module' => 'master-data', 'description' => 'Melihat Master Divisi'],
            ['name' => 'View Master Departemen', 'slug' => 'view-master-departemen', 'module' => 'master-data', 'description' => 'Melihat Master Departemen'],
            ['name' => 'View Master Bagian', 'slug' => 'view-master-bagian', 'module' => 'master-data', 'description' => 'Melihat Master Bagian'],
            ['name' => 'View Master Seksi', 'slug' => 'view-master-seksi', 'module' => 'master-data', 'description' => 'Melihat Master Seksi'],
            ['name' => 'View Master Karyawan', 'slug' => 'view-master-karyawan', 'module' => 'master-data', 'description' => 'Melihat Master Karyawan'],
            ['name' => 'View Master Golongan', 'slug' => 'view-master-golongan', 'module' => 'master-data', 'description' => 'Melihat Master Golongan'],
            ['name' => 'View Master Shift', 'slug' => 'view-master-shift', 'module' => 'master-data', 'description' => 'Melihat Master Shift Kerja'],
            ['name' => 'View Master Tidak Masuk', 'slug' => 'view-master-tidak-masuk', 'module' => 'master-data', 'description' => 'Melihat Master Tidak Masuk'],
            ['name' => 'View Master Ijin Keluar', 'slug' => 'view-master-ijin-keluar', 'module' => 'master-data', 'description' => 'Melihat Master Ijin Keluar'],
            ['name' => 'View Master Jabatan', 'slug' => 'view-master-jabatan', 'module' => 'master-data', 'description' => 'Melihat Master Jabatan'],
            ['name' => 'View Master Hari Libur', 'slug' => 'view-master-hari-libur', 'module' => 'master-data', 'description' => 'Melihat Master Hari Libur'],
            ['name' => 'View Group Hierarki', 'slug' => 'view-hirarki', 'module' => 'master-data', 'description' => 'Melihat Group Hierarki'],

            // Absensi - Granular Permissions
            ['name' => 'View Browse Absensi', 'slug' => 'view-browse-absensi', 'module' => 'absensi', 'description' => 'Melihat Browse Absensi'],
            ['name' => 'View Edit Absensi', 'slug' => 'view-edit-absensi', 'module' => 'absensi', 'description' => 'Mengedit data absensi (jam masuk/keluar)'],
            ['name' => 'View Browse Tidak Absen', 'slug' => 'view-browse-tidak-absen', 'module' => 'absensi', 'description' => 'Melihat Browse Tidak Absen'],
            ['name' => 'View Jadwal Shift Satpam', 'slug' => 'view-jadwal-shift-satpam', 'module' => 'absensi', 'description' => 'Melihat Jadwal Shift Satpam'],
            ['name' => 'View Master Shift Security', 'slug' => 'view-master-shift-security', 'module' => 'master-data', 'description' => 'Melihat Master Shift Security'],
            ['name' => 'View Override Jadwal', 'slug' => 'view-override-jadwal', 'module' => 'absensi', 'description' => 'Melihat List Override Jadwal'],
            ['name' => 'View Izin Tidak Masuk', 'slug' => 'view-tidak-masuk', 'module' => 'absensi', 'description' => 'Melihat Izin Tidak Masuk'],
            ['name' => 'View Izin Keluar Komplek', 'slug' => 'view-izin-keluar', 'module' => 'absensi', 'description' => 'Melihat Izin Keluar Komplek'],
            ['name' => 'View Instruksi Kerja Lembur', 'slug' => 'view-instruksi-kerja-lembur', 'module' => 'absensi', 'description' => 'Melihat Instruksi Kerja Lembur'],
            ['name' => 'View Statistik Absensi', 'slug' => 'view-statistik-absensi', 'module' => 'absensi', 'description' => 'Melihat Statistik Absensi'],
            ['name' => 'View Saldo Cuti', 'slug' => 'view-saldo-cuti', 'module' => 'absensi', 'description' => 'Melihat Saldo Cuti'],

            // Proses Payroll - Granular Permissions
            ['name' => 'View Master Gaji Pokok', 'slug' => 'view-master-gaji-pokok', 'module' => 'proses-gaji', 'description' => 'Melihat Master Gaji Pokok'],
            ['name' => 'View Hutang Piutang', 'slug' => 'view-hutang-piutang', 'module' => 'proses-gaji', 'description' => 'Melihat Hutang-Piutang Karyawan'],
            ['name' => 'View Realisasi Lembur', 'slug' => 'view-realisasi-lembur', 'module' => 'proses-gaji', 'description' => 'Melihat Realisasi Lembur'],
            ['name' => 'View Periode Gaji', 'slug' => 'view-periode-gaji', 'module' => 'proses-gaji', 'description' => 'Melihat Periode Closing Gaji'],
            ['name' => 'View Closing Gaji', 'slug' => 'view-closing-gaji', 'module' => 'proses-gaji', 'description' => 'Melihat Closing Gaji'],
            ['name' => 'View Update Closing Gaji', 'slug' => 'view-update-closing-gaji', 'module' => 'proses-gaji', 'description' => 'Melihat Update Closing Gaji'],
            ['name' => 'View Rekap Gaji', 'slug' => 'view-rekap-gaji', 'module' => 'proses-gaji', 'description' => 'Melihat View Rekap Gaji'],

            // Laporan - Granular Permissions
            ['name' => 'View Slip Gaji', 'slug' => 'view-slip-gaji', 'module' => 'laporan', 'description' => 'Melihat Cetak Slip Gaji'],
            ['name' => 'View Rekap Upah Karyawan', 'slug' => 'view-rekap-upah-karyawan', 'module' => 'laporan', 'description' => 'Melihat Rekap Upah Karyawan'],
            ['name' => 'View Rekap Uang Makan Transport', 'slug' => 'view-rekap-uang-makan-transport', 'module' => 'laporan', 'description' => 'Melihat Rekap Uang Makan & Transport'],
            ['name' => 'View Rekap Bank', 'slug' => 'view-rekap-bank', 'module' => 'laporan', 'description' => 'Melihat Rekap Bank'],
            ['name' => 'View Rekap Upah Per Bagian', 'slug' => 'view-rekap-upah-per-bagian', 'module' => 'laporan', 'description' => 'Melihat Rekap Upah Per Bagian/Dept.'],
            ['name' => 'View Rekap TM TU Per Bagian', 'slug' => 'view-rekap-tm-tu-per-bagian', 'module' => 'laporan', 'description' => 'Melihat Rekap TM, TU Per Bagian/Dept.'],
            ['name' => 'View Report Jadwal Shift', 'slug' => 'view-report-jadwal-shift', 'module' => 'laporan', 'description' => 'Melihat Report Jadwal Shift'],
            ['name' => 'View Rekapitulasi Absensi', 'slug' => 'view-rekapitulasi-absensi', 'module' => 'laporan', 'description' => 'Melihat Rekapitulasi Absensi'],
            ['name' => 'View Rekapitulasi Absen All', 'slug' => 'view-rekapitulasi-absen-all', 'module' => 'laporan', 'description' => 'Melihat Rekapitulasi Absen All'],
            ['name' => 'View Rekapitulasi Cuti', 'slug' => 'view-rekapitulasi-cuti', 'module' => 'laporan', 'description' => 'Melihat Rekapitulasi Cuti'],

            // Settings - Granular Permissions
            ['name' => 'View Tarik Data Absensi', 'slug' => 'view-tarik-data-absensi', 'module' => 'settings', 'description' => 'Melihat Tarik Data Absensi'],
            ['name' => 'View Tarik Data Izin', 'slug' => 'view-tarik-data-izin', 'module' => 'settings', 'description' => 'Melihat Tarik Data Izin'],
            ['name' => 'View Tarik Data Tidak Masuk', 'slug' => 'view-tarik-data-tidak-masuk', 'module' => 'settings', 'description' => 'Melihat Tarik Data Tidak Masuk'],
            ['name' => 'View Tarik Data Hutang Piutang', 'slug' => 'view-tarik-data-hutang-piutang', 'module' => 'settings', 'description' => 'Melihat Tarik Data Hutang Piutang'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['slug' => $perm['slug']],
                $perm
            );
        }

        // Create Roles
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Akses penuh ke semua fitur aplikasi',
                'is_active' => true,
            ]
        );

        $hrRole = Role::firstOrCreate(
            ['slug' => 'hr'],
            [
                'name' => 'HR',
                'description' => 'Akses untuk Human Resources',
                'is_active' => true,
            ]
        );

        $managerRole = Role::firstOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Akses untuk Manager',
                'is_active' => true,
            ]
        );

        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Akses terbatas untuk user biasa',
                'is_active' => true,
            ]
        );

        // Assign Permissions to Admin Role (All permissions)
        $adminRole->permissions()->sync(Permission::pluck('id')->toArray());

        // Assign Permissions to HR Role
        $hrPermissions = Permission::whereIn('slug', [
            'view-master-data',
            'create-master-data',
            'edit-master-data',
            'delete-master-data',
            'view-absensi',
            'create-absensi',
            'edit-absensi',
            'delete-absensi',
            'view-proses-gaji',
            'create-proses-gaji',
            'edit-proses-gaji',
            'view-laporan',
            'print-laporan',
            'export-laporan',
        ])->pluck('id')->toArray();
        $hrRole->permissions()->sync($hrPermissions);

        // Assign Permissions to Manager Role
        $managerPermissions = Permission::whereIn('slug', [
            'view-absensi',
            'view-proses-gaji',
            'create-proses-gaji',
            'edit-proses-gaji',
            'view-laporan',
            'print-laporan',
            'export-laporan',
        ])->pluck('id')->toArray();
        $managerRole->permissions()->sync($managerPermissions);

        // Assign Permissions to User Role
        $userPermissions = Permission::whereIn('slug', [
            'view-absensi',
            'view-laporan',
            'print-laporan',
        ])->pluck('id')->toArray();
        $userRole->permissions()->sync($userPermissions);

        $this->command->info('Role dan Permission berhasil dibuat!');
        $this->command->info('Roles: Admin, HR, Manager, User');
        $this->command->info('Permissions: ' . Permission::count() . ' permissions telah dibuat');
    }
}
