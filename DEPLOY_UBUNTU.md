# Panduan Deploy HRIS Seven Payroll ke Server Ubuntu

## Informasi Server

-   **IP Server**: 192.168.10.40
-   **MySQL Username**: root
-   **MySQL Password**: root123
-   **Web Server Folder**: /var/www/html
-   **PHP Version**: ^8.1 (Laravel 10)

---

## LANGKAH 1: Persiapan Server Ubuntu

### 1.1 Login ke Server Ubuntu

```bash
ssh username@192.168.10.40
# atau jika menggunakan password
ssh root@192.168.10.40
```

### 1.2 Update Sistem

```bash
sudo apt update
sudo apt upgrade -y
```

### 1.3 Install Dependencies yang Diperlukan

```bash
# Install PHP 8.1 dan Extensions
sudo apt install -y php8.1 php8.1-fpm php8.1-cli php8.1-common \
    php8.1-mysql php8.1-zip php8.1-gd php8.1-mbstring \
    php8.1-curl php8.1-xml php8.1-bcmath php8.1-intl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Apache dan MySQL (jika belum terinstall)
sudo apt install -y apache2 mysql-server

# Install Git (untuk clone/upload project)
sudo apt install -y git unzip
```

### 1.4 Install dan Konfigurasi MySQL

```bash
# Start MySQL service
sudo systemctl start mysql
sudo systemctl enable mysql

# Secure MySQL installation (opsional, tapi disarankan)
sudo mysql_secure_installation

# Login ke MySQL dan buat database
sudo mysql -u root -p
# Masukkan password: root123
```

Di dalam MySQL:

```sql
CREATE DATABASE hris_seven CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hris_user'@'localhost' IDENTIFIED BY 'root123';
GRANT ALL PRIVILEGES ON hris_seven.* TO 'hris_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## LANGKAH 2: Upload Project ke Server

### Opsi A: Menggunakan SCP (dari Windows/Local)

```bash
# Dari komputer lokal (Windows), buka PowerShell atau CMD
# Navigate ke folder project
cd C:\xampp\htdocs\hris-seven-payroll

# Upload ke server (exclude vendor dan node_modules)
scp -r -p * username@192.168.10.40:/tmp/hris-seven-payroll/
```

### Opsi B: Menggunakan Git (Recommended)

```bash
# Di server Ubuntu
cd /tmp
git clone <repository-url> hris-seven-payroll
# atau jika belum ada repository, buat folder dan upload manual
```

### Opsi C: Menggunakan FileZilla/WinSCP

1. Connect ke server 192.168.10.40 via SFTP
2. Upload semua file project ke `/tmp/hris-seven-payroll/`
3. Exclude folder: `vendor`, `node_modules`, `.git`

---

## LANGKAH 3: Pindahkan Project ke Web Server Directory

```bash
# Di server Ubuntu
sudo mkdir -p /var/www/html/hris-seven-payroll
sudo cp -r /tmp/hris-seven-payroll/* /var/www/html/hris-seven-payroll/
cd /var/www/html/hris-seven-payroll

# Set ownership ke www-data (user Apache)
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll

# Set permissions
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

---

## LANGKAH 4: Install Dependencies Laravel

```bash
cd /var/www/html/hris-seven-payroll

# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Jika ada error permission, gunakan sudo
sudo -u www-data composer install --optimize-autoloader --no-dev
```

---

## LANGKAH 5: Konfigurasi Environment (.env)

```bash
# Copy .env.example ke .env (jika ada)
cp .env.example .env

# Atau buat file .env baru
nano .env
```

Isi file `.env` dengan konfigurasi berikut:

```env
APP_NAME="HRIS Seven Payroll"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://192.168.10.40

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

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
```

Generate Application Key:

```bash
php artisan key:generate
```

---

## LANGKAH 6: Konfigurasi Database

```bash
# Run migrations
php artisan migrate --force

# Jika ada seeder, jalankan juga
# php artisan db:seed --force
```

---

## LANGKAH 7: Konfigurasi Apache Virtual Host

```bash
# Buat file konfigurasi virtual host
sudo nano /etc/apache2/sites-available/hris-seven-payroll.conf
```

Isi dengan konfigurasi berikut:

```apache
<VirtualHost *:80>
    ServerName 192.168.10.40
    ServerAlias hris-seven-payroll.local

    DocumentRoot /var/www/html/hris-seven-payroll/public

    <Directory /var/www/html/hris-seven-payroll/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hris-seven-payroll_error.log
    CustomLog ${APACHE_LOG_DIR}/hris-seven-payroll_access.log combined
</VirtualHost>
```

Aktifkan virtual host dan modul yang diperlukan:

```bash
# Enable virtual host
sudo a2ensite hris-seven-payroll.conf

# Disable default site (opsional)
sudo a2dissite 000-default.conf

# Enable mod_rewrite dan mod_headers
sudo a2enmod rewrite
sudo a2enmod headers

# Test konfigurasi Apache
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

---

## LANGKAH 8: Optimasi Laravel untuk Production

```bash
cd /var/www/html/hris-seven-payroll

# Clear dan cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## LANGKAH 9: Set Permissions Final

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll

# Set permissions untuk storage dan cache
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache

# Set permissions untuk public folder (jika ada upload)
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage/app/public
```

---

## LANGKAH 10: Konfigurasi Firewall (jika diperlukan)

```bash
# Allow HTTP dan HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
```

---

## LANGKAH 11: Test Akses

1. Buka browser dan akses: `http://192.168.10.40`
2. Atau jika menggunakan domain: `http://192.168.10.40/hris-seven-payroll/public`

---

## TROUBLESHOOTING

### Error: Permission Denied

```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 /var/www/html/hris-seven-payroll/storage
sudo chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
```

### Error: 500 Internal Server Error

```bash
# Check Apache error log
sudo tail -f /var/log/apache2/error.log

# Check Laravel log
tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Error: Database Connection Failed

```bash
# Test koneksi MySQL
mysql -u root -p -h 127.0.0.1

# Check .env file
cat /var/www/html/hris-seven-payroll/.env | grep DB_

# Restart MySQL
sudo systemctl restart mysql
```

### Error: Composer Memory Limit

```bash
# Increase PHP memory limit
sudo nano /etc/php/8.1/cli/php.ini
# Set: memory_limit = 512M

# Atau saat install
php -d memory_limit=512M /usr/local/bin/composer install
```

### Error: mod_rewrite not enabled

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## MAINTENANCE COMMANDS

### Update Project

```bash
cd /var/www/html/hris-seven-payroll
git pull origin main  # atau branch yang digunakan
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear All Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Check Laravel Logs

```bash
tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

---

## KEAMANAN TAMBAHAN (Opsional)

### 1. Setup SSL dengan Let's Encrypt

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

### 2. Harden MySQL

```bash
sudo mysql_secure_installation
```

### 3. Setup Firewall Rules

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 4. Disable Directory Listing

Tambahkan di `.htaccess` atau virtual host:

```apache
Options -Indexes
```

---

## CATATAN PENTING

1. **Jangan commit file `.env`** ke repository
2. **Backup database** secara berkala
3. **Monitor log files** untuk error
4. **Update sistem** secara berkala: `sudo apt update && sudo apt upgrade`
5. **Backup project** sebelum update besar

---

## STRUKTUR FOLDER FINAL

```
/var/www/html/hris-seven-payroll/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← DocumentRoot Apache
├── resources/
├── routes/
├── storage/         ← Harus writable (775)
├── vendor/
├── .env             ← Konfigurasi environment
└── artisan
```

---

## SELESAI!

Setelah semua langkah selesai, aplikasi HRIS Seven Payroll seharusnya sudah bisa diakses di:

-   **http://192.168.10.40** (jika virtual host sebagai default)
-   **http://192.168.10.40/hris-seven-payroll/public** (jika subfolder)

Jika ada masalah, check log files dan pastikan semua permissions sudah benar.
