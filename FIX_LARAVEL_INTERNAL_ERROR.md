# Fix Internal Server Error - HRIS Seven Payroll (Laravel)

Masalah: Aplikasi lain bisa jalan normal, tapi HRIS Seven Payroll (Laravel) Internal Server Error.

---

## 🚀 Solusi Cepat (Script Otomatis)

### **Langkah 1: Upload dan Jalankan Script**

```bash
# Upload script
scp fix-laravel-internal-error.sh root@192.168.10.40:/tmp/

# SSH ke server
ssh root@192.168.10.40

# Jalankan script
chmod +x /tmp/fix-laravel-internal-error.sh
sudo /tmp/fix-laravel-internal-error.sh
```

Script akan:

-   ✅ Fix `.env` (APP_URL, ASSET_URL, APP_DEBUG)
-   ✅ Fix `.htaccess` (hapus RewriteBase)
-   ✅ Fix permissions
-   ✅ Check PHP syntax
-   ✅ Clear semua cache
-   ✅ Regenerate autoload
-   ✅ Rebuild cache (tanpa route cache)
-   ✅ Test Laravel
-   ✅ Check error logs

---

## 📝 Solusi Manual (Step by Step)

### **Step 1: Fix .env**

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# Backup
cp .env .env.backup

# Update .env
sudo nano .env
```

Pastikan:

```env
APP_URL=http://192.168.10.40/hris-seven-payroll
ASSET_URL=http://192.168.10.40/hris-seven-payroll
APP_DEBUG=true
APP_ENV=local
```

**PENTING**: Tidak ada trailing slash!

### **Step 2: Fix .htaccess**

```bash
sudo nano public/.htaccess
```

Pastikan isinya (TANPA RewriteBase):

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### **Step 3: Fix Permissions**

```bash
cd /var/www/html/hris-seven-payroll

# Set ownership
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll

# Set permissions
sudo find /var/www/html/hris-seven-payroll -type d -exec chmod 755 {} \;
sudo find /var/www/html/hris-seven-payroll -type f -exec chmod 644 {} \;

# Set writable untuk storage dan cache
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
sudo chmod 755 /var/www/html/hris-seven-payroll/artisan
```

### **Step 4: Clear All Cache**

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua
sudo -u www-data php artisan optimize:clear
sudo rm -f bootstrap/cache/routes*.php
sudo rm -f bootstrap/cache/config.php
```

### **Step 5: Regenerate Autoload**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data composer dump-autoload --optimize
```

### **Step 6: Rebuild Cache (Tanpa Route Cache)**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache
# Jangan rebuild route cache dulu
```

### **Step 7: Test Laravel**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan --version
```

Jika ada error, fix error tersebut terlebih dahulu.

---

## 🎯 Quick Fix (All in One)

Jalankan semua command ini:

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# 1. Fix .env
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^APP_DEBUG=.*|APP_DEBUG=true|g' .env
sed -i 's|APP_URL=\(.*\)/$|APP_URL=\1|g' .env
sed -i 's|ASSET_URL=\(.*\)/$|ASSET_URL=\1|g' .env

# 2. Fix .htaccess
cat > public/.htaccess <<'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF

# 3. Fix permissions
chown -R www-data:www-data /var/www/html/hris-seven-payroll
chmod -R 775 storage bootstrap/cache
chmod 755 artisan

# 4. Clear cache
sudo -u www-data php artisan optimize:clear
rm -f bootstrap/cache/routes*.php bootstrap/cache/config.php

# 5. Regenerate autoload
sudo -u www-data composer dump-autoload --optimize

# 6. Rebuild cache
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache

# 7. Test
echo "=== Laravel Version ==="
sudo -u www-data php artisan --version
echo ""
echo "=== Test: http://192.168.10.40/hris-seven-payroll ==="
```

---

## 🔍 Debugging

### **Check Apache Error Log**

```bash
tail -50 /var/log/apache2/error.log
```

Cari error spesifik yang terkait dengan Laravel.

### **Check Laravel Log**

```bash
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

### **Test PHP**

```bash
php -r "echo 'PHP OK';"
```

### **Test Laravel Artisan**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan --version
```

Jika error, fix error tersebut.

### **Check File yang Di-update**

Pastikan file-file berikut sudah di-update dari localhost:

```bash
# Check beberapa file penting
ls -la app/Http/Controllers/InstruksiKerjaLemburController.php
ls -la app/Services/LemburCalculationService.php
ls -la routes/web.php
ls -la resources/views/instruksi-kerja-lembur/index.blade.php
```

Jika file tidak ada atau ukurannya 0, berarti belum ter-update dengan benar.

---

## ⚠️ Common Issues

### **1. File Belum Ter-update**

Jika file belum ter-update dari localhost:

```bash
# Check apakah file Services ada
ls -la app/Services/LemburCalculationService.php

# Jika tidak ada, perlu upload lagi dari localhost
```

### **2. Autoload Error**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data composer dump-autoload --optimize
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
```

### **3. Permission Error**

```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

### **4. .htaccess Error**

Pastikan `.htaccess` **TIDAK** menggunakan `RewriteBase` karena menggunakan Alias.

---

## ✅ Checklist

-   [ ] `.env` sudah di-update (APP_URL, ASSET_URL, APP_DEBUG=true)
-   [ ] `.htaccess` sudah di-fix (TANPA RewriteBase)
-   [ ] Permissions sudah benar (www-data:www-data, 775 untuk storage/cache)
-   [ ] Cache sudah di-clear
-   [ ] Autoload sudah di-regenerate
-   [ ] Config cache sudah di-rebuild
-   [ ] Laravel artisan bisa dijalankan
-   [ ] File dari localhost sudah ter-update dengan benar

---

**SELESAI!** 🎉

Setelah fix, test aplikasi di `http://192.168.10.40/hris-seven-payroll`. Jika masih error, kirimkan output dari Apache error log dan Laravel log.




