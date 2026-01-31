#!/bin/bash

# Script untuk fix route cache error setelah update
# Usage: sudo ./fix-route-cache.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo "Fixing route cache error..."

cd "$PROJECT_PATH"

# Clear semua cache
echo "Clearing all caches..."
sudo -u www-data php artisan config:clear 2>/dev/null || php artisan config:clear
sudo -u www-data php artisan route:clear 2>/dev/null || php artisan route:clear
sudo -u www-data php artisan view:clear 2>/dev/null || php artisan view:clear
sudo -u www-data php artisan cache:clear 2>/dev/null || php artisan cache:clear

echo "Cache cleared successfully!"

# Re-cache untuk production
echo "Re-caching for production..."
sudo -u www-data php artisan config:cache 2>/dev/null || php artisan config:cache
sudo -u www-data php artisan route:cache 2>/dev/null || php artisan route:cache
sudo -u www-data php artisan view:cache 2>/dev/null || php artisan view:cache

echo "Cache re-cached successfully!"

# Check route list untuk verifikasi
echo ""
echo "Verifying routes..."
php artisan route:list | grep -E "GET|POST" | head -10

echo ""
echo "Done! Please test access: http://192.168.10.40/hris-seven-payroll"









