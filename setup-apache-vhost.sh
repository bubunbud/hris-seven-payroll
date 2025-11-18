#!/bin/bash

# Script untuk setup Apache Virtual Host untuk HRIS Seven Payroll
# Usage: sudo ./setup-apache-vhost.sh

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

PROJECT_NAME="hris-seven-payroll"
PROJECT_PATH="/var/www/html/$PROJECT_NAME"
SERVER_IP="192.168.10.40"
VHOST_FILE="/etc/apache2/sites-available/$PROJECT_NAME.conf"

echo -e "${GREEN}Setting up Apache Virtual Host...${NC}\n"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

# Create virtual host configuration
echo -e "${YELLOW}Creating virtual host configuration...${NC}"
cat > "$VHOST_FILE" <<EOF
<VirtualHost *:80>
    ServerName $SERVER_IP
    ServerAlias $PROJECT_NAME.local
    
    DocumentRoot $PROJECT_PATH/public

    <Directory $PROJECT_PATH/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"

    ErrorLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_error.log
    CustomLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_access.log combined
</VirtualHost>
EOF

echo -e "${GREEN}Virtual host configuration created${NC}\n"

# Enable required Apache modules
echo -e "${YELLOW}Enabling Apache modules...${NC}"
a2enmod rewrite
a2enmod headers
echo -e "${GREEN}Modules enabled${NC}\n"

# Enable virtual host
echo -e "${YELLOW}Enabling virtual host...${NC}"
a2ensite "$PROJECT_NAME.conf"
echo -e "${GREEN}Virtual host enabled${NC}\n"

# Disable default site (optional)
read -p "Disable default Apache site? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    a2dissite 000-default.conf
    echo -e "${GREEN}Default site disabled${NC}\n"
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
    echo -e "${GREEN}Apache Virtual Host setup completed!${NC}"
    echo -e "${GREEN}========================================${NC}\n"
    echo -e "Access your application at: ${YELLOW}http://$SERVER_IP${NC}\n"
else
    echo -e "${RED}Configuration test failed. Please check the configuration.${NC}"
    exit 1
fi

