#!/bin/bash

# Fix Laravel Syntax Error - InstruksiKerjaLemburController
# Error: unexpected '=' on line 1 saat validate() dipanggil

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fix Laravel Syntax Error${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"
CONTROLLER_FILE="$PROJECT_PATH/app/Http/Controllers/InstruksiKerjaLemburController.php"

echo -e "${YELLOW}[1/5] Checking controller file...${NC}"
if [ ! -f "$CONTROLLER_FILE" ]; then
    echo -e "${RED}❌ Controller file tidak ditemukan: $CONTROLLER_FILE${NC}"
    exit 1
fi

# Check syntax dengan PHP
echo -e "${YELLOW}[2/5] Checking PHP syntax...${NC}"
if php -l "$CONTROLLER_FILE" > /dev/null 2>&1; then
    echo -e "${GREEN}✓ PHP syntax OK${NC}"
else
    echo -e "${RED}❌ PHP syntax error!${NC}"
    php -l "$CONTROLLER_FILE"
    exit 1
fi
echo ""

echo -e "${YELLOW}[3/5] Checking line 830-836 (validate method)...${NC}"
# Check apakah ada masalah di sekitar line 830-836
if sed -n '830,836p' "$CONTROLLER_FILE" | grep -q "validate"; then
    echo -e "${BLUE}Content around line 830-836:${NC}"
    sed -n '825,840p' "$CONTROLLER_FILE"
    echo ""
    
    # Check apakah ada karakter aneh atau encoding issue
    if file "$CONTROLLER_FILE" | grep -q "CRLF"; then
        echo -e "${YELLOW}⚠ File menggunakan CRLF line endings (Windows)${NC}"
        echo -e "${YELLOW}⚠ Converting to LF (Unix)...${NC}"
        dos2unix "$CONTROLLER_FILE" 2>/dev/null || sed -i 's/\r$//' "$CONTROLLER_FILE"
        echo -e "${GREEN}✓ Line endings fixed${NC}"
    fi
else
    echo -e "${RED}❌ validate() tidak ditemukan di line 830-836!${NC}"
    echo -e "${BLUE}Checking method calculateLemburNominal...${NC}"
    grep -n "calculateLemburNominal" "$CONTROLLER_FILE" || echo "Method tidak ditemukan!"
fi
echo ""

echo -e "${YELLOW}[4/5] Checking for common syntax errors...${NC}"
# Check untuk common errors
if grep -n "validate\s*=" "$CONTROLLER_FILE"; then
    echo -e "${RED}❌ Found: validate = (salah!)${NC}"
    echo -e "${YELLOW}⚠ Perlu di-fix manual${NC}"
else
    echo -e "${GREEN}✓ Tidak ada 'validate =' yang salah${NC}"
fi

# Check untuk missing semicolon atau bracket
if ! php -l "$CONTROLLER_FILE" > /dev/null 2>&1; then
    echo -e "${RED}❌ PHP syntax error ditemukan!${NC}"
    php -l "$CONTROLLER_FILE"
else
    echo -e "${GREEN}✓ Tidak ada syntax error${NC}"
fi
echo ""

echo -e "${YELLOW}[5/5] Clearing cache...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:clear 2>/dev/null || true
sudo -u www-data php artisan route:clear 2>/dev/null || true
rm -f bootstrap/cache/routes*.php 2>/dev/null || true
rm -f bootstrap/cache/config.php 2>/dev/null || true
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  CHECK SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📝 Next steps:${NC}"
echo -e "  1. Jika ada syntax error, fix manual di file controller"
echo -e "  2. Pastikan file di server sama dengan localhost"
echo -e "  3. Check Laravel log: ${BLUE}tail -50 storage/logs/laravel.log${NC}"
echo -e "  4. Test aplikasi: ${GREEN}http://192.168.10.40/hris-seven-payroll/login${NC}"
echo ""




