<?php
require_once __DIR__ . '/config.php';

echo "<h2>SNMP/MNDP System Test</h2>";

// Test 1: Check if SNMP extension is loaded
echo "<h3>1. SNMP Extension Check</h3>";
if (extension_loaded('snmp')) {
    echo "✅ SNMP extension is loaded<br>";
    echo "SNMP functions available: " . implode(', ', get_extension_funcs('snmp')) . "<br>";
} else {
    echo "❌ SNMP extension is NOT loaded<br>";
    exit;
}

// Test 2: Test basic SNMP get
echo "<h3>2. Basic SNMP Test</h3>";
$test_ip = '127.0.0.1'; // Test with localhost first
$community = 'public';

echo "Testing SNMP get on $test_ip...<br>";
$result = @snmpget($test_ip, $community, '1.3.6.1.2.1.1.1.0', 1000000, 1);
if ($result !== false) {
    echo "✅ SNMP get successful: " . htmlspecialchars($result) . "<br>";
} else {
    echo "❌ SNMP get failed (this is normal for localhost)<br>";
}

// Test 3: Test SNMP walk
echo "<h3>3. SNMP Walk Test</h3>";
$result = @snmprealwalk($test_ip, $community, '1.3.6.1.2.1.1', 1000000, 1);
if ($result !== false && is_array($result)) {
    echo "✅ SNMP walk successful, found " . count($result) . " OIDs<br>";
} else {
    echo "❌ SNMP walk failed (this is normal for localhost)<br>";
}

// Test 4: Test socket functions for MNDP
echo "<h3>4. Socket Functions Test</h3>";
if (function_exists('socket_create')) {
    echo "✅ Socket functions are available<br>";
    
    $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($socket !== false) {
        echo "✅ Socket creation successful<br>";
        socket_close($socket);
    } else {
        echo "❌ Socket creation failed<br>";
    }
} else {
    echo "❌ Socket functions are NOT available<br>";
}

// Test 5: Test database connection
echo "<h3>5. Database Connection Test</h3>";
try {
    $pdo = get_pdo();
    echo "✅ Database connection successful<br>";
    
    // Check if discovered_devices table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'discovered_devices'");
    if ($stmt->rowCount() > 0) {
        echo "✅ discovered_devices table exists<br>";
        
        // Count existing records
        $stmt = $pdo->query("SELECT COUNT(*) FROM discovered_devices");
        $count = $stmt->fetchColumn();
        echo "📊 Found $count discovered devices in database<br>";
    } else {
        echo "❌ discovered_devices table does NOT exist<br>";
    }
    
    // Check if interface_stats table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'interface_stats'");
    if ($stmt->rowCount() > 0) {
        echo "✅ interface_stats table exists<br>";
        
        // Count existing records
        $stmt = $pdo->query("SELECT COUNT(*) FROM interface_stats");
        $count = $stmt->fetchColumn();
        echo "📊 Found $count interface stats records in database<br>";
    } else {
        echo "❌ interface_stats table does NOT exist<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 6: Test MikroTikAPI class
echo "<h3>6. MikroTikAPI Class Test</h3>";
if (file_exists('modules/mikrotik_api.php')) {
    require_once 'modules/mikrotik_api.php';
    echo "✅ MikroTikAPI class loaded<br>";
    
    // Test class instantiation
    try {
        $api = new MikroTikAPI('192.168.1.1', 'admin', 'password');
        echo "✅ MikroTikAPI instantiation successful<br>";
    } catch (Exception $e) {
        echo "❌ MikroTikAPI instantiation failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ MikroTikAPI class file not found<br>";
}

echo "<h3>7. Module Files Check</h3>";
$modules = [
    'modules/network_monitoring_enhanced.php',
    'modules/discover_snmp_mndp.php', 
    'modules/mndp_monitor.php'
];

foreach ($modules as $module) {
    if (file_exists($module)) {
        echo "✅ $module exists<br>";
    } else {
        echo "❌ $module missing<br>";
    }
}

echo "<hr>";
echo "<p><strong>Test completed!</strong> Check the results above to identify any issues.</p>";
echo "<p><a href='test_snmp_mndp_system.php'>Run Full System Test</a> | <a href='create_missing_snmp_tables.php'>Create Missing Tables</a></p>";
?> 