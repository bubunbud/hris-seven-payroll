# Panduan Deployment: Tarik Data Absensi API ke Ubuntu Server

**Tanggal:** Juni 2026  
**Modul:** Tarik Data Absensi API  
**Menu:** Settings → Tarik Data Absensi API  
**Status:** Siap deploy ke production

---

## Ringkasan fitur

Modul ini menarik **log absensi** dari API HRIS eksternal dan menyinkronkannya ke tabel lokal `t_absen`.

| Item | Keterangan |
|------|------------|
| Login API | `POST /v1/auth/login` |
| Data absensi | `GET /v1/management/attendances/logs` |
| Field yang disimpan | `date` → `dtTanggal`, `nik` → `vcNik`, `clock_in` → `dtJamMasuk`, `clock_out` → `dtJamKeluar` |
| **Tidak** ditarik | `note`, `shift` → **tidak** disimpan ke `vcketerangan` |
| Insert | Record baru (tanggal + NIK belum ada di `t_absen`) |
| Update | Hanya mengisi kolom jam yang **masih kosong** di database |
| Lewati | Jam sudah lengkap, NIK tidak di master, API tanpa jam, dll. |

**Tidak ada migration database** — hanya file aplikasi + permission baru.

---

## Prasyarat di server

Modul ini memakai infrastruktur API yang sama dengan modul feeder lain. Pastikan **sudah ada** di server (jika belum, copy juga):

```
app/Services/HrisApiHttpFactory.php
app/Services/HrApiOutboundInspector.php
config/hris_api.php          ← minimal key HRIS_API_* + attendance_logs_path
```

Jika modul **List Pengajuan Cuti/Izin API** sudah jalan di production, biasanya prasyarat di atas sudah terpenuhi.

---

## Daftar file yang harus di-copy / update

### 1. File baru (3 file)

```
app/Services/HrisApiAttendanceLogService.php
app/Http/Controllers/TarikDataAbsensiApiController.php
resources/views/tarik-data-absensi-api/index.blade.php
```

### 2. File update (6 file)

```
config/hris_api.php                              ← tambah attendance_logs_path
routes/web.php                                   ← route + middleware permission
resources/views/layouts/app.blade.php            ← menu sidebar Settings
resources/views/absen/layouts/app.blade.php      ← menu sidebar Settings (layout absen)
database/seeders/UpdatePermissionsSeeder.php     ← permission view-tarik-data-absensi-api
database/seeders/RolePermissionSeeder.php      ← opsional (fresh install saja)
```

**Total: 9 file** (7 jika `config/hris_api.php` dan seeder permission sudah terbaru)

---

## Langkah deployment ke Ubuntu

### Step 1: Backup (opsional)

```bash
ssh user@server-ip
cd /var/www/html/hris-seven-payroll

# Backup database (opsional — tidak ada perubahan struktur tabel)
mysqldump -u [db_user] -p [db_name] > backup_before_tarik_absensi_api_$(date +%Y%m%d_%H%M%S).sql
```

---

### Step 2: Copy file ke server

Ganti `user@server` dan path `/var/www/html/hris-seven-payroll` sesuai environment production.

#### Opsi A: SCP dari Windows (PowerShell)

```powershell
cd C:\xampp\htdocs\hris-seven-payroll

# Service & Controller (baru)
scp app/Services/HrisApiAttendanceLogService.php user@server:/var/www/html/hris-seven-payroll/app/Services/
scp app/Http/Controllers/TarikDataAbsensiApiController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# View — buat folder jika belum ada
ssh user@server "mkdir -p /var/www/html/hris-seven-payroll/resources/views/tarik-data-absensi-api"
scp resources/views/tarik-data-absensi-api/index.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/tarik-data-absensi-api/

# Config, routes, sidebar, seeder
scp config/hris_api.php user@server:/var/www/html/hris-seven-payroll/config/
scp routes/web.php user@server:/var/www/html/hris-seven-payroll/routes/
scp resources/views/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/layouts/
scp resources/views/absen/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/absen/layouts/
scp database/seeders/UpdatePermissionsSeeder.php user@server:/var/www/html/hris-seven-payroll/database/seeders/
```

**Jika prasyarat API belum ada di server**, copy juga:

```powershell
scp app/Services/HrisApiHttpFactory.php user@server:/var/www/html/hris-seven-payroll/app/Services/
scp app/Services/HrApiOutboundInspector.php user@server:/var/www/html/hris-seven-payroll/app/Services/
```

#### Opsi B: Git pull di server

```bash
cd /var/www/html/hris-seven-payroll
git pull origin main   # atau branch deploy Anda
```

#### Opsi C: SFTP / WinSCP / FileZilla

Upload semua file di atas ke path yang sama dengan struktur project Laravel.

---

### Step 3: Autoload (jika perlu)

```bash
cd /var/www/html/hris-seven-payroll
composer dump-autoload
```

---

### Step 4: Konfigurasi `.env`

```bash
nano /var/www/html/hris-seven-payroll/.env
```

Pastikan variabel berikut ada (sama dengan modul API feeder lain):

```env
HRIS_API_BASE_URL=https://hris-api.abadinusagroup.com
HRIS_API_USERNAME=superadmin
HRIS_API_PASSWORD=<password_api>
HRIS_API_TIMEOUT=60

# Opsional — default sudah benar jika tidak di-set:
# HRIS_API_ATTENDANCE_LOGS_PATH=/v1/management/attendances/logs
```

**SSL (jika cURL error 60):** lihat bagian Troubleshooting di dokumen `DEPLOY_LIST_PENGAJUAN_CUTI_IZIN_API_UBUNTU.md` atau set:

```env
HRIS_API_CURL_CA_BUNDLE=/var/www/html/hris-seven-payroll/storage/app/cacerts/cacert.pem
```

---

### Step 5: Permission file

```bash
cd /var/www/html/hris-seven-payroll

sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

sudo chmod 644 app/Services/HrisApiAttendanceLogService.php
sudo chmod 644 app/Http/Controllers/TarikDataAbsensiApiController.php
sudo chmod 644 resources/views/tarik-data-absensi-api/index.blade.php
```

---

### Step 6: Permission database (role user)

```bash
cd /var/www/html/hris-seven-payroll
php artisan db:seed --class=UpdatePermissionsSeeder
```

Output yang diharapkan:

```
✓ Permission 'View Tarik Data Absensi API' berhasil ditambahkan
Role Admin: permission view-tarik-data-absensi-api dilampirkan (jika belum).
```

**Assign ke role lain (jika perlu):**

1. Login sebagai admin
2. **Settings → Role & Permission**
3. Centang permission: `view-tarik-data-absensi-api`
4. Simpan

Atau cek manual di database:

```bash
mysql -u [user] -p [database] -e "SELECT id, name, slug FROM permissions WHERE slug = 'view-tarik-data-absensi-api';"
```

---

### Step 7: Clear & rebuild cache

```bash
cd /var/www/html/hris-seven-payroll

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Production (disarankan)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Restart PHP-FPM / Apache jika perlu:

```bash
sudo systemctl restart php8.2-fpm    # sesuaikan versi PHP
# atau
sudo systemctl restart apache2
```

---

### Step 8: Verifikasi route & file

```bash
cd /var/www/html/hris-seven-payroll

ls -la app/Services/HrisApiAttendanceLogService.php
ls -la app/Http/Controllers/TarikDataAbsensiApiController.php
ls -la resources/views/tarik-data-absensi-api/index.blade.php

php artisan route:list | grep tarik-data-absensi-api
```

Output yang diharapkan:

```
GET|HEAD   tarik-data-absensi-api .......... tarik-data-absensi-api.index
POST       tarik-data-absensi-api/pull ..... tarik-data-absensi-api.pull
```

---

## Testing checklist

### Akses menu & halaman

- [ ] Menu **Settings → Tarik Data Absensi API** muncul (user punya permission)
- [ ] Halaman `/tarik-data-absensi-api` terbuka tanpa error 500/403

### Tarik data

- [ ] Pilih periode pendek (mis. 2 hari) → klik **Tarik Data**
- [ ] Muncul ringkasan: Total API, Insert, Update, Lewati, Error
- [ ] Tabel **daftar dilewati** tampil (tanpa record "jam sudah lengkap")
- [ ] Tabel **daftar error** tampil jika ada error

### Validasi aturan bisnis

- [ ] Record **baru** masuk ke `t_absen` dengan jam masuk/pulang dari API
- [ ] Record **sudah ada** hanya di-update jika jam di DB masih kosong
- [ ] Record dengan jam lengkap di DB **tidak** di-overwrite
- [ ] Kolom `vcketerangan` **tidak** berubah karena tarikan API

### Cek database (opsional)

```sql
-- Contoh: absensi satu NIK setelah tarik
SELECT dtTanggal, vcNik, dtJamMasuk, dtJamKeluar, vcketerangan
FROM t_absen
WHERE vcNik = '20010057' AND dtTanggal BETWEEN '2026-06-17' AND '2026-06-18';
```

### Log error

```bash
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

---

## Troubleshooting

### Menu tidak muncul

```bash
php artisan db:seed --class=UpdatePermissionsSeeder
php artisan view:clear
```

Pastikan role user punya `view-tarik-data-absensi-api` atau `view-settings`.

### Error 403 / Permission denied

Route membutuhkan permission `view-settings` **atau** `view-tarik-data-absensi-api`.

### Gagal login API / Token tidak diterima

```bash
grep HRIS_API /var/www/html/hris-seven-payroll/.env
curl -v https://hris-api.abadinusagroup.com/v1/auth/login
```

Pastikan username/password benar dan server bisa akses HTTPS (port 443).

### cURL error 60 (SSL)

Ikuti panduan SSL di `DEPLOY_LIST_PENGAJUAN_CUTI_IZIN_API_UBUNTU.md` Step 3b.

### Class not found

```bash
cd /var/www/html/hris-seven-payroll
composer dump-autoload
php artisan config:clear
```

Pastikan `HrisApiHttpFactory.php` dan `HrApiOutboundInspector.php` ada di server.

### Route 404

```bash
php artisan route:clear
php artisan route:cache
php artisan route:list | grep tarik-data-absensi-api
```

### Banyak record "Lewati — API tidak mengirim jam masuk/pulang"

Bukan error koneksi. Artinya API mengembalikan baris absensi tetapi `clock_in` dan `clock_out` keduanya kosong (belum absen, izin, alpha, dll.).

### Banyak record "Lewati — Jam sudah lengkap"

Normal — data di `t_absen` sudah punya jam masuk & pulang; modul sengaja tidak menimpa. Jumlah ini ada di **ringkasan**, tidak di tabel detail.

---

## Ringkasan perubahan `config/hris_api.php`

Pastikan file production memuat key berikut (di bagian bawah array):

```php
'attendance_logs_path' => env('HRIS_API_ATTENDANCE_LOGS_PATH', '/v1/management/attendances/logs'),
```

---

## Checklist final deployment

- [ ] Semua file (9 file) sudah di server
- [ ] `.env` HRIS_API_* sudah benar
- [ ] `UpdatePermissionsSeeder` sudah dijalankan
- [ ] Permission di-assign ke role yang perlu
- [ ] Cache di-clear & di-rebuild
- [ ] Route `tarik-data-absensi-api` terdaftar
- [ ] Menu muncul di Settings
- [ ] Uji tarik periode pendek berhasil
- [ ] Tidak ada error baru di `laravel.log`

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
