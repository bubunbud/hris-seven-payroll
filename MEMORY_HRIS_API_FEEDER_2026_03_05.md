# 📚 Memory: HRIS API Feeder - Cuti & Izin Tidak Masuk

**Tanggal:** 5 Maret 2026  
**Status:** Pembahasan dijeda, siap dilanjutkan

---

## 🎯 Ringkasan Pembahasan (Update Terakhir)

### 1. Modul List Pengajuan Cuti API

**Endpoint:** `GET /v1/leaves/requests`  
**Base URL:** `https://hris-api.abadinusagroup.com`  
**Config:** `.env` → HRIS_API_BASE_URL, HRIS_API_USERNAME, HRIS_API_PASSWORD, HRIS_API_TIMEOUT

**Fitur:**
- Filter periode (default: sebulan terakhir)
- Pagination: fetch semua halaman (page_size=100)
- **Hanya menampilkan status Approved/Completed**
- Import ke `t_tidak_masuk` (feeder cuti)

**Mapping leave_type_name → vcKodeAbsen:**
| API | vcKodeAbsen |
|-----|-------------|
| Cuti Tahunan | C010 |
| Cuti Umroh / Umroh | C013 |
| Cuti Bersama / Cuti Melahirkan | C012 |
| Cuti Perkawinan karyawan | I001 |
| Cuti Kematian Orang tua/Mertua | I001 |
| Sakit, Sick, Surat Sakit, Cuti Sakit, Medical | S010 |
| Izin Pribadi | I002 |
| Izin Resmi | I001 |

**File:** `app/Services/HrisApiService.php`, `app/Http/Controllers/ListPengajuanCutiApiController.php`, `resources/views/list-pengajuan-cuti-api/index.blade.php`

---

### 2. Modul List Pengajuan Izin API

**Endpoint:** `GET /v1/absents/requests` (bukan /v1/permits)  
**Endpoint subordinate:** `GET /v1/absents/requests/subordinate`  
**Base URL:** Sama (hris-api.abadinusagroup.com)

**Fitur:**
- Filter periode + checkbox "Bawahan" (subordinate)
- **Hanya menampilkan status Approved/Completed**
- Tipe: **Sakit (S010)** dan **Izin Pribadi (I002)**
- Import ke `t_tidak_masuk` (expand date range ke satu baris per hari)

**Mapping type/absent_type → vcKodeAbsen:**
| API type | vcKodeAbsen |
|----------|-------------|
| SAKIT, SICK, SICK_LEAVE, MEDICAL, CUTI_SAKIT, SURAT_SAKIT | S010 |
| IZIN, IZIN_PRIBADI, PERSONAL_LEAVE, TIDAK_MASUK | I002 |

**Tidak ditampilkan:** KELUAR_KOMPLEK, MASUK_SIANG, PULANG_CEPAT (sesuai permintaan user)

**File:** `app/Services/HrisApiAbsentService.php`, `app/Http/Controllers/ListPengajuanIzinApiController.php`, `resources/views/list-pengajuan-izin-api/index.blade.php`

---

## 📌 Riwayat Perubahan (Per Session)

1. **Semua status:** User minta tampilkan semua status (Pending, Approved, Completed, Rejected) → ditambah method getAllPermits, getAllLeaves
2. **Sakit tidak muncul:** Coba Permits + Leaves API, expand mapping. Ternyata user kasih info: **API yang benar untuk tidak masuk = Absents API** (`/v1/absents/requests`)
3. **Ganti ke Absents API:** Buat HrisApiAbsentService, ganti List Pengajuan Izin dari Permits+Leaves ke Absents API
4. **Hanya Sakit:** User minta KELUAR_KOMPLEK/MASUK_SIANG/PULANG_CEPAT tidak ditampilkan, hanya Sakit
5. **Tambah Izin Pribadi:** User minta tambah Izin Pribadi (status Approved/Completed)
6. **Filter status:** User minta **kedua modul** hanya menampilkan status Approved/Completed
7. **Manual deploy Ubuntu:** Buat `DEPLOY_LIST_PENGAJUAN_CUTI_IZIN_API_UBUNTU.md`

---

## 📁 File Terkait

- `config/hris_api.php` — config API
- `DEPLOY_LIST_PENGAJUAN_CUTI_API.md` — panduan deploy cuti (standar)
- `DEPLOY_LIST_PENGAJUAN_IZIN_API.md` — panduan deploy izin (standar)
- `DEPLOY_LIST_PENGAJUAN_CUTI_IZIN_API_UBUNTU.md` — **manual deploy ke Ubuntu server** (kedua modul)

---

## 🔜 Konteks untuk Lanjutan

- **User:** HRIS Seven Payroll
- **Topik:** Integrasi API HRIS eksternal sebagai feeder ke modul Izin Tidak Masuk
- **Modul Cuti:** Selesai — endpoint GET /v1/leaves/requests, hanya Approved/Completed
- **Modul Izin:** Selesai — endpoint GET /v1/absents/requests, Sakit + Izin Pribadi, hanya Approved/Completed
- **Permission:** view-list-pengajuan-cuti-api, view-list-pengajuan-izin-api
- **Menu:** Settings → List Pengajuan Cuti API, List Pengajuan Izin API
