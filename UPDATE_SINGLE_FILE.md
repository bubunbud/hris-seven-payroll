# Panduan Update Satu File ke Server Ubuntu

Panduan singkat untuk mengupdate satu file saja ke server Ubuntu.

## File yang Diupdate

-   `resources/views/instruksi-kerja-lembur/index.blade.php`

## LANGKAH 1: Upload File ke Server

### Opsi A: Menggunakan SCP (dari Windows dengan Git Bash/WSL)

```bash
# Dari folder project di Windows
cd C:\xampp\htdocs\hris-seven-payroll

# Upload file ke server
scp resources/views/instruksi-kerja-lembur/index.blade.php root@192.168.10.40:/tmp/instruksi-kerja-lembur-index.blade.php
```

### Opsi B: Menggunakan FileZilla/SFTP Client

1. Buka FileZilla atau SFTP client
2. Connect ke `192.168.10.40` dengan user `root`
3. Upload file `resources/views/instruksi-kerja-lembur/index.blade.php` ke folder `/tmp/` di server

---

## LANGKAH 2: Copy File ke Folder Project (di Server)

```bash
# Login ke server Ubuntu
ssh root@192.168.10.40

# Backup file lama (opsional, untuk jaga-jaga)
cp /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php \
   /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php.backup

# Copy file baru
cp /tmp/instruksi-kerja-lembur-index.blade.php \
   /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php

# Set ownership dan permissions
chown www-data:www-data /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php
chmod 644 /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php
```

---

## LANGKAH 3: Clear Cache Laravel (di Server)

```bash
cd /var/www/html/hris-seven-payroll

# Clear view cache
php artisan view:clear

# Clear semua cache (opsional)
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

## LANGKAH 4: Verifikasi

1. Buka browser dan akses: `http://192.168.10.40/`
2. Login dengan user admin atau user lain
3. Buka halaman "Instruksi Kerja Lembur"
4. Klik tombol "Tambah"
5. Pastikan tidak ada error di console browser (F12 > Console)
6. Pastikan modal form muncul dengan benar

---

## LANGKAH 5: Cleanup (Opsional)

```bash
# Hapus file temporary
rm /tmp/instruksi-kerja-lembur-index.blade.php

# Hapus backup jika sudah yakin (opsional)
# rm /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php.backup
```

---

## Script Otomatis (All-in-One)

Jika ingin lebih cepat, buat script di server:

```bash
# Di server Ubuntu, buat file update-instruksi-lembur.sh
cat > /tmp/update-instruksi-lembur.sh << 'EOF'
#!/bin/bash
echo "=== Update Instruksi Kerja Lembur ==="

# Backup
cp /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php \
   /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php.backup.$(date +%Y%m%d_%H%M%S)

# Copy file baru
if [ -f "/tmp/instruksi-kerja-lembur-index.blade.php" ]; then
    cp /tmp/instruksi-kerja-lembur-index.blade.php \
       /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php

    # Set permissions
    chown www-data:www-data /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php
    chmod 644 /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php

    # Clear cache
    cd /var/www/html/hris-seven-payroll
    php artisan view:clear

    echo "✓ File berhasil diupdate!"
    echo "✓ Cache sudah di-clear"
else
    echo "✗ File /tmp/instruksi-kerja-lembur-index.blade.php tidak ditemukan!"
    echo "  Pastikan file sudah di-upload ke /tmp/ terlebih dahulu"
    exit 1
fi
EOF

# Set executable
chmod +x /tmp/update-instruksi-lembur.sh
```

**Cara pakai:**

1. Upload file ke `/tmp/instruksi-kerja-lembur-index.blade.php` (Langkah 1)
2. Jalankan script: `bash /tmp/update-instruksi-lembur.sh`

---

## Troubleshooting

### Error: Permission Denied

```bash
sudo chown -R www-data:www-data /var/www/html/hris-seven-payroll/resources/views
sudo chmod -R 644 /var/www/html/hris-seven-payroll/resources/views/instruksi-kerja-lembur/index.blade.php
```

### Error: File Not Found

-   Pastikan file sudah di-upload ke `/tmp/` dengan nama yang benar
-   Check dengan: `ls -la /tmp/instruksi-kerja-lembur-index.blade.php`

### Masih Error di Browser

-   Clear browser cache (Ctrl+Shift+Delete)
-   Hard refresh (Ctrl+F5)
-   Check Laravel log: `tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log`

---

## SELESAI!

Setelah semua langkah selesai, aplikasi sudah ter-update dengan perbaikan error `freeRoleCheckbox is not defined`.







