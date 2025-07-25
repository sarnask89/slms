#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Device credentials
HOST="10.0.222.86"
USERNAME="sarna"
PASSWORD="Loveganja151!"
PORT="8728"  # Standard MikroTik API port (use 8729 for SSL)

# Debug mode
DEBUG=true

# Function to print colored output
print_result() {
    local test_name=$1
    local success=$2
    local message=$3
    
    if [ "$success" = true ]; then
        echo -e "${GREEN}✓ $test_name: $message${NC}"
    else
        echo -e "${RED}✗ $test_name: $message${NC}"
    fi
}

# Function to print debug info
debug() {
    if [ "$DEBUG" = true ]; then
        echo -e "${YELLOW}DEBUG: $1${NC}"
    fi
}

# Function to send API command
send_command() {
    local command=$1
    debug "Sending command: $command"
    
    # Use netcat with a 5-second timeout
    (echo -e "/login\n=name=$USERNAME\n=password=$PASSWORD\n\n$command"; sleep 1) | timeout 5 nc $HOST $PORT
}

echo -e "${YELLOW}Testing MikroTik API${NC}"
echo "Host: $HOST:$PORT"
echo "Username: $USERNAME"
echo

# Test 1: Login
echo -e "${YELLOW}Test 1: Login${NC}"
debug "Attempting login..."
response=$(send_command "/system/identity/print")
if [[ $response == *"!done"* ]]; then
    print_result "Login" true "Successfully logged in"
else
    print_result "Login" false "Failed to log in"
    debug "Response: $response"
    exit 1
fi
echo

# Test 2: System Resources
echo -e "${YELLOW}Test 2: System Resources${NC}"
response=$(send_command "/system/resource/print")
if [[ $response == *"!done"* ]]; then
    print_result "System Resources" true "Successfully retrieved system resources"
    debug "Response: $response"
else
    print_result "System Resources" false "Failed to get system resources"
    debug "Response: $response"
fi
echo

# Test 3: System Health
echo -e "${YELLOW}Test 3: System Health${NC}"
response=$(send_command "/system/health/print")
if [[ $response == *"!done"* ]]; then
    print_result "System Health" true "Successfully retrieved system health"
    debug "Response: $response"
else
    print_result "System Health" false "Failed to get system health"
    debug "Response: $response"
fi
echo

# Test 4: Interfaces
echo -e "${YELLOW}Test 4: Interfaces${NC}"
response=$(send_command "/interface/print")
if [[ $response == *"!done"* ]]; then
    print_result "Interfaces" true "Successfully retrieved interfaces"
    debug "Response: $response"
else
    print_result "Interfaces" false "Failed to get interfaces"
    debug "Response: $response"
fi
echo

# Test 5: DHCP Server
echo -e "${YELLOW}Test 5: DHCP Server${NC}"
response=$(send_command "/ip/dhcp-server/print")
if [[ $response == *"!done"* ]]; then
    print_result "DHCP Servers" true "Successfully retrieved DHCP servers"
    debug "Response: $response"
else
    print_result "DHCP Servers" false "Failed to get DHCP servers"
    debug "Response: $response"
fi
echo

# Test 6: DHCP Leases
echo -e "${YELLOW}Test 6: DHCP Leases${NC}"
response=$(send_command "/ip/dhcp-server/lease/print")
if [[ $response == *"!done"* ]]; then
    print_result "DHCP Leases" true "Successfully retrieved DHCP leases"
    debug "Response: $response"
else
    print_result "DHCP Leases" false "Failed to get DHCP leases"
    debug "Response: $response"
fi
echo

# Test 7: DNS Settings
echo -e "${YELLOW}Test 7: DNS Settings${NC}"
response=$(send_command "/ip/dns/print")
if [[ $response == *"!done"* ]]; then
    print_result "DNS Settings" true "Successfully retrieved DNS settings"
    debug "Response: $response"
else
    print_result "DNS Settings" false "Failed to get DNS settings"
    debug "Response: $response"
fi
echo

# Test 8: Queue Tree
echo -e "${YELLOW}Test 8: Queue Tree${NC}"
response=$(send_command "/queue/simple/print")
if [[ $response == *"!done"* ]]; then
    print_result "Queue Tree" true "Successfully retrieved queues"
    debug "Response: $response"
else
    print_result "Queue Tree" false "Failed to get queues"
    debug "Response: $response"
fi
echo

# Test 9: System Clock
echo -e "${YELLOW}Test 9: System Clock${NC}"
response=$(send_command "/system/clock/print")
if [[ $response == *"!done"* ]]; then
    print_result "System Clock" true "Successfully retrieved system clock"
    debug "Response: $response"
else
    print_result "System Clock" false "Failed to get system clock"
    debug "Response: $response"
fi
echo

# Test 10: System Identity
echo -e "${YELLOW}Test 10: System Identity${NC}"
response=$(send_command "/system/identity/print")
if [[ $response == *"!done"* ]]; then
    print_result "System Identity" true "Successfully retrieved system identity"
    debug "Response: $response"
else
    print_result "System Identity" false "Failed to get system identity"
    debug "Response: $response"
fi
echo
