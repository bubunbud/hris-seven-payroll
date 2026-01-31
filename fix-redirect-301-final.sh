#!/bin/bash

# Fix Redirect 301 - Disable mod_dir auto-trailing-slash
# Masalah: Apache mod_dir otomatis redirect /hris-seven-payroll ke /hris-seven-payroll/

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fix Redirect 301 - FINAL${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"
HTACCESS_FILE="$PROJECT_PATH/public/.htaccess"
APACHE_CONF="/etc/apache2/sites-available/000-default.conf"

echo -e "${YELLOW}[1/7] Fixing .htaccess (disable DirectorySlash)...${NC}"
# Backup
if [ -f "$HTACCESS_FILE" ]; then
    cp "$HTACCESS_FILE" "$HTACCESS_FILE.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Create .htaccess dengan DirectorySlash Off untuk mencegah redirect
cat > "$HTACCESS_FILE" <<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Disable DirectorySlash redirect (prevent 301 from /path to /path/)
    DirectorySlash Off
    
    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    
    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTACCESS
chmod 644 "$HTACCESS_FILE"
chown www-data:www-data "$HTACCESS_FILE"
echo -e "${GREEN}✓ .htaccess fixed (DirectorySlash Off)${NC}"
echo ""

echo -e "${YELLOW}[2/7] Checking Apache configuration...${NC}"
if [ -f "$APACHE_CONF" ]; then
    # Backup
    cp "$APACHE_CONF" "$APACHE_CONF.backup.$(date +%Y%m%d_%H%M%S)"
    
    # Check if Directory block exists
    if grep -q "<Directory /var/www/html/hris-seven-payroll/public>" "$APACHE_CONF"; then
        # Update Directory block to disable DirectorySlash
        sed -i '/<Directory \/var\/www\/html\/hris-seven-payroll\/public>/,/<\/Directory>/c\
    <Directory /var/www/html/hris-seven-payroll/public>\
        Options -Indexes +FollowSymLinks\
        AllowOverride All\
        Require all granted\
        DirectoryIndex index.php\
        DirectorySlash Off\
    </Directory>' "$APACHE_CONF"
        echo -e "${GREEN}✓ Directory block updated (DirectorySlash Off)${NC}"
    else
        # Add Directory block with DirectorySlash Off
        if grep -q "Alias /hris-seven-payroll" "$APACHE_CONF"; then
            sed -i '/Alias \/hris-seven-payroll/a\
\
    <Directory /var/www/html/hris-seven-payroll/public>\
        Options -Indexes +FollowSymLinks\
        AllowOverride All\
        Require all granted\
        DirectoryIndex index.php\
        DirectorySlash Off\
    </Directory>' "$APACHE_CONF"
            echo -e "${GREEN}✓ Directory block added (DirectorySlash Off)${NC}"
        else
            echo -e "${RED}❌ Apache Alias belum ada!${NC}"
            exit 1
        fi
    fi
else
    echo -e "${RED}❌ Apache config tidak ditemukan!${NC}"
    exit 1
fi
echo ""

echo -e "${YELLOW}[3/7] Checking current .htaccess content...${NC}"
echo -e "${BLUE}Current .htaccess:${NC}"
cat "$HTACCESS_FILE"
echo ""

echo -e "${YELLOW}[4/7] Testing Apache configuration...${NC}"
if apache2ctl configtest > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Apache configuration OK${NC}"
else
    echo -e "${RED}❌ Apache configuration error!${NC}"
    apache2ctl configtest
    exit 1
fi
echo ""

echo -e "${YELLOW}[5/7] Restarting Apache...${NC}"
systemctl restart apache2
sleep 2
if systemctl is-active --quiet apache2; then
    echo -e "${GREEN}✓ Apache restarted${NC}"
else
    echo -e "${RED}❌ Apache restart failed!${NC}"
    systemctl status apache2 --no-pager -l
    exit 1
fi
echo ""

echo -e "${YELLOW}[6/7] Clearing cache...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
rm -f bootstrap/cache/routes*.php 2>/dev/null || true
rm -f bootstrap/cache/config.php 2>/dev/null || true
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

echo -e "${YELLOW}[7/7] Testing redirect...${NC}"
echo -e "${BLUE}Test 1: curl -I http://192.168.10.40/hris-seven-payroll${NC}"
RESPONSE1=$(curl -s -I http://192.168.10.40/hris-seven-payroll 2>&1 | head -1)
echo "$RESPONSE1"

if echo "$RESPONSE1" | grep -q "200 OK"; then
    echo -e "${GREEN}✓ SUCCESS! Aplikasi bisa diakses tanpa redirect${NC}"
elif echo "$RESPONSE1" | grep -q "301\|302"; then
    echo -e "${RED}❌ Masih ada redirect!${NC}"
    echo -e "${YELLOW}Checking redirect location...${NC}"
    LOCATION=$(curl -s -I http://192.168.10.40/hris-seven-payroll 2>&1 | grep -i "Location:" | cut -d' ' -f2 | tr -d '\r\n')
    echo -e "${BLUE}Redirect to: $LOCATION${NC}"
    
    echo ""
    echo -e "${BLUE}Test 2: curl -I http://192.168.10.40/hris-seven-payroll/${NC}"
    RESPONSE2=$(curl -s -I http://192.168.10.40/hris-seven-payroll/ 2>&1 | head -1)
    echo "$RESPONSE2"
    
    if echo "$RESPONSE2" | grep -q "200 OK\|302"; then
        echo -e "${YELLOW}⚠ Aplikasi bisa diakses dengan trailing slash${NC}"
        echo -e "${YELLOW}⚠ Gunakan URL dengan trailing slash: http://192.168.10.40/hris-seven-payroll/${NC}"
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
echo -e "  ✓ .htaccess updated (DirectorySlash Off)"
echo -e "  ✓ Apache Directory block updated (DirectorySlash Off)"
echo -e "  ✓ Apache restarted"
echo -e "  ✓ Cache cleared"
echo ""
echo -e "${YELLOW}🌐 Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll/${NC}"
echo ""
echo -e "${YELLOW}📝 Jika masih error 500, check Laravel log:${NC}"
echo -e "  ${BLUE}tail -50 storage/logs/laravel.log${NC}"
echo ""




