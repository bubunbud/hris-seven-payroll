#!/bin/bash

# Script untuk reset ke konfigurasi yang bekerja (seperti deploy pertama)
# Usage: sudo ./RESET_TO_WORKING_CONFIG.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo "=== Reset to Working Configuration (Like First Deploy) ==="
echo ""

# 1. Disable semua konfigurasi yang ada
echo "[1/6] Disabling all existing configurations..."
a2dissite hris-seven-payroll.conf 2>/dev/null || true
a2disconf hris-seven-payroll-alias 2>/dev/null || true
a2ensite 000-default.conf 2>/dev/null || true
echo "✓ Configurations disabled"
echo ""

# 2. Gunakan script setup-subfolder.sh yang sudah ada
echo "[2/6] Running setup-subfolder.sh (proven working script)..."
if [ -f "$PROJECT_PATH/setup-subfolder.sh" ]; then
    cd "$PROJECT_PATH"
    chmod +x setup-subfolder.sh
    ./setup-subfolder.sh
    echo "✓ setup-subfolder.sh completed"
else
    echo "⚠ setup-subfolder.sh not found, creating manually..."
    
    # Create Alias
    cat > /etc/apache2/conf-available/hris-seven-payroll-alias.conf <<EOF
# Alias untuk HRIS Seven Payroll
Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

<Directory /var/www/html/hris-seven-payroll/public>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
    
    # Enable
    a2enconf hris-seven-payroll-alias
    a2enmod rewrite
    
    # Fix .htaccess dengan RewriteBase (seperti deploy pertama)
    cd "$PROJECT_PATH/public"
    cat > .htaccess <<'EOF'
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
    
    echo "✓ Configuration created manually"
fi
echo ""

# 3. Update .env
echo "[3/6] Updating .env..."
cd "$PROJECT_PATH"
if [ -f ".env" ]; then
    sed -i 's|^APP_URL=.*|APP_URL=http://192.168.10.40/hris-seven-payroll|' .env
    if ! grep -q "^ASSET_URL=" .env; then
        echo "ASSET_URL=http://192.168.10.40/hris-seven-payroll" >> .env
    else
        sed -i 's|^ASSET_URL=.*|ASSET_URL=http://192.168.10.40/hris-seven-payroll|' .env
    fi
    echo "✓ .env updated"
else
    echo "⚠ .env not found"
fi
echo ""

# 4. Clear Laravel cache
echo "[4/6] Clearing Laravel cache..."
cd "$PROJECT_PATH"
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
echo "✓ Cache cleared"
echo ""

# 5. Fix permissions
echo "[5/6] Fixing permissions..."
chown -R www-data:www-data "$PROJECT_PATH"
chmod -R 755 "$PROJECT_PATH"
chmod -R 775 "$PROJECT_PATH/storage"
chmod -R 775 "$PROJECT_PATH/bootstrap/cache"
echo "✓ Permissions fixed"
echo ""

# 6. Test and restart
echo "[6/6] Testing and restarting Apache..."
if apache2ctl configtest; then
    systemctl restart apache2
    echo "✓ Apache restarted"
else
    echo "✗ Apache configuration test failed!"
    exit 1
fi
echo ""

echo "=== Reset Complete ==="
echo ""
echo "Configuration reset to working state (like first deploy)"
echo "Test: http://192.168.10.40/hris-seven-payroll"
echo ""
echo "If still error, check:"
echo "  1. cat $PROJECT_PATH/public/.htaccess"
echo "  2. cat /etc/apache2/conf-enabled/hris-seven-payroll-alias.conf"
echo "  3. tail -f /var/log/apache2/error.log"
echo ""









