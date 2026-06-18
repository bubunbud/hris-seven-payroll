# ✅ Checklist Deployment: Update Modul Absensi
**Tanggal:** 06 Februari 2026

---

## 📋 Pre-Deployment

- [ ] Backup database
- [ ] Backup file yang akan diubah (jika perlu)
- [ ] Review perubahan di local environment

---

## 📤 Upload Files

### **Controllers**
- [ ] `app/Http/Controllers/EditAbsensiController.php`
- [ ] `app/Http/Controllers/BrowseTidakAbsenController.php`

### **Views**
- [ ] `resources/views/edit-absensi/index.blade.php`
- [ ] `resources/views/absen/tidak-absen/index.blade.php`

### **Routes**
- [ ] `routes/web.php`

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

### **Route Check**
- [ ] `php artisan route:list | grep edit-absensi` → 6 routes (termasuk DELETE)
- [ ] `php artisan route:list | grep browse-tidak-absen` → 1 route

### **File Check**
- [ ] Semua file ter-upload dengan benar
- [ ] Permission file benar (644)
- [ ] Ownership file benar (www-data:www-data)

### **Log Check**
- [ ] Tidak ada error di `storage/logs/laravel.log`
- [ ] Tidak ada error di web server error log

---

## 🧪 Testing

### **Input/Edit Absensi Karyawan Per Periode**
- [ ] Autocomplete filter NIK/Nama bekerja
- [ ] Multi-term search bekerja (pisahkan dengan koma)
- [ ] Tombol Delete muncul untuk superadmin/administrator
- [ ] Tombol Delete TIDAK muncul untuk user lain
- [ ] Delete absensi berhasil dengan konfirmasi

### **Browse Tidak Absen (Alpha)**
- [ ] Autocomplete filter NIK/Nama bekerja
- [ ] Multi-term search bekerja (pisahkan dengan koma)
- [ ] Karyawan Management TIDAK muncul di autocomplete
- [ ] Karyawan Management TIDAK muncul di hasil data
- [ ] Kolom Shift Terjadwal dan Shift Aktual TIDAK ada
- [ ] Kolom Departemen muncul (antara Divisi dan Bagian)
- [ ] Kolom Group Pegawai muncul (setelah Bagian)
- [ ] Data Departemen dan Group Pegawai terisi dengan benar

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
   cp backup/EditAbsensiController.php app/Http/Controllers/
   cp backup/BrowseTidakAbsenController.php app/Http/Controllers/
   
   # Restore views
   cp backup/index.blade.php resources/views/edit-absensi/
   cp backup/index.blade.php resources/views/absen/tidak-absen/
   
   # Restore routes
   cp backup/web.php routes/
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

**Checklist ini digunakan untuk memastikan semua langkah deployment dilakukan dengan benar.**











