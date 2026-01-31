# Dokumentasi Fungsi Override Jadwal Satpam

## 📋 Fungsi Tabel `t_override_jadwal_security`

Tabel ini berfungsi sebagai **log/audit trail** untuk mencatat semua perubahan jadwal shift satpam yang dilakukan secara **override** (mendesak/urgent), di luar jadwal rutin yang sudah direncanakan.

---

## 🎯 Tujuan & Manfaat

1. **Audit Trail**: Mencatat siapa, kapan, dan mengapa jadwal diubah
2. **Transparansi**: Memberikan informasi lengkap tentang perubahan jadwal
3. **Akuntabilitas**: Setiap perubahan harus ada alasan yang jelas
4. **Analisis**: Bisa dianalisis untuk melihat pola perubahan jadwal
5. **Compliance**: Memenuhi kebutuhan dokumentasi untuk audit internal

---

## 📊 Struktur Tabel

```sql
t_override_jadwal_security
├── id (PK, auto increment)
├── vcNik (VARCHAR 8) - NIK Satpam yang di-override
├── dtTanggal (DATE) - Tanggal yang di-override
├── intShiftLama (TINYINT, nullable) - Shift yang di-override (1, 2, 3, atau NULL)
├── intShiftBaru (TINYINT) - Shift baru yang ditetapkan (1, 2, 3)
├── vcAlasan (TEXT) - Alasan override (wajib diisi)
├── vcOverrideBy (VARCHAR 100) - User yang melakukan override
├── dtOverrideAt (DATETIME) - Waktu override dilakukan
└── dtCreate (DATETIME, nullable) - Waktu record dibuat
```

---

## 🔄 Skenario Penggunaan

### **Skenario 1: Ganti Shift (Change Shift)**

**Situasi:** Satpam A sudah dijadwalkan Shift 1 (06:30-14:30) pada tanggal 15 Desember, tapi ada keperluan mendesak dan perlu diganti ke Shift 2 (14:30-22:30).

**Proses:**

1. HR/Admin membuka form override
2. Pilih satpam A, tanggal 15 Desember
3. Shift Lama: 1
4. Shift Baru: 2
5. Alasan: "Satpam A ada keperluan keluarga mendesak"
6. Simpan

**Data yang Tersimpan:**

-   Di `t_jadwal_shift_security`: Record lama (Shift 1) dihapus, record baru (Shift 2) dibuat dengan flag `isOverride = true`
-   Di `t_override_jadwal_security`: Log override tersimpan dengan alasan lengkap

---

### **Skenario 2: Tambah Shift (Add Shift)**

**Situasi:** Satpam B sudah dijadwalkan OFF pada tanggal 20 Desember, tapi karena ada satpam lain sakit, perlu ditambahkan Shift 3 (22:30-06:30).

**Proses:**

1. HR/Admin membuka form override
2. Pilih satpam B, tanggal 20 Desember
3. Shift Lama: NULL (karena sebelumnya OFF, tidak ada shift)
4. Shift Baru: 3
5. Alasan: "Penggantian satpam C yang sakit"
6. Simpan

**Data yang Tersimpan:**

-   Di `t_jadwal_shift_security`: Record baru (Shift 3) dibuat dengan flag `isOverride = true`
-   Di `t_override_jadwal_security`: Log override tersimpan dengan `intShiftLama = NULL`

---

### **Skenario 3: Batalkan Shift (Cancel Shift)**

**Situasi:** Satpam C sudah dijadwalkan Shift 2 (14:30-22:30) pada tanggal 25 Desember, tapi karena ada libur nasional yang baru diumumkan, shift dibatalkan (OFF).

**Proses:**

1. HR/Admin membuka form override
2. Pilih satpam C, tanggal 25 Desember
3. Shift Lama: 2
4. Shift Baru: NULL (OFF)
5. Alasan: "Libur nasional yang baru diumumkan"
6. Simpan

**Data yang Tersimpan:**

-   Di `t_jadwal_shift_security`: Record lama (Shift 2) dihapus, record baru (OFF) dibuat dengan flag `isOverride = true`
-   Di `t_override_jadwal_security`: Log override tersimpan

---

### **Skenario 4: Emergency Coverage**

**Situasi:** Ada kejadian darurat di pabrik pada malam hari, perlu tambahan satpam untuk Shift 3 (22:30-06:30) pada tanggal yang sama.

**Proses:**

1. HR/Admin membuka form override
2. Pilih satpam D (yang seharusnya OFF), tanggal hari ini
3. Shift Lama: NULL (OFF)
4. Shift Baru: 3
5. Alasan: "Emergency coverage untuk kejadian darurat di pabrik"
6. Simpan

**Data yang Tersimpan:**

-   Di `t_jadwal_shift_security`: Record baru (Shift 3) dibuat dengan flag `isOverride = true`
-   Di `t_override_jadwal_security`: Log override tersimpan untuk audit

---

## 🔍 Perbedaan dengan Edit Normal

| Aspek             | Edit Normal (Bulk)      | Override (Urgent)                       |
| ----------------- | ----------------------- | --------------------------------------- |
| **Waktu**         | Perencanaan bulanan     | Perubahan mendesak                      |
| **Cakupan**       | Banyak satpam sekaligus | Satu satpam per override                |
| **Alasan**        | Tidak perlu alasan      | Wajib ada alasan                        |
| **Audit**         | Tidak dicatat di log    | Dicatat di `t_override_jadwal_security` |
| **Flag**          | `isOverride = false`    | `isOverride = true`                     |
| **User Tracking** | Tidak dicatat           | Dicatat siapa yang override             |

---

## 💻 Implementasi di Sistem

### **1. Controller Method: `override()`**

```php
// File: app/Http/Controllers/JadwalShiftSecurityController.php
public function override(Request $request)
{
    // Validasi input
    // Hapus jadwal lama (jika ada)
    // Insert jadwal baru dengan flag isOverride = true
    // Simpan log ke t_override_jadwal_security
}
```

### **2. Route:**

```php
Route::post('jadwal-shift-security/override', [JadwalShiftSecurityController::class, 'override'])
    ->name('jadwal-shift-security.override');
```

### **3. Modal Form:**

-   Modal form sudah ada di view `jadwal-shift-security/index.blade.php`
-   Form berisi: NIK, Tanggal, Shift Lama, Shift Baru, Alasan

---

## 📈 Manfaat untuk Analisis

Dari data di `t_override_jadwal_security`, bisa dianalisis:

1. **Frekuensi Override**: Berapa kali override dilakukan per bulan?
2. **Pola Override**: Apakah ada satpam yang sering di-override?
3. **Alasan Umum**: Alasan apa yang paling sering muncul?
4. **User Activity**: Siapa yang paling sering melakukan override?
5. **Trend**: Apakah override meningkat atau menurun?

---

## 🔐 Keamanan & Validasi

1. **Validasi Wajib:**

    - NIK harus valid (exists di m_karyawan)
    - Tanggal harus valid
    - Shift Baru harus 1, 2, atau 3
    - Alasan wajib diisi (max 500 karakter)

2. **User Tracking:**

    - Otomatis mencatat user yang melakukan override
    - Mencatat waktu override

3. **Transaction:**
    - Menggunakan database transaction
    - Jika gagal, rollback semua perubahan

---

## 📝 Contoh Data

```sql
-- Contoh record di t_override_jadwal_security
id: 1
vcNik: '19950011'
dtTanggal: '2025-12-15'
intShiftLama: 1
intShiftBaru: 2
vcAlasan: 'Satpam A ada keperluan keluarga mendesak, perlu diganti shift'
vcOverrideBy: 'admin@company.com'
dtOverrideAt: '2025-12-10 14:30:00'
dtCreate: '2025-12-10 14:30:00'
```

---

## 🎯 Kesimpulan

Tabel `t_override_jadwal_security` adalah **komponen penting** untuk:

-   ✅ Audit trail perubahan jadwal
-   ✅ Transparansi dan akuntabilitas
-   ✅ Dokumentasi perubahan urgent
-   ✅ Analisis pola perubahan jadwal
-   ✅ Compliance dan reporting

**Best Practice:**

-   Setiap perubahan jadwal yang mendesak harus melalui proses override
-   Alasan harus jelas dan detail
-   Review berkala log override untuk evaluasi

