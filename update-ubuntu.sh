#!/bin/bash

# Script Update HRIS Seven Payroll di Ubuntu Server
# Usage: sudo ./update-ubuntu.sh

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="hris-seven-payroll"
PROJECT_PATH="/var/www/html/$PROJECT_NAME"
TEMP_PATH="/tmp/hris-seven-payroll-update"
BACKUP_DIR="$HOME/backup_hris_$(date +%Y%m%d_%H%M%S)"
DB_NAME="hris_seven"
DB_USER="root"
DB_PASS="root123"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}HRIS Seven Payroll - Update Script${NC}"
echo -e "${GREEN}========================================${NC}\n"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

# Step 1: Create backup
echo -e "${YELLOW}[1/10] Creating backup...${NC}"
mkdir -p "$BACKUP_DIR"

# Backup database
echo -e "${BLUE}Backing up database...${NC}"
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/database_backup.sql" 2>/dev/null || {
    echo -e "${YELLOW}Database backup might have failed, but continuing...${NC}"
}

# Backup .env
if [ -f "$PROJECT_PATH/.env" ]; then
    cp "$PROJECT_PATH/.env" "$BACKUP_DIR/.env.backup"
    echo -e "${GREEN}.env backed up${NC}"
fi

# Backup storage (if exists and has files)
if [ -d "$PROJECT_PATH/storage" ]; then
    tar -czf "$BACKUP_DIR/storage_backup.tar.gz" -C "$PROJECT_PATH" storage/ 2>/dev/null || true
    echo -e "${GREEN}Storage backed up${NC}"
fi

echo -e "${GREEN}Backup created at: $BACKUP_DIR${NC}\n"

# Step 2: Check if update files exist
echo -e "${YELLOW}[2/10] Checking update files...${NC}"
if [ ! -d "$TEMP_PATH" ]; then
    echo -e "${RED}Update files not found at: $TEMP_PATH${NC}"
    echo -e "${YELLOW}Please upload files to $TEMP_PATH first${NC}"
    exit 1
fi

# Check important files
if [ ! -f "$TEMP_PATH/app/Http/Controllers/AuthController.php" ]; then
    echo -e "${YELLOW}Warning: AuthController.php not found${NC}"
fi

if [ ! -f "$TEMP_PATH/app/Models/Role.php" ]; then
    echo -e "${YELLOW}Warning: Role.php not found${NC}"
fi

echo -e "${GREEN}Update files found${NC}\n"

# Step 3: Backup current files
echo -e "${YELLOW}[3/10] Backing up current files...${NC}"
cd "$PROJECT_PATH"
cp -r app "$BACKUP_DIR/app_backup" 2>/dev/null || true
cp -r routes "$BACKUP_DIR/routes_backup" 2>/dev/null || true
cp -r resources "$BACKUP_DIR/resources_backup" 2>/dev/null || true
echo -e "${GREEN}Current files backed up${NC}\n"

# Step 4: Copy new files (exclude .env, storage, vendor)
echo -e "${YELLOW}[4/10] Copying new files...${NC}"
cd "$PROJECT_PATH"

# Use rsync if available, otherwise use cp
if command -v rsync &> /dev/null; then
    rsync -av --exclude '.env' \
              --exclude 'storage' \
              --exclude 'vendor' \
              --exclude 'node_modules' \
              --exclude '.git' \
              "$TEMP_PATH/" ./
    echo -e "${GREEN}Files copied using rsync${NC}"
else
    echo -e "${YELLOW}rsync not found, using manual copy...${NC}"
    # Manual copy (exclude sensitive folders)
    for dir in app bootstrap config database public resources routes; do
        if [ -d "$TEMP_PATH/$dir" ]; then
            cp -r "$TEMP_PATH/$dir"/* "$dir/" 2>/dev/null || true
        fi
    done
    
    # Copy root files
    for file in artisan composer.json composer.lock package.json vite.config.js; do
        if [ -f "$TEMP_PATH/$file" ]; then
            cp "$TEMP_PATH/$file" ./
        fi
    done
    echo -e "${GREEN}Files copied manually${NC}"
fi
echo ""

# Step 5: Set ownership and permissions
echo -e "${YELLOW}[5/10] Setting ownership and permissions...${NC}"
chown -R www-data:www-data "$PROJECT_PATH"
chmod -R 755 "$PROJECT_PATH"
chmod -R 775 "$PROJECT_PATH/storage"
chmod -R 775 "$PROJECT_PATH/bootstrap/cache"
if [ -d "$PROJECT_PATH/storage/app/public" ]; then
    chmod -R 775 "$PROJECT_PATH/storage/app/public"
fi
echo -e "${GREEN}Ownership and permissions set${NC}\n"

# Step 6: Update Composer dependencies
echo -e "${YELLOW}[6/10] Updating Composer dependencies...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction || {
    echo -e "${YELLOW}Composer install with www-data failed, trying with sudo...${NC}"
    composer install --optimize-autoloader --no-dev --no-interaction
}
echo -e "${GREEN}Composer dependencies updated${NC}\n"

# Step 7: Run migrations
echo -e "${YELLOW}[7/10] Running database migrations...${NC}"
cd "$PROJECT_PATH"
sudo -u www-data php artisan migrate --force || {
    echo -e "${YELLOW}Migration with www-data failed, trying with sudo...${NC}"
    php artisan migrate --force
}
echo -e "${GREEN}Migrations completed${NC}\n"

# Step 8: Run seeders
echo -e "${YELLOW}[8/10] Running database seeders...${NC}"
cd "$PROJECT_PATH"

# Run RolePermissionSeeder
if [ -f "database/seeders/RolePermissionSeeder.php" ]; then
    sudo -u www-data php artisan db:seed --class=RolePermissionSeeder --force || {
        php artisan db:seed --class=RolePermissionSeeder --force
    }
    echo -e "${GREEN}RolePermissionSeeder completed${NC}"
fi

# Run AdminUserRoleSeeder
if [ -f "database/seeders/AdminUserRoleSeeder.php" ]; then
    sudo -u www-data php artisan db:seed --class=AdminUserRoleSeeder --force || {
        php artisan db:seed --class=AdminUserRoleSeeder --force
    }
    echo -e "${GREEN}AdminUserRoleSeeder completed${NC}"
fi
echo ""

# Step 9: Clear and re-cache Laravel
echo -e "${YELLOW}[9/10] Clearing and re-caching Laravel...${NC}"
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

echo -e "${GREEN}Laravel cache cleared and re-cached${NC}\n"

# Step 10: Optimize autoloader
echo -e "${YELLOW}[10/10] Optimizing autoloader...${NC}"
cd "$PROJECT_PATH"
composer dump-autoload --optimize --no-interaction
echo -e "${GREEN}Autoloader optimized${NC}\n"

# Final message
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Update completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}\n"

echo -e "Backup location: ${YELLOW}$BACKUP_DIR${NC}"
echo -e "Project URL: ${YELLOW}http://192.168.10.40/hris-seven-payroll${NC}\n"

echo -e "${YELLOW}Next steps:${NC}"
echo -e "1. Test access: ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo -e "2. Test login: ${GREEN}http://192.168.10.40/hris-seven-payroll/login${NC}"
echo -e "3. Check logs: ${GREEN}tail -f $PROJECT_PATH/storage/logs/laravel.log${NC}"
echo -e "4. Cleanup temp files: ${GREEN}sudo rm -rf $TEMP_PATH${NC}\n"

echo -e "${YELLOW}If there are issues, restore from backup:${NC}"
echo -e "Database: ${GREEN}mysql -u $DB_USER -p$DB_PASS $DB_NAME < $BACKUP_DIR/database_backup.sql${NC}"
echo -e ".env: ${GREEN}cp $BACKUP_DIR/.env.backup $PROJECT_PATH/.env${NC}\n"









