# 🚀 Panduan Deployment: Field Tipe/Kategori Izin Keluar - Server Ubuntu

## 📋 Ringkasan

Panduan sederhana untuk mengupdate aplikasi HRIS Seven Payroll di server Ubuntu dengan fitur baru: **Field Tipe/Kategori Izin Keluar Komplek**.

---

## ✅ LANGKAH 1: Backup (OPSIONAL)

Jika ingin aman, backup database dulu:

```bash
# Login ke server Ubuntu
ssh user@192.168.10.40

# Backup database (opsional)
mysqldump -u root -p hris_seven > backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql
```

---

## ✅ LANGKAH 2: Copy File ke Server

Copy **3 file** berikut dari local ke server:

### File yang Harus Di-copy:

1. **`app/Models/Izin.php`**
   - Lokasi server: `/var/www/html/hris-seven-payroll/app/Models/Izin.php`

2. **`app/Http/Controllers/IzinKeluarController.php`**
   - Lokasi server: `/var/www/html/hris-seven-payroll/app/Http/Controllers/IzinKeluarController.php`

3. **`resources/views/absen/izin_keluar/index.blade.php`**
   - Lokasi server: `/var/www/html/hris-seven-payroll/resources/views/absen/izin_keluar/index.blade.php`

**Cara Copy:**
- Gunakan SCP, FTP, atau copy-paste manual
- Pastikan file di-overwrite dengan versi baru

---

## ✅ LANGKAH 3: Update Database

Jalankan SQL berikut di MySQL server:

```bash
# Login ke MySQL
mysql -u root -p

# Pilih database
use hris_seven;

# Jalankan SQL untuk tambah field
ALTER TABLE `t_izin` 
ADD COLUMN `vcTipeIzin` VARCHAR(20) NULL 
AFTER `vcKodeIzin` 
COMMENT 'Tipe/Kategori Izin: Masuk Siang, Izin Biasa, Pulang Cepat (hanya untuk jenis izin pribadi)';

# Verifikasi: Cek struktur tabel
DESCRIBE t_izin;

# Harus muncul kolom vcTipeIzin setelah vcKodeIzin
# Exit MySQL
exit;
```

**Atau gunakan file SQL:**
```bash
mysql -u root -p hris_seven < DEPLOY_SQL_IZIN_TIPE.sql
```

---

## ✅ LANGKAH 4: Set Permissions

Setelah copy file, set permissions yang benar:

```bash
cd /var/www/html/hris-seven-payroll

# Set ownership ke www-data
sudo chown -R www-data:www-data app/Models/Izin.php
sudo chown -R www-data:www-data app/Http/Controllers/IzinKeluarController.php
sudo chown -R www-data:www-data resources/views/absen/izin_keluar/

# Set permissions
sudo chmod -R 755 app/Models/Izin.php
sudo chmod -R 755 app/Http/Controllers/IzinKeluarController.php
sudo chmod -R 755 resources/views/absen/izin_keluar/
```

---

## ✅ LANGKAH 5: Clear Cache Laravel

**PENTING:** Wajib clear cache setelah update!

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
sudo -u www-data php artisan optimize:clear

# Rebuild cache
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache
```

---

## ✅ LANGKAH 6: Verifikasi

### A. Cek File

```bash
# Cek apakah semua file sudah ada
ls -la /var/www/html/hris-seven-payroll/app/Models/Izin.php
ls -la /var/www/html/hris-seven-payroll/app/Http/Controllers/IzinKeluarController.php
ls -la /var/www/html/hris-seven-payroll/resources/views/absen/izin_keluar/index.blade.php
```

### B. Cek Database

```bash
# Login ke MySQL
mysql -u root -p

# Pilih database
use hris_seven;

# Cek struktur tabel
DESCRIBE t_izin;

# Harus muncul kolom vcTipeIzin dengan:
# - Type: varchar(20)
# - Null: YES
# - Key: (kosong)
# - Default: NULL

# Exit MySQL
exit;
```

### C. Test Aplikasi

1. **Login ke aplikasi:** `http://hr.abncorp.lan` atau `http://192.168.10.40`

2. **Test Form Tambah:**
   - Buka menu "Izin Keluar Komplek Kantor"
   - Klik tombol "Tambah"
   - Pilih jenis izin **pribadi** (Z003 atau Z004)
   - **Field "Tipe/Kategori Izin" harus muncul** ✓
   - Pilih tipe/kategori: Masuk Siang, Izin Biasa, atau Pulang Cepat
   - Simpan data → Berhasil ✓

3. **Test Form Edit:**
   - Edit data izin dengan jenis pribadi
   - **Field "Tipe/Kategori Izin" harus muncul dengan value yang benar** ✓
   - Ubah tipe/kategori jika perlu
   - Simpan → Berhasil ✓

4. **Test Tabel Data:**
   - **Kolom "Tipe/Kategori" harus muncul di tabel** ✓
   - Data dengan tipe menampilkan badge info ✓
   - Data tanpa tipe menampilkan "-" ✓

---

## 📋 CHECKLIST DEPLOYMENT

Gunakan checklist ini untuk memastikan semua langkah sudah dilakukan:

- [ ] **Backup database** (opsional) sudah dilakukan
- [ ] **Copy Model** (`Izin.php`) sudah di-copy
- [ ] **Copy Controller** (`IzinKeluarController.php`) sudah di-copy
- [ ] **Copy View** (`index.blade.php`) sudah di-copy
- [ ] **Update database** (tambah field `vcTipeIzin`) sudah dilakukan
- [ ] **Set permissions** sudah dilakukan
- [ ] **Clear cache Laravel** sudah dilakukan
- [ ] **Verifikasi file** sudah dicek
- [ ] **Verifikasi database** sudah dicek
- [ ] **Test aplikasi** sudah dilakukan

---

## 🐛 Troubleshooting

### Error: Column 'vcTipeIzin' doesn't exist

**Masalah:** Error saat simpan/edit data

**Solusi:**
1. Pastikan SQL untuk tambah kolom sudah dijalankan
2. Verifikasi dengan: `DESCRIBE t_izin;`
3. Pastikan kolom `vcTipeIzin` ada di tabel

### Field Tipe/Kategori Tidak Muncul

**Masalah:** Field tipe/kategori tidak muncul saat pilih jenis izin pribadi

**Solusi:**
1. Clear browser cache (Ctrl+F5 atau Ctrl+Shift+R)
2. Clear view cache: `sudo -u www-data php artisan view:clear`
3. Pastikan file `index.blade.php` sudah di-update
4. Cek JavaScript console untuk error

### Data Tipe/Kategori Tidak Tersimpan

**Masalah:** Data tipe/kategori tidak tersimpan ke database

**Solusi:**
1. Cek validasi di controller (harus ada `vcTipeIzin` di validation rules)
2. Cek model (harus ada `vcTipeIzin` di `$fillable`)
3. Cek form (pastikan field `vcTipeIzin` ada di form dengan name yang benar)
4. Cek database (pastikan kolom `vcTipeIzin` ada di tabel)

---

## 📝 Catatan Penting

1. **Tidak ada perubahan database yang merusak** - Hanya tambah kolom baru (nullable)
2. **File yang di-overwrite** - Pastikan backup file lama jika perlu
3. **Permissions** - Harus di-set ke `www-data:www-data`
4. **Cache Laravel** - **WAJIB** di-clear setelah update
5. **Browser cache** - Clear browser cache jika field tidak muncul

---

## 🚀 Quick Deploy Commands

Jika semua file sudah di-copy, jalankan perintah ini sekaligus:

```bash
cd /var/www/html/hris-seven-payroll

# Set permissions
sudo chown -R www-data:www-data app/Models/Izin.php
sudo chown -R www-data:www-data app/Http/Controllers/IzinKeluarController.php
sudo chown -R www-data:www-data resources/views/absen/izin_keluar/

# Clear & rebuild cache
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache

# Verifikasi database (jika belum)
mysql -u root -p hris_seven -e "DESCRIBE t_izin;" | grep vcTipeIzin
```

---

## 📞 Support

Jika ada masalah saat deployment, cek:

1. Log Laravel: `storage/logs/laravel.log`
2. Log Apache: `/var/log/apache2/error.log`
3. Pastikan semua file sudah di-copy dengan benar
4. Pastikan SQL sudah dijalankan dengan benar
5. Pastikan permissions sudah benar
6. Pastikan cache sudah di-clear

---

**Status:** ✅ Siap untuk deployment

**Tanggal:** 4 Desember 2025

**Catatan:** Hanya update 3 file dan 1 kolom database. Tidak ada perubahan yang merusak.











