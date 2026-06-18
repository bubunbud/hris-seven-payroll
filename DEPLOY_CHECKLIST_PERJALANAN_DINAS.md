# ✅ Deployment Checklist - Modul Form Perjalanan Dinas

**Tanggal:** 12 Februari 2026  
**Modul:** Form Perjalanan Dinas  
**Total Files:** 16 files

---

## 📋 Checklist File yang Perlu Di-copy

### ✅ Migration Files (6 files)
- [ ] `database/migrations/2026_02_11_023919_create_t_perjalanan_dinas_header_table.php`
- [ ] `database/migrations/2026_02_11_023927_create_t_perjalanan_dinas_karyawan_table.php`
- [ ] `database/migrations/2026_02_11_023930_create_t_perjalanan_dinas_jadwal_table.php`
- [ ] `database/migrations/2026_02_11_023932_create_t_perjalanan_dinas_hotel_table.php`
- [ ] `database/migrations/2026_02_11_023935_create_t_perjalanan_dinas_tiba_kembali_table.php`
- [ ] `database/migrations/2026_02_11_034150_add_tanggal_dinas_fields_to_t_perjalanan_dinas_header_table.php`

### ✅ Model Files (5 files)
- [ ] `app/Models/PerjalananDinasHeader.php`
- [ ] `app/Models/PerjalananDinasKaryawan.php`
- [ ] `app/Models/PerjalananDinasJadwal.php`
- [ ] `app/Models/PerjalananDinasHotel.php`
- [ ] `app/Models/PerjalananDinasTibaKembali.php`

### ✅ Controller File (1 file)
- [ ] `app/Http/Controllers/PerjalananDinasController.php`

### ✅ View Files (2 files)
- [ ] `resources/views/perjalanan-dinas/index.blade.php`
- [ ] `resources/views/perjalanan-dinas/print.blade.php`

### ✅ Routes & Layout (2 files)
- [ ] `routes/web.php` (update, tambahkan routes perjalanan-dinas)
- [ ] `resources/views/layouts/app.blade.php` (update, tambahkan menu sidebar)

---

## 🔧 Checklist Deployment Steps

### Pre-Deployment
- [ ] Backup database sudah dilakukan
- [ ] Backup file aplikasi (optional, jika menggunakan version control)

### File Copy
- [ ] Semua 16 file sudah di-copy ke server
- [ ] Struktur direktori sudah benar
- [ ] Permission file sudah di-set

### Database
- [ ] Migration berhasil dijalankan (6 migration files)
- [ ] Semua tabel berhasil dibuat
- [ ] Kolom `dtTanggalDinasDari`, `dtTanggalDinasSampai`, `intDurasiHari` ada di header
- [ ] Foreign key constraints berfungsi

### Cache & Permission
- [ ] Config cache cleared
- [ ] Route cache cleared
- [ ] View cache cleared
- [ ] Application cache cleared
- [ ] Permission `view-perjalanan-dinas` sudah ditambahkan
- [ ] Permission sudah di-assign ke role yang sesuai

### Verification
- [ ] Route terdaftar (8 routes)
- [ ] Menu sidebar muncul
- [ ] Tidak ada error di log

---

## 🧪 Checklist Testing

### Basic Functionality
- [ ] Halaman list dapat diakses
- [ ] Form tambah dapat dibuka
- [ ] Form edit dapat dibuka
- [ ] Form dapat disimpan
- [ ] Form dapat di-update
- [ ] Form dapat dihapus

### Auto-Update Absensi ⭐
- [ ] Saat create form, absensi ter-insert/update
- [ ] Saat update form, absensi ter-update
- [ ] Hanya untuk hari kerja (Senin-Jumat)
- [ ] Hanya untuk karyawan aktif yang punya shift
- [ ] Data absensi benar di database

### Print
- [ ] Print view dapat diakses
- [ ] Layout print sesuai
- [ ] Data tercetak lengkap

---

## 📝 Deployment Notes

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Server:** _______________  
**Issues/Notes:** _______________

---

**Status:** ⬜ Success  ⬜ Failed  ⬜ Partial








