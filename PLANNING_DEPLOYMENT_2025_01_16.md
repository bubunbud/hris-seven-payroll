# 📋 Planning Deployment HRIS Seven Payroll
**Tanggal:** 16 Januari 2025  
**Status:** Setelah Restore Backup

---

## ✅ VERIFIKASI FILE YANG SUDAH ADA

Berdasarkan hasil verifikasi, berikut adalah status file-file yang sudah ada di folder kerja:

### 1. ✅ Fitur Pelatihan - Master Karyawan

**Status:** ✅ **LENGKAP** - Semua file sudah ada

#### File yang Sudah Ada:
- ✅ **Migration (3 file):**
  - `database/migrations/2025_12_15_000001_create_t_pelatihan_table.php`
  - `database/migrations/2025_12_15_000002_add_lokasi_to_t_pelatihan_table.php`
  - `database/migrations/2025_12_15_000003_add_tg_selesai_to_t_pelatihan_table.php`

- ✅ **Model:**
  - `app/Models/Pelatihan.php`

- ✅ **Controller:**
  - `app/Http/Controllers/KaryawanController.php` (sudah ada method pelatihan)

- ✅ **Routes:**
  - `routes/web.php` (sudah ada route pelatihan di dalam permission group)

- ✅ **View:**
  - `resources/views/master/karyawan/index.blade.php` (sudah ada tab Pelatihan)

**Catatan:** Fitur ini siap untuk di-deploy ke server.

---

### 2. ✅ Fitur Browse Tidak Absen (Alpha)

**Status:** ✅ **LENGKAP** - Semua file sudah ada

#### File yang Sudah Ada:
- ✅ **Controller:**
  - `app/Http/Controllers/BrowseTidakAbsenController.php`

- ✅ **Routes:**
  - `routes/web.php` (sudah ada route `browse-tidak-absen.index`)

- ✅ **View:**
  - `resources/views/absen/tidak-absen/index.blade.php`

- ✅ **Layout:**
  - `resources/views/layouts/app.blade.php` (sudah ada menu "Browse Tidak Absen")

**Catatan:** Fitur ini siap untuk di-deploy ke server. Tidak ada perubahan database/migration.

---

### 3. ✅ Fitur Tarik Data Tidak Masuk & Tarik Data Hutang Piutang

**Status:** ✅ **LENGKAP** - Semua file sudah ada

#### File yang Sudah Ada:
- ✅ **Controller (2 file):**
  - `app/Http/Controllers/TarikDataTidakMasukController.php`
  - `app/Http/Controllers/TarikDataHutangPiutangController.php`

- ✅ **Routes:**
  - `routes/web.php` (sudah ada route untuk kedua fitur)

- ✅ **View (2 file):**
  - `resources/views/tarik-data-tidak-masuk/index.blade.php`
  - `resources/views/tarik-data-hutang-piutang/index.blade.php`

- ✅ **Layout:**
  - `resources/views/layouts/app.blade.php` (sudah ada menu di Settings)

**Catatan:** Fitur ini siap untuk di-deploy ke server. Tidak ada perubahan database/migration.

---

## 🚀 RENCANA DEPLOYMENT

### **Prioritas 1: Fitur Pelatihan (PENTING - Ada Migration Database)**

Fitur ini memerlukan migration database, jadi harus di-deploy dengan hati-hati.

#### Langkah Deployment:

1. **Backup Database (WAJIB!)**
   ```bash
   # Di server Ubuntu
   cd /var/www/html/hris-seven-payroll
   mysqldump -u root -proot123 hris_seven > ~/backup_hris_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Upload File ke Server:**
   - 3 file migration (000001, 000002, 000003)
   - Model Pelatihan.php
   - Controller KaryawanController.php (update)
   - Routes web.php (update)
   - View index.blade.php (update)

3. **Run Migration:**
   ```bash
   php artisan migrate --force
   ```

4. **Clear Cache:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   ```

5. **Verifikasi:**
   - Cek tabel `t_pelatihan` sudah dibuat
   - Cek route pelatihan sudah terdaftar
   - Test di browser: Tab Pelatihan muncul dan berfungsi

**Dokumen Referensi:** `DEPLOY_PELATIHAN_MASTER_KARYAWAN.md` dan `DEPLOY_PELATIHAN_CHECKLIST.txt`

---

### **Prioritas 2: Fitur Browse Tidak Absen**

Fitur ini tidak memerlukan migration, hanya update file PHP dan Blade.

#### Langkah Deployment:

1. **Upload File ke Server:**
   - Controller BrowseTidakAbsenController.php
   - View index.blade.php
   - Routes web.php (update)
   - Layout app.blade.php (update)

2. **Set Permissions:**
   ```bash
   sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
   sudo chmod -R 755 /var/www/html/hris-seven-payroll
   ```

3. **Clear Cache:**
   ```bash
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Verifikasi:**
   - Cek route sudah terdaftar
   - Test menu "Browse Tidak Absen" di browser

**Dokumen Referensi:** `DEPLOY_BROWSE_TIDAK_ABSEN.md`

---

### **Prioritas 3: Fitur Tarik Data Tidak Masuk & Hutang Piutang**

Fitur ini tidak memerlukan migration, hanya update file PHP dan Blade.

#### Langkah Deployment:

1. **Upload File ke Server:**
   - 2 Controller (TarikDataTidakMasukController, TarikDataHutangPiutangController)
   - 2 View (index.blade.php untuk masing-masing)
   - Routes web.php (update)
   - Layout app.blade.php (update)

2. **Set Permissions:**
   ```bash
   sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
   sudo chmod -R 755 /var/www/html/hris-seven-payroll
   ```

3. **Clear Cache:**
   ```bash
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Verifikasi:**
   - Cek route sudah terdaftar
   - Test menu di Settings → Tarik Data Tidak Masuk & Tarik Data Hutang Piutang

**Dokumen Referensi:** `DEPLOY_TARIK_DATA_TIDAK_MASUK_HUTANG_PIUTANG.md` dan `DEPLOY_TARIK_DATA_TIDAK_MASUK_HUTANG_PIUTANG_CHECKLIST.txt`

---

## 📝 CHECKLIST DEPLOYMENT LENGKAP

### Pre-Deployment
- [ ] Backup database server (WAJIB untuk fitur Pelatihan)
- [ ] Backup .env server
- [ ] Backup file penting (controller, view) jika perlu

### Deployment Fitur Pelatihan
- [ ] Upload 3 file migration
- [ ] Upload Model Pelatihan.php
- [ ] Upload Controller KaryawanController.php (update)
- [ ] Upload Routes web.php (update)
- [ ] Upload View index.blade.php (update)
- [ ] Set ownership & permissions
- [ ] Run migration (`php artisan migrate --force`)
- [ ] Verifikasi tabel `t_pelatihan` sudah dibuat
- [ ] Clear cache
- [ ] Test di browser

### Deployment Fitur Browse Tidak Absen
- [ ] Upload Controller BrowseTidakAbsenController.php
- [ ] Upload View index.blade.php
- [ ] Upload Routes web.php (update)
- [ ] Upload Layout app.blade.php (update)
- [ ] Set ownership & permissions
- [ ] Clear cache
- [ ] Test di browser

### Deployment Fitur Tarik Data
- [ ] Upload 2 Controller (TarikDataTidakMasukController, TarikDataHutangPiutangController)
- [ ] Upload 2 View (index.blade.php untuk masing-masing)
- [ ] Upload Routes web.php (update)
- [ ] Upload Layout app.blade.php (update)
- [ ] Set ownership & permissions
- [ ] Clear cache
- [ ] Test di browser

### Post-Deployment
- [ ] Verifikasi semua route sudah terdaftar
- [ ] Test semua fitur di browser
- [ ] Cek log Laravel untuk error
- [ ] Cek browser console untuk error JavaScript
- [ ] Dokumentasi deployment selesai

---

## ⚠️ CATATAN PENTING

1. **Urutan Deployment:**
   - Deploy fitur Pelatihan **PERTAMA** karena memerlukan migration database
   - Setelah itu deploy fitur lainnya (tidak ada urutan khusus)

2. **Backup Database:**
   - **WAJIB** untuk fitur Pelatihan karena ada perubahan struktur database
   - Opsional untuk fitur lainnya (tidak ada perubahan database)

3. **Permission:**
   - Pastikan user memiliki permission yang sesuai:
     - `view-master-data` untuk fitur Pelatihan
     - `view-absensi` untuk fitur Browse Tidak Absen
     - `view-settings` untuk fitur Tarik Data

4. **Cache:**
   - **WAJIB** clear cache setelah setiap deployment
   - Rebuild cache untuk production (opsional)

5. **Testing:**
   - Test semua fitur setelah deployment
   - Cek log untuk error
   - Verifikasi data tersimpan dengan benar

---

## 📞 INFORMASI SERVER

- **Server IP:** 192.168.10.40
- **Domain:** http://hr.abncorp.lan
- **Lokasi Aplikasi:** /var/www/html/hris-seven-payroll
- **Database:** hris_seven
- **User:** superadmin / www-data

---

## 📚 DOKUMEN REFERENSI

1. **Fitur Pelatihan:**
   - `DEPLOY_PELATIHAN_MASTER_KARYAWAN.md`
   - `DEPLOY_PELATIHAN_CHECKLIST.txt`

2. **Fitur Browse Tidak Absen:**
   - `DEPLOY_BROWSE_TIDAK_ABSEN.md`
   - `DEPLOY_BROWSE_TIDAK_ABSEN_CHECKLIST.txt`

3. **Fitur Tarik Data:**
   - `DEPLOY_TARIK_DATA_TIDAK_MASUK_HUTANG_PIUTANG.md`
   - `DEPLOY_TARIK_DATA_TIDAK_MASUK_HUTANG_PIUTANG_CHECKLIST.txt`

---

## ✅ KESIMPULAN

**Status:** Semua file fitur sudah lengkap di folder kerja lokal. Siap untuk di-deploy ke server.

**Rekomendasi:**
1. Deploy fitur Pelatihan terlebih dahulu (ada migration)
2. Setelah itu deploy fitur lainnya secara bersamaan atau bertahap
3. Pastikan backup database dilakukan sebelum deployment fitur Pelatihan
4. Test semua fitur setelah deployment

**Selamat Deploy! 🚀**

