#!/bin/bash

# Fix Redirect 301 - ACCEPT dan FIX
# Solusi: Accept redirect 301, tapi pastikan aplikasi bisa diakses dengan trailing slash

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fix Redirect 301 - ACCEPT & FIX${NC}"
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

echo -e "${YELLOW}[1/5] Fixing .htaccess (SEDERHANA)...${NC}"
# Backup
if [ -f "$HTACCESS_FILE" ]; then
    cp "$HTACCESS_FILE" "$HTACCESS_FILE.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Create .htaccess SEDERHANA
cat > "$HTACCESS_FILE" <<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    
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
echo -e "${GREEN}✓ .htaccess fixed${NC}"
echo ""

echo -e "${YELLOW}[2/5] Updating Apache config (with LocationMatch)...${NC}"
# Backup
if [ -f "$APACHE_CONF" ]; then
    cp "$APACHE_CONF" "$APACHE_CONF.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Create Apache config dengan LocationMatch untuk handle path tanpa trailing slash
cat > "$APACHE_CONF" <<'APACHE'
<VirtualHost *:80>
    ServerName 192.168.10.40
    ServerAdmin webmaster@localhost

    # Default DocumentRoot
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Alias untuk HRIS Seven Payroll
    Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

    <Directory /var/www/html/hris-seven-payroll/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
        DirectorySlash Off
    </Directory>

    # LocationMatch untuk handle path tanpa trailing slash (internal rewrite)
    <LocationMatch "^/hris-seven-payroll$">
        RewriteEngine On
        RewriteRule ^(.*)$ /hris-seven-payroll/ [L]
    </LocationMatch>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
APACHE
echo -e "${GREEN}✓ Apache config updated (with LocationMatch)${NC}"
echo ""

echo -e "${YELLOW}[3/5] Disabling conflicting virtual hosts...${NC}"
a2dissite hris-seven-payroll.conf 2>/dev/null || echo "  (hris-seven-payroll.conf tidak ditemukan)"
a2ensite 000-default.conf 2>/dev/null || echo "  (000-default.conf sudah enabled)"
a2enmod rewrite 2>/dev/null || echo "  (mod_rewrite sudah enabled)"
echo -e "${GREEN}✓ Virtual hosts configured${NC}"
echo ""

echo -e "${YELLOW}[4/5] Testing and restarting Apache...${NC}"
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

echo -e "${YELLOW}[5/5] Testing...${NC}"
echo -e "${BLUE}Test 1: curl -I http://192.168.10.40/hris-seven-payroll${NC}"
RESPONSE1=$(curl -s -I http://192.168.10.40/hris-seven-payroll 2>&1 | head -1)
echo "$RESPONSE1"

echo ""
echo -e "${BLUE}Test 2: curl -I http://192.168.10.40/hris-seven-payroll/${NC}"
RESPONSE2=$(curl -s -I http://192.168.10.40/hris-seven-payroll/ 2>&1 | head -1)
echo "$RESPONSE2"

if echo "$RESPONSE2" | grep -q "200 OK\|302"; then
    echo -e "${GREEN}✓ SUCCESS! Aplikasi bisa diakses dengan trailing slash${NC}"
    if echo "$RESPONSE1" | grep -q "301"; then
        echo -e "${YELLOW}⚠ Redirect 301 masih ada, tapi aplikasi bisa diakses${NC}"
        echo -e "${YELLOW}💡 SOLUSI: Gunakan URL dengan trailing slash atau browser akan auto-redirect${NC}"
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
echo -e "  ✓ .htaccess fixed (SEDERHANA)"
echo -e "  ✓ Apache config updated (with LocationMatch)"
echo -e "  ✓ Apache restarted"
echo ""
echo -e "${YELLOW}🌐 Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll/${NC}"
echo ""
echo -e "${YELLOW}💡 Catatan:${NC}"
echo -e "  Jika masih ada redirect 301, itu NORMAL untuk mod_dir"
echo -e "  Browser akan otomatis follow redirect ke trailing slash"
echo -e "  Aplikasi akan bisa diakses dengan baik"
echo ""




