#!/bin/bash

# Script Deploy Sederhana - HRIS Seven Payroll
# Usage: sudo ./deploy-simple.sh

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Deploy Sederhana - HRIS Seven Payroll${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"

if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}❌ ERROR: Project path tidak ditemukan: $PROJECT_PATH${NC}"
    exit 1
fi

cd "$PROJECT_PATH"

echo -e "${YELLOW}[1/7] Fixing .env...${NC}"
if [ -f ".env" ]; then
    # Backup
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    
    # Update APP_URL dan ASSET_URL (tanpa trailing slash)
    sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' .env
    sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' .env
    # Remove trailing slash if any
    sed -i 's|APP_URL=\(.*\)/$|APP_URL=\1|g' .env
    sed -i 's|ASSET_URL=\(.*\)/$|ASSET_URL=\1|g' .env
    
    echo -e "${GREEN}✓ .env updated${NC}"
    echo -e "${BLUE}  APP_URL: $(grep "^APP_URL=" .env | cut -d'=' -f2)${NC}"
else
    echo -e "${RED}❌ .env file tidak ditemukan!${NC}"
    exit 1
fi
echo ""

echo -e "${YELLOW}[2/7] Fixing .htaccess (SEDERHANA)...${NC}"
# Backup
if [ -f "public/.htaccess" ]; then
    cp public/.htaccess public/.htaccess.backup.$(date +%Y%m%d_%H%M%S)
fi

# Create .htaccess SEDERHANA (tanpa rule redirect trailing slash)
cat > public/.htaccess <<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTACCESS
chmod 644 public/.htaccess
chown www-data:www-data public/.htaccess
echo -e "${GREEN}✓ .htaccess fixed (SEDERHANA)${NC}"
echo ""

echo -e "${YELLOW}[3/7] Fixing permissions...${NC}"
chown -R www-data:www-data "$PROJECT_PATH"
find "$PROJECT_PATH" -type d -exec chmod 755 {} \;
find "$PROJECT_PATH" -type f -exec chmod 644 {} \;
chmod -R 775 storage bootstrap/cache
mkdir -p storage/logs
touch storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

echo -e "${YELLOW}[4/7] Checking Apache configuration...${NC}"
APACHE_CONF="/etc/apache2/sites-available/000-default.conf"
if [ -f "$APACHE_CONF" ]; then
    if ! grep -q "Alias /hris-seven-payroll" "$APACHE_CONF"; then
        echo -e "${YELLOW}⚠ Apache Alias belum ada, menambahkan...${NC}"
        # Backup
        cp "$APACHE_CONF" "$APACHE_CONF.backup.$(date +%Y%m%d_%H%M%S)"
        
        # Add Alias before ErrorLog
        sed -i '/ErrorLog/i\
    # Alias untuk HRIS Seven Payroll\
    Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public\
\
    <Directory /var/www/html/hris-seven-payroll/public>\
        Options -Indexes +FollowSymLinks\
        AllowOverride All\
        Require all granted\
    </Directory>
' "$APACHE_CONF"
        echo -e "${GREEN}✓ Apache Alias ditambahkan${NC}"
    else
        echo -e "${GREEN}✓ Apache Alias sudah ada${NC}"
    fi
else
    echo -e "${RED}❌ Apache config tidak ditemukan!${NC}"
    exit 1
fi

# Disable conflicting virtual host
a2dissite hris-seven-payroll.conf 2>/dev/null || true

# Enable default site
a2ensite 000-default.conf 2>/dev/null || true

# Enable mod_rewrite
a2enmod rewrite 2>/dev/null || true
echo ""

echo -e "${YELLOW}[5/7] Clearing cache...${NC}"
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:clear 2>/dev/null || true
sudo -u www-data php artisan route:clear 2>/dev/null || true
sudo -u www-data php artisan view:clear 2>/dev/null || true
rm -f bootstrap/cache/routes*.php 2>/dev/null || true
rm -f bootstrap/cache/config.php 2>/dev/null || true
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

echo -e "${YELLOW}[6/7] Rebuilding config cache...${NC}"
sudo -u www-data php artisan config:cache 2>/dev/null || echo "  (config:cache skipped)"
echo -e "${GREEN}✓ Config cache rebuilt${NC}"
echo ""

echo -e "${YELLOW}[7/7] Testing and restarting Apache...${NC}"
if apache2ctl configtest > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Apache configuration OK${NC}"
    systemctl restart apache2
    sleep 2
    if systemctl is-active --quiet apache2; then
        echo -e "${GREEN}✓ Apache restarted successfully${NC}"
    else
        echo -e "${RED}❌ Apache restart failed!${NC}"
        systemctl status apache2 --no-pager -l
    fi
else
    echo -e "${RED}❌ Apache configuration error!${NC}"
    apache2ctl configtest
    exit 1
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  DEPLOY SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📋 Summary:${NC}"
echo -e "  ✓ .env updated"
echo -e "  ✓ .htaccess fixed (SEDERHANA)"
echo -e "  ✓ Permissions fixed"
echo -e "  ✓ Apache configuration checked"
echo -e "  ✓ Cache cleared and rebuilt"
echo -e "  ✓ Apache restarted"
echo ""
echo -e "${YELLOW}🌐 Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo ""
echo -e "${YELLOW}📝 Jika masih error, check logs:${NC}"
echo -e "  ${BLUE}tail -f /var/log/apache2/error.log${NC}"
echo -e "  ${BLUE}tail -f storage/logs/laravel.log${NC}"
echo ""




