# Fix Storage/Logs Directory - Laravel Log Tidak Ada

File `laravel.log` tidak ada biasanya karena:

1. Folder `storage/logs` tidak ada atau tidak writable
2. Permission tidak benar
3. Aplikasi belum pernah dijalankan dengan benar

---

## 🚀 Solusi Cepat (Script Otomatis)

### **Langkah 1: Upload dan Jalankan Script**

```bash
# Upload script
scp fix-storage-logs.sh root@192.168.10.40:/tmp/

# SSH ke server
ssh root@192.168.10.40

# Jalankan script
chmod +x /tmp/fix-storage-logs.sh
sudo /tmp/fix-storage-logs.sh
```

---

## 📝 Solusi Manual

### **Step 1: Check Storage Directory**

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# Check apakah storage ada
ls -la storage/
```

### **Step 2: Create Logs Directory**

```bash
# Buat folder logs jika tidak ada
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
```

### **Step 3: Fix Permissions**

```bash
# Set ownership
sudo chown -R www-data:www-data storage/

# Set permissions (775 = writable untuk www-data)
sudo chmod -R 775 storage/
```

### **Step 4: Create laravel.log File**

```bash
# Buat file log
touch storage/logs/laravel.log

# Set ownership dan permissions
chown www-data:www-data storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
```

### **Step 5: Test Write Permission**

```bash
# Test apakah www-data bisa write
sudo -u www-data touch storage/logs/test-write.log

# Jika berhasil, hapus file test
rm storage/logs/test-write.log
```

---

## 🎯 Quick Fix (All in One)

Jalankan semua command ini:

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# 1. Create directories
mkdir -p storage/logs
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/app/public

# 2. Fix permissions
chown -R www-data:www-data storage/
chmod -R 775 storage/

# 3. Create laravel.log
touch storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

# 4. Test write permission
sudo -u www-data touch storage/logs/test.log && rm storage/logs/test.log && echo "Write OK" || echo "Write ERROR"

# 5. Verify
echo "=== Storage Structure ==="
ls -la storage/
echo ""
echo "=== Logs Directory ==="
ls -la storage/logs/
echo ""
echo "=== Laravel Log ==="
if [ -f storage/logs/laravel.log ]; then
    echo "File: storage/logs/laravel.log"
    echo "Size: $(du -h storage/logs/laravel.log | cut -f1)"
    echo "Permissions: $(ls -l storage/logs/laravel.log | awk '{print $1, $3, $4}')"
else
    echo "ERROR: File masih tidak ada!"
fi
```

---

## ✅ Verifikasi

Setelah fix:

1. **Check file ada:**

    ```bash
    ls -la /var/www/html/hris-seven-payroll/storage/logs/laravel.log
    ```

2. **Check permissions:**

    ```bash
    ls -l /var/www/html/hris-seven-payroll/storage/logs/laravel.log
    ```

    Harus: `-rw-rw-r-- 1 www-data www-data`

3. **Test write:**

    ```bash
    sudo -u www-data echo "test" >> /var/www/html/hris-seven-payroll/storage/logs/laravel.log
    tail -1 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
    ```

4. **Check setelah akses aplikasi:**

    ```bash
    # Akses aplikasi di browser
    # http://192.168.10.40/hris-seven-payroll

    # Check log
    tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log
    ```

---

## 🔍 Troubleshooting

### **Masih Tidak Ada Setelah Fix?**

1. **Check permissions:**

    ```bash
    ls -ld /var/www/html/hris-seven-payroll/storage
    ls -ld /var/www/html/hris-seven-payroll/storage/logs
    ```

    Harus: `drwxrwxr-x` dan owner `www-data`

2. **Check SELinux (jika aktif):**

    ```bash
    getenforce
    # Jika Enforcing, mungkin perlu:
    sudo setsebool -P httpd_unified 1
    ```

3. **Check disk space:**

    ```bash
    df -h /var/www/html
    ```

4. **Manual create dengan www-data:**
    ```bash
    sudo -u www-data touch /var/www/html/hris-seven-payroll/storage/logs/laravel.log
    sudo -u www-data chmod 664 /var/www/html/hris-seven-payroll/storage/logs/laravel.log
    ```

---

## 📋 Checklist

-   [ ] Folder `storage/logs` ada
-   [ ] Folder `storage/framework/*` ada
-   [ ] Permissions `storage/` adalah `775`
-   [ ] Ownership `storage/` adalah `www-data:www-data`
-   [ ] File `laravel.log` ada
-   [ ] File `laravel.log` writable oleh www-data
-   [ ] Test write permission berhasil

---

**SELESAI!** 🎉

Setelah fix, file `laravel.log` seharusnya sudah ada dan bisa di-write oleh Laravel.




