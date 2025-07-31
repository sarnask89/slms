#!/bin/bash

# Enhanced Continuous Improvement Loop Runner
# SLMS v1.2.0 - Research-First Network Adaptation System

echo "🚀 Starting Enhanced Continuous Improvement Loop..."
echo "🎯 Priority: RESEARCH & NETWORK DISCOVERY"
echo "=========================================="

# Set environment variables
export SLMS_MODE="enhanced"
export DISCOVERY_ENABLED="true"
export RESEARCH_PRIORITY="true"

# Check if we're running as root (needed for network discovery)
if [ "$EUID" -ne 0 ]; then
    echo "⚠️  Warning: Not running as root. Some network discovery features may be limited."
    echo "   Consider running with sudo for full network discovery capabilities."
fi

# Check for required tools
echo "🔍 Checking required tools..."

# Check SNMP tools
if command -v snmpget &> /dev/null; then
    echo "✅ SNMP tools available"
else
    echo "⚠️  SNMP tools not found. Installing..."
    apt-get update && apt-get install -y snmp snmp-mibs-downloader
fi

# Check tcpdump for packet capture
if command -v tcpdump &> /dev/null; then
    echo "✅ tcpdump available"
else
    echo "⚠️  tcpdump not found. Installing..."
    apt-get update && apt-get install -y tcpdump
fi

# Check LLDP tools
if command -v lldpctl &> /dev/null; then
    echo "✅ LLDP tools available"
else
    echo "⚠️  LLDP tools not found. Installing..."
    apt-get update && apt-get install -y lldpd
fi

# Create log directories
mkdir -p /var/log/slms
mkdir -p /tmp/slms_discovery

# Set permissions
chmod 755 /var/log/slms
chmod 755 /tmp/slms_discovery

echo "📁 Log directories created"

# Initialize network discovery
echo "🔍 Initializing Network Discovery System..."
php -f continuous_improvement_loop.php -- --init-discovery

# Start the enhanced improvement loop
echo "🔄 Starting Enhanced Continuous Improvement Loop..."
echo "   Research Priority: ENABLED"
echo "   Network Discovery: ENABLED"
echo "   Auto-Adaptation: ENABLED"
echo ""

# Run the main loop
php -f continuous_improvement_loop.php -- --enhanced-mode

echo ""
echo "✅ Enhanced Continuous Improvement Loop completed"
echo "📊 Check logs for detailed results:"
echo "   - enhanced_improvement_loop.log"
echo "   - network_discovery.log"
echo "   - /var/log/slms/" 