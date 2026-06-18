# 🔧 Perbaikan Error 500: Edit Izin Keluar Komplek - Pulang Cepat

**Tanggal:** 12 Januari 2026  
**Error:** `SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'dtDari' cannot be null`

---

## 🐛 Masalah

Saat edit Izin Keluar Komplek dengan kondisi:
- Jenis Izin = "Pribadi" (Z003 atau Z004)
- Tipe/Kategori Izin = "Pulang Cepat"

Error 500 terjadi dengan pesan:
```
SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'dtDari' cannot be null
```

**Penyebab:**
- Kolom `dtDari` di tabel `t_izin` masih **NOT NULL** di database
- Ketika field "Dari" disabled, value tidak dikirim, sehingga `dtDari` = `null`
- Database menolak karena constraint NOT NULL

---

## ✅ Solusi yang Diterapkan

### **1. Migration: Ubah Kolom `dtDari` Menjadi Nullable**

**File:** `database/migrations/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php`

**Isi Migration:**
```php
public function up(): void
{
    Schema::table('t_izin', function (Blueprint $table) {
        // Ubah kolom dtDari menjadi nullable untuk mendukung fitur "Pulang Cepat"
        $table->time('dtDari')->nullable()->change();
    });
}
```

**Status:** ✅ Migration sudah dijalankan

### **2. Update JavaScript: Hapus Value dari FormData jika Field Disabled**

**File:** `resources/views/absen/izin_keluar/index.blade.php`

**Perubahan:**
- Menambahkan logic untuk menghapus `dtDari` dari FormData jika field disabled
- Mencegah pengiriman value kosong yang bisa menyebabkan masalah

```javascript
// Jika field "Dari" disabled (Pulang Cepat), hapus dari formData agar tidak dikirim
const dtDariField = document.getElementById('dtDari');
if (dtDariField && dtDariField.disabled) {
    formData.delete('dtDari');
}
```

---

## 📋 File yang Diubah

1. ✅ `database/migrations/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php`
   - Migration untuk mengubah kolom `dtDari` menjadi nullable

2. ✅ `resources/views/absen/izin_keluar/index.blade.php`
   - Update JavaScript untuk handle field disabled

---

## 🧪 Testing

### **Test Case: Edit Izin Keluar - Pulang Cepat**

1. Buka halaman Izin Keluar Komplek
2. Edit data dengan kondisi:
   - Jenis Izin = "Pribadi" (Z003 atau Z004)
   - Tipe/Kategori Izin = "Pulang Cepat"
3. **Expected Result:**
   - ✅ Field "Dari" disabled (abu-abu)
   - ✅ Form bisa disubmit tanpa error
   - ✅ Data berhasil di-update di database
   - ✅ Field `dtDari` di database = `null`

### **Test Case: Edit Izin Keluar - Masuk Siang**

1. Edit data dengan kondisi:
   - Jenis Izin = "Pribadi" (Z003 atau Z004)
   - Tipe/Kategori Izin = "Masuk Siang"
2. **Expected Result:**
   - ✅ Field "Dari" enabled
   - ✅ Field "Dari" auto-fill dengan jam masuk shift
   - ✅ Form bisa disubmit
   - ✅ Data berhasil di-update

---

## 🚀 Deployment Steps

### **Untuk Production Server:**

1. **Jalankan Migration:**
   ```bash
   php artisan migrate
   ```

2. **Atau Jalankan SQL Manual:**
   ```sql
   ALTER TABLE `t_izin` 
   MODIFY COLUMN `dtDari` TIME NULL;
   ```

3. **Copy File yang Diubah:**
   - `database/migrations/2026_02_02_070922_make_dt_dari_nullable_in_t_izin_table.php`
   - `resources/views/absen/izin_keluar/index.blade.php`

4. **Clear Cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

---

## ⚠️ Catatan Penting

1. **Database:**
   - Kolom `dtDari` sekarang **nullable** di database
   - Data lama dengan `dtDari` yang sudah ada tidak terpengaruh
   - Data baru dengan kondisi "Pulang Cepat" bisa memiliki `dtDari` = `null`

2. **Backward Compatibility:**
   - Data lama tetap valid
   - Fitur lama tetap berfungsi normal
   - Hanya data baru dengan kondisi "Pulang Cepat" yang bisa memiliki `dtDari` = `null`

3. **Rollback (Jika Diperlukan):**
   ```bash
   php artisan migrate:rollback --step=1
   ```
   **Catatan:** Rollback akan gagal jika ada data dengan `dtDari` = `null`

---

## ✅ Status

**Perbaikan selesai!** Error 500 sudah teratasi. Silakan test lagi edit Izin Keluar dengan kondisi Pribadi + Pulang Cepat.

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0


