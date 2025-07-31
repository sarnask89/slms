<?php
/**
 * WebGL Database Integration Interface - Clean Version
 * Provides WebGL interface for database integration using clean API endpoints
 * This version avoids header conflicts by using separate API endpoints
 */

require_once 'config.php';
?>
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
        
        .feature-badge {
            font-size: 0.8em;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
        }
        
        .api-badge { background-color: #00d4ff; color: white; }
        .webgl-badge { background-color: #28a745; color: white; }
        .sync-badge { background-color: #ff6b35; color: white; }
        
        .device-info {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .performance-metrics {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        
        .metric-item {
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid #00d4ff;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
        }
        
        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #00d4ff;
        }
        
        .metric-label {
            font-size: 12px;
            color: #b0b0b0;
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
                    <small class="text-muted">Direct Database Access & Real-time Sync</small>
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
                    
                    <!-- Performance Metrics -->
                    <div class="performance-metrics">
                        <div class="metric-item">
                            <div class="metric-value" id="query-time">0ms</div>
                            <div class="metric-label">Query Time</div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-value" id="sync-time">0ms</div>
                            <div class="metric-label">Sync Time</div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-value" id="memory-usage">0MB</div>
                            <div class="metric-label">Memory Usage</div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-value" id="fps">60</div>
                            <div class="metric-label">FPS</div>
                        </div>
                    </div>
                </div>
                
                <!-- Device Information Panel -->
                <div class="device-info" id="device-info" style="display: none;">
                    <h5><i class="bi bi-info-circle"></i> Device Information</h5>
                    <div id="device-details"></div>
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
                    
                    <button class="btn btn-secondary w-100 mb-2" onclick="exportData()">
                        <i class="bi bi-download"></i> Export Data
                    </button>
                    
                    <button class="btn btn-dark w-100" onclick="showStats()">
                        <i class="bi bi-graph-up"></i> Show Stats
                    </button>
                </div>
                
                <!-- Feature Status -->
                <div class="database-panel">
                    <h5><i class="bi bi-check-circle"></i> Feature Status</h5>
                    <div class="mb-2">
                        <span class="feature-badge api-badge">API Integration</span>
                        <span id="api-status">Connected</span>
                    </div>
                    <div class="mb-2">
                        <span class="feature-badge webgl-badge">WebGL 3D</span>
                        <span id="webgl-status">Rendering</span>
                    </div>
                    <div class="mb-2">
                        <span class="feature-badge sync-badge">Real-time Sync</span>
                        <span id="realtime-status">Active</span>
                    </div>
                    <div class="mb-2">
                        <span class="feature-badge api-badge">Database Access</span>
                        <span id="db-status">Ready</span>
                    </div>
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
        let scene, camera, renderer;
        let deviceMeshes = new Map();
        let connectionLines = [];
        let networkData = null;
        let syncInterval = null;
        let performanceMetrics = {
            queryTime: 0,
            syncTime: 0,
            memoryUsage: 0,
            fps: 60
        };
        
        // Initialize the integration
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Three.js scene
            initThreeJS();
            
            // Load initial data
            loadNetworkData();
            
            // Start performance monitoring
            startPerformanceMonitoring();
        });
        
        // Initialize Three.js scene
        function initThreeJS() {
            // Create scene
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x1a1a1a);
            
            // Create camera
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.set(0, 0, 50);
            
            // Create renderer
            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(document.getElementById('webgl-viewer').clientWidth, 
                            document.getElementById('webgl-viewer').clientHeight);
            document.getElementById('webgl-viewer').appendChild(renderer.domElement);
            
            // Add lighting
            const ambientLight = new THREE.AmbientLight(0x404040, 0.4);
            scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.6);
            directionalLight.position.set(5, 5, 5);
            scene.add(directionalLight);
            
            // Start animation loop
            animate();
        }
        
        // Animation loop
        function animate() {
            requestAnimationFrame(animate);
            renderer.render(scene, camera);
        }
        
        // Setup database schema
        async function setupDatabase() {
            try {
                log("Setting up database schema...");
                
                const response = await fetch('api/webgl_database_api_clean.php?action=setup_database');
                const result = await response.json();
                
                if (result.success) {
                    log("Database schema setup completed");
                    return true;
                } else {
                    log("Database schema setup failed");
                    return false;
                }
            } catch (error) {
                log("Error setting up database: " + error.message);
                return false;
            }
        }
        
        // Load network data from API
        async function loadNetworkData() {
            try {
                const startTime = performance.now();
                
                log("Loading network data from API...");
                updateFeatureStatus('api-status', 'Loading...');
                
                const response = await fetch('api/webgl_database_api_clean.php?action=network_data');
                const data = await response.json();
                
                if (data.success) {
                    networkData = data;
                    
                    // Update statistics
                    updateStatistics(data);
                    
                    // Visualize network
                    visualizeNetwork(data.devices, data.connections);
                    
                    // Update performance metrics
                    performanceMetrics.queryTime = Math.round(performance.now() - startTime);
                    updatePerformanceMetrics();
                    
                    updateFeatureStatus('api-status', 'Connected');
                    log(`Loaded ${data.total_devices} devices and ${data.total_connections} connections`);
                    
                } else {
                    log("Failed to load network data: " + data.error);
                    updateFeatureStatus('api-status', 'Error');
                }
                
            } catch (error) {
                log("Error loading network data: " + error.message);
                updateFeatureStatus('api-status', 'Error');
            }
        }
        
        // Start synchronization
        function startSync() {
            if (syncInterval) {
                stopSync();
            }
            
            syncInterval = setInterval(async () => {
                await syncData();
            }, 30000); // Sync every 30 seconds
            
            updateSyncStatus('syncing');
            updateFeatureStatus('realtime-status', 'Active');
            log("Started real-time synchronization");
        }
        
        // Stop synchronization
        function stopSync() {
            if (syncInterval) {
                clearInterval(syncInterval);
                syncInterval = null;
            }
            
            updateSyncStatus('offline');
            updateFeatureStatus('realtime-status', 'Stopped');
            log("Stopped synchronization");
        }
        
        // Sync data with server
        async function syncData() {
            try {
                const startTime = performance.now();
                
                // Get local changes (simulated)
                const changes = getLocalChanges();
                
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
                        log(`Synced ${result.synced} changes`);
                        updateLastSync();
                        updateSyncStatus('online');
                    } else {
                        log("Sync failed: " + result.error);
                        updateSyncStatus('offline');
                    }
                }
                
                // Update performance metrics
                performanceMetrics.syncTime = Math.round(performance.now() - startTime);
                updatePerformanceMetrics();
                
            } catch (error) {
                log("Sync error: " + error.message);
                updateSyncStatus('offline');
            }
        }
        
        // Get local changes (simulated)
        function getLocalChanges() {
            // This would normally get changes from local storage or SQLite WASM
            return [];
        }
        
        // Export data
        function exportData() {
            if (!networkData) {
                log("No data available for export");
                return;
            }
            
            const exportData = {
                ...networkData,
                export_timestamp: new Date().toISOString()
            };
            
            const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `slms_export_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.json`;
            a.click();
            URL.revokeObjectURL(url);
            
            log("Data exported successfully");
        }
        
        // Show statistics
        function showStats() {
            if (!networkData) {
                log("No data available for statistics");
                return;
            }
            
            const deviceStats = networkData.devices.reduce((acc, device) => {
                acc[device.status] = (acc[device.status] || 0) + 1;
                return acc;
            }, {});
            
            log(`Network Statistics:
                - Total Devices: ${networkData.total_devices}
                - Total Connections: ${networkData.total_connections}
                - Online Devices: ${deviceStats.online || 0}
                - Offline Devices: ${deviceStats.offline || 0}
                - Device Types: ${[...new Set(networkData.devices.map(d => d.type))].join(', ')}`);
        }
        
        // Visualize network in 3D
        function visualizeNetwork(devices, connections) {
            const startTime = performance.now();
            
            // Clear existing objects
            deviceMeshes.forEach(mesh => scene.remove(mesh));
            deviceMeshes.clear();
            
            connectionLines.forEach(line => scene.remove(line));
            connectionLines = [];
            
            // Create device geometries
            devices.forEach(device => {
                const geometry = getDeviceGeometry(device.type);
                const material = getDeviceMaterial(device.status);
                const mesh = new THREE.Mesh(geometry, material);
                
                mesh.position.set(device.position_x, device.position_y, device.position_z);
                mesh.userData = { device: device };
                
                // Add click event
                mesh.addEventListener('click', () => showDeviceInfo(device));
                
                scene.add(mesh);
                deviceMeshes.set(device.id, mesh);
            });
            
            // Create connection lines
            connections.forEach(connection => {
                const fromDevice = devices.find(d => d.id === connection.from_device_id);
                const toDevice = devices.find(d => d.id === connection.to_device_id);
                
                if (fromDevice && toDevice) {
                    const geometry = new THREE.BufferGeometry().setFromPoints([
                        new THREE.Vector3(fromDevice.position_x, fromDevice.position_y, fromDevice.position_z),
                        new THREE.Vector3(toDevice.position_x, toDevice.position_y, toDevice.position_z)
                    ]);
                    
                    const material = new THREE.LineBasicMaterial({ color: 0x00ffff });
                    const line = new THREE.Line(geometry, material);
                    
                    scene.add(line);
                    connectionLines.push(line);
                }
            });
            
            // Update performance metrics
            performanceMetrics.renderTime = Math.round(performance.now() - startTime);
            updatePerformanceMetrics();
        }
        
        // Get device geometry based on type
        function getDeviceGeometry(type) {
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
        
        // Get device material based on status
        function getDeviceMaterial(status) {
            const colors = {
                'online': 0x00ff00,
                'offline': 0xff0000,
                'warning': 0xffff00
            };
            
            return new THREE.MeshPhongMaterial({ 
                color: colors[status] || 0x666666 
            });
        }
        
        // Show device information
        function showDeviceInfo(device) {
            const deviceInfo = document.getElementById('device-info');
            const deviceDetails = document.getElementById('device-details');
            
            if (!deviceInfo || !deviceDetails) return;
            
            const statusClass = device.status === 'online' ? 'status-online' : 
                               device.status === 'warning' ? 'status-warning' : 'status-offline';
            
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
                    <strong>IP Address:</strong> ${device.ip_address || 'N/A'}
                </div>
                <div class="mb-2">
                    <strong>Position:</strong> 
                    (${device.position_x.toFixed(1)}, ${device.position_y.toFixed(1)}, ${device.position_z.toFixed(1)})
                </div>
                ${device.model ? `<div class="mb-2"><strong>Model:</strong> ${device.model}</div>` : ''}
                ${device.vendor ? `<div class="mb-2"><strong>Vendor:</strong> ${device.vendor}</div>` : ''}
                ${device.location ? `<div class="mb-2"><strong>Location:</strong> ${device.location}</div>` : ''}
                <div class="mt-3">
                    <button class="btn btn-sm btn-primary" onclick="focusOnDevice('${device.id}')">
                        <i class="bi bi-search"></i> Focus
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="updateDevicePosition('${device.id}')">
                        <i class="bi bi-gear"></i> Update Position
                    </button>
                </div>
            `;
            
            deviceInfo.style.display = 'block';
        }
        
        // Update statistics display
        function updateStatistics(data) {
            document.getElementById('total-devices').textContent = data.total_devices;
            document.getElementById('total-connections').textContent = data.total_connections;
        }
        
        // Update sync status
        function updateSyncStatus(status) {
            const statusElement = document.getElementById('sync-status');
            const indicator = statusElement.querySelector('.status-indicator');
            
            indicator.className = `status-indicator status-${status}`;
            statusElement.innerHTML = indicator.outerHTML + status.charAt(0).toUpperCase() + status.slice(1);
        }
        
        // Update last sync time
        function updateLastSync() {
            document.getElementById('last-sync').textContent = new Date().toLocaleTimeString();
        }
        
        // Update feature status
        function updateFeatureStatus(elementId, status) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = status;
            }
        }
        
        // Update performance metrics
        function updatePerformanceMetrics() {
            document.getElementById('query-time').textContent = performanceMetrics.queryTime + 'ms';
            document.getElementById('sync-time').textContent = performanceMetrics.syncTime + 'ms';
            document.getElementById('memory-usage').textContent = performanceMetrics.memoryUsage + 'MB';
            document.getElementById('fps').textContent = performanceMetrics.fps;
        }
        
        // Start performance monitoring
        function startPerformanceMonitoring() {
            setInterval(() => {
                // Update memory usage (simulated)
                performanceMetrics.memoryUsage = Math.round(Math.random() * 50 + 10);
                
                // Update FPS (simulated)
                performanceMetrics.fps = Math.round(Math.random() * 20 + 50);
                
                updatePerformanceMetrics();
            }, 5000);
        }
        
        // Log messages
        function log(message) {
            const logElement = document.getElementById('integration-log');
            const timestamp = new Date().toLocaleTimeString();
            logElement.innerHTML += `<div>[${timestamp}] ${message}</div>`;
            logElement.scrollTop = logElement.scrollHeight;
        }
        
        // Utility functions
        function focusOnDevice(deviceId) {
            const mesh = deviceMeshes.get(parseInt(deviceId));
            if (mesh) {
                camera.position.set(
                    mesh.position.x + 10,
                    mesh.position.y + 10,
                    mesh.position.z + 10
                );
                camera.lookAt(mesh.position);
            }
        }
        
        function updateDevicePosition(deviceId) {
            log(`Updating position for device ${deviceId}`);
        }
    </script>
</body>
</html> 