#!/bin/bash

# Script untuk fix konflik konfigurasi Apache
# Disable hris-seven-payroll.conf dan pastikan 000-default.conf aktif dengan Alias

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Fix Apache Config Conflict           ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

echo -e "${YELLOW}[1/6] Checking current Apache sites...${NC}"
a2query -s
echo ""

echo -e "${YELLOW}[2/6] Disabling hris-seven-payroll.conf...${NC}"
a2dissite hris-seven-payroll.conf 2>/dev/null || echo "  (Already disabled or not found)"
echo -e "${GREEN}✓ hris-seven-payroll.conf disabled${NC}"
echo ""

echo -e "${YELLOW}[3/6] Checking 000-default.conf...${NC}"
if [ ! -f "/etc/apache2/sites-available/000-default.conf" ]; then
    echo -e "${RED}ERROR: 000-default.conf tidak ditemukan!${NC}"
    exit 1
fi

# Backup
cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf.backup.$(date +%Y%m%d_%H%M%S)

# Check apakah sudah ada Alias untuk hris-seven-payroll
if ! grep -q "Alias /hris-seven-payroll" /etc/apache2/sites-available/000-default.conf; then
    echo -e "${YELLOW}  Alias untuk hris-seven-payroll tidak ditemukan, menambahkan...${NC}"
    
    # Update 000-default.conf dengan Alias
    cat > /etc/apache2/sites-available/000-default.conf <<'EOF'
<VirtualHost *:80>
    ServerName 192.168.10.40
    ServerAdmin webmaster@localhost

    # Default DocumentRoot untuk aplikasi lain
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
        DirectoryIndex index.php index.html
    </Directory>

    # Alias untuk SevenL (jika ada)
    Alias /sevenl /var/www/html/sevenl/public

    <Directory /var/www/html/sevenl/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF
    echo -e "${GREEN}✓ 000-default.conf updated dengan Alias${NC}"
else
    echo -e "${GREEN}✓ Alias sudah ada di 000-default.conf${NC}"
fi
echo ""

echo -e "${YELLOW}[4/6] Enabling 000-default.conf...${NC}"
a2ensite 000-default.conf
echo -e "${GREEN}✓ 000-default.conf enabled${NC}"
echo ""

echo -e "${YELLOW}[5/6] Fixing .htaccess for hris-seven-payroll...${NC}"
HTACCESS_FILE="/var/www/html/hris-seven-payroll/public/.htaccess"
cat > "$HTACCESS_FILE" <<'HTACCESS'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTACCESS
chmod 644 "$HTACCESS_FILE"
echo -e "${GREEN}✓ .htaccess fixed (NO RewriteBase)${NC}"
echo ""

echo -e "${YELLOW}[6/6] Testing and restarting Apache...${NC}"
if apache2ctl configtest; then
    systemctl restart apache2
    echo -e "${GREEN}✓ Apache restarted${NC}"
else
    echo -e "${RED}✗ Apache configuration test failed!${NC}"
    echo -e "${YELLOW}Restoring backup...${NC}"
    cp /etc/apache2/sites-available/000-default.conf.backup.* /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
    exit 1
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Verifikasi konfigurasi:${NC}"
echo -e "  Active sites:"
a2query -s
echo ""
echo -e "${YELLOW}Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo ""
echo -e "${YELLOW}Jika masih error, check:${NC}"
echo -e "  ${GREEN}tail -f /var/log/apache2/error.log${NC}"
echo -e "  ${GREEN}tail -f /var/www/html/hris-seven-payroll/storage/logs/laravel.log${NC}"
echo ""





