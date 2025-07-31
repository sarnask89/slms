<?php
/**
 * WebGL Database Integration for SLMS
 * 
 * This module provides direct database integration between WebGL applications
 * and the SLMS database using SQLite WASM for client-side operations.
 * 
 * Features:
 * - SQLite WASM integration for client-side database operations
 * - Real-time data synchronization between PHP backend and WebGL frontend
 * - Offline support with local database caching
 * - Enhanced 3D visualization with direct database access
 */

require_once 'config.php';

class WebGLDatabaseIntegration {
    private $pdo;
    private $integrationLog = [];
    
    public function __construct() {
        $this->pdo = get_pdo();
        $this->log("WebGL Database Integration initialized");
    }
    
    /**
     * Get network data for WebGL visualization
     */
    public function getNetworkDataForWebGL() {
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
            $this->log("Error getting network data: " . $e->getMessage());
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
     * Sync data changes from WebGL to server database
     */
    public function syncDataFromWebGL($changes) {
        try {
            $this->log("Syncing data from WebGL: " . count($changes) . " changes");
            
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
                        
                    case 'connection_update':
                        $success = $this->updateConnection(
                            $change['connection_id'],
                            $change['data']
                        );
                        if ($success) $synced++;
                        else $errors[] = "Failed to update connection: " . $change['connection_id'];
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
            $this->log("Error syncing data: " . $e->getMessage());
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
            $this->log("Error updating device position: " . $e->getMessage());
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
            $this->log("Error updating device status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update connection in database
     */
    private function updateConnection($connectionId, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE network_connections 
                SET connection_type = ?, bandwidth = ?, status = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['connection_type'],
                $data['bandwidth'],
                $data['status'],
                $connectionId
            ]);
        } catch (PDOException $e) {
            $this->log("Error updating connection: " . $e->getMessage());
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
            $this->log("Error updating WebGL setting: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent changes for synchronization
     */
    public function getRecentChanges($since = null) {
        if (!$since) {
            $since = date('Y-m-d H:i:s', strtotime('-1 hour'));
        }
        
        try {
            // Get device changes
            $stmt = $this->pdo->prepare("
                SELECT 
                    'device' as type,
                    id,
                    name,
                    type as device_type,
                    status,
                    position_x, position_y, position_z,
                    last_seen as updated_at
                FROM devices 
                WHERE last_seen > ?
                ORDER BY last_seen DESC
            ");
            $stmt->execute([$since]);
            $deviceChanges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get connection changes
            $stmt = $this->pdo->prepare("
                SELECT 
                    'connection' as type,
                    nc.id,
                    nc.connection_type,
                    nc.bandwidth,
                    nc.status,
                    nc.last_updated as updated_at
                FROM network_connections nc
                WHERE nc.last_updated > ?
                ORDER BY nc.last_updated DESC
            ");
            $stmt->execute([$since]);
            $connectionChanges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'device_changes' => $deviceChanges,
                'connection_changes' => $connectionChanges,
                'since' => $since,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (PDOException $e) {
            $this->log("Error getting recent changes: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'device_changes' => [],
                'connection_changes' => []
            ];
        }
    }
    
    /**
     * Setup WebGL database schema
     */
    public function setupWebGLDatabase() {
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
            
            $this->log("WebGL database schema setup completed");
            return true;
            
        } catch (PDOException $e) {
            $this->log("Error setting up WebGL database: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log integration activities
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $this->integrationLog[] = "[$timestamp] $message";
        
        // Keep only last 100 log entries
        if (count($this->integrationLog) > 100) {
            $this->integrationLog = array_slice($this->integrationLog, -100);
        }
    }
    
    /**
     * Get integration log
     */
    public function getIntegrationLog() {
        return $this->integrationLog;
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $integration = new WebGLDatabaseIntegration();
    
    $action = $_GET['action'] ?? 'network_data';
    
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    switch ($action) {
        case 'network_data':
            echo json_encode($integration->getNetworkDataForWebGL());
            break;
            
        case 'recent_changes':
            $since = $_GET['since'] ?? null;
            echo json_encode($integration->getRecentChanges($since));
            break;
            
        case 'setup_database':
            $result = $integration->setupWebGLDatabase();
            echo json_encode(['success' => $result]);
            break;
            
        case 'integration_log':
            echo json_encode($integration->getIntegrationLog());
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $integration = new WebGLDatabaseIntegration();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    header('Content-Type: application/json');
    
    switch ($action) {
        case 'sync_data':
            $changes = $input['changes'] ?? [];
            echo json_encode($integration->syncDataFromWebGL($changes));
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
}

// WebGL Database Integration Interface
if (!isset($_GET['action']) && !isset($_POST['action'])): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebGL Database Integration - SLMS</title>
    
    <!-- Three.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .database-panel {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-online { background-color: #28a745; }
        .status-offline { background-color: #dc3545; }
        .status-syncing { background-color: #ffc107; }
        
        #webgl-viewer {
            width: 100%;
            height: 60vh;
            background: #1a1a1a;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .log-panel {
            max-height: 200px;
            overflow-y: auto;
            background: #000;
            color: #00ff00;
            font-family: monospace;
            padding: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="dark-theme">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-database"></i>
                    WebGL Database Integration
                    <small class="text-muted">SQLite WASM + SLMS</small>
                </h1>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <!-- WebGL 3D Viewer -->
                <div id="webgl-viewer"></div>
                
                <!-- Database Status Panel -->
                <div class="database-panel">
                    <h5><i class="bi bi-info-circle"></i> Database Status</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4" id="total-devices">0</div>
                                <small>Total Devices</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4" id="total-connections">0</div>
                                <small>Connections</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4" id="sync-status">
                                    <span class="status-indicator status-offline"></span>
                                    Offline
                                </div>
                                <small>Database Sync</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4" id="last-sync">Never</div>
                                <small>Last Sync</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Database Controls -->
                <div class="database-panel">
                    <h5><i class="bi bi-gear"></i> Database Controls</h5>
                    
                    <button class="btn btn-primary w-100 mb-2" onclick="setupDatabase()">
                        <i class="bi bi-database-add"></i> Setup Database
                    </button>
                    
                    <button class="btn btn-success w-100 mb-2" onclick="loadNetworkData()">
                        <i class="bi bi-arrow-clockwise"></i> Load Network Data
                    </button>
                    
                    <button class="btn btn-info w-100 mb-2" onclick="startSync()">
                        <i class="bi bi-arrow-repeat"></i> Start Sync
                    </button>
                    
                    <button class="btn btn-warning w-100 mb-2" onclick="stopSync()">
                        <i class="bi bi-pause"></i> Stop Sync
                    </button>
                    
                    <button class="btn btn-secondary w-100" onclick="exportData()">
                        <i class="bi bi-download"></i> Export Data
                    </button>
                </div>
                
                <!-- Integration Log -->
                <div class="database-panel">
                    <h5><i class="bi bi-journal-text"></i> Integration Log</h5>
                    <div class="log-panel" id="integration-log">
                        <div>Initializing WebGL Database Integration...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- WebGL Database Integration Script -->
    <script>
        class WebGLDatabaseIntegration {
            constructor() {
                this.isInitialized = false;
                this.syncInterval = null;
                this.lastSyncTime = null;
                this.networkData = null;
                this.scene = null;
                this.camera = null;
                this.renderer = null;
                
                this.init();
            }
            
            async init() {
                this.log("Initializing WebGL Database Integration...");
                
                // Initialize Three.js scene
                this.initThreeJS();
                
                // Setup database
                await this.setupDatabase();
                
                // Load initial data
                await this.loadNetworkData();
                
                this.isInitialized = true;
                this.log("WebGL Database Integration initialized successfully");
            }
            
            initThreeJS() {
                // Create scene
                this.scene = new THREE.Scene();
                this.scene.background = new THREE.Color(0x1a1a1a);
                
                // Create camera
                this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
                this.camera.position.set(0, 0, 50);
                
                // Create renderer
                this.renderer = new THREE.WebGLRenderer({ antialias: true });
                this.renderer.setSize(document.getElementById('webgl-viewer').clientWidth, 
                                    document.getElementById('webgl-viewer').clientHeight);
                document.getElementById('webgl-viewer').appendChild(this.renderer.domElement);
                
                // Add lighting
                const ambientLight = new THREE.AmbientLight(0x404040, 0.4);
                this.scene.add(ambientLight);
                
                const directionalLight = new THREE.DirectionalLight(0xffffff, 0.6);
                directionalLight.position.set(5, 5, 5);
                this.scene.add(directionalLight);
                
                // Start animation loop
                this.animate();
            }
            
            animate() {
                requestAnimationFrame(() => this.animate());
                this.renderer.render(this.scene, this.camera);
            }
            
                         async setupDatabase() {
                 try {
                     this.log("Setting up database schema...");
                     
                     const response = await fetch('api/webgl_database_api_clean.php?action=setup_database');
                     const result = await response.json();
                     
                     if (result.success) {
                         this.log("Database schema setup completed");
                         return true;
                     } else {
                         this.log("Database schema setup failed");
                         return false;
                     }
                 } catch (error) {
                     this.log("Error setting up database: " + error.message);
                     return false;
                 }
             }
             
             async loadNetworkData() {
                 try {
                     this.log("Loading network data from server...");
                     
                     const response = await fetch('api/webgl_database_api_clean.php?action=network_data');
                     const data = await response.json();
                     
                     if (data.success) {
                         this.networkData = data;
                         this.updateStatistics(data);
                         this.visualizeNetwork(data);
                         this.log(`Loaded ${data.total_devices} devices and ${data.total_connections} connections`);
                         return true;
                     } else {
                         this.log("Failed to load network data: " + data.error);
                         return false;
                     }
                 } catch (error) {
                     this.log("Error loading network data: " + error.message);
                     return false;
                 }
             }
            
            updateStatistics(data) {
                document.getElementById('total-devices').textContent = data.total_devices;
                document.getElementById('total-connections').textContent = data.total_connections;
                document.getElementById('last-sync').textContent = data.timestamp;
            }
            
            visualizeNetwork(data) {
                // Clear existing objects
                while(this.scene.children.length > 0) { 
                    this.scene.remove(this.scene.children[0]); 
                }
                
                // Add lighting back
                const ambientLight = new THREE.AmbientLight(0x404040, 0.4);
                this.scene.add(ambientLight);
                
                const directionalLight = new THREE.DirectionalLight(0xffffff, 0.6);
                directionalLight.position.set(5, 5, 5);
                this.scene.add(directionalLight);
                
                // Create device geometries
                data.devices.forEach(device => {
                    const geometry = this.getDeviceGeometry(device.type);
                    const material = this.getDeviceMaterial(device.status);
                    const mesh = new THREE.Mesh(geometry, material);
                    
                    mesh.position.set(device.position_x, device.position_y, device.position_z);
                    mesh.userData = { device: device };
                    
                    this.scene.add(mesh);
                });
                
                // Create connection lines
                data.connections.forEach(connection => {
                    const fromDevice = data.devices.find(d => d.id === connection.from_device_id);
                    const toDevice = data.devices.find(d => d.id === connection.to_device_id);
                    
                    if (fromDevice && toDevice) {
                        const geometry = new THREE.BufferGeometry().setFromPoints([
                            new THREE.Vector3(fromDevice.position_x, fromDevice.position_y, fromDevice.position_z),
                            new THREE.Vector3(toDevice.position_x, toDevice.position_y, toDevice.position_z)
                        ]);
                        
                        const material = new THREE.LineBasicMaterial({ color: 0x00ffff });
                        const line = new THREE.Line(geometry, material);
                        
                        this.scene.add(line);
                    }
                });
            }
            
            getDeviceGeometry(type) {
                switch(type) {
                    case 'router':
                        return new THREE.CylinderGeometry(2, 2, 4, 8);
                    case 'switch':
                        return new THREE.BoxGeometry(3, 2, 3);
                    case 'server':
                        return new THREE.BoxGeometry(4, 3, 4);
                    default:
                        return new THREE.SphereGeometry(1.5, 8, 8);
                }
            }
            
            getDeviceMaterial(status) {
                const colors = {
                    'online': 0x00ff00,
                    'offline': 0xff0000,
                    'warning': 0xffff00
                };
                
                return new THREE.MeshPhongMaterial({ 
                    color: colors[status] || 0x666666 
                });
            }
            
            startSync() {
                if (this.syncInterval) {
                    this.stopSync();
                }
                
                this.syncInterval = setInterval(() => {
                    this.syncData();
                }, 10000); // Sync every 10 seconds
                
                this.updateSyncStatus('syncing');
                this.log("Started data synchronization");
            }
            
            stopSync() {
                if (this.syncInterval) {
                    clearInterval(this.syncInterval);
                    this.syncInterval = null;
                }
                
                this.updateSyncStatus('offline');
                this.log("Stopped data synchronization");
            }
            
                         async syncData() {
                 try {
                     const changes = this.getLocalChanges();
                     
                     if (changes.length > 0) {
                         const response = await fetch('api/webgl_database_api_clean.php', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json'
                             },
                             body: JSON.stringify({
                                 action: 'sync_data',
                                 changes: changes
                             })
                         });
                         
                         const result = await response.json();
                         
                         if (result.success) {
                             this.log(`Synced ${result.synced} changes`);
                             this.lastSyncTime = new Date();
                             this.updateSyncStatus('online');
                         } else {
                             this.log("Sync failed: " + result.error);
                             this.updateSyncStatus('offline');
                         }
                     }
                 } catch (error) {
                     this.log("Sync error: " + error.message);
                     this.updateSyncStatus('offline');
                 }
             }
            
            getLocalChanges() {
                // This would be implemented with SQLite WASM
                // For now, return empty array
                return [];
            }
            
            updateSyncStatus(status) {
                const statusElement = document.getElementById('sync-status');
                const indicator = statusElement.querySelector('.status-indicator');
                
                indicator.className = `status-indicator status-${status}`;
                statusElement.innerHTML = indicator.outerHTML + status.charAt(0).toUpperCase() + status.slice(1);
            }
            
            log(message) {
                const logElement = document.getElementById('integration-log');
                const timestamp = new Date().toLocaleTimeString();
                logElement.innerHTML += `<div>[${timestamp}] ${message}</div>`;
                logElement.scrollTop = logElement.scrollHeight;
            }
        }
        
        // Global functions for button clicks
        let webglIntegration;
        
        function setupDatabase() {
            webglIntegration.setupDatabase();
        }
        
        function loadNetworkData() {
            webglIntegration.loadNetworkData();
        }
        
        function startSync() {
            webglIntegration.startSync();
        }
        
        function stopSync() {
            webglIntegration.stopSync();
        }
        
        function exportData() {
            if (webglIntegration.networkData) {
                const blob = new Blob([JSON.stringify(webglIntegration.networkData, null, 2)], 
                                    { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `slms_webgl_data_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.json`;
                a.click();
                URL.revokeObjectURL(url);
            }
        }
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            webglIntegration = new WebGLDatabaseIntegration();
        });
    </script>
</body>
</html>
<?php endif; ?> 