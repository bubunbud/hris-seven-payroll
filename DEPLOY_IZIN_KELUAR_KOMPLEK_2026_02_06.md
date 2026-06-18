## Panduan Deploy: Update Modul Izin Keluar Komplek Kantor – 2026-02-06

**Tanggal:** 6 Februari 2026  
**Lingkungan target:** Ubuntu Server (Apache + PHP + MySQL)  
**Aplikasi:** HRIS Seven Payroll

---

### 1. Ringkasan Perubahan

Perubahan ini mencakup:

- **Form Tambah/Edit Izin Keluar (modal):**
  - Field **NIK** diubah menjadi **autocomplete NIK/Nama** (lokal, tanpa AJAX) dengan tampilan `NIK - Nama`.
  - Field **Tipe/Kategori Izin** (Masuk Siang, Izin Biasa, Pulang Cepat) mengontrol perilaku jam **Dari** dan **Sampai**.
  - Untuk **Izin Pribadi (Z003/Z004) + Tipe = Masuk Siang**:
    - Jam **Sampai** boleh dikosongkan (tidak wajib).
    - Jam **Dari** wajib dan bisa auto–isi jam masuk shift.

- **Logika backend untuk Izin Pribadi – Masuk Siang:**
  - `dtSampai` di `t_izin` tetap terisi aman (tidak mem–break constraint NOT NULL) walaupun user mengosongkan di form.
  - Di `t_absen`:
    - **Hanya `dtJamMasuk`** yang di–set ke jam masuk shift (Masuk Siang).
    - **`dtJamKeluar` tidak diubah** oleh izin Masuk Siang.
    - `vcketerangan` di `t_absen` diisi:  
      - `"Masuk Siang"` (jika jam “Sampai” kosong), atau  
      - `"Masuk Siang HH:MM"` (jika jam “Sampai” diisi).

- **Logika backend untuk Izin Pribadi lain:**
  - **Izin Biasa / Pulang Cepat** tetap meng–update `dtJamKeluar` seperti sebelumnya.
  - `vcketerangan` tetap `"Auto: {Tipe}"` (misal `"Auto: Izin Biasa"`, `"Auto: Pulang Cepat"`).

- **Layout Cetak Surat Izin (single & multiple):**
  - Judul surat kini dinamis sesuai tipe:
    - **Masuk Siang** → `Surat Izin Masuk Siang`
    - **Izin Biasa** → `Surat Izin Keluar Komplek Kantor`
    - **Pulang Cepat** → `Surat Izin Pulang Cepat`
    - Lainnya → default `Ijin Keluar Komplek`
  - **Kolom Perkiraan Keluar:**
    - Default: dari jam **Dari**.
    - **Pulang Cepat**: dari jam **Sampai**.
  - **Kolom Perkiraan Kembali:**
    - **Masuk Siang & Pulang Cepat**: dikosongkan (tanpa jam, tanpa teks `/ Tidak Kembali`).
    - Tipe lain: tetap menampilkan jam “Sampai” atau teks “Tidak Kembali”.
  - Tombol di atas halaman cetak:
    - **Print** (`window.print()`).
    - **Close** (`window.close()`) untuk menutup tab/jendela cetak (tidak lagi kembali ke index).

---

### 2. File yang Diubah

1. **Controller**
   - `app/Http/Controllers/IzinKeluarController.php`

2. **View – Halaman Index + Modal Tambah/Edit**
   - `resources/views/absen/izin_keluar/index.blade.php`

3. **View – Cetak Single Surat**
   - `resources/views/absen/izin_keluar/print.blade.php`

4. **View – Cetak Multiple Surat**
   - `resources/views/absen/izin_keluar/print-multiple.blade.php`

> **Catatan:** Tidak ada migration/database baru pada paket perubahan ini. Field `vcTipeIzin` dan logika dasar izin keluar komplek sudah ada dari update sebelumnya.

---

### 3. Persiapan di Server Ubuntu

Asumsi:
- Path project: `/var/www/html/hris-seven-payroll`
- User web server: `www-data`

#### 3.1. Backup File Lama (Opsional tapi Disarankan)

Di server:

```bash
cd /var/www/html/hris-seven-payroll

mkdir -p backup_izin_keluar_2026_02_06

cp app/Http/Controllers/IzinKeluarController.php backup_izin_keluar_2026_02_06/
cp resources/views/absen/izin_keluar/index.blade.php backup_izin_keluar_2026_02_06/
cp resources/views/absen/izin_keluar/print.blade.php backup_izin_keluar_2026_02_06/
cp resources/views/absen/izin_keluar/print-multiple.blade.php backup_izin_keluar_2026_02_06/
```

---

### 4. Upload File dari Local ke Server

Jalankan dari **komputer local** (Windows) menggunakan `scp`/PowerShell (sesuaikan IP & user server):

```bash
# Controller
scp app/Http/Controllers/IzinKeluarController.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/

# View Index + Modal
scp resources/views/absen/izin_keluar/index.blade.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/absen/izin_keluar/

# View Print Single
scp resources/views/absen/izin_keluar/print.blade.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/absen/izin_keluar/

# View Print Multiple
scp resources/views/absen/izin_keluar/print-multiple.blade.php \
    user@192.168.10.40:/var/www/html/hris-seven-payroll/resources/views/absen/izin_keluar/
```

Jika menggunakan akun selain `user` atau IP lain, sesuaikan perintah di atas.

---

### 5. Set Permission & Ownership

Di server Ubuntu:

```bash
cd /var/www/html/hris-seven-payroll

chmod 644 app/Http/Controllers/IzinKeluarController.php
chmod 644 resources/views/absen/izin_keluar/index.blade.php
chmod 644 resources/views/absen/izin_keluar/print.blade.php
chmod 644 resources/views/absen/izin_keluar/print-multiple.blade.php

chown www-data:www-data app/Http/Controllers/IzinKeluarController.php
chown www-data:www-data resources/views/absen/izin_keluar/index.blade.php
chown www-data:www-data resources/views/absen/izin_keluar/print*.blade.php
```

---

### 6. Clear Cache Laravel

Masih di server:

```bash
cd /var/www/html/hris-seven-payroll

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# (Opsional, jika ingin rebuild cache untuk production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### 7. Checklist Testing Fungsional

#### 7.1. Tambah Izin Keluar – Izin Pribadi Masuk Siang

1. Buka menu **Izin Keluar Komplek Kantor**.
2. Klik **Tambah**:
   - Field **NIK/Nama**:
     - Ketik minimal 2 karakter → daftar autocomplete muncul.
     - Pilih salah satu → input terisi `NIK - Nama` dan hidden `vcNik` terisi.
3. Pilih:
   - **Jenis Izin = Z003 (Pribadi)**.
   - **Tipe = Masuk Siang**.
4. Isi:
   - `Tanggal`.
   - `Dari` auto–isi jam masuk shift (atau isi manual).
   - **Biarkan `Sampai` kosong**.
5. Simpan:
   - Pastikan **tidak ada error 500**.
   - Cek tabel `t_izin` (opsional, via DB):
     - `dtSampai` **tidak null** (fallback ke nilai aman).
   - Cek tabel `t_absen`:
     - `dtJamMasuk` = jam masuk shift.
     - `dtJamKeluar` **tidak berubah** (tetap null/jam sebelumnya).
     - `vcketerangan` = `"Masuk Siang"`.

6. Ulangi dengan mengisi jam **Sampai** (misal `10:00`) dan simpan:
   - `t_absen.vcketerangan` = `"Masuk Siang 10:00"` (terpotong jika >20 char).
   - `dtJamKeluar` tetap tidak disentuh.

#### 7.2. Tambah Izin Keluar – Izin Pribadi Izin Biasa & Pulang Cepat

1. **Izin Biasa**:
   - Jenis = Z003/Z004, Tipe = Izin Biasa.
   - `Dari` & `Sampai` wajib.
   - Setelah simpan:
     - `dtJamKeluar` di `t_absen` ter–update sesuai jam `Sampai`.
     - `vcketerangan` = `"Auto: Izin Biasa"`.

2. **Pulang Cepat**:
   - Jenis = Z003/Z004, Tipe = Pulang Cepat.
   - `Dari` bisa dinonaktifkan (sesuai logika sebelumnya), `Sampai` wajib.
   - Setelah simpan:
     - `dtJamKeluar` di `t_absen` ter–update sesuai jam `Sampai`.
     - `vcketerangan` = `"Auto: Pulang Cepat"`.

#### 7.3. Cetak Surat – Single

1. Di halaman index Izin Keluar, pilih salah satu data lalu klik tombol **Print** (ikon printer).
2. Untuk masing-masing kombinasi:

   - **Izin Pribadi + Masuk Siang**
     - Judul: **"Surat Izin Masuk Siang"**.
     - Perkiraan Keluar: dari jam **Dari**.
     - **Perkiraan Kembali: kosong** (tanpa jam, tanpa `/ Tidak Kembali`).

   - **Izin Pribadi + Izin Biasa**
     - Judul: **"Surat Izin Keluar Komplek Kantor"**.
     - Perkiraan Keluar: dari jam **Dari**.
     - Perkiraan Kembali: jam **Sampai** atau “Tidak Kembali”.

   - **Izin Pribadi + Pulang Cepat**
     - Judul: **"Surat Izin Pulang Cepat"**.
     - Perkiraan Keluar: **jam “Sampai”**.
     - **Perkiraan Kembali: kosong** (tanpa jam, tanpa `/ Tidak Kembali`).

3. Tombol di atas:
   - **Print Surat Izin** → membuka dialog print.
   - **Close** → menutup tab/jendela cetak (pastikan ini bekerja di browser yang digunakan).

#### 7.4. Cetak Surat – Multiple

1. Di halaman index, centang beberapa izin lalu klik **Print Selected**.
2. Pastikan:
   - Setiap surat di halaman multiple print menampilkan judul & kolom waktu sama seperti single print (sesuai tipe).
   - Tombol **Print Semua Surat Izin** & **Close** berfungsi normal.

---

### 8. Rollback (Jika Diperlukan)

Jika setelah deploy ada masalah dan perlu revert:

```bash
cd /var/www/html/hris-seven-payroll

cp backup_izin_keluar_2026_02_06/IzinKeluarController.php app/Http/Controllers/IzinKeluarController.php
cp backup_izin_keluar_2026_02_06/index.blade.php resources/views/absen/izin_keluar/index.blade.php
cp backup_izin_keluar_2026_02_06/print.blade.php resources/views/absen/izin_keluar/print.blade.php
cp backup_izin_keluar_2026_02_06/print-multiple.blade.php resources/views/absen/izin_keluar/print-multiple.blade.php

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Setelah rollback, ulangi testing dasar (tambah izin, cetak single & multiple) untuk memastikan semuanya kembali normal.

---

### 9. Catatan Tambahan

- Perubahan ini mengikuti pola yang sudah dipakai sebelumnya di modul:
  - **Update Closing Gaji** (perhitungan & form kompleks).
  - **Instruksi Kerja Lembur** (logika tipe + tampilan cetak).
- Sangat disarankan melakukan **uji coba di staging** terlebih dahulu (jika ada) sebelum deploy ke production Ubuntu.











