# Server Deployment Guide for HRM System

## 📋 Prerequisites

Before deploying to your server, ensure you have:

- SSH access to your server
- PHP 8.0+ installed
- MySQL/MariaDB database
- Composer installed
- Laravel application already deployed
- Backup of existing database (IMPORTANT!)

## 🚀 Step-by-Step Deployment

### Step 1: Upload the Seeder File

**Option A: Using SCP/SFTP**
```bash
# Copy the seeder file to your server
scp /home/barcosis/hrm2/database/seeders/DummyDataSeeder.php username@your-server:/path/to/your/hrm/database/seeders/
```

**Option B: Using Git (Recommended)**
```bash
# On your local machine, commit the changes
cd /home/barcosis/hrm2
git add database/seeders/DummyDataSeeder.php
git add DUMMY_DATA_SUMMARY.md
git commit -m "Add dummy data seeder and fix footer text

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"

# Push to your repository
git push origin main

# On your server, pull the changes
ssh username@your-server
cd /path/to/your/hrm
git pull origin main
```

### Step 2: Update Environment Configuration

**SSH into your server and update .env:**
```bash
ssh username@your-server
cd /path/to/your/hrm

# Backup current .env
cp .env .env.backup

# Edit the .env file
nano .env
```

**Update this line in .env:**
```env
APP_NAME=Barcosys
```

### Step 3: Database Backup (CRITICAL!)

**Always backup before making changes:**
```bash
# Create database backup
mysqldump -u username -p database_name > hrm_backup_$(date +%Y%m%d_%H%M%S).sql

# Or if using specific host/port
mysqldump -h host -P port -u username -p database_name > hrm_backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 4: Run Database Operations

**Execute the following commands:**
```bash
cd /path/to/your/hrm

# Install/update composer dependencies (if needed)
composer install --optimize-autoloader --no-dev

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run migrations (if any new ones)
php artisan migrate

# Run the dummy data seeder
php artisan db:seed --class=DummyDataSeeder
```

### Step 5: Set Footer Text in Database

**Add footer text setting:**
```bash
# Connect to MySQL and run this command
mysql -u username -p database_name

# In MySQL prompt, run:
INSERT INTO settings (name, value, created_by, created_at, updated_at) 
VALUES ('footer_text', 'Barcosys', 1, NOW(), NOW()) 
ON DUPLICATE KEY UPDATE value = 'Barcosys';

# Exit MySQL
EXIT;
```

### Step 6: Set Proper Permissions

**Ensure Laravel has proper permissions:**
```bash
# Set ownership (adjust user/group as needed)
sudo chown -R www-data:www-data /path/to/your/hrm
# or
sudo chown -R nginx:nginx /path/to/your/hrm

# Set directory permissions
find /path/to/your/hrm -type d -exec chmod 755 {} \;
find /path/to/your/hrm -type f -exec chmod 644 {} \;

# Storage and cache directories need write access
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/
```

### Step 7: Final Cache Clear

**Clear all application caches:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🧪 Testing & Verification

### Test 1: Login Page Footer
1. Visit your login page
2. Verify footer shows "Barcosys" instead of "Laravel" or "2026@laravel"

### Test 2: Dummy Data Verification
```bash
# Check user count
mysql -u username -p database_name -e "SELECT COUNT(*) as total_users FROM users;"

# Check employee count
mysql -u username -p database_name -e "SELECT COUNT(*) as total_employees FROM employees;"

# Check attendance records
mysql -u username -p database_name -e "SELECT COUNT(*) as total_attendance FROM attendance_employees;"
```

### Test 3: Login with Dummy Accounts
Try logging in with these sample accounts:
- **Email**: brian.robinson1@barcosys.com
- **Password**: password123

## 📱 Alternative Deployment Methods

### Method 1: Using Docker (if containerized)
```bash
# If your application is containerized
docker-compose exec app php artisan db:seed --class=DummyDataSeeder
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
```

### Method 2: Using CI/CD Pipeline
Add to your deployment script:
```yaml
# In your CI/CD pipeline (e.g., .github/workflows/deploy.yml)
- name: Run Database Seeder
  run: |
    php artisan db:seed --class=DummyDataSeeder
    php artisan config:clear
    php artisan cache:clear
```

## 🚨 Troubleshooting

### Common Issues & Solutions

**Issue 1: Permission Denied**
```bash
# Fix Laravel permissions
sudo chown -R www-data:www-data /path/to/your/hrm
chmod -R 755 /path/to/your/hrm
chmod -R 777 storage/ bootstrap/cache/
```

**Issue 2: Database Connection Failed**
```bash
# Test database connection
php artisan tinker
# In tinker:
DB::connection()->getPdo();
```

**Issue 3: Seeder Fails with Duplicate Entries**
```bash
# Clear existing dummy data first
mysql -u username -p database_name -e "
DELETE FROM attendance_employees WHERE employee_id IN (SELECT id FROM employees WHERE email LIKE '%@barcosys.com');
DELETE FROM employees WHERE email LIKE '%@barcosys.com';
DELETE FROM users WHERE email LIKE '%@barcosys.com';
"
```

**Issue 4: Cache Issues**
```bash
# Complete cache reset
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize
```

## 🔒 Security Considerations

1. **Change Default Passwords**: After testing, change all dummy user passwords
2. **Environment Variables**: Keep .env file secure
3. **Database Backup**: Always backup before major changes
4. **Access Control**: Limit who can run seeders on production

## 📊 Expected Results

After successful deployment:
- ✅ 50+ new user accounts created
- ✅ ~9,600 attendance records (2 months of data)
- ✅ Login footer displays "Barcosys"
- ✅ Additional departments and designations
- ✅ Holidays and leave types added
- ✅ All test accounts work with password: `password123`

## 🔄 Rollback Plan

If something goes wrong:
```bash
# Restore database from backup
mysql -u username -p database_name < hrm_backup_YYYYMMDD_HHMMSS.sql

# Restore .env file
cp .env.backup .env

# Clear caches
php artisan config:clear
php artisan cache:clear
```

---

**⚠️ Important Notes:**
- Always test in staging environment first
- Keep database backups before any major changes
- Monitor server resources during seeder execution
- Verify all functionality after deployment