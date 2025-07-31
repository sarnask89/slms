#!/bin/bash

# Continuous Improvement Loop Launcher
# SLMS v1.1.0 - Automated Enhancement System

echo "🚀 Starting Continuous Improvement Loop for SLMS v1.1.0"
echo "=================================================="

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed or not in PATH"
    exit 1
fi

# Check if we're in the right directory
if [ ! -f "config.php" ]; then
    echo "❌ config.php not found. Please run this script from the SLMS root directory."
    exit 1
fi

# Create logs directory if it doesn't exist
mkdir -p logs

# Set up monitoring
echo "📊 Setting up monitoring..."
MONITOR_FILE="logs/improvement_monitor.log"
PID_FILE="logs/improvement_loop.pid"

# Function to stop the loop
stop_loop() {
    echo "🛑 Stopping improvement loop..."
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE")
        kill $PID 2>/dev/null
        rm -f "$PID_FILE"
    fi
    echo "✅ Improvement loop stopped"
    exit 0
}

# Trap Ctrl+C to stop gracefully
trap stop_loop SIGINT SIGTERM

# Start the improvement loop
echo "🔄 Starting improvement loop..."
echo "📝 Logs will be written to: $MONITOR_FILE"
echo "🆔 Process ID will be stored in: $PID_FILE"
echo ""
echo "Press Ctrl+C to stop the loop"
echo ""

# Run the improvement loop in background
php continuous_improvement_loop.php > "$MONITOR_FILE" 2>&1 &
LOOP_PID=$!

# Save PID
echo $LOOP_PID > "$PID_FILE"

echo "✅ Improvement loop started with PID: $LOOP_PID"
echo ""

# Monitor the loop
while kill -0 $LOOP_PID 2>/dev/null; do
    echo "🔄 Loop is running... (PID: $LOOP_PID)"
    echo "📊 Last 5 log entries:"
    tail -n 5 "$MONITOR_FILE" 2>/dev/null || echo "No logs yet..."
    echo ""
    sleep 30  # Check every 30 seconds
done

echo "❌ Improvement loop stopped unexpectedly"
rm -f "$PID_FILE" 