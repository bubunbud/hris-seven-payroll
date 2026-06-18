# 🚀 Manual Deploy: Modul THR dan Update Master Divisi & Jabatan

**Tanggal:** 5 Februari 2026  
**Fitur:** 
- ✅ Update Master Divisi (Pengesahan Fields)
- ✅ Modul Perhitungan THR Lengkap
- ✅ Fix Master Jabatan (Delete Function)

---

## 🎯 Ringkasan

Deployment ini mencakup:
1. **Update Master Divisi:** Menambahkan kolom pengesahan (HR&GA Manager, Senior Finance Manager, GM Back Office)
2. **Modul THR Lengkap:** Periode Closing THR, Closing THR, List THR, Rekap THR Operator, Rekap THR Staff, Slip THR
3. **Fix Master Jabatan:** Perbaikan fungsi delete yang sebelumnya error 500

---

## 📋 File yang Harus Di-Copy ke Server Production

### **1. Database Migrations**

**File yang perlu di-copy:**

```
database/migrations/2026_02_04_043701_create_t_periode_thr_table.php
database/migrations/2026_02_04_043703_create_t_closing_thr_table.php
database/migrations/2026_02_04_080602_add_vc_keterangan_to_t_closing_thr_table.php
database/migrations/2026_02_05_020015_add_vc_nama_hari_raya_to_t_periode_thr_table.php
database/migrations/2026_02_05_031209_add_pengesahan_fields_to_m_divisi_table.php
```

**Lokasi di Server:**
```
/var/www/html/hris-seven-payroll/database/migrations/
```

---

### **2. Models**

**File yang perlu di-copy:**

```
app/Models/PeriodeThr.php
app/Models/ClosingThr.php
app/Models/Divisi.php (UPDATE)
```

**Lokasi di Server:**
```
/var/www/html/hris-seven-payroll/app/Models/
```

---

### **3. Controllers**

**File yang perlu di-copy:**

```
app/Http/Controllers/PeriodeThrController.php (NEW)
app/Http/Controllers/ClosingThrController.php (NEW)
app/Http/Controllers/ListThrController.php (NEW)
app/Http/Controllers/LaporanThrController.php (NEW)
app/Http/Controllers/SlipThrController.php (NEW)
app/Http/Controllers/DivisiController.php (UPDATE)
app/Http/Controllers/JabatanController.php (UPDATE - Fix Delete)
```

**Lokasi di Server:**
```
/var/www/html/hris-seven-payroll/app/Http/Controllers/
```

---

### **4. Views**

**File yang perlu di-copy:**

#### **Master Divisi (UPDATE):**
```
resources/views/master/divisi/index.blade.php
```

#### **Modul THR (NEW):**
```
resources/views/proses/periode-thr/index.blade.php
resources/views/proses/closing-thr/index.blade.php
resources/views/proses/list-thr/index.blade.php
resources/views/laporan/laporan-thr/index.blade.php
resources/views/laporan/laporan-thr/preview.blade.php
resources/views/laporan/laporan-thr-staff/index.blade.php
resources/views/laporan/laporan-thr-staff/preview.blade.php
resources/views/laporan/slip-thr/index.blade.php
resources/views/laporan/slip-thr/preview.blade.php
```

**Lokasi di Server:**
```
/var/www/html/hris-seven-payroll/resources/views/
```

---

### **5. Routes**

**File yang perlu di-copy:**

```
routes/web.php (UPDATE - Tambah routes THR)
```

**Lokasi di Server:**
```
/var/www/html/hris-seven-payroll/routes/
```

---

### **6. Layout (Sidebar Menu)**

**File yang perlu di-copy:**

```
resources/views/layouts/app.blade.php (UPDATE - Tambah menu THR)
```

**Lokasi di Server:**
```
/var/www/html/hris-seven-payroll/resources/views/layouts/
```

---

## 🚀 Langkah-Langkah Deployment

### **Step 1: Backup Database dan File**

```bash
# Login ke server production
ssh user@server-ip

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup file yang akan diubah
mkdir -p backup_$(date +%Y%m%d)
cp -r app/Models backup_$(date +%Y%m%d)/
cp -r app/Http/Controllers backup_$(date +%Y%m%d)/
cp -r resources/views backup_$(date +%Y%m%d)/
cp routes/web.php backup_$(date +%Y%m%d)/
```

---

### **Step 2: Copy File ke Server**

**Opsi A: Copy via SCP (dari local ke server)**

```bash
# Dari local machine, copy semua file sekaligus

# Migrations
scp database/migrations/2026_02_04_043701_create_t_periode_thr_table.php user@server-ip:/var/www/html/hris-seven-payroll/database/migrations/
scp database/migrations/2026_02_04_043703_create_t_closing_thr_table.php user@server-ip:/var/www/html/hris-seven-payroll/database/migrations/
scp database/migrations/2026_02_04_080602_add_vc_keterangan_to_t_closing_thr_table.php user@server-ip:/var/www/html/hris-seven-payroll/database/migrations/
scp database/migrations/2026_02_05_020015_add_vc_nama_hari_raya_to_t_periode_thr_table.php user@server-ip:/var/www/html/hris-seven-payroll/database/migrations/
scp database/migrations/2026_02_05_031209_add_pengesahan_fields_to_m_divisi_table.php user@server-ip:/var/www/html/hris-seven-payroll/database/migrations/

# Models
scp app/Models/PeriodeThr.php user@server-ip:/var/www/html/hris-seven-payroll/app/Models/
scp app/Models/ClosingThr.php user@server-ip:/var/www/html/hris-seven-payroll/app/Models/
scp app/Models/Divisi.php user@server-ip:/var/www/html/hris-seven-payroll/app/Models/

# Controllers
scp app/Http/Controllers/PeriodeThrController.php user@server-ip:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp app/Http/Controllers/ClosingThrController.php user@server-ip:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp app/Http/Controllers/ListThrController.php user@server-ip:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp app/Http/Controllers/LaporanThrController.php user@server-ip:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp app/Http/Controllers/SlipThrController.php user@server-ip:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp app/Http/Controllers/DivisiController.php user@server-ip:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp app/Http/Controllers/JabatanController.php user@server-ip:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Views - Master Divisi
scp resources/views/master/divisi/index.blade.php user@server-ip:/var/www/html/hris-seven-payroll/resources/views/master/divisi/

# Views - Modul THR
scp -r resources/views/proses/periode-thr user@server-ip:/var/www/html/hris-seven-payroll/resources/views/proses/
scp -r resources/views/proses/closing-thr user@server-ip:/var/www/html/hris-seven-payroll/resources/views/proses/
scp -r resources/views/proses/list-thr user@server-ip:/var/www/html/hris-seven-payroll/resources/views/proses/
scp -r resources/views/laporan/laporan-thr user@server-ip:/var/www/html/hris-seven-payroll/resources/views/laporan/
scp -r resources/views/laporan/laporan-thr-staff user@server-ip:/var/www/html/hris-seven-payroll/resources/views/laporan/
scp -r resources/views/laporan/slip-thr user@server-ip:/var/www/html/hris-seven-payroll/resources/views/laporan/

# Routes & Layout
scp routes/web.php user@server-ip:/var/www/html/hris-seven-payroll/routes/
scp resources/views/layouts/app.blade.php user@server-ip:/var/www/html/hris-seven-payroll/resources/views/layouts/
```

**Opsi B: Copy via Git (jika menggunakan version control)**

```bash
# Di server production
cd /var/www/html/hris-seven-payroll
git pull origin main  # atau branch yang sesuai
```

**Opsi C: Copy via FTP/SFTP**

Upload semua file sesuai struktur direktori di atas.

---

### **Step 3: Set Permission File**

```bash
# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Set permission untuk file baru
find app/Models -type f -name "*.php" -exec chmod 644 {} \;
find app/Http/Controllers -type f -name "*.php" -exec chmod 644 {} \;
find resources/views -type f -name "*.blade.php" -exec chmod 644 {} \;
chmod 644 routes/web.php

# Set owner (sesuaikan dengan user web server)
chown -R www-data:www-data app/
chown -R www-data:www-data resources/
chown www-data:www-data routes/web.php
```

---

### **Step 4: Jalankan Database Migrations**

```bash
# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Jalankan migrations
php artisan migrate

# Atau jalankan migration spesifik jika perlu:
php artisan migrate --path=database/migrations/2026_02_04_043701_create_t_periode_thr_table.php
php artisan migrate --path=database/migrations/2026_02_04_043703_create_t_closing_thr_table.php
php artisan migrate --path=database/migrations/2026_02_04_080602_add_vc_keterangan_to_t_closing_thr_table.php
php artisan migrate --path=database/migrations/2026_02_05_020015_add_vc_nama_hari_raya_to_t_periode_thr_table.php
php artisan migrate --path=database/migrations/2026_02_05_031209_add_pengesahan_fields_to_m_divisi_table.php
```

**Verifikasi migrations berhasil:**
```bash
# Cek status migrations
php artisan migrate:status
```

---

### **Step 5: Clear Cache Laravel**

```bash
# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Rebuild cache (opsional, untuk performa)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear autoload cache
composer dump-autoload
```

---

### **Step 6: Verifikasi Deployment**

#### **6.1. Verifikasi Database**

```bash
# Login ke MySQL
mysql -u username -p database_name

# Cek tabel baru
SHOW TABLES LIKE 't_periode_thr';
SHOW TABLES LIKE 't_closing_thr';

# Cek kolom baru di m_divisi
DESCRIBE m_divisi;
# Pastikan ada kolom: vcHrGaManager, vcSeniorFinanceManager, vcGmBackOffice

# Cek struktur tabel t_periode_thr
DESCRIBE t_periode_thr;
# Pastikan ada kolom: dtPeriode, dtCutoffTHR, dtKategori, vcNamaHariRaya, vcKodeDivisi, vcKeterangan, vcStatus

# Cek struktur tabel t_closing_thr
DESCRIBE t_closing_thr;
# Pastikan ada kolom: dtTanggalTHR, vcNik, vcAgama, vcKodeDivisi, vcGroupPegawai, decGajiPokok, decNilaiTHR, vcKeterangan, dll

EXIT;
```

#### **6.2. Verifikasi Routes**

```bash
# Cek routes THR
php artisan route:list | grep -i thr

# Pastikan routes berikut ada:
# - GET  /periode-thr
# - POST /periode-thr
# - DELETE /periode-thr/{dtPeriode}/{dtKategori}/{vcKodeDivisi}
# - GET  /closing-thr
# - POST /closing-thr
# - GET  /list-thr
# - GET  /laporan-thr
# - POST /laporan-thr/preview
# - GET  /laporan-thr-staff
# - POST /laporan-thr-staff/preview
# - GET  /slip-thr
# - POST /slip-thr/preview
```

#### **6.3. Verifikasi Menu Sidebar**

1. Login ke aplikasi
2. Buka sidebar menu
3. Pastikan menu berikut muncul:
   - **Proses Payroll → Periode Closing THR**
   - **Proses Payroll → Closing THR**
   - **Proses Payroll → List THR**
   - **Laporan → Rekap THR Operator**
   - **Laporan → Rekap THR Staff**
   - **Laporan → Slip THR**

#### **6.4. Test Fitur**

**A. Master Divisi:**
1. Buka menu: **Master → Divisi**
2. Klik "Tambah Divisi" atau "Edit Divisi"
3. ✅ Pastikan ada 3 field baru di section "Tanda Tangan Laporan":
   - HR&GA Manager
   - Senior Finance Manager
   - GM Back Office
4. ✅ Simpan dan verifikasi data tersimpan

**B. Periode Closing THR:**
1. Buka menu: **Proses Payroll → Periode Closing THR**
2. ✅ Pastikan form muncul dengan field: Tahun, Tanggal Cutoff THR, Hari Keagamaan, Nama Hari Raya, Divisi, Keterangan
3. ✅ Test tambah periode THR
4. ✅ Test edit periode THR
5. ✅ Test hapus periode THR

**C. Closing THR:**
1. Buka menu: **Proses Payroll → Closing THR**
2. ✅ Pastikan list periode THR yang belum diproses muncul
3. ✅ Pilih periode dan klik "Proses THR"
4. ✅ Verifikasi proses berhasil dan status berubah menjadi "Sudah Diproses"

**D. List THR:**
1. Buka menu: **Proses Payroll → List THR**
2. ✅ Pastikan filter muncul: Tahun, Agama, Divisi, Group Pegawai, NIK/Nama
3. ✅ Test filter dan verifikasi data muncul

**E. Rekap THR Operator:**
1. Buka menu: **Laporan → Rekap THR Operator**
2. ✅ Pastikan filter muncul: Tahun, Divisi, Agama
3. ✅ Test generate report dan verifikasi format sesuai

**F. Rekap THR Staff:**
1. Buka menu: **Laporan → Rekap THR Staff**
2. ✅ Pastikan filter muncul: Tahun, Divisi, Agama, Masa
3. ✅ Test generate report dan verifikasi format sesuai

**G. Slip THR:**
1. Buka menu: **Laporan → Slip THR**
2. ✅ Pastikan filter muncul: Tahun, Divisi, Agama, NIK/Nama
3. ✅ Test generate slip dan verifikasi format sesuai (4 slip per halaman)

**H. Master Jabatan (Fix Delete):**
1. Buka menu: **Master → Jabatan**
2. ✅ Pilih satu jabatan
3. ✅ Klik delete
4. ✅ Verifikasi tidak ada error 500
5. ✅ Verifikasi data terhapus dengan benar

---

## ✅ Checklist Deployment

### **Pre-Deployment:**
- [ ] **Backup database** (mysqldump)
- [ ] **Backup file** yang akan diubah
- [ ] **Verifikasi** semua file ada di local

### **Deployment:**
- [ ] **Copy migrations** ke server
- [ ] **Copy models** ke server
- [ ] **Copy controllers** ke server
- [ ] **Copy views** ke server
- [ ] **Copy routes** ke server
- [ ] **Copy layout** ke server
- [ ] **Set permission** file dengan benar
- [ ] **Jalankan migrations**
- [ ] **Clear cache** Laravel
- [ ] **Clear autoload** composer

### **Post-Deployment:**
- [ ] **Verifikasi database** (tabel dan kolom baru)
- [ ] **Verifikasi routes** (semua routes THR ada)
- [ ] **Verifikasi menu sidebar** (semua menu THR muncul)
- [ ] **Test Master Divisi** (tambah/edit dengan field baru)
- [ ] **Test Periode Closing THR** (CRUD)
- [ ] **Test Closing THR** (proses THR)
- [ ] **Test List THR** (filter dan list)
- [ ] **Test Rekap THR Operator** (generate report)
- [ ] **Test Rekap THR Staff** (generate report)
- [ ] **Test Slip THR** (generate slip)
- [ ] **Test Master Jabatan** (delete function)
- [ ] **Verifikasi tidak ada error** di log

---

## 📝 Detail Perubahan Database

### **Tabel Baru: `t_periode_thr`**

```sql
CREATE TABLE `t_periode_thr` (
  `dtPeriode` varchar(4) NOT NULL COMMENT 'Tahun periode THR',
  `dtCutoffTHR` date NOT NULL COMMENT 'Tanggal patokan perhitungan THR',
  `dtKategori` varchar(50) NOT NULL COMMENT 'Hari Keagamaan',
  `vcNamaHariRaya` varchar(100) DEFAULT NULL COMMENT 'Nama Hari Raya',
  `vcKodeDivisi` varchar(10) NOT NULL COMMENT 'Kode Divisi',
  `vcKeterangan` varchar(255) DEFAULT NULL COMMENT 'Keterangan',
  `vcStatus` varchar(1) DEFAULT '0' COMMENT '0=Belum proses, 1=Sudah diproses',
  `dtCreate` datetime DEFAULT NULL,
  PRIMARY KEY (`dtPeriode`,`dtKategori`,`vcKodeDivisi`),
  KEY `idx_cutoff` (`dtCutoffTHR`),
  KEY `idx_status` (`vcStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### **Tabel Baru: `t_closing_thr`**

```sql
CREATE TABLE `t_closing_thr` (
  `dtTanggalTHR` date NOT NULL COMMENT 'Tanggal patokan perhitungan THR',
  `vcNik` varchar(8) NOT NULL COMMENT 'NIK Karyawan',
  `vcAgama` varchar(20) NOT NULL COMMENT 'Agama Karyawan',
  `vcKodeDivisi` varchar(10) DEFAULT NULL COMMENT 'Kode Divisi',
  `vcGroupPegawai` varchar(20) DEFAULT NULL COMMENT 'Group Pegawai',
  `vcGolongan` varchar(10) DEFAULT NULL COMMENT 'Golongan',
  `decGajiPokok` decimal(15,2) DEFAULT NULL COMMENT 'Gaji Pokok',
  `dtTanggalMasuk` date DEFAULT NULL COMMENT 'Tanggal Masuk',
  `vcMasaKerja` varchar(50) DEFAULT NULL COMMENT 'Masa Kerja',
  `intMasaKerjaHari` int(11) DEFAULT 0 COMMENT 'Masa Kerja Hari',
  `decMasaKerjaBulan` decimal(8,2) DEFAULT 0 COMMENT 'Masa Kerja Bulan',
  `decMasaKerjaTahun` decimal(8,2) DEFAULT 0 COMMENT 'Masa Kerja Tahun',
  `decXGaji` decimal(8,2) DEFAULT 0 COMMENT 'Multiplier Gaji',
  `decNilaiTHR` decimal(15,2) DEFAULT NULL COMMENT 'Nominal THR',
  `vcKeterangan` varchar(255) DEFAULT NULL COMMENT 'Keterangan',
  `dtCreate` datetime DEFAULT NULL,
  `dtChange` datetime DEFAULT NULL,
  PRIMARY KEY (`dtTanggalTHR`,`vcNik`,`vcAgama`),
  KEY `idx_nik` (`vcNik`),
  KEY `idx_divisi` (`vcKodeDivisi`),
  KEY `idx_group` (`vcGroupPegawai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### **Update Tabel: `m_divisi`**

```sql
ALTER TABLE `m_divisi` 
ADD COLUMN `vcHrGaManager` varchar(100) DEFAULT NULL COMMENT 'Nama HR&GA Manager' AFTER `vcPlantManager`,
ADD COLUMN `vcSeniorFinanceManager` varchar(100) DEFAULT NULL COMMENT 'Nama Senior Finance Manager' AFTER `vcHrGaManager`,
ADD COLUMN `vcGmBackOffice` varchar(100) DEFAULT NULL COMMENT 'Nama GM Back Office' AFTER `vcSeniorFinanceManager`;
```

---

## ⚠️ Catatan Penting

### **1. Database:**
- Pastikan backup database dilakukan sebelum migration
- Migration akan menambah 2 tabel baru dan 3 kolom baru di `m_divisi`
- Tidak ada perubahan data existing, hanya struktur

### **2. Permissions:**
- Pastikan user web server (www-data) memiliki permission untuk read/write file
- Pastikan Laravel storage dan cache writable

### **3. Performance:**
- Query sudah dioptimasi dengan index yang sesuai
- Eager loading digunakan untuk menghindari N+1 query

### **4. Rollback:**
- Jika ada masalah, restore database dari backup
- Restore file dari backup folder
- Clear cache setelah rollback

### **5. Testing:**
- Test semua fitur di environment staging dulu jika ada
- Pastikan semua permission user sudah sesuai
- Verifikasi tidak ada conflict dengan fitur existing

---

## 🔗 Related Documents

- **Manual Deploy Kolom Keterangan:** `DEPLOY_KOLOM_KETERANGAN_BROWSE_ABSENSI_2026_01_12.md`

---

## 📞 Support

Jika ada masalah saat deployment:
1. Cek log Laravel: `storage/logs/laravel.log`
2. Cek log web server: `/var/log/nginx/error.log` atau `/var/log/apache2/error.log`
3. Cek status migrations: `php artisan migrate:status`
4. Cek routes: `php artisan route:list`

---

**Dokumen ini dibuat pada:** 5 Februari 2026  
**Versi:** 1.0  
**Author:** Development Team














