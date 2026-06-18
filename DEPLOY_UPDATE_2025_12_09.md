# 📋 Panduan Deployment: Update Fitur Absensi & Tarik Data

**Tanggal:** 9 Desember 2025  
**Perubahan:**
- Optimasi Statistik Absensi (fix timeout)
- Revisi Final Kehadiran (Rekapitulasi Absensi)
- Revisi %H (Rekapitulasi Absen All)
- Filter HKN, KHL, Telat (Browse Absensi)
- Pindah menu Tarik Data Absensi ke Settings
- Revisi logika Tarik Data Absensi (sinkronisasi penuh)

**Catatan:** Tidak ada perubahan database/migration

---

## 📦 File yang Perlu Di-Copy

### 1. Controller (5 file - update)
```
app/Http/Controllers/StatistikAbsensiController.php
app/Http/Controllers/RekapitulasiAbsensiController.php
app/Http/Controllers/RekapitulasiAbsenAllController.php
app/Http/Controllers/AbsenController.php
app/Http/Controllers/TarikDataAbsensiController.php
```

### 2. View (2 file - update)
```
resources/views/layouts/app.blade.php
resources/views/absen/index.blade.php
```

### 3. Route (1 file - update)
```
routes/web.php
```

**Total: 8 file (semua update, tidak ada file baru)**

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
# 1. CONTROLLER (5 file)
# ============================================

# Copy StatistikAbsensiController
scp app/Http/Controllers/StatistikAbsensiController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Copy RekapitulasiAbsensiController
scp app/Http/Controllers/RekapitulasiAbsensiController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Copy RekapitulasiAbsenAllController
scp app/Http/Controllers/RekapitulasiAbsenAllController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Copy AbsenController
scp app/Http/Controllers/AbsenController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Copy TarikDataAbsensiController
scp app/Http/Controllers/TarikDataAbsensiController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# ============================================
# 2. VIEW (2 file)
# ============================================

# Copy Layout
scp resources/views/layouts/app.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/layouts/

# Copy View Absensi
scp resources/views/absen/index.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/absen/

# ============================================
# 3. ROUTE (1 file)
# ============================================

# Copy Route
scp routes/web.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/routes/
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
   php artisan route:list | grep -E "(absensi|rekapitulasi|tarik-data)"
   ```

2. **Cek File Permission:**
   ```bash
   ls -la app/Http/Controllers/StatistikAbsensiController.php
   ls -la resources/views/absen/index.blade.php
   ls -la routes/web.php
   ```

3. **Test Aplikasi:**
   - Buka browser: `http://hr.abncorp.lan` atau `http://192.168.10.40`
   - Login ke aplikasi
   - Test fitur-fitur yang diupdate:
     - ✅ Statistik Absensi (tidak timeout)
     - ✅ Rekapitulasi Absensi (Final Kehadiran)
     - ✅ Rekapitulasi Absen All (%H)
     - ✅ Browse Absensi (Filter HKN, KHL, Telat)
     - ✅ Settings → Tarik Data Absensi (menu sudah pindah)
     - ✅ Tarik Data Absensi (sinkronisasi penuh)

---

## 📝 Checklist Deployment

- [ ] Backup database (opsional)
- [ ] Copy 5 Controller files
- [ ] Copy 2 View files
- [ ] Copy 1 Route file
- [ ] Set permission file & direktori
- [ ] Clear Laravel cache
- [ ] Optimize cache (opsional)
- [ ] Verifikasi route
- [ ] Test aplikasi di browser
- [ ] Verifikasi semua fitur berfungsi

---

## ⚠️ Catatan Penting

1. **Tidak ada perubahan database/migration** - hanya update file PHP dan Blade
2. **Tidak perlu run `php artisan migrate`**
3. **Pastikan permission file sudah benar** (644 untuk file, 755 untuk direktori)
4. **Clear cache wajib** agar perubahan route dan view terdeteksi
5. **Test semua fitur** setelah deployment untuk memastikan tidak ada error

---

## 🔧 Troubleshooting

### Error: Route tidak ditemukan
```bash
php artisan route:clear
php artisan route:cache
```

### Error: View tidak ditemukan
```bash
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

---

## 📞 Informasi Server

- **Server IP:** 192.168.10.40
- **Domain:** http://hr.abncorp.lan
- **Lokasi Aplikasi:** /var/www/html/hris-seven-payroll
- **User:** superadmin
- **Database:** hris_seven

---

**Selamat Deploy! 🚀**








