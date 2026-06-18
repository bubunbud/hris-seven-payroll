# 📋 Manual Deploy ke Ubuntu: Modul Browse Absensi Security

**Tanggal:** 6 Maret 2026  
**Modul:** Browse Absensi Security / Satpam  
**Server:** Ubuntu

---

## 📝 Ringkasan Fitur

Modul **Browse Absensi Security** untuk melihat data performa absensi karyawan Security/Satpam dengan parameter khusus:

- **Shift Terjadwal** – dari Jadwal Shift Satpam (S1, S2, S3)
- **Shift Aktual** – terdeteksi dari jam masuk/pulang
- **Telat (menit)** – berdasarkan toleransi Master Shift Security
- **Pulang Cepat (menit)** – termasuk handle cross-day Shift 3
- **Durasi Jam Kerja** – total jam kerja per hari
- **Kepatuhan** – Sesuai / Telat / Pulang Cepat / Tidak Sesuai Jadwal / Tidak Masuk

**Prerequisite:** Modul Jadwal Shift Satpam, Master Shift Security, dan List Override Jadwal harus sudah terdeploy.

---

## 📁 Daftar File yang Perlu Di-Deploy

| No | File | Keterangan |
|----|------|------------|
| 1 | `app/Services/SecurityAbsensiService.php` | Service (tambah calculateTelatMenit, calculatePulangCepatMenit) |
| 2 | `app/Http/Controllers/BrowseAbsensiSecurityController.php` | Controller |
| 3 | `resources/views/browse-absensi-security/index.blade.php` | View |
| 4 | `routes/web.php` | Route browse-absensi-security |
| 5 | `resources/views/layouts/app.blade.php` | Sidebar menu |
| 6 | `resources/views/absen/layouts/app.blade.php` | Sidebar menu absen |

**Total: 6 file**

**Catatan:** Tidak ada migration. Modul menggunakan tabel yang sudah ada: `t_absen`, `t_jadwal_shift_security`, `m_shift_security`, `m_karyawan`, `t_tidak_masuk`.

---

## 🚀 Langkah-Langkah Deploy

### **Step 1: Backup File (Opsional)**

```bash
# Login ke server Ubuntu
ssh user@your-server-ip

cd /var/www/html/hris-seven-payroll

# Backup file yang akan diubah
cp routes/web.php routes/web.php.bak_$(date +%Y%m%d)
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.bak_$(date +%Y%m%d)
cp resources/views/absen/layouts/app.blade.php resources/views/absen/layouts/app.blade.php.bak_$(date +%Y%m%d)
```

### **Step 2: Copy File ke Server**

#### **Opsi A: SCP (dari Windows/Local)**

```bash
# Ganti user@server dengan user dan IP server Anda
# Ganti /var/www/html/hris-seven-payroll dengan path aplikasi di server

# 1. Service
scp app/Services/SecurityAbsensiService.php user@server:/var/www/html/hris-seven-payroll/app/Services/

# 2. Controller
scp app/Http/Controllers/BrowseAbsensiSecurityController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# 3. View (buat direktori jika belum ada)
ssh user@server "mkdir -p /var/www/html/hris-seven-payroll/resources/views/browse-absensi-security"
scp resources/views/browse-absensi-security/index.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/browse-absensi-security/

# 4. Routes & Sidebar
scp routes/web.php user@server:/var/www/html/hris-seven-payroll/routes/
scp resources/views/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/layouts/
scp resources/views/absen/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/absen/layouts/
```

#### **Opsi B: Git**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll
git pull origin main  # atau branch yang sesuai
```

### **Step 3: Set Permission File**

```bash
cd /var/www/html/hris-seven-payroll

# Set ownership
sudo chown -R www-data:www-data .

# Set permission file
sudo chmod 644 app/Services/SecurityAbsensiService.php
sudo chmod 644 app/Http/Controllers/BrowseAbsensiSecurityController.php
sudo chmod 644 resources/views/browse-absensi-security/index.blade.php
sudo chmod 644 routes/web.php
sudo chmod 644 resources/views/layouts/app.blade.php
sudo chmod 644 resources/views/absen/layouts/app.blade.php
```

### **Step 4: Clear Cache**

```bash
cd /var/www/html/hris-seven-payroll

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 5: Verifikasi**

```bash
# Cek route terdaftar
php artisan route:list | grep browse-absensi-security

# Expected output:
# GET|HEAD  browse-absensi-security ....... browse-absensi-security.index
```

---

## ✅ Testing Checklist

### 1. Akses Menu
- [ ] Menu **Browse Absensi Security** muncul di Absensi (setelah Jadwal Shift Satpam)
- [ ] Halaman dapat dibuka tanpa error 404/500

### 2. Filter
- [ ] Filter periode (Dari Tanggal, Sampai Tanggal) berfungsi
- [ ] Autocomplete NIK/Nama berfungsi
- [ ] Filter status (Sesuai, Telat, Pulang Cepat, dll) berfungsi

### 3. Data & Kolom
- [ ] Data Security tampil (hanya Group_pegawai = Security)
- [ ] Kolom Shift Terjadwal tampil (S1, S2, S3)
- [ ] Kolom Shift Aktual tampil
- [ ] Kolom Telat (menit) tampil
- [ ] Kolom Pulang Cepat (menit) tampil
- [ ] Kolom Durasi (jam) tampil
- [ ] Kolom Kepatuhan tampil

### 4. Summary Cards
- [ ] Total Data, Sesuai, Telat, Pulang Cepat, Tidak Masuk, Tidak Sesuai tampil benar

### 5. Pagination
- [ ] Pagination berfungsi

---

## 🔐 Permission

Modul menggunakan permission **`view-jadwal-shift-satpam`** (sama dengan Jadwal Shift Satpam).

User yang sudah punya akses Jadwal Shift Satpam otomatis bisa mengakses Browse Absensi Security.

**Cek permission:**
```sql
-- Cek permission sudah ada
SELECT * FROM permissions WHERE name = 'view-jadwal-shift-satpam';

-- Cek role yang punya akses
SELECT r.name, p.name 
FROM roles r 
JOIN role_permission rp ON r.id = rp.role_id 
JOIN permissions p ON rp.permission_id = p.id 
WHERE p.name = 'view-jadwal-shift-satpam';
```

---

## 🐛 Troubleshooting

### Error: Method getHariLiburList does not exist
- Pastikan menggunakan `getHariLiburWithTukar` (sudah diperbaiki di controller)

### Error: You must specify an orderBy clause when using this function
- Pastikan query `chunk()` sudah punya `orderBy` (sudah diperbaiki)

### Menu tidak muncul
- Cek user punya permission `view-jadwal-shift-satpam`
- Clear cache: `php artisan cache:clear`

### Data kosong
- Pastikan ada karyawan dengan `Group_pegawai = 'Security'`
- Pastikan ada data di `t_absen` atau `t_tidak_masuk` untuk periode yang dipilih
- Pastikan Jadwal Shift Satpam sudah diisi untuk periode tersebut

---

## 📋 Deployment Record

| Item | Status |
|------|--------|
| File di-copy | ⬜ |
| Permission di-set | ⬜ |
| Cache di-clear | ⬜ |
| Route terverifikasi | ⬜ |
| Testing checklist | ⬜ |

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Server:** _______________  
**Notes:** _______________

---

**Last Updated:** 6 Maret 2026
