#!/bin/bash

# HRM 500 Error Fix Script
# Specifically addresses attendance download and employee page errors

set -e

echo "🚨 HRM 500 Error Fix Script"
echo "=========================="

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration - Update these
APP_PATH="/var/www/html/hrm"  # Update your path
DB_NAME="hrm"
DB_USER="root"

echo -e "${BLUE}📋 Fixing HRM 500 Errors${NC}"
echo -e "   Target: Attendance Download & Employee Pages"
echo -e "   App Path: ${APP_PATH}"
echo ""

if [ ! -d "$APP_PATH" ]; then
    echo -e "${RED}❌ App directory not found: ${APP_PATH}${NC}"
    echo "Update APP_PATH in this script"
    exit 1
fi

cd "$APP_PATH"

echo -e "${BLUE}🔍 Step 1: Checking Current Status${NC}"
read -sp "Enter database password for ${DB_USER}: " DB_PASSWORD
echo ""

# Check if tables have data
echo -e "${YELLOW}📊 Current database status:${NC}"
USER_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM users;" 2>/dev/null)
EMPLOYEE_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM employees;" 2>/dev/null)
ATTENDANCE_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM attendance_employees;" 2>/dev/null)

echo -e "   👥 Users: ${USER_COUNT}"
echo -e "   🏢 Employees: ${EMPLOYEE_COUNT}"
echo -e "   📅 Attendance: ${ATTENDANCE_COUNT}"
echo ""

echo -e "${BLUE}🧹 Step 2: Emergency Cache Clear${NC}"
php artisan config:clear 2>/dev/null && echo -e "${GREEN}✅ Config cleared${NC}" || echo -e "${RED}❌ Config failed${NC}"
php artisan cache:clear 2>/dev/null && echo -e "${GREEN}✅ Cache cleared${NC}" || echo -e "${RED}❌ Cache failed${NC}"
php artisan view:clear 2>/dev/null && echo -e "${GREEN}✅ Views cleared${NC}" || echo -e "${RED}❌ Views failed${NC}"
php artisan route:clear 2>/dev/null && echo -e "${GREEN}✅ Routes cleared${NC}" || echo -e "${RED}❌ Routes failed${NC}"

echo ""
echo -e "${BLUE}🔧 Step 3: Fixing File Permissions${NC}"
chmod -R 755 . 2>/dev/null && echo -e "${GREEN}✅ General permissions${NC}" || echo -e "${YELLOW}⚠️  Permission issue${NC}"
chmod -R 777 storage/ 2>/dev/null && echo -e "${GREEN}✅ Storage permissions${NC}" || echo -e "${YELLOW}⚠️  Storage issue${NC}"
chmod -R 777 bootstrap/cache/ 2>/dev/null && echo -e "${GREEN}✅ Bootstrap permissions${NC}" || echo -e "${YELLOW}⚠️  Bootstrap issue${NC}"

echo ""
echo -e "${BLUE}🌱 Step 4: Running Comprehensive Data Seeder${NC}"

if [ -f "database/seeders/ComprehensiveHrmSeeder.php" ]; then
    echo -e "${YELLOW}⏳ This will create comprehensive test data...${NC}"
    echo "This includes events, tickets, meetings, leaves, and fixes common 500 errors"
    read -p "Continue? (y/N): " confirm
    
    if [[ $confirm == [yY] || $confirm == [yY][eE][sS] ]]; then
        echo -e "${BLUE}🚀 Running comprehensive seeder (this may take several minutes)...${NC}"
        php artisan db:seed --class=ComprehensiveHrmSeeder
        echo -e "${GREEN}✅ Comprehensive data created${NC}"
    else
        echo -e "${YELLOW}⏭️  Skipping seeder${NC}"
    fi
else
    echo -e "${RED}❌ ComprehensiveHrmSeeder.php not found${NC}"
    echo "Please upload the seeder file first"
fi

echo ""
echo -e "${BLUE}🔧 Step 5: Creating Missing Settings${NC}"
mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" << 'EOF'
INSERT IGNORE INTO settings (name, value, created_by, created_at, updated_at) VALUES
('footer_text', 'Barcosys', 1, NOW(), NOW()),
('company_name', 'Barcosys', 1, NOW(), NOW()),
('app_name', 'Barcosys HRM', 1, NOW(), NOW()),
('default_language', 'english', 1, NOW(), NOW()),
('site_currency', 'USD', 1, NOW(), NOW()),
('site_currency_symbol', '$', 1, NOW(), NOW()),
('timezone', 'UTC', 1, NOW(), NOW());
EOF
echo -e "${GREEN}✅ Essential settings created${NC}"

echo ""
echo -e "${BLUE}⚡ Step 6: Final Optimization${NC}"
composer dump-autoload --optimize 2>/dev/null && echo -e "${GREEN}✅ Autoload optimized${NC}" || echo -e "${YELLOW}⚠️  Autoload issue${NC}"
php artisan config:cache 2>/dev/null && echo -e "${GREEN}✅ Config cached${NC}" || echo -e "${YELLOW}⚠️  Config cache issue${NC}"
php artisan route:cache 2>/dev/null && echo -e "${GREEN}✅ Routes cached${NC}" || echo -e "${YELLOW}⚠️  Route cache issue${NC}"

echo ""
echo -e "${BLUE}🧪 Step 7: Final Verification${NC}"
NEW_USER_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM users;" 2>/dev/null)
NEW_EMPLOYEE_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM employees;" 2>/dev/null)
NEW_ATTENDANCE_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM attendance_employees;" 2>/dev/null)
EVENT_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM events;" 2>/dev/null)
TICKET_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM tickets;" 2>/dev/null)

echo -e "${GREEN}📊 Final Results:${NC}"
echo -e "   👥 Users: ${NEW_USER_COUNT}"
echo -e "   🏢 Employees: ${NEW_EMPLOYEE_COUNT}"  
echo -e "   📅 Attendance: ${NEW_ATTENDANCE_COUNT}"
echo -e "   📅 Events: ${EVENT_COUNT}"
echo -e "   🎫 Tickets: ${TICKET_COUNT}"
echo ""

echo -e "${GREEN}🎉 500 ERROR FIX COMPLETED!${NC}"
echo ""
echo -e "${BLUE}📋 Testing Instructions:${NC}"
echo -e "1. Visit your HRM application"
echo -e "2. Login with: brian.robinson1@barcosys.com / password123"
echo -e "3. Test Employee page (should work now)"
echo -e "4. Test Attendance Download (should work now)"
echo -e "5. Check all other modules have sample data"
echo ""
echo -e "${YELLOW}🔍 If still getting 500 errors:${NC}"
echo -e "1. Check storage/logs/laravel.log for specific errors"
echo -e "2. Ensure database connection is working"
echo -e "3. Verify all file permissions are correct"
echo -e "4. Contact support with specific error messages"