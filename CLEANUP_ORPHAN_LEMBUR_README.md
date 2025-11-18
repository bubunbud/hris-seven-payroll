# Cleanup Orphan Lembur Data

## Masalah

Ketika tabel `t_absen` diganti dengan backup, mungkin ada data dengan `vcCounter` yang sudah terisi tapi tidak ada di `t_lembur_header` (orphan data). Hal ini menyebabkan data muncul di Realisasi Lembur padahal seharusnya tidak muncul karena tidak ada Instruksi Kerja Lembur.

## Solusi

### 1. Perbaikan Query (Sudah Diterapkan)

Query di `RealisasiLemburController` sudah diperbaiki untuk memastikan hanya menampilkan data dengan `vcCounter` yang benar-benar ada di `t_lembur_header`:

```php
->whereNotNull('vcCounter') // Hanya yang memiliki kode lembur
->whereHas('lemburHeader') // Pastikan vcCounter benar-benar ada di t_lembur_header
```

### 2. Cleanup Data Orphan

Gunakan command berikut untuk membersihkan data orphan di `t_absen`:

#### Preview Data yang Akan Dihapus (Dry Run)

```bash
php artisan lembur:cleanup-orphan --dry-run
```

Command ini akan menampilkan data yang memiliki `vcCounter` tidak valid tanpa menghapusnya.

#### Cleanup Data (Dengan Konfirmasi)

```bash
php artisan lembur:cleanup-orphan
```

Command ini akan:

-   Menampilkan data yang akan dihapus
-   Meminta konfirmasi
-   Membersihkan data dengan meng-set:
    -   `vcCounter` → `null`
    -   `dtJamMasukLembur` → `null`
    -   `dtJamKeluarLembur` → `null`
    -   `intDurasiIstirahat` → `0`
    -   `vcCfmLembur` → `'0'`

#### Cleanup Data (Tanpa Konfirmasi)

```bash
php artisan lembur:cleanup-orphan --force
```

## Langkah-langkah Setelah Mengganti Tabel t_absen

1. **Jalankan cleanup command** untuk membersihkan data orphan:

    ```bash
    php artisan lembur:cleanup-orphan --dry-run  # Preview dulu
    php artisan lembur:cleanup-orphan            # Cleanup dengan konfirmasi
    ```

2. **Verifikasi** di halaman Realisasi Lembur bahwa hanya data dengan Instruksi Kerja Lembur yang muncul.

## Catatan

-   Data yang dibersihkan adalah data yang memiliki `vcCounter` tapi tidak ada di `t_lembur_header`
-   Field-field terkait lembur akan di-reset ke nilai default (null atau 0)
-   Data absensi normal (tanpa lembur) tidak akan terpengaruh





