# Panduan Perbaikan Error 404 Master Karyawan di Server Ubuntu

## Masalah

Error 404 saat memilih data karyawan di server Ubuntu (192.168.10.40), padahal di localhost:8000 tidak bermasalah.

## Penyebab

File JavaScript yang sudah diperbaiki belum ter-upload ke server, atau cache Laravel perlu dibersihkan.

---

## TAHAPAN PERBAIKAN

### TAHAP 1: Upload File yang Sudah Diperbaiki

**Dari komputer lokal (Windows):**

```bash
# Opsi A: Menggunakan SCP (jika ada SSH access)
scp C:\xampp\htdocs\hris-seven-payroll\resources\views\master\karyawan\index.blade.php user@192.168.10.40:/tmp/

# Opsi B: Menggunakan FTP/SFTP client (FileZilla, WinSCP, dll)
# Upload file: resources/views/master/karyawan/index.blade.php
# Ke server: /var/www/html/hris-seven-payroll/resources/views/master/karyawan/
```

**Atau copy-paste manual:**

1. Buka file lokal: `C:\xampp\htdocs\hris-seven-payroll\resources\views\master\karyawan\index.blade.php`
2. Copy seluruh isi file
3. Login ke server Ubuntu via SSH atau FTP
4. Edit file di server: `/var/www/html/hris-seven-payroll/resources/views/master/karyawan/index.blade.php`
5. Paste dan save

---

### TAHAP 2: Login ke Server Ubuntu

```bash
# Login via SSH
ssh user@192.168.10.40

# Atau jika menggunakan username lain
ssh username@192.168.10.40
```

---

### TAHAP 3: Verifikasi File Sudah Ter-upload

```bash
# Masuk ke direktori project
cd /var/www/html/hris-seven-payroll

# Cek apakah file sudah ada dan terbaru
ls -lh resources/views/master/karyawan/index.blade.php

# Verifikasi bahwa basePath sudah ada di file (line ~700)
grep -n "basePath" resources/views/master/karyawan/index.blade.php

# Harus muncul output seperti:
# 700:        const basePath = '{{ url("/") }}';
```

**Jika file belum ter-upload atau tidak ada basePath:**

-   Ulangi TAHAP 1 untuk upload file

---

### TAHAP 4: Set Permissions File

```bash
# Set ownership ke www-data
sudo chown www-data:www-data resources/views/master/karyawan/index.blade.php

# Set permissions
sudo chmod 644 resources/views/master/karyawan/index.blade.php
```

---

### TAHAP 5: Clear Cache Laravel

```bash
# Masuk ke direktori project
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Re-cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Catatan:** Jika menggunakan `sudo`, gunakan:

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

### TAHAP 6: Verifikasi Konfigurasi .env

```bash
# Cek APP_URL di .env
cat .env | grep APP_URL

# Harus menunjukkan:
# APP_URL=http://192.168.10.40/hris-seven-payroll

# Jika belum benar, edit:
nano .env

# Update APP_URL menjadi:
# APP_URL=http://192.168.10.40/hris-seven-payroll

# Setelah edit, clear config cache lagi
php artisan config:clear
php artisan config:cache
```

---

### TAHAP 7: Verifikasi .htaccess

```bash
# Cek file .htaccess di public folder
cat public/.htaccess

# Pastikan ada RewriteBase (jika menggunakan subfolder)
# Atau pastikan tidak ada RewriteBase jika menggunakan alias Apache

# Jika menggunakan subfolder dengan alias, .htaccess seharusnya:
# TIDAK ada RewriteBase /hris-seven-payroll/public/
# Karena alias sudah handle path-nya
```

**Jika menggunakan alias Apache (sudah dikonfigurasi sebelumnya):**

-   `.htaccess` seharusnya **TIDAK** punya `RewriteBase`
-   Alias Apache sudah handle path `/hris-seven-payroll` → `/var/www/html/hris-seven-payroll/public`

---

### TAHAP 8: Restart Apache (Opsional)

```bash
# Restart Apache untuk memastikan semua perubahan ter-load
sudo systemctl restart apache2

# Atau reload (lebih ringan)
sudo systemctl reload apache2

# Cek status Apache
sudo systemctl status apache2
```

---

### TAHAP 9: Clear Browser Cache

**Di browser client (komputer yang akses aplikasi):**

1. **Chrome/Edge:**

    - Tekan `Ctrl + Shift + Delete`
    - Pilih "Cached images and files"
    - Klik "Clear data"
    - Atau tekan `Ctrl + F5` untuk hard refresh

2. **Firefox:**

    - Tekan `Ctrl + Shift + Delete`
    - Pilih "Cache"
    - Klik "Clear Now"
    - Atau tekan `Ctrl + F5` untuk hard refresh

3. **Atau gunakan Incognito/Private mode:**
    - Buka browser dalam mode incognito
    - Akses: `http://192.168.10.40/hris-seven-payroll`

---

### TAHAP 10: Test Aplikasi

1. **Akses aplikasi:**

    ```
    http://192.168.10.40/hris-seven-payroll
    ```

2. **Buka Master Karyawan:**

    - Klik menu Master Karyawan
    - Pilih salah satu data karyawan dari list
    - **Harusnya tidak ada error 404 lagi**

3. **Check Browser Console (F12):**
    - Buka Developer Tools (F12)
    - Tab Console
    - Pastikan tidak ada error 404 untuk `/karyawan/...`
    - URL yang dipanggil harus: `http://192.168.10.40/hris-seven-payroll/karyawan/...`

---

## TROUBLESHOOTING

### Masih Error 404?

**1. Cek apakah basePath ter-generate dengan benar:**

Buka browser console (F12), ketik:

```javascript
console.log(basePath);
```

Harus muncul: `http://192.168.10.40/hris-seven-payroll`

**2. Cek Network Tab di Browser:**

-   Buka Developer Tools (F12)
-   Tab Network
-   Klik data karyawan
-   Lihat request yang gagal
-   Pastikan URL-nya: `http://192.168.10.40/hris-seven-payroll/karyawan/...`

**3. Cek Laravel Log:**

```bash
# Di server Ubuntu
tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log

# Lalu coba akses lagi dari browser
# Lihat apakah ada error di log
```

**4. Cek Apache Error Log:**

```bash
# Di server Ubuntu
sudo tail -f /var/log/apache2/error.log

# Lalu coba akses lagi dari browser
# Lihat apakah ada error di log
```

**5. Verifikasi Route Laravel:**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll
php artisan route:list | grep karyawan

# Harus muncul route:
# GET|HEAD  karyawan/{id} ............ karyawan.show
```

**6. Test URL Langsung di Browser:**

Akses langsung:

```
http://192.168.10.40/hris-seven-payroll/karyawan/20120211
```

Jika muncul JSON response, berarti route bekerja. Masalahnya di JavaScript.

**7. Cek File JavaScript di Browser:**

-   Buka Developer Tools (F12)
-   Tab Sources atau Network
-   Cari file `karyawan` (view blade yang di-compile)
-   Buka file tersebut
-   Cari `basePath` (Ctrl+F)
-   Pastikan ada: `const basePath = 'http://192.168.10.40/hris-seven-payroll';`

---

## RINGKASAN PERINTAH CEPAT

```bash
# Login ke server
ssh user@192.168.10.40

# Masuk ke project
cd /var/www/html/hris-seven-payroll

# Clear cache
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear

# Re-cache
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Restart Apache
sudo systemctl restart apache2

# Cek log (opsional)
tail -f storage/logs/laravel.log
```

---

## CATATAN PENTING

1. **Pastikan file `index.blade.php` sudah ter-upload** dengan benar
2. **Clear cache Laravel** setelah upload file
3. **Clear browser cache** di client
4. **Verifikasi APP_URL** di `.env` sudah benar
5. **Test di browser incognito** untuk memastikan tidak ada cache lama

---

## SELESAI!

Setelah semua tahapan selesai, error 404 seharusnya sudah teratasi. Jika masih bermasalah, ikuti troubleshooting di atas.












