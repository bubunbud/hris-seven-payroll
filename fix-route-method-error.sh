#!/bin/bash

# Script untuk fix "GET method is not supported" error
# Usage: sudo ./fix-route-method-error.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo "=== Fixing Route Method Error ==="
echo ""

cd "$PROJECT_PATH"

# 1. Clear all cache
echo "[1/4] Clearing all cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "Cache cleared."
echo ""

# 2. Remove route cache file manually
echo "[2/4] Removing route cache file..."
rm -f bootstrap/cache/routes-v7.php 2>/dev/null || true
rm -f bootstrap/cache/routes.php 2>/dev/null || true
echo "Route cache files removed."
echo ""

# 3. Verify route definition
echo "[3/4] Verifying route definition..."
if grep -q "Route::get('/'," routes/web.php; then
    echo "✓ Route '/' found in web.php"
else
    echo "✗ Route '/' NOT found in web.php"
    exit 1
fi
echo ""

# 4. Rebuild route cache
echo "[4/4] Rebuilding route cache..."
php artisan route:cache
echo "Route cache rebuilt."
echo ""

# 5. Verify route list
echo "Verifying routes..."
php artisan route:list | grep -E "GET.*/" | head -3
echo ""

# 6. Clear config cache and re-cache
echo "Re-caching config..."
php artisan config:clear
php artisan config:cache
echo ""

echo "=== Fix Complete ==="
echo ""
echo "Please test: http://192.168.10.40/hris-seven-payroll"
echo ""









