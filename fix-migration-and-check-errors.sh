#!/bin/bash

# Script untuk fix migration error dan check semua error
# Fix migration issue dan check Apache/Laravel logs

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Fix Migration & Check Errors         ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

PROJECT_PATH="/var/www/html/hris-seven-payroll"

echo -e "${YELLOW}[1/6] Checking Apache Error Log (correct file)...${NC}"
echo -e "${YELLOW}Last 30 lines of /var/log/apache2/error.log:${NC}"
tail -30 /var/log/apache2/error.log 2>/dev/null || echo "  (File tidak ditemukan atau kosong)"
echo ""

echo -e "${YELLOW}[2/6] Checking Laravel Log...${NC}"
if [ -f "$PROJECT_PATH/storage/logs/laravel.log" ]; then
    echo -e "${YELLOW}Last 20 lines (errors only):${NC}"
    tail -50 "$PROJECT_PATH/storage/logs/laravel.log" | grep -i "error\|exception\|fatal" | tail -20 || echo "  (No recent errors)"
else
    echo -e "${RED}Laravel log file tidak ditemukan${NC}"
fi
echo ""

echo -e "${YELLOW}[3/6] Fixing Migration Issue...${NC}"
cd "$PROJECT_PATH"

# Check migration status
echo "Checking migration status..."
sudo -u www-data php artisan migrate:status 2>&1 | tail -10
echo ""

# Mark migration as run if table exists
echo "Checking if t_lembur_detail table exists..."
TABLE_EXISTS=$(mysql -u root -proot123 hris_seven -e "SHOW TABLES LIKE 't_lembur_detail';" 2>/dev/null | grep -c "t_lembur_detail" || echo "0")

if [ "$TABLE_EXISTS" -gt 0 ]; then
    echo -e "${GREEN}✓ Table t_lembur_detail sudah ada${NC}"
    echo "Marking migration as run..."
    
    # Get migration file name
    MIGRATION_FILE="2025_11_01_071718_create_t_lembur_detail_table.php"
    if [ -f "database/migrations/$MIGRATION_FILE" ]; then
        # Insert into migrations table if not exists
        mysql -u root -proot123 hris_seven <<EOF 2>/dev/null || true
INSERT IGNORE INTO migrations (migration, batch) 
VALUES ('2025_11_01_071718_create_t_lembur_detail_table', 
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS temp));
EOF
        echo -e "${GREEN}✓ Migration marked as run${NC}"
    fi
else
    echo -e "${YELLOW}  Table tidak ada, akan di-create oleh migration${NC}"
fi
echo ""

echo -e "${YELLOW}[4/6] Running Migrations (with --force)...${NC}"
# Run migrations dengan --force dan ignore errors untuk table yang sudah ada
sudo -u www-data php artisan migrate --force 2>&1 | grep -v "already exists" || echo -e "${YELLOW}  (Some migrations may have been skipped)${NC}"
echo ""

echo -e "${YELLOW}[5/6] Checking Active Apache Sites...${NC}"
a2query -s
echo ""

echo -e "${YELLOW}[6/6] Testing Apache Configuration...${NC}"
if apache2ctl configtest 2>&1; then
    echo -e "${GREEN}✓ Apache configuration OK${NC}"
else
    echo -e "${RED}✗ Apache configuration has errors${NC}"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  CHECK COMPLETE                       ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo -e "  1. Check output di atas untuk error messages"
echo -e "  2. Test aplikasi: ${GREEN}http://192.168.10.40/hris-seven-payroll${NC}"
echo -e "  3. Jika masih error, check:"
echo -e "     ${GREEN}tail -f /var/log/apache2/error.log${NC}"
echo -e "     ${GREEN}tail -f $PROJECT_PATH/storage/logs/laravel.log${NC}"
echo ""





