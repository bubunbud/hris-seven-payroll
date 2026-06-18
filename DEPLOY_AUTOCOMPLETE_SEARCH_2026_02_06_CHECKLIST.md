# ✅ Checklist Deployment: Update Autocomplete Search Filter
**Tanggal:** 06 Februari 2026

---

## 📋 Pre-Deployment

- [ ] Backup database
- [ ] Backup file yang akan diubah (jika perlu)
- [ ] Review perubahan di local environment

---

## 📤 Upload Files

### **Controllers (3 files)**
- [ ] `app/Http/Controllers/StatistikAbsensiController.php`
- [ ] `app/Http/Controllers/IzinKeluarController.php`
- [ ] `app/Http/Controllers/TidakMasukController.php`

### **Views (3 files)**
- [ ] `resources/views/absen/statistik/index.blade.php`
- [ ] `resources/views/absen/izin_keluar/index.blade.php`
- [ ] `resources/views/absen/tidak_masuk/index.blade.php`

**Total: 6 files**

---

## ⚙️ Server Configuration

- [ ] Set file permissions (644 untuk file, 755 untuk directory)
- [ ] Set file ownership (www-data:www-data)
- [ ] Clear Laravel cache: `php artisan cache:clear`
- [ ] Clear config cache: `php artisan config:clear`
- [ ] Clear route cache: `php artisan route:clear`
- [ ] Clear view cache: `php artisan view:clear`
- [ ] Regenerate autoload: `composer dump-autoload` (jika perlu)
- [ ] Restart web server (jika perlu)

---

## ✅ Verification

### **File Check**
- [ ] Semua file ter-upload dengan benar
- [ ] Permission file benar (644)
- [ ] Ownership file benar (www-data:www-data)

### **Log Check**
- [ ] Tidak ada error di `storage/logs/laravel.log`
- [ ] Tidak ada error di web server error log

---

## 🧪 Testing

### **Statistik Absensi**
- [ ] Autocomplete filter NIK/Nama bekerja
- [ ] Multi-term search bekerja (pisahkan dengan koma)
- [ ] Tombol Preview sejajar dengan field lainnya
- [ ] Statistik masih terhitung dengan benar setelah filter

### **Izin Keluar Komplek Kantor**
- [ ] Autocomplete filter NIK/Nama bekerja
- [ ] Multi-term search bekerja (pisahkan dengan koma)
- [ ] Tombol Preview sejajar dengan field lainnya
- [ ] Filter data bekerja dengan benar

### **Izin Tidak Masuk**
- [ ] Autocomplete filter NIK/Nama bekerja
- [ ] Multi-term search bekerja (pisahkan dengan koma)
- [ ] Tombol Preview sejajar dengan field lainnya
- [ ] Filter data bekerja dengan benar

---

## 📝 Post-Deployment

- [ ] Monitor aplikasi selama 1-2 hari
- [ ] Kumpulkan feedback dari user
- [ ] Dokumentasikan issue jika ada
- [ ] Update dokumentasi jika diperlukan

---

## 🆘 Rollback Plan (jika diperlukan)

Jika ada masalah kritis:

1. **Restore file dari backup:**
   ```bash
   # Restore controllers
   cp backup/StatistikAbsensiController.php app/Http/Controllers/
   cp backup/IzinKeluarController.php app/Http/Controllers/
   cp backup/TidakMasukController.php app/Http/Controllers/
   
   # Restore views
   cp backup/index.blade.php resources/views/absen/statistik/
   cp backup/index.blade.php resources/views/absen/izin_keluar/
   cp backup/index.blade.php resources/views/absen/tidak_masuk/
   ```

2. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

3. **Restart web server:**
   ```bash
   sudo systemctl restart apache2
   # atau
   sudo systemctl restart nginx
   ```

---

## 📦 Quick Reference: Files to Upload

```
Controllers:
- app/Http/Controllers/StatistikAbsensiController.php
- app/Http/Controllers/IzinKeluarController.php
- app/Http/Controllers/TidakMasukController.php

Views:
- resources/views/absen/statistik/index.blade.php
- resources/views/absen/izin_keluar/index.blade.php
- resources/views/absen/tidak_masuk/index.blade.php
```

---

**Checklist ini digunakan untuk memastikan semua langkah deployment dilakukan dengan benar.**











