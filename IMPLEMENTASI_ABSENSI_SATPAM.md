# Dokumentasi Implementasi Sistem Absensi Satpam

## 📋 Ringkasan

Sistem absensi satpam dengan fitur:

-   Form input jadwal shift (grid seperti spreadsheet)
-   Mapping absensi ke shift berdasarkan jam masuk/pulang
-   Validasi absensi vs jadwal
-   Browse absensi gabung (Security + non-Security)
-   Fitur override jadwal untuk kasus urgent

---

## 🗄️ Struktur Database

### 1. Tabel `m_shift_security` (Master Shift)

```sql
- vcKodeShift (PK): 1, 2, 3
- vcNamaShift: Shift 1, Shift 2, Shift 3
- dtJamMasuk: 06:30, 14:30, 22:30
- dtJamPulang: 14:30, 22:30, 06:30
- isCrossDay: false, false, true
- intDurasiJam: 8.00
- intToleransiMasuk: 30 (menit)
- intToleransiPulang: 30 (menit)
```

### 2. Tabel `t_jadwal_shift_security` (Jadwal Shift)

```sql
- id (PK, auto increment)
- vcNik (FK ke m_karyawan)
- dtTanggal (date)
- intShift: 1, 2, 3
- vcKeterangan: nullable
- isOverride: boolean (default false)
- vcOverrideBy: nullable
- dtOverrideAt: nullable
- dtCreate, dtChange
```

**Catatan:** Bisa multiple shift per hari (untuk kasus penggantian)

### 3. Tabel `t_override_jadwal_security` (Log Override)

```sql
- id (PK, auto increment)
- vcNik (FK ke m_karyawan)
- dtTanggal (date)
- intShiftLama: nullable
- intShiftBaru: required
- vcAlasan: text
- vcOverrideBy: required
- dtOverrideAt: datetime
- dtCreate
```

---

## 📁 File yang Dibuat/Dimodifikasi

### Migration Files

-   `database/migrations/2025_12_01_063526_create_m_shift_security_table.php`
-   `database/migrations/2025_12_01_063527_create_t_jadwal_shift_security_table.php`
-   `database/migrations/2025_12_01_063529_create_t_override_jadwal_security_table.php`

### Models

-   `app/Models/ShiftSecurity.php`
-   `app/Models/JadwalShiftSecurity.php`
-   `app/Models/OverrideJadwalSecurity.php`

### Controllers

-   `app/Http/Controllers/JadwalShiftSecurityController.php` (BARU)
-   `app/Http/Controllers/AbsenController.php` (DIMODIFIKASI)

### Services

-   `app/Services/SecurityAbsensiService.php` (BARU)

### Views

-   `resources/views/jadwal-shift-security/index.blade.php` (BARU)
-   `resources/views/absen/index.blade.php` (DIMODIFIKASI - tambah kolom shift)

### Seeders

-   `database/seeders/ShiftSecuritySeeder.php`

### Routes

-   `routes/web.php` (DIMODIFIKASI - tambah route jadwal shift security)

---

## 🚀 Cara Menjalankan

### 1. Run Migration

```bash
php artisan migrate
```

### 2. Seed Master Shift

```bash
php artisan db:seed --class=ShiftSecuritySeeder
```

### 3. Clear Cache

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📝 Fitur yang Tersedia

### 1. Form Input Jadwal Shift

**URL:** `/jadwal-shift-security`

**Fitur:**

-   Grid interaktif (baris = satpam, kolom = tanggal)
-   Input per cell: 1, 2, 3, OFF, atau "1,2" (multiple shift)
-   Highlight weekend dan hari libur
-   Total shift per satpam (kolom terakhir)
-   Validasi format input
-   Simpan bulk (semua jadwal sekaligus)

**Cara Pakai:**

1. Pilih bulan dan tahun
2. Isi jadwal di grid (klik cell untuk edit)
3. Format: `1` = Shift 1, `2` = Shift 2, `3` = Shift 3, `OFF` = Lepas
4. Multiple shift: `1,2` = Shift 1 dan Shift 2 (penggantian)
5. Klik "Simpan Jadwal"

### 2. Browse Absensi (Gabung Security + Non-Security)

**URL:** `/absen`

**Fitur Baru:**

-   Kolom "Shift Terjadwal" (untuk Security)
-   Kolom "Shift Aktual" (untuk Security)
-   Status validasi: Sesuai / Tidak sesuai / Tidak masuk

**Tampilan:**

-   Security: Menampilkan shift terjadwal dan shift aktual
-   Non-Security: Kolom shift kosong (normal)

### 3. Override Jadwal (Urgent)

**Fitur:**

-   Override jadwal untuk kasus urgent
-   Log override tersimpan di `t_override_jadwal_security`
-   Modal form untuk input alasan override

---

## 🔧 Logic Mapping Shift

### Shift 1: 06:30 - 14:30

-   **Toleransi Masuk:** 06:00 - 08:00
-   **Toleransi Pulang:** 14:00 - 15:00

### Shift 2: 14:30 - 22:30

-   **Toleransi Masuk:** 14:00 - 15:00
-   **Toleransi Pulang:** 22:00 - 23:00

### Shift 3: 22:30 - 06:30 (Cross-Day)

-   **Toleransi Masuk:** >= 22:00 atau <= 07:00 (hari berikutnya)
-   **Toleransi Pulang:** <= 07:00 (hari berikutnya)

**Contoh:**

-   Masuk: 22:11:39 (28 Nov) → Pulang: 06:50:42 (29 Nov) = **Shift 3**
-   Masuk: 06:32:30 (28 Nov) → Pulang: 14:39:27 (28 Nov) = **Shift 1**
-   Masuk: 14:12:40 (28 Nov) → Pulang: 22:33:18 (28 Nov) = **Shift 2**

---

## ✅ Validasi Absensi vs Jadwal

**Status Validasi:**

1. **Sesuai:** Shift aktual ada di jadwal
2. **Tidak Sesuai:** Shift aktual tidak ada di jadwal
3. **Tidak Masuk:** Ada jadwal tapi tidak ada absensi
4. **Tidak Ada Jadwal:** Tidak ada jadwal untuk tanggal tersebut

---

## 🎯 Testing Checklist

### 1. Form Input Jadwal

-   [ ] Buka `/jadwal-shift-security`
-   [ ] Pilih bulan dan tahun
-   [ ] Input jadwal di grid (1, 2, 3, OFF)
-   [ ] Test multiple shift (1,2)
-   [ ] Simpan jadwal
-   [ ] Verifikasi data tersimpan di database

### 2. Browse Absensi

-   [ ] Buka `/absen`
-   [ ] Filter tanggal tertentu
-   [ ] Verifikasi kolom "Shift Terjadwal" muncul untuk Security
-   [ ] Verifikasi kolom "Shift Aktual" muncul untuk Security
-   [ ] Verifikasi status validasi (Sesuai/Tidak sesuai)

### 3. Mapping Shift

-   [ ] Test absensi Shift 1 (06:30-14:30)
-   [ ] Test absensi Shift 2 (14:30-22:30)
-   [ ] Test absensi Shift 3 (22:30-06:30 cross-day)
-   [ ] Verifikasi shift terdeteksi dengan benar

### 4. Validasi

-   [ ] Test absensi sesuai jadwal
-   [ ] Test absensi tidak sesuai jadwal
-   [ ] Test tidak masuk (ada jadwal, tidak ada absensi)
-   [ ] Test tidak ada jadwal

---

## 📌 Catatan Penting

1. **Data Absensi:** Tetap diambil dari `t_absen` (tidak perlu tabel terpisah)
2. **Perhitungan Gaji:** Security menggunakan logic yang sama dengan Operator
3. **Multiple Shift:** Satu satpam bisa punya multiple shift dalam 1 hari (untuk penggantian)
4. **Cross-Day Shift:** Shift 3 melewati tengah malam, perlu handling khusus

---

## 🔍 Troubleshooting

### Error: Table not found

```bash
php artisan migrate
```

### Error: Class not found

```bash
php artisan optimize:clear
composer dump-autoload
```

### Jadwal tidak muncul di browse

-   Pastikan sudah input jadwal di form
-   Pastikan `Group_pegawai = 'Security'` di `m_karyawan`
-   Check query di `AbsenController`

### Shift tidak terdeteksi

-   Check jam masuk/pulang di `t_absen`
-   Check logic di `SecurityAbsensiService::determineShiftFromTime()`
-   Pastikan format waktu benar (HH:mm:ss)

---

**Status:** ✅ Siap untuk evaluasi

