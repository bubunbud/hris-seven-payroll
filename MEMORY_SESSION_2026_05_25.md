# Memory Session — 25 Mei 2026

**Status:** Ditunda; lanjut besok.  
**Lingkungan:** Local XAMPP, DB `hris_seven`.

---

## 1. Closing Gaji — Periode RMA tidak ada hasil / error kolom

### Gejala
- Periode: awal `07/05/2026`, akhir `21/05/2026`, gajian `01/06/2026`, divisi **RMA**, quarter **1**.
- Proses closing: 0 baris `t_closing`, periode tetap `vcStatus=1`.

### Penyebab
- Kolom DB belum ada meski migrasi tercatat **Ran** (gagal diam-diam karena `->after()`):
  - `decTunjanganJabatan`
  - `decJamLemburKerja4`, `decLemburKerja4`, `decTotallembur4`
- Log: `Unknown column 'decTunjanganJabatan'` lalu `decJamLemburKerja4`.

### Perbaikan kode (sudah)
- Migrasi: `database/migrations/2026_05_25_100000_ensure_t_closing_payroll_columns.php` (tanpa `after()`).
- `ClosingController`: tidak mark sukses jika semua karyawan gagal; pesan error lebih jelas.
- Lokal: kolom sudah ditambahkan; status periode RMA pernah di-reset ke `vcStatus=0`.

### Deploy production
```bash
php artisan migrate --path=database/migrations/2026_05_25_100000_ensure_t_closing_payroll_columns.php --force
```

---

## 2. Skema lembur closing (J1–J4) — dokumentasi

- Sumber: `ClosingController::calculateLembur()`, `LemburCalculationService`.
- **HKN:** J1 1.5× (1j), J2 2× (8j), J3 3× (1j), J4 4× (sisa). Rate = gapok bulan / 173.
- **Hari libur:** 8j×2, jam ke-9 ×3, jam 10–12 ×4.
- Simpan ke `t_closing`: `decJamLemburKerja1–4`, `decLemburKerja1–4`, `decJamLemburLibur2/3`, `decLembur2/3`, `decTotallembur1–4`, beban `decBebanTgi` dll.
- File `SKEMA_PERHITUNGAN_LEMBUR_CLOSING_GAJI.md` **belum mutakhir** (masih J1+J2 saja untuk HKN).

---

## 3. Browse Absensi — NIK 20010057 (Dedi Junaedi) tanggal 07/05/2026

### Gejala
- Filter tidak masuk: **255 baris** untuk satu tanggal (harusnya ~1).

### Penyebab data
- **255 record** `t_tidak_masuk` overlap `2026-05-07` karena **`dtTanggalSelesai = 2026-05-07`** dengan **`dtTanggalMulai` tahun 2010–2026** (import/riwayat tidak ditutup benar).
- **Bukan** dari input sakit sah: **S010**, mulai `2026-05-06`, selesai `2026-05-07` (**1 record**).
- `t_absen` tanggal 07/05/2026: **0 baris**.

### Dampak closing (periode 07/05–21/05/2026)
- Sebelum fix: `calculateHariTidakMasuk` menjumlahkan overlap → potongan absen (`I002` / `decPotonganAbsen`) membengkak.
- Setelah fix kode (hari unik): I002=**1**, S010=**1**, C010=**2** hari.

---

## 4. Perbaikan overlap — sudah diimplementasi (belum execute data)

### Kode baru
| File | Fungsi |
|------|--------|
| `app/Traits/TidakMasukOverlapHelper.php` | Hari unik + dedupe browse |
| `app/Http/Controllers/ClosingController.php` | `calculateHariTidakMasuk` pakai hari unik |
| `app/Http/Controllers/AbsenController.php` | Browse + print dedupe |
| `app/Http/Controllers/BrowseAbsensiSecurityController.php` | Idem |
| `app/Console/Commands/PerbaikiTidakMasukOverlapCommand.php` | Perbaikan data |
| `PERBAIKAN_TIDAK_MASUK_OVERLAP.md` | Panduan |

### Command data (dry-run NIK 20010057)
```bash
php artisan tidak-masuk:perbaiki-overlap --nik=20010057 --selesai=2026-05-07
# → 254 baris akan di-set dtTanggalSelesai = dtTanggalMulai (1 record sakit 06-07 Mei tetap)

php artisan tidak-masuk:perbaiki-overlap --nik=20010057 --selesai=2026-05-07 --execute
```

**Klarifikasi variabel:** `--selesai=2026-05-07` = kolom **`t_tidak_masuk.dtTanggalSelesai`** (modul **Izin Tidak Masuk**), bukan periode gajian `01/06/2026`.

### Besok — langkah lanjut (belum dikerjakan)
1. [ ] Jalankan `--execute` perbaikan data (setuju user).
2. [ ] Re-proses closing RMA + NIK terdampak periode Juni 2026.
3. [ ] Verifikasi Browse 07/05/2026 → 1 baris untuk 20010057.
4. [ ] Deploy Ubuntu: migrate kolom closing + file overlap helper + command.
5. [ ] Opsional: update `SKEMA_PERHITUNGAN_LEMBUR_CLOSING_GAJI.md` (J3/J4).

---

## 5. File penting disentuh hari ini

- `app/Http/Controllers/ClosingController.php`
- `database/migrations/2026_05_25_100000_ensure_t_closing_payroll_columns.php`
- `app/Traits/TidakMasukOverlapHelper.php`
- `app/Console/Commands/PerbaikiTidakMasukOverlapCommand.php`
- `PERBAIKAN_TIDAK_MASUK_OVERLAP.md`

---

**Untuk melanjutkan besok:** baca file ini + `PERBAIKAN_TIDAK_MASUK_OVERLAP.md`.
