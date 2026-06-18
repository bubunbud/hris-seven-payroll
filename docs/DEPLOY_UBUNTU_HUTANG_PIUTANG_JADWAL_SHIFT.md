# Manual deploy ke Ubuntu — Hutang Piutang & Jadwal Shift Satpam

Dokumen ini merangkum langkah **update deployment** untuk perbaikan berikut pada server Ubuntu (Laravel / PHP-FPM / Nginx atau Apache):

1. **Hutang Piutang**
   - Perbaikan **hapus record** (error *Illegal offset type* / 500) pada model dengan **primary key komposit**.
   - Field **jumlah** mendukung input **lebih dari satu digit** dan format desimal yang wajar (perilaku input/blur).
   - Filter pencarian diselaraskan dengan pola **Browse Absensi**: **NIK/Nama**, istilah ganda dipisah koma, autocomplete; validasi panjang NIK disesuaikan (**max 24**); parameter URL lama `?nik=` tetap dipetakan ke pencarian.
2. **Jadwal Shift Satpam**
   - Perbaikan error **422** *"The bulan field must be an integer"* saat **Simpan Jadwal** (normalisasi bulan/tahun: string `"01"`–`"09"` tidak lagi gagal validasi `integer`).

**Tidak ada file migrasi database baru** untuk pembaruan ini; deploy bersifat **kode aplikasi (PHP + Blade)** saja.

---

## 1. Prasyarat & cadangan

- Akses SSH ke server, user dengan hak deploy (biasanya ke folder aplikasi).
- **Backup database** aplikasi (kebiasaan baik sebelum deploy; untuk update ini tidak wajib karena tidak mengubah skema).
- **Backup folder aplikasi** atau snapshot Git sebelum menimpa file, agar mudah rollback.

Contoh backup database (sesuaikan user, nama DB, dan path):

```bash
mysqldump -u DB_USER -p NAMA_DATABASE > ~/backup-hris-$(date +%Y%m%d_%H%M).sql
```

---

## 2. Deploy kode aplikasi

Pilih **salah satu** metode: **Git** (disarankan) atau **salin file manual** (SCP/SFTP/rsync/ZIP).

### 2.1 Opsi A — Git (pull di server)

```bash
cd /var/www/hris-seven-payroll   # sesuaikan path project
git fetch origin
git checkout main                  # atau branch release yang dipakai
git pull origin main
```

Jalankan Composer jika `composer.json` / `composer.lock` ikut berubah di commit Anda:

```bash
composer install --no-dev --optimize-autoloader
```

Untuk update yang hanya menyentuh file di bawah, biasanya **cukup** `git pull`; Composer hanya jika dependensi berubah.

### 2.2 Opsi B — Salin file secara manual (lokal → server)

**Hal yang perlu diperhatikan**

- **Jangan** menimpa `.env` production, isi `storage/` yang sudah ada di server, atau menghapus `vendor/` tanpa rencana menjalankan `composer install`.
- Setelah menyalin file PHP/Blade, jalankan **`composer install`** hanya jika Anda juga memperbarui `composer.json` / `composer.lock`.

**Daftar path kode yang wajib tersalin untuk fitur ini**

| Path relatif ke root project | Keterangan |
|------------------------------|------------|
| `app/Models/HutangPiutang.php` | Override `delete()` untuk PK komposit; menghindari error saat hapus |
| `app/Http/Controllers/HutangPiutangController.php` | Filter `search` (NIK/Nama, multi-term), daftar karyawan autocomplete, validasi |
| `resources/views/hutang_piutang/index.blade.php` | Jumlah (input multi-digit), filter & autocomplete |
| `resources/views/absen/hutang_piutang/index.blade.php` | Mirror halaman absen (sama dengan perbaikan di atas) |
| `app/Http/Controllers/JadwalShiftSecurityController.php` | `mergePeriodeInt()`, normalisasi bulan/tahun di `index`/`report` dan sebelum validasi POST |

**Contoh salin dengan `scp`**

```bash
# Ganti USER, HOST, dan path tujuan di server
scp ./app/Models/HutangPiutang.php USER@HOST:/var/www/hris-seven-payroll/app/Models/

scp ./app/Http/Controllers/HutangPiutangController.php USER@HOST:/var/www/hris-seven-payroll/app/Http/Controllers/

scp ./app/Http/Controllers/JadwalShiftSecurityController.php USER@HOST:/var/www/hris-seven-payroll/app/Http/Controllers/

scp ./resources/views/hutang_piutang/index.blade.php USER@HOST:/var/www/hris-seven-payroll/resources/views/hutang_piutang/

scp ./resources/views/absen/hutang_piutang/index.blade.php USER@HOST:/var/www/hris-seven-payroll/resources/views/absen/hutang_piutang/
```

**Contoh `rsync` (beberapa file sekaligus)**

```bash
rsync -avz --progress \
  ./app/Models/HutangPiutang.php \
  ./app/Http/Controllers/HutangPiutangController.php \
  ./app/Http/Controllers/JadwalShiftSecurityController.php \
  ./resources/views/hutang_piutang/index.blade.php \
  ./resources/views/absen/hutang_piutang/index.blade.php \
  USER@HOST:/var/www/hris-seven-payroll/
```

Sesuaikan path sumber (`./`) dengan folder project di mesin Anda.

---

## 3. Pembaruan database

**Untuk rilis ini: tidak ada migrasi Laravel baru** yang harus dijalankan karena perubahan hanya di logika aplikasi dan tampilan.

Jika di masa depan ada migrasi terkait tabel hutang piutang atau jadwal, ikuti pola di dokumen deploy lain (mis. `php artisan migrate --force` setelah file migrasi tersalin).

---

## 4. Cache & optimasi (production)

Setelah file terbaru ada di server, bersihkan atau bangun ulang cache agar route/config/view tidak memakai versi lama:

```bash
cd /var/www/hris-seven-payroll
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika ada masalah setelah deploy, coba bersihkan dulu:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Restart PHP-FPM (sesuaikan versi PHP):

```bash
sudo systemctl restart php8.2-fpm
```

---

## 5. Izin folder (bila perlu)

Pastikan user proses web server (mis. `www-data`) dapat menulis ke `storage/` dan `bootstrap/cache/`:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
```

---

## 6. Verifikasi fungsi setelah deploy

| No | Modul | Yang dicek |
|----|--------|------------|
| 1 | **Hutang Piutang** | Buka halaman Hutang Piutang → **hapus** satu baris: tidak ada error 500 / *Illegal offset type*. |
| 2 | **Hutang Piutang** | Isi **jumlah** lebih dari satu digit (mis. `150000` atau desimal sesuai aturan form); tidak terpotong jadi satu digit saat mengetik. |
| 3 | **Hutang Piutang** | Filter: cari dengan **nama** atau **NIK**, coba beberapa kata dipisah koma; autocomplete karyawan berjalan; URL lama `?nik=...` tetap memfilter. |
| 4 | **Jadwal Shift Satpam** | Pilih bulan **Januari–September** (1–9) atau muat halaman dengan bulan default; isi grid (1, 2, 3, OFF) lalu **Simpan Jadwal** → sukses, **bukan** 422 *bulan must be an integer*. |
| 5 | **Jadwal Shift Satpam** | Uji **Copy bulan sebelumnya** dan **import** (jika dipakai): tidak gagal karena validasi bulan/tahun. |

Perintah opsional untuk memastikan rute ada:

```bash
php artisan route:list --path=hutang-piutang
php artisan route:list --path=jadwal-shift-security
```

---

## 7. Ringkasan perubahan (untuk tim)

- **Hutang Piutang:** perbaikan backend hapus (model), controller filter/search, dan dua view Blade (modul utama + mirror absensi).
- **Jadwal Shift Satpam:** normalisasi integer **bulan**/**tahun** di controller agar string dengan leading zero dan default `date('m')` tidak memicu gagal validasi.

---

## 8. Rollback (darurat)

- **Kode:** kembalikan file dari backup atau `git checkout` ke commit sebelumnya; lalu ulangi `php artisan config:cache` (dan `route:cache` / `view:cache`) atau `* :clear` sesuai kebijakan server.
- **Database:** tidak ada migrasi khusus rilis ini; rollback database umumnya tidak diperlukan kecuali ada operasi lain di sesi deploy yang sama.

---

*Dokumen ini disusun untuk HRIS Seven Payroll — update Hutang Piutang & Jadwal Shift Satpam. Sesuaikan path server (`/var/www/...`), user database, user web server, dan versi PHP dengan environment Ubuntu Anda.*
