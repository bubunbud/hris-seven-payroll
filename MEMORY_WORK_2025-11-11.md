# Memory Work - 11 November 2025

## Ringkasan Pekerjaan Hari Ini

### 1. Perbaikan Perhitungan Cuti dan Sakit di Proses Gaji

**Masalah:**

-   Hasil proses gaji menunjukkan jumlah cuti dan sakit yang tidak sesuai dengan data di `t_tidak_masuk`
-   Contoh: Periode 1 Oktober 2025, NIK 19970001 (Tatang) menunjukkan cuti (C) = 2, padahal di `t_tidak_masuk` tidak ada pengajuan cuti

**Penyebab:**

-   Query menggunakan `sum('jumlah_hari')` yang menghitung seluruh hari dari cuti/sakit, bukan hanya hari yang overlap dengan range periode
-   Contoh: Periode 09-09-2025 sampai 23-09-2025, Cuti 01-09-2025 sampai 15-09-2025 (15 hari) → dihitung 15 hari, padahal seharusnya hanya 7 hari (09-09 sampai 15-09)

**Solusi:**

-   Membuat fungsi helper baru `calculateHariTidakMasuk()` di `ClosingController.php`
-   Fungsi ini menghitung hari yang benar-benar overlap dengan range periode
-   Query overlap: `dtTanggalMulai <= tanggalAkhir AND dtTanggalSelesai >= tanggalAwal`
-   Untuk setiap record, hitung range overlap: `max(dtTanggalMulai, tanggalAwal)` sampai `min(dtTanggalSelesai, tanggalAkhir)`
-   Hitung hari overlap (inklusif): `diffInDays + 1`

**File yang dimodifikasi:**

-   `app/Http/Controllers/ClosingController.php`
    -   Method `calculateHariTidakMasuk($nik, $kodeAbsen, $tanggalAwal, $tanggalAkhir)` - Baris 1004-1040
    -   Method `calculateEmployeePayroll()` - Menggunakan fungsi helper untuk cuti, sakit, izin
    -   Method `getDataPeriodeSebelumnya()` - Menggunakan fungsi helper yang sama

**Konsep penting:**

-   Perhitungan hari overlap harus mempertimbangkan range periode, bukan seluruh `jumlah_hari` dari record
-   Query overlap menggunakan logika standar: `dtTanggalMulai <= tanggalAkhir AND dtTanggalSelesai >= tanggalAwal`
-   Perhitungan hari overlap menggunakan `diffInDays + 1` (inklusif)

---

### 2. Perbaikan Status "OK" di Realisasi Lembur untuk Hari Libur

**Masalah:**

-   Status "OK" di halaman Realisasi Lembur hanya diberikan jika jam akhir lembur <= jam pulang
-   Untuk hari libur, perlu logika tambahan

**Ketentuan baru:**

-   Untuk hari libur, status "OK" jika:
    1. Jam awal lembur = jam masuk absensi DAN jam akhir lembur = jam pulang absensi, ATAU
    2. Range lembur masih di dalam range absensi (jam awal lembur >= jam masuk DAN jam akhir lembur <= jam pulang)

**Solusi:**

-   Menambahkan `data-hari-libur` pada setiap row di view
-   Memodifikasi fungsi `checkStatusLembur()` di JavaScript
-   Menambahkan logika khusus untuk hari libur dengan 2 kondisi

**File yang dimodifikasi:**

-   `resources/views/lembur/realisasi.blade.php`
    -   Baris 82: Menambahkan `data-hari-libur` attribute
    -   Baris 272-391: Memodifikasi fungsi `checkStatusLembur()`

**Konsep penting:**

-   Perbandingan waktu menggunakan `Date` object untuk akurasi
-   Handle overnight shift dengan menambahkan 1 hari jika keluar < masuk
-   Event listener sudah memanggil `checkStatusLembur` saat jam masuk atau keluar lembur berubah

---

### 3. Perbaikan Perhitungan Total Jam Lembur di Realisasi Lembur

**Masalah:**

-   Total jam lembur menampilkan 0.00 untuk beberapa record spesifik
-   Contoh: Tanggal 04-10-2025, NIK 20020013, vcCounter 202510047142
-   Data di database sudah benar (07:00:00 - 12:00:00 = 5 jam)
-   Masalah ada di JavaScript yang tidak membaca value dengan benar saat halaman load

**Solusi:**

-   Memperbaiki fungsi `calculateTotalJam()` untuk lebih robust
-   Menambahkan data-counter pada row untuk identifikasi yang lebih spesifik
-   Memperbaiki fungsi `initializeCalculations()` dengan retry mechanism
-   Menambahkan event listener untuk `input` event
-   Force set value ke input type="time" jika value belum ter-set

**File yang dimodifikasi:**

-   `resources/views/lembur/realisasi.blade.php`
    -   Baris 82: Menambahkan `data-counter` attribute
    -   Baris 220-282: Memperbaiki fungsi `calculateTotalJam()`
    -   Baris 409-500: Memperbaiki fungsi `initializeCalculations()` dan event listener

**Konsep penting:**

-   Input type="time" mungkin tidak langsung membaca value saat halaman load
-   Perlu retry mechanism dengan delay untuk memastikan value sudah ter-set
-   Menggunakan counter untuk identifikasi row yang lebih spesifik jika ada multiple rows dengan NIK yang sama

---

### 4. Perbaikan Spacing Cetak Slip Gaji

**Masalah:**

-   Spasi antar baris terlalu rapat
-   Layout 2x2 per halaman harus tetap terjaga

**Solusi:**

-   Meningkatkan `line-height` dari 1.1-1.2 menjadi 1.3-1.4
-   Mengubah `margin-bottom` dari `mb-0` menjadi `mb-1`
-   Meningkatkan `margin-top` dari 2px-3px menjadi 4px-5px
-   Meningkatkan padding card body dari 6px menjadi 8px
-   Meningkatkan padding pada absensi dan footer section

**File yang dimodifikasi:**

-   `resources/views/laporan/slip-gaji/preview.blade.php`
    -   Semua baris data: Meningkatkan line-height dan margin
    -   Baris 288: Meningkatkan padding card-body

**Konsep penting:**

-   Layout 2x2 per halaman tetap terjaga dengan `height: 48vh` untuk setiap slip container
-   Spacing yang lebih proporsional meningkatkan keterbacaan tanpa mengubah layout

---

### 5. Menghilangkan Judul "Preview Slip Gaji" saat Print

**Masalah:**

-   Judul "Preview Slip Gaji" muncul di halaman pertama saat print
-   Menggeser posisi 4 kotak slip

**Solusi:**

-   Menambahkan class `no-print` pada header dan alert box
-   Memperbaiki CSS print untuk menyembunyikan elemen dengan class `no-print`
-   Mengubah `body padding` dari `10px` menjadi `0`
-   Menambahkan CSS khusus untuk memastikan tidak ada ruang kosong

**File yang dimodifikasi:**

-   `resources/views/laporan/slip-gaji/preview.blade.php`
    -   Baris 9, 23: Menambahkan class `no-print`
    -   Baris 259-286: Memperbaiki CSS print

**Konsep penting:**

-   Class `no-print` digunakan untuk menyembunyikan elemen saat print
-   Padding dan margin harus diset ke 0 untuk menghindari ruang kosong

---

### 6. Perbaikan Laporan Rekap Upah Karyawan

#### 6.1 Grouping Berdasarkan Hirarki

**Masalah:**

-   Data perlu di-group per departemen dan per bagian untuk setiap bisnis unit
-   Menggunakan hirarki dari `m_hirarki_dept` dan `m_hirarki_bagian`
-   Urutan: Divisi → Departemen (judul) → Bagian (dengan sub total) → Total Departemen

**Solusi:**

-   Memodifikasi method `groupDataHierarchically()` di controller
-   Menggunakan query hirarki untuk mengurutkan data
-   Loop melalui departemen berdasarkan `m_hirarki_dept`
-   Loop melalui bagian berdasarkan `m_hirarki_bagian`
-   Fallback untuk karyawan yang tidak ada di hirarki

**File yang dimodifikasi:**

-   `app/Http/Controllers/RekapUpahKaryawanController.php`
    -   Baris 96-233: Memodifikasi method `groupDataHierarchically()`

**Konsep penting:**

-   Data diurutkan berdasarkan hirarki dari `m_hirarki_dept` dan `m_hirarki_bagian`
-   Fallback mechanism untuk karyawan yang tidak ada di hirarki

#### 6.2 Menambahkan Judul Departemen

**Masalah:**

-   Judul departemen belum ada di awal setiap departemen

**Solusi:**

-   Menambahkan row dengan judul "DEPARTEMEN: [Nama Departemen]" di awal setiap departemen
-   Background color `#e0e0e0` untuk membedakan
-   Muncul sekali di awal departemen, sebelum semua bagian

**File yang dimodifikasi:**

-   `resources/views/laporan/rekap-upah-karyawan/preview.blade.php`
    -   Baris 205-209: Menambahkan judul departemen

**Konsep penting:**

-   Judul departemen muncul sekali di awal, sebelum semua bagian
-   Total departemen muncul setelah semua bagian dalam departemen tersebut

#### 6.3 Menambahkan Kolom JM3 (Jam Lembur ke-3)

**Masalah:**

-   Kolom JM3 belum ada di laporan

**Solusi:**

-   Menambahkan kolom `<th>JM3</th>` di header
-   Menambahkan data JM3 di setiap row: `decJamLemburKerja3 + decJamLemburLibur3`
-   Menambahkan JM3 di total bagian, departemen, divisi, dan grand total
-   Menambahkan `jam_lembur_jm3` di controller `calculateTotal()`

**File yang dimodifikasi:**

-   `app/Http/Controllers/RekapUpahKaryawanController.php`
    -   Baris 246: Menambahkan `jam_lembur_jm3` di array total
    -   Baris 265: Menambahkan perhitungan `jam_lembur_jm3`
-   `resources/views/laporan/rekap-upah-karyawan/preview.blade.php`
    -   Baris 157-159, 181: Menambahkan kolom JM3 di header
    -   Baris 267, 296, 320, 354, 387: Menambahkan data JM3 di setiap row

**Konsep penting:**

-   JM3 = `decJamLemburKerja3 + decJamLemburLibur3`
-   JM3 ditambahkan di semua level (karyawan, bagian, departemen, divisi, grand total)

#### 6.4 Menyembunyikan Departemen Tanpa Data

**Masalah:**

-   Departemen yang tidak ada datanya masih ditampilkan

**Solusi:**

-   Menambahkan pengecekan `$hasDataForDept` sebelum memproses departemen
-   Menggunakan `continue` untuk skip departemen yang tidak memiliki data
-   Setelah loop bagian, cek apakah departemen memiliki bagian dengan data
-   Jika tidak ada bagian dengan data, hapus departemen dari grouped data

**File yang dimodifikasi:**

-   `app/Http/Controllers/RekapUpahKaryawanController.php`
    -   Baris 123-133: Menambahkan pengecekan data sebelum memproses departemen
    -   Baris 173-176: Menambahkan pengecekan setelah loop bagian

**Konsep penting:**

-   Hanya departemen yang memiliki minimal 1 bagian dengan data karyawan yang ditampilkan
-   Pengecekan dilakukan sebelum dan setelah loop bagian

---

## Struktur Data dan Tabel

### Tabel yang digunakan:

-   `t_closing` - Data closing gaji
-   `t_tidak_masuk` - Data tidak masuk (cuti, sakit, izin)
-   `t_absen` - Data absensi
-   `m_hirarki_dept` - Hirarki divisi-departemen
-   `m_hirarki_bagian` - Hirarki divisi-departemen-bagian
-   `m_karyawan` - Data karyawan
-   `m_dept` - Master departemen
-   `m_bagian` - Master bagian
-   `m_divisi` - Master divisi

### Field penting:

-   `t_tidak_masuk`: `dtTanggalMulai`, `dtTanggalSelesai`, `jumlah_hari` (accessor)
-   `t_absen`: `dtJamMasukLembur`, `dtJamKeluarLembur`, `intDurasiIstirahat`, `vcCounter`
-   `t_closing`: `decJamLemburKerja1`, `decJamLemburKerja2`, `decJamLemburKerja3`, `decJamLemburLibur2`, `decJamLemburLibur3`

---

## Teknik dan Konsep yang Digunakan

### 1. Perhitungan Overlap Range Tanggal

```php
// Query overlap
dtTanggalMulai <= tanggalAkhir AND dtTanggalSelesai >= tanggalAwal

// Hitung range overlap
$overlapMulai = max($mulai, $tanggalAwal);
$overlapSelesai = min($selesai, $tanggalAkhir);
$hariOverlap = $overlapMulai->diffInDays($overlapSelesai) + 1;
```

### 2. Grouping Data Hierarkis

-   Menggunakan query hirarki untuk mengurutkan data
-   Loop melalui hirarki (divisi → departemen → bagian)
-   Filter data berdasarkan hirarki
-   Fallback untuk data yang tidak ada di hirarki

### 3. JavaScript untuk Input Type="time"

-   Perlu retry mechanism dengan delay
-   Force set value jika belum ter-set
-   Menggunakan multiple sources untuk value (value, attribute, defaultValue)
-   Event listener untuk `input` event

### 4. CSS Print Optimization

-   Class `no-print` untuk menyembunyikan elemen
-   Padding dan margin diset ke 0
-   Height calculation untuk layout 2x2

---

## File yang Dimodifikasi Hari Ini

1. `app/Http/Controllers/ClosingController.php`

    - Method `calculateHariTidakMasuk()` - Baris 1004-1040
    - Method `calculateEmployeePayroll()` - Baris 268, 285-287
    - Method `getDataPeriodeSebelumnya()` - Baris 878-880
    - Method `calculateTotal()` - Baris 246, 265

2. `resources/views/lembur/realisasi.blade.php`

    - Baris 82: `data-hari-libur` dan `data-counter` attributes
    - Baris 220-282: Fungsi `calculateTotalJam()`
    - Baris 272-391: Fungsi `checkStatusLembur()`
    - Baris 409-500: Fungsi `initializeCalculations()` dan event listener

3. `resources/views/laporan/slip-gaji/preview.blade.php`

    - Baris 9, 23: Class `no-print`
    - Baris 51-58, 61-74, 76, 79-152, 155-223: Spacing improvements
    - Baris 259-286: CSS print improvements

4. `app/Http/Controllers/RekapUpahKaryawanController.php`

    - Baris 96-233: Method `groupDataHierarchically()`
    - Baris 240-257: Method `calculateTotal()` - Menambahkan `jam_lembur_jm3`

5. `resources/views/laporan/rekap-upah-karyawan/preview.blade.php`
    - Baris 157-159, 181: Kolom JM3 di header
    - Baris 205-209: Judul departemen
    - Baris 267, 296, 320, 354, 387: Data JM3 di setiap row

---

## Catatan Penting

1. **Perhitungan Hari Overlap:**

    - Selalu gunakan fungsi `calculateHariTidakMasuk()` untuk menghitung hari yang overlap dengan periode
    - Jangan langsung menggunakan `sum('jumlah_hari')` karena akan menghitung seluruh hari dari record

2. **Hirarki Data:**

    - Selalu gunakan `m_hirarki_dept` dan `m_hirarki_bagian` untuk mengurutkan data
    - Fallback mechanism untuk data yang tidak ada di hirarki

3. **JavaScript Input Type="time":**

    - Selalu gunakan retry mechanism dengan delay
    - Force set value jika belum ter-set
    - Gunakan multiple sources untuk value

4. **CSS Print:**
    - Gunakan class `no-print` untuk menyembunyikan elemen
    - Set padding dan margin ke 0 untuk menghindari ruang kosong

---

## Pending Tasks

1. Perbaikan Rekap Upah Karyawan - masih ada yang perlu di-adjust (user akan lanjutkan besok)

---

## Kesimpulan

Hari ini telah dilakukan perbaikan pada:

1. Perhitungan cuti dan sakit di proses gaji
2. Status "OK" di Realisasi Lembur untuk hari libur
3. Perhitungan total jam lembur di Realisasi Lembur
4. Spacing cetak slip gaji
5. Menghilangkan judul saat print slip gaji
6. Grouping dan struktur laporan Rekap Upah Karyawan berdasarkan hirarki
7. Menambahkan kolom JM3 di laporan Rekap Upah Karyawan
8. Menyembunyikan departemen tanpa data

Semua perubahan telah diimplementasikan dan siap untuk testing lebih lanjut.








