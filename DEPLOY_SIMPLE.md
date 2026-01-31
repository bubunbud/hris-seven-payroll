# Panduan Deploy Sederhana - HRIS Seven Payroll

**Tujuan:** Deploy aplikasi dari localhost ke server Ubuntu dengan langkah sederhana.

---

## 📋 Prasyarat

1. Server Ubuntu sudah terinstall:

    - Apache2
    - PHP 8.x
    - MySQL
    - Composer

2. Database sudah disamakan antara localhost dan server (tidak perlu migrate)

3. Akses SSH ke server: `ssh root@192.168.10.40`

---

## 🚀 Langkah Deploy (Sederhana)

### **Step 1: Upload File dari Localhost ke Server**

```bash
# Dari localhost (Windows PowerShell atau CMD)
cd C:\xampp\htdocs\hris-seven-payroll

# Upload semua file ke server (kecuali vendor, node_modules, dll)
scp -r app bootstrap config database public resources routes storage vendor artisan composer.json composer.lock .env root@192.168.10.40:/var/www/html/hris-seven-payroll/
```

**Atau gunakan FileZilla/WinSCP** untuk upload file secara manual.

---

### **Step 2: Setup di Server (SSH ke Server)**

```bash
# SSH ke server
ssh root@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll
```

---

### **Step 3: Setup .env**

```bash
# Pastikan .env ada
ls -la .env

# Edit .env (jika perlu)
nano .env
```

**Pastikan di .env:**

```env
APP_URL=http://192.168.10.40/hris-seven-payroll
ASSET_URL=http://192.168.10.40/hris-seven-payroll
APP_DEBUG=true
APP_ENV=local

# Database (sesuaikan dengan server)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris_seven
DB_USERNAME=root
DB_PASSWORD=root123
```

**TIDAK ADA trailing slash di APP_URL dan ASSET_URL!**

---

### **Step 4: Setup Permissions**

```bash
# Set ownership
chown -R www-data:www-data /var/www/html/hris-seven-payroll

# Set permissions
find /var/www/html/hris-seven-payroll -type d -exec chmod 755 {} \;
find /var/www/html/hris-seven-payroll -type f -exec chmod 644 {} \;

# Set writable untuk storage dan cache
chmod -R 775 storage bootstrap/cache

# Pastikan storage/logs ada
mkdir -p storage/logs
touch storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
```

---

### **Step 5: Setup .htaccess (SEDERHANA)**

```bash
# Edit .htaccess
nano public/.htaccess
```

**Isi dengan ini saja (SEDERHANA, tanpa rule redirect trailing slash):**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**Simpan:** `Ctrl+X`, lalu `Y`, lalu `Enter`

---

### **Step 6: Setup Apache (SEDERHANA)**

```bash
# Edit Apache config
nano /etc/apache2/sites-available/000-default.conf
```

**Pastikan ada ini di dalam `<VirtualHost *:80>`:**

```apache
<VirtualHost *:80>
    ServerName 192.168.10.40
    DocumentRoot /var/www/html

    # Alias untuk HRIS Seven Payroll
    Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

    <Directory /var/www/html/hris-seven-payroll/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

**Simpan:** `Ctrl+X`, lalu `Y`, lalu `Enter`

---

### **Step 7: Enable mod_rewrite dan Restart Apache**

```bash
# Enable mod_rewrite
a2enmod rewrite

# Disable virtual host lain (jika ada)
a2dissite hris-seven-payroll.conf 2>/dev/null || true

# Enable default site
a2ensite 000-default.conf

# Test Apache config
apache2ctl configtest

# Jika OK, restart Apache
systemctl restart apache2
```

---

### **Step 8: Clear Cache Laravel**

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Hapus cache files
rm -f bootstrap/cache/routes*.php
rm -f bootstrap/cache/config.php

# Rebuild config cache
sudo -u www-data php artisan config:cache
```

---

### **Step 9: Test Aplikasi**

```bash
# Test dari server
curl -I http://192.168.10.40/hris-seven-payroll
```

**Harusnya dapat response `200 OK`, bukan `301` atau `500`.**

**Atau buka browser:** `http://192.168.10.40/hris-seven-payroll`

---

## ✅ Checklist Deploy

-   [ ] File sudah di-upload ke server
-   [ ] .env sudah di-setup (APP_URL, ASSET_URL, database)
-   [ ] Permissions sudah benar (www-data:www-data, 775 untuk storage/cache)
-   [ ] .htaccess sudah sederhana (tanpa rule redirect trailing slash)
-   [ ] Apache config sudah ada Alias
-   [ ] mod_rewrite sudah enabled
-   [ ] Apache sudah di-restart
-   [ ] Cache sudah di-clear
-   [ ] Aplikasi bisa diakses di browser

---

## 🔧 Troubleshooting

### **Masalah: Internal Server Error (500)**

```bash
# Check Apache error log
tail -50 /var/log/apache2/error.log

# Check Laravel log
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

### **Masalah: Redirect Loop (301)**

```bash
# Pastikan .htaccess SEDERHANA (lihat Step 5)
# Jangan ada rule redirect trailing slash!
```

### **Masalah: Permission Denied**

```bash
# Fix permissions
chown -R www-data:www-data /var/www/html/hris-seven-payroll
chmod -R 775 storage bootstrap/cache
```

### **Masalah: Route Not Found**

```bash
# Clear route cache
rm -f bootstrap/cache/routes*.php
sudo -u www-data php artisan route:clear
```

---

## 📝 Catatan Penting

1. **.htaccess harus SEDERHANA** - hanya rule dasar untuk Laravel, tanpa redirect trailing slash
2. **APP_URL tidak boleh ada trailing slash** - `http://192.168.10.40/hris-seven-payroll` (bukan `/hris-seven-payroll/`)
3. **Apache Alias harus ada** - di `000-default.conf`
4. **Storage harus writable** - `chmod 775 storage`
5. **Cache harus di-clear** - setelah update .env atau file

---

## 🎯 Quick Deploy Script (All in One)

Jika ingin otomatis, buat file `deploy-simple.sh` di server:

```bash
#!/bin/bash
cd /var/www/html/hris-seven-payroll

# Fix .env
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' .env

# Fix .htaccess (SEDERHANA)
cat > public/.htaccess <<'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF

# Fix permissions
chown -R www-data:www-data /var/www/html/hris-seven-payroll
chmod -R 775 storage bootstrap/cache

# Clear cache
sudo -u www-data php artisan optimize:clear
rm -f bootstrap/cache/routes*.php

# Rebuild
sudo -u www-data php artisan config:cache

# Restart Apache
systemctl restart apache2

echo "Deploy selesai! Test: http://192.168.10.40/hris-seven-payroll"
```

Jalankan:

```bash
chmod +x deploy-simple.sh
sudo ./deploy-simple.sh
```

---

**SELESAI!** 🎉

Dengan langkah-langkah sederhana ini, aplikasi seharusnya bisa diakses di `http://192.168.10.40/hris-seven-payroll`



