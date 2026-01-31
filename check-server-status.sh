#!/bin/bash

# Check Server Status - Pastikan semua benar di server Ubuntu

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Check Server Status${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo -e "${YELLOW}[1/6] Checking PHP syntax...${NC}"
if php -l "$PROJECT_PATH/app/Http/Controllers/InstruksiKerjaLemburController.php" > /dev/null 2>&1; then
    echo -e "${GREEN}✓ PHP syntax OK${NC}"
else
    echo -e "${RED}❌ PHP syntax error!${NC}"
    php -l "$PROJECT_PATH/app/Http/Controllers/InstruksiKerjaLemburController.php"
fi
echo ""

echo -e "${YELLOW}[2/6] Checking validate method (line 830-836)...${NC}"
echo -e "${BLUE}Content:${NC}"
sed -n '828,836p' "$PROJECT_PATH/app/Http/Controllers/InstruksiKerjaLemburController.php"
echo ""

echo -e "${YELLOW}[3/6] Checking file paths in Laravel log...${NC}"
if [ -f "$PROJECT_PATH/storage/logs/laravel.log" ]; then
    # Check apakah log berisi path Windows (berarti dari localhost)
    if grep -q "C:\\\\xampp" "$PROJECT_PATH/storage/logs/laravel.log"; then
        echo -e "${YELLOW}⚠ Log berisi path Windows (dari localhost)${NC}"
        echo -e "${YELLOW}⚠ Log ini mungkin dari localhost, bukan server${NC}"
    fi
    
    # Check error terakhir yang relevan (bukan tinker/artisan)
    echo -e "${BLUE}Latest relevant errors (excluding tinker/artisan):${NC}"
    grep -v "tinker\|artisan\|Command.*is not defined" "$PROJECT_PATH/storage/logs/laravel.log" | tail -20 || echo "  (No relevant errors)"
else
    echo -e "${YELLOW}⚠ Laravel log tidak ditemukan${NC}"
fi
echo ""

echo -e "${YELLOW}[4/6] Checking .env configuration...${NC}"
if [ -f "$PROJECT_PATH/.env" ]; then
    echo -e "${BLUE}APP_URL: $(grep "^APP_URL=" "$PROJECT_PATH/.env" | cut -d'=' -f2)${NC}"
    echo -e "${BLUE}APP_DEBUG: $(grep "^APP_DEBUG=" "$PROJECT_PATH/.env" | cut -d'=' -f2)${NC}"
    echo -e "${BLUE}APP_ENV: $(grep "^APP_ENV=" "$PROJECT_PATH/.env" | cut -d'=' -f2)${NC}"
else
    echo -e "${RED}❌ .env file tidak ditemukan!${NC}"
fi
echo ""

echo -e "${YELLOW}[5/6] Testing application access...${NC}"
echo -e "${BLUE}Test 1: curl -I http://192.168.10.40/hris-seven-payroll/${NC}"
RESPONSE1=$(curl -s -I http://192.168.10.40/hris-seven-payroll/ 2>&1 | head -1)
echo "$RESPONSE1"

if echo "$RESPONSE1" | grep -q "200 OK\|302"; then
    echo -e "${GREEN}✓ Aplikasi bisa diakses${NC}"
elif echo "$RESPONSE1" | grep -q "500"; then
    echo -e "${RED}❌ Internal Server Error (500)${NC}"
    echo -e "${YELLOW}⚠ Check Laravel log untuk detail error${NC}"
elif echo "$RESPONSE1" | grep -q "301"; then
    echo -e "${YELLOW}⚠ Redirect 301 (normal, browser akan follow)${NC}"
else
    echo -e "${RED}❌ Error: $RESPONSE1${NC}"
fi
echo ""

echo -e "${YELLOW}[6/6] Checking recent Laravel errors (web requests only)...${NC}"
if [ -f "$PROJECT_PATH/storage/logs/laravel.log" ]; then
    # Filter hanya error dari web requests (bukan artisan/tinker)
    echo -e "${BLUE}Latest web errors (last 10 lines, excluding artisan/tinker):${NC}"
    grep -v "artisan\|tinker\|Command.*is not defined\|php artisan" "$PROJECT_PATH/storage/logs/laravel.log" | grep -i "error\|exception" | tail -10 || echo "  (No web errors found)"
    
    echo ""
    echo -e "${BLUE}Full latest log (last 5 lines):${NC}"
    tail -5 "$PROJECT_PATH/storage/logs/laravel.log"
else
    echo -e "${YELLOW}⚠ Laravel log tidak ditemukan${NC}"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  CHECK SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📝 Summary:${NC}"
echo -e "  - PHP syntax: $(php -l "$PROJECT_PATH/app/Http/Controllers/InstruksiKerjaLemburController.php" > /dev/null 2>&1 && echo 'OK' || echo 'ERROR')"
echo -e "  - Application access: $(echo "$RESPONSE1" | grep -q "200\|302" && echo 'OK' || echo 'ERROR')"
echo ""
echo -e "${YELLOW}🌐 Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll/${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll/login${NC}"
echo ""




