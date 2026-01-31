#!/bin/bash

# Script untuk debug 500 Internal Server Error
# Usage: sudo ./debug-500-error.sh

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo "=== Debugging 500 Internal Server Error ==="
echo ""

# Check Laravel log
echo "1. Checking Laravel log (last 50 lines)..."
echo "----------------------------------------"
tail -50 "$PROJECT_PATH/storage/logs/laravel.log" 2>/dev/null || echo "Log file not found or empty"
echo ""

# Check Apache error log
echo "2. Checking Apache error log (last 30 lines)..."
echo "----------------------------------------"
tail -30 /var/log/apache2/error.log 2>/dev/null || tail -30 /var/log/apache2/error_log 2>/dev/null || echo "Apache error log not found"
echo ""

# Check permissions
echo "3. Checking permissions..."
echo "----------------------------------------"
ls -la "$PROJECT_PATH/storage/logs/" | head -5
ls -la "$PROJECT_PATH/bootstrap/cache/" | head -5
echo ""

# Check .env file
echo "4. Checking .env file (APP_URL, DB config)..."
echo "----------------------------------------"
if [ -f "$PROJECT_PATH/.env" ]; then
    grep -E "APP_URL|DB_|APP_DEBUG" "$PROJECT_PATH/.env" | head -10"
else
    echo ".env file not found!"
fi
echo ""

# Check PHP syntax
echo "5. Checking PHP syntax for key files..."
echo "----------------------------------------"
php -l "$PROJECT_PATH/app/Http/Controllers/AuthController.php" 2>&1 || echo "Syntax error in AuthController"
php -l "$PROJECT_PATH/routes/web.php" 2>&1 || echo "Syntax error in web.php"
echo ""

# Check if storage is writable
echo "6. Checking storage writability..."
echo "----------------------------------------"
touch "$PROJECT_PATH/storage/test_write.txt" 2>&1 && rm "$PROJECT_PATH/storage/test_write.txt" && echo "Storage is writable" || echo "Storage is NOT writable!"
echo ""

echo "=== Debug Complete ==="
echo ""
echo "Most common fixes:"
echo "1. Fix permissions: sudo chown -R www-data:www-data $PROJECT_PATH"
echo "2. Clear cache: cd $PROJECT_PATH && php artisan config:clear && php artisan cache:clear"
echo "3. Check Laravel log above for specific error message"









