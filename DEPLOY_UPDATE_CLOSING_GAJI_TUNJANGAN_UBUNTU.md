# 📋 Panduan Deployment: Update Closing Gaji - Kolom Tunjangan (Ubuntu Server)

**Tanggal:** Februari 2026  
**Server:** Ubuntu Production  
**Fitur:** Tambah kolom breakdown Makan/Transport & kolom read-only di Update Closing Gaji

---

## 📋 RINGKASAN UPDATE

Update ini menambah dan mengubah bagian **Tunjangan** di halaman **Update Closing Gaji**:

1. **Kolom baru:**
   - Makan Kerja (`intMakanKerja`)
   - Makan Libur (`intMakanLibur`)
   - Transport Kerja (`intTransportKerja`)
   - Transport Libur (`intTransportLibur`)

2. **Kolom read-only (auto):**
   - Jumlah Makan = Makan Kerja + Makan Libur
   - Jumlah Transport = Transport Kerja + Transport Libur
   - Total Uang Makan = Jumlah Makan × Tarif Makan
   - Total Uang Transport = Jumlah Transport × Tarif Transport

**Tidak ada perubahan database/migration** — hanya update 1 file view.

---

## ✅ LANGKAH 1: BACKUP (DISARANKAN)

```bash
# Login ke server Ubuntu
ssh root@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup view
cp resources/views/proses/update-closing-gaji/index.blade.php resources/views/proses/update-closing-gaji/index.blade.php.backup_$(date +%Y%m%d_%H%M%S)

# Verifikasi backup ada
ls -la resources/views/proses/update-closing-gaji/
```

---

## ✅ LANGKAH 2: COPY FILE DARI LOCAL KE SERVER

### **File yang Harus Di-copy (1 file):**

| No | File | Keterangan |
|----|------|------------|
| 1 | `resources/views/proses/update-closing-gaji/index.blade.php` | View form Update Closing Gaji |

### **Cara Copy (Opsi A: SCP dari Windows)**

```bash
# Dari Windows (Git Bash atau PowerShell)
cd C:\xampp\htdocs\hris-seven-payroll

# Copy file ke server
scp resources/views/proses/update-closing-gaji/index.blade.php root@192.168.10.40:/tmp/
```

### **Cara Copy (Opsi B: FileZilla / WinSCP)**

1. Connect ke server: `192.168.10.40`
2. Upload `index.blade.php` ke folder `/tmp/` di server
3. Pastikan path lengkap di server: `resources/views/proses/update-closing-gaji/index.blade.php`

---

## ✅ LANGKAH 3: PASTIKAN FILE DI SERVER

```bash
# Login ke server
ssh root@192.168.10.40

cd /var/www/html/hris-seven-payroll

# Copy file dari /tmp/ ke lokasi target
cp /tmp/index.blade.php resources/views/proses/update-closing-gaji/index.blade.php

# Set ownership ke www-data
sudo chown www-data:www-data resources/views/proses/update-closing-gaji/index.blade.php

# Set permission
sudo chmod 644 resources/views/proses/update-closing-gaji/index.blade.php
```

---

## ✅ LANGKAH 4: CLEAR CACHE LARAVEL

```bash
cd /var/www/html/hris-seven-payroll

php artisan view:clear
php artisan cache:clear

# Opsional: rebuild view cache
php artisan view:cache
```

---

## ✅ LANGKAH 5: VERIFIKASI

### **1. Cek File**

```bash
ls -la resources/views/proses/update-closing-gaji/index.blade.php
# Harus: -rw-r--r-- 1 www-data www-data ...
```

### **2. Test di Browser**

1. Buka aplikasi: `http://hr.abncorp.lan` atau `http://192.168.10.40`
2. Login
3. Buka menu: **Proses Gaji → Update Closing Gaji**
4. Klik **Tambah** atau **Edit** pada salah satu record
5. Buka accordion **Tunjangan**
6. **Verifikasi:**
   - Ada kolom: Makan Kerja, Makan Libur, Transport Kerja, Transport Libur
   - Kolom Jumlah Makan, Jumlah Transport, Total Uang Makan, Total Uang Transport tampak abu-abu (read-only)
   - Isi Makan Kerja dan Makan Libur → Jumlah Makan dan Total Uang Makan ter-update otomatis
   - Isi Transport Kerja dan Transport Libur → Jumlah Transport dan Total Uang Transport ter-update otomatis
7. Simpan/Update data dan pastikan tidak ada error

---

## 📝 CHECKLIST DEPLOYMENT

- [ ] Backup file `index.blade.php`
- [ ] Copy 1 file view ke server
- [ ] Set ownership `www-data:www-data`
- [ ] Set permission `644`
- [ ] Clear Laravel view cache
- [ ] Test form Tambah dan Edit di browser
- [ ] Verifikasi kolom breakdown dan auto-calculate
- [ ] Test simpan/update data

---

## ⚠️ CATATAN PENTING

1. **Tidak ada migration** — tidak perlu `php artisan migrate`
2. **Tidak ada perubahan controller** — hanya view
3. Tabel `t_closing` sudah memiliki kolom `intMakanKerja`, `intMakanLibur`, `intTransportKerja`, `intTransportLibur`
4. Jika terjadi error, restore backup:  
   `cp resources/views/proses/update-closing-gaji/index.blade.php.backup_YYYYMMDD_HHMMSS resources/views/proses/update-closing-gaji/index.blade.php`

---

## 🔧 TROUBLESHOOTING

### Form tidak menampilkan kolom baru
```bash
php artisan view:clear
php artisan cache:clear
```
Hard refresh browser (Ctrl+F5)

### Nilai tidak tersimpan
Pastikan field `intMakanKerja`, `intMakanLibur`, `intTransportKerja`, `intTransportLibur` ada di `fillable` model Closing (sudah ada di codebase).

---

## 📞 INFORMASI SERVER

- **Server IP:** 192.168.10.40
- **Domain:** http://hr.abncorp.lan
- **Lokasi Aplikasi:** /var/www/html/hris-seven-payroll
- **User:** root atau superadmin

---

**Selamat Deploy! 🚀**
