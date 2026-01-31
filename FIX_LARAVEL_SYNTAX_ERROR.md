# Fix Laravel Syntax Error - InstruksiKerjaLemburController

**Error:** `unexpected '=' on line 1` saat `Request->validate()` dipanggil di line 831

**Penyebab:** File di server mungkin berbeda dengan localhost atau ada encoding issue.

---

## 🚀 Solusi Cepat

### **Step 1: Check Syntax Error di Server**

```bash
ssh root@192.168.10.40
cd /var/www/html/hris-seven-payroll

# Check PHP syntax
php -l app/Http/Controllers/InstruksiKerjaLemburController.php

# Check line 830-836
sed -n '825,840p' app/Http/Controllers/InstruksiKerjaLemburController.php
```

### **Step 2: Fix Line Endings (jika perlu)**

```bash
# Convert CRLF ke LF (jika file dari Windows)
dos2unix app/Http/Controllers/InstruksiKerjaLemburController.php

# Atau dengan sed
sed -i 's/\r$//' app/Http/Controllers/InstruksiKerjaLemburController.php
```

### **Step 3: Pastikan File Sama dengan Localhost**

**Upload ulang file controller dari localhost ke server:**

```bash
# Dari localhost
scp app/Http/Controllers/InstruksiKerjaLemburController.php root@192.168.10.40:/var/www/html/hris-seven-payroll/app/Http/Controllers/
```

### **Step 4: Clear Cache**

```bash
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
rm -f bootstrap/cache/routes*.php
rm -f bootstrap/cache/config.php
```

### **Step 5: Test**

```bash
# Check Laravel log
tail -50 storage/logs/laravel.log

# Test aplikasi
curl -I http://192.168.10.40/hris-seven-payroll/login
```

---

## 📝 Solusi Manual

Jika masih error, cek file di server dan pastikan method `calculateLemburNominal` seperti ini:

```php
public function calculateLemburNominal(Request $request)
{
    $request->validate([
        'nik' => 'required|string',
        'tanggal' => 'required|date',
        'jam_mulai' => 'required|string|date_format:H:i',
        'jam_selesai' => 'required|string|date_format:H:i',
        'durasi_istirahat' => 'nullable|integer|min:0',
    ]);

    // ... rest of code
}
```

**PENTING:**

-   ✅ `$request->validate([...])` (benar)
-   ❌ `$request->validate = ([...])` (salah)
-   ❌ `$request->validate = [...]` (salah)

---

## 🔧 Script All-in-One

```bash
#!/bin/bash
cd /var/www/html/hris-seven-payroll

# 1. Check syntax
echo "=== Checking PHP syntax ==="
php -l app/Http/Controllers/InstruksiKerjaLemburController.php

# 2. Fix line endings
echo "=== Fixing line endings ==="
sed -i 's/\r$//' app/Http/Controllers/InstruksiKerjaLemburController.php

# 3. Check validate method
echo "=== Checking validate method ==="
sed -n '828,836p' app/Http/Controllers/InstruksiKerjaLemburController.php

# 4. Clear cache
echo "=== Clearing cache ==="
sudo -u www-data php artisan optimize:clear
rm -f bootstrap/cache/routes*.php
rm -f bootstrap/cache/config.php

# 5. Check log
echo "=== Latest Laravel log ==="
tail -20 storage/logs/laravel.log
```

---

## ✅ Checklist

-   [ ] PHP syntax check passed (`php -l` tidak ada error)
-   [ ] Line endings sudah LF (bukan CRLF)
-   [ ] File controller sama dengan localhost
-   [ ] Cache sudah di-clear
-   [ ] Laravel log tidak ada error baru

---

**SELESAI!** 🎉

Setelah fix, test aplikasi lagi. Jika masih error, check Laravel log untuk detail error.



