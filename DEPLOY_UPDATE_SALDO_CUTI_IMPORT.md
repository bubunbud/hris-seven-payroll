# 📋 Panduan Deployment: Update Saldo Cuti (Import Feature) & Rekapitulasi Cuti

**Tanggal:** 11 Desember 2025  
**Fitur Update:** 
- **Saldo Cuti** - Fitur Import/Update Saldo Cuti dari Excel/CSV
- **Rekapitulasi Cuti** - Fitur rekapitulasi cuti karyawan (sudah ada sebelumnya)  
**Lokasi Menu:** 
- Absensi → Saldo Cuti (dengan fitur Import)
- Absensi → Rekapitulasi Cuti

---

## 📦 File yang Perlu Di-Copy

### 1. Controller (2 file)
```
app/Http/Controllers/SaldoCutiController.php (UPDATE - tambah method importExcel)
app/Http/Controllers/RekapitulasiCutiController.php (UPDATE - jika ada perubahan)
```

### 2. View (2 file)
```
resources/views/cuti/saldo/index.blade.php (UPDATE - tambah form upload)
resources/views/cuti/rekapitulasi/index.blade.php (UPDATE - jika ada perubahan)
```

### 3. Route (1 file - update)
```
routes/web.php (UPDATE - tambah route saldo-cuti.import)
```

### 4. Template File (1 file - baru)
```
public/TEMPLATE_SALDO_CUTI.csv (BARU - template untuk import)
```

**Total: 6 file (1 file baru, 5 file update)**

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
# 1. SALDO CUTI (UPDATE - Import Feature)
# ============================================

# Copy Controller (update - tambah method importExcel)
scp app/Http/Controllers/SaldoCutiController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Copy View (update - tambah form upload)
scp resources/views/cuti/saldo/index.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/cuti/saldo/

# ============================================
# 2. REKAPITULASI CUTI (UPDATE - jika ada perubahan)
# ============================================

# Copy Controller (update - jika ada perubahan)
scp app/Http/Controllers/RekapitulasiCutiController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Copy View (update - jika ada perubahan)
scp resources/views/cuti/rekapitulasi/index.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/cuti/rekapitulasi/

# ============================================
# 3. UPDATE FILE (Route & Template)
# ============================================

# Copy Route (update - tambah route import)
scp routes/web.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/routes/

# Copy Template CSV (baru)
scp public/TEMPLATE_SALDO_CUTI.csv superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/public/
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
sudo chown -R www-data:www-data resources/views/cuti/
sudo chown -R www-data:www-data routes/web.php
sudo chown -R www-data:www-data public/TEMPLATE_SALDO_CUTI.csv

# Set permission
sudo chmod -R 755 app/Http/Controllers/
sudo chmod -R 755 resources/views/
sudo chmod -R 755 routes/
sudo chmod -R 755 public/
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
# POST      saldo-cuti/import ................ saldo-cuti.import  <-- ROUTE BARU
```

---

### **Langkah 6: Verifikasi File**

```bash
# Cek apakah semua file sudah ter-copy dengan benar
ls -la app/Http/Controllers/SaldoCutiController.php
ls -la app/Http/Controllers/RekapitulasiCutiController.php
ls -la resources/views/cuti/saldo/index.blade.php
ls -la resources/views/cuti/rekapitulasi/index.blade.php
ls -la routes/web.php
ls -la public/TEMPLATE_SALDO_CUTI.csv

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
   - Scroll ke bagian "Import/Update Saldo Cuti dari Excel"
   - Pastikan form upload muncul dengan:
     - Input file CSV/TXT
     - Dropdown "Mode Import" (Sisa Saldo / Detail)
     - Checkbox "Skip Header"
     - Dropdown "Separator"
     - Tombol "Upload & Update"
   - Test download template: Klik link "Download Template CSV"
   - Test upload file CSV dengan data sisa saldo
   - Verifikasi bahwa kolom "Saldo Sisa" sesuai dengan data yang di-upload

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

### **Error 5: Template CSV tidak bisa di-download**

```bash
# Pastikan file template sudah ada
ls -la public/TEMPLATE_SALDO_CUTI.csv

# Set permission
sudo chown -R www-data:www-data public/TEMPLATE_SALDO_CUTI.csv
sudo chmod 644 public/TEMPLATE_SALDO_CUTI.csv
```

### **Error 6: Upload file gagal**

```bash
# Cek permission storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/storage

# Cek upload_max_filesize di php.ini
php -i | grep upload_max_filesize

# Jika perlu, edit php.ini:
# upload_max_filesize = 10M
# post_max_size = 10M
```

### **Error 7: Import gagal - data tidak sesuai**

- Pastikan format CSV sesuai template:
  - Mode "Sisa Saldo": `NIK,Tahun,Sisa Saldo,Keterangan`
  - Mode "Detail": `NIK,Tahun,Saldo Tahun Lalu,Saldo Tahun Ini,Keterangan`
- Pastikan NIK ada di database `m_karyawan`
- Pastikan Tahun valid (2020-2100)
- Pastikan nilai saldo >= 0

---

## ✅ Checklist Deployment

### **Pre-Deployment**
- [ ] Backup database sudah dilakukan
- [ ] Backup .env sudah dilakukan
- [ ] File lokal sudah di-verifikasi (tidak ada error)
- [ ] Fitur import sudah di-test di localhost

### **File Upload**
- [ ] SaldoCutiController.php sudah di-copy (dengan method importExcel)
- [ ] RekapitulasiCutiController.php sudah di-copy (jika ada perubahan)
- [ ] cuti/saldo/index.blade.php sudah di-copy (dengan form upload)
- [ ] cuti/rekapitulasi/index.blade.php sudah di-copy (jika ada perubahan)
- [ ] routes/web.php sudah di-update (dengan route import)
- [ ] TEMPLATE_SALDO_CUTI.csv sudah di-copy ke public/

### **Server Configuration**
- [ ] Permission file sudah di-set (www-data:www-data)
- [ ] Permission direktori sudah di-set (755)
- [ ] Permission storage sudah di-set (775)
- [ ] Cache Laravel sudah di-clear
- [ ] Route sudah di-verifikasi (termasuk route import)

### **Testing**
- [ ] Menu Saldo Cuti bisa diakses tanpa error
- [ ] Menu Rekapitulasi Cuti bisa diakses tanpa error
- [ ] Form upload muncul di halaman Saldo Cuti
- [ ] Template CSV bisa di-download
- [ ] Upload file CSV berhasil
- [ ] Data saldo sisa sesuai dengan input setelah import
- [ ] Filter Saldo Cuti berfungsi
- [ ] Filter Rekapitulasi Cuti berfungsi
- [ ] Export Excel Rekapitulasi Cuti berfungsi
- [ ] Simpan/Edit Saldo Cuti berfungsi

---

## 📝 Catatan Penting

1. **Fitur Import Saldo Cuti:**
   - **Mode "Sisa Saldo" (Recommended):** Format: `NIK,Tahun,Sisa Saldo,Keterangan`
     - Sistem akan otomatis menghitung `decTahunLalu` dan `decTahunIni` agar saldo sisa sesuai input
     - `decTahunIni` bisa lebih dari 12 hari untuk proses awal update
   - **Mode "Detail":** Format: `NIK,Tahun,Saldo Tahun Lalu,Saldo Tahun Ini,Keterangan`
     - Langsung set `decTahunLalu` dan `decTahunIni` sesuai input
   - File harus berformat CSV atau TXT (untuk Excel, simpan sebagai CSV dulu)
   - Maksimal ukuran file: 10MB
   - Validasi: NIK harus ada di database, Tahun 2020-2100, Saldo >= 0

2. **Saldo Cuti:**
   - Pastikan tabel `m_saldo_cuti` sudah ada di database
   - Tabel menggunakan composite primary key: `(vcNik, intTahun)`
   - Perhitungan saldo sisa: `(decTahunLalu - tahunLaluTerpakai) + (decTahunIni - tahunIniTerpakai)`
   - Prioritas pengurangan: Tahun Lalu dulu, baru Tahun Ini

3. **Rekapitulasi Cuti:**
   - Data diambil dari:
     - `t_tidak_masuk` (Cuti Pribadi - C010)
     - `m_hari_libur` (Cuti Bersama - vcTipeHariLibur = 'Cuti Bersama')
     - `m_saldo_cuti` (Saldo Cuti)
   - Pastikan data master sudah lengkap

4. **Permission:**
   - User harus memiliki permission `view-absensi` untuk mengakses menu ini
   - Permission sudah di-set di route middleware

5. **Export Excel:**
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
5. Format CSV sesuai template

---

## 🎯 Fitur Baru yang Ditambahkan

### **Import/Update Saldo Cuti dari Excel/CSV**

**Fitur:**
- Upload file CSV/TXT untuk import/update saldo cuti
- Mode "Sisa Saldo": Import langsung sisa saldo, sistem hitung otomatis
- Mode "Detail": Import saldo tahun lalu dan tahun ini secara detail
- Auto-detect separator CSV (koma, tab, titik koma)
- Skip header baris pertama
- Validasi data (NIK, Tahun, Saldo)
- Update existing record atau insert new record
- Download template CSV untuk referensi format

**Cara Menggunakan:**
1. Siapkan file CSV dengan format sesuai template
2. Buka menu: **Absensi → Saldo Cuti**
3. Scroll ke bagian "Import/Update Saldo Cuti dari Excel"
4. Pilih file CSV
5. Pilih Mode Import (Sisa Saldo / Detail)
6. Set opsi (Skip Header, Separator)
7. Klik "Upload & Update"
8. Verifikasi hasil import

---

**Selamat Deployment! 🚀**


