# Ringkasan Pekerjaan - Master Karyawan (15 Januari 2025)

## 1. Auto-generate NIK

### Konsep

-   Format NIK: 8 digit = 4 digit tahun masuk + 4 digit counter
-   Contoh: `20250001` (tahun 2025, counter 1)
-   Counter menyambung dari NIK terakhir untuk tahun yang sama

### Implementasi

**Backend (`app/Http/Controllers/KaryawanController.php`):**

-   Method `generateNik()`: API endpoint untuk generate NIK berdasarkan tahun
    -   Route: `POST /karyawan/generate-nik`
    -   Parameter: `tahun` (integer, 2000-2099)
    -   Logic: Cari NIK terakhir untuk tahun tersebut, extract counter, increment, generate NIK baru
    -   Ensure uniqueness dengan while loop
-   Method `store()`: Auto-generate NIK jika kosong
    -   Jika NIK kosong dan Tgl_Masuk ada → generate NIK otomatis
    -   Jika NIK kosong dan Tgl_Masuk juga kosong → return error
    -   Validasi: NIK menjadi `nullable` (tidak required)

**Frontend (`resources/views/master/karyawan/index.blade.php`):**

-   Field NIK: `readonly` karena auto-generate
-   Event listener pada `tanggal_masuk`: saat diisi, auto-generate NIK (hanya mode baru)
-   Function `generateNikFromTahunMasuk()`: extract tahun dari date, call API, set NIK
-   Hanya generate jika field NIK kosong

### File yang Diubah

-   `app/Http/Controllers/KaryawanController.php`
-   `routes/web.php` (tambah route generate-nik)
-   `resources/views/master/karyawan/index.blade.php`

---

## 2. Auto-fill Nama Lengkap

### Konsep

-   Nama Lengkap = Nama Depan + (spasi) + Nama Tengah (jika ada) + (spasi) + Nama Akhir
-   Field Nama Lengkap readonly karena auto-fill

### Implementasi

**Frontend (`resources/views/master/karyawan/index.blade.php`):**

-   Function `updateNamaLengkap()`:
    -   Ambil nilai dari Nama Depan, Nama Tengah, Nama Akhir
    -   Gabungkan dengan spasi
    -   Nama Tengah hanya ditambahkan jika ada isinya
    -   Set ke field Nama Lengkap
-   Event listener: `input` dan `change` pada ketiga field
-   Auto-update saat load data untuk edit (dipanggil di `populateForm()`)
-   Field Nama Lengkap: `readonly`

### File yang Diubah

-   `resources/views/master/karyawan/index.blade.php`

---

## 3. Upload Foto Karyawan

### Konsep

-   Upload foto dengan preview real-time
-   Validasi: hanya image, max 2MB
-   Simpan di `storage/app/public/photos/`
-   Naming: `timestamp_NIK.extension`

### Implementasi

**Backend (`app/Http/Controllers/KaryawanController.php`):**

-   Import: `use Illuminate\Support\Facades\Storage;`
-   Validasi: `'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'`
-   Method `store()`:
    -   Jika ada file photo → upload ke `public/photos/`
    -   Nama file: `time() . '_' . $data['Nik'] . '.' . $photo->getClientOriginalExtension()`
-   Method `update()`:
    -   Handle `remove_photo` flag: hapus file dari storage, set photo = null
    -   Jika upload foto baru → hapus foto lama, upload foto baru

**Frontend (`resources/views/master/karyawan/index.blade.php`):**

-   HTML: Input file hidden, tombol "CHANGE", preview image, tombol "HAPUS"
-   JavaScript:
    -   Event listener tombol "CHANGE" → trigger file input
    -   Event listener file input → validasi (type & size), preview dengan FileReader
    -   Event listener tombol "HAPUS" → reset preview, set flag `photoToRemove = true`
    -   Function `populateForm()`: load foto jika ada (path: `/storage/photos/${karyawan.photo}`)
    -   Function `saveKaryawan()`: append file ke FormData jika ada, append `remove_photo` flag jika perlu

**Storage:**

-   Symlink: `php artisan storage:link` (sudah dijalankan)
-   Path: `storage/app/public/photos/`
-   URL: `/storage/photos/filename.jpg`

### File yang Diubah

-   `app/Http/Controllers/KaryawanController.php`
-   `resources/views/master/karyawan/index.blade.php`

---

## 4. Dropdown Status Pegawai

### Konsep

-   Field "Status Pegawai" di Tab Pekerjaan menjadi dropdown
-   Opsi dinamis dari database (mirip Group Pegawai)
-   Default options jika belum ada data

### Implementasi

**Backend (`app/Http/Controllers/KaryawanController.php`):**

-   Method `index()`:
    -   Query distinct `Status_Pegawai` dari database
    -   Filter empty values
    -   Default options jika kosong: `['Tetap', 'Kontrak', 'Magang', 'Harian', 'Outsourcing']`
    -   Pass ke view sebagai `$statusPegawais`

**Frontend (`resources/views/master/karyawan/index.blade.php`):**

-   Ubah input text menjadi `<select>` dropdown
-   Loop `$statusPegawais` untuk generate options
-   Opsi default: "Pilih Status Pegawai"

### File yang Diubah

-   `app/Http/Controllers/KaryawanController.php`
-   `resources/views/master/karyawan/index.blade.php`

---

## 5. Perbaikan Error Handling Save

### Masalah

-   Notifikasi error "terjadi kesalahan" muncul meskipun data tersimpan dengan baik
-   Request tidak dikenali sebagai AJAX

### Solusi

**Backend (`app/Http/Controllers/KaryawanController.php`):**

-   Response format: tambah `JSON_UNESCAPED_UNICODE` untuk karakter Indonesia
-   Check: `$request->ajax() || $request->wantsJson()`

**Frontend (`resources/views/master/karyawan/index.blade.php`):**

-   Header: tambah `'X-Requested-With': 'XMLHttpRequest'` dan `'Accept': 'application/json'`
-   Error handling: async/await untuk handle berbagai format response
-   Check Content-Type sebelum parse JSON
-   Hanya tampilkan error jika benar-benar error

### File yang Diubah

-   `app/Http/Controllers/KaryawanController.php`
-   `resources/views/master/karyawan/index.blade.php`

---

## 6. Perbaikan Error Tanggal Lahir Anggota Keluarga

### Masalah

-   Error SQL: `Invalid datetime format: 1292 Incorrect date value: '-0001-11-30 00:00:00'`
-   Terjadi saat upload foto karyawan yang memiliki anggota keluarga dengan tanggal lahir kosong

### Solusi

**Backend (`app/Http/Controllers/KaryawanController.php`):**

-   Method `store()` dan `update()`:
    -   Handle `tglLahir` sebelum create:
        -   Jika empty string, '0000-00-00', atau invalid → set ke `null`
        -   Jika valid → parse dengan Carbon, format ke 'Y-m-d'
        -   Try-catch untuk handle exception
    -   Clean field optional: `temLahir`, `golDarah`, `jenKelamin` (empty string → null)

**Frontend (`resources/views/master/karyawan/index.blade.php`):**

-   Form submission keluarga:
    -   Convert `tglLahir` empty string menjadi `null` sebelum push ke array
    -   Clean field optional dari empty string menjadi `null`

**Catatan Penting:**

-   Input file tidak bisa di-set programmatically
-   Skip di `populateForm()`: `if (element.type === 'file') return;`

### File yang Diubah

-   `app/Http/Controllers/KaryawanController.php`
-   `resources/views/master/karyawan/index.blade.php`

---

## Teknik & Best Practices

1. **Auto-generate dengan Counter:**

    - Query NIK terakhir untuk tahun tertentu
    - Extract counter dari 4 digit terakhir
    - Increment dan ensure uniqueness dengan while loop

2. **Readonly Fields:**

    - Set `readonly` attribute di HTML
    - Pastikan tetap readonly saat `enableForm()` dengan `readOnly = true`

3. **File Upload:**

    - Validasi di frontend (type & size) dan backend (Laravel validation)
    - Preview dengan FileReader sebelum upload
    - Handle delete old file saat update
    - Symlink untuk akses public

4. **Date Handling:**

    - Convert empty string ke `null` untuk optional dates
    - Validate dengan Carbon sebelum save
    - Handle invalid dates dengan try-catch

5. **AJAX Request:**
    - Header `X-Requested-With: XMLHttpRequest` untuk Laravel detect AJAX
    - Header `Accept: application/json` untuk response JSON
    - Handle berbagai format response (JSON, HTML, text)

---

## Struktur Data

### NIK Format

-   8 digit: `YYYYNNNN`
-   YYYY = tahun masuk (4 digit)
-   NNNN = counter (4 digit, zero-padded)

### Photo Storage

-   Path: `storage/app/public/photos/`
-   Naming: `{timestamp}_{NIK}.{extension}`
-   URL: `/storage/photos/{filename}`

### Keluarga Data

-   `tglLahir`: nullable date (null jika kosong)
-   `temLahir`: nullable string
-   `golDarah`: nullable string
-   `jenKelamin`: nullable string

---

## File yang Dimodifikasi Hari Ini

1. `app/Http/Controllers/KaryawanController.php`

    - Method `generateNik()`
    - Method `store()` (auto-generate NIK, upload foto, handle keluarga)
    - Method `update()` (upload foto, handle keluarga)
    - Method `index()` (tambah $statusPegawais)

2. `routes/web.php`

    - Route: `POST /karyawan/generate-nik`

3. `resources/views/master/karyawan/index.blade.php`
    - HTML: Field NIK & Nama Lengkap readonly, photo upload UI, dropdown Status Pegawai
    - JavaScript: Auto-generate NIK, auto-fill Nama Lengkap, upload foto, error handling, date cleaning

---

## Catatan untuk Pengembangan Selanjutnya

1. NIK auto-generate hanya untuk mode baru, tidak untuk edit
2. Foto preview menggunakan FileReader untuk preview sebelum upload
3. Tanggal lahir keluarga harus di-handle dengan hati-hati (null jika kosong)
4. Input file tidak bisa di-set programmatically, harus di-skip di populateForm
5. AJAX request perlu header khusus untuk Laravel detect sebagai AJAX

---

**Status:** Semua fitur telah diimplementasikan dan diuji. Siap untuk pengembangan selanjutnya.







