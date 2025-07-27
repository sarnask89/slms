<?php
/**
 * WebGL Network Viewer Module
 * Integrates Three.js-based 3D network visualization with the AI Service Network Management System
 * 
 * Features:
 * - Network topology data API
 * - Real-time device status updates
 * - WebSocket support for live updates
 * - Integration with existing device management
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers/auth_helper.php';

class WebGLNetworkViewer {
    private $pdo;
    private $networkData = [];
    
    public function __construct() {
        $this->pdo = get_pdo();
        $this->loadNetworkData();
    }
    
    /**
     * Load network topology data from database
     */
    private function loadNetworkData() {
        try {
            // Load devices
            $stmt = $this->pdo->prepare("
                SELECT 
                    id, name, type, ip_address, status, 
                    created_at, last_seen
                FROM devices 
                ORDER BY type, name
            ");
            $stmt->execute();
            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Load network connections (from network_monitoring table)
            $stmt = $this->pdo->prepare("
                SELECT 
                    device_id, interface_name, 
                    bytes_in, bytes_out, status
                FROM network_monitoring 
                ORDER BY device_id
            ");
            $stmt->execute();
            $interfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Transform data for WebGL viewer
            $this->networkData = [
                'devices' => $this->transformDevices($devices),
                'connections' => $this->generateConnections($devices, $interfaces)
            ];
            
        } catch (PDOException $e) {
            error_log("WebGL Network Viewer Error: " . $e->getMessage());
            $this->networkData = ['devices' => [], 'connections' => []];
        }
    }
    
    /**
     * Transform device data for 3D visualization
     */
    private function transformDevices($devices) {
        $transformed = [];
        $positionIndex = 0;
        
        foreach ($devices as $device) {
            // Generate 3D positions in a grid layout
            $x = ($positionIndex % 5) * 15 - 30;
            $y = floor($positionIndex / 5) * 15 - 30;
            $z = 0;
            
            // Adjust position based on device type
            switch ($device['type']) {
                case 'router':
                    $z = 10;
                    break;
                case 'switch':
                    $z = 5;
                    break;
                case 'server':
                    $z = 15;
                    break;
                default:
                    $z = 0;
            }
            
            $transformed[] = [
                'id' => $device['id'],
                'name' => $device['name'],
                'type' => $device['type'],
                'x' => $x,
                'y' => $y,
                'z' => $z,
                'status' => $device['status'] ?? 'online',
                'ip_address' => $device['ip_address'],
                'last_seen' => $device['last_seen']
            ];
            
            $positionIndex++;
        }
        
        return $transformed;
    }
    
    /**
     * Generate network connections between devices
     */
    private function generateConnections($devices, $interfaces) {
        $connections = [];
        $deviceIds = array_column($devices, 'id');
        
        // Create connections based on device hierarchy
        foreach ($devices as $device) {
            if ($device['type'] === 'router') {
                // Connect router to switches
                foreach ($devices as $targetDevice) {
                    if ($targetDevice['type'] === 'switch' && $targetDevice['id'] !== $device['id']) {
                        $connections[] = [
                            'from' => $device['id'],
                            'to' => $targetDevice['id'],
                            'bandwidth' => 1000, // 1Gbps
                            'type' => 'ethernet'
                        ];
                    }
                }
            } elseif ($device['type'] === 'switch') {
                // Connect switch to other devices/servers
                foreach ($devices as $targetDevice) {
                    if (in_array($targetDevice['type'], ['other', 'server']) && $targetDevice['id'] !== $device['id']) {
                        $connections[] = [
                            'from' => $device['id'],
                            'to' => $targetDevice['id'],
                            'bandwidth' => 100, // 100Mbps
                            'type' => 'ethernet'
                        ];
                    }
                }
            }
        }
        
        return $connections;
    }
    
    /**
     * Get network topology data as JSON
     */
    public function getNetworkData() {
        return json_encode($this->networkData, JSON_PRETTY_PRINT);
    }
    
    /**
     * Get real-time device status updates
     */
    public function getDeviceStatusUpdates() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    id, name, status, last_seen,
                    CASE 
                        WHEN last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'online'
                        ELSE 'offline'
                    END as current_status
                FROM devices 
            ");
            $stmt->execute();
            $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'updates' => $updates
            ]);
            
        } catch (PDOException $e) {
            error_log("Device Status Update Error: " . $e->getMessage());
            return json_encode(['error' => 'Failed to get status updates']);
        }
    }
    
    /**
     * Get traffic flow data for connections
     */
    public function getTrafficFlowData() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    device_id, interface_name,
                    bytes_in, bytes_out,
                    packets_in, packets_out,
                    errors_in, errors_out,
                    status
                FROM network_monitoring 
                ORDER BY device_id, interface_name
            ");
            $stmt->execute();
            $traffic = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'traffic' => $traffic
            ]);
            
        } catch (PDOException $e) {
            error_log("Traffic Flow Error: " . $e->getMessage());
            return json_encode(['error' => 'Failed to get traffic data']);
        }
    }
    
    /**
     * Update device status in real-time
     */
    public function updateDeviceStatus($deviceId, $status) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE devices 
                SET status = ?, last_seen = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$status, $deviceId]);
            
            return json_encode([
                'success' => true,
                'device_id' => $deviceId,
                'status' => $status,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (PDOException $e) {
            error_log("Device Status Update Error: " . $e->getMessage());
            return json_encode(['error' => 'Failed to update device status']);
        }
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $viewer = new WebGLNetworkViewer();
    
    $action = $_GET['action'] ?? 'network_data';
    
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    switch ($action) {
        case 'network_data':
            echo $viewer->getNetworkData();
            break;
            
        case 'device_status':
            echo $viewer->getDeviceStatusUpdates();
            break;
            
        case 'traffic_flow':
            echo $viewer->getTrafficFlowData();
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $viewer = new WebGLNetworkViewer();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    header('Content-Type: application/json');
    
    switch ($action) {
        case 'update_device_status':
            $deviceId = $input['device_id'] ?? null;
            $status = $input['status'] ?? null;
            
            if ($deviceId && $status) {
                echo $viewer->updateDeviceStatus($deviceId, $status);
            } else {
                echo json_encode(['error' => 'Missing device_id or status']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
}

// WebSocket support for real-time updates
if (isset($_GET['websocket']) && $_GET['websocket'] === 'true') {
    // This would require a WebSocket server implementation
    // For now, we'll use Server-Sent Events (SSE) as a fallback
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('Access-Control-Allow-Origin: *');
    
    $viewer = new WebGLNetworkViewer();
    
    while (true) {
        // Send network data updates every 5 seconds
        $data = $viewer->getDeviceStatusUpdates();
        echo "data: " . $data . "\n\n";
        
        ob_flush();
        flush();
        sleep(5);
    }
}
?>

<!-- WebGL Network Viewer Interface -->
<?php if (!isset($_GET['action']) && !isset($_GET['websocket'])): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Network Topology - AI Service Network Management System</title>
    
    <!-- Three.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/style.css" rel="stylesheet">
    
    <style>
        #network-viewer {
            width: 100%;
            height: 80vh;
            background: #1a1a1a;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .controls-panel {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .device-info {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
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
        .status-warning { background-color: #ffc107; }
    </style>
</head>
<body class="dark-theme">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-diagram-3"></i>
                    3D Network Topology Viewer
                </h1>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-9">
                <!-- 3D Network Viewer -->
                <div id="network-viewer"></div>
                
                <!-- Device Information Panel -->
                <div class="device-info" id="device-info" style="display: none;">
                    <h5><i class="bi bi-info-circle"></i> Device Information</h5>
                    <div id="device-details"></div>
                </div>
            </div>
            
            <div class="col-md-3">
                <!-- Controls Panel -->
                <div class="controls-panel">
                    <h5><i class="bi bi-gear"></i> Controls</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">View Mode</label>
                        <select class="form-select" id="view-mode">
                            <option value="topology">Network Topology</option>
                            <option value="traffic">Traffic Flow</option>
                            <option value="status">Device Status</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Auto Refresh</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto-refresh" checked>
                            <label class="form-check-label" for="auto-refresh">Enable</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Refresh Interval</label>
                        <select class="form-select" id="refresh-interval">
                            <option value="5">5 seconds</option>
                            <option value="10">10 seconds</option>
                            <option value="30">30 seconds</option>
                            <option value="60">1 minute</option>
                        </select>
                    </div>
                    
                    <button class="btn btn-primary w-100 mb-2" onclick="refreshNetworkData()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh Now
                    </button>
                    
                    <button class="btn btn-secondary w-100" onclick="resetCamera()">
                        <i class="bi bi-camera"></i> Reset Camera
                    </button>
                </div>
                
                <!-- Network Statistics -->
                <div class="controls-panel">
                    <h5><i class="bi bi-graph-up"></i> Statistics</h5>
                    <div id="network-stats">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h4" id="total-devices">0</div>
                                <small>Total Devices</small>
                            </div>
                            <div class="col-6">
                                <div class="h4" id="online-devices">0</div>
                                <small>Online</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h4" id="total-connections">0</div>
                                <small>Connections</small>
                            </div>
                            <div class="col-6">
                                <div class="h4" id="total-traffic">0</div>
                                <small>MB/s</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- WebGL Network Viewer -->
    <script src="../assets/webgl-network-viewer.js"></script>
    
    <script>
        let networkViewer;
        let refreshInterval;
        let autoRefresh = true;
        
        // Initialize the network viewer
        document.addEventListener('DOMContentLoaded', function() {
            // Create network viewer instance
            networkViewer = new NetworkTopologyViewer('network-viewer', {
                backgroundColor: 0x1a1a1a,
                deviceColors: {
                    router: 0x00ff00,
                    switch: 0x0088ff,
                    client: 0xff8800,
                    server: 0xff0088,
                    offline: 0x666666
                }
            });
            
            // Load initial network data
            loadNetworkData();
            
            // Setup event listeners
            setupEventListeners();
            
            // Start auto refresh
            startAutoRefresh();
        });
        
        // Load network data from API
        async function loadNetworkData() {
            try {
                const response = await fetch('?action=network_data');
                const data = await response.json();
                
                if (data.devices && data.connections) {
                    networkViewer.loadNetworkData(data);
                    updateStatistics(data);
                }
            } catch (error) {
                console.error('Failed to load network data:', error);
            }
        }
        
        // Update network statistics
        function updateStatistics(data) {
            const totalDevices = data.devices.length;
            const onlineDevices = data.devices.filter(d => d.status === 'online').length;
            const totalConnections = data.connections.length;
            
            document.getElementById('total-devices').textContent = totalDevices;
            document.getElementById('online-devices').textContent = onlineDevices;
            document.getElementById('total-connections').textContent = totalConnections;
            document.getElementById('total-traffic').textContent = '0'; // TODO: Calculate from traffic data
        }
        
        // Setup event listeners
        function setupEventListeners() {
            // Device selection event
            document.getElementById('network-viewer').addEventListener('deviceSelected', function(event) {
                showDeviceInfo(event.detail);
            });
            
            // Auto refresh toggle
            document.getElementById('auto-refresh').addEventListener('change', function(event) {
                autoRefresh = event.target.checked;
                if (autoRefresh) {
                    startAutoRefresh();
                } else {
                    stopAutoRefresh();
                }
            });
            
            // Refresh interval change
            document.getElementById('refresh-interval').addEventListener('change', function(event) {
                if (autoRefresh) {
                    stopAutoRefresh();
                    startAutoRefresh();
                }
            });
        }
        
        // Show device information
        function showDeviceInfo(device) {
            const deviceInfo = document.getElementById('device-info');
            const deviceDetails = document.getElementById('device-details');
            
            const statusClass = device.status === 'online' ? 'status-online' : 'status-offline';
            
            deviceDetails.innerHTML = `
                <div class="mb-2">
                    <strong>Name:</strong> ${device.name}
                </div>
                <div class="mb-2">
                    <strong>Type:</strong> ${device.type}
                </div>
                <div class="mb-2">
                    <strong>Status:</strong> 
                    <span class="status-indicator ${statusClass}"></span>
                    ${device.status}
                </div>
                <div class="mb-2">
                    <strong>Position:</strong> 
                    (${device.position.x.toFixed(1)}, ${device.position.y.toFixed(1)}, ${device.position.z.toFixed(1)})
                </div>
                <div class="mt-3">
                    <button class="btn btn-sm btn-primary" onclick="focusOnDevice('${device.id}')">
                        <i class="bi bi-search"></i> Focus
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="manageDevice('${device.id}')">
                        <i class="bi bi-gear"></i> Manage
                    </button>
                </div>
            `;
            
            deviceInfo.style.display = 'block';
        }
        
        // Focus camera on device
        function focusOnDevice(deviceId) {
            networkViewer.focusOnDevice(deviceId);
        }
        
        // Manage device (redirect to device management page)
        function manageDevice(deviceId) {
            window.open(`../modules/devices.php?action=edit&id=${deviceId}`, '_blank');
        }
        
        // Refresh network data
        function refreshNetworkData() {
            loadNetworkData();
        }
        
        // Reset camera position
        function resetCamera() {
            networkViewer.setCameraPosition(0, 0, 50);
        }
        
        // Start auto refresh
        function startAutoRefresh() {
            const interval = parseInt(document.getElementById('refresh-interval').value) * 1000;
            refreshInterval = setInterval(loadNetworkData, interval);
        }
        
        // Stop auto refresh
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopAutoRefresh();
        });
    </script>
</body>
</html>
<?php endif; ?> 