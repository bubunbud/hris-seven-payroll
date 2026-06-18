# 📋 Panduan Deployment Modul Form Perjalanan Dinas

**Tanggal:** 11 Februari 2026  
**Modul:** Form Perjalanan Dinas  
**Status:** ✅ Siap Deploy

---

## 📝 Ringkasan Perubahan

Modul baru **Form Perjalanan Dinas** telah dibuat untuk mencatat perjalanan dinas karyawan, baik untuk Dinas Dalam Negeri (Antar Kota dan Wilayah) maupun Dinas Luar Negeri (Antar Negara).

### Fitur Utama:
1. **Header Form**: No RPD, Tanggal Form, Pemberi Tugas, Tujuan Dinas, Maksud/Uraian
2. **Karyawan Yang Ditugaskan**: Multi karyawan dengan detail (NIK, Nama, Departemen, Jabatan, Klasifikasi Grade)
3. **Jadwal dan Moda Perjalanan**: Berangkat & Kembali dengan moda (Kendaraan Dinas/Pribadi/Umum)
4. **Hotel/Penginapan**: Optional, dengan detail tanggal, kota/provinsi/negara, nama hotel
5. **Otorisasi**: Mengajukan, Menyetujui, Mengetahui
6. **Tiba/Kembali di Tempat Tujuan**: Tracking kedatangan dan kepulangan (opsional)

---

## 📁 File yang Perlu Di-copy/Update

### 1. **Migration Files** (5 files)
```
database/migrations/2026_02_11_023919_create_t_perjalanan_dinas_header_table.php
database/migrations/2026_02_11_023927_create_t_perjalanan_dinas_karyawan_table.php
database/migrations/2026_02_11_023930_create_t_perjalanan_dinas_jadwal_table.php
database/migrations/2026_02_11_023932_create_t_perjalanan_dinas_hotel_table.php
database/migrations/2026_02_11_023935_create_t_perjalanan_dinas_tiba_kembali_table.php
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

### 4. **View Files** (1 file)
```
resources/views/perjalanan-dinas/index.blade.php
```

### 5. **Routes Update** (1 file)
```
routes/web.php
```

### 6. **Sidebar Menu Update** (1 file)
```
resources/views/layouts/app.blade.php
```

---

## 🗄️ Struktur Database

### Tabel: `t_perjalanan_dinas_header`
- `vcNoRpd` (PK, string 50) - No RPD (Rencana Perjalanan Dinas)
- `dtTanggalForm` (date) - Tanggal Form Dinas
- `vcPemberiTugas` (string 100) - Nama Pemberi Tugas
- `vcJabatanPemberiTugas` (string 100, nullable) - Jabatan Pemberi Tugas
- `vcTujuanDinas` (string 200) - Tujuan Dinas
- `vcMaksudPerjalananDinas` (text, nullable) - Maksud/Uraian
- `vcMengajukan` (string 100, nullable) - Mengajukan - Penerima Tugas
- `vcMenyetujui` (string 100, nullable) - Menyetujui - Pemberi Tugas
- `vcMengetahui` (string 100, nullable) - Mengetahui - HRD
- `dtCreate`, `dtChange` (datetime, nullable)

### Tabel: `t_perjalanan_dinas_karyawan`
- `vcCounterKaryawan` (PK, string 50)
- `vcNoRpd` (FK, string 50) → `t_perjalanan_dinas_header.vcNoRpd`
- `vcNik` (string 10)
- `vcNamaKaryawan` (string 100)
- `vcKodeDept` (string 10, nullable)
- `vcKodeJabatan` (string 20, nullable)
- `vcKlasifikasiGrade` (string 50, nullable) - Senior Management, Middle Management, Junior Management, Staff, Operator/Driver
- `dtCreate`, `dtChange` (datetime, nullable)

### Tabel: `t_perjalanan_dinas_jadwal`
- `vcCounterJadwal` (PK, string 50)
- `vcNoRpd` (FK, string 50) → `t_perjalanan_dinas_header.vcNoRpd`
- `vcModaPerjalanan` (string 50) - Kendaraan Dinas, Kendaraan Pribadi, Kendaraan Umum
- `vcHariBerangkat` (string 20, nullable)
- `dtTanggalBerangkat` (date, nullable)
- `dtJamBerangkat` (time, nullable)
- `vcKeteranganBerangkat` (string 200, nullable)
- `vcHariKembali` (string 20, nullable)
- `dtTanggalKembali` (date, nullable)
- `dtJamKembali` (time, nullable)
- `vcKeteranganKembali` (string 200, nullable)
- `dtCreate`, `dtChange` (datetime, nullable)

### Tabel: `t_perjalanan_dinas_hotel`
- `vcCounterHotel` (PK, string 50)
- `vcNoRpd` (FK, string 50) → `t_perjalanan_dinas_header.vcNoRpd`
- `isMenginap` (boolean, default false)
- `dtTanggalMenginap` (date, nullable)
- `vcKotaProvinsiNegara` (string 200, nullable)
- `vcNamaHotel` (string 200, nullable)
- `vcKeteranganHotel` (text, nullable)
- `dtCreate`, `dtChange` (datetime, nullable)

### Tabel: `t_perjalanan_dinas_tiba_kembali`
- `vcCounterTibaKembali` (PK, string 50)
- `vcNoRpd` (FK, string 50) → `t_perjalanan_dinas_header.vcNoRpd`
- `vcHariTiba` (string 20, nullable)
- `dtTanggalTiba` (date, nullable)
- `dtJamTiba` (time, nullable)
- `vcHariKembali` (string 20, nullable)
- `dtTanggalKembali` (date, nullable)
- `dtJamKembali` (time, nullable)
- `vcKeteranganKedatangan` (text, nullable)
- `vcTandaTanganPihakBerwenang` (string 100, nullable)
- `dtCreate`, `dtChange` (datetime, nullable)

---

## 🚀 Langkah-Langkah Deployment

### **Step 1: Backup Database**
```bash
# Backup database sebelum migration
mysqldump -u [username] -p [database_name] > backup_before_perjalanan_dinas_$(date +%Y%m%d_%H%M%S).sql
```

### **Step 2: Copy Files ke Server**

#### A. Migration Files
```bash
# Copy semua migration files ke server
scp database/migrations/2026_02_11_*_perjalanan_dinas*.php user@server:/path/to/hris-seven-payroll/database/migrations/
```

#### B. Model Files
```bash
# Copy semua model files
scp app/Models/PerjalananDinas*.php user@server:/path/to/hris-seven-payroll/app/Models/
```

#### C. Controller File
```bash
# Copy controller
scp app/Http/Controllers/PerjalananDinasController.php user@server:/path/to/hris-seven-payroll/app/Http/Controllers/
```

#### D. View Files
```bash
# Buat direktori jika belum ada
mkdir -p /path/to/hris-seven-payroll/resources/views/perjalanan-dinas

# Copy view
scp resources/views/perjalanan-dinas/index.blade.php user@server:/path/to/hris-seven-payroll/resources/views/perjalanan-dinas/
```

#### E. Update Routes & Sidebar
```bash
# Update routes/web.php
scp routes/web.php user@server:/path/to/hris-seven-payroll/routes/

# Update sidebar
scp resources/views/layouts/app.blade.php user@server:/path/to/hris-seven-payroll/resources/views/layouts/
```

### **Step 3: Run Migration**

```bash
cd /path/to/hris-seven-payroll
php artisan migrate
```

**Expected Output:**
```
Migrating: 2026_02_11_023919_create_t_perjalanan_dinas_header_table
Migrated:  2026_02_11_023919_create_t_perjalanan_dinas_header_table (XX.XXms)
Migrating: 2026_02_11_023927_create_t_perjalanan_dinas_karyawan_table
Migrated:  2026_02_11_023927_create_t_perjalanan_dinas_karyawan_table (XX.XXms)
Migrating: 2026_02_11_023930_create_t_perjalanan_dinas_jadwal_table
Migrated:  2026_02_11_023930_create_t_perjalanan_dinas_jadwal_table (XX.XXms)
Migrating: 2026_02_11_023932_create_t_perjalanan_dinas_hotel_table
Migrated:  2026_02_11_023932_create_t_perjalanan_dinas_hotel_table (XX.XXms)
Migrating: 2026_02_11_023935_create_t_perjalanan_dinas_tiba_kembali_table
Migrated:  2026_02_11_023935_create_t_perjalanan_dinas_tiba_kembali_table (XX.XXms)
```

### **Step 4: Clear Cache**

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### **Step 5: Set Permission (jika diperlukan)**

```bash
# Set permission untuk storage dan cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### **Step 6: Set Permission di Database**

Pastikan user database memiliki permission untuk:
- CREATE TABLE
- ALTER TABLE
- INSERT, UPDATE, DELETE, SELECT

---

## 🔐 Permission Setup

### **Tambahkan Permission Baru di Database**

Jalankan query berikut di database:

```sql
-- Insert permission untuk view-perjalanan-dinas
INSERT INTO permissions (name, guard_name, created_at, updated_at)
VALUES ('view-perjalanan-dinas', 'web', NOW(), NOW());

-- Assign permission ke role yang sesuai (contoh: admin, hr)
-- Ganti role_id sesuai dengan ID role yang diinginkan
INSERT INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name IN ('admin', 'hr')
AND p.name = 'view-perjalanan-dinas';
```

**Atau melalui UI Settings → Role & Permission:**
1. Buka menu **Settings → Role & Permission**
2. Tambahkan permission baru: `view-perjalanan-dinas`
3. Assign permission ke role yang sesuai (admin, hr, dll)

---

## ✅ Testing Checklist

### **1. Test Migration**
- [ ] Migration berhasil tanpa error
- [ ] Semua tabel berhasil dibuat
- [ ] Foreign key constraints berfungsi

### **2. Test Menu & Route**
- [ ] Menu "Form Perjalanan Dinas" muncul di sidebar (jika user punya permission)
- [ ] Route `/perjalanan-dinas` dapat diakses
- [ ] Tidak ada error 404 atau 500

### **3. Test CRUD**
- [ ] **Create**: Tambah Form Perjalanan Dinas baru
  - [ ] Header form dapat diisi
  - [ ] Karyawan dapat ditambahkan (multi)
  - [ ] Jadwal dapat ditambahkan (multi)
  - [ ] Hotel dapat ditambahkan (optional)
  - [ ] Otorisasi dapat diisi
  - [ ] No RPD ter-generate otomatis (format: RPDYYYYMMDDXXX)
- [ ] **Read**: List Form Perjalanan Dinas
  - [ ] Filter tanggal berfungsi
  - [ ] Search No RPD/NIK/Nama berfungsi
  - [ ] Pagination berfungsi
- [ ] **Update**: Edit Form Perjalanan Dinas
  - [ ] Data header dapat diupdate
  - [ ] Karyawan dapat ditambah/dihapus
  - [ ] Jadwal dapat ditambah/dihapus
  - [ ] Hotel dapat ditambah/dihapus
- [ ] **Delete**: Hapus Form Perjalanan Dinas
  - [ ] Data header dan detail terhapus (cascade)

### **4. Test Autocomplete**
- [ ] Autocomplete NIK/Nama di filter form berfungsi
- [ ] Autocomplete NIK/Nama di modal form berfungsi
- [ ] Keyboard navigation (Arrow Up/Down, Enter) berfungsi

### **5. Test Validasi**
- [ ] Validasi required field berfungsi
- [ ] Validasi format tanggal/jam berfungsi
- [ ] Validasi NIK exists berfungsi

### **6. Test Print** (jika sudah dibuat)
- [ ] Print view dapat diakses
- [ ] Layout print sesuai dengan form rujukan
- [ ] Data tercetak lengkap

---

## 🐛 Troubleshooting

### **Error: Table already exists**
```bash
# Jika tabel sudah ada, rollback migration terlebih dahulu
php artisan migrate:rollback --step=5

# Atau drop tabel manual jika diperlukan
# HATI-HATI: Backup database terlebih dahulu!
```

### **Error: Foreign key constraint fails**
- Pastikan tabel `t_perjalanan_dinas_header` dibuat terlebih dahulu
- Pastikan urutan migration sesuai (header → detail)

### **Error: Permission denied**
- Pastikan permission `view-perjalanan-dinas` sudah ditambahkan
- Pastikan user memiliki role yang memiliki permission tersebut

### **Error: Route not found**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache
```

### **Error: Class not found**
```bash
# Clear autoload cache
composer dump-autoload
```

---

## 📝 Catatan Penting

1. **No RPD Format**: `RPDYYYYMMDDXXX` (contoh: RPD20260211001)
   - Prefix: RPD
   - Tanggal: YYYYMMDD (dari dtTanggalForm)
   - Counter: XXX (3 digit, auto increment per tanggal)

2. **Klasifikasi Grade**: 
   - Senior Management
   - Middle Management
   - Junior Management
   - Staff
   - Operator/Driver

3. **Moda Perjalanan**:
   - Kendaraan Dinas
   - Kendaraan Pribadi
   - Kendaraan Umum

4. **Cascade Delete**: 
   - Jika header dihapus, semua detail (karyawan, jadwal, hotel, tiba_kembali) akan terhapus otomatis

5. **Print View**: 
   - Fitur print masih dalam pengembangan
   - Method `print()` saat ini hanya redirect ke index dengan pesan info

---

## 📞 Support

Jika ada masalah saat deployment, silakan hubungi tim development atau cek log:
- Laravel Log: `storage/logs/laravel.log`
- Apache/Nginx Error Log: `/var/log/apache2/error.log` atau `/var/log/nginx/error.log`

---

## ✅ Checklist Final

- [ ] Semua file sudah di-copy ke server
- [ ] Migration berhasil dijalankan
- [ ] Cache sudah di-clear
- [ ] Permission sudah di-set
- [ ] Testing checklist sudah dilakukan
- [ ] Tidak ada error di log
- [ ] Modul dapat diakses dan berfungsi normal

---

**Status:** ✅ Siap untuk Production  
**Deployment Date:** _______________  
**Deployed By:** _______________  
**Notes:** _______________









