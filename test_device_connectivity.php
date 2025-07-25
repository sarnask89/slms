<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/modules/mikrotik_api.php';

echo "<h2>Device Connectivity Test</h2>";

try {
    $pdo = get_pdo();
    
    // Get devices with API credentials
    $stmt = $pdo->query("SELECT id, name, ip_address, api_username, api_password FROM skeleton_devices WHERE api_username IS NOT NULL AND api_password IS NOT NULL");
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($devices) . " devices with API credentials</p>";
    
    foreach ($devices as $device) {
        echo "<h3>Testing Device: {$device['name']} ({$device['ip_address']})</h3>";
        
        // Test 1: Basic connectivity
        echo "<h4>1. Basic Connectivity Test</h4>";
        $ping_result = systemPing($device['ip_address'], 2);
        if ($ping_result) {
            echo "✅ Ping successful<br>";
        } else {
            echo "❌ Ping failed<br>";
        }
        
        // Test 2: MikroTik API connection
        echo "<h4>2. MikroTik API Test</h4>";
        try {
            $api = new MikroTikAPI($device['ip_address'], $device['api_username'], $device['api_password']);
            
            // Test simple command
            $result = $api->execute("/system/resource/print");
            if (isset($result['error'])) {
                echo "❌ API Error: " . $result['error'] . "<br>";
            } else {
                echo "✅ API connection successful<br>";
                echo "Response: " . substr($result, 0, 200) . "...<br>";
            }
        } catch (Exception $e) {
            echo "❌ API Exception: " . $e->getMessage() . "<br>";
        }
        
        // Test 3: SNMP connectivity
        echo "<h4>3. SNMP Test</h4>";
        if (extension_loaded('snmp')) {
            $sysDescr = @snmpget($device['ip_address'], 'public', '1.3.6.1.2.1.1.1.0', 1000000, 1);
            if ($sysDescr !== false && stripos($sysDescr, 'No Such') === false) {
                echo "✅ SNMP accessible<br>";
                echo "System Description: " . htmlspecialchars($sysDescr) . "<br>";
            } else {
                echo "❌ SNMP not accessible or no response<br>";
            }
        } else {
            echo "❌ SNMP extension not loaded<br>";
        }
        
        // Test 4: Port connectivity
        echo "<h4>4. Port Connectivity Test</h4>";
        $ports = [8728, 8729, 22, 23]; // MikroTik API, API SSL, SSH, Telnet
        foreach ($ports as $port) {
            $connection = @fsockopen($device['ip_address'], $port, $errno, $errstr, 3);
            if ($connection) {
                echo "✅ Port $port open<br>";
                fclose($connection);
            } else {
                echo "❌ Port $port closed ($errstr)<br>";
            }
        }
        
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage();
}

echo "<p><a href='modules/network_monitoring_enhanced.php'>Back to Network Monitoring</a></p>";
?> 