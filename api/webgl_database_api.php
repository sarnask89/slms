<?php
/**
 * WebGL Database API
 * Clean API endpoints for WebGL database integration
 */

// Prevent direct access without API call
if (!isset($_GET['action']) && !isset($_POST['action'])) {
    http_response_code(403);
    exit('Direct access not allowed');
}

require_once '../config.php';

class WebGLDatabaseAPI {
    private $pdo;
    
    public function __construct() {
        $this->pdo = get_pdo();
    }
    
    /**
     * Get network data for WebGL visualization
     */
    public function getNetworkData() {
        try {
            // Get devices with 3D positions
            $stmt = $this->pdo->prepare("
                SELECT 
                    id, name, type, ip_address, status, 
                    COALESCE(position_x, 0) as position_x,
                    COALESCE(position_y, 0) as position_y,
                    COALESCE(position_z, 0) as position_z,
                    mac_address, model, vendor, location, description,
                    created_at, last_seen
                FROM devices 
                ORDER BY type, name
            ");
            $stmt->execute();
            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get network connections
            $stmt = $this->pdo->prepare("
                SELECT 
                    nc.id, nc.from_device_id, nc.to_device_id,
                    nc.connection_type, nc.bandwidth, nc.status,
                    d1.name as from_device_name, d1.type as from_device_type,
                    d2.name as to_device_name, d2.type as to_device_type
                FROM network_connections nc
                JOIN devices d1 ON nc.from_device_id = d1.id
                JOIN devices d2 ON nc.to_device_id = d2.id
                ORDER BY nc.from_device_id, nc.to_device_id
            ");
            $stmt->execute();
            $connections = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get WebGL settings
            $stmt = $this->pdo->prepare("
                SELECT setting_name, setting_value 
                FROM webgl_settings 
                ORDER BY setting_name
            ");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            return [
                'success' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'devices' => $devices,
                'connections' => $connections,
                'settings' => $settings,
                'total_devices' => count($devices),
                'total_connections' => count($connections)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'devices' => [],
                'connections' => [],
                'settings' => []
            ];
        }
    }
    
    /**
     * Setup WebGL database schema
     */
    public function setupDatabase() {
        try {
            // Add 3D position columns to devices table if they don't exist
            $this->pdo->exec("
                ALTER TABLE devices 
                ADD COLUMN IF NOT EXISTS position_x DECIMAL(10,2) DEFAULT 0
            ");
            
            $this->pdo->exec("
                ALTER TABLE devices 
                ADD COLUMN IF NOT EXISTS position_y DECIMAL(10,2) DEFAULT 0
            ");
            
            $this->pdo->exec("
                ALTER TABLE devices 
                ADD COLUMN IF NOT EXISTS position_z DECIMAL(10,2) DEFAULT 0
            ");
            
            $this->pdo->exec("
                ALTER TABLE devices 
                ADD COLUMN IF NOT EXISTS webgl_visible BOOLEAN DEFAULT TRUE
            ");
            
            // Create WebGL settings table
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS webgl_settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_name VARCHAR(100) UNIQUE,
                    setting_value TEXT,
                    user_id INTEGER,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id)
                )
            ");
            
            // Insert default WebGL settings
            $defaultSettings = [
                'background_color' => '0x1a1a1a',
                'auto_refresh_interval' => '10',
                'device_colors_router' => '0x00ff00',
                'device_colors_switch' => '0x0088ff',
                'device_colors_server' => '0xff0088',
                'device_colors_client' => '0xff8800',
                'show_connections' => 'true',
                'show_labels' => 'true',
                'camera_distance' => '50',
                'animation_speed' => '1.0'
            ];
            
            foreach ($defaultSettings as $name => $value) {
                $stmt = $this->pdo->prepare("
                    INSERT IGNORE INTO webgl_settings (setting_name, setting_value)
                    VALUES (?, ?)
                ");
                $stmt->execute([$name, $value]);
            }
            
            return ['success' => true, 'message' => 'Database schema setup completed'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Sync data changes from WebGL to server database
     */
    public function syncData($changes) {
        try {
            $synced = 0;
            $errors = [];
            
            foreach ($changes as $change) {
                switch ($change['type']) {
                    case 'device_position_update':
                        $success = $this->updateDevicePosition(
                            $change['device_id'],
                            $change['position_x'],
                            $change['position_y'],
                            $change['position_z']
                        );
                        if ($success) $synced++;
                        else $errors[] = "Failed to update device position: " . $change['device_id'];
                        break;
                        
                    case 'device_status_update':
                        $success = $this->updateDeviceStatus(
                            $change['device_id'],
                            $change['status']
                        );
                        if ($success) $synced++;
                        else $errors[] = "Failed to update device status: " . $change['device_id'];
                        break;
                        
                    case 'setting_update':
                        $success = $this->updateWebGLSetting(
                            $change['setting_name'],
                            $change['setting_value']
                        );
                        if ($success) $synced++;
                        else $errors[] = "Failed to update setting: " . $change['setting_name'];
                        break;
                }
            }
            
            return [
                'success' => true,
                'synced' => $synced,
                'errors' => $errors,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'synced' => 0,
                'errors' => [$e->getMessage()]
            ];
        }
    }
    
    /**
     * Update device position in database
     */
    private function updateDevicePosition($deviceId, $x, $y, $z) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE devices 
                SET position_x = ?, position_y = ?, position_z = ?
                WHERE id = ?
            ");
            return $stmt->execute([$x, $y, $z, $deviceId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Update device status in database
     */
    private function updateDeviceStatus($deviceId, $status) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE devices 
                SET status = ?, last_seen = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$status, $deviceId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Update WebGL setting in database
     */
    private function updateWebGLSetting($settingName, $settingValue) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO webgl_settings (setting_name, setting_value, updated_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value),
                updated_at = NOW()
            ");
            return $stmt->execute([$settingName, $settingValue]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize API
$api = new WebGLDatabaseAPI();

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'network_data';
    
    switch ($action) {
        case 'network_data':
            echo json_encode($api->getNetworkData());
            break;
            
        case 'setup_database':
            echo json_encode($api->setupDatabase());
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'sync_data':
            $changes = $input['changes'] ?? [];
            echo json_encode($api->syncData($changes));
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
}
?> 