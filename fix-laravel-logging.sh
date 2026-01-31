#!/bin/bash

# Fix Laravel Logging - Pastikan log bisa ditulis

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fix Laravel Logging${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"
LOG_FILE="$PROJECT_PATH/storage/logs/laravel.log"
ENV_FILE="$PROJECT_PATH/.env"

echo -e "${YELLOW}[1/6] Checking storage/logs directory...${NC}"
# Ensure directory exists
mkdir -p "$PROJECT_PATH/storage/logs"
mkdir -p "$PROJECT_PATH/storage/framework/cache"
mkdir -p "$PROJECT_PATH/storage/framework/sessions"
mkdir -p "$PROJECT_PATH/storage/framework/views"
echo -e "${GREEN}✓ Directories created${NC}"
echo ""

echo -e "${YELLOW}[2/6] Fixing permissions...${NC}"
# Set ownership
chown -R www-data:www-data "$PROJECT_PATH/storage"
chown -R www-data:www-data "$PROJECT_PATH/bootstrap/cache"

# Set permissions
chmod -R 775 "$PROJECT_PATH/storage"
chmod -R 775 "$PROJECT_PATH/bootstrap/cache"

# Ensure log file exists and is writable
touch "$LOG_FILE"
chown www-data:www-data "$LOG_FILE"
chmod 664 "$LOG_FILE"
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

echo -e "${YELLOW}[3/6] Checking .env configuration...${NC}"
if [ -f "$ENV_FILE" ]; then
    # Backup
    cp "$ENV_FILE" "$ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"
    
    # Ensure APP_DEBUG=true
    if grep -q "^APP_DEBUG=" "$ENV_FILE"; then
        sed -i 's|^APP_DEBUG=.*|APP_DEBUG=true|g' "$ENV_FILE"
    else
        echo "APP_DEBUG=true" >> "$ENV_FILE"
    fi
    
    # Ensure APP_ENV=local
    if grep -q "^APP_ENV=" "$ENV_FILE"; then
        sed -i 's|^APP_ENV=.*|APP_ENV=local|g' "$ENV_FILE"
    else
        echo "APP_ENV=local" >> "$ENV_FILE"
    fi
    
    # Ensure LOG_CHANNEL=stack
    if ! grep -q "^LOG_CHANNEL=" "$ENV_FILE"; then
        echo "LOG_CHANNEL=stack" >> "$ENV_FILE"
    fi
    
    echo -e "${GREEN}✓ .env updated${NC}"
    echo -e "${BLUE}  APP_DEBUG: $(grep "^APP_DEBUG=" "$ENV_FILE" | cut -d'=' -f2)${NC}"
    echo -e "${BLUE}  APP_ENV: $(grep "^APP_ENV=" "$ENV_FILE" | cut -d'=' -f2)${NC}"
else
    echo -e "${RED}❌ .env file tidak ditemukan!${NC}"
    exit 1
fi
echo ""

echo -e "${YELLOW}[4/6] Clearing cache...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:clear 2>/dev/null || true
rm -f bootstrap/cache/routes*.php 2>/dev/null || true
rm -f bootstrap/cache/config.php 2>/dev/null || true
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

echo -e "${YELLOW}[5/6] Rebuilding config cache...${NC}"
sudo -u www-data php artisan config:cache 2>/dev/null || echo "  (config:cache skipped)"
echo -e "${GREEN}✓ Config cache rebuilt${NC}"
echo ""

echo -e "${YELLOW}[6/6] Testing logging...${NC}"
# Test write permission
if sudo -u www-data touch "$LOG_FILE" 2>/dev/null; then
    echo -e "${GREEN}✓ Log file is writable${NC}"
else
    echo -e "${RED}❌ Log file is NOT writable!${NC}"
    echo -e "${YELLOW}⚠ Fixing permissions...${NC}"
    chown www-data:www-data "$LOG_FILE"
    chmod 664 "$LOG_FILE"
    echo -e "${GREEN}✓ Permissions fixed${NC}"
fi

# Test write dengan echo
echo "$(date '+%Y-%m-%d %H:%M:%S') - Test log entry" | sudo -u www-data tee -a "$LOG_FILE" > /dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Log writing test successful${NC}"
    # Remove test entry
    sudo -u www-data sed -i '/Test log entry/d' "$LOG_FILE" 2>/dev/null || true
else
    echo -e "${RED}❌ Log writing test failed!${NC}"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📋 Summary:${NC}"
echo -e "  ✓ Storage directories created"
echo -e "  ✓ Permissions fixed (www-data:www-data, 775)"
echo -e "  ✓ .env updated (APP_DEBUG=true)"
echo -e "  ✓ Cache cleared and rebuilt"
echo -e "  ✓ Log file is writable"
echo ""
echo -e "${YELLOW}🌐 Next steps:${NC}"
echo -e "  1. Akses aplikasi dari browser: ${GREEN}http://192.168.10.40/hris-seven-payroll/${NC}"
echo -e "  2. Check log: ${BLUE}tail -f storage/logs/laravel.log${NC}"
echo ""
echo -e "${YELLOW}💡 Jika masih tidak ada log:${NC}"
echo -e "  - Pastikan APP_DEBUG=true di .env"
echo -e "  - Pastikan aplikasi benar-benar diakses dari browser (bukan curl)"
echo -e "  - Check Apache error log: ${BLUE}tail -f /var/log/apache2/error.log${NC}"
echo ""




