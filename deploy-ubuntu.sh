#!/bin/bash

# Script Deploy HRIS Seven Payroll ke Ubuntu Server
# Usage: ./deploy-ubuntu.sh

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="hris-seven-payroll"
PROJECT_PATH="/var/www/html/$PROJECT_NAME"
DB_NAME="hris_seven"
DB_USER="root"
DB_PASS="root123"
SERVER_IP="192.168.10.40"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}HRIS Seven Payroll - Deploy Script${NC}"
echo -e "${GREEN}========================================${NC}\n"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

# Step 1: Check PHP version
echo -e "${YELLOW}[1/10] Checking PHP installation...${NC}"
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Please install PHP 8.1 first.${NC}"
    exit 1
fi
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
echo -e "${GREEN}PHP version: $PHP_VERSION${NC}\n"

# Step 2: Check Composer
echo -e "${YELLOW}[2/10] Checking Composer installation...${NC}"
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Composer not found. Installing Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi
echo -e "${GREEN}Composer installed${NC}\n"

# Step 3: Check if project directory exists
echo -e "${YELLOW}[3/10] Checking project directory...${NC}"
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}Project directory not found: $PROJECT_PATH${NC}"
    echo -e "${YELLOW}Please upload project files first to: $PROJECT_PATH${NC}"
    exit 1
fi
echo -e "${GREEN}Project directory found${NC}\n"

# Step 4: Set ownership
echo -e "${YELLOW}[4/10] Setting ownership...${NC}"
chown -R www-data:www-data "$PROJECT_PATH"
echo -e "${GREEN}Ownership set to www-data${NC}\n"

# Step 5: Set permissions
echo -e "${YELLOW}[5/10] Setting permissions...${NC}"
chmod -R 755 "$PROJECT_PATH"
chmod -R 775 "$PROJECT_PATH/storage"
chmod -R 775 "$PROJECT_PATH/bootstrap/cache"
if [ -d "$PROJECT_PATH/storage/app/public" ]; then
    chmod -R 775 "$PROJECT_PATH/storage/app/public"
fi
echo -e "${GREEN}Permissions set${NC}\n"

# Step 6: Install Composer dependencies
echo -e "${YELLOW}[6/10] Installing Composer dependencies...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction
echo -e "${GREEN}Composer dependencies installed${NC}\n"

# Step 7: Check .env file
echo -e "${YELLOW}[7/10] Checking .env file...${NC}"
if [ ! -f "$PROJECT_PATH/.env" ]; then
    if [ -f "$PROJECT_PATH/.env.example" ]; then
        cp "$PROJECT_PATH/.env.example" "$PROJECT_PATH/.env"
        echo -e "${GREEN}.env file created from .env.example${NC}"
    else
        echo -e "${YELLOW}Creating .env file...${NC}"
        cat > "$PROJECT_PATH/.env" <<EOF
APP_NAME="HRIS Seven Payroll"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://$SERVER_IP

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASS

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOF
        echo -e "${GREEN}.env file created${NC}"
    fi
    # Generate app key
    sudo -u www-data php artisan key:generate --force
    echo -e "${GREEN}Application key generated${NC}"
else
    echo -e "${GREEN}.env file exists${NC}"
fi
echo ""

# Step 8: Create database if not exists
echo -e "${YELLOW}[8/10] Checking database...${NC}"
mysql -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
    echo -e "${YELLOW}Database might already exist or MySQL connection failed${NC}"
}
echo -e "${GREEN}Database checked${NC}\n"

# Step 9: Run migrations
echo -e "${YELLOW}[9/10] Running database migrations...${NC}"
sudo -u www-data php artisan migrate --force
echo -e "${GREEN}Migrations completed${NC}\n"

# Step 10: Optimize Laravel
echo -e "${YELLOW}[10/10] Optimizing Laravel...${NC}"
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
echo -e "${GREEN}Laravel optimized${NC}\n"

# Final message
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Deployment completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}\n"
echo -e "Project URL: ${YELLOW}http://$SERVER_IP${NC}"
echo -e "Project Path: ${YELLOW}$PROJECT_PATH${NC}\n"
echo -e "${YELLOW}Next steps:${NC}"
echo -e "1. Configure Apache virtual host (see DEPLOY_UBUNTU.md)"
echo -e "2. Enable mod_rewrite: ${GREEN}sudo a2enmod rewrite${NC}"
echo -e "3. Restart Apache: ${GREEN}sudo systemctl restart apache2${NC}"
echo -e "4. Test access: ${GREEN}http://$SERVER_IP${NC}\n"

