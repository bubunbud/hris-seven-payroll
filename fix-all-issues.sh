#!/bin/bash

# Script untuk fix semua issues (migration + route)
# Usage: sudo ./fix-all-issues.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo "=== Fixing All Issues ==="
echo ""

cd "$PROJECT_PATH"

# 1. Fix Migration - Mark problematic migrations as run
echo "[1/5] Fixing migration issues..."
MIGRATIONS_TO_SKIP=(
    "2025_10_29_043311_add_tipe_hari_libur_to_m_hari_libur_table"
)

for migration in "${MIGRATIONS_TO_SKIP[@]}"; do
    # Check if column/table already exists
    if [[ "$migration" == *"vcTipeHariLibur"* ]]; then
        COLUMN_EXISTS=$(mysql -u root -proot123 hris_seven -e "DESCRIBE m_hari_libur;" 2>/dev/null | grep -c "vcTipeHariLibur" || echo "0")
        if [ "$COLUMN_EXISTS" -gt 0 ]; then
            echo "  Marking $migration as run (column exists)..."
            mysql -u root -proot123 hris_seven -e "INSERT IGNORE INTO migrations (migration, batch) VALUES ('$migration', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m));" 2>/dev/null || true
        fi
    fi
done

# Run remaining migrations
echo "  Running remaining migrations..."
php artisan migrate --force 2>&1 | tail -10 || echo "  Some migrations might have failed"
echo ""

# 2. Clear all cache
echo "[2/5] Clearing all cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
rm -f bootstrap/cache/routes*.php
rm -f bootstrap/cache/config.php
echo "✓ Cache cleared"
echo ""

# 3. Fix .htaccess
echo "[3/5] Fixing .htaccess..."
cd public
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
cd ..
echo "✓ .htaccess fixed (without RewriteBase)"
echo ""

# 4. Rebuild cache (without route cache for testing)
echo "[4/5] Rebuilding cache (without route cache for testing)..."
php artisan config:cache
php artisan view:cache
# JANGAN route:cache dulu
echo "✓ Cache rebuilt (route cache skipped)"
echo ""

# 5. Restart Apache
echo "[5/5] Restarting Apache..."
systemctl restart apache2
echo "✓ Apache restarted"
echo ""

echo "=== Fix Complete ==="
echo ""
echo "Test access: http://192.168.10.40/hris-seven-payroll"
echo ""
echo "If it works, enable route cache:"
echo "  php artisan route:cache"
echo ""
echo "Check logs if still error:"
echo "  tail -f storage/logs/laravel.log"
echo ""









