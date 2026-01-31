#!/bin/bash

# Script untuk debug 500 Internal Server Error - Final
# Usage: sudo ./debug-500-final.sh

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo "=== Final Debug for 500 Internal Server Error ==="
echo ""

cd "$PROJECT_PATH"

# 1. Enable debug mode
echo "[1/6] Enabling debug mode..."
if [ -f ".env" ]; then
    cp .env .env.backup
    sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env
    sed -i 's/APP_ENV=production/APP_ENV=local/' .env
    echo "✓ Debug enabled"
else
    echo "✗ .env not found!"
    exit 1
fi
echo ""

# 2. Check and generate APP_KEY
echo "[2/6] Checking APP_KEY..."
if ! grep -q "APP_KEY=base64:" .env; then
    echo "  APP_KEY missing, generating..."
    php artisan key:generate --force
    echo "✓ APP_KEY generated"
else
    echo "✓ APP_KEY exists"
fi
echo ""

# 3. Fix permissions
echo "[3/6] Fixing permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "✓ Permissions fixed"
echo ""

# 4. Clear all cache
echo "[4/6] Clearing all cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
rm -f bootstrap/cache/*.php
echo "✓ Cache cleared"
echo ""

# 5. Test database connection
echo "[5/6] Testing database connection..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'DB OK'; } catch(Exception \$e) { echo 'DB ERROR: ' . \$e->getMessage(); }" 2>&1
echo ""

# 6. Re-cache config (with debug enabled)
echo "[6/6] Re-caching config..."
php artisan config:cache
php artisan view:cache
# Jangan route:cache untuk test
echo "✓ Config cached"
echo ""

# 7. Restart Apache
echo "Restarting Apache..."
systemctl restart apache2
echo "✓ Apache restarted"
echo ""

echo "=== Debug Complete ==="
echo ""
echo "Debug mode is now ENABLED."
echo "Try accessing: http://192.168.10.40/hris-seven-payroll"
echo ""
echo "You should now see detailed error message in browser."
echo ""
echo "Check logs:"
echo "  tail -f storage/logs/laravel.log"
echo "  tail -f /var/log/apache2/error.log"
echo ""
echo "After fixing, disable debug:"
echo "  sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env"
echo "  sed -i 's/APP_ENV=local/APP_ENV=production/' .env"
echo "  php artisan config:clear && php artisan config:cache"
echo ""









