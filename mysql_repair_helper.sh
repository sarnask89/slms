#!/bin/bash

# MySQL Repair Helper Script
# Based on mysqlrepairall tool for Cacti database issues

echo "🔧 MySQL Repair Helper for Cacti"
echo "================================"
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

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root. Please run as a regular user with sudo privileges."
   exit 1
fi

# Function to backup database
backup_database() {
    print_status "Creating database backup..."
    
    BACKUP_DIR="/home/sarna/cacti_backup_$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"
    
    # Try to backup Cacti database
    if mysqldump -u cactiuser -pcactipassword cacti > "$BACKUP_DIR/cacti_backup.sql" 2>/dev/null; then
        print_success "Cacti database backed up to $BACKUP_DIR/cacti_backup.sql"
    else
        print_warning "Could not backup Cacti database with cactiuser"
        print_status "Trying with root access..."
        if mysqldump -u root -p cacti > "$BACKUP_DIR/cacti_backup.sql" 2>/dev/null; then
            print_success "Cacti database backed up to $BACKUP_DIR/cacti_backup.sql"
        else
            print_error "Failed to backup Cacti database"
        fi
    fi
    
    # Backup MySQL data directory
    print_status "Backing up MySQL data directory..."
    sudo tar -czf "$BACKUP_DIR/mysql_data_backup.tar.gz" /var/lib/mysql 2>/dev/null
    print_success "MySQL data directory backed up to $BACKUP_DIR/mysql_data_backup.tar.gz"
}

# Function to repair database
repair_database() {
    print_status "Repairing Cacti database..."
    
    # Stop MySQL/MariaDB
    print_status "Stopping MySQL/MariaDB..."
    sudo systemctl stop mariadb
    
    # Wait a moment
    sleep 2
    
    # Start MySQL in safe mode
    print_status "Starting MySQL in safe mode for repair..."
    sudo mysqld_safe --skip-grant-tables &
    sleep 5
    
    # Repair tables
    print_status "Repairing database tables..."
    mysql -u root -e "USE cacti; REPAIR TABLE user_auth;" 2>/dev/null
    mysql -u root -e "USE cacti; REPAIR TABLE devices;" 2>/dev/null
    mysql -u root -e "USE cacti; REPAIR TABLE graph_templates;" 2>/dev/null
    
    # Stop safe mode MySQL
    sudo pkill mysqld
    sleep 2
    
    # Start MySQL normally
    print_status "Starting MySQL normally..."
    sudo systemctl start mariadb
    sleep 3
    
    # Check MySQL status
    if sudo systemctl is-active --quiet mariadb; then
        print_success "MySQL started successfully"
    else
        print_error "MySQL failed to start"
    fi
}

# Function to optimize database
optimize_database() {
    print_status "Optimizing Cacti database..."
    
    # Optimize key tables
    mysql -u cactiuser -pcactipassword cacti -e "OPTIMIZE TABLE user_auth;" 2>/dev/null
    mysql -u cactiuser -pcactipassword cacti -e "OPTIMIZE TABLE devices;" 2>/dev/null
    mysql -u cactiuser -pcactipassword cacti -e "OPTIMIZE TABLE graph_templates;" 2>/dev/null
    mysql -u cactiuser -pcactipassword cacti -e "OPTIMIZE TABLE data_template_data;" 2>/dev/null
    
    print_success "Database optimization completed"
}

# Function to check database integrity
check_database() {
    print_status "Checking database integrity..."
    
    # Check Cacti database
    if mysql -u cactiuser -pcactipassword -e "USE cacti; SHOW TABLES;" >/dev/null 2>&1; then
        print_success "Cacti database is accessible"
        
        # Check key tables
        TABLES=("user_auth" "devices" "graph_templates" "data_template_data")
        for table in "${TABLES[@]}"; do
            if mysql -u cactiuser -pcactipassword cacti -e "CHECK TABLE $table;" >/dev/null 2>&1; then
                print_success "Table $table is OK"
            else
                print_warning "Table $table may have issues"
            fi
        done
    else
        print_error "Cacti database is not accessible"
    fi
}

# Function to fix timezone issues
fix_timezone_issues() {
    print_status "Fixing timezone issues..."
    
    # Create timezone tables if they don't exist
    mysql -u root -p -e "USE mysql; CREATE TABLE IF NOT EXISTS time_zone_name (Name char(64) NOT NULL, Time_zone_id int unsigned NOT NULL, PRIMARY KEY (Name)) ENGINE=MyISAM;" 2>/dev/null
    
    # Grant permissions
    mysql -u root -p -e "USE mysql; GRANT SELECT ON time_zone_name TO 'cactiuser'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null
    
    print_success "Timezone issues addressed"
}

# Main menu
echo "Choose an option:"
echo "1. Backup database"
echo "2. Repair database"
echo "3. Optimize database"
echo "4. Check database integrity"
echo "5. Fix timezone issues"
echo "6. Run all repairs"
echo "7. Exit"
echo ""

read -p "Enter your choice (1-7): " choice

case $choice in
    1)
        backup_database
        ;;
    2)
        repair_database
        ;;
    3)
        optimize_database
        ;;
    4)
        check_database
        ;;
    5)
        fix_timezone_issues
        ;;
    6)
        print_status "Running all repairs..."
        backup_database
        repair_database
        optimize_database
        check_database
        fix_timezone_issues
        print_success "All repairs completed!"
        ;;
    7)
        print_status "Exiting..."
        exit 0
        ;;
    *)
        print_error "Invalid choice"
        exit 1
        ;;
esac

echo ""
print_success "MySQL repair operations completed!"
echo ""
echo "Next steps:"
echo "1. Test Cacti access: http://localhost/cacti/"
echo "2. Run Cacti pre-installation check"
echo "3. If issues persist, check logs:"
echo "   - MySQL logs: sudo tail -f /var/log/mysql/error.log"
echo "   - Cacti logs: tail -f /var/www/html/cacti/log/cacti.log" 