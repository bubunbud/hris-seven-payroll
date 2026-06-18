# 📝 Update: Statistik Absensi - Logika Perhitungan Jumlah Jam Izin Pulang Cepat

**Tanggal:** 12 Januari 2026  
**Fitur:** Statistik Absensi

---

## 🎯 Ringkasan

Update logika perhitungan **"Jumlah Jam Izin Pulang Cepat"** di halaman Statistik Absensi.

**Logika Baru:**
- **Jumlah Jam Izin Pulang Cepat** = **Jam Pulang Shift** - **Jam Sampai** (dari izin)

**Contoh:**
- Jam Pulang Shift: `17:00`
- Jam Sampai (izin): `15:00`
- **Jumlah Jam Izin Pulang Cepat:** `17:00 - 15:00 = 2 jam`

---

## 📋 Perubahan yang Dilakukan

### **Controller - Method `index()`**

**File:** `app/Http/Controllers/StatistikAbsensiController.php`

**Perubahan:**

#### **Sebelumnya:**
- Perhitungan menggunakan `dtDari` sampai `dtSampai` (sama seperti izin biasa)
- Untuk "Pulang Cepat", `dtDari` bisa `null`, sehingga perhitungan tidak akurat

#### **Sekarang:**
- Perhitungan khusus untuk "Pulang Cepat":
  - Ambil **Jam Pulang Shift** dari `shift_pulang` (sudah di-join di query)
  - Ambil **Jam Sampai** dari `dtSampai` (dari izin)
  - Hitung selisih: **Jam Pulang Shift - Jam Sampai**
  - Konversi ke jam (dalam format desimal, contoh: 2.00 jam)

**Logic Code:**
```php
// PC (Pulang Cepat): Tipe = "Pulang Cepat"
// Logika: Jam Pulang Shift - Jam Sampai (dari izin)
if ($tipeIzin === 'Pulang Cepat') {
    $pulangCepatPerNikIzin[$iz->vcNik] = ($pulangCepatPerNikIzin[$iz->vcNik] ?? 0) + 1;
    
    // Hitung jam izin pulang cepat: Jam Pulang Shift - Jam Sampai
    $jamPulangCepat = 0.0;
    $shiftPulang = $iz->shift_pulang ? substr((string) $iz->shift_pulang, 0, 5) : null;
    $jamSampai = $iz->dtSampai ? substr((string) $iz->dtSampai, 0, 5) : null;
    
    if ($shiftPulang && $jamSampai) {
        $tanggal = $iz->dtTanggal instanceof Carbon ? $iz->dtTanggal->copy() : Carbon::parse($iz->dtTanggal);
        $tShiftPulang = $tanggal->copy()->setTimeFromTimeString($shiftPulang);
        $tSampai = $tanggal->copy()->setTimeFromTimeString($jamSampai);
        
        // Hitung selisih dalam menit, lalu konversi ke jam
        if ($tSampai->lessThan($tShiftPulang)) {
            $menit = $tSampai->diffInMinutes($tShiftPulang, true);
            $jamPulangCepat = round($menit / 60, 2);
        }
    }
    
    // Total jam izin pulang cepat (PC)
    $izinPulangCepatJamPerNik[$iz->vcNik] = ($izinPulangCepatJamPerNik[$iz->vcNik] ?? 0) + $jamPulangCepat;
}
```

---

## 🧪 Testing

### **Test Case 1: Izin Pulang Cepat - Normal**

**Data:**
- NIK: `12345`
- Tanggal: `2026-01-15` (hari kerja)
- Jenis Izin: `Z003` (Pribadi)
- Tipe: `Pulang Cepat`
- Jam Pulang Shift: `17:00`
- Jam Sampai (izin): `15:00`

**Expected Result:**
- ✅ Jumlah Jam Izin Pulang Cepat = `2.00` jam
- ✅ Card "Jumlah Jam Izin Pulang Cepat" menampilkan `2.00`

### **Test Case 2: Izin Pulang Cepat - Multiple**

**Data:**
- Izin 1: Jam Pulang Shift `17:00`, Jam Sampai `15:00` → 2 jam
- Izin 2: Jam Pulang Shift `17:00`, Jam Sampai `16:00` → 1 jam

**Expected Result:**
- ✅ Total Jumlah Jam Izin Pulang Cepat = `3.00` jam (2 + 1)

### **Test Case 3: Izin Pulang Cepat - Tanpa Shift**

**Data:**
- NIK tanpa shift (shift_pulang = null)
- Tipe: `Pulang Cepat`

**Expected Result:**
- ✅ Jumlah Jam Izin Pulang Cepat = `0.00` jam (tidak dihitung karena tidak ada shift)

### **Test Case 4: Izin Pulang Cepat - Jam Sampai > Jam Pulang Shift**

**Data:**
- Jam Pulang Shift: `17:00`
- Jam Sampai: `18:00` (tidak masuk akal untuk pulang cepat)

**Expected Result:**
- ✅ Jumlah Jam Izin Pulang Cepat = `0.00` jam (tidak dihitung karena tidak valid)

### **Test Case 5: Izin Biasa (Tidak Berubah)**

**Data:**
- Tipe: `Izin Biasa` atau kosong

**Expected Result:**
- ✅ Perhitungan tetap menggunakan logika lama (dtDari sampai dtSampai)
- ✅ Tidak terpengaruh perubahan logika Pulang Cepat

---

## 📝 File yang Diubah

1. ✅ `app/Http/Controllers/StatistikAbsensiController.php`
   - Update logic perhitungan "Jumlah Jam Izin Pulang Cepat" di method `index()`
   - Line: ~552-577

---

## ✅ Checklist Deployment

- [x] Update logic perhitungan di controller
- [ ] **Test di local:**
  - [ ] Test Case 1: Izin Pulang Cepat - Normal
  - [ ] Test Case 2: Izin Pulang Cepat - Multiple
  - [ ] Test Case 3: Izin Pulang Cepat - Tanpa Shift
  - [ ] Test Case 4: Izin Pulang Cepat - Jam Sampai > Jam Pulang Shift
  - [ ] Test Case 5: Izin Biasa (tidak berubah)
- [ ] **Deploy ke production server**

---

## 🚀 Deployment Steps

1. **Copy File:**
   - `app/Http/Controllers/StatistikAbsensiController.php`

2. **Clear Cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Test:**
   - Buka halaman Statistik Absensi
   - Verifikasi Card "Jumlah Jam Izin Pulang Cepat"
   - Pastikan perhitungan sesuai dengan logika baru

---

## ⚠️ Catatan Penting

1. **Kondisi Valid:**
   - Hanya dihitung jika `shift_pulang` dan `dtSampai` ada
   - Hanya dihitung jika `Jam Sampai < Jam Pulang Shift`
   - Hanya dihitung untuk hari kerja normal (bukan weekend/hari libur)

2. **Format:**
   - Hasil dalam format desimal (contoh: `2.00` jam)
   - Dibulatkan 2 desimal menggunakan `round($menit / 60, 2)`

3. **Backward Compatibility:**
   - Izin Biasa tetap menggunakan logika lama
   - Izin Masuk Siang tetap menggunakan logika lama
   - Hanya Izin Pulang Cepat yang menggunakan logika baru

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0


