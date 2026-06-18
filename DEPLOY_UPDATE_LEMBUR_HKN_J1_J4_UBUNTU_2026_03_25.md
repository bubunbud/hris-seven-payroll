# Panduan Deployment Ubuntu: Update Lembur HKN J1–J4 & Laporan Gaji Terkait

**Tanggal referensi:** 25 Maret 2026  
**Server:** Ubuntu Production (lihat juga `DEPLOY_UBUNTU.md` untuk path & kredensial umum)  
**Cakupan:** Closing Gaji (perhitungan lembur jam ke-1 s/d ke-4 + kolom DB), View Rekap Gaji, Cetak Slip Gaji, Rekap Upah Karyawan, Rekap Upah Per Bagian/Dept., Rekap Bank, Rekap Upah Finance Ver.

---

## Ringkasan perubahan

1. **Lembur Hari Kerja Normal (HKN)**  
   Perhitungan tier diatur ulang (termasuk batas jam J2/J3/J4 dan nominal **J4**). Logika utama ada di `LemburCalculationService` dan pemakaiannya di proses **Closing** / **Update Closing Gaji**.

2. **Database `t_closing`**  
   Penambahan kolom lembur kerja ke-4 dan total nominal J4 (lihat migrasi di bawah). **Wajib** jalankan `php artisan migrate` setelah kode ter-deploy.

3. **Laporan & tampilan**  
   View Rekap Gaji, Slip Gaji, Rekap Upah (karyawan / per bagian), Rekap Bank, dan Rekap Upah Finance Ver diselaraskan agar menampilkan/menjumlahkan **J1–J4** secara konsisten (termasuk Excel export di mana berlaku).

4. **Catatan data lama**  
   Data closing yang **sudah tersimpan** sebelum deploy tidak otomatis berubah. Jika perlu angka sesuai rumus baru, lakukan **ulang proses** yang sesuai di aplikasi (mis. hitung ulang / simpan ulang closing periode terkait) sesuai prosedur internal Anda.

---

## Migrasi database (wajib)

| File migrasi | Tabel | Perubahan |
|--------------|--------|-----------|
| `database/migrations/2026_03_26_000001_add_lembur_kerja4_and_totallembur4_to_t_closing_table.php` | `t_closing` | Menambah `decJamLemburKerja4`, `decLemburKerja4`, `decTotallembur4` |

Setelah migrate, verifikasi kolom ada:

```sql
DESCRIBE t_closing;
-- Pastikan ada: decJamLemburKerja4, decLemburKerja4, decTotallembur4
```

---

## Langkah 1: Backup (disarankan)

```bash
ssh user@SERVER_IP
cd /var/www/html/hris-seven-payroll   # sesuaikan path aplikasi di server Anda

# Backup database (contoh)
mysqldump -u USER -p NAMA_DATABASE > ~/backup_hris_seven_$(date +%Y%m%d_%H%M%S).sql

# Opsional: snapshot git
git status
git branch
```

---

## Langkah 2: Deploy kode (disarankan: Git)

Dari server:

```bash
cd /var/www/html/hris-seven-payroll
git fetch origin
git pull origin main    # atau nama branch yang dipakai production
```

Jika deploy manual file (tanpa git), salin **semua path** pada tabel di bagian **Daftar file** di bawah.

---

## Langkah 3: Dependensi PHP

```bash
cd /var/www/html/hris-seven-payroll
composer install --no-dev --optimize-autoloader
```

*(Jalankan jika ada perubahan `composer.json` / `composer.lock` atau setelah pull besar.)*

---

## Langkah 4: Migrasi

```bash
php artisan migrate --force
```

Pastikan tidak ada error. Jika migrasi ini sudah pernah jalan di server, Laravel akan melewatinya otomatis.

---

## Langkah 5: Cache & view Laravel

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
# Opsional setelah verifikasi:
# php artisan config:cache
# php artisan view:cache
```

---

## Langkah 6: Permission (jika perlu)

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

---

## Langkah 7: Restart layanan web PHP

Sesuaikan dengan stack server (contoh PHP-FPM + Nginx):

```bash
sudo systemctl reload php8.1-fpm
sudo systemctl reload nginx
```

*(Ganti versi PHP jika berbeda.)*

---

## Langkah 8: Verifikasi fungsional

| Modul | Menu / perilaku yang dicek |
|--------|----------------------------|
| Update Closing Gaji | Form menampilkan lembur J4; simpan tidak error; nilai konsisten dengan rumus baru |
| View Rekap Gaji | Kolom lembur / total selaras dengan J1–J4 |
| Cetak Slip Gaji | Baris/nominal lembur J4 dan total lembur |
| Rekap Upah Karyawan | JM4 / breakdown jam lembur |
| Rekap Upah Per Bagian/Dept. | Agregat `jam_lembur_jm4` dan tier |
| Rekap Bank | Preview **Gaji + Lembur** sama dengan logika export (termasuk J4) |
| Rekap Upah Finance Ver | Kolom JM4, tier, unduh Excel |

---

## Daftar file yang ikut update (referensi deploy)

Gunakan sebagai checklist jika copy manual atau audit diff.

### Inti closing, lembur, model & DB

| Path |
|------|
| `database/migrations/2026_03_26_000001_add_lembur_kerja4_and_totallembur4_to_t_closing_table.php` |
| `app/Models/Closing.php` |
| `app/Services/LemburCalculationService.php` |
| `app/Http/Controllers/ClosingController.php` |
| `app/Http/Controllers/UpdateClosingGajiController.php` |
| `resources/views/proses/update-closing-gaji/index.blade.php` |
| `resources/views/absen/proses/update-closing-gaji/index.blade.php` |

### View Rekap Gaji

| Path |
|------|
| `resources/views/proses/view-gaji/index.blade.php` |
| `resources/views/absen/proses/view-gaji/index.blade.php` |

### Cetak Slip Gaji

| Path |
|------|
| `resources/views/laporan/slip-gaji/preview.blade.php` |
| `resources/views/laporan/slip-gaji/print.blade.php` |
| `resources/views/absen/laporan/slip-gaji/preview.blade.php` |
| `resources/views/absen/laporan/slip-gaji/print.blade.php` |

### Rekap Upah Karyawan

| Path |
|------|
| `app/Http/Controllers/RekapUpahKaryawanController.php` |
| `resources/views/laporan/rekap-upah-karyawan/preview.blade.php` |
| `resources/views/absen/laporan/rekap-upah-karyawan/preview.blade.php` |

### Rekap Upah Per Bagian/Dept.

| Path |
|------|
| `app/Http/Controllers/RekapUpahPerBagianDeptController.php` |
| `resources/views/laporan/rekap-upah-per-bagian-dept/preview.blade.php` |
| `resources/views/absen/laporan/rekap-upah-per-bagian-dept/preview.blade.php` |

### Rekap Bank

| Path |
|------|
| `app/Exports/RekapBankExport.php` |
| `resources/views/laporan/rekap-bank/preview.blade.php` |
| `resources/views/laporan/rekap-bank/index.blade.php` *(hanya jika ada perubahan)* |
| `resources/views/absen/laporan/rekap-bank/preview.blade.php` |

### Rekap Upah Finance Ver

| Path |
|------|
| `app/Http/Controllers/RekapUpahFinanceVerController.php` |
| `app/Exports/RekapUpahFinanceVerExport.php` |
| `resources/views/laporan/rekap-upah-finance-ver/preview.blade.php` |
| `resources/views/absen/laporan/rekap-upah-finance-ver/preview.blade.php` |

---

## Rollback (darurat)

1. Kembalikan kode ke commit sebelumnya (`git checkout` / restore backup).  
2. **Hati-hati** dengan `migrate:rollback` — hanya jika migrasi ini yang terakhir dan tim setuju, karena bisa menghapus kolom J4:

```bash
php artisan migrate:rollback --step=1
```

3. Restore database dari file `mysqldump` jika diperlukan.

---

## Dokumen terkait

- `DEPLOY_UBUNTU.md` — lingkungan server & path umum  
- `DEPLOY_UPDATE_CLOSING_GAJI_TUNJANGAN_UBUNTU.md` — pola deploy khusus Update Closing Gaji (referensi gaya prosedur)
