#!/bin/bash

# Script untuk fix Apache Alias configuration
# Usage: sudo ./fix-apache-alias-config.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"
ALIAS_CONF="/etc/apache2/conf-available/hris-seven-payroll-alias.conf"

echo "=== Fixing Apache Alias Configuration ==="
echo ""

# 1. Disable Virtual Host jika ada
echo "[1/5] Disabling Virtual Host (if exists)..."
if [ -f "/etc/apache2/sites-enabled/hris-seven-payroll.conf" ]; then
    a2dissite hris-seven-payroll.conf
    echo "✓ Virtual Host disabled"
else
    echo "✓ No Virtual Host to disable"
fi

# Re-enable default site jika perlu
if [ ! -f "/etc/apache2/sites-enabled/000-default.conf" ]; then
    a2ensite 000-default.conf
    echo "✓ Default site enabled"
fi
echo ""

# 2. Create/Update Apache Alias configuration
echo "[2/5] Creating/Updating Apache Alias configuration..."
cat > "$ALIAS_CONF" <<EOF
# Alias untuk HRIS Seven Payroll
Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

<Directory /var/www/html/hris-seven-payroll/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
    
    # Prevent directory listing
    DirectoryIndex index.php index.html
</Directory>
EOF

# Enable alias configuration
a2enconf hris-seven-payroll-alias 2>/dev/null || echo "Configuration might already be enabled"
echo "✓ Alias configuration created/updated"
echo ""

# 3. Fix .htaccess (no RewriteBase)
echo "[3/5] Fixing .htaccess..."
cd "$PROJECT_PATH/public"

# Backup
cp .htaccess .htaccess.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# Create .htaccess without RewriteBase
cat > .htaccess <<'EOF'
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

# Verify no RewriteBase
if grep -q "RewriteBase" .htaccess; then
    echo "✗ ERROR: RewriteBase still found!"
    exit 1
else
    echo "✓ .htaccess fixed (no RewriteBase)"
fi
echo ""

# 4. Enable required Apache modules
echo "[4/5] Enabling Apache modules..."
a2enmod rewrite
a2enmod headers
echo "✓ Apache modules enabled"
echo ""

# 5. Test and restart Apache
echo "[5/5] Testing Apache configuration..."
if apache2ctl configtest; then
    echo "✓ Apache configuration is valid"
    systemctl restart apache2
    echo "✓ Apache restarted"
else
    echo "✗ Apache configuration test failed!"
    exit 1
fi
echo ""

echo "=== Fix Complete ==="
echo ""
echo "Configuration:"
echo "  - Apache Alias: /hris-seven-payroll -> $PROJECT_PATH/public"
echo "  - .htaccess: No RewriteBase (correct for Alias)"
echo ""
echo "Test access: http://192.168.10.40/hris-seven-payroll"
echo "Test login: http://192.168.10.40/hris-seven-payroll/login"
echo ""
echo "If still having issues:"
echo "  1. Check: cat $ALIAS_CONF"
echo "  2. Check: cat $PROJECT_PATH/public/.htaccess | grep RewriteBase"
echo "  3. Check logs: tail -f /var/log/apache2/error.log"
echo ""









