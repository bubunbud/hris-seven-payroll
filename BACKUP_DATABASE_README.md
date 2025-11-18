# Backup Database Script

## Deskripsi

Script untuk melakukan backup database "seven" dengan mengatasi error view yang invalid.

## Masalah yang Diatasi

Error yang terjadi saat backup manual:

```
mysqldump: Got error: 1356: "View 'seven.view_rekap_absen_union' references invalid table(s) or column(s) or function(s) or definer/invoker of view lack rights to use them" when using LOCK TABLES
```

## Solusi

Menggunakan opsi mysqldump:

-   `--skip-lock-tables`: Melewati LOCK TABLES untuk menghindari error view
-   `--single-transaction`: Memastikan konsistensi data untuk InnoDB
-   `--routines`: Include stored procedures dan functions
-   `--triggers`: Include triggers

## Cara Menggunakan

### Method 1: Menggunakan Script PHP (Recommended)

```bash
php backup_database.php
```

Script akan:

1. Membaca konfigurasi database dari Laravel (.env)
2. Membuat backup dengan nama: `backup_seven_YYYYMMDD_HHMMSS.sql`
3. Menampilkan status dan ukuran file backup

### Method 2: Manual Command

```bash
C:\xampp\mysql\bin\mysqldump.exe -u root -h localhost --skip-lock-tables --single-transaction --routines --triggers seven > backup_seven_YYYYMMDD.sql
```

## File Backup

-   Lokasi: Root project directory
-   Format: `backup_seven_YYYYMMDD_HHMMSS.sql`
-   Ukuran: ~118 MB (tergantung data)

## Restore Database

### Restore ke database yang sama

```bash
C:\xampp\mysql\bin\mysql.exe -u root -h localhost seven < backup_seven_YYYYMMDD_HHMMSS.sql
```

### Restore ke database baru (misal: hris_seven)

```bash
# 1. Buat database baru
C:\xampp\mysql\bin\mysql.exe -u root -h localhost -e "CREATE DATABASE hris_seven;"

# 2. Restore backup
C:\xampp\mysql\bin\mysql.exe -u root -h localhost hris_seven < backup_seven_YYYYMMDD_HHMMSS.sql
```

## Catatan Penting

1. **View yang Bermasalah**: View `view_rekap_absen_union` mungkin tidak akan ter-restore dengan sempurna jika ada dependency yang hilang. Perlu dicek manual setelah restore.

2. **Ukuran File**: Backup file cukup besar (~118 MB), pastikan ada cukup space disk.

3. **Waktu Backup**: Backup memakan waktu beberapa menit tergantung ukuran database.

4. **Verifikasi**: Setelah backup, selalu verifikasi bahwa file backup valid dengan mengecek:
    - Ukuran file (harus > 1 MB)
    - Header file (harus berisi SQL dump header)
    - Tidak ada error message di dalam file

## Troubleshooting

### Error: Access Denied

-   Pastikan user MySQL memiliki hak akses yang cukup
-   Cek password di file .env

### Error: mysqldump not found

-   Pastikan XAMPP terinstall di `C:\xampp\`
-   Atau update path di script `backup_database.php`

### Error: File terlalu kecil

-   Kemungkinan ada error saat backup
-   Cek isi file backup untuk melihat error message
-   Pastikan database tidak sedang digunakan oleh aplikasi lain

## Opsi Tambahan

Jika ingin backup tanpa view yang bermasalah:

```bash
C:\xampp\mysql\bin\mysqldump.exe -u root -h localhost --skip-lock-tables --single-transaction --routines --triggers --ignore-table=seven.view_rekap_absen_union seven > backup_seven.sql
```

Untuk backup hanya struktur (tanpa data):

```bash
C:\xampp\mysql\bin\mysqldump.exe -u root -h localhost --skip-lock-tables --no-data seven > backup_seven_structure.sql
```

Untuk backup hanya data (tanpa struktur):

```bash
C:\xampp\mysql\bin\mysqldump.exe -u root -h localhost --skip-lock-tables --no-create-info seven > backup_seven_data.sql
```


