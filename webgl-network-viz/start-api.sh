#!/bin/bash

# WebGL Network Visualization API Server Startup Script

# Configuration
API_DIR="/var/www/html/webgl-network-viz/api"
LOG_DIR="/var/log/webgl-network-viz"
PID_FILE="/var/run/webgl-network-viz.pid"
PYTHON_VENV="/home/sarna/mikrotik_venv"

# Create log directory if it doesn't exist
mkdir -p "$LOG_DIR"

# Function to start the API server
start_api() {
    echo "Starting WebGL Network Visualization API Server..."
    
    # Activate virtual environment
    source "$PYTHON_VENV/bin/activate"
    
    # Change to API directory
    cd "$API_DIR"
    
    # Start the API server
    nohup python network_api_server.py > "$LOG_DIR/api.log" 2>&1 &
    
    # Save PID
    echo $! > "$PID_FILE"
    
    echo "API Server started with PID $(cat $PID_FILE)"
    echo "Logs available at: $LOG_DIR/api.log"
}

# Function to stop the API server
stop_api() {
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE")
        echo "Stopping API Server (PID: $PID)..."
        kill "$PID" 2>/dev/null
        rm -f "$PID_FILE"
        echo "API Server stopped"
    else
        echo "API Server is not running"
    fi
}

# Function to restart the API server
restart_api() {
    stop_api
    sleep 2
    start_api
}

# Function to check status
status_api() {
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE")
        if kill -0 "$PID" 2>/dev/null; then
            echo "API Server is running (PID: $PID)"
            return 0
        else
            echo "API Server is not running (stale PID file)"
            rm -f "$PID_FILE"
            return 1
        fi
    else
        echo "API Server is not running"
        return 1
    fi
}

# Function to show logs
show_logs() {
    if [ -f "$LOG_DIR/api.log" ]; then
        tail -f "$LOG_DIR/api.log"
    else
        echo "No log file found at $LOG_DIR/api.log"
    fi
}

# Main script logic
case "$1" in
    start)
        start_api
        ;;
    stop)
        stop_api
        ;;
    restart)
        restart_api
        ;;
    status)
        status_api
        ;;
    logs)
        show_logs
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|status|logs}"
        echo ""
        echo "Commands:"
        echo "  start   - Start the API server"
        echo "  stop    - Stop the API server"
        echo "  restart - Restart the API server"
        echo "  status  - Check server status"
        echo "  logs    - Show live logs"
        exit 1
        ;;
esac

exit 0 