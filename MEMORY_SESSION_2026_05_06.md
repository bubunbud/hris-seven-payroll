# Ringkasan sesi 6 Mei 2026

## Yang dibuat / diubah

### Modul: Login Aktif & Riwayat (Settings)
- **Tujuan:** Melihat user yang sedang aktif (heartbeat sesi) dan riwayat login/logout.
- **Permission:** `view-login-activity` (module settings). Akses route juga jika punya `view-settings`.
- **Route:** `GET /login-activity` — nama route `login-activity.index`.
- **Tabel DB:** `t_user_sessions`, `t_login_history` (migration `2026_05_06_120000_create_t_user_sessions_and_t_login_history_tables.php`).
- **Model:** `App\Models\UserSession`, `App\Models\LoginHistory`.
- **Middleware:** `App\Http\Middleware\TrackUserSession` — terdaftar di grup `web` di `app/Http/Kernel.php` (update heartbeat, throttle ~1 menit).
- **Auth:** `AuthController` — setelah `session()->regenerate()` memanggil `recordLoginSession()`; logout mencatat history + hapus baris `t_user_sessions`.
- **Controller:** `UserLoginActivityController@index` — view `resources/views/user-login-activity/index.blade.php`.
- **Menu:** `resources/views/layouts/app.blade.php` dan `resources/views/absen/layouts/app.blade.php` (submenu Settings + `hasMenuPermission` include `view-login-activity`).
- **Routes:** `routes/web.php` — import `UserLoginActivityController`, tambah `view-login-activity` di middleware Settings, grup route `login-activity`.
- **Seeder:** `RolePermissionSeeder`, `UpdatePermissionsSeeder` (permission baru + lampir ke role admin slug `admin`).
- **Env opsional:** `LOGIN_ACTIVE_WITHIN_MINUTES` (default 15) — tercatat di `.env.example`.

### Dokumentasi deploy
- Awal: `DEPLOY_LOGIN_AKTIF_RIWAYAT_UBUNTU_2026_05_06.md` — user sulit buka (nama panjang); isi dipindah ke **`DEPLOY_LOGIN_AKTIF_UBUNTU.md`**.
- File nama panjang diganti isi jadi pointer ke file pendek.
- **`DEPLOY_LOGIN_AKTIF_UBUNTU.md`** disederhanakan: fokus **salin manual lokal → server** (tabel file baru vs timpa) + 4 perintah artisan setelah unggah.

## Perintah yang pernah dijalankan (lokal)
- `php artisan migrate` — sukses.
- `php artisan db:seed --class=UpdatePermissionsSeeder` — permission `view-login-activity` terpasang.

## Lanjutan besok (ide)
- Bila perlu: pembersihan berkala `t_user_sessions` kadaluarsa; atau penyempurnaan UI/ filter.
- Di server produksi: ikuti `DEPLOY_LOGIN_AKTIF_UBUNTU.md` (copy file + migrate + seeder).

---
*Disimpan otomatis untuk kontinuitas kerja.*
