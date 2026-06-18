# 📋 Manual Deploy: Update Instruksi Kerja Lembur
**Tanggal:** 06 Februari 2026  
**Modul:** Instruksi Kerja Lembur  
**Target:** Production Server Ubuntu

---

## 📌 Ringkasan Perubahan

Update pada modul Instruksi Kerja Lembur dengan perubahan berikut:

### **Instruksi Kerja Lembur**
- ✅ Filter NIK diubah menjadi autocomplete search (NIK/Nama)
- ✅ Perbaikan alignment tombol Preview
- ✅ Mendukung multi-term search (pisahkan dengan koma)

---

## 📁 File yang Diubah

### **1. Controller**
- **File:** `app/Http/Controllers/InstruksiKerjaLemburController.php`
- **Status:** Diubah
- **Perubahan:**
  - Method `index()`: Filter NIK menjadi `search` dengan autocomplete
  - Tambah `karyawanList` untuk autocomplete
  - Update logika filter untuk mendukung search (multi-term)
  - Filter menggunakan relasi `karyawan` di `LemburDetail`

### **2. View**
- **File:** `resources/views/instruksi-kerja-lembur/index.blade.php`
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
mysqldump -u [username] -p [database_name] > backup_before_ikl_update_$(date +%Y%m%d_%H%M%S).sql
```

### **Step 2: Upload File ke Server**

#### **A. Upload Controller**
```bash
# Upload controller ke server
scp app/Http/Controllers/InstruksiKerjaLemburController.php user@server:/path/to/project/app/Http/Controllers/
```

#### **B. Upload View File**
```bash
# Upload view file
scp resources/views/instruksi-kerja-lembur/index.blade.php user@server:/path/to/project/resources/views/instruksi-kerja-lembur/
```

**ATAU** gunakan Git untuk push dan pull:

```bash
# Di local
git add .
git commit -m "Update autocomplete search filter untuk Instruksi Kerja Lembur"
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
chmod 644 app/Http/Controllers/InstruksiKerjaLemburController.php

# Set permission untuk view file
chmod 644 resources/views/instruksi-kerja-lembur/index.blade.php

# Set ownership (sesuaikan dengan user web server)
chown www-data:www-data app/Http/Controllers/InstruksiKerjaLemburController.php
chown www-data:www-data resources/views/instruksi-kerja-lembur/index.blade.php
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
ls -la app/Http/Controllers/InstruksiKerjaLemburController.php
ls -la resources/views/instruksi-kerja-lembur/index.blade.php
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

### **Test: Instruksi Kerja Lembur**

1. **Login ke aplikasi** dengan user yang memiliki permission

2. **Akses menu:**
   - Buka menu "Instruksi Kerja Lembur"

3. **Test Autocomplete Filter:**
   - ✅ Ketik NIK atau nama di field "NIK / Nama"
   - ✅ Verifikasi dropdown autocomplete muncul
   - ✅ Pilih dari dropdown atau gunakan keyboard navigation
   - ✅ Test multi-term search (pisahkan dengan koma)
   - ✅ Submit form dan verifikasi filter bekerja

4. **Test Alignment:**
   - ✅ Verifikasi tombol "Preview" sejajar dengan field lainnya

5. **Test Filter Data:**
   - ✅ Verifikasi data lembur ter-filter berdasarkan NIK/Nama karyawan
   - ✅ Verifikasi filter bekerja dengan relasi detail lembur

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
- Pastikan relasi `karyawan` ada di model `LemburDetail`

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
- [ ] Upload file controller InstruksiKerjaLemburController.php ke server
- [ ] Upload file view instruksi-kerja-lembur/index.blade.php ke server
- [ ] Set permission file dengan benar
- [ ] Clear Laravel cache (config, route, view)
- [ ] Regenerate autoload (jika perlu)
- [ ] Restart web server (jika perlu)
- [ ] Verifikasi file terdaftar
- [ ] Test autocomplete di Instruksi Kerja Lembur
- [ ] Test alignment tombol Preview
- [ ] Test filter data berdasarkan NIK/Nama
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
3. Verifikasi semua fitur berfungsi dengan baik
4. Dokumentasikan jika ada issue atau improvement yang diperlukan

---

## 📋 Detail Perubahan Teknis

### **Instruksi Kerja Lembur**

#### **Controller Changes:**
- Parameter filter: `nik` → `search` (dengan backward compatibility)
- Tambah `karyawanList` untuk autocomplete
- Update logika filter untuk mendukung multi-term search
- Filter menggunakan `whereHas` dengan relasi `karyawan` di `LemburDetail`

#### **View Changes:**
- Input NIK → 1 input "search" dengan autocomplete
- Tambah CSS untuk dropdown autocomplete
- Tambah JavaScript untuk autocomplete (debounce, keyboard navigation)
- Perbaikan alignment tombol Preview

---

## 📦 File yang Perlu Di-Upload

### **Controller (1 file):**
1. `app/Http/Controllers/InstruksiKerjaLemburController.php`

### **View (1 file):**
1. `resources/views/instruksi-kerja-lembur/index.blade.php`

**Total: 2 files**

---

## 🔍 Quick Reference: Files to Upload

```
Controller:
- app/Http/Controllers/InstruksiKerjaLemburController.php

View:
- resources/views/instruksi-kerja-lembur/index.blade.php
```

---

**Dokumen ini dibuat untuk memudahkan proses deployment update Instruksi Kerja Lembur ke production server.**

**Tanggal:** 06 Februari 2026  
**Versi:** 1.0











