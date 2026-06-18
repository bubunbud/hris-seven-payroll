# 📝 Update: Izin Keluar Komplek - Disable Field "Dari" untuk Pulang Cepat

**Tanggal:** 12 Januari 2026  
**Fitur:** Izin Keluar Komplek Kantor

---

## 🎯 Ringkasan

Ketika input/edit Izin Keluar Komplek dengan kondisi:
- **Jenis Izin** = "Pribadi" (Z003 atau Z004)
- **Tipe/Kategori Izin** = "Pulang Cepat"

Maka:
- ✅ Field **"Dari"** akan **disable** (tidak wajib diisi)
- ✅ Field **"Sampai"** tetap bisa di input manual (wajib diisi)

---

## 📋 Perubahan yang Dilakukan

### **1. View - Form HTML**

**File:** `resources/views/absen/izin_keluar/index.blade.php`

#### **A. Update Label Field "Dari"**
- Menambahkan `id="dtDariRequired"` pada span tanda required untuk kontrol dinamis
- Label: `Dari (HH:MM) <span class="text-danger" id="dtDariRequired">*</span>`

#### **B. JavaScript Logic**

**1. Event Listener untuk `vcTipeIzin` (Tipe/Kategori Izin):**
- Ketika Tipe = "Pulang Cepat" dan Jenis Izin = Pribadi:
  - Disable field "Dari" (`dtDariField.disabled = true`)
  - Hapus attribute `required`
  - Clear value field "Dari"
  - Sembunyikan tanda required (`dtDariRequired.style.display = 'none'`)
- Untuk tipe lain:
  - Enable field "Dari" (`dtDariField.disabled = false`)
  - Set attribute `required`
  - Tampilkan tanda required
  - Auto-fill jam "Dari" jika Tipe = "Masuk Siang"

**2. Event Listener untuk `vcKodeIzin` (Jenis Izin):**
- Logic yang sama seperti di atas
- Memastikan field "Dari" di-disable/enable saat jenis izin berubah

**3. Function `toggleTipeIzinField()`:**
- Update logic untuk handle field "Dari" berdasarkan tipe izin
- Jika Tipe = "Pulang Cepat" → disable "Dari"
- Jika Tipe = "Masuk Siang" → enable "Dari" dan auto-fill
- Jika bukan pribadi → enable "Dari"

**4. Reset Form (Add Mode):**
- Reset field "Dari" ke enabled dan required saat form dibuka untuk add

**5. Edit Mode:**
- Handle field "Dari" berdasarkan tipe izin yang sudah ada
- Jika Tipe = "Pulang Cepat" → disable "Dari"
- Jika Tipe = "Masuk Siang" → enable "Dari" dan auto-fill jika ada jam masuk shift

---

### **2. Controller - Validasi**

**File:** `app/Http/Controllers/IzinKeluarController.php`

#### **A. Method `store()` (Create)**

**Perubahan:**
- Menambahkan logic untuk cek kondisi "Pulang Cepat":
  ```php
  $isPribadi = in_array($request->vcKodeIzin, ['Z003', 'Z004']);
  $isPulangCepat = $request->vcTipeIzin === 'Pulang Cepat';
  $isDariRequired = !($isPribadi && $isPulangCepat);
  ```

- Validasi `dtDari`:
  - Jika **bukan** kondisi "Pulang Cepat": `required|date_format:H:i`
  - Jika kondisi "Pulang Cepat": `nullable|date_format:H:i`

- Saat create izin:
  - Jika kondisi "Pulang Cepat", `dtDari` bisa `null`
  - Jika bukan, `dtDari` wajib diisi

#### **B. Method `update()` (Edit)**

**Perubahan:**
- Logic validasi sama seperti `store()`
- Field `dtDari` bisa `null` untuk kondisi "Pulang Cepat"

---

## 🧪 Testing

### **Test Case 1: Input Baru - Pulang Cepat**

1. Buka halaman Izin Keluar Komplek
2. Klik "Tambah"
3. Pilih:
   - Jenis Izin: "Pribadi" (Z003 atau Z004)
   - Tipe/Kategori Izin: "Pulang Cepat"
4. **Expected Result:**
   - ✅ Field "Dari" menjadi **disabled** (abu-abu, tidak bisa diisi)
   - ✅ Tanda required (*) pada label "Dari" **hilang**
   - ✅ Field "Sampai" tetap **enabled** dan **required**
   - ✅ Form bisa disubmit tanpa mengisi field "Dari"

### **Test Case 2: Input Baru - Masuk Siang**

1. Buka halaman Izin Keluar Komplek
2. Klik "Tambah"
3. Pilih:
   - Jenis Izin: "Pribadi" (Z003 atau Z004)
   - Tipe/Kategori Izin: "Masuk Siang"
4. **Expected Result:**
   - ✅ Field "Dari" tetap **enabled** dan **required**
   - ✅ Field "Dari" **auto-fill** dengan jam masuk shift (jika ada)
   - ✅ Field "Sampai" tetap **enabled** dan **required**

### **Test Case 3: Edit - Ubah dari Masuk Siang ke Pulang Cepat**

1. Edit data izin dengan Tipe = "Masuk Siang"
2. Ubah Tipe menjadi "Pulang Cepat"
3. **Expected Result:**
   - ✅ Field "Dari" menjadi **disabled**
   - ✅ Value field "Dari" **ter-clear**
   - ✅ Tanda required (*) pada label "Dari" **hilang**

### **Test Case 4: Edit - Ubah dari Pulang Cepat ke Masuk Siang**

1. Edit data izin dengan Tipe = "Pulang Cepat"
2. Ubah Tipe menjadi "Masuk Siang"
3. **Expected Result:**
   - ✅ Field "Dari" menjadi **enabled**
   - ✅ Tanda required (*) pada label "Dari" **muncul**
   - ✅ Field "Dari" **auto-fill** dengan jam masuk shift (jika ada)

### **Test Case 5: Validasi Backend**

1. Submit form dengan kondisi "Pulang Cepat" tanpa mengisi field "Dari"
2. **Expected Result:**
   - ✅ Data berhasil disimpan
   - ✅ Field `dtDari` di database = `null`

3. Submit form dengan kondisi "Masuk Siang" tanpa mengisi field "Dari"
4. **Expected Result:**
   - ✅ Validasi error: "The dt dari field is required"

---

## 📝 File yang Diubah

1. ✅ `resources/views/absen/izin_keluar/index.blade.php`
   - Update HTML label field "Dari"
   - Update JavaScript logic untuk disable/enable field "Dari"
   - Update event listener untuk `vcTipeIzin` dan `vcKodeIzin`
   - Update function `toggleTipeIzinField()`
   - Update reset form dan edit mode

2. ✅ `app/Http/Controllers/IzinKeluarController.php`
   - Update method `store()` - validasi dinamis untuk `dtDari`
   - Update method `update()` - validasi dinamis untuk `dtDari`
   - Handle `dtDari` = `null` untuk kondisi "Pulang Cepat"

---

## ✅ Checklist Deployment

- [x] Update view dengan JavaScript logic
- [x] Update controller dengan validasi dinamis
- [x] Test input baru dengan kondisi "Pulang Cepat"
- [x] Test input baru dengan kondisi "Masuk Siang"
- [x] Test edit mode - ubah tipe izin
- [x] Test validasi backend
- [ ] **Deploy ke production server**

---

## 🚀 Deployment Steps

1. **Backup file yang akan diubah:**
   ```bash
   cp resources/views/absen/izin_keluar/index.blade.php resources/views/absen/izin_keluar/index.blade.php.backup
   cp app/Http/Controllers/IzinKeluarController.php app/Http/Controllers/IzinKeluarController.php.backup
   ```

2. **Copy file baru:**
   - Copy `resources/views/absen/izin_keluar/index.blade.php`
   - Copy `app/Http/Controllers/IzinKeluarController.php`

3. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

4. **Test di production:**
   - Test semua test case di atas
   - Verifikasi data tersimpan dengan benar di database

---

## ⚠️ Catatan Penting

1. **Database:**
   - Field `dtDari` di tabel `t_izin` harus bisa `null` (nullable)
   - Pastikan migration sudah benar

2. **Backward Compatibility:**
   - Data lama dengan `dtDari` yang sudah ada tidak terpengaruh
   - Data baru dengan kondisi "Pulang Cepat" bisa memiliki `dtDari` = `null`

3. **Validasi:**
   - Validasi dilakukan di frontend (JavaScript) dan backend (Laravel)
   - Frontend: disable field dan hapus required
   - Backend: validasi dinamis berdasarkan kondisi

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0


