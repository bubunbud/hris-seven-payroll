#!/bin/bash

# Fix Apache Config - DEFINITIF
# Solusi lengkap untuk redirect loop 301

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fix Apache Config - DEFINITIF${NC}"
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

echo -e "${YELLOW}[1/6] Fixing .htaccess (SEDERHANA - NO redirect rules)...${NC}"
# Backup
if [ -f "$HTACCESS_FILE" ]; then
    cp "$HTACCESS_FILE" "$HTACCESS_FILE.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Create .htaccess SEDERHANA (hanya rule Laravel, TIDAK ADA redirect)
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
echo -e "${GREEN}✓ .htaccess fixed (SEDERHANA)${NC}"
echo ""

echo -e "${YELLOW}[2/6] Fixing Apache config (000-default.conf)...${NC}"
# Backup
if [ -f "$APACHE_CONF" ]; then
    cp "$APACHE_CONF" "$APACHE_CONF.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Create Apache config dengan DirectorySlash Off
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

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
APACHE
echo -e "${GREEN}✓ Apache config fixed (DirectorySlash Off)${NC}"
echo ""

echo -e "${YELLOW}[3/6] Disabling conflicting virtual hosts...${NC}"
# Disable hris-seven-payroll.conf
a2dissite hris-seven-payroll.conf 2>/dev/null || echo "  (hris-seven-payroll.conf tidak ditemukan atau sudah disabled)"

# Enable 000-default.conf
a2ensite 000-default.conf 2>/dev/null || echo "  (000-default.conf sudah enabled)"
echo -e "${GREEN}✓ Virtual hosts configured${NC}"
echo ""

echo -e "${YELLOW}[4/6] Enabling mod_rewrite...${NC}"
a2enmod rewrite 2>/dev/null || echo "  (mod_rewrite sudah enabled)"
echo -e "${GREEN}✓ mod_rewrite enabled${NC}"
echo ""

echo -e "${YELLOW}[5/6] Testing Apache configuration...${NC}"
if apache2ctl configtest > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Apache configuration OK${NC}"
    echo -e "${YELLOW}⚠ Restarting Apache...${NC}"
    systemctl restart apache2
    sleep 2
    if systemctl is-active --quiet apache2; then
        echo -e "${GREEN}✓ Apache restarted successfully${NC}"
    else
        echo -e "${RED}❌ Apache restart failed!${NC}"
        systemctl status apache2 --no-pager -l
        exit 1
    fi
else
    echo -e "${RED}❌ Apache configuration error!${NC}"
    apache2ctl configtest
    exit 1
fi
echo ""

echo -e "${YELLOW}[6/6] Testing application...${NC}"
echo -e "${BLUE}Test: curl -I http://192.168.10.40/hris-seven-payroll${NC}"
RESPONSE=$(curl -s -I http://192.168.10.40/hris-seven-payroll 2>&1 | head -1)
echo "$RESPONSE"

if echo "$RESPONSE" | grep -q "200 OK"; then
    echo -e "${GREEN}✓ SUCCESS! Aplikasi bisa diakses (200 OK)${NC}"
elif echo "$RESPONSE" | grep -q "302"; then
    echo -e "${GREEN}✓ SUCCESS! Aplikasi redirect ke login (302 - normal)${NC}"
elif echo "$RESPONSE" | grep -q "301"; then
    echo -e "${RED}❌ Masih ada redirect 301!${NC}"
    echo -e "${YELLOW}⚠ Coba akses dengan trailing slash: http://192.168.10.40/hris-seven-payroll/${NC}"
    RESPONSE2=$(curl -s -I http://192.168.10.40/hris-seven-payroll/ 2>&1 | head -1)
    echo "$RESPONSE2"
    if echo "$RESPONSE2" | grep -q "200 OK\|302"; then
        echo -e "${YELLOW}⚠ Aplikasi bisa diakses dengan trailing slash${NC}"
    fi
else
    echo -e "${RED}❌ Error: $RESPONSE${NC}"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📋 Summary:${NC}"
echo -e "  ✓ .htaccess fixed (SEDERHANA)"
echo -e "  ✓ Apache config fixed (DirectorySlash Off)"
echo -e "  ✓ Virtual hosts configured"
echo -e "  ✓ mod_rewrite enabled"
echo -e "  ✓ Apache restarted"
echo ""
echo -e "${YELLOW}🌐 Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll/${NC}"
echo ""
echo -e "${YELLOW}📝 Jika masih error, check logs:${NC}"
echo -e "  ${BLUE}tail -f /var/log/apache2/error.log${NC}"
echo -e "  ${BLUE}tail -f $PROJECT_PATH/storage/logs/laravel.log${NC}"
echo ""




