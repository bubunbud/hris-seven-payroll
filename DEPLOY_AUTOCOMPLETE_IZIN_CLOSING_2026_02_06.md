# 📋 Deployment Guide: Autocomplete NIK/Nama - Izin Keluar, Izin Tidak Masuk, Update Closing Gaji

**Tanggal:** 6 Februari 2026  
**Versi:** 1.0  
**Status:** Ready for Production

---

## 🎯 Ringkasan Perubahan

Implementasi autocomplete search filter NIK/Nama pada:
1. **Izin Keluar Komplek Kantor** - Form "Tambah Izin Keluar" (modal)
2. **Izin Tidak Masuk** - Form "Tambah Izin Tidak Masuk" (modal)
3. **Update Closing Gaji** - Filter form (halaman utama)

Semua menggunakan konsep autocomplete yang sama dengan "Browse Absensi Karyawan Per Periode".

---

## 📁 File yang Diubah

### 1. Izin Keluar Komplek Kantor
- **File:** `resources/views/absen/izin_keluar/index.blade.php`
- **Perubahan:**
  - Field NIK di modal "Tambah Izin Keluar" diubah menjadi autocomplete NIK/Nama
  - Menambahkan hidden field untuk NIK murni
  - Menambahkan JavaScript autocomplete untuk modal
  - Menambahkan CSS untuk styling autocomplete di modal

### 2. Izin Tidak Masuk
- **File:** `resources/views/absen/tidak_masuk/index.blade.php`
- **Perubahan:**
  - Field NIK di modal "Tambah Izin Tidak Masuk" diubah menjadi autocomplete NIK/Nama
  - Menambahkan hidden field untuk NIK murni
  - Menambahkan JavaScript autocomplete untuk modal
  - Menambahkan CSS untuk styling autocomplete di modal

### 3. Update Closing Gaji
- **File:** `app/Http/Controllers/UpdateClosingGajiController.php`
- **File:** `resources/views/proses/update-closing-gaji/index.blade.php`
- **Perubahan:**
  - Controller: Mengubah filter dari parameter `nik` menjadi `search` (NIK/Nama)
  - Controller: Menambahkan load `karyawanList` untuk autocomplete
  - View: Mengubah field filter dari `nik` menjadi `search` dengan autocomplete
  - View: Menyesuaikan lebar kolom (Periode Dari/Sampai diperkecil, NIK/Nama diperlebar)
  - View: Menyelaraskan posisi tombol Preview dengan field lainnya
  - View: Menambahkan JavaScript dan CSS untuk autocomplete

---

## 🚀 Langkah-Langkah Deployment

### Step 1: Backup Database (Opsional tapi Disarankan)

```bash
# Backup database sebelum deployment
mysqldump -u [username] -p [database_name] > backup_before_autocomplete_update_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Upload File ke Server

**Via SCP (dari local ke server):**

```bash
# Izin Keluar Komplek Kantor
scp resources/views/absen/izin_keluar/index.blade.php [user]@[server]:/path/to/project/resources/views/absen/izin_keluar/

# Izin Tidak Masuk
scp resources/views/absen/tidak_masuk/index.blade.php [user]@[server]:/path/to/project/resources/views/absen/tidak_masuk/

# Update Closing Gaji - Controller
scp app/Http/Controllers/UpdateClosingGajiController.php [user]@[server]:/path/to/project/app/Http/Controllers/

# Update Closing Gaji - View
scp resources/views/proses/update-closing-gaji/index.blade.php [user]@[server]:/path/to/project/resources/views/proses/update-closing-gaji/
```

**Atau via Git (jika menggunakan version control):**

```bash
# Di server
cd /path/to/project
git pull origin [branch-name]
```

### Step 3: Set Permission File

```bash
# Set permission untuk file yang di-upload
chmod 644 resources/views/absen/izin_keluar/index.blade.php
chmod 644 resources/views/absen/tidak_masuk/index.blade.php
chmod 644 app/Http/Controllers/UpdateClosingGajiController.php
chmod 644 resources/views/proses/update-closing-gaji/index.blade.php

# Pastikan owner file sesuai dengan web server user
chown www-data:www-data resources/views/absen/izin_keluar/index.blade.php
chown www-data:www-data resources/views/absen/tidak_masuk/index.blade.php
chown www-data:www-data app/Http/Controllers/UpdateClosingGajiController.php
chown www-data:www-data resources/views/proses/update-closing-gaji/index.blade.php
```

### Step 4: Clear Laravel Cache

```bash
cd /path/to/project

# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear compiled files
php artisan clear-compiled

# Rebuild cache (opsional, untuk production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 5: Clear Browser Cache

**Penting:** User harus clear browser cache atau hard refresh (Ctrl+F5 / Cmd+Shift+R) untuk memastikan JavaScript dan CSS terbaru ter-load.

---

## ✅ Testing Checklist

### 1. Izin Keluar Komplek Kantor

- [ ] Buka halaman "Izin Keluar Komplek Kantor"
- [ ] Klik tombol "Tambah" untuk membuka modal
- [ ] Di field "NIK / Nama", ketik minimal 2 karakter
- [ ] Pastikan dropdown autocomplete muncul dengan daftar karyawan
- [ ] Test navigasi keyboard (Arrow Up/Down, Enter)
- [ ] Pilih karyawan dari autocomplete
- [ ] Pastikan field terisi dengan format "NIK - Nama"
- [ ] Pastikan nama karyawan muncul di preview
- [ ] Submit form dan pastikan data tersimpan dengan NIK yang benar
- [ ] Test edit mode: pastikan field NIK readOnly dan menampilkan "NIK - Nama"

### 2. Izin Tidak Masuk

- [ ] Buka halaman "Izin Tidak Masuk"
- [ ] Klik tombol "Tambah" untuk membuka modal
- [ ] Di field "NIK / Nama", ketik minimal 2 karakter
- [ ] Pastikan dropdown autocomplete muncul dengan daftar karyawan
- [ ] Test navigasi keyboard (Arrow Up/Down, Enter)
- [ ] Pilih karyawan dari autocomplete
- [ ] Pastikan field terisi dengan format "NIK - Nama"
- [ ] Pastikan nama karyawan muncul di preview
- [ ] Submit form dan pastikan data tersimpan dengan NIK yang benar
- [ ] Test edit mode: pastikan field NIK readOnly dan menampilkan "NIK - Nama"

### 3. Update Closing Gaji

- [ ] Buka halaman "Update Closing Gaji"
- [ ] Di field filter "NIK / Nama", ketik minimal 2 karakter
- [ ] Pastikan dropdown autocomplete muncul dengan daftar karyawan
- [ ] Test navigasi keyboard (Arrow Up/Down, Enter)
- [ ] Pilih karyawan dari autocomplete
- [ ] Pastikan field terisi dengan format "NIK - Nama"
- [ ] Test multiple terms (pisahkan dengan koma)
- [ ] Klik tombol "Preview" dan pastikan data ter-filter dengan benar
- [ ] Pastikan lebar kolom filter sudah proporsional (Periode Dari/Sampai lebih kecil, NIK/Nama lebih lebar)
- [ ] Pastikan tombol Preview sejajar dengan field lainnya

---

## 🔍 Troubleshooting

### Masalah: Autocomplete tidak muncul

**Kemungkinan Penyebab:**
1. JavaScript belum ter-load (browser cache)
2. Error JavaScript di console
3. `karyawanList` tidak ter-load dari controller

**Solusi:**
1. Clear browser cache dan hard refresh (Ctrl+F5)
2. Buka browser console (F12) dan cek error JavaScript
3. Pastikan controller mengirim `karyawanList` ke view
4. Pastikan `@json($karyawanList ?? [])` di view tidak error

### Masalah: Form submit gagal dengan error "NIK harus diisi"

**Kemungkinan Penyebab:**
1. Hidden field `vcNikHidden` tidak terisi
2. JavaScript autocomplete tidak set hidden field dengan benar

**Solusi:**
1. Pastikan saat memilih dari autocomplete, hidden field terisi
2. Pastikan saat mengetik manual, hidden field terisi saat blur
3. Cek browser console untuk error JavaScript

### Masalah: Edit mode tidak menampilkan "NIK - Nama"

**Kemungkinan Penyebab:**
1. Fetch data karyawan gagal saat edit
2. JavaScript edit mode tidak set format dengan benar

**Solusi:**
1. Cek network tab di browser console, pastikan fetch `/karyawan/{nik}` berhasil
2. Pastikan JavaScript edit mode memanggil fetch dan set format dengan benar

### Masalah: Filter Update Closing Gaji tidak bekerja

**Kemungkinan Penyebab:**
1. Controller tidak menerima parameter `search`
2. Query filter tidak benar

**Solusi:**
1. Pastikan form menggunakan `name="search"` bukan `name="nik"`
2. Pastikan controller menggunakan `$request->get('search')`
3. Cek route cache sudah di-clear

---

## 📝 Catatan Penting

1. **Backward Compatibility:**
   - Update Closing Gaji masih support parameter `nik` lama (akan digabung ke `search`)
   - Izin Keluar dan Izin Tidak Masuk tidak ada perubahan di filter, hanya di form modal

2. **Browser Compatibility:**
   - Autocomplete menggunakan vanilla JavaScript (tanpa library tambahan)
   - Compatible dengan browser modern (Chrome, Firefox, Safari, Edge)

3. **Performance:**
   - Autocomplete menggunakan client-side filtering (tidak ada AJAX request)
   - Data karyawan di-load sekali saat halaman dimuat
   - Maksimal 20 hasil ditampilkan di dropdown

4. **Accessibility:**
   - Support keyboard navigation (Arrow Up/Down, Enter)
   - Support mouse click untuk memilih

---

## 🔄 Rollback Plan (Jika Diperlukan)

Jika terjadi masalah dan perlu rollback:

```bash
# Restore file dari backup
# Izin Keluar Komplek Kantor
cp backup/resources/views/absen/izin_keluar/index.blade.php resources/views/absen/izin_keluar/index.blade.php

# Izin Tidak Masuk
cp backup/resources/views/absen/tidak_masuk/index.blade.php resources/views/absen/tidak_masuk/index.blade.php

# Update Closing Gaji - Controller
cp backup/app/Http/Controllers/UpdateClosingGajiController.php app/Http/Controllers/UpdateClosingGajiController.php

# Update Closing Gaji - View
cp backup/resources/views/proses/update-closing-gaji/index.blade.php resources/views/proses/update-closing-gaji/index.blade.php

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 📞 Kontak Support

Jika ada masalah atau pertanyaan terkait deployment ini, silakan hubungi tim development.

---

**Dokumen ini dibuat pada:** 6 Februari 2026  
**Terakhir diupdate:** 6 Februari 2026










