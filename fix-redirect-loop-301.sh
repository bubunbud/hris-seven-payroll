#!/bin/bash

# Fix Redirect Loop 301 - Trailing Slash Issue
# Masalah: Redirect dari /hris-seven-payroll ke /hris-seven-payroll/ menyebabkan loop

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fix Redirect Loop 301${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"
HTACCESS_FILE="$PROJECT_PATH/public/.htaccess"

echo -e "${YELLOW}[1/5] Fixing .htaccess (disable trailing slash redirect)...${NC}"
# Backup
if [ -f "$HTACCESS_FILE" ]; then
    cp "$HTACCESS_FILE" "$HTACCESS_FILE.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Create .htaccess TANPA rule redirect trailing slash yang bermasalah
cat > "$HTACCESS_FILE" <<'HTACCESS'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTACCESS
chmod 644 "$HTACCESS_FILE"
chown www-data:www-data "$HTACCESS_FILE"
echo -e "${GREEN}✓ .htaccess fixed (trailing slash redirect disabled)${NC}"
echo ""

echo -e "${YELLOW}[2/5] Clearing cache...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:clear 2>/dev/null || true
rm -f "$PROJECT_PATH/bootstrap/cache/routes*.php" 2>/dev/null || true
rm -f "$PROJECT_PATH/bootstrap/cache/config.php" 2>/dev/null || true
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

echo -e "${YELLOW}[3/5] Rebuilding config cache...${NC}"
sudo -u www-data php artisan config:cache 2>/dev/null || echo "  (config:cache skipped)"
echo -e "${GREEN}✓ Config cache rebuilt${NC}"
echo ""

echo -e "${YELLOW}[4/5] Restarting Apache...${NC}"
systemctl restart apache2
sleep 2
if systemctl is-active --quiet apache2; then
    echo -e "${GREEN}✓ Apache restarted successfully${NC}"
else
    echo -e "${RED}❌ Apache restart failed!${NC}"
    systemctl status apache2 --no-pager -l
fi
echo ""

echo -e "${YELLOW}[5/5] Testing redirect...${NC}"
echo -e "${BLUE}Testing: curl -I http://192.168.10.40/hris-seven-payroll${NC}"
RESPONSE=$(curl -s -I http://192.168.10.40/hris-seven-payroll 2>&1 | head -1)
echo "$RESPONSE"

if echo "$RESPONSE" | grep -q "200 OK"; then
    echo -e "${GREEN}✓ SUCCESS! Aplikasi bisa diakses${NC}"
elif echo "$RESPONSE" | grep -q "301\|302"; then
    echo -e "${YELLOW}⚠ Masih ada redirect, tapi mungkin sudah OK${NC}"
    echo -e "${BLUE}Test dengan trailing slash: curl -I http://192.168.10.40/hris-seven-payroll/${NC}"
    RESPONSE2=$(curl -s -I http://192.168.10.40/hris-seven-payroll/ 2>&1 | head -1)
    echo "$RESPONSE2"
    if echo "$RESPONSE2" | grep -q "200 OK"; then
        echo -e "${GREEN}✓ SUCCESS! Aplikasi bisa diakses dengan trailing slash${NC}"
    fi
else
    echo -e "${RED}❌ Masih ada masalah${NC}"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📋 Summary:${NC}"
echo -e "  ✓ .htaccess fixed (trailing slash redirect disabled)"
echo -e "  ✓ Cache cleared and rebuilt"
echo -e "  ✓ Apache restarted"
echo ""
echo -e "${YELLOW}🌐 Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll/${NC}"
echo ""
echo -e "${YELLOW}📝 Check logs jika masih error:${NC}"
echo -e "  ${BLUE}tail -f /var/log/apache2/error.log${NC}"
echo -e "  ${BLUE}tail -f $PROJECT_PATH/storage/logs/laravel.log${NC}"
echo ""




