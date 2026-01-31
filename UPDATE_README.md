# 📦 Panduan Update ke Server Ubuntu

Panduan cepat untuk mengupdate perubahan **Instruksi Kerja Lembur** ke server Ubuntu (192.168.10.40).

---

## 🚀 Cara Update (Pilih Salah Satu)

### **Opsi 1: Menggunakan Script Otomatis** ⚡ (Recommended)

#### **Windows (PowerShell)**

```powershell
# Buka PowerShell di folder project
cd C:\xampp\htdocs\hris-seven-payroll

# Jalankan script
.\update-to-ubuntu.ps1
```

#### **Windows (Git Bash) atau Linux/Mac**

```bash
# Buka terminal di folder project
cd /c/xampp/htdocs/hris-seven-payroll

# Berikan permission execute (jika perlu)
chmod +x update-to-ubuntu.sh

# Jalankan script
./update-to-ubuntu.sh
```

**Catatan:** Script akan otomatis:

-   ✅ Upload file ke server
-   ✅ Backup database dan .env
-   ✅ Copy file ke folder aplikasi
-   ✅ Update autoload
-   ✅ Run migration
-   ✅ Clear cache

---

### **Opsi 2: Manual (Step by Step)** 📝

Ikuti panduan lengkap di file **`QUICK_UPDATE_UBUNTU.md`**

---

## 📋 File yang Akan Di-update

1. `app/Http/Controllers/InstruksiKerjaLemburController.php`
2. `app/Http/Controllers/ClosingController.php`
3. `app/Services/LemburCalculationService.php` (BARU)
4. `app/Models/LemburDetail.php`
5. `resources/views/instruksi-kerja-lembur/index.blade.php`
6. `routes/web.php`
7. `database/migrations/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php` (BARU)

---

## ⚠️ PENTING: Backup Sebelum Update!

Script otomatis akan membuat backup otomatis, tapi disarankan untuk backup manual juga:

```bash
# Di server Ubuntu
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll
mysqldump -u root -proot123 hris_seven > ~/backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql
```

---

## ✅ Setelah Update, Test:

1. **Akses aplikasi**: `http://192.168.10.40/hris-seven-payroll`
2. **Login** dengan user yang ada
3. **Buka menu**: Instruksi Kerja Lembur
4. **Test fitur**:
    - Tambah data baru
    - Edit data existing
    - Pastikan kolom "Nominal Lembur" muncul
    - Pastikan layout detail sudah sesuai

---

## 🔧 Troubleshooting

### Error: SCP tidak ditemukan

-   **Windows**: Install Git Bash atau WSL
-   **Alternatif**: Gunakan FileZilla/WinSCP untuk upload manual

### Error: Permission Denied

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll
chown -R www-data:www-data /var/www/html/hris-seven-payroll
chmod -R 775 storage bootstrap/cache
```

### Error: Migration Error

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll
php artisan migrate:status
php artisan migrate --force
```

### Error: Class Not Found

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll
composer dump-autoload --optimize
php artisan config:clear
```

---

## 📞 Bantuan

Jika ada masalah, check log:

```bash
ssh root@192.168.10.40
tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

---

**Selamat Update!** 🎉




