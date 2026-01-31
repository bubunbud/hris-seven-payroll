# Fix Error: MethodNotAllowedHttpException untuk Route /

Error ini terjadi karena konfigurasi `.env` belum disesuaikan untuk subfolder dan cache masih menggunakan konfigurasi lama.

---

## 🚀 Solusi Cepat (Script Otomatis)

### **Langkah 1: Upload Script ke Server**

```bash
# Dari localhost (Windows)
scp fix-subfolder-config.sh root@192.168.10.40:/tmp/
```

### **Langkah 2: Jalankan Script di Server**

```bash
# SSH ke server
ssh root@192.168.10.40

# Berikan permission execute
chmod +x /tmp/fix-subfolder-config.sh

# Jalankan script
sudo /tmp/fix-subfolder-config.sh
```

Script akan otomatis:

-   ✅ Backup `.env`
-   ✅ Update `APP_URL` dan `ASSET_URL` ke `http://192.168.10.40/hris-seven-payroll`
-   ✅ Clear semua cache Laravel
-   ✅ Rebuild cache
-   ✅ Verify routes

---

## 📝 Solusi Manual

### **Langkah 1: Update .env File**

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# Backup .env
cp .env .env.backup

# Edit .env
sudo nano .env
```

Update baris berikut:

```env
APP_URL=http://192.168.10.40/hris-seven-payroll
ASSET_URL=http://192.168.10.40/hris-seven-payroll
```

**PENTING**: Pastikan tidak ada trailing slash (`/`) di akhir URL!

### **Langkah 2: Clear Semua Cache**

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua cache
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear
```

### **Langkah 3: Rebuild Cache**

```bash
# Rebuild cache dengan konfigurasi baru
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### **Langkah 4: Verify Routes**

```bash
# Check apakah route / ada
sudo -u www-data php artisan route:list | grep "GET.*/"
```

Harus muncul:

```
GET|HEAD  /  ........................ dashboard › Closure
```

---

## ✅ Test Setelah Fix

1. **Akses aplikasi**: `http://192.168.10.40/hris-seven-payroll`
2. **Harus redirect ke login** (jika belum login) atau **dashboard** (jika sudah login)
3. **Tidak ada error** MethodNotAllowedHttpException

---

## ⚠️ Troubleshooting

### Masih Error Setelah Fix?

**1. Check .env File**

```bash
cat /var/www/html/hris-seven-payroll/.env | grep APP_URL
cat /var/www/html/hris-seven-payroll/.env | grep ASSET_URL
```

Harus muncul:

```
APP_URL=http://192.168.10.40/hris-seven-payroll
ASSET_URL=http://192.168.10.40/hris-seven-payroll
```

**2. Check Route Cache**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan route:list
```

Pastikan route `/` ada dengan method `GET|HEAD`.

**3. Check Laravel Log**

```bash
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

**4. Clear Semua Lagi (Deep Clean)**

```bash
cd /var/www/html/hris-seven-payroll

# Clear semua
sudo -u www-data php artisan optimize:clear

# Rebuild
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

**5. Check .htaccess**

```bash
cat /var/www/html/hris-seven-payroll/public/.htaccess
```

Pastikan **TIDAK** ada `RewriteBase` (karena menggunakan Alias).

Jika ada `RewriteBase`, hapus baris tersebut:

```bash
sudo nano /var/www/html/hris-seven-payroll/public/.htaccess
```

---

## 🔍 Verifikasi Lengkap

Setelah fix, pastikan:

1. ✅ `.env` sudah di-update dengan `APP_URL` dan `ASSET_URL` yang benar
2. ✅ Cache sudah di-clear dan rebuild
3. ✅ Route `/` ada di `route:list`
4. ✅ `.htaccess` tidak menggunakan `RewriteBase`
5. ✅ Aplikasi bisa diakses di `http://192.168.10.40/hris-seven-payroll`

---

**SELESAI!** 🎉

Setelah menjalankan script atau langkah manual di atas, error seharusnya sudah teratasi.




