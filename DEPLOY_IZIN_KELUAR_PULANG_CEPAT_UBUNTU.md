# 🚀 Panduan Deployment: Update Izin Keluar Komplek - Pulang Cepat (Ubuntu Server)

**Tanggal:** 12 Januari 2026  
**Server:** Ubuntu Production  
**Fitur:** Izin Keluar Komplek - Disable Field "Dari" untuk Pulang Cepat + Auto Save dtJamKeluar

---

## 📋 RINGKASAN UPDATE

Update ini mencakup 3 perubahan utama:

1. **Disable Field "Dari" untuk Pulang Cepat**
   - Field "Dari" otomatis disable ketika Jenis Izin = Pribadi dan Tipe = Pulang Cepat
   - Field "Dari" tidak wajib diisi untuk kondisi ini

2. **Migration: Kolom dtDari Menjadi Nullable**
   - Kolom `dtDari` di tabel `t_izin` diubah menjadi nullable
   - Mendukung kondisi "Pulang Cepat" dengan `dtDari` = null

3. **Auto Save dtJamKeluar ke t_absen**
   - Field "Sampai (HH:MM)" otomatis tersimpan ke `t_absen.dtJamKeluar`
   - Berlaku untuk semua jenis izin pribadi (Z003, Z004)

---

## ✅ LANGKAH 1: BACKUP (WAJIB!)

### **A. Backup Database**

```bash
# Login ke server Ubuntu
ssh root@192.168.10.40

# Masuk ke folder aplikasi
cd /var/www/html/hris-seven-payroll

# Backup database
mysqldump -u root -proot123 hris_seven > ~/backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql

# Verifikasi backup file ada
ls -lh ~/backup_hris_seven_*.sql
```

### **B. Backup File yang Akan Diubah**

```bash
# Backup file controller
cp app/Http/Controllers/IzinKeluarController.php app/Http/Controllers/IzinKeluarController.php.backup_$(date +%Y%m%d_%H%M%S)

# Backup file view
cp resources/views/absen/izin_keluar/index.blade.php resources/views/absen/izin_keluar/index.blade.php.backup_$(date +%Y%m%d_%H%M%S)

# Verifikasi backup
ls -lh app/Http/Controllers/IzinKeluarController.php.backup_*
ls -lh resources/views/absen/izin_keluar/index.blade.php.backup_*
```

---

## ✅ LANGKAH 2: COPY FILE DARI LOCAL KE SERVER

### **File yang Harus Di-copy:**

1. **`app/Http/Controllers/IzinKeluarController.php`**
2. **`resources/views/absen/izin_keluar/index.blade.php`**
3. **`database/migrations/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php`**

### **Cara Copy (Opsi A: SCP dari Windows)**

```bash
# Dari Windows (Git Bash atau PowerShell dengan SCP)
# Pastikan sudah di folder project lokal
cd C:\xampp\htdocs\hris-seven-payroll

# Copy file ke server
scp app/Http/Controllers/IzinKeluarController.php root@192.168.10.40:/tmp/
scp resources/views/absen/izin_keluar/index.blade.php root@192.168.10.40:/tmp/
scp database/migrations/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php root@192.168.10.40:/tmp/
```

### **Cara Copy (Opsi B: FileZilla / WinSCP)**

1. Buka FileZilla atau WinSCP
2. Connect ke server: `192.168.10.40`
3. Upload 3 file di atas ke folder `/tmp/` di server

### **Cara Copy (Opsi C: Manual Copy-Paste)**

1. Buka file di local dengan text editor
2. Copy semua isinya
3. SSH ke server dan buat/edit file di server
4. Paste isinya

---

## ✅ LANGKAH 3: PASTIKAN FILE DI SERVER

```bash
# Login ke server
ssh root@192.168.10.40

# Masuk ke folder aplikasi
cd /var/www/html/hris-seven-payroll

# Copy file dari /tmp/ ke folder aplikasi
cp /tmp/IzinKeluarController.php app/Http/Controllers/IzinKeluarController.php
cp /tmp/index.blade.php resources/views/absen/izin_keluar/index.blade.php
cp /tmp/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php database/migrations/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php

# Set ownership ke www-data
sudo chown -R www-data:www-data app/Http/Controllers/IzinKeluarController.php
sudo chown -R www-data:www-data resources/views/absen/izin_keluar/index.blade.php
sudo chown -R www-data:www-data database/migrations/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php

# Set permissions
sudo chmod 644 app/Http/Controllers/IzinKeluarController.php
sudo chmod 644 resources/views/absen/izin_keluar/index.blade.php
sudo chmod 644 database/migrations/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php

# Verifikasi file sudah ada
ls -lh app/Http/Controllers/IzinKeluarController.php
ls -lh resources/views/absen/izin_keluar/index.blade.php
ls -lh database/migrations/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php
```

---

## ✅ LANGKAH 4: UPDATE DATABASE

### **Opsi A: Menggunakan Migration (Recommended)**

```bash
# Masuk ke folder aplikasi
cd /var/www/html/hris-seven-payroll

# Jalankan migration
php artisan migrate

# Verifikasi migration berhasil
php artisan migrate:status
```

### **Opsi B: Manual SQL (Jika Migration Gagal)**

```bash
# Login ke MySQL
mysql -u root -proot123

# Pilih database
use hris_seven;

# Jalankan SQL untuk ubah kolom dtDari menjadi nullable
ALTER TABLE `t_izin` 
MODIFY COLUMN `dtDari` TIME NULL;

# Verifikasi struktur tabel
DESCRIBE t_izin;

# Pastikan kolom dtDari menunjukkan NULL di kolom "Null"
# Exit MySQL
exit;
```

---

## ✅ LANGKAH 5: CLEAR CACHE

```bash
# Masuk ke folder aplikasi
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Rebuild cache (opsional, untuk performa)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ✅ LANGKAH 6: SET PERMISSIONS

```bash
# Set permissions untuk storage dan cache
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Verifikasi permissions
ls -ld storage
ls -ld bootstrap/cache
```

---

## ✅ LANGKAH 7: TESTING

### **Test 1: Disable Field "Dari" untuk Pulang Cepat**

1. Buka aplikasi di browser: `http://192.168.10.40/` (atau sesuai URL server)
2. Login dengan user yang memiliki akses
3. Buka menu **"Izin Keluar Komplek Kantor"**
4. Klik **"Tambah"**
5. Pilih:
   - **Jenis Izin:** "Pribadi" (Z003 atau Z004)
   - **Tipe/Kategori Izin:** "Pulang Cepat"
6. **Expected Result:**
   - ✅ Field "Dari" menjadi **disabled** (abu-abu, tidak bisa diisi)
   - ✅ Tanda required (*) pada label "Dari" **hilang**
   - ✅ Field "Sampai" tetap **enabled** dan **required**
   - ✅ Form bisa disubmit tanpa mengisi field "Dari"

### **Test 2: Create Izin - Pulang Cepat**

1. Input Izin Keluar baru:
   - Tanggal: (pilih tanggal)
   - NIK: (input NIK karyawan)
   - Jenis Izin: "Pribadi" (Z003/Z004)
   - Tipe: "Pulang Cepat"
   - Sampai: "15:00"
   - Keterangan: (opsional)
2. Klik **"Simpan"**
3. **Expected Result:**
   - ✅ Data izin tersimpan
   - ✅ Tidak ada error
   - ✅ Cek database `t_izin`: `dtDari` = `NULL`
   - ✅ Cek database `t_absen`: `dtJamKeluar` = "15:00:00"

### **Test 3: Edit Izin - Pulang Cepat**

1. Edit data izin dengan Tipe = "Pulang Cepat"
2. Ubah "Sampai" menjadi "16:00"
3. Klik **"Simpan"**
4. **Expected Result:**
   - ✅ Data izin ter-update
   - ✅ Cek database `t_absen`: `dtJamKeluar` = "16:00:00"

### **Test 4: Create Izin - Masuk Siang**

1. Input Izin Keluar baru:
   - Jenis Izin: "Pribadi" (Z003/Z004)
   - Tipe: "Masuk Siang"
   - Dari: (auto-fill dari shift)
   - Sampai: "14:00"
2. Klik **"Simpan"**
3. **Expected Result:**
   - ✅ Data izin tersimpan
   - ✅ Cek database `t_absen`:
     - `dtJamMasuk` = jam masuk shift
     - `dtJamKeluar` = "14:00:00"

### **Test 5: Verifikasi Database**

```bash
# Login ke MySQL
mysql -u root -proot123

# Pilih database
use hris_seven;

# Cek struktur tabel t_izin (pastikan dtDari nullable)
DESCRIBE t_izin;

# Cek data izin dengan Pulang Cepat
SELECT vcCounter, dtTanggal, vcNik, vcKodeIzin, vcTipeIzin, dtDari, dtSampai 
FROM t_izin 
WHERE vcTipeIzin = 'Pulang Cepat' 
ORDER BY dtCreate DESC 
LIMIT 5;

# Cek data absensi yang ter-update
SELECT dtTanggal, vcNik, dtJamMasuk, dtJamKeluar, vcketerangan 
FROM t_absen 
WHERE vcketerangan LIKE 'Auto:%' 
ORDER BY dtChange DESC 
LIMIT 10;

# Exit MySQL
exit;
```

---

## ⚠️ TROUBLESHOOTING

### **Error 1: Migration Gagal**

**Error:**
```
SQLSTATE[42000]: Syntax error or access violation: 1091 Can't DROP 'dtDari'
```

**Solusi:**
- Jalankan manual SQL (Opsi B di Langkah 4)
- Atau skip migration jika kolom sudah nullable

### **Error 2: Permission Denied**

**Error:**
```
Permission denied: cannot write to file
```

**Solusi:**
```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

### **Error 3: Field "Dari" Tidak Disable**

**Solusi:**
1. Clear browser cache (Ctrl + F5)
2. Clear Laravel cache:
   ```bash
   php artisan view:clear
   php artisan cache:clear
   ```
3. Restart Apache:
   ```bash
   sudo systemctl restart apache2
   ```

### **Error 4: dtJamKeluar Tidak Tersimpan**

**Solusi:**
1. Cek log error:
   ```bash
   tail -f storage/logs/laravel.log
   ```
2. Pastikan data absensi ada di `t_absen` untuk tanggal dan NIK yang sama
3. Cek apakah ada constraint di database

---

## 📋 CHECKLIST DEPLOYMENT

- [ ] Backup database sudah dibuat
- [ ] Backup file controller dan view sudah dibuat
- [ ] File `IzinKeluarController.php` sudah di-copy ke server
- [ ] File `index.blade.php` sudah di-copy ke server
- [ ] File migration sudah di-copy ke server
- [ ] Permissions sudah di-set dengan benar
- [ ] Migration sudah dijalankan (atau SQL manual sudah dijalankan)
- [ ] Cache sudah di-clear
- [ ] Test 1: Field "Dari" disable untuk Pulang Cepat ✅
- [ ] Test 2: Create Izin - Pulang Cepat ✅
- [ ] Test 3: Edit Izin - Pulang Cepat ✅
- [ ] Test 4: Create Izin - Masuk Siang ✅
- [ ] Test 5: Verifikasi database ✅

---

## 🔄 ROLLBACK (Jika Diperlukan)

### **Rollback File:**

```bash
# Restore file dari backup
cp app/Http/Controllers/IzinKeluarController.php.backup_* app/Http/Controllers/IzinKeluarController.php
cp resources/views/absen/izin_keluar/index.blade.php.backup_* resources/views/absen/izin_keluar/index.blade.php

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### **Rollback Database (HATI-HATI!):**

```bash
# Restore database dari backup
mysql -u root -proot123 hris_seven < ~/backup_hris_seven_YYYYMMDD_HHMMSS.sql

# Atau rollback migration (HATI-HATI: akan gagal jika ada data dengan dtDari = null)
php artisan migrate:rollback --step=1
```

**Catatan:** Rollback migration akan gagal jika sudah ada data dengan `dtDari` = `null`. Lebih aman restore dari backup database.

---

## 📝 FILE YANG TERLIBAT

1. ✅ `app/Http/Controllers/IzinKeluarController.php`
   - Update method `store()` dan `update()`
   - Logic disable field "Dari" untuk Pulang Cepat
   - Logic auto-save `dtJamKeluar` ke `t_absen`

2. ✅ `resources/views/absen/izin_keluar/index.blade.php`
   - Update JavaScript untuk disable/enable field "Dari"
   - Update event listener untuk `vcTipeIzin` dan `vcKodeIzin`

3. ✅ `database/migrations/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php`
   - Migration untuk mengubah kolom `dtDari` menjadi nullable

---

## ✅ SETELAH DEPLOYMENT

1. **Monitor Log:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Verifikasi Fitur:**
   - Test semua test case di atas
   - Pastikan tidak ada error di log

3. **Dokumentasi:**
   - Update dokumentasi jika ada perubahan
   - Catat tanggal deployment

---

## 📞 SUPPORT

Jika ada masalah saat deployment:
1. Cek log error: `storage/logs/laravel.log`
2. Cek database: pastikan migration berhasil
3. Cek permissions: pastikan file bisa diakses
4. Restore dari backup jika diperlukan

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0  
**Server:** Ubuntu Production (192.168.10.40)


