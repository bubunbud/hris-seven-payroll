# Quick Start Deploy - HRIS Seven Payroll

## Ringkasan Cepat

**Server**: 192.168.10.40  
**Database**: MySQL (root/root123)  
**Path**: /var/www/html/hris-seven-payroll

---

## Langkah Cepat (5 Menit)

### 1. Upload Project

```bash
# Dari komputer lokal, upload ke server
scp -r C:\xampp\htdocs\hris-seven-payroll\* user@192.168.10.40:/tmp/hris-seven-payroll/
```

### 2. Di Server Ubuntu

```bash
# Login ke server
ssh user@192.168.10.40

# Pindahkan ke web directory
sudo mkdir -p /var/www/html/hris-seven-payroll
sudo cp -r /tmp/hris-seven-payroll/* /var/www/html/hris-seven-payroll/
cd /var/www/html/hris-seven-payroll

# Set permissions
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
sudo chmod -R 755 /var/www/html/hris-seven-payroll
sudo chmod -R 775 storage bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev

# Setup .env
cp .env.example .env  # atau buat manual
nano .env  # edit database config
php artisan key:generate

# Setup database
mysql -u root -proot123 -e "CREATE DATABASE hris_seven CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Setup Apache

```bash
# Buat virtual host
sudo nano /etc/apache2/sites-available/hris-seven-payroll.conf
```

Paste ini:

```apache
<VirtualHost *:80>
    ServerName 192.168.10.40
    DocumentRoot /var/www/html/hris-seven-payroll/public
    <Directory /var/www/html/hris-seven-payroll/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
# Enable
sudo a2ensite hris-seven-payroll.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 4. Test

Buka browser: `http://192.168.10.40`

---

## Atau Gunakan Script Otomatis

```bash
# Di server, berikan execute permission
chmod +x deploy-ubuntu.sh
chmod +x setup-apache-vhost.sh

# Jalankan deploy script
sudo ./deploy-ubuntu.sh

# Setup Apache virtual host
sudo ./setup-apache-vhost.sh
```

---

## Troubleshooting Cepat

**Error 500**: Check permissions dan .env file

```bash
sudo chmod -R 775 storage bootstrap/cache
tail -f storage/logs/laravel.log
```

**Database Error**: Check .env dan MySQL

```bash
mysql -u root -proot123
SHOW DATABASES;
```

**Permission Denied**: Set ownership

```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
```

---

Lihat `DEPLOY_UBUNTU.md` untuk panduan lengkap.
