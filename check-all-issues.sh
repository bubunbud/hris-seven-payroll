#!/bin/bash

# Script untuk check semua kemungkinan masalah
# Usage: sudo ./check-all-issues.sh

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo "=== Checking All Possible Issues ==="
echo ""

cd "$PROJECT_PATH"

# 1. Check PHP error
echo "[1/8] Checking PHP syntax..."
echo "Routes:"
php -l routes/web.php 2>&1
echo ""
echo "AuthController:"
php -l app/Http/Controllers/AuthController.php 2>&1
echo ""
echo "Kernel:"
php -l app/Http/Kernel.php 2>&1
echo ""

# 2. Check .env
echo "[2/8] Checking .env..."
if [ -f ".env" ]; then
    echo "✓ .env exists"
    echo "APP_KEY: $(grep APP_KEY .env | cut -d '=' -f2 | cut -c1-20)..."
    echo "APP_DEBUG: $(grep APP_DEBUG .env | cut -d '=' -f2)"
    echo "APP_URL: $(grep APP_URL .env | cut -d '=' -f2)"
else
    echo "✗ .env NOT found!"
fi
echo ""

# 3. Check permissions
echo "[3/8] Checking permissions..."
echo "Storage:"
ls -la storage/ | head -3
echo "Bootstrap cache:"
ls -la bootstrap/cache/ | head -3
echo ""

# 4. Test storage writability
echo "[4/8] Testing storage writability..."
touch storage/test.txt 2>&1 && rm storage/test.txt && echo "✓ Storage writable" || echo "✗ Storage NOT writable!"
echo ""

# 5. Test database
echo "[5/8] Testing database connection..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'DB OK'; } catch(Exception \$e) { echo 'DB ERROR: ' . \$e->getMessage(); }" 2>&1
echo ""

# 6. Check required files
echo "[6/8] Checking required files..."
[ -f "app/Http/Controllers/AuthController.php" ] && echo "✓ AuthController.php" || echo "✗ AuthController.php NOT found"
[ -f "app/Models/User.php" ] && echo "✓ User.php" || echo "✗ User.php NOT found"
[ -f "resources/views/dashboard.blade.php" ] && echo "✓ dashboard.blade.php" || echo "✗ dashboard.blade.php NOT found"
[ -f "resources/views/auth/login.blade.php" ] && echo "✓ login.blade.php" || echo "✗ login.blade.php NOT found"
[ -f "public/index.php" ] && echo "✓ index.php" || echo "✗ index.php NOT found"
echo ""

# 7. Check Apache configuration
echo "[7/8] Checking Apache configuration..."
if [ -f "/etc/apache2/conf-enabled/hris-seven-payroll-alias.conf" ]; then
    echo "✓ Alias config exists:"
    cat /etc/apache2/conf-enabled/hris-seven-payroll-alias.conf
else
    echo "✗ Alias config NOT found!"
fi
echo ""

# 8. Check .htaccess
echo "[8/8] Checking .htaccess..."
if [ -f "public/.htaccess" ]; then
    echo "✓ .htaccess exists"
    echo "RewriteBase:"
    grep -i rewritebase public/.htaccess || echo "  (no RewriteBase found)"
else
    echo "✗ .htaccess NOT found!"
fi
echo ""

# 9. Check recent errors
echo "=== Recent Errors ==="
echo "Laravel log (last 30 lines):"
tail -30 storage/logs/laravel.log 2>/dev/null || echo "Log file empty or not accessible"
echo ""
echo "Apache error log (last 10 lines):"
tail -10 /var/log/apache2/error.log 2>/dev/null || echo "Apache log not accessible"
echo ""

echo "=== Check Complete ==="
echo ""
echo "IMPORTANT: With APP_DEBUG=true, you should see detailed error in browser."
echo "Please:"
echo "1. Open browser: http://192.168.10.40/hris-seven-payroll"
echo "2. Copy the FULL error message from browser"
echo "3. Also run: tail -50 storage/logs/laravel.log"
echo ""









