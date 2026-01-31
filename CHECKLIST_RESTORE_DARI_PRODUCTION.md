# Checklist Restore Project dari Production ke Lokal

**Tanggal:** 12 Januari 2026  
**Status:** Sedang proses download dari Production

---

## ⚠️ PERINGATAN PENTING

### File yang TIDAK BOLEH di-Overwrite:
- ❌ **`.env`** - File ini berisi credential database lokal, JANGAN di-overwrite!
- ❌ **`storage/`** - Folder storage lokal mungkin berbeda dengan production
- ❌ **`vendor/`** - Install via `composer install` di lokal, jangan copy dari production
- ❌ **`node_modules/`** - Install via `npm install` jika ada
- ❌ **File backup/temporary** - Folder `_app/`, `_vendor/`, dll

### File yang HARUS di-Overwrite (karena kosong/rusak):
- ✅ **`routes/web.php`** - File ini kosong, HARUS di-replace
- ✅ **`app/Http/Controllers/AbsenController.php`** - File ini kosong
- ✅ **`app/Http/Controllers/ClosingController.php`** - File ini kosong
- ✅ **`app/Http/Controllers/BrowseTidakAbsenController.php`** - File ini kosong
- ✅ **`app/Http/Controllers/RekapBankController.php`** - File ini kosong
- ✅ **`app/Http/Controllers/ActivityLogController.php`** - File ini kosong
- ✅ **`app/Http/Controllers/DashboardController.php`** - File ini kosong (opsional)
- ✅ **`app/Http/Controllers/DashboardBUController.php`** - File ini kosong (opsional)
- ✅ **`app/Http/Controllers/DashboardEmployeeController.php`** - File ini kosong (opsional)
- ✅ **`app/Http/Controllers/ListKaryawanAktifController.php`** - File ini kosong (opsional)

---

## 📋 Checklist File yang Perlu di-Copy/Replace

### 1. Routes (PRIORITAS TINGGI)
- [ ] **`routes/web.php`** ⚠️ **SANGAT KRITIS** - File ini kosong, HARUS di-replace
- [ ] `routes/api.php` - Cek apakah ada perubahan

### 2. Controllers (Yang Kosong)
- [ ] `app/Http/Controllers/AbsenController.php` - File kosong
- [ ] `app/Http/Controllers/ClosingController.php` - File kosong
- [ ] `app/Http/Controllers/BrowseTidakAbsenController.php` - File kosong
- [ ] `app/Http/Controllers/RekapBankController.php` - File kosong
- [ ] `app/Http/Controllers/ActivityLogController.php` - File kosong

### 3. Controllers (Opsional - Cek dulu)
- [ ] `app/Http/Controllers/DashboardController.php` - File kosong (cek apakah digunakan)
- [ ] `app/Http/Controllers/DashboardBUController.php` - File kosong (cek apakah digunakan)
- [ ] `app/Http/Controllers/DashboardEmployeeController.php` - File kosong (cek apakah digunakan)
- [ ] `app/Http/Controllers/ListKaryawanAktifController.php` - File kosong (cek apakah digunakan)

### 4. Controllers (Sudah Ada - Cek Apakah Sama)
- [ ] `app/Http/Controllers/AuthController.php` - Sudah ada, bandingkan dengan production
- [ ] `app/Http/Controllers/BagianController.php` - Sudah ada, bandingkan dengan production
- [ ] `app/Http/Controllers/KaryawanController.php` - Cek apakah ada perubahan terbaru
- [ ] `app/Http/Controllers/SeksiController.php` - Cek apakah ada perubahan terbaru

### 5. Views (Cek Apakah Ada Perubahan)
- [ ] `resources/views/master/karyawan/index.blade.php` - Update terbaru (foto, filter seksi/jabatan)
- [ ] `resources/views/master/seksi/index.blade.php` - Update terbaru (auto-generate kode)
- [ ] `resources/views/absen/print.blade.php` - Update terbaru (print absensi)

### 6. Models (Cek Apakah Ada Perubahan)
- [ ] `app/Models/Karyawan.php` - Cek relationship seksi
- [ ] `app/Models/Seksi.php` - Cek apakah ada perubahan

---

## 🔄 Langkah-Langkah Restore

### Step 1: Backup File Lokal (Jika Ada Isinya)
```bash
# Buat folder backup
mkdir backup_before_restore

# Backup file yang akan di-replace (jika ada isinya)
copy routes\web.php backup_before_restore\web.php.backup
copy app\Http\Controllers\AbsenController.php backup_before_restore\AbsenController.php.backup
# dst...
```

### Step 2: Copy File dari Production
**Setelah download selesai, copy file-file berikut:**

1. **Copy routes/web.php:**
   ```bash
   # Dari folder download production
   copy [folder-download]\routes\web.php routes\web.php
   ```

2. **Copy Controllers yang kosong:**
   ```bash
   copy [folder-download]\app\Http\Controllers\AbsenController.php app\Http\Controllers\AbsenController.php
   copy [folder-download]\app\Http\Controllers\ClosingController.php app\Http\Controllers\ClosingController.php
   copy [folder-download]\app\Http\Controllers\BrowseTidakAbsenController.php app\Http\Controllers\BrowseTidakAbsenController.php
   copy [folder-download]\app\Http\Controllers\RekapBankController.php app\Http\Controllers\RekapBankController.php
   copy [folder-download]\app\Http\Controllers\ActivityLogController.php app\Http\Controllers\ActivityLogController.php
   ```

### Step 3: Verifikasi File
```bash
# Cek apakah file tidak kosong
Get-Content routes\web.php | Measure-Object -Line
Get-Content app\Http\Controllers\AbsenController.php | Measure-Object -Line
```

### Step 4: Test Aplikasi
1. Buka aplikasi di browser
2. Test login
3. Test browse absensi
4. Test fitur-fitur utama
5. Cek error log: `storage/logs/laravel.log`

### Step 5: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🔍 Verifikasi Setelah Restore

### Checklist Verifikasi:
- [ ] File `routes/web.php` tidak kosong (minimal 100 baris)
- [ ] File `app/Http/Controllers/AbsenController.php` tidak kosong
- [ ] File `app/Http/Controllers/ClosingController.php` tidak kosong
- [ ] Aplikasi bisa diakses di browser
- [ ] Login berfungsi
- [ ] Browse absensi berfungsi
- [ ] Tidak ada error di Laravel log
- [ ] Route list berfungsi: `php artisan route:list`

---

## 📝 Setelah Restore - Commit ke Git

### Step 1: Check Status
```bash
git status
```

### Step 2: Add File yang di-Restore
```bash
git add routes/web.php
git add app/Http/Controllers/AbsenController.php
git add app/Http/Controllers/ClosingController.php
git add app/Http/Controllers/BrowseTidakAbsenController.php
git add app/Http/Controllers/RekapBankController.php
git add app/Http/Controllers/ActivityLogController.php
```

### Step 3: Commit
```bash
git commit -m "Restore file yang kosong dari server production (fix failed git commit)"
```

### Step 4: Push ke GitHub
```bash
git push origin main
```

---

## ⚠️ Catatan Penting

1. **JANGAN copy file `.env`** dari production - File ini berisi credential database production
2. **JANGAN copy folder `vendor/`** - Install via `composer install` di lokal
3. **JANGAN copy folder `storage/`** - Folder storage lokal berbeda dengan production
4. **Backup dulu** - Sebelum replace, backup file lokal yang ada
5. **Test setelah restore** - Pastikan aplikasi berjalan normal
6. **Clear cache** - Setelah restore, clear semua cache Laravel

---

## 🐛 Troubleshooting

### Problem: File masih kosong setelah copy
**Solusi:**
- Pastikan file di production tidak kosong
- Cek permission file
- Cek encoding file (harus UTF-8)
- Coba copy manual via copy-paste

### Problem: Aplikasi error setelah restore
**Solusi:**
- Cek Laravel log: `storage/logs/laravel.log`
- Clear cache: `php artisan cache:clear`
- Clear config cache: `php artisan config:clear`
- Clear route cache: `php artisan route:clear`
- Cek apakah `.env` masih benar (jangan di-overwrite!)

### Problem: Route tidak ditemukan
**Solusi:**
- Clear route cache: `php artisan route:clear`
- Cek `routes/web.php` sudah benar
- Cek apakah controller sudah ada

---

## ✅ Final Checklist

Setelah semua proses selesai, pastikan:
- [ ] Semua file yang kosong sudah di-restore
- [ ] Aplikasi berjalan normal
- [ ] Tidak ada error di log
- [ ] File sudah di-commit ke Git
- [ ] File sudah di-push ke GitHub
- [ ] Backup file lokal sudah dibuat (jika diperlukan)

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0


