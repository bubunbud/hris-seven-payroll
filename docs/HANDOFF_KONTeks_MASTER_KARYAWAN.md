# Handoff konteks — Master Karyawan & deploy (lanjutan besok)

Dokumen ringkas agar pembahasan **berkesinambungan** saat dilanjutkan. Terakhir diperbarui: sesi yang membahas tab Catatan Karyawan, cetak biodata, perbaikan UI, dan dokumentasi deploy Ubuntu.

---

## 1. Yang sudah selesai (ringkas)

### Tab « Catatan Karyawan » (Master Karyawan)

- **Tabel DB:** `t_karyawan_catatan` — migrasi `database/migrations/2026_04_02_140000_create_t_karyawan_catatan_table.php` (relasi `karyawan_nik` ke NIK karyawan, ENUM jenis/kategori/level/status, lampiran path, tanggal berlaku/berakhir).
- **Backend:** `KaryawanController` — CRUD JSON, upload ke `storage/app/public/karyawan_catatan/`, copy saat duplikasi karyawan, hapus file saat hapus karyawan/catatan.
- **Rute:** `GET/POST/PUT/DELETE` … `catatan-karyawan`, `POST karyawan/copy-catatan-karyawan`.
- **View:** `resources/views/master/karyawan/index.blade.php` + mirror `resources/views/absen/master/karyawan/index.blade.php` — tab, tabel, modal, JS (load/simpan/hapus/edit, `Promise.all` saat salin data).
- **Bug pernah terjadi:** tombol Edit tidak jalan karena handler `onclick` membutuhkan fungsi global — **diperbaiki** dengan `window.editCatatanMember` dan `window.removeCatatanMember` (sama pola seperti mutasi).

### Cetak biodata karyawan

- **Rute:** `GET karyawan/{nik}/biodata-cetak` → `KaryawanController::biodataCetak`.
- **View:** `resources/views/master/karyawan/biodata-cetak.blade.php`.
- **Data:** Controller memuat `$catatanKaryawan` dari `t_karyawan_catatan` + URL lampiran via helper (sama seperti API).
- **Layout Catatan di cetak:** **bukan tabel horizontal** — tiap record = **blok bernomor** (form vertikal `dt/dd`), field **kosong tidak ditampilkan**, **deskripsi utuh** (`white-space: pre-wrap`), lampiran sebagai tautan jika ada.

### Dokumentasi deploy Ubuntu

- **File:** `docs/DEPLOY_UBUNTU_MASTER_KARYAWAN.md`
- Isi: Git pull, **deploy salin file manual** (scp/rsync + daftar path penting), **`php artisan migrate`**, **export/import SQL manual** (`mysqldump` / `mysql` untuk `t_mutasi` & `t_karyawan_catatan`), peringatan FK/encoding, **salin folder upload** `mutasi_sk` & `karyawan_catatan`, `storage:link`, cache, verifikasi.

### Tab Mutasi (konteks terkait deploy doc)

- **Tabel:** `t_mutasi` — migrasi `2026_04_04_100000_*` dan `2026_04_04_100001_*` (kolom SK/jabatan/file).
- Upload SK di **`storage/app/public/mutasi_sk/`**.

---

## 2. File / area penting (referensi cepat)

| Area | Lokasi utama |
|------|----------------|
| Controller karyawan | `app/Http/Controllers/KaryawanController.php` |
| Rute | `routes/web.php` (grup master karyawan) |
| UI master | `resources/views/master/karyawan/index.blade.php` |
| UI absen (mirror) | `resources/views/absen/master/karyawan/index.blade.php` |
| Cetak biodata | `resources/views/master/karyawan/biodata-cetak.blade.php` |
| Migrasi catatan | `database/migrations/2026_04_02_140000_create_t_karyawan_catatan_table.php` |
| Migrasi mutasi | `database/migrations/2026_04_04_100000_create_t_mutasi_table.php`, `2026_04_04_100001_add_vcjabatan_vcfilesk_to_t_mutasi_table.php` |
| Deploy doc | `docs/DEPLOY_UBUNTU_MASTER_KARYAWAN.md` |

---

## 3. Hal yang bisa dilanjutkan nanti (opsional)

- Penyesuaian **izin/role** khusus tab Catatan jika dibutuhkan terpisah dari master karyawan.
- **Uji deploy** nyata ke Ubuntu mengikuti `DEPLOY_UBUNTU_MASTER_KARYAWAN.md`.
- Perluasan cetak biodata (urutan section, watermark, dll.) jika ada permintaan bisnis baru.

---

## 4. Cara melanjutkan besok

1. Baca dokumen ini + `docs/DEPLOY_UBUNTU_MASTER_KARYAWAN.md` bila lanjut soal server.
2. Sebutkan fitur atau file yang ingin diubah; konteks di atas mengasumsikan branch/repo sama dengan kondisi terakhir sesi ini.

Selamat istirahat — sampai lanjut di sesi berikutnya.
