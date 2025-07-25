<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/modules/mndp_enhanced.php';

echo "<h2>Enhanced MNDP Test</h2>";

try {
    echo "<h3>Starting MNDP Discovery...</h3>";
    echo "<p>This will scan for Mikrotik devices using the enhanced MNDP implementation.</p>";
    
    $mndp = new MNDPEnhanced();
    $devices = $mndp->discover(10); // 10 second timeout
    
    echo "<h3>Discovery Results</h3>";
    echo "<p>Found " . count($devices) . " Mikrotik devices via MNDP</p>";
    
    if (!empty($devices)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>IP Address</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>MAC Address</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Identity</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Platform</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Version</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Board Name</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd;'>Uptime</th>";
        echo "</tr>";
        
        foreach ($devices as $device) {
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($device['ip']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($device['mac_address']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($device['identity']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($device['platform']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($device['version_info']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($device['board_name']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($device['uptime']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Save to database
        try {
            $pdo = get_pdo();
            foreach ($devices as $device) {
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO discovered_devices 
                    (ip_address, sys_name, sys_descr, method, discovered_at) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $device['ip'],
                    $device['identity'] ?: $device['platform'],
                    "MNDP: {$device['platform']} {$device['version_info']} (MAC: {$device['mac_address']})",
                    'MNDP',
                    $device['discovered_at']
                ]);
            }
            echo "<p style='color: green;'>✅ Devices saved to database successfully!</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ No Mikrotik devices found via MNDP.</p>";
        echo "<p>This could mean:</p>";
        echo "<ul>";
        echo "<li>No Mikrotik devices are broadcasting MNDP packets</li>";
        echo "<li>MNDP is disabled on the devices</li>";
        echo "<li>Network firewall is blocking UDP port 5678</li>";
        echo "<li>You're not on the same network segment as Mikrotik devices</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ MNDP Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>MNDP Protocol Information</h3>";
echo "<ul>";
echo "<li><strong>Protocol:</strong> UDP broadcast on port 5678</li>";
echo "<li><strong>Packet:</strong> 4-byte unsigned int = 0</li>";
echo "<li><strong>Response:</strong> Device information including MAC, identity, platform, version</li>";
echo "<li><strong>Timeout:</strong> 10 seconds for discovery</li>";
echo "</ul>";

echo "<p><a href='modules/mndp_monitor.php'>Go to MNDP Monitor</a> | ";
echo "<a href='modules/discover_snmp_mndp.php'>Go to SNMP/MNDP Discovery</a></p>";
?> 