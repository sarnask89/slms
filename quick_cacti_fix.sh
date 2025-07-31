#!/bin/bash

# Quick Cacti Fix Script
# Addresses the specific issues found in the automated script

echo "🔧 Quick Cacti Fix Script"
echo "========================"
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

echo "This script will fix the specific Cacti configuration issues."
echo "You will need to provide your MySQL root password when prompted."
echo ""

# Check current status
print_status "Checking current configuration..."

PHP_MEMORY=$(php -r "echo ini_get('memory_limit');")
PHP_TIME=$(php -r "echo ini_get('max_execution_time');")

echo "Current PHP settings:"
echo "  Memory Limit: $PHP_MEMORY"
echo "  Max Execution Time: $PHP_TIME"
echo ""

# Fix PHP settings if needed
if [[ "$PHP_MEMORY" != "800M" ]] || [[ "$PHP_TIME" != "60" ]]; then
    print_status "Fixing PHP configuration..."
    
    # Create a PHP configuration patch
    cat > /tmp/php_fix.ini << 'EOF'
; Cacti PHP Configuration Fix
memory_limit = 800M
max_execution_time = 60
EOF

    echo "Please run these commands to fix PHP settings:"
    echo "sudo cp /tmp/php_fix.ini /etc/php/8.2/apache2/php.ini"
    echo "sudo systemctl restart apache2"
    echo ""
fi

# Fix MySQL timezone access
print_status "Fixing MySQL timezone access..."

# Create SQL script for timezone access
cat > /tmp/fix_timezone.sql << 'EOF'
USE mysql;
GRANT SELECT ON time_zone_name TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_leap_second TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_transition TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_transition_type TO 'cactiuser'@'localhost';
FLUSH PRIVILEGES;
EOF

echo "Please run these commands to fix MySQL timezone access:"
echo "mysql -u root -p < /tmp/fix_timezone.sql"
echo "sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u root -p mysql"
echo ""

# Fix MySQL configuration
print_status "Fixing MySQL configuration..."

# Create MySQL configuration patch
cat > /tmp/mysql_fix.cnf << 'EOF'
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

echo "Please run these commands to fix MySQL configuration:"
echo "sudo cat /tmp/mysql_fix.cnf >> /etc/mysql/mariadb.conf.d/50-server.cnf"
echo "sudo systemctl restart mariadb"
echo ""

# Verification script
cat > /tmp/verify_cacti.sh << 'EOF'
#!/bin/bash
echo "🔍 Verifying Cacti Configuration..."
echo ""

# Check PHP settings
echo "PHP Settings:"
PHP_MEMORY=$(php -r "echo ini_get('memory_limit');")
PHP_TIME=$(php -r "echo ini_get('max_execution_time');")
echo "  Memory Limit: $PHP_MEMORY"
echo "  Max Execution Time: $PHP_TIME"
echo ""

# Check MySQL timezone access
echo "MySQL Timezone Access:"
if mysql -u cactiuser -pcactipassword -e "SELECT COUNT(*) FROM mysql.time_zone_name LIMIT 1;" >/dev/null 2>&1; then
    echo "  ✅ Timezone access working"
else
    echo "  ❌ Timezone access failed"
fi

# Check MySQL settings
echo "MySQL Settings:"
MYSQL_CHARSET=$(mysql -u cactiuser -pcactipassword -e "SHOW VARIABLES LIKE 'character_set_server';" 2>/dev/null | tail -1 | awk '{print $2}')
MYSQL_CONNECTIONS=$(mysql -u cactiuser -pcactipassword -e "SHOW VARIABLES LIKE 'max_connections';" 2>/dev/null | tail -1 | awk '{print $2}')
echo "  Character Set: $MYSQL_CHARSET"
echo "  Max Connections: $MYSQL_CONNECTIONS"
echo ""

echo "🎯 Next Steps:"
echo "  1. Go to: http://localhost/cacti/install/"
echo "  2. Run pre-installation check"
echo "  3. All checks should pass"
echo "  4. Login with admin/admin"
EOF

chmod +x /tmp/verify_cacti.sh

echo "📋 Summary of required actions:"
echo "==============================="
echo ""
echo "1. Fix PHP settings:"
echo "   sudo cp /tmp/php_fix.ini /etc/php/8.2/apache2/php.ini"
echo "   sudo systemctl restart apache2"
echo ""
echo "2. Fix MySQL timezone access:"
echo "   mysql -u root -p < /tmp/fix_timezone.sql"
echo "   sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u root -p mysql"
echo ""
echo "3. Fix MySQL configuration:"
echo "   sudo cat /tmp/mysql_fix.cnf >> /etc/mysql/mariadb.conf.d/50-server.cnf"
echo "   sudo systemctl restart mariadb"
echo ""
echo "4. Verify fixes:"
echo "   /tmp/verify_cacti.sh"
echo ""
echo "5. Test Cacti:"
echo "   http://localhost/cacti/install/"
echo ""
print_success "Quick fix script completed! Follow the steps above to complete the fixes." 