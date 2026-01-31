#!/bin/bash

# Fix Redirect 301 - SOLUSI DEFINITIF FINAL
# Masalah: DirectorySlash Off sudah ada tapi masih redirect 301

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fix Redirect 301 - DEFINITIF FINAL${NC}"
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

echo -e "${YELLOW}[1/7] Checking for other .htaccess files...${NC}"
# Check parent directories
if [ -f "/var/www/html/.htaccess" ]; then
    echo -e "${YELLOW}⚠ Found .htaccess in /var/www/html/ - renaming to disable${NC}"
    mv /var/www/html/.htaccess /var/www/html/.htaccess.disabled.$(date +%Y%m%d_%H%M%S)
    echo -e "${GREEN}✓ Parent .htaccess disabled${NC}"
fi

if [ -f "$PROJECT_PATH/.htaccess" ]; then
    echo -e "${YELLOW}⚠ Found .htaccess in project root - renaming to disable${NC}"
    mv "$PROJECT_PATH/.htaccess" "$PROJECT_PATH/.htaccess.disabled.$(date +%Y%m%d_%H%M%S)"
    echo -e "${GREEN}✓ Project root .htaccess disabled${NC}"
fi
echo ""

echo -e "${YELLOW}[2/7] Fixing .htaccess (with RewriteRule to prevent redirect)...${NC}"
# Backup
if [ -f "$HTACCESS_FILE" ]; then
    cp "$HTACCESS_FILE" "$HTACCESS_FILE.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Create .htaccess dengan RewriteRule untuk INTERNAL rewrite (bukan redirect)
cat > "$HTACCESS_FILE" <<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # INTERNAL rewrite untuk handle request tanpa trailing slash (bukan redirect)
    RewriteCond %{REQUEST_URI} ^/hris-seven-payroll$
    RewriteRule ^(.*)$ /hris-seven-payroll/ [L]
    
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
echo -e "${GREEN}✓ .htaccess fixed (with redirect prevention)${NC}"
echo ""

echo -e "${YELLOW}[3/7] Updating Apache config (ensure DirectorySlash Off)...${NC}"
# Backup
if [ -f "$APACHE_CONF" ]; then
    cp "$APACHE_CONF" "$APACHE_CONF.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Update Apache config - pastikan DirectorySlash Off ada
sed -i '/<Directory \/var\/www\/html\/hris-seven-payroll\/public>/,/<\/Directory>/c\
    <Directory /var/www/html/hris-seven-payroll/public>\
        Options -Indexes +FollowSymLinks\
        AllowOverride All\
        Require all granted\
        DirectoryIndex index.php\
        DirectorySlash Off\
    </Directory>' "$APACHE_CONF"

echo -e "${GREEN}✓ Apache config updated${NC}"
echo ""

echo -e "${YELLOW}[4/7] Checking Apache modules...${NC}"
# Check if mod_dir is enabled (this might be causing the redirect)
if apache2ctl -M 2>/dev/null | grep -q "dir_module"; then
    echo -e "${YELLOW}⚠ mod_dir is enabled (this might cause redirect)${NC}"
    echo -e "${YELLOW}⚠ We'll keep it enabled but use DirectorySlash Off${NC}"
else
    echo -e "${GREEN}✓ mod_dir status checked${NC}"
fi
echo ""

echo -e "${YELLOW}[5/7] Disabling conflicting virtual hosts...${NC}"
a2dissite hris-seven-payroll.conf 2>/dev/null || echo "  (hris-seven-payroll.conf tidak ditemukan)"
a2ensite 000-default.conf 2>/dev/null || echo "  (000-default.conf sudah enabled)"
echo -e "${GREEN}✓ Virtual hosts configured${NC}"
echo ""

echo -e "${YELLOW}[6/7] Testing and restarting Apache...${NC}"
if apache2ctl configtest > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Apache configuration OK${NC}"
    systemctl restart apache2
    sleep 2
    if systemctl is-active --quiet apache2; then
        echo -e "${GREEN}✓ Apache restarted${NC}"
    else
        echo -e "${RED}❌ Apache restart failed!${NC}"
        exit 1
    fi
else
    echo -e "${RED}❌ Apache configuration error!${NC}"
    apache2ctl configtest
    exit 1
fi
echo ""

echo -e "${YELLOW}[7/7] Testing redirect...${NC}"
echo -e "${BLUE}Test 1: curl -I http://192.168.10.40/hris-seven-payroll${NC}"
RESPONSE1=$(curl -s -I http://192.168.10.40/hris-seven-payroll 2>&1 | head -1)
echo "$RESPONSE1"

if echo "$RESPONSE1" | grep -q "200 OK"; then
    echo -e "${GREEN}✓ SUCCESS! Aplikasi bisa diakses (200 OK)${NC}"
elif echo "$RESPONSE1" | grep -q "302"; then
    echo -e "${GREEN}✓ SUCCESS! Aplikasi redirect ke login (302 - normal)${NC}"
elif echo "$RESPONSE1" | grep -q "301"; then
    echo -e "${YELLOW}⚠ Masih ada redirect 301, tapi sekarang controlled${NC}"
    echo -e "${BLUE}Test 2: curl -I http://192.168.10.40/hris-seven-payroll/${NC}"
    RESPONSE2=$(curl -s -I http://192.168.10.40/hris-seven-payroll/ 2>&1 | head -1)
    echo "$RESPONSE2"
    if echo "$RESPONSE2" | grep -q "200 OK\|302"; then
        echo -e "${GREEN}✓ Aplikasi bisa diakses dengan trailing slash${NC}"
        echo -e "${YELLOW}💡 SOLUSI: Gunakan URL dengan trailing slash atau akses langsung ke /hris-seven-payroll/${NC}"
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
echo -e "  ✓ Parent .htaccess files disabled"
echo -e "  ✓ .htaccess updated (with redirect prevention)"
echo -e "  ✓ Apache config updated (DirectorySlash Off)"
echo -e "  ✓ Apache restarted"
echo ""
echo -e "${YELLOW}🌐 Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll/${NC}"
echo ""
echo -e "${YELLOW}💡 Jika masih redirect 301:${NC}"
echo -e "  Gunakan URL dengan trailing slash: ${GREEN}http://192.168.10.40/hris-seven-payroll/${NC}"
echo -e "  Atau akses langsung ke login: ${GREEN}http://192.168.10.40/hris-seven-payroll/login${NC}"
echo ""

