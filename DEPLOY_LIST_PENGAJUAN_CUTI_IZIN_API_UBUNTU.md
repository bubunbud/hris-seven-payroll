# 📋 Manual Deployment Modul List Pengajuan Cuti API & List Pengajuan Izin API ke Ubuntu Server

**Tanggal:** Maret 2026  
**Modul:** List Pengajuan Cuti API, List Pengajuan Izin API  
**Status:** ✅ Siap Deploy ke Production

---

## 📝 Ringkasan Fitur

### Modul 1: List Pengajuan Cuti API
- Mengambil pengajuan cuti dari API HRIS eksternal (endpoint `GET /v1/leaves/requests`)
- **Hanya menampilkan status Approved/Completed**
- Import ke tabel `t_tidak_masuk` sebagai feeder cuti
- Mapping: Cuti Tahunan (C010), Cuti Umroh (C013), Sakit (S010), Izin Pribadi (I002), dll.

### Modul 2: List Pengajuan Izin API
- Mengambil pengajuan tidak masuk (Sakit + Izin Pribadi) dari API HRIS (endpoint `GET /v1/absents/requests`)
- **Hanya menampilkan status Approved/Completed**
- Import ke tabel `t_tidak_masuk` sebagai feeder izin tidak masuk
- Mendukung endpoint subordinate: `GET /v1/absents/requests/subordinate`

---

## 📁 Daftar File yang Perlu Di-copy

### 1. Config (1 file)
```
config/hris_api.php
```

### 2. Service Files (4 files — termasuk factory SSL terpusat)
```
app/Services/HrisApiHttpFactory.php
app/Services/HrisApiService.php
app/Services/HrisApiAbsentService.php
app/Services/HrisApiPermitService.php
```

### 3. Controller Files (2 files)
```
app/Http/Controllers/ListPengajuanCutiApiController.php
app/Http/Controllers/ListPengajuanIzinApiController.php
```

### 4. View Files (2 files)
```
resources/views/list-pengajuan-cuti-api/index.blade.php
resources/views/list-pengajuan-izin-api/index.blade.php
```

### 5. Routes Update (1 file)
```
routes/web.php
```

### 6. Sidebar Menu Update (2 files — jika project punya kedua layout)
```
resources/views/layouts/app.blade.php
resources/views/absen/layouts/app.blade.php
```

### 7. Seeder (1 file)
```
database/seeders/UpdatePermissionsSeeder.php
```

**Total: 13 file** (atau 12 jika hanya memakai satu layout `app.blade.php`)

---

## 🚀 Langkah-Langkah Deployment ke Ubuntu Server

### **Step 1: Backup Database (Opsional)**

```bash
# Login ke server Ubuntu
ssh user@your-server-ip

# Backup database (opsional, tidak ada migration)
cd /var/www/html/hris-seven-payroll
mysqldump -u [db_user] -p [db_name] > backup_before_api_modules_$(date +%Y%m%d_%H%M%S).sql
```

### **Step 2: Copy Files ke Server**

#### **Opsi A: Menggunakan SCP (dari Windows/Local)**

```bash
# Dari local machine (PowerShell/CMD), navigasi ke folder project
cd C:\xampp\htdocs\hris-seven-payroll

# 1. Config
scp config/hris_api.php user@server:/var/www/html/hris-seven-payroll/config/

# 2. Service files (4)
scp app/Services/HrisApiHttpFactory.php user@server:/var/www/html/hris-seven-payroll/app/Services/
scp app/Services/HrisApiService.php user@server:/var/www/html/hris-seven-payroll/app/Services/
scp app/Services/HrisApiAbsentService.php user@server:/var/www/html/hris-seven-payroll/app/Services/
scp app/Services/HrisApiPermitService.php user@server:/var/www/html/hris-seven-payroll/app/Services/

# 3. Controller Files
scp app/Http/Controllers/ListPengajuanCutiApiController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp app/Http/Controllers/ListPengajuanIzinApiController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# 4. View Files - buat direktori jika belum ada
ssh user@server "mkdir -p /var/www/html/hris-seven-payroll/resources/views/list-pengajuan-cuti-api"
ssh user@server "mkdir -p /var/www/html/hris-seven-payroll/resources/views/list-pengajuan-izin-api"
scp resources/views/list-pengajuan-cuti-api/index.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/list-pengajuan-cuti-api/
scp resources/views/list-pengajuan-izin-api/index.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/list-pengajuan-izin-api/

# 5. Routes & Sidebar & Seeder
scp routes/web.php user@server:/var/www/html/hris-seven-payroll/routes/
scp resources/views/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/layouts/
scp resources/views/absen/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/absen/layouts/
scp database/seeders/UpdatePermissionsSeeder.php user@server:/var/www/html/hris-seven-payroll/database/seeders/
```

Setelah copy, pada server jalankan (opsional, jika muncul error class tidak ditemukan):

```bash
cd /var/www/html/hris-seven-payroll && composer dump-autoload
```

#### **Opsi B: Menggunakan Git**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll
git pull origin main   # atau branch yang sesuai
```

#### **Opsi C: Manual Copy via FTP/SFTP**

1. Connect ke server via FileZilla, WinSCP, atau tool SFTP
2. Upload semua file ke path yang sesuai
3. Pastikan struktur direktori sama dengan di local

### **Step 3: Konfigurasi .env**

```bash
# Login ke server
ssh user@server

cd /var/www/html/hris-seven-payroll

# Edit file .env
nano .env
```

Tambahkan atau perbarui konfigurasi berikut di file `.env`:

```env
# HRIS API (External - feeder pengajuan cuti & izin)
HRIS_API_BASE_URL=https://hris-api.abadinusagroup.com
HRIS_API_USERNAME=superadmin
HRIS_API_PASSWORD=<password_api>
HRIS_API_TIMEOUT=60

# SSL (opsional — hanya jika di server muncul cURL error 60, lihat Troubleshooting):
# HRIS_API_CURL_CA_BUNDLE=/etc/ssl/certs/ca-certificates.crt
# HRIS_API_VERIFY_SSL=false
```

**Catatan:** Ganti `<password_api>` dengan password API yang valid. Hindari `HRIS_API_VERIFY_SSL=false` di production; perbaiki CA di sistem terlebih dulu.

### **Step 3b: Jika error SSL / cURL error 60 di Ubuntu**

Gejala: `SSL certificate problem: unable to get local issuer certificate` saat memanggil `https://hris-api.abadinusagroup.com/...`.

**Penyebab umum:** paket **`ca-certificates`** tidak lengkap atau versi PHP/cURL tidak memakai bundle CA sistem.

**Urutan perbaikan yang disarankan:**

1. **Perbarui CA di sistem (disarankan):**
   ```bash
   sudo apt-get update
   sudo apt-get install -y ca-certificates
   sudo update-ca-certificates
   ```

2. **Uji dari server (harus sukses verifikasi):**
   ```bash
   curl -vI https://hris-api.abadinusagroup.com/
   ```

3. **Jika `curl` dari server tetap gagal verifikasi:** pastikan tidak ada proxy/firewall yang melakukan SSL inspection tanpa CA internal terpasang. Jika perlu CA khusus, simpan file `.pem` lalu:
   ```env
   HRIS_API_CURL_CA_BUNDLE=/path/ke/ca-bundle.pem
   ```
   Lalu: `php artisan config:clear` dan `php artisan config:cache`.

4. **PHP (opsional):** pastikan `php.ini` memakai CA sistem (biasanya tidak perlu jika langkah 1 berhasil):
   ```ini
   curl.cainfo=/etc/ssl/certs/ca-certificates.crt
   openssl.cafile=/etc/ssl/certs/ca-certificates.crt
   ```
   Restart PHP-FPM / Apache: `sudo systemctl restart php*-fpm` atau `sudo systemctl restart apache2`.

5. **Tanpa menyentuh `apt` (prioritas aplikasi ini):** unduh **`cacert.pem`** di PC dari [cacert.pem (Mozilla)](https://curl.se/ca/cacert.pem), SCP ke server:
   ```bash
   sudo mkdir -p /var/www/html/hris-seven-payroll/storage/app/cacerts
   sudo install -o www-data -g www-data -m 644 cacert.pem /var/www/html/hris-seven-payroll/storage/app/cacerts/cacert.pem
   ```
   Laravel akan memilih file **`storage/app/cacerts/cacert.pem`** *sebelum* bundle OS `/etc/ssl/` sehingga cocok ketika **`apt`**/`ca-certificates` tidak ter‑update.

6. **Hanya untuk diagnosa singkat** (tidak untuk production): `HRIS_API_VERIFY_SSL=false` di `.env` — aplikasi memanggil API tanpa verifikasi sertifikat (rentan MITM).

Setelah salah satu cara di atas terpasang:

```bash
cd /var/www/html/hris-seven-payroll && php artisan config:clear && sudo systemctl restart php*-fpm
```

### **Step 4: Set Permission File**

```bash
cd /var/www/html/hris-seven-payroll

# Set ownership (sesuaikan user web server: www-data untuk Apache/Nginx)
sudo chown -R www-data:www-data .

# Set permission untuk storage dan cache
sudo chmod -R 775 storage bootstrap/cache

# Set permission untuk file baru
sudo chmod 644 config/hris_api.php
sudo chmod 644 app/Services/HrisApiHttpFactory.php
sudo chmod 644 app/Services/HrisApiService.php
sudo chmod 644 app/Services/HrisApiAbsentService.php
sudo chmod 644 app/Services/HrisApiPermitService.php
sudo chmod 644 app/Http/Controllers/ListPengajuanCutiApiController.php
sudo chmod 644 app/Http/Controllers/ListPengajuanIzinApiController.php
sudo chmod 644 resources/views/list-pengajuan-cuti-api/index.blade.php
sudo chmod 644 resources/views/list-pengajuan-izin-api/index.blade.php
```

### **Step 5: Run Permission Seeder**

```bash
cd /var/www/html/hris-seven-payroll

# Jalankan seeder untuk menambahkan permission
php artisan db:seed --class=UpdatePermissionsSeeder

# Expected output:
# ✓ Permission 'View List Pengajuan Cuti API' berhasil ditambahkan (atau sudah ada)
# ✓ Permission 'View List Pengajuan Izin API' berhasil ditambahkan (atau sudah ada)
```

**Assign permission ke role:**
1. Login ke aplikasi sebagai admin
2. Buka **Settings → Role & Permission** (atau **Manage Roles**)
3. Edit role yang memerlukan akses (misal: Admin, HR)
4. Centang permission:
   - `view-list-pengajuan-cuti-api`
   - `view-list-pengajuan-izin-api`
5. Simpan

### **Step 6: Clear All Cache**

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 7: Verifikasi File & Route**

```bash
cd /var/www/html/hris-seven-payroll

# Cek file sudah ada
ls -la config/hris_api.php
ls -la app/Services/HrisApiHttpFactory.php
ls -la app/Services/HrisApiService.php
ls -la app/Services/HrisApiAbsentService.php
ls -la app/Services/HrisApiPermitService.php
ls -la app/Http/Controllers/ListPengajuanCutiApiController.php
ls -la app/Http/Controllers/ListPengajuanIzinApiController.php
ls -la resources/views/list-pengajuan-cuti-api/index.blade.php
ls -la resources/views/list-pengajuan-izin-api/index.blade.php

# Cek route terdaftar
php artisan route:list | grep list-pengajuan

# Expected output:
# GET|HEAD  list-pengajuan-cuti-api ........ list-pengajuan-cuti-api.index
# POST      list-pengajuan-cuti-api/import . list-pengajuan-cuti-api.import
# GET|HEAD  list-pengajuan-izin-api ........ list-pengajuan-izin-api.index
# POST      list-pengajuan-izin-api/import . list-pengajuan-izin-api.import
```

---

## ✅ Testing Checklist

### **1. Test List Pengajuan Cuti API**
- [ ] Menu "List Pengajuan Cuti API" muncul di **Settings** (jika user punya permission)
- [ ] Route `/list-pengajuan-cuti-api` dapat diakses
- [ ] Form filter periode (Dari Tanggal, Sampai Tanggal) tampil
- [ ] Klik **"Ambil Data dari API"** → data cuti tampil (hanya Approved/Completed)
- [ ] Pilih baris → klik **"Import ke Tidak Masuk"** → import berhasil
- [ ] Cek **Absensi → Izin Tidak Masuk** → data cuti sudah masuk

### **2. Test List Pengajuan Izin API**
- [ ] Menu "List Pengajuan Izin API" muncul di **Settings**
- [ ] Route `/list-pengajuan-izin-api` dapat diakses
- [ ] Form filter periode + checkbox "Bawahan" tampil
- [ ] Klik **"Ambil Data dari API"** → data Sakit + Izin Pribadi tampil (hanya Approved/Completed)
- [ ] Pilih baris → klik **"Import ke Tidak Masuk"** → import berhasil
- [ ] Cek **Absensi → Izin Tidak Masuk** → data sudah masuk

### **3. Test Koneksi API**
- [ ] Tidak ada error "Gagal login ke API HRIS"
- [ ] Tidak ada error "Token tidak diterima"
- [ ] Server dapat akses URL API (cek firewall/network)

### **4. Test Import Validasi**
- [ ] NIK yang tidak ada di m_karyawan → di-skip dengan pesan error
- [ ] vcKodeAbsen yang tidak ada di m_jenis_absen → di-skip
- [ ] Duplikasi (NIK + kode + tanggal sama) → di-update, tidak double insert

---

## 🐛 Troubleshooting

### **Error: Gagal login ke API HRIS / Token tidak diterima**
```bash
# Cek konfigurasi .env
cat /var/www/html/hris-seven-payroll/.env | grep HRIS_API

# Pastikan:
# - HRIS_API_BASE_URL benar (https://...)
# - HRIS_API_USERNAME dan HRIS_API_PASSWORD benar
# - Server dapat akses URL API (test: curl https://hris-api.abadinusagroup.com)
```

Jika log error masih menyebut **`Http::timeout(...)->post` di `HrisApiService`** (bukan `HrisApiHttpFactory`), berarti **file di server belum terbarui** — deploy ulang minimal: `app/Services/HrisApiHttpFactory.php`, `HrisApiService.php`, `HrisApiAbsentService.php`, `HrisApiPermitService.php`, `config/hris_api.php`, lalu `php artisan config:clear` dan restart PHP-FPM (`sudo systemctl restart php*-fpm`).

### **Error: Class HrisApiAbsentService not found**
```bash
# Clear autoload
cd /var/www/html/hris-seven-payroll
composer dump-autoload
php artisan config:clear
```

### **Error: Route not found / 404**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Cek route
php artisan route:list | grep list-pengajuan
```

### **Error: Permission denied (menu tidak muncul)**
```bash
# Jalankan ulang seeder
php artisan db:seed --class=UpdatePermissionsSeeder

# Cek permission di database
mysql -u [user] -p [database] -e "SELECT * FROM permissions WHERE slug LIKE 'view-list-pengajuan%';"

# Pastikan role user punya permission tersebut
```

### **Error: NIK tidak ditemukan saat import**
- Pastikan NIK di API sama dengan NIK di tabel `m_karyawan`
- Cek master karyawan sudah di-sync dari sumber yang sama

### **Error: Kode absen tidak ada**
- Pastikan tabel `m_jenis_absen` memiliki: C010, C012, C013, S010, I002, I001
- Cek: `SELECT vcKodeAbsen FROM m_jenis_absen;`

### **Server tidak bisa akses API (timeout/connection refused)**
```bash
# Test koneksi dari server
curl -v https://hris-api.abadinusagroup.com/v1/auth/login

# Cek firewall outbound (port 443)
# Cek proxy jika ada
```

---

## 📝 Catatan Penting

### **1. Endpoint API**
| Modul | Endpoint | Keterangan |
|-------|----------|------------|
| List Pengajuan Cuti API | GET /v1/leaves/requests | Pengajuan cuti |
| List Pengajuan Izin API | GET /v1/absents/requests | Tidak masuk (Sakit + Izin Pribadi) |
| List Pengajuan Izin API | GET /v1/absents/requests/subordinate | Data bawahan |

### **2. Status yang Diproses**
- **Kedua modul** hanya menampilkan dan memproses data dengan status **Approved** atau **Completed**

### **3. Mapping vcKodeAbsen**
**Cuti API:** C010 (Cuti Tahunan), C012 (Cuti Bersama/Melahirkan), C013 (Umroh), S010 (Sakit), I002 (Izin Pribadi), I001 (Izin Resmi)

**Izin API:** S010 (Sakit), I002 (Izin Pribadi)

### **4. Syarat Import**
- NIK harus ada di `m_karyawan`
- vcKodeAbsen harus ada di `m_jenis_absen`
- Duplikasi dicek: vcNik + vcKodeAbsen + dtTanggalMulai + dtTanggalSelesai

---

## ✅ Checklist Final Deployment

- [ ] Semua file (config, services, controllers, views, routes, sidebar, seeder) sudah di-copy ke server
- [ ] Konfigurasi `.env` (HRIS_API_*) sudah diisi
- [ ] Permission file sudah di-set
- [ ] Seeder `UpdatePermissionsSeeder` sudah dijalankan
- [ ] Permission sudah di-assign ke role yang sesuai
- [ ] Semua cache sudah di-clear dan di-rebuild
- [ ] Route terdaftar dengan benar
- [ ] Menu muncul di Settings
- [ ] List Pengajuan Cuti API dapat fetch data dan import
- [ ] List Pengajuan Izin API dapat fetch data dan import
- [ ] Tidak ada error di `storage/logs/laravel.log`

---

## 📋 Deployment Record

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Server IP/Hostname:** _______________  
**Status:** ⬜ Success  ⬜ Failed  
**Notes:** _______________

---

**Status:** ✅ Siap untuk Production  
**Last Updated:** Mei 2026
