# 📚 Memory: Wacana Perhitungan Lembur Hari Kerja Normal (J3 & J4)

**Tanggal:** 6 Maret 2026  
**Status:** Diimplementasikan (26 Mar 2026): `ClosingController::calculateLembur`, `LemburCalculationService`, kolom `decJamLemburKerja4`, `decLemburKerja4`, `decTotallembur4`, migration `2026_03_26_000001_*`

---

## 🎯 Ringkasan Pembahasan

### Konteks Awal

- **Topik:** Skema perhitungan lembur dari menu Closing Gaji
- **File:** `app/Http/Controllers/ClosingController.php` → method `calculateLembur()`
- **Dokumen:** `SKEMA_PERHITUNGAN_LEMBUR_CLOSING_GAJI.md`

### Formulasi Saat Ini (Hari Kerja Normal)

| Kategori | Maksimal | Kelipatan |
|----------|----------|-----------|
| J1 (jam ke-1) | 1 jam | 1.5× |
| J2 (jam ke-2 dst) | Tidak dibatasi | 2× |

---

## 📋 Spesifikasi Baru yang Disepakati (Belum Diimplementasi)

**Berlaku hanya untuk HARI KERJA NORMAL, bukan hari libur.**

| Kategori | Maksimal | Kelipatan |
|----------|----------|-----------|
| **J1** (jam ke-1) | 1 jam | 1.5× |
| **J2** (jam ke-2 s/d ke-8) | 7 jam | 2× |
| **J3** (jam ke-9) | 1 jam | 3× |
| **J4** (jam ke-10 dst) | Sisa (tidak dibatasi) | 4× |

### Catatan Penting

- **J4** mencakup semua sisa jam, termasuk lembur yang **cross day** (melewati tengah malam)
- Perhitungan total jam lembur sudah mendukung overnight (existing logic)

### Rumus

```
J1 = min(1, totalJam)
J2 = min(7, max(0, totalJam - 1))
J3 = min(1, max(0, totalJam - 8))
J4 = max(0, totalJam - 9)
```

### Contoh Perhitungan

| Total Lembur | J1 | J2 | J3 | J4 |
|--------------|----|----|----|-----|
| 8 jam | 1 | 7 | 0 | 0 |
| 9 jam | 1 | 7 | 1 | 0 |
| 10 jam | 1 | 7 | 1 | 1 |
| 12 jam | 1 | 7 | 1 | 3 |
| 15 jam (cross day) | 1 | 7 | 1 | 6 |

---

## 📁 File yang Perlu Diubah (Saat Implementasi)

- `app/Http/Controllers/ClosingController.php` → method `calculateLembur()`
- Bagian: `} else { // Hari kerja normal (HKN)` — sekitar baris 693–716

---

## 🔜 Konteks untuk Lanjutan

- **User:** HRIS Seven Payroll
- **Topik:** Implementasi perhitungan lembur J3 & J4 untuk hari kerja normal
- **Status:** Spesifikasi sudah disepakati, implementasi belum dimulai
- **Hari libur:** Tetap pakai aturan lama (8 jam × 2×, jam ke-9 × 3×, jam ke-10–12 × 4×)
