# Formulasi Perhitungan Lembur di Closing Gaji

Dokumentasi lengkap formulasi dan perhitungan lembur yang digunakan di halaman Closing Gaji.

## 📋 Ringkasan

Formulasi perhitungan lembur di Closing Gaji sudah baik dan sesuai. Dokumen ini menjelaskan detail lengkap formulasi yang digunakan.

---

## 🔢 Dasar Perhitungan

### Rate Per Jam

```
ratePerJam = gapokPerBulan / 173
```

**Keterangan:**

-   `gapokPerBulan` = Upah + Tunjangan Keluarga + Tunjangan Masa Kerja + Tunjangan Jabatan 1 + Tunjangan Jabatan 2
-   173 = jumlah jam kerja per bulan (standar)

---

## 📊 Formulasi Lembur

### 1. Hari Kerja Normal (HKN)

**Ketentuan:**

-   Jam ke-1: Maksimal 1 jam per hari, tarif **1.5x** rate per jam
-   Jam ke-2: Sisa jam setelah jam ke-1, tarif **2x** rate per jam

**Contoh Perhitungan:**

| Total Jam Lembur | Jam ke-1 | Jam ke-2 | Rupiah ke-1      | Rupiah ke-2  |
| ---------------- | -------- | -------- | ---------------- | ------------ |
| 0.5 jam          | 0.5      | 0        | 0.5 × 1.5 × rate | 0            |
| 1 jam            | 1        | 0        | 1 × 1.5 × rate   | 0            |
| 2 jam            | 1        | 1        | 1 × 1.5 × rate   | 1 × 2 × rate |
| 4 jam            | 1        | 3        | 1 × 1.5 × rate   | 3 × 2 × rate |
| 8 jam            | 1        | 7        | 1 × 1.5 × rate   | 7 × 2 × rate |

**Kode Implementasi:**

```php
if ($totalJamLembur > 0) {
    // Jam ke-1: maksimal 1 jam per hari (hanya di hari kerja)
    $jam1 = min(1, $totalJamLembur);
    // Jam ke-2: sisa jam setelah jam ke-1 (hanya di hari kerja)
    $jam2 = max(0, $totalJamLembur - $jam1);

    // Hitung rupiah: Jam ke-1 = 1.5x, Jam ke-2 = 2x
    $rupiah1 = $jam1 * 1.5 * $ratePerJam;
    $rupiah2 = $jam2 * 2 * $ratePerJam;

    // Akumulasi per hari kerja
    $jamKerja1 += $jam1;
    $jamKerja2 += $jam2;
    $rupiahKerja1 += $rupiah1;
    $rupiahKerja2 += $rupiah2;
}
```

---

### 2. Hari Libur

**Ketentuan:**

-   Jam ke-1 sampai ke-8: Tarif **2x** rate per jam
-   Jam ke-9: Tarif **3x** rate per jam
-   Jam ke-10 sampai ke-12: Tarif **4x** rate per jam

**Contoh Perhitungan:**

| Total Jam Lembur | Jam ke-1 (2x) | Jam ke-2 (3x) | Jam ke-3 (4x) | Rupiah ke-1  | Rupiah ke-2  | Rupiah ke-3  |
| ---------------- | ------------- | ------------- | ------------- | ------------ | ------------ | ------------ |
| 4 jam            | 4             | 0             | 0             | 4 × 2 × rate | 0            | 0            |
| 8 jam            | 8             | 0             | 0             | 8 × 2 × rate | 0            | 0            |
| 9 jam            | 8             | 1             | 0             | 8 × 2 × rate | 1 × 3 × rate | 0            |
| 10 jam           | 8             | 1             | 1             | 8 × 2 × rate | 1 × 3 × rate | 1 × 4 × rate |
| 12 jam           | 8             | 1             | 3             | 8 × 2 × rate | 1 × 3 × rate | 3 × 4 × rate |

**Kode Implementasi:**

```php
if ($isHariLibur) {
    // Hari libur: 2x (8 jam pertama), 3x (jam ke-9), 4x (jam ke-10 sampai 12)
    if ($totalJamLembur > 0) {
        $jam1 = min(8, $totalJamLembur);
        $jam2 = $totalJamLembur > 8 ? min(1, $totalJamLembur - 8) : 0;
        $jam3 = $totalJamLembur > 9 ? min(3, $totalJamLembur - 9) : 0;

        $rupiah2 = $jam1 * 2 * $ratePerJam;
        $rupiah3 = $jam2 * 3 * $ratePerJam + $jam3 * 4 * $ratePerJam;

        $jamLibur2 += $jam1;
        $jamLibur3 += ($jam2 + $jam3);
        $rupiahLibur2 += $rupiah2;
        $rupiahLibur3 += $rupiah3;
    }
}
```

**Catatan:**

-   Variabel `jamLibur2` dan `rupiahLibur2` untuk jam ke-1 sampai ke-8 (tarif 2x)
-   Variabel `jamLibur3` dan `rupiahLibur3` untuk jam ke-9 ke atas (tarif 3x dan 4x)

---

## ⏱️ Perhitungan Durasi Lembur

### Langkah-langkah:

1. **Ambil jam masuk dan keluar lembur:**

    ```php
    $jamMasukLembur = substr((string) $absen->dtJamMasukLembur, 0, 5);
    $jamKeluarLembur = substr((string) $absen->dtJamKeluarLembur, 0, 5);
    ```

2. **Parse ke Carbon untuk perhitungan:**

    ```php
    $tanggal = $absen->dtTanggal instanceof Carbon ? $absen->dtTanggal->copy() : Carbon::parse($absen->dtTanggal);
    $masukLembur = $tanggal->copy()->setTimeFromTimeString($jamMasukLembur);
    $keluarLembur = $tanggal->copy()->setTimeFromTimeString($jamKeluarLembur);
    ```

3. **Handle jika keluar lembur melewati tengah malam:**

    ```php
    if ($keluarLembur->lessThan($masukLembur)) {
        $keluarLembur->addDay();
    }
    ```

4. **Hitung total menit lembur:**

    ```php
    $totalMenitLembur = $masukLembur->diffInMinutes($keluarLembur, true);
    ```

5. **Kurangi waktu istirahat (jika ada):**

    ```php
    $durasiIstirahat = $absen->intDurasiIstirahat ?? 0;
    if ($durasiIstirahat > 0) {
        $totalMenitLembur = max(0, $totalMenitLembur - $durasiIstirahat);
    }
    ```

6. **Konversi ke jam (dibulatkan 2 desimal):**
    ```php
    $totalJamLembur = round($totalMenitLembur / 60, 2);
    ```

---

## 🔍 Kondisi Lembur yang Dihitung

### Syarat Lembur Diperhitungkan:

1. ✅ Ada `dtJamMasukLembur` (tidak kosong)
2. ✅ Ada `dtJamKeluarLembur` (tidak kosong)
3. ✅ Ada `vcCounter` (lembur sudah dikonfirmasi dari Instruksi Kerja Lembur)

**Kode:**

```php
if (empty($absen->dtJamMasukLembur) || empty($absen->dtJamKeluarLembur)) continue;
if (empty($absen->vcCounter)) continue; // Hanya lembur yang sudah dikonfirmasi
```

---

## 📦 Output Data

### Field yang Dihasilkan:

**Hari Kerja:**

-   `jam_kerja_1` - Total jam lembur ke-1 (maks 1 jam/hari)
-   `rupiah_kerja_1` - Total rupiah lembur ke-1 (1.5x)
-   `jam_kerja_2` - Total jam lembur ke-2 (sisa setelah ke-1)
-   `rupiah_kerja_2` - Total rupiah lembur ke-2 (2x)
-   `jam_kerja_3` - Total jam lembur ke-3 (tidak digunakan di hari kerja)
-   `rupiah_kerja_3` - Total rupiah lembur ke-3 (tidak digunakan di hari kerja)

**Hari Libur:**

-   `jam_libur_2` - Total jam lembur ke-1 sampai ke-8 (tarif 2x)
-   `rupiah_libur_2` - Total rupiah lembur ke-1 sampai ke-8 (2x)
-   `jam_libur_3` - Total jam lembur ke-9 ke atas (tarif 3x dan 4x)
-   `rupiah_libur_3` - Total rupiah lembur ke-9 ke atas (3x dan 4x)

**Total:**

-   `total_jam_kerja` - Total jam lembur hari kerja
-   `total_jam_libur` - Total jam lembur hari libur

**Beban (berdasarkan penanggung biaya):**

-   `beban_tgi` - Beban TGI
-   `beban_sia_exp` - Beban SIA Export
-   `beban_sia_prod` - Beban SIA Produksi
-   `beban_rma` - Beban RMA
-   `beban_smu` - Beban SMU/Sutek
-   `beban_abn_jkt` - Beban Abadinusa/ABN-JKT

---

## 🏢 Mapping Penanggung Biaya

### Mapping ke Field Beban:

```php
$penanggungBiaya = $lemburHeader->vcPenanggungBiaya ?? '';

if (stripos($penanggungBiaya, 'TGI') !== false) {
    $bebanTgi += $bebanLembur;
} elseif (stripos($penanggungBiaya, 'SIA Export') !== false || stripos($penanggungBiaya, 'SIA-EXP') !== false) {
    $bebanSiaExp += $bebanLembur;
} elseif (stripos($penanggungBiaya, 'SIA Produksi') !== false || stripos($penanggungBiaya, 'SIA-PROD') !== false || stripos($penanggungBiaya, 'SIA-P11') !== false || stripos($penanggungBiaya, 'SIA-P12') !== false) {
    $bebanSiaProd += $bebanLembur;
} elseif (stripos($penanggungBiaya, 'RMA') !== false) {
    $bebanRma += $bebanLembur;
} elseif (stripos($penanggungBiaya, 'Sutek') !== false || stripos($penanggungBiaya, 'SMU') !== false) {
    $bebanSmu += $bebanLembur;
} elseif (stripos($penanggungBiaya, 'Abadinusa') !== false || stripos($penanggungBiaya, 'ABN-JKT') !== false) {
    $bebanAbnJkt += $bebanLembur;
}
```

**Catatan:**

-   Mapping menggunakan `stripos()` untuk case-insensitive matching
-   Beban dihitung per hari (bukan akumulasi total)

---

## 📝 Penyimpanan ke Database

### Field di `t_closing`:

```php
'decJamLemburKerja1' => $lemburData['jam_kerja_1'],
'decJamLemburKerja2' => $lemburData['jam_kerja_2'],
'decJamLemburKerja3' => $lemburData['jam_kerja_3'],
'decLemburKerja1' => $lemburData['rupiah_kerja_1'],
'decLemburKerja2' => $lemburData['rupiah_kerja_2'],
'decLemburKerja3' => $lemburData['rupiah_kerja_3'],
'decJamLemburLibur2' => $lemburData['jam_libur_2'],
'decJamLemburLibur3' => $lemburData['jam_libur_3'],
'decLembur2' => $lemburData['rupiah_libur_2'],
'decLembur3' => $lemburData['rupiah_libur_3'],
'decJamLemburKerja' => $lemburData['total_jam_kerja'],
'decJamLemburLibur' => $lemburData['total_jam_libur'],
'decTotallembur1' => $lemburData['rupiah_kerja_1'],
'decTotallembur2' => $lemburData['rupiah_kerja_2'] + $lemburData['rupiah_libur_2'],
'decTotallembur3' => $lemburData['rupiah_kerja_3'] + $lemburData['rupiah_libur_3'],
'decBebanTgi' => $lemburData['beban_tgi'],
'decBebanSiaExp' => $lemburData['beban_sia_exp'],
'decBebanSiaProd' => $lemburData['beban_sia_prod'],
'decBebanRma' => $lemburData['beban_rma'],
'decBebanSmu' => $lemburData['beban_smu'],
'decBebanAbnJkt' => $lemburData['beban_abn_jkt'],
```

---

## 🔗 Keterkaitan dengan Fitur Lain

### 1. Instruksi Kerja Lembur

-   Lembur hanya dihitung jika memiliki `vcCounter` (dari Instruksi Kerja Lembur)
-   `vcCounter` menghubungkan `t_absen` dengan `t_lembur_header`

### 2. Realisasi Lembur

-   Data lembur diambil dari `t_absen` dengan field:
    -   `dtJamMasukLembur`
    -   `dtJamKeluarLembur`
    -   `intDurasiIstirahat`
    -   `vcCounter`

### 3. Tunjangan Makan & Transport

-   Tunjangan makan dan transport lembur juga dihitung berdasarkan data lembur yang sama
-   Lihat fungsi `calculateTunjanganMakanTransport()`

---

## ✅ Status Formulasi

**Status:** ✅ **Sudah Baik dan Sesuai**

Formulasi perhitungan lembur di Closing Gaji sudah:

-   ✅ Menggunakan rate per jam yang benar (gapokPerBulan / 173)
-   ✅ Membedakan perhitungan hari kerja dan hari libur
-   ✅ Menghitung durasi dengan presisi (menit, dikurangi istirahat)
-   ✅ Membagi jam lembur sesuai ketentuan tarif
-   ✅ Menghitung beban berdasarkan penanggung biaya
-   ✅ Hanya menghitung lembur yang sudah dikonfirmasi (ada vcCounter)

---

## 📍 Lokasi File

**Controller:**

-   `app/Http/Controllers/ClosingController.php`
-   Method: `calculateLembur()` (baris 567-710)

**Pemanggilan:**

-   Method: `calculateEmployeePayroll()` (baris 234-476)
-   Dipanggil dari: `calculatePayroll()` (baris 149-229)
-   Dipanggil dari: `store()` (baris 68-143)

---

**Dokumentasi dibuat:** 17 Januari 2025
**Versi:** 1.0





