# Deploy Ubuntu — Sinkron Absen Izin Keluar Komplek (hanya Pulang Cepat)

**Ringkas perubahan:** **`t_absen.dtJamKeluar`** dari jam “Sampai” **hanya** untuk **Jenis Izin Pribadi (Z003/Z004)** + **Tipe = Pulang Cepat**. **Tipe Izin Biasa** **tidak** mengubah **`t_absen`**.

**Ini perubahan kode Laravel saja** — **tidak ada migrasi database**; **`composer install` tidak wajib**.

---

## File yang harus di-copy / di-update di server production

| Path relatif dari root project | Keterangan |
|---------------------------------|------------|
| **`app/Http/Controllers/IzinKeluarController.php`** | Logic `store` / `update` sinkron **`t_absen`** |

Pastikan **`routes`**, **`view` Blade**, dan **`.env`** untuk modul Izin Keluar **tidak ikut berganti** oleh patch ini — satu file itu saja untuk fitur tersebut.

---

## Opsi deploy

### Opsi A — Git (disarankan)

Di server Ubuntu:

```bash
cd /var/www/hris-seven-payroll   # sesuaikan path
git fetch origin
git pull origin main             # atau branch release Anda
php artisan optimize:clear
sudo systemctl reload php8.2-fpm  # sesuaikan versi PHP/service
```

### Opsi B — Salin satu file lewat SCP/SFTP/rsync

```bash
scp ./app/Http/Controllers/IzinKeluarController.php USER@SERVER:/var/www/hris-seven-payroll/app/Http/Controllers/
```

Di server sesudah menyalin:

```bash
php artisan optimize:clear
sudo systemctl reload php8.2-fpm
```

---

## Verifikasi singkat setelah deploy

1. **Izin Biasa (Z003/Z004)** — simpan/update: **`t_izin`** ada; **`t_absen`** **tidak** ter-update otomatis dari flow ini untuk **`dtJamKeluar`**.
2. **Pulang Cepat (Z003/Z004)** + isi **Sampai** — **`t_absen.dtJamKeluar`** sesuai jam Sampai; **`vcketerangan`** pola **`Auto: Pulang Cepat`** (terpotong batas kolom).
3. **Masuk Siang** — tetap: **`dtJamMasuk`** dari shift; **`dtJamKeluar`** tidak di-set oleh blok Masuk Siang.

---

## Catatan

- **Rollback:** backup file controller production sebelum menimpa atau `git revert` commit terkait.
- **Data lama:** isi **`t_absen`** dari Izin **Biasa** di masa sebelumnya **tidak** otomatik dibersihkan.
