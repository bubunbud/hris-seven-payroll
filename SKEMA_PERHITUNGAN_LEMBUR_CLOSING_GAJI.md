# Skema Perhitungan Lembur - Menu Closing Gaji

**Sumber:** `app/Http/Controllers/ClosingController.php`  
**Method:** `calculateLembur()` dan `calculateBebanLemburFromDetail()`

---

## 1. Dasar Perhitungan

### 1.1. Rate Per Jam

```
Rate Per Jam = Gaji Pokok Per Bulan / 173
```

**Gaji Pokok Per Bulan** = Upah + Tunjangan Keluarga + Tunjangan Masa Kerja + Tunjangan Jabatan 1 + Tunjangan Jabatan 2  
*(dari `m_gapok` berdasarkan golongan karyawan)*

**Angka 173** = Jam kerja standar per bulan (sesuai peraturan ketenagakerjaan Indonesia: ±8 jam × 21,67 hari kerja)

### 1.2. Sumber Data Lembur

- **Tabel:** `t_absen` (kolom `dtJamMasukLembur`, `dtJamKeluarLembur`, `vcCounter`)
- **Syarat:** Hanya record yang punya `vcCounter` (lembur sudah dikonfirmasi via Realisasi Lembur)
- **Hari kerja vs libur:** Menggunakan `isHariKerjaNormal()` dengan mempertimbangkan **tukar hari kerja**

### 1.3. Total Jam Lembur per Hari

```
Total Menit Lembur = (Jam Keluar Lembur - Jam Masuk Lembur) - Durasi Istirahat
Total Jam Lembur = Total Menit Lembur / 60  (bulatkan 2 desimal)
```

- Jika jam keluar < jam masuk → dianggap lembur melewati tengah malam (jam keluar + 1 hari)
- `intDurasiIstirahat` dari `t_absen` dikurangi dari total menit

---

## 2. Lembur Hari Kerja Normal (HKN)

**Kondisi:** Hari kerja normal (bukan weekend/hari libur, atau ada tukar hari kerja LIBUR_KE_KERJA)

### 2.1. Pembagian Jam

| Kategori | Jam | Kelipatan Upah | Maksimal |
|----------|-----|----------------|----------|
| **Jam Kerja 1** (J1) | Jam pertama | 1.5× | 1 jam/hari |
| **Jam Kerja 2** (J2) | Jam berikutnya | 2× | Sisa jam |

### 2.2. Contoh

| Total Lembur | J1 | J2 | Rupiah J1 | Rupiah J2 |
|--------------|----|----|-----------|-----------|
| 1 jam | 1 | 0 | 1 × 1.5 × rate | 0 |
| 2 jam | 1 | 1 | 1 × 1.5 × rate | 1 × 2 × rate |
| 4 jam | 1 | 3 | 1 × 1.5 × rate | 3 × 2 × rate |
| 8 jam | 1 | 7 | 1 × 1.5 × rate | 7 × 2 × rate |

### 2.3. Rumus Rupiah

```
Rupiah J1 = Jam1 × 1.5 × Rate Per Jam
Rupiah J2 = Jam2 × 2 × Rate Per Jam
```

---

## 3. Lembur Hari Libur (KHL)

**Kondisi:** Hari libur (weekend/hari libur nasional, atau KERJA_KE_LIBUR, dan bukan LIBUR_KE_KERJA)

### 3.1. Pembagian Jam

| Kategori | Jam | Kelipatan Upah | Rentang |
|----------|-----|----------------|---------|
| **Jam Libur 2** (JL2) | 8 jam pertama | 2× | Jam 1–8 |
| **Jam Libur 3** (JL3) | Jam ke-9 | 3× | 1 jam |
| **Jam Libur 3** (JL3) | Jam ke-10–12 | 4× | Maks 3 jam |

### 3.2. Contoh

| Total Lembur | JL2 | JL3 (3×) | JL3 (4×) | Rupiah |
|--------------|-----|----------|----------|--------|
| 4 jam | 4 | 0 | 0 | 4 × 2 × rate |
| 8 jam | 8 | 0 | 0 | 8 × 2 × rate |
| 9 jam | 8 | 1 | 0 | (8×2) + (1×3) × rate |
| 12 jam | 8 | 1 | 3 | (8×2) + (1×3) + (3×4) × rate |

### 3.3. Rumus Rupiah

```
Rupiah Libur 2 = Jam1 × 2 × Rate Per Jam
Rupiah Libur 3 = (Jam2 × 3 + Jam3 × 4) × Rate Per Jam
```

---

## 4. Output ke Tabel t_closing

| Field | Keterangan |
|-------|------------|
| `decJamLemburKerja1` | Total jam J1 (hari kerja) |
| `decJamLemburKerja2` | Total jam J2 (hari kerja) |
| `decJamLemburKerja3` | 0 (tidak dipakai di hari kerja) |
| `decLemburKerja1` | Rupiah J1 |
| `decLemburKerja2` | Rupiah J2 |
| `decLemburKerja3` | 0 |
| `decJamLemburLibur2` | Total jam JL2 (hari libur) |
| `decJamLemburLibur3` | Total jam JL3 (hari libur) |
| `decLembur2` | Rupiah Libur 2 |
| `decLembur3` | Rupiah Libur 3 |
| `decTotallembur1` | = decLemburKerja1 |
| `decTotallembur2` | = decLemburKerja2 + decLembur2 |
| `decTotallembur3` | = decLemburKerja3 + decLembur3 |

---

## 5. Beban Lembur (Cost Center)

Lembur dialokasikan ke cost center berdasarkan **penanggung beban**:

### 5.1. Sumber Beban

1. **Dari `t_lembur_detail`** (prioritas): `decLemburExternal` + `vcPenanggungBebanLembur`
2. **Fallback dari `t_lembur_header`**: `vcPenanggungBiaya` dari header

### 5.2. Mapping Penanggung Beban

| Penanggung Beban | Field t_closing |
|------------------|-----------------|
| TGI | `decBebanTgi` |
| SIA-EXP | `decBebanSiaExp` |
| SIA-PROD | `decBebanSiaProd` |
| RMA | `decBebanRma` |
| SMU | `decBebanSmu` |
| ABN-JKT | `decBebanAbnJkt` |

### 5.3. Lembur Eksternal (Instruksi Kerja Lembur)

`calculateBebanLemburFromDetail()` menambah beban dari `t_lembur_detail` yang **belum** dihitung di `calculateLembur()` (mis. lembur dari instruksi kerja lembur tanpa absensi).

---

## 6. Ringkasan Alur

```
1. Ambil absensi dari t_absen (dtJamMasukLembur, dtJamKeluarLembur, vcCounter)
2. Cek vcCounter tidak kosong (lembur sudah dikonfirmasi)
3. Tentukan hari kerja vs hari libur (isHariKerjaNormal + tukar hari kerja)
4. Hitung total jam lembur (kurangi durasi istirahat)
5. Hari kerja: J1 (1 jam × 1.5×) + J2 (sisa × 2×)
6. Hari libur: JL2 (8 jam × 2×) + JL3 (jam 9 × 3×, jam 10–12 × 4×)
7. Alokasi beban ke cost center dari LemburDetail/LemburHeader
8. Tambah beban dari lembur eksternal (calculateBebanLemburFromDetail)
```

---

## 7. Referensi Peraturan

Perhitungan mengacu pada **Peraturan Menteri Tenaga Kerja No. 102/MEN/VI/2004** tentang Waktu Kerja Lembur dan Upah Lembur:

- **Hari kerja:** Jam ke-1 = 1.5×, jam ke-2 dst = 2×
- **Hari libur:** 8 jam pertama = 2×, jam ke-9 = 3×, jam ke-10–12 = 4×

---

**Last Updated:** 6 Maret 2026
