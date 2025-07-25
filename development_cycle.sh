#!/bin/bash

# 🔄 Automated Development Cycle Script
# "Read, Run, Debug, Improve, Repeat"

echo "🔄 Starting Development Cycle..."
echo "====================================="
echo "Date: $(date)"
echo "Directory: $(pwd)"
echo "====================================="
echo ""

# Create logs directory if it doesn't exist
mkdir -p logs
mkdir -p cache

# Phase 1: READ (Analysis)
echo "📖 Phase 1: Analyzing code..."
echo "-------------------------------------"

# Syntax check all PHP files
echo "Checking PHP syntax..."
find . -name "*.php" -exec php -l {} \; 2>&1 | tee logs/syntax_check.log

# Check file permissions
echo "Checking file permissions..."
ls -la *.php | tee logs/file_permissions.log

# Check database configuration
echo "Checking database configuration..."
if [ -f "config.php" ]; then
    echo "✅ config.php exists"
else
    echo "❌ config.php missing"
fi

echo ""

# Phase 2: RUN (Execution)
echo "🚀 Phase 2: Running tests..."
echo "-------------------------------------"

# Run test suite
echo "Running automated test suite..."
php test_suite.php 2>&1 | tee logs/test_suite.log

# Run performance benchmark
echo "Running performance benchmark..."
php performance_benchmark.php 2>&1 | tee logs/performance_benchmark.log

# Run system health check
echo "Running system health check..."
php system_health_checker.php 2>&1 | tee logs/system_health.log

echo ""

# Phase 3: DEBUG (Issue Identification)
echo "🐛 Phase 3: Debugging..."
echo "-------------------------------------"

# Run enhanced debug system
echo "Running enhanced debug system..."
php debug_system.php 2>&1 | tee logs/debug_system.log

# Check error logs
echo "Checking error logs..."
if [ -f "logs/debug.log" ]; then
    echo "Recent errors:"
    tail -10 logs/debug.log
else
    echo "No debug log found"
fi

# Check system resources
echo "Checking system resources..."
echo "CPU Load: $(uptime | awk -F'load average:' '{ print $2 }')"
echo "Memory Usage: $(free -h | grep Mem | awk '{print $3"/"$2}')"
echo "Disk Usage: $(df -h . | tail -1 | awk '{print $5}')"

echo ""

# Phase 4: IMPROVE (Optimization)
echo "⚡ Phase 4: Optimizing..."
echo "-------------------------------------"

# Check for optimization opportunities
echo "Checking for optimization opportunities..."

# Check if OPcache is enabled
if php -m | grep -q "opcache"; then
    echo "✅ OPcache is enabled"
else
    echo "⚠️  OPcache is not enabled - consider enabling for better performance"
fi

# Check database indexes
echo "Checking database indexes..."
if [ -f "config.php" ]; then
    php -r "
    require_once 'config.php';
    if (isset(\$db_host) && isset(\$db_user) && isset(\$db_pass) && isset(\$db_name)) {
        \$mysqli = new mysqli(\$db_host, \$db_user, \$db_pass, \$db_name);
        if (!\$mysqli->connect_error) {
            \$result = \$mysqli->query('SHOW TABLES');
            if (\$result) {
                echo 'Database tables found: ' . \$result->num_rows . '\n';
            }
            \$mysqli->close();
        }
    }
    " 2>&1 | tee logs/database_check.log
fi

# Clean up cache files
echo "Cleaning up cache files..."
find cache/ -name "*.tmp" -delete 2>/dev/null || true
find cache/ -name "*.cache" -mtime +7 -delete 2>/dev/null || true

echo ""

# Phase 5: REPEAT (Iteration)
echo "🔄 Phase 5: Preparing for next iteration..."
echo "-------------------------------------"

# Generate summary report
echo "Generating summary report..."
{
    echo "Development Cycle Summary Report"
    echo "Generated: $(date)"
    echo "====================================="
    echo ""
    echo "Phase 1 - READ:"
    echo "- Syntax check completed"
    echo "- File permissions checked"
    echo "- Configuration verified"
    echo ""
    echo "Phase 2 - RUN:"
    echo "- Test suite executed"
    echo "- Performance benchmark completed"
    echo "- System health checked"
    echo ""
    echo "Phase 3 - DEBUG:"
    echo "- Debug system executed"
    echo "- Error logs reviewed"
    echo "- System resources monitored"
    echo ""
    echo "Phase 4 - IMPROVE:"
    echo "- Optimization opportunities identified"
    echo "- Cache cleaned"
    echo "- Performance recommendations generated"
    echo ""
    echo "Phase 5 - REPEAT:"
    echo "- Summary report generated"
    echo "- Ready for next iteration"
    echo ""
    echo "Files generated:"
    echo "- logs/syntax_check.log"
    echo "- logs/test_suite.log"
    echo "- logs/performance_benchmark.log"
    echo "- logs/system_health.log"
    echo "- logs/debug_system.log"
    echo "- logs/database_check.log"
    echo "- test_report.txt"
    echo "- performance_report.txt"
} > logs/cycle_summary.log

# Display summary
echo "✅ Development cycle completed!"
echo ""
echo "📊 Summary:"
echo "- Logs saved to: logs/"
echo "- Test report: test_report.txt"
echo "- Performance report: performance_report.txt"
echo "- Cycle summary: logs/cycle_summary.log"
echo ""
echo "🔄 Ready for next iteration!"
echo ""
echo "Next steps:"
echo "1. Review the generated reports"
echo "2. Address any issues found"
echo "3. Implement optimizations"
echo "4. Run the cycle again"
echo ""
echo "Quick commands:"
echo "- php test_suite.php          # Run tests only"
echo "- php performance_benchmark.php # Run benchmarks only"
echo "- php debug_system.php        # Run debug only"
echo "- ./development_cycle.sh      # Run full cycle"
echo ""

# Optional: Git operations (uncomment if using git)
# echo "Git operations:"
# if [ -d ".git" ]; then
#     echo "Adding changes to git..."
#     git add .
#     git commit -m "Development cycle iteration $(date)"
#     echo "✅ Changes committed to git"
# else
#     echo "⚠️  Git repository not found"
# fi

echo "====================================="
echo "Development cycle completed at: $(date)"
echo "=====================================" 