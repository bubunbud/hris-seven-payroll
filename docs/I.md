# Panduan pengembangan HRIS Seven Payroll

Dokumentasi ini mendeskripsikan aplikasi **HRIS Seven Payroll** berbasis **Laravel 10** secara terstruktur untuk tim pengembang, dokumentasi QA, dan operasional TI.

**Buka cepat Cursor:** tekan `Ctrl+P`, ketik: `docs I`

---

## Daftar dokumen

| No | Berkas | Isi singkat |
|----|--------|--------------|
| 1 | [01-rancangan-bisnis-proses-modul.md](01-rancangan-bisnis-proses-modul.md) | Alur bisnis per grup menu dan modul |
| 2 | [02-struktur-folder-dan-file-modul.md](02-struktur-folder-dan-file-modul.md) | Konvensi folder Laravel dan pemetaan file per area fitur |
| 3 | [03-formulasi-dan-alur-perhitungan.md](03-formulasi-dan-alur-perhitungan.md) | Input, proses, output (payroll, lembur, laporan); rujukan kode untuk rumus detail |
| 4 | [04-mapping-database-per-modul.md](04-mapping-database-per-modul.md) | Tabel database utama tiap domain modul |
| 5 | [05-komponen-teknis-dan-integrasi.md](05-komponen-teknis-dan-integrasi.md) | Laravel, paket Composer, middleware, service, konfigurasi eksternal |
| 6 | [06-instalasi-dan-deploy-ubuntu.md](06-instalasi-dan-deploy-ubuntu.md) | Instal server, aplikasi pertama kali, deploy berkala, referensi panduan lain |
| 7 | [07_MENU.md](07_MENU.md) | Per menu sidebar: fungsi, proses, rumus ringkas, file, mapping tabel |
| 8 | [d08.md](d08.md) | Layout Blade, asset, struktur folder view, halaman cetak |

---

## Dokumen pendukung di repositori (di luar kumpulan ini)

- `DEPLOY_UBUNTU.md` (akar proyekt) - panduan deploy instalasi menyeluruh ke Ubuntu (Apache/PHP/MySQL).
- `DEPLOY_LOGIN_AKTIF_UBUNTU.md` - deploy incremental modul sesi/login.
- Berkas lain berawalan `DEPLOY_*` - catatan deployment fitur tertentu.
- Berkas berawalan `MEMORY_SESSION_*` atau `MEMORY_*` - catatan kerja sesi tertentu (bukan dokumentasi formal).

---

## Prinsip arsitektur singkat

- **Otentikasi:** session web (`Auth`), role dan permission granularity lewat middleware `permission:`.
- **Data:** banyak tabel memakai awalan **`m_`** (master), **`t_`** (transaksi); beberapa tabel standar Laravel (`users`, `roles`, dll.).
- **Payroll utama:** konsolidasi di **`t_closing`** per kombinasi periode, divisi, NIK, kuarter closing; hitungan inti banyak berada di `ClosingController` dan `LemburCalculationService`.
- **UI:** Blade + Bootstrap 5 (CDN); layout utama `resources/views/layouts/app.blade.php`, varian satpam/`absen` memakai `resources/views/absen/layouts/app.blade.php`.

---

*Berkas indeks ini dapat diperbarui seiring bertambahnya modul atau pemecahan dokumentasi.*
