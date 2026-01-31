# Panduan Restore Aplikasi ke Posisi 31 Januari 2026 (Lengkap)

**Tanggal:** 12 Januari 2026  
**Tujuan:** Restore aplikasi ke posisi terakhir tanggal 31 Januari 2026 yang sudah full dan lengkap

---

## 🎯 Tujuan

Restore aplikasi lokal ke kondisi **sama persis** dengan production server tanggal **31 Januari 2026** yang sudah full dan lengkap, semua aplikasi jalan.

---

## ⚠️ PENJELASAN: Kenapa Aplikasi Lokal Jadi Tidak Lengkap Setelah Push ke GitHub?

### Penyebab Utama:

1. **Git Filter-Branch Rewrite History**
   - Saat menghapus file SQL backup, `git filter-branch` melakukan **rewrite seluruh history Git**
   - Semua commit di-rewrite dengan SHA hash baru
   - Commit yang sudah ada di GitHub (dengan SHA hash lama) menjadi **orphan** (tidak terhubung)
   - File-file yang belum ter-commit sebelum filter-branch **hilang**

2. **Force Push Menghapus Commit di GitHub**
   - Force push **menimpa** semua commit yang ada di GitHub
   - Commit-commit penting yang sudah ada di GitHub **terhapus**
   - History di GitHub menjadi sama dengan history lokal (yang sudah di-rewrite)
   - File-file yang ada di commit GitHub sebelumnya **hilang**

3. **File Belum Ter-Commit**
   - File yang masih **untracked** (belum di-add ke Git) **tidak ikut** dalam rewrite history
   - File yang masih **modified** (belum di-commit) **tidak ikut** dalam rewrite history
   - Setelah force push, file-file ini **hilang** dari repository

**Detail lengkap ada di:** `PENJELASAN_MASALAH_GIT_FORCE_PUSH.md`

---

## 📋 Langkah-Langkah Restore

### Step 1: Backup Kondisi Lokal Saat Ini

```powershell
# Buat folder backup dengan timestamp
$backupDate = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFolder = "backup_lokal_sebelum_restore_$backupDate"
New-Item -ItemType Directory -Path $backupFolder

# Backup file penting
Copy-Item -Recurse app "$backupFolder\app"
Copy-Item -Recurse resources "$backupFolder\resources"
Copy-Item routes "$backupFolder\routes"
Copy-Item database "$backupFolder\database"
Copy-Item config "$backupFolder\config"
```

### Step 2: Download Semua File dari Production Server (31 Januari 2026)

**Metode 1: Via SSH/SCP (Recommended)**

```bash
# Download seluruh folder aplikasi dari production
scp -r user@production-server:/path/to/hris-seven-payroll/* C:\xampp\htdocs\hris-seven-payroll\
```

**Metode 2: Via File Manager (cPanel/Plesk)**

1. Login ke cPanel/Plesk production server
2. Buka **File Manager**
3. Navigate ke folder aplikasi: `/path/to/hris-seven-payroll`
4. **Select All** file dan folder
5. Klik **Compress** (zip)
6. **Download** zip file
7. **Extract** zip file ke folder lokal: `C:\xampp\htdocs\hris-seven-payroll\`

**Metode 3: Via FTP (FileZilla/WinSCP)**

1. Buka FTP client (FileZilla atau WinSCP)
2. Connect ke production server
3. Navigate ke folder aplikasi
4. **Download semua file** ke folder lokal

### Step 3: Copy File-File Penting (Overwrite)

**⚠️ PERINGATAN:** Pastikan Anda sudah backup kondisi lokal di Step 1!

**File yang HARUS di-Copy (Overwrite):**

```powershell
# Controllers
Copy-Item -Recurse -Force "[folder-download]\app\Http\Controllers\*" "app\Http\Controllers\"

# Routes
Copy-Item -Force "[folder-download]\routes\web.php" "routes\web.php"
Copy-Item -Force "[folder-download]\routes\api.php" "routes\api.php"

# Models
Copy-Item -Recurse -Force "[folder-download]\app\Models\*" "app\Models\"

# Views
Copy-Item -Recurse -Force "[folder-download]\resources\views\*" "resources\views\"

# Migrations
Copy-Item -Recurse -Force "[folder-download]\database\migrations\*" "database\migrations\"

# Middleware
Copy-Item -Recurse -Force "[folder-download]\app\Http\Middleware\*" "app\Http\Middleware\"

# Services
Copy-Item -Recurse -Force "[folder-download]\app\Services\*" "app\Services\"

# Traits
Copy-Item -Recurse -Force "[folder-download]\app\Traits\*" "app\Traits\"

# Exports
Copy-Item -Recurse -Force "[folder-download]\app\Exports\*" "app\Exports\"

# Config
Copy-Item -Recurse -Force "[folder-download]\config\*" "config\"
```

**File yang TIDAK BOLEH di-Copy:**

❌ **`.env`** - Jangan copy dari production (berisi credential production)  
❌ **`vendor/`** - Install via `composer install` di lokal  
❌ **`node_modules/`** - Install via `npm install` jika ada  
❌ **`storage/`** - Folder storage lokal berbeda  
❌ **`bootstrap/cache/`** - Cache akan di-generate otomatis  
❌ **`backup_*.sql`** - File backup database (terlalu besar, sudah di .gitignore)

### Step 4: Install Dependencies

```bash
# Install Composer dependencies
composer install --no-dev

# Install NPM dependencies (jika ada)
npm install
```

### Step 5: Setup Environment Lokal

```bash
# Pastikan .env lokal sudah benar (jangan copy dari production!)
# Jika belum ada .env, copy dari .env.example
if (!(Test-Path .env)) {
    Copy-Item .env.example .env
}

# Generate application key
php artisan key:generate

# Setup storage link
php artisan storage:link
```

### Step 6: Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Step 7: Verifikasi File

```powershell
# Cek apakah routes/web.php tidak kosong (minimal 100 baris)
$webRoutes = Get-Content routes\web.php | Measure-Object -Line
Write-Host "routes/web.php: $($webRoutes.Lines) lines"

# Cek apakah controller utama ada dan tidak kosong
$controllers = Get-ChildItem app\Http\Controllers\*.php
Write-Host "Total controllers: $($controllers.Count)"

# Cek beberapa controller penting
$importantControllers = @(
    "AbsenController.php",
    "ClosingController.php",
    "KaryawanController.php",
    "AuthController.php"
)

foreach ($controller in $importantControllers) {
    $file = "app\Http\Controllers\$controller"
    if (Test-Path $file) {
        $lines = (Get-Content $file | Measure-Object -Line).Lines
        Write-Host "$controller : $lines lines"
    } else {
        Write-Host "$controller : MISSING!" -ForegroundColor Red
    }
}
```

### Step 8: Test Aplikasi

1. **Buka aplikasi di browser:** `http://localhost/hris-seven-payroll` atau sesuai konfigurasi lokal
2. **Test login:** Pastikan bisa login
3. **Test fitur utama:**
   - Browse Absensi
   - Master Karyawan
   - Closing Gaji
   - Laporan
4. **Cek error log:** `storage/logs/laravel.log`

### Step 9: Commit ke Git (Setelah Restore)

```bash
# Check status
git status

# Add semua file yang di-restore
git add app/ resources/ routes/ database/ config/

# Commit
git commit -m "Restore aplikasi lengkap dari production server (31 Januari 2026) - Full restore semua controllers, models, views, migrations, dan file penting lainnya"

# Push ke GitHub
git push origin main
```

---

## 📝 Checklist Restore

Gunakan checklist ini untuk memastikan semua langkah sudah dilakukan:

### Persiapan:
- [ ] Backup kondisi lokal saat ini
- [ ] Download semua file dari production server (tanggal 31 Jan 2026)

### Copy File:
- [ ] Copy controllers (semua file)
- [ ] Copy routes (web.php, api.php)
- [ ] Copy models (semua file)
- [ ] Copy views (semua file)
- [ ] Copy migrations (semua file)
- [ ] Copy config (semua file)
- [ ] Copy middleware (semua file)
- [ ] Copy services (semua file)
- [ ] Copy traits (semua file)
- [ ] Copy exports (semua file)

### Setup:
- [ ] Install dependencies (composer install)
- [ ] Setup .env (jangan copy dari production)
- [ ] Generate key (php artisan key:generate)
- [ ] Setup storage link (php artisan storage:link)
- [ ] Clear cache (semua cache)

### Verifikasi:
- [ ] File routes/web.php tidak kosong (minimal 100 baris)
- [ ] Semua controller ada dan tidak kosong
- [ ] Aplikasi bisa diakses di browser
- [ ] Login berfungsi
- [ ] Fitur utama berfungsi (Browse Absensi, Master Karyawan, dll)
- [ ] Tidak ada error di Laravel log

### Commit:
- [ ] Commit semua file ke Git
- [ ] Push ke GitHub

---

## 🐛 Troubleshooting

### Problem: File masih tidak lengkap setelah restore
**Solusi:**
- Pastikan download **semua file** dari production (bukan hanya beberapa folder)
- Cek apakah ada file yang ter-skip saat copy
- Bandingkan jumlah file di production vs lokal
- Pastikan copy dengan opsi `-Recurse -Force` untuk overwrite semua file

### Problem: Aplikasi error setelah restore
**Solusi:**
- Clear semua cache: `php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear`
- Install dependencies: `composer install --no-dev`
- Setup storage link: `php artisan storage:link`
- Cek Laravel log: `storage/logs/laravel.log`
- Pastikan `.env` sudah benar (jangan copy dari production)

### Problem: Database error
**Solusi:**
- Pastikan `.env` sudah benar (jangan copy dari production)
- Cek koneksi database di `.env`
- Run migration jika perlu: `php artisan migrate`
- Pastikan database lokal sudah ada dan bisa diakses

### Problem: Route tidak ditemukan
**Solusi:**
- Clear route cache: `php artisan route:clear`
- Cek `routes/web.php` sudah benar
- Cek apakah controller sudah ada
- Run: `php artisan route:list` untuk melihat semua route

---

## ✅ Setelah Restore Berhasil

Setelah semua langkah selesai dan aplikasi berjalan normal:

1. **Test semua fitur utama:**
   - Login/Logout
   - Browse Absensi
   - Master Karyawan
   - Closing Gaji
   - Laporan
   - Settings

2. **Commit ke Git:**
   ```bash
   git add .
   git commit -m "Restore aplikasi lengkap dari production server (31 Januari 2026)"
   git push origin main
   ```

3. **Dokumentasikan:**
   - Catat tanggal restore
   - Catat file-file yang di-restore
   - Catat masalah yang ditemukan dan solusinya

---

## 📚 Referensi

- **Penjelasan Masalah:** `PENJELASAN_MASALAH_GIT_FORCE_PUSH.md`
- **Solusi Sinkronisasi:** `SOLUSI_SINKRONISASI_GITHUB.md`
- **Checklist Restore:** `CHECKLIST_RESTORE_DARI_PRODUCTION.md`

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0  
**Target Restore:** 31 Januari 2026 (Production Server)

