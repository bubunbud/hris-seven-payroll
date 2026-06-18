# Checklist Deploy: Update Cetak Form Perjalanan Dinas

**Tanggal:** 13 Februari 2026  
**File yang diubah:** 1 file

---

## ✅ Checklist Cepat

### Pre-Deploy
- [ ] Backup file `resources/views/perjalanan-dinas/print.blade.php`
- [ ] Siapkan file baru `print.blade.php` dari local

### Deploy
- [ ] Upload file `print.blade.php` ke server
- [ ] Set permission: `chmod 644 resources/views/perjalanan-dinas/print.blade.php`
- [ ] Set ownership: `chown www-data:www-data resources/views/perjalanan-dinas/print.blade.php`
- [ ] Clear cache: `php artisan view:clear && php artisan config:clear && php artisan cache:clear`

### Testing
- [ ] Login ke aplikasi
- [ ] Buka Form Perjalanan Dinas
- [ ] Test print dengan data yang ada Destinasi
- [ ] Test print dengan data yang tidak ada Destinasi
- [ ] Verifikasi margin kiri/kanan lebih kecil
- [ ] Verifikasi logo & "ABN GROUP" posisi lebih rendah
- [ ] Verifikasi kolom NIK lebih kecil, Bisnis Unit lebih lebar
- [ ] Verifikasi jarak antar section lebih rapat
- [ ] Verifikasi kolom "Tanda Tangan/Cap" muncul di Destinasi
- [ ] Verifikasi label "Tiba" muncul
- [ ] Verifikasi data kosong tidak menampilkan "-"

---

## 🚀 Perintah Cepat Deploy

```bash
# Di server Ubuntu
cd /var/www/html/hris-seven-payroll

# Backup
cp resources/views/perjalanan-dinas/print.blade.php resources/views/perjalanan-dinas/print.blade.php.backup_$(date +%Y%m%d_%H%M%S)

# Upload file baru (via SCP/Git/manual), lalu:
chmod 644 resources/views/perjalanan-dinas/print.blade.php
chown www-data:www-data resources/views/perjalanan-dinas/print.blade.php
php artisan view:clear && php artisan config:clear && php artisan cache:clear
```

---

## 📋 File yang Diubah

| File | Lokasi | Status |
|------|--------|--------|
| `print.blade.php` | `resources/views/perjalanan-dinas/` | ✅ Update |

---

**Catatan:** Tidak ada perubahan database, controller, atau route. Hanya update view/template.






