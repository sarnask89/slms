#!/bin/bash
# AI Assistant & ML Model Automation Startup Script
# This script automates everything for your AI assistant system

echo "🤖 AI Assistant & ML Model Automation System"
echo "=============================================="
echo "Starting comprehensive automation..."

# Function to log messages
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

# Function to check if a service is running
check_service() {
    local service_name=$1
    local port=$2
    local url=$3
    
    if curl -s "$url" > /dev/null 2>&1; then
        log_message "✅ $service_name is running on port $port"
        return 0
    else
        log_message "❌ $service_name is not running on port $port"
        return 1
    fi
}

# Function to start a service
start_service() {
    local service_name=$1
    local setup_script=$2
    
    if [ -f "$setup_script" ]; then
        log_message "🚀 Starting $service_name..."
        sudo "$setup_script"
        sleep 30  # Wait for service to start
    else
        log_message "⚠️ Setup script not found: $setup_script"
    fi
}

# Function to run tests
run_tests() {
    log_message "🧪 Running automated tests..."
    
    # Test AI Assistant API
    if check_service "AI Assistant API" 80 "http://localhost/ai_assistant_api.php?action=model_status"; then
        log_message "✅ AI Assistant API test passed"
    else
        log_message "❌ AI Assistant API test failed"
    fi
    
    # Test Adaptive AI API
    if check_service "Adaptive AI API" 80 "http://localhost/adaptive_ai_api.php?action=suggest_improvements"; then
        log_message "✅ Adaptive AI API test passed"
    else
        log_message "❌ Adaptive AI API test failed"
    fi
    
    # Test LocalAI (if available)
    if check_service "LocalAI" 8080 "http://localhost:8080/v1/models"; then
        log_message "✅ LocalAI test passed"
    else
        log_message "⚠️ LocalAI not running (optional service)"
    fi
    
    # Test Focused ML Service (if available)
    if check_service "Focused ML Service" 8000 "http://localhost:8000/health"; then
        log_message "✅ Focused ML Service test passed"
    else
        log_message "⚠️ Focused ML Service not running (optional service)"
    fi
}

# Function to generate report
generate_report() {
    log_message "📊 Generating automation report..."
    
    # Create report directory
    mkdir -p /var/www/html/reports
    
    # Generate timestamp
    timestamp=$(date '+%Y%m%d_%H%M%S')
    
    # Create report
    cat > "/var/www/html/reports/automation_report_$timestamp.txt" << EOF
AI Assistant & ML Model Automation Report
Generated: $(date)
==========================================

System Status:
- AI Assistant API: $(check_service "AI Assistant API" 80 "http://localhost/ai_assistant_api.php?action=model_status" && echo "✅ Running" || echo "❌ Not Running")
- Adaptive AI API: $(check_service "Adaptive AI API" 80 "http://localhost/adaptive_ai_api.php?action=suggest_improvements" && echo "✅ Running" || echo "❌ Not Running")
- LocalAI: $(check_service "LocalAI" 8080 "http://localhost:8080/v1/models" && echo "✅ Running" || echo "❌ Not Running")
- Focused ML Service: $(check_service "Focused ML Service" 8000 "http://localhost:8000/health" && echo "✅ Running" || echo "❌ Not Running")

Services Started:
$(cat /tmp/services_started.log 2>/dev/null || echo "No services started in this session")

Test Results:
$(cat /tmp/test_results.log 2>/dev/null || echo "No test results available")

EOF
    
    log_message "📋 Report saved to: /var/www/html/reports/automation_report_$timestamp.txt"
}

# Function to start monitoring
start_monitoring() {
    log_message "📡 Starting continuous monitoring..."
    
    # Create monitoring script
    cat > /tmp/monitor_services.sh << 'EOF'
#!/bin/bash
while true; do
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Checking services..."
    
    # Check AI Assistant API
    if ! curl -s "http://localhost/ai_assistant_api.php?action=model_status" > /dev/null; then
        echo "❌ AI Assistant API is down, attempting restart..."
        sudo systemctl restart apache2
    fi
    
    # Check Adaptive AI API
    if ! curl -s "http://localhost/adaptive_ai_api.php?action=suggest_improvements" > /dev/null; then
        echo "❌ Adaptive AI API is down"
    fi
    
    # Check LocalAI (if it was running)
    if [ -f /tmp/localai_running ]; then
        if ! curl -s "http://localhost:8080/v1/models" > /dev/null; then
            echo "❌ LocalAI is down, attempting restart..."
            sudo /var/www/html/setup_localai.sh
        fi
    fi
    
    # Check Focused ML Service (if it was running)
    if [ -f /tmp/focused_ml_running ]; then
        if ! curl -s "http://localhost:8000/health" > /dev/null; then
            echo "❌ Focused ML Service is down, attempting restart..."
            sudo /var/www/html/setup_focused_ml_models.sh
        fi
    fi
    
    sleep 60  # Check every minute
done
EOF
    
    chmod +x /tmp/monitor_services.sh
    nohup /tmp/monitor_services.sh > /var/www/html/monitoring.log 2>&1 &
    echo $! > /tmp/monitoring_pid
    log_message "📡 Monitoring started (PID: $(cat /tmp/monitoring_pid))"
}

# Function to stop monitoring
stop_monitoring() {
    if [ -f /tmp/monitoring_pid ]; then
        pid=$(cat /tmp/monitoring_pid)
        if kill -0 "$pid" 2>/dev/null; then
            kill "$pid"
            log_message "⏹️ Monitoring stopped (PID: $pid)"
        else
            log_message "⚠️ Monitoring process not found"
        fi
        rm -f /tmp/monitoring_pid
    else
        log_message "⚠️ No monitoring process found"
    fi
}

# Main automation function
main() {
    log_message "🚀 Starting AI Assistant automation..."
    
    # Clear previous logs
    > /tmp/services_started.log
    > /tmp/test_results.log
    
    # Check current status
    log_message "📊 Checking current system status..."
    
    # Start services if not running
    log_message "🔧 Starting required services..."
    
    # Start Apache if not running
    if ! systemctl is-active --quiet apache2; then
        log_message "🚀 Starting Apache..."
        sudo systemctl start apache2
        echo "Apache started" >> /tmp/services_started.log
    fi
    
    # Check and start LocalAI if setup script exists
    if [ -f "/var/www/html/setup_localai.sh" ]; then
        if ! check_service "LocalAI" 8080 "http://localhost:8080/v1/models"; then
            start_service "LocalAI" "/var/www/html/setup_localai.sh"
            echo "LocalAI started" >> /tmp/services_started.log
            touch /tmp/localai_running
        else
            touch /tmp/localai_running
        fi
    fi
    
    # Check and start Focused ML Service if setup script exists
    if [ -f "/var/www/html/setup_focused_ml_models.sh" ]; then
        if ! check_service "Focused ML Service" 8000 "http://localhost:8000/health"; then
            start_service "Focused ML Service" "/var/www/html/setup_focused_ml_models.sh"
            echo "Focused ML Service started" >> /tmp/services_started.log
            touch /tmp/focused_ml_running
        else
            touch /tmp/focused_ml_running
        fi
    fi
    
    # Wait for services to stabilize
    log_message "⏳ Waiting for services to stabilize..."
    sleep 30
    
    # Run tests
    run_tests
    
    # Generate report
    generate_report
    
    # Start monitoring if requested
    if [ "$1" = "--monitor" ]; then
        start_monitoring
        log_message "📡 Continuous monitoring enabled"
        log_message "💡 Use './start_automation.sh --stop-monitor' to stop monitoring"
    fi
    
    log_message "✅ Automation completed successfully!"
    log_message "🌐 Access your AI Assistant at: http://localhost/ai_assistant_demo.html"
    log_message "📊 View automation dashboard at: http://localhost/automation_dashboard.html"
    log_message "📋 View latest report at: http://localhost/automation_report.html"
}

# Handle command line arguments
case "$1" in
    "--monitor")
        main --monitor
        ;;
    "--stop-monitor")
        stop_monitoring
        ;;
    "--test-only")
        run_tests
        generate_report
        ;;
    "--start-services")
        main
        ;;
    "--help"|"-h")
        echo "AI Assistant Automation Script"
        echo ""
        echo "Usage: $0 [OPTION]"
        echo ""
        echo "Options:"
        echo "  --monitor        Start automation with continuous monitoring"
        echo "  --stop-monitor   Stop continuous monitoring"
        echo "  --test-only      Run tests only"
        echo "  --start-services Start services only"
        echo "  --help, -h       Show this help message"
        echo ""
        echo "Examples:"
        echo "  $0 --monitor     # Start full automation with monitoring"
        echo "  $0 --test-only   # Run tests only"
        echo "  $0               # Start automation without monitoring"
        ;;
    *)
        main
        ;;
esac 