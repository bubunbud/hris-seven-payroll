# Fix Konflik Konfigurasi Apache

Masalah: Virtual host `hris-seven-payroll.conf` masih aktif dan mengarah ke root, padahal kita ingin akses via subfolder `/hris-seven-payroll`.

---

## 🚀 Solusi Cepat (Script Otomatis)

### **Langkah 1: Upload dan Jalankan Script**

```bash
# Upload script
scp fix-apache-config-conflict.sh root@192.168.10.40:/tmp/

# SSH ke server
ssh root@192.168.10.40

# Jalankan script
chmod +x /tmp/fix-apache-config-conflict.sh
sudo /tmp/fix-apache-config-conflict.sh
```

Script akan:

-   ✅ Disable `hris-seven-payroll.conf`
-   ✅ Update `000-default.conf` dengan Alias (jika belum ada)
-   ✅ Enable `000-default.conf`
-   ✅ Fix `.htaccess` (hapus RewriteBase)
-   ✅ Test dan restart Apache

---

## 📝 Solusi Manual

### **Step 1: Disable hris-seven-payroll.conf**

```bash
ssh root@192.168.10.40
sudo a2dissite hris-seven-payroll.conf
```

### **Step 2: Pastikan 000-default.conf Punya Alias**

```bash
sudo nano /etc/apache2/sites-available/000-default.conf
```

Pastikan isinya seperti ini:

```apache
<VirtualHost *:80>
    ServerName 192.168.10.40
    ServerAdmin webmaster@localhost

    # Default DocumentRoot untuk aplikasi lain
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Alias untuk HRIS Seven Payroll
    Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

    <Directory /var/www/html/hris-seven-payroll/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>

    # Alias untuk SevenL (jika ada)
    Alias /sevenl /var/www/html/sevenl/public

    <Directory /var/www/html/sevenl/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### **Step 3: Enable 000-default.conf**

```bash
sudo a2ensite 000-default.conf
```

### **Step 4: Fix .htaccess**

```bash
sudo nano /var/www/html/hris-seven-payroll/public/.htaccess
```

Pastikan TANPA RewriteBase:

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

### **Step 5: Test dan Restart Apache**

```bash
# Test konfigurasi
sudo apache2ctl configtest

# Jika OK, restart
sudo systemctl restart apache2
```

### **Step 6: Fix Laravel Configuration**

```bash
cd /var/www/html/hris-seven-payroll

# Update .env
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^APP_DEBUG=.*|APP_DEBUG=true|g' .env

# Clear cache
sudo -u www-data php artisan optimize:clear
sudo rm -f bootstrap/cache/routes*.php

# Rebuild
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache
```

---

## ✅ Verifikasi

### **1. Check Active Sites**

```bash
a2query -s
```

Harus muncul:

```
000-default (enabled)
```

Tidak boleh ada:

```
hris-seven-payroll (enabled)
```

### **2. Check Apache Config**

```bash
apache2ctl -S
```

Harus menunjukkan:

-   `*:80` menggunakan `000-default.conf`
-   Alias `/hris-seven-payroll` mengarah ke `/var/www/html/hris-seven-payroll/public`

### **3. Test Aplikasi**

Akses: `http://192.168.10.40/hris-seven-payroll`

Harus bisa diakses tanpa error.

---

## 🎯 Quick Fix (All in One)

Jalankan semua command ini:

```bash
ssh root@192.168.10.40

# 1. Disable virtual host
sudo a2dissite hris-seven-payroll.conf

# 2. Update 000-default.conf dengan Alias
sudo tee /etc/apache2/sites-available/000-default.conf > /dev/null <<'EOF'
<VirtualHost *:80>
    ServerName 192.168.10.40
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public
    <Directory /var/www/html/hris-seven-payroll/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# 3. Enable 000-default
sudo a2ensite 000-default.conf

# 4. Fix .htaccess
sudo tee /var/www/html/hris-seven-payroll/public/.htaccess > /dev/null <<'EOF'
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

# 5. Fix Laravel config
cd /var/www/html/hris-seven-payroll
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^APP_DEBUG=.*|APP_DEBUG=true|g' .env
sudo -u www-data php artisan optimize:clear
sudo rm -f bootstrap/cache/routes*.php
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache

# 6. Test dan restart Apache
sudo apache2ctl configtest && sudo systemctl restart apache2

# 7. Verifikasi
echo "=== Active Sites ==="
a2query -s
echo ""
echo "=== Test: http://192.168.10.40/hris-seven-payroll ==="
```

---

## ⚠️ Troubleshooting

### **Masih Error Setelah Fix?**

1. **Check Active Sites:**

    ```bash
    a2query -s
    ```

    Pastikan hanya `000-default` yang enabled.

2. **Check Apache Config:**

    ```bash
    apache2ctl -S
    ```

    Pastikan Alias `/hris-seven-payroll` ada.

3. **Check Error Log:**

    ```bash
    tail -50 /var/log/apache2/error.log
    tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
    ```

4. **Reload Apache:**
    ```bash
    sudo systemctl reload apache2
    ```

---

**SELESAI!** 🎉

Setelah fix, pastikan hanya `000-default.conf` yang aktif dan aplikasi bisa diakses di `/hris-seven-payroll`.




