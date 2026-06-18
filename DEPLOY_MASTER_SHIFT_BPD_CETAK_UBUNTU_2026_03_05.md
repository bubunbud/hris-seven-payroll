# 📋 Manual Deploy ke Ubuntu: Master Shift Security, Update Form BPD, Cetak Kasbon & Biaya Perjalanan Dinas

**Tanggal:** 5 Maret 2026  
**Modul:** 
1. Form Master Shift Security  
2. Update Form Biaya Perjalanan Dinas (Draft + Status)  
3. Cetak Kasbon dan Biaya Perjalanan Dinas  

**Server:** Ubuntu

---

## 📝 Ringkasan Perubahan

### 1. Form Master Shift Security
- Modul CRUD Master Shift Security/Satpam (Shift 1, 2, 3)
- Tabel: `m_shift_security`
- Menu: Absensi → Master Shift Security
- Permission: `view-master-shift-security`

### 2. Update Form Biaya Perjalanan Dinas
- **Simpan Draft**: Simpan hanya Bagian 1 (Informasi Umum) dan Bagian 2 (Kasbon) tanpa detail biaya
- **Status**: Kolom `vcStatus` (draft / complete)
- **Tombol**: Simpan Draft | Simpan (lengkap)
- **Edit**: Record draft dapat dilengkapi via tombol Edit

### 3. Cetak Kasbon dan Biaya Perjalanan Dinas
- **Draft**: Judul "KASBON PERJALANAN DINAS", cetak hanya Bagian 1 & 2 sampai tanda tangan Dept. Keuangan
- **Lengkap**: Judul "BIAYA PERJALANAN DINAS", cetak lengkap + QR code Dept. Keuangan (Rina Aryani | No RPD)
- Label No. RPD: tanpa "(diisi oleh Keu.)"

---

## 📁 Daftar File yang Perlu Di-Deploy

### A. Master Shift Security (jika belum ada di server)

| No | File | Keterangan |
|----|------|------------|
| 1 | `database/migrations/2025_12_01_063526_create_m_shift_security_table.php` | Migration tabel m_shift_security |
| 2 | `app/Models/ShiftSecurity.php` | Model ShiftSecurity |
| 3 | `app/Http/Controllers/MasterShiftSecurityController.php` | Controller |
| 4 | `resources/views/master/shift-security/index.blade.php` | View index (modal) |
| 5 | `resources/views/master/shift-security/create.blade.php` | View create |
| 6 | `resources/views/master/shift-security/edit.blade.php` | View edit |
| 7 | `routes/web.php` | Route master-shift-security |
| 8 | `resources/views/layouts/app.blade.php` | Sidebar menu |
| 9 | `resources/views/absen/layouts/app.blade.php` | Sidebar menu absen |
| 10 | `database/seeders/RolePermissionSeeder.php` | Permission view-master-shift-security |

**Catatan:** Jika tabel `m_shift_security` sudah ada, skip migration 2025_12_01_063526.

### B. Update Form Biaya Perjalanan Dinas

| No | File | Keterangan |
|----|------|------------|
| 1 | `database/migrations/2026_03_05_000001_add_vc_status_to_t_biaya_perjalanan_dinas_header_table.php` | Tambah kolom vcStatus |
| 2 | `database/migrations/2026_03_05_000002_set_existing_bpd_status_to_complete.php` | Set existing BPD ke complete |
| 3 | `app/Models/BiayaPerjalananDinasHeader.php` | Tambah vcStatus di fillable |
| 4 | `app/Http/Controllers/BiayaPerjalananDinasController.php` | Validasi draft, store/update |
| 5 | `resources/views/biaya-perjalanan-dinas/index.blade.php` | Tombol Simpan Draft, kolom Status |

### C. Cetak Kasbon dan Biaya Perjalanan Dinas

| No | File | Keterangan |
|----|------|------------|
| 1 | `resources/views/biaya-perjalanan-dinas/print.blade.php` | Logic draft vs lengkap, judul, QR, label |

---

## 🚀 Langkah-Langkah Deploy

### **Step 1: Backup Database dan File**

```bash
# Login ke server Ubuntu
ssh user@your-server-ip

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup database
mysqldump -u [db_user] -p [db_name] > backup_before_deploy_$(date +%Y%m%d_%H%M%S).sql

# Backup file yang akan diubah
cp app/Models/BiayaPerjalananDinasHeader.php app/Models/BiayaPerjalananDinasHeader.php.bak
cp app/Http/Controllers/BiayaPerjalananDinasController.php app/Http/Controllers/BiayaPerjalananDinasController.php.bak
cp resources/views/biaya-perjalanan-dinas/index.blade.php resources/views/biaya-perjalanan-dinas/index.blade.php.bak
cp resources/views/biaya-perjalanan-dinas/print.blade.php resources/views/biaya-perjalanan-dinas/print.blade.php.bak
cp routes/web.php routes/web.php.bak
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.bak
cp resources/views/absen/layouts/app.blade.php resources/views/absen/layouts/app.blade.php.bak
```

### **Step 2: Copy File ke Server**

#### **Opsi A: SCP (dari Windows/Local)**

```bash
# Ganti user@server dengan user dan IP server Anda
# Ganti /var/www/html/hris-seven-payroll dengan path aplikasi di server

# === A. Master Shift Security (jika belum ada) ===
scp database/migrations/2025_12_01_063526_create_m_shift_security_table.php user@server:/var/www/html/hris-seven-payroll/database/migrations/
scp app/Models/ShiftSecurity.php user@server:/var/www/html/hris-seven-payroll/app/Models/
scp app/Http/Controllers/MasterShiftSecurityController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/
ssh user@server "mkdir -p /var/www/html/hris-seven-payroll/resources/views/master/shift-security"
scp resources/views/master/shift-security/*.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/master/shift-security/

# === B. Update Form BPD ===
scp database/migrations/2026_03_05_000001_add_vc_status_to_t_biaya_perjalanan_dinas_header_table.php user@server:/var/www/html/hris-seven-payroll/database/migrations/
scp database/migrations/2026_03_05_000002_set_existing_bpd_status_to_complete.php user@server:/var/www/html/hris-seven-payroll/database/migrations/
scp app/Models/BiayaPerjalananDinasHeader.php user@server:/var/www/html/hris-seven-payroll/app/Models/
scp app/Http/Controllers/BiayaPerjalananDinasController.php user@server:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp resources/views/biaya-perjalanan-dinas/index.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/biaya-perjalanan-dinas/
scp resources/views/biaya-perjalanan-dinas/print.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/biaya-perjalanan-dinas/

# === C. Routes & Sidebar ===
scp routes/web.php user@server:/var/www/html/hris-seven-payroll/routes/
scp resources/views/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/layouts/
scp resources/views/absen/layouts/app.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/absen/layouts/

# === D. Seeder (jika perlu update permission) ===
scp database/seeders/RolePermissionSeeder.php user@server:/var/www/html/hris-seven-payroll/database/seeders/
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

# Set permission storage & cache
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Set permission file baru
sudo chmod 644 app/Models/ShiftSecurity.php
sudo chmod 644 app/Models/BiayaPerjalananDinasHeader.php
sudo chmod 644 app/Http/Controllers/MasterShiftSecurityController.php
sudo chmod 644 app/Http/Controllers/BiayaPerjalananDinasController.php
sudo chmod 644 resources/views/master/shift-security/*.blade.php
sudo chmod 644 resources/views/biaya-perjalanan-dinas/*.blade.php
```

### **Step 4: Jalankan Migration**

```bash
cd /var/www/html/hris-seven-payroll

# Migration BPD (vcStatus) - WAJIB
php artisan migrate --path=database/migrations/2026_03_05_000001_add_vc_status_to_t_biaya_perjalanan_dinas_header_table.php --force
php artisan migrate --path=database/migrations/2026_03_05_000002_set_existing_bpd_status_to_complete.php --force

# Migration Master Shift Security - HANYA jika tabel m_shift_security BELUM ada
# Cek dulu: mysql -u user -p -e "SHOW TABLES LIKE 'm_shift_security';" database_name
php artisan migrate --path=database/migrations/2025_12_01_063526_create_m_shift_security_table.php --force
# Jika error "Table already exists", skip migration ini
```

### **Step 5: Update Permission (jika Master Shift Security baru)**

```bash
# Via Artisan seeder
php artisan db:seed --class=RolePermissionSeeder

# Atau manual via MySQL:
# mysql -u user -p database_name
```

```sql
-- Cek permission sudah ada
SELECT * FROM permissions WHERE name = 'view-master-shift-security';

-- Jika belum ada, insert
INSERT INTO permissions (name, guard_name, created_at, updated_at)
VALUES ('View Master Shift Security', 'web', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Assign ke role admin (sesuaikan role_id)
INSERT INTO role_permission (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'admin' AND p.name = 'view-master-shift-security'
ON DUPLICATE KEY UPDATE role_id = role_id;
```

### **Step 6: Clear Cache**

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

### **Step 7: Verifikasi**

```bash
# Cek route
php artisan route:list | grep -E "master-shift-security|biaya-perjalanan-dinas"

# Cek kolom vcStatus
mysql -u user -p database_name -e "DESCRIBE t_biaya_perjalanan_dinas_header;" | grep vcStatus
```

---

## ✅ Testing Checklist

### 1. Master Shift Security
- [ ] Menu "Master Shift Security" muncul di Absensi
- [ ] CRUD berfungsi (Tambah, Edit, Hapus)
- [ ] Modal form berfungsi (jika menggunakan index modal)

### 2. Form Biaya Perjalanan Dinas - Simpan Draft
- [ ] Tombol "Simpan Draft" ada
- [ ] Simpan Draft hanya dengan Bagian 1 & 2 berhasil
- [ ] Status "Draft" tampil di tabel
- [ ] Edit record draft dapat melengkapi data
- [ ] Simpan lengkap mengubah status ke "Lengkap"

### 3. Cetak BPD
- [ ] **Draft**: Judul "KASBON PERJALANAN DINAS", hanya Bagian 1 & 2 + tanda tangan Dept. Keuangan
- [ ] **Lengkap**: Judul "BIAYA PERJALANAN DINAS", cetak lengkap + QR code Dept. Keuangan
- [ ] Label No. RPD tanpa "(diisi oleh Keu.)"
- [ ] QR code berisi "Rina Aryani | {No RPD}" saat di-scan

---

## 🐛 Troubleshooting

### Error: Table m_shift_security already exists
```bash
# Skip migration, tabel sudah ada dari deploy sebelumnya
php artisan migrate:status
# Cek apakah migration 2025_12_01_063526 sudah di-run
```

### Error: Column vcStatus already exists
```bash
# Migration 2026_03_05_000001 sudah dijalankan
# Cukup deploy file controller, model, view saja
```

### QR code tidak tampil
- Pastikan server dapat akses internet ke `https://api.qrserver.com`
- Atau gunakan proxy jika ada firewall

### Menu Master Shift Security tidak muncul
- Cek permission `view-master-shift-security` sudah di-assign ke role user
- Clear cache: `php artisan cache:clear`

---

## 📋 Deployment Record

| Item | Status |
|------|--------|
| Backup database | ⬜ |
| File di-copy | ⬜ |
| Migration dijalankan | ⬜ |
| Permission di-set | ⬜ |
| Cache di-clear | ⬜ |
| Testing checklist | ⬜ |

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Server:** _______________  
**Notes:** _______________

---

**Last Updated:** 5 Maret 2026
