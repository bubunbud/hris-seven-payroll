# Panduan Update HRIS Seven Payroll di Ubuntu Server

Panduan ini untuk mengupdate project yang sudah di-deploy di Ubuntu server dengan versi terbaru dari localhost.

## ⚠️ PENTING: BACKUP SEBELUM UPDATE

**LANGKAH WAJIB** - Backup sebelum melakukan update:

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# 1. Backup Database
mysqldump -u root -proot123 hris_seven > ~/backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql

# 2. Backup .env file
cp .env ~/backup_env_$(date +%Y%m%d_%H%M%S).txt

# 3. Backup storage folder (jika ada file upload penting)
tar -czf ~/backup_storage_$(date +%Y%m%d_%H%M%S).tar.gz storage/

# 4. Backup folder public/storage (jika ada)
if [ -d "public/storage" ]; then
    tar -czf ~/backup_public_storage_$(date +%Y%m%d_%H%M%S).tar.gz public/storage/
fi
```

---

## LANGKAH 1: Persiapan File di Localhost

### 1.1 Buat Archive Project (Exclude yang tidak perlu)

**Di Windows (PowerShell atau CMD):**

```powershell
# Navigate ke folder project
cd C:\xampp\htdocs\hris-seven-payroll

# Buat file .gitignore atau .rsync-exclude untuk exclude folder
# Folder yang TIDAK perlu di-upload:
# - vendor/
# - node_modules/
# - .git/
# - storage/logs/*.log
# - .env (akan di-copy manual)
# - public/storage (jika ada symlink)
```

**Opsi A: Menggunakan WinRAR/7-Zip (Manual)**

1. Buat archive ZIP dari folder project
2. Exclude folder: `vendor`, `node_modules`, `.git`, `storage/logs`, `.env`
3. Upload ke server via SFTP/SCP

**Opsi B: Menggunakan SCP (Recommended)**

```bash
# Dari Windows (dengan Git Bash atau WSL)
cd /c/xampp/htdocs/hris-seven-payroll

# Upload dengan exclude folder tertentu
rsync -avz --exclude 'vendor' \
           --exclude 'node_modules' \
           --exclude '.git' \
           --exclude 'storage/logs/*.log' \
           --exclude '.env' \
           --exclude 'public/storage' \
           ./ username@192.168.10.40:/tmp/hris-seven-payroll-update/
```

---

## LANGKAH 2: Upload File ke Server

### 2.1 Upload ke Temporary Folder

```bash
# Di server Ubuntu, buat folder temporary
sudo mkdir -p /tmp/hris-seven-payroll-update

# Upload file dari localhost ke server
# (Gunakan SCP, SFTP, atau FileZilla)
# Pastikan file sudah di-upload ke /tmp/hris-seven-payroll-update/
```

### 2.2 Verifikasi File yang Di-upload

```bash
# Check apakah file penting sudah ada
ls -la /tmp/hris-seven-payroll-update/app/Http/Controllers/AuthController.php
ls -la /tmp/hris-seven-payroll-update/app/Models/Role.php
ls -la /tmp/hris-seven-payroll-update/database/migrations/2025_11_20_*.php
ls -la /tmp/hris-seven-payroll-update/database/seeders/RolePermissionSeeder.php
```

---

## LANGKAH 3: Update File di Server

### 3.1 Backup File yang Akan Di-replace

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Backup file penting yang mungkin sudah di-modify
sudo cp -r app app_backup_$(date +%Y%m%d_%H%M%S)
sudo cp -r routes routes_backup_$(date +%Y%m%d_%H%M%S)
sudo cp -r resources resources_backup_$(date +%Y%m%d_%H%M%S)
```

### 3.2 Copy File Baru (Maintain .env dan storage)

```bash
# Copy file baru, tapi jangan overwrite .env dan storage
cd /var/www/html/hris-seven-payroll

# Copy semua file kecuali yang perlu dipertahankan
sudo rsync -av --exclude '.env' \
              --exclude 'storage' \
              --exclude 'vendor' \
              --exclude 'node_modules' \
              /tmp/hris-seven-payroll-update/ ./

# Atau jika tidak ada rsync, gunakan cp dengan exclude manual
# sudo cp -r /tmp/hris-seven-payroll-update/app/* app/
# sudo cp -r /tmp/hris-seven-payroll-update/routes/* routes/
# sudo cp -r /tmp/hris-seven-payroll-update/resources/* resources/
# sudo cp -r /tmp/hris-seven-payroll-update/database/* database/
# ... dst
```

### 3.3 Set Ownership dan Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll

# Set permissions
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

---

## LANGKAH 4: Update Dependencies

### 4.1 Update Composer Dependencies

```bash
cd /var/www/html/hris-seven-payroll

# Install/update composer dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction

# Jika ada error permission, gunakan:
# sudo composer install --optimize-autoloader --no-dev --no-interaction
```

### 4.2 Update NPM Dependencies (jika ada)

```bash
# Jika menggunakan Vite/Mix
npm install --production
npm run build
```

---

## LANGKAH 5: Update Database

### 5.1 Run Migrations Baru

```bash
cd /var/www/html/hris-seven-payroll

# Check migrations yang belum dijalankan
sudo -u www-data php artisan migrate:status

# Run migrations baru
sudo -u www-data php artisan migrate --force

# Jika ada error, check log:
# tail -f storage/logs/laravel.log
```

### 5.2 Run Seeders Baru

```bash
# Run RolePermissionSeeder (untuk membuat roles dan permissions)
sudo -u www-data php artisan db:seed --class=RolePermissionSeeder

# Run AdminUserRoleSeeder (untuk assign admin role ke admin users)
sudo -u www-data php artisan db:seed --class=AdminUserRoleSeeder

# Jika perlu update UserSeeder juga
sudo -u www-data php artisan db:seed --class=UserSeeder
```

---

## LANGKAH 6: Update Konfigurasi

### 6.1 Check dan Update .env (jika perlu)

```bash
cd /var/www/html/hris-seven-payroll

# Check .env file
sudo nano .env

# Pastikan konfigurasi berikut sudah benar:
# APP_URL=http://192.168.10.40/hris-seven-payroll
# ASSET_URL=http://192.168.10.40/hris-seven-payroll
# APP_DEBUG=false
# APP_ENV=production
```

### 6.2 Clear dan Re-cache Laravel

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear

# Re-cache untuk production
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

## LANGKAH 7: Verifikasi Update

### 7.1 Test Akses Aplikasi

```bash
# Test dari browser:
# http://192.168.10.40/hris-seven-payroll

# Check log jika ada error
tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log
tail -f /var/log/apache2/error.log
```

### 7.2 Test Fitur Baru

1. **Test Login System**

    - Akses: `http://192.168.10.40/hris-seven-payroll/login`
    - Login dengan admin@hris.com / admin123
    - Pastikan redirect ke dashboard berhasil

2. **Test Role & Permission**

    - Login sebagai admin
    - Check menu "Settings" muncul di sidebar
    - Test akses ke "Pengelolaan User", "Pengelolaan Role", "Pengelolaan Permission"

3. **Test Menu Visibility**

    - Login dengan user yang punya role berbeda
    - Pastikan menu yang muncul sesuai dengan permission

4. **Test Fitur Existing**
    - Test semua fitur yang sudah ada sebelumnya
    - Pastikan tidak ada yang broken

---

## LANGKAH 8: Cleanup (Opsional)

### 8.1 Hapus File Temporary

```bash
# Hapus folder temporary
sudo rm -rf /tmp/hris-seven-payroll-update

# Hapus backup folder (jika sudah yakin update berhasil)
# sudo rm -rf app_backup_*
# sudo rm -rf routes_backup_*
```

---

## TROUBLESHOOTING

### Error: Migration Already Exists

```bash
# Jika migration sudah pernah dijalankan tapi ada error
php artisan migrate:refresh --force  # HATI-HATI: Ini akan drop semua data!

# Atau rollback dan migrate lagi
php artisan migrate:rollback --step=1
php artisan migrate --force
```

### Error: Permission Denied

```bash
# Fix ownership dan permissions
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

### Error: Class Not Found

```bash
# Clear autoload dan regenerate
composer dump-autoload --optimize
php artisan config:clear
php artisan route:clear
```

### Error: 500 Internal Server Error

```bash
# Check Laravel log
tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log

# Check Apache error log
tail -f /var/log/apache2/error.log

# Clear semua cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Error: Database Connection Failed

```bash
# Test koneksi database
mysql -u root -proot123 -e "USE hris_seven; SHOW TABLES;"

# Check .env file
cat /var/www/html/hris-seven-payroll/.env | grep DB_
```

---

## CHECKLIST UPDATE

Gunakan checklist ini untuk memastikan semua langkah sudah dilakukan:

-   [ ] **Backup Database** - Database sudah di-backup
-   [ ] **Backup .env** - File .env sudah di-backup
-   [ ] **Backup Storage** - Folder storage sudah di-backup (jika ada file penting)
-   [ ] **Upload File** - File baru sudah di-upload ke server
-   [ ] **Update Dependencies** - Composer dependencies sudah di-update
-   [ ] **Run Migrations** - Migrations baru sudah dijalankan
-   [ ] **Run Seeders** - Seeders baru sudah dijalankan
-   [ ] **Clear Cache** - Cache Laravel sudah di-clear dan re-cache
-   [ ] **Test Login** - Login system berfungsi
-   [ ] **Test Role/Permission** - Role & permission system berfungsi
-   [ ] **Test Menu** - Menu visibility sesuai permission
-   [ ] **Test Fitur Existing** - Semua fitur existing masih berfungsi
-   [ ] **Check Logs** - Tidak ada error di log files
-   [ ] **Cleanup** - File temporary sudah dihapus

---

## ROLLBACK (Jika Update Gagal)

Jika update menyebabkan masalah, lakukan rollback:

```bash
# 1. Restore Database
mysql -u root -proot123 hris_seven < ~/backup_hris_seven_YYYYMMDD_HHMMSS.sql

# 2. Restore .env
cp ~/backup_env_YYYYMMDD_HHMMSS.txt /var/www/html/hris-seven-payroll/.env

# 3. Restore File (jika perlu)
sudo cp -r app_backup_YYYYMMDD_HHMMSS/* app/
sudo cp -r routes_backup_YYYYMMDD_HHMMSS/* routes/
# ... dst

# 4. Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Restart Apache
sudo systemctl restart apache2
```

---

## CATATAN PENTING

1. **Selalu backup** sebelum update
2. **Test di staging** dulu jika memungkinkan
3. **Update satu per satu** jika ada banyak perubahan besar
4. **Monitor log files** setelah update
5. **Test semua fitur** setelah update
6. **Dokumentasikan** perubahan yang dilakukan

---

## SELESAI!

Setelah semua langkah selesai, aplikasi seharusnya sudah ter-update dengan versi terbaru.

Jika ada masalah, check log files dan lakukan troubleshooting sesuai panduan di atas.








