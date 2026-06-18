# 📋 Panduan Deployment: Fix Memory Exhausted - Browse Absensi

**Tanggal:** 9 Desember 2025  
**Masalah:** Memory exhausted saat browse data absensi >4 bulan  
**Solusi:** Optimasi dengan chunking dan peningkatan memory limit

**Catatan:** Tidak ada perubahan database/migration

---

## 📦 File yang Perlu Di-Copy

### 1. Controller (1 file - UPDATE)
```
app/Http/Controllers/AbsenController.php
```

**Total: 1 file (update)**

---

## 🚀 Langkah-Langkah Deployment

### **Langkah 1: Copy File dari Local ke Server**

**Dari komputer lokal (Windows), jalankan perintah berikut:**

```bash
# Copy AbsenController
scp app/Http/Controllers/AbsenController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/
```

**ATAU menggunakan FileZilla/WinSCP:**
- Upload file `app/Http/Controllers/AbsenController.php` ke `/var/www/html/hris-seven-payroll/app/Http/Controllers/`

---

### **Langkah 2: Set Permission File (Di Server Ubuntu)**

```bash
# Login ke server Ubuntu
ssh superadmin@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Set ownership ke www-data
sudo chown www-data:www-data app/Http/Controllers/AbsenController.php

# Set permission file
sudo chmod 644 app/Http/Controllers/AbsenController.php
```

---

### **Langkah 3: Clear Cache Laravel (Di Server Ubuntu)**

```bash
# Masih di direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

### **Langkah 4: Verifikasi**

1. **Cek File Permission:**
   ```bash
   ls -la app/Http/Controllers/AbsenController.php
   ```
   Harus menunjukkan: `-rw-r--r-- 1 www-data www-data ...`

2. **Test Aplikasi:**
   - Buka browser: `http://hr.abncorp.lan` atau `http://192.168.10.40`
   - Login ke aplikasi
   - Test Browse Absensi dengan periode >4 bulan
   - Pastikan tidak ada error "memory exhausted"

---

## 📝 Checklist Deployment

- [ ] Copy 1 Controller file (AbsenController.php)
- [ ] Set permission file (644, owner www-data)
- [ ] Clear Laravel cache
- [ ] Test Browse Absensi dengan periode >4 bulan
- [ ] Verifikasi tidak ada error memory exhausted

---

## ⚠️ Catatan Penting

1. **Tidak ada perubahan database/migration** - hanya update file PHP
2. **Tidak perlu run `php artisan migrate`**
3. **Pastikan permission file sudah benar** (644 untuk file)
4. **Clear cache wajib** agar perubahan terdeteksi
5. **Test dengan periode >4 bulan** untuk memastikan fix berfungsi

---

## 🔧 Troubleshooting

### Error: Memory exhausted masih terjadi
**Solusi:** Tingkatkan memory_limit di `php.ini` atau `.htaccess`:
```bash
# Edit php.ini
sudo nano /etc/php/8.1/apache2/php.ini
# Cari: memory_limit = 128M
# Ubah menjadi: memory_limit = 512M
# Restart Apache
sudo systemctl restart apache2
```

### Error: Permission denied
```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Http/Controllers/
sudo chmod -R 644 /var/www/html/hris-seven-payroll/app/Http/Controllers/*.php
```

### Error: Class not found
```bash
composer dump-autoload
```

---

## 📞 Informasi Server

- **Server IP:** 192.168.10.40
- **Domain:** http://hr.abncorp.lan
- **Lokasi Aplikasi:** /var/www/html/hris-seven-payroll
- **User:** superadmin
- **Database:** hris_seven

---

## 🔍 Perubahan yang Dilakukan

1. **Meningkatkan memory limit:** `ini_set('memory_limit', '512M')`
2. **Meningkatkan timeout:** `set_time_limit(300)`
3. **Optimasi dengan chunking:** Proses data per 1000 record
4. **Optimasi $absenExists:** Proses per 5000 record dengan chunking

---

**Selamat Deploy! 🚀**








