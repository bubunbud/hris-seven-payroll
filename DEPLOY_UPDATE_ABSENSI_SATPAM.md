# 📦 Panduan Deployment Update: Sistem Absensi Satpam

## 🎯 Ringkasan

Dokumentasi ini berisi langkah-langkah manual untuk mengupdate aplikasi HRIS Seven Payroll di server Ubuntu dengan fitur baru: **Sistem Absensi Satpam**.

---

## 📋 LANGKAH 1: Backup Database (WAJIB!)

Sebelum melakukan update, backup database terlebih dahulu:

```bash
# Login ke server Ubuntu
ssh user@192.168.10.40

# Backup database
mysqldump -u root -p hris_seven > backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql
```

**Catatan:** Simpan file backup di lokasi yang aman!

---

## 📋 LANGKAH 2: Copy File ke Server

### A. File yang HARUS di-copy (dari local ke server):

#### 1. **Models** (3 file)

```
app/Models/ShiftSecurity.php
app/Models/JadwalShiftSecurity.php
app/Models/OverrideJadwalSecurity.php
```

**Lokasi di server:**

```
/var/www/html/hris-seven-payroll/app/Models/
```

#### 2. **Controllers** (3 file)

```
app/Http/Controllers/JadwalShiftSecurityController.php
app/Http/Controllers/MasterShiftSecurityController.php
app/Http/Controllers/OverrideJadwalSecurityController.php
```

**Lokasi di server:**

```
/var/www/html/hris-seven-payroll/app/Http/Controllers/
```

#### 3. **Services** (1 file)

```
app/Services/SecurityAbsensiService.php
```

**Lokasi di server:**

```
/var/www/html/hris-seven-payroll/app/Services/
```

**Catatan:** Jika folder `Services` belum ada, buat dulu:

```bash
mkdir -p /var/www/html/hris-seven-payroll/app/Services
```

#### 4. **Views** (6 file)

```
resources/views/jadwal-shift-security/index.blade.php
resources/views/master/shift-security/index.blade.php
resources/views/master/shift-security/create.blade.php
resources/views/master/shift-security/edit.blade.php
resources/views/override-jadwal-security/index.blade.php
resources/views/override-jadwal-security/show.blade.php
```

**Lokasi di server:**

```
/var/www/html/hris-seven-payroll/resources/views/jadwal-shift-security/
/var/www/html/hris-seven-payroll/resources/views/master/shift-security/
/var/www/html/hris-seven-payroll/resources/views/override-jadwal-security/
```

**Catatan:** Buat folder jika belum ada:

```bash
mkdir -p /var/www/html/hris-seven-payroll/resources/views/jadwal-shift-security
mkdir -p /var/www/html/hris-seven-payroll/resources/views/master/shift-security
mkdir -p /var/www/html/hris-seven-payroll/resources/views/override-jadwal-security
```

#### 5. **File yang DIMODIFIKASI** (2 file)

```
resources/views/absen/index.blade.php
resources/views/layouts/app.blade.php
routes/web.php
```

**Lokasi di server:**

```
/var/www/html/hris-seven-payroll/resources/views/absen/
/var/www/html/hris-seven-payroll/resources/views/layouts/
/var/www/html/hris-seven-payroll/routes/
```

#### 6. **Migrations** (4 file - OPSIONAL, hanya untuk dokumentasi)

```
database/migrations/2025_12_01_063526_create_m_shift_security_table.php
database/migrations/2025_12_01_063527_create_t_jadwal_shift_security_table.php
database/migrations/2025_12_01_063529_create_t_override_jadwal_security_table.php
database/migrations/2025_12_01_074715_update_t_jadwal_shift_security_allow_null_shift.php
```

**Catatan:** File migration ini TIDAK perlu di-copy karena kita akan membuat tabel secara manual via SQL.

---

## 📋 LANGKAH 3: Update Database (Manual SQL)

### A. Buat Tabel `m_shift_security`

Jalankan SQL berikut di MySQL server:

```sql
-- Tabel Master Shift Security
CREATE TABLE IF NOT EXISTS `m_shift_security` (
  `vcKodeShift` TINYINT PRIMARY KEY COMMENT '1=Shift 1, 2=Shift 2, 3=Shift 3',
  `vcNamaShift` VARCHAR(20) COMMENT 'Shift 1, Shift 2, Shift 3',
  `dtJamMasuk` TIME COMMENT 'Jam masuk shift',
  `dtJamPulang` TIME COMMENT 'Jam pulang shift',
  `isCrossDay` BOOLEAN DEFAULT FALSE COMMENT 'True jika shift melewati tengah malam',
  `intDurasiJam` DECIMAL(4,2) DEFAULT 8.00 COMMENT 'Durasi shift dalam jam',
  `intToleransiMasuk` INT DEFAULT 30 COMMENT 'Toleransi terlambat dalam menit',
  `intToleransiPulang` INT DEFAULT 30 COMMENT 'Toleransi pulang cepat dalam menit',
  `vcKeterangan` VARCHAR(100) NULL,
  `dtCreate` DATETIME NULL,
  `dtChange` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### B. Buat Tabel `t_jadwal_shift_security`

```sql
-- Tabel Jadwal Shift Security
CREATE TABLE IF NOT EXISTS `t_jadwal_shift_security` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vcNik` VARCHAR(8) COMMENT 'NIK Satpam',
  `dtTanggal` DATE COMMENT 'Tanggal jadwal',
  `intShift` TINYINT NULL COMMENT '1=Shift 1, 2=Shift 2, 3=Shift 3, NULL=OFF',
  `vcKeterangan` VARCHAR(50) NULL COMMENT 'OFF, Libur Nasional, Penggantian, dll',
  `isOverride` BOOLEAN DEFAULT FALSE COMMENT 'True jika jadwal di-override karena urgent',
  `vcOverrideBy` VARCHAR(100) NULL COMMENT 'User yang override',
  `dtOverrideAt` DATETIME NULL,
  `dtCreate` DATETIME NULL,
  `dtChange` DATETIME NULL,
  INDEX `idx_nik_tanggal` (`vcNik`, `dtTanggal`),
  INDEX `idx_tanggal` (`dtTanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### C. Buat Tabel `t_override_jadwal_security`

```sql
-- Tabel Override Jadwal Security (Audit Trail)
CREATE TABLE IF NOT EXISTS `t_override_jadwal_security` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vcNik` VARCHAR(8) COMMENT 'NIK Satpam',
  `dtTanggal` DATE COMMENT 'Tanggal yang di-override',
  `intShiftLama` TINYINT NULL COMMENT 'Shift yang di-override (bisa null jika tambah shift baru)',
  `intShiftBaru` TINYINT COMMENT 'Shift baru',
  `vcAlasan` TEXT COMMENT 'Alasan override',
  `vcOverrideBy` VARCHAR(100) COMMENT 'User yang override',
  `dtOverrideAt` DATETIME,
  `dtCreate` DATETIME NULL,
  INDEX `idx_nik_tanggal` (`vcNik`, `dtTanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### D. Insert Data Master Shift (Seed Data)

```sql
-- Insert data default untuk Master Shift Security
INSERT INTO `m_shift_security`
(`vcKodeShift`, `vcNamaShift`, `dtJamMasuk`, `dtJamPulang`, `isCrossDay`, `intDurasiJam`, `intToleransiMasuk`, `intToleransiPulang`, `vcKeterangan`, `dtCreate`, `dtChange`)
VALUES
(1, 'Shift 1', '06:30:00', '14:30:00', FALSE, 8.00, 30, 30, 'Pagi', NOW(), NOW()),
(2, 'Shift 2', '14:30:00', '22:30:00', FALSE, 8.00, 30, 30, 'Siang', NOW(), NOW()),
(3, 'Shift 3', '22:30:00', '06:30:00', TRUE, 8.00, 30, 30, 'Malam (Cross-Day)', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `vcNamaShift` = VALUES(`vcNamaShift`),
  `dtJamMasuk` = VALUES(`dtJamMasuk`),
  `dtJamPulang` = VALUES(`dtJamPulang`),
  `isCrossDay` = VALUES(`isCrossDay`),
  `intDurasiJam` = VALUES(`intDurasiJam`),
  `intToleransiMasuk` = VALUES(`intToleransiMasuk`),
  `intToleransiPulang` = VALUES(`intToleransiPulang`),
  `vcKeterangan` = VALUES(`vcKeterangan`),
  `dtChange` = NOW();
```

---

## 📋 LANGKAH 4: Set Permissions

Setelah copy file, set permissions yang benar:

```bash
# Set ownership ke www-data
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Models/ShiftSecurity.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Models/JadwalShiftSecurity.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Models/OverrideJadwalSecurity.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Http/Controllers/JadwalShiftSecurityController.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Http/Controllers/MasterShiftSecurityController.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Http/Controllers/OverrideJadwalSecurityController.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Services/
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/resources/views/jadwal-shift-security/
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/resources/views/master/shift-security/
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/resources/views/override-jadwal-security/

# Set permissions
sudo chmod -R 755 /var/www/html/hris-seven-payroll/app/
sudo chmod -R 755 /var/www/html/hris-seven-payroll/resources/
```

---

## 📋 LANGKAH 5: Clear Cache Laravel

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📋 LANGKAH 6: Verifikasi

### A. Cek Tabel Database

```sql
-- Cek apakah tabel sudah dibuat
SHOW TABLES LIKE '%shift_security%';
SHOW TABLES LIKE '%override%';

-- Cek data master shift
SELECT * FROM m_shift_security;
```

**Hasil yang diharapkan:**

-   3 tabel muncul: `m_shift_security`, `t_jadwal_shift_security`, `t_override_jadwal_security`
-   Tabel `m_shift_security` berisi 3 record (Shift 1, 2, 3)

### B. Cek File

```bash
# Cek apakah semua file sudah ada
ls -la /var/www/html/hris-seven-payroll/app/Models/ShiftSecurity.php
ls -la /var/www/html/hris-seven-payroll/app/Models/JadwalShiftSecurity.php
ls -la /var/www/html/hris-seven-payroll/app/Models/OverrideJadwalSecurity.php
ls -la /var/www/html/hris-seven-payroll/app/Http/Controllers/JadwalShiftSecurityController.php
ls -la /var/www/html/hris-seven-payroll/app/Services/SecurityAbsensiService.php
ls -la /var/www/html/hris-seven-payroll/resources/views/jadwal-shift-security/index.blade.php
```

### C. Test Aplikasi

1. **Login ke aplikasi:** `http://hr.abncorp.lan` atau `http://192.168.10.40`
2. **Cek menu baru:**
    - Menu "Absensi" → "Jadwal Shift Satpam"
    - Menu "Absensi" → "Master Shift Security"
    - Menu "Absensi" → "List Override Jadwal"
3. **Test fitur:**
    - Buka halaman "Jadwal Shift Satpam"
    - Buka halaman "Master Shift Security" (harus muncul 3 shift)
    - Buka halaman "List Override Jadwal" (kosong, normal)

---

## 📋 CHECKLIST DEPLOYMENT

Gunakan checklist ini untuk memastikan semua langkah sudah dilakukan:

-   [ ] **Backup database** sudah dilakukan
-   [ ] **Copy Models** (3 file) sudah di-copy
-   [ ] **Copy Controllers** (3 file) sudah di-copy
-   [ ] **Copy Services** (1 file) sudah di-copy
-   [ ] **Copy Views** (6 file) sudah di-copy
-   [ ] **Update file yang dimodifikasi** (3 file) sudah di-update
-   [ ] **Buat tabel `m_shift_security`** sudah dibuat
-   [ ] **Buat tabel `t_jadwal_shift_security`** sudah dibuat
-   [ ] **Buat tabel `t_override_jadwal_security`** sudah dibuat
-   [ ] **Insert data master shift** sudah di-insert
-   [ ] **Set permissions** sudah dilakukan
-   [ ] **Clear cache Laravel** sudah dilakukan
-   [ ] **Verifikasi tabel** sudah dicek
-   [ ] **Verifikasi file** sudah dicek
-   [ ] **Test aplikasi** sudah dilakukan

---

## 🐛 Troubleshooting

### Error: Class not found

**Solusi:** Pastikan semua file sudah di-copy dan permissions sudah benar. Clear cache:

```bash
php artisan optimize:clear
```

### Error: Table not found

**Solusi:** Pastikan SQL untuk membuat tabel sudah dijalankan. Cek dengan:

```sql
SHOW TABLES LIKE '%shift_security%';
```

### Error: Route not found (RouteNotFoundException)

**Error:** `Route [jadwal-shift-security.index] not defined`

**Solusi:**

1. Pastikan `routes/web.php` sudah di-update dengan route baru (baris 136-154)
2. Pastikan controller sudah di-import di bagian atas `routes/web.php`:
    ```php
    use App\Http\Controllers\JadwalShiftSecurityController;
    use App\Http\Controllers\MasterShiftSecurityController;
    use App\Http\Controllers\OverrideJadwalSecurityController;
    ```
3. Clear route cache:

```bash
php artisan route:clear
php artisan optimize:clear
php artisan route:cache
```

4. Verifikasi route sudah terdaftar:

```bash
php artisan route:list --name=jadwal-shift-security
```

**Catatan:** Error ini sering terjadi jika:

-   File `routes/web.php` di server belum di-update
-   Route cache masih menyimpan versi lama
-   Controller belum di-import di `routes/web.php`

### Error: View not found

**Solusi:** Pastikan semua view sudah di-copy ke folder yang benar. Clear view cache:

```bash
php artisan view:clear
php artisan view:cache
```

---

## 📝 Catatan Penting

1. **Backup wajib dilakukan** sebelum update
2. **Tabel database** dibuat manual via SQL (tidak pakai migrate)
3. **Data master shift** harus di-insert setelah tabel dibuat
4. **Permissions** harus di-set ke `www-data:www-data`
5. **Cache Laravel** harus di-clear setelah update
6. **Test semua fitur** setelah deployment

---

## 📞 Support

Jika ada masalah saat deployment, cek:

1. Log Laravel: `storage/logs/laravel.log`
2. Log Apache: `/var/log/apache2/error.log`
3. Pastikan semua file sudah di-copy dengan benar
4. Pastikan SQL sudah dijalankan dengan benar

---

**Status:** ✅ Siap untuk deployment

**Tanggal:** 2 Desember 2025
