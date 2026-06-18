# 📦 Panduan Deployment: Fitur Baru Jadwal Shift Satpam

## 🎯 Ringkasan
Dokumentasi ini berisi langkah-langkah manual untuk mengupdate aplikasi HRIS Seven Payroll di server Ubuntu dengan **3 fitur baru**:
1. Excel/CSV Import untuk jadwal shift
2. Copy jadwal bulan sebelumnya
3. Report jadwal shift per periode

---

## 📋 LANGKAH 1: Backup (OPSIONAL)

Karena tidak ada perubahan database, backup tidak wajib. Tapi jika ingin aman:

```bash
# Login ke server Ubuntu
ssh user@192.168.10.40

# Backup database (opsional)
mysqldump -u root -p hris_seven > backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql
```

---

## 📋 LANGKAH 2: Copy File ke Server

### A. File yang HARUS di-copy (dari local ke server):

#### 1. **Controller** (1 file - DIMODIFIKASI)
```
app/Http/Controllers/JadwalShiftSecurityController.php
```

**Lokasi di server:**
```
/var/www/html/hris-seven-payroll/app/Http/Controllers/JadwalShiftSecurityController.php
```

**Catatan:** File ini sudah ada, **OVERWRITE** dengan versi baru (ada 3 method baru: `importExcel`, `copyFromPreviousMonth`, `report`, `exportReport`)

#### 2. **Views** (2 file)
```
resources/views/jadwal-shift-security/index.blade.php (DIMODIFIKASI)
resources/views/jadwal-shift-security/report.blade.php (FILE BARU)
```

**Lokasi di server:**
```
/var/www/html/hris-seven-payroll/resources/views/jadwal-shift-security/index.blade.php
/var/www/html/hris-seven-payroll/resources/views/jadwal-shift-security/report.blade.php
```

**Catatan:** 
- `index.blade.php` sudah ada, **OVERWRITE** dengan versi baru
- `report.blade.php` adalah file baru, pastikan folder `jadwal-shift-security` sudah ada

#### 3. **Routes** (1 file - DIMODIFIKASI)
```
routes/web.php
```

**Lokasi di server:**
```
/var/www/html/hris-seven-payroll/routes/web.php
```

**Catatan:** File ini sudah ada, **OVERWRITE** dengan versi baru (ada 3 route baru)

#### 4. **Layout** (1 file - DIMODIFIKASI)
```
resources/views/layouts/app.blade.php
```

**Lokasi di server:**
```
/var/www/html/hris-seven-payroll/resources/views/layouts/app.blade.php
```

**Catatan:** File ini sudah ada, **OVERWRITE** dengan versi baru (menu "Report Jadwal Shift" ditambahkan)

#### 5. **Template CSV** (1 file - FILE BARU)
```
public/template_jadwal_shift_security.csv
```

**Lokasi di server:**
```
/var/www/html/hris-seven-payroll/public/template_jadwal_shift_security.csv
```

**Catatan:** File baru, copy ke folder `public/`

---

## 📋 LANGKAH 3: Set Permissions

Setelah copy file, set permissions yang benar:

```bash
# Set ownership ke www-data
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Http/Controllers/JadwalShiftSecurityController.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/resources/views/jadwal-shift-security/
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/routes/web.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/resources/views/layouts/app.blade.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/public/template_jadwal_shift_security.csv

# Set permissions
sudo chmod -R 755 /var/www/html/hris-seven-payroll/app/
sudo chmod -R 755 /var/www/html/hris-seven-payroll/resources/
sudo chmod -R 755 /var/www/html/hris-seven-payroll/routes/
sudo chmod -R 755 /var/www/html/hris-seven-payroll/public/template_jadwal_shift_security.csv
```

---

## 📋 LANGKAH 4: Clear Cache Laravel

**PENTING:** Wajib clear cache setelah update!

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

## 📋 LANGKAH 5: Verifikasi

### A. Cek File

```bash
# Cek apakah semua file sudah ada
ls -la /var/www/html/hris-seven-payroll/app/Http/Controllers/JadwalShiftSecurityController.php
ls -la /var/www/html/hris-seven-payroll/resources/views/jadwal-shift-security/index.blade.php
ls -la /var/www/html/hris-seven-payroll/resources/views/jadwal-shift-security/report.blade.php
ls -la /var/www/html/hris-seven-payroll/routes/web.php
ls -la /var/www/html/hris-seven-payroll/public/template_jadwal_shift_security.csv
```

### B. Cek Route

```bash
# Verifikasi route baru sudah terdaftar
sudo -u www-data php artisan route:list --name=jadwal-shift-security
```

**Expected output:**
```
jadwal-shift-security.index
jadwal-shift-security.store
jadwal-shift-security.override
jadwal-shift-security.get-jadwal
jadwal-shift-security.copy-previous-month  ← BARU
jadwal-shift-security.import                ← BARU
jadwal-shift-security.report                ← BARU
```

### C. Test Aplikasi

1. **Login ke aplikasi:** `http://hr.abncorp.lan` atau `http://192.168.10.40`

2. **Cek menu baru:**
   - Menu "Absensi" → "Report Jadwal Shift" (harus muncul)

3. **Test fitur:**
   - **Import Excel/CSV:**
     - Buka halaman "Jadwal Shift Satpam"
     - Klik tombol "Import Excel/CSV" (hijau)
     - Modal harus muncul
   
   - **Copy Bulan Sebelumnya:**
     - Buka halaman "Jadwal Shift Satpam"
     - Klik tombol "Copy Bulan Sebelumnya" (biru)
     - Harus ada konfirmasi
   
   - **Report:**
     - Buka menu "Report Jadwal Shift"
     - Filter periode harus berfungsi
     - Tombol "Export CSV" harus muncul

---

## 📋 CHECKLIST DEPLOYMENT

Gunakan checklist ini untuk memastikan semua langkah sudah dilakukan:

- [ ] **Copy Controller** (1 file) sudah di-copy
- [ ] **Copy Views** (2 file) sudah di-copy
- [ ] **Copy Routes** (1 file) sudah di-copy
- [ ] **Copy Layout** (1 file) sudah di-copy
- [ ] **Copy Template CSV** (1 file) sudah di-copy
- [ ] **Set permissions** sudah dilakukan
- [ ] **Clear cache Laravel** sudah dilakukan
- [ ] **Verifikasi file** sudah dicek
- [ ] **Verifikasi route** sudah dicek
- [ ] **Test aplikasi** sudah dilakukan

---

## 🐛 Troubleshooting

### Error: Route not found
**Solusi:** Pastikan `routes/web.php` sudah di-update. Clear route cache:
```bash
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan route:cache
```

### Error: Method not found
**Solusi:** Pastikan `JadwalShiftSecurityController.php` sudah di-update dengan method baru. Clear cache:
```bash
sudo -u www-data php artisan optimize:clear
```

### Error: View not found
**Solusi:** Pastikan `report.blade.php` sudah di-copy. Clear view cache:
```bash
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan view:cache
```

### Error: Template CSV tidak ditemukan
**Solusi:** Pastikan file `template_jadwal_shift_security.csv` sudah di-copy ke folder `public/`

### Tombol Import/Copy tidak muncul
**Solusi:** Pastikan `index.blade.php` sudah di-update. Clear view cache:
```bash
sudo -u www-data php artisan view:clear
```

### Menu Report tidak muncul
**Solusi:** Pastikan `app.blade.php` sudah di-update. Clear view cache:
```bash
sudo -u www-data php artisan view:clear
```

---

## 📝 Catatan Penting

1. **Tidak ada perubahan database** - Tidak perlu run migration atau SQL
2. **File yang di-overwrite** - Pastikan backup file lama jika perlu
3. **Permissions** - Harus di-set ke `www-data:www-data`
4. **Cache Laravel** - **WAJIB** di-clear setelah update
5. **Route cache** - Harus di-rebuild setelah update routes

---

## 📊 Ringkasan File yang Di-copy

| No | File | Status | Lokasi Server |
|----|------|--------|---------------|
| 1 | `JadwalShiftSecurityController.php` | MODIFIKASI | `app/Http/Controllers/` |
| 2 | `index.blade.php` | MODIFIKASI | `resources/views/jadwal-shift-security/` |
| 3 | `report.blade.php` | BARU | `resources/views/jadwal-shift-security/` |
| 4 | `web.php` | MODIFIKASI | `routes/` |
| 5 | `app.blade.php` | MODIFIKASI | `resources/views/layouts/` |
| 6 | `template_jadwal_shift_security.csv` | BARU | `public/` |

**Total:** 6 file (4 modifikasi, 2 baru)

---

## 🚀 Quick Deploy Commands

Jika semua file sudah di-copy, jalankan perintah ini sekaligus:

```bash
cd /var/www/html/hris-seven-payroll

# Set permissions
sudo chown -R www-data:www-data app/Http/Controllers/JadwalShiftSecurityController.php
sudo chown -R www-data:www-data resources/views/jadwal-shift-security/
sudo chown -R www-data:www-data routes/web.php
sudo chown -R www-data:www-data resources/views/layouts/app.blade.php
sudo chown -R www-data:www-data public/template_jadwal_shift_security.csv

# Clear & rebuild cache
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Verifikasi
sudo -u www-data php artisan route:list --name=jadwal-shift-security
```

---

## 📞 Support

Jika ada masalah saat deployment, cek:
1. Log Laravel: `storage/logs/laravel.log`
2. Log Apache: `/var/log/apache2/error.log`
3. Pastikan semua file sudah di-copy dengan benar
4. Pastikan permissions sudah benar
5. Pastikan cache sudah di-clear

---

**Status:** ✅ Siap untuk deployment

**Tanggal:** 2 Desember 2025

**Catatan:** Tidak ada perubahan database, hanya update file aplikasi.













