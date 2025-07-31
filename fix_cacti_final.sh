#!/bin/bash

# Final Cacti Fix Script - Addresses MySQL access issues
# This script handles the specific problems encountered

echo "🔧 Final Cacti Fix Script"
echo "========================"
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

echo "This script will fix the specific MySQL access issues and complete Cacti setup."
echo ""

# Step 1: Check and fix MySQL access
print_status "Step 1: Checking MySQL access..."

# Check if we can access MySQL as root
if mysql -u root -p -e "SELECT 1;" >/dev/null 2>&1; then
    print_success "MySQL root access working"
else
    print_warning "MySQL root access failed. Let's try alternative methods..."
    
    # Try to find MySQL root password or reset it
    echo ""
    echo "MySQL root access issues detected. Please try one of these solutions:"
    echo ""
    echo "Option 1: Reset MySQL root password"
    echo "sudo systemctl stop mariadb"
    echo "sudo mysqld_safe --skip-grant-tables &"
    echo "mysql -u root"
    echo "USE mysql;"
    echo "UPDATE user SET password=PASSWORD('newpassword') WHERE User='root';"
    echo "FLUSH PRIVILEGES;"
    echo "EXIT;"
    echo "sudo systemctl restart mariadb"
    echo ""
    echo "Option 2: Use existing credentials"
    echo "Try: mysql -u root -p (with your current password)"
    echo ""
    echo "Press Enter when you've resolved MySQL access..."
    read -p ""
fi

# Step 2: Fix PHP configuration
print_status "Step 2: Fixing PHP configuration..."

# Create PHP configuration fix
cat > /tmp/php_cacti_fix.ini << 'EOF'
; Cacti PHP Configuration Fix
memory_limit = 800M
max_execution_time = 60
date.timezone = UTC
EOF

echo "PHP configuration fix created at /tmp/php_cacti_fix.ini"
echo "Please run: sudo cp /tmp/php_cacti_fix.ini /etc/php/8.2/apache2/php.ini"
echo "Then run: sudo systemctl restart apache2"
echo ""
echo "Press Enter when you've completed this step..."
read -p ""

# Step 3: Fix MySQL timezone access (alternative method)
print_status "Step 3: Fixing MySQL timezone access..."

# Create SQL script for timezone access
cat > /tmp/fix_timezone_final.sql << 'EOF'
USE mysql;

-- Grant timezone access to cactiuser
GRANT SELECT ON time_zone_name TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_leap_second TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_transition TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_transition_type TO 'cactiuser'@'localhost';

-- Also grant to root for timezone population
GRANT SELECT, INSERT, UPDATE, DELETE ON time_zone_name TO 'root'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON time_zone_leap_second TO 'root'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON time_zone_transition TO 'root'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON time_zone_transition_type TO 'root'@'localhost';

FLUSH PRIVILEGES;
EOF

echo "Timezone access SQL script created at /tmp/fix_timezone_final.sql"
echo "Please run: mysql -u root -p < /tmp/fix_timezone_final.sql"
echo ""

# Step 4: Alternative timezone population
print_status "Step 4: Populating timezone data..."

echo "Since mysql_tzinfo_to_sql is not found, we'll use an alternative method:"
echo ""
echo "Method 1: Install timezone data package"
echo "sudo apt-get install mysql-client"
echo ""
echo "Method 2: Manual timezone population"
echo "mysql -u root -p"
echo "USE mysql;"
echo "SOURCE /usr/share/mysql/mysql_test_data_timezone.sql;"
echo "EXIT;"
echo ""
echo "Method 3: Download and import timezone data"
echo "wget https://dev.mysql.com/get/Downloads/MySQL-8.0/mysql-8.0.36-linux-glibc2.12-x86_64.tar.gz"
echo "tar -xzf mysql-8.0.36-linux-glibc2.12-x86_64.tar.gz"
echo "mysql-8.0.36-linux-glibc2.12-x86_64/bin/mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p mysql"
echo ""
echo "Press Enter when you've completed timezone population..."
read -p ""

# Step 5: Fix MySQL configuration
print_status "Step 5: Fixing MySQL configuration..."

# Create MySQL configuration fix
cat > /tmp/mysql_cacti_fix.cnf << 'EOF'
[mysqld]
# Character set and collation
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Connection settings
max_connections = 200
max_allowed_packet = 16M

# Memory settings
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
EOF

echo "MySQL configuration fix created at /tmp/mysql_cacti_fix.cnf"
echo "Please run: sudo cat /tmp/mysql_cacti_fix.cnf >> /etc/mysql/mariadb.conf.d/50-server.cnf"
echo "Then run: sudo systemctl restart mariadb"
echo ""
echo "Press Enter when you've completed this step..."
read -p ""

# Step 6: Verification
print_status "Step 6: Running verification..."

# Create verification script
cat > /tmp/verify_cacti_final.sh << 'EOF'
#!/bin/bash
echo "🔍 Final Cacti Configuration Verification"
echo "========================================"
echo ""

# Check PHP settings
echo "PHP Settings:"
PHP_MEMORY=$(php -r "echo ini_get('memory_limit');")
PHP_TIME=$(php -r "echo ini_get('max_execution_time');")
PHP_TZ=$(php -r "echo ini_get('date.timezone');")
echo "  Memory Limit: $PHP_MEMORY"
echo "  Max Execution Time: $PHP_TIME"
echo "  Timezone: $PHP_TZ"
echo ""

# Check MySQL timezone access
echo "MySQL Timezone Access:"
if mysql -u cactiuser -pcactipassword -e "SELECT COUNT(*) FROM mysql.time_zone_name LIMIT 1;" >/dev/null 2>&1; then
    echo "  ✅ Timezone access working"
else
    echo "  ❌ Timezone access failed"
    echo "  Trying alternative check..."
    if mysql -u cactiuser -pcactipassword -e "SHOW TABLES LIKE 'time_zone%';" >/dev/null 2>&1; then
        echo "  ⚠️  Timezone tables exist but access may be limited"
    else
        echo "  ❌ Timezone tables not found"
    fi
fi

# Check MySQL settings
echo ""
echo "MySQL Settings:"
MYSQL_CHARSET=$(mysql -u cactiuser -pcactipassword -e "SHOW VARIABLES LIKE 'character_set_server';" 2>/dev/null | tail -1 | awk '{print $2}')
MYSQL_CONNECTIONS=$(mysql -u cactiuser -pcactipassword -e "SHOW VARIABLES LIKE 'max_connections';" 2>/dev/null | tail -1 | awk '{print $2}')
echo "  Character Set: $MYSQL_CHARSET"
echo "  Max Connections: $MYSQL_CONNECTIONS"
echo ""

# Check Cacti access
echo "Cacti Access:"
if curl -s http://localhost/cacti/ >/dev/null; then
    echo "  ✅ Cacti is accessible"
else
    echo "  ❌ Cacti is not accessible"
fi
echo ""

echo "🎯 Next Steps:"
echo "  1. Go to: http://localhost/cacti/install/"
echo "  2. Run pre-installation check"
echo "  3. If all checks pass, proceed with installation"
echo "  4. Login with admin/admin"
echo ""
echo "📞 If issues persist:"
echo "  - Check Apache error logs: sudo tail -f /var/log/apache2/error.log"
echo "  - Check MySQL error logs: sudo tail -f /var/log/mysql/error.log"
echo "  - Check Cacti logs: tail -f /var/www/html/cacti/log/cacti.log"
EOF

chmod +x /tmp/verify_cacti_final.sh

echo "Verification script created at /tmp/verify_cacti_final.sh"
echo "Please run: /tmp/verify_cacti_final.sh"
echo ""

# Final instructions
echo "🎉 Final Cacti Fix Script Complete!"
echo "==================================="
echo ""
echo "📋 Summary of required actions:"
echo "==============================="
echo ""
echo "1. Fix PHP settings:"
echo "   sudo cp /tmp/php_cacti_fix.ini /etc/php/8.2/apache2/php.ini"
echo "   sudo systemctl restart apache2"
echo ""
echo "2. Fix MySQL timezone access:"
echo "   mysql -u root -p < /tmp/fix_timezone_final.sql"
echo ""
echo "3. Populate timezone data (choose one method):"
echo "   sudo apt-get install mysql-client"
echo "   OR manually populate timezone data"
echo ""
echo "4. Fix MySQL configuration:"
echo "   sudo cat /tmp/mysql_cacti_fix.cnf >> /etc/mysql/mariadb.conf.d/50-server.cnf"
echo "   sudo systemctl restart mariadb"
echo ""
echo "5. Verify fixes:"
echo "   /tmp/verify_cacti_final.sh"
echo ""
echo "6. Test Cacti:"
echo "   http://localhost/cacti/install/"
echo ""
print_success "Final fix script completed! Follow the steps above to complete the fixes." 