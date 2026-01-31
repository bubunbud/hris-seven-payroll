#!/bin/bash

# Script untuk fix migration error dan Apache redirect loop
# Usage: sudo ./fix-server-issues.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"
MIGRATION_FILE="$PROJECT_PATH/database/migrations/2025_10_29_043311_add_tipe_hari_libur_to_m_hari_libur_table.php"

echo "=== Fixing Server Issues ==="
echo ""

# 1. Fix Migration - Mark as run if column exists
echo "[1/4] Fixing migration duplicate column error..."
cd "$PROJECT_PATH"

# Check if column exists
COLUMN_EXISTS=$(mysql -u root -proot123 hris_seven -e "DESCRIBE m_hari_libur;" 2>/dev/null | grep -c "vcTipeHariLibur" || echo "0")

if [ "$COLUMN_EXISTS" -gt 0 ]; then
    echo "Column vcTipeHariLibur already exists, marking migration as run..."
    mysql -u root -proot123 hris_seven -e "INSERT IGNORE INTO migrations (migration, batch) VALUES ('2025_10_29_043311_add_tipe_hari_libur_to_m_hari_libur_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m));" 2>/dev/null || echo "Migration might already be marked"
    echo "Migration marked as run."
else
    echo "Column does not exist, will run migration after fix."
fi

# Update migration file to check column existence
if [ -f "$MIGRATION_FILE" ]; then
    echo "Updating migration file to check column existence..."
    cat > "$MIGRATION_FILE" <<'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('m_hari_libur', 'vcTipeHariLibur')) {
            Schema::table('m_hari_libur', function (Blueprint $table) {
                $table->string('vcTipeHariLibur', 20)->nullable()->after('vcKeterangan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('m_hari_libur', 'vcTipeHariLibur')) {
            Schema::table('m_hari_libur', function (Blueprint $table) {
                $table->dropColumn('vcTipeHariLibur');
            });
        }
    }
};
EOF
    echo "Migration file updated."
fi

# Run remaining migrations
echo "Running remaining migrations..."
php artisan migrate --force 2>&1 | tail -20 || echo "Some migrations might have failed, but continuing..."

echo ""

# 2. Fix .htaccess - Remove RewriteBase if causing redirect loop
echo "[2/4] Fixing .htaccess redirect loop..."
cd "$PROJECT_PATH/public"

# Backup
cp .htaccess .htaccess.backup.$(date +%Y%m%d_%H%M%S)

# Check if using Apache Alias (if Alias is configured, don't use RewriteBase)
if [ -f "/etc/apache2/conf-enabled/hris-seven-payroll-alias.conf" ] || [ -f "/etc/apache2/conf-available/hris-seven-payroll-alias.conf" ]; then
    echo "Apache Alias detected, removing RewriteBase from .htaccess..."
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
    echo ".htaccess updated (without RewriteBase for Alias setup)."
else
    echo "No Apache Alias detected, using RewriteBase..."
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
    echo ".htaccess updated (with RewriteBase)."
fi

echo ""

# 3. Clear all cache
echo "[3/4] Clearing all cache..."
cd "$PROJECT_PATH"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "Cache cleared."

# 4. Re-cache
echo "[4/4] Re-caching..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "Cache re-cached."

# Restart Apache
echo ""
echo "Restarting Apache..."
systemctl restart apache2
echo "Apache restarted."

echo ""
echo "=== Fix Complete ==="
echo ""
echo "Please test: http://192.168.10.40/hris-seven-payroll/login"
echo ""
echo "If still having issues, check:"
echo "1. Apache alias configuration: cat /etc/apache2/conf-available/hris-seven-payroll-alias.conf"
echo "2. Laravel log: tail -50 $PROJECT_PATH/storage/logs/laravel.log"
echo "3. Apache error log: tail -30 /var/log/apache2/error.log"









