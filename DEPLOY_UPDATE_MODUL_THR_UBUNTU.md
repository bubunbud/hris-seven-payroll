# 📋 Panduan Deployment: Update Modul THR (Ubuntu Server)

**Tanggal:** Februari 2026  
**Server:** Ubuntu Production  
**Fitur:** Revisi Modul THR (Closing THR, Slip THR, Rekap THR Operator, Rekap THR Staff, Rekap Bank THR)

---

## 📋 RINGKASAN UPDATE

Update ini merevisi seluruh modul THR:

| No | Modul | Perubahan |
|----|-------|-----------|
| 1 | **Closing THR** | Process semua group (Management, Staff, Operator, Security). Nilai THR hanya dihitung untuk Operator & Security |
| 2 | **Slip THR** | Cetak/preview hanya untuk Operator & Security |
| 3 | **Rekap THR Operator** | Menampilkan data Operator & Security |
| 4 | **Rekap THR Staff** | Menampilkan data Staff & Management |
| 5 | **Rekap Bank THR** | Menampilkan data Operator & Security; tambah kolom Jumlah; tambah RINCIAN JUMLAH per divisi (saat filter SEMUA) |

**Tidak ada perubahan database/migration** — hanya update controller, export, view, dan layout.

---

## 📁 DAFTAR FILE YANG HARUS DI-COPY

| No | File | Modul |
|----|------|-------|
| 1 | `app/Http/Controllers/ClosingThrController.php` | Closing THR |
| 2 | `app/Http/Controllers/SlipThrController.php` | Slip THR |
| 3 | `app/Http/Controllers/LaporanThrController.php` | Rekap THR Operator, Rekap THR Staff |
| 4 | `app/Http/Controllers/RekapBankThrController.php` | Rekap Bank THR |
| 5 | `app/Exports/RekapBankThrExport.php` | Rekap Bank THR |
| 6 | `resources/views/laporan/rekap-bank-thr/index.blade.php` | Rekap Bank THR |
| 7 | `resources/views/laporan/rekap-bank-thr/preview.blade.php` | Rekap Bank THR |
| 8 | `resources/views/layouts/app.blade.php` | Menu (posisi Rekap Bank THR) |
| 9 | `routes/web.php` | Route Rekap Bank THR dll (wajib jika route belum ada di server) |

**Total: 9 file** (8 file modul + 1 file routes jika diperlukan)

> **Catatan:** Untuk fitur cetak PDF sulit dikonversi ke Excel (html2canvas), lihat **DEPLOY_PRINT_PDF_SULIT_CONVERT_EXCEL_UBUNTU.md**

---

## ✅ LANGKAH 1: BACKUP (DISARANKAN)

```bash
# Login ke server Ubuntu
ssh root@192.168.10.40

# Masuk ke direktori aplikasi
cd /var/www/html/hris-seven-payroll

# Backup tanggal
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)

# Backup semua file
cp app/Http/Controllers/ClosingThrController.php app/Http/Controllers/ClosingThrController.php.backup_$BACKUP_DATE
cp app/Http/Controllers/SlipThrController.php app/Http/Controllers/SlipThrController.php.backup_$BACKUP_DATE
cp app/Http/Controllers/LaporanThrController.php app/Http/Controllers/LaporanThrController.php.backup_$BACKUP_DATE
cp app/Http/Controllers/RekapBankThrController.php app/Http/Controllers/RekapBankThrController.php.backup_$BACKUP_DATE
cp app/Exports/RekapBankThrExport.php app/Exports/RekapBankThrExport.php.backup_$BACKUP_DATE
cp resources/views/laporan/rekap-bank-thr/index.blade.php resources/views/laporan/rekap-bank-thr/index.blade.php.backup_$BACKUP_DATE
cp resources/views/laporan/rekap-bank-thr/preview.blade.php resources/views/laporan/rekap-bank-thr/preview.blade.php.backup_$BACKUP_DATE
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.backup_$BACKUP_DATE
cp routes/web.php routes/web.php.backup_$BACKUP_DATE 2>/dev/null || true

# Verifikasi backup
ls -la app/Http/Controllers/*.backup_*
ls -la app/Exports/*.backup_*
ls -la resources/views/laporan/rekap-bank-thr/*.backup_*
ls -la resources/views/layouts/*.backup_*
```

---

## ✅ LANGKAH 2: COPY FILE DARI LOCAL KE SERVER

### **Opsi A: SCP dari Windows (Batch)**

```bash
# Dari Windows (Git Bash atau PowerShell)
cd C:\xampp\htdocs\hris-seven-payroll

# Definisi server (sesuaikan IP jika berbeda)
SERVER="root@192.168.10.40"
REMOTE_PATH="/var/www/html/hris-seven-payroll"

# Copy semua file sekaligus
scp app/Http/Controllers/ClosingThrController.php $SERVER:/tmp/
scp app/Http/Controllers/SlipThrController.php $SERVER:/tmp/
scp app/Http/Controllers/LaporanThrController.php $SERVER:/tmp/
scp app/Http/Controllers/RekapBankThrController.php $SERVER:/tmp/
scp app/Exports/RekapBankThrExport.php $SERVER:/tmp/
scp resources/views/laporan/rekap-bank-thr/index.blade.php $SERVER:/tmp/rekap-bank-thr-index.blade.php
scp resources/views/laporan/rekap-bank-thr/preview.blade.php $SERVER:/tmp/rekap-bank-thr-preview.blade.php
scp resources/views/layouts/app.blade.php $SERVER:/tmp/app.blade.php
scp routes/web.php $SERVER:/tmp/web.php
```

### **Opsi B: FileZilla / WinSCP**

1. Connect ke server: `192.168.10.40`
2. Upload file sesuai tabel di atas ke folder `/tmp/` di server
3. Untuk view rekap-bank-thr, gunakan nama sementara di `/tmp/` agar tidak bentrok path

---

## ✅ LANGKAH 3: TEMPATKAN FILE DI SERVER

```bash
# Login ke server
ssh root@192.168.10.40

cd /var/www/html/hris-seven-payroll

# Copy Controller
cp /tmp/ClosingThrController.php app/Http/Controllers/ClosingThrController.php
cp /tmp/SlipThrController.php app/Http/Controllers/SlipThrController.php
cp /tmp/LaporanThrController.php app/Http/Controllers/LaporanThrController.php
cp /tmp/RekapBankThrController.php app/Http/Controllers/RekapBankThrController.php

# Copy Export
cp /tmp/RekapBankThrExport.php app/Exports/RekapBankThrExport.php

# Copy View Rekap Bank THR
cp /tmp/rekap-bank-thr-index.blade.php resources/views/laporan/rekap-bank-thr/index.blade.php
cp /tmp/rekap-bank-thr-preview.blade.php resources/views/laporan/rekap-bank-thr/preview.blade.php

# Copy Layout
cp /tmp/app.blade.php resources/views/layouts/app.blade.php

# Copy Routes (wajib jika error "Route [rekap-bank-thr.index] not defined")
cp /tmp/web.php routes/web.php

# Set ownership ke www-data
sudo chown -R www-data:www-data app/Http/Controllers/ClosingThrController.php \
  app/Http/Controllers/SlipThrController.php \
  app/Http/Controllers/LaporanThrController.php \
  app/Http/Controllers/RekapBankThrController.php \
  app/Exports/RekapBankThrExport.php \
  resources/views/laporan/rekap-bank-thr/ \
  resources/views/layouts/app.blade.php \
  routes/web.php

# Set permission
sudo chmod 644 app/Http/Controllers/ClosingThrController.php \
  app/Http/Controllers/SlipThrController.php \
  app/Http/Controllers/LaporanThrController.php \
  app/Http/Controllers/RekapBankThrController.php \
  app/Exports/RekapBankThrExport.php \
  resources/views/laporan/rekap-bank-thr/*.blade.php \
  resources/views/layouts/app.blade.php \
  routes/web.php
```

---

## ✅ LANGKAH 4: CLEAR CACHE LARAVEL

```bash
cd /var/www/html/hris-seven-payroll

php artisan view:clear
php artisan cache:clear
php artisan config:clear

# PENTING: Clear route cache agar route baru/updated terbaca
php artisan route:clear

# Opsional: optimize (jangan route:cache jika sering update routes)
php artisan view:cache
# php artisan route:cache  # skip jika masih ada update route
```

---

## ✅ LANGKAH 5: VERIFIKASI

### **1. Cek File**

```bash
ls -la app/Http/Controllers/ClosingThrController.php
ls -la app/Http/Controllers/SlipThrController.php
ls -la app/Http/Controllers/LaporanThrController.php
ls -la app/Http/Controllers/RekapBankThrController.php
ls -la app/Exports/RekapBankThrExport.php
ls -la resources/views/laporan/rekap-bank-thr/
ls -la resources/views/layouts/app.blade.php
```

### **2. Test di Browser**

#### **Closing THR**
1. Buka: **Proses THR → Closing THR**
2. Pilih periode, klik **Proses Closing THR**
3. Verifikasi: Data diproses untuk Management, Staff, Operator, Security
4. Cek `t_closing_thr`: Operator & Security punya `decGajiPokok` dan `decNilaiTHR` terisi; Management & Staff punya nilai null

#### **Slip THR**
1. Buka: **Laporan → Slip THR**
2. Pilih filter, preview
3. Verifikasi: Hanya Operator dan Security yang bisa dipilih dan dicetak

#### **Rekap THR Operator**
1. Buka: **Laporan → Rekap THR Operator**
2. Pilih tahun, divisi, agama, preview
3. Verifikasi: Data menampilkan Operator dan Security

#### **Rekap THR Staff**
1. Buka: **Laporan → Rekap THR Staff**
2. Pilih filter, preview
3. Verifikasi: Data menampilkan Staff dan Management

#### **Rekap Bank THR**
1. Buka: **Laporan → Rekap Bank THR** (posisi di bawah Rekap THR Staff)
2. Pilih tanggal THR, filter SEMUA divisi, preview
3. Verifikasi:
   - Data Operator dan Security tampil
   - Ada kolom **Jumlah** (sama dengan Nilai THR)
   - Ada tabel **RINCIAN JUMLAH** per divisi di bawah
4. Export Excel: Cek kolom Jumlah dan RINCIAN JUMLAH ada

---

## 📝 CHECKLIST DEPLOYMENT

- [ ] Backup 9 file
- [ ] Copy 9 file ke server
- [ ] Set ownership `www-data:www-data`
- [ ] Set permission `644`
- [ ] Clear Laravel cache (view, config, route)
- [ ] Test Closing THR (proses untuk semua group)
- [ ] Test Slip THR (Operator & Security)
- [ ] Test Rekap THR Operator (Operator & Security; cetak via html2canvas)
- [ ] Test Rekap THR Staff (Staff & Management)
- [ ] Test Rekap Bank THR (Operator & Security, kolom Jumlah, RINCIAN JUMLAH)
- [ ] Verifikasi urutan menu (Rekap Bank THR di bawah Rekap THR Staff)

---

## ⚠️ CATATAN PENTING

1. **Tidak ada migration** — tidak perlu `php artisan migrate`
2. **Data Closing THR lama**: Jika Closing THR pernah dijalankan sebelum update, data Security mungkin belum ada. Jalankan ulang proses Closing THR untuk periode yang mencakup Security agar muncul di Rekap Bank THR dan Slip THR.
3. **Restore backup** jika ada error:
   ```bash
   cp app/Http/Controllers/ClosingThrController.php.backup_YYYYMMDD_HHMMSS app/Http/Controllers/ClosingThrController.php
   # ... dan seterusnya untuk file lainnya
   ```

---

## 🔧 TROUBLESHOOTING

### Error: "Route [rekap-bank-thr.index] not defined"

Route Rekap Bank THR tidak terdaftar. Jalankan di server:

```bash
cd /var/www/html/hris-seven-payroll

# 1. Clear route cache (wajib)
php artisan route:clear

# 2. Jika masih error, copy routes/web.php dari local ke server
# Pastikan file routes/web.php di server berisi route rekap-bank-thr
# Cek: grep -n "rekap-bank-thr" routes/web.php
# Harus ada baris: Route::get('rekap-bank-thr', ...

# 3. Setelah copy web.php, clear lagi
php artisan route:clear
php artisan cache:clear
```

**Penyebab:** Route cache (`bootstrap/cache/routes-v7.php`) usang, atau `routes/web.php` di server tidak memiliki route Rekap Bank THR (versi lama).

---

### Error: "Permission denied" pada storage/framework/views

Laravel membutuhkan hak tulis di folder `storage` dan `bootstrap/cache` agar bisa mengompilasi view. Jalankan di server:

```bash
cd /var/www/html/hris-seven-payroll

# Set ownership ke www-data (user yang dipakai Apache/Nginx)
sudo chown -R www-data:www-data storage bootstrap/cache

# Set permission untuk direktori (775 = rwxrwxr-x)
sudo chmod -R 775 storage bootstrap/cache

# Jika masih error, bisa coba 777 (lebih permisif, untuk debug)
# sudo chmod -R 777 storage bootstrap/cache

# Clear view cache supaya Laravel compile ulang
php artisan view:clear
php artisan cache:clear
```

**Penyebab:** Setelah deploy/copy file, ownership atau permission folder `storage` dan `bootstrap/cache` berubah sehingga user `www-data` (web server) tidak bisa menulis file cache view.

---

### Security tidak muncul di Rekap Bank THR / Slip THR
- Pastikan proses **Closing THR** sudah dijalankan ulang untuk periode yang mencakup karyawan Security
- Cek `t_closing_thr`: harus ada record dengan `vcGroupPegawai = 'Security'`

### Kolom Jumlah / RINCIAN JUMLAH tidak tampil di Rekap Bank THR
- Clear cache: `php artisan view:clear && php artisan cache:clear`
- Hard refresh browser (Ctrl+F5)

### Menu Rekap Bank THR posisi salah
- Pastikan `resources/views/layouts/app.blade.php` ter-copy dengan benar
- Clear cache view

---

## 📞 INFORMASI SERVER

- **Server IP:** 192.168.10.40
- **Domain:** http://hr.abncorp.lan
- **Lokasi Aplikasi:** /var/www/html/hris-seven-payroll
- **User:** root atau superadmin

---

**Selamat Deploy! 🚀**
