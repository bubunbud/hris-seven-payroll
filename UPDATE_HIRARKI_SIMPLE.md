# Update Perbaikan Group Hierarki ke Ubuntu Server

Panduan sederhana untuk update perbaikan form Group Hierarki ke server Ubuntu.

---

## 📋 PERUBAHAN YANG AKAN DI-UPDATE

### File yang Berubah:

1. **`app/Http/Controllers/HirarkiController.php`**

    - Tambah try-catch di method `storeSeksi` (untuk error handling JSON)
    - Hapus `dtCreate` dan `dtChange` dari insert statement di semua method (`storeDept`, `storeBagian`, `storeSeksi`)

2. **`resources/views/master/hirarki/index.blade.php`**
    - Tambah header `Accept: application/json` dan `X-Requested-With: XMLHttpRequest` di semua fetch request
    - Perbaiki error handling untuk response non-JSON

---

## 🚀 LANGKAH UPDATE (PILIH SALAH SATU)

### OPSI A: Update Manual (Paling Sederhana)

#### 1. Upload File ke Server

**Dari Windows (PowerShell/CMD):**

```powershell
# Upload HirarkiController.php
scp app/Http/Controllers/HirarkiController.php root@192.168.10.40:/tmp/

# Upload view hirarki
scp resources/views/master/hirarki/index.blade.php root@192.168.10.40:/tmp/
```

**Atau gunakan WinSCP/FileZilla:**

-   Upload `app/Http/Controllers/HirarkiController.php` ke `/tmp/`
-   Upload `resources/views/master/hirarki/index.blade.php` ke `/tmp/`

#### 2. Copy File di Server

**SSH ke server:**

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# Backup file lama (opsional tapi disarankan)
cp app/Http/Controllers/HirarkiController.php app/Http/Controllers/HirarkiController.php.backup
cp resources/views/master/hirarki/index.blade.php resources/views/master/hirarki/index.blade.php.backup

# Copy file baru
cp /tmp/HirarkiController.php app/Http/Controllers/
cp /tmp/index.blade.php resources/views/master/hirarki/

# Set ownership
chown www-data:www-data app/Http/Controllers/HirarkiController.php
chown www-data:www-data resources/views/master/hirarki/index.blade.php

# Set permissions
chmod 644 app/Http/Controllers/HirarkiController.php
chmod 644 resources/views/master/hirarki/index.blade.php
```

#### 3. Clear Cache Laravel

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

#### 4. Test Aplikasi

Buka browser dan akses:

-   http://hr.abncorp.lan/hirarki

Test fitur:

1. Tab "Group Departement": Pilih Divisi → Pilih Departemen → Klik "Tambah"
2. Tab "Group Bagian": Pilih Divisi → Departemen → Bagian → Klik "Tambah"
3. Tab "Group Seksi": Pilih Divisi → Departemen → Bagian → Seksi → Klik "Tambah"

---

### OPSI B: Menggunakan Script Otomatis

#### 1. Buat Script Update

Buat file `update-hirarki.sh` di root project:

```bash
#!/bin/bash

SERVER_IP="192.168.10.40"
SERVER_USER="root"
SERVER_PATH="/var/www/html/hris-seven-payroll"

echo "=========================================="
echo "  Update Perbaikan Group Hierarki"
echo "=========================================="
echo ""

# Upload file
echo "[1/4] Uploading files..."
scp app/Http/Controllers/HirarkiController.php ${SERVER_USER}@${SERVER_IP}:/tmp/
scp resources/views/master/hirarki/index.blade.php ${SERVER_USER}@${SERVER_IP}:/tmp/
echo "✓ Upload selesai"
echo ""

# Copy file dan set permissions
echo "[2/4] Copying files and setting permissions..."
ssh ${SERVER_USER}@${SERVER_IP} << 'ENDSSH'
cd /var/www/html/hris-seven-payroll

# Backup
cp app/Http/Controllers/HirarkiController.php app/Http/Controllers/HirarkiController.php.backup.$(date +%Y%m%d_%H%M%S)
cp resources/views/master/hirarki/index.blade.php resources/views/master/hirarki/index.blade.php.backup.$(date +%Y%m%d_%H%M%S)

# Copy file baru
cp /tmp/HirarkiController.php app/Http/Controllers/
cp /tmp/index.blade.php resources/views/master/hirarki/

# Set ownership dan permissions
chown www-data:www-data app/Http/Controllers/HirarkiController.php
chown www-data:www-data resources/views/master/hirarki/index.blade.php
chmod 644 app/Http/Controllers/HirarkiController.php
chmod 644 resources/views/master/hirarki/index.blade.php

echo "✓ File berhasil di-copy"
ENDSSH
echo ""

# Clear cache
echo "[3/4] Clearing cache..."
ssh ${SERVER_USER}@${SERVER_IP} << 'ENDSSH'
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
echo "✓ Cache berhasil di-clear"
ENDSSH
echo ""

# Cleanup
echo "[4/4] Cleaning up..."
ssh ${SERVER_USER}@${SERVER_IP} << 'ENDSSH'
rm -f /tmp/HirarkiController.php
rm -f /tmp/index.blade.php
echo "✓ Cleanup selesai"
ENDSSH
echo ""

echo "=========================================="
echo "  UPDATE SELESAI! 🎉"
echo "=========================================="
echo ""
echo "Silakan test aplikasi di:"
echo "  http://hr.abncorp.lan/hirarki"
echo ""
```

#### 2. Jalankan Script

**Dari Windows (Git Bash atau WSL):**

```bash
chmod +x update-hirarki.sh
./update-hirarki.sh
```

**Atau dari PowerShell:**

```powershell
bash update-hirarki.sh
```

---

## ✅ CHECKLIST SETELAH UPDATE

-   [ ] File berhasil di-upload ke server
-   [ ] File berhasil di-copy ke folder aplikasi
-   [ ] Permissions sudah benar (www-data:www-data)
-   [ ] Cache Laravel sudah di-clear
-   [ ] Test form Group Hierarki:
    -   [ ] Tab "Group Departement" bisa tambah data
    -   [ ] Tab "Group Bagian" bisa tambah data
    -   [ ] Tab "Group Seksi" bisa tambah data
    -   [ ] Tidak ada error di console browser (F12)
    -   [ ] Response error (jika ada) sudah dalam format JSON

---

## 🔍 TROUBLESHOOTING

### Error: Permission Denied

```bash
# Set ownership dan permissions ulang
cd /var/www/html/hris-seven-payroll
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
```

### Error: File Not Found

```bash
# Pastikan file ada di lokasi yang benar
ls -la app/Http/Controllers/HirarkiController.php
ls -la resources/views/master/hirarki/index.blade.php
```

### Error: Cache Masih Lama

```bash
# Clear cache ulang
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### Error: Response Masih HTML (Bukan JSON)

-   Pastikan `HirarkiController.php` sudah ada try-catch di semua method
-   Pastikan view sudah ada header `Accept: application/json`
-   Clear cache dan test ulang

---

## 📝 CATATAN

-   **Server Path**: `/var/www/html/hris-seven-payroll`
-   **Server URL**: `http://hr.abncorp.lan`
-   **User Web Server**: `www-data`
-   **Backup**: File lama akan di-backup dengan timestamp sebelum di-replace

---

**Selamat Update! 🚀**

