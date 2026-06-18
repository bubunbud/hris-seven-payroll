# 3. Formulasi, parameter input, proses & output

Dokumen ini **tidak** mengganti spesifikasi detail rumus gaji bertahun dari tim finance; Ia menunjuk **di mana rumus tersebut diimplementasi** serta **alur data** utama. Untuk revisi hukum baru, konsultasi harus mencakup `ClosingController` & `UpdateClosingGajiController`.

---

## 3.1 Closing gaji (inti payroll)

### Input utama (implicit/user)

Parameter yang digunakan batch closing (pemilihan di halaman Closing):

| Parameter | Deskripsi |
|-----------|-----------|
| Rentang kalender periode gaji (`dtPeriodeFrom`/`dtPeriodeTo`) | Batas pembacaan jam masuk/lembur. |
| `periode`, `vcQuarter`, `vcKodeDivisi` | Unik pembayaran wilayah divisi + kuarter 1 atau 2. |
| Konteks tahun sebelumnya | Digunakan saat rollover saldo sakit/alpa/cuti dll. (**`calculateEmployeePayroll`**) |

Data referensi pembacaan aplikasi lain:

| Sumber DB | Pemakaian bisnis ringkas |
|-----------|---------------------------|
| `m_karyawan`, `m_gapok`, `m_jabatan` | Profil golongan, divisi dept; tarif besar gapok+tunjangannya. |
| `t_absen` | Hari hadir/absen tidak berangkat tepat pada hari kerja net per NIK. |
| `m_hari_libur` + **`tukar`** | Menentukan hari libur/pekerja bersyarat bersifat personal. |
| `t_tidak_masuk`, `t_izin` | Hari tidak dapat hadir atas absensi tetapi absen bisa dikoreksi secara administratif sesuai proses Anda. |
| `t_lembur_header/detail` atau modul lain | Pemetaan nominal lewat `LemburCalculationService`. |
| `t_hutang_piutang` | Potensi potongannya sebelum pembayaran. |

### Pemrosesan (ringkas algoritme)

Telusuri metode utama:

1. **`ClosingController@store`** memvalidasi periode kemudian memanggil `calculatePayroll` per kombinasi.
2. **`calculatePayroll`** memuat kandidat pegawai bertugas kemudian iterasi **`calculateEmployeePayroll`** satu per satu.
3. Dalam **`calculateEmployeePayroll`** beberapa blok penting secara beruntun mencakup (disederhanakan):
   - Pembentukan **`$gapokPerBulan`** = jumlah beberapa kolom **`m_gapok`** (bagian pertama file).
   - **`$decGapok` = pembagian dua** (dibayarkan setengah bulan).
   - **Perhitungan hari kerja** personal melalui `calculateHariKerjaWithTukar` kemudian **libur bersyarat** `getHariLiburWithTukar`.
   - Penjadwalan menghitung kombinasi lembur hari kerja/libur serta **pembebanan biaya BU** (**`decBeban*`** kolom Closing).
   - Potongan & tunjangan peraturan pajak serta **premi**/potongannya.
   - **Upsert akhirnya** **`DB::table('t_closing')->updateOrInsert([...composite key...])`**.

### Output

| Output | Lokasi penyimpanan / media |
|---------|----------------------------|
| Baris pembayaran karyawan | **`t_closing`** (compound primary key kombinasi). |
| Pesan kesuksesan/peringatan kolektif dari batch | Feedback UI Laravel. |
| Rekap pembayaran & slip | Bacalah baris sama melalui modul **`View Rekap Gaji`**, **`SlipGajiController`**, serta export finance. |

---

## 3.2 Lembur nominal & verifikasi

| Tahap | File / controller | Deskripsi |
|-------|-------------------|-----------|
| Rencana lemburan | `InstruksiKerjaLemburController` | Pemilihan pola jenis overtime + hitung AJAX **nominal** melalui `calculateLemburNominal`. |
| Realisasi & persetujuan | `RealisasiLemburController` | Perubahan status baris **`t_lembur_*`** (header/detail lamanya `t_lembur` bisa legacy). |
| Agregasi saat Closing | **`LemburCalculationService`** + referensi Closing | Pemetaan total jam & distribusi BU beban (**`decBebanTgi`**..., **`decJamLemburKerjaN`**, dll.) yang akhirnya mendarat Closing. |

Untuk formulasi tepat faktor pembulatan per menit/overtime bertingkat bacalah **service** secara baris-per-baris; ini sering termasuk hukum industri perusahaan.

---

## 3.3 THR (Tunjangan Hari Raya)

| Tahap | Tabel utama | Deskripsi |
|-------|-------------|-----------|
| Definisikan pembayaran | `t_periode_thr` | Pemilihan masa THR sama konsep `t_periode` gaji. |
| Pemrosesan | `ClosingThrController` | Menghasilkan `t_closing_thr`. |
| Laporan/upah slip | Controller `SlipThr`, `LaporanThr`, export bank THR (`RekapBankThrController`) |

---

## 3.4 Absensi, izin tidak masuk, izin keluar

Umum pola:

```
Input formulir Blade -> Validation Controller -> Persist `t_tidak_masuk`, `t_izin`
-> Dampak perhitungan hari Closing jika pola absensi menghitung status administratif tersebut
```

Ada service khusus **SecurityAttendance** seperti `SecurityAbsensiService.php` bagi modul Browse Absensi Satpam membaca struktur **`t_jadwal_shift_security`**.

Statistik menggunakan agregasi query (`StatistikAbsensiController`, dll.) menghasil HTML + chart (jika digunakan).

---

## 3.5 Integrasi feeder cuti/feeder lain

| Modul Setting | Pemrosesan tinggi | Env |
|----------------|-------------------|-----|
| **Tarik/Data List API** controllers | Pembaca HTTP lewat **`HrisApiService`**, `HrisApiAbsentService`, `HrisApiPermitService`; transform -> insert **`t_*`**. | **`HRIS_API_BASE_URL`** dsb dari `config/hris_api.php` |

Pastikan dokumentasi SLA rate-limit API eksternal.

---

## 3.6 Visual / export

| Kebutuhan | Mekanisme |
|-----------|-----------|
| HTML tabel besar | Pagination manual maupun server-side Laravel `paginate()`. |
| Excel | Trait export `Maatwebsite\Excel` (lihat **`app/Exports/`** seperti `RekapUpahFinanceVerExport.php`). |
| PDF/cetak | Beberapa kontroller memiliki metode **`print`** memakai view Blade cetak atau redirect ke layout `print.blade`. |
