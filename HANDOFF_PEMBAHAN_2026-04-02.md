# Handoff pembahasan — 2 April 2026

Dokumen ini merangkum konteks kerja **Master Karyawan**, **migrasi**, dan **cetak biodata** agar lanjutan besok tidak kehilangan konteks.

---

## 1. Tab Mutasi (Master Karyawan)

- **Posisi tab:** antara **Pekerjaan** dan **Pendidikan** (juga di mirror `resources/views/absen/master/karyawan/index.blade.php`).
- **Tabel database aktual** `t_mutasi` (bukan skema migrasi lama ber-`id`):
  - **PK:** `(nik, NoSK)`
  - Kolom: `vcTglSK`, `vcDivisi`, `vcDept`, `vcbagian`, `vcSeksi`
- **Backend:** `KaryawanController` — `getMutasi`, `addMutasi`, `updateMutasi`, `deleteMutasi`, `copyMutasi`; hapus karyawan menghapus `t_mutasi` dengan `where('nik', $id)`.
- **Rute:** `GET karyawan/{id}/mutasi`, `POST/PUT/DELETE karyawan/{nik}/mutasi/{noSK}` (No. SK di-URL-encode), `POST karyawan/copy-mutasi`.
- **Model** `app/Models/Mutasi.php` — `fillable` selaras kolom legacy.
- **Migrasi** `2026_04_04_100000_create_t_mutasi_table.php` — jika tabel belum ada, membuat skema **sama dengan produksi** (PK komposit, tanpa kolom lama `vcNik`/`dtMutasi`).

---

## 2. Migrasi & error “table already exists”

- Laravel **tidak punya** `php artisan migrate:fake` (bawaan).
- Solusi yang dipakai: di migrasi `create_*`, bungkus dengan `if (Schema::hasTable('...')) return;` (contoh: `m_shift_security`, `t_jadwal_shift_security`, `t_override_jadwal_security`).
- File migrasi **kosong** pernah memutus rantai migrate — sudah diisi (contoh: `t_pelatihan`, `vcTipeIzin`, dll.).
- **`migrate:fake`** diganti dengan **INSERT manual** ke tabel `migrations` hanya jika benar-benar perlu (dan data DB sudah cocok).

---

## 3. Cetak biodata (CV / arsip)

- **Rute:** `GET karyawan/{nik}/biodata-cetak` → `karyawan.biodata-cetak`, method `biodataCetak`.
- **View:** `resources/views/master/karyawan/biodata-cetak.blade.php`.
- **Tombol UI:** **Biodata Cetak** di Master Karyawan (aktif jika karyawan dipilih / mode edit dengan NIK tersimpan).
- **Isi:** Data Pribadi, Fisik, Pekerjaan, Riwayat Mutasi, Pendidikan, Keluarga, Pelatihan — data dari **DB**, bukan form yang belum disimpan.
- **Orang tua (Nama Ayah / Ibu):** diambil eksplisit dari **`m_karyawan.nama_ayah`** & **`m_karyawan.nama_ibu`** (variabel `$namaAyahOrtu`, `$namaIbuOrtu` di controller). Teks penjelasan sumber kolom di cetakan **sudah dihapus** atas permintaan user.
- **Layout cetak:**
  - Awalnya: satu bagian ≈ satu halaman → **diubah** jadi **satu alur berkelanjutan** (tanpa `page-break-after` per bagian).
  - **Riwayat Mutasi** dan seterusnya: class **`cv-section--page-2`** → di **`@media print`** pakai **`page-break-before: always`** agar **mulai dari halaman 2** (setelah Data Pribadi + Fisik + Pekerjaan di halaman 1, selama muat).
  - Judul bagian: hindari orphan (`page-break-after: avoid` pada `h2.section-title`); tabel boleh terpotong antar halaman dengan header berulang; baris tabel `break-inside: avoid` pada `tr`.

---

## 4. File / area utama untuk lanjutan

| Area | Lokasi |
|------|--------|
| Controller karyawan + biodata + mutasi | `app/Http/Controllers/KaryawanController.php` |
| Rute karyawan | `routes/web.php` (grup permission master karyawan) |
| Master Karyawan UI | `resources/views/master/karyawan/index.blade.php` |
| Mirror absen | `resources/views/absen/master/karyawan/index.blade.php` |
| Cetak biodata | `resources/views/master/karyawan/biodata-cetak.blade.php` |
| Model Mutasi | `app/Models/Mutasi.php` |
| Migrasi t_mutasi | `database/migrations/2026_04_04_100000_create_t_mutasi_table.php` |

---

## 5. Catatan operasional

- Pastikan **`php artisan migrate`** sudah jalan di environment yang membutuhkan `t_mutasi` (jika tabel belum ada).
- **Cetak / PDF:** dari dialog cetak browser → “Simpan sebagai PDF” jika diperlukan.

---

*Ringkasan ini dibuat untuk melanjutkan pembahasan pada sesi berikutnya.*
