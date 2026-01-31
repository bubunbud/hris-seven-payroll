#!/bin/bash

# Script untuk fix Apache redirect loop
# Usage: sudo ./fix-redirect-loop.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"
ALIAS_CONF="/etc/apache2/conf-available/hris-seven-payroll-alias.conf"

echo "=== Fixing Apache Redirect Loop ==="
echo ""

# 1. Check Apache Alias configuration
echo "[1/4] Checking Apache Alias configuration..."
if [ -f "$ALIAS_CONF" ]; then
    echo "✓ Alias configuration found"
    cat "$ALIAS_CONF"
    echo ""
else
    echo "✗ Alias configuration NOT found!"
    echo "Creating alias configuration..."
    cat > "$ALIAS_CONF" <<EOF
# Alias untuk HRIS Seven Payroll
Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

<Directory /var/www/html/hris-seven-payroll/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
    a2enconf hris-seven-payroll-alias
    echo "✓ Alias configuration created and enabled"
fi
echo ""

# 2. Fix .htaccess - Remove RewriteBase completely
echo "[2/4] Fixing .htaccess (removing RewriteBase)..."
cd "$PROJECT_PATH/public"

# Backup
cp .htaccess .htaccess.backup.$(date +%Y%m%d_%H%M%S)

# Create minimal .htaccess without RewriteBase
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

echo "✓ .htaccess fixed (no RewriteBase)"
echo ""

# 3. Verify .htaccess
echo "[3/4] Verifying .htaccess..."
if grep -q "RewriteBase" .htaccess; then
    echo "✗ ERROR: RewriteBase still found in .htaccess!"
    exit 1
else
    echo "✓ No RewriteBase found (correct)"
fi
echo ""

# 4. Test Apache configuration and restart
echo "[4/4] Testing Apache configuration..."
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
echo "The redirect loop should be fixed now."
echo "Test access: http://192.168.10.40/hris-seven-payroll"
echo ""
echo "If still having issues, try:"
echo "  1. Clear browser cache"
echo "  2. Try incognito/private mode"
echo "  3. Check: tail -f /var/log/apache2/error.log"
echo ""









