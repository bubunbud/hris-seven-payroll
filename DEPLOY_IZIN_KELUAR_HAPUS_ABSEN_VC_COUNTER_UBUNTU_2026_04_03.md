## Panduan Deploy Ubuntu: Izin Keluar Komplek — Hapus + Absensi & Antiduplikasi `vcCounter`

**Tanggal dokumen:** 3 April 2026  
**Lingkungan target:** Ubuntu Server (Nginx/Apache + PHP-FPM + MySQL)  
**Aplikasi:** HRIS Seven Payroll  

**Referensi umum server/path:** `DEPLOY_UBUNTU.md`

---

### 1. Ringkasan perubahan

#### 1.1. Hapus izin keluar komplek + opsi hapus data absen

- Saat menghapus **Izin Pribadi (Z003/Z004) + tipe Masuk Siang**, pengguna mendapat **modal konfirmasi** dengan dua pilihan:
  - **Hanya hapus izin** — baris di `t_izin` dihapus, **`t_absen` tidak diubah**.
  - **Hapus izin + hapus record absensi** — dalam satu transaksi, baris **`t_absen`** untuk kombinasi **tanggal izin + NIK** dihapus (seluruh record), lalu izin dihapus.
- Permintaan hapus memakai **FormData** (`_method=DELETE`, `hapus_absensi=1` atau `0`) agar parameter terbaca konsisten oleh Laravel.
- Backend: **`whereDate('dtTanggal', ...)`** dan **`TRIM(vcNik)`** untuk mencocokkan baris absensi (datetime / NIK ter-padding).
- Method **`destroy(Request $request, string $id)`** + helper pembacaan flag **`hapus_absensi`**.

#### 1.2. Antisipasi duplikasi `vcCounter`

- Algoritma lama (`mdY` + `mt_rand` + `substr` 9 digit) menghasilkan **ruang unik yang terlalu kecil** dan mudah bentrok.
- **Penomoran baru:** 9 digit = **2 digit tahun (`yy`) + 7 digit acak** (entropy jauh lebih besar).
- **Retry insert** hingga 25 kali jika database mengembalikan **duplicate key** (MySQL **1062**).
- **Antidouble-submit** di modal simpan: flag JS + **disable tombol Simpan** sampai respons selesai.

> **Migration:** tidak ada file migrasi baru untuk paket ini. Pastikan di database **`t_izin.vcCounter`** sudah berperan sebagai **PRIMARY KEY / UNIQUE**; jika belum, disarankan perbaiki setelah membersihkan data duplikat lama.

---

### 2. File yang harus di-deploy

| No | Path | Keterangan |
|----|------|------------|
| 1 | `app/Http/Controllers/IzinKeluarController.php` | `destroy` + `requestHapusAbsensi`, generate/retry `vcCounter`, method bantu |
| 2 | `resources/views/absen/izin_keluar/index.blade.php` | Modal hapus, tombol `data-*`, `FormData` hapus, antidouble-submit, delegasi klik hapus |

---

### 3. Backup (disarankan)

Di server:

```bash
cd /var/www/html/hris-seven-payroll

mkdir -p backup_izin_keluar_2026_04_03

cp app/Http/Controllers/IzinKeluarController.php backup_izin_keluar_2026_04_03/
cp resources/views/absen/izin_keluar/index.blade.php backup_izin_keluar_2026_04_03/
```

---

### 4. Deploy kode (Git — disarankan)

```bash
cd /var/www/html/hris-seven-payroll
git fetch origin
git pull origin main
```

*(Ganti `main` dengan branch production Anda.)*

**Alternatif — salin manual (SCP dari Windows),** contoh:

```bash
scp app/Http/Controllers/IzinKeluarController.php user@SERVER_IP:/var/www/html/hris-seven-payroll/app/Http/Controllers/
scp resources/views/absen/izin_keluar/index.blade.php user@SERVER_IP:/var/www/html/hris-seven-payroll/resources/views/absen/izin_keluar/
```

Lalu di server:

```bash
cd /var/www/html/hris-seven-payroll
sudo chown www-data:www-data app/Http/Controllers/IzinKeluarController.php resources/views/absen/izin_keluar/index.blade.php
sudo chmod 644 app/Http/Controllers/IzinKeluarController.php resources/views/absen/izin_keluar/index.blade.php
```

---

### 5. Composer & migrasi

- **`composer install`:** hanya jika ada perubahan dependensi (paket ini **tidak** menambah package).
- **`php artisan migrate`:** **tidak wajib** untuk fitur ini; jalankan hanya jika branch Anda memuat migrasi lain.

```bash
cd /var/www/html/hris-seven-payroll
# composer install --no-dev --optimize-autoloader   # jika perlu
# php artisan migrate --force                       # jika ada migrasi baru di branch
```

---

### 6. Clear cache Laravel

```bash
cd /var/www/html/hris-seven-payroll
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

### 7. Permission & reload layanan

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

Contoh reload (sesuaikan versi PHP):

```bash
sudo systemctl reload php8.1-fpm
sudo systemctl reload nginx
```

---

### 8. Checklist verifikasi

| Uji | Langkah |
|-----|--------|
| **Counter baru** | Tambah izin keluar beberapa kali; `vcCounter` 9 digit, tidak mudah bentrok; simpan tidak error. |
| **Hapus + absen** | Buat **Masuk Siang** (pribadi); hapus pilih **Hapus izin + hapus record absensi**; pastikan baris `t_absen` (tanggal + NIK) ikut hilang. |
| **Hapus tanpa absen** | Pilih **Hanya hapus izin**; `t_izin` hilang, `t_absen` tidak dihapus. |
| **Non–Masuk Siang** | Hapus izin tipe lain; perilaku konfirmasi satu langkah (`confirm`) seperti sebelumnya. |
| **Double klik Simpan** | Klik ganda cepat pada Simpan; hanya satu request utama (tombol disabled sampai selesai). |

---

### 9. Database — duplikat `vcCounter` lama (opsional)

Jika historis sudah ada **lebih dari satu baris** per `vcCounter`:

```sql
SELECT vcCounter, COUNT(*) AS jumlah
FROM t_izin
GROUP BY vcCounter
HAVING jumlah > 1;
```

Tindakan: rapikan data (retensi baris yang benar, hapus duplikat) sesuai kebijakan HR, lalu pastikan **UNIQUE / PRIMARY KEY** pada `vcCounter` agar konsisten dengan aplikasi.

---

### 10. Rollback (darurat)

```bash
cd /var/www/html/hris-seven-payroll
cp backup_izin_keluar_2026_04_03/IzinKeluarController.php app/Http/Controllers/
cp backup_izin_keluar_2026_04_03/index.blade.php resources/views/absen/izin_keluar/
php artisan view:clear
```

---

### 11. Dokumen terkait

- `DEPLOY_UBUNTU.md` — lingkungan & path standar  
- `DEPLOY_IZIN_KELUAR_KOMPLEK_2026_02_06.md` — baseline modul izin keluar komplek  
