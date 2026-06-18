# 📋 Panduan Deployment: Update Rekapitulasi Cuti

**Tanggal:** 11 Desember 2025  
**Fitur Update:** 
- **Rekapitulasi Cuti** - Update perhitungan cuti bersama dan cuti pribadi, tambah kolom "Cuti Tahun Lalu" & "Cuti Tahun Ini", tambah tombol Print dengan layout Landscape A4  
**Lokasi Menu:** 
- Absensi → Rekapitulasi Cuti

---

## 📦 File yang Perlu Di-Copy

### 1. Controller (1 file)
```
app/Http/Controllers/RekapitulasiCutiController.php (UPDATE)
```

### 2. View (1 file)
```
resources/views/cuti/rekapitulasi/index.blade.php (UPDATE)
```

**Total: 2 file (semua update)**

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

---

### **Langkah 2: Copy File dari Local ke Server**

**Dari komputer lokal (Windows), jalankan perintah berikut:**

```bash
# Pastikan Anda sudah terhubung ke server via SSH atau SCP

# ============================================
# REKAPITULASI CUTI (UPDATE)
# ============================================

# Copy Controller (update)
scp app/Http/Controllers/RekapitulasiCutiController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Copy View (update)
scp resources/views/cuti/rekapitulasi/index.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/cuti/rekapitulasi/
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
sudo chown -R www-data:www-data app/Http/Controllers/RekapitulasiCutiController.php
sudo chown -R www-data:www-data resources/views/cuti/rekapitulasi/index.blade.php

# Set permission
sudo chmod -R 755 app/Http/Controllers/
sudo chmod -R 755 resources/views/
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
php artisan route:list | grep -i rekapitulasi

# Harus muncul:
# GET|HEAD  cuti/rekapitulasi ................ rekapitulasi-cuti.index
# GET|HEAD  cuti/rekapitulasi/export ......... rekapitulasi-cuti.export
```

---

### **Langkah 6: Verifikasi File**

```bash
# Cek apakah semua file sudah ter-copy dengan benar
ls -la app/Http/Controllers/RekapitulasiCutiController.php
ls -la resources/views/cuti/rekapitulasi/index.blade.php

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

3. **Test Menu Rekapitulasi Cuti:**
   - Klik menu: **Absensi → Rekapitulasi Cuti**
   - Pastikan halaman terbuka tanpa error
   - Test filter (tanggal, divisi, departemen, group)
   - Test tombol "Preview"
   - **Verifikasi kolom baru:**
     - Pastikan kolom "Cuti Tahun Lalu" muncul setelah kolom "Bagian"
     - Pastikan kolom "Cuti Tahun Ini" muncul setelah kolom "Cuti Tahun Lalu"
   - **Verifikasi perhitungan:**
     - Pastikan kolom "Cuti Bersama" menampilkan jumlah yang sama dengan halaman Saldo Cuti
     - Pastikan kolom "Cuti Pribadi" menampilkan jumlah yang sama dengan kolom "Individu" di halaman Saldo Cuti
   - **Test tombol Print:**
     - Klik tombol "Cetak/Print"
     - Pastikan dialog print muncul
     - Verifikasi layout landscape A4 dengan semua kolom fit to page
   - Test tombol "Export Excel"
   - Verifikasi kolom baru juga muncul di export Excel

---

## ⚠️ Troubleshooting

### **Error 1: Route tidak ditemukan**

```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Cek route lagi
php artisan route:list | grep -i rekapitulasi
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
mkdir -p /var/www/html/hris-seven-payroll/resources/views/cuti/rekapitulasi

# Set permission
sudo chown -R www-data:www-data resources/views/cuti/
sudo chmod -R 755 resources/views/cuti/
```

### **Error 5: Print tidak berfungsi**

- Pastikan browser mendukung `window.print()`
- Cek console browser untuk error JavaScript
- Pastikan CSS print sudah ter-load dengan benar

---

## ✅ Checklist Deployment

### **Pre-Deployment**
- [ ] Backup database sudah dilakukan
- [ ] Backup .env sudah dilakukan
- [ ] File lokal sudah di-verifikasi (tidak ada error)
- [ ] Fitur sudah di-test di localhost

### **File Upload**
- [ ] RekapitulasiCutiController.php sudah di-copy
- [ ] cuti/rekapitulasi/index.blade.php sudah di-copy

### **Server Configuration**
- [ ] Permission file sudah di-set (www-data:www-data)
- [ ] Permission direktori sudah di-set (755)
- [ ] Cache Laravel sudah di-clear
- [ ] Route sudah di-verifikasi

### **Testing**
- [ ] Menu Rekapitulasi Cuti bisa diakses tanpa error
- [ ] Filter Rekapitulasi Cuti berfungsi
- [ ] Kolom "Cuti Tahun Lalu" muncul dan berisi data
- [ ] Kolom "Cuti Tahun Ini" muncul dan berisi data
- [ ] Kolom "Cuti Bersama" sesuai dengan halaman Saldo Cuti
- [ ] Kolom "Cuti Pribadi" sesuai dengan kolom "Individu" di halaman Saldo Cuti
- [ ] Tombol "Cetak/Print" berfungsi
- [ ] Layout print landscape A4 dengan semua kolom fit to page
- [ ] Export Excel berfungsi dan kolom baru muncul

---

## 📝 Catatan Penting

1. **Perhitungan Cuti Bersama:**
   - Sekarang dihitung per TAHUN (bukan range tanggal)
   - Menggunakan `whereYear('dtTanggal', $tahun)` bukan `whereBetween`
   - Sama seperti perhitungan di halaman Saldo Cuti

2. **Perhitungan Cuti Pribadi:**
   - Sekarang menghitung C010 + C012 untuk SELURUH TAHUN (bukan range tanggal)
   - Menggunakan logika overlap yang sama dengan SaldoCutiController
   - Sama seperti kolom "Individu" di halaman Saldo Cuti

3. **Kolom Baru:**
   - "Cuti Tahun Lalu": Menampilkan `decTahunLalu` dari tabel `m_saldo_cuti`
   - "Cuti Tahun Ini": Menampilkan `decTahunIni` dari tabel `m_saldo_cuti`
   - Kolom ini muncul setelah kolom "Bagian"

4. **Fitur Print:**
   - Layout: Landscape A4
   - Semua kolom fit to page
   - Font size: 7pt untuk tabel
   - Header tabel muncul di setiap halaman
   - Form filter dan tombol disembunyikan saat print

5. **Export Excel:**
   - Kolom baru juga sudah ditambahkan di export Excel
   - Urutan kolom sama dengan tampilan di halaman

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

## 🎯 Perubahan yang Dilakukan

### **1. Perhitungan Cuti Bersama**
- **Sebelum:** Dihitung dalam range tanggal (`whereBetween`)
- **Sesudah:** Dihitung per tahun (`whereYear`) - sama dengan halaman Saldo Cuti

### **2. Perhitungan Cuti Pribadi**
- **Sebelum:** Hanya C010, dalam range tanggal
- **Sesudah:** C010 + C012, untuk seluruh tahun - sama dengan kolom "Individu" di Saldo Cuti

### **3. Kolom Baru**
- **Cuti Tahun Lalu:** Menampilkan saldo cuti tahun lalu
- **Cuti Tahun Ini:** Menampilkan saldo cuti tahun ini

### **4. Fitur Print**
- **Tombol Print:** Ditambahkan tombol "Cetak/Print"
- **Layout:** Landscape A4 dengan semua kolom fit to page
- **Styling:** CSS khusus untuk print dengan font size optimal

---

**Selamat Deployment! 🚀**


