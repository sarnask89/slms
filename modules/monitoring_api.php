<?php
require_once 'module_loader.php';


// Monitoring API for SNMP and Cacti integration
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_devices':
            getDevices();
            break;
        case 'test_snmp':
            testSNMP();
            break;
        case 'get_system_health':
            getSystemHealth();
            break;
        case 'add_to_cacti':
            addToCacti();
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function getDevices() {
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT id, name, ip_address, type, status FROM devices WHERE ip_address IS NOT NULL AND ip_address != '' ORDER BY name");
    $devices = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'devices' => $devices,
        'total' => count($devices)
    ]);
}

function testSNMP() {
    $ip = $_GET['ip'] ?? '';
    $community = $_GET['community'] ?? 'public';
    $oid = $_GET['oid'] ?? '.1.3.6.1.2.1.1.1.0';
    
    if (empty($ip)) {
        echo json_encode(['error' => 'IP address is required']);
        return;
    }
    
    // Execute SNMP command
    $command = "snmpget -v 2c -c " . escapeshellarg($community) . " " . escapeshellarg($ip) . " " . escapeshellarg($oid) . " 2>&1";
    $output = shell_exec($command);
    
    if ($output && !strpos($output, 'Timeout')) {
        echo json_encode([
            'success' => true,
            'output' => trim($output),
            'ip' => $ip,
            'community' => $community,
            'oid' => $oid
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'SNMP timeout or no response',
            'ip' => $ip,
            'command' => $command
        ]);
    }
}

function getSystemHealth() {
    $pdo = get_pdo();
    
    // Get device statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM devices");
    $totalDevices = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as online FROM devices WHERE status = 'online'");
    $onlineDevices = $stmt->fetch()['online'];
    
    // Check SNMP availability
    $snmpAvailable = !empty(shell_exec('which snmpget'));
    
    // Check Cacti availability
    $cactiUrl = 'http://localhost/cacti/';
    $cactiAvailable = @file_get_contents($cactiUrl) !== false;
    
    // Calculate health percentage
    $healthPercent = $totalDevices > 0 ? ($onlineDevices / $totalDevices) * 100 : 0;
    
    echo json_encode([
        'success' => true,
        'health' => [
            'total_devices' => $totalDevices,
            'online_devices' => $onlineDevices,
            'health_percent' => round($healthPercent, 1),
            'snmp_available' => $snmpAvailable,
            'cacti_available' => $cactiAvailable,
            'database_connected' => true
        ]
    ]);
}

function addToCacti() {
    $deviceName = $_POST['device_name'] ?? '';
    $deviceIp = $_POST['device_ip'] ?? '';
    $snmpCommunity = $_POST['snmp_community'] ?? 'public';
    $deviceType = $_POST['device_type'] ?? 'other';
    
    if (empty($deviceName) || empty($deviceIp)) {
        echo json_encode(['error' => 'Device name and IP are required']);
        return;
    }
    
    // In a real implementation, this would integrate with Cacti's API
    // For now, we'll just return success and provide instructions
    
    echo json_encode([
        'success' => true,
        'message' => 'Device ready for Cacti integration',
        'device' => [
            'name' => $deviceName,
            'ip' => $deviceIp,
            'snmp_community' => $snmpCommunity,
            'type' => $deviceType
        ],
        'instructions' => [
            '1. Open Cacti in a new tab',
            '2. Go to Devices → Add',
            '3. Use the device information above',
            '4. Configure SNMP settings',
            '5. Add graphs and monitoring'
        ]
    ]);
}
?> 