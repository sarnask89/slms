<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Control Console - WebGL 3D (Fallback)</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --console-bg: #0a0a0a;
            --panel-bg: #1a1a1a;
            --accent-blue: #00d4ff;
            --accent-green: #00ff88;
            --accent-orange: #ff6b35;
            --accent-purple: #8b5cf6;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --border-glow: #00d4ff;
            --shadow-deep: rgba(0, 212, 255, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--console-bg);
            color: var(--text-primary);
            font-family: 'Courier New', monospace;
            overflow: hidden;
            height: 100vh;
            perspective: 1000px;
        }

        /* Futuristic Console Layout */
        .console-container {
            display: grid;
            grid-template-areas: 
                "header header header"
                "sidebar main controls"
                "footer footer footer";
            grid-template-rows: 80px 1fr 60px;
            grid-template-columns: 300px 1fr 350px;
            height: 100vh;
            gap: 2px;
            background: linear-gradient(45deg, #000, #1a1a1a);
        }

        /* Header Panel */
        .console-header {
            grid-area: header;
            background: linear-gradient(90deg, var(--panel-bg), #2a2a2a);
            border-bottom: 2px solid var(--accent-blue);
            box-shadow: 0 4px 20px var(--shadow-deep);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: relative;
            overflow: hidden;
        }

        .console-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-blue), transparent);
            animation: scan-line 3s linear infinite;
        }

        @keyframes scan-line {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--accent-blue);
            text-shadow: 0 0 10px var(--accent-blue);
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid var(--accent-blue);
            border-radius: 4px;
            font-size: 12px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent-green);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        /* Sidebar Panel */
        .console-sidebar {
            grid-area: sidebar;
            background: var(--panel-bg);
            border-right: 2px solid var(--accent-blue);
            box-shadow: 4px 0 20px var(--shadow-deep);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* 3D Panel Styling */
        .panel-3d {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border: 2px solid var(--accent-blue);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.1),
                0 0 20px var(--shadow-deep);
            transform: perspective(1000px) rotateX(5deg);
            transition: all 0.3s ease;
        }

        .panel-3d:hover {
            transform: perspective(1000px) rotateX(0deg) translateY(-5px);
            box-shadow: 
                0 12px 40px rgba(0, 0, 0, 0.9),
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                0 0 30px var(--shadow-deep);
        }

        .panel-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent-blue);
            margin-bottom: 15px;
            text-align: center;
            text-shadow: 0 0 8px var(--accent-blue);
        }

        /* 3D Buttons */
        .btn-3d {
            background: linear-gradient(145deg, #3a3a3a, #2a2a2a);
            border: 2px solid var(--accent-blue);
            border-radius: 6px;
            color: var(--text-primary);
            padding: 12px 20px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.6),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            transform: perspective(500px) rotateX(10deg);
            position: relative;
            overflow: hidden;
        }

        .btn-3d::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-3d:hover {
            transform: perspective(500px) rotateX(0deg) translateY(-2px);
            box-shadow: 
                0 6px 12px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                0 0 15px var(--shadow-deep);
            border-color: var(--accent-green);
        }

        .btn-3d:hover::before {
            left: 100%;
        }

        .btn-3d:active {
            transform: perspective(500px) rotateX(15deg) translateY(1px);
            box-shadow: 
                0 2px 4px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        /* Main Viewport */
        .console-main {
            grid-area: main;
            background: #000;
            position: relative;
            overflow: hidden;
        }

        #webgl-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        /* Viewport Border */
        .console-main::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 2px solid var(--accent-blue);
            border-radius: 8px;
            pointer-events: none;
            box-shadow: 
                inset 0 0 20px var(--shadow-deep),
                0 0 20px var(--shadow-deep);
            z-index: 1;
        }

        /* Controls Panel */
        .console-controls {
            grid-area: controls;
            background: var(--panel-bg);
            border-left: 2px solid var(--accent-blue);
            box-shadow: -4px 0 20px var(--shadow-deep);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
        }

        /* 3D Knobs */
        .knob-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .knob {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #3a3a3a, #2a2a2a, #3a3a3a);
            border: 3px solid var(--accent-blue);
            position: relative;
            cursor: pointer;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.6),
                inset 0 2px 4px rgba(0, 0, 0, 0.8);
            transition: all 0.3s ease;
        }

        .knob::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 4px;
            height: 20px;
            background: var(--accent-green);
            transform: translate(-50%, -50%) rotate(45deg);
            border-radius: 2px;
            box-shadow: 0 0 8px var(--accent-green);
        }

        .knob:hover {
            box-shadow: 
                0 6px 12px rgba(0, 0, 0, 0.8),
                inset 0 2px 4px rgba(0, 0, 0, 0.8),
                0 0 15px var(--shadow-deep);
            transform: scale(1.05);
        }

        .knob-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            text-align: center;
        }

        /* Statistics Display */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .stat-item {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border: 1px solid var(--accent-blue);
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.6),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-green);
            text-shadow: 0 0 8px var(--accent-green);
        }

        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            margin-top: 5px;
        }

        /* Device Info Panel */
        .device-info {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border: 2px solid var(--accent-blue);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            display: none;
        }

        .device-info.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .device-title {
            font-size: 16px;
            font-weight: bold;
            color: var(--accent-blue);
            margin-bottom: 15px;
            text-align: center;
            text-shadow: 0 0 8px var(--accent-blue);
        }

        .device-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .device-detail .label {
            color: var(--text-secondary);
        }

        .device-detail .value {
            color: var(--text-primary);
            font-weight: bold;
        }

        /* Footer */
        .console-footer {
            grid-area: footer;
            background: linear-gradient(90deg, var(--panel-bg), #2a2a2a);
            border-top: 2px solid var(--accent-blue);
            box-shadow: 0 -4px 20px var(--shadow-deep);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        /* Loading Screen */
        #loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--console-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .loading-content {
            text-align: center;
            color: var(--accent-blue);
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid transparent;
            border-top: 4px solid var(--accent-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Error Display */
        .error-display {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff4444;
            border-radius: 8px;
            padding: 20px;
            margin: 20px;
            color: #ff4444;
            text-align: center;
        }

        .error-display h3 {
            color: #ff6666;
            margin-bottom: 10px;
        }

        .error-display pre {
            background: rgba(0, 0, 0, 0.5);
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            text-align: left;
            overflow-x: auto;
        }

        /* Fallback Network Display */
        .fallback-network {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
            height: 100%;
            overflow-y: auto;
        }

        .device-card {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border: 2px solid var(--accent-blue);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.1),
                0 0 20px var(--shadow-deep);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .device-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 12px 40px rgba(0, 0, 0, 0.9),
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                0 0 30px var(--shadow-deep);
        }

        .device-card.online {
            border-color: var(--accent-green);
        }

        .device-card.offline {
            border-color: #ff4444;
        }

        .device-icon {
            font-size: 32px;
            margin-bottom: 10px;
            text-align: center;
        }

        .device-name {
            font-size: 16px;
            font-weight: bold;
            color: var(--accent-blue);
            margin-bottom: 5px;
            text-align: center;
        }

        .device-type {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            text-align: center;
        }

        .device-status {
            font-size: 12px;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        .device-status.online {
            background: rgba(0, 255, 136, 0.2);
            color: var(--accent-green);
            border: 1px solid var(--accent-green);
        }

        .device-status.offline {
            background: rgba(255, 68, 68, 0.2);
            color: #ff4444;
            border: 1px solid #ff4444;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .console-container {
                grid-template-areas: 
                    "header header"
                    "sidebar main"
                    "controls controls"
                    "footer footer";
                grid-template-rows: 80px 1fr 300px 60px;
                grid-template-columns: 250px 1fr;
            }
        }

        @media (max-width: 768px) {
            .console-container {
                grid-template-areas: 
                    "header"
                    "main"
                    "sidebar"
                    "controls"
                    "footer";
                grid-template-rows: 80px 1fr 300px 300px 60px;
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <h3>INITIALIZING NETWORK CONSOLE</h3>
            <p id="loading-status">Establishing 3D visualization protocols...</p>
            <div id="loading-progress" style="margin-top: 20px; font-size: 12px; color: var(--text-secondary);"></div>
        </div>
    </div>

    <!-- Console Container -->
    <div class="console-container">
        <!-- Header -->
        <header class="console-header">
            <div class="header-title">
                <i class="bi bi-cpu"></i> NETWORK CONTROL CONSOLE
            </div>
            <div class="header-status">
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    SYSTEM ONLINE
                </div>
                <div class="status-indicator">
                    <i class="bi bi-wifi"></i>
                    <span id="webgl-status">WEBGL ACTIVE</span>
                </div>
            </div>
        </header>

        <!-- Sidebar -->
        <aside class="console-sidebar">
            <div class="panel-3d">
                <div class="panel-title">Navigation</div>
                <div class="d-grid gap-3">
                    <button class="btn-3d" onclick="resetView()">
                        <i class="bi bi-camera"></i> Reset View
                    </button>
                    <button class="btn-3d" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh Data
                    </button>
                    <button class="btn-3d" onclick="toggleAutoRefresh()">
                        <i class="bi bi-play-circle"></i> Auto Refresh
                    </button>
                </div>
            </div>

            <div class="panel-3d">
                <div class="panel-title">View Mode</div>
                <select class="form-select" id="view-mode" style="background: #2a2a2a; color: var(--text-primary); border: 1px solid var(--accent-blue);">
                    <option value="topology">Network Topology</option>
                    <option value="traffic">Traffic Flow</option>
                    <option value="status">Device Status</option>
                </select>
            </div>

            <div class="panel-3d">
                <div class="panel-title">System Controls</div>
                <div class="knob-container">
                    <div class="knob" onclick="adjustBrightness()"></div>
                    <div class="knob-label">Brightness</div>
                </div>
                <div class="knob-container">
                    <div class="knob" onclick="adjustContrast()"></div>
                    <div class="knob-label">Contrast</div>
                </div>
            </div>
        </aside>

        <!-- Main Viewport -->
        <main class="console-main">
            <div id="webgl-container">
                <!-- Fallback network display -->
                <div id="fallback-network" class="fallback-network" style="display: none;"></div>
            </div>
        </main>

        <!-- Controls Panel -->
        <aside class="console-controls">
            <div class="panel-3d">
                <div class="panel-title">Network Statistics</div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value" id="total-devices">0</div>
                        <div class="stat-label">Devices</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="online-devices">0</div>
                        <div class="stat-label">Online</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="total-connections">0</div>
                        <div class="stat-label">Connections</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="total-traffic">0</div>
                        <div class="stat-label">MB/s</div>
                    </div>
                </div>
            </div>

            <div class="device-info" id="device-info">
                <div class="device-title" id="device-title">Device Information</div>
                <div id="device-details"></div>
            </div>

            <div class="panel-3d">
                <div class="panel-title">Quick Actions</div>
                <div class="d-grid gap-2">
                    <button class="btn-3d btn-sm" onclick="focusOnSelected()">
                        <i class="bi bi-search"></i> Focus Device
                    </button>
                    <button class="btn-3d btn-sm" onclick="simulateStatusChange()">
                        <i class="bi bi-arrow-repeat"></i> Toggle Status
                    </button>
                    <button class="btn-3d btn-sm" onclick="exportData()">
                        <i class="bi bi-download"></i> Export Data
                    </button>
                </div>
            </div>
        </aside>

        <!-- Footer -->
        <footer class="console-footer">
            <div>
                <i class="bi bi-clock"></i> 
                <span id="current-time"></span>
            </div>
            <div>
                <i class="bi bi-activity"></i> 
                <span id="system-status">SYSTEM OPERATIONAL</span>
            </div>
            <div>
                <i class="bi bi-gear"></i> 
                <span>v2.1.0 - WebGL Enhanced</span>
            </div>
        </footer>
    </div>

    <script>
        let networkViewer;
        let refreshInterval;
        let selectedDevice = null;
        let initializationTimeout;
        let useFallback = false;

        // Sample network data for demo
        const sampleNetworkData = {
            devices: [
                { id: 1, name: 'Main Router', type: 'router', x: 0, y: 0, z: 10, status: 'online' },
                { id: 2, name: 'Core Switch 1', type: 'switch', x: -15, y: 0, z: 5, status: 'online' },
                { id: 3, name: 'Core Switch 2', type: 'switch', x: 15, y: 0, z: 5, status: 'online' },
                { id: 4, name: 'Web Server', type: 'server', x: -10, y: 15, z: 0, status: 'online' },
                { id: 5, name: 'Database Server', type: 'server', x: 10, y: 15, z: 0, status: 'online' },
                { id: 6, name: 'Client PC 1', type: 'other', x: -20, y: -10, z: 0, status: 'online' },
                { id: 7, name: 'Client PC 2', type: 'other', x: 20, y: -10, z: 0, status: 'online' },
                { id: 8, name: 'Printer', type: 'other', x: 0, y: -20, z: 0, status: 'offline' }
            ],
            connections: [
                { from: 1, to: 2, bandwidth: 1000 },
                { from: 1, to: 3, bandwidth: 1000 },
                { from: 2, to: 4, bandwidth: 100 },
                { from: 3, to: 5, bandwidth: 100 },
                { from: 2, to: 6, bandwidth: 100 },
                { from: 3, to: 7, bandwidth: 100 },
                { from: 2, to: 8, bandwidth: 100 }
            ]
        };

        // Update loading status
        function updateLoadingStatus(message, progress = null) {
            const statusElement = document.getElementById('loading-status');
            const progressElement = document.getElementById('loading-progress');
            
            if (statusElement) {
                statusElement.textContent = message;
            }
            
            if (progressElement && progress !== null) {
                progressElement.textContent = `Progress: ${progress}%`;
            }
        }

        // Check WebGL support
        function checkWebGLSupport() {
            updateLoadingStatus('Checking WebGL support...', 10);
            
            try {
                const canvas = document.createElement('canvas');
                const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                
                if (!gl) {
                    throw new Error('WebGL not supported');
                }
                
                return true;
            } catch (error) {
                console.error('WebGL support check failed:', error);
                return false;
            }
        }

        // Load Three.js from CDN with fallback
        async function loadThreeJS() {
            updateLoadingStatus('Loading Three.js library...', 20);
            
            try {
                // Try CDN first
                await loadScript('https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js');
                await loadScript('https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js');
                
                if (typeof THREE === 'undefined') {
                    throw new Error('Three.js failed to load from CDN');
                }
                
                return true;
            } catch (error) {
                console.warn('CDN failed, trying alternative sources...', error);
                
                try {
                    // Try alternative CDN
                    await loadScript('https://unpkg.com/three@0.128.0/build/three.min.js');
                    await loadScript('https://unpkg.com/three@0.128.0/examples/js/controls/OrbitControls.js');
                    
                    if (typeof THREE === 'undefined') {
                        throw new Error('Three.js failed to load from alternative CDN');
                    }
                    
                    return true;
                } catch (error2) {
                    console.error('All CDN sources failed:', error2);
                    return false;
                }
            }
        }

        // Load script dynamically
        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // Load NetworkTopologyViewer
        async function loadNetworkViewer() {
            updateLoadingStatus('Loading network viewer...', 30);
            
            try {
                await loadScript('assets/webgl-network-viewer.js');
                
                if (typeof NetworkTopologyViewer === 'undefined') {
                    throw new Error('NetworkTopologyViewer class not found');
                }
                
                return true;
            } catch (error) {
                console.error('Failed to load NetworkTopologyViewer:', error);
                return false;
            }
        }

        // Create fallback network display
        function createFallbackDisplay() {
            updateLoadingStatus('Creating fallback display...', 50);
            
            const fallbackContainer = document.getElementById('fallback-network');
            fallbackContainer.style.display = 'grid';
            
            sampleNetworkData.devices.forEach(device => {
                const deviceCard = document.createElement('div');
                deviceCard.className = `device-card ${device.status}`;
                deviceCard.onclick = () => showDeviceInfo(device);
                
                const icon = getDeviceIcon(device.type);
                
                deviceCard.innerHTML = `
                    <div class="device-icon">${icon}</div>
                    <div class="device-name">${device.name}</div>
                    <div class="device-type">${device.type.toUpperCase()}</div>
                    <div class="device-status ${device.status}">${device.status.toUpperCase()}</div>
                `;
                
                fallbackContainer.appendChild(deviceCard);
            });
            
            updateStatistics(sampleNetworkData);
        }

        // Get device icon
        function getDeviceIcon(type) {
            switch (type) {
                case 'router': return '<i class="bi bi-router"></i>';
                case 'switch': return '<i class="bi bi-hdd-network"></i>';
                case 'server': return '<i class="bi bi-server"></i>';
                default: return '<i class="bi bi-pc-display"></i>';
            }
        }

        // Initialize the console with error handling
        async function initializeConsole() {
            try {
                updateLoadingStatus('Starting initialization sequence...', 5);
                
                // Check WebGL support
                if (!checkWebGLSupport()) {
                    throw new Error('WebGL is not supported in this browser');
                }
                
                // Load Three.js
                const threeJSLoaded = await loadThreeJS();
                if (!threeJSLoaded) {
                    useFallback = true;
                    throw new Error('Three.js library failed to load, using fallback mode');
                }
                
                // Load NetworkTopologyViewer
                const viewerLoaded = await loadNetworkViewer();
                if (!viewerLoaded) {
                    useFallback = true;
                    throw new Error('NetworkTopologyViewer failed to load, using fallback mode');
                }
                
                updateLoadingStatus('Creating 3D scene...', 50);
                
                // Create network viewer with futuristic theme
                networkViewer = new NetworkTopologyViewer('webgl-container', {
                    backgroundColor: 0x000000,
                    deviceColors: {
                        router: 0x00d4ff,
                        switch: 0x00ff88,
                        other: 0xff6b35,
                        server: 0x8b5cf6,
                        offline: 0x666666
                    }
                });
                
                updateLoadingStatus('Loading network data...', 70);
                
                // Load sample data
                loadSampleData();
                
                updateLoadingStatus('Setting up event listeners...', 80);
                
                // Setup event listeners
                setupEventListeners();
                
                updateLoadingStatus('Starting auto refresh...', 90);
                
                // Start auto refresh
                startAutoRefresh();
                
                updateLoadingStatus('Finalizing initialization...', 95);
                
                // Update time
                updateTime();
                setInterval(updateTime, 1000);
                
                updateLoadingStatus('Initialization complete!', 100);
                
                // Hide loading screen after a short delay
                setTimeout(() => {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('webgl-status').textContent = useFallback ? 'FALLBACK MODE' : 'WEBGL ACTIVE';
                }, 1000);
                
            } catch (error) {
                console.error('Initialization failed:', error);
                
                if (useFallback) {
                    // Use fallback mode
                    createFallbackDisplay();
                    setupEventListeners();
                    startAutoRefresh();
                    updateTime();
                    setInterval(updateTime, 1000);
                    
                    setTimeout(() => {
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById('webgl-status').textContent = 'FALLBACK MODE';
                    }, 1000);
                } else {
                    showError('Initialization Failed', error.message, error.stack);
                }
            }
        }

        // Show error display
        function showError(title, message, stack = null) {
            const loading = document.getElementById('loading');
            loading.innerHTML = `
                <div class="error-display">
                    <h3>${title}</h3>
                    <p>${message}</p>
                    ${stack ? `<pre>${stack}</pre>` : ''}
                    <button class="btn-3d" onclick="location.reload()" style="margin-top: 15px;">
                        <i class="bi bi-arrow-clockwise"></i> Retry
                    </button>
                </div>
            `;
        }

        // Load sample data
        function loadSampleData() {
            try {
                if (networkViewer && !useFallback) {
                    networkViewer.loadNetworkData(sampleNetworkData);
                }
                updateStatistics(sampleNetworkData);
            } catch (error) {
                console.error('Failed to load sample data:', error);
                throw error;
            }
        }

        // Update statistics
        function updateStatistics(data) {
            document.getElementById('total-devices').textContent = data.devices.length;
            document.getElementById('online-devices').textContent = data.devices.filter(d => d.status === 'online').length;
            document.getElementById('total-connections').textContent = data.connections.length;
            document.getElementById('total-traffic').textContent = Math.floor(Math.random() * 100 + 50);
        }

        // Setup event listeners
        function setupEventListeners() {
            // View mode change
            const viewMode = document.getElementById('view-mode');
            if (viewMode) {
                viewMode.addEventListener('change', function() {
                    const mode = this.value;
                    console.log('View mode changed to:', mode);
                    // Implement view mode switching
                });
            }
        }

        // Show device information
        function showDeviceInfo(device) {
            selectedDevice = device;
            const deviceInfo = document.getElementById('device-info');
            const deviceTitle = document.getElementById('device-title');
            const deviceDetails = document.getElementById('device-details');
            
            const statusClass = device.status === 'online' ? 'text-success' : 'text-danger';
            const statusIcon = device.status === 'online' ? 'bi-check-circle' : 'bi-x-circle';
            
            deviceTitle.textContent = device.name;
            deviceDetails.innerHTML = `
                <div class="device-detail">
                    <span class="label">Type:</span>
                    <span class="value">${device.type.toUpperCase()}</span>
                </div>
                <div class="device-detail">
                    <span class="label">Status:</span>
                    <span class="value ${statusClass}">
                        <i class="bi ${statusIcon}"></i> ${device.status.toUpperCase()}
                    </span>
                </div>
                <div class="device-detail">
                    <span class="label">Position:</span>
                    <span class="value">(${device.x}, ${device.y}, ${device.z})</span>
                </div>
                <div class="device-detail">
                    <span class="label">IP Address:</span>
                    <span class="value">192.168.0.${device.id}</span>
                </div>
            `;
            
            deviceInfo.classList.add('show');
        }

        // Focus on selected device
        function focusOnSelected() {
            if (selectedDevice && networkViewer && !useFallback) {
                networkViewer.focusOnDevice(selectedDevice.id);
            }
        }

        // Simulate device status change
        function simulateStatusChange() {
            if (selectedDevice) {
                const newStatus = selectedDevice.status === 'online' ? 'offline' : 'online';
                selectedDevice.status = newStatus;
                
                if (networkViewer && !useFallback) {
                    networkViewer.updateDeviceStatus(selectedDevice.id, newStatus);
                }
                
                // Update sample data
                const deviceData = sampleNetworkData.devices.find(d => d.id === selectedDevice.id);
                if (deviceData) {
                    deviceData.status = newStatus;
                    updateStatistics(sampleNetworkData);
                    showDeviceInfo(selectedDevice);
                }
                
                // Update fallback display
                if (useFallback) {
                    createFallbackDisplay();
                }
            }
        }

        // Refresh data
        function refreshData() {
            loadSampleData();
        }

        // Reset view
        function resetView() {
            if (networkViewer && !useFallback) {
                networkViewer.setCameraPosition(0, 0, 50);
            }
        }

        // Toggle auto refresh
        function toggleAutoRefresh() {
            const checkbox = document.getElementById('auto-refresh');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                if (checkbox.checked) {
                    startAutoRefresh();
                } else {
                    stopAutoRefresh();
                }
            }
        }

        // Start auto refresh
        function startAutoRefresh() {
            refreshInterval = setInterval(loadSampleData, 10000);
        }

        // Stop auto refresh
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }

        // Update time
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString();
        }

        // Adjust brightness (placeholder)
        function adjustBrightness() {
            console.log('Adjusting brightness...');
        }

        // Adjust contrast (placeholder)
        function adjustContrast() {
            console.log('Adjusting contrast...');
        }

        // Export data (placeholder)
        function exportData() {
            console.log('Exporting data...');
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Set a timeout for initialization
            initializationTimeout = setTimeout(() => {
                if (document.getElementById('loading').style.display !== 'none') {
                    showError('Initialization Timeout', 'The console failed to initialize within the expected time. Please check your internet connection and try again.');
                }
            }, 20000); // 20 second timeout
            
            // Start initialization
            initializeConsole();
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (initializationTimeout) {
                clearTimeout(initializationTimeout);
            }
            stopAutoRefresh();
        });
    </script>
</body>
</html> 