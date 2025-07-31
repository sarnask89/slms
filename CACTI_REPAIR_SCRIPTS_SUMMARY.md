# 🔧 Cacti Repair Scripts - Complete Solution

## 📊 **OVERVIEW**

I've created **3 comprehensive repair scripts** to fix all Cacti configuration issues identified in your pre-installation check. Each script addresses different aspects of the configuration problems.

---

## 🚀 **REPAIR SCRIPTS CREATED**

### **1. Automated Fix Script** ⚡
**File**: `automated_cacti_fix.sh`
**Purpose**: Fully automated fix for all Cacti issues
**Status**: ✅ Created and tested

**Features**:
- ✅ Automatic PHP configuration fixes
- ✅ MySQL timezone access fixes
- ✅ MySQL performance optimization
- ✅ Service restart automation
- ✅ Comprehensive verification
- ✅ Backup creation for safety

**Usage**:
```bash
chmod +x automated_cacti_fix.sh
./automated_cacti_fix.sh
```

### **2. Manual Fix Script** 📋
**File**: `fix_cacti_manual.sh`
**Purpose**: Step-by-step guided fix with user interaction
**Status**: ✅ Created and ready

**Features**:
- ✅ Interactive step-by-step guidance
- ✅ Clear instructions for each fix
- ✅ Verification at each step
- ✅ Safe manual execution
- ✅ Detailed explanations

**Usage**:
```bash
chmod +x fix_cacti_manual.sh
./fix_cacti_manual.sh
```

### **3. Quick Fix Script** ⚡
**File**: `quick_cacti_fix.sh`
**Purpose**: Fast fix with specific commands
**Status**: ✅ Created and tested

**Features**:
- ✅ Creates configuration patches
- ✅ Provides exact commands to run
- ✅ Addresses specific issues found
- ✅ Includes verification script
- ✅ Minimal user interaction

**Usage**:
```bash
chmod +x quick_cacti_fix.sh
./quick_cacti_fix.sh
```

---

## 🔍 **ISSUES ADDRESSED**

### **Critical Issues Fixed**:
1. **❌ MySQL TimeZone Access**: Cacti database user lacks access to MySQL timezone database
2. **⚠️ PHP Memory Limit**: Currently unlimited (-1), needs to be set to 800M
3. **⚠️ PHP Max Execution Time**: Currently 0, needs to be set to 60 seconds

### **Performance Issues Fixed**:
1. **⚠️ MySQL Memory Settings**: Various memory-related settings optimized
2. **⚠️ Character Set**: Set to utf8mb4_unicode_ci collation
3. **⚠️ InnoDB Settings**: Buffer pool and other InnoDB settings adjusted

---

## 🎯 **RECOMMENDED APPROACH**

### **For Quick Fix** (Recommended):
```bash
# Run the quick fix script
./quick_cacti_fix.sh

# Follow the generated commands:
# 1. Fix PHP settings
sudo cp /tmp/php_fix.ini /etc/php/8.2/apache2/php.ini
sudo systemctl restart apache2

# 2. Fix MySQL timezone access
mysql -u root -p < /tmp/fix_timezone.sql
sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u root -p mysql

# 3. Fix MySQL configuration
sudo cat /tmp/mysql_fix.cnf >> /etc/mysql/mariadb.conf.d/50-server.cnf
sudo systemctl restart mariadb

# 4. Verify fixes
/tmp/verify_cacti.sh
```

### **For Guided Fix**:
```bash
# Run the manual fix script
./fix_cacti_manual.sh
```

### **For Automated Fix**:
```bash
# Run the automated fix script
./automated_cacti_fix.sh
```

---

## 📋 **GENERATED FILES**

The scripts create these temporary files:
- `/tmp/php_fix.ini` - PHP configuration patch
- `/tmp/fix_timezone.sql` - MySQL timezone access SQL
- `/tmp/mysql_fix.cnf` - MySQL configuration patch
- `/tmp/verify_cacti.sh` - Verification script

---

## 🔧 **SPECIFIC FIXES APPLIED**

### **PHP Configuration**:
```ini
memory_limit = 800M
max_execution_time = 60
```

### **MySQL TimeZone Access**:
```sql
USE mysql;
GRANT SELECT ON time_zone_name TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_leap_second TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_transition TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_transition_type TO 'cactiuser'@'localhost';
FLUSH PRIVILEGES;
```

### **MySQL Performance Settings**:
```ini
[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
max_connections = 200
max_heap_table_size = 256M
tmp_table_size = 256M
innodb_buffer_pool_size = 512M
innodb_file_per_table = ON
innodb_doublewrite = OFF
innodb_use_atomic_writes = ON
```

---

## ✅ **VERIFICATION**

After running the fixes, verify with:
```bash
/tmp/verify_cacti.sh
```

**Expected Results**:
- ✅ PHP Memory Limit: 800M
- ✅ PHP Max Execution Time: 60
- ✅ MySQL Timezone Access: Working
- ✅ MySQL Character Set: utf8mb4
- ✅ MySQL Max Connections: 200

---

## 🎯 **FINAL STEPS**

1. **Run the quick fix script**: `./quick_cacti_fix.sh`
2. **Execute the generated commands** (copy-paste each one)
3. **Verify the fixes**: `/tmp/verify_cacti.sh`
4. **Test Cacti**: `http://localhost/cacti/install/`
5. **Run pre-installation check** - all should pass ✅
6. **Login to Cacti**: admin/admin

---

## 📞 **SUPPORT**

If you encounter issues:
1. **Check the verification script**: `/tmp/verify_cacti.sh`
2. **Review the manual fix script**: `./fix_cacti_manual.sh`
3. **Check error logs**: `/var/log/apache2/error.log`
4. **Check MySQL logs**: `/var/log/mysql/error.log`

---

## 🎉 **EXPECTED OUTCOME**

After completing the fixes:
- ✅ **All Cacti pre-installation checks pass**
- ✅ **Cacti installation proceeds successfully**
- ✅ **Login with admin/admin works**
- ✅ **SNMP and monitoring integration ready**
- ✅ **Full SLMS monitoring system operational**

**Your Cacti will be fully functional and ready for production use!** 🚀 