#!/bin/bash

# Script untuk setup HRIS Seven Payroll sebagai subfolder
# Usage: sudo ./setup-subfolder.sh

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

PROJECT_NAME="hris-seven-payroll"
PROJECT_PATH="/var/www/html/$PROJECT_NAME"
PUBLIC_PATH="$PROJECT_PATH/public"
ALIAS_PATH="/hris-seven-payroll"
CONF_FILE="/etc/apache2/conf-available/${PROJECT_NAME}-alias.conf"

echo -e "${GREEN}Setting up HRIS Seven Payroll as subfolder...${NC}\n"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

# Step 1: Disable existing virtual host if exists
echo -e "${YELLOW}[1/7] Disabling existing virtual host...${NC}"
if [ -f "/etc/apache2/sites-enabled/${PROJECT_NAME}.conf" ]; then
    a2dissite "${PROJECT_NAME}.conf"
    echo -e "${GREEN}Virtual host disabled${NC}\n"
else
    echo -e "${GREEN}No virtual host to disable${NC}\n"
fi

# Step 2: Check if project exists
echo -e "${YELLOW}[2/7] Checking project directory...${NC}"
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}Project directory not found: $PROJECT_PATH${NC}"
    exit 1
fi
echo -e "${GREEN}Project directory found${NC}\n"

# Step 3: Update .htaccess
echo -e "${YELLOW}[3/7] Updating .htaccess...${NC}"
if [ -f "$PUBLIC_PATH/.htaccess" ]; then
    # Backup existing .htaccess
    cp "$PUBLIC_PATH/.htaccess" "$PUBLIC_PATH/.htaccess.backup"
    
    # Create new .htaccess with RewriteBase
    cat > "$PUBLIC_PATH/.htaccess" <<'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On
    RewriteBase /hris-seven-payroll/public/

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
    echo -e "${GREEN}.htaccess updated${NC}\n"
else
    echo -e "${YELLOW}.htaccess not found, creating new one...${NC}"
    mkdir -p "$PUBLIC_PATH"
    cat > "$PUBLIC_PATH/.htaccess" <<'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On
    RewriteBase /hris-seven-payroll/public/

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
    echo -e "${GREEN}.htaccess created${NC}\n"
fi

# Step 4: Create Apache alias configuration
echo -e "${YELLOW}[4/7] Creating Apache alias configuration...${NC}"
cat > "$CONF_FILE" <<EOF
# Alias untuk HRIS Seven Payroll
Alias ${ALIAS_PATH} ${PUBLIC_PATH}

<Directory ${PUBLIC_PATH}>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
echo -e "${GREEN}Apache alias configuration created${NC}\n"

# Step 5: Enable Apache modules and configuration
echo -e "${YELLOW}[5/7] Enabling Apache modules...${NC}"
a2enmod rewrite
a2enconf "${PROJECT_NAME}-alias"
echo -e "${GREEN}Modules and configuration enabled${NC}\n"

# Step 6: Update .env file
echo -e "${YELLOW}[6/7] Updating .env file...${NC}"
if [ -f "$PROJECT_PATH/.env" ]; then
    # Update APP_URL
    sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|' "$PROJECT_PATH/.env"
    
    # Add ASSET_URL if not exists
    if ! grep -q "^ASSET_URL=" "$PROJECT_PATH/.env"; then
        echo "ASSET_URL=http://192.168.10.40/hris-seven-payroll" >> "$PROJECT_PATH/.env"
    else
        sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|' "$PROJECT_PATH/.env"
    fi
    
    echo -e "${GREEN}.env file updated${NC}\n"
else
    echo -e "${YELLOW}.env file not found, skipping...${NC}\n"
fi

# Step 7: Clear Laravel cache
echo -e "${YELLOW}[7/7] Clearing Laravel cache...${NC}"
cd "$PROJECT_PATH"
if [ -f "artisan" ]; then
    sudo -u www-data php artisan config:clear 2>/dev/null || true
    sudo -u www-data php artisan route:clear 2>/dev/null || true
    sudo -u www-data php artisan view:clear 2>/dev/null || true
    sudo -u www-data php artisan cache:clear 2>/dev/null || true
    echo -e "${GREEN}Laravel cache cleared${NC}\n"
else
    echo -e "${YELLOW}artisan not found, skipping cache clear...${NC}\n"
fi

# Test Apache configuration
echo -e "${YELLOW}Testing Apache configuration...${NC}"
if apache2ctl configtest; then
    echo -e "${GREEN}Configuration test passed${NC}\n"
    
    # Restart Apache
    echo -e "${YELLOW}Restarting Apache...${NC}"
    systemctl restart apache2
    echo -e "${GREEN}Apache restarted${NC}\n"
    
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}Setup completed successfully!${NC}"
    echo -e "${GREEN}========================================${NC}\n"
    echo -e "Access your application at: ${YELLOW}http://192.168.10.40/hris-seven-payroll${NC}\n"
    echo -e "${YELLOW}Next steps:${NC}"
    echo -e "1. Test access: ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
    echo -e "2. Verify other apps still work: ${GREEN}http://192.168.10.40/seven${NC}"
    echo -e "3. Check asset loading (CSS, JS, images)\n"
else
    echo -e "${RED}Configuration test failed. Please check the configuration.${NC}"
    exit 1
fi

