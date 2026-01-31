#!/bin/bash

# Script untuk update perbaikan Group Hierarki ke Server Ubuntu
# Usage: ./update-hirarki.sh

set -e  # Exit on error

# Konfigurasi
SERVER_IP="192.168.10.40"
SERVER_USER="root"
SERVER_PATH="/var/www/html/hris-seven-payroll"

# Colors untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Update Perbaikan Group Hierarki${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check apakah file-file yang diperlukan ada
echo -e "${YELLOW}[1/5] Checking files...${NC}"
FILES=(
    "app/Http/Controllers/HirarkiController.php"
    "resources/views/master/hirarki/index.blade.php"
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
echo -e "${YELLOW}[2/5] Uploading files to server...${NC}"
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

# Copy file ke folder aplikasi dan set permissions
echo -e "${YELLOW}[3/5] Copying files and setting permissions...${NC}"
ssh "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
cd /var/www/html/hris-seven-payroll

# Backup file lama
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
cp app/Http/Controllers/HirarkiController.php app/Http/Controllers/HirarkiController.php.backup.${BACKUP_DATE}
cp resources/views/master/hirarki/index.blade.php resources/views/master/hirarki/index.blade.php.backup.${BACKUP_DATE}
echo "✓ Backup file lama selesai"

# Copy file baru
cp /tmp/HirarkiController.php app/Http/Controllers/
cp /tmp/index.blade.php resources/views/master/hirarki/

# Set ownership dan permissions
chown www-data:www-data app/Http/Controllers/HirarkiController.php
chown www-data:www-data resources/views/master/hirarki/index.blade.php
chmod 644 app/Http/Controllers/HirarkiController.php
chmod 644 resources/views/master/hirarki/index.blade.php

echo "✓ File berhasil di-copy dan permissions di-set"
ENDSSH
echo -e "${GREEN}✓ File berhasil di-copy${NC}"
echo ""

# Clear cache Laravel
echo -e "${YELLOW}[4/5] Clearing cache...${NC}"
ssh "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
cd /var/www/html/hris-seven-payroll
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
echo "✓ Cache berhasil di-clear dan di-rebuild"
ENDSSH
echo -e "${GREEN}✓ Cache berhasil di-clear${NC}"
echo ""

# Cleanup temporary files
echo -e "${YELLOW}[5/5] Cleaning up temporary files...${NC}"
ssh "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
rm -f /tmp/HirarkiController.php
rm -f /tmp/index.blade.php
echo "✓ Temporary files berhasil dihapus"
ENDSSH
echo -e "${GREEN}✓ Cleanup selesai${NC}"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  UPDATE SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Silakan test aplikasi di:${NC}"
echo -e "  http://hr.abncorp.lan/hirarki"
echo ""
echo -e "${YELLOW}Check log jika ada error:${NC}"
echo -e "  ssh ${SERVER_USER}@${SERVER_IP}"
echo -e "  tail -f ${SERVER_PATH}/storage/logs/laravel.log"
echo ""


