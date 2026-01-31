# Fix Apache Config - Solusi DEFINITIF

**Masalah:** Redirect loop 301 dari `/hris-seven-payroll` ke `/hris-seven-payroll/`

**Penyebab:** Konfigurasi Apache yang tidak tepat atau konflik antara virtual host.

---

## 🎯 Solusi DEFINITIF (Step by Step)

### **Step 1: Check File Apache Config**

Buka file `/etc/apache2/sites-available/000-default.conf` dan pastikan isinya seperti ini:

```apache
<VirtualHost *:80>
    ServerName 192.168.10.40
    ServerAdmin webmaster@localhost

    # Default DocumentRoot
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
        DirectoryIndex index.php
        DirectorySlash Off
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

**PENTING:**

-   Harus ada `DirectorySlash Off` di dalam `<Directory>` block
-   `DirectoryIndex index.php` (bukan `index.php index.html`)

---

### **Step 2: Disable Virtual Host Lain**

```bash
# Disable hris-seven-payroll.conf (jika ada)
a2dissite hris-seven-payroll.conf

# Enable 000-default.conf
a2ensite 000-default.conf
```

---

### **Step 3: Fix .htaccess (SANGAT SEDERHANA)**

File `/var/www/html/hris-seven-payroll/public/.htaccess` harus seperti ini:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**TIDAK ADA:**

-   ❌ `RewriteBase`
-   ❌ `DirectorySlash Off` (sudah di Apache config)
-   ❌ Rule redirect trailing slash

---

### **Step 4: Test dan Restart**

```bash
# Test Apache config
apache2ctl configtest

# Jika OK, restart
systemctl restart apache2

# Test
curl -I http://192.168.10.40/hris-seven-payroll
```

---

## 🔧 Script All-in-One

Jalankan script ini di server:

```bash
#!/bin/bash
cd /var/www/html/hris-seven-payroll

# 1. Fix .htaccess (SEDERHANA)
cat > public/.htaccess <<'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF

# 2. Fix Apache config
cat > /etc/apache2/sites-available/000-default.conf <<'EOF'
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
        DirectoryIndex index.php
        DirectorySlash Off
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# 3. Disable conflicting virtual host
a2dissite hris-seven-payroll.conf 2>/dev/null || true
a2ensite 000-default.conf

# 4. Enable mod_rewrite
a2enmod rewrite

# 5. Test and restart
apache2ctl configtest && systemctl restart apache2

# 6. Test
curl -I http://192.168.10.40/hris-seven-payroll
```

---

## ✅ Checklist

-   [ ] File `000-default.conf` sudah benar (ada `DirectorySlash Off`)
-   [ ] File `.htaccess` SEDERHANA (tidak ada redirect rule)
-   [ ] `hris-seven-payroll.conf` sudah disabled
-   [ ] `000-default.conf` sudah enabled
-   [ ] `mod_rewrite` sudah enabled
-   [ ] Apache sudah di-restart
-   [ ] Test `curl -I` dapat `200 OK` atau `302` (bukan `301`)

---

**KUNCI UTAMA:**

-   `DirectorySlash Off` di Apache config (bukan di .htaccess)
-   `.htaccess` harus SEDERHANA (hanya rule Laravel)



