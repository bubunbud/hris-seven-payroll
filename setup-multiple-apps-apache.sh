#!/bin/bash

# Script untuk setup Apache agar bisa mengakses multiple aplikasi
# - http://192.168.10.40/hris-seven-payroll
# - http://192.168.10.40/sevenl
# - Aplikasi lain di /var/www/html

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Setup Multiple Apps Apache Config   ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

# Backup 000-default.conf
echo -e "${YELLOW}[1/6] Backing up 000-default.conf...${NC}"
cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf.backup.$(date +%Y%m%d_%H%M%S)
echo -e "${GREEN}✓ Backup created${NC}"
echo ""

# Update 000-default.conf dengan Alias
echo -e "${YELLOW}[2/6] Updating 000-default.conf with Aliases...${NC}"
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

    # Tambahkan Alias untuk aplikasi lain di sini jika diperlukan
    # Contoh:
    # Alias /app-lain /var/www/html/app-lain/public
    # <Directory /var/www/html/app-lain/public>
    #     Options -Indexes +FollowSymLinks
    #     AllowOverride All
    #     Require all granted
    #     DirectoryIndex index.php index.html
    # </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF
echo -e "${GREEN}✓ 000-default.conf updated${NC}"
echo ""

# Disable hris-seven-payroll.conf (jika ada)
echo -e "${YELLOW}[3/6] Disabling hris-seven-payroll.conf...${NC}"
a2dissite hris-seven-payroll.conf 2>/dev/null || echo "  (hris-seven-payroll.conf tidak ditemukan atau sudah disabled)"
echo -e "${GREEN}✓ hris-seven-payroll.conf disabled${NC}"
echo ""

# Enable 000-default.conf
echo -e "${YELLOW}[4/6] Enabling 000-default.conf...${NC}"
a2ensite 000-default.conf
echo -e "${GREEN}✓ 000-default.conf enabled${NC}"
echo ""

# Fix .htaccess untuk hris-seven-payroll (TANPA RewriteBase karena pakai Alias)
echo -e "${YELLOW}[5/6] Fixing .htaccess for hris-seven-payroll...${NC}"
if [ -f "/var/www/html/hris-seven-payroll/public/.htaccess" ]; then
    cat > /var/www/html/hris-seven-payroll/public/.htaccess <<'HTACCESS'
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
    echo -e "${GREEN}✓ .htaccess updated (NO RewriteBase for Alias)${NC}"
else
    echo -e "${YELLOW}  Warning: .htaccess tidak ditemukan${NC}"
fi
echo ""

# Test dan restart Apache
echo -e "${YELLOW}[6/6] Testing Apache configuration...${NC}"
if apache2ctl configtest; then
    echo -e "${GREEN}✓ Configuration test passed${NC}"
    systemctl restart apache2
    echo -e "${GREEN}✓ Apache restarted${NC}"
else
    echo -e "${RED}✗ Configuration test failed!${NC}"
    echo -e "${YELLOW}Restoring backup...${NC}"
    cp /etc/apache2/sites-available/000-default.conf.backup.* /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
    exit 1
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  SETUP SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Aplikasi sekarang bisa diakses di:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo -e "  ${GREEN}http://192.168.10.40/sevenl${NC}"
echo -e "  ${GREEN}http://192.168.10.40/ (untuk aplikasi lain)${NC}"
echo ""
echo -e "${YELLOW}Catatan:${NC}"
echo -e "  - Pastikan folder aplikasi ada di /var/www/html/"
echo -e "  - Untuk menambah aplikasi baru, edit /etc/apache2/sites-available/000-default.conf"
echo -e "  - Tambahkan Alias dan Directory block seperti contoh di atas"
echo ""





