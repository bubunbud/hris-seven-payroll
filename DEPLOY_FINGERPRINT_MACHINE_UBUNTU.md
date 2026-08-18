# Panduan Deployment: Modul Mesin Fingerprint ke Ubuntu Server

**Tanggal:** Juni 2026
**Modul:** Master Mesin Fingerprint + Tarik Data Fingerprint (penarik log absensi dari mesin ZK/ZKTeco)
**Menu:** Settings → Master Mesin Fingerprint, Settings → Tarik Data Fingerprint
**Status:** Siap deploy ke production

---

## Ringkasan fitur

Modul menarik **log absensi** langsung dari mesin fingerprint **Solution (engine ZK/ZKTeco)** via **TCP/IP port 4370**, lalu menyimpannya ke tabel `t_absen`.

| Item | Keterangan |
|------|------------|
| Koneksi | TCP/IP langsung ke mesin, port 4370 (engine ZK) |
| Multi mesin | Pilih beberapa mesin sekaligus untuk satu tarikan |
| Penentuan jam | Field **Type**: `0` = masuk (jam paling awal), `1` = pulang (jam paling akhir) per NIK + tanggal |
| PIN mesin | = `m_karyawan.Nik` (tanpa mapping tambahan) |
| Dry run | Preview log mentah + preview agregasi sebelum disimpan |
| Aturan simpan | Hanya `dtJamMasuk` & `dtJamKeluar`; update hanya mengisi kolom jam yang **masih kosong** (tidak overwrite) |
| Shift malam | Belum ditangani (agregasi per tanggal kalender) |

---

## Prasyarat penting di server

### 1. Ekstensi PHP `sockets` (WAJIB)

Library ZK memerlukan fungsi `socket_create()`. Tanpa ekstensi ini muncul error:
`Call to undefined function ...socket_create()`

> **Kompatibilitas:** library `0mithun/php-zkteco` butuh **PHP ^8.0**, jadi PHP **8.1 / 8.2 / 8.3** semuanya didukung.

```bash
# Cek versi PHP dulu
php -v

# Pasang sockets sesuai versi PHP yang aktif (otomatis)
PHPV=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
sudo apt-get update
sudo apt-get install -y php${PHPV}-sockets

# Verifikasi
php -m | grep sockets

# Restart web server (sesuaikan versi, contoh PHP 8.1)
sudo systemctl restart php8.1-fpm     # atau php8.2-fpm / php8.3-fpm
sudo systemctl restart apache2        # atau nginx

# Lihat daftar service PHP-FPM yang tersedia jika ragu
systemctl list-units --type=service | grep php
```

**Contoh untuk PHP 8.1 (mis. `PHP 8.1.2-1ubuntu2.22`):**

```bash
sudo apt-get install -y php8.1-sockets
php -m | grep sockets
sudo systemctl restart php8.1-fpm
```

### 2. Jaringan ke mesin

Server Ubuntu **harus** bisa menjangkau IP mesin di port 4370.

```bash
# Uji konektivitas (contoh mesin Gedung Utama)
nc -zv 192.168.29.9 4370
# atau
telnet 192.168.29.9 4370
```

Jika gagal: cek VLAN/route/firewall antara server dan jaringan mesin (192.168.29.x / 192.168.30.x).

### 3. Cache driver

Preview menyimpan batch sementara via `Cache` (TTL 60 menit). Cache driver default `file` sudah cukup. Pastikan `storage/framework/cache` writable.

---

## Daftar file

### File baru

```
database/migrations/2026_06_25_100000_create_m_mesin_fingerprint_table.php
app/Models/MesinFingerprint.php
database/seeders/MesinFingerprintSeeder.php
app/Services/ZkTecoFingerprintService.php
app/Http/Controllers/MesinFingerprintController.php
app/Http/Controllers/TarikDataFingerprintController.php
resources/views/mesin-fingerprint/index.blade.php
resources/views/tarik-data-fingerprint/index.blade.php
```

### File update

```
composer.json                                    ← dependency 0mithun/php-zkteco
composer.lock                                    ← lockfile
routes/web.php                                   ← route + middleware permission
resources/views/layouts/app.blade.php            ← menu sidebar Settings
resources/views/absen/layouts/app.blade.php      ← menu sidebar Settings (layout absen)
database/seeders/UpdatePermissionsSeeder.php     ← permission baru
```

### Dependency Composer

```
0mithun/php-zkteco (v1.3.5)
```

---

## Langkah deployment ke Ubuntu

### Step 1: Backup

```bash
ssh user@server-ip
cd /var/www/html/hris-seven-payroll
mysqldump -u [db_user] -p [db_name] > backup_before_fingerprint_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Pasang ekstensi sockets

(lihat Prasyarat 1 di atas — wajib sebelum modul dipakai)

### Step 3: Copy file ke server

#### Opsi A: Git

```bash
cd /var/www/html/hris-seven-payroll
git pull origin main
composer install --no-dev --optimize-autoloader
```

#### Opsi B: SCP dari Windows (PowerShell)

```powershell
cd C:\xampp\htdocs\hris-seven-payroll

# Migration, Model, Seeder
scp database/migrations/2026_06_25_100000_create_m_mesin_fingerprint_table.php user@server:/var/www/html/hris-seven-payroll/database/migrations/
scp app/Models/MesinFingerprint.php user@server:/var/www/html/hris-seven-payroll/app/Models/
scp database/seeders/MesinFingerprintSeeder.php user@server:/var/www/html/hris-seven-payroll/database/seeders/
scp database/seeders/UpdatePermissionsSeeder.php user@server:/var/www/html/hris-seven-payroll/database/seeders/

# Service & Controllers
scp app/Services/ZkTecoFingerprintService.php user@server:/var/www/html/hris-seven-payroll/app/Services/
scp app/Http/Controllers/MesinFingerprintController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp app/Http/Controllers/TarikDataFingerprintController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Views — buat folder dulu
ssh user@server "mkdir -p /var/www/html/hris-seven-payroll/resources/views/mesin-fingerprint /var/www/html/hris-seven-payroll/resources/views/tarik-data-fingerprint"
scp resources/views/mesin-fingerprint/index.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/mesin-fingerprint/
scp resources/views/tarik-data-fingerprint/index.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/tarik-data-fingerprint/

# Routes, sidebar, composer
scp routes/web.php user@server:/var/www/html/hris-seven-payroll/routes/
scp resources/views/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/layouts/
scp resources/views/absen/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/absen/layouts/
scp composer.json user@server:/var/www/html/hris-seven-payroll/
scp composer.lock user@server:/var/www/html/hris-seven-payroll/
```

Lalu pasang dependency di server:

```bash
cd /var/www/html/hris-seven-payroll
composer install --no-dev --optimize-autoloader
```

> Jika tidak boleh `composer install` di server, jalankan `composer require 0mithun/php-zkteco` atau copy folder `vendor/0mithun/php-zkteco` manual lalu `composer dump-autoload`.

### Step 4: Migrasi database

```bash
cd /var/www/html/hris-seven-payroll
php artisan migrate --path=database/migrations/2026_06_25_100000_create_m_mesin_fingerprint_table.php --force
```

### Step 5: Seeder permission + data mesin awal

```bash
php artisan db:seed --class=UpdatePermissionsSeeder --force
php artisan db:seed --class=MesinFingerprintSeeder --force
```

Output permission yang diharapkan:

```
✓ Permission 'View Master Mesin Fingerprint' berhasil ditambahkan
✓ Permission 'View Tarik Data Fingerprint' berhasil ditambahkan
Role Admin: permission view-mesin-fingerprint dilampirkan (jika belum).
Role Admin: permission view-tarik-data-fingerprint dilampirkan (jika belum).
```

`MesinFingerprintSeeder` membuat data awal (jika belum ada):

| Nama | Tipe | IP | Port |
|------|------|-----|------|
| Gedung Utama | X302-S | 192.168.29.9 | 4370 |
| Prod1.1 | X100-C | 192.168.30.10 | 4370 |

> Mesin lain bisa ditambah manual lewat menu **Master Mesin Fingerprint**.

### Step 6: Permission file

```bash
cd /var/www/html/hris-seven-payroll
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

sudo chmod 644 app/Services/ZkTecoFingerprintService.php
sudo chmod 644 app/Http/Controllers/MesinFingerprintController.php
sudo chmod 644 app/Http/Controllers/TarikDataFingerprintController.php
sudo chmod 644 app/Models/MesinFingerprint.php
sudo chmod 644 resources/views/mesin-fingerprint/index.blade.php
sudo chmod 644 resources/views/tarik-data-fingerprint/index.blade.php
```

### Step 7: Clear & rebuild cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 8: Verifikasi route

```bash
php artisan route:list | grep fingerprint
```

Output yang diharapkan:

```
GET|HEAD   mesin-fingerprint .......................... mesin-fingerprint.index
POST       mesin-fingerprint .......................... mesin-fingerprint.store
GET|HEAD   mesin-fingerprint/{id} ..................... mesin-fingerprint.show
PUT        mesin-fingerprint/{id} ..................... mesin-fingerprint.update
DELETE     mesin-fingerprint/{id} ..................... mesin-fingerprint.destroy
POST       mesin-fingerprint/{id}/test-connection ..... mesin-fingerprint.test-connection
GET|HEAD   tarik-data-fingerprint ..................... tarik-data-fingerprint.index
POST       tarik-data-fingerprint/pull ................ tarik-data-fingerprint.pull
POST       tarik-data-fingerprint/save ................ tarik-data-fingerprint.save
```

---

## Penyesuaian timeout (penting untuk data banyak)

Library ZK menarik **seluruh** log dari mesin lalu memfilter di PHP, jadi periode panjang bisa lama. Controller sudah memanggil `set_time_limit(600)`, tetapi jika server mengabaikannya, naikkan di tingkat server:

**PHP-FPM (`php.ini` atau pool):**

```ini
max_execution_time = 600
```

**Nginx (`/etc/nginx/...`):**

```nginx
fastcgi_read_timeout 600;
```

**Apache (`httpd.conf` / vhost):**

```apache
Timeout 600
```

Restart service setelah perubahan. Saran operasional: tarik per **rentang pendek** (1–3 hari) atau per mesin agar cepat dan stabil.

---

## Testing checklist

### Master Mesin Fingerprint
- [ ] Menu muncul di Settings
- [ ] Daftar mesin tampil (Gedung Utama, Prod1.1)
- [ ] Tambah / edit / hapus mesin berfungsi
- [ ] **Test Koneksi** (ikon plug) → sukses ke IP mesin

### Tarik Data Fingerprint
- [ ] Pilih mesin + periode → **Pull Logs** → log mentah tampil (kolom State & Type)
- [ ] Preview agregasi: Jam Masuk (type 0 paling awal), Jam Pulang (type 1 paling akhir)
- [ ] **Save to Database** → data masuk ke `t_absen`
- [ ] Record dengan jam sudah terisi di DB → dilewati (tidak overwrite)

### Verifikasi DB

```sql
SELECT dtTanggal, vcNik, dtJamMasuk, dtJamKeluar
FROM t_absen
WHERE dtTanggal = '2026-06-24'
ORDER BY vcNik
LIMIT 20;
```

---

## Troubleshooting

### `Call to undefined function ...socket_create()`
Ekstensi `sockets` belum aktif. Lihat Prasyarat 1. Pasang `php-sockets` lalu restart PHP-FPM/Apache.

### Test koneksi gagal / timeout
- Cek jaringan: `nc -zv [ip] 4370`
- Pastikan server satu jaringan/route dengan mesin
- Cek firewall outbound port 4370

### `Maximum execution time exceeded`
Naikkan timeout PHP-FPM + Nginx/Apache (lihat bagian timeout). Atau tarik periode lebih pendek.

### Jam Masuk kosong, Jam Pulang terisi (atau sebaliknya)
Field penentu masuk/pulang adalah kolom **Type** (`type_raw`), bukan `state`. Modul sudah memakai `type_raw`. Jika mesin baru berperilaku beda, cek nilai State/Type di preview log mentah untuk scan pagi vs sore.

### Menu tidak muncul
```bash
php artisan db:seed --class=UpdatePermissionsSeeder --force
php artisan view:clear
```
Pastikan role user punya `view-mesin-fingerprint` / `view-tarik-data-fingerprint` atau `view-settings`.

### Class ZKTeco not found
```bash
composer install --no-dev --optimize-autoloader
composer dump-autoload
```

---

## Catatan teknis

| Aspek | Nilai |
|-------|-------|
| Library | `0mithun/php-zkteco` v1.3.5 |
| Protocol | TCP, port 4370 |
| Tabel baru | `m_mesin_fingerprint` |
| Comm Key | default 0 (tanpa password), bisa diisi per mesin |
| Penentu masuk/pulang | field **Type** (`type_raw`): 0 = masuk, 1 = pulang |
| PIN mesin | = `m_karyawan.Nik` |
| Permission | `view-mesin-fingerprint`, `view-tarik-data-fingerprint` |

---

## Checklist final deployment

- [ ] Ekstensi `php-sockets` terpasang & PHP-FPM/Apache restart
- [ ] Server bisa akses mesin (port 4370)
- [ ] Semua file ter-copy
- [ ] `composer install` (paket `0mithun/php-zkteco` ada)
- [ ] Migrasi `m_mesin_fingerprint` sukses
- [ ] Seeder permission + mesin awal dijalankan
- [ ] Permission di-assign ke role yang perlu
- [ ] Cache di-clear & di-rebuild
- [ ] Route `fingerprint` terdaftar
- [ ] Timeout server dinaikkan (jika perlu)
- [ ] Test koneksi + pull + save berhasil

---

## Deployment record

| Field | Isi |
|-------|-----|
| Deployment Date | _______________ |
| Deployed By | _______________ |
| Server | _______________ |
| Status | ⬜ Success ⬜ Failed |
| Notes | _______________ |

---

**Last updated:** Juni 2026
