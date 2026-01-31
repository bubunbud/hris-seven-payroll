#!/bin/bash

# Script untuk fix error route / yang hanya support HEAD method
# Deep fix: clear semua cache, check route, fix .htaccess

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Deep Fix Route Error                ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

PROJECT_PATH="/var/www/html/hris-seven-payroll"
ENV_FILE="$PROJECT_PATH/.env"

echo -e "${YELLOW}[1/8] Checking project path...${NC}"
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}ERROR: Project path tidak ditemukan: $PROJECT_PATH${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Project path OK${NC}"
echo ""

echo -e "${YELLOW}[2/8] Checking .env file...${NC}"
if [ ! -f "$ENV_FILE" ]; then
    echo -e "${RED}ERROR: .env file tidak ditemukan${NC}"
    exit 1
fi
echo -e "${GREEN}✓ .env file found${NC}"
echo ""

echo -e "${YELLOW}[3/8] Updating APP_URL and ASSET_URL...${NC}"
# Backup .env
cp "$ENV_FILE" "$ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"

# Update APP_URL dan ASSET_URL (pastikan tidak ada trailing slash)
sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' "$ENV_FILE"
sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' "$ENV_FILE"

# Remove trailing slash jika ada
sed -i 's|APP_URL=\(.*\)/$|APP_URL=\1|g' "$ENV_FILE"
sed -i 's|ASSET_URL=\(.*\)/$|ASSET_URL=\1|g' "$ENV_FILE"

# Jika belum ada, tambahkan
if ! grep -q "^APP_URL=" "$ENV_FILE"; then
    echo "APP_URL=http://192.168.10.40/hris-seven-payroll" >> "$ENV_FILE"
fi
if ! grep -q "^ASSET_URL=" "$ENV_FILE"; then
    echo "ASSET_URL=http://192.168.10.40/hris-seven-payroll" >> "$ENV_FILE"
fi

echo -e "${GREEN}✓ APP_URL dan ASSET_URL updated${NC}"
grep "^APP_URL=" "$ENV_FILE"
grep "^ASSET_URL=" "$ENV_FILE"
echo ""

echo -e "${YELLOW}[4/8] Fixing .htaccess (NO RewriteBase)...${NC}"
HTACCESS_FILE="$PROJECT_PATH/public/.htaccess"
if [ -f "$HTACCESS_FILE" ]; then
    # Backup
    cp "$HTACCESS_FILE" "$HTACCESS_FILE.backup.$(date +%Y%m%d_%H%M%S)"
    
    # Remove RewriteBase jika ada
    sed -i '/RewriteBase/d' "$HTACCESS_FILE"
    
    # Pastikan isinya benar (tanpa RewriteBase)
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
    echo -e "${GREEN}✓ .htaccess fixed (NO RewriteBase)${NC}"
else
    echo -e "${YELLOW}  Warning: .htaccess tidak ditemukan, membuat baru...${NC}"
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
    echo -e "${GREEN}✓ .htaccess created${NC}"
fi
echo ""

echo -e "${YELLOW}[5/8] Clearing ALL Laravel cache (deep clean)...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan optimize:clear 2>/dev/null || true
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan event:clear 2>/dev/null || true
echo -e "${GREEN}✓ All cache cleared${NC}"
echo ""

echo -e "${YELLOW}[6/8] Removing route cache file manually...${NC}"
# Hapus file cache route secara manual
rm -f "$PROJECT_PATH/bootstrap/cache/routes-v7.php" 2>/dev/null || true
rm -f "$PROJECT_PATH/bootstrap/cache/routes*.php" 2>/dev/null || true
echo -e "${GREEN}✓ Route cache files removed${NC}"
echo ""

echo -e "${YELLOW}[7/8] Rebuilding cache...${NC}"
sudo -u www-data php artisan config:cache
# Jangan cache route dulu, test dulu tanpa cache
# sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
echo -e "${GREEN}✓ Cache rebuilt (route cache skipped for testing)${NC}"
echo ""

echo -e "${YELLOW}[8/8] Verifying routes...${NC}"
# Check route list
ROUTE_CHECK=$(sudo -u www-data php artisan route:list 2>/dev/null | grep -E "GET.*/" || echo "")
if [ -z "$ROUTE_CHECK" ]; then
    echo -e "${YELLOW}  Warning: Route untuk / tidak ditemukan di route:list${NC}"
    echo -e "${YELLOW}  Checking routes/web.php...${NC}"
    if grep -q "Route::get('/'," "$PROJECT_PATH/routes/web.php"; then
        echo -e "${GREEN}  ✓ Route GET / ditemukan di routes/web.php${NC}"
    else
        echo -e "${RED}  ✗ Route GET / TIDAK ditemukan di routes/web.php${NC}"
    fi
else
    echo -e "${GREEN}✓ Route / ditemukan:${NC}"
    echo "$ROUTE_CHECK"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  DEEP FIX SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}PENTING: Route cache TIDAK di-rebuild untuk testing${NC}"
echo -e "${YELLOW}Silakan test aplikasi terlebih dahulu:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo ""
echo -e "${YELLOW}Jika sudah berhasil, rebuild route cache:${NC}"
echo -e "  ${GREEN}cd $PROJECT_PATH${NC}"
echo -e "  ${GREEN}sudo -u www-data php artisan route:cache${NC}"
echo ""
echo -e "${YELLOW}Jika masih error, check:${NC}"
echo -e "  1. Laravel log: tail -f $PROJECT_PATH/storage/logs/laravel.log"
echo -e "  2. Apache error log: tail -f /var/log/apache2/error.log"
echo -e "  3. Route list: cd $PROJECT_PATH && sudo -u www-data php artisan route:list"
echo ""





