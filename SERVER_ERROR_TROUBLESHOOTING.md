# 🚨 Server 500 Error - Emergency Troubleshooting Guide

## 🔍 **STEP 1: Check Laravel Error Logs (CRITICAL)**

**SSH into your server and check the error logs:**
```bash
ssh username@your-server
cd /path/to/your/hrm-application

# Check the latest Laravel error logs
tail -50 storage/logs/laravel.log

# Or check today's log specifically
tail -100 storage/logs/laravel-$(date +%Y-%m-%d).log
```

**Look for errors related to:**
- Database connection failures
- Permission issues
- Seeder execution problems
- Missing files or classes

---

## 🔍 **STEP 2: Quick Database Check**

**Verify if data was actually created:**
```bash
# Connect to your database
mysql -u username -p database_name

# Check if users were created
SELECT COUNT(*) as user_count FROM users;

# Check if employees were created
SELECT COUNT(*) as employee_count FROM employees;

# Check if attendance was created
SELECT COUNT(*) as attendance_count FROM attendance_employees;

# Exit MySQL
EXIT;
```

---

## 🔍 **STEP 3: Check Application Status**

**Test basic Laravel functionality:**
```bash
# Test if Laravel can connect to database
php artisan tinker
# In tinker, run:
DB::connection()->getPdo();
# Press Ctrl+C to exit

# Clear all caches (might fix 500 error)
php artisan config:clear
php artisan cache:clear  
php artisan view:clear
php artisan route:clear

# Test if app works now
curl -I http://your-domain.com
```

---

## 🔍 **STEP 4: Check File Permissions**

**Fix common permission issues:**
```bash
cd /path/to/your/hrm-application

# Check current permissions
ls -la storage/
ls -la bootstrap/cache/

# Fix permissions
sudo chown -R www-data:www-data . 
# OR (depending on your server)
sudo chown -R nginx:nginx .

# Set correct permissions
chmod -R 755 .
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/
```

---

## 🚨 **EMERGENCY ROLLBACK** (If nothing works)

**Restore your system to working state:**

### Restore Database Backup
```bash
# If you created a backup before deployment
mysql -u username -p database_name < hrm_backup_YYYYMMDD_HHMMSS.sql
```

### Restore Environment File
```bash
# If you have .env.backup
cp .env.backup .env

# Clear caches after restoration
php artisan config:clear
php artisan cache:clear
```

### Remove Problematic Seeder
```bash
# Temporarily remove the seeder if it's causing issues
mv database/seeders/DummyDataSeeder.php database/seeders/DummyDataSeeder.php.backup
```

---

## 🔧 **Common 500 Error Causes & Solutions**

### **Cause 1: Database Connection Issues**
**Symptoms:** Can't connect to database
**Solution:**
```bash
# Check .env database settings
cat .env | grep DB_

# Test connection
php artisan tinker
DB::connection()->getPdo();
```

### **Cause 2: Seeder Memory Issues**
**Symptoms:** Server runs out of memory during seeding
**Solution:**
```bash
# Increase PHP memory limit temporarily
php -d memory_limit=512M artisan db:seed --class=DummyDataSeeder

# Or edit php.ini
memory_limit = 512M
```

### **Cause 3: Missing Dependencies**
**Symptoms:** Class not found errors
**Solution:**
```bash
composer dump-autoload
php artisan config:clear
```

### **Cause 4: Permission Problems**
**Symptoms:** Cannot write to storage/logs
**Solution:**
```bash
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/
```

---

## 🔍 **Debugging Commands to Run**

**Run these commands and send me the output:**

```bash
# 1. Check PHP version and extensions
php -v
php -m | grep -i mysql

# 2. Check Laravel installation
php artisan --version

# 3. Check database connection
php artisan tinker
DB::connection()->getPdo();

# 4. Check recent logs (last 20 lines)
tail -20 storage/logs/laravel.log

# 5. Check if seeder file exists
ls -la database/seeders/DummyDataSeeder.php

# 6. Check web server error logs
# For Apache:
tail -20 /var/log/apache2/error.log
# For Nginx:  
tail -20 /var/log/nginx/error.log

# 7. Check current user and permissions
whoami
ls -la storage/
```

---

## 📱 **Quick Recovery Script**

**If you need to quickly restore:**

```bash
#!/bin/bash
# Save as recover.sh and run: chmod +x recover.sh && ./recover.sh

echo "🚨 Emergency Recovery Started..."

# Restore database if backup exists
if [ -f "hrm_backup_*.sql" ]; then
    echo "📦 Restoring database..."
    mysql -u username -p database_name < $(ls -t hrm_backup_*.sql | head -1)
fi

# Restore environment
if [ -f ".env.backup" ]; then
    echo "⚙️ Restoring environment..."
    cp .env.backup .env
fi

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Fix permissions
echo "🔐 Fixing permissions..."
sudo chown -R www-data:www-data .
chmod -R 755 .
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/

echo "✅ Recovery completed. Test your application now."
```

---

## 📞 **Next Steps**

**Please run the debugging commands above and share:**

1. **Laravel error log output** (`tail -20 storage/logs/laravel.log`)
2. **Database connection test result**
3. **User/employee count from database**
4. **Any specific error messages you see**

**I'll help you fix the specific issue once I see the error details!**

---

## ⚠️ **Important Notes**

- **Don't panic** - we can fix this!
- **Always backup** before trying fixes
- **Test in staging** environment if possible
- **Document** what commands you run for troubleshooting

**The 500 error is usually fixable once we identify the root cause.**