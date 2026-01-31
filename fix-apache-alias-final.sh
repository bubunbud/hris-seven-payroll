#!/bin/bash

# Script untuk fix Apache Alias dan .htaccess - Final
# Usage: sudo ./fix-apache-alias-final.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"
ALIAS_CONF="/etc/apache2/conf-available/hris-seven-payroll-alias.conf"

echo "=== Fixing Apache Alias and .htaccess - Final ==="
echo ""

# 1. Create Apache Alias configuration
echo "[1/4] Creating Apache Alias configuration..."
cat > "$ALIAS_CONF" <<'EOF'
# Alias untuk HRIS Seven Payroll
Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

<Directory /var/www/html/hris-seven-payroll/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
    DirectoryIndex index.php index.html
</Directory>
EOF

# Enable alias configuration
a2enconf hris-seven-payroll-alias
echo "✓ Apache Alias configuration created and enabled"
echo ""

# 2. Fix .htaccess (NO RewriteBase for Alias)
echo "[2/4] Fixing .htaccess (removing RewriteBase)..."
cd "$PROJECT_PATH/public"

# Backup
cp .htaccess .htaccess.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# Create .htaccess WITHOUT RewriteBase (karena pakai Alias)
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
if grep -qi "RewriteBase" .htaccess; then
    echo "✗ ERROR: RewriteBase still found!"
    exit 1
else
    echo "✓ .htaccess fixed (no RewriteBase)"
fi
echo ""

# 3. Enable required modules
echo "[3/4] Enabling Apache modules..."
a2enmod rewrite
a2enmod headers
echo "✓ Apache modules enabled"
echo ""

# 4. Test and restart
echo "[4/4] Testing Apache configuration..."
if apache2ctl configtest; then
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
echo ""
echo "Verify:"
echo "  cat /etc/apache2/conf-enabled/hris-seven-payroll-alias.conf"
echo "  cat $PROJECT_PATH/public/.htaccess | grep RewriteBase"
echo "  (should show no RewriteBase)"
echo ""









