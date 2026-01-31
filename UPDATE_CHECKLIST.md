# Checklist Update HRIS Seven Payroll di Ubuntu Server

Gunakan checklist ini untuk memastikan semua langkah update sudah dilakukan dengan benar.

## ⚠️ PRE-UPDATE (Backup)

-   [ ] **Backup Database** - Database sudah di-backup

    ```bash
    mysqldump -u root -proot123 hris_seven > ~/backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql
    ```

-   [ ] **Backup .env** - File .env sudah di-backup

    ```bash
    cp /var/www/html/hris-seven-payroll/.env ~/backup_env_$(date +%Y%m%d_%H%M%S).txt
    ```

-   [ ] **Backup Storage** - Folder storage sudah di-backup (jika ada file penting)

    ```bash
    tar -czf ~/backup_storage_$(date +%Y%m%d_%H%M%S).tar.gz -C /var/www/html/hris-seven-payroll storage/
    ```

-   [ ] **Backup Current Files** - File penting sudah di-backup
    ```bash
    cp -r /var/www/html/hris-seven-payroll/app ~/app_backup_$(date +%Y%m%d_%H%M%S)
    cp -r /var/www/html/hris-seven-payroll/routes ~/routes_backup_$(date +%Y%m%d_%H%M%S)
    ```

---

## 📤 UPLOAD FILES

-   [ ] **Prepare Files di Localhost** - File sudah disiapkan (exclude vendor, node_modules, .env)
-   [ ] **Upload ke Server** - File sudah di-upload ke `/tmp/hris-seven-payroll-update/`
-   [ ] **Verify Files** - File penting sudah ter-verify:
    -   [ ] `app/Http/Controllers/AuthController.php`
    -   [ ] `app/Models/Role.php`
    -   [ ] `app/Models/Permission.php`
    -   [ ] `app/Http/Middleware/PermissionMiddleware.php`
    -   [ ] `app/Http/Middleware/RoleMiddleware.php`
    -   [ ] `database/migrations/2025_11_20_*.php` (8 migration files)
    -   [ ] `database/seeders/RolePermissionSeeder.php`
    -   [ ] `database/seeders/AdminUserRoleSeeder.php`
    -   [ ] `routes/web.php`
    -   [ ] `resources/views/layouts/app.blade.php`
    -   [ ] `resources/views/auth/login.blade.php`
    -   [ ] `resources/views/settings/**/*.blade.php`

---

## 🔄 UPDATE PROCESS

-   [ ] **Copy New Files** - File baru sudah di-copy (maintain .env dan storage)
-   [ ] **Set Ownership** - Ownership sudah di-set ke `www-data:www-data`
-   [ ] **Set Permissions** - Permissions sudah benar:

    -   [ ] Root folder: 755
    -   [ ] storage folder: 775
    -   [ ] bootstrap/cache folder: 775

-   [ ] **Update Composer** - Composer dependencies sudah di-update

    ```bash
    composer install --optimize-autoloader --no-dev
    ```

-   [ ] **Run Migrations** - Migrations baru sudah dijalankan

    ```bash
    php artisan migrate --force
    ```

    -   [ ] `2025_11_20_061252_add_role_and_nik_to_users_table.php`
    -   [ ] `2025_11_20_070310_create_roles_table.php`
    -   [ ] `2025_11_20_070312_create_permissions_table.php`
    -   [ ] `2025_11_20_070314_create_role_permission_table.php`
    -   [ ] `2025_11_20_070315_create_user_role_table.php`
    -   [ ] `2025_11_20_093200_sync_user_roles_pivot.php`
    -   [ ] `2025_11_20_093941_alter_role_column_in_users_table.php`
    -   [ ] `2025_11_20_094004_sync_users_role_column_from_pivot.php`

-   [ ] **Run Seeders** - Seeders baru sudah dijalankan

    ```bash
    php artisan db:seed --class=RolePermissionSeeder
    php artisan db:seed --class=AdminUserRoleSeeder
    ```

-   [ ] **Clear Cache** - Cache Laravel sudah di-clear

    ```bash
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    ```

-   [ ] **Re-cache** - Cache sudah di-re-cache untuk production
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

---

## ✅ TESTING

-   [ ] **Test Access** - Website bisa diakses

    -   URL: `http://192.168.10.40/hris-seven-payroll`
    -   Status: ✅ Bisa diakses / ❌ Error

-   [ ] **Test Login** - Login system berfungsi

    -   URL: `http://192.168.10.40/hris-seven-payroll/login`
    -   Test dengan: `admin@hris.com` / `admin123`
    -   Status: ✅ Berhasil / ❌ Error

-   [ ] **Test Dashboard** - Dashboard bisa diakses setelah login

    -   Status: ✅ Berhasil / ❌ Error

-   [ ] **Test Menu Visibility** - Menu sesuai dengan permission

    -   [ ] Menu "Master Data" muncul jika punya permission `view-master-data`
    -   [ ] Menu "Absensi" muncul jika punya permission `view-absensi`
    -   [ ] Menu "Proses Payroll" muncul jika punya permission `view-proses-gaji`
    -   [ ] Menu "Laporan" muncul jika punya permission `view-laporan`
    -   [ ] Menu "Settings" muncul jika punya permission `view-settings`

-   [ ] **Test Settings Menu** - Settings menu berfungsi

    -   [ ] "Pengelolaan User" bisa diakses (permission: `manage-users`)
    -   [ ] "Pengelolaan Role" bisa diakses (permission: `manage-roles`)
    -   [ ] "Pengelolaan Permission" bisa diakses (permission: `manage-permissions`)

-   [ ] **Test Role Management** - Role management berfungsi

    -   [ ] Bisa melihat list roles
    -   [ ] Bisa create role baru
    -   [ ] Bisa edit role
    -   [ ] Bisa assign permissions ke role

-   [ ] **Test User Management** - User management berfungsi

    -   [ ] Bisa melihat list users
    -   [ ] Bisa create user baru
    -   [ ] Bisa edit user (termasuk assign role)
    -   [ ] Bisa delete user (kecuali admin terakhir)

-   [ ] **Test Permission System** - Permission system berfungsi

    -   [ ] User dengan role berbeda melihat menu berbeda
    -   [ ] Route protection bekerja (403 jika tidak punya permission)
    -   [ ] Admin punya akses penuh

-   [ ] **Test Existing Features** - Fitur existing masih berfungsi

    -   [ ] Master Data (Divisi, Departemen, Karyawan, dll)
    -   [ ] Absensi
    -   [ ] Proses Payroll
    -   [ ] Laporan
    -   [ ] Instruksi Kerja Lembur
    -   [ ] Saldo Cuti (sekarang di group Absensi)

-   [ ] **Check Logs** - Tidak ada error di log files
    ```bash
    tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log
    tail -f /var/log/apache2/error.log
    ```
    -   Status: ✅ Tidak ada error / ❌ Ada error

---

## 🧹 CLEANUP

-   [ ] **Remove Temp Files** - File temporary sudah dihapus

    ```bash
    sudo rm -rf /tmp/hris-seven-payroll-update
    ```

-   [ ] **Remove Backup Files** (Opsional) - Backup files sudah dihapus jika tidak diperlukan
    ```bash
    # Hanya hapus jika sudah yakin update berhasil
    # rm -rf ~/app_backup_*
    # rm -rf ~/routes_backup_*
    ```

---

## 📝 NOTES

Tambahkan catatan khusus di sini:

-
-
-

---

## ⚠️ ROLLBACK (Jika Diperlukan)

Jika update menyebabkan masalah:

-   [ ] **Restore Database**

    ```bash
    mysql -u root -proot123 hris_seven < ~/backup_hris_seven_YYYYMMDD_HHMMSS.sql
    ```

-   [ ] **Restore .env**

    ```bash
    cp ~/backup_env_YYYYMMDD_HHMMSS.txt /var/www/html/hris-seven-payroll/.env
    ```

-   [ ] **Restore Files** (jika perlu)

    ```bash
    cp -r ~/app_backup_YYYYMMDD_HHMMSS/* /var/www/html/hris-seven-payroll/app/
    cp -r ~/routes_backup_YYYYMMDD_HHMMSS/* /var/www/html/hris-seven-payroll/routes/
    ```

-   [ ] **Clear Cache**

    ```bash
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    ```

-   [ ] **Restart Apache**
    ```bash
    sudo systemctl restart apache2
    ```

---

**Status Update**: ⬜ Belum Dimulai | 🟡 Sedang Berlangsung | ✅ Selesai | ❌ Error

**Tanggal Update**: ******\_\_\_\_******

**Updated By**: ******\_\_\_\_******

**Backup Location**: ******\_\_\_\_******








