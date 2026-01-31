#!/bin/bash

# Script untuk memperbaiki error 404 Master Karyawan di Server Ubuntu
# Usage: sudo bash fix-404-karyawan.sh

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Project path
PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}Fix 404 Error - Master Karyawan${NC}"
echo -e "${YELLOW}========================================${NC}\n"

# Check if project exists
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}Error: Project tidak ditemukan di $PROJECT_PATH${NC}"
    exit 1
fi

cd "$PROJECT_PATH"

# Step 1: Check if file exists
echo -e "${YELLOW}[1/7] Checking file...${NC}"
FILE_PATH="resources/views/master/karyawan/index.blade.php"
if [ ! -f "$FILE_PATH" ]; then
    echo -e "${RED}Error: File $FILE_PATH tidak ditemukan!${NC}"
    echo -e "${YELLOW}Silakan upload file terlebih dahulu.${NC}"
    exit 1
fi

# Check if basePath exists in file
if ! grep -q "basePath.*url" "$FILE_PATH"; then
    echo -e "${RED}Error: basePath tidak ditemukan di file!${NC}"
    echo -e "${YELLOW}Pastikan file sudah di-update dengan versi terbaru.${NC}"
    exit 1
fi

echo -e "${GREEN}File ditemukan dan basePath sudah ada${NC}\n"

# Step 2: Set permissions
echo -e "${YELLOW}[2/7] Setting permissions...${NC}"
sudo chown www-data:www-data "$FILE_PATH"
sudo chmod 644 "$FILE_PATH"
echo -e "${GREEN}Permissions updated${NC}\n"

# Step 3: Clear Laravel cache
echo -e "${YELLOW}[3/7] Clearing Laravel cache...${NC}"
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear
echo -e "${GREEN}Cache cleared${NC}\n"

# Step 4: Re-cache
echo -e "${YELLOW}[4/7] Re-caching Laravel...${NC}"
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
echo -e "${GREEN}Cache regenerated${NC}\n"

# Step 5: Check APP_URL in .env
echo -e "${YELLOW}[5/7] Checking APP_URL in .env...${NC}"
if [ -f ".env" ]; then
    APP_URL=$(grep "^APP_URL=" .env | cut -d '=' -f2)
    echo -e "Current APP_URL: ${YELLOW}$APP_URL${NC}"
    
    if [[ ! "$APP_URL" == *"hris-seven-payroll"* ]]; then
        echo -e "${YELLOW}Warning: APP_URL mungkin belum sesuai untuk subfolder${NC}"
        echo -e "${YELLOW}Disarankan: APP_URL=http://192.168.10.40/hris-seven-payroll${NC}"
        read -p "Update APP_URL sekarang? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|' .env
            echo -e "${GREEN}APP_URL updated${NC}"
            sudo -u www-data php artisan config:clear
            sudo -u www-data php artisan config:cache
        fi
    else
        echo -e "${GREEN}APP_URL sudah sesuai${NC}"
    fi
else
    echo -e "${RED}Warning: .env file tidak ditemukan${NC}"
fi
echo ""

# Step 6: Restart Apache
echo -e "${YELLOW}[6/7] Restarting Apache...${NC}"
sudo systemctl restart apache2
echo -e "${GREEN}Apache restarted${NC}\n"

# Step 7: Verify route
echo -e "${YELLOW}[7/7] Verifying routes...${NC}"
if sudo -u www-data php artisan route:list | grep -q "karyawan.show"; then
    echo -e "${GREEN}Route karyawan.show ditemukan${NC}"
else
    echo -e "${RED}Warning: Route karyawan.show tidak ditemukan${NC}"
fi
echo ""

# Final message
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Perbaikan selesai!${NC}"
echo -e "${GREEN}========================================${NC}\n"
echo -e "${YELLOW}Langkah selanjutnya:${NC}"
echo -e "1. Clear browser cache (Ctrl + Shift + Delete)"
echo -e "2. Atau gunakan Incognito/Private mode"
echo -e "3. Akses: ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo -e "4. Test Master Karyawan dengan memilih data karyawan"
echo -e "5. Check browser console (F12) jika masih ada error\n"
echo -e "${YELLOW}Untuk melihat log:${NC}"
echo -e "tail -f $PROJECT_PATH/storage/logs/laravel.log\n"













