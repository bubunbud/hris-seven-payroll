# ✅ Deployment Checklist: Autocomplete NIK/Nama - Izin Keluar, Izin Tidak Masuk, Update Closing Gaji

**Tanggal:** 6 Februari 2026  
**Versi:** 1.0

---

## 📋 Pre-Deployment

- [ ] Backup database (opsional tapi disarankan)
- [ ] Backup file yang akan diubah (jika ada)
- [ ] Review perubahan di local/staging environment
- [ ] Pastikan tidak ada konflik dengan perubahan lain

---

## 📤 File Upload

### File yang Perlu Di-Upload:

- [ ] `resources/views/absen/izin_keluar/index.blade.php`
- [ ] `resources/views/absen/tidak_masuk/index.blade.php`
- [ ] `app/Http/Controllers/UpdateClosingGajiController.php`
- [ ] `resources/views/proses/update-closing-gaji/index.blade.php`

### Upload Method:

- [ ] Via SCP (manual upload)
- [ ] Via Git (git pull)
- [ ] Via FTP/SFTP

---

## ⚙️ Server Configuration

- [ ] Set permission file (644 untuk file, 755 untuk directory)
- [ ] Set owner file sesuai web server user (www-data/apache)
- [ ] Clear Laravel cache:
  - [ ] `php artisan cache:clear`
  - [ ] `php artisan config:clear`
  - [ ] `php artisan route:clear`
  - [ ] `php artisan view:clear`
- [ ] Rebuild cache (untuk production):
  - [ ] `php artisan config:cache`
  - [ ] `php artisan route:cache`
  - [ ] `php artisan view:cache`

---

## 🧪 Verification

### 1. Izin Keluar Komplek Kantor

- [ ] Buka halaman "Izin Keluar Komplek Kantor"
- [ ] Klik "Tambah" → Modal terbuka
- [ ] Field "NIK / Nama" muncul
- [ ] Ketik 2+ karakter → Autocomplete muncul
- [ ] Navigasi keyboard bekerja (Arrow Up/Down, Enter)
- [ ] Pilih dari autocomplete → Format "NIK - Nama" terisi
- [ ] Nama preview muncul
- [ ] Submit form → Data tersimpan
- [ ] Edit mode → Field NIK readOnly, format "NIK - Nama" tampil

### 2. Izin Tidak Masuk

- [ ] Buka halaman "Izin Tidak Masuk"
- [ ] Klik "Tambah" → Modal terbuka
- [ ] Field "NIK / Nama" muncul
- [ ] Ketik 2+ karakter → Autocomplete muncul
- [ ] Navigasi keyboard bekerja (Arrow Up/Down, Enter)
- [ ] Pilih dari autocomplete → Format "NIK - Nama" terisi
- [ ] Nama preview muncul
- [ ] Submit form → Data tersimpan
- [ ] Edit mode → Field NIK readOnly, format "NIK - Nama" tampil

### 3. Update Closing Gaji

- [ ] Buka halaman "Update Closing Gaji"
- [ ] Field filter "NIK / Nama" muncul
- [ ] Ketik 2+ karakter → Autocomplete muncul
- [ ] Navigasi keyboard bekerja (Arrow Up/Down, Enter)
- [ ] Pilih dari autocomplete → Format "NIK - Nama" terisi
- [ ] Test multiple terms (pisahkan koma) → Bekerja
- [ ] Klik "Preview" → Data ter-filter benar
- [ ] Kolom filter proporsional (Periode Dari/Sampai kecil, NIK/Nama lebar)
- [ ] Tombol Preview sejajar dengan field lainnya

---

## 🐛 Troubleshooting (Jika Ada Masalah)

- [ ] Clear browser cache (Ctrl+F5 / Cmd+Shift+R)
- [ ] Cek browser console untuk error JavaScript
- [ ] Cek network tab untuk error AJAX
- [ ] Verifikasi `karyawanList` ter-load dari controller
- [ ] Verifikasi hidden field terisi saat submit
- [ ] Cek Laravel log untuk error server-side

---

## 📝 Post-Deployment

- [ ] Dokumentasi deployment selesai
- [ ] Informasi perubahan ke user/tim
- [ ] Monitor error log selama 24 jam pertama
- [ ] Kumpulkan feedback dari user

---

## 🔄 Rollback (Jika Diperlukan)

- [ ] Restore file dari backup
- [ ] Clear Laravel cache
- [ ] Verifikasi aplikasi kembali normal
- [ ] Dokumentasikan masalah yang terjadi

---

## ✅ Sign-Off

**Deployed by:** _________________  
**Date:** _________________  
**Time:** _________________  
**Status:** ☐ Success  ☐ Failed  ☐ Partial

**Verified by:** _________________  
**Date:** _________________  
**Time:** _________________

---

**Notes:**
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________










