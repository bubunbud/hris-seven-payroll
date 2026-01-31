#!/bin/bash

# Script untuk fix konfigurasi setelah setup multiple apps
# Fix .env dan clear cache

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Fix Subfolder Configuration        ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"
ENV_FILE="$PROJECT_PATH/.env"

echo -e "${YELLOW}[1/5] Checking .env file...${NC}"
if [ ! -f "$ENV_FILE" ]; then
    echo -e "${RED}ERROR: .env file tidak ditemukan di $ENV_FILE${NC}"
    exit 1
fi
echo -e "${GREEN}✓ .env file ditemukan${NC}"
echo ""

echo -e "${YELLOW}[2/5] Updating APP_URL and ASSET_URL in .env...${NC}"
# Backup .env
cp "$ENV_FILE" "$ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"

# Update APP_URL dan ASSET_URL
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' "$ENV_FILE"
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' "$ENV_FILE"

# Jika belum ada, tambahkan
if ! grep -q "^APP_URL=" "$ENV_FILE"; then
    echo "APP_URL=http://192.168.10.40/hris-seven-payroll" >> "$ENV_FILE"
fi
if ! grep -q "^ASSET_URL=" "$ENV_FILE"; then
    echo "ASSET_URL=http://192.168.10.40/hris-seven-payroll" >> "$ENV_FILE"
fi

echo -e "${GREEN}✓ APP_URL dan ASSET_URL updated${NC}"
echo "  APP_URL=http://192.168.10.40/hris-seven-payroll"
echo "  ASSET_URL=http://192.168.10.40/hris-seven-payroll"
echo ""

echo -e "${YELLOW}[3/5] Clearing all Laravel cache...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

echo -e "${YELLOW}[4/5] Rebuilding cache...${NC}"
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
echo -e "${GREEN}✓ Cache rebuilt${NC}"
echo ""

echo -e "${YELLOW}[5/5] Verifying routes...${NC}"
# Check apakah ada route untuk /
ROUTE_CHECK=$(sudo -u www-data php artisan route:list | grep -E "GET.*/" || echo "")
if [ -z "$ROUTE_CHECK" ]; then
    echo -e "${YELLOW}  Warning: Route untuk / tidak ditemukan${NC}"
    echo -e "${YELLOW}  Pastikan ada route di routes/web.php${NC}"
else
    echo -e "${GREEN}✓ Routes OK${NC}"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Silakan test aplikasi di:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo ""
echo -e "${YELLOW}Jika masih ada error, check:${NC}"
echo -e "  1. Route di routes/web.php"
echo -e "  2. Log: tail -f $PROJECT_PATH/storage/logs/laravel.log"
echo ""





