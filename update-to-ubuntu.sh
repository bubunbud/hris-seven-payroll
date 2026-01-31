#!/bin/bash

# Script untuk update HRIS Seven Payroll ke Server Ubuntu
# Usage: ./update-to-ubuntu.sh

set -e  # Exit on error

# Konfigurasi
SERVER_IP="192.168.10.40"
SERVER_USER="root"
SERVER_PATH="/var/www/html/hris-seven-payroll"
LOCAL_PATH="C:/xampp/htdocs/hris-seven-payroll"

# Colors untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Update HRIS Seven Payroll ke Ubuntu  ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check apakah file-file yang diperlukan ada
echo -e "${YELLOW}[1/8] Checking files...${NC}"
FILES=(
    "app/Http/Controllers/InstruksiKerjaLemburController.php"
    "app/Http/Controllers/ClosingController.php"
    "app/Services/LemburCalculationService.php"
    "app/Models/LemburDetail.php"
    "resources/views/instruksi-kerja-lembur/index.blade.php"
    "routes/web.php"
    "database/migrations/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php"
)

for file in "${FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo -e "${RED}ERROR: File tidak ditemukan: $file${NC}"
        exit 1
    fi
done
echo -e "${GREEN}✓ Semua file ditemukan${NC}"
echo ""

# Upload file ke server
echo -e "${YELLOW}[2/8] Uploading files to server...${NC}"
for file in "${FILES[@]}"; do
    filename=$(basename "$file")
    echo "  Uploading: $file"
    scp "$file" "${SERVER_USER}@${SERVER_IP}:/tmp/$filename" || {
        echo -e "${RED}ERROR: Gagal upload $file${NC}"
        exit 1
    }
done
echo -e "${GREEN}✓ Semua file berhasil di-upload${NC}"
echo ""

# Backup di server
echo -e "${YELLOW}[3/8] Creating backup on server...${NC}"
ssh "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
cd /var/www/html/hris-seven-payroll
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -proot123 hris_seven > ~/backup_hris_seven_${BACKUP_DATE}.sql
cp .env ~/backup_env_${BACKUP_DATE}.txt
echo "✓ Backup database dan .env selesai"
ENDSSH
echo -e "${GREEN}✓ Backup selesai${NC}"
echo ""

# Copy file ke folder aplikasi
echo -e "${YELLOW}[4/8] Copying files to application folder...${NC}"
ssh "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
cd /var/www/html/hris-seven-payroll

# Pastikan folder Services ada
mkdir -p app/Services

# Copy file dari /tmp/
cp /tmp/InstruksiKerjaLemburController.php app/Http/Controllers/
cp /tmp/ClosingController.php app/Http/Controllers/
cp /tmp/LemburCalculationService.php app/Services/
cp /tmp/LemburDetail.php app/Models/
cp /tmp/index.blade.php resources/views/instruksi-kerja-lembur/
cp /tmp/web.php routes/
cp /tmp/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php database/migrations/

# Set ownership dan permissions
chown -R www-data:www-data /var/www/html/hris-seven-payroll
chmod -R 755 /var/www/html/hris-seven-payroll
chmod -R 775 /var/www/html/hris-seven-payroll/storage
chmod -R 775 /var/www/html/hris-seven-payroll/bootstrap/cache

echo "✓ File berhasil di-copy dan permissions di-set"
ENDSSH
echo -e "${GREEN}✓ File berhasil di-copy${NC}"
echo ""

# Update autoload
echo -e "${YELLOW}[5/8] Updating composer autoload...${NC}"
ssh "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
cd /var/www/html/hris-seven-payroll
sudo -u www-data composer dump-autoload --optimize
echo "✓ Autoload berhasil di-update"
ENDSSH
echo -e "${GREEN}✓ Autoload berhasil di-update${NC}"
echo ""

# Run migration
echo -e "${YELLOW}[6/8] Running migration...${NC}"
ssh "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan migrate --force
echo "✓ Migration berhasil dijalankan"
ENDSSH
echo -e "${GREEN}✓ Migration berhasil dijalankan${NC}"
echo ""

# Clear cache
echo -e "${YELLOW}[7/8] Clearing cache...${NC}"
ssh "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
echo "✓ Cache berhasil di-clear dan di-rebuild"
ENDSSH
echo -e "${GREEN}✓ Cache berhasil di-clear${NC}"
echo ""

# Cleanup
echo -e "${YELLOW}[8/8] Cleaning up temporary files...${NC}"
ssh "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
rm -f /tmp/InstruksiKerjaLemburController.php
rm -f /tmp/ClosingController.php
rm -f /tmp/LemburCalculationService.php
rm -f /tmp/LemburDetail.php
rm -f /tmp/index.blade.php
rm -f /tmp/web.php
rm -f /tmp/2025_01_17_100000_add_dec_lembur_external_to_t_lembur_detail_table.php
echo "✓ Temporary files berhasil dihapus"
ENDSSH
echo -e "${GREEN}✓ Cleanup selesai${NC}"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  UPDATE SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Silakan test aplikasi di:${NC}"
echo -e "  http://${SERVER_IP}/hris-seven-payroll"
echo ""
echo -e "${YELLOW}Check log jika ada error:${NC}"
echo -e "  ssh ${SERVER_USER}@${SERVER_IP}"
echo -e "  tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log"
echo ""





