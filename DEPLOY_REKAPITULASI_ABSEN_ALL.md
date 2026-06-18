# DEPLOY REKAPITULASI ABSENSI - Manual Update ke Ubuntu Server

## Tanggal Update

**04 Desember 2025**

## Ringkasan Perubahan

### 1. REKAPITULASI ABSENSI (Per Karyawan)

**Perubahan pada fitur yang sudah ada:**

-   Filter digabung menjadi satu kolom "Pencarian (NIK / Nama)" (bukan terpisah)
-   Tambah baris "Final Kehadiran" di tabel rekapitulasi
-   Tambah kolom Divisi di header informasi karyawan
-   Tambah fitur cetak/print dengan format landscape
-   Perbaikan layout header (Divisi, Departemen, Bagian dipindah ke baris kedua)

### 2. REKAPITULASI ABSEN ALL (Semua Karyawan)

**Fitur baru:**

-   Halaman rekapitulasi absensi untuk semua karyawan dalam format tabel Excel
-   Filter berdasarkan: Range Tanggal, Divisi, Departemen, Group Pegawai
-   Fitur cetak/print dengan format landscape
-   Perhitungan metrik absensi lengkap (S, I, A, IR, IO, CT, CM, PC, T, DT, TA, TW, H, <8, JHK, %H, %TW)

---

## LANGKAH-LANGKAH DEPLOYMENT

### 1. BACKUP (Opsional tapi Disarankan)

```bash
# Backup database (jika diperlukan)
mysqldump -u root -p hris_seven > backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql

# Backup file aplikasi (jika diperlukan)
cd /var/www/html
tar -czf backup_hris-seven-payroll_$(date +%Y%m%d_%H%M%S).tar.gz hris-seven-payroll/
```

---

### 2. COPY FILE-FILE BARU DAN UPDATE

#### A. Controller Baru (Rekapitulasi Absen All)

```bash
# Copy controller baru
scp app/Http/Controllers/RekapitulasiAbsenAllController.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/
```

**Atau manual copy:**

-   File: `app/Http/Controllers/RekapitulasiAbsenAllController.php`
-   Destination: `/var/www/html/hris-seven-payroll/app/Http/Controllers/`

#### B. Update Controller Rekapitulasi Absensi (Yang Sudah Ada)

```bash
# Update controller yang sudah ada
scp app/Http/Controllers/RekapitulasiAbsensiController.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/
```

**Atau manual copy:**

-   File: `app/Http/Controllers/RekapitulasiAbsensiController.php`
-   Destination: `/var/www/html/hris-seven-payroll/app/Http/Controllers/`

**Perubahan di RekapitulasiAbsensiController.php:**

-   Filter digabung menjadi satu kolom "search" (NIK/Nama)
-   Tambah method `print()` untuk cetak
-   Tambah baris "Final Kehadiran" di rekapitulasi

#### C. View Baru (Rekapitulasi Absen All)

```bash
# Buat direktori jika belum ada
ssh user@192.168.10.40 "mkdir -p /var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all"

# Copy view index
scp resources/views/absen/rekapitulasi-all/index.blade.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all/

# Copy view print
scp resources/views/absen/rekapitulasi-all/print.blade.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all/
```

**Atau manual copy:**

-   File: `resources/views/absen/rekapitulasi-all/index.blade.php`
-   File: `resources/views/absen/rekapitulasi-all/print.blade.php`
-   Destination: `/var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all/`

#### D. Update View Rekapitulasi Absensi (Yang Sudah Ada)

```bash
# Update view index
scp resources/views/absen/rekapitulasi/index.blade.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi/

# Copy view print (baru)
scp resources/views/absen/rekapitulasi/print.blade.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi/
```

**Atau manual copy:**

-   File: `resources/views/absen/rekapitulasi/index.blade.php` (UPDATE)
-   File: `resources/views/absen/rekapitulasi/print.blade.php` (BARU)
-   Destination: `/var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi/`

**Perubahan di index.blade.php:**

-   Filter digabung menjadi satu kolom "Pencarian (NIK / Nama)"
-   Tambah kolom Divisi di header
-   Layout header diubah (Divisi, Departemen, Bagian di baris kedua)
-   Tambah tombol "Cetak"

#### E. Update File yang Sudah Ada

**1. Update routes/web.php**

```bash
scp routes/web.php user@192.168.10.40:/var/www/html/hris-seven-payroll/routes/
```

**Atau manual copy:**

-   File: `routes/web.php`
-   Destination: `/var/www/html/hris-seven-payroll/routes/`

**Perubahan di routes/web.php:**

-   Tambah `use App\Http\Controllers\RekapitulasiAbsenAllController;`
-   Tambah route: `Route::get('absensi/rekapitulasi-all', ...)`
-   Tambah route: `Route::get('absensi/rekapitulasi-all/print', ...)`
-   Update route: `Route::get('absensi/rekapitulasi/print', ...)` (tambah route print untuk Rekapitulasi Absensi)

**2. Update layouts/app.blade.php**

```bash
scp resources/views/layouts/app.blade.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/layouts/
```

**Atau manual copy:**

-   File: `resources/views/layouts/app.blade.php`
-   Destination: `/var/www/html/hris-seven-payroll/resources/views/layouts/`

**Perubahan di layouts/app.blade.php:**

-   Tambah menu: `<li><a href="{{ route('rekapitulasi-absen-all.index') }}">Rekapitulasi Absen All</a></li>`

---

### 3. SET PERMISSIONS (Jika Diperlukan)

```bash
ssh user@192.168.10.40

# Set ownership
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Http/Controllers/RekapitulasiAbsenAllController.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all/

# Set permissions
sudo chmod -R 755 /var/www/html/hris-seven-payroll/app/Http/Controllers/RekapitulasiAbsenAllController.php
sudo chmod -R 755 /var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all/
```

---

### 4. CLEAR CACHE LARAVEL

```bash
ssh user@192.168.10.40
cd /var/www/html/hris-seven-payroll

# Clear semua cache
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Rebuild cache (opsional, untuk performa)
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

**PENTING:** Pastikan menjalankan command dengan `sudo -u www-data` untuk menghindari permission issues.

---

### 5. VERIFIKASI

#### A. Cek File Sudah Ter-copy

```bash
ssh user@192.168.10.40

# Cek controller
ls -la /var/www/html/hris-seven-payroll/app/Http/Controllers/RekapitulasiAbsenAllController.php

# Cek view
ls -la /var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all/

# Cek route sudah terdaftar
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan route:list | grep rekapitulasi-absen-all
```

#### B. Test di Browser

1. Login ke aplikasi: `http://192.168.10.40/` (atau sesuai URL server)
2. Buka menu **Absensi** → **Rekapitulasi Absen All**
3. Test filter:
    - Pilih range tanggal
    - Pilih Divisi (opsional)
    - Pilih Departemen (opsional)
    - Pilih Group Pegawai (opsional)
    - Klik "Preview"
4. Pastikan tabel muncul dengan data
5. Test tombol "Cetak" → pastikan halaman print terbuka

#### C. Cek Error Log (Jika Ada Masalah)

```bash
ssh user@192.168.10.40
tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

---

## CHECKLIST DEPLOYMENT

-   [ ] Backup database (opsional)
-   [ ] Copy `RekapitulasiAbsenAllController.php` ke server
-   [ ] Copy `index.blade.php` ke server
-   [ ] Copy `print.blade.php` ke server
-   [ ] Update `routes/web.php` di server
-   [ ] Update `layouts/app.blade.php` di server
-   [ ] Set permissions (jika diperlukan)
-   [ ] Clear Laravel cache
-   [ ] Verifikasi route terdaftar
-   [ ] Test halaman di browser
-   [ ] Test filter (tanggal, divisi, departemen, group)
-   [ ] Test tombol cetak/print
-   [ ] Cek error log (jika ada masalah)

---

## TROUBLESHOOTING

### Error: "Route [rekapitulasi-absen-all.index] not defined"

**Solusi:**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan route:cache
```

### Error: "Class RekapitulasiAbsenAllController not found"

**Solusi:**

1. Pastikan file controller sudah ter-copy dengan benar
2. Cek namespace di controller: `namespace App\Http\Controllers;`
3. Clear cache:

```bash
sudo -u www-data php artisan optimize:clear
```

### Error: "View [absen.rekapitulasi-all.index] not found"

**Solusi:**

1. Pastikan direktori dan file sudah ter-copy:

```bash
ls -la /var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all/
```

2. Clear view cache:

```bash
sudo -u www-data php artisan view:clear
```

### Error: Permission Denied

**Solusi:**

```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/
sudo chmod -R 755 /var/www/html/hris-seven-payroll/
```

### Tabel Tidak Muncul atau Data Kosong

**Kemungkinan:**

1. Tidak ada karyawan yang sesuai filter
2. Range tanggal tidak valid
3. Cek error log untuk detail

---

## FILE YANG PERLU DI-COPY

### File Baru (5 file)

1. `app/Http/Controllers/RekapitulasiAbsenAllController.php`
2. `resources/views/absen/rekapitulasi-all/index.blade.php`
3. `resources/views/absen/rekapitulasi-all/print.blade.php`
4. `resources/views/absen/rekapitulasi/print.blade.php` (untuk Rekapitulasi Absensi)

### File yang Diupdate (4 file)

1. `app/Http/Controllers/RekapitulasiAbsensiController.php` (update filter & tambah print)
2. `resources/views/absen/rekapitulasi/index.blade.php` (update filter & layout)
3. `routes/web.php` (tambah route baru)
4. `resources/views/layouts/app.blade.php` (tambah menu)

---

## CATATAN PENTING

1. **Tidak ada perubahan database** - Fitur ini hanya menambahkan halaman baru, tidak ada migration atau perubahan tabel.

2. **Cache harus di-clear** - Setelah copy file, WAJIB clear cache Laravel agar perubahan terdeteksi.

3. **Permission** - Pastikan file memiliki permission yang benar (755 untuk file, 755 untuk direktori).

4. **Route Cache** - Setelah update routes, clear dan rebuild route cache.

5. **Testing** - Setelah deploy, test semua fitur:
    - Filter tanggal
    - Filter divisi
    - Filter departemen
    - Filter group pegawai
    - Tombol cetak/print

---

## QUICK DEPLOYMENT (Ringkasan Singkat)

```bash
# 1. Copy controller baru
scp app/Http/Controllers/RekapitulasiAbsenAllController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# 2. Update controller yang sudah ada
scp app/Http/Controllers/RekapitulasiAbsensiController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# 3. Copy view baru (Rekapitulasi Absen All)
ssh user@server "mkdir -p /var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all"
scp resources/views/absen/rekapitulasi-all/index.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all/
scp resources/views/absen/rekapitulasi-all/print.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi-all/

# 4. Update view Rekapitulasi Absensi
scp resources/views/absen/rekapitulasi/index.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi/
scp resources/views/absen/rekapitulasi/print.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/absen/rekapitulasi/

# 5. Update routes dan layout
scp routes/web.php user@server:/var/www/html/hris-seven-payroll/routes/
scp resources/views/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/layouts/

# 6. SSH ke server dan clear cache
ssh user@server
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan route:cache
```

---

## SELESAI

Setelah semua langkah di atas selesai, fitur "Rekapitulasi Absen All" sudah siap digunakan di server Ubuntu.

Jika ada masalah, cek error log dan ikuti troubleshooting di atas.
