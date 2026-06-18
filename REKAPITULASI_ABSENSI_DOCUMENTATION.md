# 📊 Dokumentasi: Rekapitulasi Absensi Karyawan

## 🎯 Ringkasan

Menu baru **Rekapitulasi Absensi Karyawan** untuk menampilkan ringkasan absensi per karyawan dalam format tabel dengan kolom: Kriteria, Jumlah, Persentase, dan Formulasi/Aturan.

---

## 📋 Fitur

### 1. Filter
- **Dari Tanggal** - Tanggal awal periode
- **Sampai Tanggal** - Tanggal akhir periode
- **NIK** (wajib) - NIK karyawan yang akan direkapitulasi

### 2. Tampilan Data
- **Header:** NIK, Nama, Departemen, Bagian
- **Tabel Rekapitulasi** dengan 4 kolom:
  - Kriteria
  - Jumlah
  - Persentase (format Indonesia dengan koma)
  - Formulasi atau aturan

### 3. Kriteria yang Dihitung

1. **Jumlah Hari Kerja Normal Kalender**
   - Total hari kerja (exclude Sabtu/Minggu & hari libur nasional)
   - Tidak ada persentase

2. **Hadir**
   - Jumlah baris `t_absen` yang memiliki jam masuk atau jam keluar
   - Persentase: (Hadir / Jumlah Hari Kerja) × 100%
   - Formulasi: "masuk kehadiran"

3. **Cuti Tahunan**
   - Dari `t_tidak_masuk` dengan `vcKodeAbsen = 'C010'`
   - Tidak ada persentase

4. **Sakit Dengan Surat Dokter**
   - Dari `t_tidak_masuk` dengan `vcKodeAbsen = 'S010'`
   - Persentase: (Sakit / Jumlah Hari Kerja) × 100%
   - Formulasi: "Persentase izin sakit dengan surat dokter dari total jumlah hari kerja"

5. **Cuti Melahirkan**
   - Dari `t_tidak_masuk` dengan `vcKodeAbsen = 'C011'`
   - Persentase: (Cuti Melahirkan / Jumlah Hari Kerja) × 100%
   - Formulasi: "persentase Tidak masuk kehadiran dari total hari kerja"

6. **Izin Pribadi**
   - Dari `t_tidak_masuk` dengan `vcKodeAbsen = 'I002'`
   - Persentase: (Izin Pribadi / Hadir) × 100%
   - Formulasi: "persentae izin dari total kehadiran"

7. **Ijin Resmi**
   - Dari `t_tidak_masuk` dengan `vcKodeAbsen = 'I001'`
   - Persentase: (Ijin Resmi / Hadir) × 100%
   - Formulasi: "persentase ijin resmi dari total kehadiran"

8. **Ijin Organisasi**
   - Dari `t_tidak_masuk` dengan `vcKodeAbsen = 'I003'` (asumsi)
   - Persentase: (Ijin Organisasi / Hadir) × 100%
   - Formulasi: "persentase ijin organisasi dari total kehadrian"

9. **Alfa**
   - Hari kerja yang tidak ada absensi dan tidak ada ijin/tidak masuk
   - Persentase: (Alfa / Jumlah Hari Kerja) × 100%
   - Formulasi: "persentase Alfa tidak masuk kehadiran dari total hari kerja"

10. **Pulang Cepat**
    - Jumlah hari dengan jam keluar < jam shift pulang
    - Persentase: (Pulang Cepat / Hadir) × 100%
    - Formulasi: "persentase jumlah jam pulang lebih cepat dari jam pulang shiftnya"

11. **Datang Telat / Masuk siang**
    - Gabungan: Terlambat + Masuk Siang (izin Z004)
    - Persentase: ((Terlambat + Masuk Siang) / Hadir) × 100%
    - Formulasi: "persentase izin pribadi yang di awali dari jam awal shit kerja"

12. **<8 Jam**
    - Jumlah hari dengan jam kerja < 8 jam (dikurangi jam istirahat)
    - Persentase: (<8 Jam / Hadir) × 100%
    - Formulasi: "persentase kerja kirang dari 8 jam dari jumlah ke hadiran"

13. **Terlambat**
    - Jumlah hari dengan jam masuk > jam shift masuk (lebih dari 1 menit)
    - Persentase: (Terlambat / Hadir) × 100%
    - Formulasi: "persentase jumlah terlambat dari jumlah kehadiran"

14. **Tepat Waktu**
    - Total kehadiran tanpa terlambat ataupun izin masuk siang
    - Persentase: (Tepat Waktu / Hadir) × 100%
    - Formulasi: "Persentase total kehadiran tanpa terlambat ataupun izin masuk siang (izin di mulai dari awal jam masuk shift)"

---

## 🔧 Logic & Konsep Penting

### 1. Jumlah Hari Kerja Normal Kalender
- Loop dari tanggal awal sampai tanggal akhir
- Exclude: Sabtu (6), Minggu (0), Hari Libur Nasional
- Hitung total hari kerja

### 2. Hadir
- Query `t_absen` dengan kondisi: `dtJamMasuk IS NOT NULL OR dtJamKeluar IS NOT NULL`
- Count jumlah baris

### 3. Tidak Masuk
- Query `t_tidak_masuk` dengan kondisi:
  - `dtTanggalMulai` atau `dtTanggalSelesai` dalam periode, ATAU
  - Periode overlap dengan tanggal mulai-selesai
- Group by `vcKodeAbsen` untuk menghitung per jenis

### 4. Alfa
- Loop semua hari kerja
- Cek apakah tanggal ada di:
  - `tanggalHadir` (dari `t_absen`)
  - `tanggalTidakMasuk` (dari `t_tidak_masuk`)
- Jika tidak ada di keduanya → Alfa

### 5. Pulang Cepat
- Query `t_absen` dengan join `m_karyawan` dan `m_shift`
- Bandingkan `dtJamKeluar` dengan `vcPulang` shift
- Jika `dtJamKeluar < vcPulang` → Pulang Cepat

### 6. Masuk Siang
- Query `t_izin` dengan `vcKodeIzin = 'Z004'`
- Hitung jumlah record (bukan jam)

### 7. Terlambat
- Query `t_absen` dengan join `m_karyawan` dan `m_shift`
- Bandingkan `dtJamMasuk` dengan `vcMasuk` shift
- Jika `dtJamMasuk > vcMasuk` dan selisih > 1 menit → Terlambat

### 8. <8 Jam
- Query `t_absen` dengan `dtJamMasuk` dan `dtJamKeluar` tidak null
- Hitung durasi kerja (dikurangi 1 jam istirahat jika melewati 12:00-13:00)
- Jika durasi < 8 jam → <8 Jam

### 9. Tepat Waktu
- Formula: `Hadir - Terlambat - Masuk Siang`

---

## 📁 File yang Dibuat

1. **`app/Http/Controllers/RekapitulasiAbsensiController.php`** (BARU)
   - Method `index()` - Tampilkan form filter dan hasil rekapitulasi
   - Method `calculateRekapitulasi()` - Hitung semua kriteria rekapitulasi

2. **`resources/views/absen/rekapitulasi/index.blade.php`** (BARU)
   - Form filter dengan periode dan NIK
   - Tabel rekapitulasi dengan format seperti spreadsheet
   - Header: NIK, Nama, Departemen, Bagian

3. **`routes/web.php`** (MODIFIKASI)
   - Route baru: `GET absensi/rekapitulasi` → `RekapitulasiAbsensiController@index`

4. **`resources/views/layouts/app.blade.php`** (MODIFIKASI)
   - Menu baru: "Rekapitulasi Absensi" di sidebar → Absensi

---

## 🎨 Format Tampilan

### Tabel Rekapitulasi
- **Header:** Background biru (#4472C4), text putih
- **Kolom Kriteria:** Background biru muda (#D9E1F2)
- **Kolom Jumlah:** Text align right, format number tanpa desimal
- **Kolom Persentase:** Text align right, format dengan koma (100,000% bukan 100.000%)
- **Kolom Formulasi:** Text biasa

### Format Persentase
- Menggunakan koma sebagai pemisah desimal (format Indonesia)
- Contoh: `100,000%`, `0,467%`
- 3 desimal

---

## 🔐 Validasi

- **NIK wajib diisi** untuk melihat rekapitulasi
- **Karyawan harus aktif** (`vcAktif = '1'`)
- **Periode valid** (dari tanggal <= sampai tanggal)

---

## 📊 Data Flow

```
User Input Filter → Validate NIK → Get Karyawan Data → 
Calculate Rekapitulasi → Display Table
```

**Calculate Rekapitulasi:**
1. Hitung Jumlah Hari Kerja
2. Hitung Hadir
3. Hitung Tidak Masuk (per jenis)
4. Hitung Alfa
5. Hitung Pulang Cepat
6. Hitung Masuk Siang
7. Hitung Terlambat
8. Hitung <8 Jam
9. Hitung Tepat Waktu
10. Build Array Rekapitulasi
11. Return Array

---

## 🐛 Catatan Penting

1. **Ijin Organisasi:** Kode `I003` adalah asumsi. Perlu konfirmasi kode yang benar.
2. **Format Persentase:** Menggunakan koma (format Indonesia), bukan titik
3. **Pulang Cepat:** Dihitung dari absensi (bukan dari izin Z003)
4. **Datang Telat / Masuk siang:** Gabungan antara Terlambat dan Masuk Siang
5. **<8 Jam:** Durasi kerja dikurangi 1 jam istirahat jika melewati 12:00-13:00

---

## ✅ Testing Checklist

- [ ] Filter periode berfungsi
- [ ] Filter NIK berfungsi (wajib)
- [ ] Validasi NIK tidak ditemukan
- [ ] Tabel rekapitulasi tampil dengan benar
- [ ] Format persentase menggunakan koma
- [ ] Semua kriteria dihitung dengan benar
- [ ] Header (NIK, Nama, Departemen, Bagian) tampil dengan benar
- [ ] Menu "Rekapitulasi Absensi" muncul di sidebar

---

**Status:** ✅ Selesai dan siap untuk testing

**Tanggal:** 4 Desember 2025











