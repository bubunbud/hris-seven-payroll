#!/bin/bash

# Script untuk fix storage/logs dan pastikan laravel.log bisa dibuat

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Fix Storage/Logs Directory          ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

PROJECT_PATH="/var/www/html/hris-seven-payroll"
STORAGE_PATH="$PROJECT_PATH/storage"
LOGS_PATH="$STORAGE_PATH/logs"

echo -e "${YELLOW}[1/6] Checking storage directory structure...${NC}"
if [ ! -d "$STORAGE_PATH" ]; then
    echo -e "${RED}ERROR: storage directory tidak ditemukan!${NC}"
    exit 1
fi

# Check struktur folder storage
echo "Storage structure:"
ls -la "$STORAGE_PATH" | head -10
echo ""

echo -e "${YELLOW}[2/6] Creating logs directory if not exists...${NC}"
mkdir -p "$LOGS_PATH"
mkdir -p "$STORAGE_PATH/framework/cache"
mkdir -p "$STORAGE_PATH/framework/sessions"
mkdir -p "$STORAGE_PATH/framework/views"
mkdir -p "$STORAGE_PATH/app/public"
echo -e "${GREEN}✓ Directories created${NC}"
echo ""

echo -e "${YELLOW}[3/6] Fixing permissions for storage...${NC}"
chown -R www-data:www-data "$STORAGE_PATH"
chmod -R 775 "$STORAGE_PATH"
# Pastikan logs folder writable
chmod 775 "$LOGS_PATH"
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

echo -e "${YELLOW}[4/6] Creating laravel.log file if not exists...${NC}"
if [ ! -f "$LOGS_PATH/laravel.log" ]; then
    touch "$LOGS_PATH/laravel.log"
    chown www-data:www-data "$LOGS_PATH/laravel.log"
    chmod 664 "$LOGS_PATH/laravel.log"
    echo -e "${GREEN}✓ laravel.log created${NC}"
else
    echo -e "${GREEN}✓ laravel.log sudah ada${NC}"
fi
echo ""

echo -e "${YELLOW}[5/6] Verifying permissions...${NC}"
echo "Storage permissions:"
ls -ld "$STORAGE_PATH"
ls -ld "$LOGS_PATH"
if [ -f "$LOGS_PATH/laravel.log" ]; then
    ls -l "$LOGS_PATH/laravel.log"
else
    echo -e "${RED}✗ laravel.log masih tidak ada setelah dibuat!${NC}"
fi
echo ""

echo -e "${YELLOW}[6/6] Testing write permission...${NC}"
# Test write permission
if sudo -u www-data touch "$LOGS_PATH/test-write.log" 2>/dev/null; then
    rm -f "$LOGS_PATH/test-write.log"
    echo -e "${GREEN}✓ Write permission OK${NC}"
else
    echo -e "${RED}✗ Write permission ERROR!${NC}"
    echo "Fixing permissions again..."
    chown -R www-data:www-data "$STORAGE_PATH"
    chmod -R 775 "$STORAGE_PATH"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX COMPLETE! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Storage structure:${NC}"
tree -L 2 "$STORAGE_PATH" 2>/dev/null || find "$STORAGE_PATH" -maxdepth 2 -type d | head -10
echo ""
echo -e "${YELLOW}Check laravel.log:${NC}"
if [ -f "$LOGS_PATH/laravel.log" ]; then
    echo -e "${GREEN}  File: $LOGS_PATH/laravel.log${NC}"
    echo -e "${GREEN}  Size: $(du -h $LOGS_PATH/laravel.log | cut -f1)${NC}"
    echo -e "${GREEN}  Last 10 lines:${NC}"
    tail -10 "$LOGS_PATH/laravel.log" || echo "  (File kosong)"
else
    echo -e "${RED}  File masih tidak ada!${NC}"
fi
echo ""





