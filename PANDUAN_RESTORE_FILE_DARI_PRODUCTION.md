# Panduan Restore File dari Server Production

**Tanggal:** 12 Januari 2026  
**Masalah:** File-file controller dan routes menjadi kosong karena proses Git commit yang gagal (system not responding)

---

## 📋 File yang Perlu di-Restore

### File Kritis (Harus di-restore):
1. **`routes/web.php`** ⚠️ **SANGAT KRITIS** - Tanpa file ini aplikasi tidak akan berjalan
2. **`app/Http/Controllers/AbsenController.php`** - Digunakan untuk browse absensi
3. **`app/Http/Controllers/ClosingController.php`** - Digunakan untuk closing gaji
4. **`app/Http/Controllers/AuthController.php`** - Digunakan untuk login/logout
5. **`app/Http/Controllers/BrowseTidakAbsenController.php`** - Digunakan untuk browse tidak absen
6. **`app/Http/Controllers/RekapBankController.php`** - Digunakan untuk rekap bank
7. **`app/Http/Controllers/ActivityLogController.php`** - Digunakan untuk activity logs

### File Opsional (Mungkin tidak digunakan):
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/DashboardBUController.php`
- `app/Http/Controllers/DashboardEmployeeController.php`
- `app/Http/Controllers/ListKaryawanAktifController.php`

---

## 🚀 Metode Restore dari Server Production

### Metode 1: Copy via SSH/SCP (Recommended)

**Langkah-langkah:**

1. **SSH ke server production:**
   ```bash
   ssh user@your-production-server
   ```

2. **Masuk ke folder aplikasi:**
   ```bash
   cd /path/to/hris-seven-payroll
   ```

3. **Copy file routes/web.php:**
   ```bash
   # Dari server production ke local (via SCP)
   scp user@production-server:/path/to/hris-seven-payroll/routes/web.php C:\xampp\htdocs\hris-seven-payroll\routes\web.php
   ```

4. **Copy semua controller yang kosong:**
   ```bash
   # Copy AbsenController
   scp user@production-server:/path/to/hris-seven-payroll/app/Http/Controllers/AbsenController.php C:\xampp\htdocs\hris-seven-payroll\app\Http\Controllers\AbsenController.php
   
   # Copy ClosingController
   scp user@production-server:/path/to/hris-seven-payroll/app/Http/Controllers/ClosingController.php C:\xampp\htdocs\hris-seven-payroll\app\Http\Controllers\ClosingController.php
   
   # Copy AuthController (jika belum ada)
   scp user@production-server:/path/to/hris-seven-payroll/app/Http/Controllers/AuthController.php C:\xampp\htdocs\hris-seven-payroll\app\Http\Controllers\AuthController.php
   
   # Copy BrowseTidakAbsenController
   scp user@production-server:/path/to/hris-seven-payroll/app/Http/Controllers/BrowseTidakAbsenController.php C:\xampp\htdocs\hris-seven-payroll\app\Http\Controllers\BrowseTidakAbsenController.php
   
   # Copy RekapBankController
   scp user@production-server:/path/to/hris-seven-payroll/app/Http/Controllers/RekapBankController.php C:\xampp\htdocs\hris-seven-payroll\app\Http\Controllers\RekapBankController.php
   
   # Copy ActivityLogController
   scp user@production-server:/path/to/hris-seven-payroll/app/Http/Controllers/ActivityLogController.php C:\xampp\htdocs\hris-seven-payroll\app\Http\Controllers\ActivityLogController.php
   ```

### Metode 2: Copy via FTP/SFTP Client

1. **Buka FTP/SFTP client** (FileZilla, WinSCP, dll)
2. **Connect ke server production**
3. **Download file-file berikut dari server:**
   - `routes/web.php`
   - `app/Http/Controllers/AbsenController.php`
   - `app/Http/Controllers/ClosingController.php`
   - `app/Http/Controllers/AuthController.php`
   - `app/Http/Controllers/BrowseTidakAbsenController.php`
   - `app/Http/Controllers/RekapBankController.php`
   - `app/Http/Controllers/ActivityLogController.php`
4. **Copy file-file tersebut ke folder lokal yang sesuai**

### Metode 3: Copy via File Manager (cPanel/Plesk)

1. **Login ke cPanel/Plesk**
2. **Buka File Manager**
3. **Navigate ke folder aplikasi**
4. **Download file-file yang diperlukan**
5. **Upload ke folder lokal yang sesuai**

---

## 📝 Checklist File yang Perlu di-Restore

Gunakan checklist ini untuk memastikan semua file sudah di-restore:

- [ ] `routes/web.php` ⚠️ **PRIORITAS TINGGI**
- [ ] `app/Http/Controllers/AbsenController.php`
- [ ] `app/Http/Controllers/ClosingController.php`
- [ ] `app/Http/Controllers/AuthController.php` (sudah ada, cek apakah lengkap)
- [ ] `app/Http/Controllers/BrowseTidakAbsenController.php`
- [ ] `app/Http/Controllers/RekapBankController.php`
- [ ] `app/Http/Controllers/ActivityLogController.php`
- [ ] `app/Http/Controllers/BagianController.php` (sudah ada, cek apakah lengkap)
- [ ] `app/Http/Controllers/DashboardController.php` (opsional)
- [ ] `app/Http/Controllers/DashboardBUController.php` (opsional)
- [ ] `app/Http/Controllers/DashboardEmployeeController.php` (opsional)
- [ ] `app/Http/Controllers/ListKaryawanAktifController.php` (opsional)

---

## ✅ Verifikasi Setelah Restore

Setelah file-file di-restore, lakukan verifikasi:

1. **Cek apakah file tidak kosong:**
   ```bash
   # Windows PowerShell
   Get-Content routes\web.php | Measure-Object -Line
   Get-Content app\Http\Controllers\AbsenController.php | Measure-Object -Line
   ```

2. **Test aplikasi:**
   - Buka aplikasi di browser
   - Test login
   - Test browse absensi
   - Test fitur-fitur utama

3. **Cek error log:**
   ```bash
   # Cek Laravel log
   tail -f storage/logs/laravel.log
   ```

---

## 🔄 Setelah Restore - Commit ke Git

Setelah semua file di-restore dan aplikasi berjalan normal:

1. **Check status:**
   ```bash
   git status
   ```

2. **Add file yang di-restore:**
   ```bash
   git add routes/web.php
   git add app/Http/Controllers/AbsenController.php
   git add app/Http/Controllers/ClosingController.php
   git add app/Http/Controllers/BrowseTidakAbsenController.php
   git add app/Http/Controllers/RekapBankController.php
   git add app/Http/Controllers/ActivityLogController.php
   ```

3. **Commit:**
   ```bash
   git commit -m "Restore file yang kosong dari server production (fix failed git commit)"
   ```

4. **Push ke GitHub:**
   ```bash
   git push origin main
   ```

---

## ⚠️ Catatan Penting

1. **Backup dulu** - Sebelum restore, backup file lokal yang ada (jika ada)
2. **Jangan overwrite file yang sudah benar** - Pastikan file yang akan di-restore memang kosong
3. **Cek perbedaan** - Jika file lokal sudah ada isinya (meskipun tidak lengkap), bandingkan dengan file production
4. **Test setelah restore** - Pastikan aplikasi berjalan normal setelah restore

---

## 🐛 Troubleshooting

### Problem: File masih kosong setelah copy
**Solusi:**
- Pastikan file di server production tidak kosong
- Cek permission file
- Cek encoding file (harus UTF-8)

### Problem: Aplikasi error setelah restore
**Solusi:**
- Cek Laravel log: `storage/logs/laravel.log`
- Clear cache: `php artisan cache:clear`
- Clear config cache: `php artisan config:clear`
- Clear route cache: `php artisan route:clear`

### Problem: Git conflict setelah restore
**Solusi:**
- Jika ada conflict, resolve manual
- Atau gunakan: `git checkout --theirs routes/web.php` (hati-hati!)

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0


