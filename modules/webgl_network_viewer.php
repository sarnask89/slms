<?php
/**
 * Enhanced WebGL Network Viewer Module
 * Integrates Three.js-based 3D network visualization with the AI Service Network Management System
 * 
 * Features:
 * - Network topology data API with existing module integration
 * - Real-time device status updates from SNMP and Cacti
 * - WebSocket support for live updates
 * - Integration with existing device management, Cacti, and SNMP systems
 * - Enhanced data sources from existing modules
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers/auth_helper.php';

// Include existing module dependencies
require_once __DIR__ . '/cacti_integration.php';
require_once __DIR__ . '/snmp_graph.php';
require_once __DIR__ . '/network_monitoring_enhanced.php';
require_once __DIR__ . '/mikrotik_api.php';

class WebGLNetworkViewer {
    private $pdo;
    private $networkData = [];
    private $cactiIntegration;
    private $snmpHelper;
    private $networkMonitoring;
    
    public function __construct() {
        $this->pdo = get_pdo();
        $this->loadNetworkData();
        
        // Initialize existing module integrations
        $this->initializeModuleIntegrations();
    }
    
    /**
     * Initialize integrations with existing modules
     */
    private function initializeModuleIntegrations() {
        try {
            // Initialize Cacti integration if available
            if (class_exists('CactiIntegration')) {
                $this->cactiIntegration = new CactiIntegration();
            }
            
            // Initialize SNMP helper if available
            if (class_exists('SNMPHelper')) {
                $this->snmpHelper = new SNMPHelper();
            }
            
            // Initialize network monitoring if available
            if (class_exists('NetworkMonitoringEnhanced')) {
                $this->networkMonitoring = new NetworkMonitoringEnhanced();
            }
            
        } catch (Exception $e) {
            error_log("Module Integration Error: " . $e->getMessage());
        }
    }
    
    /**
     * Load network topology data from database with enhanced sources
     */
    private function loadNetworkData() {
        try {
            // Load devices with enhanced data
            $stmt = $this->pdo->prepare("
                SELECT 
                    d.id, d.name, d.type, d.ip_address, d.status, 
                    d.created_at, d.last_seen,
                    d.position_x, d.position_y, d.position_z,
                    d.mac_address, d.model, d.vendor,
                    d.serial_number, d.firmware_version,
                    d.location, d.description
                FROM devices d
                ORDER BY d.type, d.name
            ");
            $stmt->execute();
            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Load network connections with enhanced data
            $stmt = $this->pdo->prepare("
                SELECT 
                    nm.device_id, nm.interface_name, 
                    nm.bytes_in, nm.bytes_out, nm.status,
                    nm.packets_in, nm.packets_out,
                    nm.errors_in, nm.errors_out,
                    nm.last_polled,
                    d.name as device_name, d.type as device_type
                FROM network_monitoring nm
                JOIN devices d ON nm.device_id = d.id
                ORDER BY nm.device_id, nm.interface_name
            ");
            $stmt->execute();
            $interfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Load Cacti integration data if available
            $cactiData = $this->loadCactiData();
            
            // Load SNMP monitoring data if available
            $snmpData = $this->loadSNMPData();
            
            // Transform data for WebGL viewer with enhanced information
            $this->networkData = [
                'devices' => $this->transformDevices($devices, $cactiData, $snmpData),
                'connections' => $this->generateConnections($devices, $interfaces),
                'cacti_integration' => $cactiData,
                'snmp_data' => $snmpData,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (PDOException $e) {
            error_log("WebGL Network Viewer Error: " . $e->getMessage());
            $this->networkData = ['devices' => [], 'connections' => [], 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Load Cacti integration data
     */
    private function loadCactiData() {
        $cactiData = [];
        
        try {
            if ($this->cactiIntegration) {
                // Get Cacti device list
                $cactiDevices = $this->cactiIntegration->getDeviceList();
                
                // Get Cacti graphs for devices
                foreach ($cactiDevices as $device) {
                    $graphs = $this->cactiIntegration->getDeviceGraphs($device['id']);
                    $cactiData[$device['id']] = [
                        'device' => $device,
                        'graphs' => $graphs,
                        'last_updated' => date('Y-m-d H:i:s')
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Cacti Data Load Error: " . $e->getMessage());
        }
        
        return $cactiData;
    }
    
    /**
     * Load SNMP monitoring data
     */
    private function loadSNMPData() {
        $snmpData = [];
        
        try {
            if ($this->snmpHelper) {
                // Get SNMP device list
                $snmpDevices = $this->snmpHelper->getSNMPDevices();
                
                foreach ($snmpDevices as $device) {
                    $snmpData[$device['id']] = [
                        'device' => $device,
                        'interfaces' => $this->snmpHelper->getDeviceInterfaces($device['id']),
                        'system_info' => $this->snmpHelper->getSystemInfo($device['id']),
                        'last_polled' => date('Y-m-d H:i:s')
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("SNMP Data Load Error: " . $e->getMessage());
        }
        
        return $snmpData;
    }
    
    /**
     * Transform device data for 3D visualization with enhanced information
     */
    private function transformDevices($devices, $cactiData, $snmpData) {
        $transformed = [];
        $positionIndex = 0;
        
        foreach ($devices as $device) {
            // Use stored 3D positions if available, otherwise generate
            if (isset($device['position_x']) && isset($device['position_y']) && isset($device['position_z'])) {
                $x = $device['position_x'];
                $y = $device['position_y'];
                $z = $device['position_z'];
            } else {
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
            }
            
            // Enhanced device information
            $enhancedDevice = [
                'id' => $device['id'],
                'name' => $device['name'],
                'type' => $device['type'],
                'x' => $x,
                'y' => $y,
                'z' => $z,
                'status' => $device['status'] ?? 'online',
                'ip_address' => $device['ip_address'],
                'last_seen' => $device['last_seen'],
                'mac_address' => $device['mac_address'] ?? null,
                'model' => $device['model'] ?? null,
                'vendor' => $device['vendor'] ?? null,
                'serial_number' => $device['serial_number'] ?? null,
                'firmware_version' => $device['firmware_version'] ?? null,
                'location' => $device['location'] ?? null,
                'description' => $device['description'] ?? null,
                'created_at' => $device['created_at']
            ];
            
            // Add Cacti integration data if available
            if (isset($cactiData[$device['id']])) {
                $enhancedDevice['cacti_data'] = $cactiData[$device['id']];
            }
            
            // Add SNMP data if available
            if (isset($snmpData[$device['id']])) {
                $enhancedDevice['snmp_data'] = $snmpData[$device['id']];
            }
            
            $transformed[] = $enhancedDevice;
            $positionIndex++;
        }
        
        return $transformed;
    }
    
    /**
     * Generate network connections between devices with enhanced data
     */
    private function generateConnections($devices, $interfaces) {
        $connections = [];
        $deviceIds = array_column($devices, 'id');
        
        // Create connections based on device hierarchy and interface data
        foreach ($devices as $device) {
            if ($device['type'] === 'router') {
                // Connect router to switches
                foreach ($devices as $targetDevice) {
                    if ($targetDevice['type'] === 'switch' && $targetDevice['id'] !== $device['id']) {
                        $connections[] = [
                            'from' => $device['id'],
                            'to' => $targetDevice['id'],
                            'bandwidth' => 1000, // 1Gbps
                            'type' => 'ethernet',
                            'interface' => 'Router-Switch Link',
                            'status' => 'active'
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
                            'type' => 'ethernet',
                            'interface' => 'Switch-Device Link',
                            'status' => 'active'
                        ];
                    }
                }
            }
        }
        
        // Add interface-based connections from network monitoring
        foreach ($interfaces as $interface) {
            // Find corresponding device connections
            foreach ($connections as &$connection) {
                if ($connection['from'] == $interface['device_id']) {
                    $connection['interface_data'] = [
                        'name' => $interface['interface_name'],
                        'bytes_in' => $interface['bytes_in'],
                        'bytes_out' => $interface['bytes_out'],
                        'packets_in' => $interface['packets_in'],
                        'packets_out' => $interface['packets_out'],
                        'errors_in' => $interface['errors_in'],
                        'errors_out' => $interface['errors_out'],
                        'last_polled' => $interface['last_polled']
                    ];
                }
            }
        }
        
        return $connections;
    }
    
    /**
     * Get network topology data as JSON with enhanced information
     */
    public function getNetworkData() {
        return json_encode($this->networkData, JSON_PRETTY_PRINT);
    }
    
    /**
     * Get real-time device status updates with enhanced monitoring
     */
    public function getDeviceStatusUpdates() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    d.id, d.name, d.status, d.last_seen, d.type,
                    d.ip_address, d.mac_address,
                    CASE 
                        WHEN d.last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'online'
                        WHEN d.last_seen > DATE_SUB(NOW(), INTERVAL 15 MINUTE) THEN 'warning'
                        ELSE 'offline'
                    END as current_status
                FROM devices d 
                ORDER BY d.type, d.name
            ");
            $stmt->execute();
            $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add SNMP status if available
            foreach ($updates as &$update) {
                if ($this->snmpHelper && $update['ip_address']) {
                    try {
                        $snmpStatus = $this->snmpHelper->checkDeviceStatus($update['ip_address']);
                        $update['snmp_status'] = $snmpStatus;
                    } catch (Exception $e) {
                        $update['snmp_status'] = 'error';
                    }
                }
            }
            
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
     * Get traffic flow data for connections with enhanced monitoring
     */
    public function getTrafficFlowData() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    nm.device_id, nm.interface_name,
                    nm.bytes_in, nm.bytes_out,
                    nm.packets_in, nm.packets_out,
                    nm.errors_in, nm.errors_out,
                    nm.status, nm.last_polled,
                    d.name as device_name, d.type as device_type,
                    d.ip_address
                FROM network_monitoring nm
                JOIN devices d ON nm.device_id = d.id
                ORDER BY nm.device_id, nm.interface_name
            ");
            $stmt->execute();
            $traffic = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add Cacti graph data if available
            foreach ($traffic as &$flow) {
                if ($this->cactiIntegration) {
                    try {
                        $cactiGraphs = $this->cactiIntegration->getInterfaceGraphs($flow['device_id'], $flow['interface_name']);
                        $flow['cacti_graphs'] = $cactiGraphs;
                    } catch (Exception $e) {
                        $flow['cacti_graphs'] = null;
                    }
                }
            }
            
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
     * Update device status in real-time with enhanced monitoring
     */
    public function updateDeviceStatus($deviceId, $status) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE devices 
                SET status = ?, last_seen = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$status, $deviceId]);
            
            // Update SNMP monitoring if available
            if ($this->snmpHelper) {
                try {
                    $device = $this->getDeviceById($deviceId);
                    if ($device && $device['ip_address']) {
                        $this->snmpHelper->updateDeviceStatus($device['ip_address'], $status);
                    }
                } catch (Exception $e) {
                    error_log("SNMP Status Update Error: " . $e->getMessage());
                }
            }
            
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
    
    /**
     * Get device by ID with enhanced information
     */
    public function getDeviceById($deviceId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM devices WHERE id = ?
            ");
            $stmt->execute([$deviceId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Device Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get network statistics with enhanced data
     */
    public function getNetworkStatistics() {
        try {
            // Device statistics
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_devices,
                    SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online_devices,
                    SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) as offline_devices,
                    SUM(CASE WHEN status = 'warning' THEN 1 ELSE 0 END) as warning_devices
                FROM devices
            ");
            $stmt->execute();
            $deviceStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Traffic statistics
            $stmt = $this->pdo->prepare("
                SELECT 
                    SUM(bytes_in) as total_bytes_in,
                    SUM(bytes_out) as total_bytes_out,
                    SUM(packets_in) as total_packets_in,
                    SUM(packets_out) as total_packets_out,
                    SUM(errors_in) as total_errors_in,
                    SUM(errors_out) as total_errors_out
                FROM network_monitoring
            ");
            $stmt->execute();
            $trafficStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'device_statistics' => $deviceStats,
                'traffic_statistics' => $trafficStats
            ]);
            
        } catch (PDOException $e) {
            error_log("Network Statistics Error: " . $e->getMessage());
            return json_encode(['error' => 'Failed to get network statistics']);
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
            
        case 'network_statistics':
            echo $viewer->getNetworkStatistics();
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

<!-- Enhanced WebGL Network Viewer Interface -->
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
        
        .integration-badge {
            font-size: 0.8em;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
        }
        
        .cacti-badge { background-color: #17a2b8; color: white; }
        .snmp-badge { background-color: #6f42c1; color: white; }
    </style>
</head>
<body class="dark-theme">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-diagram-3"></i>
                    3D Network Topology Viewer
                    <small class="text-muted">Enhanced with Cacti & SNMP Integration</small>
                </h1>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-9">
                <!-- 3D Network Viewer -->
                <div id="network-viewer"></div>
                
                <!-- Enhanced Device Information Panel -->
                <div class="device-info" id="device-info" style="display: none;">
                    <h5><i class="bi bi-info-circle"></i> Device Information</h5>
                    <div id="device-details"></div>
                </div>
            </div>
            
            <div class="col-md-3">
                <!-- Enhanced Controls Panel -->
                <div class="controls-panel">
                    <h5><i class="bi bi-gear"></i> Controls</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">View Mode</label>
                        <select class="form-select" id="view-mode">
                            <option value="topology">Network Topology</option>
                            <option value="traffic">Traffic Flow</option>
                            <option value="status">Device Status</option>
                            <option value="cacti">Cacti Integration</option>
                            <option value="snmp">SNMP Monitoring</option>
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
                    
                    <button class="btn btn-secondary w-100 mb-2" onclick="resetCamera()">
                        <i class="bi bi-camera"></i> Reset Camera
                    </button>
                    
                    <button class="btn btn-info w-100" onclick="exportNetworkData()">
                        <i class="bi bi-download"></i> Export Data
                    </button>
                </div>
                
                <!-- Enhanced Network Statistics -->
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
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h6" id="cacti-devices">0</div>
                                <small>Cacti Devices</small>
                            </div>
                            <div class="col-6">
                                <div class="h6" id="snmp-devices">0</div>
                                <small>SNMP Devices</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Enhanced WebGL Network Viewer -->
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
                    other: 0xff8800,
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
        
        // Load network data from API with enhanced information
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
        
        // Update network statistics with enhanced data
        function updateStatistics(data) {
            const totalDevices = data.devices.length;
            const onlineDevices = data.devices.filter(d => d.status === 'online').length;
            const totalConnections = data.connections.length;
            
            // Count Cacti and SNMP devices
            const cactiDevices = data.devices.filter(d => d.cacti_data).length;
            const snmpDevices = data.devices.filter(d => d.snmp_data).length;
            
            document.getElementById('total-devices').textContent = totalDevices;
            document.getElementById('online-devices').textContent = onlineDevices;
            document.getElementById('total-connections').textContent = totalConnections;
            document.getElementById('cacti-devices').textContent = cactiDevices;
            document.getElementById('snmp-devices').textContent = snmpDevices;
            document.getElementById('total-traffic').textContent = '0'; // TODO: Calculate from traffic data
        }
        
        // Setup event listeners
        function setupEventListeners() {
            // Device selection event
            const container = document.getElementById('network-viewer');
            if (container) {
                container.addEventListener('deviceSelected', function(event) {
                    showDeviceInfo(event.detail);
                });
            }
            
            // Auto refresh toggle
            const autoRefreshElement = document.getElementById('auto-refresh');
            if (autoRefreshElement) {
                autoRefreshElement.addEventListener('change', function(event) {
                    autoRefresh = event.target.checked;
                    if (autoRefresh) {
                        startAutoRefresh();
                    } else {
                        stopAutoRefresh();
                    }
                });
            }
            
            // Refresh interval change
            const refreshIntervalElement = document.getElementById('refresh-interval');
            if (refreshIntervalElement) {
                refreshIntervalElement.addEventListener('change', function(event) {
                    if (autoRefresh) {
                        stopAutoRefresh();
                        startAutoRefresh();
                    }
                });
            }
        }
        
        // Show enhanced device information
        function showDeviceInfo(device) {
            const deviceInfo = document.getElementById('device-info');
            const deviceDetails = document.getElementById('device-details');
            
            if (!deviceInfo || !deviceDetails) return;
            
            const statusClass = device.status === 'online' ? 'status-online' : 
                               device.status === 'warning' ? 'status-warning' : 'status-offline';
            
            let integrationBadges = '';
            if (device.cacti_data) {
                integrationBadges += '<span class="integration-badge cacti-badge">Cacti</span>';
            }
            if (device.snmp_data) {
                integrationBadges += '<span class="integration-badge snmp-badge">SNMP</span>';
            }
            
            deviceDetails.innerHTML = `
                <div class="mb-2">
                    <strong>Name:</strong> ${device.name} ${integrationBadges}
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
                    <strong>IP Address:</strong> ${device.ip_address || 'N/A'}
                </div>
                <div class="mb-2">
                    <strong>Position:</strong> 
                    (${device.x.toFixed(1)}, ${device.y.toFixed(1)}, ${device.z.toFixed(1)})
                </div>
                ${device.model ? `<div class="mb-2"><strong>Model:</strong> ${device.model}</div>` : ''}
                ${device.vendor ? `<div class="mb-2"><strong>Vendor:</strong> ${device.vendor}</div>` : ''}
                ${device.location ? `<div class="mb-2"><strong>Location:</strong> ${device.location}</div>` : ''}
                <div class="mt-3">
                    <button class="btn btn-sm btn-primary" onclick="focusOnDevice('${device.id}')">
                        <i class="bi bi-search"></i> Focus
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="manageDevice('${device.id}')">
                        <i class="bi bi-gear"></i> Manage
                    </button>
                    ${device.cacti_data ? `<button class="btn btn-sm btn-info" onclick="viewCactiData('${device.id}')">
                        <i class="bi bi-graph-up"></i> Cacti
                    </button>` : ''}
                    ${device.snmp_data ? `<button class="btn btn-sm btn-warning" onclick="viewSNMPData('${device.id}')">
                        <i class="bi bi-cpu"></i> SNMP
                    </button>` : ''}
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
        
        // View Cacti data for device
        function viewCactiData(deviceId) {
            window.open(`../modules/cacti_device_details.php?device_id=${deviceId}`, '_blank');
        }
        
        // View SNMP data for device
        function viewSNMPData(deviceId) {
            window.open(`../modules/snmp_graph.php?device_id=${deviceId}`, '_blank');
        }
        
        // Export network data
        function exportNetworkData() {
            fetch('?action=network_data')
                .then(response => response.json())
                .then(data => {
                    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `network_data_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.json`;
                    a.click();
                    URL.revokeObjectURL(url);
                });
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