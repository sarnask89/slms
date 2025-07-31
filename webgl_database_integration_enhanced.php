<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced WebGL Database Integration - SLMS</title>
    
    <!-- Three.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SQLite WASM Integration -->
    <script src="assets/webgl-sqlite-integration.js"></script>
    <!-- Fallback Integration (when SQLite WASM is not available) -->
    <script src="assets/webgl-sqlite-integration-fallback.js"></script>
    
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
        .status-local { background-color: #17a2b8; }
        
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
        
        .sqlite-badge { background-color: #00d4ff; color: white; }
        .offline-badge { background-color: #28a745; color: white; }
        .realtime-badge { background-color: #ff6b35; color: white; }
        
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
                    Enhanced WebGL Database Integration
                    <small class="text-muted">SQLite WASM + Real-time Sync</small>
                </h1>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <!-- WebGL 3D Viewer -->
                <div id="webgl-viewer"></div>
                
                <!-- Enhanced Database Status Panel -->
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
                                <div class="h4" id="pending-changes">0</div>
                                <small>Pending Changes</small>
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
                <!-- Enhanced Database Controls -->
                <div class="database-panel">
                    <h5><i class="bi bi-gear"></i> Database Controls</h5>
                    
                    <button class="btn btn-primary w-100 mb-2" onclick="initializeSQLite()">
                        <i class="bi bi-database-add"></i> Initialize SQLite WASM
                    </button>
                    
                    <button class="btn btn-success w-100 mb-2" onclick="loadLocalData()">
                        <i class="bi bi-arrow-clockwise"></i> Load Local Data
                    </button>
                    
                    <button class="btn btn-info w-100 mb-2" onclick="startSync()">
                        <i class="bi bi-arrow-repeat"></i> Start Sync
                    </button>
                    
                    <button class="btn btn-warning w-100 mb-2" onclick="stopSync()">
                        <i class="bi bi-pause"></i> Stop Sync
                    </button>
                    
                    <button class="btn btn-secondary w-100 mb-2" onclick="exportLocalData()">
                        <i class="bi bi-download"></i> Export Local Data
                    </button>
                    
                    <button class="btn btn-dark w-100" onclick="showDatabaseStats()">
                        <i class="bi bi-graph-up"></i> Database Stats
                    </button>
                </div>
                
                <!-- Feature Status -->
                <div class="database-panel">
                    <h5><i class="bi bi-check-circle"></i> Feature Status</h5>
                    <div class="mb-2">
                        <span class="feature-badge sqlite-badge">SQLite WASM</span>
                        <span id="sqlite-status">Loading...</span>
                    </div>
                    <div class="mb-2">
                        <span class="feature-badge offline-badge">Offline Mode</span>
                        <span id="offline-status">Available</span>
                    </div>
                    <div class="mb-2">
                        <span class="feature-badge realtime-badge">Real-time Sync</span>
                        <span id="realtime-status">Active</span>
                    </div>
                    <div class="mb-2">
                        <span class="feature-badge sqlite-badge">Local Database</span>
                        <span id="local-db-status">Initializing...</span>
                    </div>
                </div>
                
                <!-- Integration Log -->
                <div class="database-panel">
                    <h5><i class="bi bi-journal-text"></i> Integration Log</h5>
                    <div class="log-panel" id="integration-log">
                        <div>Initializing Enhanced WebGL Database Integration...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Enhanced WebGL Database Integration Script -->
    <script>
        let webglIntegration;
        let sqliteIntegration;
        let scene, camera, renderer;
        let deviceMeshes = new Map();
        let connectionLines = [];
        let isInitialized = false;
        let syncInterval = null;
        let performanceMetrics = {
            queryTime: 0,
            syncTime: 0,
            memoryUsage: 0,
            fps: 60
        };
        
        // Initialize the enhanced integration
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Three.js scene
            initThreeJS();
            
            // Initialize SQLite integration
            initializeSQLite();
            
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
        
        // Initialize SQLite WASM integration
        async function initializeSQLite() {
            try {
                log("Initializing SQLite WASM integration...");
                updateFeatureStatus('sqlite-status', 'Initializing...');
                
                // Try SQLite WASM first
                if (typeof WebGLSQLiteIntegration !== 'undefined') {
                    sqliteIntegration = new WebGLSQLiteIntegration();
                    const success = await sqliteIntegration.initialize();
                    
                    if (success) {
                        log("SQLite WASM integration initialized successfully");
                        updateFeatureStatus('sqlite-status', 'Active (WASM)');
                        updateFeatureStatus('local-db-status', 'Ready');
                        
                        // Load initial data
                        await loadLocalData();
                        
                        // Start sync
                        startSync();
                        return;
                    }
                }
                
                // Fallback to localStorage-based implementation
                log("SQLite WASM not available, using fallback implementation...");
                updateFeatureStatus('sqlite-status', 'Active (Fallback)');
                
                sqliteIntegration = new WebGLSQLiteIntegrationFallback();
                const fallbackSuccess = await sqliteIntegration.initialize();
                
                if (fallbackSuccess) {
                    log("Fallback database integration initialized successfully");
                    updateFeatureStatus('local-db-status', 'Ready (LocalStorage)');
                    
                    // Load initial data
                    await loadLocalData();
                    
                    // Start sync
                    startSync();
                } else {
                    log("Failed to initialize fallback database");
                    updateFeatureStatus('sqlite-status', 'Failed');
                    updateFeatureStatus('local-db-status', 'Error');
                }
                
            } catch (error) {
                log("Error initializing database: " + error.message);
                updateFeatureStatus('sqlite-status', 'Error');
                updateFeatureStatus('local-db-status', 'Error');
            }
        }
        
        // Load data from local SQLite database
        async function loadLocalData() {
            try {
                const startTime = performance.now();
                
                if (!sqliteIntegration || !sqliteIntegration.isInitialized) {
                    log("SQLite integration not ready");
                    return;
                }
                
                log("Loading data from local SQLite database...");
                
                // Get data from local database
                const devices = sqliteIntegration.getDevices();
                const connections = sqliteIntegration.getConnections();
                const settings = sqliteIntegration.getSettings();
                
                // Update statistics
                updateStatistics({
                    total_devices: devices.length,
                    total_connections: connections.length,
                    devices: devices,
                    connections: connections,
                    settings: settings
                });
                
                // Visualize network
                visualizeNetwork(devices, connections);
                
                // Update performance metrics
                performanceMetrics.queryTime = Math.round(performance.now() - startTime);
                updatePerformanceMetrics();
                
                log(`Loaded ${devices.length} devices and ${connections.length} connections from local database`);
                
            } catch (error) {
                log("Error loading local data: " + error.message);
            }
        }
        
        // Visualize network in 3D
        function visualizeNetwork(devices, connections) {
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
                    <button class="btn btn-sm btn-info" onclick="exportDeviceData('${device.id}')">
                        <i class="bi bi-download"></i> Export
                    </button>
                </div>
            `;
            
            deviceInfo.style.display = 'block';
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
                
                if (!sqliteIntegration || !sqliteIntegration.isInitialized) {
                    return;
                }
                
                // Sync to server (handled by SQLite integration)
                await sqliteIntegration.syncToServer();
                
                // Update pending changes count
                const stats = sqliteIntegration.getDatabaseStats();
                document.getElementById('pending-changes').textContent = stats.pending_changes;
                
                // Update performance metrics
                performanceMetrics.syncTime = Math.round(performance.now() - startTime);
                updatePerformanceMetrics();
                
            } catch (error) {
                log("Sync error: " + error.message);
            }
        }
        
        // Export local data
        function exportLocalData() {
            if (!sqliteIntegration || !sqliteIntegration.isInitialized) {
                log("SQLite integration not ready");
                return;
            }
            
            const data = sqliteIntegration.exportDatabase();
            if (data) {
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `slms_local_data_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.json`;
                a.click();
                URL.revokeObjectURL(url);
                
                log("Local data exported successfully");
            } else {
                log("Failed to export local data");
            }
        }
        
        // Show database statistics
        function showDatabaseStats() {
            if (!sqliteIntegration || !sqliteIntegration.isInitialized) {
                log("SQLite integration not ready");
                return;
            }
            
            const stats = sqliteIntegration.getDatabaseStats();
            log(`Database Statistics:
                - Devices: ${stats.devices}
                - Connections: ${stats.connections}
                - Settings: ${stats.settings}
                - Pending Changes: ${stats.pending_changes}
                - Last Updated: ${stats.timestamp}`);
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
            // This would open a dialog to update device position
            log(`Updating position for device ${deviceId}`);
        }
        
        function exportDeviceData(deviceId) {
            // This would export specific device data
            log(`Exporting data for device ${deviceId}`);
        }
    </script>
</body>
</html> 