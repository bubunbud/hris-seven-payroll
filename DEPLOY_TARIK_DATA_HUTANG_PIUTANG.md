# 📋 Panduan Deployment: Fitur Tarik Data Hutang Piutang

**Tanggal:** 8 Desember 2025  
**Fitur:** Tarik Data Hutang Piutang dari Server Remote  
**Lokasi Menu:** Settings → Tarik Data Hutang Piutang

---

## 📦 File yang Perlu Di-Copy

### 1. Controller (1 file)
```
app/Http/Controllers/TarikDataHutangPiutangController.php
```

### 2. View (1 file)
```
resources/views/tarik-data-hutang-piutang/index.blade.php
```

### 3. Route (1 file - update)
```
routes/web.php
```

### 4. Layout (1 file - update)
```
resources/views/layouts/app.blade.php
```

**Total: 4 file (2 file baru, 2 file update)**

---

## 🚀 Langkah-Langkah Deployment

### **Langkah 1: Backup Database (Opsional tapi Disarankan)**

```bash
# Login ke server Ubuntu
ssh superadmin@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup database (opsional)
mysqldump -u root -p seven > backup_seven_$(date +%Y%m%d_%H%M%S).sql
```

### **Langkah 2: Copy File dari Local ke Server**

**Dari komputer lokal (Windows), jalankan perintah berikut:**

```bash
# Pastikan Anda sudah terhubung ke server via SSH atau SCP

# Copy Controller
scp app/Http/Controllers/TarikDataHutangPiutangController.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# Buat direktori view jika belum ada
ssh superadmin@192.168.10.40 "mkdir -p /var/www/html/hris-seven-payroll/resources/views/tarik-data-hutang-piutang"

# Copy View
scp resources/views/tarik-data-hutang-piutang/index.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/tarik-data-hutang-piutang/

# Copy Route (update)
scp routes/web.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/routes/

# Copy Layout (update)
scp resources/views/layouts/app.blade.php superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/layouts/
```

**ATAU menggunakan FileZilla/WinSCP:**
- Upload file-file di atas ke lokasi yang sesuai di server

### **Langkah 3: Set Permissions (di Server Ubuntu)**

```bash
# Login ke server
ssh superadmin@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Set ownership ke www-data
sudo chown -R www-data:www-data app/Http/Controllers/TarikDataHutangPiutangController.php
sudo chown -R www-data:www-data resources/views/tarik-data-hutang-piutang/
sudo chown -R www-data:www-data routes/web.php
sudo chown -R www-data:www-data resources/views/layouts/app.blade.php

# Set permissions
sudo chmod 644 app/Http/Controllers/TarikDataHutangPiutangController.php
sudo chmod 755 resources/views/tarik-data-hutang-piutang/
sudo chmod 644 resources/views/tarik-data-hutang-piutang/index.blade.php
sudo chmod 644 routes/web.php
sudo chmod 644 resources/views/layouts/app.blade.php
```

### **Langkah 4: Clear Cache Laravel**

```bash
# Masih di direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Clear semua cache
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Rebuild cache (opsional, untuk performa)
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### **Langkah 5: Verifikasi**

```bash
# Cek route sudah terdaftar
sudo -u www-data php artisan route:list | grep tarik-data-hutang-piutang

# Output yang diharapkan:
# GET|HEAD  tarik-data-hutang-piutang ................ tarik-data-hutang-piutang.index
# POST      tarik-data-hutang-piutang/pull ............ tarik-data-hutang-piutang.pull
```

### **Langkah 6: Test di Browser**

1. Buka browser dan akses aplikasi: `http://192.168.10.40/` (atau sesuai konfigurasi)
2. Login dengan user yang memiliki permission `view-settings`
3. Klik menu **Settings** di sidebar
4. Pastikan menu **"Tarik Data Hutang Piutang"** muncul di submenu Settings (di bawah "Tarik Data Tidak Masuk")
5. Klik menu **"Tarik Data Hutang Piutang"**
6. Pastikan halaman form muncul dengan benar
7. Test koneksi dengan mengisi form dan klik "Tarik Data"

---

## ✅ Checklist Deployment

- [ ] Backup database (opsional)
- [ ] Copy `TarikDataHutangPiutangController.php` ke server
- [ ] Copy `tarik-data-hutang-piutang/index.blade.php` ke server
- [ ] Update `routes/web.php` di server
- [ ] Update `layouts/app.blade.php` di server
- [ ] Set ownership ke `www-data:www-data`
- [ ] Set permissions dengan benar
- [ ] Clear cache Laravel (cache, config, route, view)
- [ ] Rebuild cache (opsional)
- [ ] Verifikasi route dengan `php artisan route:list`
- [ ] Test menu di browser
- [ ] Test form tarik data

---

## 🔍 Troubleshooting

### **Error: Route not found**
```bash
# Clear route cache
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan route:cache

# Cek route sudah terdaftar
sudo -u www-data php artisan route:list | grep tarik-data-hutang-piutang
```

### **Error: Class not found**
```bash
# Clear semua cache
sudo -u www-data php artisan optimize:clear

# Rebuild autoload
sudo -u www-data composer dump-autoload
```

### **Error: Permission denied**
```bash
# Set ownership dan permissions
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Http/Controllers/TarikDataHutangPiutangController.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/resources/views/tarik-data-hutang-piutang/
sudo chmod 644 app/Http/Controllers/TarikDataHutangPiutangController.php
sudo chmod 644 resources/views/tarik-data-hutang-piutang/index.blade.php
```

### **Menu tidak muncul di sidebar**
- Pastikan user memiliki permission `view-settings`
- Clear view cache: `sudo -u www-data php artisan view:clear`
- Cek file `layouts/app.blade.php` sudah ter-update

### **Error koneksi database remote**
- Pastikan server remote dapat diakses dari server Ubuntu
- Cek firewall dan network configuration
- Test koneksi manual dengan MySQL client:
  ```bash
  mysql -h 192.168.10.40 -u root -p -P 3306
  ```

### **Error: Field wajib tidak lengkap**
- Pastikan field `dtTanggalAwal`, `dtTanggalAkhir`, `vcNik`, dan `vcJenis` dipilih
- Field-field tersebut wajib untuk composite key di tabel `t_hutang_piutang`

---

## 📝 Catatan Penting

1. **Tidak ada perubahan database**: Fitur ini tidak memerlukan migration atau perubahan struktur database
2. **Permission**: User harus memiliki permission `view-settings` untuk mengakses menu ini
3. **Koneksi Remote**: Pastikan server Ubuntu dapat mengakses server remote (firewall, network)
4. **Composite Key**: Tabel `t_hutang_piutang` menggunakan composite key: `dtTanggalAwal + dtTanggalAkhir + vcNik + vcJenis`
5. **Filter Periode**: Filter berdasarkan overlap periode (sama seperti di HutangPiutangController):
   - Periode awal dalam range filter
   - Periode akhir dalam range filter
   - Periode mencakup seluruh range filter
6. **Update Logic**: Data akan di-update jika composite key sudah ada di database lokal

---

## 🔄 Rollback (Jika Perlu)

Jika terjadi masalah dan perlu rollback:

```bash
# Hapus file baru
rm -f app/Http/Controllers/TarikDataHutangPiutangController.php
rm -rf resources/views/tarik-data-hutang-piutang/

# Restore routes/web.php dan layouts/app.blade.php dari backup
# (jika ada backup sebelumnya)

# Clear cache
sudo -u www-data php artisan optimize:clear
```

---

## 📞 Support

Jika ada masalah saat deployment, cek:
1. Log Laravel: `storage/logs/laravel.log`
2. Log Apache: `/var/log/apache2/error.log`
3. Permission file dan direktori
4. Route sudah terdaftar dengan benar

---

**Selamat! Fitur Tarik Data Hutang Piutang sudah siap digunakan.** 🎉









