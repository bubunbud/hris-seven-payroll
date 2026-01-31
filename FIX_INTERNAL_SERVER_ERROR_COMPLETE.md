# Fix Internal Server Error - Panduan Lengkap

**Catatan Penting:**

-   Server Ubuntu menggunakan **Apache web server** (port 80, tidak perlu port khusus)
-   **Bukan** seperti localhost yang pakai `php artisan serve` (port 8000)
-   Akses aplikasi di: `http://192.168.10.40/hris-seven-payroll`

---

## 🚀 Solusi Cepat (Script Otomatis)

### **Langkah 1: Upload dan Jalankan Script**

```bash
# Upload script dari localhost ke server
scp fix-internal-server-error-complete.sh root@192.168.10.40:/tmp/

# SSH ke server
ssh root@192.168.10.40

# Jalankan script
chmod +x /tmp/fix-internal-server-error-complete.sh
sudo /tmp/fix-internal-server-error-complete.sh
```

Script akan otomatis:

-   ✅ Check dan fix Apache configuration (Alias)
-   ✅ Fix permissions (www-data:www-data)
-   ✅ Fix .env (APP_URL, ASSET_URL, APP_DEBUG)
-   ✅ Fix .htaccess (TANPA RewriteBase)
-   ✅ Clear dan rebuild cache
-   ✅ Optimize autoload
-   ✅ Test dan restart Apache
-   ✅ Show error logs

---

## 📝 Solusi Manual (Step by Step)

### **Step 1: Check Apache Configuration**

```bash
ssh root@192.168.10.40
cat /etc/apache2/sites-available/000-default.conf | grep -A 10 "hris-seven-payroll"
```

Pastikan ada:

```apache
Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

<Directory /var/www/html/hris-seven-payroll/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
    DirectoryIndex index.php index.html
</Directory>
```

Jika belum ada, tambahkan sebelum `ErrorLog` di `000-default.conf`:

```bash
sudo nano /etc/apache2/sites-available/000-default.conf
```

### **Step 2: Disable Conflicting Virtual Host**

```bash
# Disable hris-seven-payroll.conf jika ada
sudo a2dissite hris-seven-payroll.conf

# Enable 000-default.conf
sudo a2ensite 000-default.conf
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

# Ensure storage/logs exists
sudo mkdir -p /var/www/html/hris-seven-payroll/storage/logs
sudo touch /var/www/html/hris-seven-payroll/storage/logs/laravel.log
sudo chown www-data:www-data /var/www/html/hris-seven-payroll/storage/logs/laravel.log
sudo chmod 664 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
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

Atau gunakan sed:

```bash
sudo sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sudo sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sudo sed -i 's|^APP_DEBUG=.*|APP_DEBUG=true|g' .env
sudo sed -i 's|^APP_ENV=.*|APP_ENV=local|g' .env
```

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
sudo rm -f bootstrap/cache/config.php

# Rebuild (tanpa route cache)
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache
```

### **Step 7: Optimize Autoload**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data composer dump-autoload --optimize
```

### **Step 8: Test Apache Configuration**

```bash
# Test configuration
sudo apache2ctl configtest

# Jika OK, restart Apache
sudo systemctl restart apache2

# Check status
sudo systemctl status apache2
```

### **Step 9: Check Error Logs**

```bash
# Apache error log
tail -50 /var/log/apache2/error.log

# Laravel log
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

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
php artisan key:generate
```

### **Error: Apache Alias Not Working**

```bash
# Check if mod_rewrite enabled
sudo a2enmod rewrite

# Check Apache config
sudo apache2ctl -S

# Restart Apache
sudo systemctl restart apache2
```

---

## ✅ Checklist

-   [ ] Apache configuration sudah benar (Alias di 000-default.conf)
-   [ ] hris-seven-payroll.conf sudah disabled
-   [ ] 000-default.conf sudah enabled
-   [ ] Permissions sudah benar (www-data:www-data, 775 untuk storage/cache)
-   [ ] .env sudah di-update (APP_URL, ASSET_URL, APP_DEBUG=true)
-   [ ] .htaccess sudah benar (tanpa RewriteBase)
-   [ ] Cache sudah di-clear dan rebuild
-   [ ] Autoload sudah di-optimize
-   [ ] Apache configuration test passed
-   [ ] Apache sudah di-restart
-   [ ] Error logs sudah di-check

---

## 🎯 Quick Fix (All in One Command)

```bash
ssh root@192.168.10.40 <<'EOF'
cd /var/www/html/hris-seven-payroll

# 1. Fix permissions
chown -R www-data:www-data /var/www/html/hris-seven-payroll
chmod -R 775 storage bootstrap/cache

# 2. Fix .env
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^APP_DEBUG=.*|APP_DEBUG=true|g' .env

# 3. Fix .htaccess
cat > public/.htaccess <<'HTACCESS'
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
HTACCESS

# 4. Clear cache
sudo -u www-data php artisan optimize:clear
rm -f bootstrap/cache/routes*.php

# 5. Rebuild
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache

# 6. Restart Apache
systemctl restart apache2

# 7. Check logs
echo "=== Apache Error Log ==="
tail -20 /var/log/apache2/error.log
echo ""
echo "=== Laravel Log ==="
tail -20 storage/logs/laravel.log
EOF
```

---

## 📌 Catatan Penting

1. **Port:**

    - Localhost: `php artisan serve` → port 8000
    - Server Ubuntu: Apache → port 80 (default, tidak perlu port khusus)

2. **URL Akses:**

    - Localhost: `http://localhost:8000`
    - Server Ubuntu: `http://192.168.10.40/hris-seven-payroll`

3. **.htaccess:**

    - Jangan pakai `RewriteBase` karena menggunakan Apache Alias
    - Jika pakai `RewriteBase`, akan terjadi redirect loop

4. **.env:**

    - `APP_URL` dan `ASSET_URL` harus include subfolder path
    - Tidak ada trailing slash di akhir

5. **Cache:**
    - Selalu clear cache setelah update .env
    - Jangan rebuild route cache jika ada masalah routing

---

**SELESAI!** 🎉

Setelah menjalankan fix, test aplikasi di: `http://192.168.10.40/hris-seven-payroll`

Jika masih error, check logs dan share error message untuk troubleshooting lebih lanjut.



