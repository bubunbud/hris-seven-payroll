# Manual Deploy: Update Permission Form Biaya Perjalanan Dinas (BPD)

**Tanggal:** 19 Februari 2026  
**Update:** Menambahkan Permission untuk Form Biaya Perjalanan Dinas  
**Server:** Ubuntu

---

## 📋 Ringkasan Perubahan

Menambahkan permission baru `view-biaya-perjalanan-dinas` untuk modul Form Biaya Perjalanan Dinas (BPD) agar bisa dikelola di Settings → Pengelolaan Role dan Permission.

**Permission Baru:**
- **Name:** View Biaya Perjalanan Dinas
- **Slug:** `view-biaya-perjalanan-dinas`
- **Module:** `absensi`
- **Description:** Melihat Form Biaya Perjalanan Dinas (BPD)

---

## 📁 File yang Perlu Di-Deploy

### 1. Seeder Files (2 file)
- `database/seeders/RolePermissionSeeder.php` - Tambah permission di seeder utama
- `database/seeders/UpdatePermissionsSeeder.php` - Tambah permission di seeder update

### 2. Routes Update (1 file)
- `routes/web.php` - Update middleware route BPD untuk include permission baru

### 3. Sidebar Update (1 file)
- `resources/views/layouts/app.blade.php` - Update permission check untuk menu BPD

**Total: 4 file**

---

## 🚀 Langkah-Langkah Deploy

### **Langkah 1: Backup File Existing**

```bash
# Login ke server Ubuntu
ssh user@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup file yang akan diubah
cp database/seeders/RolePermissionSeeder.php database/seeders/RolePermissionSeeder.php.backup_$(date +%Y%m%d_%H%M%S)
cp database/seeders/UpdatePermissionsSeeder.php database/seeders/UpdatePermissionsSeeder.php.backup_$(date +%Y%m%d_%H%M%S)
cp routes/web.php routes/web.php.backup_$(date +%Y%m%d_%H%M%S)
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.backup_$(date +%Y%m%d_%H%M%S)
```

### **Langkah 2: Upload File Baru**

**Opsi A: Menggunakan SCP (dari Windows/Local)**

```bash
# Dari terminal lokal (Windows PowerShell atau Git Bash)

# Upload Seeder Files
scp database/seeders/RolePermissionSeeder.php user@192.168.10.40:/var/www/html/hris-seven-payroll/database/seeders/
scp database/seeders/UpdatePermissionsSeeder.php user@192.168.10.40:/var/www/html/hris-seven-payroll/database/seeders/

# Upload Routes Update
scp routes/web.php user@192.168.10.40:/var/www/html/hris-seven-payroll/routes/

# Upload Sidebar Update
scp resources/views/layouts/app.blade.php user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/layouts/
```

**Opsi B: Menggunakan Git (jika menggunakan version control)**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll
git pull origin main  # atau branch yang sesuai
```

### **Langkah 3: Set Permission File**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Set ownership
sudo chown -R www-data:www-data .

# Set permission untuk file
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Set permission khusus untuk storage dan cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### **Langkah 4: Jalankan Seeder untuk Menambahkan Permission**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Jalankan UpdatePermissionsSeeder untuk menambahkan permission baru
php artisan db:seed --class=UpdatePermissionsSeeder

# Output yang diharapkan:
# INFO  Seeding database.
# ⊘ Permission 'View Perjalanan Dinas' sudah ada, dilewati
# ✓ Permission 'View Biaya Perjalanan Dinas' berhasil ditambahkan
# ⊘ Permission 'View Rekap Keterlambatan' sudah ada, dilewati
# ...
# === Summary ===
# Permission baru ditambahkan: 1
# Permission sudah ada (dilewati): X
# Total permission di database: XX
```

**⚠️ Catatan:** Seeder ini menggunakan `firstOrCreate`, jadi aman dijalankan berkali-kali tanpa membuat duplikat.

### **Langkah 5: Clear Cache**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize untuk production (opsional)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Langkah 6: Verifikasi Permission di Database**

```bash
# Login ke MySQL
mysql -u username -p hris_seven

# Cek permission sudah ditambahkan
SELECT * FROM permissions WHERE slug = 'view-biaya-perjalanan-dinas';

# Output yang diharapkan:
# +----+------------------------------+------------------------------+----------+----------------------------------+---------------------+---------------------+
# | id | name                         | slug                         | module   | description                      | created_at          | updated_at          |
# +----+------------------------------+------------------------------+----------+----------------------------------+---------------------+---------------------+
# | XX | View Biaya Perjalanan Dinas  | view-biaya-perjalanan-dinas  | absensi  | Melihat Form Biaya Perjalanan... | 2026-02-19 XX:XX:XX | 2026-02-19 XX:XX:XX |
# +----+------------------------------+------------------------------+----------+----------------------------------+---------------------+---------------------+

# Exit MySQL
EXIT;
```

### **Langkah 7: Verifikasi Route**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Cek route BPD sudah terdaftar dengan permission yang benar
php artisan route:list --name=biaya-perjalanan-dinas

# Pastikan route menggunakan middleware permission yang benar
```

---

## ✅ Testing

### **Test 1: Permission Muncul di Pengelolaan Permission**

1. Login ke aplikasi
2. Buka **Settings** → **Pengelolaan Permission**
3. Filter atau cari "Biaya Perjalanan Dinas"
4. Pastikan permission **"View Biaya Perjalanan Dinas"** muncul dengan:
   - Module: **Absensi**
   - Slug: `view-biaya-perjalanan-dinas`
   - Description: Melihat Form Biaya Perjalanan Dinas (BPD)

### **Test 2: Permission Muncul di Pengelolaan Role**

1. Buka **Settings** → **Pengelolaan Role**
2. Klik **Tambah Role** atau **Edit** role yang sudah ada
3. Scroll ke section **"Absensi"** → **"Permission Granular"**
4. Pastikan checkbox **"View Biaya Perjalanan Dinas"** muncul di list

### **Test 3: Assign Permission ke Role**

1. Di halaman **Tambah/Edit Role**, centang **"View Biaya Perjalanan Dinas"**
2. Simpan role
3. Pastikan permission ter-assign dengan benar

### **Test 4: User dengan Permission Bisa Akses Menu**

1. Assign permission `view-biaya-perjalanan-dinas` ke role user test
2. Login dengan user tersebut
3. Pastikan menu **"Form Biaya Perjalanan Dinas"** muncul di sidebar (di bawah menu Absensi)
4. Pastikan bisa mengakses halaman BPD tanpa error 403

### **Test 5: Route Middleware Bekerja**

1. Pastikan route BPD bisa diakses dengan permission:
   - `view-absensi` ✅
   - `view-perjalanan-dinas` ✅
   - `view-biaya-perjalanan-dinas` ✅ (baru)

---

## 🔍 Troubleshooting

### **Error: Permission tidak muncul di Pengelolaan Permission**

**Solusi:**
```bash
# Pastikan seeder sudah dijalankan
php artisan db:seed --class=UpdatePermissionsSeeder

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### **Error: Permission tidak muncul di Pengelolaan Role**

**Solusi:**
- Pastikan permission sudah ada di database (cek dengan query SQL di Langkah 6)
- Clear cache view: `php artisan view:clear`
- Refresh browser (Ctrl+F5)

### **Error: Menu BPD tidak muncul di sidebar**

**Solusi:**
- Pastikan user memiliki salah satu permission:
  - `view-absensi`
  - `view-perjalanan-dinas`
  - `view-biaya-perjalanan-dinas`
- Clear cache: `php artisan view:clear`
- Refresh browser

### **Error: Route tidak bisa diakses (403 Forbidden)**

**Solusi:**
- Pastikan user memiliki salah satu permission yang diperlukan
- Cek middleware di route sudah benar
- Clear route cache: `php artisan route:clear` dan `php artisan route:cache`

### **Error: Seeder gagal (duplicate entry)**

**Solusi:**
- Seeder menggunakan `firstOrCreate`, jadi seharusnya tidak ada error duplicate
- Jika masih error, cek apakah permission sudah ada:
```sql
SELECT * FROM permissions WHERE slug = 'view-biaya-perjalanan-dinas';
```
- Jika sudah ada, seeder akan skip (tidak error)

---

## ✅ Checklist Deploy

- [ ] Backup 4 file existing
- [ ] Upload 2 seeder files
- [ ] Upload routes/web.php
- [ ] Upload layouts/app.blade.php
- [ ] Set permission file & direktori
- [ ] Jalankan `php artisan db:seed --class=UpdatePermissionsSeeder`
- [ ] Clear semua cache
- [ ] Verifikasi permission di database
- [ ] Test permission muncul di Pengelolaan Permission
- [ ] Test permission muncul di Pengelolaan Role
- [ ] Test assign permission ke role
- [ ] Test user dengan permission bisa akses menu BPD
- [ ] Test route middleware bekerja dengan benar

---

## 📝 Catatan Tambahan

1. **Seeder Aman:** `UpdatePermissionsSeeder` menggunakan `firstOrCreate`, jadi bisa dijalankan berkali-kali tanpa membuat duplikat.

2. **Backward Compatibility:** Route BPD tetap bisa diakses dengan permission lama (`view-absensi` atau `view-perjalanan-dinas`), permission baru (`view-biaya-perjalanan-dinas`) adalah tambahan.

3. **Module Grouping:** Permission BPD berada di module `absensi`, jadi akan muncul di section "Absensi" di Pengelolaan Role dan Permission.

4. **Permission Granular:** Permission ini adalah permission granular (bukan group permission), jadi akan muncul di section "Permission Granular (Akses Per Submenu)" di Pengelolaan Role.

---

## 📞 Support

Jika ada masalah saat deploy:
1. Pastikan semua file sudah ter-upload dengan benar
2. Pastikan seeder sudah dijalankan
3. Pastikan cache sudah di-clear
4. Pastikan permission file sudah benar
5. Cek log error di `storage/logs/laravel.log`

**Selamat Deploy! 🚀**




