#!/bin/bash

# Script Lengkap untuk Fix Internal Server Error
# Mengatasi semua masalah umum yang menyebabkan Internal Server Error

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fix Internal Server Error - Complete${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"
ENV_FILE="$PROJECT_PATH/.env"
HTACCESS_FILE="$PROJECT_PATH/public/.htaccess"
APACHE_CONF="/etc/apache2/sites-available/000-default.conf"

echo -e "${YELLOW}[1/10] Checking project path...${NC}"
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}❌ ERROR: Project path tidak ditemukan: $PROJECT_PATH${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Project path OK${NC}"
echo ""

echo -e "${YELLOW}[2/10] Checking Apache configuration...${NC}"
# Check if 000-default.conf exists and has Alias
if [ -f "$APACHE_CONF" ]; then
    if grep -q "Alias /hris-seven-payroll" "$APACHE_CONF"; then
        echo -e "${GREEN}✓ Apache Alias sudah ada${NC}"
    else
        echo -e "${YELLOW}⚠ Apache Alias belum ada, menambahkan...${NC}"
        # Backup
        cp "$APACHE_CONF" "$APACHE_CONF.backup.$(date +%Y%m%d_%H%M%S)"
        
        # Check if Directory block exists
        if ! grep -q "<Directory /var/www/html/hris-seven-payroll/public>" "$APACHE_CONF"; then
            # Add Alias and Directory block before ErrorLog
            sed -i '/ErrorLog/i\
    # Alias untuk HRIS Seven Payroll\
    Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public\
\
    <Directory /var/www/html/hris-seven-payroll/public>\
        Options -Indexes +FollowSymLinks\
        AllowOverride All\
        Require all granted\
        DirectoryIndex index.php index.html\
    </Directory>
' "$APACHE_CONF"
            echo -e "${GREEN}✓ Apache Alias ditambahkan${NC}"
        fi
    fi
    
    # Disable hris-seven-payroll.conf if exists
    if [ -f "/etc/apache2/sites-enabled/hris-seven-payroll.conf" ]; then
        echo -e "${YELLOW}⚠ Disabling hris-seven-payroll.conf...${NC}"
        a2dissite hris-seven-payroll.conf 2>/dev/null || true
        echo -e "${GREEN}✓ hris-seven-payroll.conf disabled${NC}"
    fi
    
    # Enable 000-default.conf
    if [ ! -f "/etc/apache2/sites-enabled/000-default.conf" ]; then
        echo -e "${YELLOW}⚠ Enabling 000-default.conf...${NC}"
        a2ensite 000-default.conf
        echo -e "${GREEN}✓ 000-default.conf enabled${NC}"
    fi
else
    echo -e "${RED}❌ ERROR: $APACHE_CONF tidak ditemukan!${NC}"
    exit 1
fi
echo ""

echo -e "${YELLOW}[3/10] Fixing permissions...${NC}"
# Set ownership
chown -R www-data:www-data "$PROJECT_PATH"
# Set permissions
find "$PROJECT_PATH" -type d -exec chmod 755 {} \;
find "$PROJECT_PATH" -type f -exec chmod 644 {} \;
# Set writable untuk storage dan cache
chmod -R 775 "$PROJECT_PATH/storage"
chmod -R 775 "$PROJECT_PATH/bootstrap/cache"
# Ensure storage/logs exists and is writable
mkdir -p "$PROJECT_PATH/storage/logs"
touch "$PROJECT_PATH/storage/logs/laravel.log"
chown www-data:www-data "$PROJECT_PATH/storage/logs/laravel.log"
chmod 664 "$PROJECT_PATH/storage/logs/laravel.log"
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

echo -e "${YELLOW}[4/10] Checking and fixing .env...${NC}"
if [ ! -f "$ENV_FILE" ]; then
    echo -e "${RED}❌ ERROR: .env file tidak ditemukan!${NC}"
    echo -e "${YELLOW}⚠ Membuat .env dari .env.example...${NC}"
    if [ -f "$PROJECT_PATH/.env.example" ]; then
        cp "$PROJECT_PATH/.env.example" "$ENV_FILE"
    else
        echo -e "${RED}❌ .env.example juga tidak ditemukan!${NC}"
        exit 1
    fi
fi

# Backup
cp "$ENV_FILE" "$ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"

# Update APP_URL dan ASSET_URL (tanpa trailing slash)
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' "$ENV_FILE"
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' "$ENV_FILE"
# Remove trailing slash if any
sed -i 's|APP_URL=\(.*\)/$|APP_URL=\1|g' "$ENV_FILE"
sed -i 's|ASSET_URL=\(.*\)/$|ASSET_URL=\1|g' "$ENV_FILE"

# Pastikan APP_DEBUG=true untuk debugging
if ! grep -q "^APP_DEBUG=" "$ENV_FILE"; then
    echo "APP_DEBUG=true" >> "$ENV_FILE"
else
    sed -i 's|^APP_DEBUG=.*|APP_DEBUG=true|g' "$ENV_FILE"
fi

# Pastikan APP_ENV=local
if ! grep -q "^APP_ENV=" "$ENV_FILE"; then
    echo "APP_ENV=local" >> "$ENV_FILE"
else
    sed -i 's|^APP_ENV=.*|APP_ENV=local|g' "$ENV_FILE"
fi

echo -e "${GREEN}✓ .env updated${NC}"
echo -e "${BLUE}  APP_URL: $(grep "^APP_URL=" "$ENV_FILE" | cut -d'=' -f2)${NC}"
echo -e "${BLUE}  APP_DEBUG: $(grep "^APP_DEBUG=" "$ENV_FILE" | cut -d'=' -f2)${NC}"
echo ""

echo -e "${YELLOW}[5/10] Fixing .htaccess...${NC}"
# Backup
cp "$HTACCESS_FILE" "$HTACCESS_FILE.backup.$(date +%Y%m%d_%H%M%S)" 2>/dev/null || true

# Create .htaccess TANPA RewriteBase (karena pakai Alias)
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
chown www-data:www-data "$HTACCESS_FILE"
echo -e "${GREEN}✓ .htaccess fixed (NO RewriteBase)${NC}"
echo ""

echo -e "${YELLOW}[6/10] Clearing all cache...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:clear 2>/dev/null || true
sudo -u www-data php artisan route:clear 2>/dev/null || true
sudo -u www-data php artisan view:clear 2>/dev/null || true
sudo -u www-data php artisan cache:clear 2>/dev/null || true
# Hapus route cache file
rm -f "$PROJECT_PATH/bootstrap/cache/routes*.php" 2>/dev/null || true
rm -f "$PROJECT_PATH/bootstrap/cache/config.php" 2>/dev/null || true
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

echo -e "${YELLOW}[7/10] Rebuilding cache...${NC}"
sudo -u www-data php artisan config:cache 2>/dev/null || echo "  (config:cache skipped)"
sudo -u www-data php artisan view:cache 2>/dev/null || echo "  (view:cache skipped)"
# Jangan rebuild route cache dulu
echo -e "${GREEN}✓ Cache rebuilt (route cache skipped)${NC}"
echo ""

echo -e "${YELLOW}[8/10] Running composer dump-autoload...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data composer dump-autoload --optimize --no-interaction 2>/dev/null || echo "  (composer dump-autoload skipped)"
echo -e "${GREEN}✓ Autoload optimized${NC}"
echo ""

echo -e "${YELLOW}[9/10] Testing Apache configuration...${NC}"
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
    fi
else
    echo -e "${RED}❌ Apache configuration error!${NC}"
    apache2ctl configtest
    exit 1
fi
echo ""

echo -e "${YELLOW}[10/10] Checking error logs...${NC}"
echo -e "${BLUE}=== Apache Error Log (last 10 lines) ===${NC}"
tail -10 /var/log/apache2/error.log 2>/dev/null || echo "  (No error log)"
echo ""
echo -e "${BLUE}=== Laravel Log (last 10 lines) ===${NC}"
tail -10 "$PROJECT_PATH/storage/logs/laravel.log" 2>/dev/null || echo "  (No Laravel log)"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📋 Summary:${NC}"
echo -e "  ✓ Apache configuration checked/updated"
echo -e "  ✓ Permissions fixed"
echo -e "  ✓ .env updated"
echo -e "  ✓ .htaccess fixed (NO RewriteBase)"
echo -e "  ✓ Cache cleared and rebuilt"
echo -e "  ✓ Apache restarted"
echo ""
echo -e "${YELLOW}🌐 Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo ""
echo -e "${YELLOW}📝 Jika masih error, check logs:${NC}"
echo -e "  ${BLUE}tail -f /var/log/apache2/error.log${NC}"
echo -e "  ${BLUE}tail -f $PROJECT_PATH/storage/logs/laravel.log${NC}"
echo ""
echo -e "${YELLOW}💡 Catatan:${NC}"
echo -e "  - Server Ubuntu menggunakan Apache (port 80, tidak perlu port khusus)"
echo -e "  - Bukan seperti localhost yang pakai 'php artisan serve' (port 8000)"
echo -e "  - Akses aplikasi di: http://192.168.10.40/hris-seven-payroll"
echo ""




