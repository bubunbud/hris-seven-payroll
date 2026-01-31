#!/bin/bash

# Script untuk fix migration duplicate column error
# Usage: sudo ./fix-migration-duplicate.sh

set -e

PROJECT_PATH="/var/www/html/hris-seven-payroll"
MIGRATION_NAME="2025_10_29_043311_add_tipe_hari_libur_to_m_hari_libur_table"

echo "Fixing migration duplicate column error..."

cd "$PROJECT_PATH"

# Check if migration table exists
if ! php artisan migrate:status | grep -q "$MIGRATION_NAME"; then
    echo "Migration belum dijalankan, akan dijalankan sekarang..."
    php artisan migrate --force
else
    echo "Migration sudah dijalankan sebelumnya."
    echo "Marking migration as run manually..."
    
    # Insert migration record manually
    mysql -u root -proot123 hris_seven -e "INSERT IGNORE INTO migrations (migration, batch) VALUES ('$MIGRATION_NAME', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS m));"
    
    echo "Migration marked as run. Continuing with other migrations..."
    php artisan migrate --force
fi

echo "Done!"









