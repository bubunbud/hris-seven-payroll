# Quick Update Guide - Instruksi Kerja Lembur ke Server Ubuntu

Panduan cepat untuk mengupdate perubahan Instruksi Kerja Lembur ke server Ubuntu (192.168.10.40).

## 📋 File yang Akan Di-update

1. **Controller**: `app/Http/Controllers/InstruksiKerjaLemburController.php`
2. **Controller**: `app/Http/Controllers/ClosingController.php`
3. **Service**: `app/Services/LemburCalculationService.php` (BARU)
4. **Model**: `app/Models/LemburDetail.php`
5. **View**: `resources/views/instruksi-kerja-lembur/index.blade.php`
6. **Routes**: `routes/web.php`
7. **Migration**: `database/migrations/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php` (BARU)

---

## 🚀 TAHAPAN UPDATE (Simple & Efektif)

### **TAHAP 1: Backup di Server** ⚠️ PENTING!

```bash
# SSH ke server Ubuntu
ssh root@192.168.10.40

# Masuk ke folder aplikasi
cd /var/www/html/hris-seven-payroll

# Backup database
mysqldump -u root -proot123 hris_seven > ~/backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql

# Backup .env
cp .env ~/backup_env_$(date +%Y%m%d_%H%M%S).txt

# Backup folder app dan resources (jika perlu rollback)
sudo cp -r app app_backup_$(date +%Y%m%d_%H%M%S)
sudo cp -r resources resources_backup_$(date +%Y%m%d_%H%M%S)
```

---

### **TAHAP 2: Upload File dari Localhost ke Server**

**Opsi A: Menggunakan SCP (Recommended - dari Git Bash atau WSL)**

```bash
# Dari Windows (Git Bash atau WSL)
cd /c/xampp/htdocs/hris-seven-payroll

# Upload file-file yang berubah saja
scp app/Http/Controllers/InstruksiKerjaLemburController.php root@192.168.10.40:/tmp/
scp app/Http/Controllers/ClosingController.php root@192.168.10.40:/tmp/
scp app/Services/LemburCalculationService.php root@192.168.10.40:/tmp/
scp app/Models/LemburDetail.php root@192.168.10.40:/tmp/
scp resources/views/instruksi-kerja-lembur/index.blade.php root@192.168.10.40:/tmp/
scp routes/web.php root@192.168.10.40:/tmp/
scp database/migrations/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php root@192.168.10.40:/tmp/
```

**Opsi B: Menggunakan FileZilla/WinSCP (Manual)**

1. Buka FileZilla atau WinSCP
2. Connect ke `192.168.10.40` dengan user `root`
3. Upload file-file berikut ke folder `/tmp/` di server:
    - `app/Http/Controllers/InstruksiKerjaLemburController.php`
    - `app/Http/Controllers/ClosingController.php`
    - `app/Services/LemburCalculationService.php` (folder Services mungkin perlu dibuat)
    - `app/Models/LemburDetail.php`
    - `resources/views/instruksi-kerja-lembur/index.blade.php`
    - `routes/web.php`
    - `database/migrations/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php`

---

### **TAHAP 3: Copy File ke Folder Aplikasi di Server**

```bash
# SSH ke server (jika belum)
ssh root@192.168.10.40

# Masuk ke folder aplikasi
cd /var/www/html/hris-seven-payroll

# Pastikan folder Services ada
mkdir -p app/Services

# Copy file dari /tmp/ ke folder aplikasi
sudo cp /tmp/InstruksiKerjaLemburController.php app/Http/Controllers/
sudo cp /tmp/ClosingController.php app/Http/Controllers/
sudo cp /tmp/LemburCalculationService.php app/Services/
sudo cp /tmp/LemburDetail.php app/Models/
sudo cp /tmp/index.blade.php resources/views/instruksi-kerja-lembur/
sudo cp /tmp/web.php routes/
sudo cp /tmp/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php database/migrations/

# Set ownership
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

---

### **TAHAP 4: Update Dependencies & Autoload**

```bash
cd /var/www/html/hris-seven-payroll

# Update composer autoload (untuk class LemburCalculationService yang baru)
sudo -u www-data composer dump-autoload --optimize

# Jika ada perubahan di composer.json, install dependencies
# sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction
```

---

### **TAHAP 5: Run Migration Baru**

```bash
cd /var/www/html/hris-seven-payroll

# Check status migration
sudo -u www-data php artisan migrate:status

# Run migration baru (tambah kolom decLemburExternal)
sudo -u www-data php artisan migrate --force

# Verifikasi kolom sudah ditambahkan
mysql -u root -proot123 hris_seven -e "DESCRIBE t_lembur_detail;" | grep decLemburExternal
```

---

### **TAHAP 6: Clear Cache Laravel**

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

### **TAHAP 7: Verifikasi Update**

```bash
# Test akses aplikasi
curl -I http://192.168.10.40/hris-seven-payroll

# Check log jika ada error
tail -20 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
tail -20 /var/log/apache2/error.log
```

**Test di Browser:**

1. Akses: `http://192.168.10.40/hris-seven-payroll`
2. Login dengan user yang ada
3. Buka menu **Instruksi Kerja Lembur**
4. Test fitur:
    - Tambah data baru
    - Edit data existing
    - Pastikan kolom "Nominal Lembur" muncul di detail
    - Pastikan layout detail sudah sesuai (kolom deskripsi lebih lebar)

---

### **TAHAP 8: Cleanup (Opsional)**

```bash
# Hapus file temporary
sudo rm -f /tmp/InstruksiKerjaLemburController.php
sudo rm -f /tmp/ClosingController.php
sudo rm -f /tmp/LemburCalculationService.php
sudo rm -f /tmp/LemburDetail.php
sudo rm -f /tmp/index.blade.php
sudo rm -f /tmp/web.php
sudo rm -f /tmp/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php
```

---

## ⚠️ TROUBLESHOOTING

### Error: Class 'App\Services\LemburCalculationService' not found

```bash
# Clear autoload dan regenerate
cd /var/www/html/hris-seven-payroll
sudo -u www-data composer dump-autoload --optimize
sudo -u www-data php artisan config:clear
```

### Error: Column 'decLemburExternal' not found

```bash
# Pastikan migration sudah dijalankan
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan migrate:status
sudo -u www-data php artisan migrate --force
```

### Error: 500 Internal Server Error

```bash
# Check log
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log

# Clear semua cache
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear

# Restart Apache
sudo systemctl restart apache2
```

### Error: Permission Denied

```bash
# Fix ownership dan permissions
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

---

## 🔄 ROLLBACK (Jika Update Gagal)

```bash
# 1. Restore file dari backup
cd /var/www/html/hris-seven-payroll
sudo cp -r app_backup_YYYYMMDD_HHMMSS/* app/
sudo cp -r resources_backup_YYYYMMDD_HHMMSS/* resources/

# 2. Rollback migration (jika perlu)
sudo -u www-data php artisan migrate:rollback --step=1

# 3. Clear cache
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# 4. Restart Apache
sudo systemctl restart apache2
```

---

## ✅ CHECKLIST UPDATE

-   [ ] **Backup Database** - Database sudah di-backup
-   [ ] **Backup .env** - File .env sudah di-backup
-   [ ] **Upload File** - Semua file sudah di-upload ke server
-   [ ] **Copy File** - File sudah di-copy ke folder aplikasi
-   [ ] **Set Permissions** - Ownership dan permissions sudah benar
-   [ ] **Update Autoload** - Composer autoload sudah di-update
-   [ ] **Run Migration** - Migration baru sudah dijalankan
-   [ ] **Clear Cache** - Cache Laravel sudah di-clear
-   [ ] **Test Aplikasi** - Aplikasi bisa diakses dan fitur berfungsi
-   [ ] **Check Logs** - Tidak ada error di log files

---

## 📝 CATATAN

1. **Selalu backup** sebelum update
2. **Test di browser** setelah update
3. **Monitor log files** untuk error
4. **Jika ada masalah**, lakukan rollback sesuai panduan di atas

---

**SELESAI!** 🎉

Setelah semua tahapan selesai, aplikasi di server sudah ter-update dengan perubahan terbaru.




