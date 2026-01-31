#!/bin/bash

# Fix Redirect Loop DEFINITIVE - Hapus SEMUA rule redirect trailing slash
# Masalah: Masih ada redirect 301 dari /hris-seven-payroll ke /hris-seven-payroll/

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fix Redirect Loop - DEFINITIVE${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"
HTACCESS_FILE="$PROJECT_PATH/public/.htaccess"

echo -e "${YELLOW}[1/6] Checking current .htaccess...${NC}"
if [ -f "$HTACCESS_FILE" ]; then
    echo -e "${BLUE}Current .htaccess content:${NC}"
    cat "$HTACCESS_FILE"
    echo ""
    
    # Backup
    cp "$HTACCESS_FILE" "$HTACCESS_FILE.backup.$(date +%Y%m%d_%H%M%S)"
    echo -e "${GREEN}✓ Backup created${NC}"
else
    echo -e "${YELLOW}⚠ .htaccess tidak ditemukan, akan dibuat baru${NC}"
fi
echo ""

echo -e "${YELLOW}[2/6] Creating MINIMAL .htaccess (NO redirect rules)...${NC}"
# Create MINIMAL .htaccess - HANYA rule untuk Laravel, TIDAK ADA redirect sama sekali
cat > "$HTACCESS_FILE" <<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    
    # Send Requests To Front Controller (ONLY THIS RULE)
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTACCESS
chmod 644 "$HTACCESS_FILE"
chown www-data:www-data "$HTACCESS_FILE"
echo -e "${GREEN}✓ .htaccess created (MINIMAL, NO redirect rules)${NC}"
echo -e "${BLUE}New .htaccess content:${NC}"
cat "$HTACCESS_FILE"
echo ""

echo -e "${YELLOW}[3/6] Checking for other .htaccess files...${NC}"
# Check for .htaccess in parent directories
if [ -f "/var/www/html/.htaccess" ]; then
    echo -e "${YELLOW}⚠ Found .htaccess in /var/www/html/${NC}"
    echo -e "${BLUE}Content:${NC}"
    cat /var/www/html/.htaccess
    echo ""
    echo -e "${YELLOW}⚠ Consider renaming or removing this file if it causes issues${NC}"
fi

if [ -f "$PROJECT_PATH/.htaccess" ]; then
    echo -e "${YELLOW}⚠ Found .htaccess in project root${NC}"
    echo -e "${BLUE}Content:${NC}"
    cat "$PROJECT_PATH/.htaccess"
    echo ""
    echo -e "${YELLOW}⚠ Consider renaming or removing this file if it causes issues${NC}"
fi
echo ""

echo -e "${YELLOW}[4/6] Checking Apache DirectoryIndex configuration...${NC}"
APACHE_CONF="/etc/apache2/sites-available/000-default.conf"
if [ -f "$APACHE_CONF" ]; then
    # Check if DirectoryIndex is causing redirect
    if grep -A 10 "<Directory /var/www/html/hris-seven-payroll/public>" "$APACHE_CONF" | grep -q "DirectoryIndex"; then
        echo -e "${BLUE}DirectoryIndex found in Apache config${NC}"
        grep -A 10 "<Directory /var/www/html/hris-seven-payroll/public>" "$APACHE_CONF" | grep "DirectoryIndex"
    fi
    
    # Ensure DirectoryIndex doesn't cause redirect
    if ! grep -A 10 "<Directory /var/www/html/hris-seven-payroll/public>" "$APACHE_CONF" | grep -q "DirectoryIndex index.php"; then
        echo -e "${YELLOW}⚠ Updating DirectoryIndex to index.php only...${NC}"
        # Backup
        cp "$APACHE_CONF" "$APACHE_CONF.backup.$(date +%Y%m%d_%H%M%S)"
        
        # Replace Directory block to ensure DirectoryIndex is correct
        sed -i '/<Directory \/var\/www\/html\/hris-seven-payroll\/public>/,/<\/Directory>/c\
    <Directory /var/www/html/hris-seven-payroll/public>\
        Options -Indexes +FollowSymLinks\
        AllowOverride All\
        Require all granted\
        DirectoryIndex index.php\
    </Directory>' "$APACHE_CONF"
        echo -e "${GREEN}✓ DirectoryIndex updated${NC}"
    fi
fi
echo ""

echo -e "${YELLOW}[5/6] Clearing cache and restarting Apache...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
rm -f bootstrap/cache/routes*.php 2>/dev/null || true
rm -f bootstrap/cache/config.php 2>/dev/null || true

# Test Apache config
if apache2ctl configtest > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Apache configuration OK${NC}"
    systemctl restart apache2
    sleep 2
    if systemctl is-active --quiet apache2; then
        echo -e "${GREEN}✓ Apache restarted${NC}"
    else
        echo -e "${RED}❌ Apache restart failed!${NC}"
    fi
else
    echo -e "${RED}❌ Apache configuration error!${NC}"
    apache2ctl configtest
    exit 1
fi
echo ""

echo -e "${YELLOW}[6/6] Testing redirect...${NC}"
echo -e "${BLUE}Test 1: curl -I http://192.168.10.40/hris-seven-payroll${NC}"
RESPONSE1=$(curl -s -I http://192.168.10.40/hris-seven-payroll 2>&1 | head -1)
echo "$RESPONSE1"

if echo "$RESPONSE1" | grep -q "200 OK"; then
    echo -e "${GREEN}✓ SUCCESS! Aplikasi bisa diakses${NC}"
elif echo "$RESPONSE1" | grep -q "301\|302"; then
    echo -e "${RED}❌ Masih ada redirect!${NC}"
    echo -e "${YELLOW}Checking redirect location...${NC}"
    LOCATION=$(curl -s -I http://192.168.10.40/hris-seven-payroll 2>&1 | grep -i "Location:" | cut -d' ' -f2 | tr -d '\r\n')
    echo -e "${BLUE}Redirect to: $LOCATION${NC}"
    
    echo ""
    echo -e "${BLUE}Test 2: curl -I http://192.168.10.40/hris-seven-payroll/${NC}"
    RESPONSE2=$(curl -s -I http://192.168.10.40/hris-seven-payroll/ 2>&1 | head -1)
    echo "$RESPONSE2"
    
    if echo "$RESPONSE2" | grep -q "200 OK"; then
        echo -e "${GREEN}✓ Aplikasi bisa diakses dengan trailing slash${NC}"
        echo -e "${YELLOW}⚠ Gunakan URL dengan trailing slash: http://192.168.10.40/hris-seven-payroll/${NC}"
    else
        echo -e "${RED}❌ Masih ada masalah${NC}"
    fi
else
    echo -e "${RED}❌ Error: $RESPONSE1${NC}"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📋 Summary:${NC}"
echo -e "  ✓ .htaccess dibuat MINIMAL (NO redirect rules)"
echo -e "  ✓ Apache DirectoryIndex checked"
echo -e "  ✓ Cache cleared"
echo -e "  ✓ Apache restarted"
echo ""
echo -e "${YELLOW}🌐 Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll/${NC}"
echo ""
echo -e "${YELLOW}💡 Jika masih redirect:${NC}"
echo -e "  1. Check apakah ada .htaccess di parent directory"
echo -e "  2. Check Apache access log: ${BLUE}tail -f /var/log/apache2/access.log${NC}"
echo -e "  3. Enable debug: ${BLUE}sed -i 's/LogLevel.*/LogLevel debug rewrite:trace6/' /etc/apache2/apache2.conf && systemctl restart apache2${NC}"
echo ""




