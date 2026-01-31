# Memory: Sistem Absensi Satpam - 1 Desember 2025

## 📋 Ringkasan Pekerjaan Hari Ini

Implementasi lengkap sistem absensi satpam dengan fitur shift management, override jadwal, dan audit trail.

---

## 🎯 Fitur yang Telah Diimplementasikan

### 1. **Sistem Jadwal Shift Satpam**

-   Form input jadwal shift dalam format grid (baris = satpam, kolom = tanggal)
-   Support multiple shift per hari (untuk penggantian)
-   Input format: `1`, `2`, `3`, `OFF`, atau `1,2` (multiple)
-   Filter berdasarkan NIK/Nama
-   Visual indicator untuk weekend dan hari libur

### 2. **Master Shift Security**

-   CRUD lengkap untuk master shift satpam
-   3 shift default: Shift 1 (06:30-14:30), Shift 2 (14:30-22:30), Shift 3 (22:30-06:30)
-   Support cross-day shift (Shift 3 melewati tengah malam)
-   Toleransi masuk/pulang (dalam menit)
-   Durasi shift (dalam jam)

### 3. **Fitur Override Jadwal**

-   Tombol override di setiap cell jadwal
-   Modal form untuk override dengan alasan wajib
-   Visual indicator untuk jadwal yang di-override (background biru muda)
-   Logging semua override ke tabel `t_override_jadwal_security`

### 4. **List Override Jadwal**

-   Halaman untuk melihat riwayat semua override
-   Filter berdasarkan tanggal, NIK, dan Nama
-   Detail override dengan alasan lengkap
-   Pagination untuk data besar

### 5. **Integrasi dengan Browse Absensi**

-   Kolom "Shift Terjadwal" dan "Shift Aktual" untuk Security
-   Status validasi: Sesuai, Tidak Sesuai, Tidak Masuk, Tidak Ada Jadwal
-   Mapping otomatis absensi ke shift berdasarkan jam masuk/pulang

---

## 🗄️ Struktur Database

### Tabel `m_shift_security`

```sql
- vcKodeShift (PK, TINYINT): 1, 2, 3
- vcNamaShift (VARCHAR 20): Nama shift
- dtJamMasuk (TIME): Jam masuk shift
- dtJamPulang (TIME): Jam pulang shift
- isCrossDay (BOOLEAN): Apakah shift melewati tengah malam
- intDurasiJam (DECIMAL 4,2): Durasi shift dalam jam
- intToleransiMasuk (INT): Toleransi terlambat dalam menit
- intToleransiPulang (INT): Toleransi pulang cepat dalam menit
- vcKeterangan (VARCHAR 100): Keterangan shift
- dtCreate, dtChange (DATETIME)
```

### Tabel `t_jadwal_shift_security`

```sql
- id (PK, BIGINT)
- vcNik (VARCHAR 8, FK ke m_karyawan.Nik)
- dtTanggal (DATE): Tanggal jadwal
- intShift (TINYINT, nullable): 1, 2, 3, atau NULL untuk OFF
- vcKeterangan (VARCHAR 50, nullable): OFF, Libur Nasional, dll
- isOverride (BOOLEAN): Flag apakah jadwal di-override
- vcOverrideBy (VARCHAR 100, nullable): User yang override
- dtOverrideAt (DATETIME, nullable): Waktu override
- dtCreate, dtChange (DATETIME)
- Index: (vcNik, dtTanggal), dtTanggal
```

**Catatan Penting:**

-   `intShift` bisa NULL untuk kasus "OFF"
-   Bisa multiple record per NIK per tanggal (untuk multiple shift)
-   `isOverride = true` menandakan jadwal diubah secara urgent

### Tabel `t_override_jadwal_security`

```sql
- id (PK, BIGINT)
- vcNik (VARCHAR 8, FK ke m_karyawan.Nik)
- dtTanggal (DATE): Tanggal yang di-override
- intShiftLama (TINYINT, nullable): Shift yang di-override (bisa NULL)
- intShiftBaru (TINYINT): Shift baru
- vcAlasan (TEXT): Alasan override (wajib, min 10 karakter, max 500)
- vcOverrideBy (VARCHAR 100): User yang override
- dtOverrideAt (DATETIME): Waktu override
- dtCreate (DATETIME, nullable)
- Index: (vcNik, dtTanggal)
```

**Fungsi:** Audit trail untuk semua perubahan jadwal yang dilakukan secara override.

---

## 📁 File yang Dibuat/Dimodifikasi

### Migrations

1. `2025_12_01_063526_create_m_shift_security_table.php`
2. `2025_12_01_063527_create_t_jadwal_shift_security_table.php`
3. `2025_12_01_063529_create_t_override_jadwal_security_table.php`
4. `2025_12_01_074715_update_t_jadwal_shift_security_allow_null_shift.php` (allow NULL untuk OFF)

### Models

1. `app/Models/ShiftSecurity.php`
2. `app/Models/JadwalShiftSecurity.php`
3. `app/Models/OverrideJadwalSecurity.php`

### Controllers

1. `app/Http/Controllers/JadwalShiftSecurityController.php`

    - `index()`: Tampilkan form grid jadwal
    - `store()`: Simpan jadwal bulk
    - `override()`: Override jadwal urgent
    - `getJadwalByPeriode()`: Get jadwal untuk periode tertentu

2. `app/Http/Controllers/MasterShiftSecurityController.php`

    - CRUD lengkap untuk master shift

3. `app/Http/Controllers/OverrideJadwalSecurityController.php`

    - `index()`: List override dengan filter
    - `show()`: Detail override

4. `app/Http/Controllers/AbsenController.php` (DIMODIFIKASI)
    - Tambah logic mapping shift untuk Security
    - Integrasi dengan jadwal shift security

### Services

1. `app/Services/SecurityAbsensiService.php`
    - `determineShiftFromTime()`: Tentukan shift dari jam masuk/pulang
    - `validateAbsensiVsJadwal()`: Validasi absensi vs jadwal

### Views

1. `resources/views/jadwal-shift-security/index.blade.php`

    - Form grid input jadwal
    - Filter NIK/Nama
    - Tombol override di setiap cell
    - Modal override

2. `resources/views/master/shift-security/index.blade.php`

    - List master shift

3. `resources/views/master/shift-security/create.blade.php`

    - Form tambah shift

4. `resources/views/master/shift-security/edit.blade.php`

    - Form edit shift

5. `resources/views/override-jadwal-security/index.blade.php`

    - List override dengan filter

6. `resources/views/override-jadwal-security/show.blade.php`

    - Detail override

7. `resources/views/absen/index.blade.php` (DIMODIFIKASI)

    - Tambah kolom "Shift Terjadwal" dan "Shift Aktual"

8. `resources/views/layouts/app.blade.php` (DIMODIFIKASI)
    - Tambah menu: Master Shift Security, List Override Jadwal

### Seeders

1. `database/seeders/ShiftSecuritySeeder.php`
    - Seed data default: Shift 1, 2, 3

### Routes

-   `routes/web.php` (DIMODIFIKASI)
    -   Route untuk jadwal shift security
    -   Resource route untuk master shift security
    -   Route untuk list override

---

## 🔧 Logic & Konsep Penting

### 1. Mapping Absensi ke Shift

**Service:** `SecurityAbsensiService::determineShiftFromTime()`

**Logic:**

-   Ambil jam masuk dan jam pulang dari `t_absen`
-   Bandingkan dengan jam shift di `m_shift_security`
-   Gunakan toleransi untuk matching (±30 menit default)
-   Handle cross-day shift (Shift 3: 22:30-06:30)

**Contoh:**

-   Masuk: 22:11:39, Pulang: 06:50:42 → **Shift 3**
-   Masuk: 06:32:30, Pulang: 14:39:27 → **Shift 1**
-   Masuk: 14:12:40, Pulang: 22:33:18 → **Shift 2**

### 2. Validasi Absensi vs Jadwal

**Service:** `SecurityAbsensiService::validateAbsensiVsJadwal()`

**Status:**

-   `sesuai`: Shift aktual ada di jadwal
-   `tidak_sesuai`: Shift aktual tidak ada di jadwal
-   `tidak_masuk`: Ada jadwal tapi tidak ada absensi
-   `tidak_ada_jadwal`: Tidak ada jadwal untuk tanggal tersebut

### 3. Penyimpanan "OFF"

**Konsep:**

-   "OFF" disimpan sebagai record dengan `intShift = NULL` dan `vcKeterangan = 'OFF'`
-   Migration `2025_12_01_074715` mengubah `intShift` menjadi nullable
-   JavaScript mengirim flag `isOff: true` untuk kasus OFF

### 4. Override Jadwal

**Flow:**

1. User klik tombol override di cell jadwal
2. Modal terbuka dengan data pre-filled
3. User pilih Shift Lama (optional) dan Shift Baru (required)
4. User isi alasan (min 10 karakter, max 500)
5. Submit → Hapus jadwal lama (jika ada) → Insert jadwal baru dengan `isOverride = true`
6. Simpan log ke `t_override_jadwal_security`

**Validasi:**

-   Shift Baru wajib dipilih
-   Alasan wajib diisi (min 10, max 500 karakter)
-   Konfirmasi sebelum submit

---

## 🎨 UI/UX Features

### 1. Grid Jadwal Shift

-   Sticky header dan kolom pertama (Nama Satpam)
-   Highlight weekend (kuning) dan hari libur (merah)
-   Input cell dengan placeholder dan tooltip
-   Tombol override di setiap cell
-   Badge "Override" untuk jadwal yang di-override

### 2. Filter

-   Filter bulan dan tahun (auto-submit)
-   Filter NIK/Nama dengan tombol search dan clear
-   Badge info menampilkan jumlah satpam yang difilter

### 3. Visual Indicators

-   Background biru muda untuk cell yang di-override
-   Badge warna untuk shift (primary, success, warning)
-   Icon untuk berbagai status

---

## 🔐 Validasi & Keamanan

### Validasi Input Jadwal

-   Format: `1`, `2`, `3`, `OFF`, atau `1,2` (multiple)
-   Auto-normalize: sort dan remove duplicate
-   Validasi di frontend (JavaScript) dan backend (Laravel)

### Validasi Override

-   NIK harus exists di `m_karyawan`
-   Shift Baru harus 1, 2, atau 3
-   Alasan wajib (min 10, max 500 karakter)
-   User tracking otomatis (dari auth user)

### Validasi Master Shift

-   Kode Shift harus 1, 2, atau 3
-   Kode Shift unique
-   Tidak bisa hapus jika sudah digunakan di jadwal

---

## 📊 Data Flow

### Input Jadwal

```
User Input Grid → JavaScript Collect → JSON → POST /jadwal-shift-security/store
→ Delete jadwal lama periode → Insert jadwal baru → Response JSON → Reload
```

### Override Jadwal

```
User Klik Override → Modal Open → Fill Form → POST /jadwal-shift-security/override
→ Delete jadwal lama (jika ada) → Insert jadwal baru (isOverride=true)
→ Insert log ke t_override_jadwal_security → Response JSON → Reload
```

### Browse Absensi

```
Filter → Query t_absen + Join m_karyawan + Left Join t_jadwal_shift_security
→ For Security: Determine shift from time → Validate vs jadwal
→ Display dengan kolom shift terjadwal/aktual
```

---

## 🐛 Issues yang Sudah Diatasi

1. **Error: Table not found**

    - **Solusi:** Run migration untuk tabel baru

2. **Error: Foreign key constraint**

    - **Solusi:** Hapus foreign key constraint sementara (bisa ditambahkan nanti jika perlu)

3. **Input "OFF" hilang setelah simpan**

    - **Solusi:** Ubah `intShift` menjadi nullable, simpan OFF dengan `intShift = NULL` dan `vcKeterangan = 'OFF'`

4. **Route parameter mismatch**
    - **Solusi:** Gunakan `$master_shift_security` sebagai parameter di controller

---

## 📝 Catatan Penting

1. **Data Absensi:** Tetap diambil dari `t_absen` (tidak perlu tabel terpisah untuk Security)

2. **Perhitungan Gaji:** Security menggunakan logic yang sama dengan Operator

3. **Multiple Shift:** Satu satpam bisa punya multiple shift dalam 1 hari (untuk penggantian)

4. **Cross-Day Shift:** Shift 3 (22:30-06:30) melewati tengah malam, perlu handling khusus

5. **Filter Default:** List Override default menampilkan 30 hari terakhir

6. **Menu Location:** Semua menu baru berada di Sidebar → Absensi

---

## 🚀 Cara Deploy ke Server

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

### 4. Set Permissions (jika perlu)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 📚 Referensi File Dokumentasi

1. `IMPLEMENTASI_ABSENSI_SATPAM.md` - Dokumentasi implementasi lengkap
2. `DOKUMENTASI_OVERRIDE_JADWAL_SATPAM.md` - Dokumentasi fungsi override

---

## ✅ Testing Checklist

### Master Shift Security

-   [x] List shift
-   [x] Tambah shift baru
-   [x] Edit shift
-   [x] Hapus shift (dengan validasi)

### Jadwal Shift Satpam

-   [x] Input jadwal di grid
-   [x] Simpan jadwal bulk
-   [x] Input "OFF" tersimpan
-   [x] Multiple shift per hari
-   [x] Filter NIK/Nama
-   [x] Visual indicator weekend/libur

### Override Jadwal

-   [x] Buka modal override
-   [x] Submit override dengan alasan
-   [x] Visual indicator jadwal di-override
-   [x] Log tersimpan di database

### List Override

-   [x] List semua override
-   [x] Filter tanggal/NIK/Nama
-   [x] Detail override
-   [x] Pagination

### Integrasi Browse Absensi

-   [x] Kolom shift muncul untuk Security
-   [x] Status validasi tampil
-   [x] Mapping shift otomatis

---

## 🎯 Next Steps (Jika Diperlukan)

1. **Excel Import:** Fitur import jadwal dari Excel
2. **Copy Jadwal:** Fitur copy jadwal bulan sebelumnya
3. **Report:** Laporan jadwal shift per periode
4. **Notification:** Notifikasi untuk jadwal yang tidak sesuai
5. **Dashboard:** Dashboard statistik override

---

**Status:** ✅ Semua fitur utama sudah selesai dan berfungsi dengan baik

**Tanggal:** 1 Desember 2025

**Catatan:** Sistem absensi satpam sudah lengkap dan siap digunakan. Semua fitur telah diuji dan berfungsi dengan baik.

