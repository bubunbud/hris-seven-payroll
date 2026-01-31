#!/bin/bash

# Script untuk setup Virtual Host (seperti deploy pertama)
# Usage: sudo ./setup-virtual-host.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"
VHOST_CONF="/etc/apache2/sites-available/hris-seven-payroll.conf"

echo "=== Setting up Virtual Host (Like First Deploy) ==="
echo ""

# 1. Disable Alias
echo "[1/6] Disabling Apache Alias..."
a2disconf hris-seven-payroll-alias 2>/dev/null || true
echo "✓ Alias disabled"
echo ""

# 2. Create Virtual Host
echo "[2/6] Creating Virtual Host..."
cat > "$VHOST_CONF" <<EOF
<VirtualHost *:80>
    ServerName 192.168.10.40
    ServerAlias hris-seven-payroll.local

    # DocumentRoot ke public folder
    DocumentRoot /var/www/html/hris-seven-payroll/public

    <Directory /var/www/html/hris-seven-payroll/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/hris-seven-payroll_error.log
    CustomLog \${APACHE_LOG_DIR}/hris-seven-payroll_access.log combined
</VirtualHost>
EOF
echo "✓ Virtual Host created"
echo ""

# 3. Enable Virtual Host
echo "[3/6] Enabling Virtual Host..."
a2ensite hris-seven-payroll.conf
echo "✓ Virtual Host enabled"
echo ""

# 4. Fix .htaccess dengan RewriteBase (untuk Virtual Host)
echo "[4/6] Fixing .htaccess with RewriteBase..."
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
echo "✓ .htaccess updated (with RewriteBase for Virtual Host)"
echo ""

# 5. Enable modules
echo "[5/6] Enabling Apache modules..."
a2enmod rewrite
a2enmod headers
echo "✓ Modules enabled"
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

echo "=== Setup Complete ==="
echo ""
echo "Virtual Host configured (like first deploy)"
echo "Test: http://192.168.10.40/hris-seven-payroll"
echo ""
echo "Note: With Virtual Host, access should be at root or via ServerAlias"
echo "If you need /hris-seven-payroll path, we need different approach"
echo ""









