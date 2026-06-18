# 📝 Update: Field Tipe/Kategori Izin Keluar Komplek

## 🎯 Ringkasan

Menambahkan field **Tipe/Kategori Izin** pada form Izin Keluar Komplek Kantor. Field ini muncul secara otomatis ketika jenis izin dipilih sebagai **"pribadi"** (Z003 atau Z004), dengan pilihan:
- Masuk Siang
- Izin Biasa
- Pulang Cepat

---

## 📋 Perubahan yang Dilakukan

### 1. Database

**Tabel:** `t_izin`

**Field Baru:**
- `vcTipeIzin` (VARCHAR(20), nullable)
  - Lokasi: setelah kolom `vcKodeIzin`
  - Comment: "Tipe/Kategori Izin: Masuk Siang, Izin Biasa, Pulang Cepat (hanya untuk jenis izin pribadi)"

**Migration File:**
- `database/migrations/2025_12_04_045022_add_vc_tipe_izin_to_t_izin_table.php`

### 2. Model

**File:** `app/Models/Izin.php`

**Perubahan:**
- Tambah `vcTipeIzin` ke array `$fillable`

### 3. Controller

**File:** `app/Http/Controllers/IzinKeluarController.php`

**Perubahan:**
- Method `store()`:
  - Tambah validasi: `'vcTipeIzin' => 'nullable|string|max:20|in:Masuk Siang,Izin Biasa,Pulang Cepat'`
  - Tambah `vcTipeIzin` ke `Izin::create()`

- Method `update()`:
  - Tambah validasi: `'vcTipeIzin' => 'nullable|string|max:20|in:Masuk Siang,Izin Biasa,Pulang Cepat'`
  - Tambah `vcTipeIzin` ke `$record->update()`

### 4. View

**File:** `resources/views/absen/izin_keluar/index.blade.php`

**Perubahan:**

#### A. Form Modal
- Tambah field select "Tipe/Kategori Izin" setelah field "Jenis Izin"
- Field ini hidden secara default (`d-none`)
- Muncul otomatis ketika jenis izin = Z003 atau Z004 (atau keterangan mengandung "pribadi")
- Pilihan: Masuk Siang, Izin Biasa, Pulang Cepat

#### B. Tabel Data
- Tambah kolom "Tipe/Kategori" di header tabel
- Tampilkan badge info jika ada tipe, atau "-" jika kosong
- Update colspan untuk empty state (dari 9 menjadi 10)

#### C. JavaScript
- Tambah event listener untuk `vcKodeIzin` change:
  - Cek apakah jenis izin = Z003, Z004, atau keterangan mengandung "pribadi"
  - Show/hide field `vcTipeIzinGroup` berdasarkan kondisi
  - Set/remove attribute `required` pada field tipe
  - Reset value field tipe jika bukan pribadi

- Update function `editRecord()`:
  - Trigger change event pada `vcKodeIzin` untuk show/hide field
  - Set value `vcTipeIzin` setelah field muncul (dengan setTimeout)

- Update function `addBtn` click:
  - Reset field tipe (hide dan remove required)

---

## 🔧 Logic & Konsep

### Kondisi Muncul Field Tipe/Kategori

Field tipe/kategori muncul jika:
1. **Kode Izin = Z003** (Izin Keluar Pribadi)
2. **Kode Izin = Z004** (Izin Masuk Siang Pribadi)
3. **Keterangan jenis izin mengandung kata "pribadi"** (case insensitive)

### Validasi

- Field `vcTipeIzin` adalah **nullable** (tidak wajib)
- Jika diisi, harus salah satu dari: "Masuk Siang", "Izin Biasa", "Pulang Cepat"
- Maksimal panjang: 20 karakter

### Tampilan

- **Form:** Field select dengan 3 pilihan
- **Tabel:** Badge info (biru) jika ada tipe, text muted "-" jika kosong

---

## 📁 File yang Dimodifikasi

1. ✅ `database/migrations/2025_12_04_045022_add_vc_tipe_izin_to_t_izin_table.php` (BARU)
2. ✅ `app/Models/Izin.php` (MODIFIKASI)
3. ✅ `app/Http/Controllers/IzinKeluarController.php` (MODIFIKASI)
4. ✅ `resources/views/absen/izin_keluar/index.blade.php` (MODIFIKASI)

---

## 🚀 Cara Deploy ke Server

### 1. Copy File

Copy file berikut ke server:

```
app/Models/Izin.php
app/Http/Controllers/IzinKeluarController.php
resources/views/absen/izin_keluar/index.blade.php
```

**Lokasi di server:**
```
/var/www/html/hris-seven-payroll/app/Models/Izin.php
/var/www/html/hris-seven-payroll/app/Http/Controllers/IzinKeluarController.php
/var/www/html/hris-seven-payroll/resources/views/absen/izin_keluar/index.blade.php
```

### 2. Update Database (Manual SQL)

Jalankan SQL berikut di MySQL server:

```sql
-- Tambah field vcTipeIzin ke tabel t_izin
ALTER TABLE `t_izin` 
ADD COLUMN `vcTipeIzin` VARCHAR(20) NULL 
AFTER `vcKodeIzin` 
COMMENT 'Tipe/Kategori Izin: Masuk Siang, Izin Biasa, Pulang Cepat (hanya untuk jenis izin pribadi)';
```

**Verifikasi:**
```sql
-- Cek struktur tabel
DESCRIBE t_izin;

-- Harus muncul kolom vcTipeIzin setelah vcKodeIzin
```

### 3. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Models/Izin.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/app/Http/Controllers/IzinKeluarController.php
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/resources/views/absen/izin_keluar/
```

### 4. Clear Cache

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache
```

---

## ✅ Testing Checklist

### Form Tambah
- [ ] Buka form tambah izin keluar
- [ ] Pilih jenis izin **bukan pribadi** → Field tipe/kategori **tidak muncul** ✓
- [ ] Pilih jenis izin **Z003** (pribadi) → Field tipe/kategori **muncul** ✓
- [ ] Pilih jenis izin **Z004** (pribadi) → Field tipe/kategori **muncul** ✓
- [ ] Pilih tipe/kategori: Masuk Siang, Izin Biasa, Pulang Cepat
- [ ] Simpan data → Berhasil tersimpan dengan tipe/kategori ✓

### Form Edit
- [ ] Edit data izin dengan jenis pribadi → Field tipe/kategori muncul dengan value yang benar ✓
- [ ] Edit data izin dengan jenis bukan pribadi → Field tipe/kategori tidak muncul ✓
- [ ] Ubah jenis izin dari pribadi ke bukan pribadi → Field tipe/kategori hilang, value di-reset ✓
- [ ] Ubah jenis izin dari bukan pribadi ke pribadi → Field tipe/kategori muncul ✓

### Tabel Data
- [ ] Kolom "Tipe/Kategori" muncul di header tabel ✓
- [ ] Data dengan tipe/kategori menampilkan badge info ✓
- [ ] Data tanpa tipe/kategori menampilkan "-" (text muted) ✓

### Validasi
- [ ] Simpan tanpa pilih jenis izin → Error validasi ✓
- [ ] Simpan dengan jenis pribadi tanpa pilih tipe → Bisa (karena nullable) ✓
- [ ] Simpan dengan jenis pribadi dengan tipe → Berhasil ✓

---

## 🐛 Troubleshooting

### Field Tipe/Kategori Tidak Muncul

**Masalah:** Field tipe/kategori tidak muncul saat pilih jenis izin pribadi

**Solusi:**
1. Cek JavaScript console untuk error
2. Pastikan attribute `data-keterangan` ada di option select jenis izin
3. Clear browser cache
4. Clear view cache: `php artisan view:clear`

### Error: Column 'vcTipeIzin' doesn't exist

**Masalah:** Error saat simpan/edit data

**Solusi:**
1. Pastikan SQL untuk tambah kolom sudah dijalankan
2. Verifikasi dengan: `DESCRIBE t_izin;`
3. Pastikan kolom `vcTipeIzin` ada di tabel

### Data Tipe/Kategori Tidak Tersimpan

**Masalah:** Data tipe/kategori tidak tersimpan ke database

**Solusi:**
1. Cek validasi di controller (harus ada `vcTipeIzin` di validation rules)
2. Cek model (harus ada `vcTipeIzin` di `$fillable`)
3. Cek form (pastikan field `vcTipeIzin` ada di form dengan name yang benar)

---

## 📝 Catatan Penting

1. **Field Nullable:** Field `vcTipeIzin` adalah nullable, jadi tidak wajib diisi meskipun jenis izin pribadi
2. **Jenis Izin Pribadi:** Saat ini hanya Z003 dan Z004 yang dianggap sebagai jenis izin pribadi
3. **Auto Show/Hide:** Field tipe/kategori muncul/hilang otomatis berdasarkan jenis izin yang dipilih
4. **Value Reset:** Jika jenis izin diubah dari pribadi ke bukan pribadi, value tipe/kategori akan di-reset

---

**Status:** ✅ Selesai dan siap untuk deployment

**Tanggal:** 4 Desember 2025











