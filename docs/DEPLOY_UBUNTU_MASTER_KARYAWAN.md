# Manual deploy ke Ubuntu — pembaruan Master Data Karyawan

Dokumen ini merangkum langkah **update deployment** untuk fitur berikut pada server Ubuntu (Laravel / PHP-FPM / Nginx atau Apache):

1. **Cetak biodata karyawan** (`biodata-cetak`)
2. **Tab Mutasi** + skema tabel **`t_mutasi`** (dan migrasi penyesuaian kolom)
3. **Tab Catatan Karyawan** + tabel **`t_karyawan_catatan`**

Pastikan versi kode di server sudah memuat perubahan pada `app/Http/Controllers/KaryawanController.php`, `routes/web.php`, view `resources/views/master/karyawan/`, `resources/views/master/karyawan/biodata-cetak.blade.php`, serta file migrasi di bawah.

---

## 1. Prasyarat & cadangan

- Akses SSH ke server, user dengan hak deploy (biasanya ke folder aplikasi).
- **Backup database** aplikasi (minimal dump MySQL/MariaDB) sebelum migrasi atau import.
- **Backup folder aplikasi** atau arsipkan salinan file sebelum menimpa, agar mudah rollback.

Contoh backup database penuh (sesuaikan user, nama DB, dan path):

```bash
mysqldump -u DB_USER -p NAMA_DATABASE > ~/backup-hris-$(date +%Y%m%d_%H%M).sql
```

---

## 2. Deploy kode aplikasi

Pilih **salah satu** metode: **Git** (disarankan jika repositori sama) atau **salin file/folder manual** (tanpa Git).

### 2.1 Opsi A — Git (pull di server)

```bash
cd /var/www/hris-seven-payroll   # sesuaikan path project
git fetch origin
git checkout main                  # atau branch release yang dipakai
git pull origin main
```

Instal dependensi PHP jika ada perubahan `composer.json` / `composer.lock`:

```bash
composer install --no-dev --optimize-autoloader
```

### 2.2 Opsi B — Salin file/folder secara manual (lokal → server)

Gunakan bila deploy **tidak** lewat Git (misalnya dari laptop Windows/XAMPP ke VPS), dengan **SCP**, **SFTP** (FileZilla, WinSCP), **rsync**, atau **ZIP** lalu ekstrak di server.

**Hal yang perlu diperhatikan**

- **Jangan** menimpa seluruh project tanpa sengaja menghapus `.env` server, folder `storage/` berisi data upload production, atau `vendor/` jika Anda tidak akan menjalankan `composer install` lagi.
- Setelah menyalin file PHP/view, di server tetap jalankan **`composer install`** jika `composer.json` / `composer.lock` ikut berubah.
- Folder berikut **biasanya tidak** perlu disalin dari lokal ke production (server sudah punya versi sendiri): `vendor/`, `node_modules/`, `.git/`, file `.env`.

**Daftar path kode yang relevan dengan fitur Master Karyawan (minimal)**

| Path relatif ke root project | Keterangan |
|------------------------------|------------|
| `app/Http/Controllers/KaryawanController.php` | Logika biodata, mutasi, catatan |
| `routes/web.php` | Rute `karyawan`, mutasi, catatan, biodata-cetak |
| `resources/views/master/karyawan/index.blade.php` | UI master karyawan + tab |
| `resources/views/master/karyawan/biodata-cetak.blade.php` | Halaman cetak biodata |
| `resources/views/absen/master/karyawan/index.blade.php` | Mirror UI jika modul absen memakai halaman sama |
| `database/migrations/2026_04_04_100000_create_t_mutasi_table.php` | Migrasi `t_mutasi` |
| `database/migrations/2026_04_04_100001_add_vcjabatan_vcfilesk_to_t_mutasi_table.php` | Penyesuaian kolom `t_mutasi` |
| `database/migrations/2026_04_02_140000_create_t_karyawan_catatan_table.php` | Migrasi `t_karyawan_catatan` |

**Contoh salin satu folder view dengan `scp` (dari mesin lokal Linux/macOS; di Windows bisa pakai WinSCP atau WSL)**

```bash
# Ganti USER, HOST, dan path tujuan di server
scp -r ./resources/views/master/karyawan USER@HOST:/var/www/hris-seven-payroll/resources/views/master/

scp ./app/Http/Controllers/KaryawanController.php USER@HOST:/var/www/hris-seven-payroll/app/Http/Controllers/

scp ./routes/web.php USER@HOST:/var/www/hris-seven-payroll/routes/
```

**Contoh `rsync` (efisien, bisa exclude)**

```bash
rsync -avz --progress \
  ./app/Http/Controllers/KaryawanController.php \
  ./routes/web.php \
  ./resources/views/master/karyawan/ \
  USER@HOST:/var/www/hris-seven-payroll/
```

Sesuaikan path sumber (`./`) dengan folder project di mesin Anda.

**Setelah file tersalin**

```bash
cd /var/www/hris-seven-payroll
composer install --no-dev --optimize-autoloader
```

---

## 3. Pembaruan database

Pilih **salah satu** atau kombinasi: **(A) migrasi Laravel** untuk membuat/memperbarui struktur tabel di server, dan/atau **(B) export/import SQL manual** untuk menyalin **struktur** dan/atau **isi** tabel dari lokal ke server.

### 3.1 Opsi A — Migrasi Laravel (`php artisan migrate`)

Migrasi yang relevan dengan fitur ini:

| File migrasi | Keterangan |
|--------------|------------|
| `2026_04_04_100000_create_t_mutasi_table.php` | Membuat **`t_mutasi`** (PK gabungan `nik` + `NoSK`). **Skip** jika tabel sudah ada. |
| `2026_04_04_100001_add_vcjabatan_vcfilesk_to_t_mutasi_table.php` | Menambah **`vcJabatan`** & **`vcFileSK`** jika belum ada. |
| `2026_04_02_140000_create_t_karyawan_catatan_table.php` | Membuat **`t_karyawan_catatan`**. **Skip** jika tabel sudah ada. |

```bash
cd /var/www/hris-seven-payroll
php artisan migrate --force
```

`--force` wajib di lingkungan **production**.

**Catatan**

- Jika **`t_mutasi` sudah ada** di server, migrasi pembuatan akan dilewati; migrasi penambahan kolom tetap menambah kolom yang belum ada.
- File migrasi harus **sudah tersalin** ke server (lihat bagian 2.2) agar Artisan bisa menjalankannya.

### 3.2 Opsi B — Salin tabel secara manual (SQL dari lokal ke server)

Digunakan bila Anda ingin:

- Membuat struktur tabel **tanpa** lewat Artisan (misalnya DBA menjalankan SQL langsung), atau
- Menyalin **data** dari database lokal (development) ke server (staging/production), atau
- Menggabungkan: struktur dari migrasi di server, lalu **hanya** mengimpor data tertentu.

**Tabel yang terkait fitur ini**

| Tabel | Keterangan singkat |
|-------|---------------------|
| `t_mutasi` | Riwayat mutasi (PK: `nik`, `NoSK`) |
| `t_karyawan_catatan` | Catatan HR / SP / penghargaan (relasi `karyawan_nik` → `m_karyawan.Nik`) |

**3.2.1 Export di mesin lokal (hanya struktur, tanpa data)**

Berguna untuk menyalin definisi tabel ke server lalu import dengan mysql client (hindari duplikasi data antar lingkungan jika tidak diperlukan).

```bash
mysqldump -u LOCAL_USER -p --no-data NAMA_DATABASE_LOCAL t_mutasi t_karyawan_catatan > ~/struktur-master-karyawan-tables.sql
```

**3.2.2 Export di mesin lokal (struktur + data)**

Hati-hati: data lokal akan **menimpa atau duplikat** jika diimpor mentah ke server yang sudah berisi data. Diskusikan dengan tim; biasanya untuk **staging** atau **seed awal** saja.

```bash
mysqldump -u LOCAL_USER -p NAMA_DATABASE_LOCAL t_mutasi t_karyawan_catatan > ~/data-master-karyawan-tables.sql
```

**3.2.3 Import di server Ubuntu**

1. Unggah file `.sql` ke server (scp/sftp).
2. **Backup** database server terlebih dahulu (lihat bagian 1).
3. Jalankan import (sesuaikan user DB server):

```bash
mysql -u DB_USER -p NAMA_DATABASE_SERVER < ~/data-master-karyawan-tables.sql
```

**Peringatan penting**

- **Encoding**: pastikan charset/collation konsisten (misalnya `utf8mb4`) agar tidak rusak karakter Indonesia.
- **Foreign key / urutan**: jika ada constraint ke `m_karyawan`, pastikan NIK yang direferensikan **sudah ada** di server sebelum mengimpor baris di `t_mutasi` / `t_karyawan_catatan`.
- **Primary key bentrok**: import data ke server yang **sudah punya** baris dengan `NoSK` / `id` sama dapat gagal atau memerlukan `INSERT ... ON DUPLICATE` — lebih aman impor ke DB kosong atau gunakan mode **hanya struktur** lalu isi data lewat aplikasi.
- **Auto increment**: tabel `t_karyawan_catatan` memakai `id` bigint; setelah import besar, kadang perlu menyesuaikan `AUTO_INCREMENT` (opsional, konsultasi DBA).

**3.2.4 Alternatif: salin lewat phpMyAdmin / MySQL Workbench**

- **Export** tabel `t_mutasi` dan/atau `t_karyawan_catatan` dari lokal (SQL).
- Di server, **Import** file SQL ke database target dengan antarmuka yang sama.
- Tetap berlaku peringatan backup dan bentrok data di atas.

---

## 4. Storage publik & symlink lampiran

Aplikasi menyimpan file di `storage/app/public/` dan mengaksesnya lewat URL `/storage/...` setelah symlink.

```bash
cd /var/www/hris-seven-payroll
php artisan storage:link
```

Pastikan user proses web server (mis. `www-data`) bisa **menulis** ke `storage/` dan `bootstrap/cache/`:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
```

### Folder upload yang dipakai fitur ini

| Fitur | Path (relatif `storage/app/public/`) | URL publik |
|--------|----------------------------------------|------------|
| Foto karyawan | `photos/` | `/storage/photos/...` |
| Dokumen SK mutasi | `mutasi_sk/` | `/storage/mutasi_sk/...` |
| Lampiran catatan | `karyawan_catatan/` | `/storage/karyawan_catatan/...` |

**Menyalin berkas upload secara manual (lokal → server)**

Jika di lokal sudah ada file di `storage/app/public/mutasi_sk/` atau `karyawan_catatan/`, Anda bisa menyalin folder tersebut ke server **ke path yang sama** (setelah `storage:link`, berkas tetap di bawah `storage/app/public/...`).

Contoh dengan `scp` (sesuaikan path):

```bash
scp -r ./storage/app/public/mutasi_sk USER@HOST:/var/www/hris-seven-payroll/storage/app/public/
scp -r ./storage/app/public/karyawan_catatan USER@HOST:/var/www/hris-seven-payroll/storage/app/public/
```

Lalu set pemilik/izin lagi agar web server bisa membaca (dan menulis untuk upload baru):

```bash
sudo chown -R www-data:www-data /var/www/hris-seven-payroll/storage
```

---

## 5. Optimasi cache (production)

```bash
cd /var/www/hris-seven-payroll
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Bersihkan cache jika ada masalah:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Restart PHP-FPM (sesuaikan versi PHP):

```bash
sudo systemctl restart php8.2-fpm
```

---

## 6. Web server & izin

- Document root mengarah ke **`public/`** Laravel.
- Sesuaikan batas unggah (Nginx `client_max_body_size`; PHP `upload_max_filesize`, `post_max_size`).

---

## 7. Verifikasi fungsi setelah deploy

| No | Fitur | Cara cek singkat |
|----|--------|------------------|
| 1 | **Cetak biodata** | Master Karyawan → pilih karyawan → cetak biodata → ada section Mutasi, Pelatihan, **Catatan Karyawan**. |
| 2 | **Tab Mutasi** | CRUD + unggah SK; unduh dokumen. |
| 3 | **Tab Catatan Karyawan** | CRUD + lampiran; salin data karyawan (jika dipakai). |

```bash
php artisan route:list --path=karyawan
```

Pastikan ada rute: `biodata-cetak`, `mutasi`, `catatan-karyawan`, `copy-mutasi`, `copy-catatan-karyawan`.

---

## 8. Ringkasan file / area kode utama

- **Controller:** `app/Http/Controllers/KaryawanController.php`
- **Rute:** `routes/web.php`
- **View master:** `resources/views/master/karyawan/index.blade.php` (dan mirror `resources/views/absen/master/karyawan/index.blade.php` jika dipakai)
- **View cetak:** `resources/views/master/karyawan/biodata-cetak.blade.php`
- **Migrasi:** `database/migrations/2026_04_04_100000_create_t_mutasi_table.php`, `2026_04_04_100001_add_vcjabatan_vcfilesk_to_t_mutasi_table.php`, `2026_04_02_140000_create_t_karyawan_catatan_table.php`

---

## 9. Rollback (darurat)

- **Kode:** kembalikan file dari backup atau checkout Git ke commit sebelumnya; lalu `composer install` jika perlu.
- **Database:** restore dari dump backup **sebelum** migrasi/import, atau koordinasi DBA untuk rollback terkontrol.
- **`php artisan migrate:rollback`** hanya jika Anda paham batch migrasi terakhir dan dampaknya di production.

---

*Dokumen ini disusun berdasarkan struktur aplikasi HRIS Seven Payroll (biodata cetak, mutasi, catatan karyawan). Sesuaikan path server, user database, user web server, dan versi PHP dengan environment Ubuntu Anda.*
