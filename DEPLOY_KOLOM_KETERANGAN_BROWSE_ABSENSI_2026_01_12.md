# 🚀 Manual Deploy: Kolom Keterangan di Browse Absensi Karyawan Per Periode

**Tanggal:** 12 Januari 2026  
**Fitur:** Tambah Kolom "Keterangan" di Halaman Browse Absensi Karyawan Per Periode

---

## 🎯 Ringkasan

Menambahkan kolom **"Keterangan"** di halaman Browse Absensi Karyawan Per Periode yang menampilkan data dari `t_absen.vcketerangan`.

**Perubahan:**
- ✅ Kolom "Keterangan" ditambahkan di tabel list absensi
- ✅ Menampilkan `vcketerangan` dari tabel `t_absen` untuk data absen
- ✅ Menampilkan `vcKeterangan` dari tabel `t_tidak_masuk` untuk data tidak masuk

---

## 📋 File yang Harus Di-Copy ke Server Production

### **1. Controller**

**File:** `app/Http/Controllers/AbsenController.php`

**Lokasi di Server:**
```
/var/www/html/hris-seven-payroll/app/Http/Controllers/AbsenController.php
```

**Perubahan yang Dilakukan:**
- Line 125: Menambahkan `'t_absen.vcketerangan'` ke SELECT query
- Line 373: Menambahkan `'vcketerangan' => $absen->vcketerangan ?? null` ke array mapping
- Line 801: Menambahkan `'vcketerangan' => $absen->vcketerangan ?? null` ke array mapping (untuk method kedua)

---

### **2. View**

**File:** `resources/views/absen/index.blade.php`

**Lokasi di Server:**
```
/var/www/html/hris-seven-payroll/resources/views/absen/index.blade.php
```

**Perubahan yang Dilakukan:**
- Line 149: Menambahkan header kolom `<th width="10%">Keterangan</th>`
- Line 180: Menambahkan `$vcketerangan = $item['vcketerangan'] ?? null;` untuk mengambil data
- Line 356-364: Menambahkan kolom keterangan di tabel dengan logika:
  - Jika `source === 'absen'` dan ada `vcketerangan` → tampilkan `vcketerangan` dari `t_absen`
  - Jika `source === 'tidak_masuk'` dan ada `vcKeterangan` → tampilkan `vcKeterangan` dari `t_tidak_masuk`
  - Jika tidak ada → tampilkan "-"

---

## 🚀 Langkah-Langkah Deployment

### **Step 1: Backup File yang Akan Diubah**

```bash
# Login ke server production
ssh user@server-ip

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup file yang akan diubah
cp app/Http/Controllers/AbsenController.php app/Http/Controllers/AbsenController.php.backup_$(date +%Y%m%d)
cp resources/views/absen/index.blade.php resources/views/absen/index.blade.php.backup_$(date +%Y%m%d)
```

---

### **Step 2: Copy File Baru**

**Opsi A: Copy via SCP (dari local ke server)**

```bash
# Dari local machine
scp app/Http/Controllers/AbsenController.php user@server-ip:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp resources/views/absen/index.blade.php user@server-ip:/var/www/html/hris-seven-payroll/resources/views/absen/
```

**Opsi B: Copy via FTP/SFTP**

1. Upload file `app/Http/Controllers/AbsenController.php` ke:
   ```
   /var/www/html/hris-seven-payroll/app/Http/Controllers/AbsenController.php
   ```

2. Upload file `resources/views/absen/index.blade.php` ke:
   ```
   /var/www/html/hris-seven-payroll/resources/views/absen/index.blade.php
   ```

**Opsi C: Copy via Git (jika menggunakan version control)**

```bash
# Di server production
cd /var/www/html/hris-seven-payroll
git pull origin main  # atau branch yang sesuai
```

---

### **Step 3: Set Permission File**

```bash
# Set permission yang benar
chmod 644 app/Http/Controllers/AbsenController.php
chmod 644 resources/views/absen/index.blade.php

# Pastikan owner benar (sesuaikan dengan user web server)
chown www-data:www-data app/Http/Controllers/AbsenController.php
chown www-data:www-data resources/views/absen/index.blade.php
```

---

### **Step 4: Clear Cache Laravel**

```bash
# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Rebuild cache (opsional, untuk performa)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### **Step 5: Verifikasi Deployment**

1. **Akses Halaman Browse Absensi:**
   - Login ke aplikasi
   - Buka menu: **Absensi → Browse Absensi Karyawan Per Periode**
   - Pilih periode (contoh: 1-31 Januari 2026)

2. **Cek Kolom Keterangan:**
   - ✅ Pastikan kolom "Keterangan" muncul di header tabel
   - ✅ Pastikan data `vcketerangan` dari `t_absen` tampil untuk data absen
   - ✅ Pastikan data `vcKeterangan` dari `t_tidak_masuk` tampil untuk data tidak masuk
   - ✅ Pastikan menampilkan "-" jika tidak ada keterangan

3. **Test dengan Data:**
   - Cari karyawan yang memiliki data absen dengan `vcketerangan` terisi
   - Cari karyawan yang memiliki data tidak masuk dengan `vcKeterangan` terisi
   - Verifikasi kolom keterangan menampilkan data dengan benar

---

## ✅ Checklist Deployment

- [ ] **Backup file lama** (`AbsenController.php` dan `index.blade.php`)
- [ ] **Copy file baru** ke server production
- [ ] **Set permission** file dengan benar
- [ ] **Clear cache** Laravel (config, cache, view, route)
- [ ] **Verifikasi kolom Keterangan** muncul di header tabel
- [ ] **Test dengan data absen** yang memiliki `vcketerangan`
- [ ] **Test dengan data tidak masuk** yang memiliki `vcKeterangan`
- [ ] **Test dengan data tanpa keterangan** (harus menampilkan "-")
- [ ] **Verifikasi tidak ada error** di halaman Browse Absensi

---

## 📝 Detail Perubahan File

### **File 1: `app/Http/Controllers/AbsenController.php`**

**Perubahan di Query SELECT (Line ~125):**
```php
->select(
    't_absen.dtTanggal',
    't_absen.vcNik',
    't_absen.dtJamMasuk',
    't_absen.dtJamKeluar',
    't_absen.dtJamMasukLembur',
    't_absen.dtJamKeluarLembur',
    't_absen.vcketerangan',  // ✅ TAMBAHAN
    'm_karyawan.Nama',
    // ... field lain
)
```

**Perubahan di Array Mapping (Line ~373):**
```php
'vcketerangan' => $absen->vcketerangan ?? null,  // ✅ TAMBAHAN
```

---

### **File 2: `resources/views/absen/index.blade.php`**

**Perubahan di Header Tabel (Line ~149):**
```blade
<th width="10%">Keterangan</th>  // ✅ TAMBAHAN
```

**Perubahan di Body Tabel (Line ~356-364):**
```blade
<td>
    @if($source === 'absen' && $vcketerangan)
        <small class="text-muted">{{ $vcketerangan }}</small>
    @elseif($source === 'tidak_masuk' && $vcKeterangan)
        <small class="text-muted">{{ $vcKeterangan }}</small>
    @else
        <span class="text-muted">-</span>
    @endif
</td>
```

---

## ⚠️ Catatan Penting

1. **Database:**
   - Tidak ada perubahan struktur database
   - Kolom `vcketerangan` sudah ada di tabel `t_absen`
   - Kolom `vcKeterangan` sudah ada di tabel `t_tidak_masuk`

2. **Backward Compatibility:**
   - Jika data tidak memiliki keterangan, akan menampilkan "-"
   - Tidak akan menyebabkan error jika field `vcketerangan` kosong/null

3. **Performance:**
   - Query sudah dioptimasi dengan SELECT field yang diperlukan saja
   - Tidak ada query tambahan yang memberatkan

4. **Rollback:**
   - Jika ada masalah, gunakan file backup yang sudah dibuat
   - Restore file backup dengan command:
     ```bash
     cp app/Http/Controllers/AbsenController.php.backup_YYYYMMDD app/Http/Controllers/AbsenController.php
     cp resources/views/absen/index.blade.php.backup_YYYYMMDD resources/views/absen/index.blade.php
     php artisan view:clear
     ```

---

## 🔗 Related Documents

- **Analisis Perhitungan Telat:** `ANALISIS_PERHITUNGAN_TELAT_2026_01_12.md`
- **Perbaikan Perhitungan Telat:** `PERBAIKAN_PERHITUNGAN_TELAT_2026_01_12.md`

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0
















