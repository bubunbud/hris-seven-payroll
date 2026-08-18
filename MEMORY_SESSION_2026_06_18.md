# Memory Session — 18 Juni 2026

**Status:** Ditunda; lanjut nanti.  
**Lingkungan:** Local XAMPP, DB `hris_seven`.  
**Fokus hari ini:** Modul **Tarik Data Absensi API** (selesai develop + uji lokal + panduan deploy).

**Konteks sesi sebelumnya:** lihat `MEMORY_SESSION_2026_05_25.md` (closing RMA, overlap tidak masuk, fix delete/update TidakMasuk).

---

## 1. Modul baru: Tarik Data Absensi API

### Ringkasan
- Menu: **Settings → Tarik Data Absensi API**
- Login API: `POST /v1/auth/login`
- Data: `GET /v1/management/attendances/logs` (paginated, `page_size` max ~100)
- Target DB: `t_absen` (upsert per `dtTanggal` + `vcNik`)
- Kredensial: `.env` → `HRIS_API_BASE_URL`, `HRIS_API_USERNAME`, `HRIS_API_PASSWORD`

### Permission
- Slug: `view-tarik-data-absensi-api`
- Seeder: `UpdatePermissionsSeeder` (Admin otomatis dilampirkan)

### Aturan bisnis (versi final setelah revisi user)

| Aspek | Perilaku |
|-------|----------|
| Field ditarik | `date`→`dtTanggal`, `nik`→`vcNik`, `clock_in`→`dtJamMasuk`, `clock_out`→`dtJamKeluar` |
| **Tidak ditarik** | `note`, `shift` → **tidak** disimpan ke `vcketerangan` |
| Insert | Record baru jika tanggal+NIK belum ada di `t_absen` |
| Update | **Hanya** mengisi `dtJamMasuk` / `dtJamKeluar` yang **masih kosong** di DB |
| Lewati (jam lengkap) | Jika jam masuk & pulang di DB sudah terisi → tidak di-overwrite |
| Lewati (tanpa jam API) | Jika `clock_in` dan `clock_out` dari API keduanya kosong |
| Lewati (NIK) | Jika checkbox aktif & NIK tidak ada di `m_karyawan` |

### Tampilan hasil tarik
- Ringkasan: Total API, Insert, Update, Lewati, Error
- Ringkasan alasan lewati (per kategori)
- **Tabel daftar dilewati** — **tidak** memuat record alasan *"Jam masuk & pulang di database sudah terisi"* (hanya di ringkasan angka)
- **Tabel daftar error** (sampai 100 baris)

### Kategori "Lewati" — penjelasan untuk user awam

| Kode | Label | Arti |
|------|-------|------|
| `nik_tidak_di_master` | NIK tidak ada di master | NIK API tidak dikenal di `m_karyawan` |
| `data_tidak_lengkap` | Data tidak lengkap | Tanpa tanggal atau NIK |
| `format_tidak_valid` | Format tidak valid | Baris API bukan array |
| `tidak_ada_jam_dari_api` | API tidak mengirim jam | Record ada di API, tapi `clock_in` & `clock_out` kosong — **bukan error koneksi** |
| `jam_sudah_lengkap` | Jam sudah lengkap | DB sudah punya jam masuk & pulang; sengaja tidak ditimpa |

---

## 2. Uji lokal user — periode 17–18 Juni 2026

### Hasil pertama (sebelum revisi)
```
Total API: 7052 | Insert: 4071 | Update: 2572 | Lewati: 204 | Error: 205
```

### Penyebab 205 error (sudah dianalisis)
- **Semua** error: `Data too long for column 'vcketerangan'`
- Kolom DB: `vcketerangan` = **`varchar(25)`** saja
- Modul awal menyimpan `note | Shift: X` → melebihi 25 karakter
- **Solusi final:** tidak tarik `note`/`shift` sama sekali (bukan perluas kolom)

### Catatan volume data
- 7052 baris API untuk 2 hari = banyak baris per NIK/tanggal dari API; lokal tetap 1 baris per tanggal+NIK (duplikat API → update, bukan insert baru).

### Setelah revisi
- User diminta **tarik ulang**; error `vcketerangan` seharusnya **0**
- Belum dikonfirmasi hasil tarik ulang pasca-revisi final di sesi ini

---

## 3. File modul (deploy ke Ubuntu)

### File baru
```
app/Services/HrisApiAttendanceLogService.php
app/Http/Controllers/TarikDataAbsensiApiController.php
resources/views/tarik-data-absensi-api/index.blade.php
```

### File update
```
config/hris_api.php                    ← attendance_logs_path
routes/web.php
resources/views/layouts/app.blade.php
resources/views/absen/layouts/app.blade.php
database/seeders/UpdatePermissionsSeeder.php
database/seeders/RolePermissionSeeder.php   (opsional fresh install)
```

### Prasyarat (jika modul API feeder lain belum ada)
```
app/Services/HrisApiHttpFactory.php
app/Services/HrApiOutboundInspector.php
```

### Routes
- `GET  tarik-data-absensi-api` → `tarik-data-absensi-api.index`
- `POST tarik-data-absensi-api/pull` → `tarik-data-absensi-api.pull`

### Panduan deploy
- **`DEPLOY_TARIK_DATA_ABSENSI_API_UBUNTU.md`** — lengkap: SCP, .env, seeder, cache, checklist, troubleshooting

### Deploy singkat
```bash
php artisan db:seed --class=UpdatePermissionsSeeder
php artisan config:clear cache:clear route:clear view:clear
php artisan config:cache route:cache view:cache
```

**Tidak ada migration** untuk modul ini.

---

## 4. Topik lain (dari sesi sebelumnya — belum selesai)

Masih terbuka dari `MEMORY_SESSION_2026_05_25.md`:

1. [ ] `php artisan tidak-masuk:perbaiki-overlap --nik=20010057 --selesai=2026-05-07 --execute`
2. [ ] Re-proses closing periode RMA / NIK terdampak
3. [ ] Deploy production: migrasi kolom `t_closing` + overlap helper + fix `TidakMasuk` delete/update
4. [ ] Update `SKEMA_PERHITUNGAN_LEMBUR_CLOSING_GAJI.md` (J3/J4 HKN)

### Fix bug Izin Tidak Masuk (siap deploy, dari sesi lalu)
- `app/Models/TidakMasuk.php` — `scopeCompositeKey()`
- `app/Http/Controllers/TidakMasukController.php` — delete/update pakai composite key

---

## 5. Langkah lanjut (saat melanjutkan)

1. [ ] **Deploy** modul Tarik Data Absensi API ke Ubuntu (`DEPLOY_TARIK_DATA_ABSENSI_API_UBUNTU.md`)
2. [ ] **Tarik ulang** 17–18 Juni 2026 setelah revisi → konfirmasi Error = 0
3. [ ] Verifikasi aturan: tidak ubah `vcketerangan`, update hanya jam kosong
4. [ ] (Opsional) Export Excel daftar lewati/error — pernah ditawarkan, belum diminta
5. [ ] Lanjut item pending sesi 25 Mei (overlap, closing, skema lembur)

---

## 6. API & env referensi

```env
HRIS_API_BASE_URL=https://hris-api.abadinusagroup.com
HRIS_API_USERNAME=superadmin
HRIS_API_PASSWORD=<isi di .env, jangan commit>
# HRIS_API_ATTENDANCE_LOGS_PATH=/v1/management/attendances/logs
```

Struktur kolom `t_absen` relevan:
- `vcketerangan` = `varchar(25)` — modul API **tidak menulis** kolom ini
- PK logis: `dtTanggal` + `vcNik`

---

**Untuk melanjutkan nanti:** baca file ini + `DEPLOY_TARIK_DATA_ABSENSI_API_UBUNTU.md` + `MEMORY_SESSION_2026_05_25.md`.
