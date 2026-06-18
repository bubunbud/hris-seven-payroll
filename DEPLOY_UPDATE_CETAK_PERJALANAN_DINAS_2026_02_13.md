# Manual Deploy: Update Modul Cetak Form Perjalanan Dinas

**Tanggal:** 13 Februari 2026  
**Modul:** Form Perjalanan Dinas - Update Layout Cetak  
**Server:** Ubuntu

---

## 📋 Ringkasan Perubahan

Update ini mencakup perbaikan dan penyesuaian layout cetak Form Perjalanan Dinas berdasarkan feedback user:

1. **Margin halaman diperkecil** - Margin kiri/kanan dari 0.5cm menjadi 0.2cm
2. **Logo dan nama perusahaan diturunkan** - Posisi lebih dekat ke judul form
3. **Nama perusahaan diubah** - "AbadiNusa Group Of Companies" → "ABN GROUP"
4. **Kolom NIK dan Nama disesuaikan** - Lebar kolom dioptimalkan
5. **Kolom Bisnis Unit diperlebar** - Dari 15% menjadi 19%
6. **Jarak antar section diperkecil** - Menghemat margin vertikal
7. **Section Destinasi ditambahkan kolom Tanda Tangan/Cap**
8. **Label "Tiba" ditambahkan** - Di atas tanggal/jam kedatangan
9. **Rowspan kolom Keterangan & Tanda Tangan disesuaikan** - Dari 3 menjadi 4 untuk alignment
10. **Data kosong tidak menampilkan "-"** - Dibiarkan kosong jika tidak ada data

---

## 📁 File yang Berubah

### 1. View Print
- **File:** `resources/views/perjalanan-dinas/print.blade.php`
- **Perubahan:** Update layout, CSS, dan struktur tabel untuk section Destinasi

---

## 🚀 Langkah-Langkah Deploy

### **Langkah 1: Backup File Existing**

```bash
# Login ke server Ubuntu
ssh user@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup file yang akan diubah
cp resources/views/perjalanan-dinas/print.blade.php resources/views/perjalanan-dinas/print.blade.php.backup_$(date +%Y%m%d_%H%M%S)
```

### **Langkah 2: Upload File Baru**

**Opsi A: Menggunakan SCP (dari local Windows)**

```powershell
# Dari Windows PowerShell atau CMD
scp resources/views/perjalanan-dinas/print.blade.php user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/perjalanan-dinas/
```

**Opsi B: Menggunakan Git (jika menggunakan version control)**

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll
git pull origin main  # atau branch yang sesuai
```

**Opsi C: Copy-Paste Manual**

1. Buka file `resources/views/perjalanan-dinas/print.blade.php` di local
2. Copy seluruh isi file
3. Login ke server via SSH
4. Edit file di server: `nano resources/views/perjalanan-dinas/print.blade.php`
5. Paste isi file baru
6. Save (Ctrl+O, Enter, Ctrl+X)

### **Langkah 3: Set Permission File**

```bash
# Pastikan permission file benar
chmod 644 resources/views/perjalanan-dinas/print.blade.php

# Pastikan ownership benar (sesuaikan dengan user web server)
chown www-data:www-data resources/views/perjalanan-dinas/print.blade.php
# atau jika menggunakan user lain:
# chown apache:apache resources/views/perjalanan-dinas/print.blade.php
```

### **Langkah 4: Clear Cache Laravel**

```bash
# Clear view cache
php artisan view:clear

# Clear config cache (opsional, untuk memastikan)
php artisan config:clear

# Clear route cache (opsional)
php artisan route:clear

# Clear application cache
php artisan cache:clear
```

### **Langkah 5: Verifikasi File**

```bash
# Cek apakah file sudah terupload dengan benar
ls -lh resources/views/perjalanan-dinas/print.blade.php

# Cek isi file (opsional, untuk memastikan)
head -50 resources/views/perjalanan-dinas/print.blade.php
```

### **Langkah 6: Test di Browser**

1. Login ke aplikasi HRIS
2. Buka menu **Form Perjalanan Dinas**
3. Pilih salah satu data RPD
4. Klik tombol **Print** atau **Cetak**
5. Verifikasi perubahan:
   - ✅ Margin kiri/kanan lebih kecil
   - ✅ Logo dan "ABN GROUP" posisinya lebih rendah
   - ✅ Kolom NIK lebih kecil, Bisnis Unit lebih lebar
   - ✅ Jarak antar section lebih rapat
   - ✅ Section Destinasi ada kolom "Tanda Tangan/Cap"
   - ✅ Label "Tiba" muncul di atas tanggal/jam
   - ✅ Data kosong tidak menampilkan "-"

---

## ✅ Checklist Deploy

- [ ] Backup file existing (`print.blade.php`)
- [ ] Upload file baru ke server
- [ ] Set permission file (644)
- [ ] Set ownership file (www-data atau sesuai)
- [ ] Clear Laravel cache (`view:clear`, `config:clear`, `route:clear`, `cache:clear`)
- [ ] Test print Form Perjalanan Dinas
- [ ] Verifikasi semua perubahan layout sesuai
- [ ] Test dengan data yang ada Destinasi
- [ ] Test dengan data yang tidak ada Destinasi (kosong)

---

## 🔍 Troubleshooting

### **Masalah 1: Layout tidak berubah setelah deploy**

**Solusi:**
```bash
# Clear semua cache
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Hard refresh browser (Ctrl+F5 atau Ctrl+Shift+R)
```

### **Masalah 2: File tidak bisa diakses atau error 500**

**Solusi:**
```bash
# Cek permission file
ls -l resources/views/perjalanan-dinas/print.blade.php

# Pastikan permission 644
chmod 644 resources/views/perjalanan-dinas/print.blade.php

# Cek ownership
chown www-data:www-data resources/views/perjalanan-dinas/print.blade.php

# Cek error log
tail -50 storage/logs/laravel.log
```

### **Masalah 3: Layout print tidak sesuai (margin, spacing)**

**Solusi:**
- Pastikan browser menggunakan print preview (Ctrl+P)
- Cek apakah ada CSS custom di browser yang override
- Pastikan file `print.blade.php` sudah terupload lengkap (cek line count)

### **Masalah 4: Kolom Tanda Tangan/Cap tidak muncul**

**Solusi:**
- Pastikan file sudah terupload lengkap
- Clear view cache: `php artisan view:clear`
- Hard refresh browser

---

## 📝 Catatan Penting

1. **Tidak ada perubahan database** - Update ini hanya mengubah view/template
2. **Tidak ada perubahan controller** - Controller tidak diubah
3. **Tidak ada perubahan route** - Route tetap sama
4. **Backward compatible** - Data lama tetap bisa dicetak dengan layout baru
5. **Tidak perlu migration** - Tidak ada perubahan struktur database

---

## 🔄 Rollback (Jika Diperlukan)

Jika ada masalah dan perlu rollback ke versi sebelumnya:

```bash
# Restore dari backup
cp resources/views/perjalanan-dinas/print.blade.php.backup_YYYYMMDD_HHMMSS resources/views/perjalanan-dinas/print.blade.php

# Clear cache
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

---

## 📞 Kontak & Support

Jika ada masalah saat deploy atau setelah deploy, silakan:
1. Cek error log: `storage/logs/laravel.log`
2. Cek browser console (F12) untuk error JavaScript
3. Verifikasi file sudah terupload dengan benar
4. Pastikan semua cache sudah di-clear

---

## ✨ Summary

**File yang diubah:** 1 file  
**Waktu estimasi deploy:** 5-10 menit  
**Risiko:** Rendah (hanya update view, tidak ada perubahan database/controller)  
**Dependency:** Tidak ada

**Perintah cepat deploy:**
```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll
cp resources/views/perjalanan-dinas/print.blade.php resources/views/perjalanan-dinas/print.blade.php.backup_$(date +%Y%m%d_%H%M%S)
# Upload file baru (via SCP/Git/manual)
chmod 644 resources/views/perjalanan-dinas/print.blade.php
chown www-data:www-data resources/views/perjalanan-dinas/print.blade.php
php artisan view:clear && php artisan config:clear && php artisan cache:clear
```

---

**Dokumen ini dibuat:** 13 Februari 2026  
**Versi:** 1.0






