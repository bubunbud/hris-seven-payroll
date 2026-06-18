# Deploy perbaikan Closing Gaji (kolom Periode Sebelumnya) - Ubuntu

**Perubahan:** Perbaikan pengisian **Periode Awal Sebelumnya** dan **Periode Akhir Sebelumnya** pada daftar Closing Gaji (`vcStatus = Belum proses`).  
**Teknis:** Pembaruan logic di **`ClosingController`** (`findPeriodeGajiLangsungSebelumnya` + pemanggilan di `index()`).  
**Database:** Tidak ada migrasi baru; tidak perlu `migrate`.

---

## File yang ditimpa di server

Hanya **satu berkas**:

| Salin dari lokal (root project) | Letakkan di server (path sama) |
|----------------------------------|---------------------------------|
| `app/Http/Controllers/ClosingController.php` | `app/Http/Controllers/ClosingController.php` |

Pastikan struktur foldernya mengikuti project Laravel Anda (misalnya `/var/www/html/hris-seven-payroll`).

---

## Langkah manual (misalnya WinSCP / FileZilla)

1. Backup berkas di server jika biasa Anda lakukan: salin `ClosingController.php` lama sebagai `ClosingController.php.bak`.
2. Unggah `ClosingController.php` dari PC lokal ke folder server yang sama (**timpa** file yang ada).
3. Setelah unggah, pastikan hak baca bisa dijalankan oleh webserver (biasanya sama seperti berkas lain di `app/Http`).

---

## Perintah di server setelah berkas ada (SSH)

Masuk folder root aplikasi Laravel, contoh:

```bash
cd /var/www/html/hris-seven-payroll

php artisan optimize:clear
```

*(Opsional)* jika menggunakan PHP OPcache agresif:

```bash
sudo systemctl reload php8.1-fpm
```

Sesuaikan versi PHP (`php8.2-fpm`, dll).

**Tidak wajib** menjalankan `composer install` atau `migrate` untuk perubahan ini saja.

---

## Verifikasi

1. Buka menu **Proses Payroll - Closing Gaji**.
2. Pada daftar dengan status **Belum Diproses**, cek kolom **Periode Awal Sebelumnya** / **Periode Akhir Sebelumnya**.
3. Untuk Kuarter **1**, seharusnya mengacu ke periode Kuarter **2** bulan pembayaran sebelumnya yang berurutan benar (bukan tanggal sangat lama secara acak atau kosong jika datanya lengkap di **`t_periode`**).

---

## Rollback (jika bermasalah)

Kembalikan `ClosingController.php` dari backup `.bak` atau dari salinan kode sebelum deploy, lalu jalankan lagi:

```bash
php artisan optimize:clear
```

---

## Catatan Git (alternatif)

Jika deployment server memakai `git pull`:

```bash
cd /var/www/html/hris-seven-payroll
git pull
php artisan optimize:clear
```

Pastikan branch atau commit yang dipull sudah memuat perbaikan Closing Gaji ini.
