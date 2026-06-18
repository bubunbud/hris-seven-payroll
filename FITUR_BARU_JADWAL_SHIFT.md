# 📋 Dokumentasi Fitur Baru: Jadwal Shift Satpam

## 🎯 Ringkasan
Dokumentasi ini menjelaskan 3 fitur baru yang ditambahkan ke sistem jadwal shift satpam:
1. **Excel/CSV Import** - Import jadwal dari file Excel/CSV
2. **Copy Jadwal Bulan Sebelumnya** - Copy jadwal dari bulan sebelumnya
3. **Report Jadwal Shift** - Laporan jadwal shift per periode dengan export

---

## 1. 📥 Excel/CSV Import

### Deskripsi
Fitur untuk mengimport jadwal shift dari file CSV/Excel secara bulk, memudahkan input jadwal dalam jumlah besar.

### Cara Menggunakan

1. **Buka halaman Jadwal Shift Satpam**
   - Menu: Absensi → Jadwal Shift Satpam
   - Pilih bulan dan tahun yang akan di-import

2. **Klik tombol "Import Excel/CSV"**
   - Tombol hijau di bagian kanan atas

3. **Pilih file CSV**
   - Format yang didukung: CSV, TXT
   - Maksimal ukuran: 10MB
   - Format Excel (XLSX/XLS) belum didukung (konversi ke CSV dulu)

4. **Format File CSV**
   ```
   NIK,Tanggal,Shift,Keterangan
   19950011,2025-12-01,1,
   19950011,2025-12-02,2,
   19950011,2025-12-03,3,
   19950011,2025-12-04,OFF,Libur
   19970010,2025-12-01,1,2,Penggantian
   ```

5. **Aturan Format:**
   - **Kolom 1 (NIK):** NIK satpam (harus ada di database, Group_pegawai = Security)
   - **Kolom 2 (Tanggal):** Format `Y-m-d` (2025-12-01) atau `d/m/Y` (01/12/2025) atau `d-m-Y` (01-12-2025)
   - **Kolom 3 (Shift):** `1`, `2`, `3`, atau `OFF` (bisa multiple: `1,2` atau `1, 2`)
   - **Kolom 4 (Keterangan):** Optional, bisa dikosongkan
   - **Baris pertama:** Header (akan di-skip)

6. **Klik "Import"**
   - Sistem akan memvalidasi setiap baris
   - Menampilkan jumlah berhasil dan gagal
   - Error detail ditampilkan jika ada

### Validasi
- NIK harus ada di database dan Group_pegawai = Security
- Tanggal harus dalam format yang valid
- Tanggal harus dalam periode yang dipilih
- Shift harus 1, 2, 3, atau OFF
- Duplikasi (NIK + Tanggal + Shift sama) akan diganti dengan data baru

### Template File
Template CSV tersedia di: `/public/template_jadwal_shift_security.csv`

---

## 2. 📋 Copy Jadwal Bulan Sebelumnya

### Deskripsi
Fitur untuk menyalin jadwal dari bulan sebelumnya ke bulan yang sedang dipilih, memudahkan pembuatan jadwal bulanan yang serupa.

### Cara Menggunakan

1. **Buka halaman Jadwal Shift Satpam**
   - Menu: Absensi → Jadwal Shift Satpam
   - Pilih bulan dan tahun yang akan diisi jadwal

2. **Klik tombol "Copy Bulan Sebelumnya"**
   - Tombol biru di bagian kanan atas

3. **Konfirmasi**
   - Sistem akan menanyakan konfirmasi
   - **Peringatan:** Jadwal bulan ini akan diganti dengan jadwal bulan sebelumnya

4. **Proses Copy**
   - Sistem akan mengambil jadwal dari bulan sebelumnya
   - Menyalin ke bulan yang dipilih dengan tanggal yang sesuai
   - Tanggal yang tidak valid (misal: 31 Februari) akan di-skip
   - Flag override akan di-reset (tidak di-copy)

### Catatan Penting
- **Jadwal bulan ini akan diganti** dengan jadwal bulan sebelumnya
- Tanggal yang tidak valid (misal: 31 Februari) akan di-skip
- Flag override tidak di-copy (reset ke false)
- Jika bulan sebelumnya tidak ada jadwal, akan muncul pesan error

### Contoh
- Bulan dipilih: **Desember 2025**
- Bulan sebelumnya: **November 2025**
- Jadwal 1 November → 1 Desember
- Jadwal 15 November → 15 Desember
- Jadwal 31 November → 31 Desember (jika valid)

---

## 3. 📊 Report Jadwal Shift

### Deskripsi
Halaman laporan untuk melihat jadwal shift dalam periode tertentu dengan summary dan export ke CSV.

### Cara Menggunakan

1. **Buka halaman Report**
   - Menu: Absensi → Report Jadwal Shift
   - Atau klik tombol "Report" di halaman Jadwal Shift Satpam

2. **Filter Periode**
   - Pilih Bulan Awal dan Tahun Awal
   - Pilih Bulan Akhir dan Tahun Akhir
   - Filter NIK/Nama (optional)
   - Klik "Filter"

3. **Lihat Data**
   - Tabel menampilkan semua jadwal dalam periode
   - Kolom: No, NIK, Nama, Tanggal, Shift, Keterangan, Override
   - Summary per shift ditampilkan di bawah

4. **Export ke CSV**
   - Klik tombol "Export CSV"
   - File akan didownload dengan format CSV
   - Nama file: `report_jadwal_shift_YYYY-MM-DD_YYYY-MM-DD.csv`

### Fitur Report

#### Tabel Data
- Menampilkan semua jadwal dalam periode
- Diurutkan berdasarkan tanggal dan NIK
- Badge warna untuk shift (1=primary, 2=success, 3=info, OFF=secondary)
- Badge untuk override (Ya=warning, Tidak=success)

#### Summary per Shift
- **Shift 1:** Jumlah jadwal shift 1
- **Shift 2:** Jumlah jadwal shift 2
- **Shift 3:** Jumlah jadwal shift 3
- **OFF:** Jumlah jadwal OFF
- **Override:** Jumlah jadwal yang di-override
- **Total:** Total semua jadwal

#### Export CSV
Format export:
```
NIK,Nama,Tanggal,Shift,Keterangan,Override
19950011,John Doe,2025-12-01,Shift 1,,Tidak
19950011,John Doe,2025-12-02,Shift 2,,Ya
```

---

## 🔧 Technical Details

### Routes
```php
// Import Excel/CSV
POST /jadwal-shift-security/import
Route: jadwal-shift-security.import

// Copy bulan sebelumnya
POST /jadwal-shift-security/copy-previous-month
Route: jadwal-shift-security.copy-previous-month

// Report
GET /jadwal-shift-security/report
Route: jadwal-shift-security.report
```

### Controller Methods
- `importExcel(Request $request)` - Handle import CSV/Excel
- `copyFromPreviousMonth(Request $request)` - Copy jadwal bulan sebelumnya
- `report(Request $request)` - Tampilkan report
- `exportReport($jadwalGrouped, $tanggalAwal, $tanggalAkhir, $format)` - Export ke CSV

### Views
- `resources/views/jadwal-shift-security/index.blade.php` - Updated dengan tombol import & copy
- `resources/views/jadwal-shift-security/report.blade.php` - Halaman report baru

### Files
- `public/template_jadwal_shift_security.csv` - Template CSV untuk import

---

## 📝 Catatan Penting

1. **Import Excel:**
   - Format Excel (XLSX/XLS) belum didukung, gunakan CSV
   - File akan divalidasi per baris
   - Duplikasi akan diganti dengan data baru

2. **Copy Bulan Sebelumnya:**
   - Jadwal bulan ini akan **diganti** (bukan ditambahkan)
   - Flag override tidak di-copy
   - Tanggal tidak valid akan di-skip

3. **Report:**
   - Periode bisa lebih dari 1 bulan
   - Export CSV menggunakan UTF-8 dengan BOM untuk Excel
   - Summary dihitung otomatis

---

## 🐛 Troubleshooting

### Import Error: "NIK tidak ditemukan"
- Pastikan NIK ada di database
- Pastikan Group_pegawai = Security
- Pastikan vcAktif = '1'

### Import Error: "Format tanggal tidak valid"
- Gunakan format: `Y-m-d` (2025-12-01)
- Atau: `d/m/Y` (01/12/2025)
- Atau: `d-m-Y` (01-12-2025)

### Copy Error: "Tidak ada jadwal di bulan sebelumnya"
- Pastikan bulan sebelumnya sudah ada jadwal
- Cek dengan filter di halaman jadwal

### Report tidak muncul data
- Pastikan periode sudah benar
- Pastikan ada jadwal dalam periode tersebut
- Cek filter NIK/Nama

---

## ✅ Testing Checklist

### Import Excel/CSV
- [ ] Upload file CSV berhasil
- [ ] Validasi NIK berfungsi
- [ ] Validasi tanggal berfungsi
- [ ] Validasi shift berfungsi
- [ ] Error message jelas
- [ ] Data ter-import dengan benar

### Copy Bulan Sebelumnya
- [ ] Copy berhasil jika ada jadwal bulan sebelumnya
- [ ] Error message jika tidak ada jadwal
- [ ] Tanggal tidak valid di-skip
- [ ] Flag override di-reset

### Report Jadwal Shift
- [ ] Filter periode berfungsi
- [ ] Filter NIK/Nama berfungsi
- [ ] Tabel data tampil dengan benar
- [ ] Summary per shift akurat
- [ ] Export CSV berhasil
- [ ] File CSV bisa dibuka di Excel

---

**Status:** ✅ Semua fitur sudah diimplementasikan dan siap digunakan

**Tanggal:** 2 Desember 2025













