# Script PowerShell untuk update HRIS Seven Payroll ke Server Ubuntu
# Usage: .\update-to-ubuntu.ps1

# Konfigurasi
$SERVER_IP = "192.168.10.40"
$SERVER_USER = "root"
$SERVER_PATH = "/var/www/html/hris-seven-payroll"
$LOCAL_PATH = "C:\xampp\htdocs\hris-seven-payroll"

Write-Host "========================================" -ForegroundColor Green
Write-Host "  Update HRIS Seven Payroll ke Ubuntu  " -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Check apakah file-file yang diperlukan ada
Write-Host "[1/8] Checking files..." -ForegroundColor Yellow
$FILES = @(
    "app\Http\Controllers\InstruksiKerjaLemburController.php",
    "app\Http\Controllers\ClosingController.php",
    "app\Services\LemburCalculationService.php",
    "app\Models\LemburDetail.php",
    "resources\views\instruksi-kerja-lembur\index.blade.php",
    "routes\web.php",
    "database\migrations\2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php"
)

foreach ($file in $FILES) {
    if (-not (Test-Path $file)) {
        Write-Host "ERROR: File tidak ditemukan: $file" -ForegroundColor Red
        exit 1
    }
}
Write-Host "✓ Semua file ditemukan" -ForegroundColor Green
Write-Host ""

# Check apakah SCP tersedia (dari Git Bash atau WSL)
$scpAvailable = $false
if (Get-Command scp -ErrorAction SilentlyContinue) {
    $scpAvailable = $true
} elseif (Get-Command wsl -ErrorAction SilentlyContinue) {
    $scpAvailable = $true
    $useWSL = $true
}

if (-not $scpAvailable) {
    Write-Host "ERROR: SCP tidak ditemukan. Silakan install Git Bash atau WSL." -ForegroundColor Red
    Write-Host "Atau gunakan FileZilla/WinSCP untuk upload file secara manual." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "File yang perlu di-upload:" -ForegroundColor Yellow
    foreach ($file in $FILES) {
        Write-Host "  - $file" -ForegroundColor Cyan
    }
    exit 1
}

# Upload file ke server
Write-Host "[2/8] Uploading files to server..." -ForegroundColor Yellow
foreach ($file in $FILES) {
    $filename = Split-Path $file -Leaf
    Write-Host "  Uploading: $file"
    
    if ($useWSL) {
        wsl scp "$file" "${SERVER_USER}@${SERVER_IP}:/tmp/$filename"
    } else {
        scp "$file" "${SERVER_USER}@${SERVER_IP}:/tmp/$filename"
    }
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: Gagal upload $file" -ForegroundColor Red
        exit 1
    }
}
Write-Host "✓ Semua file berhasil di-upload" -ForegroundColor Green
Write-Host ""

# Backup di server
Write-Host "[3/8] Creating backup on server..." -ForegroundColor Yellow
$backupScript = @"
cd /var/www/html/hris-seven-payroll
BACKUP_DATE=`$(date +%Y%m%d_%H%M%S)
mysqldump -u root -proot123 hris_seven > ~/backup_hris_seven_`${BACKUP_DATE}.sql
cp .env ~/backup_env_`${BACKUP_DATE}.txt
echo "✓ Backup database dan .env selesai"
"@

if ($useWSL) {
    wsl bash -c "ssh ${SERVER_USER}@${SERVER_IP} '$backupScript'"
} else {
    ssh "${SERVER_USER}@${SERVER_IP}" $backupScript
}
Write-Host "✓ Backup selesai" -ForegroundColor Green
Write-Host ""

# Copy file ke folder aplikasi
Write-Host "[4/8] Copying files to application folder..." -ForegroundColor Yellow
$copyScript = @"
cd /var/www/html/hris-seven-payroll
mkdir -p app/Services
cp /tmp/InstruksiKerjaLemburController.php app/Http/Controllers/
cp /tmp/ClosingController.php app/Http/Controllers/
cp /tmp/LemburCalculationService.php app/Services/
cp /tmp/LemburDetail.php app/Models/
cp /tmp/index.blade.php resources/views/instruksi-kerja-lembur/
cp /tmp/web.php routes/
cp /tmp/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php database/migrations/
chown -R www-data:www-data /var/www/html/hris-seven-payroll
chmod -R 755 /var/www/html/hris-seven-payroll
chmod -R 775 /var/www/html/hris-seven-payroll/storage
chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache
echo "✓ File berhasil di-copy dan permissions di-set"
"@

if ($useWSL) {
    wsl bash -c "ssh ${SERVER_USER}@${SERVER_IP} '$copyScript'"
} else {
    ssh "${SERVER_USER}@${SERVER_IP}" $copyScript
}
Write-Host "✓ File berhasil di-copy" -ForegroundColor Green
Write-Host ""

# Update autoload
Write-Host "[5/8] Updating composer autoload..." -ForegroundColor Yellow
$autoloadScript = @"
cd /var/www/html/hris-seven-payroll
sudo -u www-data composer dump-autoload --optimize
echo "✓ Autoload berhasil di-update"
"@

if ($useWSL) {
    wsl bash -c "ssh ${SERVER_USER}@${SERVER_IP} '$autoloadScript'"
} else {
    ssh "${SERVER_USER}@${SERVER_IP}" $autoloadScript
}
Write-Host "✓ Autoload berhasil di-update" -ForegroundColor Green
Write-Host ""

# Run migration
Write-Host "[6/8] Running migration..." -ForegroundColor Yellow
$migrationScript = @"
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan migrate --force
echo "✓ Migration berhasil dijalankan"
"@

if ($useWSL) {
    wsl bash -c "ssh ${SERVER_USER}@${SERVER_IP} '$migrationScript'"
} else {
    ssh "${SERVER_USER}@${SERVER_IP}" $migrationScript
}
Write-Host "✓ Migration berhasil dijalankan" -ForegroundColor Green
Write-Host ""

# Clear cache
Write-Host "[7/8] Clearing cache..." -ForegroundColor Yellow
$cacheScript = @"
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
echo "✓ Cache berhasil di-clear dan di-rebuild"
"@

if ($useWSL) {
    wsl bash -c "ssh ${SERVER_USER}@${SERVER_IP} '$cacheScript'"
} else {
    ssh "${SERVER_USER}@${SERVER_IP}" $cacheScript
}
Write-Host "✓ Cache berhasil di-clear" -ForegroundColor Green
Write-Host ""

# Cleanup
Write-Host "[8/8] Cleaning up temporary files..." -ForegroundColor Yellow
$cleanupScript = @"
rm -f /tmp/InstruksiKerjaLemburController.php
rm -f /tmp/ClosingController.php
rm -f /tmp/LemburCalculationService.php
rm -f /tmp/LemburDetail.php
rm -f /tmp/index.blade.php
rm -f /tmp/web.php
rm -f /tmp/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php
echo "✓ Temporary files berhasil dihapus"
"@

if ($useWSL) {
    wsl bash -c "ssh ${SERVER_USER}@${SERVER_IP} '$cleanupScript'"
} else {
    ssh "${SERVER_USER}@${SERVER_IP}" $cleanupScript
}
Write-Host "✓ Cleanup selesai" -ForegroundColor Green
Write-Host ""

Write-Host "========================================" -ForegroundColor Green
Write-Host "  UPDATE SELESAI! 🎉" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Silakan test aplikasi di:" -ForegroundColor Yellow
Write-Host "  http://${SERVER_IP}/hris-seven-payroll" -ForegroundColor Cyan
Write-Host ""
Write-Host "Check log jika ada error:" -ForegroundColor Yellow
Write-Host "  ssh ${SERVER_USER}@${SERVER_IP}" -ForegroundColor Cyan
Write-Host "  tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log" -ForegroundColor Cyan
Write-Host ""





