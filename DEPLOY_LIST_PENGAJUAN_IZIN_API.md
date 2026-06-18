# 📋 Panduan Deployment: List Pengajuan Izin dari API

**Tanggal:** Maret 2026  
**Fitur:** Modul feeder pengajuan izin (permits) dari API HRIS ke tabel `t_tidak_masuk`  
**Endpoint:** GET /v1/permits  
**Status:** Approved dan Completed

---

## 📋 RINGKASAN

Modul ini mengambil daftar pengajuan izin dari API HRIS eksternal (endpoint `/v1/permits`) dan memungkinkan import ke tabel `t_tidak_masuk` sebagai feeder Izin Tidak Masuk. Menggunakan base URL, username, password yang sama dengan modul List Pengajuan Cuti API.

---

## 📁 FILE YANG HARUS DI-COPY

| No | File | Keterangan |
|----|------|------------|
| 1 | `app/Services/HrisApiPermitService.php` | Service API permits |
| 2 | `app/Http/Controllers/ListPengajuanIzinApiController.php` | Controller |
| 3 | `resources/views/list-pengajuan-izin-api/index.blade.php` | View |

---

## ✅ LANGKAH 1: PERMISSION

```bash
php artisan db:seed --class=UpdatePermissionsSeeder
```

---

## ✅ LANGKAH 2: CLEAR CACHE

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## ✅ LANGKAH 3: VERIFIKASI

1. Login → **Settings → List Pengajuan Izin API**
2. Pilih periode → **Ambil Data dari API**
3. Pilih baris → **Import ke Tidak Masuk**
4. Cek **Absensi → Izin Tidak Masuk**

---

## 📝 MAPPING PURPOSE/TYPE → vcKodeAbsen

Sistem cek field **purpose** dan **type** (API bisa pakai salah satu).

| API purpose/type | vcKodeAbsen |
|-----------------|-------------|
| SAKIT, SICK, SICK_LEAVE, MEDICAL, CUTI_SAKIT, SURAT_SAKIT | S010 |
| KELUAR_KOMPLEK, MASUK_SIANG, PULANG_CEPAT | I002 |
| TIDAK_MASUK / IZIN | I002 |

---

## ⚠️ CATATAN

- Permits punya **single date** → di-import sebagai 1 hari (dtTanggalMulai = dtTanggalSelesai)
- NIK harus ada di m_karyawan
- vcKodeAbsen (I002, S010) harus ada di m_jenis_absen
- Duplikasi dicek: vcNik + vcKodeAbsen + dtTanggalMulai + dtTanggalSelesai

---

**Selamat Deploy! 🚀**
