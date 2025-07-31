# 🔧 Cacti Configuration Fix Guide

## 📊 **CURRENT ISSUES IDENTIFIED**

Based on your pre-installation check, here are the issues that need to be fixed:

### **❌ Critical Issues**
1. **MySQL TimeZone Access**: Cacti database user lacks access to MySQL timezone database
2. **PHP Memory Limit**: Currently unlimited (-1), needs to be set to 800M
3. **PHP Max Execution Time**: Currently 0, needs to be set to 60 seconds

### **⚠️ Performance Issues**
1. **MySQL Memory Settings**: Various memory-related settings need optimization
2. **Character Set**: Should use utf8mb4_unicode_ci collation
3. **InnoDB Settings**: Buffer pool and other InnoDB settings need adjustment

---

## 🚀 **STEP-BY-STEP FIXES**

### **Step 1: Fix PHP Configuration**

**File**: `/etc/php/8.2/apache2/php.ini`

**Find and change these lines**:
```ini
# Change from -1 to 800M
memory_limit = 800M

# Change from 0 to 60
max_execution_time = 60

# Verify timezone is set (should already be correct)
date.timezone = UTC
```

**Commands to edit**:
```bash
sudo nano /etc/php/8.2/apache2/php.ini
```

### **Step 2: Fix MySQL TimeZone Access**

**Connect to MySQL as root**:
```bash
mysql -u root -p
```

**Execute these SQL commands**:
```sql
USE mysql;
GRANT SELECT ON time_zone_name TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_leap_second TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_transition TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_transition_type TO 'cactiuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Populate timezone data**:
```bash
sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u root -p mysql
```

### **Step 3: Optimize MySQL Performance**

**File**: `/etc/mysql/mariadb.conf.d/50-server.cnf`

**Add/update these settings in the [mysqld] section**:
```ini
[mysqld]
# Character set and collation
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Connection settings
max_connections = 200
max_allowed_packet = 16M

# Memory settings (adjust based on your system RAM - 2GB system example)
max_heap_table_size = 256M
tmp_table_size = 256M
join_buffer_size = 262144
sort_buffer_size = 2097152

# InnoDB settings
innodb_buffer_pool_size = 512M
innodb_file_per_table = ON
innodb_doublewrite = OFF
innodb_lock_wait_timeout = 50
innodb_flush_method = O_DIRECT
innodb_use_atomic_writes = ON
```

### **Step 4: Restart Services**

```bash
sudo systemctl restart apache2
sudo systemctl restart mariadb
```

### **Step 5: Verify Fixes**

**Run the diagnostic script**:
```bash
php fix_cacti_config.php
```

**Check Cacti pre-installation**:
- Go to: `http://localhost/cacti/install/`
- Run the pre-installation check again

---

## 🔍 **VERIFICATION COMMANDS**

### **Check PHP Settings**
```bash
php -r "echo 'Memory: ' . ini_get('memory_limit') . PHP_EOL;"
php -r "echo 'Max Time: ' . ini_get('max_execution_time') . PHP_EOL;"
php -r "echo 'Timezone: ' . ini_get('date.timezone') . PHP_EOL;"
```

### **Check MySQL TimeZone Access**
```bash
mysql -u cactiuser -pcactipassword -e "SELECT COUNT(*) FROM mysql.time_zone_name;"
```

### **Check MySQL Settings**
```bash
mysql -u root -p -e "SHOW VARIABLES LIKE 'character_set_server';"
mysql -u root -p -e "SHOW VARIABLES LIKE 'max_connections';"
mysql -u root -p -e "SHOW VARIABLES LIKE 'innodb_buffer_pool_size';"
```

---

## 📋 **EXPECTED RESULTS**

### **After Fixes, You Should See**:
- ✅ **PHP Memory Limit**: 800M
- ✅ **PHP Max Execution Time**: 60
- ✅ **MySQL TimeZone Access**: No errors
- ✅ **Character Set**: utf8mb4
- ✅ **Collation**: utf8mb4_unicode_ci
- ✅ **Max Connections**: 200
- ✅ **InnoDB Buffer Pool**: 512M

### **Cacti Pre-Installation Check Should Show**:
- ✅ All PHP recommendations passed
- ✅ MySQL TimeZone support working
- ✅ MySQL settings optimized
- ✅ Ready for Cacti installation

---

## 🚨 **TROUBLESHOOTING**

### **If MySQL TimeZone Still Fails**:
```sql
-- As root user
USE mysql;
SHOW TABLES LIKE 'time_zone%';
-- If tables don't exist, populate them:
mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p mysql
```

### **If PHP Settings Don't Apply**:
```bash
# Check if Apache is using the correct php.ini
php -i | grep "Loaded Configuration File"
# Restart Apache
sudo systemctl restart apache2
```

### **If MySQL Settings Don't Apply**:
```bash
# Check MySQL configuration
sudo mysql -u root -p -e "SHOW VARIABLES LIKE 'character_set_server';"
# Restart MariaDB
sudo systemctl restart mariadb
```

---

## 🎯 **NEXT STEPS**

1. **Complete the fixes** using the steps above
2. **Run verification commands** to confirm fixes
3. **Re-run Cacti pre-installation check**
4. **Proceed with Cacti installation** if all checks pass
5. **Login to Cacti** with admin/admin credentials

---

## 📞 **SUPPORT**

If you encounter issues:
1. Check the error logs: `/var/log/apache2/error.log`
2. Check MySQL logs: `/var/log/mysql/error.log`
3. Run the diagnostic script: `php fix_cacti_config.php`

**Your Cacti installation will be ready once these configuration issues are resolved!** 🚀 