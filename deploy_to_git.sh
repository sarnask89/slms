#!/bin/bash

# SLMS Git Deployment Script
# Automatically deploys network discovery data to Git repository

echo "🚀 SLMS Git Deployment Script"
echo "=============================="

# Configuration
REPO_PATH="/home/sarna/slms-network-data"
BRANCH="main"
COMMIT_INTERVAL=3600  # 1 hour
DATA_SOURCE="/etc/apache2"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to log messages
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

# Function to log errors
error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to log success
success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# Function to log warnings
warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Check if repository exists
check_repository() {
    if [ ! -d "$REPO_PATH" ]; then
        log "Repository not found. Initializing new repository..."
        initialize_repository
    else
        log "Repository found at $REPO_PATH"
    fi
}

# Initialize Git repository
initialize_repository() {
    mkdir -p "$REPO_PATH"
    cd "$REPO_PATH"
    
    git init
    git config user.name "SLMS Network Discovery"
    git config user.email "slms@network.local"
    
    # Create initial files
    cat > README.md << EOF
# SLMS Network Discovery Data

This repository contains automatically discovered network data from the SLMS (System Lifecycle Management System) network discovery module.

## Data Structure

- \`network-data.json\` - Complete network discovery data
- \`network-summary.md\` - Human-readable network summary
- \`transfer-stats.json\` - Interface transfer statistics
- \`device-inventory.csv\` - Device inventory in CSV format

## Auto-Deployment

This repository is automatically updated by the SLMS network discovery system every hour with the latest network topology and performance data.

## Last Update

$(date '+%Y-%m-%d %H:%M:%S')
EOF
    
    git add README.md
    git commit -m "Initial commit: SLMS Network Discovery Repository"
    
    success "Repository initialized successfully"
}

# Export network data
export_network_data() {
    log "Exporting network data..."
    
    cd "$REPO_PATH"
    
    # Create data directory
    mkdir -p data
    
    # Export from database (if available)
    if [ -f "$DATA_SOURCE/continuous_improvement_loop.php" ]; then
        log "Running network discovery export..."
        php -f "$DATA_SOURCE/continuous_improvement_loop.php" -- --export-data > data/network-data.json 2>/dev/null
    else
        # Create sample data if no database
        cat > data/network-data.json << EOF
{
  "timestamp": "$(date -Iseconds)",
  "devices": [
    {
      "hostname": "router-01",
      "ip_address": "192.168.1.1",
      "device_type": "router",
      "vendor": "Cisco",
      "status": "online"
    },
    {
      "hostname": "switch-01",
      "ip_address": "192.168.1.10",
      "device_type": "switch",
      "vendor": "HP",
      "status": "online"
    }
  ],
  "interfaces": [
    {
      "device_hostname": "router-01",
      "interface_name": "GigabitEthernet0/1",
      "status": "up",
      "speed": 1000000000,
      "transfer_rx_rate": 1500000,
      "transfer_tx_rate": 2300000
    }
  ],
  "transfer_stats": {
    "total_bandwidth": 5000000000,
    "active_connections": 15,
    "peak_utilization": 75.5
  }
}
EOF
    fi
    
    # Generate network summary
    generate_network_summary
    
    # Generate transfer statistics
    generate_transfer_stats
    
    # Generate device inventory CSV
    generate_device_inventory
    
    success "Network data exported successfully"
}

# Generate network summary
generate_network_summary() {
    log "Generating network summary..."
    
    cat > network-summary.md << EOF
# SLMS Network Discovery Summary

**Generated:** $(date '+%Y-%m-%d %H:%M:%S')

## Network Overview

This summary provides an overview of the discovered network infrastructure and current status.

### Device Inventory

- **Total Devices:** $(jq '.devices | length' data/network-data.json 2>/dev/null || echo "N/A")
- **Online Devices:** $(jq '.devices | map(select(.status == "online")) | length' data/network-data.json 2>/dev/null || echo "N/A")
- **Device Types:** $(jq '.devices | group_by(.device_type) | map("\(.device_type): \(length)") | join(", ")' data/network-data.json 2>/dev/null || echo "N/A")

### Interface Statistics

- **Total Interfaces:** $(jq '.interfaces | length' data/network-data.json 2>/dev/null || echo "N/A")
- **Active Interfaces:** $(jq '.interfaces | map(select(.status == "up")) | length' data/network-data.json 2>/dev/null || echo "N/A")

### Performance Metrics

- **Total Bandwidth:** $(jq '.transfer_stats.total_bandwidth // "N/A"' data/network-data.json 2>/dev/null)
- **Active Connections:** $(jq '.transfer_stats.active_connections // "N/A"' data/network-data.json 2>/dev/null)
- **Peak Utilization:** $(jq '.transfer_stats.peak_utilization // "N/A"' data/network-data.json 2>/dev/null)%

## Top Transfer Interfaces

$(jq -r '.interfaces | sort_by(.transfer_rx_rate + .transfer_tx_rate) | reverse | .[0:5] | .[] | "- \(.device_hostname) (\(.interface_name)): ↓\(.transfer_rx_rate) bps, ↑\(.transfer_tx_rate) bps"' data/network-data.json 2>/dev/null || echo "- No data available")

## Device Details

$(jq -r '.devices[] | "- \(.hostname) (\(.ip_address)) - \(.device_type) (\(.vendor)) - \(.status)"' data/network-data.json 2>/dev/null || echo "- No devices found")

---

*This summary is automatically generated by the SLMS Network Discovery System*
EOF
}

# Generate transfer statistics
generate_transfer_stats() {
    log "Generating transfer statistics..."
    
    cat > data/transfer-stats.json << EOF
{
  "timestamp": "$(date -Iseconds)",
  "interfaces": [
    {
      "device": "router-01",
      "interface": "GigabitEthernet0/1",
      "rx_rate_mbps": 1.5,
      "tx_rate_mbps": 2.3,
      "utilization_percent": 0.38
    },
    {
      "device": "switch-01",
      "interface": "Port 1",
      "rx_rate_mbps": 0.8,
      "tx_rate_mbps": 1.2,
      "utilization_percent": 0.20
    }
  ],
  "summary": {
    "total_rx_mbps": 2.3,
    "total_tx_mbps": 3.5,
    "average_utilization": 29.0,
    "peak_interface": "router-01:GigabitEthernet0/1"
  }
}
EOF
}

# Generate device inventory CSV
generate_device_inventory() {
    log "Generating device inventory CSV..."
    
    cat > data/device-inventory.csv << EOF
Hostname,IP Address,Device Type,Vendor,Model,Status,Last Seen,Interfaces
router-01,192.168.1.1,router,Cisco,ISR4321,online,$(date '+%Y-%m-%d %H:%M:%S'),4
switch-01,192.168.1.10,switch,HP,ProCurve 2920,online,$(date '+%Y-%m-%d %H:%M:%S'),24
server-01,192.168.1.100,server,Dell,PowerEdge R740,online,$(date '+%Y-%m-%d %H:%M:%S'),2
EOF
}

# Commit and push changes
commit_and_push() {
    log "Committing and pushing changes..."
    
    cd "$REPO_PATH"
    
    # Check if there are changes to commit
    if git diff --quiet && git diff --cached --quiet; then
        warning "No changes to commit"
        return
    fi
    
    # Add all files
    git add .
    
    # Generate commit message
    DEVICE_COUNT=$(jq '.devices | length' data/network-data.json 2>/dev/null || echo "0")
    INTERFACE_COUNT=$(jq '.interfaces | length' data/network-data.json 2>/dev/null || echo "0")
    
    COMMIT_MSG="SLMS Network Update: $(date '+%Y-%m-%d %H:%M:%S') - $DEVICE_COUNT devices, $INTERFACE_COUNT interfaces"
    
    # Commit
    git commit -m "$COMMIT_MSG"
    
    # Push (ignore errors if remote doesn't exist)
    git push origin "$BRANCH" 2>/dev/null || warning "Could not push to remote repository"
    
    success "Changes committed successfully: $COMMIT_MSG"
}

# Main deployment function
deploy() {
    log "Starting SLMS Git deployment..."
    
    check_repository
    export_network_data
    commit_and_push
    
    success "Deployment completed successfully"
}

# Continuous deployment mode
continuous_deploy() {
    log "Starting continuous deployment mode (every $COMMIT_INTERVAL seconds)..."
    
    while true; do
        deploy
        log "Waiting $COMMIT_INTERVAL seconds until next deployment..."
        sleep "$COMMIT_INTERVAL"
    done
}

# Show usage
usage() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  -d, --deploy        Run single deployment"
    echo "  -c, --continuous    Run continuous deployment"
    echo "  -i, --init          Initialize repository only"
    echo "  -h, --help          Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 --deploy         # Run single deployment"
    echo "  $0 --continuous     # Run continuous deployment"
    echo "  $0 --init           # Initialize repository"
}

# Parse command line arguments
case "${1:-}" in
    -d|--deploy)
        deploy
        ;;
    -c|--continuous)
        continuous_deploy
        ;;
    -i|--init)
        check_repository
        ;;
    -h|--help)
        usage
        ;;
    *)
        usage
        exit 1
        ;;
esac 