# 📋 Konfirmasi Pemahaman: Modul THR (Tunjangan Hari Raya)

**Tanggal:** 4 Februari 2026  
**Fitur:** Modul THR - Periode Closing THR & Closing THR

---

## ✅ Konfirmasi Pemahaman

### **Tahap 1: Periode Closing THR**

#### **1. Struktur Tabel `t_periode_thr`**

**Primary Key:** Composite `(dtPeriode, dtKategori, vcKodeDivisi)`

**Field:**
- `dtPeriode` (VARCHAR(4)) - Tahun periode THR, contoh: "2025"
- `dtCutoffTHR` (DATE) - Tanggal Patokan Perhitungan THR
- `dtKategori` (VARCHAR(50)) - Hari Keagamaan
  - Dropdown: "Islam (Idul Fitri)", "Kristen (Natal)", "Hindu (Nyepi)", "Budha (Waisak)", "Lainnya"
- `vcKodeDivisi` (VARCHAR(10)) - Kode Divisi/Bisnis Unit
- `vcKeterangan` (VARCHAR(255), nullable) - Free text
- `vcStatus` (VARCHAR(1), default '0') - '0' = Belum proses, '1' = Sudah diproses
- `dtCreate` (DATETIME, nullable) - Tanggal buat

#### **2. Layout & Fitur**

- ✅ Mirip dengan halaman "Periode Closing Gaji"
- ✅ Form buat periode THR dengan field:
  - Tahun Periode (input tahun, contoh: 2025)
  - Tanggal Cutoff THR (date picker)
  - Kategori (dropdown: Islam (Idul Fitri), Kristen (Natal), Hindu (Nyepi), Budha (Waisak), Lainnya)
  - Pilih Divisi (checkbox multiple, sama seperti Periode Gaji)
  - Keterangan (text input, optional)
- ✅ List periode THR dengan kolom:
  - Tahun Periode
  - Tanggal Cutoff THR
  - Kategori
  - Kode Divisi
  - Nama Divisi
  - Status (Belum Diproses / Sudah Diproses)
- ✅ Hapus periode (hanya yang belum diproses)

---

### **Tahap 2: Closing THR**

#### **1. Kategori Closing THR**

- **Closing THR Operator** (prioritas pertama)
- **Closing THR Staff** (nanti, perhitungan sama tapi tanpa nilai nominal)

#### **2. Filter Closing THR Operator**

- `m_karyawan.Group_pegawai = "Operator"`
- `t_periode_thr.dtKategori = m_karyawan.Agama` (mapping otomatis)

**Mapping Agama ke Kategori:**
- Agama "Islam" → Kategori "Islam (Idul Fitri)"
- Agama "Kristen" → Kategori "Kristen (Natal)"
- Agama "Hindu" → Kategori "Hindu (Nyepi)"
- Agama "Budha" → Kategori "Budha (Waisak)"
- Agama lainnya → Kategori "Lainnya"

#### **3. Ketentuan Perhitungan THR**

**A. Masa Kerja >= 12 Bulan:**
- THR = **1 bulan upah penuh**
- `decXGaji` = 1.0

**B. Masa Kerja < 12 Bulan:**
- THR = **(Masa Kerja / 12) × 1 bulan upah**
- `decXGaji` = Masa Kerja (bulan) / 12

**C. 1 Bulan Upah:**
```
Gaji Pokok = m_gapok.upah 
           + m_gapok.tunj_keluarga 
           + m_gapok.tunj_masa_kerja 
           + m_gapok.tunj_jabatan1 
           + m_gapok.tunj_jabatan2
```

**D. Perhitungan Masa Kerja:**
- Dari: `t_periode_thr.dtCutoffTHR - m_karyawan.Tgl_Masuk`
- Format lengkap: "X Tahun, Y Bulan, Z Hari"
- Dalam Hari: `intMasaKerjaHari`
- Dalam Bulan: `decMasaKerjaBulan` (desimal, contoh: 0.90)
- Dalam Tahun: `decMasaKerjaTahun` (desimal, contoh: 0.90)

**E. Nilai THR:**
```
decNilaiTHR = decGajiPokok × decXGaji
```

#### **4. Struktur Tabel `t_closing_thr`**

**Primary Key:** Composite `(dtTanggalTHR, vcNik, vcAgama)`

**Field:**
- `dtTanggalTHR` (DATE) - dari `t_periode_thr.dtCutoffTHR`
- `vcNik` (VARCHAR(10)) - dari `m_karyawan.Nik`
- `vcAgama` (VARCHAR(20)) - dari `m_karyawan.Agama`
- `vcKodeDivisi` (VARCHAR(10)) - untuk tracking divisi
- `vcGroupPegawai` (VARCHAR(20)) - dari `m_karyawan.Group_pegawai`
- `vcGolongan` (VARCHAR(10)) - dari `m_karyawan.Gol`
- `decGajiPokok` (DECIMAL(15,2), nullable) - dari `m_gapok` (null untuk Staff)
- `dtTanggalMasuk` (DATE) - dari `m_karyawan.Tgl_Masuk`
- `vcMasaKerja` (VARCHAR(50)) - format "X Tahun, Y Bulan, Z Hari"
- `intMasaKerjaHari` (INTEGER) - masa kerja dalam hari
- `decMasaKerjaBulan` (DECIMAL(10,2)) - masa kerja dalam bulan (desimal)
- `decMasaKerjaTahun` (DECIMAL(10,2)) - masa kerja dalam tahun (desimal)
- `decXGaji` (DECIMAL(5,2)) - multiplier (x Gaji), contoh: 1.0, 0.9, 0.3
- `decNilaiTHR` (DECIMAL(15,2), nullable) - Nominal uang THR (null untuk Staff)
- `dtCreate` (DATETIME, nullable)
- `dtChange` (DATETIME, nullable)

#### **5. Closing THR Staff**

- ✅ Perhitungan sama dengan Operator
- ✅ `decGajiPokok` = NULL (tidak ditentukan)
- ✅ `decNilaiTHR` = NULL (tidak ditentukan)
- ✅ Laporan terpisah (nanti)

---

## 📝 Contoh Perhitungan

### **Contoh 1: Karyawan dengan Masa Kerja >= 12 Bulan**

**Data:**
- Tgl Masuk: 01/01/2024
- dtCutoffTHR: 31/03/2025
- Gaji Pokok: 3.994.465

**Perhitungan:**
- Masa Kerja: 31/03/2025 - 01/01/2024 = 1 Tahun, 2 Bulan, 30 Hari
- Masa Kerja (Hari): 454 hari
- Masa Kerja (Bulan): 14.97 bulan
- Masa Kerja (Tahun): 1.25 tahun
- `decXGaji` = 1.0 (karena >= 12 bulan)
- `decNilaiTHR` = 3.994.465 × 1.0 = **3.994.465**

---

### **Contoh 2: Karyawan dengan Masa Kerja < 12 Bulan**

**Data:**
- Tgl Masuk: 06/05/2024
- dtCutoffTHR: 31/03/2025
- Gaji Pokok: 3.736.741

**Perhitungan:**
- Masa Kerja: 31/03/2025 - 06/05/2024 = 0 Tahun, 10 Bulan, 25 Hari
- Masa Kerja (Hari): 329 hari
- Masa Kerja (Bulan): 10.83 bulan
- Masa Kerja (Tahun): 0.90 tahun
- `decXGaji` = 10.83 / 12 = 0.90
- `decNilaiTHR` = 3.736.741 × 0.90 = **3.363.067**

---

### **Contoh 3: Karyawan dengan Masa Kerja < 12 Bulan (Kurang dari 1 Bulan)**

**Data:**
- Tgl Masuk: 03/02/2025
- dtCutoffTHR: 31/03/2025
- Gaji Pokok: 3.898.965

**Perhitungan:**
- Masa Kerja: 31/03/2025 - 03/02/2025 = 0 Tahun, 1 Bulan, 28 Hari
- Masa Kerja (Hari): 56 hari
- Masa Kerja (Bulan): 1.87 bulan
- Masa Kerja (Tahun): 0.15 tahun
- `decXGaji` = 1.87 / 12 = 0.16 (dibulatkan menjadi 0.2 sesuai dokumen?)
- `decNilaiTHR` = 3.898.965 × 0.16 = **623.834**

**Catatan:** Dari dokumen, terlihat `decXGaji` = 0.2 untuk masa kerja 56 hari. Apakah ada pembulatan khusus?

---

## ❓ Pertanyaan Tambahan

1. **Pembulatan `decXGaji`:**
   - Apakah ada aturan pembulatan khusus?
   - Contoh: 0.16 dibulatkan menjadi 0.2?
   - Atau tetap presisi 2 desimal (0.16)?

2. **Format `dtPeriode`:**
   - Apakah VARCHAR "2025" atau INTEGER 2025?
   - Saya akan gunakan VARCHAR(4) untuk konsistensi dengan format string

3. **Mapping Agama ke Kategori:**
   - Apakah mapping otomatis di controller?
   - Atau user pilih kategori saat buat periode, lalu filter karyawan berdasarkan agama yang cocok?

4. **Validasi:**
   - Apakah perlu validasi: karyawan harus aktif (`vcAktif = '1'`)?
   - Apakah perlu validasi: karyawan tidak boleh `Tgl_Berhenti`?

5. **Closing THR Staff:**
   - Apakah perlu dibuat sekarang atau nanti?
   - Untuk sementara fokus ke Operator dulu?

---

## ✅ Status Implementasi

### **Sudah Selesai:**
- ✅ Migration `t_periode_thr`
- ✅ Migration `t_closing_thr`
- ✅ Model `PeriodeThr`
- ✅ Model `ClosingThr`

### **Belum Selesai:**
- ⏳ Controller `PeriodeThrController`
- ⏳ View `periode-thr/index.blade.php`
- ⏳ Route dan menu sidebar
- ⏳ Controller `ClosingThrController`
- ⏳ View `closing-thr/index.blade.php`
- ⏳ Logic perhitungan masa kerja
- ⏳ Logic perhitungan THR

---

**Mohon konfirmasi untuk pertanyaan tambahan di atas sebelum saya lanjutkan implementasi controller dan view.**















