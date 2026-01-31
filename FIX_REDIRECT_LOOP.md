# Fix Redirect Loop - Request Exceeded 10 Internal Redirects

**Masalah**: Error "Request exceeded the limit of 10 internal redirects" - ini adalah redirect loop!

**Penyebab**: `.htaccess` masih menggunakan `RewriteBase` padahal menggunakan Alias di Apache.

---

## 🚨 MASALAH UTAMA

File `.htaccess` di localhost masih ada `RewriteBase /hris-seven-payroll/public/` yang menyebabkan redirect loop ketika menggunakan Alias.

**Ketika menggunakan Alias di Apache, `.htaccess` TIDAK boleh menggunakan `RewriteBase`!**

---

## 🚀 Solusi Cepat

### **Langkah 1: Fix .htaccess di Localhost (SUDAH DILAKUKAN)**

File `public/.htaccess` sudah di-fix - `RewriteBase` sudah dihapus.

### **Langkah 2: Upload .htaccess yang Sudah Di-fix ke Server**

```bash
# Upload .htaccess yang sudah di-fix
scp public/.htaccess root@192.168.10.40:/tmp/htaccess-fixed

# SSH ke server
ssh root@192.168.10.40

# Copy ke folder aplikasi
sudo cp /tmp/htaccess-fixed /var/www/html/hris-seven-payroll/public/.htaccess
sudo chown www-data:www-data /var/www/html/hris-seven-payroll/public/.htaccess
sudo chmod 644 /var/www/html/hris-seven-payroll/public/.htaccess
```

### **Langkah 3: Atau Gunakan Script Otomatis**

```bash
# Upload script
scp fix-redirect-loop-final.sh root@192.168.10.40:/tmp/

# Jalankan di server
ssh root@192.168.10.40
chmod +x /tmp/fix-redirect-loop-final.sh
sudo /tmp/fix-redirect-loop-final.sh
```

---

## 📝 Solusi Manual di Server

### **Step 1: Fix .htaccess di Server**

```bash
ssh root@192.168.10.40
sudo nano /var/www/html/hris-seven-payroll/public/.htaccess
```

**HAPUS baris `RewriteBase`** dan pastikan isinya seperti ini:

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

**PENTING**: Tidak ada `RewriteBase` di dalam file!

### **Step 2: Hapus .htaccess dari Root Project (jika ada)**

```bash
# Check apakah ada .htaccess di root
ls -la /var/www/html/hris-seven-payroll/.htaccess

# Jika ada, hapus atau backup
sudo mv /var/www/html/hris-seven-payroll/.htaccess /var/www/html/hris-seven-payroll/.htaccess.backup
```

### **Step 3: Reload Apache**

```bash
sudo systemctl reload apache2
```

### **Step 4: Clear Laravel Cache**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo rm -f bootstrap/cache/routes*.php
```

---

## 🎯 Quick Fix (All in One)

Jalankan semua command ini:

```bash
ssh root@192.168.10.40

# 1. Fix .htaccess (HAPUS RewriteBase)
cat > /var/www/html/hris-seven-payroll/public/.htaccess <<'EOF'
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

# 2. Set permissions
chown www-data:www-data /var/www/html/hris-seven-payroll/public/.htaccess
chmod 644 /var/www/html/hris-seven-payroll/public/.htaccess

# 3. Hapus .htaccess dari root (jika ada)
rm -f /var/www/html/hris-seven-payroll/.htaccess

# 4. Verify tidak ada RewriteBase
echo "=== Checking .htaccess ==="
if grep -q "RewriteBase" /var/www/html/hris-seven-payroll/public/.htaccess; then
    echo "ERROR: RewriteBase masih ada!"
else
    echo "OK: Tidak ada RewriteBase"
fi

# 5. Clear cache
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
rm -f bootstrap/cache/routes*.php

# 6. Reload Apache
systemctl reload apache2

# 7. Test
echo ""
echo "Test: http://192.168.10.40/hris-seven-payroll"
```

---

## ✅ Verifikasi

Setelah fix:

1. **Check .htaccess:**

    ```bash
    cat /var/www/html/hris-seven-payroll/public/.htaccess | grep -i rewritebase
    ```

    Harus tidak ada output (tidak ada RewriteBase)

2. **Test aplikasi:**
   `http://192.168.10.40/hris-seven-payroll`

3. **Check error log:**
    ```bash
    tail -f /var/log/apache2/error.log
    ```
    Tidak boleh ada error "Request exceeded the limit of 10 internal redirects"

---

## 🔍 Penjelasan

**Mengapa RewriteBase menyebabkan redirect loop?**

Ketika menggunakan Alias `/hris-seven-payroll` → `/var/www/html/hris-seven-payroll/public`:

-   Apache sudah menangani path `/hris-seven-payroll`
-   Request ke `/hris-seven-payroll/` sudah di-map ke folder `public/`
-   Jika `.htaccess` menggunakan `RewriteBase /hris-seven-payroll/public/`, akan terjadi konflik:
    -   Request: `/hris-seven-payroll/`
    -   Alias map ke: `/var/www/html/hris-seven-payroll/public/`
    -   `.htaccess` dengan `RewriteBase /hris-seven-payroll/public/` mencoba redirect lagi
    -   Terjadi loop!

**Solusi**: Hapus `RewriteBase` dari `.htaccess` ketika menggunakan Alias.

---

**SELESAI!** 🎉

Setelah fix, redirect loop seharusnya sudah teratasi dan aplikasi bisa diakses normal.




