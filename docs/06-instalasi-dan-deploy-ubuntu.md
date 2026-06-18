# 6. Instalasi, pemasangan & deploy Ubuntu

Ringkasan praktik agar aplikasi **HRIS Seven Payroll** dapat dipakai oleh banyak pengguna lewat browser. Detail langkah instal server dan variabel sensitif Anda sudah banyak didokumentasikan di panduan besar di akar repo.

---

## 6.1 Prasyarat lingkungan

| Komponen | Catatan |
|----------|---------|
| **Ubuntu LTS** (20.04 / 22.04 dll.) disarankan | Update paket rutin (`apt update`). |
| **PHP 8.1+** + ekstensi: `mbstring`, `xml`, `mysql`, `curl`, `gd`, `zip`, `intl`, **`bcmath`** | Wajib untuk Laravel & angka payroll. |
| **Composer** 2.x | Instal dependency PHP. |
| **MySQL / MariaDB** | Database utama aplikasi. |
| **Apache + mod_php** atau **Nginx + PHP-FPM** | Sesuai kebijakan TI Anda. |

---

## 6.2 Instalasi pertama (greenfield)

Ikuti secara utuh dokumentasi utama di root proyek:

- **`DEPLOY_UBUNTU.md`** — panduan urut: instal PHP, Composer, DB, cloning/unggah kode ke `/var/www/html/...`, `composer install`, salin `.env`, `php artisan key:generate`, migrasi, permission `storage`/`bootstrap/cache`, konfigurasi virtual host, dsb.

**Penting keamanan:** Jangan commit **password produksi asli** ke Git. Salin pola dari `.env.example` kemudian isi kredensial hanya di server.

---

## 6.3 Setelah aplikasi hidup pertama kali

Urutan umum (dari dalam folder aplikasi):

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env          # atau salin dari backup aman
php artisan key:generate
php artisan migrate --force     # atau impor schema jika Anda migrasi dari dump DBA
php artisan db:seed             # sekali jika butuh RolePermission awal; hati-hati di produksi

php artisan storage:link       # jika Anda memakai link publik untuk file upload tertentu
php artisan optimize:clear
sudo chown -R www-data:www-data storage bootstrap/cache
```

Sesuaikan user web (`www-data` vs `nginx` vs custom).

---

## 6.4 Deploy berkala (update kode tanpa reinstall server)

Gunakan salah satu pola:

### A. Git pull (disarankan jika ada repositori)

```bash
cd /var/www/html/hris-seven-payroll
git pull origin <branch-produksi>
composer install --no-dev -o
php artisan migrate --force
php artisan db:seed --class=UpdatePermissionsSeeder   # bila ada permission baru dokumentasi sprint
php artisan optimize:clear
```

### B. Unggah manual (FTP/WinSCP)

- Timpa hanya berkas atau folder berubah; **pertahankan** `.env`.
- Jalankan lagi: `composer dump-autoload -o`, `migrate`, `optimize:clear`.
- Dokumentasi contoh copy file per-fitur ada di beberapa berkas **`DEPLOY_*.md`** dan **`docs/DEPLOY_*.md`**.

---

## 6.5 Fitur baru “Login Aktif & Riwayat”

Lihat panduan pendek **`DEPLOY_LOGIN_AKTIF_UBUNTU.md`** di akar proyek untuk daftar berkas baru + dua tabel migrate.

---

## 6.6 Konfigurasi integrasi feeder (HRIS API)

Pada `.env` server produksi sesuaikan (nama variabel contoh dari `config/hris_api.php`):

- `HRIS_API_BASE_URL`
- `HRIS_API_USERNAME`
- `HRIS_API_PASSWORD`
- `HRIS_API_TIMEOUT`

Uji konektivitas dari server (firewall outbound, SSL sertifikat).

---

## 6.7 Cadangan & rollback

| Artefak | Rekomendasi |
|---------|-------------|
| **Database** | `mysqldump` harian sebelum `migrate` besar; simpan off-server. |
| **Kode** | Tag Git release / arsip tar sebelum deploy sensitif. |
| **Rollback** | Pulihkan dump DB + checkout commit sebelumnya; jangan hapus migrasi yang sudah jalan di produksi tanpa prosedur DBA. |

---

## 6.8 Checklist produksi singkat

- [ ] HTTPS (TLS) pada domain publik
- [ ] `APP_DEBUG=false`, `APP_ENV=production` di `.env`
- [ ] Izin file `storage`, `bootstrap/cache`
- [ ] Cron (jika nanti ada scheduler Laravel) — saat ini banyak proses manual/batch user
- [ ] Monitoring log error & ruang disk

Untuk detail server contoh (IP, user DB contoh) yang pernah dipakai internal, rujuk teks asli di **`DEPLOY_UBUNTU.md`** — **ganti** dengan kredensial environment Anda sebelum produksi.
