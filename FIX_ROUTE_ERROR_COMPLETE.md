# Fix Error: MethodNotAllowedHttpException - Solusi Lengkap

Error ini terjadi karena route cache corrupt atau ada masalah dengan konfigurasi subfolder.

---

## 🚀 Solusi 1: Deep Fix Script (Recommended)

### **Langkah 1: Upload Script**

```bash
# Dari localhost
scp fix-route-error-deep.sh root@192.168.10.40:/tmp/
```

### **Langkah 2: Jalankan Script**

```bash
# SSH ke server
ssh root@192.168.10.40

# Jalankan script
chmod +x /tmp/fix-route-error-deep.sh
sudo /tmp/fix-route-error-deep.sh
```

Script akan:

-   ✅ Update `.env` (APP_URL & ASSET_URL)
-   ✅ Fix `.htaccess` (hapus RewriteBase)
-   ✅ Deep clear semua cache
-   ✅ Hapus route cache file secara manual
-   ✅ Rebuild cache (tanpa route cache untuk testing)
-   ✅ Verify routes

**PENTING**: Script sengaja TIDAK rebuild route cache untuk testing. Setelah aplikasi berhasil, jalankan:

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan route:cache
```

---

## 📝 Solusi 2: Manual Fix (Step by Step)

### **Step 1: Update .env**

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# Edit .env
sudo nano .env
```

Pastikan:

```env
APP_URL=http://192.168.10.40/hris-seven-payroll
ASSET_URL=http://192.168.10.40/hris-seven-payroll
```

**PENTING**: Tidak ada trailing slash (`/`) di akhir!

### **Step 2: Fix .htaccess**

```bash
sudo nano public/.htaccess
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

### **Step 3: Deep Clear Cache**

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear

# Hapus route cache file secara manual
sudo rm -f bootstrap/cache/routes*.php
```

### **Step 4: Rebuild Cache (TANPA Route Cache)**

```bash
# Rebuild config dan view cache
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache

# JANGAN rebuild route cache dulu!
# sudo -u www-data php artisan route:cache  # SKIP INI DULU
```

### **Step 5: Test Aplikasi**

Akses: `http://192.168.10.40/hris-seven-payroll`

Jika sudah berhasil, baru rebuild route cache:

```bash
sudo -u www-data php artisan route:cache
```

---

## 🔍 Solusi 3: Check Route List

Jika masih error, check apakah route `/` benar-benar ada:

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan route:list | grep -E "GET.*/"
```

Harus muncul:

```
GET|HEAD  /  ........................ dashboard › Closure
```

Jika tidak muncul, check `routes/web.php`:

```bash
grep -n "Route::get('/'," routes/web.php
```

Harus ada di line 54-56:

```php
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');
```

---

## 🔧 Solusi 4: Fix TrustProxies (Jika Masih Error)

Jika masih error, mungkin perlu update `TrustProxies` middleware:

```bash
sudo nano app/Http/Middleware/TrustProxies.php
```

Pastikan `$proxies` dan `$headers` sudah benar:

```php
protected $proxies = '*';
protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO;
```

Kemudian clear cache lagi:

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
```

---

## 🐛 Debugging

### **Check Laravel Log**

```bash
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

### **Check Apache Error Log**

```bash
tail -50 /var/log/apache2/error.log
```

### **Check Request Path**

Tambahkan di `public/index.php` untuk debug (temporary):

```php
// Di baris setelah Request::capture()
$request = Request::capture();
\Log::info('Request URI: ' . $request->getRequestUri());
\Log::info('Request Path: ' . $request->getPathInfo());
```

Kemudian check log:

```bash
tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

### **Test Route Langsung**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan tinker
```

Di tinker:

```php
Route::getRoutes()->match(Request::create('/'));
```

---

## ✅ Checklist Fix

-   [ ] `.env` sudah di-update (APP_URL & ASSET_URL, tanpa trailing slash)
-   [ ] `.htaccess` sudah di-fix (TANPA RewriteBase)
-   [ ] Semua cache sudah di-clear (termasuk route cache file)
-   [ ] Route cache TIDAK di-rebuild untuk testing
-   [ ] Config cache sudah di-rebuild
-   [ ] Route `/` ada di `route:list`
-   [ ] Aplikasi sudah di-test
-   [ ] Route cache di-rebuild setelah aplikasi berhasil

---

## 🎯 Quick Fix Command (All in One)

Jika semua solusi di atas tidak berhasil, jalankan command ini:

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# 1. Update .env
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' .env
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' .env
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

# 3. Deep clear
sudo -u www-data php artisan optimize:clear
sudo rm -f bootstrap/cache/routes*.php

# 4. Rebuild (tanpa route cache)
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache

# 5. Test
echo "Test: http://192.168.10.40/hris-seven-payroll"
```

---

**SELESAI!** 🎉

Setelah menjalankan salah satu solusi di atas, error seharusnya sudah teratasi.




