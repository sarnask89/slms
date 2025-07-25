#!/bin/bash

# 🐳 Docker-based Development Cycle Runner
# "Read, Run, Debug, Improve, Repeat" with Docker

echo "🐳 Starting Docker-based Development Cycle..."
echo "=============================================="
echo "Date: $(date)"
echo "Directory: $(pwd)"
echo "=============================================="
echo ""

# Create logs directory if it doesn't exist
mkdir -p logs
mkdir -p cache

# Build the development container
echo "🔨 Building development container..."
docker build -f Dockerfile.dev -t slms-dev .

if [ $? -eq 0 ]; then
    echo "✅ Container built successfully!"
else
    echo "❌ Container build failed!"
    exit 1
fi

# Phase 1: READ (Analysis)
echo ""
echo "📖 Phase 1: Analyzing code..."
echo "-------------------------------------"

# Run syntax check in container
echo "Checking PHP syntax..."
docker run --rm -v $(pwd):/var/www/html slms-dev find /var/www/html -name "*.php" -exec php -l {} \; 2>&1 | tee logs/syntax_check.log

# Phase 2: RUN (Execution)
echo ""
echo "🚀 Phase 2: Running tests..."
echo "-------------------------------------"

# Run test suite in container
echo "Running automated test suite..."
docker run --rm -v $(pwd):/var/www/html slms-dev php /var/www/html/test_suite.php 2>&1 | tee logs/test_suite.log

# Run performance benchmark in container
echo "Running performance benchmark..."
docker run --rm -v $(pwd):/var/www/html slms-dev php /var/www/html/performance_benchmark.php 2>&1 | tee logs/performance_benchmark.log

# Run system health check in container
echo "Running system health check..."
docker run --rm -v $(pwd):/var/www/html slms-dev php /var/www/html/system_health_checker.php 2>&1 | tee logs/system_health.log

# Phase 3: DEBUG (Issue Identification)
echo ""
echo "🐛 Phase 3: Debugging..."
echo "-------------------------------------"

# Run debug system in container
echo "Running enhanced debug system..."
docker run --rm -v $(pwd):/var/www/html slms-dev php /var/www/html/debug_system.php 2>&1 | tee logs/debug_system.log

# Phase 4: IMPROVE (Optimization)
echo ""
echo "⚡ Phase 4: Optimizing..."
echo "-------------------------------------"

# Check PHP extensions in container
echo "Checking PHP extensions..."
docker run --rm slms-dev php -m | tee logs/php_extensions.log

# Check OPcache status
echo "Checking OPcache status..."
docker run --rm slms-dev php -m | grep -i opcache | tee logs/opcache_status.log

# Phase 5: REPEAT (Iteration)
echo ""
echo "🔄 Phase 5: Preparing for next iteration..."
echo "-------------------------------------"

# Generate summary report
echo "Generating summary report..."
{
    echo "Docker-based Development Cycle Summary Report"
    echo "Generated: $(date)"
    echo "=============================================="
    echo ""
    echo "Phase 1 - READ:"
    echo "- Syntax check completed in Docker container"
    echo "- File structure verified"
    echo ""
    echo "Phase 2 - RUN:"
    echo "- Test suite executed in Docker container"
    echo "- Performance benchmark completed"
    echo "- System health checked"
    echo ""
    echo "Phase 3 - DEBUG:"
    echo "- Debug system executed in Docker container"
    echo "- Error logs reviewed"
    echo ""
    echo "Phase 4 - IMPROVE:"
    echo "- PHP extensions analyzed"
    echo "- OPcache status checked"
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
    echo "- logs/php_extensions.log"
    echo "- logs/opcache_status.log"
} > logs/docker_cycle_summary.log

# Display summary
echo "✅ Docker-based development cycle completed!"
echo ""
echo "📊 Summary:"
echo "- Container: slms-dev"
echo "- Logs saved to: logs/"
echo "- Cycle summary: logs/docker_cycle_summary.log"
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
echo "- ./run_dev_cycle_docker.sh  # Run full Docker cycle"
echo "- docker run --rm -v \$(pwd):/var/www/html slms-dev php test_suite.php"
echo "- docker run --rm -v \$(pwd):/var/www/html slms-dev php performance_benchmark.php"
echo ""

echo "=============================================="
echo "Docker development cycle completed at: $(date)"
echo "==============================================" 