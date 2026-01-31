#!/bin/bash

# Script untuk fix "GET method is not supported" meskipun route sudah terdaftar
# Usage: sudo ./fix-route-get-method.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo "=== Fixing GET Method Not Supported Error ==="
echo ""

cd "$PROJECT_PATH"

# 1. Clear semua cache
echo "[1/6] Clearing all cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "✓ Cache cleared"
echo ""

# 2. Hapus semua route cache files
echo "[2/6] Removing route cache files..."
find bootstrap/cache -name "routes*.php" -delete 2>/dev/null || true
rm -f bootstrap/cache/routes*.php 2>/dev/null || true
echo "✓ Route cache files removed"
echo ""

# 3. Test route tanpa cache
echo "[3/6] Testing route without cache..."
php artisan route:list | grep -E "^.*GET.*/" | head -1
echo ""

# 4. Check .htaccess
echo "[4/6] Checking .htaccess..."
if [ -f "public/.htaccess" ]; then
    echo "✓ .htaccess exists"
    if grep -q "RewriteBase" public/.htaccess; then
        echo "⚠ RewriteBase found in .htaccess"
        echo "  If using Apache Alias, RewriteBase should be removed"
    fi
else
    echo "✗ .htaccess not found!"
fi
echo ""

# 5. Rebuild cache (tanpa route cache dulu untuk test)
echo "[5/6] Rebuilding config cache (without route cache for testing)..."
php artisan config:cache
php artisan view:cache
# JANGAN route:cache dulu untuk test
echo "✓ Config and view cached (route cache skipped for testing)"
echo ""

# 6. Check Apache alias configuration
echo "[6/6] Checking Apache alias configuration..."
if [ -f "/etc/apache2/conf-enabled/hris-seven-payroll-alias.conf" ]; then
    echo "✓ Apache alias configuration found"
    cat /etc/apache2/conf-enabled/hris-seven-payroll-alias.conf | grep -E "Alias|Directory"
else
    echo "⚠ Apache alias configuration not found"
fi
echo ""

echo "=== Fix Complete ==="
echo ""
echo "IMPORTANT: Route cache is NOT enabled for testing."
echo "Test access now: http://192.168.10.40/hris-seven-payroll"
echo ""
echo "If it works, then enable route cache:"
echo "  php artisan route:cache"
echo ""
echo "If still error, check:"
echo "  1. .htaccess should NOT have RewriteBase if using Apache Alias"
echo "  2. Apache alias configuration should be correct"
echo "  3. Check Laravel log: tail -f storage/logs/laravel.log"
echo ""









