# 📋 Panduan Deployment: Update Browse Tidak Absen - Tukar Hari Kerja (Ubuntu Server)

**Tanggal:** 24 Februari 2026  
**Server:** Ubuntu Production  
**Fitur:** Browse Tidak Absen mempertimbangkan Tukar Hari Kerja

---

## 📋 RINGKASAN UPDATE

Update ini memperbaiki halaman **Browse Tidak Absen** agar mempertimbangkan data **Tukar Hari Kerja**:

- **KERJA_KE_LIBUR:** Karyawan yang hari kerjanya ditukar menjadi libur **tidak lagi** muncul sebagai tidak absen/alpha pada tanggal libur tersebut.
- **LIBUR_KE_KERJA:** Karyawan yang hari liburnya (mis. Sabtu) ditukar menjadi kerja **tetap** dianggap wajib absen pada tanggal tersebut.

**Tidak ada perubahan database/migration** — hanya update 1 file controller.

---

## ✅ LANGKAH 1: BACKUP (DISARANKAN)

### **A. Backup File yang Akan Diubah**

```bash
# Login ke server Ubuntu
ssh root@192.168.10.40
# atau: ssh superadmin@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup controller
cp app/Http/Controllers/BrowseTidakAbsenController.php app/Http/Controllers/BrowseTidakAbsenController.php.backup_$(date +%Y%m%d_%H%M%S)

# Verifikasi backup ada
ls -la app/Http/Controllers/BrowseTidakAbsenController.php*
```

---

## ✅ LANGKAH 2: COPY FILE DARI LOCAL KE SERVER

### **File yang Harus Di-copy (1 file):**

| No | File | Keterangan |
|----|------|------------|
| 1 | `app/Http/Controllers/BrowseTidakAbsenController.php` | Update logic tukar hari kerja |

### **Cara Copy (Opsi A: SCP dari Windows)**

```bash
# Dari Windows (Git Bash atau PowerShell)
# Pastikan sudah di folder project lokal
cd C:\xampp\htdocs\hris-seven-payroll

# Copy file ke server
scp app/Http/Controllers/BrowseTidakAbsenController.php root@192.168.10.40:/tmp/
```

### **Cara Copy (Opsi B: FileZilla / WinSCP)**

1. Buka FileZilla atau WinSCP
2. Connect ke server: `192.168.10.40`
3. Upload `BrowseTidakAbsenController.php` ke folder `/tmp/` di server

---

## ✅ LANGKAH 3: PASTIKAN FILE DI SERVER

```bash
# Login ke server
ssh root@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Copy file dari /tmp/ ke lokasi target
cp /tmp/BrowseTidakAbsenController.php app/Http/Controllers/BrowseTidakAbsenController.php

# Set ownership ke www-data
sudo chown www-data:www-data app/Http/Controllers/BrowseTidakAbsenController.php

# Set permission
sudo chmod 644 app/Http/Controllers/BrowseTidakAbsenController.php
```

---

## ✅ LANGKAH 4: CLEAR CACHE LARAVEL

```bash
cd /var/www/html/hris-seven-payroll

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Opsional: optimize cache
php artisan config:cache
php artisan route:cache
```

---

## ✅ LANGKAH 5: VERIFIKASI

### **1. Cek File**

```bash
ls -la app/Http/Controllers/BrowseTidakAbsenController.php
# Harus: -rw-r--r-- 1 www-data www-data ... BrowseTidakAbsenController.php
```

### **2. Cek Method getTukarHariKerjaSet Ada**

```bash
grep -n "getTukarHariKerjaSet" app/Http/Controllers/BrowseTidakAbsenController.php
grep -n "TukarHariKerja" app/Http/Controllers/BrowseTidakAbsenController.php
```

### **3. Test di Browser**

1. Buka aplikasi: `http://hr.abncorp.lan` atau `http://192.168.10.40`
2. Login
3. Buka menu: **Absensi → Browse Tidak Absen**
4. Pilih periode tanggal yang memiliki data tukar hari kerja
5. **Verifikasi:** Karyawan yang punya KERJA_KE_LIBUR pada tanggal tertentu **tidak** muncul di list tidak absen untuk tanggal tersebut

---

## 📝 CHECKLIST DEPLOYMENT

- [ ] Backup file `BrowseTidakAbsenController.php`
- [ ] Copy 1 file controller ke server
- [ ] Set ownership `www-data:www-data`
- [ ] Set permission `644`
- [ ] Clear Laravel cache
- [ ] Verifikasi file & test di browser
- [ ] Test dengan periode yang ada data tukar hari kerja

---

## ⚠️ CATATAN PENTING

1. **Tidak ada migration** — tidak perlu `php artisan migrate`
2. **Tidak ada perubahan view/route** — hanya controller
3. **Dependency:** Pastikan tabel `t_tukar_hari_kerja` dan model `TukarHariKerja` sudah ada di server (modul Tukar Hari Kerja sudah terdeploy)
4. Jika terjadi error, restore backup:  
   `cp app/Http/Controllers/BrowseTidakAbsenController.php.backup_YYYYMMDD_HHMMSS app/Http/Controllers/BrowseTidakAbsenController.php`

---

## 🔧 TROUBLESHOOTING

### Error: Class 'App\Models\TukarHariKerja' not found

```bash
composer dump-autoload
php artisan cache:clear
```

### Error: Table 't_tukar_hari_kerja' doesn't exist

Pastikan modul Tukar Hari Kerja sudah terdeploy dan migration sudah dijalankan.

### Data masih salah / karyawan tetap muncul

1. Pastikan data tukar hari kerja di `t_tukar_hari_kerja` benar (kolom `tanggal_libur`, `nik`, `vcTipeTukar`)
2. Clear cache lagi: `php artisan cache:clear`
3. Cek log: `tail -f storage/logs/laravel.log`

---

## 📞 INFORMASI SERVER

- **Server IP:** 192.168.10.40
- **Domain:** http://hr.abncorp.lan
- **Lokasi Aplikasi:** /var/www/html/hris-seven-payroll
- **User:** root atau superadmin

---

**Selamat Deploy! 🚀**
