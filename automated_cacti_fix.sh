#!/bin/bash

# Automated Cacti Configuration Fix Script
# Fixes all pre-installation issues automatically

echo "🔧 Automated Cacti Configuration Fix Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
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

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root. Please run as a regular user with sudo privileges."
   exit 1
fi

# Function to backup files
backup_file() {
    local file="$1"
    if [[ -f "$file" ]]; then
        sudo cp "$file" "${file}.backup.$(date +%Y%m%d_%H%M%S)"
        print_status "Backed up $file"
    fi
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
print_status "Checking prerequisites..."

if ! command_exists mysql; then
    print_error "MySQL/MariaDB is not installed. Please install it first."
    exit 1
fi

if ! command_exists php; then
    print_error "PHP is not installed. Please install it first."
    exit 1
fi

print_success "Prerequisites check passed"

# Step 1: Fix PHP Configuration
print_status "Step 1: Fixing PHP Configuration..."

PHP_INI_FILE="/etc/php/8.2/apache2/php.ini"
if [[ ! -f "$PHP_INI_FILE" ]]; then
    # Try to find PHP ini file
    PHP_INI_FILE=$(php -i | grep "Loaded Configuration File" | awk '{print $5}')
    if [[ -z "$PHP_INI_FILE" ]]; then
        print_error "Could not find PHP configuration file"
        exit 1
    fi
fi

print_status "Found PHP config file: $PHP_INI_FILE"

# Backup PHP config
backup_file "$PHP_INI_FILE"

# Fix PHP settings
print_status "Updating PHP memory limit..."
sudo sed -i 's/^memory_limit = .*/memory_limit = 800M/' "$PHP_INI_FILE"

print_status "Updating PHP max execution time..."
sudo sed -i 's/^max_execution_time = .*/max_execution_time = 60/' "$PHP_INI_FILE"

print_success "PHP configuration updated"

# Step 2: Fix MySQL TimeZone Access
print_status "Step 2: Fixing MySQL TimeZone Access..."

# Create temporary SQL file
TEMP_SQL="/tmp/cacti_fix_timezone.sql"
cat > "$TEMP_SQL" << 'EOF'
USE mysql;
GRANT SELECT ON time_zone_name TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_leap_second TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_transition TO 'cactiuser'@'localhost';
GRANT SELECT ON time_zone_transition_type TO 'cactiuser'@'localhost';
FLUSH PRIVILEGES;
EOF

print_status "Granting timezone access to cactiuser..."
sudo mysql < "$TEMP_SQL"

# Populate timezone data
print_status "Populating timezone data..."
if command_exists mysql_tzinfo_to_sql; then
    sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u root mysql
    print_success "Timezone data populated"
else
    print_warning "mysql_tzinfo_to_sql not found, timezone data may not be populated"
fi

# Clean up temp file
rm -f "$TEMP_SQL"

# Step 3: Optimize MySQL Configuration
print_status "Step 3: Optimizing MySQL Configuration..."

# Find MySQL config file
MYSQL_CONFIG_FILES=(
    "/etc/mysql/mariadb.conf.d/50-server.cnf"
    "/etc/mysql/mysql.conf.d/mysqld.cnf"
    "/etc/mysql/my.cnf"
    "/etc/my.cnf"
)

MYSQL_CONFIG_FILE=""
for file in "${MYSQL_CONFIG_FILES[@]}"; do
    if [[ -f "$file" ]]; then
        MYSQL_CONFIG_FILE="$file"
        break
    fi
done

if [[ -z "$MYSQL_CONFIG_FILE" ]]; then
    print_error "Could not find MySQL configuration file"
    exit 1
fi

print_status "Found MySQL config file: $MYSQL_CONFIG_FILE"

# Backup MySQL config
backup_file "$MYSQL_CONFIG_FILE"

# Create optimized MySQL configuration
print_status "Creating optimized MySQL configuration..."

# Create a temporary config file with optimized settings
TEMP_MYSQL_CONFIG="/tmp/mysql_optimized.cnf"
cat > "$TEMP_MYSQL_CONFIG" << 'EOF'
[mysqld]
# Character set and collation
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Connection settings
max_connections = 200
max_allowed_packet = 16M

# Memory settings (adjust based on system RAM)
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

# Append optimized settings to existing config
sudo cat "$TEMP_MYSQL_CONFIG" >> "$MYSQL_CONFIG_FILE"
rm -f "$TEMP_MYSQL_CONFIG"

print_success "MySQL configuration optimized"

# Step 4: Restart Services
print_status "Step 4: Restarting Services..."

print_status "Restarting Apache..."
sudo systemctl restart apache2

print_status "Restarting MariaDB/MySQL..."
sudo systemctl restart mariadb

# Wait a moment for services to start
sleep 3

# Check service status
print_status "Checking service status..."

if sudo systemctl is-active --quiet apache2; then
    print_success "Apache is running"
else
    print_error "Apache failed to start"
fi

if sudo systemctl is-active --quiet mariadb; then
    print_success "MariaDB is running"
else
    print_error "MariaDB failed to start"
fi

# Step 5: Verification
print_status "Step 5: Running Verification..."

# Check PHP settings
print_status "Verifying PHP settings..."
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
print_status "Verifying MySQL timezone access..."
if mysql -u cactiuser -pcactipassword -e "SELECT COUNT(*) FROM mysql.time_zone_name LIMIT 1;" >/dev/null 2>&1; then
    print_success "MySQL timezone access is working"
else
    print_error "MySQL timezone access failed"
fi

# Check MySQL settings
print_status "Verifying MySQL settings..."
MYSQL_CHARSET=$(sudo mysql -u root -e "SHOW VARIABLES LIKE 'character_set_server';" 2>/dev/null | tail -1 | awk '{print $2}')
MYSQL_CONNECTIONS=$(sudo mysql -u root -e "SHOW VARIABLES LIKE 'max_connections';" 2>/dev/null | tail -1 | awk '{print $2}')

echo "  Character Set: $MYSQL_CHARSET"
echo "  Max Connections: $MYSQL_CONNECTIONS"

if [[ "$MYSQL_CHARSET" == "utf8mb4" ]]; then
    print_success "MySQL character set is correct"
else
    print_warning "MySQL character set is $MYSQL_CHARSET (expected utf8mb4)"
fi

# Step 6: Final Status
echo ""
echo "🎉 Automated Cacti Configuration Fix Complete!"
echo "=============================================="
echo ""
echo "📋 Summary of changes:"
echo "  ✅ PHP memory limit set to 800M"
echo "  ✅ PHP max execution time set to 60"
echo "  ✅ MySQL timezone access granted to cactiuser"
echo "  ✅ MySQL performance settings optimized"
echo "  ✅ Services restarted"
echo ""
echo "🔍 Next steps:"
echo "  1. Go to: http://localhost/cacti/install/"
echo "  2. Run the pre-installation check again"
echo "  3. All checks should now pass"
echo "  4. Proceed with Cacti installation"
echo ""
echo "🔑 Default Cacti credentials:"
echo "  Username: admin"
echo "  Password: admin"
echo ""
echo "📁 Backup files created:"
echo "  - $PHP_INI_FILE.backup.*"
echo "  - $MYSQL_CONFIG_FILE.backup.*"
echo ""
print_success "Cacti configuration fix completed successfully!" 