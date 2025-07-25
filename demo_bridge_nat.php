<?php
/**
 * Bridge NAT/Mangle System Demonstration
 * Shows how to control traffic flow through bridged interfaces
 */

require_once 'modules/bridge_nat_controller.php';

echo "=== Bridge NAT/Mangle Traffic Control Demo ===\n\n";

// Initialize the bridge NAT controller
$controller = new BridgeNATController();

echo "1. Initializing Bridge NAT System...\n";
$result = $controller->initializeBridgeSystem();
if ($result['success']) {
    echo "✅ Bridge system initialized successfully\n";
} else {
    echo "❌ Bridge system initialization failed: " . $result['error'] . "\n";
}

echo "\n2. Processing New Bridge Connection (Guest)...\n";
$macAddress = '00:11:22:33:44:55';
$result = $controller->processBridgeConnection($macAddress, null, 'guest');
if ($result['success']) {
    echo "✅ Guest connection established\n";
    echo "   MAC: " . $result['mac_address'] . "\n";
    echo "   Role: " . $result['user_role'] . "\n";
    echo "   Filter Rules: " . count($result['filter_rules']) . "\n";
    echo "   NAT Rules: " . count($result['nat_rules']) . "\n";
    echo "   Mangle Rules: " . count($result['mangle_rules']) . "\n";
} else {
    echo "❌ Guest connection failed: " . $result['error'] . "\n";
}

echo "\n3. Processing New Bridge Connection (User)...\n";
$macAddress2 = 'AA:BB:CC:DD:EE:FF';
$result = $controller->processBridgeConnection($macAddress2, 'john_doe', 'user');
if ($result['success']) {
    echo "✅ User connection established\n";
    echo "   MAC: " . $result['mac_address'] . "\n";
    echo "   Username: john_doe\n";
    echo "   Role: " . $result['user_role'] . "\n";
} else {
    echo "❌ User connection failed: " . $result['error'] . "\n";
}

echo "\n4. Handling Portal Authentication...\n";
$result = $controller->handlePortalAuthentication($macAddress, 'guest_user', 'password123');
if ($result['success']) {
    echo "✅ Authentication successful\n";
    echo "   User Role: " . $result['user_role'] . "\n";
    echo "   Message: " . $result['message'] . "\n";
} else {
    echo "❌ Authentication failed: " . $result['error'] . "\n";
}

echo "\n5. Getting Bridge Statistics...\n";
$stats = $controller->getBridgeStats();
if ($stats) {
    echo "✅ Bridge statistics retrieved\n";
    echo "   Total Access: " . $stats['total_access'] . "\n";
    echo "   Active Access: " . $stats['active_access'] . "\n";
    echo "   Expired Access: " . $stats['expired_access'] . "\n";
    echo "   Users by Role: " . json_encode($stats['users_by_role']) . "\n";
    echo "   Bridge Rules:\n";
    echo "     - Filters: " . $stats['bridge_rules']['filters'] . "\n";
    echo "     - NAT: " . $stats['bridge_rules']['nat'] . "\n";
    echo "     - Mangle: " . $stats['bridge_rules']['mangle'] . "\n";
} else {
    echo "❌ Failed to get bridge statistics\n";
}

echo "\n6. Cleanup Expired Access...\n";
$cleaned = $controller->cleanupExpiredAccess();
echo "✅ Cleaned up " . $cleaned . " expired access records\n";

echo "\n=== Demo Complete ===\n";
echo "\nKey Features Demonstrated:\n";
echo "• Bridge filter rules for traffic control\n";
echo "• NAT rules for HTTP redirect and masquerading\n";
echo "• Mangle rules for connection marking\n";
echo "• User role-based access control\n";
echo "• Session management and cleanup\n";
echo "• Statistics and monitoring\n";

echo "\nNext Steps:\n";
echo "1. Configure your Mikrotik router with bridge interfaces\n";
echo "2. Update the controller configuration with your router details\n";
echo "3. Integrate with your captive portal web interface\n";
echo "4. Test with real traffic between your bridged interfaces\n";

echo "\nExample Mikrotik Bridge Configuration:\n";
echo "/interface bridge add name=bridge1\n";
echo "/interface bridge port add bridge=bridge1 interface=ether1\n";
echo "/interface bridge port add bridge=bridge1 interface=ether2\n";
echo "/interface bridge settings set use-ip-firewall=yes use-ip-firewall-for-vlan=yes\n";
echo "/ip address add address=192.168.100.1/24 interface=bridge1\n";
?> 