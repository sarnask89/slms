<?php
/**
 * WebGL Database Setup Script
 * Creates necessary tables and fields for WebGL network visualization
 */

require_once __DIR__ . '/config.php';

class WebGLDatabaseSetup {
    private $pdo;
    private $errors = [];
    private $success = [];
    
    public function __construct() {
        try {
            $this->pdo = get_pdo();
        } catch (Exception $e) {
            $this->errors[] = "Database connection failed: " . $e->getMessage();
        }
    }
    
    public function setup() {
        echo "<h2>WebGL Database Setup</h2>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>\n";
        
        if (!empty($this->errors)) {
            echo "<div style='color: red;'>❌ Setup failed due to database connection issues.</div>\n";
            return false;
        }
        
        $this->addDevicePositionFields();
        $this->createNetworkConnectionsTable();
        $this->createWebGLSettingsTable();
        $this->insertDefaultSettings();
        $this->createSampleData();
        
        $this->displayResults();
        
        echo "</div>\n";
        return empty($this->errors);
    }
    
    private function addDevicePositionFields() {
        try {
            // Check if position fields already exist
            $stmt = $this->pdo->prepare("
                SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'devices' 
                AND COLUMN_NAME IN ('position_x', 'position_y', 'position_z', 'device_type')
            ");
            $stmt->execute();
            $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $columnsToAdd = [];
            
            if (!in_array('position_x', $existingColumns)) {
                $columnsToAdd[] = "ADD COLUMN position_x DECIMAL(10,2) DEFAULT 0";
            }
            if (!in_array('position_y', $existingColumns)) {
                $columnsToAdd[] = "ADD COLUMN position_y DECIMAL(10,2) DEFAULT 0";
            }
            if (!in_array('position_z', $existingColumns)) {
                $columnsToAdd[] = "ADD COLUMN position_z DECIMAL(10,2) DEFAULT 0";
            }
            // Note: device_type column will be mapped to existing 'type' column
            // The existing 'type' column already supports router, switch, server, etc.
            
            if (!empty($columnsToAdd)) {
                $sql = "ALTER TABLE devices " . implode(", ", $columnsToAdd);
                $this->pdo->exec($sql);
                $this->success[] = "Added position and device type fields to devices table";
            } else {
                $this->success[] = "Device position fields already exist";
            }
            
        } catch (PDOException $e) {
            $this->errors[] = "Failed to add device position fields: " . $e->getMessage();
        }
    }
    
    private function createNetworkConnectionsTable() {
        try {
            // Check if table exists
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'network_connections'
            ");
            $stmt->execute();
            
            if ($stmt->fetchColumn() == 0) {
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
                
                $this->pdo->exec($sql);
                $this->success[] = "Created network_connections table";
            } else {
                $this->success[] = "Network connections table already exists";
            }
            
        } catch (PDOException $e) {
            $this->errors[] = "Failed to create network_connections table: " . $e->getMessage();
        }
    }
    
    private function createWebGLSettingsTable() {
        try {
            // Check if table exists
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'webgl_settings'
            ");
            $stmt->execute();
            
            if ($stmt->fetchColumn() == 0) {
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
                
                $this->pdo->exec($sql);
                $this->success[] = "Created webgl_settings table";
            } else {
                $this->success[] = "WebGL settings table already exists";
            }
            
        } catch (PDOException $e) {
            $this->errors[] = "Failed to create webgl_settings table: " . $e->getMessage();
        }
    }
    
    private function insertDefaultSettings() {
        try {
            $settings = [
                ['background_color', '0x1a1a1a', '3D scene background color'],
                ['auto_refresh_interval', '10', 'Auto refresh interval in seconds'],
                ['show_traffic_particles', 'true', 'Show animated traffic particles'],
                ['device_colors_router', '0x00ff00', 'Router device color'],
                ['device_colors_switch', '0x0088ff', 'Switch device color'],
                ['device_colors_server', '0xff0088', 'Server device color'],
                ['device_colors_client', '0xff8800', 'Client device color'],
                ['camera_speed', '0.1', 'Camera movement speed'],
                ['particle_count', '50', 'Maximum number of traffic particles'],
                ['lod_distance_high', '100', 'High detail distance threshold'],
                ['lod_distance_medium', '200', 'Medium detail distance threshold'],
                ['lod_distance_low', '500', 'Low detail distance threshold']
            ];
            
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO webgl_settings 
                (setting_key, setting_value, description) 
                VALUES (?, ?, ?)
            ");
            
            $inserted = 0;
            foreach ($settings as $setting) {
                $stmt->execute($setting);
                if ($stmt->rowCount() > 0) {
                    $inserted++;
                }
            }
            
            if ($inserted > 0) {
                $this->success[] = "Inserted $inserted default WebGL settings";
            } else {
                $this->success[] = "All default WebGL settings already exist";
            }
            
        } catch (PDOException $e) {
            $this->errors[] = "Failed to insert default settings: " . $e->getMessage();
        }
    }
    
    private function createSampleData() {
        try {
            // Check if we have any devices
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM devices");
            $stmt->execute();
            $deviceCount = $stmt->fetchColumn();
            
            if ($deviceCount == 0) {
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
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO devices (name, type, position_x, position_y, position_z, ip_address, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'online')
                ");
                
                $ipBase = 192168001;
                foreach ($sampleDevices as $device) {
                    $ip = "192.168.0." . ($ipBase % 255);
                    $stmt->execute([$device[0], $device[1], $device[2], $device[3], $device[4], $ip]);
                    $ipBase++;
                }
                
                $this->success[] = "Created " . count($sampleDevices) . " sample devices";
                
                // Create sample connections
                $this->createSampleConnections();
            } else {
                $this->success[] = "Devices already exist, skipping sample data creation";
            }
            
        } catch (PDOException $e) {
            $this->errors[] = "Failed to create sample data: " . $e->getMessage();
        }
    }
    
    private function createSampleConnections() {
        try {
            // Get device IDs
            $stmt = $this->pdo->prepare("SELECT id, type FROM devices ORDER BY id");
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
            
            $stmt = $this->pdo->prepare("
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
            
            if ($inserted > 0) {
                $this->success[] = "Created $inserted sample network connections";
            }
            
        } catch (PDOException $e) {
            $this->errors[] = "Failed to create sample connections: " . $e->getMessage();
        }
    }
    
    private function displayResults() {
        echo "<h3>Setup Results:</h3>\n";
        
        if (!empty($this->success)) {
            echo "<div style='color: green; margin-bottom: 10px;'>\n";
            echo "<strong>✅ Successful Operations:</strong><br>\n";
            foreach ($this->success as $message) {
                echo "• $message<br>\n";
            }
            echo "</div>\n";
        }
        
        if (!empty($this->errors)) {
            echo "<div style='color: red; margin-bottom: 10px;'>\n";
            echo "<strong>❌ Errors:</strong><br>\n";
            foreach ($this->errors as $error) {
                echo "• $error<br>\n";
            }
            echo "</div>\n";
        }
        
        if (empty($this->errors)) {
            echo "<div style='color: green; font-weight: bold;'>\n";
            echo "🎉 WebGL database setup completed successfully!\n";
            echo "</div>\n";
            
            echo "<div style='margin-top: 20px;'>\n";
            echo "<h4>Next Steps:</h4>\n";
            echo "<ol>\n";
            echo "<li>Test the WebGL integration: <a href='test_webgl_integration.php'>test_webgl_integration.php</a></li>\n";
            echo "<li>View the 3D demo: <a href='webgl_demo.php'>webgl_demo.php</a></li>\n";
            echo "<li>Check the API: <a href='modules/webgl_network_viewer.php?action=network_data'>WebGL API</a></li>\n";
            echo "</ol>\n";
            echo "</div>\n";
        }
    }
}

// Run setup
if (php_sapi_name() === 'cli') {
    $setup = new WebGLDatabaseSetup();
    $setup->setup();
} else {
    // Web interface
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>WebGL Database Setup - AI Service Network Management System</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
        <link href="assets/style.css" rel="stylesheet">
    </head>
    <body class="dark-theme">
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card dark-card">
                        <div class="card-header">
                            <h2><i class="bi bi-database"></i> WebGL Database Setup</h2>
                        </div>
                        <div class="card-body">
                            <?php
                            $setup = new WebGLDatabaseSetup();
                            $setup->setup();
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?> 