#!/bin/bash

# Script untuk fix Internal Server Error
# Fix permissions, .env, cache, dan .htaccess

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Fix Internal Server Error            ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"
ENV_FILE="$PROJECT_PATH/.env"

echo -e "${YELLOW}[1/7] Checking project path...${NC}"
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}ERROR: Project path tidak ditemukan: $PROJECT_PATH${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Project path OK${NC}"
echo ""

echo -e "${YELLOW}[2/7] Fixing permissions...${NC}"
# Set ownership
chown -R www-data:www-data "$PROJECT_PATH"
# Set permissions
find "$PROJECT_PATH" -type d -exec chmod 755 {} \;
find "$PROJECT_PATH" -type f -exec chmod 644 {} \;
# Set writable untuk storage dan cache
chmod -R 775 "$PROJECT_PATH/storage"
chmod -R 775 "$PROJECT_PATH/bootstrap/cache"
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

echo -e "${YELLOW}[3/7] Checking and fixing .env...${NC}"
if [ ! -f "$ENV_FILE" ]; then
    echo -e "${RED}ERROR: .env file tidak ditemukan!${NC}"
    exit 1
fi

# Backup
cp "$ENV_FILE" "$ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"

# Update APP_URL dan ASSET_URL
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' "$ENV_FILE"
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' "$ENV_FILE"
sed -i 's|APP_URL=\(.*\)/$|APP_URL=\1|g' "$ENV_FILE"
sed -i 's|ASSET_URL=\(.*\)/$|ASSET_URL=\1|g' "$ENV_FILE"

# Pastikan APP_DEBUG=true untuk debugging
if ! grep -q "^APP_DEBUG=" "$ENV_FILE"; then
    echo "APP_DEBUG=true" >> "$ENV_FILE"
else
    sed -i 's|^APP_DEBUG=.*|APP_DEBUG=true|g' "$ENV_FILE"
fi

echo -e "${GREEN}✓ .env updated${NC}"
grep "^APP_URL=" "$ENV_FILE"
grep "^APP_DEBUG=" "$ENV_FILE"
echo ""

echo -e "${YELLOW}[4/7] Fixing .htaccess...${NC}"
HTACCESS_FILE="$PROJECT_PATH/public/.htaccess"
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
echo -e "${GREEN}✓ .htaccess fixed${NC}"
echo ""

echo -e "${YELLOW}[5/7] Clearing all cache...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear
# Hapus route cache file
rm -f "$PROJECT_PATH/bootstrap/cache/routes*.php" 2>/dev/null || true
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

echo -e "${YELLOW}[6/7] Rebuilding cache...${NC}"
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache
# Jangan rebuild route cache dulu
echo -e "${GREEN}✓ Cache rebuilt (route cache skipped)${NC}"
echo ""

echo -e "${YELLOW}[7/7] Testing PHP and Laravel...${NC}"
# Test PHP
php -v
echo ""

# Test Laravel
cd "$PROJECT_PATH"
if sudo -u www-data php artisan --version > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Laravel OK${NC}"
else
    echo -e "${RED}✗ Laravel Error${NC}"
    echo "Error details:"
    sudo -u www-data php artisan --version 2>&1 || true
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo -e "  1. Check Apache error log: ${GREEN}tail -f /var/log/apache2/error.log${NC}"
echo -e "  2. Check Laravel log: ${GREEN}tail -f $PROJECT_PATH/storage/logs/laravel.log${NC}"
echo -e "  3. Test aplikasi: ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo ""
echo -e "${YELLOW}Jika masih error, jalankan debug script:${NC}"
echo -e "  ${GREEN}sudo ./debug-internal-server-error.sh${NC}"
echo ""





