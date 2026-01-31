# Quick Start: Update HRIS Seven Payroll di Ubuntu Server

Panduan cepat untuk update project dari localhost ke Ubuntu server.

---

## 📋 RINGKASAN PERUBAHAN YANG AKAN DI-UPDATE

### Fitur Baru yang Akan Di-deploy:

1. **Sistem Login & Authentication**

    - Login page (`/login`)
    - AuthController untuk handle login/logout
    - Middleware authentication

2. **Sistem Role & Permission (RBAC)**

    - Tabel `roles`, `permissions`, `user_role`, `role_permission`
    - Model: Role, Permission
    - Middleware: RoleMiddleware, PermissionMiddleware
    - Controller: UserController, RoleController, PermissionController

3. **UI Settings Menu**

    - Menu "Settings" di sidebar (hanya untuk admin)
    - Submenu: Pengelolaan User, Pengelolaan Role, Pengelolaan Permission
    - Views: `resources/views/settings/**/*.blade.php`

4. **Perubahan Menu Sidebar**

    - Menu dinamis berdasarkan permission
    - "Proses" diubah menjadi "Proses Payroll"
    - "Saldo Cuti" dipindah ke group "Absensi"

5. **Database Changes**
    - 8 migration files baru untuk role & permission
    - 2 seeder baru: RolePermissionSeeder, AdminUserRoleSeeder

---

## 🚀 LANGKAH CEPAT UPDATE

### Opsi A: Menggunakan Script Otomatis (Recommended)

```bash
# 1. Upload file ke server (exclude vendor, node_modules, .env)
#    Letakkan di: /tmp/hris-seven-payroll-update/

# 2. Upload script update-ubuntu.sh ke server
#    Letakkan di: /var/www/html/hris-seven-payroll/

# 3. Jalankan script
cd /var/www/html/hris-seven-payroll
chmod +x update-ubuntu.sh
sudo ./update-ubuntu.sh
```

### Opsi B: Manual Step-by-Step

#### 1. Backup (WAJIB!)

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Backup database
mysqldump -u root -proot123 hris_seven > ~/backup_hris_$(date +%Y%m%d_%H%M%S).sql

# Backup .env
cp .env ~/backup_env_$(date +%Y%m%d_%H%M%S).txt
```

#### 2. Upload File dari Localhost

**Dari Windows (PowerShell/CMD):**

```powershell
# Exclude: vendor, node_modules, .git, storage/logs, .env
# Upload ke: /tmp/hris-seven-payroll-update/
```

**Atau menggunakan SCP:**

```bash
# Dari Git Bash atau WSL
cd /c/xampp/htdocs/hris-seven-payroll
scp -r --exclude='vendor' --exclude='node_modules' --exclude='.git' \
    --exclude='storage/logs' --exclude='.env' \
    * username@192.168.10.40:/tmp/hris-seven-payroll-update/
```

#### 3. Copy File ke Project Folder

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Copy file (jangan overwrite .env dan storage)
sudo rsync -av --exclude '.env' --exclude 'storage' --exclude 'vendor' \
    /tmp/hris-seven-payroll-update/ ./

# Set ownership
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

#### 4. Update Dependencies

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data composer install --optimize-autoloader --no-dev
```

#### 5. Run Migrations & Seeders

```bash
# Run migrations
sudo -u www-data php artisan migrate --force

# Run seeders
sudo -u www-data php artisan db:seed --class=RolePermissionSeeder
sudo -u www-data php artisan db:seed --class=AdminUserRoleSeeder
```

#### 6. Clear & Re-cache

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear

sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

#### 7. Test

```bash
# Test akses
# http://192.168.10.40/hris-seven-payroll

# Test login
# http://192.168.10.40/hris-seven-payroll/login
# Email: admin@hris.com
# Password: admin123

# Check logs
tail -f storage/logs/laravel.log
```

---

## ⚠️ PENTING!

1. **SELALU BACKUP** sebelum update
2. **Test di browser** setelah update
3. **Check logs** jika ada error
4. **Rollback** jika ada masalah besar

---

## 📚 DOKUMENTASI LENGKAP

-   **UPDATE_UBUNTU.md** - Panduan lengkap step-by-step
-   **UPDATE_CHECKLIST.md** - Checklist untuk memastikan semua langkah
-   **update-ubuntu.sh** - Script otomatis untuk update

---

## 🔄 ROLLBACK (Jika Diperlukan)

```bash
# Restore database
mysql -u root -proot123 hris_seven < ~/backup_hris_YYYYMMDD_HHMMSS.sql

# Restore .env
cp ~/backup_env_YYYYMMDD_HHMMSS.txt /var/www/html/hris-seven-payroll/.env

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart Apache
sudo systemctl restart apache2
```

---

**Selesai!** 🎉








