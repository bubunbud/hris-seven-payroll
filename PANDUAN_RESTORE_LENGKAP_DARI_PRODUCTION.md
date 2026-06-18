# 🚨 PANDUAN RESTORE LENGKAP DARI PRODUCTION SERVER

**Tanggal:** 12 Januari 2026  
**Status:** URGENT - Restore Lengkap dari Production

---

## ⚠️ SITUASI SAAT INI

- Banyak fitur aplikasi yang hilang
- Fitur login tidak berfungsi
- Restore dari backup lokal tidak cukup
- **SOLUSI:** Restore lengkap dari Production Server

---

## 📋 LANGKAH RESTORE LENGKAP DARI PRODUCTION

### **TAHAP 1: Backup Lokal Saat Ini (Opsional - untuk jaga-jaga)**

```powershell
# Di Windows PowerShell
cd C:\xampp\htdocs\hris-seven-payroll

# Backup folder penting
Copy-Item -Recurse -Force "app" "app_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item -Recurse -Force "resources" "resources_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item -Force "routes\web.php" "routes\web.php.backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
```

---

### **TAHAP 2: Download Folder Lengkap dari Production**

#### **Opsi A: Menggunakan FileZilla / WinSCP (Recommended)**

1. **Buka FileZilla atau WinSCP**
2. **Connect ke Production Server:**
   - Host: `192.168.10.40` (atau IP production)
   - Username: `root` (atau username SSH)
   - Password: (password SSH)
   - Port: `22`

3. **Download Folder Lengkap:**
   - Navigate ke: `/var/www/html/hris-seven-payroll/`
   - Download folder berikut ke lokal:
     - ✅ `app/` (seluruh folder)
     - ✅ `resources/` (seluruh folder)
     - ✅ `routes/` (seluruh folder)
     - ✅ `database/` (seluruh folder - migrations, seeders)
     - ✅ `config/` (seluruh folder)
     - ✅ `public/` (seluruh folder - images, CSS, JS)
     - ✅ `bootstrap/` (seluruh folder)
     - ✅ `storage/` (hanya struktur folder, JANGAN download file log)

4. **Simpan di Lokal:**
   - Letakkan di: `C:\xampp\htdocs\hris-seven-payroll\`

---

#### **Opsi B: Menggunakan SCP (Git Bash / WSL)**

```bash
# Dari Git Bash atau WSL
cd /c/xampp/htdocs/hris-seven-payroll

# Download folder lengkap
scp -r root@192.168.10.40:/var/www/html/hris-seven-payroll/app ./
scp -r root@192.168.10.40:/var/www/html/hris-seven-payroll/resources ./
scp -r root@192.168.10.40:/var/www/html/hris-seven-payroll/routes ./
scp -r root@192.168.10.40:/var/www/html/hris-seven-payroll/database ./
scp -r root@192.168.10.40:/var/www/html/hris-seven-payroll/config ./
scp -r root@192.168.10.40:/var/www/html/hris-seven-payroll/public ./
scp -r root@192.168.10.40:/var/www/html/hris-seven-payroll/bootstrap ./
```

---

### **TAHAP 3: Copy File ke Lokal (Replace)**

#### **File yang HARUS di-Replace:**

```powershell
# Di Windows PowerShell
cd C:\xampp\htdocs\hris-seven-payroll

# Replace folder lengkap
Copy-Item -Recurse -Force ".\app" ".\app" -ErrorAction SilentlyContinue
Copy-Item -Recurse -Force ".\resources" ".\resources" -ErrorAction SilentlyContinue
Copy-Item -Recurse -Force ".\routes" ".\routes" -ErrorAction SilentlyContinue
Copy-Item -Recurse -Force ".\database" ".\database" -ErrorAction SilentlyContinue
Copy-Item -Recurse -Force ".\config" ".\config" -ErrorAction SilentlyContinue
Copy-Item -Recurse -Force ".\public" ".\public" -ErrorAction SilentlyContinue
Copy-Item -Recurse -Force ".\bootstrap" ".\bootstrap" -ErrorAction SilentlyContinue
```

---

### **TAHAP 4: File yang JANGAN di-Replace (Tetap Pakai Lokal)**

⚠️ **JANGAN replace file berikut (tetap pakai yang lokal):**

- ❌ `.env` - Konfigurasi database lokal
- ❌ `vendor/` - Dependencies (install ulang dengan composer)
- ❌ `node_modules/` - Dependencies (install ulang dengan npm)
- ❌ `storage/logs/*.log` - Log file lokal
- ❌ `storage/framework/cache/*` - Cache lokal
- ❌ `storage/framework/sessions/*` - Session lokal
- ❌ `storage/framework/views/*` - Compiled views lokal

---

### **TAHAP 5: Install Dependencies**

```powershell
# Install Composer dependencies
composer install --no-dev

# Install NPM dependencies (jika ada)
npm install

# Build assets (jika ada)
npm run build
```

---

### **TAHAP 6: Setup Environment**

```powershell
# Pastikan .env sudah benar untuk lokal
# JANGAN copy .env dari production!

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache ulang
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### **TAHAP 7: Set Permission (Jika di Linux/WSL)**

```bash
# Jika menggunakan WSL atau Linux
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

### **TAHAP 8: Test Aplikasi**

1. **Buka Browser:**
   - URL: `http://localhost/hris-seven-payroll`

2. **Test Login:**
   - Buka halaman login
   - Test login dengan user yang ada

3. **Test Fitur Utama:**
   - Dashboard
   - Master Karyawan
   - Absensi
   - Payroll
   - Dll

---

### **TAHAP 9: Commit ke Git (Setelah Semua Berfungsi)**

```powershell
# Cek status
git status

# Add semua perubahan
git add -A

# Commit
git commit -m "Restore lengkap dari production server (12 Jan 2026) - Semua file aplikasi lengkap dari production"

# Push ke GitHub
git push origin main
```

---

## 📝 CHECKLIST RESTORE

- [ ] Backup lokal sudah dibuat (opsional)
- [ ] Download folder `app/` dari production
- [ ] Download folder `resources/` dari production
- [ ] Download folder `routes/` dari production
- [ ] Download folder `database/` dari production
- [ ] Download folder `config/` dari production
- [ ] Download folder `public/` dari production
- [ ] Download folder `bootstrap/` dari production
- [ ] Copy semua folder ke lokal (replace)
- [ ] **TIDAK** replace `.env` (tetap pakai lokal)
- [ ] **TIDAK** replace `vendor/` (install ulang)
- [ ] **TIDAK** replace `storage/logs/` (tetap pakai lokal)
- [ ] Install dependencies (`composer install`)
- [ ] Clear cache (`php artisan config:clear`, dll)
- [ ] Test login berfungsi
- [ ] Test fitur utama berfungsi
- [ ] Commit dan push ke GitHub

---

## ⚠️ CATATAN PENTING

1. **JANGAN replace `.env`** - Tetap pakai konfigurasi database lokal
2. **JANGAN replace `vendor/`** - Install ulang dengan `composer install`
3. **JANGAN replace `storage/logs/`** - Tetap pakai log lokal
4. **Backup dulu** sebelum replace (jika perlu rollback)
5. **Test dulu** sebelum commit ke Git
6. **Commit hanya setelah semua fitur berfungsi**

---

## 🆘 TROUBLESHOOTING

### **Error: Class not found**
```powershell
composer dump-autoload
php artisan optimize:clear
```

### **Error: Route not found**
```powershell
php artisan route:clear
php artisan route:cache
```

### **Error: View not found**
```powershell
php artisan view:clear
php artisan view:cache
```

### **Error: Permission denied**
```bash
chmod -R 775 storage bootstrap/cache
```

---

## ✅ SETELAH RESTORE BERHASIL

1. **Test semua fitur utama**
2. **Commit ke Git** (jika semua berfungsi)
3. **Push ke GitHub**
4. **Dokumentasikan** perubahan yang dilakukan

---

**Dokumen ini dibuat pada:** 12 Januari 2026  
**Versi:** 1.0  
**Status:** URGENT - Restore Lengkap dari Production



