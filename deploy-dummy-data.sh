#!/bin/bash

# HRM Dummy Data Deployment Script
# Usage: ./deploy-dummy-data.sh

set -e  # Exit on any error

echo "🚀 Starting HRM Dummy Data Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration - Update these variables for your server
APP_PATH="/var/www/html/hrm"  # Update this path
DB_NAME="hrm"                 # Update database name
DB_USER="root"                # Update database user
DB_HOST="localhost"           # Update database host
DB_PORT="3306"                # Update database port

echo -e "${BLUE}📋 Configuration:${NC}"
echo -e "   App Path: ${APP_PATH}"
echo -e "   Database: ${DB_NAME}"
echo -e "   DB User: ${DB_USER}"
echo -e "   DB Host: ${DB_HOST}:${DB_PORT}"
echo ""

# Check if running as root for permission operations
if [[ $EUID -eq 0 ]]; then
   echo -e "${YELLOW}⚠️  Warning: Running as root. Be careful with file permissions.${NC}"
fi

# Function to check command success
check_command() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ $1 completed successfully${NC}"
    else
        echo -e "${RED}❌ $1 failed${NC}"
        exit 1
    fi
}

# Step 1: Navigate to application directory
echo -e "${BLUE}📁 Navigating to application directory...${NC}"
if [ ! -d "$APP_PATH" ]; then
    echo -e "${RED}❌ Application directory not found: ${APP_PATH}${NC}"
    echo "Please update APP_PATH variable in this script"
    exit 1
fi

cd "$APP_PATH"
check_command "Directory navigation"

# Step 2: Create backup
echo -e "${BLUE}💾 Creating database backup...${NC}"
BACKUP_FILE="hrm_backup_$(date +%Y%m%d_%H%M%S).sql"
read -sp "Enter database password for ${DB_USER}: " DB_PASSWORD
echo ""

mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null
check_command "Database backup"
echo -e "${GREEN}✅ Backup saved as: ${BACKUP_FILE}${NC}"

# Step 3: Update .env file
echo -e "${BLUE}⚙️  Updating environment configuration...${NC}"
if [ -f ".env" ]; then
    cp .env .env.backup
    sed -i 's/APP_NAME=.*/APP_NAME=Barcosys/' .env
    check_command "Environment update"
else
    echo -e "${YELLOW}⚠️  .env file not found${NC}"
fi

# Step 4: Check if seeder exists
echo -e "${BLUE}🔍 Checking if DummyDataSeeder exists...${NC}"
if [ ! -f "database/seeders/DummyDataSeeder.php" ]; then
    echo -e "${RED}❌ DummyDataSeeder.php not found!${NC}"
    echo "Please ensure the seeder file is uploaded to database/seeders/"
    exit 1
fi

# Step 5: Install/Update dependencies
echo -e "${BLUE}📦 Installing/Updating dependencies...${NC}"
composer install --optimize-autoloader --no-dev --quiet
check_command "Composer install"

# Step 6: Clear caches
echo -e "${BLUE}🧹 Clearing application caches...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
check_command "Cache clearing"

# Step 7: Run migrations (if any)
echo -e "${BLUE}🔄 Running database migrations...${NC}"
php artisan migrate --force
check_command "Database migrations"

# Step 8: Run the dummy data seeder
echo -e "${BLUE}🌱 Running dummy data seeder...${NC}"
echo "This may take a few minutes..."
php artisan db:seed --class=DummyDataSeeder
check_command "Dummy data seeding"

# Step 9: Set footer text in database
echo -e "${BLUE}🔧 Setting footer text in database...${NC}"
mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" << EOF
INSERT INTO settings (name, value, created_by, created_at, updated_at) 
VALUES ('footer_text', 'Barcosys', 1, NOW(), NOW()) 
ON DUPLICATE KEY UPDATE value = 'Barcosys';
EOF
check_command "Footer text configuration"

# Step 10: Set proper permissions
echo -e "${BLUE}🔐 Setting proper file permissions...${NC}"
# Detect web server user
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
else
    WEB_USER=$(whoami)
    echo -e "${YELLOW}⚠️  Could not detect web server user, using: ${WEB_USER}${NC}"
fi

# Only change ownership if not running as the web user
if [ "$(whoami)" != "$WEB_USER" ]; then
    if [ "$EUID" -eq 0 ]; then
        chown -R "$WEB_USER":"$WEB_USER" .
        check_command "File ownership"
    else
        echo -e "${YELLOW}⚠️  Skipping ownership change (not running as root)${NC}"
    fi
fi

# Set directory and file permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/
check_command "File permissions"

# Step 11: Final optimization
echo -e "${BLUE}⚡ Final optimization...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
check_command "Final optimization"

# Step 12: Verification
echo -e "${BLUE}🧪 Verifying deployment...${NC}"

# Check user count
USER_COUNT=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM users;" 2>/dev/null)
EMPLOYEE_COUNT=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM employees;" 2>/dev/null)
ATTENDANCE_COUNT=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM attendance_employees;" 2>/dev/null)

echo -e "${GREEN}📊 Deployment Results:${NC}"
echo -e "   👥 Total Users: ${USER_COUNT}"
echo -e "   🏢 Total Employees: ${EMPLOYEE_COUNT}"
echo -e "   📅 Attendance Records: ${ATTENDANCE_COUNT}"
echo ""

# Success message
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo ""
echo -e "${BLUE}📋 Next Steps:${NC}"
echo -e "   1. Test login at your application URL"
echo -e "   2. Verify footer shows 'Barcosys'"
echo -e "   3. Test sample account: brian.robinson1@barcosys.com / password123"
echo -e "   4. Check attendance reports for Feb-Apr 2026"
echo ""
echo -e "${BLUE}💾 Backup Information:${NC}"
echo -e "   Database backup: ${BACKUP_FILE}"
echo -e "   Environment backup: .env.backup"
echo ""
echo -e "${YELLOW}🔒 Security Note:${NC}"
echo -e "   Remember to change default passwords for production use!"