# 5. Komponen pendukung (teknologi & integritas kerangka)

---

## 5.1 Stack versi utama

| Komponen | Versi / target |
|----------|----------------|
| PHP | **^8.1** (`composer.json`) |
| Framework | **Laravel 10.x** (`laravel/framework` ^10.10) |
| Database | Biasanya **MySQL/MariaDB** |
| HTTP client | **Guzzle** ^7 bagi integrasi API |
| Otentikasi API opsional | **Laravel Sanctum** ^3 |

---

## 5.2 Pustaka penting Composer

| Paket | Pemakaian |
|-------|-----------|
| `maatwebsite/excel` | Import / export Excel (`app/Exports/`, beberapa controller import). |

---

## 5.3 Struktur `app/` yang relevan

### HTTP (`app/Http/`)

| Bagian | Keterangan |
|--------|------------|
| **`Kernel.php`** | Grup middleware `web` termasuk `TrackUserSession` untuk modul login aktif. |
| **`Middleware/PermissionMiddleware.php`** | Cek hak `permission:...`. |
| **`Controllers/`** | Satu kumpulan controller domain (lihat bab 02). |

### Services (`app/Services/`)

| Berkas | Fungsi ringkas |
|--------|----------------|
| `ActivityLogService.php` | Mencatat aktivitas aplikasi ke `activity_logs`. |
| `LemburCalculationService.php` | Perhitungan lembur untuk proses closing. |
| `SecurityAbsensiService.php` | Logika browse absensi security / satpam. |
| `HrisApiService.php`, `HrisApiAbsentService.php`, `HrisApiPermitService.php` | Konsumsi API HR eksternal (cuti/feeder absensi izin). |

### Models (`app/Models/`)

- Banyak model mendefinisikan **`protected $table`** eksplisit (`m_*`, `t_*`).
- Model **`Closing`** memakai **primary key komposit**, `incrementing = false`.

---

## 5.4 Konfigurasi

| File | Isi bermakna bisnis |
|------|---------------------|
| `.env` | DB, APP_URL, `HRIS_API_*`, `LOGIN_ACTIVE_WITHIN_MINUTES`, dsb. |
| `config/hris_api.php` | Default feeder HRIS API (`base_url`, kredensial dari env). |
| `config/excel.php` | Paket Excel. |

---

## 5.5 Frontend (Blade)

- **Bootstrap 5** + **Font Awesome 6** lewat CDN di layout utama.
- Layout: `resources/views/layouts/app.blade.php` dan `resources/views/absen/layouts/app.blade.php`.

---

## 5.6 Keamanan aplikasi

| Mekanisme | Lokasi / perilaku |
|-----------|-------------------|
| `auth` / `guest` | Rute login vs area terproteksi. |
| Middleware `permission:...` | Slug permission granular. |
| CSRF | `VerifyCsrfToken` pada grup `web`. |

---

## 5.7 Logging

- Kesalahan runtime: **`storage/logs/laravel.log`**.
- Pastikan **writable** untuk `storage/` dan `bootstrap/cache` pada server produksi.

---

## 5.8 Testing / dev tools

PHPUnit, Pint, Collision tercantum sebagai `require-dev`; cakupan tes otomatis modul klasik bisa terbatas. QA sering memakai skenario bisnis manual (closing sampai rekonsiliasi bank).
