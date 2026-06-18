# 📋 Panduan Deployment: Print ke PDF Sulit Dikonversi ke Excel (Ubuntu Server)

**Tanggal:** Februari 2026  
**Server:** Ubuntu Production  
**Fitur:** Cetak via html2canvas — hasil PDF berupa image, sulit dikonversi ke Excel

---

## 📋 RINGKASAN UPDATE

Update ini mengubah cara cetak di 2 halaman:

| Halaman | File |
|---------|------|
| Rekap THR Operator | `laporan/laporan-thr/preview.blade.php` |
| Browse Absensi Karyawan Per Periode | `absen/print.blade.php` |

- **Sebelum:** Cetak langsung → PDF berisi teks yang bisa dipilih → mudah dikonversi ke Excel
- **Sesudah:** Cetak via html2canvas → konten di-render sebagai image → PDF berisi gambar → sulit dikonversi ke Excel

**File yang berubah:** 2 file view.

---

## 📁 FILE YANG HARUS DI-COPY

| No | File | Keterangan |
|----|------|-------------|
| 1 | `resources/views/laporan/laporan-thr/preview.blade.php` | Rekap THR Operator (tombol Cetak pakai html2canvas) |
| 2 | `resources/views/absen/print.blade.php` | Browse Absensi Karyawan Per Periode (tombol Cetak pakai html2canvas) |

**Total: 2 file**

---

## ✅ LANGKAH 1: BACKUP

```bash
# Login ke server Ubuntu
ssh root@192.168.10.40

cd /var/www/html/hris-seven-payroll

# Backup
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
cp resources/views/laporan/laporan-thr/preview.blade.php resources/views/laporan/laporan-thr/preview.blade.php.backup_$BACKUP_DATE
cp resources/views/absen/print.blade.php resources/views/absen/print.blade.php.backup_$BACKUP_DATE

# Verifikasi
ls -la resources/views/laporan/laporan-thr/preview.blade.php*
ls -la resources/views/absen/print.blade.php*
```

---

## ✅ LANGKAH 2: COPY FILE DARI LOCAL KE SERVER

### **Opsi A: SCP dari Windows**

```bash
cd C:\xampp\htdocs\hris-seven-payroll

scp resources/views/laporan/laporan-thr/preview.blade.php root@192.168.10.40:/tmp/laporan-thr-preview.blade.php
scp resources/views/absen/print.blade.php root@192.168.10.40:/tmp/absen-print.blade.php
```

### **Opsi B: FileZilla / WinSCP**

1. Connect ke server
2. Upload `preview.blade.php` ke `/tmp/` di server

---

## ✅ LANGKAH 3: TEMPATKAN FILE DI SERVER

```bash
ssh root@192.168.10.40

cd /var/www/html/hris-seven-payroll

# Copy file
cp /tmp/laporan-thr-preview.blade.php resources/views/laporan/laporan-thr/preview.blade.php
cp /tmp/absen-print.blade.php resources/views/absen/print.blade.php

# Set ownership
sudo chown www-data:www-data resources/views/laporan/laporan-thr/preview.blade.php resources/views/absen/print.blade.php

# Set permission
sudo chmod 644 resources/views/laporan/laporan-thr/preview.blade.php resources/views/absen/print.blade.php
```

---

## ✅ LANGKAH 4: CLEAR CACHE

```bash
cd /var/www/html/hris-seven-payroll

php artisan view:clear
php artisan cache:clear
```

---

## ✅ LANGKAH 5: VERIFIKASI

**Rekap THR Operator:**
1. Buka **Laporan → Rekap THR Operator**
2. Pilih filter → **Preview & Cetak**
3. Klik tombol **Cetak** → pastikan "Memproses..." lalu jendela baru terbuka
4. Save as PDF → coba pilih teks → harus tidak bisa (karena berupa image)

**Browse Absensi:**
1. Buka **Absensi → Browse Absensi Karyawan Per Periode**
2. Pilih periode, filter → **Preview**
3. Klik **Cetak** → buka halaman print
4. Klik tombol **Cetak** di halaman print → "Memproses..." lalu jendela baru terbuka
5. Save as PDF → coba pilih teks → harus tidak bisa (karena berupa image)

---

## 📝 CHECKLIST

- [ ] Backup file `preview.blade.php`
- [ ] Copy 1 file ke server
- [ ] Set ownership `www-data:www-data`
- [ ] Set permission `644`
- [ ] Clear view cache
- [ ] Test cetak Rekap THR Operator
- [ ] Test cetak Browse Absensi
- [ ] Verifikasi PDF tidak bisa select text / sulit convert ke Excel

---

## ⚠️ CATATAN

1. **html2canvas** di-load dari CDN (cdnjs.cloudflare.com) — server perlu akses internet
2. Jika CDN gagal, cetak akan fallback ke `window.print()` biasa
3. Tidak ada perubahan database atau migration

---

## 🔧 TROUBLESHOOTING

**Cetak gagal / "Memproses..." tidak selesai**
- Cek akses internet server ke cdnjs.cloudflare.com
- Buka DevTools (F12) → Console, lihat error
- Jika html2canvas gagal load, akan fallback ke cetak biasa

**Restore backup**
```bash
cp resources/views/laporan/laporan-thr/preview.blade.php.backup_YYYYMMDD_HHMMSS resources/views/laporan/laporan-thr/preview.blade.php
cp resources/views/absen/print.blade.php.backup_YYYYMMDD_HHMMSS resources/views/absen/print.blade.php
```

---

## 📞 INFORMASI SERVER

- **Server IP:** 192.168.10.40
- **Domain:** http://hr.abncorp.lan
- **Lokasi Aplikasi:** /var/www/html/hris-seven-payroll

---

**Selamat Deploy! 🚀**
