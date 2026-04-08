#!/bin/bash

# Emergency Recovery Script for HRM Server Issues
# Usage: chmod +x emergency-recovery.sh && ./emergency-recovery.sh

set -e

echo "🚨 HRM Emergency Recovery Script"
echo "================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration - Update these for your server
APP_PATH="/var/www/html/hrm"  # Update this
DB_NAME="hrm"                 # Update this  
DB_USER="root"                # Update this

echo -e "${BLUE}📋 Configuration:${NC}"
echo -e "   App Path: ${APP_PATH}"
echo -e "   Database: ${DB_NAME}"
echo -e "   DB User: ${DB_USER}"
echo ""

# Check if app directory exists
if [ ! -d "$APP_PATH" ]; then
    echo -e "${RED}❌ Application directory not found: ${APP_PATH}${NC}"
    echo "Please update APP_PATH in this script"
    exit 1
fi

cd "$APP_PATH"

echo -e "${BLUE}🔍 Step 1: Checking Laravel Error Logs${NC}"
if [ -f "storage/logs/laravel.log" ]; then
    echo -e "${YELLOW}📄 Last 20 lines of Laravel log:${NC}"
    tail -20 storage/logs/laravel.log
    echo ""
else
    echo -e "${YELLOW}⚠️  No Laravel log file found${NC}"
fi

echo -e "${BLUE}🔍 Step 2: Testing Database Connection${NC}"
read -sp "Enter database password for ${DB_USER}: " DB_PASSWORD
echo ""

# Test database connection
if mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "SELECT 1;" 2>/dev/null; then
    echo -e "${GREEN}✅ Database connection successful${NC}"
    
    # Check data counts
    echo -e "${BLUE}📊 Checking database records:${NC}"
    USER_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM users;" 2>/dev/null)
    EMPLOYEE_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM employees;" 2>/dev/null)
    ATTENDANCE_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM attendance_employees;" 2>/dev/null)
    
    echo -e "   👥 Users: ${USER_COUNT}"
    echo -e "   🏢 Employees: ${EMPLOYEE_COUNT}"
    echo -e "   📅 Attendance: ${ATTENDANCE_COUNT}"
    echo ""
else
    echo -e "${RED}❌ Database connection failed${NC}"
    echo "Check your database credentials and server status"
fi

echo -e "${BLUE}🔍 Step 3: Checking File Permissions${NC}"
echo -e "${YELLOW}📁 Storage directory permissions:${NC}"
ls -la storage/ | head -5
echo ""

echo -e "${BLUE}🔧 Step 4: Clearing All Caches${NC}"
php artisan config:clear 2>/dev/null && echo -e "${GREEN}✅ Config cache cleared${NC}" || echo -e "${RED}❌ Config cache failed${NC}"
php artisan cache:clear 2>/dev/null && echo -e "${GREEN}✅ Application cache cleared${NC}" || echo -e "${RED}❌ App cache failed${NC}"
php artisan view:clear 2>/dev/null && echo -e "${GREEN}✅ View cache cleared${NC}" || echo -e "${RED}❌ View cache failed${NC}"
php artisan route:clear 2>/dev/null && echo -e "${GREEN}✅ Route cache cleared${NC}" || echo -e "${RED}❌ Route cache failed${NC}"

echo ""
echo -e "${BLUE}🔧 Step 5: Fixing File Permissions${NC}"

# Detect web server user
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
else
    WEB_USER=$(whoami)
    echo -e "${YELLOW}⚠️  Using current user: ${WEB_USER}${NC}"
fi

echo -e "Setting ownership to: ${WEB_USER}"

# Fix ownership (requires sudo)
if [ "$EUID" -eq 0 ]; then
    chown -R "$WEB_USER":"$WEB_USER" . 2>/dev/null && echo -e "${GREEN}✅ Ownership fixed${NC}" || echo -e "${YELLOW}⚠️  Ownership fix failed${NC}"
else
    echo -e "${YELLOW}⚠️  Not running as root, skipping ownership change${NC}"
fi

# Fix permissions
chmod -R 755 . 2>/dev/null && echo -e "${GREEN}✅ General permissions set${NC}" || echo -e "${YELLOW}⚠️  Permission set failed${NC}"
chmod -R 777 storage/ 2>/dev/null && echo -e "${GREEN}✅ Storage permissions set${NC}" || echo -e "${YELLOW}⚠️  Storage permission failed${NC}"
chmod -R 777 bootstrap/cache/ 2>/dev/null && echo -e "${GREEN}✅ Bootstrap cache permissions set${NC}" || echo -e "${YELLOW}⚠️  Bootstrap permission failed${NC}"

echo ""
echo -e "${BLUE}🧪 Step 6: Testing Application${NC}"

# Test Artisan
if php artisan --version >/dev/null 2>&1; then
    echo -e "${GREEN}✅ Laravel Artisan working${NC}"
    php artisan --version
else
    echo -e "${RED}❌ Laravel Artisan not working${NC}"
fi

echo ""
echo -e "${BLUE}📋 Step 7: Recovery Options${NC}"

echo -e "${YELLOW}Available recovery actions:${NC}"
echo "1. Restore database backup"
echo "2. Restore .env backup" 
echo "3. Remove DummyDataSeeder"
echo "4. Show detailed error logs"
echo "5. Exit"
echo ""

read -p "Choose an option (1-5): " choice

case $choice in
    1)
        echo -e "${BLUE}📦 Looking for database backups...${NC}"
        BACKUP_FILES=$(ls hrm_backup_*.sql 2>/dev/null || echo "")
        if [ -n "$BACKUP_FILES" ]; then
            echo "Available backups:"
            ls -la hrm_backup_*.sql
            read -p "Enter backup filename to restore: " BACKUP_FILE
            if [ -f "$BACKUP_FILE" ]; then
                echo -e "${YELLOW}🔄 Restoring database backup...${NC}"
                mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$BACKUP_FILE"
                echo -e "${GREEN}✅ Database restored${NC}"
            else
                echo -e "${RED}❌ Backup file not found${NC}"
            fi
        else
            echo -e "${YELLOW}⚠️  No backup files found${NC}"
        fi
        ;;
    2)
        if [ -f ".env.backup" ]; then
            echo -e "${BLUE}🔄 Restoring .env backup...${NC}"
            cp .env.backup .env
            php artisan config:clear
            echo -e "${GREEN}✅ Environment restored${NC}"
        else
            echo -e "${YELLOW}⚠️  No .env backup found${NC}"
        fi
        ;;
    3)
        if [ -f "database/seeders/DummyDataSeeder.php" ]; then
            echo -e "${BLUE}🔄 Removing DummyDataSeeder...${NC}"
            mv database/seeders/DummyDataSeeder.php database/seeders/DummyDataSeeder.php.disabled
            echo -e "${GREEN}✅ Seeder disabled${NC}"
        else
            echo -e "${YELLOW}⚠️  DummyDataSeeder not found${NC}"
        fi
        ;;
    4)
        echo -e "${BLUE}📄 Detailed error logs:${NC}"
        echo ""
        if [ -f "storage/logs/laravel.log" ]; then
            echo -e "${YELLOW}=== Laravel Error Log (last 50 lines) ===${NC}"
            tail -50 storage/logs/laravel.log
        fi
        echo ""
        if [ -f "/var/log/apache2/error.log" ]; then
            echo -e "${YELLOW}=== Apache Error Log (last 20 lines) ===${NC}"
            tail -20 /var/log/apache2/error.log
        elif [ -f "/var/log/nginx/error.log" ]; then
            echo -e "${YELLOW}=== Nginx Error Log (last 20 lines) ===${NC}"
            tail -20 /var/log/nginx/error.log
        fi
        ;;
    5)
        echo -e "${BLUE}👋 Exiting...${NC}"
        exit 0
        ;;
    *)
        echo -e "${RED}❌ Invalid option${NC}"
        ;;
esac

echo ""
echo -e "${GREEN}🏁 Recovery script completed${NC}"
echo ""
echo -e "${BLUE}📞 Next Steps:${NC}"
echo "1. Test your application in browser"
echo "2. Check if 500 error is resolved"
echo "3. If still having issues, share the error logs above"
echo ""
echo -e "${YELLOW}💡 Common fixes that work:${NC}"
echo "- Clear all caches: php artisan config:clear && php artisan cache:clear"  
echo "- Fix permissions: chmod -R 777 storage/ bootstrap/cache/"
echo "- Check .env database settings"