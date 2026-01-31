# Solusi Sinkronisasi GitHub dengan Aplikasi Terakhir

**Tanggal:** 12 Januari 2026  
**Masalah:** Aplikasi di lokal menjadi tidak selengkap aplikasi terakhir setelah push ke GitHub

---

## 🔍 Analisis Masalah

### Penyebab:
1. **Git Filter-Branch:** Saat menghapus file SQL backup dari history, proses `git filter-branch` melakukan rewrite history yang mungkin mempengaruhi commit-commit lain
2. **Force Push:** Force push ke GitHub mungkin menghapus commit-commit penting yang belum ter-push sebelumnya
3. **File Tidak Ter-Commit:** Mungkin ada file-file penting yang belum ter-commit sebelum force push

---

## ✅ Solusi: Restore dari Production Server

Karena aplikasi di production server adalah versi terbaru dan lengkap, **solusi terbaik adalah restore semua file dari production server**.

### Langkah-Langkah:

#### Step 1: Backup Kondisi Lokal Saat Ini
```bash
# Buat folder backup
mkdir backup_lokal_sebelum_restore_$(Get-Date -Format "yyyyMMdd_HHmmss")

# Backup file penting
Copy-Item -Recurse app backup_lokal_sebelum_restore_*/app
Copy-Item -Recurse resources backup_lokal_sebelum_restore_*/resources
Copy-Item routes backup_lokal_sebelum_restore_*/routes
```

#### Step 2: Download Semua File dari Production Server

**Metode 1: Via SSH/SCP (Recommended)**
```bash
# Download seluruh folder aplikasi dari production
scp -r user@production-server:/path/to/hris-seven-payroll/* C:\xampp\htdocs\hris-seven-payroll\
```

**Metode 2: Via File Manager (cPanel/Plesk)**
1. Login ke cPanel/Plesk
2. Buka File Manager
3. Zip seluruh folder aplikasi
4. Download zip file
5. Extract ke folder lokal

**Metode 3: Via FTP**
1. Gunakan FTP client (FileZilla, WinSCP)
2. Connect ke production server
3. Download semua file ke lokal

#### Step 3: File yang HARUS di-Copy (Prioritas Tinggi)

**Controllers:**
- `app/Http/Controllers/*.php` (semua controller)

**Routes:**
- `routes/web.php`
- `routes/api.php`

**Models:**
- `app/Models/*.php` (semua model)

**Views:**
- `resources/views/**/*.blade.php` (semua view)

**Migrations:**
- `database/migrations/*.php` (semua migration)

**Config:**
- `config/*.php` (semua config file)

**Middleware:**
- `app/Http/Middleware/*.php` (semua middleware)

**Services:**
- `app/Services/*.php` (semua service)

**Traits:**
- `app/Traits/*.php` (semua trait)

**Exports:**
- `app/Exports/*.php` (semua export class)

#### Step 4: File yang TIDAK BOLEH di-Copy

❌ **`.env`** - Jangan copy dari production (berisi credential production)  
❌ **`vendor/`** - Install via `composer install` di lokal  
❌ **`node_modules/`** - Install via `npm install` jika ada  
❌ **`storage/`** - Folder storage lokal berbeda  
❌ **`bootstrap/cache/`** - Cache akan di-generate otomatis  
❌ **`backup_*.sql`** - File backup database (terlalu besar)

#### Step 5: Setelah Copy - Verifikasi

```bash
# 1. Cek apakah semua controller ada
Get-ChildItem app\Http\Controllers\*.php | Measure-Object

# 2. Cek apakah routes/web.php tidak kosong
Get-Content routes\web.php | Measure-Object -Line

# 3. Test aplikasi
php artisan route:list
php artisan config:clear
php artisan cache:clear
```

#### Step 6: Install Dependencies

```bash
# Install Composer dependencies
composer install --no-dev

# Install NPM dependencies (jika ada)
npm install
```

#### Step 7: Setup Environment

```bash
# Copy .env.example ke .env (jika belum ada)
Copy-Item .env.example .env

# Generate key
php artisan key:generate

# Setup storage link
php artisan storage:link
```

#### Step 8: Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🔄 Alternatif: Pull dari GitHub (Jika Ada)

Jika di GitHub sudah ada commit yang lengkap:

```bash
# 1. Stash perubahan lokal
git stash

# 2. Pull dari GitHub
git pull origin main

# 3. Restore perubahan lokal (jika perlu)
git stash pop
```

---

## ⚠️ Catatan Penting

1. **JANGAN copy `.env`** dari production - File ini berisi credential database production
2. **Backup dulu** - Sebelum restore, backup kondisi lokal saat ini
3. **Test setelah restore** - Pastikan aplikasi berjalan normal
4. **Commit setelah restore** - Setelah semua file lengkap, commit ke Git

---

## 📝 Checklist Restore

- [ ] Backup kondisi lokal saat ini
- [ ] Download semua file dari production server
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
- [ ] Install dependencies (composer install)
- [ ] Setup .env (jangan copy dari production)
- [ ] Clear cache
- [ ] Test aplikasi
- [ ] Commit ke Git

---

## 🐛 Troubleshooting

### Problem: File masih tidak lengkap setelah restore
**Solusi:**
- Pastikan download semua file dari production (bukan hanya beberapa folder)
- Cek apakah ada file yang ter-skip saat copy
- Bandingkan jumlah file di production vs lokal

### Problem: Aplikasi error setelah restore
**Solusi:**
- Clear semua cache: `php artisan cache:clear`
- Install dependencies: `composer install`
- Setup storage link: `php artisan storage:link`
- Cek Laravel log: `storage/logs/laravel.log`

### Problem: Database error
**Solusi:**
- Pastikan `.env` sudah benar (jangan copy dari production)
- Cek koneksi database di `.env`
- Run migration jika perlu: `php artisan migrate`

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0

