# Quick Server Deployment Checklist ✅

## Before Starting
- [ ] **BACKUP DATABASE** (Most Important!)
- [ ] SSH access to server
- [ ] Know your application path
- [ ] Database credentials ready

## Files to Upload/Deploy
- [ ] `database/seeders/DummyDataSeeder.php`
- [ ] `deploy-dummy-data.sh` (optional - automated script)

## Quick Manual Steps

### 1. Connect to Server
```bash
ssh username@your-server-ip
cd /path/to/your/hrm-application
```

### 2. Backup Database
```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

### 3. Update Environment
```bash
nano .env
# Change: APP_NAME=Barcosys
```

### 4. Upload Seeder
Upload `DummyDataSeeder.php` to `database/seeders/` folder

### 5. Run Commands
```bash
# Clear cache
php artisan config:clear && php artisan cache:clear

# Run seeder  
php artisan db:seed --class=DummyDataSeeder

# Set footer text
mysql -u username -p database_name
INSERT INTO settings (name, value, created_by, created_at, updated_at) VALUES ('footer_text', 'Barcosys', 1, NOW(), NOW());
```

### 6. Test
- [ ] Login page shows "Barcosys" in footer
- [ ] Can login with: brian.robinson1@barcosys.com / password123
- [ ] Attendance data visible in reports

## OR Use Automated Script
```bash
# Make script executable and run
chmod +x deploy-dummy-data.sh
./deploy-dummy-data.sh
```

## Results Expected
✅ 50+ users created  
✅ ~9,600 attendance records  
✅ Footer fixed to show "Barcosys"  
✅ Additional departments & designations  
✅ Test accounts ready for use