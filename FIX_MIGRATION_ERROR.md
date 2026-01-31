# Fix Migration Error - Table Already Exists

Error: `SQLSTATE[42S01]: Base table or view already exists: 1050 Table 't_lembur_detail' already exists`

Ini terjadi karena migration mencoba membuat tabel yang sudah ada.

---

## 🚀 Solusi Cepat

### **Langkah 1: Check dan Fix Migration**

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# Check migration status
sudo -u www-data php artisan migrate:status

# Mark migration as run jika table sudah ada
mysql -u root -proot123 hris_seven <<'EOF'
INSERT IGNORE INTO migrations (migration, batch)
VALUES ('2025_11_01_071718_create_t_lembur_detail_table',
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS temp));
EOF

# Run migrations lagi
sudo -u www-data php artisan migrate --force
```

### **Langkah 2: Check Apache Error Log (File yang Benar)**

```bash
# File yang benar (tanpa ~)
tail -50 /var/log/apache2/error.log

# Bukan error.log~ (itu backup file)
```

### **Langkah 3: Check Laravel Log**

```bash
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

---

## 📝 Solusi Manual

### **Option 1: Mark Migration as Run**

Jika tabel sudah ada, mark migration sebagai sudah dijalankan:

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# Check apakah table ada
mysql -u root -proot123 hris_seven -e "SHOW TABLES LIKE 't_lembur_detail';"

# Jika table ada, insert ke migrations table
mysql -u root -proot123 hris_seven <<'EOF'
INSERT IGNORE INTO migrations (migration, batch)
SELECT '2025_11_01_071718_create_t_lembur_detail_table',
       COALESCE(MAX(batch), 0) + 1
FROM migrations;
EOF

# Run migrations
sudo -u www-data php artisan migrate --force
```

### **Option 2: Skip Migration yang Error**

Edit migration file untuk check table existence:

```bash
sudo nano database/migrations/2025_11_01_071718_create_t_lembur_detail_table.php
```

Tambahkan check di method `up()`:

```php
public function up(): void
{
    if (!Schema::hasTable('t_lembur_detail')) {
        Schema::create('t_lembur_detail', function (Blueprint $table) {
            // ... existing code ...
        });
    }
}
```

Kemudian:

```bash
sudo -u www-data php artisan migrate --force
```

### **Option 3: Rollback dan Migrate Lagi (HATI-HATI!)**

**WARNING**: Ini akan menghapus data di tabel!

```bash
# Rollback migration terakhir
sudo -u www-data php artisan migrate:rollback --step=1

# Migrate lagi
sudo -u www-data php artisan migrate --force
```

---

## 🔍 Check Error yang Benar

### **Apache Error Log**

```bash
# File yang benar
tail -50 /var/log/apache2/error.log

# Bukan error.log~ (itu backup)
```

### **Laravel Log**

```bash
tail -50 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
```

### **Check Active Sites**

```bash
a2query -s
```

Pastikan hanya `000-default` yang aktif.

### **Test Apache Config**

```bash
apache2ctl configtest
apache2ctl -S
```

---

## 🎯 Quick Fix (All in One)

Jalankan semua command ini:

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# 1. Fix migration
mysql -u root -proot123 hris_seven <<'EOF'
INSERT IGNORE INTO migrations (migration, batch)
SELECT '2025_11_01_071718_create_t_lembur_detail_table',
       COALESCE(MAX(batch), 0) + 1
FROM migrations;
EOF

# 2. Run migrations
sudo -u www-data php artisan migrate --force

# 3. Clear cache
sudo -u www-data php artisan optimize:clear
sudo rm -f bootstrap/cache/routes*.php
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache

# 4. Check Apache
echo "=== Active Sites ==="
a2query -s
echo ""
echo "=== Apache Config Test ==="
apache2ctl configtest
echo ""
echo "=== Apache Error Log (last 20 lines) ==="
tail -20 /var/log/apache2/error.log
echo ""
echo "=== Laravel Log (last 20 lines) ==="
tail -20 storage/logs/laravel.log
```

---

## ✅ Verifikasi

Setelah fix:

1. **Migration Status:**

    ```bash
    sudo -u www-data php artisan migrate:status
    ```

    Semua migration harus marked as "Ran"

2. **Test Aplikasi:**
   `http://192.168.10.40/hris-seven-payroll`

3. **Check Logs:**
    ```bash
    tail -f /var/log/apache2/error.log
    tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log
    ```

---

**SELESAI!** 🎉

Setelah fix migration, aplikasi seharusnya bisa diakses.




