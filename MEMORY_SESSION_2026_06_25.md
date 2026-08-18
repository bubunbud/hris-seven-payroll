# Memory Session — 25 Juni 2026

**Status:** Ditunda; lanjut nanti.  
**Lingkungan:** Local XAMPP, DB `hris_seven`.  
**Fokus hari ini:** Modul **Master Mesin Fingerprint** + **Tarik Data Fingerprint** langsung dari mesin ZK/ZKTeco.

**Konteks terkait sebelumnya:**  
- `MEMORY_SESSION_2026_06_18.md` — modul Tarik Data Absensi API.
- `MEMORY_SESSION_2026_05_25.md` — closing RMA, overlap tidak masuk, fix delete/update TidakMasuk.

---

## 1. Modul Baru: Fingerprint Machine

### Konsep final yang disepakati

- Menu di **Settings**:
  - **Master Mesin Fingerprint**
  - **Tarik Data Fingerprint**
- Mesin: merk rata-rata **Solution**, engine **ZK/ZKTeco**.
- Koneksi: TCP/IP langsung ke mesin, port umum **4370**.
- Comm Key: dicoba **tanpa password** dulu (`0`).
- PIN mesin = `m_karyawan.Nik` (tidak perlu mapping tambahan).
- Server production diasumsikan bisa akses IP semua mesin.
- Shift malam **di-skip dulu**; agregasi per tanggal kalender.

### Data mesin awal

| Nama | Tipe | IP | Port | Comm Key |
|------|------|----|------|----------|
| Gedung Utama | X302-S | `192.168.29.9` | 4370 | 0 |
| Prod1.1 | X100-C | `192.168.30.10` | 4370 | 0 |

Seeder: `database/seeders/MesinFingerprintSeeder.php`

---

## 2. Aturan Tarik & Simpan

### Source log dari mesin

Preview log mentah menampilkan:
- Date & Time
- Employee NIK
- State
- Type
- Verified
- Mesin

### Temuan penting field ZK

- Awalnya agregasi memakai `state`, tapi di mesin Solution/X302-S/X100-C `state` cenderung konstan (`1`), sehingga **Jam Masuk kosong dan Jam Pulang terisi**.
- Setelah dicek di preview, field yang benar membedakan masuk/pulang adalah **Type** (`type_raw` dari library).
- Final:
  - `type_raw = 0` → **jam masuk**
  - `type_raw = 1` → **jam pulang**

Kode final di `app/Services/ZkTecoFingerprintService.php`:
- Preview tetap menampilkan `state` dan `type_raw`.
- Agregasi memakai:
  ```php
  $type = (int) ($log['type_raw'] ?? $log['type'] ?? -1);
  ```

### Aturan agregasi

Per **NIK + tanggal**:

| Type | Arti | Aturan |
|------|------|--------|
| `0` | Masuk | ambil jam paling awal |
| `1` | Pulang | ambil jam paling akhir |
| selain 0/1 | Diabaikan dulu | belum dipakai |

### Aturan simpan ke `t_absen`

- Hanya isi `dtJamMasuk` dan `dtJamKeluar`.
- Tidak menulis `vcketerangan`.
- Jika record belum ada (`dtTanggal + vcNik`) → insert.
- Jika record sudah ada → hanya isi jam yang masih kosong.
- Tidak overwrite jam yang sudah ada.

---

## 3. File Baru / Update

### File baru

```
database/migrations/2026_06_25_100000_create_m_mesin_fingerprint_table.php
app/Models/MesinFingerprint.php
database/seeders/MesinFingerprintSeeder.php
app/Services/ZkTecoFingerprintService.php
app/Http/Controllers/MesinFingerprintController.php
app/Http/Controllers/TarikDataFingerprintController.php
resources/views/mesin-fingerprint/index.blade.php
resources/views/tarik-data-fingerprint/index.blade.php
```

### File update

```
composer.json
composer.lock
routes/web.php
resources/views/layouts/app.blade.php
resources/views/absen/layouts/app.blade.php
database/seeders/UpdatePermissionsSeeder.php
DEPLOY_FINGERPRINT_MACHINE_UBUNTU.md
```

### Dependency baru

```
0mithun/php-zkteco v1.3.5
```

Install lokal sudah dilakukan via Composer.

---

## 4. Migration, Seeder, Permission

### Migration

Tabel baru: `m_mesin_fingerprint`

Kolom utama:
- `vcNama`
- `vcMerk`
- `vcTipe`
- `vcIp`
- `intPort`
- `intCommKey`
- `vcAktif`
- `vcKeterangan`
- `dtLastPull`
- `dtCreate`, `dtChange`

Migration lokal sudah dijalankan:

```bash
php artisan migrate --path=database/migrations/2026_06_25_100000_create_m_mesin_fingerprint_table.php
```

### Seeder

Permission baru:
- `view-mesin-fingerprint`
- `view-tarik-data-fingerprint`

Seeder lokal sudah dijalankan:

```bash
php artisan db:seed --class=UpdatePermissionsSeeder
php artisan db:seed --class=MesinFingerprintSeeder
```

Admin otomatis dilampirkan permission baru.

---

## 5. Masalah yang Ditemukan & Solusi

### 1. Error `socket_create()`

Saat test koneksi mesin muncul:

```text
Error koneksi: Call to undefined function Mithun\PhpZkteco\Libs\socket_create()
```

Penyebab: ekstensi PHP `sockets` belum aktif.

Lokal XAMPP:
- `C:\xampp\php\php.ini`
- ubah:
  ```ini
  ;extension=sockets
  ```
  menjadi:
  ```ini
  extension=sockets
  ```
- Restart Apache.

CLI lokal sudah dicek aktif:

```bash
php -m | Select-String sockets
```

### 2. Pull data > 1 hari timeout 60 detik

Error:

```text
Maximum execution time of 60 seconds exceeded
```

Penyebab: library ZK menarik seluruh log dari mesin lalu filter di PHP, sehingga data banyak lambat.

Solusi kode:
- Di `TarikDataFingerprintController::pull()` ditambahkan:
  ```php
  @set_time_limit(600);
  ```
- Di `TarikDataFingerprintController::save()` juga ditambahkan:
  ```php
  @set_time_limit(600);
  ```

Catatan: jika server tetap timeout, naikkan `max_execution_time`, `fastcgi_read_timeout`, atau Apache `Timeout`.

### 3. Preview Type sudah 0/1, tetapi agregasi Jam Masuk kosong

Penyebab: agregasi membaca `state`, bukan `type_raw`.

Solusi:
- `ZkTecoFingerprintService::aggregateLogs()` sekarang pakai `type_raw`.

---

## 6. Deploy Ubuntu

Dokumen deploy lengkap:

```
DEPLOY_FINGERPRINT_MACHINE_UBUNTU.md
```

Catatan khusus Ubuntu:

### PHP server user punya versi:

```text
PHP 8.1.2-1ubuntu2.22 (cli)
```

Library `0mithun/php-zkteco` kompatibel karena butuh **PHP ^8.0**.

Untuk PHP 8.1:

```bash
sudo apt-get install -y php8.1-sockets
php -m | grep sockets
sudo systemctl restart php8.1-fpm
sudo systemctl restart apache2
```

Dokumen deploy sudah diperbarui agar tidak hardcode PHP 8.2 saja dan menambahkan opsi deteksi versi:

```bash
PHPV=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
sudo apt-get install -y php${PHPV}-sockets
```

### Verifikasi jaringan

Server Ubuntu harus bisa akses port 4370 mesin:

```bash
nc -zv 192.168.29.9 4370
nc -zv 192.168.30.10 4370
```

---

## 7. Langkah Lanjut Saat Dilanjutkan

1. [ ] User uji ulang pull setelah fix `type_raw` → pastikan **Jam Masuk** dan **Jam Pulang** tampil benar.
2. [ ] Jika sudah benar, uji **Save to Database** dan cek `t_absen`.
3. [ ] Jika pull periode panjang masih timeout, pertimbangkan:
   - tambah timeout server;
   - tarik per 1–3 hari;
   - optimasi proses/queue jika dibutuhkan.
4. [ ] Jika ada mesin tambahan, tambah lewat menu Master Mesin Fingerprint.
5. [ ] Saat deploy Ubuntu, ikuti `DEPLOY_FINGERPRINT_MACHINE_UBUNTU.md`.

---

## 8. File Referensi

- `DEPLOY_FINGERPRINT_MACHINE_UBUNTU.md`
- `app/Services/ZkTecoFingerprintService.php`
- `app/Http/Controllers/TarikDataFingerprintController.php`
- `resources/views/tarik-data-fingerprint/index.blade.php`
- `MEMORY_SESSION_2026_06_18.md`

---

**Untuk melanjutkan nanti:** baca file ini + `DEPLOY_FINGERPRINT_MACHINE_UBUNTU.md`.
