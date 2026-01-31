#!/bin/bash

# Script untuk debug Internal Server Error
# Check log, permissions, dan konfigurasi

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Debug Internal Server Error         ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo -e "${YELLOW}[1/6] Checking Apache Error Log...${NC}"
echo -e "${YELLOW}Last 20 lines of Apache error log:${NC}"
tail -20 /var/log/apache2/error.log
echo ""

echo -e "${YELLOW}[2/6] Checking Laravel Log...${NC}"
if [ -f "$PROJECT_PATH/storage/logs/laravel.log" ]; then
    echo -e "${YELLOW}Last 30 lines of Laravel log:${NC}"
    tail -30 "$PROJECT_PATH/storage/logs/laravel.log"
else
    echo -e "${RED}Laravel log file tidak ditemukan${NC}"
fi
echo ""

echo -e "${YELLOW}[3/6] Checking Permissions...${NC}"
ls -la "$PROJECT_PATH/storage" | head -5
ls -la "$PROJECT_PATH/bootstrap/cache" | head -5
echo ""

echo -e "${YELLOW}[4/6] Checking .env file...${NC}"
if [ -f "$PROJECT_PATH/.env" ]; then
    echo "APP_URL: $(grep '^APP_URL=' $PROJECT_PATH/.env || echo 'NOT SET')"
    echo "APP_DEBUG: $(grep '^APP_DEBUG=' $PROJECT_PATH/.env || echo 'NOT SET')"
    echo "DB_CONNECTION: $(grep '^DB_CONNECTION=' $PROJECT_PATH/.env || echo 'NOT SET')"
else
    echo -e "${RED}.env file tidak ditemukan!${NC}"
fi
echo ""

echo -e "${YELLOW}[5/6] Checking PHP Syntax...${NC}"
cd "$PROJECT_PATH"
php -l public/index.php
php -l routes/web.php
echo ""

echo -e "${YELLOW}[6/6] Testing Laravel Application...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan --version 2>&1 || echo -e "${RED}Error running artisan${NC}"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Debug Complete                       ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Check output di atas untuk error messages${NC}"
echo ""
