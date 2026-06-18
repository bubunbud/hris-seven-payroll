# 📋 CHECKLIST FITUR YANG BELUM BERFUNGSI

**Tanggal:** 12 Januari 2026  
**Status:** Setelah Restore dari Production

---

## ✅ FOLDER YANG SUDAH DI-RESTORE

- ✅ `app/` (seluruh folder)
- ✅ `database/` (seluruh folder)
- ✅ `routes/` (seluruh folder)
- ✅ `resources/` (seluruh folder)

---

## ❓ FITUR YANG BELUM BERFUNGSI

**Silakan isi daftar fitur yang bermasalah di bawah ini:**

### 1. [Nama Fitur/Menu]
   - **Deskripsi masalah:**
   - **Error message (jika ada):**
   - **Langkah untuk reproduce:**
   - [ ] Sudah dicek

### 2. [Nama Fitur/Menu]
   - **Deskripsi masalah:**
   - **Error message (jika ada):**
   - **Langkah untuk reproduce:**
   - [ ] Sudah dicek

### 3. [Nama Fitur/Menu]
   - **Deskripsi masalah:**
   - **Error message (jika ada):**
   - **Langkah untuk reproduce:**
   - [ ] Sudah dicek

---

## 🔍 HAL YANG PERLU DICEK

### **A. File yang Mungkin Masih Perlu di-Restore:**

- [ ] `config/` - File konfigurasi aplikasi
- [ ] `public/` - Assets (CSS, JS, images)
- [ ] `bootstrap/` - Bootstrap files
- [ ] `storage/app/public/photos/` - Foto karyawan (jika ada)

### **B. Dependencies:**

- [ ] `composer install` sudah dijalankan
- [ ] `npm install` sudah dijalankan (jika ada)
- [ ] `php artisan storage:link` sudah dijalankan

### **C. Cache & Config:**

- [x] `php artisan config:clear` ✅
- [x] `php artisan cache:clear` ✅
- [x] `php artisan route:clear` ✅
- [x] `php artisan view:clear` ✅

### **D. Database:**

- [ ] Migration sudah dijalankan (`php artisan migrate`)
- [ ] Seeder sudah dijalankan (jika perlu)
- [ ] Koneksi database di `.env` sudah benar

### **E. Storage & Permissions:**

- [ ] `storage/` folder writable
- [ ] `bootstrap/cache/` folder writable
- [ ] `public/storage` symlink sudah ada

---

## 🛠️ LANGKAH PERBAIKAN UMUM

### **1. Clear Semua Cache:**
```powershell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### **2. Rebuild Cache:**
```powershell
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **3. Check Log:**
```powershell
# Cek error terbaru
Get-Content storage\logs\laravel.log -Tail 50
```

### **4. Check Route:**
```powershell
# Cek route yang terdaftar
php artisan route:list | Select-String "nama-fitur"
```

### **5. Check Controller:**
```powershell
# Cek apakah controller ada dan tidak kosong
Get-Item "app\Http\Controllers\NamaController.php" | Select-Object Name, Length
```

---

## 📝 CATATAN

- Isi daftar fitur yang bermasalah di atas
- Berikan detail error message jika ada
- Berikan langkah untuk reproduce masalah
- Setelah diisi, akan dilakukan perbaikan satu per satu

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0


