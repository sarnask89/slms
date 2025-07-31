#!/bin/bash

# WebGL Integration Debug & Optimization Script
# Algorithm: 1. SCAN WEBGL CODE -> 2. RESEARCH WEBGL ERRORS & IMPROVEMENTS -> 3. TEST WEBGL FUNCTIONALITY -> 4. DEBUG & FIX -> 5. BACK TO 1

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
PROJECT_ROOT="/var/www/html"
LOG_FILE="/var/www/html/webgl_debug.log"
MAX_ITERATIONS=5
CURRENT_ITERATION=1

# WebGL specific files to monitor
WEBGL_FILES=(
    "webgl_demo_integrated.php"
    "webgl_interface.js"
    "webgl_module_integration.php"
    "config.php"
)

# Function to log messages
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a "$LOG_FILE"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

log_info() {
    echo -e "${CYAN}[INFO]${NC} $1" | tee -a "$LOG_FILE"
}

# STEP 1: SCAN WEBGL SOURCE CODE
scan_webgl_code() {
    log_info "=== STEP 1: SCANNING WEBGL SOURCE CODE ==="
    
    cd "$PROJECT_ROOT"
    
    # Check WebGL specific files
    log_info "Checking WebGL core files..."
    for file in "${WEBGL_FILES[@]}"; do
        if [ -f "$file" ]; then
            log_success "Found WebGL file: $file"
            # Check file size
            SIZE=$(stat -c%s "$file")
            log_info "File size: $SIZE bytes"
        else
            log_error "Missing WebGL file: $file"
        fi
    done
    
    # Check for WebGL related files
    log_info "Scanning for WebGL related files..."
    WEBGL_PHP_FILES=$(find . -name "*.php" -exec grep -l "WebGL\|webgl\|SLMSWebGLInterface" {} \; 2>/dev/null | wc -l)
    WEBGL_JS_FILES=$(find . -name "*.js" -exec grep -l "WebGL\|webgl\|SLMSWebGLInterface" {} \; 2>/dev/null | wc -l)
    WEBGL_HTML_FILES=$(find . -name "*.html" -exec grep -l "WebGL\|webgl\|SLMSWebGLInterface" {} \; 2>/dev/null | wc -l)
    
    log_info "Found $WEBGL_PHP_FILES PHP files with WebGL references"
    log_info "Found $WEBGL_JS_FILES JS files with WebGL references"
    log_info "Found $WEBGL_HTML_FILES HTML files with WebGL references"
    
    # Check for syntax errors in WebGL files
    log_info "Checking WebGL file syntax..."
    for file in "${WEBGL_FILES[@]}"; do
        if [ -f "$file" ]; then
            if [[ "$file" == *.php ]]; then
                if php -l "$file" >/dev/null 2>&1; then
                    log_success "PHP syntax OK: $file"
                else
                    log_error "PHP syntax error in: $file"
                fi
            elif [[ "$file" == *.js ]]; then
                if node -c "$file" >/dev/null 2>&1; then
                    log_success "JavaScript syntax OK: $file"
                else
                    log_error "JavaScript syntax error in: $file"
                fi
            fi
        fi
    done
}

# STEP 2: RESEARCH WEBGL ERRORS & IMPROVEMENTS
research_webgl_errors_and_improvements() {
    log_info "=== STEP 2: RESEARCHING WEBGL ERRORS & IMPROVEMENTS ==="
    
    cd "$PROJECT_ROOT"
    
    # Check for common WebGL issues
    log_info "Checking for common WebGL issues..."
    
    # Check for console.log statements in WebGL code
    CONSOLE_LOGS=$(grep -r "console\.log" webgl_interface.js 2>/dev/null | wc -l)
    if [ $CONSOLE_LOGS -gt 0 ]; then
        log_warning "Found $CONSOLE_LOGS console.log statements in WebGL code"
    else
        log_success "No console.log statements in WebGL code"
    fi
    
    # Check for error handling in WebGL code
    ERROR_HANDLING=$(grep -r "catch\|error\|exception" webgl_interface.js 2>/dev/null | wc -l)
    if [ $ERROR_HANDLING -gt 0 ]; then
        log_success "Found $ERROR_HANDLING error handling statements"
    else
        log_warning "No error handling found in WebGL code"
    fi
    
    # Check for module integration issues
    log_info "Checking module integration..."
    
    # Check for missing module functions
    MISSING_MODULES=$(grep -r "execute_module_function" webgl_interface.js 2>/dev/null | wc -l)
    log_info "Found $MISSING_MODULES module function calls"
    
    # Check for API endpoint consistency
    API_ENDPOINTS=$(grep -r "webgl_module_integration.php" webgl_interface.js 2>/dev/null | wc -l)
    log_info "Found $API_ENDPOINTS API endpoint references"
}

# STEP 3: TEST WEBGL FUNCTIONALITY
test_webgl_functionality() {
    log_info "=== STEP 3: TESTING WEBGL FUNCTIONALITY ==="
    
    cd "$PROJECT_ROOT"
    
    # Test main WebGL application
    log_info "Testing main WebGL application..."
    if curl -s http://localhost/webgl_demo_integrated.php | grep -q "SLMSWebGLInterface"; then
        log_success "Main WebGL application loads correctly"
    else
        log_error "Main WebGL application failed to load"
    fi
    
    # Test WebGL API endpoints
    log_info "Testing WebGL API endpoints..."
    
    # Test module listing
    if curl -s "http://localhost/webgl_module_integration.php?action=list&module=clients" | grep -q '"success":true'; then
        log_success "WebGL API: Clients module working"
    else
        log_error "WebGL API: Clients module failed"
    fi
    
    # Test core devices
    if curl -s "http://localhost/webgl_module_integration.php?action=list&module=core_devices" | grep -q '"success":true'; then
        log_success "WebGL API: Core devices module working"
    else
        log_error "WebGL API: Core devices module failed"
    fi
    
    # Test scan jobs functionality
    log_info "Testing scan jobs functionality..."
    
    # Test scan jobs listing
    if curl -s "http://localhost/webgl_module_integration.php?action=list&module=scan_jobs" | grep -q '"success":true'; then
        log_success "WebGL API: Scan jobs listing working"
    else
        log_error "WebGL API: Scan jobs listing failed"
    fi
    
    # Test adding new scan job
    TEST_RESPONSE=$(curl -s -X POST "http://localhost/webgl_module_integration.php?action=execute_module_function&module=scan_jobs" \
        -d "function=start&name=WebGLTest&type=network&targets=192.168.1.0/24")
    
    if echo "$TEST_RESPONSE" | grep -q '"success":true'; then
        log_success "WebGL API: Adding scan job working"
    else
        log_error "WebGL API: Adding scan job failed: $TEST_RESPONSE"
    fi
    
    # Test network segments
    if curl -s "http://localhost/webgl_module_integration.php?action=list&module=network_segments" | grep -q '"success":true'; then
        log_success "WebGL API: Network segments working"
    else
        log_error "WebGL API: Network segments failed"
    fi
}

# STEP 4: DEBUG AND FIX WEBGL ISSUES
debug_and_fix_webgl_issues() {
    log_info "=== STEP 4: DEBUGGING AND FIXING WEBGL ISSUES ==="
    
    cd "$PROJECT_ROOT"
    
    # Fix common WebGL issues automatically
    
    # Fix file permissions for WebGL files
    log_info "Fixing WebGL file permissions..."
    for file in "${WEBGL_FILES[@]}"; do
        if [ -f "$file" ]; then
            chmod 644 "$file"
        fi
    done
    log_success "WebGL file permissions fixed"
    
    # Check for module function availability
    log_info "Checking module function availability..."
    
    # Test if all required module functions are available
    REQUIRED_FUNCTIONS=("start" "stop" "list" "add" "edit" "delete")
    for func in "${REQUIRED_FUNCTIONS[@]}"; do
        if curl -s "http://localhost/webgl_module_integration.php?action=get_module_functions&module=scan_jobs" | grep -q "$func"; then
            log_success "Function '$func' available for scan_jobs"
        else
            log_error "Function '$func' not available for scan_jobs"
        fi
    done
    
    # Create WebGL-specific backup
    log_info "Creating WebGL-specific backup..."
    BACKUP_DIR="/var/www/html/webgl_backup_$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"
    for file in "${WEBGL_FILES[@]}"; do
        if [ -f "$file" ]; then
            cp "$file" "$BACKUP_DIR/"
        fi
    done
    log_success "WebGL backup created in $BACKUP_DIR"
    
    # Generate WebGL performance report
    log_info "Generating WebGL performance report..."
    echo "=== WebGL Performance Report ===" > /var/www/html/webgl_performance_report.txt
    echo "Generated: $(date)" >> /var/www/html/webgl_performance_report.txt
    echo "" >> /var/www/html/webgl_performance_report.txt
    
    # Count functions in WebGL interface
    FUNCTION_COUNT=$(grep -c "function " webgl_interface.js 2>/dev/null || echo "0")
    echo "Total functions in WebGL interface: $FUNCTION_COUNT" >> /var/www/html/webgl_performance_report.txt
    
    # Count API calls
    API_CALL_COUNT=$(grep -c "fetch\|XMLHttpRequest" webgl_interface.js 2>/dev/null || echo "0")
    echo "Total API calls: $API_CALL_COUNT" >> /var/www/html/webgl_performance_report.txt
    
    log_success "WebGL performance report generated"
}

# Main loop
main() {
    log_info "Starting WebGL Integration Debug & Optimization Script"
    log_info "Algorithm: SCAN WEBGL -> RESEARCH WEBGL -> TEST WEBGL -> DEBUG WEBGL -> REPEAT"
    
    # Check prerequisites
    log_info "Checking prerequisites..."
    if systemctl is-active --quiet apache2; then
        log_success "Apache is running"
    else
        log_error "Apache is not running"
        return 1
    fi
    
    if systemctl is-active --quiet mysql; then
        log_success "MySQL is running"
    else
        log_error "MySQL is not running"
        return 1
    fi
    
    while [ $CURRENT_ITERATION -le $MAX_ITERATIONS ]; do
        log_info "=== WEBGL ITERATION $CURRENT_ITERATION/$MAX_ITERATIONS ==="
        
        # Step 1: Scan WebGL source code
        scan_webgl_code
        
        # Step 2: Research WebGL errors and improvements
        research_webgl_errors_and_improvements
        
        # Step 3: Test WebGL functionality
        test_webgl_functionality
        
        # Step 4: Debug and fix WebGL issues
        debug_and_fix_webgl_issues
        
        log_success "Completed WebGL iteration $CURRENT_ITERATION"
        
        # Check if we should continue
        if [ $CURRENT_ITERATION -lt $MAX_ITERATIONS ]; then
            log_info "Waiting 3 seconds before next WebGL iteration..."
            sleep 3
        fi
        
        ((CURRENT_ITERATION++))
    done
    
    log_success "WebGL Integration Debug & Optimization Script completed!"
    log_info "Check $LOG_FILE for detailed WebGL results"
    log_info "Check /var/www/html/webgl_performance_report.txt for performance analysis"
}

# Run the script
main "$@"
