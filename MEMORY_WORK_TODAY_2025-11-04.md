# Memory Pekerjaan Hari Ini - 4 November 2025

## Overview
Pekerjaan hari ini fokus pada:
1. **Perhitungan Payroll (Closing Gaji)** - Implementasi lengkap sistem perhitungan gaji untuk operator
2. **View Gaji** - Halaman untuk melihat hasil perhitungan gaji
3. **Cetak Slip Gaji** - Form dan cetakan slip gaji dengan layout 4 slip per halaman
4. **Master Karyawan** - Perbaikan form edit dan dropdown fields
5. **Dashboard & Sidebar** - Perubahan default expand dan card dashboard

---

## 1. PERHITUNGAN PAYROLL (CLOSING GAJI)

### 1.1 Struktur Database - Tabel `t_closing`

**Migration:** `database/migrations/2025_11_04_034400_create_t_closing_table.php`

**Primary Key:** Composite key
- `vcPeriodeAwal` (date)
- `vcPeriodeAkhir` (date)
- `vcNik` (string, 8)
- `periode` (date) - Tanggal gajian (1 atau 15)
- `vcClosingKe` (string, 1) - Periode closing (1 atau 2)

**Field-field Penting:**

**Gaji Pokok:**
- `decGapok` - Gaji pokok setengah bulan = (Upah + Tunj.Keluarga + Tunj.Masa Kerja + Tunj.Jabatan1 + Tunj.Jabatan2) / 2
- `jumlahHari` - Jumlah hari kerja (exclude Sabtu, Minggu, hari libur)
- `vcKodeGolongan`, `vcKodeDivisi`, `vcStatusPegawai`

**Potongan:**
- `decPotonganHC` - Potongan ijin keluar komplek (per jam)
- `decPotonganBPR` - Potongan DPLK/Asuransi
- `decIuranSPN` - Potongan Iuran SPN (hanya periode 2)
- `decPotonganBPJSKes` - BPJS Kesehatan (hanya periode 1)
- `decPotonganBPJSJHT` - BPJS JHT (hanya periode 1)
- `decPotonganBPJSJP` - BPJS JP (hanya periode 1)
- `decPotonganKoperasi` - Potongan Koperasi
- `decPotonganAbsen` - Potongan ijin pribadi (tidak masuk)
- `decPotonganLain` - Potongan lain-lain

**Tunjangan:**
- `decVarMakan` - Tarif uang makan per kali
- `decVarTransport` - Tarif transport per kali
- `decUangMakan` - Total uang makan
- `decTransport` - Total uang transport
- `intMakan` - Jumlah kali makan
- `intTransport` - Jumlah kali transport

**Lembur:**
- `decJamLemburKerja1` - Jam lembur hari kerja ke-1
- `decJamLemburKerja2` - Jam lembur hari kerja ke-2
- `decJamLemburKerja3` - Jam lembur hari kerja ke-3
- `decLemburKerja1` - Nominal lembur jam ke-1 hari kerja
- `decLemburKerja2` - Nominal lembur jam ke-2 hari kerja
- `decLemburKerja3` - Nominal lembur jam ke-3 hari kerja
- `decJamLemburLibur2` - Jam lembur libur ke-2
- `decJamLemburLibur3` - Jam lembur libur ke-3
- `decLembur2` - Total nominal lembur jam ke-2
- `decLembur3` - Total nominal lembur jam ke-3
- `decJamLemburKerja` - Grand total jam lembur hari kerja
- `decJamLemburLibur` - Grand total jam lembur hari libur
- `decTotallembur1` - Total nominal lembur jam 1
- `decTotallembur2` - Total nominal lembur jam 2
- `decTotallembur3` - Total nominal lembur jam 3

**Absensi:**
- `intHadir` - Jumlah kehadiran
- `intTidakMasuk` - Jumlah hari tidak masuk pribadi
- `intJumlahHari` - Jumlah hari kerja
- `intJmlSakit` - Jumlah sakit
- `intJmlAlpha` - Jumlah alpha
- `intJmlIzin` - Jumlah Izin Pribadi
- `intJmlIzinR` - Jumlah Izin Resmi
- `intJmlCuti` - Jumlah Cuti
- `intJmlTelat` - Jumlah telat
- `intHC` - Jumlah ijin keluar komplek untuk keperluan pribadi
- `intKHL` - Jumlah kerja hari libur

**Premi Hadir:**
- `decPremi` - Premi hadir (hanya periode 2)
  - Total Ijin + Telat + HC (P1 + P2) = 0: Rp 12.000
  - Total Ijin + Telat + HC (P1 + P2) = 1: Rp 6.000
  - Total Ijin + Telat + HC (P1 + P2) > 1: Rp 0

**Rapel:**
- `decRapel` - Selisih upah/rapel dari t_hutang_piutang

**Data Periode Sebelumnya (untuk premi hadir):**
- `intCutiLalu`, `intSakitLalu`, `intHcLalu`, `intIzinLalu`, `intAlphaLalu`, `intTelatLalu`

**Beban Lembur:**
- `decBebanTgi`, `decBebanSiaExp`, `decBebanSiaProd`, `decBebanRma`, `decBebanSmu`, `decBebanAbnJkt`

**Migration Tambahan:**
- `database/migrations/2025_11_04_093044_add_dec_potongan_absen_to_t_closing_table.php` - Menambahkan field `decPotonganAbsen`

### 1.2 Controller - ClosingController

**File:** `app/Http/Controllers/ClosingController.php`

**Method Utama:**

1. **`index()`** - Menampilkan form closing gaji dengan daftar periode yang belum diproses
2. **`store()`** - Proses closing gaji untuk periode yang dipilih
3. **`calculatePayroll()`** - Orchestrator untuk perhitungan gaji
4. **`calculateEmployeePayroll()`** - Core logic perhitungan gaji per karyawan
5. **`viewGaji()`** - Menampilkan hasil perhitungan gaji dengan filter

**Method Helper:**

1. **`calculateLembur()`** - Perhitungan lembur dengan logika:
   - **Hari Kerja Normal (HKN):**
     - Jam ke-1: maksimal 1 jam per hari × 1.5 × (Gaji Pokok / 173)
     - Jam ke-2: sisa jam setelah jam ke-1 × 2 × (Gaji Pokok / 173)
   - **Hari Libur:**
     - Jam ke-2: 8 jam pertama × 2 × (Gaji Pokok / 173)
     - Jam ke-3: jam ke-9 × 3 × (Gaji Pokok / 173)
     - Jam ke-4: jam ke-10-12 × 4 × (Gaji Pokok / 173)
   - **Akumulasi:** Total jam ke-1, ke-2, ke-3 dihitung per hari kerja

2. **`calculateTunjanganMakanTransport()`** - Perhitungan tunjangan:
   - Makan dan transport = jumlah hari hadir
   - Jika lembur >= 3 jam di hari biasa, tambah 1x makan

3. **`calculateAlpha()`** - Identifikasi hari kerja tanpa absensi dan tanpa ijin
4. **`calculateTelat()`** - Hitung telat jika jam masuk > jam shift masuk (lebih dari 1 menit)
5. **`getRapel()`** - Ambil rapel dari t_hutang_piutang dengan jenis 'RAPEL' atau flag Credit
6. **`calculatePotonganHutangPiutang()`** - Mapping potongan:
   - `vcJenis='0'` → Koperasi
   - `vcJenis='1'` → DPLK
   - `vcJenis='2'` → SPN
   - Filter: `vcFlag='Debit'` atau `vcFlag='1'`

7. **`getHariLiburList()`** - Ambil hari libur (weekend + hari libur nasional)
8. **`calculateWorkingDays()`** - Hitung hari kerja (exclude weekend dan hari libur)

**Logika Perhitungan Gaji Pokok:**
```php
$gapokPerBulan = $gapok->upah + $gapok->tunj_keluarga + $gapok->tunj_masa_kerja 
                 + $gapok->tunj_jabatan1 + $gapok->tunj_jabatan2;
$decGapok = $gapokPerBulan / 2; // Setengah bulan
```

**Logika Potongan Tidak Masuk:**
```php
$decPotonganTidakMasuk = $intTidakMasuk * ($gapokPerBulan / 21);
// Disimpan di decPotonganAbsen
```

**Logika Potongan HC (Ijin Keluar Komplek):**
```php
$decPotonganHC = $totalJamIzinKeluar * ($gapokPerBulan / (21 * 8));
```

### 1.3 Model - Closing

**File:** `app/Models/Closing.php`

**Relationships:**
- `karyawan()` - belongsTo Karyawan
- `gapok()` - belongsTo Gapok
- `divisi()` - belongsTo Divisi

**Fillable:** Semua field dari migration termasuk `decPotonganAbsen`

### 1.4 Routes

**File:** `routes/web.php`

```php
Route::get('closing', [ClosingController::class, 'index'])->name('closing.index');
Route::post('closing', [ClosingController::class, 'store'])->name('closing.store');
Route::get('closing/{periodeAwal}/{periodeAkhir}/{nik}/{periode}/{closingKe}', [ClosingController::class, 'show'])->name('closing.show');
Route::get('view-gaji', [ClosingController::class, 'viewGaji'])->name('view-gaji.index');
```

---

## 2. VIEW GAJI

### 2.1 Controller Method - `viewGaji()`

**Fitur:**
- Filter: Periode (date range), Divisi, NIK/Nama (multi-search)
- Menampilkan data dari `t_closing` yang sudah diproses
- Mengambil data periode sebelumnya untuk display absensi periode 1 dan 2

**Query:**
```php
$query = Closing::with(['karyawan', 'divisi', 'gapok'])
    ->whereBetween('periode', [$tanggalAwal, $tanggalAkhir]);
    
// Ambil periode sebelumnya untuk setiap record
if ($record->vcClosingKe == '2') {
    $periodeSebelumnya = Closing::where('vcNik', $record->vcNik)
        ->where('periode', $record->periode)
        ->where('vcClosingKe', '1')
        ->first();
}
```

### 2.2 View - `resources/views/proses/view-gaji/index.blade.php`

**Layout:**
- Tabel dengan kolom: Periode Gajian, Periode Awal, Periode Akhir, Closing, NIK, Nama, Divisi, Hari Kerja, Absensi, Lembur, Penerimaan, Potongan, Gaji Bersih, Aksi

**Kolom "Hari Kerja":**
- Menampilkan jumlah hari kerja
- Info hadir
- Info KHL (jika ada)

**Kolom "Lembur":**
- Total jam lembur
- Breakdown: K (kerja) | L (libur)
- Breakdown jam: J1, J2, J3
- Total nominal lembur

**Kolom "Penerimaan":**
- Gapok, Makan, Transport, Premi, Rapel
- Breakdown lembur: Lembur J1, J2, J3 (dengan indentasi)
- Lembur Total di bawah

**Kolom "Potongan":**
- BPJS Kes, JHT, JP, SPN, Koperasi, DPLK, Ijin Keluar, Absen, Lain-lain
- Label "Absen" (bukan "Potongan Absen")

**Kolom "Absensi":**
- Dibagi 2 kolom: Periode 1 (kiri) dan Periode 2 (kanan)
- Menampilkan: S, I, A, T, C, HC

**Styling:**
- Font size: 0.75rem untuk beberapa kolom
- Column widths sudah dioptimalkan

---

## 3. CETAK SLIP GAJI

### 3.1 Filter - `resources/views/laporan/slip-gaji/index.blade.php`

**Perubahan:**
- Hanya input "Periode Gajian" (tanggal 1 atau 15)
- Tanggal awal dan akhir otomatis diambil dari `t_closing`

### 3.2 Controller - SlipGajiController

**Method `preview()`:**
- Query berdasarkan `periode` (tanggal gajian)
- Ambil data periode sebelumnya untuk absensi periode 1 dan 2
- Return `$closingsWithPrevious` array

### 3.3 View Preview - `resources/views/laporan/slip-gaji/preview.blade.php`

**Layout:**
- 4 slip per halaman (2 kolom × 2 baris)
- Font size: 0.7rem (body), lebih kecil untuk header
- Compact spacing dengan `line-height: 1.1`

**Header:**
- Nama Divisi (bukan "PT. RENALTECH MITRA ABADI")
- Tanggal periode gajian
- Tanggal range periode (dd/mm/yyyy - dd/mm/yyyy) dengan font kecil

**Konten:**
- Group "PENERIMAAN" dengan breakdown
- Group "POTONGAN" dengan breakdown
- Label item tidak bold (hanya judul group yang bold)
- Absensi di paling bawah (setelah Total Gaji)
- Penerima di paling bawah (setelah Absensi)

**Print CSS:**
- `height: 48vh` untuk container
- `page-break-after: always` setiap 4 slip
- Padding dan margin dikurangi untuk compact

---

## 4. BROWSE ABSENSI

### 4.1 Controller - AbsenController

**Optimasi:**
- Menggunakan `DB::table()` dengan JOIN langsung
- Pre-check `t_absen` sebelum expand `t_tidak_masuk`
- Manual pagination menggunakan `LengthAwarePaginator`

**Status Logic:**
- **HKN** - Hari Kerja Normal (ada jam masuk dan keluar, >= 8 jam)
- **KHL** - Kerja Hari Libur (weekend/holiday dengan attendance)
- **ATL** - Absen Tidak Lengkap (hanya ada satu dari jam masuk/keluar)
- **HC** - Hari Kerja Kurang (ada jam masuk dan keluar tapi < 8 jam)
- **Tidak Masuk** - Tidak ada jam masuk dan keluar

**Query:**
```php
// t_absen dengan JOIN
$absenQuery = DB::table('t_absen')
    ->join('m_karyawan', 't_absen.vcNik', '=', 'm_karyawan.Nik')
    ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
    ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian');

// t_tidak_masuk dengan JOIN
$tidakMasukQuery = DB::table('t_tidak_masuk')
    ->join('m_karyawan', 't_tidak_masuk.vcNik', '=', 'm_karyawan.Nik')
    ->leftJoin('m_jenis_absen', 't_tidak_masuk.vcKodeAbsen', '=', 'm_jenis_absen.vcKodeAbsen')
    ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
    ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian');
```

---

## 5. MASTER KARYAWAN

### 5.1 Perbaikan Error 405

**Masalah:** PUT method tidak didukung langsung
**Solusi:** Method spoofing dengan `formData.append('_method', 'PUT')` dan menggunakan POST

**File:** `resources/views/master/karyawan/index.blade.php`

```javascript
if (isEditMode) {
    formData.append('_method', 'PUT');
}
fetch(url, {
    method: 'POST',
    // ...
});
```

### 5.2 Dropdown Fields

**Controller:** `app/Http/Controllers/KaryawanController.php`

**Menambahkan:**
- `$golongans` - dari `Golongan::orderBy('vcKodeGolongan')->get()`
- `$jabatans` - dari `Jabatan::orderBy('vcKodeJabatan')->get()`
- `$groupPegawais` - distinct values dari `m_karyawan.Group_pegawai`

**View Changes:**
- **Golongan:** Input text → Dropdown dari `m_golongan`
- **Jabatan:** Input text → Dropdown dari `m_jabatan` (tampilkan nama)
- **Group Pegawai:** Input text → Dropdown dari distinct values database
- **Gelar:** Select dropdown → Input text (free text)
- **KTP:** Field `Job_ID` → `intNoBadge`

**PopulateForm Function:**
- Menambahkan handling untuk SELECT element:
```javascript
else if (element.tagName === 'SELECT') {
    element.value = karyawan[key] || '';
}
```

---

## 6. DASHBOARD & SIDEBAR

### 6.1 Sidebar - Default Expand

**File:** `resources/views/layouts/app.blade.php`

**Perubahan:**
- Master Data: `aria-expanded="false"`, `class="collapse"`
- Absensi: `aria-expanded="true"`, `class="collapse show"`

### 6.2 Dashboard Cards

**File:** `resources/views/dashboard.blade.php`

**Card Baru:**
1. Browse Absensi (Primary) - route `absen.index`
2. Input Tidak Masuk (Success) - route `tidak-masuk.index`
3. Izin Keluar Komplek (Warning) - route `izin-keluar.index`
4. Saldo Cuti (Info) - route `saldo-cuti.index`
5. Statistik Absensi (Danger) - route `absensi.statistik.index`

**Styling:**
- Semua card menggunakan `h-100` untuk tinggi seragam
- `d-flex flex-column` pada card-body
- `mb-auto` pada konten utama

---

## 7. KONSEP & LOGIKA PENTING

### 7.1 Periode Gajian
- **Periode 1:** Tanggal 1 bulan (contoh: 1 Oktober 2025)
- **Periode 2:** Tanggal 15 bulan (contoh: 15 Oktober 2025)
- Setiap periode memiliki `vcClosingKe` (1 atau 2)

### 7.2 Perhitungan Lembur
- **Jam ke-1:** Hanya ada di hari kerja normal, maksimal 1 jam per hari
- **Jam ke-2:** Sisa jam setelah jam ke-1 (hari kerja) + 8 jam pertama (hari libur)
- **Jam ke-3:** Hanya ada di hari libur (jam ke-9 dan ke-10-12)
- **Akumulasi:** Total per hari dijumlahkan untuk seluruh periode

### 7.3 Premi Hadir
- Hanya diberikan di **Periode 2**
- Perhitungan berdasarkan total Ijin + Telat + HC dari Periode 1 + Periode 2

### 7.4 Potongan BPJS
- BPJS Kesehatan, JHT, JP: Hanya di **Periode 1**
- SPN: Hanya di **Periode 2**

### 7.5 Master Data Relationships
- `m_karyawan.Gol` → `m_golongan.vcKodeGolongan`
- `m_karyawan.Jabat` → `m_jabatan.vcKodeJabatan`
- `m_karyawan.intNoBadge` → No KTP

---

## 8. FILE-FILE YANG DIMODIFIKASI

### Controllers:
1. `app/Http/Controllers/ClosingController.php` - Logic perhitungan gaji
2. `app/Http/Controllers/SlipGajiController.php` - Filter dan preview slip
3. `app/Http/Controllers/AbsenController.php` - Optimasi query
4. `app/Http/Controllers/KaryawanController.php` - Dropdown data

### Views:
1. `resources/views/proses/closing/index.blade.php` - Form closing gaji
2. `resources/views/proses/view-gaji/index.blade.php` - View hasil gaji
3. `resources/views/laporan/slip-gaji/index.blade.php` - Filter slip gaji
4. `resources/views/laporan/slip-gaji/preview.blade.php` - Preview 4 slip per halaman
5. `resources/views/laporan/slip-gaji/print.blade.php` - Print single slip
6. `resources/views/absen/index.blade.php` - Browse absensi dengan status KHL, ATL, HC
7. `resources/views/master/karyawan/index.blade.php` - Form edit dengan dropdown
8. `resources/views/layouts/app.blade.php` - Sidebar default expand
9. `resources/views/dashboard.blade.php` - Card dashboard
10. `resources/views/master/gapok/index.blade.php` - Kolom Gaji Pokok

### Models:
1. `app/Models/Closing.php` - Model t_closing
2. `app/Models/Karyawan.php` - Relationship dengan Golongan dan Jabatan

### Migrations:
1. `database/migrations/2025_11_04_034400_create_t_closing_table.php`
2. `database/migrations/2025_11_04_093044_add_dec_potongan_absen_to_t_closing_table.php`

### Routes:
- `routes/web.php` - Route untuk closing, view-gaji, slip-gaji

---

## 9. CATATAN PENTING

### Performance:
- Browse Absensi dioptimalkan dengan `DB::table()` dan JOIN langsung
- Pre-check untuk menghindari duplikasi data
- Manual pagination untuk combined data

### Data Consistency:
- Potongan Koperasi: `vcJenis='0'`, `vcFlag='Debit'` atau `'1'`
- Potongan DPLK: `vcJenis='1'`
- Potongan SPN: `vcJenis='2'`
- Rapel: `vcJenis='RAPEL'` atau `vcFlag='Credit'` dengan jenis mengandung 'RAPEL'

### UI/UX:
- Font sizes: 0.7rem untuk body, 0.75rem untuk header, 0.6rem untuk detail
- Compact spacing dengan `line-height: 1.1`
- 4 slip per halaman A4 dengan height 48vh
- Kolom width sudah dioptimalkan untuk readability

---

## 10. PENDING / NEXT STEPS

Tidak ada pending task eksplisit. Semua request sudah selesai.

**Potential Improvements:**
- Validasi tambahan untuk perhitungan gaji
- Export Excel untuk View Gaji
- Print preview untuk slip gaji individual
- Optimasi lebih lanjut untuk query besar

---

**End of Memory - 4 November 2025**
