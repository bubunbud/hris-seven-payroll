# 📋 Cara Mengganti Logo di HRIS Seven Payroll

## 📍 Lokasi Logo Saat Ini

Logo saat ini berada di **sidebar** aplikasi, tepatnya di bagian atas sidebar sebelah kiri.

**File yang perlu diubah:**
- `resources/views/layouts/app.blade.php` (baris 119-122)

---

## 🖼️ Langkah-Langkah Mengganti Logo

### **Langkah 1: Siapkan File Logo**

1. Siapkan file logo Anda dengan format:
   - **Format:** PNG, JPG, atau SVG (disarankan PNG dengan transparan)
   - **Ukuran:** Disarankan maksimal 150px lebar x 32px tinggi
   - **Nama file:** `logo.png` (atau `logo.jpg`, `logo.svg`)

2. **Tips untuk logo:**
   - Gunakan logo dengan background transparan (PNG) untuk hasil terbaik
   - Pastikan logo terlihat jelas di background gelap (sidebar berwarna gelap)
   - Jika logo terlalu besar, resize terlebih dahulu

---

### **Langkah 2: Upload Logo ke Server**

**Lokasi penyimpanan logo:**
```
public/images/logo.png
```

**Cara upload:**

**A. Dari komputer lokal (Windows):**

```bash
# Buat direktori images jika belum ada
# (Di server Ubuntu, jalankan: mkdir -p /var/www/html/hris-seven-payroll/public/images)

# Upload logo ke server
scp logo.png superadmin@192.168.10.40:/var/www/html/hris-seven-payroll/public/images/
```

**B. Atau menggunakan FileZilla/WinSCP:**
- Upload file logo ke: `/var/www/html/hris-seven-payroll/public/images/`
- Pastikan nama file: `logo.png` (atau sesuai yang diatur di layout)

---

### **Langkah 3: Update Layout (Sudah Diupdate)**

File `resources/views/layouts/app.blade.php` sudah diupdate untuk menggunakan logo gambar.

**Kode yang sudah diubah:**
```php
<!-- Sebelum (menggunakan icon Font Awesome) -->
<div class="brand d-flex align-items-center gap-2">
    <i class="fas fa-building"></i>
    <span>HRIS Seven Payroll</span>
</div>

<!-- Sesudah (menggunakan logo gambar) -->
<div class="brand d-flex align-items-center gap-2">
    <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 32px; width: auto; object-fit: contain;">
    <span>HRIS Seven Payroll</span>
</div>
```

---

### **Langkah 4: Set Permission di Server**

```bash
# Login ke server Ubuntu
ssh superadmin@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Buat direktori images jika belum ada
mkdir -p public/images

# Set ownership ke www-data
sudo chown -R www-data:www-data public/images/

# Set permission
sudo chmod -R 755 public/images/
```

---

### **Langkah 5: Clear Cache (Jika Perlu)**

```bash
# Masih di direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Clear cache
php artisan view:clear
php artisan cache:clear
```

---

## 🔧 Kustomisasi Logo

### **Mengubah Ukuran Logo**

Edit file `resources/views/layouts/app.blade.php`, baris 120:

```php
<!-- Ukuran kecil -->
<img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 24px; width: auto;">

<!-- Ukuran sedang (default) -->
<img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 32px; width: auto;">

<!-- Ukuran besar -->
<img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 40px; width: auto;">
```

### **Mengubah Nama File Logo**

Jika nama file logo berbeda (misal: `company-logo.png`), edit di `app.blade.php`:

```php
<img src="{{ asset('images/company-logo.png') }}" alt="Logo" style="height: 32px; width: auto;">
```

### **Menyembunyikan Teks "HRIS Seven Payroll"**

Jika hanya ingin menampilkan logo tanpa teks, edit di `app.blade.php`:

```php
<div class="brand d-flex align-items-center gap-2">
    <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 32px; width: auto;">
    <!-- Hapus atau comment baris di bawah ini -->
    <!-- <span>HRIS Seven Payroll</span> -->
</div>
```

---

## 📝 File yang Perlu Di-Upload ke Server

Setelah mengganti logo, file yang perlu di-upload:

1. **Logo file:**
   ```
   public/images/logo.png
   ```

2. **Layout file (jika sudah diupdate):**
   ```
   resources/views/layouts/app.blade.php
   ```

---

## ✅ Checklist

- [ ] File logo sudah disiapkan (PNG/JPG/SVG)
- [ ] Logo sudah di-upload ke `public/images/`
- [ ] Permission direktori `public/images/` sudah di-set (755)
- [ ] Permission file logo sudah di-set (644)
- [ ] Layout `app.blade.php` sudah diupdate
- [ ] Cache sudah di-clear
- [ ] Logo sudah muncul di sidebar

---

## 🎨 Tips Desain Logo

1. **Background Transparan:** Gunakan PNG dengan background transparan untuk hasil terbaik
2. **Warna Terang:** Karena sidebar berwarna gelap, gunakan logo dengan warna terang atau putih
3. **Ukuran Optimal:** Maksimal 150px lebar x 32px tinggi
4. **Format File:**
   - **PNG:** Untuk logo dengan transparan (disarankan)
   - **JPG:** Untuk logo dengan background solid
   - **SVG:** Untuk logo vektor (scalable)

---

## 🔗 Lokasi File

- **Logo:** `public/images/logo.png`
- **Layout:** `resources/views/layouts/app.blade.php`
- **CSS:** Sudah ada di `app.blade.php` (inline style)

---

**Selamat! Logo Anda sudah bisa digunakan! 🎉**


