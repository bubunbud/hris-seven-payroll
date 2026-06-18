# 📋 Panduan Deployment: Saldo Cuti (Perbaikan) & Rekapitulasi Cuti (Baru)

**Tanggal:** 10 Desember 2025  
**Fitur:** 
- **Saldo Cuti** - Perbaikan error di server Ubuntu
- **Rekapitulasi Cuti** - Fitur baru untuk rekapitulasi cuti karyawan  
**Lokasi Menu:** 
- Absensi → Saldo Cuti
- Absensi → Rekapitulasi Cuti

---

## 📦 File yang Perlu Di-Copy

### 1. Controller (2 file)
```
app/Http/Controllers/SaldoCutiController.php (UPDATE - perbaikan error)
app/Http/Controllers/RekapitulasiCutiController.php (BARU)
```

### 2. Model (1 file - jika ada perubahan)
```
app/Models/SaldoCuti.php (UPDATE - jika ada perubahan)
```

### 3. View (2 file)
```
resources/views/cuti/saldo/index.blade.php (UPDATE - jika ada perubahan)
resources/views/cuti/rekapitulasi/index.blade.php (BARU)
```

### 4. Route (1 file - update)
```
routes/web.php
```

### 5. Layout (1 file - update)
```
resources/views/layouts/app.blade.php
```

**Total: 7 file (1 file baru, 6 file update)**

---

## 🚀 Langkah-Langkah Deployment

### **Langkah 1: Backup Database (WAJIB!)**

```bash
# Login ke server Ubuntu
ssh superadmin@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup database
mysqldump -u root -p hris_seven > backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql

# Backup .env (jika ada perubahan)
cp .env ~/backup_env_$(date +%Y%m%d_%H%M%S).txt
```

### **Langkah 2: Copy File dari Local ke Server**

**Dari komputer lokal (Windows), jalankan perintah berikut:**

```bash
# Pastikan Anda sudah terhubung ke server via SSH atau SCP

# ============================================
# 1. SALDO CUTI (PERBAIKAN)
# ============================================

# Copy Controller (update)
scp app/Http/Controllers/SaldoCutiController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Copy Model (jika ada perubahan)
scp app/Models/SaldoCuti.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Models/

# Buat direktori view jika belum ada
ssh superadmin@192.168.10.40 "mkdir -p /var/www/html/hris-seven-payroll/resources/views/cuti/saldo"

# Copy View (jika ada perubahan)
scp resources/views/cuti/saldo/index.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/cuti/saldo/

# ============================================
# 2. REKAPITULASI CUTI (BARU)
# ============================================

# Copy Controller (baru)
scp app/Http/Controllers/RekapitulasiCutiController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Buat direktori view jika belum ada
ssh superadmin@192.168.10.40 "mkdir -p /var/www/html/hris-seven-payroll/resources/views/cuti/rekapitulasi"

# Copy View (baru)
scp resources/views/cuti/rekapitulasi/index.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/cuti/rekapitulasi/

# ============================================
# 3. UPDATE FILE (Route & Layout)
# ============================================

# Copy Route (update)
scp routes/web.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/routes/

# Copy Layout (update)
scp resources/views/layouts/app.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/layouts/
```

**ATAU menggunakan FileZilla/WinSCP:**
- Upload file-file di atas ke lokasi yang sesuai di server
- Pastikan direktori sudah dibuat jika belum ada

---

### **Langkah 3: Set Permission di Server**

```bash
# Login ke server Ubuntu
ssh superadmin@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Set ownership ke www-data
sudo chown -R www-data:www-data app/Http/Controllers/SaldoCutiController.php
sudo chown -R www-data:www-data app/Http/Controllers/RekapitulasiCutiController.php
sudo chown -R www-data:www-data app/Models/SaldoCuti.php
sudo chown -R www-data:www-data resources/views/cuti/
sudo chown -R www-data:www-data routes/web.php
sudo chown -R www-data:www-data resources/views/layouts/app.blade.php

# Set permission
sudo chmod -R 755 app/Http/Controllers/
sudo chmod -R 755 app/Models/
sudo chmod -R 755 resources/views/
sudo chmod -R 755 routes/
```

---

### **Langkah 4: Clear Cache Laravel**

```bash
# Masih di direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize (opsional, untuk production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### **Langkah 5: Verifikasi Route**

```bash
# Cek route yang sudah terdaftar
php artisan route:list | grep -i cuti

# Harus muncul:
# GET|HEAD  cuti/rekapitulasi ................ rekapitulasi-cuti.index
# GET|HEAD  cuti/rekapitulasi/export ......... rekapitulasi-cuti.export
# GET|HEAD  saldo-cuti ........................ saldo-cuti.index
# POST      saldo-cuti ........................ saldo-cuti.store
# GET|HEAD  saldo-cuti/{id} ................. saldo-cuti.show
# POST      saldo-cuti/migrate ............... saldo-cuti.migrate
```

---

### **Langkah 6: Verifikasi File**

```bash
# Cek apakah semua file sudah ter-copy dengan benar
ls -la app/Http/Controllers/SaldoCutiController.php
ls -la app/Http/Controllers/RekapitulasiCutiController.php
ls -la app/Models/SaldoCuti.php
ls -la resources/views/cuti/saldo/index.blade.php
ls -la resources/views/cuti/rekapitulasi/index.blade.php
ls -la routes/web.php
ls -la resources/views/layouts/app.blade.php

# Semua file harus ada dan permission-nya benar
```

---

### **Langkah 7: Test Aplikasi**

1. **Akses aplikasi di browser:**
   ```
   http://hr.abncorp.lan
   atau
   http://192.168.10.40
   ```

2. **Login dengan user yang memiliki permission `view-absensi`**

3. **Test Menu Saldo Cuti:**
   - Klik menu: **Absensi → Saldo Cuti**
   - Pastikan halaman terbuka tanpa error
   - Test filter dan pencarian
   - Test simpan/edit saldo cuti

4. **Test Menu Rekapitulasi Cuti:**
   - Klik menu: **Absensi → Rekapitulasi Cuti**
   - Pastikan halaman terbuka tanpa error
   - Test filter (tanggal, divisi, departemen, group)
   - Test tombol "Preview"
   - Test tombol "Export Excel"

---

## ⚠️ Troubleshooting

### **Error 1: Route tidak ditemukan**

```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Cek route lagi
php artisan route:list | grep -i cuti
```

### **Error 2: Class not found**

```bash
# Clear autoload
composer dump-autoload

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### **Error 3: Permission denied**

```bash
# Set permission ulang
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

### **Error 4: View tidak ditemukan**

```bash
# Pastikan direktori sudah dibuat
mkdir -p /var/www/html/hris-seven-payroll/resources/views/cuti/saldo
mkdir -p /var/www/html/hris-seven-payroll/resources/views/cuti/rekapitulasi

# Set permission
sudo chown -R www-data:www-data resources/views/cuti/
sudo chmod -R 755 resources/views/cuti/
```

### **Error 5: Database error (Saldo Cuti)**

```bash
# Cek apakah tabel m_saldo_cuti sudah ada
mysql -u root -p hris_seven -e "DESCRIBE m_saldo_cuti;"

# Jika tabel tidak ada, jalankan migration
php artisan migrate

# Jika ada error migration, cek migration file
ls -la database/migrations/*saldo_cuti*
```

---

## ✅ Checklist Deployment

### **Pre-Deployment**
- [ ] Backup database sudah dilakukan
- [ ] Backup .env sudah dilakukan
- [ ] File lokal sudah di-verifikasi (tidak ada error)

### **File Upload**
- [ ] SaldoCutiController.php sudah di-copy
- [ ] RekapitulasiCutiController.php sudah di-copy
- [ ] SaldoCuti.php (Model) sudah di-copy (jika ada perubahan)
- [ ] cuti/saldo/index.blade.php sudah di-copy (jika ada perubahan)
- [ ] cuti/rekapitulasi/index.blade.php sudah di-copy
- [ ] routes/web.php sudah di-update
- [ ] layouts/app.blade.php sudah di-update

### **Server Configuration**
- [ ] Permission file sudah di-set (www-data:www-data)
- [ ] Permission direktori sudah di-set (755)
- [ ] Cache Laravel sudah di-clear
- [ ] Route sudah di-verifikasi

### **Testing**
- [ ] Menu Saldo Cuti bisa diakses tanpa error
- [ ] Menu Rekapitulasi Cuti bisa diakses tanpa error
- [ ] Filter Saldo Cuti berfungsi
- [ ] Filter Rekapitulasi Cuti berfungsi
- [ ] Export Excel Rekapitulasi Cuti berfungsi
- [ ] Simpan/Edit Saldo Cuti berfungsi

---

## 📝 Catatan Penting

1. **Saldo Cuti:**
   - Pastikan tabel `m_saldo_cuti` sudah ada di database
   - Jika belum ada, jalankan migration: `php artisan migrate`
   - Tabel menggunakan composite primary key: `(vcNik, intTahun)`

2. **Rekapitulasi Cuti:**
   - Data diambil dari:
     - `t_tidak_masuk` (Cuti Pribadi - C010)
     - `m_hari_libur` (Cuti Bersama - vcTipeHariLibur = 'Cuti Bersama')
     - `m_saldo_cuti` (Saldo Cuti)
   - Pastikan data master sudah lengkap

3. **Permission:**
   - User harus memiliki permission `view-absensi` untuk mengakses menu ini
   - Permission sudah di-set di route middleware

4. **Export Excel:**
   - Format file: `.xls` (TSV format untuk kompatibilitas Excel)
   - File akan di-download otomatis saat klik tombol "Export Excel"

---

## 🔗 Informasi Server

- **Server IP:** 192.168.10.40
- **Domain:** http://hr.abncorp.lan
- **Lokasi Aplikasi:** `/var/www/html/hris-seven-payroll`
- **Database:** `hris_seven`
- **Web Server:** Apache
- **PHP Version:** 8.1+

---

## 📞 Support

Jika ada masalah saat deployment, cek:
1. Log Laravel: `storage/logs/laravel.log`
2. Log Apache: `/var/log/apache2/error.log`
3. Permission file dan direktori
4. Cache Laravel sudah di-clear

---

**Selamat Deployment! 🚀**



