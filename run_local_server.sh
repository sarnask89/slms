#!/bin/bash
# run_local_server.sh
# Enhanced script to start local PHP server with comprehensive cleanup and Docker management

set -e  # Exit on any error

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

# Function to cleanup PHP processes and port conflicts
cleanup_php_servers() {
    print_status "Cleaning up PHP server processes and port conflicts..."
    
    # Kill all previous PHP built-in server instances
    PIDS=$(pgrep -f "php -S" 2>/dev/null || true)
    if [ -n "$PIDS" ]; then
        print_warning "Killing previous PHP server instances: $PIDS"
        kill -9 $PIDS 2>/dev/null || true
        sleep 2
    else
        print_success "No PHP server processes found"
    fi
    
    # Kill any processes using port 8000
    PORT_PIDS=$(lsof -ti:8000 2>/dev/null || true)
    if [ -n "$PORT_PIDS" ]; then
        print_warning "Killing processes using port 8000: $PORT_PIDS"
        kill -9 $PORT_PIDS 2>/dev/null || true
        sleep 2
    else
        print_success "Port 8000 is free"
    fi
    
    # Kill any processes using port 8081 (Cacti)
    PORT_8081_PIDS=$(lsof -ti:8081 2>/dev/null || true)
    if [ -n "$PORT_8081_PIDS" ]; then
        print_warning "Killing processes using port 8081: $PORT_8081_PIDS"
        kill -9 $PORT_8081_PIDS 2>/dev/null || true
        sleep 2
    else
        print_success "Port 8081 is free"
    fi
    
    # Additional cleanup for common development servers
    print_status "Cleaning up other development servers..."
    
    # Kill any Node.js development servers
    NODE_PIDS=$(pgrep -f "node.*serve\|node.*dev\|npm.*start" 2>/dev/null || true)
    if [ -n "$NODE_PIDS" ]; then
        print_warning "Killing Node.js development servers: $NODE_PIDS"
        kill -9 $NODE_PIDS 2>/dev/null || true
    fi
    
    # Kill any Python development servers
    PYTHON_PIDS=$(pgrep -f "python.*http.server\|python.*-m.*http" 2>/dev/null || true)
    if [ -n "$PYTHON_PIDS" ]; then
        print_warning "Killing Python development servers: $PYTHON_PIDS"
        kill -9 $PYTHON_PIDS 2>/dev/null || true
    fi
    
    # Kill any Ruby development servers
    RUBY_PIDS=$(pgrep -f "ruby.*server\|rails.*server" 2>/dev/null || true)
    if [ -n "$RUBY_PIDS" ]; then
        print_warning "Killing Ruby development servers: $RUBY_PIDS"
        kill -9 $RUBY_PIDS 2>/dev/null || true
    fi
    
    # Wait a moment for processes to fully terminate
    sleep 3
}

# Function to cleanup Docker containers
cleanup_docker() {
    print_status "Managing Docker containers..."
    
    if command -v docker &> /dev/null; then
        # Check if docker-compose.yml exists
        if [ -f "docker-compose.yml" ]; then
            print_status "Stopping existing Cacti containers..."
            docker-compose down 2>/dev/null || true
            
            # Remove any existing containers with conflicting names
            print_status "Removing conflicting containers..."
            docker rm -f cacti 2>/dev/null || true
            
            print_status "Starting Cacti containers..."
            docker-compose up -d
            
            # Wait for containers to be ready
            print_status "Waiting for containers to initialize..."
            sleep 10
            
            # Check container status
            print_status "Container status:"
            docker-compose ps
            
        else
            print_warning "docker-compose.yml not found, skipping Docker management"
        fi
    else
        print_warning "Docker not installed, skipping Docker management"
    fi
}

# Function to clear cache
clear_cache() {
    print_status "Clearing cache..."
    
    # Clear PHP opcache if available
    if command -v php &> /dev/null; then
        php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'PHP opcache cleared\n'; } else { echo 'PHP opcache not available\n'; }" 2>/dev/null || true
    fi
    
    # Clear browser cache files if they exist
    if [ -d "cache" ]; then
        rm -rf cache/*
        print_success "Cache directory cleared"
    fi
    
    # Clear temporary files
    find . -name "*.tmp" -delete 2>/dev/null || true
    find . -name "*.cache" -delete 2>/dev/null || true
}

# Function to check system requirements
check_requirements() {
    print_status "Checking system requirements..."
    
    # Check if PHP is installed
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed. Please install PHP to use this script."
        exit 1
    fi
    
    # Check PHP version
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_success "PHP version: $PHP_VERSION"
    
    # Check required PHP extensions
    REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "json" "curl" "snmp")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if php -r "echo extension_loaded('$ext') ? 'OK' : 'NOT_FOUND';" 2>/dev/null | grep -q "OK"; then
            print_success "PHP extension $ext: OK"
        else
            print_warning "PHP extension $ext: NOT FOUND"
        fi
    done
    
    # Check for required system tools
    print_status "Checking system tools..."
    
    # Check for lsof (needed for port management)
    if ! command -v lsof &> /dev/null; then
        print_warning "lsof not found. Installing..."
        if command -v apt-get &> /dev/null; then
            sudo apt-get update && sudo apt-get install -y lsof
        elif command -v yum &> /dev/null; then
            sudo yum install -y lsof
        elif command -v dnf &> /dev/null; then
            sudo dnf install -y lsof
        else
            print_warning "Could not install lsof automatically. Please install it manually."
        fi
    else
        print_success "lsof: OK"
    fi
    
    # Check for other useful tools
    if command -v netstat &> /dev/null; then
        print_success "netstat: OK"
    else
        print_warning "netstat: NOT FOUND"
    fi
    
    if command -v ss &> /dev/null; then
        print_success "ss: OK"
    else
        print_warning "ss: NOT FOUND"
    fi
    
    # Check if we're in the right directory
    if [ ! -f "config.php" ]; then
        print_error "config.php not found. Please run this script from the sLMS root directory."
        exit 1
    fi
    
    print_success "System requirements check completed"
}

# Function to test database connection
test_database() {
    print_status "Testing database connection..."
    
    if command -v php &> /dev/null; then
        php -r "
        require_once 'config.php';
        try {
            \$pdo = get_pdo();
            echo 'Database connection: SUCCESS\n';
        } catch (Exception \$e) {
            echo 'Database connection: FAILED - ' . \$e->getMessage() . '\n';
            exit(1);
        }
        " 2>/dev/null || {
            print_error "Database connection failed"
            return 1
        }
        print_success "Database connection test passed"
    fi
}

# Function to check port availability and force cleanup if needed
check_port() {
    local port=$1
    local host=$2
    
    print_status "Checking port $port availability..."
    
    # Check if port is in use using multiple methods
    local port_in_use=false
    
    if command -v lsof &> /dev/null; then
        if lsof -i:$port >/dev/null 2>&1; then
            port_in_use=true
        fi
    elif command -v netstat &> /dev/null; then
        if netstat -tuln 2>/dev/null | grep -q ":$port "; then
            port_in_use=true
        fi
    elif command -v ss &> /dev/null; then
        if ss -tuln 2>/dev/null | grep -q ":$port "; then
            port_in_use=true
        fi
    fi
    
    if [ "$port_in_use" = true ]; then
        print_warning "Port $port is still in use, attempting to force cleanup..."
        
        # Try to kill processes using this port
        local port_pids=$(lsof -ti:$port 2>/dev/null || true)
        if [ -n "$port_pids" ]; then
            print_warning "Force killing processes using port $port: $port_pids"
            kill -9 $port_pids 2>/dev/null || true
            sleep 3
            
            # Check again after cleanup
            if lsof -i:$port >/dev/null 2>&1; then
                print_error "Port $port is still in use after cleanup attempt"
                return 1
            else
                print_success "Port $port freed after cleanup"
                return 0
            fi
        else
            print_warning "Port $port appears to be in use but no processes found"
            return 1
        fi
    else
        print_success "Port $port is available"
        return 0
    fi
}

# Main execution
main() {
    echo "================================================"
    echo "           sLMS Development Server Setup"
    echo "================================================"
    echo
    
    # Configuration
    PORT=8000
    HOST=10.0.222.223
    
    # Run cleanup and setup
    check_requirements
    cleanup_php_servers
    clear_cache
    test_database
    cleanup_docker
    
    # Final port availability check with force cleanup
    if ! check_port $PORT $HOST; then
        print_error "Port $PORT is still in use after cleanup attempts. Please check manually."
        exit 1
    fi
    
    echo
    echo "================================================"
    echo "Starting local PHP server at http://$HOST:$PORT"
    echo "Serving files from: $(pwd)"
    echo
    echo "Services:"
    echo "- sLMS Web Interface: http://$HOST:$PORT"
    echo "- Cacti Integration: http://$HOST:$PORT/modules/cacti_integration.php"
    echo "- Admin Menu: http://$HOST:$PORT/admin_menu.php"
    echo
    echo "To stop the server, press Ctrl+C"
    echo "================================================"
    echo
    
    # Start the PHP built-in server
    print_status "Starting PHP development server..."
    
    # Display menu to choose server mode
    echo "Choose an option:"
    echo "  1) Start server normally"
    echo "  2) Start server with Xdebug enabled"
    echo "  3) Run system debugger"
    read -p "Enter your choice [1]: " choice
    choice=${choice:-1}
    
    case $choice in
        1)
            print_status "Starting server in normal mode..."
            php -S $HOST:$PORT
            ;;
        2)
            print_status "Starting server with Xdebug enabled..."
            export XDEBUG_MODE=debug
            export XDEBUG_CONFIG="client_host=10.0.222.223 client_port=9003"
            php -S $HOST:$PORT
            ;;
        3)
            print_status "Running system debugger..."
            php debug_system.php
            ;;
        *)
            print_error "Invalid choice. Starting server normally."
            php -S $HOST:$PORT
            ;;
    esac
}

# Run main function
main "$@" 
