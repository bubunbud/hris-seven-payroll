# Setup Multiple Apps di Apache Server Ubuntu

Panduan untuk mengkonfigurasi Apache agar bisa mengakses beberapa aplikasi dari path yang berbeda:

-   `http://192.168.10.40/hris-seven-payroll`
-   `http://192.168.10.40/sevenl`
-   `http://192.168.10.40/` (aplikasi lain)

---

## 🚀 Cara Setup (Otomatis)

### **Opsi 1: Menggunakan Script Otomatis** ⚡ (Recommended)

```bash
# Upload script ke server
scp setup-multiple-apps-apache.sh root@192.168.10.40:/tmp/

# SSH ke server
ssh root@192.168.10.40

# Berikan permission execute
chmod +x /tmp/setup-multiple-apps-apache.sh

# Jalankan script
sudo /tmp/setup-multiple-apps-apache.sh
```

Script akan otomatis:

-   ✅ Backup `000-default.conf`
-   ✅ Update `000-default.conf` dengan Alias untuk multiple apps
-   ✅ Disable `hris-seven-payroll.conf`
-   ✅ Enable `000-default.conf`
-   ✅ Fix `.htaccess` untuk hris-seven-payroll (tanpa RewriteBase)
-   ✅ Test dan restart Apache

---

## 📝 Cara Setup (Manual)

### **LANGKAH 1: Backup Konfigurasi**

```bash
ssh root@192.168.10.40
cd /etc/apache2/sites-available
cp 000-default.conf 000-default.conf.backup
```

### **LANGKAH 2: Update 000-default.conf**

```bash
sudo nano /etc/apache2/sites-available/000-default.conf
```

Ganti isinya dengan:

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

    # Tambahkan Alias untuk aplikasi lain di sini jika diperlukan
    # Contoh:
    # Alias /app-lain /var/www/html/app-lain/public
    # <Directory /var/www/html/app-lain/public>
    #     Options -Indexes +FollowSymLinks
    #     AllowOverride All
    #     Require all granted
    #     DirectoryIndex index.php index.html
    # </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### **LANGKAH 3: Disable hris-seven-payroll.conf**

```bash
sudo a2dissite hris-seven-payroll.conf
```

### **LANGKAH 4: Enable 000-default.conf**

```bash
sudo a2ensite 000-default.conf
```

### **LANGKAH 5: Fix .htaccess untuk hris-seven-payroll**

Karena menggunakan Alias, `.htaccess` **TIDAK** boleh menggunakan `RewriteBase`.

```bash
sudo nano /var/www/html/hris-seven-payroll/public/.htaccess
```

Pastikan isinya seperti ini (TANPA RewriteBase):

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

### **LANGKAH 6: Test dan Restart Apache**

```bash
# Test konfigurasi
sudo apache2ctl configtest

# Jika test berhasil, restart Apache
sudo systemctl restart apache2
```

---

## ➕ Menambah Aplikasi Baru

Untuk menambah aplikasi baru (misalnya `/app-baru`), tambahkan di `000-default.conf`:

```apache
# Alias untuk App Baru
Alias /app-baru /var/www/html/app-baru/public

<Directory /var/www/html/app-baru/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
    DirectoryIndex index.php index.html
</Directory>
```

Kemudian:

```bash
sudo apache2ctl configtest
sudo systemctl restart apache2
```

---

## ✅ Verifikasi

Setelah setup, test akses:

1. **HRIS Seven Payroll**: `http://192.168.10.40/hris-seven-payroll`
2. **SevenL**: `http://192.168.10.40/sevenl`
3. **Aplikasi lain**: `http://192.168.10.40/`

---

## ⚠️ Troubleshooting

### Error: 404 Not Found

**Penyebab**: Folder aplikasi tidak ada atau path salah

**Solusi**:

```bash
# Check apakah folder ada
ls -la /var/www/html/hris-seven-payroll/public
ls -la /var/www/html/sevenl/public

# Pastikan path di Alias sesuai dengan lokasi folder
```

### Error: 500 Internal Server Error

**Penyebab**: `.htaccess` masih menggunakan `RewriteBase`

**Solusi**:

```bash
# Check .htaccess
cat /var/www/html/hris-seven-payroll/public/.htaccess | grep RewriteBase

# Jika ada RewriteBase, hapus baris tersebut
sudo nano /var/www/html/hris-seven-payroll/public/.htaccess
```

### Error: Forbidden (403)

**Penyebab**: Permission folder tidak benar

**Solusi**:

```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
```

### Error: Apache Config Test Failed

**Penyebab**: Syntax error di konfigurasi

**Solusi**:

```bash
# Check error detail
sudo apache2ctl configtest

# Restore backup jika perlu
sudo cp /etc/apache2/sites-available/000-default.conf.backup /etc/apache2/sites-available/000-default.conf
```

---

## 📋 Checklist Setup

-   [ ] Backup `000-default.conf`
-   [ ] Update `000-default.conf` dengan Alias
-   [ ] Disable `hris-seven-payroll.conf`
-   [ ] Enable `000-default.conf`
-   [ ] Fix `.htaccess` (hapus RewriteBase)
-   [ ] Test Apache configuration
-   [ ] Restart Apache
-   [ ] Test akses semua aplikasi

---

## 🔄 Rollback (Jika Perlu)

Jika ada masalah, restore backup:

```bash
sudo cp /etc/apache2/sites-available/000-default.conf.backup /etc/apache2/sites-available/000-default.conf
sudo a2dissite 000-default.conf
sudo a2ensite hris-seven-payroll.conf
sudo systemctl restart apache2
```

---

**SELESAI!** 🎉

Setelah setup, semua aplikasi bisa diakses dari path yang berbeda.




