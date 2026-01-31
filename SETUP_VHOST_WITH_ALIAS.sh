#!/bin/bash

# Setup Virtual Host dengan Alias untuk akses di /hris-seven-payroll
# Usage: sudo ./SETUP_VHOST_WITH_ALIAS.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"
VHOST_CONF="/etc/apache2/sites-available/hris-seven-payroll.conf"

echo "=== Setting up Virtual Host with Alias ==="
echo ""

# 1. Disable old configurations
echo "[1/5] Disabling old configurations..."
a2disconf hris-seven-payroll-alias 2>/dev/null || true
a2dissite hris-seven-payroll.conf 2>/dev/null || true
a2ensite 000-default.conf 2>/dev/null || true
echo "✓ Old configurations disabled"
echo ""

# 2. Create Virtual Host dengan Alias di dalamnya
echo "[2/5] Creating Virtual Host with Alias..."
cat > "$VHOST_CONF" <<EOF
<VirtualHost *:80>
    ServerName 192.168.10.40
    ServerAlias hris-seven-payroll.local

    # Default DocumentRoot (untuk aplikasi lain)
    DocumentRoot /var/www/html

    # Alias untuk HRIS Seven Payroll
    Alias /hris-seven-payroll /var/www/html/hris-seven-payroll/public

    <Directory /var/www/html/hris-seven-payroll/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/hris-seven-payroll_error.log
    CustomLog \${APACHE_LOG_DIR}/hris-seven-payroll_access.log combined
</VirtualHost>
EOF
echo "✓ Virtual Host with Alias created"
echo ""

# 3. Enable Virtual Host
echo "[3/5] Enabling Virtual Host..."
a2ensite hris-seven-payroll.conf
a2dissite 000-default.conf 2>/dev/null || true
echo "✓ Virtual Host enabled"
echo ""

# 4. Fix .htaccess TANPA RewriteBase (karena pakai Alias)
echo "[4/5] Fixing .htaccess (NO RewriteBase for Alias)..."
cd "$PROJECT_PATH/public"
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
    echo "✗ ERROR: RewriteBase masih ada!"
    exit 1
fi
echo "✓ .htaccess fixed (NO RewriteBase)"
echo ""

# 5. Enable modules and restart
echo "[5/5] Enabling modules and restarting..."
a2enmod rewrite
a2enmod headers

if apache2ctl configtest; then
    systemctl restart apache2
    echo "✓ Apache restarted"
else
    echo "✗ Apache configuration test failed!"
    exit 1
fi
echo ""

echo "=== Setup Complete ==="
echo ""
echo "Virtual Host with Alias configured"
echo "Test: http://192.168.10.40/hris-seven-payroll"
echo ""
echo "Configuration:"
echo "  - Virtual Host: *:80"
echo "  - Alias: /hris-seven-payroll -> $PROJECT_PATH/public"
echo "  - .htaccess: NO RewriteBase (correct for Alias)"
echo ""









