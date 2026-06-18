# 📋 Manual Deploy: Update Modul Absensi
**Tanggal:** 06 Februari 2026  
**Modul:** Input/Edit Absensi & Browse Tidak Absen (Alpha)  
**Target:** Production Server Ubuntu

---

## 📌 Ringkasan Perubahan

Update pada 2 modul absensi dengan perubahan berikut:

### **1. Input/Edit Absensi Karyawan Per Periode**
- ✅ Filter NIK/Nama diubah menjadi autocomplete (sama seperti Browse Absensi)
- ✅ Tambah tombol Delete dengan authorization (hanya superadmin/administrator)

### **2. Browse Tidak Absen (Alpha)**
- ✅ Filter NIK/Nama diubah menjadi autocomplete (sama seperti Browse Absensi)
- ✅ Exclude karyawan dengan Group_pegawai = 'Management'
- ✅ Hapus kolom Shift Terjadwal dan Shift Aktual
- ✅ Tambah kolom Group Pegawai
- ✅ Tambah kolom Departemen (antara Divisi dan Bagian)

---

## 📁 File yang Diubah

### **1. Controller**
- **File:** `app/Http/Controllers/EditAbsensiController.php`
- **Status:** Diubah
- **Perubahan:**
  - Method `index()`: Filter NIK/Nama menjadi `search` dengan autocomplete
  - Method `destroy()`: Method baru untuk delete absensi (hanya superadmin/administrator)

- **File:** `app/Http/Controllers/BrowseTidakAbsenController.php`
- **Status:** Diubah
- **Perubahan:**
  - Method `index()`: Filter NIK/Nama menjadi `search` dengan autocomplete
  - Exclude Group_pegawai = 'Management' dari semua query
  - Tambah data `vcNamaDepartemen` ke response

### **2. View**
- **File:** `resources/views/edit-absensi/index.blade.php`
- **Status:** Diubah
- **Perubahan:**
  - Input NIK dan Nama digabung menjadi 1 input "search" dengan autocomplete
  - Tambah tombol Delete dengan authorization check
  - Tambah CSS dan JavaScript untuk autocomplete

- **File:** `resources/views/absen/tidak-absen/index.blade.php`
- **Status:** Diubah
- **Perubahan:**
  - Input NIK dan Nama digabung menjadi 1 input "search" dengan autocomplete
  - Hapus kolom Shift Terjadwal dan Shift Aktual
  - Tambah kolom Departemen (antara Divisi dan Bagian)
  - Tambah kolom Group Pegawai
  - Tambah CSS dan JavaScript untuk autocomplete
  - Update pesan alert untuk menjelaskan exclude Management

### **3. Route**
- **File:** `routes/web.php`
- **Status:** Diubah
- **Perubahan:**
  - Tambah route `DELETE edit-absensi/delete` untuk delete absensi

---

## 🗄️ Database Changes

**TIDAK ADA PERUBAHAN DATABASE**  
Semua perubahan hanya pada aplikasi (controller, view, route).

---

## 🚀 Langkah-Langkah Deployment

### **Step 1: Backup Database (Opsional tapi Disarankan)**

```bash
# Backup database sebelum deploy
mysqldump -u [username] -p [database_name] > backup_before_absensi_update_$(date +%Y%m%d_%H%M%S).sql
```

### **Step 2: Upload File ke Server**

#### **A. Upload Controller**
```bash
# Upload controller ke server
scp app/Http/Controllers/EditAbsensiController.php user@server:/path/to/project/app/Http/Controllers/
scp app/Http/Controllers/BrowseTidakAbsenController.php user@server:/path/to/project/app/Http/Controllers/
```

#### **B. Upload View Files**
```bash
# Upload view files
scp resources/views/edit-absensi/index.blade.php user@server:/path/to/project/resources/views/edit-absensi/
scp resources/views/absen/tidak-absen/index.blade.php user@server:/path/to/project/resources/views/absen/tidak-absen/
```

#### **C. Upload Route**
```bash
# Upload routes/web.php (atau edit langsung di server)
scp routes/web.php user@server:/path/to/project/routes/
```

**ATAU** gunakan Git untuk push dan pull:

```bash
# Di local
git add .
git commit -m "Update modul Input/Edit Absensi dan Browse Tidak Absen (Alpha)"
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
chmod 644 app/Http/Controllers/EditAbsensiController.php
chmod 644 app/Http/Controllers/BrowseTidakAbsenController.php

# Set permission untuk view files
chmod 644 resources/views/edit-absensi/index.blade.php
chmod 644 resources/views/absen/tidak-absen/index.blade.php

# Set permission untuk routes
chmod 644 routes/web.php

# Set ownership (sesuaikan dengan user web server)
chown www-data:www-data app/Http/Controllers/EditAbsensiController.php
chown www-data:www-data app/Http/Controllers/BrowseTidakAbsenController.php
chown www-data:www-data resources/views/edit-absensi/index.blade.php
chown www-data:www-data resources/views/absen/tidak-absen/index.blade.php
chown www-data:www-data routes/web.php
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
php artisan route:list | grep edit-absensi
php artisan route:list | grep browse-tidak-absen
```

**Expected output untuk edit-absensi:**
```
GET|HEAD  edit-absensi ................... edit-absensi.index
GET|HEAD  edit-absensi/create ........... edit-absensi.create
POST      edit-absensi/store ............ edit-absensi.store
GET|HEAD  edit-absensi/edit ............. edit-absensi.edit
POST      edit-absensi/update ........... edit-absensi.update
DELETE    edit-absensi/delete ........... edit-absensi.destroy
```

**Expected output untuk browse-tidak-absen:**
```
GET|HEAD  browse-tidak-absen ............ browse-tidak-absen.index
```

#### **B. Cek File Permission**
```bash
# Cek apakah file ada dan permission benar
ls -la app/Http/Controllers/EditAbsensiController.php
ls -la app/Http/Controllers/BrowseTidakAbsenController.php
ls -la resources/views/edit-absensi/index.blade.php
ls -la resources/views/absen/tidak-absen/index.blade.php
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

---

## 🧪 Testing di Production

### **Test 1: Input/Edit Absensi Karyawan Per Periode**

1. **Login ke aplikasi** dengan user yang memiliki permission `view-edit-absensi`

2. **Akses menu:**
   - Buka menu "Input/Edit Absensi Karyawan Per Periode"

3. **Test Autocomplete Filter:**
   - ✅ Ketik NIK atau nama di field "NIK / Nama"
   - ✅ Verifikasi dropdown autocomplete muncul
   - ✅ Pilih dari dropdown atau gunakan keyboard navigation
   - ✅ Test multi-term search (pisahkan dengan koma)
   - ✅ Submit form dan verifikasi filter bekerja

4. **Test Tombol Delete (Superadmin/Administrator):**
   - ✅ Login sebagai user dengan role superadmin atau administrator
   - ✅ Verifikasi tombol "Hapus" muncul di kolom Aksi
   - ✅ Klik tombol "Hapus" dan verifikasi konfirmasi muncul
   - ✅ Konfirmasi delete dan verifikasi data terhapus
   - ✅ Login sebagai user dengan role lain (misalnya HR)
   - ✅ Verifikasi tombol "Hapus" TIDAK muncul

### **Test 2: Browse Tidak Absen (Alpha)**

1. **Login ke aplikasi** dengan user yang memiliki permission `view-browse-tidak-absen`

2. **Akses menu:**
   - Buka menu "Browse Tidak Absen (Alpha)"

3. **Test Autocomplete Filter:**
   - ✅ Ketik NIK atau nama di field "NIK / Nama"
   - ✅ Verifikasi dropdown autocomplete muncul
   - ✅ Pilih dari dropdown atau gunakan keyboard navigation
   - ✅ Test multi-term search (pisahkan dengan koma)
   - ✅ Submit form dan verifikasi filter bekerja

4. **Test Exclude Management:**
   - ✅ Verifikasi karyawan dengan Group_pegawai = 'Management' tidak muncul di autocomplete
   - ✅ Verifikasi karyawan dengan Group_pegawai = 'Management' tidak muncul di hasil data Alpha
   - ✅ Verifikasi "Management" tidak muncul di dropdown Group

5. **Test Kolom Tabel:**
   - ✅ Verifikasi kolom "Shift Terjadwal" dan "Shift Aktual" TIDAK ada
   - ✅ Verifikasi kolom "Departemen" muncul antara "Divisi" dan "Bagian"
   - ✅ Verifikasi kolom "Group Pegawai" muncul setelah "Bagian"
   - ✅ Verifikasi data Departemen dan Group Pegawai terisi dengan benar

---

## ⚠️ Troubleshooting

### **Problem 1: Route tidak ditemukan**
**Solution:**
```bash
php artisan route:clear
php artisan route:cache
```

### **Problem 2: Autocomplete tidak muncul**
**Solution:**
- Cek browser console untuk error JavaScript
- Pastikan file view sudah ter-upload dengan lengkap
- Clear view cache: `php artisan view:clear`
- Pastikan `karyawanList` variable ada di view (cek di source HTML)

### **Problem 3: Tombol Delete tidak muncul**
**Solution:**
- Pastikan user login dengan role superadmin atau administrator
- Cek apakah method `hasAnyRole()` bekerja dengan benar
- Cek role user di database: `SELECT * FROM user_role WHERE user_id = [user_id]`

### **Problem 4: Data Management masih muncul**
**Solution:**
- Pastikan controller sudah ter-update dengan filter `where('Group_pegawai', '!=', 'Management')`
- Clear cache: `php artisan cache:clear`
- Cek query di controller apakah sudah benar

### **Problem 5: Kolom Departemen kosong**
**Solution:**
- Pastikan relasi `departemen` sudah ada di model Karyawan
- Cek apakah data departemen ada di database untuk karyawan tersebut
- Pastikan controller menggunakan `with(['departemen'])` untuk eager loading

### **Problem 6: Error saat delete absensi**
**Solution:**
- Pastikan user memiliki role superadmin atau administrator
- Cek Laravel log untuk detail error
- Pastikan route DELETE sudah terdaftar: `php artisan route:list | grep edit-absensi.destroy`
- Pastikan method spoofing `@method('DELETE')` ada di form

---

## 📝 Checklist Deployment

- [ ] Backup database (opsional)
- [ ] Upload file controller EditAbsensiController.php ke server
- [ ] Upload file controller BrowseTidakAbsenController.php ke server
- [ ] Upload file view edit-absensi/index.blade.php ke server
- [ ] Upload file view absen/tidak-absen/index.blade.php ke server
- [ ] Update routes/web.php
- [ ] Set permission file dengan benar
- [ ] Clear Laravel cache (config, route, view)
- [ ] Regenerate autoload (jika perlu)
- [ ] Restart web server (jika perlu)
- [ ] Verifikasi route terdaftar
- [ ] Test autocomplete di Input/Edit Absensi
- [ ] Test tombol delete di Input/Edit Absensi (superadmin/administrator)
- [ ] Test autocomplete di Browse Tidak Absen
- [ ] Test exclude Management di Browse Tidak Absen
- [ ] Test kolom tabel di Browse Tidak Absen
- [ ] Cek log untuk error

---

## 📞 Support

Jika ada masalah saat deployment, cek:
1. Laravel log: `storage/logs/laravel.log`
2. Web server error log
3. Browser console untuk error JavaScript
4. Network tab di browser untuk error API
5. Database untuk memastikan data relasi (departemen) ada

---

## ✅ Post-Deployment

Setelah deployment berhasil:
1. Monitor aplikasi untuk beberapa hari
2. Kumpulkan feedback dari user tentang fitur autocomplete
3. Verifikasi tombol delete hanya muncul untuk user yang berwenang
4. Dokumentasikan jika ada issue atau improvement yang diperlukan

---

## 📋 Detail Perubahan Teknis

### **1. Input/Edit Absensi Karyawan Per Periode**

#### **Controller Changes:**
- Parameter filter: `nik` dan `nama` → `search` (dengan backward compatibility)
- Tambah `karyawanList` untuk autocomplete
- Tambah method `destroy()` dengan authorization check

#### **View Changes:**
- 2 input (NIK dan Nama) → 1 input "search" dengan autocomplete
- Tambah CSS untuk dropdown autocomplete
- Tambah JavaScript untuk autocomplete (debounce, keyboard navigation)
- Tambah tombol Delete dengan form dan konfirmasi

#### **Route Changes:**
- Tambah route: `DELETE edit-absensi/delete` → `edit-absensi.destroy`

### **2. Browse Tidak Absen (Alpha)**

#### **Controller Changes:**
- Parameter filter: `nik` dan `nama` → `search` (dengan backward compatibility)
- Tambah `karyawanList` untuk autocomplete
- Exclude `Group_pegawai = 'Management'` dari semua query
- Tambah `vcNamaDepartemen` ke response data

#### **View Changes:**
- 2 input (NIK dan Nama) → 1 input "search" dengan autocomplete
- Hapus kolom "Shift Terjadwal" dan "Shift Aktual"
- Tambah kolom "Departemen" (antara Divisi dan Bagian)
- Tambah kolom "Group Pegawai" (setelah Bagian)
- Tambah CSS untuk dropdown autocomplete
- Tambah JavaScript untuk autocomplete
- Update pesan alert untuk menjelaskan exclude Management

---

**Dokumen ini dibuat untuk memudahkan proses deployment update modul absensi ke production server.**

**Tanggal:** 06 Februari 2026  
**Versi:** 1.0











