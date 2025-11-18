# Konfigurasi HRIS Seven Payroll sebagai Subfolder

Panduan ini untuk mengkonfigurasi aplikasi agar bisa diakses di `http://192.168.10.40/hris-seven-payroll`
sehingga aplikasi lain seperti `http://192.168.10.40/seven` tetap bisa berjalan.

---

## LANGKAH 1: Disable Virtual Host yang Ada

```bash
# Disable virtual host hris-seven-payroll
sudo a2dissite hris-seven-payroll.conf
sudo systemctl reload apache2
```

---

## LANGKAH 2: Pindahkan Project ke Subfolder (jika belum)

```bash
# Pastikan project ada di subfolder
sudo mkdir -p /var/www/html/hris-seven-payroll
# Jika project sudah ada di root, pindahkan:
# sudo mv /var/www/html/hris-seven-payroll/* /var/www/html/hris-seven-payroll/
```

---

## LANGKAH 3: Update .htaccess di Public Folder

File `/var/www/html/hris-seven-payroll/public/.htaccess` harus dikonfigurasi untuk subfolder.

```bash
sudo nano /var/www/html/hris-seven-payroll/public/.htaccess
```

Pastikan isinya seperti ini:

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

**PENTING**: Jika menggunakan subfolder, tambahkan konfigurasi berikut di bagian atas file `.htaccess`:

```apache
# Set base path untuk subfolder
RewriteBase /hris-seven-payroll/public/

<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On
    RewriteBase /hris-seven-payroll/public/

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

---

## LANGKAH 4: Update APP_URL di .env

```bash
sudo nano /var/www/html/hris-seven-payroll/.env
```

Ubah `APP_URL` menjadi:

```env
APP_URL=http://192.168.10.40/hris-seven-payroll
```

---

## LANGKAH 5: Konfigurasi Apache Alias (Opsi A - Recommended)

Buat alias di konfigurasi Apache default atau di file konfigurasi khusus:

```bash
sudo nano /etc/apache2/sites-available/000-default.conf
```

Atau buat file konfigurasi baru:

```bash
sudo nano /etc/apache2/conf-available/hris-seven-payroll-alias.conf
```

Tambahkan konfigurasi berikut:

```apache
# Alias untuk HRIS Seven Payroll
Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

<Directory /var/www/html/hris-seven-payroll/public>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

Enable konfigurasi:

```bash
sudo a2enconf hris-seven-payroll-alias
sudo systemctl reload apache2
```

---

## LANGKAH 6: Atau Gunakan Symlink (Opsi B - Alternatif)

Jika opsi alias tidak bekerja, gunakan symlink:

```bash
# Buat symlink dari public ke html root
sudo ln -s /var/www/html/hris-seven-payroll/public /var/www/html/hris-seven-payroll-public

# Atau jika ingin di root html langsung
sudo ln -s /var/www/html/hris-seven-payroll/public /var/www/html/hris-seven-payroll
```

---

## LANGKAH 7: Update Asset URLs (jika menggunakan Vite/Mix)

Jika menggunakan Laravel Mix atau Vite, pastikan `ASSET_URL` di `.env`:

```env
ASSET_URL=http://192.168.10.40/hris-seven-payroll
```

---

## LANGKAH 8: Clear Cache Laravel

```bash
cd /var/www/html/hris-seven-payroll
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Re-cache
php artisan config:cache
php artisan route:cache
```

---

## LANGKAH 9: Test Akses

1. Akses: `http://192.168.10.40/hris-seven-payroll`
2. Pastikan aplikasi lain masih bisa diakses: `http://192.168.10.40/seven`
3. Check apakah asset (CSS, JS, images) ter-load dengan benar

---

## TROUBLESHOOTING

### Error 404 di subfolder

-   Pastikan mod_rewrite enabled: `sudo a2enmod rewrite`
-   Check `.htaccess` sudah benar
-   Check permissions folder

### Asset tidak ter-load

-   Update `APP_URL` dan `ASSET_URL` di `.env`
-   Clear cache: `php artisan config:clear`
-   Check browser console untuk error 404 pada asset

### Route tidak bekerja

-   Pastikan `RewriteBase` di `.htaccess` sudah benar
-   Check `APP_URL` di `.env`
-   Clear route cache: `php artisan route:clear`

---

## STRUKTUR FOLDER FINAL

```
/var/www/html/
├── hris-seven-payroll/          ← Aplikasi HRIS
│   ├── app/
│   ├── public/                  ← DocumentRoot untuk alias
│   ├── storage/
│   └── ...
├── seven/                       ← Aplikasi lain
│   └── ...
└── index.html                   ← Default page (opsional)
```

---

## CATATAN PENTING

1. **Jangan** gunakan virtual host terpisah jika ingin akses sebagai subfolder
2. **Pastikan** `RewriteBase` di `.htaccess` sesuai dengan path subfolder
3. **Update** semua URL di aplikasi jika ada hardcoded paths
4. **Test** semua fitur setelah perubahan konfigurasi
