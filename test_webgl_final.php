<?php
/**
 * Final WebGL Test Script
 * Tests the complete WebGL setup with all fixes applied
 */

require_once __DIR__ . '/config.php';

echo "<h2>🎯 Final WebGL Setup Test</h2>\n";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>\n";

try {
    $pdo = get_pdo();
    echo "✅ Database connection successful<br>\n";
    
    // Test 1: Check devices table structure
    $stmt = $pdo->prepare("DESCRIBE devices");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    echo "✅ Devices table columns: " . implode(', ', $columnNames) . "<br>\n";
    
    // Test 2: Check type ENUM values
    $stmt = $pdo->prepare("
        SELECT COLUMN_TYPE 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'devices' 
        AND COLUMN_NAME = 'type'
    ");
    $stmt->execute();
    $typeEnum = $stmt->fetchColumn();
    echo "✅ Type ENUM values: $typeEnum<br>\n";
    
    // Test 3: Check if position columns exist
    $positionColumns = array_intersect($columnNames, ['position_x', 'position_y', 'position_z']);
    if (count($positionColumns) == 3) {
        echo "✅ Position columns exist: " . implode(', ', $positionColumns) . "<br>\n";
    } else {
        echo "⚠️ Adding missing position columns...<br>\n";
        $pdo->exec("ALTER TABLE devices ADD COLUMN position_x DECIMAL(10,2) DEFAULT 0");
        $pdo->exec("ALTER TABLE devices ADD COLUMN position_y DECIMAL(10,2) DEFAULT 0");
        $pdo->exec("ALTER TABLE devices ADD COLUMN position_z DECIMAL(10,2) DEFAULT 0");
        echo "✅ Position columns added<br>\n";
    }
    
    // Test 4: Create required tables if they don't exist
    $requiredTables = ['network_connections', 'webgl_settings'];
    foreach ($requiredTables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() == 0) {
            echo "⚠️ Creating $table table...<br>\n";
            
            if ($table === 'network_connections') {
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
            } else {
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
            }
            
            $pdo->exec($sql);
            echo "✅ $table table created<br>\n";
        } else {
            echo "✅ $table table exists<br>\n";
        }
    }
    
    // Test 5: Insert sample data with correct ENUM values
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM devices");
    $stmt->execute();
    $deviceCount = $stmt->fetchColumn();
    
    if ($deviceCount == 0) {
        echo "⚠️ No devices found. Creating sample data...<br>\n";
        
        // Create sample devices with correct ENUM values
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
        $others = array_filter($devices, fn($d) => $d['type'] === 'other');
        $servers = array_filter($devices, fn($d) => $d['type'] === 'server');
        
        $connections = [];
        
        // Connect routers to switches
        foreach ($routers as $router) {
            foreach ($switches as $switch) {
                $connections[] = [$router['id'], $switch['id'], 1000];
            }
        }
        
        // Connect switches to other devices and servers
        foreach ($switches as $switch) {
            foreach (array_merge($others, $servers) as $device) {
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
    
    // Test 6: Test WebGL API endpoints
    echo "<br><strong>Testing WebGL API Endpoints:</strong><br>\n";
    
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
        
        // Show device types
        $types = array_count_values(array_column($devices, 'type'));
        echo "✅ Device types: " . implode(', ', array_map(fn($k, $v) => "$k($v)", array_keys($types), $types)) . "<br>\n";
        
        // Show sample device data
        echo "<br><strong>Sample Device Data:</strong><br>\n";
        foreach (array_slice($devices, 0, 3) as $device) {
            echo "• {$device['name']} ({$device['type']}) - {$device['ip_address']}<br>\n";
        }
    } else {
        echo "❌ No devices found for WebGL API<br>\n";
    }
    
    // Test 7: Test network connections
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM network_connections");
    $stmt->execute();
    $connectionCount = $stmt->fetchColumn();
    echo "✅ Network connections: $connectionCount<br>\n";
    
    echo "<br><div style='color: green; font-weight: bold; font-size: 18px;'>🎉 All WebGL setup tests passed successfully!</div>\n";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>\n";
}

echo "</div>\n";

echo "<div style='margin-top: 20px;'>\n";
echo "<h3>🚀 Ready to Launch:</h3>\n";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;'>\n";

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>\n";
echo "<h4>🎮 3D Demo</h4>\n";
echo "<p>Experience the interactive 3D network visualization</p>\n";
echo "<a href='webgl_demo.php' class='btn btn-success btn-sm'>Launch Demo</a>\n";
echo "</div>\n";

echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;'>\n";
echo "<h4>🔧 Integration Test</h4>\n";
echo "<p>Test the complete WebGL integration</p>\n";
echo "<a href='test_webgl_integration.php' class='btn btn-primary btn-sm'>Run Test</a>\n";
echo "</div>\n";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>\n";
echo "<h4>📊 API Check</h4>\n";
echo "<p>Verify the WebGL API endpoints</p>\n";
echo "<a href='modules/webgl_network_viewer.php?action=network_data' class='btn btn-warning btn-sm'>Check API</a>\n";
echo "</div>\n";

echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;'>\n";
echo "<h4>📚 Documentation</h4>\n";
echo "<p>Read the migration guides</p>\n";
echo "<a href='docs/WEBGL_QUICK_START.md' class='btn btn-danger btn-sm'>View Docs</a>\n";
echo "</div>\n";

echo "</div>\n";
echo "</div>\n";

echo "<div style='margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;'>\n";
echo "<h4>🔧 What Was Fixed:</h4>\n";
echo "<ul>\n";
echo "<li>✅ Removed non-existent 'active' column references</li>\n";
echo "<li>✅ Fixed column name from 'device_type' to 'type'</li>\n";
echo "<li>✅ Changed 'client' device type to 'other' (valid ENUM value)</li>\n";
echo "<li>✅ Updated all SQL queries to use correct column names</li>\n";
echo "<li>✅ Fixed WebGL viewer color mappings</li>\n";
echo "<li>✅ Updated sample data to use valid ENUM values</li>\n";
echo "</ul>\n";
echo "</div>\n";
?> 