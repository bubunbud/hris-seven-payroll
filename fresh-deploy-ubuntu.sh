#!/bin/bash

# Script Fresh Deploy HRIS Seven Payroll ke Ubuntu Server
# Usage: sudo ./fresh-deploy-ubuntu.sh
# Pastikan file sudah di-upload ke /tmp/hris-seven-payroll/ atau /tmp/hris-seven-payroll.zip

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
PROJECT_NAME="hris-seven-payroll"
PROJECT_PATH="/var/www/html/$PROJECT_NAME"
TEMP_PATH="/tmp/$PROJECT_NAME"
DB_NAME="hris_seven"
DB_USER="root"
DB_PASS="root123"
SERVER_IP="192.168.10.40"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}HRIS Seven Payroll - Fresh Deploy${NC}"
echo -e "${GREEN}========================================${NC}\n"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

# Step 1: Check if source files exist
echo -e "${YELLOW}[1/10] Checking source files...${NC}"
if [ -f "/tmp/${PROJECT_NAME}.zip" ]; then
    echo -e "${BLUE}ZIP file found, extracting...${NC}"
    cd /tmp
    unzip -q "${PROJECT_NAME}.zip" -d "$TEMP_PATH"
    echo -e "${GREEN}ZIP extracted${NC}\n"
elif [ -d "$TEMP_PATH" ]; then
    echo -e "${GREEN}Source folder found${NC}\n"
else
    echo -e "${RED}Source files not found at /tmp/${PROJECT_NAME}/ or /tmp/${PROJECT_NAME}.zip${NC}"
    echo -e "${YELLOW}Please upload files first${NC}"
    exit 1
fi

# Step 2: Remove old project (if exists)
echo -e "${YELLOW}[2/10] Removing old project (if exists)...${NC}"
if [ -d "$PROJECT_PATH" ]; then
    echo -e "${YELLOW}Backing up .env if exists...${NC}"
    if [ -f "$PROJECT_PATH/.env" ]; then
        cp "$PROJECT_PATH/.env" "/tmp/.env.backup.$(date +%Y%m%d_%H%M%S)"
    fi
    rm -rf "$PROJECT_PATH"
    echo -e "${GREEN}Old project removed${NC}\n"
else
    echo -e "${GREEN}No old project to remove${NC}\n"
fi

# Step 3: Create project directory and copy files
echo -e "${YELLOW}[3/10] Creating project directory and copying files...${NC}"
mkdir -p "$PROJECT_PATH"
cp -r "$TEMP_PATH"/* "$PROJECT_PATH/" 2>/dev/null || {
    echo -e "${RED}Failed to copy files${NC}"
    exit 1
}
echo -e "${GREEN}Files copied${NC}\n"

# Step 4: Set ownership and permissions
echo -e "${YELLOW}[4/10] Setting ownership and permissions...${NC}"
chown -R www-data:www-data "$PROJECT_PATH"
chmod -R 755 "$PROJECT_PATH"
chmod -R 775 "$PROJECT_PATH/storage"
chmod -R 775 "$PROJECT_PATH/bootstrap/cache"
if [ -d "$PROJECT_PATH/public/storage" ]; then
    chmod -R 775 "$PROJECT_PATH/public/storage"
fi
echo -e "${GREEN}Ownership and permissions set${NC}\n"

# Step 5: Install Composer dependencies
echo -e "${YELLOW}[5/10] Installing Composer dependencies...${NC}"
cd "$PROJECT_PATH"
if [ -f "composer.json" ]; then
    sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction || {
        echo -e "${YELLOW}Composer install with www-data failed, trying with sudo...${NC}"
        composer install --optimize-autoloader --no-dev --no-interaction
    }
    echo -e "${GREEN}Composer dependencies installed${NC}\n"
else
    echo -e "${YELLOW}composer.json not found, skipping...${NC}\n"
fi

# Step 6: Create .env file
echo -e "${YELLOW}[6/10] Creating .env file...${NC}"
if [ -f ".env.example" ]; then
    cp .env.example .env
    echo -e "${GREEN}.env created from .env.example${NC}"
else
    cat > .env <<EOF
APP_NAME="HRIS Seven Payroll"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://${SERVER_IP}/hris-seven-payroll

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

ASSET_URL=http://${SERVER_IP}/hris-seven-payroll
EOF
    echo -e "${GREEN}.env created${NC}"
fi

# Generate APP_KEY
sudo -u www-data php artisan key:generate --force || php artisan key:generate --force
echo -e "${GREEN}APP_KEY generated${NC}\n"

# Step 7: Run migrations
echo -e "${YELLOW}[7/10] Running database migrations...${NC}"
sudo -u www-data php artisan migrate --force || {
    echo -e "${YELLOW}Migration with www-data failed, trying with sudo...${NC}"
    php artisan migrate --force
}
echo -e "${GREEN}Migrations completed${NC}\n"

# Step 8: Run seeders
echo -e "${YELLOW}[8/10] Running database seeders...${NC}"
if [ -f "database/seeders/RolePermissionSeeder.php" ]; then
    sudo -u www-data php artisan db:seed --class=RolePermissionSeeder --force || {
        php artisan db:seed --class=RolePermissionSeeder --force
    }
    echo -e "${GREEN}RolePermissionSeeder completed${NC}"
fi

if [ -f "database/seeders/AdminUserRoleSeeder.php" ]; then
    sudo -u www-data php artisan db:seed --class=AdminUserRoleSeeder --force || {
        php artisan db:seed --class=AdminUserRoleSeeder --force
    }
    echo -e "${GREEN}AdminUserRoleSeeder completed${NC}"
fi
echo ""

# Step 9: Setup Apache
echo -e "${YELLOW}[9/10] Setting up Apache...${NC}"

# Create Apache alias configuration
ALIAS_CONF="/etc/apache2/conf-available/${PROJECT_NAME}-alias.conf"
cat > "$ALIAS_CONF" <<EOF
# Alias untuk HRIS Seven Payroll
Alias /${PROJECT_NAME} ${PROJECT_PATH}/public

<Directory ${PROJECT_PATH}/public>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF

# Enable configuration
a2enconf "${PROJECT_NAME}-alias" 2>/dev/null || echo "Configuration might already be enabled"
a2enmod rewrite

# Update .htaccess (without RewriteBase for Alias)
cat > "${PROJECT_PATH}/public/.htaccess" <<'EOF'
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
EOF

# Test Apache configuration
if apache2ctl configtest; then
    systemctl restart apache2
    echo -e "${GREEN}Apache configured and restarted${NC}\n"
else
    echo -e "${RED}Apache configuration test failed${NC}"
    exit 1
fi

# Step 10: Optimize Laravel
echo -e "${YELLOW}[10/10] Optimizing Laravel...${NC}"
cd "$PROJECT_PATH"

# Clear cache
sudo -u www-data php artisan config:clear 2>/dev/null || php artisan config:clear
sudo -u www-data php artisan route:clear 2>/dev/null || php artisan route:clear
sudo -u www-data php artisan view:clear 2>/dev/null || php artisan view:clear
sudo -u www-data php artisan cache:clear 2>/dev/null || php artisan cache:clear

# Re-cache
sudo -u www-data php artisan config:cache 2>/dev/null || php artisan config:cache
sudo -u www-data php artisan route:cache 2>/dev/null || php artisan route:cache
sudo -u www-data php artisan view:cache 2>/dev/null || php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize --no-interaction

echo -e "${GREEN}Laravel optimized${NC}\n"

# Final message
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Fresh Deploy Completed Successfully!${NC}"
echo -e "${GREEN}========================================${NC}\n"

echo -e "Project URL: ${YELLOW}http://${SERVER_IP}/${PROJECT_NAME}${NC}"
echo -e "Login URL: ${YELLOW}http://${SERVER_IP}/${PROJECT_NAME}/login${NC}"
echo -e "Project Path: ${YELLOW}${PROJECT_PATH}${NC}\n"

echo -e "${YELLOW}Default Login Credentials:${NC}"
echo -e "Email: ${GREEN}admin@hris.com${NC}"
echo -e "Password: ${GREEN}admin123${NC}\n"

echo -e "${YELLOW}Next Steps:${NC}"
echo -e "1. Test access: ${GREEN}http://${SERVER_IP}/${PROJECT_NAME}${NC}"
echo -e "2. Test login: ${GREEN}http://${SERVER_IP}/${PROJECT_NAME}/login${NC}"
echo -e "3. Check logs if issues: ${GREEN}tail -f ${PROJECT_PATH}/storage/logs/laravel.log${NC}\n"

echo -e "${YELLOW}Cleanup temporary files:${NC}"
echo -e "sudo rm -rf ${TEMP_PATH}*${NC}\n"









