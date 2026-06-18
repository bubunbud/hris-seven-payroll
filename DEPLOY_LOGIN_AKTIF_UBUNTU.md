# Deploy modul Login Aktif & Riwayat (Ubuntu)

**Ringkas:** salin file dari PC lokal ke folder aplikasi di server (struktur foldernya sama), lalu jalankan beberapa perintah di server.

Anggap folder aplikasi di server = **`/var/www/html/hris-seven-payroll`** (sesuaikan dengan server Anda).

---

## 1. Salin manual (lokal ke server)

Gunakan **WinSCP**, **FileZilla**, atau Explorer jaringan. Di sisi **lokal**, buka root project (misalnya `C:\xampp\htdocs\hris-seven-payroll`). Di **server**, buka folder root aplikasi yang sama strukturnya.

**Aturan:** nama folder dan file di server harus **sama persis** seperti di lokal. File **baru** = unggah ke folder yang dituju. File **lama** = **timpa** (replace) file yang sudah ada.

### File baru (unggah / buat folder jika belum ada)

| Dari lokal (relatif root project) | Di server, masukkan ke |
|-----------------------------------|-------------------------|
| `database/migrations/2026_05_06_120000_create_t_user_sessions_and_t_login_history_tables.php` | `database/migrations/` |
| `app/Models/UserSession.php` | `app/Models/` |
| `app/Models/LoginHistory.php` | `app/Models/` |
| `app/Http/Middleware/TrackUserSession.php` | `app/Http/Middleware/` |
| `app/Http/Controllers/UserLoginActivityController.php` | `app/Http/Controllers/` |
| `resources/views/user-login-activity/index.blade.php` | `resources/views/user-login-activity/` |

Buat folder **`user-login-activity`** di server jika belum ada, lalu masukkan `index.blade.php`.

### File lama (unggah dan timpa)

| Dari lokal | Di server (folder yang sama) |
|------------|------------------------------|
| `app/Http/Controllers/AuthController.php` | `app/Http/Controllers/` |
| `app/Http/Kernel.php` | `app/Http/` |
| `routes/web.php` | `routes/` |
| `resources/views/layouts/app.blade.php` | `resources/views/layouts/` |
| `resources/views/absen/layouts/app.blade.php` | `resources/views/absen/layouts/` |
| `database/seeders/RolePermissionSeeder.php` | `database/seeders/` |
| `database/seeders/UpdatePermissionsSeeder.php` | `database/seeders/` |

**Jangan** mengunggah folder **`storage/framework/views`** dari lokal ke server (biarkan server yang menghasilkan cache view).

---

## 2. Setelah file terkirim (jalankan di server)

Login SSH ke Ubuntu, lalu:

```bash
cd /var/www/html/hris-seven-payroll

composer dump-autoload -o
php artisan migrate --force
php artisan db:seed --class=UpdatePermissionsSeeder
php artisan optimize:clear
```

*(Opsional)* tambahkan di file **`.env`** server:

```env
LOGIN_ACTIVE_WITHIN_MINUTES=15
```

---

## 3. Cek cepat

- Buka di browser: **`https://domain-anda/login-activity`** (user harus punya permission **`view-login-activity`** atau **`view-settings`**).
- Role **admin** biasanya otomasis dapat permission baru setelah seeder di atas.

---

## Jika perlu: backup DB (sebelum migrate)

```bash
mysqldump -u DB_USER -p DB_NAME > ~/backup_sebelum_login_aktif.sql
```

---

## Lain-lain (opsional)

**Git:** jika kode di server dari `git pull`, tidak perlu salin manual; cukup pull lalu jalankan bagian **langkah 2** dari folder project.

**Rollback singkat:** kembalikan file dari backup, hapus tabel `t_login_history` dan `t_user_sessions` jika migrasi sudah jalan dan Anda ingin membatalkan.

**Masalah umum:** error baca `storage/logs/laravel.log`; pastikan file migration dan `Kernel.php` tidak tertukar path.
