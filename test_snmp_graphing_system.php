<?php
require_once __DIR__ . '/config.php';
$pdo = get_pdo();

echo "<h2>SNMP Graphing System Test</h2>";

// Test 1: Check if SNMP extension is available
echo "<h3>1. SNMP Extension Check</h3>";
if (extension_loaded('snmp')) {
    echo "<div style='color: green;'>✓ SNMP extension is loaded</div>";
    echo "<div>SNMP version: " . snmp_get_valueretrieval() . "</div>";
} else {
    echo "<div style='color: red;'>✗ SNMP extension is NOT loaded</div>";
    echo "<div>Please install: sudo apt-get install php-snmp</div>";
}

// Test 2: Check database table
echo "<h3>2. Database Table Check</h3>";
try {
    $result = $pdo->query("SHOW TABLES LIKE 'snmp_graph_data'");
    if ($result->rowCount() > 0) {
        echo "<div style='color: green;'>✓ snmp_graph_data table exists</div>";
        
        // Check table structure
        $columns = $pdo->query("DESCRIBE snmp_graph_data")->fetchAll(PDO::FETCH_ASSOC);
        echo "<div>Table columns:</div><ul>";
        foreach ($columns as $col) {
            echo "<li>{$col['Field']} - {$col['Type']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<div style='color: red;'>✗ snmp_graph_data table does not exist</div>";
        echo "<div><a href='create_snmp_graph_table.php'>Click here to create the table</a></div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test 3: Check OID helper
echo "<h3>3. OID Helper Check</h3>";
try {
    $oids = require __DIR__ . '/modules/snmp_oid_helper.php';
    echo "<div style='color: green;'>✓ OID helper loaded successfully</div>";
    echo "<div>Total OIDs available: " . count($oids) . "</div>";
    
    // Show categories
    $categories = [];
    foreach ($oids as $oid => $info) {
        $cat = $info['category'];
        if (!isset($categories[$cat])) {
            $categories[$cat] = 0;
        }
        $categories[$cat]++;
    }
    
    echo "<div>Categories:</div><ul>";
    foreach ($categories as $cat => $count) {
        echo "<li>" . ucfirst($cat) . ": $count OIDs</li>";
    }
    echo "</ul>";
    
    // Show sample OIDs
    echo "<div>Sample OIDs:</div><ul>";
    $sample_count = 0;
    foreach ($oids as $oid => $info) {
        if ($sample_count < 5) {
            echo "<li><strong>{$info['name']}</strong> ({$info['category']}): $oid</li>";
            $sample_count++;
        }
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ OID helper error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test 4: Check available devices
echo "<h3>4. Available Devices Check</h3>";
try {
    $devices = $pdo->query("SELECT DISTINCT ip_address FROM skeleton_devices ORDER BY ip_address")->fetchAll(PDO::FETCH_COLUMN);
    if (count($devices) > 0) {
        echo "<div style='color: green;'>✓ Found " . count($devices) . " devices</div>";
        echo "<div>Devices:</div><ul>";
        foreach ($devices as $ip) {
            echo "<li>$ip</li>";
        }
        echo "</ul>";
    } else {
        echo "<div style='color: orange;'>⚠ No devices found in skeleton_devices table</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Device query error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test 5: Check existing SNMP data
echo "<h3>5. Existing SNMP Data Check</h3>";
try {
    $data_count = $pdo->query("SELECT COUNT(*) FROM snmp_graph_data")->fetchColumn();
    echo "<div>Total SNMP data points: $data_count</div>";
    
    if ($data_count > 0) {
        echo "<div style='color: green;'>✓ SNMP data exists</div>";
        
        // Show recent data
        $recent_data = $pdo->query("
            SELECT device_ip, oid, value, polled_at 
            FROM snmp_graph_data 
            ORDER BY polled_at DESC 
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div>Recent data:</div><ul>";
        foreach ($recent_data as $row) {
            echo "<li>{$row['device_ip']} - {$row['oid']} = {$row['value']} ({$row['polled_at']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<div style='color: orange;'>⚠ No SNMP data found</div>";
        echo "<div><a href='modules/snmp_graph_poll.php'>Click here to poll SNMP data</a></div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Data query error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test 6: Test SNMP connectivity (if devices available)
echo "<h3>6. SNMP Connectivity Test</h3>";
if (!empty($devices) && extension_loaded('snmp')) {
    $test_device = $devices[0];
    $test_oid = '1.3.6.1.2.1.1.1.0'; // System description
    
    echo "<div>Testing SNMP connectivity to: $test_device</div>";
    
    $result = @snmpget($test_device, 'public', $test_oid, 1000000, 1);
    if ($result !== false) {
        echo "<div style='color: green;'>✓ SNMP connectivity successful</div>";
        echo "<div>System description: " . htmlspecialchars($result) . "</div>";
    } else {
        echo "<div style='color: red;'>✗ SNMP connectivity failed</div>";
        echo "<div>Possible issues:</div><ul>";
        echo "<li>SNMP not enabled on device</li>";
        echo "<li>Wrong community string (using 'public')</li>";
        echo "<li>Network connectivity issues</li>";
        echo "<li>Firewall blocking SNMP (port 161)</li>";
        echo "</ul>";
    }
} else {
    echo "<div style='color: orange;'>⚠ Skipping SNMP test (no devices or SNMP extension)</div>";
}

// Test 7: Module availability check
echo "<h3>7. Module Availability Check</h3>";
$modules = [
    'modules/snmp_graph.php' => 'SNMP Graphing Module',
    'modules/interface_monitoring.php' => 'Interface Monitoring Module',
    'modules/queue_monitoring.php' => 'Queue Monitoring Module',
    'modules/snmp_graph_poll.php' => 'SNMP Polling Module',
    'modules/snmp_oid_helper.php' => 'OID Helper Module'
];

foreach ($modules as $file => $name) {
    if (file_exists($file)) {
        echo "<div style='color: green;'>✓ $name ($file)</div>";
    } else {
        echo "<div style='color: red;'>✗ $name ($file) - Missing</div>";
    }
}

echo "<h3>8. Next Steps</h3>";
echo "<div><ol>";
echo "<li><a href='create_snmp_graph_table.php'>Create SNMP tables</a> (if not exists)</li>";
echo "<li><a href='modules/snmp_graph_poll.php'>Poll SNMP data</a> to collect metrics</li>";
echo "<li><a href='modules/snmp_graph.php'>Test SNMP graphing</a> with collected data</li>";
echo "<li><a href='modules/interface_monitoring.php'>Test interface monitoring</a></li>";
echo "<li><a href='modules/queue_monitoring.php'>Test queue monitoring</a></li>";
echo "</ol></div>";

echo "<h3>9. Quick Links</h3>";
echo "<div><a href='admin_menu.php' class='btn btn-primary'>Back to Admin Menu</a></div>";
echo "<div><a href='index.php' class='btn btn-secondary'>Back to Main Menu</a></div>";
?> 