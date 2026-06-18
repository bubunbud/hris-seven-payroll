# Manual Deploy: Modul Form BPD (Biaya Perjalanan Dinas)

**Tanggal:** 14 Februari 2026  
**Modul:** Form Biaya Perjalanan Dinas (BPD) - Modul Baru  
**Server:** Ubuntu

---

## 📋 Ringkasan Perubahan

Modul baru **Form Biaya Perjalanan Dinas (BPD)** yang terintegrasi dengan modul **Form Perjalanan Dinas**:

1. **Database Tables Baru:**
   - `t_biaya_perjalanan_dinas_header` - Header BPD
   - `t_biaya_perjalanan_dinas_detail` - Detail biaya per kategori

2. **Fitur Utama:**
   - CRUD Form BPD dengan referensi No. RPD
   - Auto-fill data dari Form Perjalanan Dinas
   - Auto-generate No. BPD
   - Auto-generate Counter Detail
   - Konversi angka ke terbilang (Bahasa Indonesia)
   - Auto-calculate total pengeluaran dan kekurangan/kelebihan
   - Print Form BPD dengan layout lengkap

3. **Integrasi:**
   - Link ke Form Perjalanan Dinas via No. RPD
   - Auto-fill: Pemberi Tugas, Karyawan yang Ditugaskan, Tanggal Dinas
   - Relasi database dengan foreign key

---

## 📁 File yang Perlu Di-Deploy

### 1. Migration Files (2 file)
- `database/migrations/2026_02_13_000001_create_t_biaya_perjalanan_dinas_header_table.php`
- `database/migrations/2026_02_13_000002_create_t_biaya_perjalanan_dinas_detail_table.php`

### 2. Models (2 file)
- `app/Models/BiayaPerjalananDinasHeader.php`
- `app/Models/BiayaPerjalananDinasDetail.php`

### 3. Controller (1 file)
- `app/Http/Controllers/BiayaPerjalananDinasController.php`

### 4. Views (2 file)
- `resources/views/biaya-perjalanan-dinas/index.blade.php`
- `resources/views/biaya-perjalanan-dinas/print.blade.php`

### 5. Routes Update (1 file)
- `routes/web.php` - Tambah route group untuk BPD

### 6. Sidebar Update (1 file)
- `resources/views/layouts/app.blade.php` - Tambah menu "Form Biaya Perjalanan Dinas"

### 7. Model Update (1 file)
- `app/Models/PerjalananDinasHeader.php` - Tambah relasi `biayaPerjalananDinas()`

**Total: 10 file**

---

## 🚀 Langkah-Langkah Deploy

### **Langkah 1: Backup Database dan File Existing**

```bash
# Login ke server Ubuntu
ssh user@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup database (opsional, tapi sangat disarankan)
mysqldump -u username -p hris_seven > backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql

# Backup file yang akan diubah (jika ada)
# Routes
cp routes/web.php routes/web.php.backup_$(date +%Y%m%d_%H%M%S)

# Sidebar
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.backup_$(date +%Y%m%d_%H%M%S)

# Model PerjalananDinasHeader (jika sudah ada)
cp app/Models/PerjalananDinasHeader.php app/Models/PerjalananDinasHeader.php.backup_$(date +%Y%m%d_%H%M%S)
```

### **Langkah 2: Upload File Baru**

**Opsi A: Menggunakan SCP (dari Windows/Local)**

```bash
# Dari terminal lokal (Windows PowerShell atau Git Bash)
# Pastikan sudah ada SSH key atau siapkan password

# Upload Migration Files
scp database/migrations/2026_02_13_000001_create_t_biaya_perjalanan_dinas_header_table.php user@192.168.10.40:/var/www/html/hris-seven-payroll/database/migrations/
scp database/migrations/2026_02_13_000002_create_t_biaya_perjalanan_dinas_detail_table.php user@192.168.10.40:/var/www/html/hris-seven-payroll/database/migrations/

# Upload Models
scp app/Models/BiayaPerjalananDinasHeader.php user@192.168.10.40:/var/www/html/hris-seven-payroll/app/Models/
scp app/Models/BiayaPerjalananDinasDetail.php user@192.168.10.40:/var/www/html/hris-seven-payroll/app/Models/

# Upload Controller
scp app/Http/Controllers/BiayaPerjalananDinasController.php user@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Upload Views (buat direktori dulu jika belum ada)
ssh user@192.168.10.40 "mkdir -p /var/www/html/hris-seven-payroll/resources/views/biaya-perjalanan-dinas"
scp resources/views/biaya-perjalanan-dinas/index.blade.php user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/biaya-perjalanan-dinas/
scp resources/views/biaya-perjalanan-dinas/print.blade.php user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/biaya-perjalanan-dinas/

# Upload Routes (update)
scp routes/web.php user@192.168.10.40:/var/www/html/hris-seven-payroll/routes/

# Upload Sidebar (update)
scp resources/views/layouts/app.blade.php user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/layouts/

# Upload Model Update
scp app/Models/PerjalananDinasHeader.php user@192.168.10.40:/var/www/html/hris-seven-payroll/app/Models/
```

**Opsi B: Menggunakan Git (jika menggunakan version control)**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll
git pull origin main  # atau branch yang sesuai
```

### **Langkah 3: Set Permission File**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Set ownership (sesuaikan user:group dengan konfigurasi server)
sudo chown -R www-data:www-data .

# Set permission untuk direktori
find . -type d -exec chmod 755 {} \;

# Set permission untuk file
find . -type f -exec chmod 644 {} \;

# Set permission khusus untuk storage dan cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### **Langkah 4: Jalankan Migration**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Jalankan migration untuk membuat tabel baru
php artisan migrate

# Output yang diharapkan:
# Migrating: 2026_02_13_000001_create_t_biaya_perjalanan_dinas_header_table
# Migrated:  2026_02_13_000001_create_t_biaya_perjalanan_dinas_header_table (XX.XXms)
# Migrating: 2026_02_13_000002_create_t_biaya_perjalanan_dinas_detail_table
# Migrated:  2026_02_13_000002_create_t_biaya_perjalanan_dinas_detail_table (XX.XXms)
```

**⚠️ Catatan:** Jika ada error "table already exists", bisa skip migration tersebut:
```bash
php artisan migrate --pretend  # Preview saja
php artisan migrate --force     # Force jika diperlukan
```

### **Langkah 5: Clear Cache**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize (opsional, untuk production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Langkah 6: Verifikasi Route**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Cek route BPD sudah terdaftar
php artisan route:list --name=biaya-perjalanan-dinas

# Output yang diharapkan:
# GET|HEAD  biaya-perjalanan-dinas .................... biaya-perjalanan-dinas.index
# POST      biaya-perjalanan-dinas .................... biaya-perjalanan-dinas.store
# GET|HEAD  biaya-perjalanan-dinas/{id} ............... biaya-perjalanan-dinas.show
# PUT       biaya-perjalanan-dinas/{id} ............... biaya-perjalanan-dinas.update
# DELETE    biaya-perjalanan-dinas/{id} ............... biaya-perjalanan-dinas.destroy
# GET|HEAD  biaya-perjalanan-dinas/{id}/print ......... biaya-perjalanan-dinas.print
# GET|HEAD  biaya-perjalanan-dinas/get-rpd-data/{noRpd} biaya-perjalanan-dinas.get-rpd-data
# GET|HEAD  biaya-perjalanan-dinas/convert-terbilang/{number} biaya-perjalanan-dinas.convert-terbilang
```

### **Langkah 7: Verifikasi Database**

```bash
# Login ke MySQL
mysql -u username -p hris_seven

# Cek tabel sudah dibuat
SHOW TABLES LIKE 't_biaya_perjalanan_dinas%';

# Output yang diharapkan:
# +----------------------------------------------+
# | Tables_in_hris_seven (t_biaya_perjalanan_dinas%) |
# +----------------------------------------------+
# | t_biaya_perjalanan_dinas_header              |
# | t_biaya_perjalanan_dinas_detail              |
# +----------------------------------------------+

# Cek struktur tabel header
DESCRIBE t_biaya_perjalanan_dinas_header;

# Cek struktur tabel detail
DESCRIBE t_biaya_perjalanan_dinas_detail;

# Cek foreign key
SHOW CREATE TABLE t_biaya_perjalanan_dinas_header;
SHOW CREATE TABLE t_biaya_perjalanan_dinas_detail;

# Exit MySQL
EXIT;
```

### **Langkah 8: Set Permission untuk User (Opsional)**

Jika menggunakan sistem permission berbasis role, pastikan user yang akan mengakses modul BPD memiliki permission:
- `view-absensi` atau `view-perjalanan-dinas`

**Cek permission di database:**
```sql
-- Cek permission yang ada
SELECT * FROM permissions WHERE name LIKE '%perjalanan%' OR name LIKE '%absensi%';

-- Assign permission ke role (contoh: role 'admin')
-- (Sesuaikan dengan sistem permission yang digunakan)
```

### **Langkah 9: Test Aplikasi**

1. **Akses Menu:**
   - Login ke aplikasi
   - Buka menu **Absensi** → **Form Biaya Perjalanan Dinas**
   - Pastikan menu muncul dan bisa diakses

2. **Test CRUD:**
   - **Tambah Data:** Klik tombol "Tambah", pilih No. RPD, pastikan auto-fill bekerja
   - **Simpan:** Isi form lengkap, klik "Simpan", pastikan data tersimpan
   - **Edit:** Klik edit pada data yang sudah ada, pastikan data ter-load dengan benar
   - **Update:** Ubah data, klik "Update", pastikan perubahan tersimpan
   - **Hapus:** Hapus data test, pastikan data terhapus

3. **Test Auto-Fill:**
   - Pilih No. RPD yang sudah ada di Form Perjalanan Dinas
   - Pastikan field berikut terisi otomatis:
     - Pemberi Tugas
     - Karyawan yang Ditugaskan (untuk "Melaporkan")
     - Tanggal Dinas

4. **Test Auto-Generate:**
   - Pastikan No. BPD ter-generate otomatis saat simpan
   - Pastikan Counter Detail ter-generate otomatis saat tambah baris detail

5. **Test Terbilang:**
   - Isi field "Kasbon Nilai" atau "Total Pengeluaran"
   - Pastikan field "Terbilang" terisi otomatis dengan konversi angka ke kata

6. **Test Print:**
   - Klik tombol "Print" pada data yang sudah ada
   - Pastikan layout print sesuai dengan yang diharapkan
   - Pastikan semua field ter-render dengan benar:
     - No. BPD, No. RPD
     - Nama Penerima Tugas
     - Tanggal Dinas
     - Pemberi Tugas
     - Kasbon (Nilai & Terbilang)
     - Signature Dept. Keuangan
     - Laporan Biaya (tabel detail)
     - Summary
     - Laporan Singkat
     - Otorisasi

7. **Test Relasi:**
   - Pastikan BPD yang dibuat ter-link dengan RPD yang dipilih
   - Pastikan foreign key constraint bekerja (tidak bisa hapus RPD yang sudah punya BPD)

---

## 🔍 Troubleshooting

### **Error: Table doesn't exist**
```bash
# Pastikan migration sudah dijalankan
php artisan migrate:status

# Jika migration belum jalan, jalankan:
php artisan migrate
```

### **Error: Class not found**
```bash
# Clear cache dan optimize
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

### **Error: Permission denied**
```bash
# Set permission ulang
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
chmod -R 775 storage bootstrap/cache
```

### **Error: Route not found**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Verifikasi route
php artisan route:list --name=biaya-perjalanan-dinas
```

### **Error: Foreign key constraint**
- Pastikan tabel `t_perjalanan_dinas_header` sudah ada
- Pastikan data RPD yang dipilih benar-benar ada di database
- Pastikan foreign key constraint di migration sudah benar

### **Menu tidak muncul**
- Pastikan user memiliki permission `view-absensi` atau `view-perjalanan-dinas`
- Clear cache: `php artisan cache:clear` dan `php artisan view:clear`
- Refresh browser (Ctrl+F5)

### **Auto-fill tidak bekerja**
- Cek browser console untuk error JavaScript
- Pastikan route `get-rpd-data` bisa diakses
- Cek network tab di browser devtools untuk melihat response API

### **Print layout tidak sesuai**
- Clear browser cache
- Pastikan file `print.blade.php` sudah ter-upload dengan benar
- Cek CSS print media query

---

## ✅ Checklist Deploy

- [ ] Backup database
- [ ] Backup file existing (routes, sidebar, model)
- [ ] Upload 2 migration files
- [ ] Upload 2 model files (BPD Header & Detail)
- [ ] Upload 1 controller file
- [ ] Upload 2 view files (index & print)
- [ ] Update routes/web.php
- [ ] Update layouts/app.blade.php (sidebar)
- [ ] Update PerjalananDinasHeader.php (relasi)
- [ ] Set permission file & direktori
- [ ] Jalankan migration
- [ ] Clear semua cache
- [ ] Verifikasi route terdaftar
- [ ] Verifikasi tabel database dibuat
- [ ] Test menu muncul di sidebar
- [ ] Test tambah data BPD
- [ ] Test auto-fill dari RPD
- [ ] Test auto-generate No. BPD & Counter
- [ ] Test konversi terbilang
- [ ] Test edit & update data
- [ ] Test hapus data
- [ ] Test print form BPD
- [ ] Test relasi dengan RPD

---

## 📝 Catatan Tambahan

1. **No. BPD Format:** `BPD-YYYYMM-XXXX` (contoh: BPD-202602-0001)
2. **Counter Detail Format:** `BPD-YYYYMM-XXXX-DET-YYY` (contoh: BPD-202602-0001-DET-001)
3. **Foreign Key:** BPD terikat dengan RPD, pastikan RPD sudah ada sebelum membuat BPD
4. **Terbilang:** Menggunakan helper function `convertToTerbilang()` di controller
5. **Permission:** Modul BPD menggunakan permission yang sama dengan Perjalanan Dinas (`view-absensi` atau `view-perjalanan-dinas`)

---

## 📞 Support

Jika ada masalah saat deploy, pastikan:
1. Semua file sudah ter-upload dengan benar
2. Migration sudah dijalankan
3. Cache sudah di-clear
4. Permission file sudah benar
5. Database connection sudah benar
6. User memiliki permission yang sesuai

**Selamat Deploy! 🚀**




