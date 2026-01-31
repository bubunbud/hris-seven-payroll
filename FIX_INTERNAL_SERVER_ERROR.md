# Fix Internal Server Error - Panduan Lengkap

Error "Internal Server Error" biasanya disebabkan oleh:

1. Permission issues
2. .env configuration error
3. Cache corrupt
4. PHP syntax error
5. Missing dependencies

---

## 🚀 Solusi Cepat (Script Otomatis)

### **Langkah 1: Debug Error**

```bash
# Upload script debug
scp debug-internal-server-error.sh root@192.168.10.40:/tmp/

# SSH ke server
ssh root@192.168.10.40

# Jalankan debug
chmod +x /tmp/debug-internal-server-error.sh
sudo /tmp/debug-internal-server-error.sh
```

Script akan menampilkan:

-   Apache error log
-   Laravel log
-   Permissions
-   .env configuration
-   PHP syntax check

### **Langkah 2: Fix Error**

```bash
# Upload script fix
scp fix-internal-server-error.sh root@192.168.10.40:/tmp/

# Jalankan fix
chmod +x /tmp/fix-internal-server-error.sh
sudo /tmp/fix-internal-server-error.sh
```

---

## 📝 Solusi Manual

### **Step 1: Check Apache Error Log**

```bash
ssh root@192.168.10.40
tail -50 /var/log/apache2/error.log
```

Cari error message yang spesifik.

### **Step 2: Check Laravel Log**

```bash
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
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
```

### **Step 4: Fix .env**

```bash
cd /var/www/html/hris-seven-payroll
sudo nano .env
```

Pastikan:

```env
APP_URL=http://192.168.10.40/hris-seven-payroll
ASSET_URL=http://192.168.10.40/hris-seven-payroll
APP_DEBUG=true
APP_ENV=local
```

**Tidak ada trailing slash!**

### **Step 5: Fix .htaccess**

```bash
sudo nano /var/www/html/hris-seven-payroll/public/.htaccess
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

### **Step 6: Clear All Cache**

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua
sudo -u www-data php artisan optimize:clear
sudo rm -f bootstrap/cache/routes*.php

# Rebuild (tanpa route cache)
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache
```

### **Step 7: Test PHP Syntax**

```bash
cd /var/www/html/hris-seven-payroll
php -l public/index.php
php -l routes/web.php
```

Jika ada syntax error, fix file tersebut.

---

## 🔍 Common Errors & Solutions

### **Error: Permission Denied**

```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

### **Error: Class Not Found**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data composer dump-autoload --optimize
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
```

### **Error: Database Connection**

```bash
# Check .env
cat /var/www/html/hris-seven-payroll/.env | grep DB_

# Test connection
mysql -u root -proot123 -e "USE hris_seven; SHOW TABLES;"
```

### **Error: Storage Not Writable**

```bash
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/storage
```

### **Error: .env File Missing**

```bash
cd /var/www/html/hris-seven-payroll
cp .env.example .env  # Jika ada
# atau buat manual
php artisan key:generate
```

---

## 🎯 Quick Fix (All in One)

Jalankan semua command ini:

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# 1. Fix permissions
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 775 storage bootstrap/cache

# 2. Fix .env
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^APP_DEBUG=.*|APP_DEBUG=true|g' .env

# 3. Fix .htaccess
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

# 4. Clear cache
sudo -u www-data php artisan optimize:clear
sudo rm -f bootstrap/cache/routes*.php

# 5. Rebuild
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache

# 6. Check error log
echo "Check Apache error log:"
tail -20 /var/log/apache2/error.log
```

---

## ✅ Checklist

-   [ ] Permissions sudah benar (www-data:www-data, 775 untuk storage/cache)
-   [ ] .env sudah di-update (APP_URL, ASSET_URL, APP_DEBUG=true)
-   [ ] .htaccess sudah benar (tanpa RewriteBase)
-   [ ] Cache sudah di-clear
-   [ ] PHP syntax tidak ada error
-   [ ] Apache error log sudah di-check
-   [ ] Laravel log sudah di-check

---

**SELESAI!** 🎉

Setelah menjalankan fix, test aplikasi lagi dan check log jika masih ada error.




