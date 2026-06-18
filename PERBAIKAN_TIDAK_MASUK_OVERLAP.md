# Perbaikan Overlap Izin Tidak Masuk

**Masalah:** Banyak record `t_tidak_masuk` lama dengan `dtTanggalSelesai` sama (mis. `2026-05-07`) sehingga:
- Browse Absensi menampilkan ratusan baris per satu tanggal
- Closing Gaji menjumlahkan hari overlap berkali-kali → **potongan absen (`decPotonganAbsen`) membengkak**

**Contoh:** NIK `20010057` — 255 record overlap tanggal 07/05/2026; input sah Sakit S010 `06/05–07/05/2026` hanya **1 baris**.

---

## 1. Perbaikan kode (sudah diterapkan)

| File | Perubahan |
|------|-----------|
| `app/Traits/TidakMasukOverlapHelper.php` | Hitung **hari unik** + expand browse **1 baris per NIK+tanggal** |
| `app/Http/Controllers/ClosingController.php` | `calculateHariTidakMasuk()` pakai hari unik |
| `app/Http/Controllers/AbsenController.php` | Browse + cetak pakai deduplicate |
| `app/Http/Controllers/BrowseAbsensiSecurityController.php` | Idem |

**Dampak closing** (periode 07/05–21/05/2026, NIK 20010057, setelah fix kode):
- I002 (izin pribadi / potongan absen): **1 hari** (bukan ratusan)
- S010: **1 hari**
- C010: **2 hari**

---

## 2. Perbaikan data (disarankan)

Command:

```bash
# Cek dulu (dry-run)
php artisan tidak-masuk:perbaiki-overlap --nik=20010057 --selesai=2026-05-07

# Terapkan: set dtTanggalSelesai = dtTanggalMulai untuk riwayat lama
php artisan tidak-masuk:perbaiki-overlap --nik=20010057 --selesai=2026-05-07 --execute

# Semua NIK yang pola sama (min 5 record selesai identik)
php artisan tidak-masuk:perbaiki-overlap --selesai=2026-05-07 --execute
```

**Aturan:** Record dengan `dtTanggalMulai` lebih dari **14 hari** sebelum `dtTanggalSelesai` → `dtTanggalSelesai` di-set = `dtTanggalMulai`.  
Record aktif (mis. sakit **06/05–07/05/2026**) **tidak** diubah.

---

## 3. Setelah deploy

1. Jalankan perbaikan data (jika perlu).
2. **Re-proses closing gaji** periode yang sudah salah (reset `vcStatus` periode lalu proses ulang).
3. Cek Browse Absensi NIK 20010057 tanggal 07/05/2026 → seharusnya **1 baris** tidak masuk (Sakit).

---

**Last updated:** 25 Mei 2026
