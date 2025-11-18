# Checklist Deploy HRIS Seven Payroll ke Ubuntu

Gunakan checklist ini untuk memastikan semua langkah deploy sudah dilakukan dengan benar.

## ✅ PRE-DEPLOYMENT

-   [ ] Server Ubuntu sudah siap dan bisa diakses
-   [ ] IP Server: 192.168.10.40 sudah bisa di-ping
-   [ ] SSH access ke server sudah berfungsi
-   [ ] Backup database lokal (jika ada data penting)
-   [ ] Backup file project lokal

## ✅ SERVER SETUP

-   [ ] Update sistem: `sudo apt update && sudo apt upgrade -y`
-   [ ] PHP 8.1 terinstall: `php -v`
-   [ ] PHP extensions terinstall:
    -   [ ] php8.1-mysql
    -   [ ] php8.1-zip
    -   [ ] php8.1-gd
    -   [ ] php8.1-mbstring
    -   [ ] php8.1-curl
    -   [ ] php8.1-xml
    -   [ ] php8.1-bcmath
    -   [ ] php8.1-intl
-   [ ] Composer terinstall: `composer --version`
-   [ ] Apache terinstall: `apache2 -v`
-   [ ] MySQL terinstall: `mysql --version`
-   [ ] MySQL service running: `sudo systemctl status mysql`

## ✅ DATABASE SETUP

-   [ ] Database `hris_seven` sudah dibuat
-   [ ] User database sudah dibuat (atau menggunakan root)
-   [ ] Koneksi MySQL berhasil: `mysql -u root -p`
-   [ ] Database charset: utf8mb4_unicode_ci

## ✅ PROJECT UPLOAD

-   [ ] Project sudah di-upload ke server
-   [ ] Lokasi project: `/var/www/html/hris-seven-payroll`
-   [ ] Folder `vendor` dan `node_modules` tidak di-upload (akan di-install di server)
-   [ ] File `.env` tidak di-upload (akan dibuat di server)

## ✅ PROJECT CONFIGURATION

-   [ ] Ownership sudah di-set: `www-data:www-data`
-   [ ] Permissions sudah benar:
    -   [ ] Root folder: 755
    -   [ ] storage folder: 775
    -   [ ] bootstrap/cache folder: 775
-   [ ] Composer dependencies terinstall: `composer install`
-   [ ] File `.env` sudah dibuat
-   [ ] APP_KEY sudah di-generate: `php artisan key:generate`
-   [ ] Database configuration di `.env` sudah benar:
    -   [ ] DB_HOST=127.0.0.1
    -   [ ] DB_DATABASE=hris_seven
    -   [ ] DB_USERNAME=root
    -   [ ] DB_PASSWORD=root123

## ✅ DATABASE MIGRATION

-   [ ] Migrations sudah dijalankan: `php artisan migrate`
-   [ ] Tidak ada error saat migration
-   [ ] Data seeder sudah dijalankan (jika ada)

## ✅ APACHE CONFIGURATION

-   [ ] Virtual host sudah dibuat: `/etc/apache2/sites-available/hris-seven-payroll.conf`
-   [ ] Virtual host sudah di-enable: `a2ensite`
-   [ ] mod_rewrite sudah di-enable: `a2enmod rewrite`
-   [ ] mod_headers sudah di-enable: `a2enmod headers`
-   [ ] Apache config test passed: `apache2ctl configtest`
-   [ ] Apache sudah di-restart: `sudo systemctl restart apache2`

## ✅ LARAVEL OPTIMIZATION

-   [ ] Config cached: `php artisan config:cache`
-   [ ] Route cached: `php artisan route:cache`
-   [ ] View cached: `php artisan view:cache`
-   [ ] Composer autoload optimized: `composer dump-autoload --optimize`

## ✅ TESTING

-   [ ] Website bisa diakses: `http://192.168.10.40`
-   [ ] Tidak ada error 500
-   [ ] Database connection berhasil
-   [ ] Login/logout berfungsi (jika ada)
-   [ ] Upload file berfungsi (jika ada)
-   [ ] Semua fitur utama berfungsi

## ✅ SECURITY

-   [ ] APP_DEBUG=false di production
-   [ ] File `.env` tidak accessible dari web
-   [ ] Storage folder permissions sudah benar
-   [ ] Firewall sudah dikonfigurasi (jika diperlukan)
-   [ ] SSL certificate sudah di-setup (jika diperlukan)

## ✅ MONITORING & MAINTENANCE

-   [ ] Log files bisa diakses: `/var/www/html/hris-seven-payroll/storage/logs/laravel.log`
-   [ ] Apache error log bisa diakses: `/var/log/apache2/hris-seven-payroll_error.log`
-   [ ] Backup strategy sudah direncanakan
-   [ ] Update procedure sudah didokumentasikan

## 📝 NOTES

Tambahkan catatan khusus di sini:

-
-
-

---

**Status Deploy**: ⬜ Belum Dimulai | 🟡 Sedang Berlangsung | ✅ Selesai | ❌ Error

**Tanggal Deploy**: ********\_********

**Deployed By**: ********\_********
