# 📋 Panduan Deployment: Fitur Browse Tidak Absen (Alpha)

**Tanggal:** 9 Desember 2025  
**Fitur:** Browse Tidak Absen (Alpha)  
**Lokasi Menu:** Absensi → Browse Tidak Absen  
**Catatan:** Tidak ada perubahan database/migration

---

## 📦 File yang Perlu Di-Copy

### 1. Controller (1 file baru)
```
app/Http/Controllers/BrowseTidakAbsenController.php
```

### 2. View (1 file baru)
```
resources/views/absen/tidak-absen/index.blade.php
```

### 3. Route (1 file - update)
```
routes/web.php
```

### 4. Layout (1 file - update)
```
resources/views/layouts/app.blade.php
```

**Total: 4 file (2 file baru, 2 file update)**

---

## 🚀 Langkah-Langkah Deployment

### **Langkah 1: Backup Database (Opsional tapi Disarankan)**

```bash
# Login ke server Ubuntu
ssh superadmin@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup database (opsional)
mysqldump -u root -p hris_seven > backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql
```

### **Langkah 2: Copy File dari Local ke Server**

**Dari komputer lokal (Windows), jalankan perintah berikut:**

```bash
# Pastikan Anda sudah terhubung ke server via SSH atau SCP

# ============================================
# 1. CONTROLLER (1 file baru)
# ============================================

# Copy BrowseTidakAbsenController
scp app/Http/Controllers/BrowseTidakAbsenController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# ============================================
# 2. VIEW (1 file baru)
# ============================================

# Buat direktori view jika belum ada
ssh superadmin@192.168.10.40 "mkdir -p /var/www/html/hris-seven-payroll/resources/views/absen/tidak-absen"

# Copy View
scp resources/views/absen/tidak-absen/index.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/absen/tidak-absen/

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

---

### **Langkah 3: Set Permission File (Di Server Ubuntu)**

```bash
# Login ke server Ubuntu
ssh superadmin@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Set ownership ke www-data
sudo chown -R www-data:www-data app/Http/Controllers/
sudo chown -R www-data:www-data resources/views/
sudo chown -R www-data:www-data routes/

# Set permission file
sudo find app/Http/Controllers/ -type f -exec chmod 644 {} \;
sudo find resources/views/ -type f -exec chmod 644 {} \;
sudo find routes/ -type f -exec chmod 644 {} \;

# Set permission direktori
sudo find app/Http/Controllers/ -type d -exec chmod 755 {} \;
sudo find resources/views/ -type d -exec chmod 755 {} \;
sudo find routes/ -type d -exec chmod 755 {} \;
```

---

### **Langkah 4: Clear Cache Laravel (Di Server Ubuntu)**

```bash
# Masih di direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize (opsional)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### **Langkah 5: Verifikasi**

1. **Cek Route:**
   ```bash
   php artisan route:list | grep browse-tidak-absen
   ```
   Harus menampilkan route: `browse-tidak-absen.index`

2. **Cek File Permission:**
   ```bash
   ls -la app/Http/Controllers/BrowseTidakAbsenController.php
   ls -la resources/views/absen/tidak-absen/index.blade.php
   ls -la routes/web.php
   ls -la resources/views/layouts/app.blade.php
   ```
   Harus menunjukkan: `-rw-r--r-- 1 www-data www-data ...`

3. **Test Aplikasi:**
   - Buka browser: `http://hr.abncorp.lan` atau `http://192.168.10.40`
   - Login ke aplikasi
   - Test menu: **Absensi → Browse Tidak Absen**
   - Test filter: Tanggal, NIK, Nama, Group
   - Pastikan data yang ditampilkan adalah Alpha (tidak ada absensi dan tidak ada tidak masuk)

---

## 📝 Checklist Deployment

- [ ] Backup database (opsional)
- [ ] Copy 1 Controller file baru (BrowseTidakAbsenController.php)
- [ ] Buat direktori view `absen/tidak-absen` jika belum ada
- [ ] Copy 1 View file baru (index.blade.php)
- [ ] Copy 1 Route file (update: routes/web.php)
- [ ] Copy 1 Layout file (update: layouts/app.blade.php)
- [ ] Set permission file & direktori
- [ ] Clear Laravel cache
- [ ] Optimize cache (opsional)
- [ ] Verifikasi route
- [ ] Test aplikasi di browser
- [ ] Verifikasi menu "Browse Tidak Absen" muncul di sidebar
- [ ] Test filter dan pastikan data Alpha ditampilkan dengan benar

---

## ⚠️ Catatan Penting

1. **Tidak ada perubahan database/migration** - hanya update file PHP dan Blade
2. **Tidak perlu run `php artisan migrate`**
3. **Pastikan permission file sudah benar** (644 untuk file, 755 untuk direktori)
4. **Clear cache wajib** agar perubahan route dan view terdeteksi
5. **Test semua fitur** setelah deployment untuk memastikan tidak ada error
6. **Direktori view baru** harus dibuat sebelum copy file view

---

## 🔧 Troubleshooting

### Error: Route tidak ditemukan
```bash
php artisan route:clear
php artisan route:cache
```

### Error: View tidak ditemukan
```bash
# Pastikan direktori sudah dibuat
mkdir -p /var/www/html/hris-seven-payroll/resources/views/absen/tidak-absen

# Clear view cache
php artisan view:clear
php artisan view:cache
```

### Error: Permission denied
```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 644 /var/www/html/hris-seven-payroll/app/Http/Controllers/*.php
sudo chmod -R 644 /var/www/html/hris-seven-payroll/resources/views/**/*.blade.php
```

### Error: Class not found
```bash
composer dump-autoload
```

### Error: Menu tidak muncul
```bash
# Pastikan file layouts/app.blade.php sudah di-copy
# Clear cache
php artisan view:clear
php artisan cache:clear
```

---

## 📞 Informasi Server

- **Server IP:** 192.168.10.40
- **Domain:** http://hr.abncorp.lan
- **Lokasi Aplikasi:** /var/www/html/hris-seven-payroll
- **User:** superadmin
- **Database:** hris_seven

---

## 🔍 Detail Fitur

**Fitur Browse Tidak Absen:**
- Menampilkan karyawan aktif yang tidak ada data absensi (jam masuk dan jam pulang)
- Dan tidak ada data tidak masuk pada hari kerja normal
- Status default: Alpha
- Filter: Tanggal, NIK, Nama, Group
- Hanya memproses hari kerja normal (bukan weekend dan bukan hari libur)

---

**Selamat Deploy! 🚀**








