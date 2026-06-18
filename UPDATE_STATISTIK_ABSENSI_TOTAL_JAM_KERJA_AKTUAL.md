# 📝 Update: Statistik Absensi - Logika Perhitungan Total Jam Kerja Aktual

**Tanggal:** 12 Januari 2026  
**Fitur:** Statistik Absensi

---

## 🎯 Ringkasan

Update logika perhitungan **"Total Jam Kerja Aktual"** di halaman Statistik Absensi.

**Logika Baru:**
- **Total Jam Kerja Aktual** = akumulasi jam kerja karyawan pada satu periode
- **Jumlah jam** = **jam pulang - jam masuk** (untuk setiap record absensi)

**Formula:**
```
Total Jam Kerja Aktual = Σ (Jam Pulang - Jam Masuk) untuk semua karyawan dalam periode
```

---

## 📋 Perubahan yang Dilakukan

### **Controller - Method `index()`**

**File:** `app/Http/Controllers/StatistikAbsensiController.php`

**Perubahan:**

#### **Sebelumnya:**
- `$totalJamKerjaAktual` hanya dihitung jika ada:
  - `$jamMasuk` dan `$jamKeluar` **DAN**
  - `$shiftMasuk` dan `$shiftPulang`
- Perhitungan dilakukan dalam blok yang sama dengan perhitungan surplus/deficit

#### **Sekarang:**
- `$totalJamKerjaAktual` dihitung jika ada:
  - `$jamMasuk` dan `$jamKeluar` saja (tidak perlu shift)
- Perhitungan dipisah dari perhitungan surplus/deficit
- Logika: **Jam Pulang - Jam Masuk** (dikurangi 1 jam jika melewati jam istirahat 12:00-13:00)

**Logic Code:**
```php
// Hitung Total Jam Kerja Aktual: akumulasi jam kerja karyawan (jam pulang - jam masuk)
// Logika baru: hanya hitung jika ada jam masuk dan jam keluar (tidak perlu shift)
if ($jamMasuk && $jamKeluar) {
    $tMasuk = $tanggal->copy()->setTimeFromTimeString($jamMasuk);
    $tKeluar = $tanggal->copy()->setTimeFromTimeString($jamKeluar);
    if ($tKeluar->lessThan($tMasuk)) {
        $tKeluar->addDay();
    }
    $menitAktual = $tMasuk->diffInMinutes($tKeluar, true);
    // Kurangi 1 jam jika interval melewati jam istirahat 12:00-13:00
    $lunchStart = $tanggal->copy()->setTimeFromTimeString('12:00');
    $lunchEnd = $tanggal->copy()->setTimeFromTimeString('13:00');
    if ($tMasuk->lt($lunchEnd) && $tKeluar->gt($lunchStart)) {
        $menitAktual = max(0, $menitAktual - 60);
    }
    $totalJamKerjaAktual += round($menitAktual / 60, 2);
}
```

---

## 🧪 Testing

### **Test Case 1: Perhitungan Normal**

**Data Absensi:**
- Karyawan A, Tanggal 1: Masuk `08:00`, Keluar `17:00` → 9 jam (dikurangi 1 jam istirahat) = 8 jam
- Karyawan A, Tanggal 2: Masuk `08:00`, Keluar `17:00` → 8 jam
- Karyawan B, Tanggal 1: Masuk `09:00`, Keluar `18:00` → 9 jam (dikurangi 1 jam istirahat) = 8 jam

**Expected Result:**
- ✅ Total Jam Kerja Aktual = `8 + 8 + 8 = 24.00` jam

### **Test Case 2: Tanpa Shift (Tetap Dihitung)**

**Data Absensi:**
- Karyawan C: Masuk `08:00`, Keluar `17:00` (tidak ada shift)
- **Expected Result:**
  - ✅ Total Jam Kerja Aktual tetap dihitung = `8.00` jam

### **Test Case 3: Melewati Jam Istirahat**

**Data Absensi:**
- Karyawan D: Masuk `11:00`, Keluar `14:00`
- **Expected Result:**
  - ✅ Total Jam Kerja Aktual = `3 jam - 1 jam istirahat = 2.00` jam

### **Test Case 4: Tidak Melewati Jam Istirahat**

**Data Absensi:**
- Karyawan E: Masuk `14:00`, Keluar `17:00`
- **Expected Result:**
  - ✅ Total Jam Kerja Aktual = `3.00` jam (tidak dikurangi istirahat)

### **Test Case 5: Hanya Masuk atau Hanya Keluar**

**Data Absensi:**
- Karyawan F: Masuk `08:00`, Keluar `null`
- Karyawan G: Masuk `null`, Keluar `17:00`
- **Expected Result:**
  - ✅ Total Jam Kerja Aktual = `0.00` jam (tidak dihitung karena tidak lengkap)

---

## 📝 File yang Diubah

1. ✅ `app/Http/Controllers/StatistikAbsensiController.php`
   - Update logic perhitungan `$totalJamKerjaAktual` di method `index()`
   - Pisahkan perhitungan Total Jam Kerja Aktual dari perhitungan surplus/deficit
   - Hapus requirement shift untuk perhitungan Total Jam Kerja Aktual

---

## ✅ Checklist Deployment

- [x] Update logic perhitungan `$totalJamKerjaAktual`
- [x] Pisahkan dari perhitungan surplus/deficit
- [ ] **Test di local:**
  - [ ] Test Case 1: Perhitungan Normal
  - [ ] Test Case 2: Tanpa Shift
  - [ ] Test Case 3: Melewati Jam Istirahat
  - [ ] Test Case 4: Tidak Melewati Jam Istirahat
  - [ ] Test Case 5: Hanya Masuk atau Hanya Keluar
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
   - Verifikasi Card "Total Jam Kerja Aktual"
   - Pastikan perhitungan sesuai dengan logika baru

---

## ⚠️ Catatan Penting

1. **Kondisi Valid:**
   - Hanya dihitung jika ada `jamMasuk` **DAN** `jamKeluar`
   - Tidak perlu shift untuk perhitungan Total Jam Kerja Aktual
   - Dikurangi 1 jam jika melewati jam istirahat 12:00-13:00

2. **Format:**
   - Hasil dalam format desimal (contoh: `24.00` jam)
   - Dibulatkan 2 desimal menggunakan `round($menitAktual / 60, 2)`

3. **Perbedaan dengan Surplus/Defisit:**
   - **Total Jam Kerja Aktual:** Hanya perlu `jamMasuk` dan `jamKeluar`
   - **Surplus/Defisit:** Masih perlu `shiftMasuk` dan `shiftPulang` untuk perbandingan

4. **Akumulasi:**
   - Total adalah akumulasi dari semua record absensi dalam periode
   - Setiap record absensi dengan jam masuk dan keluar lengkap akan dihitung

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0


