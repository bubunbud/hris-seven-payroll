# 📝 Update: Auto Save dtJamKeluar dari Izin Keluar Komplek

**Tanggal:** 12 Januari 2026  
**Fitur:** Izin Keluar Komplek Kantor

---

## 🎯 Ringkasan

Ketika simpan (create/edit) Izin Keluar Komplek dengan kondisi:
- **Jenis Izin** = "Pribadi" (Z003 atau Z004)

Maka:
- ✅ Field **"Sampai (HH:MM)"** otomatis tersimpan ke `t_absen.dtJamKeluar`
- ✅ Berlaku untuk semua tipe (Masuk Siang, Izin Biasa, Pulang Cepat)
- ✅ Berlaku untuk create dan update

---

## 📋 Perubahan yang Dilakukan

### **1. Controller - Method `store()` (Create)**

**File:** `app/Http/Controllers/IzinKeluarController.php`

**Perubahan:**

#### **A. Untuk Kondisi "Masuk Siang":**
- Update `dtJamMasuk` dari jam masuk shift
- Update `dtJamKeluar` dari field "Sampai"
- Keterangan: "Auto: Masuk Siang"

#### **B. Untuk Kondisi Lain (Izin Biasa, Pulang Cepat):**
- Update `dtJamKeluar` dari field "Sampai"
- Keterangan: "Auto: [Tipe Izin]"
- Jika data absensi belum ada, insert baru dengan `dtJamKeluar`

**Logic:**
```php
// Format jam "Sampai" ke HH:mm:ss
$jamSampaiFormatted = $request->dtSampai . ':00';

// Untuk semua jenis izin pribadi
if ($isPribadi) {
    if ($isMasukSiang) {
        // Update dtJamMasuk dan dtJamKeluar
    } else {
        // Update dtJamKeluar saja
    }
}
```

---

### **2. Controller - Method `update()` (Edit)**

**File:** `app/Http/Controllers/IzinKeluarController.php`

**Perubahan:**

- **Sebelumnya:** Hanya update keterangan di `t_absen` untuk kondisi "Masuk Siang"
- **Sekarang:** Update `dtJamKeluar` untuk semua jenis izin pribadi

**Logic:**
- Update `dtJamKeluar` dari field "Sampai" (format: HH:mm:ss)
- Jika kondisi "Masuk Siang", juga update `dtJamMasuk` dari shift
- Update keterangan berdasarkan tipe izin
- Jika data absensi belum ada, insert baru

---

## 🧪 Testing

### **Test Case 1: Create Izin - Masuk Siang**

1. Input Izin Keluar baru:
   - Jenis Izin: "Pribadi" (Z003/Z004)
   - Tipe: "Masuk Siang"
   - Sampai: "14:00"
2. **Expected Result:**
   - ✅ Data izin tersimpan di `t_izin`
   - ✅ Data absensi ter-update/insert di `t_absen`:
     - `dtJamMasuk` = jam masuk shift
     - `dtJamKeluar` = "14:00:00"
     - `vcketerangan` = "Auto: Masuk Siang"

### **Test Case 2: Create Izin - Pulang Cepat**

1. Input Izin Keluar baru:
   - Jenis Izin: "Pribadi" (Z003/Z004)
   - Tipe: "Pulang Cepat"
   - Sampai: "15:00"
2. **Expected Result:**
   - ✅ Data izin tersimpan di `t_izin`
   - ✅ Data absensi ter-update/insert di `t_absen`:
     - `dtJamKeluar` = "15:00:00"
     - `vcketerangan` = "Auto: Pulang Cepat"

### **Test Case 3: Create Izin - Izin Biasa**

1. Input Izin Keluar baru:
   - Jenis Izin: "Pribadi" (Z003/Z004)
   - Tipe: "Izin Biasa"
   - Sampai: "16:00"
2. **Expected Result:**
   - ✅ Data izin tersimpan di `t_izin`
   - ✅ Data absensi ter-update/insert di `t_absen`:
     - `dtJamKeluar` = "16:00:00"
     - `vcketerangan` = "Auto: Izin Biasa"

### **Test Case 4: Edit Izin - Ubah Jam Sampai**

1. Edit Izin Keluar yang sudah ada:
   - Ubah "Sampai" dari "14:00" menjadi "15:00"
2. **Expected Result:**
   - ✅ Data izin ter-update di `t_izin`
   - ✅ Data absensi ter-update di `t_absen`:
     - `dtJamKeluar` = "15:00:00"

---

## 📝 File yang Diubah

1. ✅ `app/Http/Controllers/IzinKeluarController.php`
   - Method `store()`: Tambah logic update `dtJamKeluar` untuk semua jenis izin pribadi
   - Method `update()`: Update logic untuk update `dtJamKeluar` untuk semua jenis izin pribadi

---

## ✅ Checklist Deployment

- [x] Update method `store()` dengan logic update `dtJamKeluar`
- [x] Update method `update()` dengan logic update `dtJamKeluar`
- [ ] **Test create Izin Keluar - Masuk Siang**
- [ ] **Test create Izin Keluar - Pulang Cepat**
- [ ] **Test create Izin Keluar - Izin Biasa**
- [ ] **Test edit Izin Keluar - ubah jam Sampai**
- [ ] **Verifikasi data di database `t_absen.dtJamKeluar`**

---

## 🚀 Deployment Steps

1. **Copy File:**
   - `app/Http/Controllers/IzinKeluarController.php`

2. **Clear Cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Test:**
   - Test semua test case di atas
   - Verifikasi data tersimpan dengan benar di database

---

## ⚠️ Catatan Penting

1. **Format Jam:**
   - Field "Sampai" di form: `HH:MM` (contoh: "14:00")
   - Disimpan ke database: `HH:MM:SS` (contoh: "14:00:00")

2. **Kondisi:**
   - Hanya berlaku untuk **Jenis Izin = "Pribadi"** (Z003 atau Z004)
   - Berlaku untuk **semua tipe** (Masuk Siang, Izin Biasa, Pulang Cepat)

3. **Data Absensi:**
   - Jika data absensi sudah ada → **Update** `dtJamKeluar`
   - Jika data absensi belum ada → **Insert** baru dengan `dtJamKeluar`

4. **Error Handling:**
   - Jika error saat update `t_absen`, error di-log tapi tidak gagalkan proses simpan izin
   - Izin tetap tersimpan meskipun update `t_absen` gagal

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0


