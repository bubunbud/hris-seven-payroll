#!/bin/bash

# Script FINAL untuk fix redirect loop
# Masalah: RewriteBase di .htaccess + Apache Alias = redirect loop
# Solusi: HAPUS RewriteBase karena pakai Alias

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo "=== FIXING REDIRECT LOOP - FINAL SOLUTION ==="
echo ""
echo "Masalah: RewriteBase di .htaccess menyebabkan loop dengan Apache Alias"
echo "Solusi: Hapus RewriteBase (Alias sudah handle base path)"
echo ""

cd "$PROJECT_PATH/public"

# Backup
cp .htaccess .htaccess.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# Buat .htaccess TANPA RewriteBase
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

# Verify NO RewriteBase
if grep -qi "RewriteBase" .htaccess; then
    echo "✗ ERROR: RewriteBase masih ada!"
    exit 1
fi

echo "✓ .htaccess fixed (NO RewriteBase)"
echo ""

# Restart Apache
cd "$PROJECT_PATH"
systemctl restart apache2
echo "✓ Apache restarted"
echo ""

echo "=== FIX COMPLETE ==="
echo ""
echo "Test: http://192.168.10.40/hris-seven-payroll"
echo ""
echo "Verify:"
echo "  cat $PROJECT_PATH/public/.htaccess | grep -i rewritebase"
echo "  (should show nothing)"
echo ""









