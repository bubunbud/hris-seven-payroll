#!/bin/bash

# Script untuk fix Internal Server Error khusus untuk Laravel HRIS Seven Payroll
# Check .htaccess, .env, permissions, cache, dan dependencies

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Fix Laravel Internal Server Error   ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"
ENV_FILE="$PROJECT_PATH/.env"

echo -e "${YELLOW}[1/10] Checking project path...${NC}"
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}ERROR: Project path tidak ditemukan: $PROJECT_PATH${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Project path OK${NC}"
echo ""

echo -e "${YELLOW}[2/10] Checking .env file...${NC}"
if [ ! -f "$ENV_FILE" ]; then
    echo -e "${RED}ERROR: .env file tidak ditemukan!${NC}"
    exit 1
fi

# Backup .env
cp "$ENV_FILE" "$ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"

# Update .env
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

echo -e "${YELLOW}[3/10] Fixing .htaccess (NO RewriteBase for Alias)...${NC}"
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
echo -e "${GREEN}✓ .htaccess fixed (NO RewriteBase)${NC}"
echo ""

echo -e "${YELLOW}[4/10] Fixing permissions...${NC}"
chown -R www-data:www-data "$PROJECT_PATH"
find "$PROJECT_PATH" -type d -exec chmod 755 {} \;
find "$PROJECT_PATH" -type f -exec chmod 644 {} \;
chmod -R 775 "$PROJECT_PATH/storage"
chmod -R 775 "$PROJECT_PATH/bootstrap/cache"
chmod 755 "$PROJECT_PATH/artisan"
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

echo -e "${YELLOW}[5/10] Checking PHP syntax...${NC}"
cd "$PROJECT_PATH"
if php -l public/index.php > /dev/null 2>&1; then
    echo -e "${GREEN}✓ public/index.php syntax OK${NC}"
else
    echo -e "${RED}✗ public/index.php has syntax error${NC}"
    php -l public/index.php
fi
if php -l routes/web.php > /dev/null 2>&1; then
    echo -e "${GREEN}✓ routes/web.php syntax OK${NC}"
else
    echo -e "${RED}✗ routes/web.php has syntax error${NC}"
    php -l routes/web.php
fi
echo ""

echo -e "${YELLOW}[6/10] Clearing ALL cache...${NC}"
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear
# Hapus route cache file secara manual
rm -f "$PROJECT_PATH/bootstrap/cache/routes*.php" 2>/dev/null || true
rm -f "$PROJECT_PATH/bootstrap/cache/config.php" 2>/dev/null || true
echo -e "${GREEN}✓ All cache cleared${NC}"
echo ""

echo -e "${YELLOW}[7/10] Regenerating autoload...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data composer dump-autoload --optimize --no-interaction 2>&1 | tail -5
echo -e "${GREEN}✓ Autoload regenerated${NC}"
echo ""

echo -e "${YELLOW}[8/10] Rebuilding cache (without route cache)...${NC}"
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan view:cache
# Jangan rebuild route cache dulu
echo -e "${GREEN}✓ Cache rebuilt (route cache skipped)${NC}"
echo ""

echo -e "${YELLOW}[9/10] Testing Laravel application...${NC}"
if sudo -u www-data php artisan --version > /dev/null 2>&1; then
    ARTISAN_VERSION=$(sudo -u www-data php artisan --version 2>&1)
    echo -e "${GREEN}✓ Laravel OK: $ARTISAN_VERSION${NC}"
else
    echo -e "${RED}✗ Laravel Error${NC}"
    echo "Error details:"
    sudo -u www-data php artisan --version 2>&1 || true
fi
echo ""

echo -e "${YELLOW}[10/10] Checking error logs...${NC}"
echo -e "${YELLOW}Apache error log (last 10 lines):${NC}"
tail -10 /var/log/apache2/error.log 2>/dev/null || echo "  (No recent errors)"
echo ""
echo -e "${YELLOW}Laravel log (last 10 lines with errors):${NC}"
if [ -f "$PROJECT_PATH/storage/logs/laravel.log" ]; then
    tail -50 "$PROJECT_PATH/storage/logs/laravel.log" | grep -i "error\|exception\|fatal" | tail -10 || echo "  (No recent errors)"
else
    echo "  (Log file not found)"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX COMPLETE! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo ""
echo -e "${YELLOW}Jika masih error, check:${NC}"
echo -e "  1. Apache error log: ${GREEN}tail -f /var/log/apache2/error.log${NC}"
echo -e "  2. Laravel log: ${GREEN}tail -f $PROJECT_PATH/storage/logs/laravel.log${NC}"
echo -e "  3. Test PHP: ${GREEN}php -r \"echo 'PHP OK';\"${NC}"
echo -e "  4. Test Laravel: ${GREEN}cd $PROJECT_PATH && sudo -u www-data php artisan --version${NC}"
echo ""





