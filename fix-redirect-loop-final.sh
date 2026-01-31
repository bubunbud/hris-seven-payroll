#!/bin/bash

# Script untuk Fix Redirect Loop (10 internal redirects)
# Masalah: .htaccess atau Apache config menyebabkan redirect loop

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fix Redirect Loop - Final${NC}"
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

echo -e "${YELLOW}[1/8] Checking project path...${NC}"
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}❌ ERROR: Project path tidak ditemukan: $PROJECT_PATH${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Project path OK${NC}"
echo ""

echo -e "${YELLOW}[2/8] Fixing .htaccess (REMOVE RewriteBase completely)...${NC}"
# Backup
if [ -f "$HTACCESS_FILE" ]; then
    cp "$HTACCESS_FILE" "$HTACCESS_FILE.backup.$(date +%Y%m%d_%H%M%S)"
fi

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

echo -e "${YELLOW}[3/8] Checking Apache configuration...${NC}"
# Check if 000-default.conf has correct Alias
if [ -f "$APACHE_CONF" ]; then
    if grep -q "Alias /hris-seven-payroll" "$APACHE_CONF"; then
        echo -e "${GREEN}✓ Apache Alias sudah ada${NC}"
        
        # Check if Directory block is correct
        if grep -q "<Directory /var/www/html/hris-seven-payroll/public>" "$APACHE_CONF"; then
            # Check if AllowOverride All exists
            if grep -A 5 "<Directory /var/www/html/hris-seven-payroll/public>" "$APACHE_CONF" | grep -q "AllowOverride All"; then
                echo -e "${GREEN}✓ Directory block sudah benar${NC}"
            else
                echo -e "${YELLOW}⚠ Directory block perlu di-update...${NC}"
                # Backup
                cp "$APACHE_CONF" "$APACHE_CONF.backup.$(date +%Y%m%d_%H%M%S)"
                
                # Replace Directory block
                sed -i '/<Directory \/var\/www\/html\/hris-seven-payroll\/public>/,/<\/Directory>/c\
    <Directory /var/www/html/hris-seven-payroll/public>\
        Options -Indexes +FollowSymLinks\
        AllowOverride All\
        Require all granted\
        DirectoryIndex index.php index.html\
    </Directory>' "$APACHE_CONF"
                echo -e "${GREEN}✓ Directory block updated${NC}"
            fi
        else
            echo -e "${YELLOW}⚠ Directory block belum ada, menambahkan...${NC}"
            # Backup
            cp "$APACHE_CONF" "$APACHE_CONF.backup.$(date +%Y%m%d_%H%M%S)"
            
            # Add Directory block after Alias
            sed -i '/Alias \/hris-seven-payroll/a\
\
    <Directory /var/www/html/hris-seven-payroll/public>\
        Options -Indexes +FollowSymLinks\
        AllowOverride All\
        Require all granted\
        DirectoryIndex index.php index.html\
    </Directory>' "$APACHE_CONF"
            echo -e "${GREEN}✓ Directory block ditambahkan${NC}"
        fi
    else
        echo -e "${RED}❌ Apache Alias belum ada!${NC}"
        echo -e "${YELLOW}⚠ Menambahkan Alias...${NC}"
        # Backup
        cp "$APACHE_CONF" "$APACHE_CONF.backup.$(date +%Y%m%d_%H%M%S)"
        
        # Add Alias and Directory before ErrorLog
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
else
    echo -e "${RED}❌ ERROR: $APACHE_CONF tidak ditemukan!${NC}"
    exit 1
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
echo ""

echo -e "${YELLOW}[4/8] Ensuring mod_rewrite is enabled...${NC}"
a2enmod rewrite 2>/dev/null || echo "  (mod_rewrite already enabled)"
echo -e "${GREEN}✓ mod_rewrite enabled${NC}"
echo ""

echo -e "${YELLOW}[5/8] Fixing .env (remove trailing slash)...${NC}"
ENV_FILE="$PROJECT_PATH/.env"
if [ -f "$ENV_FILE" ]; then
    # Backup
    cp "$ENV_FILE" "$ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"
    
    # Update APP_URL dan ASSET_URL (tanpa trailing slash)
    sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|g' "$ENV_FILE"
    sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|g' "$ENV_FILE"
    # Remove trailing slash if any
    sed -i 's|APP_URL=\(.*\)/$|APP_URL=\1|g' "$ENV_FILE"
    sed -i 's|ASSET_URL=\(.*\)/$|ASSET_URL=\1|g' "$ENV_FILE"
    
    echo -e "${GREEN}✓ .env updated${NC}"
    echo -e "${BLUE}  APP_URL: $(grep "^APP_URL=" "$ENV_FILE" | cut -d'=' -f2)${NC}"
else
    echo -e "${RED}❌ .env file tidak ditemukan!${NC}"
fi
echo ""

echo -e "${YELLOW}[6/8] Clearing all cache...${NC}"
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

echo -e "${YELLOW}[7/8] Testing and restarting Apache...${NC}"
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

echo -e "${YELLOW}[8/8] Creating storage/logs if not exists...${NC}"
mkdir -p "$PROJECT_PATH/storage/logs"
touch "$PROJECT_PATH/storage/logs/laravel.log"
chown www-data:www-data "$PROJECT_PATH/storage/logs/laravel.log"
chmod 664 "$PROJECT_PATH/storage/logs/laravel.log"
echo -e "${GREEN}✓ storage/logs created${NC}"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  FIX SELESAI! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📋 Summary:${NC}"
echo -e "  ✓ .htaccess fixed (NO RewriteBase)"
echo -e "  ✓ Apache Alias configuration checked/updated"
echo -e "  ✓ mod_rewrite enabled"
echo -e "  ✓ .env updated (no trailing slash)"
echo -e "  ✓ Cache cleared"
echo -e "  ✓ Apache restarted"
echo -e "  ✓ storage/logs created"
echo ""
echo -e "${YELLOW}🌐 Test aplikasi:${NC}"
echo -e "  ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo ""
echo -e "${YELLOW}📝 Check logs jika masih error:${NC}"
echo -e "  ${BLUE}tail -f /var/log/apache2/error.log${NC}"
echo -e "  ${BLUE}tail -f $PROJECT_PATH/storage/logs/laravel.log${NC}"
echo ""
echo -e "${YELLOW}💡 Jika masih redirect loop:${NC}"
echo -e "  1. Check apakah ada .htaccess lain di parent directory"
echo -e "  2. Check Apache access log: ${BLUE}tail -f /var/log/apache2/access.log${NC}"
echo -e "  3. Test dengan curl: ${BLUE}curl -I http://192.168.10.40/hris-seven-payroll${NC}"
echo ""
