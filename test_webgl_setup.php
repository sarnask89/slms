<?php
/**
 * WebGL Setup Test Script
 * Tests the database setup and verifies it works correctly
 */

require_once __DIR__ . '/config.php';

echo "<h2>WebGL Setup Test</h2>\n";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>\n";

try {
    $pdo = get_pdo();
    echo "✅ Database connection successful<br>\n";
    
    // Test 1: Check if devices table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'devices'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "✅ Devices table exists<br>\n";
    } else {
        echo "❌ Devices table does not exist<br>\n";
        exit;
    }
    
    // Test 2: Check devices table structure
    $stmt = $pdo->prepare("DESCRIBE devices");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Devices table columns: " . implode(', ', $columns) . "<br>\n";
    
    // Test 3: Check if position columns exist
    $stmt = $pdo->prepare("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'devices' 
        AND COLUMN_NAME IN ('position_x', 'position_y', 'position_z')
    ");
    $stmt->execute();
    $positionColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($positionColumns) == 3) {
        echo "✅ Position columns exist: " . implode(', ', $positionColumns) . "<br>\n";
    } else {
        echo "⚠️ Position columns missing. Adding them...<br>\n";
        
        // Add position columns
        $pdo->exec("ALTER TABLE devices ADD COLUMN position_x DECIMAL(10,2) DEFAULT 0");
        $pdo->exec("ALTER TABLE devices ADD COLUMN position_y DECIMAL(10,2) DEFAULT 0");
        $pdo->exec("ALTER TABLE devices ADD COLUMN position_z DECIMAL(10,2) DEFAULT 0");
        echo "✅ Position columns added<br>\n";
    }
    
    // Test 4: Check if network_connections table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'network_connections'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "✅ Network connections table exists<br>\n";
    } else {
        echo "⚠️ Network connections table missing. Creating it...<br>\n";
        
        // Create network_connections table
        $sql = "
        CREATE TABLE network_connections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            from_device_id INT NOT NULL,
            to_device_id INT NOT NULL,
            connection_type ENUM('ethernet', 'fiber', 'wireless') DEFAULT 'ethernet',
            bandwidth INT DEFAULT 100,
            status ENUM('active', 'inactive', 'error') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (from_device_id) REFERENCES devices(id) ON DELETE CASCADE,
            FOREIGN KEY (to_device_id) REFERENCES devices(id) ON DELETE CASCADE,
            UNIQUE KEY unique_connection (from_device_id, to_device_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($sql);
        echo "✅ Network connections table created<br>\n";
    }
    
    // Test 5: Check if webgl_settings table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'webgl_settings'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "✅ WebGL settings table exists<br>\n";
    } else {
        echo "⚠️ WebGL settings table missing. Creating it...<br>\n";
        
        // Create webgl_settings table
        $sql = "
        CREATE TABLE webgl_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($sql);
        echo "✅ WebGL settings table created<br>\n";
    }
    
    // Test 6: Insert sample data
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM devices");
    $stmt->execute();
    $deviceCount = $stmt->fetchColumn();
    
    if ($deviceCount == 0) {
        echo "⚠️ No devices found. Creating sample data...<br>\n";
        
        // Create sample devices
        $sampleDevices = [
            ['Main Router', 'router', 0, 0, 10],
            ['Core Switch 1', 'switch', -15, 0, 5],
            ['Core Switch 2', 'switch', 15, 0, 5],
            ['Web Server', 'server', -10, 15, 0],
            ['Database Server', 'server', 10, 15, 0],
            ['Client PC 1', 'other', -20, -10, 0],
            ['Client PC 2', 'other', 20, -10, 0],
            ['Network Printer', 'other', 0, -20, 0]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO devices (name, type, position_x, position_y, position_z, ip_address, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'online')
        ");
        
        $ipBase = 192168001;
        foreach ($sampleDevices as $device) {
            $ip = "192.168.0." . ($ipBase % 255);
            $stmt->execute([$device[0], $device[1], $device[2], $device[3], $device[4], $ip]);
            $ipBase++;
        }
        
        echo "✅ Created " . count($sampleDevices) . " sample devices<br>\n";
        
        // Create sample connections
        $stmt = $pdo->prepare("SELECT id, type FROM devices ORDER BY id");
        $stmt->execute();
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
                    $routers = array_filter($devices, fn($d) => $d['type'] === 'router');
            $switches = array_filter($devices, fn($d) => $d['type'] === 'switch');
            $clients = array_filter($devices, fn($d) => $d['type'] === 'other');
            $servers = array_filter($devices, fn($d) => $d['type'] === 'server');
        
        $connections = [];
        
        // Connect routers to switches
        foreach ($routers as $router) {
            foreach ($switches as $switch) {
                $connections[] = [$router['id'], $switch['id'], 1000];
            }
        }
        
        // Connect switches to clients and servers
        foreach ($switches as $switch) {
            foreach (array_merge($clients, $servers) as $device) {
                $connections[] = [$switch['id'], $device['id'], 100];
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO network_connections 
            (from_device_id, to_device_id, bandwidth) 
            VALUES (?, ?, ?)
        ");
        
        $inserted = 0;
        foreach ($connections as $connection) {
            $stmt->execute($connection);
            if ($stmt->rowCount() > 0) {
                $inserted++;
            }
        }
        
        echo "✅ Created $inserted sample network connections<br>\n";
        
    } else {
        echo "✅ Found $deviceCount existing devices<br>\n";
    }
    
    // Test 7: Test WebGL API
    echo "<br><strong>Testing WebGL API:</strong><br>\n";
    
    // Test network data endpoint
    $stmt = $pdo->prepare("
        SELECT 
            id, name, type, ip_address, status, 
            created_at, last_seen
        FROM devices 
        ORDER BY type, name
    ");
    $stmt->execute();
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($devices) > 0) {
        echo "✅ WebGL API test successful - found " . count($devices) . " devices<br>\n";
        
        // Show sample device data
        echo "<br><strong>Sample Device Data:</strong><br>\n";
        foreach (array_slice($devices, 0, 3) as $device) {
            echo "• {$device['name']} ({$device['type']}) - {$device['ip_address']}<br>\n";
        }
    } else {
        echo "❌ No devices found for WebGL API<br>\n";
    }
    
    echo "<br><div style='color: green; font-weight: bold;'>🎉 WebGL setup test completed successfully!</div>\n";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>\n";
}

echo "</div>\n";

echo "<div style='margin-top: 20px;'>\n";
echo "<h3>Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li><a href='test_webgl_integration.php'>Test WebGL Integration</a></li>\n";
echo "<li><a href='webgl_demo.php'>View 3D Demo</a></li>\n";
echo "<li><a href='modules/webgl_network_viewer.php?action=network_data'>Check WebGL API</a></li>\n";
echo "</ol>\n";
echo "</div>\n";
?> 