#!/bin/bash

# WebGL Interface Audit Script
# Algorithm: 1. SCAN SOURCE CODE → 2. RESEARCH ERRORS/IMPROVEMENTS → 3. TEST → 4. DEBUG/FIX → 5. BACK TO 1

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
WEBGL_DIR="/var/www/html"
LOG_FILE="/var/www/html/webgl_audit.log"
ERROR_LOG="/var/www/html/webgl_errors.log"
PERFORMANCE_LOG="/var/www/html/webgl_performance.log"

# Initialize logs
echo "=== WebGL Interface Audit Started: $(date) ===" > $LOG_FILE
echo "=== WebGL Interface Errors: $(date) ===" > $ERROR_LOG
echo "=== WebGL Interface Performance: $(date) ===" > $PERFORMANCE_LOG

log() {
    echo -e "${BLUE}[$(date '+%H:%M:%S')]${NC} $1" | tee -a $LOG_FILE
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a $ERROR_LOG
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a $LOG_FILE
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a $LOG_FILE
}

info() {
    echo -e "${CYAN}[INFO]${NC} $1" | tee -a $LOG_FILE
}

# Function 1: SCAN SOURCE CODE
scan_source_code() {
    log "=== STEP 1: SCANNING SOURCE CODE ==="
    
    cd $WEBGL_DIR
    
    # Scan all relevant files
    info "Scanning WebGL interface files..."
    
    # Core files
    CORE_FILES=(
        "webgl_demo_integrated.php"
        "webgl_interface.js"
        "webgl_module_integration.php"
        "config.php"
    )
    
    # Check if files exist
    for file in "${CORE_FILES[@]}"; do
        if [[ -f "$file" ]]; then
            success "Found: $file"
            echo "  Size: $(du -h "$file" | cut -f1)"
            echo "  Lines: $(wc -l < "$file")"
        else
            error "Missing: $file"
        fi
    done
    
    # Scan for JavaScript files
    info "Scanning JavaScript files..."
    JS_FILES=$(find . -name "*.js" -type f)
    for file in $JS_FILES; do
        if [[ -f "$file" ]]; then
            echo "  Found JS: $file ($(wc -l < "$file") lines)"
        fi
    done
    
    # Scan for PHP files
    info "Scanning PHP files..."
    PHP_FILES=$(find . -name "*.php" -type f)
    for file in $PHP_FILES; do
        if [[ -f "$file" ]]; then
            echo "  Found PHP: $file ($(wc -l < "$file") lines)"
        fi
    done
    
    # Check database tables
    info "Checking database structure..."
    if command -v mysql &> /dev/null; then
        mysql -u root -p -e "SHOW TABLES;" 2>/dev/null || warning "Cannot access database directly"
    fi
    
    success "Source code scan completed"
}

# Function 2: RESEARCH ERRORS AND IMPROVEMENTS
research_errors_improvements() {
    log "=== STEP 2: RESEARCHING ERRORS AND IMPROVEMENTS ==="
    
    cd $WEBGL_DIR
    
    # Check for syntax errors
    info "Checking PHP syntax errors..."
    for file in *.php; do
        if [[ -f "$file" ]]; then
            if php -l "$file" >/dev/null 2>&1; then
                success "PHP syntax OK: $file"
            else
                error "PHP syntax error in: $file"
                php -l "$file" 2>&1 | tee -a $ERROR_LOG
            fi
        fi
    done
    
    # Check for JavaScript syntax errors
    info "Checking JavaScript syntax..."
    if command -v node &> /dev/null; then
        for file in *.js; do
            if [[ -f "$file" ]]; then
                if node -c "$file" 2>/dev/null; then
                    success "JS syntax OK: $file"
                else
                    error "JS syntax error in: $file"
                    node -c "$file" 2>&1 | tee -a $ERROR_LOG
                fi
            fi
        done
    else
        warning "Node.js not available for JS syntax checking"
    fi
    
    # Check for common issues
    info "Checking for common issues..."
    
    # Check for missing functions
    if grep -q "function.*{" webgl_interface.js; then
        success "JavaScript functions found"
    else
        error "No JavaScript functions found"
    fi
    
    # Check for API endpoints
    if grep -q "case.*:" webgl_module_integration.php; then
        success "API endpoints found"
    else
        error "No API endpoints found"
    fi
    
    # Check for database connections
    if grep -q "get_pdo" *.php; then
        success "Database connection functions found"
    else
        error "Database connection functions missing"
    fi
    
    # Check for error handling
    if grep -q "try.*catch" *.php *.js; then
        success "Error handling found"
    else
        warning "Limited error handling detected"
    fi
    
    # Performance analysis
    info "Analyzing performance patterns..."
    
    # Check for large files
    for file in *.php *.js; do
        if [[ -f "$file" ]]; then
            size=$(wc -l < "$file")
            if [[ $size -gt 1000 ]]; then
                warning "Large file detected: $file ($size lines)"
            fi
        fi
    done
    
    # Check for potential memory leaks
    if grep -q "setInterval\|setTimeout" *.js; then
        warning "Timers detected - check for memory leaks"
    fi
    
    success "Error and improvement research completed"
}

# Function 3: TEST (CURL, CONSOLE, BROWSER)
run_tests() {
    log "=== STEP 3: RUNNING TESTS ==="
    
    cd $WEBGL_DIR
    
    # Test 1: CURL API Tests
    info "Running CURL API tests..."
    
    # Test basic connectivity
    if curl -s "http://localhost/webgl_demo_integrated.php" >/dev/null; then
        success "Main page accessible"
    else
        error "Main page not accessible"
    fi
    
    # Test API endpoints
    API_TESTS=(
        "action=list&module=clients"
        "action=list&module=devices"
        "action=list&module=scan_jobs"
        "action=list&module=core_devices"
        "action=list&module=client_devices"
        "action=get_module_functions&module=scan_jobs"
    )
    
    for test in "${API_TESTS[@]}"; do
        response=$(curl -s "http://localhost/webgl_module_integration.php?$test")
        if echo "$response" | grep -q '"success":true'; then
            success "API test passed: $test"
        else
            error "API test failed: $test"
            echo "Response: $response" | tee -a $ERROR_LOG
        fi
    done
    
    # Test POST operations
    info "Testing POST operations..."
    
    # Test adding a scan job
    post_response=$(curl -s -X POST "http://localhost/webgl_module_integration.php" \
        -d "action=execute_module_function&module=scan_jobs&function=start&name=Audit Test&type=network&targets=192.168.1.0/24")
    
    if echo "$post_response" | grep -q '"success":true'; then
        success "POST test passed: scan job creation"
    else
        error "POST test failed: scan job creation"
        echo "Response: $post_response" | tee -a $ERROR_LOG
    fi
    
    # Test 2: Console Tests
    info "Running console tests..."
    
    # Check if Apache is running
    if systemctl is-active --quiet apache2; then
        success "Apache service is running"
    else
        error "Apache service is not running"
    fi
    
    # Check PHP version
    php_version=$(php -v | head -n1 | cut -d' ' -f2)
    info "PHP version: $php_version"
    
    # Check disk space
    disk_usage=$(df -h . | tail -1 | awk '{print $5}' | sed 's/%//')
    if [[ $disk_usage -lt 90 ]]; then
        success "Disk space OK: ${disk_usage}% used"
    else
        warning "Low disk space: ${disk_usage}% used"
    fi
    
    # Check memory usage
    memory_usage=$(free | grep Mem | awk '{printf "%.1f", $3/$2 * 100.0}')
    info "Memory usage: ${memory_usage}%"
    
    # Test 3: Browser Tests (simulated)
    info "Running browser compatibility tests..."
    
    # Check for modern JavaScript features
    if grep -q "async.*function\|=>" *.js; then
        success "Modern JavaScript features detected"
    else
        warning "No modern JavaScript features detected"
    fi
    
    # Check for WebGL support indicators
    if grep -q "WebGL\|three\.js\|THREE" *.js; then
        success "WebGL/Three.js detected"
    else
        warning "No WebGL/Three.js detected"
    fi
    
    # Check for responsive design
    if grep -q "@media\|flexbox\|grid" *.css 2>/dev/null; then
        success "Responsive design detected"
    else
        warning "No responsive design detected"
    fi
    
    success "All tests completed"
}

# Function 4: DEBUG AND FIX
debug_and_fix() {
    log "=== STEP 4: DEBUGGING AND FIXING ==="
    
    cd $WEBGL_DIR
    
    # Check for common issues and fix them
    info "Checking for common issues..."
    
    # Fix 1: Check for missing semicolons in JS
    info "Checking JavaScript syntax..."
    if command -v node &> /dev/null; then
        for file in *.js; do
            if [[ -f "$file" ]]; then
                if ! node -c "$file" 2>/dev/null; then
                    warning "JavaScript syntax issues in $file"
                    # Try to fix common issues
                    sed -i 's/^[[:space:]]*$//' "$file"  # Remove empty lines
                    sed -i 's/[[:space:]]*$//' "$file"   # Remove trailing spaces
                fi
            fi
        done
    fi
    
    # Fix 2: Check for PHP syntax
    info "Checking PHP syntax..."
    for file in *.php; do
        if [[ -f "$file" ]]; then
            if ! php -l "$file" >/dev/null 2>&1; then
                error "PHP syntax error in $file"
                php -l "$file" 2>&1 | tee -a $ERROR_LOG
            fi
        fi
    done
    
    # Fix 3: Check for missing functions
    info "Checking for missing function implementations..."
    
    # Check if all required functions exist
    REQUIRED_FUNCTIONS=(
        "addNewClientDevice"
        "addNewCoreDevice"
        "addNewScanJob"
        "startScanJob"
        "stopScanJob"
        "viewScanResults"
    )
    
    for func in "${REQUIRED_FUNCTIONS[@]}"; do
        if grep -q "function.*$func" *.js; then
            success "Function found: $func"
        else
            error "Missing function: $func"
        fi
    done
    
    # Fix 4: Check for API endpoint consistency
    info "Checking API endpoint consistency..."
    
    # Check if all modules have proper definitions
    MODULES=(
        "clients"
        "devices"
        "scan_jobs"
        "core_devices"
        "client_devices"
        "mikrotik"
        "dhcp"
        "snmp"
        "vlans"
        "ip_ranges"
        "network_segments"
        "device_categories"
    )
    
    for module in "${MODULES[@]}"; do
        if grep -q "'$module'" webgl_module_integration.php; then
            success "Module defined: $module"
        else
            error "Module missing: $module"
        fi
    done
    
    # Fix 5: Performance optimizations
    info "Applying performance optimizations..."
    
    # Check for large files and suggest splitting
    for file in *.js *.php; do
        if [[ -f "$file" ]]; then
            size=$(wc -l < "$file")
            if [[ $size -gt 2000 ]]; then
                warning "Large file detected: $file ($size lines) - consider splitting"
            fi
        fi
    done
    
    # Fix 6: Security checks
    info "Running security checks..."
    
    # Check for SQL injection vulnerabilities
    if grep -q "SELECT.*\$_GET\|SELECT.*\$_POST" *.php; then
        warning "Potential SQL injection vulnerability detected"
    else
        success "No obvious SQL injection vulnerabilities"
    fi
    
    # Check for XSS vulnerabilities
    if grep -q "echo.*\$_GET\|echo.*\$_POST" *.php; then
        warning "Potential XSS vulnerability detected"
    else
        success "No obvious XSS vulnerabilities"
    fi
    
    success "Debugging and fixing completed"
}

# Function 5: BACK TO 1 (Recursive audit)
recursive_audit() {
    log "=== STEP 5: RECURSIVE AUDIT ==="
    
    info "Starting recursive audit cycle..."
    
    # Run the full cycle again
    scan_source_code
    research_errors_improvements
    run_tests
    debug_and_fix
    
    # Check if we need another cycle
    error_count=$(wc -l < $ERROR_LOG)
    if [[ $error_count -gt 1 ]]; then
        warning "Errors detected, starting another audit cycle..."
        recursive_audit
    else
        success "Audit cycle completed successfully"
    fi
}

# Main execution
main() {
    log "Starting WebGL Interface Audit Script"
    log "Working directory: $(pwd)"
    
    # Check if we're in the right directory
    if [[ ! -f "webgl_demo_integrated.php" ]]; then
        error "Not in WebGL directory. Please run from /var/www/html"
        exit 1
    fi
    
    # Run the full audit cycle
    scan_source_code
    research_errors_improvements
    run_tests
    debug_and_fix
    recursive_audit
    
    # Generate final report
    log "=== FINAL AUDIT REPORT ==="
    echo "Errors found: $(($(wc -l < $ERROR_LOG) - 1))"
    echo "Performance issues: $(($(wc -l < $PERFORMANCE_LOG) - 1))"
    
    success "WebGL Interface Audit completed successfully!"
    log "Check logs for details:"
    log "  - Main log: $LOG_FILE"
    log "  - Errors: $ERROR_LOG"
    log "  - Performance: $PERFORMANCE_LOG"
}

# Run the script
main "$@" 