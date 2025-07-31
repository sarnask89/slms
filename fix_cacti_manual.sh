#!/bin/bash

# Manual Cacti Configuration Fix Script
# Provides step-by-step instructions for fixing Cacti issues

echo "🔧 Manual Cacti Configuration Fix Script"
echo "======================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

echo "This script will guide you through fixing Cacti configuration issues."
echo "You will need sudo privileges for some steps."
echo ""

# Step 1: PHP Configuration
echo "Step 1: Fixing PHP Configuration"
echo "================================"
echo ""

print_status "You need to edit the PHP configuration file:"
echo "sudo nano /etc/php/8.2/apache2/php.ini"
echo ""
echo "Find and change these lines:"
echo "  memory_limit = 800M"
echo "  max_execution_time = 60"
echo ""
echo "Press Enter when you've made these changes..."
read -p ""

# Step 2: MySQL TimeZone Access
echo ""
echo "Step 2: Fixing MySQL TimeZone Access"
echo "===================================="
echo ""

print_status "You need to connect to MySQL as root and run these commands:"
echo ""
echo "mysql -u root -p"
echo ""
echo "Then run these SQL commands:"
echo "USE mysql;"
echo "GRANT SELECT ON time_zone_name TO 'cactiuser'@'localhost';"
echo "GRANT SELECT ON time_zone_leap_second TO 'cactiuser'@'localhost';"
echo "GRANT SELECT ON time_zone_transition TO 'cactiuser'@'localhost';"
echo "GRANT SELECT ON time_zone_transition_type TO 'cactiuser'@'localhost';"
echo "FLUSH PRIVILEGES;"
echo "EXIT;"
echo ""
echo "Then populate timezone data:"
echo "sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u root -p mysql"
echo ""
echo "Press Enter when you've completed these steps..."
read -p ""

# Step 3: MySQL Performance Optimization
echo ""
echo "Step 3: Optimizing MySQL Performance"
echo "===================================="
echo ""

print_status "You need to edit the MySQL configuration file:"
echo "sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf"
echo ""
echo "Add these settings to the [mysqld] section:"
echo ""
echo "[mysqld]"
echo "# Character set and collation"
echo "character-set-server = utf8mb4"
echo "collation-server = utf8mb4_unicode_ci"
echo ""
echo "# Connection settings"
echo "max_connections = 200"
echo "max_allowed_packet = 16M"
echo ""
echo "# Memory settings"
echo "max_heap_table_size = 256M"
echo "tmp_table_size = 256M"
echo "join_buffer_size = 262144"
echo "sort_buffer_size = 2097152"
echo ""
echo "# InnoDB settings"
echo "innodb_buffer_pool_size = 512M"
echo "innodb_file_per_table = ON"
echo "innodb_doublewrite = OFF"
echo "innodb_lock_wait_timeout = 50"
echo "innodb_flush_method = O_DIRECT"
echo "innodb_use_atomic_writes = ON"
echo ""
echo "Press Enter when you've made these changes..."
read -p ""

# Step 4: Restart Services
echo ""
echo "Step 4: Restarting Services"
echo "==========================="
echo ""

print_status "Now restart the services:"
echo "sudo systemctl restart apache2"
echo "sudo systemctl restart mariadb"
echo ""
echo "Press Enter when you've restarted the services..."
read -p ""

# Step 5: Verification
echo ""
echo "Step 5: Verification"
echo "==================="
echo ""

print_status "Let's verify the fixes..."

# Check PHP settings
echo "Checking PHP settings..."
PHP_MEMORY=$(php -r "echo ini_get('memory_limit');")
PHP_TIME=$(php -r "echo ini_get('max_execution_time');")
PHP_TZ=$(php -r "echo ini_get('date.timezone');")

echo "  Memory Limit: $PHP_MEMORY"
echo "  Max Execution Time: $PHP_TIME"
echo "  Timezone: $PHP_TZ"

if [[ "$PHP_MEMORY" == "800M" ]]; then
    print_success "PHP memory limit is correct"
else
    print_warning "PHP memory limit is $PHP_MEMORY (expected 800M)"
fi

if [[ "$PHP_TIME" == "60" ]]; then
    print_success "PHP max execution time is correct"
else
    print_warning "PHP max execution time is $PHP_TIME (expected 60)"
fi

# Check MySQL timezone access
echo ""
echo "Checking MySQL timezone access..."
if mysql -u cactiuser -pcactipassword -e "SELECT COUNT(*) FROM mysql.time_zone_name LIMIT 1;" >/dev/null 2>&1; then
    print_success "MySQL timezone access is working"
else
    print_error "MySQL timezone access failed"
    echo "You may need to complete Step 2 again"
fi

# Check MySQL settings
echo ""
echo "Checking MySQL settings..."
MYSQL_CHARSET=$(mysql -u cactiuser -pcactipassword -e "SHOW VARIABLES LIKE 'character_set_server';" 2>/dev/null | tail -1 | awk '{print $2}')
MYSQL_CONNECTIONS=$(mysql -u cactiuser -pcactipassword -e "SHOW VARIABLES LIKE 'max_connections';" 2>/dev/null | tail -1 | awk '{print $2}')

echo "  Character Set: $MYSQL_CHARSET"
echo "  Max Connections: $MYSQL_CONNECTIONS"

if [[ "$MYSQL_CHARSET" == "utf8mb4" ]]; then
    print_success "MySQL character set is correct"
else
    print_warning "MySQL character set is $MYSQL_CHARSET (expected utf8mb4)"
fi

# Final instructions
echo ""
echo "🎉 Manual Cacti Configuration Fix Complete!"
echo "=========================================="
echo ""
echo "📋 Next steps:"
echo "  1. Go to: http://localhost/cacti/install/"
echo "  2. Run the pre-installation check again"
echo "  3. All checks should now pass"
echo "  4. Proceed with Cacti installation"
echo ""
echo "🔑 Default Cacti credentials:"
echo "  Username: admin"
echo "  Password: admin"
echo ""
print_success "Configuration fix process completed!" 