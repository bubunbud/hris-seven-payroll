#!/bin/bash

# Test tanpa .htaccess untuk isolasi masalah
# Usage: sudo ./TEST_WITHOUT_HTACCESS.sh

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo "=== Testing WITHOUT .htaccess ==="
echo ""

cd "$PROJECT_PATH/public"

# Disable .htaccess
if [ -f ".htaccess" ]; then
    mv .htaccess .htaccess.disabled
    echo "✓ .htaccess disabled (renamed to .htaccess.disabled)"
else
    echo "✓ .htaccess already disabled"
fi

# Restart Apache
cd "$PROJECT_PATH"
systemctl restart apache2
echo "✓ Apache restarted"
echo ""

echo "=== Test Now ==="
echo ""
echo "Test access: http://192.168.10.40/hris-seven-payroll"
echo ""
echo "If it works WITHOUT .htaccess:"
echo "  - Masalahnya di .htaccess"
echo "  - Kita akan buat .htaccess yang lebih sederhana"
echo ""
echo "If still error:"
echo "  - Masalahnya di Apache Alias atau Laravel"
echo "  - Perlu check konfigurasi lain"
echo ""









