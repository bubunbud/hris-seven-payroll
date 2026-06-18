# 📋 Manual Deployment Modul Form Perjalanan Dinas ke Ubuntu Server

**Tanggal:** 12 Februari 2026  
**Modul:** Form Perjalanan Dinas  
**Versi:** 1.0 (dengan Auto-Update Absensi)  
**Status:** ✅ Siap Deploy ke Production

---

## 📝 Ringkasan Fitur

Modul **Form Perjalanan Dinas** untuk mencatat perjalanan dinas karyawan, baik Dinas Dalam Negeri maupun Dinas Luar Negeri.

### Fitur Utama:
1. **CRUD Form Perjalanan Dinas** (Header, Karyawan, Jadwal, Hotel, Otorisasi)
2. **Auto-Update Absensi**: Otomatis update/insert data ke `t_absen` saat form disimpan/di-update
3. **Print/Cetak Form**: Layout print yang sudah disesuaikan
4. **Filter & Search**: Filter tanggal, search No RPD/NIK/Nama
5. **Autocomplete**: NIK/Nama dengan autocomplete

---

## 📁 Daftar File yang Perlu Di-copy

### 1. **Migration Files** (6 files)
```
database/migrations/2026_02_11_023919_create_t_perjalanan_dinas_header_table.php
database/migrations/2026_02_11_023927_create_t_perjalanan_dinas_karyawan_table.php
database/migrations/2026_02_11_023930_create_t_perjalanan_dinas_jadwal_table.php
database/migrations/2026_02_11_023932_create_t_perjalanan_dinas_hotel_table.php
database/migrations/2026_02_11_023935_create_t_perjalanan_dinas_tiba_kembali_table.php
database/migrations/2026_02_11_034150_add_tanggal_dinas_fields_to_t_perjalanan_dinas_header_table.php
```

### 2. **Model Files** (5 files)
```
app/Models/PerjalananDinasHeader.php
app/Models/PerjalananDinasKaryawan.php
app/Models/PerjalananDinasJadwal.php
app/Models/PerjalananDinasHotel.php
app/Models/PerjalananDinasTibaKembali.php
```

### 3. **Controller File** (1 file)
```
app/Http/Controllers/PerjalananDinasController.php
```

### 4. **View Files** (2 files)
```
resources/views/perjalanan-dinas/index.blade.php
resources/views/perjalanan-dinas/print.blade.php
```

### 5. **Routes Update** (1 file)
```
routes/web.php
```

### 6. **Sidebar Menu Update** (1 file)
```
resources/views/layouts/app.blade.php
```

**Total: 16 files**

---

## 🚀 Langkah-Langkah Deployment ke Ubuntu Server

### **Step 1: Backup Database**

```bash
# Login ke server Ubuntu
ssh user@your-server-ip

# Backup database sebelum migration
cd /var/www/html/hris-seven-payroll
mysqldump -u [db_user] -p [db_name] > backup_before_perjalanan_dinas_$(date +%Y%m%d_%H%M%S).sql

# Contoh:
# mysqldump -u hris_user -p hris_seven > backup_before_perjalanan_dinas_20260212.sql
```

### **Step 2: Copy Files ke Server**

#### **Opsi A: Menggunakan SCP (dari Windows/Local)**

```bash
# Dari local machine, copy semua file ke server

# 1. Migration Files
scp database/migrations/2026_02_11_*_perjalanan_dinas*.php user@server:/var/www/html/hris-seven-payroll/database/migrations/
scp database/migrations/2026_02_11_034150_add_tanggal_dinas_fields_to_t_perjalanan_dinas_header_table.php user@server:/var/www/html/hris-seven-payroll/database/migrations/

# 2. Model Files
scp app/Models/PerjalananDinas*.php user@server:/var/www/html/hris-seven-payroll/app/Models/

# 3. Controller File
scp app/Http/Controllers/PerjalananDinasController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# 4. View Files
# Buat direktori jika belum ada
ssh user@server "mkdir -p /var/www/html/hris-seven-payroll/resources/views/perjalanan-dinas"
scp resources/views/perjalanan-dinas/*.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/perjalanan-dinas/

# 5. Routes & Sidebar
scp routes/web.php user@server:/var/www/html/hris-seven-payroll/routes/
scp resources/views/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/layouts/
```

#### **Opsi B: Menggunakan Git (jika menggunakan version control)**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll
git pull origin main  # atau branch yang sesuai
```

#### **Opsi C: Manual Copy via FTP/SFTP**

1. Upload semua file menggunakan FileZilla, WinSCP, atau tool SFTP lainnya
2. Pastikan struktur direktori sama dengan di local

### **Step 3: Set Permission File**

```bash
# Login ke server
ssh user@server

cd /var/www/html/hris-seven-payroll

# Set ownership (ganti www-data sesuai user web server)
sudo chown -R www-data:www-data .

# Set permission untuk storage dan cache
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 755 app database resources routes

# Set permission untuk file baru
sudo chmod 644 app/Models/PerjalananDinas*.php
sudo chmod 644 app/Http/Controllers/PerjalananDinasController.php
sudo chmod 644 resources/views/perjalanan-dinas/*.blade.php
```

### **Step 4: Run Migration**

```bash
cd /var/www/html/hris-seven-payroll

# Jalankan migration
php artisan migrate

# Expected Output:
# Migrating: 2026_02_11_023919_create_t_perjalanan_dinas_header_table
# Migrated:  2026_02_11_023919_create_t_perjalanan_dinas_header_table
# Migrating: 2026_02_11_023927_create_t_perjalanan_dinas_karyawan_table
# Migrated:  2026_02_11_023927_create_t_perjalanan_dinas_karyawan_table
# Migrating: 2026_02_11_023930_create_t_perjalanan_dinas_jadwal_table
# Migrated:  2026_02_11_023930_create_t_perjalanan_dinas_jadwal_table
# Migrating: 2026_02_11_023932_create_t_perjalanan_dinas_hotel_table
# Migrated:  2026_02_11_023932_create_t_perjalanan_dinas_hotel_table
# Migrating: 2026_02_11_023935_create_t_perjalanan_dinas_tiba_kembali_table
# Migrated:  2026_02_11_023935_create_t_perjalanan_dinas_tiba_kembali_table
# Migrating: 2026_02_11_034150_add_tanggal_dinas_fields_to_t_perjalanan_dinas_header_table
# Migrated:  2026_02_11_034150_add_tanggal_dinas_fields_to_t_perjalanan_dinas_header_table
```

**Jika ada error "Table already exists":**
```bash
# Cek status migration
php artisan migrate:status

# Jika perlu rollback (HATI-HATI: Backup dulu!)
php artisan migrate:rollback --step=6
```

### **Step 5: Clear All Cache**

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache (optional, untuk production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 6: Set Permission di Database**

```bash
# Login ke MySQL
mysql -u root -p

# Atau jika menggunakan user tertentu
mysql -u [db_user] -p [db_name]
```

Jalankan query berikut:

```sql
-- Tambahkan permission baru
INSERT INTO permissions (name, guard_name, created_at, updated_at)
VALUES ('view-perjalanan-dinas', 'web', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Assign permission ke role admin (ganti role_id sesuai kebutuhan)
-- Cek dulu ID role admin
SELECT id, name FROM roles WHERE name = 'admin';

-- Assign permission (ganti 1 dengan role_id admin yang sebenarnya)
INSERT INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'admin'
AND p.name = 'view-perjalanan-dinas'
ON DUPLICATE KEY UPDATE role_id = role_id;
```

**Atau melalui UI:**
1. Login sebagai admin
2. Buka menu **Settings → Role & Permission**
3. Tambahkan permission: `view-perjalanan-dinas`
4. Assign ke role yang sesuai (admin, hr, dll)

### **Step 7: Verifikasi File & Permission**

```bash
cd /var/www/html/hris-seven-payroll

# Cek apakah semua file sudah ada
ls -la app/Models/PerjalananDinas*.php
ls -la app/Http/Controllers/PerjalananDinasController.php
ls -la resources/views/perjalanan-dinas/
ls -la database/migrations/2026_02_11_*perjalanan*.php

# Cek permission
ls -la storage/logs/
ls -la bootstrap/cache/
```

### **Step 8: Test Route**

```bash
# Cek route sudah terdaftar
php artisan route:list | grep perjalanan-dinas

# Expected output:
# GET|HEAD  perjalanan-dinas .................... perjalanan-dinas.index
# POST      perjalanan-dinas .................... perjalanan-dinas.store
# GET|HEAD  perjalanan-dinas/{id} ............... perjalanan-dinas.show
# PUT       perjalanan-dinas/{id} ............... perjalanan-dinas.update
# DELETE    perjalanan-dinas/{id} ............... perjalanan-dinas.destroy
# GET|HEAD  perjalanan-dinas/{id}/print ......... perjalanan-dinas.print
# GET|HEAD  perjalanan-dinas/get-karyawan-data .. perjalanan-dinas.get-karyawan-data
# POST      perjalanan-dinas/{id}/update-absensi perjalanan-dinas.trigger-update-absensi
```

---

## ✅ Testing Checklist

### **1. Test Database & Migration**
- [ ] Migration berhasil tanpa error
- [ ] Semua 6 tabel berhasil dibuat:
  - [ ] `t_perjalanan_dinas_header`
  - [ ] `t_perjalanan_dinas_karyawan`
  - [ ] `t_perjalanan_dinas_jadwal`
  - [ ] `t_perjalanan_dinas_hotel`
  - [ ] `t_perjalanan_dinas_tiba_kembali`
- [ ] Kolom `dtTanggalDinasDari`, `dtTanggalDinasSampai`, `intDurasiHari` ada di header
- [ ] Foreign key constraints berfungsi

### **2. Test Menu & Route**
- [ ] Menu "Form Perjalanan Dinas" muncul di sidebar (jika user punya permission)
- [ ] Route `/perjalanan-dinas` dapat diakses
- [ ] Tidak ada error 404 atau 500
- [ ] Route print dapat diakses: `/perjalanan-dinas/{id}/print`

### **3. Test CRUD**
- [ ] **Create**: Tambah Form Perjalanan Dinas baru
  - [ ] Header form dapat diisi (termasuk Tanggal Mulai & Sampai Dinas)
  - [ ] Karyawan dapat ditambahkan (multi)
  - [ ] Jadwal dapat ditambahkan (multi)
  - [ ] Hotel dapat ditambahkan (optional)
  - [ ] Otorisasi dapat diisi
  - [ ] No RPD ter-generate otomatis (format: RPDYYYYMMDDXXX)
  - [ ] Durasi otomatis terhitung
- [ ] **Read**: List Form Perjalanan Dinas
  - [ ] Filter tanggal (Dari Tanggal & Sampai Tanggal) berfungsi
  - [ ] Search No RPD/NIK/Nama berfungsi
  - [ ] Pagination berfungsi
  - [ ] Kolom "Tanggal Dinas", "Durasi", "Karyawan yang Bertugas" tampil benar
- [ ] **Update**: Edit Form Perjalanan Dinas
  - [ ] Data header dapat diupdate
  - [ ] Karyawan dapat ditambah/dihapus
  - [ ] Jadwal dapat ditambah/dihapus
  - [ ] Hotel dapat ditambah/dihapus
  - [ ] Tanggal dan jam tampil dengan benar di edit mode
- [ ] **Delete**: Hapus Form Perjalanan Dinas
  - [ ] Data header dan detail terhapus (cascade)

### **4. Test Autocomplete**
- [ ] Autocomplete NIK/Nama di filter form berfungsi
- [ ] Autocomplete NIK/Nama di modal form berfungsi
- [ ] Auto-fill Bisnis Unit, Departemen, Jabatan saat pilih karyawan
- [ ] Keyboard navigation (Arrow Up/Down, Enter) berfungsi

### **5. Test Auto-Update Absensi** ⭐ **FITUR BARU**
- [ ] **Saat Create Form:**
  - [ ] Data absensi ter-insert/update ke `t_absen` untuk semua karyawan
  - [ ] Hanya untuk hari kerja (Senin-Jumat)
  - [ ] Hanya untuk karyawan aktif yang punya shift
  - [ ] `dtJamMasuk` dan `dtJamKeluar` sesuai shift karyawan
  - [ ] `vcketerangan` berisi "Dinas Luar {No.RPD}"
- [ ] **Saat Update Form:**
  - [ ] Data absensi ter-update sesuai perubahan
  - [ ] Jika tanggal dinas berubah, absensi ter-update untuk range baru
- [ ] **Verifikasi di Database:**
  ```sql
  SELECT * FROM t_absen 
  WHERE vcketerangan LIKE 'Dinas Luar%' 
  ORDER BY dtTanggal DESC, vcNik;
  ```
- [ ] **Test Manual Trigger:**
  - [ ] Route `POST /perjalanan-dinas/{id}/update-absensi` dapat diakses
  - [ ] Trigger manual berhasil update absensi

### **6. Test Print/Cetak**
- [ ] Print view dapat diakses: `/perjalanan-dinas/{id}/print`
- [ ] Layout print sesuai dengan form rujukan
- [ ] Data tercetak lengkap (Header, Karyawan, Jadwal, Hotel, Otorisasi)
- [ ] Font Calibri digunakan
- [ ] Logo dan header tampil dengan benar
- [ ] No. RPD posisi benar (rata kanan, di atas Tanggal Mulai Dinas)
- [ ] Hotel section selalu tampil (walaupun kosong)

### **7. Test Validasi**
- [ ] Validasi required field berfungsi (Tanggal Form, Tanggal Mulai & Sampai Dinas, Pemberi Tugas, Tujuan Dinas)
- [ ] Validasi format tanggal/jam berfungsi
- [ ] Validasi NIK exists berfungsi
- [ ] Validasi minimal 1 karyawan dan 1 jadwal

### **8. Test Error Handling**
- [ ] Error handling untuk karyawan tanpa shift (skip, tidak error)
- [ ] Error handling untuk karyawan tidak aktif (skip, tidak error)
- [ ] Error handling untuk hari libur (skip, tidak error)
- [ ] Log error tercatat di `storage/logs/laravel.log`

---

## 🐛 Troubleshooting

### **Error: Table already exists**
```bash
# Cek apakah tabel sudah ada
mysql -u [user] -p [database] -e "SHOW TABLES LIKE 't_perjalanan_dinas%';"

# Jika sudah ada, cek migration status
php artisan migrate:status

# Rollback jika perlu (HATI-HATI: Backup dulu!)
php artisan migrate:rollback --step=6
```

### **Error: Foreign key constraint fails**
- Pastikan tabel `t_perjalanan_dinas_header` dibuat terlebih dahulu
- Pastikan urutan migration sesuai (header → detail)
- Cek apakah ada data orphan di detail table

### **Error: Permission denied**
```bash
# Pastikan permission sudah ditambahkan
mysql -u [user] -p [database] -e "SELECT * FROM permissions WHERE name = 'view-perjalanan-dinas';"

# Pastikan user memiliki role yang memiliki permission
mysql -u [user] -p [database] -e "SELECT r.name, p.name FROM roles r JOIN role_permission rp ON r.id = rp.role_id JOIN permissions p ON rp.permission_id = p.id WHERE p.name = 'view-perjalanan-dinas';"
```

### **Error: Route not found**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Cek route sudah terdaftar
php artisan route:list | grep perjalanan-dinas
```

### **Error: Class not found**
```bash
# Clear autoload cache
composer dump-autoload

# Clear config cache
php artisan config:clear
```

### **Error: Illegal offset type / array_key_exists**
- ✅ **Sudah diperbaiki**: Menggunakan `DB::table()` untuk insert/update karena composite primary key
- Pastikan menggunakan versi controller terbaru

### **Error: Column 'intDurasiIstirahat' cannot be null**
- ✅ **Sudah diperbaiki**: Set default value `0` untuk `intDurasiIstirahat`
- Pastikan menggunakan versi controller terbaru

### **Data absensi tidak ter-insert/update**
1. Cek log file: `tail -f storage/logs/laravel.log | grep PerjalananDinas`
2. Pastikan karyawan aktif (`vcAktif = '1'`)
3. Pastikan karyawan punya shift (`vcShift` tidak null dan ada di `m_shift`)
4. Pastikan tanggal dinas adalah hari kerja (Senin-Jumat)
5. Test manual trigger: `POST /perjalanan-dinas/{id}/update-absensi`

---

## 📝 Catatan Penting

### **1. No RPD Format**
- Format: `RPDYYYYMMDDXXX`
- Contoh: `RPD20260211001`
- Prefix: RPD
- Tanggal: YYYYMMDD (dari `dtTanggalForm`)
- Counter: XXX (3 digit, auto increment per tanggal)

### **2. Auto-Update Absensi**
- **Trigger**: Otomatis saat `store()` dan `update()` form
- **Scope**: 
  - Hanya karyawan aktif (`vcAktif = '1'`)
  - Hanya karyawan yang punya shift
  - Hanya hari kerja (Senin-Jumat)
  - Range tanggal: `dtTanggalDinasDari` sampai `dtTanggalDinasSampai`
- **Data yang di-update**:
  - `dtJamMasuk`: dari `m_shift.vcMasuk` (format HH:mm:ss)
  - `dtJamKeluar`: dari `m_shift.vcPulang` (format HH:mm:ss)
  - `vcketerangan`: "Dinas Luar {No.RPD}"
  - `intDurasiIstirahat`: 0 (default)
- **Method**: Menggunakan `DB::table()` untuk menghindari masalah composite primary key

### **3. Klasifikasi Grade**
- Senior Management
- Middle Management
- Junior Management
- Staff
- Operator/Driver

### **4. Moda Perjalanan**
- Kendaraan Dinas
- Kendaraan Pribadi
- Kendaraan Umum

### **5. Cascade Delete**
- Jika header dihapus, semua detail (karyawan, jadwal, hotel, tiba_kembali) akan terhapus otomatis

### **6. Print Layout**
- Font: Calibri
- Margin: 1cm
- Logo: 25px height
- No. RPD: Rata kanan, di atas Tanggal Mulai Dinas
- Hotel section: Selalu tampil (walaupun kosong)

---

## 🔍 Verifikasi Final

### **Cek Database**
```sql
-- Cek tabel sudah dibuat
SHOW TABLES LIKE 't_perjalanan_dinas%';

-- Cek struktur tabel header
DESCRIBE t_perjalanan_dinas_header;

-- Cek kolom tanggal dinas sudah ada
SHOW COLUMNS FROM t_perjalanan_dinas_header LIKE 'dtTanggalDinas%';
SHOW COLUMNS FROM t_perjalanan_dinas_header LIKE 'intDurasiHari';
```

### **Cek Log**
```bash
# Cek log untuk error
tail -n 100 storage/logs/laravel.log | grep -i error

# Cek log untuk PerjalananDinas
tail -n 100 storage/logs/laravel.log | grep PerjalananDinas
```

### **Cek Permission**
```bash
# Via browser: Login → Settings → Role & Permission
# Pastikan permission 'view-perjalanan-dinas' ada dan sudah di-assign ke role
```

---

## ✅ Checklist Final Deployment

- [ ] Backup database sudah dilakukan
- [ ] Semua 16 file sudah di-copy ke server
- [ ] Permission file sudah di-set (775 untuk storage/cache, 644 untuk file)
- [ ] Migration berhasil dijalankan (6 migration files)
- [ ] Semua cache sudah di-clear
- [ ] Permission database sudah di-set (`view-perjalanan-dinas`)
- [ ] Route sudah terdaftar (8 routes)
- [ ] Menu sidebar sudah muncul
- [ ] Testing checklist sudah dilakukan
- [ ] Auto-update absensi sudah berfungsi
- [ ] Print view sudah berfungsi
- [ ] Tidak ada error di log
- [ ] Modul dapat diakses dan berfungsi normal

---

## 📞 Support & Contact

Jika ada masalah saat deployment:
1. Cek log: `storage/logs/laravel.log`
2. Cek error log web server: `/var/log/apache2/error.log` atau `/var/log/nginx/error.log`
3. Hubungi tim development

---

## 📋 Deployment Record

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Server IP/Hostname:** _______________  
**Database Name:** _______________  
**Status:** ⬜ Success  ⬜ Failed  
**Notes:** _______________

---

**Status:** ✅ Siap untuk Production  
**Version:** 1.0 (dengan Auto-Update Absensi)  
**Last Updated:** 12 Februari 2026








