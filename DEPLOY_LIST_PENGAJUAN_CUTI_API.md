# 📋 Panduan Deployment: List Pengajuan Cuti dari API

**Tanggal:** Maret 2026  
**Fitur:** Modul feeder pengajuan cuti dari API HRIS eksternal ke tabel `t_tidak_masuk`  
**Status yang di-import:** Hanya **Approved**

---

## 📋 RINGKASAN

Modul ini mengambil daftar pengajuan cuti dari API HRIS eksternal (`https://hris-api.abndev.duckdns.org`) dan memungkinkan user untuk meng-import data yang sudah disetujui (Approved) ke tabel `t_tidak_masuk` sebagai feeder cuti.

---

## 📁 FILE YANG HARUS DI-COPY

| No | File | Keterangan |
|----|------|------------|
| 1 | `config/hris_api.php` | Konfigurasi API |
| 2 | `app/Services/HrisApiService.php` | Service koneksi API |
| 3 | `app/Http/Controllers/ListPengajuanCutiApiController.php` | Controller |
| 4 | `resources/views/list-pengajuan-cuti-api/index.blade.php` | View |

---

## ✅ LANGKAH 1: KONFIGURASI .env

Tambahkan di file `.env`:

```env
# HRIS API (External - feeder pengajuan cuti)
HRIS_API_BASE_URL=https://hris-api.abadinusagroup.com
HRIS_API_USERNAME=superadmin
HRIS_API_PASSWORD=<password_api>
HRIS_API_TIMEOUT=60
```

---

## ✅ LANGKAH 2: PERMISSION

Jalankan seeder untuk menambahkan permission baru (jika belum):

```bash
php artisan db:seed --class=UpdatePermissionsSeeder
```

Atau jalankan full RolePermissionSeeder:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

Assign permission `view-list-pengajuan-cuti-api` ke role yang memerlukan akses (Admin sudah dapat semua permission).

---

## ✅ LANGKAH 3: CLEAR CACHE

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## ✅ LANGKAH 4: VERIFIKASI

1. Login ke aplikasi
2. Buka **Settings → List Pengajuan Cuti API**
3. Klik **"Ambil Data dari API"** → pastikan data tampil (hanya status Approved)
4. Pilih beberapa baris → klik **"Import ke Tidak Masuk"**
5. Cek di **Absensi → Izin Tidak Masuk** bahwa data sudah masuk

---

## 📝 MAPPING JENIS CUTI

| API leave_type_name | vcKodeAbsen (m_jenis_absen) |
|--------------------|-----------------------------|
| Cuti Tahunan       | C010                        |
| Cuti Bersama / Cuti Melahirkan | C012              |
| Cuti Umroh / Umroh             | C013              |
| Cuti Perkawinan karyawan       | I001              |
| Cuti Kematian Orang tua/Mertua | I001              |
| Sakit              | S010                        |
| Izin Pribadi       | I002                        |
| Izin Resmi         | I001                        |

---

## 📄 FILTER & PAGINATION

**Filter periode (default: sebulan terakhir):**
- Parameter API: `start_date`, `end_date` (format Y-m-d)
- User dapat pilih Dari Tanggal dan Sampai Tanggal di form

**Pagination:**
- API: default 20/halaman, max ~100/halaman
- Modul otomatis fetch **semua halaman** dalam periode yang dipilih

---

## ⚠️ CATATAN

1. **NIK harus ada** di `m_karyawan` — jika NIK dari API tidak ada di master karyawan, record akan di-skip
2. **vcKodeAbsen** harus ada di `m_jenis_absen` — pastikan C010, C012, S010, I002, I001 tersedia
3. **Duplikasi** dicek berdasarkan: vcNik + vcKodeAbsen + dtTanggalMulai + dtTanggalSelesai — jika sudah ada akan di-update
4. Server perlu akses internet ke `hris-api.abndev.duckdns.org`

---

## 🔧 TROUBLESHOOTING

**Gagal login / Token tidak diterima**
- Cek HRIS_API_USERNAME dan HRIS_API_PASSWORD di .env
- Pastikan server bisa akses https://hris-api.abadinusagroup.com

**NIK tidak ditemukan**
- Pastikan NIK di API sama dengan NIK di m_karyawan
- Cek master karyawan sudah di-sync

**Kode absen tidak ada**
- Pastikan m_jenis_absen memiliki C010, C012, S010, I002, I001

---

**Selamat Deploy! 🚀**
