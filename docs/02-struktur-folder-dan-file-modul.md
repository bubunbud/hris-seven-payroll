# 2. Rancangan file & folder (posisi artefak) per wilayah modul

Struktur root mengikuti standar Laravel: `app/`, `routes/`, `resources/views/`, `database/`, `config/`, `public/`, dll.

---

## 2.1 Konvensi umum Laravel di proyek ini

| Area | Lokasi | Fungsi |
|------|--------|--------|
| Route HTTP utama | `routes/web.php` | Semua halaman SPA-style traditional + middleware `auth` dan `permission:...`. |
| Controller | `app/Http/Controllers/*.php` | Satu atau beberapa controller per domain modul dengan nama intuitif bahasa Inggris. |
| Domain logic berat | `app/Services/*.php` | Pemusatan agar controller tetap kurus (contoh hitung lembur, integrasi feeder). |
| Model Eloquent | `app/Models/*.php` | Biasanya 1 berkas utama per tabel inti (**`protected $table`** menunjuk nama tabel sebenarnya). |
| View Blade | `resources/views/**` | Banyak modul mempunyai subfolder bermakna: `resources/views/absen`, `resources/views/master`, `resources/views/proses`, `resources/views/laporan`, `resources/views/settings`. |
| Layout | `resources/views/layouts/app.blade.php` | Sidebar & kerangka utama. Varian **`resources/views/absen/layouts/app.blade.php`** meniru menu untuk beberapa halaman konsumsi sama. |
| Migrasi schema | `database/migrations/` | DDL versi aplikasi Laravel. Basis data bisa juga diisi/impor langsung oleh DBA luar migrasi untuk tabel legacy. |
| Seeder akses kontrol | `database/seeders/RolePermissionSeeder.php`, `UpdatePermissionsSeeder.php` | Menyelaraskan hak akses baru di tiap sprint. |

---

## 2.2 Pemetaan besar: menu → Controller → folder view utama

Gunakan kolom pertama sebagai kata kunci pencarian file (`*Controller.php`).

| Kawasan bisnis menu | Controller (contoh) | View utama (cek subfolder konkret dalam repo jika refactor) |
|---------------------|----------------------|--------------------------------------------------------------|
| Auth | `AuthController` | `resources/views/auth/*.blade.php` |
| Dashboard | `DashboardController`, `DashboardGroupController`, ... | `resources/views/dashboard*.blade.php` |
| Divisi/Hirarki | `DivisiController`, `DepartemenController`, `BagianController`, `SeksiController`, `HirarkiController` | `resources/views/divisi`, `departemen`, `bagian`, `seksi`, `hirarki` |
| Karyawan lengkap | `KaryawanController`, `ListKaryawanAktifController` | `resources/views/karyawan/**`, dll. |
| Referensi lain | `GolonganController`, `ShiftController`, `JenisIjinController`, `JenisIzinController`, `JabatanController`, `HariLiburController` | nama folder mirip plural resource |
| Absensi utama | `AbsenController`, `EditAbsensiController`, `BrowseTidakAbsenController`, `BrowseAbsensiSecurityController` | `resources/views/absen/**` banyak |
| Kehadiran/izin | `TidakMasukController`, `IzinKeluarController`, `StatistikAbsensiController`, `RekapKeterlambatanController` | `tidak-masuk`, `izin-keluar`, dll. |
| Lembur instruksi | `InstruksiKerjaLemburController` | biasanya folder `instruksi-kerja-lembur` atau `instruksi_kerja` |
| Rekap HR | `RekapitulasiAbsensiController`, `RekapitulasiAbsenAllController`, `RekapitulasiCutiController` | `rekapitulasi-*` |
| Cuti swap & pelunasan hutang HR | `TukarHariKerjaController`, `HutangPiutangController`, `SaldoCutiController` | folder bertema sama |
| Dinas/BPD | `PerjalananDinasController`, `BiayaPerjalananDinasController` | `perjalanan-dinas`, `biaya-perjalanan-dinas` |
| Satpam Shift | `JadwalShiftSecurityController`, `MasterShiftSecurityController`, `OverrideJadwalSecurityController` | `jadwal-shift-security`, `master-shift-*` |
| Gaji pokok & lembur | `GapokController`, `RealisasiLemburController` | `gapok`, `realisasi-lembur` |
| Periode & closing | `PeriodeGajiController`, `ClosingController`, `UpdateClosingGajiController` | `proses/closing*` |
| THR terpisah | `PeriodeThrController`, `ClosingThrController`, `ListThrController`, `LaporanThrController`, `SlipThrController`, `RekapBankThrController` | `proses` & `laporan` |
| Laporan gaji/upah bank | `SlipGajiController`, `RekapUpah*`, `RekapBankController`, `RekapUangMakan*` | folder `laporan/*` |
| Pengatur sistem | `UserController`, `RoleController`, `PermissionController`, `ActivityLogController`, `UserLoginActivityController` | `settings/**` |
| Integrasi/feeder tarik data | `TarikData*Controller`, `ListPengajuan*ApiController` | `tarik-*`, folder settings |

Pastikan lokasi tepat sebuah fitur baru dengan mencari nama route (`routes/web.php`) atau string judul Blade di `resources/views` (fitur pencarian editor / Git).

---

## 2.3 File konfigurasi penting aplikasi bisnis

| Berkas | Relevan modul |
|--------|----------------|
| `config/hris_api.php` + env `HRIS_API_*` | Feeder/API cuti/izin. |
| `config/excel.php` | Export `Maatwebsite\Excel`. |
| `config/app.php` | Timezone aplikasi dll. |

---

## 2.4 Aset publik statis & logo

| Item | Lokasi |
|------|--------|
| Logo sidebar | biasanya **`public/images/logo.png`** direferensi `layouts` |
| Ikons | FontAwesome dari CDN dalam layout |
