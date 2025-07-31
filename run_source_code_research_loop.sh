#!/bin/bash

# Source Code Research Loop Runner
# SLMS v1.2.0 - Source Code Analysis System

echo "🚀 Starting Source Code Research Loop..."
echo "📁 Current directory: $(pwd)"
echo "⏰ Start time: $(date)"

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed or not in PATH"
    exit 1
fi

echo "✅ PHP version: $(php -v | head -n 1)"

# Check if we're in the correct directory
if [ ! -f "source_code_research_loop.php" ]; then
    echo "❌ source_code_research_loop.php not found in current directory"
    echo "📁 Available files:"
    ls -la *.php
    exit 1
fi

# Check if config.php exists
if [ ! -f "config.php" ]; then
    echo "⚠️ config.php not found, running in test mode"
    TEST_MODE="--test"
else
    echo "✅ config.php found"
    TEST_MODE=""
fi

# Create log directory if it doesn't exist
mkdir -p logs

# Run the source code research loop
echo "🔬 Starting source code analysis..."
echo "=================================="

if [ "$1" = "--test" ] || [ "$TEST_MODE" = "--test" ]; then
    echo "🧪 Running in TEST MODE"
    php source_code_research_loop.php --test
else
    echo "🚀 Running in PRODUCTION MODE"
    php source_code_research_loop.php
fi

# Check exit status
if [ $? -eq 0 ]; then
    echo "✅ Source Code Research Loop completed successfully"
else
    echo "❌ Source Code Research Loop failed"
    exit 1
fi

echo "📊 Generating summary report..."

# Check for generated reports
if [ -f "source_code_research_report_*.json" ]; then
    echo "📄 Research reports generated:"
    ls -la source_code_research_report_*.json
fi

# Check log file
if [ -f "source_code_research_loop.log" ]; then
    echo "📝 Log file size: $(du -h source_code_research_loop.log | cut -f1)"
    echo "📋 Last 10 log entries:"
    tail -10 source_code_research_loop.log
fi

echo "⏰ End time: $(date)"
echo "🎯 Source Code Research Loop completed!" 