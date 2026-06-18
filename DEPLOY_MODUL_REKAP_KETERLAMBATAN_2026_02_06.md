# 📋 Manual Deploy: Modul Rekap Absensi Keterlambatan

**Tanggal:** 06 Februari 2026  
**Modul:** Rekap Absensi Keterlambatan  
**Target:** Production Server Ubuntu

---

## 📌 Ringkasan Modul

Modul **Rekap Absensi Keterlambatan** adalah modul baru untuk menampilkan rekap keterlambatan karyawan dengan fitur:
- Filter hierarki: Divisi → Departemen → Bagian
- Filter tanggal, NIK/Nama dengan autocomplete
- Expandable row untuk detail tanggal keterlambatan
- Halaman preview cetak dengan format portrait
- Perhitungan konsisten: telat 1 menit sudah dianggap telat

---

## 📁 File yang Ditambahkan/Diubah

### **1. Controller Baru**
- **File:** `app/Http/Controllers/RekapKeterlambatanController.php`
- **Status:** File baru
- **Fungsi:** 
  - Method `index()`: Menampilkan halaman utama dengan filter dan data rekap
  - Method `preview()`: Menampilkan halaman preview untuk cetak
  - Method `getDepartemensByDivisi()`: API untuk filter hierarki Departemen
  - Method `getBagiansByDepartemen()`: API untuk filter hierarki Bagian

### **2. View Baru**
- **File:** `resources/views/absen/rekap-keterlambatan/index.blade.php`
- **Status:** File baru
- **Fungsi:** Halaman utama dengan filter, tabel data, dan expandable detail

- **File:** `resources/views/absen/rekap-keterlambatan/preview.blade.php`
- **Status:** File baru
- **Fungsi:** Halaman preview khusus untuk cetak/print

### **3. Route Baru**
- **File:** `routes/web.php`
- **Status:** Diubah (menambahkan route baru)
- **Route yang ditambahkan:**
  ```php
  Route::get('absensi/rekap-keterlambatan', [RekapKeterlambatanController::class, 'index'])->name('rekap-keterlambatan.index');
  Route::get('absensi/rekap-keterlambatan/preview', [RekapKeterlambatanController::class, 'preview'])->name('rekap-keterlambatan.preview');
  Route::get('absensi/rekap-keterlambatan/get-departemens', [RekapKeterlambatanController::class, 'getDepartemensByDivisi'])->name('rekap-keterlambatan.get-departemens');
  Route::get('absensi/rekap-keterlambatan/get-bagians', [RekapKeterlambatanController::class, 'getBagiansByDepartemen'])->name('rekap-keterlambatan.get-bagians');
  ```

### **4. Sidebar Menu**
- **File:** `resources/views/layouts/app.blade.php`
- **Status:** Diubah (menambahkan menu item)
- **Lokasi:** Di grup menu "Laporan", setelah "Rekapitulasi Absen All"

---

## 🗄️ Database Changes

**TIDAK ADA PERUBAHAN DATABASE**  
Modul ini menggunakan tabel yang sudah ada:
- `t_absen` - untuk data absensi
- `m_karyawan` - untuk data karyawan
- `m_divisi` - untuk data divisi
- `m_dept` - untuk data departemen
- `m_bagian` - untuk data bagian
- `m_shift` - untuk data shift

---

## 🚀 Langkah-Langkah Deployment

### **Step 1: Backup Database (Opsional tapi Disarankan)**

```bash
# Backup database sebelum deploy
mysqldump -u [username] -p [database_name] > backup_before_rekap_keterlambatan_$(date +%Y%m%d_%H%M%S).sql
```

### **Step 2: Upload File ke Server**

#### **A. Upload Controller**
```bash
# Upload controller ke server
scp app/Http/Controllers/RekapKeterlambatanController.php user@server:/path/to/project/app/Http/Controllers/
```

#### **B. Upload View Files**
```bash
# Buat directory jika belum ada
ssh user@server "mkdir -p /path/to/project/resources/views/absen/rekap-keterlambatan"

# Upload view files
scp resources/views/absen/rekap-keterlambatan/index.blade.php user@server:/path/to/project/resources/views/absen/rekap-keterlambatan/
scp resources/views/absen/rekap-keterlambatan/preview.blade.php user@server:/path/to/project/resources/views/absen/rekap-keterlambatan/
```

#### **C. Upload Route dan Layout**
```bash
# Upload routes/web.php (atau edit langsung di server)
scp routes/web.php user@server:/path/to/project/routes/

# Upload layouts/app.blade.php (atau edit langsung di server)
scp resources/views/layouts/app.blade.php user@server:/path/to/project/resources/views/layouts/
```

**ATAU** gunakan Git untuk push dan pull:

```bash
# Di local
git add .
git commit -m "Add modul Rekap Absensi Keterlambatan"
git push origin main

# Di server
cd /path/to/project
git pull origin main
```

### **Step 3: Set Permission File**

```bash
# Set permission untuk file yang diupload
cd /path/to/project

# Set permission untuk controller
chmod 644 app/Http/Controllers/RekapKeterlambatanController.php

# Set permission untuk view files
chmod 644 resources/views/absen/rekap-keterlambatan/index.blade.php
chmod 644 resources/views/absen/rekap-keterlambatan/preview.blade.php

# Set permission untuk routes dan layout
chmod 644 routes/web.php
chmod 644 resources/views/layouts/app.blade.php

# Set ownership (sesuaikan dengan user web server)
chown www-data:www-data app/Http/Controllers/RekapKeterlambatanController.php
chown -R www-data:www-data resources/views/absen/rekap-keterlambatan/
chown www-data:www-data routes/web.php
chown www-data:www-data resources/views/layouts/app.blade.php
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

#### **A. Cek Route**
```bash
cd /path/to/project
php artisan route:list | grep rekap-keterlambatan
```

**Expected output:**
```
GET|HEAD  absensi/rekap-keterlambatan ................ rekap-keterlambatan.index
GET|HEAD  absensi/rekap-keterlambatan/preview ......... rekap-keterlambatan.preview
GET|HEAD  absensi/rekap-keterlambatan/get-departemens . rekap-keterlambatan.get-departemens
GET|HEAD  absensi/rekap-keterlambatan/get-bagians ..... rekap-keterlambatan.get-bagians
```

#### **B. Cek File Permission**
```bash
# Cek apakah file ada dan permission benar
ls -la app/Http/Controllers/RekapKeterlambatanController.php
ls -la resources/views/absen/rekap-keterlambatan/
```

#### **C. Cek Log untuk Error**
```bash
# Cek Laravel log
tail -f storage/logs/laravel.log

# Cek web server error log
tail -f /var/log/nginx/error.log
# atau
tail -f /var/log/apache2/error.log
```

### **Step 8: Testing di Production**

1. **Login ke aplikasi** dengan user yang memiliki permission `view-absensi` atau `view-statistik-absensi`

2. **Akses menu:**
   - Buka sidebar menu "Laporan"
   - Cari menu "Rekap Absensi Keterlambatan" (harus ada di bawah "Rekapitulasi Absen All")

3. **Test fitur:**
   - ✅ Filter tanggal (dari tanggal - sampai tanggal)
   - ✅ Filter hierarki: Divisi → Departemen → Bagian
   - ✅ Filter NIK/Nama dengan autocomplete
   - ✅ Tampilkan data rekap keterlambatan
   - ✅ Expand detail tanggal keterlambatan
   - ✅ Tombol cetak membuka preview
   - ✅ Preview dan cetak berfungsi dengan baik

4. **Test API endpoint (opsional):**
   ```bash
   # Test get departemens
   curl "https://your-domain.com/absensi/rekap-keterlambatan/get-departemens?divisi=DIV001"
   
   # Test get bagians
   curl "https://your-domain.com/absensi/rekap-keterlambatan/get-bagians?departemen=DEPT001&divisi=DIV001"
   ```

---

## 🔐 Permission yang Diperlukan

User harus memiliki salah satu permission berikut untuk mengakses modul ini:
- `view-absensi`
- `view-statistik-absensi`

**Note:** Permission ini sudah ada di sistem, tidak perlu membuat permission baru.

---

## ⚠️ Troubleshooting

### **Problem 1: Route tidak ditemukan**
**Solution:**
```bash
php artisan route:clear
php artisan route:cache
```

### **Problem 2: Class not found**
**Solution:**
```bash
composer dump-autoload
php artisan clear-compiled
```

### **Problem 3: View tidak ditemukan**
**Solution:**
```bash
php artisan view:clear
# Pastikan directory dan file ada dengan permission yang benar
ls -la resources/views/absen/rekap-keterlambatan/
```

### **Problem 4: Menu tidak muncul**
**Solution:**
- Pastikan user memiliki permission `view-absensi` atau `view-statistik-absensi`
- Clear cache: `php artisan cache:clear`
- Cek file `resources/views/layouts/app.blade.php` sudah ter-update

### **Problem 5: Filter hierarki tidak berfungsi**
**Solution:**
- Cek route API sudah terdaftar: `php artisan route:list | grep rekap-keterlambatan`
- Cek browser console untuk error JavaScript
- Pastikan file `index.blade.php` sudah ter-upload dengan lengkap

### **Problem 6: Preview/Cetak tidak muncul**
**Solution:**
- Pastikan file `preview.blade.php` sudah ter-upload
- Cek route `rekap-keterlambatan.preview` sudah terdaftar
- Clear view cache: `php artisan view:clear`

---

## 📝 Checklist Deployment

- [ ] Backup database (opsional)
- [ ] Upload file controller ke server
- [ ] Upload file view ke server
- [ ] Update routes/web.php
- [ ] Update layouts/app.blade.php
- [ ] Set permission file dengan benar
- [ ] Clear Laravel cache (config, route, view)
- [ ] Regenerate autoload (jika perlu)
- [ ] Restart web server (jika perlu)
- [ ] Verifikasi route terdaftar
- [ ] Test akses menu di browser
- [ ] Test semua fitur modul
- [ ] Test preview dan cetak
- [ ] Cek log untuk error

---

## 📞 Support

Jika ada masalah saat deployment, cek:
1. Laravel log: `storage/logs/laravel.log`
2. Web server error log
3. Browser console untuk error JavaScript
4. Network tab di browser untuk error API

---

## ✅ Post-Deployment

Setelah deployment berhasil:
1. Monitor aplikasi untuk beberapa hari
2. Cek performa query (jika data banyak, mungkin perlu optimasi)
3. Kumpulkan feedback dari user
4. Dokumentasikan jika ada issue atau improvement yang diperlukan

---

**Dokumen ini dibuat untuk memudahkan proses deployment modul Rekap Absensi Keterlambatan ke production server.**













