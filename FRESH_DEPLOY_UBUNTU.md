# Panduan Fresh Deploy HRIS Seven Payroll ke Ubuntu Server

Panduan lengkap untuk upload project dari awal ke Ubuntu server.

## 📋 INFORMASI SERVER

-   **IP Server**: 192.168.10.40
-   **Project Path**: `/var/www/html/hris-seven-payroll`
-   **Database**: `hris_seven`
-   **Database User**: `root`
-   **Database Password**: `root123`
-   **Access URL**: `http://192.168.10.40/hris-seven-payroll`

---

## LANGKAH 1: Persiapan di Localhost (Windows)

### 1.1 Siapkan File untuk Upload

**Folder yang TIDAK perlu di-upload:**

-   `vendor/` (akan di-install di server)
-   `node_modules/` (jika ada)
-   `.git/` (jika ada)
-   `storage/logs/*.log` (file log)
-   `.env` (akan dibuat di server)

**Folder yang HARUS di-upload:**

-   Semua folder dan file lainnya

### 1.2 Buat Archive (Opsi A - Recommended)

**Menggunakan WinRAR atau 7-Zip:**

1. Klik kanan pada folder `C:\xampp\htdocs\hris-seven-payroll`
2. Pilih "Add to archive" atau "7-Zip > Add to archive"
3. Exclude folder berikut:
    - `vendor`
    - `node_modules`
    - `.git`
    - `storage/logs` (folder logs, bukan storage)
4. Buat file ZIP: `hris-seven-payroll-update.zip`

### 1.3 Atau Siapkan untuk SCP/RSYNC (Opsi B)

Jika menggunakan SCP atau RSYNC, siapkan command untuk exclude folder.

---

## LANGKAH 2: Upload ke Server

### Opsi A: Menggunakan FileZilla/WinSCP (Recommended)

1. **Connect ke Server:**

    - Host: `192.168.10.40`
    - Username: `root` (atau username SSH Anda)
    - Password: (password server)
    - Port: `22` (SSH)

2. **Upload File:**
    - Jika pakai ZIP: Upload `hris-seven-payroll-update.zip` ke `/tmp/`
    - Jika pakai folder: Upload semua file ke `/tmp/hris-seven-payroll/`

### Opsi B: Menggunakan SCP (Command Line)

**Dari Windows (Git Bash atau PowerShell):**

```bash
# Jika pakai ZIP, upload dulu lalu extract di server
scp C:\xampp\htdocs\hris-seven-payroll-update.zip root@192.168.10.40:/tmp/

# Atau upload folder langsung (exclude vendor, node_modules)
# Install rsync dulu di Windows atau gunakan WSL
```

---

## LANGKAH 3: Setup di Server Ubuntu

### 3.1 Login ke Server

```bash
ssh root@192.168.10.40
# atau
ssh username@192.168.10.40
```

### 3.2 Extract File (jika pakai ZIP)

```bash
cd /tmp
unzip hris-seven-payroll-update.zip -d hris-seven-payroll
# atau jika sudah ada folder
# cd /tmp/hris-seven-payroll
```

### 3.3 Pindahkan ke Web Server Directory

```bash
# Buat direktori project
sudo mkdir -p /var/www/html/hris-seven-payroll

# Copy semua file
sudo cp -r /tmp/hris-seven-payroll/* /var/www/html/hris-seven-payroll/

# Atau jika file sudah di /tmp/hris-seven-payroll/
sudo cp -r /tmp/hris-seven-payroll/* /var/www/html/hris-seven-payroll/
```

### 3.4 Set Ownership dan Permissions

```bash
cd /var/www/html/hris-seven-payroll

# Set ownership
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll

# Set permissions
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache

# Jika ada folder public/storage
if [ -d "public/storage" ]; then
    sudo chmod -R 775 public/storage
fi
```

---

## LANGKAH 4: Install Dependencies

### 4.1 Install Composer Dependencies

```bash
cd /var/www/html/hris-seven-payroll

# Install composer dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction

# Jika error permission, gunakan:
# composer install --optimize-autoloader --no-dev --no-interaction
```

### 4.2 Install NPM Dependencies (jika ada)

```bash
# Jika menggunakan Vite/Mix
npm install --production
npm run build
```

---

## LANGKAH 5: Konfigurasi Environment

### 5.1 Buat File .env

```bash
cd /var/www/html/hris-seven-payroll

# Copy dari .env.example jika ada
if [ -f ".env.example" ]; then
    cp .env.example .env
else
    # Buat .env baru
    cat > .env <<'EOF'
APP_NAME="HRIS Seven Payroll"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://192.168.10.40/hris-seven-payroll

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris_seven
DB_USERNAME=root
DB_PASSWORD=root123

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

ASSET_URL=http://192.168.10.40/hris-seven-payroll
EOF
fi

# Generate APP_KEY
php artisan key:generate --force
```

### 5.2 Verifikasi .env

```bash
# Check konfigurasi penting
cat .env | grep -E "APP_URL|DB_|APP_DEBUG|APP_KEY"
```

---

## LANGKAH 6: Setup Database

### 6.1 Verifikasi Database

```bash
# Test koneksi database
mysql -u root -proot123 -e "USE hris_seven; SHOW TABLES;" | head -10
```

### 6.2 Run Migrations

```bash
cd /var/www/html/hris-seven-payroll

# Check migration status
php artisan migrate:status

# Run migrations
php artisan migrate --force
```

### 6.3 Run Seeders

```bash
# Run seeders untuk role & permission
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=AdminUserRoleSeeder --force

# Run UserSeeder jika perlu
php artisan db:seed --class=UserSeeder --force
```

---

## LANGKAH 7: Konfigurasi Apache

### 7.1 Setup Apache Alias

```bash
# Buat file konfigurasi alias
sudo nano /etc/apache2/conf-available/hris-seven-payroll-alias.conf
```

**Isi dengan:**

```apache
# Alias untuk HRIS Seven Payroll
Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

<Directory /var/www/html/hris-seven-payroll/public>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### 7.2 Enable Konfigurasi

```bash
# Enable alias configuration
sudo a2enconf hris-seven-payroll-alias

# Enable mod_rewrite
sudo a2enmod rewrite

# Test Apache configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

### 7.3 Update .htaccess (Tanpa RewriteBase untuk Alias)

```bash
cd /var/www/html/hris-seven-payroll/public

# Update .htaccess (tanpa RewriteBase karena pakai Alias)
cat > .htaccess <<'EOF'
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
EOF
```

---

## LANGKAH 8: Optimasi Laravel

### 8.1 Clear Cache

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 8.2 Re-cache untuk Production

```bash
# Re-cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## LANGKAH 9: Verifikasi & Testing

### 9.1 Test Akses

```bash
# Test dari browser:
# http://192.168.10.40/hris-seven-payroll
```

### 9.2 Test Login

```bash
# Test login:
# http://192.168.10.40/hris-seven-payroll/login
# Email: admin@hris.com
# Password: admin123
```

### 9.3 Check Logs

```bash
# Check Laravel log
tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log

# Check Apache error log
tail -f /var/log/apache2/error.log
```

---

## LANGKAH 10: Cleanup

### 10.1 Hapus File Temporary

```bash
# Hapus file di /tmp
sudo rm -rf /tmp/hris-seven-payroll*
```

---

## ✅ CHECKLIST FINAL

-   [ ] File sudah di-upload ke server
-   [ ] Ownership dan permissions sudah benar
-   [ ] Composer dependencies terinstall
-   [ ] File .env sudah dibuat dan APP_KEY sudah di-generate
-   [ ] Database connection berhasil
-   [ ] Migrations sudah dijalankan
-   [ ] Seeders sudah dijalankan
-   [ ] Apache alias sudah dikonfigurasi
-   [ ] .htaccess sudah benar (tanpa RewriteBase)
-   [ ] Cache sudah di-clear dan re-cache
-   [ ] Apache sudah di-restart
-   [ ] Website bisa diakses
-   [ ] Login berfungsi
-   [ ] Menu dan fitur berfungsi

---

## 🚨 TROUBLESHOOTING

### Error: Permission Denied

```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

### Error: 500 Internal Server Error

```bash
# Check log
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Error: Database Connection Failed

```bash
# Test koneksi
mysql -u root -proot123 -e "USE hris_seven; SHOW TABLES;"

# Check .env
cat /var/www/html/hris-seven-payroll/.env | grep DB_
```

### Error: Route Not Found / Redirect Loop

```bash
# Pastikan .htaccess tanpa RewriteBase (karena pakai Alias)
cd /var/www/html/hris-seven-payroll/public
cat .htaccess | grep RewriteBase
# Seharusnya TIDAK ada RewriteBase

# Clear route cache
php artisan route:clear
php artisan route:cache
```

---

## 📝 CATATAN PENTING

1. **Jangan upload folder `vendor`** - akan di-install di server
2. **Jangan upload file `.env`** - akan dibuat di server dengan konfigurasi server
3. **Pastikan Apache Alias sudah dikonfigurasi** sebelum akses
4. **Gunakan `.htaccess` tanpa RewriteBase** jika pakai Apache Alias
5. **Selalu backup database** sebelum perubahan besar

---

## SELESAI! 🎉

Setelah semua langkah selesai, aplikasi seharusnya sudah bisa diakses di:

-   **http://192.168.10.40/hris-seven-payroll**
-   **http://192.168.10.40/hris-seven-payroll/login**

Jika ada masalah, check log files dan ikuti troubleshooting di atas.








