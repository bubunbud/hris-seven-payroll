# Panduan Deploy Fitur Pelatihan - Master Karyawan

## 📋 Ringkasan Fitur

Fitur **Tab Pelatihan** pada halaman Master Karyawan memungkinkan:
- Menambah riwayat pelatihan karyawan
- Mengedit data pelatihan
- Menghapus data pelatihan
- Copy data pelatihan saat copy data karyawan
- Menampilkan data pelatihan dalam tabel

**Tabel Database:** `t_pelatihan`  
**Composite Key:** `Nik + nm_pelatihan` (unique)

---

## 📁 FILE YANG PERLU DI-UPLOAD

### A. MIGRATION FILES (3 file) ⚠️ PENTING

**Lokasi Server:** `/var/www/html/hris-seven-payroll/database/migrations/`

1. **`2025_12_15_000001_create_t_pelatihan_table.php`**
   - Create table `t_pelatihan` dengan kolom dasar
   - **Urutan:** Jalankan pertama

2. **`2025_12_15_000002_add_lokasi_to_t_pelatihan_table.php`**
   - Tambah kolom `lokasi` (nullable, string 150)
   - **Urutan:** Jalankan kedua

3. **`2025_12_15_000003_add_tg_selesai_to_t_pelatihan_table.php`**
   - Tambah kolom `tg_selesai` (nullable, date)
   - **Urutan:** Jalankan ketiga

**Catatan:** Pastikan urutan migration sesuai timestamp (000001 → 000002 → 000003)

---

### B. MODEL (1 file baru)

**Lokasi Server:** `/var/www/html/hris-seven-payroll/app/Models/`

1. **`Pelatihan.php`** ⭐ FILE BARU
   - Model untuk tabel `t_pelatihan`
   - Composite key (tidak pakai auto-increment)
   - Relasi `belongsTo` ke `Karyawan`

---

### C. CONTROLLER (1 file - UPDATE)

**Lokasi Server:** `/var/www/html/hris-seven-payroll/app/Http/Controllers/`

1. **`KaryawanController.php`** ⚠️ UPDATE
   - Tambah import: `use App\Models\Pelatihan;`
   - Tambah method:
     - `getPelatihan($nik)` - GET data pelatihan
     - `addPelatihan($request, $nik)` - POST tambah pelatihan
     - `updatePelatihan($request, $nik, $nm_pelatihan_lama)` - PUT update pelatihan
     - `deletePelatihan($nik, $nm_pelatihan)` - DELETE hapus pelatihan
     - `copyPelatihan($request)` - POST copy batch pelatihan
   - Update method `getKaryawanForCopy()` - tambah data pelatihan

**Catatan:** File ini sudah ada, hanya perlu di-update dengan method baru

---

### D. ROUTES (1 file - UPDATE)

**Lokasi Server:** `/var/www/html/hris-seven-payroll/routes/`

1. **`web.php`** ⚠️ UPDATE
   - Tambah route di dalam group `Route::middleware(['permission:view-master-data'])`:
     ```php
     // Pelatihan
     Route::get('karyawan/{id}/pelatihan', [KaryawanController::class, 'getPelatihan']);
     Route::post('karyawan/{nik}/pelatihan', [KaryawanController::class, 'addPelatihan']);
     Route::put('karyawan/{nik}/pelatihan/{nm_pelatihan}', [KaryawanController::class, 'updatePelatihan']);
     Route::delete('karyawan/{nik}/pelatihan/{nm_pelatihan}', [KaryawanController::class, 'deletePelatihan']);
     Route::post('karyawan/copy-pelatihan', [KaryawanController::class, 'copyPelatihan'])->name('karyawan.copy-pelatihan');
     ```

**Catatan:** Route harus berada di dalam permission group yang sama dengan route karyawan lainnya

---

### E. VIEW (1 file - UPDATE)

**Lokasi Server:** `/var/www/html/hris-seven-payroll/resources/views/master/karyawan/`

1. **`index.blade.php`** ⚠️ UPDATE
   - Tambah Tab "Pelatihan" di tab navigation
   - Tambah tabel pelatihan dengan kolom:
     - Nama Pelatihan, Penyelenggara, Lokasi, Tgl Pelatihan, Tgl Selesai, Sertifikat, Keterangan, Aksi
   - Tambah Modal Add/Edit Pelatihan
   - Tambah JavaScript functions:
     - `loadPelatihanData(nik)`
     - `updatePelatihanTable()`
     - `editPelatihanMember(index)`
     - `removePelatihanMember(index)`
     - `savePelatihanMembers(nikLama, nikBaru)`
     - `resetPelatihanModalState()`
   - Update function `populateFormFromCopy()` - handle pelatihan data
   - Update function `saveCopiedData()` - include pelatihan

**Catatan:** File ini besar, pastikan semua perubahan terkait pelatihan sudah termasuk

---

## 🚀 LANGKAH DEPLOYMENT

### STEP 1: Backup (WAJIB!)

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Backup database
mysqldump -u root -proot123 hris_seven > ~/backup_hris_$(date +%Y%m%d_%H%M%S).sql

# Backup .env
cp .env ~/backup_env_$(date +%Y%m%d_%H%M%S).txt

# Backup file penting (opsional tapi disarankan)
cp -r app/Http/Controllers/KaryawanController.php ~/KaryawanController_backup_$(date +%Y%m%d_%H%M%S).php
cp -r resources/views/master/karyawan/index.blade.php ~/index_backup_$(date +%Y%m%d_%H%M%S).blade.php
```

---

### STEP 2: Upload File dari Localhost ke Server

**Dari Windows (PowerShell/CMD):**

```powershell
# Navigate ke folder project
cd C:\xampp\htdocs\hris-seven-payroll

# Upload migration files
scp database/migrations/2025_12_15_000001_create_t_pelatihan_table.php user@192.168.10.40:/tmp/hris-pelatihan/
scp database/migrations/2025_12_15_000002_add_lokasi_to_t_pelatihan_table.php user@192.168.10.40:/tmp/hris-pelatihan/
scp database/migrations/2025_12_15_000003_add_tg_selesai_to_t_pelatihan_table.php user@192.168.10.40:/tmp/hris-pelatihan/

# Upload model
scp app/Models/Pelatihan.php user@192.168.10.40:/tmp/hris-pelatihan/

# Upload controller (update)
scp app/Http/Controllers/KaryawanController.php user@192.168.10.40:/tmp/hris-pelatihan/

# Upload routes (update)
scp routes/web.php user@192.168.10.40:/tmp/hris-pelatihan/

# Upload view (update)
scp resources/views/master/karyawan/index.blade.php user@192.168.10.40:/tmp/hris-pelatihan/
```

**Atau upload semua sekaligus:**

```powershell
# Buat folder temporary di server
ssh user@192.168.10.40 "mkdir -p /tmp/hris-pelatihan"

# Upload semua file sekaligus
scp -r database/migrations/2025_12_15_00000*_pelatihan*.php user@192.168.10.40:/tmp/hris-pelatihan/
scp app/Models/Pelatihan.php user@192.168.10.40:/tmp/hris-pelatihan/
scp app/Http/Controllers/KaryawanController.php user@192.168.10.40:/tmp/hris-pelatihan/
scp routes/web.php user@192.168.10.40:/tmp/hris-pelatihan/
scp resources/views/master/karyawan/index.blade.php user@192.168.10.40:/tmp/hris-pelatihan/
```

---

### STEP 3: Copy File ke Lokasi Project di Server

**Di Server Ubuntu:**

```bash
# Login ke server
ssh user@192.168.10.40

# Navigate ke project directory
cd /var/www/html/hris-seven-payroll

# Copy migration files
sudo cp /tmp/hris-pelatihan/2025_12_15_000001_create_t_pelatihan_table.php database/migrations/
sudo cp /tmp/hris-pelatihan/2025_12_15_000002_add_lokasi_to_t_pelatihan_table.php database/migrations/
sudo cp /tmp/hris-pelatihan/2025_12_15_000003_add_tg_selesai_to_t_pelatihan_table.php database/migrations/

# Copy model
sudo cp /tmp/hris-pelatihan/Pelatihan.php app/Models/

# Copy controller (update)
sudo cp /tmp/hris-pelatihan/KaryawanController.php app/Http/Controllers/

# Copy routes (update)
sudo cp /tmp/hris-pelatihan/web.php routes/

# Copy view (update)
sudo cp /tmp/hris-pelatihan/index.blade.php resources/views/master/karyawan/

# Set ownership dan permissions
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 storage bootstrap/cache
```

---

### STEP 4: Run Migrations

```bash
cd /var/www/html/hris-seven-payroll

# Jalankan migrations (pastikan urutan benar)
php artisan migrate --force

# Verifikasi tabel sudah dibuat
php artisan tinker
# Di dalam tinker:
# >>> Schema::hasTable('t_pelatihan')
# >>> exit
```

**Catatan:** 
- Migration akan otomatis berjalan sesuai urutan timestamp
- Jika ada error "table already exists", cek apakah migration sudah pernah dijalankan sebelumnya
- Jika kolom sudah ada, migration akan skip (ada pengecekan `hasColumn`)

---

### STEP 5: Clear Cache

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild cache (opsional, untuk production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### STEP 6: Verifikasi

#### 6.1 Verifikasi Database

```bash
# Login ke MySQL
mysql -u root -proot123 hris_seven

# Cek struktur tabel
DESCRIBE t_pelatihan;

# Harus ada kolom:
# - id (bigint, auto increment)
# - Nik (varchar 24, indexed)
# - nm_pelatihan (varchar 150)
# - penyelenggara (varchar 150, nullable)
# - lokasi (varchar 150, nullable) ⭐
# - tg_pelatihan (date, nullable)
# - tg_selesai (date, nullable) ⭐
# - sertifikat (tinyint, default 0)
# - keterangan (text, nullable)
# - dtCreate, dtChange (datetime, nullable)
# - Unique key: uniq_pelatihan_nik_name (Nik + nm_pelatihan)

EXIT;
```

#### 6.2 Verifikasi Route

```bash
cd /var/www/html/hris-seven-payroll

# List semua route karyawan
php artisan route:list | grep pelatihan

# Harus muncul:
# GET|HEAD  karyawan/{id}/pelatihan
# POST      karyawan/{nik}/pelatihan
# PUT       karyawan/{nik}/pelatihan/{nm_pelatihan}
# DELETE    karyawan/{nik}/pelatihan/{nm_pelatihan}
# POST      karyawan/copy-pelatihan
```

#### 6.3 Verifikasi File

```bash
# Cek model
ls -la app/Models/Pelatihan.php

# Cek controller (cari method pelatihan)
grep -n "function.*Pelatihan" app/Http/Controllers/KaryawanController.php

# Cek routes
grep -n "pelatihan" routes/web.php

# Cek view (cari tab pelatihan)
grep -n "pelatihan" resources/views/master/karyawan/index.blade.php | head -5
```

#### 6.4 Test di Browser

1. **Login ke aplikasi:** `http://192.168.10.40` atau `http://hr.abncorp.lan`
2. **Buka Master Karyawan:** Menu → Master Data → Karyawan
3. **Pilih atau buat karyawan baru**
4. **Cek Tab Pelatihan:**
   - Tab "Pelatihan" harus muncul
   - Klik tab, harus muncul tabel kosong dengan pesan "Belum ada data pelatihan"
   - Klik "Tambah Pelatihan", modal harus muncul
5. **Test Tambah Data:**
   - Isi form: Nama Pelatihan (wajib), Penyelenggara, Lokasi, Tgl Pelatihan, Tgl Selesai, Sertifikat, Keterangan
   - Klik Simpan
   - Data harus muncul di tabel
6. **Test Edit:**
   - Klik dropdown Aksi → Edit
   - Ubah data, klik Update
   - Data harus ter-update
7. **Test Hapus:**
   - Klik dropdown Aksi → Hapus
   - Konfirmasi, data harus terhapus
8. **Test Copy Data:**
   - Pilih karyawan yang punya data pelatihan
   - Klik "Copy Data"
   - Pilih karyawan target
   - Data pelatihan harus ter-copy

---

## ⚠️ TROUBLESHOOTING

### Error: "Table t_pelatihan already exists"

**Solusi:**
```bash
# Cek apakah migration sudah dijalankan
php artisan migrate:status | grep pelatihan

# Jika sudah, skip migration atau rollback dulu
php artisan migrate:rollback --step=1
php artisan migrate
```

### Error: "Column lokasi already exists"

**Solusi:**
- Migration 000002 sudah dijalankan sebelumnya
- Skip migration ini atau hapus kolom dulu (tidak disarankan jika ada data)

### Error: "Route not found"

**Solusi:**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Verifikasi route
php artisan route:list | grep pelatihan
```

### Error: "Class Pelatihan not found"

**Solusi:**
```bash
# Clear autoload cache
composer dump-autoload

# Verifikasi file model
ls -la app/Models/Pelatihan.php
```

### Tab Pelatihan tidak muncul

**Solusi:**
1. Clear view cache: `php artisan view:clear`
2. Hard refresh browser (Ctrl+F5)
3. Cek JavaScript console untuk error
4. Verifikasi file `index.blade.php` sudah ter-update dengan tab pelatihan

### Data tidak tersimpan

**Solusi:**
1. Cek browser console untuk error JavaScript
2. Cek Laravel log: `tail -f storage/logs/laravel.log`
3. Verifikasi CSRF token
4. Cek permission user (harus punya `view-master-data`)

---

## ✅ CHECKLIST DEPLOYMENT

Gunakan checklist ini untuk memastikan semua langkah sudah dilakukan:

### Pre-Deployment
- [ ] Backup database sudah dilakukan
- [ ] Backup .env sudah dilakukan
- [ ] Backup file penting (controller, view) sudah dilakukan

### Upload Files
- [ ] Migration 000001 (create table) sudah di-upload
- [ ] Migration 000002 (add lokasi) sudah di-upload
- [ ] Migration 000003 (add tg_selesai) sudah di-upload
- [ ] Model Pelatihan.php sudah di-upload
- [ ] Controller KaryawanController.php sudah di-update
- [ ] Routes web.php sudah di-update
- [ ] View index.blade.php sudah di-update

### Server Setup
- [ ] File sudah di-copy ke lokasi project
- [ ] Ownership sudah di-set (www-data:www-data)
- [ ] Permissions sudah benar (755 untuk folder, 775 untuk storage)

### Database
- [ ] Migration sudah dijalankan (`php artisan migrate`)
- [ ] Tabel `t_pelatihan` sudah dibuat
- [ ] Kolom `lokasi` sudah ada
- [ ] Kolom `tg_selesai` sudah ada
- [ ] Unique key sudah dibuat

### Cache
- [ ] Config cache sudah di-clear
- [ ] Route cache sudah di-clear
- [ ] View cache sudah di-clear
- [ ] Cache sudah di-rebuild (opsional)

### Verification
- [ ] Route pelatihan sudah terdaftar
- [ ] Model Pelatihan bisa di-load
- [ ] Tab Pelatihan muncul di halaman Master Karyawan
- [ ] Form tambah pelatihan berfungsi
- [ ] Form edit pelatihan berfungsi
- [ ] Hapus pelatihan berfungsi
- [ ] Copy data pelatihan berfungsi

---

## 📝 CATATAN PENTING

1. **Urutan Migration:** Pastikan migration dijalankan sesuai urutan timestamp (000001 → 000002 → 000003)

2. **Composite Key:** Tabel `t_pelatihan` menggunakan composite key (Nik + nm_pelatihan), bukan auto-increment ID sebagai primary key

3. **URL Encoding:** Route update/delete menggunakan `urldecode()` untuk handle nama pelatihan yang mengandung spasi/karakter khusus

4. **Permission:** Route pelatihan berada di dalam permission group `view-master-data`, pastikan user memiliki permission ini

5. **Copy Data:** Fitur copy data pelatihan terintegrasi dengan fitur copy data karyawan, data pelatihan akan otomatis ter-copy saat copy data karyawan

6. **Normalisasi Data:** Frontend melakukan normalisasi field `Sertifikasi`/`sertifikat` dan `Keterangan`/`keterangan` saat load data

---

## 📞 SUPPORT

Jika ada masalah saat deployment, cek:
1. Laravel log: `storage/logs/laravel.log`
2. Apache error log: `/var/log/apache2/error.log`
3. Browser console untuk error JavaScript
4. Network tab di browser untuk error API

---

**Dokumen ini dibuat:** 2025-12-16  
**Fitur:** Tab Pelatihan - Master Karyawan  
**Versi:** 1.0

