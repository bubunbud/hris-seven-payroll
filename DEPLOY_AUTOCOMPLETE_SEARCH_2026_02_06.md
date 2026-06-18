# 📋 Manual Deploy: Update Autocomplete Search Filter
**Tanggal:** 06 Februari 2026  
**Modul:** Statistik Absensi, Izin Keluar Komplek Kantor, Izin Tidak Masuk  
**Target:** Production Server Ubuntu

---

## 📌 Ringkasan Perubahan

Update pada 3 modul dengan perubahan berikut:

### **1. Statistik Absensi**
- ✅ Filter NIK diubah menjadi autocomplete search (NIK/Nama)
- ✅ Perbaikan alignment tombol Preview

### **2. Izin Keluar Komplek Kantor**
- ✅ Filter NIK diubah menjadi autocomplete search (NIK/Nama)
- ✅ Perbaikan alignment tombol Preview

### **3. Izin Tidak Masuk**
- ✅ Filter NIK diubah menjadi autocomplete search (NIK/Nama)
- ✅ Perbaikan alignment tombol Preview

---

## 📁 File yang Diubah

### **1. Controllers**
- **File:** `app/Http/Controllers/StatistikAbsensiController.php`
- **Status:** Diubah
- **Perubahan:**
  - Method `index()`: Filter NIK menjadi `search` dengan autocomplete
  - Tambah `karyawanList` untuk autocomplete
  - Update logika filter untuk mendukung search
  - Update debug info untuk menggunakan search

- **File:** `app/Http/Controllers/IzinKeluarController.php`
- **Status:** Diubah
- **Perubahan:**
  - Method `index()`: Filter NIK menjadi `search` dengan autocomplete
  - Tambah `karyawanList` untuk autocomplete
  - Update logika filter untuk mendukung search

- **File:** `app/Http/Controllers/TidakMasukController.php`
- **Status:** Diubah
- **Perubahan:**
  - Method `index()`: Filter NIK menjadi `search` dengan autocomplete
  - Tambah `karyawanList` untuk autocomplete
  - Update logika filter untuk mendukung search

### **2. Views**
- **File:** `resources/views/absen/statistik/index.blade.php`
- **Status:** Diubah
- **Perubahan:**
  - Input NIK digabung menjadi 1 input "search" dengan autocomplete
  - Tambah CSS dan JavaScript untuk autocomplete
  - Perbaikan alignment tombol Preview

- **File:** `resources/views/absen/izin_keluar/index.blade.php`
- **Status:** Diubah
- **Perubahan:**
  - Input NIK digabung menjadi 1 input "search" dengan autocomplete
  - Tambah CSS dan JavaScript untuk autocomplete
  - Perbaikan alignment tombol Preview

- **File:** `resources/views/absen/tidak_masuk/index.blade.php`
- **Status:** Diubah
- **Perubahan:**
  - Input NIK digabung menjadi 1 input "search" dengan autocomplete
  - Tambah CSS dan JavaScript untuk autocomplete
  - Perbaikan alignment tombol Preview

---

## 🗄️ Database Changes

**TIDAK ADA PERUBAHAN DATABASE**  
Semua perubahan hanya pada aplikasi (controller, view).

---

## 🚀 Langkah-Langkah Deployment

### **Step 1: Backup Database (Opsional tapi Disarankan)**

```bash
# Backup database sebelum deploy
mysqldump -u [username] -p [database_name] > backup_before_autocomplete_update_$(date +%Y%m%d_%H%M%S).sql
```

### **Step 2: Upload File ke Server**

#### **A. Upload Controllers**
```bash
# Upload controller ke server
scp app/Http/Controllers/StatistikAbsensiController.php user@server:/path/to/project/app/Http/Controllers/
scp app/Http/Controllers/IzinKeluarController.php user@server:/path/to/project/app/Http/Controllers/
scp app/Http/Controllers/TidakMasukController.php user@server:/path/to/project/app/Http/Controllers/
```

#### **B. Upload View Files**
```bash
# Upload view files
scp resources/views/absen/statistik/index.blade.php user@server:/path/to/project/resources/views/absen/statistik/
scp resources/views/absen/izin_keluar/index.blade.php user@server:/path/to/project/resources/views/absen/izin_keluar/
scp resources/views/absen/tidak_masuk/index.blade.php user@server:/path/to/project/resources/views/absen/tidak_masuk/
```

**ATAU** gunakan Git untuk push dan pull:

```bash
# Di local
git add .
git commit -m "Update autocomplete search filter untuk Statistik Absensi, Izin Keluar, dan Izin Tidak Masuk"
git push origin main

# Di server
cd /path/to/project
git pull origin main
```

### **Step 3: Set Permission File**

```bash
# Set permission untuk file yang diupload
cd /path/to/project

# Set permission untuk controllers
chmod 644 app/Http/Controllers/StatistikAbsensiController.php
chmod 644 app/Http/Controllers/IzinKeluarController.php
chmod 644 app/Http/Controllers/TidakMasukController.php

# Set permission untuk view files
chmod 644 resources/views/absen/statistik/index.blade.php
chmod 644 resources/views/absen/izin_keluar/index.blade.php
chmod 644 resources/views/absen/tidak_masuk/index.blade.php

# Set ownership (sesuaikan dengan user web server)
chown www-data:www-data app/Http/Controllers/StatistikAbsensiController.php
chown www-data:www-data app/Http/Controllers/IzinKeluarController.php
chown www-data:www-data app/Http/Controllers/TidakMasukController.php
chown www-data:www-data resources/views/absen/statistik/index.blade.php
chown www-data:www-data resources/views/absen/izin_keluar/index.blade.php
chown www-data:www-data resources/views/absen/tidak_masuk/index.blade.php
```

### **Step 4: Clear Laravel Cache**

```bash
cd /path/to/project

# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear compiled files
php artisan clear-compiled

# Optimize (opsional, untuk production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 5: Regenerate Autoload (jika perlu)**

```bash
cd /path/to/project
composer dump-autoload
```

### **Step 6: Restart Web Server (jika perlu)**

```bash
# Untuk Nginx
sudo systemctl restart nginx

# Untuk Apache
sudo systemctl restart apache2

# Untuk PHP-FPM (jika menggunakan)
sudo systemctl restart php8.1-fpm
# atau
sudo systemctl restart php-fpm
```

### **Step 7: Verifikasi Deployment**

#### **A. Cek File Permission**
```bash
# Cek apakah file ada dan permission benar
ls -la app/Http/Controllers/StatistikAbsensiController.php
ls -la app/Http/Controllers/IzinKeluarController.php
ls -la app/Http/Controllers/TidakMasukController.php
ls -la resources/views/absen/statistik/index.blade.php
ls -la resources/views/absen/izin_keluar/index.blade.php
ls -la resources/views/absen/tidak_masuk/index.blade.php
```

#### **B. Cek Log untuk Error**
```bash
# Cek Laravel log
tail -f storage/logs/laravel.log

# Cek web server error log
tail -f /var/log/nginx/error.log
# atau
tail -f /var/log/apache2/error.log
```

---

## 🧪 Testing di Production

### **Test 1: Statistik Absensi**

1. **Login ke aplikasi** dengan user yang memiliki permission

2. **Akses menu:**
   - Buka menu "Statistik Absensi"

3. **Test Autocomplete Filter:**
   - ✅ Ketik NIK atau nama di field "NIK / Nama"
   - ✅ Verifikasi dropdown autocomplete muncul
   - ✅ Pilih dari dropdown atau gunakan keyboard navigation
   - ✅ Test multi-term search (pisahkan dengan koma)
   - ✅ Submit form dan verifikasi filter bekerja

4. **Test Alignment:**
   - ✅ Verifikasi tombol "Preview" sejajar dengan field lainnya

### **Test 2: Izin Keluar Komplek Kantor**

1. **Login ke aplikasi** dengan user yang memiliki permission

2. **Akses menu:**
   - Buka menu "Izin Keluar Komplek Kantor"

3. **Test Autocomplete Filter:**
   - ✅ Ketik NIK atau nama di field "NIK / Nama"
   - ✅ Verifikasi dropdown autocomplete muncul
   - ✅ Pilih dari dropdown atau gunakan keyboard navigation
   - ✅ Test multi-term search (pisahkan dengan koma)
   - ✅ Submit form dan verifikasi filter bekerja

4. **Test Alignment:**
   - ✅ Verifikasi tombol "Preview" sejajar dengan field lainnya

### **Test 3: Izin Tidak Masuk**

1. **Login ke aplikasi** dengan user yang memiliki permission

2. **Akses menu:**
   - Buka menu "Izin Tidak Masuk"

3. **Test Autocomplete Filter:**
   - ✅ Ketik NIK atau nama di field "NIK / Nama"
   - ✅ Verifikasi dropdown autocomplete muncul
   - ✅ Pilih dari dropdown atau gunakan keyboard navigation
   - ✅ Test multi-term search (pisahkan dengan koma)
   - ✅ Submit form dan verifikasi filter bekerja

4. **Test Alignment:**
   - ✅ Verifikasi tombol "Preview" sejajar dengan field lainnya

---

## ⚠️ Troubleshooting

### **Problem 1: Autocomplete tidak muncul**
**Solution:**
- Cek browser console untuk error JavaScript
- Pastikan file view sudah ter-upload dengan lengkap
- Clear view cache: `php artisan view:clear`
- Pastikan `karyawanList` variable ada di view (cek di source HTML)

### **Problem 2: Filter tidak bekerja**
**Solution:**
- Pastikan controller sudah ter-update dengan filter `search`
- Clear cache: `php artisan cache:clear`
- Cek Laravel log untuk error: `tail -f storage/logs/laravel.log`

### **Problem 3: Tombol Preview tidak sejajar**
**Solution:**
- Pastikan file view sudah ter-update dengan benar
- Clear view cache: `php artisan view:clear`
- Cek browser untuk memastikan CSS ter-load dengan benar

### **Problem 4: Error 500 atau blank page**
**Solution:**
- Cek Laravel log: `tail -f storage/logs/laravel.log`
- Pastikan semua file ter-upload dengan benar
- Pastikan permission file benar (644 untuk file)
- Pastikan ownership file benar (www-data:www-data)
- Clear semua cache: `php artisan cache:clear && php artisan view:clear && php artisan config:clear`

---

## 📝 Checklist Deployment

- [ ] Backup database (opsional)
- [ ] Upload file controller StatistikAbsensiController.php ke server
- [ ] Upload file controller IzinKeluarController.php ke server
- [ ] Upload file controller TidakMasukController.php ke server
- [ ] Upload file view statistik/index.blade.php ke server
- [ ] Upload file view izin_keluar/index.blade.php ke server
- [ ] Upload file view tidak_masuk/index.blade.php ke server
- [ ] Set permission file dengan benar
- [ ] Clear Laravel cache (config, route, view)
- [ ] Regenerate autoload (jika perlu)
- [ ] Restart web server (jika perlu)
- [ ] Verifikasi file terdaftar
- [ ] Test autocomplete di Statistik Absensi
- [ ] Test autocomplete di Izin Keluar Komplek Kantor
- [ ] Test autocomplete di Izin Tidak Masuk
- [ ] Test alignment tombol Preview di semua halaman
- [ ] Cek log untuk error

---

## 📞 Support

Jika ada masalah saat deployment, cek:
1. Laravel log: `storage/logs/laravel.log`
2. Web server error log
3. Browser console untuk error JavaScript
4. Network tab di browser untuk error API
5. Pastikan semua file ter-upload dengan benar

---

## ✅ Post-Deployment

Setelah deployment berhasil:
1. Monitor aplikasi untuk beberapa hari
2. Kumpulkan feedback dari user tentang fitur autocomplete
3. Verifikasi semua halaman berfungsi dengan baik
4. Dokumentasikan jika ada issue atau improvement yang diperlukan

---

## 📋 Detail Perubahan Teknis

### **1. Statistik Absensi**

#### **Controller Changes:**
- Parameter filter: `nik` → `search` (dengan backward compatibility)
- Tambah `karyawanList` untuk autocomplete
- Update logika filter untuk mendukung multi-term search
- Update debug info untuk menggunakan search

#### **View Changes:**
- Input NIK → 1 input "search" dengan autocomplete
- Tambah CSS untuk dropdown autocomplete
- Tambah JavaScript untuk autocomplete (debounce, keyboard navigation)
- Perbaikan alignment tombol Preview

### **2. Izin Keluar Komplek Kantor**

#### **Controller Changes:**
- Parameter filter: `nik` → `search` (dengan backward compatibility)
- Tambah `karyawanList` untuk autocomplete
- Update logika filter untuk mendukung multi-term search

#### **View Changes:**
- Input NIK → 1 input "search" dengan autocomplete
- Tambah CSS untuk dropdown autocomplete
- Tambah JavaScript untuk autocomplete
- Perbaikan alignment tombol Preview

### **3. Izin Tidak Masuk**

#### **Controller Changes:**
- Parameter filter: `nik` → `search` (dengan backward compatibility)
- Tambah `karyawanList` untuk autocomplete
- Update logika filter untuk mendukung multi-term search

#### **View Changes:**
- Input NIK → 1 input "search" dengan autocomplete
- Tambah CSS untuk dropdown autocomplete
- Tambah JavaScript untuk autocomplete
- Perbaikan alignment tombol Preview

---

## 📦 File yang Perlu Di-Upload

### **Controllers (3 files):**
1. `app/Http/Controllers/StatistikAbsensiController.php`
2. `app/Http/Controllers/IzinKeluarController.php`
3. `app/Http/Controllers/TidakMasukController.php`

### **Views (3 files):**
1. `resources/views/absen/statistik/index.blade.php`
2. `resources/views/absen/izin_keluar/index.blade.php`
3. `resources/views/absen/tidak_masuk/index.blade.php`

**Total: 6 files**

---

**Dokumen ini dibuat untuk memudahkan proses deployment update autocomplete search filter ke production server.**

**Tanggal:** 06 Februari 2026  
**Versi:** 1.0











