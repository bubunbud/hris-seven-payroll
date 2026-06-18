# 📚 Memory Work Summary - 10 Desember 2025

## 🎯 Ringkasan Pekerjaan Hari Ini

### 1. Fitur Baru: Rekapitulasi Cuti

**Controller:** `app/Http/Controllers/RekapitulasiCutiController.php`
- Method `index()`: Menampilkan halaman dengan filter dan tabel data
- Method `exportExcel()`: Export data ke Excel (format TSV/.xls)
- Method `calculateRekapitulasiCutiKaryawan()`: Menghitung rekapitulasi per karyawan
- Helper `putCsvLine()`: Helper untuk menulis baris CSV dengan tab separator

**View:** `resources/views/cuti/rekapitulasi/index.blade.php`
- Form filter: Dari Tanggal, Sampai Tanggal, Divisi, Departemen, Group Pegawai
- Tabel data: No, NIK, Nama, Bisnis Unit/Divisi, Departemen, Bagian, Cuti Pribadi, Cuti Bersama, Saldo Cuti
- Tombol "Export Excel"
- Keterangan/legenda di bawah tabel

**Route:** `routes/web.php`
- `GET /cuti/rekapitulasi` → `rekapitulasi-cuti.index`
- `GET /cuti/rekapitulasi/export` → `rekapitulasi-cuti.export`
- Ditambahkan di group permission `view-absensi`

**Menu:** `resources/views/layouts/app.blade.php`
- Menu "Rekapitulasi Cuti" ditambahkan di group Absensi (setelah "Rekapitulasi Absen All")

---

## 📊 Logika Perhitungan Rekapitulasi Cuti

### 1. Cuti Pribadi (C010)
- **Sumber Data:** `t_tidak_masuk` dengan `vcKodeAbsen = 'C010'`
- **Filter:** Overlap dengan range tanggal yang dipilih
- **Perhitungan:** 
  - Ambil semua record cuti pribadi yang overlap dengan range tanggal
  - Hitung hari yang overlap: `overlapMulai` sampai `overlapSelesai`
  - Formula: `diffInDays(overlapSelesai, overlapMulai) + 1`
  - Jumlahkan semua hari overlap dari semua record

### 2. Cuti Bersama
- **Sumber Data:** `m_hari_libur` dengan `vcTipeHariLibur = 'Cuti Bersama'`
- **Filter:** `whereBetween('dtTanggal', [$startDate, $endDate])`
- **Perhitungan:** Count jumlah hari libur dengan tipe "Cuti Bersama" dalam range tanggal
- **Catatan:** Sama untuk semua karyawan dalam periode yang sama

### 3. Saldo Cuti
- **Sumber Data:** `m_saldo_cuti`
- **Filter:** Tahun dari range tanggal (gunakan tahun akhir jika range lintas tahun)
- **Perhitungan:**
  1. Ambil saldo dari `m_saldo_cuti` untuk tahun tersebut
  2. Hitung penggunaan cuti (C010 + C012) untuk tahun tersebut (overlap dengan tahun)
  3. Tambahkan cuti bersama untuk tahun tersebut
  4. Hitung saldo sisa dengan prioritas:
     - Kurangi `decTahunLalu` terlebih dahulu
     - Baru kurangi `decTahunIni`
  5. Formula: `saldoSisa = (tahunLalu - tahunLaluTerpakai) + (tahunIni - tahunIniTerpakai)`

---

## 🗄️ Struktur Tabel m_saldo_cuti

**Nama Tabel:** `m_saldo_cuti`

**Primary Key:** Composite Key `(vcNik, intTahun)`

**Kolom-kolom:**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `vcNik` | VARCHAR(8) | NIK karyawan (FK ke `m_karyawan.Nik`) |
| `intTahun` | INTEGER | Tahun cuti (misal: 2024, 2025) |
| `decTahunLalu` | DECIMAL(5,0) | Saldo cuti tahun lalu (jumlah hari) |
| `decTahunIni` | DECIMAL(5,0) | Saldo cuti tahun ini (jumlah hari) |
| `decSaldoDigunakan` | DECIMAL(5,0) | Total saldo yang sudah digunakan (auto calculate) |
| `decSaldoSisa` | DECIMAL(5,0) | Sisa saldo (auto calculate) |
| `vcKeterangan` | TEXT | Catatan/keterangan |
| `dtCreate` | DATETIME | Tanggal dibuat |
| `dtChange` | DATETIME | Tanggal diubah |

**Catatan Penting:**
- Menggunakan `DB::table()` untuk update/insert karena composite key
- Eloquent tidak mendukung composite key secara native
- Satu karyawan bisa punya beberapa record (satu per tahun)
- Relasi: `vcNik` → `m_karyawan.Nik`

**Contoh Query Update:**
```php
DB::table('m_saldo_cuti')
    ->where('vcNik', $vcNik)
    ->where('intTahun', $intTahun)
    ->update($data);
```

---

## 📁 File yang Dibuat/Diupdate

### File Baru:
1. `app/Http/Controllers/RekapitulasiCutiController.php`
2. `resources/views/cuti/rekapitulasi/index.blade.php`
3. `DEPLOY_SALDO_CUTI_REKAPITULASI_CUTI.md`
4. `DEPLOY_SALDO_CUTI_REKAPITULASI_CUTI_CHECKLIST.txt`
5. `deploy-saldo-cuti-rekapitulasi-cuti.sh`
6. `MEMORY_WORK_2025_12_10.md` (file ini)

### File Update:
1. `routes/web.php` - Tambah route rekapitulasi cuti
2. `resources/views/layouts/app.blade.php` - Tambah menu Rekapitulasi Cuti
3. `app/Http/Controllers/SaldoCutiController.php` - Perbaikan error (jika ada)

---

## 🔧 Teknik & Konsep yang Digunakan

### 1. Pola Rekapitulasi (Mengikuti Rekapitulasi Absen All)
- Filter karyawan aktif dengan divisi/departemen/group
- Hitung per karyawan dengan method terpisah
- Return array data untuk ditampilkan di view
- Pagination manual jika diperlukan

### 2. Export Excel
- Format: TSV (Tab Separated Values) untuk kompatibilitas Excel
- Menggunakan `response()->stream()` untuk download file
- Helper `putCsvLine()` untuk escape tab/newline/quote
- BOM UTF-8 untuk encoding yang benar di Excel

### 3. Perhitungan Overlap Tanggal
- Gunakan Carbon untuk manipulasi tanggal
- Formula overlap: `max(mulai, tanggalAwal)` sampai `min(selesai, tanggalAkhir)`
- Hitung hari: `diffInDays(overlapSelesai, overlapMulai) + 1`

### 4. Composite Primary Key
- Gunakan `DB::table()` untuk operasi update/insert
- Query dengan `where()` untuk kedua kolom primary key
- Eloquent tidak support composite key secara native

---

## 📋 Dokumentasi Deployment

### File Dokumentasi:
1. **DEPLOY_SALDO_CUTI_REKAPITULASI_CUTI.md**
   - Panduan deployment lengkap
   - Langkah-langkah detail
   - Troubleshooting
   - Catatan penting

2. **DEPLOY_SALDO_CUTI_REKAPITULASI_CUTI_CHECKLIST.txt**
   - Checklist deployment
   - Dapat dicetak dan ditandai

3. **deploy-saldo-cuti-rekapitulasi-cuti.sh**
   - Script deployment untuk server Ubuntu
   - Otomatisasi backup, permission, clear cache

### File yang Perlu Di-Copy ke Server:
1. `app/Http/Controllers/SaldoCutiController.php` (update)
2. `app/Http/Controllers/RekapitulasiCutiController.php` (baru)
3. `app/Models/SaldoCuti.php` (jika ada perubahan)
4. `resources/views/cuti/saldo/index.blade.php` (jika ada perubahan)
5. `resources/views/cuti/rekapitulasi/index.blade.php` (baru)
6. `routes/web.php` (update)
7. `resources/views/layouts/app.blade.php` (update)

### Langkah Deployment:
1. Backup database dan .env
2. Copy file ke server (SCP atau FileZilla)
3. Set permission (www-data:www-data, 755)
4. Clear cache Laravel (config/route/view/cache)
5. Verifikasi route
6. Test aplikasi

---

## 🔍 Formula & Struktur Data

### Formula Perhitungan Saldo Cuti:
```
Total Penggunaan = Penggunaan Individu (C010 + C012) + Cuti Bersama Tahun

Prioritas Pengurangan:
1. Kurangi decTahunLalu terlebih dahulu
2. Baru kurangi decTahunIni

Saldo Sisa = (decTahunLalu - tahunLaluTerpakai) + (decTahunIni - tahunIniTerpakai)
```

### Struktur Data Return Rekapitulasi:
```php
[
    'no' => 1,
    'nik' => '00012345',
    'nama' => 'Nama Karyawan',
    'divisi' => 'Nama Divisi',
    'departemen' => 'Nama Departemen',
    'bagian' => 'Nama Bagian',
    'cuti_pribadi' => 5,  // jumlah hari
    'cuti_bersama' => 2,  // jumlah hari
    'saldo_cuti' => 10    // sisa saldo
]
```

---

## 🎯 Fitur yang Sudah Berhasil

✅ **Rekapitulasi Cuti**
- Filter: tanggal, divisi, departemen, group pegawai
- Tabel data lengkap dengan semua kolom yang diminta
- Export Excel berfungsi
- Menu sudah ditambahkan di sidebar

✅ **Perbaikan Saldo Cuti**
- Error di server Ubuntu sudah diperbaiki
- Tabel m_saldo_cuti sudah dipahami strukturnya

✅ **Dokumentasi Deployment**
- Panduan lengkap sudah dibuat
- Checklist sudah dibuat
- Script deployment sudah dibuat

---

## 📝 Catatan untuk Sesi Berikutnya

1. **Rekapitulasi Cuti** sudah selesai dan berhasil di-deploy
2. **Tabel m_saldo_cuti** menggunakan composite primary key, update harus pakai `DB::table()`
3. **Logika perhitungan** cuti sudah jelas: overlap tanggal, prioritas pengurangan saldo
4. **Pola development** mengikuti Rekapitulasi Absen All untuk konsistensi
5. **Export Excel** menggunakan TSV format untuk kompatibilitas

---

**Status:** ✅ Semua fitur sudah selesai dan berhasil di-deploy ke server Ubuntu

**Tanggal:** 10 Desember 2025

**Siap untuk pengembangan berikutnya!** 🚀



