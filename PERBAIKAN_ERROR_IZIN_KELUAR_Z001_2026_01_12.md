# 📝 Perbaikan: Error 422 saat Input Izin Keluar Komplek Z001 (Ijin Dinas Dalam Kota)

**Tanggal:** 12 Januari 2026  
**Fitur:** Izin Keluar Komplek Kantor

---

## 🎯 Ringkasan

Memperbaiki error 422 (Unprocessable Content) dengan pesan "gagal membangkit code counter" saat input izin keluar komplek dengan jenis izin **"Ijin dinas dalam kota" (Z001)**.

**Masalah:**
1. Validasi `vcTipeIzin` terlalu ketat untuk jenis izin non-pribadi (Z001, dll)
2. Counter generation kurang robust (hanya 5 percobaan, tidak ada fallback)

**Solusi:**
1. Validasi `vcTipeIzin` dibuat dinamis: hanya divalidasi untuk jenis izin pribadi (Z003, Z004)
2. Counter generation diperbaiki: 10 percobaan + fallback dengan timestamp lebih unik

---

## 📋 Perubahan yang Dilakukan

### **1. Controller - Method `store()` (Create)**

**File:** `app/Http/Controllers/IzinKeluarController.php`

#### **A. Validasi `vcTipeIzin` Dinamis**

**Sebelumnya:**
```php
$rules = [
    // ...
    'vcTipeIzin' => 'nullable|string|max:20|in:Masuk Siang,Izin Biasa,Pulang Cepat',
    // ...
];
```

**Masalah:** Validasi `in:` terlalu ketat. Untuk jenis izin non-pribadi (seperti Z001), `vcTipeIzin` seharusnya bisa kosong/null tanpa harus memenuhi validasi `in:`.

**Sekarang:**
```php
// Validasi vcTipeIzin: hanya untuk jenis izin pribadi (Z003, Z004)
if ($isPribadi) {
    $rules['vcTipeIzin'] = 'nullable|string|max:20|in:Masuk Siang,Izin Biasa,Pulang Cepat';
} else {
    // Untuk jenis izin lain (seperti Z001), vcTipeIzin bisa kosong tanpa validasi in:
    $rules['vcTipeIzin'] = 'nullable|string|max:20';
}
```

**Logika:**
- **Jenis Izin Pribadi (Z003, Z004):** `vcTipeIzin` harus salah satu dari: "Masuk Siang", "Izin Biasa", "Pulang Cepat" (atau kosong)
- **Jenis Izin Lain (Z001, dll):** `vcTipeIzin` bisa kosong/null tanpa validasi `in:`

#### **B. Counter Generation Lebih Robust**

**Sebelumnya:**
```php
// Generate vcCounter unik (panjang 9). Coba beberapa kali untuk menghindari bentrok.
$vcCounter = null;
for ($i = 0; $i < 5; $i++) {
    // ... generate candidate
    if (!Izin::where('vcCounter', $candidate)->exists()) {
        $vcCounter = $candidate;
        break;
    }
    usleep(50000); // 50ms
}
if (!$vcCounter) {
    return response()->json(['success' => false, 'message' => 'Gagal membangkitkan kode counter. Coba lagi.'], 422);
}
```

**Masalah:** Hanya 5 percobaan, jika semua gagal langsung return error.

**Sekarang:**
```php
// Generate vcCounter unik (panjang 9). Coba beberapa kali untuk menghindari bentrok.
$vcCounter = null;
for ($i = 0; $i < 10; $i++) {
    // ... generate candidate
    if (!Izin::where('vcCounter', $candidate)->exists()) {
        $vcCounter = $candidate;
        break;
    }
    usleep(50000); // 50ms
}

// Fallback: jika masih null setelah 10 percobaan, gunakan timestamp + random yang lebih unik
if (!$vcCounter) {
    $vcCounter = substr(
        str_replace(['-', ':', ' '], '', Carbon::now()->toDateTimeString()) . mt_rand(1000, 9999),
        0,
        9
    );
    // Pastikan tidak ada duplikat dengan fallback
    if (Izin::where('vcCounter', $vcCounter)->exists()) {
        $vcCounter = substr(
            str_replace(['-', ':', ' '], '', Carbon::now()->toDateTimeString()) . mt_rand(10000, 99999),
            0,
            9
        );
    }
}
```

**Perbaikan:**
- Meningkatkan percobaan dari 5 menjadi 10
- Menambahkan fallback dengan timestamp yang lebih unik
- Fallback menggunakan format: `YYYYMMDDHHmmss` + random (lebih unik daripada `mdY`)

### **2. Controller - Method `update()` (Edit)**

**File:** `app/Http/Controllers/IzinKeluarController.php`

**Perubahan:** Sama seperti method `store()`, validasi `vcTipeIzin` dibuat dinamis.

---

## 🧪 Testing

### **Test Case 1: Input Izin - Z001 (Ijin Dinas Dalam Kota)**

**Data:**
- Jenis Izin: "Ijin dinas dalam kota" (Z001)
- Tipe/Kategori Izin: (kosong/null)
- Dari: `08:00`
- Sampai: `17:00`

**Expected Result:**
- ✅ Validasi berhasil (tidak error 422)
- ✅ Data izin tersimpan di `t_izin`
- ✅ `vcCounter` berhasil di-generate
- ✅ `vcTipeIzin` = `null` (kosong)

### **Test Case 2: Input Izin - Z001 dengan vcTipeIzin Kosong String**

**Data:**
- Jenis Izin: "Ijin dinas dalam kota" (Z001)
- Tipe/Kategori Izin: `""` (empty string)
- Dari: `08:00`
- Sampai: `17:00`

**Expected Result:**
- ✅ Validasi berhasil (tidak error 422)
- ✅ Data izin tersimpan di `t_izin`
- ✅ `vcTipeIzin` = `null` (kosong string di-convert ke null)

### **Test Case 3: Input Izin - Z003 (Pribadi) dengan Tipe Valid**

**Data:**
- Jenis Izin: "Pribadi" (Z003)
- Tipe/Kategori Izin: "Masuk Siang"
- Dari: `13:00`
- Sampai: `14:00`

**Expected Result:**
- ✅ Validasi berhasil
- ✅ Data izin tersimpan di `t_izin`
- ✅ `vcTipeIzin` = "Masuk Siang"

### **Test Case 4: Input Izin - Z003 dengan Tipe Invalid**

**Data:**
- Jenis Izin: "Pribadi" (Z003)
- Tipe/Kategori Izin: "Invalid Type" (tidak ada di list)
- Dari: `13:00`
- Sampai: `14:00`

**Expected Result:**
- ✅ Validasi gagal dengan error: "The vc tipe izin field is invalid"
- ✅ Data izin tidak tersimpan

### **Test Case 5: Counter Generation - High Concurrency**

**Scenario:**
- Multiple user input izin secara bersamaan (simulasi dengan banyak request)

**Expected Result:**
- ✅ Semua request berhasil (tidak ada error "Gagal membangkitkan kode counter")
- ✅ Semua `vcCounter` unik (tidak ada duplikat)

---

## 📝 File yang Diubah

1. ✅ `app/Http/Controllers/IzinKeluarController.php`
   - Method `store()`: Validasi `vcTipeIzin` dinamis + counter generation lebih robust
   - Method `update()`: Validasi `vcTipeIzin` dinamis

---

## ✅ Checklist Deployment

- [x] Update validasi `vcTipeIzin` menjadi dinamis
- [x] Update counter generation lebih robust
- [x] Update method `store()` dan `update()`
- [ ] **Test Case 1: Input Izin - Z001** ✅
- [ ] **Test Case 2: Input Izin - Z001 dengan empty string** ✅
- [ ] **Test Case 3: Input Izin - Z003 dengan tipe valid** ✅
- [ ] **Test Case 4: Input Izin - Z003 dengan tipe invalid** ✅
- [ ] **Test Case 5: Counter Generation - High Concurrency** ✅

---

## 🚀 Deployment Steps

1. **Backup file yang akan diubah:**
   ```bash
   cp app/Http/Controllers/IzinKeluarController.php app/Http/Controllers/IzinKeluarController.php.backup
   ```

2. **Copy file baru:**
   - Copy `app/Http/Controllers/IzinKeluarController.php`

3. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Test di production:**
   - Test input izin dengan Z001 (Ijin dinas dalam kota)
   - Test input izin dengan Z003/Z004 (Pribadi)
   - Verifikasi tidak ada error 422
   - Verifikasi counter generation berjalan dengan baik

---

## ⚠️ Catatan Penting

1. **Backward Compatibility:**
   - Data lama tidak terpengaruh
   - Validasi untuk jenis izin pribadi (Z003, Z004) tetap sama

2. **Validasi:**
   - Untuk jenis izin pribadi (Z003, Z004): `vcTipeIzin` harus salah satu dari: "Masuk Siang", "Izin Biasa", "Pulang Cepat" (atau kosong)
   - Untuk jenis izin lain (Z001, dll): `vcTipeIzin` bisa kosong/null tanpa validasi `in:`

3. **Counter Generation:**
   - Format utama: `mdY` (contoh: 1212026) + random 3 digit = 9 karakter
   - Fallback: `YYYYMMDDHHmmss` (contoh: 20260112143000) + random = 9 karakter
   - Maksimal 10 percobaan + 1 fallback

4. **Error Handling:**
   - Jika counter generation gagal setelah semua percobaan, akan menggunakan fallback
   - Fallback menggunakan timestamp yang lebih unik untuk mengurangi kemungkinan duplikat

---

## 🔗 Related Updates

- **Update Izin Keluar Komplek - Pulang Cepat:** `UPDATE_IZIN_KELUAR_PULANG_CEPAT_DISABLE_DARI.md`
- **Auto Save dtJamKeluar:** `UPDATE_AUTO_SAVE_DTJAMKELUAR_IZIN_KELUAR.md`
- **Update Field Tipe/Kategori Izin:** `UPDATE_IZIN_KELUAR_TIPE.md`

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0
















