# Checklist Deploy: Modul Form BPD (Biaya Perjalanan Dinas)

**Tanggal Deploy:** _______________  
**Deployed By:** _______________  
**Server:** Ubuntu 192.168.10.40

---

## 📋 Pre-Deployment

- [ ] Backup database (`mysqldump`)
- [ ] Backup file existing:
  - [ ] `routes/web.php`
  - [ ] `resources/views/layouts/app.blade.php`
  - [ ] `app/Models/PerjalananDinasHeader.php` (jika sudah ada)

---

## 📁 File Upload

### Migration Files
- [ ] `database/migrations/2026_02_13_000001_create_t_biaya_perjalanan_dinas_header_table.php`
- [ ] `database/migrations/2026_02_13_000002_create_t_biaya_perjalanan_dinas_detail_table.php`

### Models
- [ ] `app/Models/BiayaPerjalananDinasHeader.php`
- [ ] `app/Models/BiayaPerjalananDinasDetail.php`

### Controller
- [ ] `app/Http/Controllers/BiayaPerjalananDinasController.php`

### Views
- [ ] `resources/views/biaya-perjalanan-dinas/index.blade.php`
- [ ] `resources/views/biaya-perjalanan-dinas/print.blade.php`

### Updates
- [ ] `routes/web.php` (tambah route BPD)
- [ ] `resources/views/layouts/app.blade.php` (tambah menu sidebar)
- [ ] `app/Models/PerjalananDinasHeader.php` (tambah relasi)

**Total: 10 file**

---

## ⚙️ Server Configuration

- [ ] Set file permission (`chown`, `chmod`)
- [ ] Set storage & cache permission (775)
- [ ] Jalankan migration (`php artisan migrate`)
- [ ] Clear cache:
  - [ ] `php artisan cache:clear`
  - [ ] `php artisan config:clear`
  - [ ] `php artisan route:clear`
  - [ ] `php artisan view:clear`

---

## ✅ Verification

### Database
- [ ] Tabel `t_biaya_perjalanan_dinas_header` sudah dibuat
- [ ] Tabel `t_biaya_perjalanan_dinas_detail` sudah dibuat
- [ ] Foreign key constraint sudah benar
- [ ] Index sudah dibuat

### Routes
- [ ] Route `biaya-perjalanan-dinas.index` terdaftar
- [ ] Route `biaya-perjalanan-dinas.store` terdaftar
- [ ] Route `biaya-perjalanan-dinas.show` terdaftar
- [ ] Route `biaya-perjalanan-dinas.update` terdaftar
- [ ] Route `biaya-perjalanan-dinas.destroy` terdaftar
- [ ] Route `biaya-perjalanan-dinas.print` terdaftar
- [ ] Route `biaya-perjalanan-dinas.get-rpd-data` terdaftar
- [ ] Route `biaya-perjalanan-dinas.convert-terbilang` terdaftar

### UI/UX
- [ ] Menu "Form Biaya Perjalanan Dinas" muncul di sidebar
- [ ] Menu bisa diakses (tidak error 403/404)

---

## 🧪 Testing

### CRUD Operations
- [ ] **Create:** Tambah data BPD baru berhasil
- [ ] **Read:** List data BPD tampil dengan benar
- [ ] **Update:** Edit dan update data BPD berhasil
- [ ] **Delete:** Hapus data BPD berhasil

### Auto-Fill Features
- [ ] Pilih No. RPD → Auto-fill "Pemberi Tugas"
- [ ] Pilih No. RPD → Auto-fill "Karyawan yang Ditugaskan"
- [ ] Pilih No. RPD → Auto-fill "Tanggal Dinas"

### Auto-Generate Features
- [ ] No. BPD ter-generate otomatis saat simpan
- [ ] Counter Detail ter-generate otomatis saat tambah baris

### Calculations
- [ ] Total Pengeluaran terhitung otomatis
- [ ] Kekurangan/Kelebihan terhitung otomatis
- [ ] Konversi terbilang bekerja dengan benar

### Print
- [ ] Tombol "Print" muncul di list
- [ ] Print form BPD tampil dengan layout benar
- [ ] Semua field ter-render:
  - [ ] No. BPD, No. RPD
  - [ ] Nama Penerima Tugas
  - [ ] Tanggal Dinas
  - [ ] Pemberi Tugas
  - [ ] Kasbon (Nilai & Terbilang)
  - [ ] Signature Dept. Keuangan
  - [ ] Tabel Laporan Biaya (detail)
  - [ ] Summary (Total Pengeluaran, Kekurangan/Kelebihan)
  - [ ] Laporan Singkat
  - [ ] Otorisasi (4 signature)

### Integration
- [ ] BPD ter-link dengan RPD yang dipilih
- [ ] Foreign key constraint bekerja (tidak bisa hapus RPD yang punya BPD)
- [ ] Relasi model bekerja dengan benar

---

## 🐛 Issues Found

**Issue 1:**
- Description: _________________________________
- Status: [ ] Fixed [ ] Pending
- Solution: _________________________________

**Issue 2:**
- Description: _________________________________
- Status: [ ] Fixed [ ] Pending
- Solution: _________________________________

---

## ✅ Final Sign-Off

- [ ] Semua checklist sudah completed
- [ ] Tidak ada error yang blocking
- [ ] Testing berhasil semua
- [ ] User bisa menggunakan modul dengan normal

**Deployed By:** _______________  
**Date:** _______________  
**Time:** _______________  
**Status:** [ ] Success [ ] Partial [ ] Failed

**Notes:**
_________________________________
_________________________________
_________________________________




