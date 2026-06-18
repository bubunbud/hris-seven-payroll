# 📋 Manual Deployment Update Permission ke Ubuntu Server

**Tanggal:** 12 Februari 2026  
**Update:** Melengkapi Permission yang Belum Ada  
**Status:** ✅ Siap Deploy

---

## 📝 Ringkasan Perubahan

Update untuk melengkapi permission yang belum ada di modul Pengelolaan Role & Permission, termasuk:
- Permission untuk modul **Perjalanan Dinas** (view-perjalanan-dinas)
- Permission untuk modul **THR** (view-periode-thr, view-closing-thr, view-list-thr, view-laporan-thr)
- Permission untuk **Rekap Keterlambatan** (view-rekap-keterlambatan)
- Permission untuk **Rekap Upah Finance Ver** (view-rekap-upah-finance-ver)
- Update label module **Dashboard** di semua view settings

---

## 📁 File yang Perlu Di-copy ke Server

### **1. Seeder Files** (2 files)
```
database/seeders/RolePermissionSeeder.php (update)
database/seeders/UpdatePermissionsSeeder.php (baru)
```

### **2. View Files** (4 files)
```
resources/views/settings/permissions/index.blade.php (update)
resources/views/settings/roles/create.blade.php (update)
resources/views/settings/roles/edit.blade.php (update)
resources/views/settings/roles/show.blade.php (update)
```

**Total: 6 files**

---

## 🚀 Langkah-Langkah Deployment ke Ubuntu Server

### **Step 1: Backup Database** (Optional tapi Recommended)

```bash
# Login ke server Ubuntu
ssh user@your-server-ip

# Backup database sebelum update
cd /var/www/html/hris-seven-payroll
mysqldump -u [db_user] -p [db_name] > backup_before_update_permissions_$(date +%Y%m%d_%H%M%S).sql

# Contoh:
# mysqldump -u hris_user -p hris_seven > backup_before_update_permissions_20260212.sql
```

### **Step 2: Copy Files ke Server**

#### **Opsi A: Menggunakan SCP (dari Windows/Local)**

```bash
# Dari local machine, copy semua file ke server

# 1. Seeder Files
scp database/seeders/RolePermissionSeeder.php user@server:/var/www/html/hris-seven-payroll/database/seeders/
scp database/seeders/UpdatePermissionsSeeder.php user@server:/var/www/html/hris-seven-payroll/database/seeders/

# 2. View Files
scp resources/views/settings/permissions/index.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/settings/permissions/
scp resources/views/settings/roles/create.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/settings/roles/
scp resources/views/settings/roles/edit.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/settings/roles/
scp resources/views/settings/roles/show.blade.php user@server:/var/www/html/hris-seven-payroll/resources/views/settings/roles/
```

#### **Opsi B: Menggunakan Git (jika menggunakan version control)**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll
git pull origin main  # atau branch yang sesuai
```

#### **Opsi C: Manual Copy via FTP/SFTP**

1. Upload semua 6 file menggunakan FileZilla, WinSCP, atau tool SFTP lainnya
2. Pastikan struktur direktori sama dengan di local

### **Step 3: Set Permission File**

```bash
# Login ke server
ssh user@server

cd /var/www/html/hris-seven-payroll

# Set ownership (ganti www-data sesuai user web server)
sudo chown -R www-data:www-data .

# Set permission untuk file baru
sudo chmod 644 database/seeders/RolePermissionSeeder.php
sudo chmod 644 database/seeders/UpdatePermissionsSeeder.php
sudo chmod 644 resources/views/settings/permissions/index.blade.php
sudo chmod 644 resources/views/settings/roles/*.blade.php
```

### **Step 4: Jalankan Seeder Update**

```bash
cd /var/www/html/hris-seven-payroll

# Jalankan seeder update (Recommended)
php artisan db:seed --class=UpdatePermissionsSeeder
```

**Expected Output:**
```
✓ Permission 'View Perjalanan Dinas' berhasil ditambahkan
✓ Permission 'View Rekap Keterlambatan' berhasil ditambahkan
✓ Permission 'View Periode THR' berhasil ditambahkan
✓ Permission 'View Closing THR' berhasil ditambahkan
✓ Permission 'View List THR' berhasil ditambahkan
✓ Permission 'View Laporan THR' berhasil ditambahkan
✓ Permission 'View Rekap Upah Finance Ver' berhasil ditambahkan

=== Summary ===
Permission baru ditambahkan: 7
Permission sudah ada (dilewati): 0
Total permission di database: XX
```

**Jika ada error atau permission sudah ada:**
```
⊘ Permission 'View Perjalanan Dinas' sudah ada, dilewati
...
=== Summary ===
Permission baru ditambahkan: 0
Permission sudah ada (dilewati): 7
Total permission di database: XX
```

### **Step 5: Clear Cache**

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache untuk production (optional)
php artisan config:cache
php artisan view:cache
```

### **Step 6: Verifikasi**

#### **A. Cek Permission di Database**

```bash
# Login ke MySQL
mysql -u [db_user] -p [db_name]

# Cek permission yang baru ditambahkan
SELECT * FROM permissions 
WHERE slug IN (
    'view-perjalanan-dinas',
    'view-rekap-keterlambatan',
    'view-periode-thr',
    'view-closing-thr',
    'view-list-thr',
    'view-laporan-thr',
    'view-rekap-upah-finance-ver'
)
ORDER BY module, name;
```

**Expected:** 7 rows

#### **B. Cek via Browser**

1. Login sebagai admin
2. Buka menu **Settings → Pengelolaan Permission**
3. Filter atau search permission baru (misal: "Perjalanan Dinas")
4. Pastikan semua 7 permission muncul dengan module yang benar

#### **C. Cek di Role Management**

1. Buka menu **Settings → Pengelolaan Role**
2. Edit role (misal: Admin, HR)
3. Pastikan permission baru muncul di list:
   - **Module Absensi**: View Perjalanan Dinas, View Rekap Keterlambatan
   - **Module Proses Payroll**: View Periode THR, View Closing THR, View List THR
   - **Module Laporan**: View Laporan THR, View Rekap Upah Finance Ver
4. Pastikan label "Dashboard" muncul di filter module

---

## 🔐 Assign Permission ke Role (Optional)

Setelah permission ditambahkan, assign ke role yang sesuai:

### **Via UI (Recommended):**
1. Buka **Settings → Pengelolaan Role**
2. Edit role (misal: Admin, HR)
3. Centang permission baru yang ingin diberikan
4. Simpan

### **Via SQL (Jika perlu):**
```sql
-- Assign semua permission baru ke role Admin
-- Ganti 'Administrator' dengan nama role yang sesuai
INSERT INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'Administrator'
AND p.slug IN (
    'view-perjalanan-dinas',
    'view-rekap-keterlambatan',
    'view-periode-thr',
    'view-closing-thr',
    'view-list-thr',
    'view-laporan-thr',
    'view-rekap-upah-finance-ver'
)
ON DUPLICATE KEY UPDATE role_id = role_id;
```

---

## ✅ Checklist Deployment

### **Pre-Deployment**
- [ ] Backup database sudah dilakukan (optional tapi recommended)

### **File Copy**
- [ ] Seeder `RolePermissionSeeder.php` sudah di-copy
- [ ] Seeder `UpdatePermissionsSeeder.php` sudah di-copy
- [ ] View `permissions/index.blade.php` sudah di-copy
- [ ] View `roles/create.blade.php` sudah di-copy
- [ ] View `roles/edit.blade.php` sudah di-copy
- [ ] View `roles/show.blade.php` sudah di-copy
- [ ] Permission file sudah di-set

### **Database Update**
- [ ] Seeder update sudah dijalankan
- [ ] Semua 7 permission berhasil ditambahkan
- [ ] Tidak ada error saat seeder

### **Cache & Verification**
- [ ] Cache sudah di-clear
- [ ] Permission muncul di UI Pengelolaan Permission
- [ ] Permission muncul di Role Management
- [ ] Label "Dashboard" muncul di filter module
- [ ] Tidak ada error di log

### **Post-Deployment**
- [ ] Permission di-assign ke role yang sesuai (optional)
- [ ] Testing: User dengan role yang sudah di-assign dapat mengakses modul baru

---

## 🐛 Troubleshooting

### **Error: Class 'UpdatePermissionsSeeder' not found**
```bash
# Clear autoload cache
composer dump-autoload

# Coba jalankan lagi
php artisan db:seed --class=UpdatePermissionsSeeder
```

### **Error: Permission already exists**
- Ini normal, seeder akan skip otomatis
- Cek summary di output seeder

### **Permission tidak muncul di UI**
```bash
# Clear cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Refresh browser (Ctrl+F5 atau Cmd+Shift+R)
```

### **Module 'Dashboard' tidak muncul**
- Pastikan semua 4 view files sudah di-copy
- Clear view cache: `php artisan view:clear`
- Refresh browser

### **Permission tidak muncul di Role Management**
- Pastikan permission sudah ada di database (cek via SQL)
- Clear cache dan refresh browser
- Pastikan view files sudah di-copy dengan benar

---

## 📝 Catatan Penting

1. **Seeder menggunakan `firstOrCreate`**: Permission tidak akan duplikat jika sudah ada
2. **Backward Compatible**: Permission yang sudah ada tidak akan terpengaruh
3. **View Update**: Label "Dashboard" ditambahkan untuk menampilkan permission dashboard dengan benar
4. **Role Assignment**: Permission baru perlu di-assign ke role secara manual (via UI atau SQL)

---

## 📋 Quick Command Summary

```bash
# 1. Copy files (via SCP atau Git)
# 2. Set permission
sudo chmod 644 database/seeders/*.php resources/views/settings/**/*.blade.php

# 3. Jalankan seeder
php artisan db:seed --class=UpdatePermissionsSeeder

# 4. Clear cache
php artisan config:clear && php artisan cache:clear && php artisan view:clear

# 5. Verifikasi
mysql -u [user] -p [db] -e "SELECT name, slug, module FROM permissions WHERE slug LIKE 'view-%thr%' OR slug LIKE 'view-perjalanan%' OR slug LIKE 'view-rekap%';"
```

---

## ✅ Checklist Final

- [ ] Semua 6 file sudah di-copy ke server
- [ ] Permission file sudah di-set
- [ ] Seeder update sudah dijalankan
- [ ] Semua 7 permission berhasil ditambahkan
- [ ] Cache sudah di-clear
- [ ] Permission muncul di UI
- [ ] Label "Dashboard" muncul di filter
- [ ] Permission di-assign ke role (optional)
- [ ] Tidak ada error di log
- [ ] Testing berhasil

---

**Status:** ✅ Siap untuk Production  
**Deployment Date:** _______________  
**Deployed By:** _______________  
**Notes:** _______________








