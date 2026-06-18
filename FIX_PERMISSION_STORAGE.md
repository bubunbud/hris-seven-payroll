# 🔧 Fix Permission Error: Storage/Logs

## Error yang Terjadi
```
The stream or file "/var/www/html/hris-seven-payroll/storage/logs/laravel.log" 
could not be opened in append mode: Failed to open stream: Permission denied
```

## Solusi: Set Permission Storage & Logs

### Langkah 1: Set Ownership ke www-data

```bash
# Login ke server
ssh user@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Set ownership untuk storage dan bootstrap/cache
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/

# Set ownership untuk seluruh aplikasi (jika perlu)
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll
```

### Langkah 2: Set Permission yang Benar

```bash
# Set permission untuk storage
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/

# Pastikan folder logs writable
sudo chmod -R 775 storage/logs/
sudo touch storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
```

### Langkah 3: Clear Cache (sekarang harusnya berhasil)

```bash
# Clear semua cache
php artisan optimize:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Langkah 4: Verifikasi

```bash
# Cek permission storage
ls -la storage/logs/

# Cek ownership
ls -la storage/ | head -5

# Test write permission
sudo -u www-data touch storage/logs/test.log
sudo -u www-data rm storage/logs/test.log
```

## Jika Masih Error (Permission Sudah Benar)

Jika permission sudah benar (www-data:www-data, 775/664) tapi masih error, kemungkinan:

### Solusi 1: Jalankan sebagai www-data

```bash
# Jalankan artisan command sebagai user www-data
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache
```

### Solusi 2: Set Permission Lebih Luas (Temporary)

```bash
# Set permission 777 (temporary, untuk testing)
sudo chmod -R 777 storage/
sudo chmod -R 777 bootstrap/cache/

# Setelah berhasil, kembalikan ke 775
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/
```

### Solusi 3: Cek User yang Menjalankan PHP

```bash
# Cek user saat ini
whoami

# Cek user Apache
ps aux | grep apache | head -1

# Jika user berbeda, jalankan sebagai www-data
sudo -u www-data php artisan route:cache
```

## Catatan Penting

1. **www-data** adalah user yang digunakan oleh Apache/Nginx di Ubuntu
2. Permission **775** = rwxrwxr-x (owner & group bisa write, others hanya read)
3. Permission **777** = rwxrwxrwx (semua bisa write - kurang aman, hanya untuk testing)
4. Setelah set permission, **wajib** clear cache

## Troubleshooting

### Jika masih error setelah set permission:

```bash
# Cek siapa yang menjalankan PHP
whoami

# Cek user Apache
ps aux | grep apache

# Set permission dengan sudo
sudo -u www-data php artisan route:cache
```

### Jika file laravel.log tidak ada:

```bash
# Buat file log manual
sudo touch storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log
```

---

**Status:** ✅ Solusi untuk permission error

