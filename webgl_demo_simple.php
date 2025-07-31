<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLMS - WebGL Network Console</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            color: #00ff41;
            overflow: hidden;
            height: 100vh;
        }

        .console-container {
            display: grid;
            grid-template-areas: 
                "header header header"
                "sidebar main controls"
                "footer footer footer";
            grid-template-rows: 60px 1fr 40px;
            grid-template-columns: 300px 1fr 250px;
            height: 100vh;
            gap: 2px;
            background: #000;
            padding: 2px;
        }

        .console-header {
            grid-area: header;
            background: linear-gradient(90deg, #1a1a2e, #16213e);
            border: 2px solid #00ff41;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
            position: relative;
            overflow: hidden;
        }

        .console-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 65, 0.1), transparent);
            animation: scan 3s linear infinite;
        }

        @keyframes scan {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
            text-shadow: 0 0 10px #00ff41;
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #00ff41;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        .console-sidebar {
            grid-area: sidebar;
            background: rgba(26, 26, 46, 0.9);
            border: 2px solid #00ff41;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: inset 0 0 20px rgba(0, 255, 65, 0.1);
        }

        .panel-3d {
            background: linear-gradient(145deg, #1a1a2e, #16213e);
            border: 1px solid #00ff41;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            transform: perspective(1000px) rotateX(5deg);
        }

        .panel-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
            text-shadow: 0 0 5px #00ff41;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat-item {
            text-align: center;
            padding: 8px;
            background: rgba(0, 255, 65, 0.1);
            border-radius: 4px;
            border: 1px solid rgba(0, 255, 65, 0.3);
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #00ff41;
        }

        .stat-label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
        }

        .console-main {
            grid-area: main;
            background: #000;
            border: 2px solid #00ff41;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }

        #webgl-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            color: #00ff41;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(0, 255, 65, 0.3);
            border-top: 3px solid #00ff41;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .console-controls {
            grid-area: controls;
            background: rgba(26, 26, 46, 0.9);
            border: 2px solid #00ff41;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: inset 0 0 20px rgba(0, 255, 65, 0.1);
        }

        .btn-3d {
            background: linear-gradient(145deg, #1a1a2e, #16213e);
            border: 2px solid #00ff41;
            color: #00ff41;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            transform: perspective(1000px) rotateX(5deg);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-3d:hover {
            background: linear-gradient(145deg, #16213e, #1a1a2e);
            box-shadow: 
                0 6px 12px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                0 0 20px rgba(0, 255, 65, 0.5);
            transform: perspective(1000px) rotateX(5deg) translateY(-2px);
        }

        .btn-3d:active {
            transform: perspective(1000px) rotateX(5deg) translateY(0px);
            box-shadow: 
                0 2px 4px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .btn-3d::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 65, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-3d:hover::before {
            left: 100%;
        }

        .knob {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #1a1a2e, #16213e, #1a1a2e);
            border: 3px solid #00ff41;
            position: relative;
            cursor: pointer;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.3),
                inset 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .knob::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 4px;
            height: 20px;
            background: #00ff41;
            transform: translate(-50%, -50%) rotate(45deg);
            box-shadow: 0 0 5px #00ff41;
        }

        .device-info {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #00ff41;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            display: none;
        }

        .device-info.show {
            display: block;
        }

        .device-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #00ff41;
        }

        .device-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .device-detail .label {
            color: #888;
        }

        .device-detail .value {
            color: #00ff41;
            font-weight: bold;
        }

        .console-footer {
            grid-area: footer;
            background: linear-gradient(90deg, #1a1a2e, #16213e);
            border: 2px solid #00ff41;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            font-size: 12px;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
        }

        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid #ff0000;
            color: #ff0000;
            padding: 15px;
            border-radius: 8px;
            margin: 20px;
            text-align: center;
        }

        .retry-btn {
            background: #ff0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="console-container">
        <div class="console-header">
            <div class="header-title">SLMS NETWORK CONSOLE v2.0</div>
            <div class="header-status">
                <div class="status-dot"></div>
                <span>SYSTEM ONLINE</span>
                <span id="current-time">--:--:--</span>
            </div>
        </div>

        <div class="console-sidebar">
            <div class="panel-3d">
                <div class="panel-title">NETWORK STATS</div>
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
                        <div class="stat-label">Traffic (MB/s)</div>
                    </div>
                </div>
            </div>

            <div class="panel-3d">
                <div class="panel-title">DEVICE INFO</div>
                <div class="device-info" id="device-info">
                    <div class="device-title" id="device-title">Select a device</div>
                    <div class="device-details" id="device-details"></div>
                </div>
            </div>
        </div>

        <div class="console-main">
            <div id="webgl-container">
                <div class="loading-overlay" id="loading">
                    <div class="spinner"></div>
                    <div id="loading-status">Initializing WebGL Console...</div>
                    <div id="loading-progress">0%</div>
                </div>
            </div>
        </div>

        <div class="console-controls">
            <div class="panel-3d">
                <div class="panel-title">CONTROLS</div>
                <button class="btn-3d" onclick="refreshData()">REFRESH DATA</button>
                <button class="btn-3d" onclick="resetView()">RESET VIEW</button>
                <button class="btn-3d" onclick="focusOnSelected()">FOCUS DEVICE</button>
                <button class="btn-3d" onclick="simulateStatusChange()">TOGGLE STATUS</button>
            </div>

            <div class="panel-3d">
                <div class="panel-title">SETTINGS</div>
                <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox" id="auto-refresh" style="accent-color: #00ff41;">
                    <span>Auto Refresh</span>
                </label>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <span>Brightness:</span>
                    <div class="knob" onclick="adjustBrightness()"></div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span>Contrast:</span>
                    <div class="knob" onclick="adjustContrast()"></div>
                </div>
            </div>
        </div>

        <div class="console-footer">
            <span>WebGL Network Topology Viewer</span>
            <span>Three.js Engine v1.0</span>
            <span id="fps-counter">FPS: --</span>
        </div>
    </div>

    <script>
        // Sample network data
        const sampleNetworkData = {
            devices: [
                { id: 1, name: 'Main Router', type: 'router', status: 'online', position: { x: 0, y: 0, z: 0 } },
                { id: 2, name: 'Core Switch', type: 'switch', status: 'online', position: { x: 5, y: 0, z: 0 } },
                { id: 3, name: 'File Server', type: 'server', status: 'online', position: { x: 10, y: 0, z: 0 } },
                { id: 4, name: 'Workstation 1', type: 'other', status: 'online', position: { x: 5, y: 5, z: 0 } },
                { id: 5, name: 'Workstation 2', type: 'other', status: 'offline', position: { x: 5, y: -5, z: 0 } },
                { id: 6, name: 'Network Printer', type: 'other', status: 'online', position: { x: 10, y: 5, z: 0 } }
            ],
            connections: [
                { from: 1, to: 2 },
                { from: 2, to: 3 },
                { from: 2, to: 4 },
                { from: 2, to: 5 },
                { from: 3, to: 6 }
            ]
        };

        let networkViewer = null;
        let selectedDevice = null;
        let refreshInterval = null;
        let frameCount = 0;
        let lastTime = performance.now();

        // Update loading status
        function updateLoadingStatus(message, progress) {
            const status = document.getElementById('loading-status');
            const progressEl = document.getElementById('loading-progress');
            if (status) status.textContent = message;
            if (progressEl) progressEl.textContent = progress + '%';
        }

        // Check WebGL support
        function checkWebGLSupport() {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            return !!gl;
        }

        // Simple 3D renderer fallback
        function createSimpleRenderer() {
            updateLoadingStatus('Creating simple 3D renderer...', 50);
            
            const container = document.getElementById('webgl-container');
            const canvas = document.createElement('canvas');
            canvas.width = container.clientWidth;
            canvas.height = container.clientHeight;
            canvas.style.width = '100%';
            canvas.style.height = '100%';
            canvas.style.background = 'radial-gradient(circle, #1a1a2e 0%, #000 70%)';
            
            const ctx = canvas.getContext('2d');
            container.appendChild(canvas);

            // Draw network topology in 2D
            function drawNetwork() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Draw grid
                ctx.strokeStyle = 'rgba(0, 255, 65, 0.1)';
                ctx.lineWidth = 1;
                for (let i = 0; i < canvas.width; i += 50) {
                    ctx.beginPath();
                    ctx.moveTo(i, 0);
                    ctx.lineTo(i, canvas.height);
                    ctx.stroke();
                }
                for (let i = 0; i < canvas.height; i += 50) {
                    ctx.beginPath();
                    ctx.moveTo(0, i);
                    ctx.lineTo(canvas.width, i);
                    ctx.stroke();
                }

                // Draw connections
                ctx.strokeStyle = 'rgba(0, 255, 65, 0.5)';
                ctx.lineWidth = 2;
                sampleNetworkData.connections.forEach(conn => {
                    const from = sampleNetworkData.devices.find(d => d.id === conn.from);
                    const to = sampleNetworkData.devices.find(d => d.id === conn.to);
                    if (from && to) {
                        const x1 = (from.position.x + 10) * 30 + canvas.width / 2;
                        const y1 = (from.position.y + 10) * 30 + canvas.height / 2;
                        const x2 = (to.position.x + 10) * 30 + canvas.width / 2;
                        const y2 = (to.position.y + 10) * 30 + canvas.height / 2;
                        
                        ctx.beginPath();
                        ctx.moveTo(x1, y1);
                        ctx.lineTo(x2, y2);
                        ctx.stroke();
                    }
                });

                // Draw devices
                sampleNetworkData.devices.forEach(device => {
                    const x = (device.position.x + 10) * 30 + canvas.width / 2;
                    const y = (device.position.y + 10) * 30 + canvas.height / 2;
                    
                    // Device circle
                    ctx.fillStyle = device.status === 'online' ? '#00ff41' : '#ff0000';
                    ctx.beginPath();
                    ctx.arc(x, y, 15, 0, Math.PI * 2);
                    ctx.fill();
                    
                    // Glow effect
                    ctx.shadowColor = device.status === 'online' ? '#00ff41' : '#ff0000';
                    ctx.shadowBlur = 10;
                    ctx.beginPath();
                    ctx.arc(x, y, 15, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.shadowBlur = 0;
                    
                    // Device label
                    ctx.fillStyle = '#00ff41';
                    ctx.font = '12px Courier New';
                    ctx.textAlign = 'center';
                    ctx.fillText(device.name, x, y + 30);
                    ctx.fillText(device.type.toUpperCase(), x, y + 45);
                });
            }

            // Animation loop
            function animate() {
                drawNetwork();
                frameCount++;
                const currentTime = performance.now();
                if (currentTime - lastTime >= 1000) {
                    const fps = Math.round((frameCount * 1000) / (currentTime - lastTime));
                    document.getElementById('fps-counter').textContent = `FPS: ${fps}`;
                    frameCount = 0;
                    lastTime = currentTime;
                }
                requestAnimationFrame(animate);
            }

            // Handle clicks
            canvas.addEventListener('click', (event) => {
                const rect = canvas.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;
                
                sampleNetworkData.devices.forEach(device => {
                    const deviceX = (device.position.x + 10) * 30 + canvas.width / 2;
                    const deviceY = (device.position.y + 10) * 30 + canvas.height / 2;
                    const distance = Math.sqrt((x - deviceX) ** 2 + (y - deviceY) ** 2);
                    
                    if (distance < 20) {
                        showDeviceInfo(device);
                    }
                });
            });

            animate();
            updateLoadingStatus('Simple renderer ready', 100);
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
            }, 1000);
        }

        // Initialize console
        function initializeConsole() {
            updateLoadingStatus('Checking system requirements...', 10);
            
            if (!checkWebGLSupport()) {
                updateLoadingStatus('WebGL not supported, using fallback renderer...', 30);
                createSimpleRenderer();
                return;
            }

            updateLoadingStatus('Loading Three.js library...', 20);
            
            // Try to load Three.js from CDN
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
            script.onload = () => {
                updateLoadingStatus('Three.js loaded, initializing 3D scene...', 40);
                setTimeout(() => {
                    try {
                        createWebGLRenderer();
                    } catch (error) {
                        console.error('WebGL initialization failed:', error);
                        updateLoadingStatus('WebGL failed, switching to fallback...', 60);
                        createSimpleRenderer();
                    }
                }, 500);
            };
            script.onerror = () => {
                updateLoadingStatus('Three.js failed to load, using fallback...', 30);
                createSimpleRenderer();
            };
            document.head.appendChild(script);
        }

        // Create WebGL renderer
        function createWebGLRenderer() {
            const container = document.getElementById('webgl-container');
            
            // Scene setup
            const scene = new THREE.Scene();
            scene.background = new THREE.Color(0x000000);
            scene.fog = new THREE.Fog(0x000000, 50, 200);

            // Camera setup
            const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
            camera.position.set(30, 30, 50);
            camera.lookAt(0, 0, 0);

            // Renderer setup
            const renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 1.2;
            container.appendChild(renderer.domElement);

            // Lighting
            const ambientLight = new THREE.AmbientLight(0x404040, 0.3);
            scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0x00ff41, 1);
            directionalLight.position.set(50, 50, 50);
            directionalLight.castShadow = true;
            directionalLight.shadow.mapSize.width = 2048;
            directionalLight.shadow.mapSize.height = 2048;
            scene.add(directionalLight);

            // Grid
            const gridHelper = new THREE.GridHelper(100, 20, 0x00ff41, 0x004400);
            scene.add(gridHelper);

            // Device colors
            const deviceColors = {
                router: 0x0066ff,
                switch: 0x00ff41,
                server: 0xff6600,
                other: 0x9900ff
            };

            // Create devices
            const devices = [];
            sampleNetworkData.devices.forEach(device => {
                let geometry, material;
                
                switch (device.type) {
                    case 'router':
                        geometry = new THREE.CylinderGeometry(2, 2, 4, 8);
                        break;
                    case 'switch':
                        geometry = new THREE.BoxGeometry(3, 2, 3);
                        break;
                    case 'server':
                        geometry = new THREE.BoxGeometry(4, 3, 2);
                        break;
                    default:
                        geometry = new THREE.SphereGeometry(1.5);
                }

                material = new THREE.MeshPhongMaterial({
                    color: deviceColors[device.type] || deviceColors.other,
                    shininess: 100,
                    specular: 0x444444
                });

                const mesh = new THREE.Mesh(geometry, material);
                mesh.position.set(device.position.x * 5, device.position.y * 5, device.position.z * 5);
                mesh.castShadow = true;
                mesh.receiveShadow = true;
                mesh.userData = { id: device.id, status: device.status };
                
                scene.add(mesh);
                devices.push(mesh);

                // Add label
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = 256;
                canvas.height = 64;
                context.fillStyle = '#000000';
                context.fillRect(0, 0, 256, 64);
                context.fillStyle = '#00ff41';
                context.font = '24px Courier New';
                context.textAlign = 'center';
                context.fillText(device.name, 128, 32);
                context.fillText(device.type.toUpperCase(), 128, 56);

                const texture = new THREE.CanvasTexture(canvas);
                const labelMaterial = new THREE.SpriteMaterial({ map: texture });
                const label = new THREE.Sprite(labelMaterial);
                label.position.set(device.position.x * 5, device.position.y * 5 + 5, device.position.z * 5);
                label.scale.set(5, 1.25, 1);
                scene.add(label);
            });

            // Create connections
            sampleNetworkData.connections.forEach(conn => {
                const from = sampleNetworkData.devices.find(d => d.id === conn.from);
                const to = sampleNetworkData.devices.find(d => d.id === conn.to);
                if (from && to) {
                    const geometry = new THREE.BufferGeometry().setFromPoints([
                        new THREE.Vector3(from.position.x * 5, from.position.y * 5, from.position.z * 5),
                        new THREE.Vector3(to.position.x * 5, to.position.y * 5, to.position.z * 5)
                    ]);
                    const material = new THREE.LineBasicMaterial({ color: 0x00ff41, linewidth: 2 });
                    const line = new THREE.Line(geometry, material);
                    scene.add(line);
                }
            });

            // Animation loop
            function animate() {
                requestAnimationFrame(animate);
                
                // Rotate devices
                devices.forEach(device => {
                    device.rotation.y += 0.01;
                });

                renderer.render(scene, camera);
                
                frameCount++;
                const currentTime = performance.now();
                if (currentTime - lastTime >= 1000) {
                    const fps = Math.round((frameCount * 1000) / (currentTime - lastTime));
                    document.getElementById('fps-counter').textContent = `FPS: ${fps}`;
                    frameCount = 0;
                    lastTime = currentTime;
                }
            }

            // Handle clicks
            const raycaster = new THREE.Raycaster();
            const mouse = new THREE.Vector2();

            renderer.domElement.addEventListener('click', (event) => {
                const rect = renderer.domElement.getBoundingClientRect();
                mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

                raycaster.setFromCamera(mouse, camera);
                const intersects = raycaster.intersectObjects(devices);

                if (intersects.length > 0) {
                    const deviceId = intersects[0].object.userData.id;
                    const device = sampleNetworkData.devices.find(d => d.id === deviceId);
                    if (device) {
                        showDeviceInfo(device);
                    }
                }
            });

            animate();
            updateLoadingStatus('WebGL renderer ready', 100);
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
            }, 1000);
        }

        // Show device information
        function showDeviceInfo(device) {
            selectedDevice = device;
            const deviceInfo = document.getElementById('device-info');
            const deviceTitle = document.getElementById('device-title');
            const deviceDetails = document.getElementById('device-details');
            
            const statusClass = device.status === 'online' ? 'text-success' : 'text-danger';
            const statusIcon = device.status === 'online' ? '●' : '○';
            
            deviceTitle.textContent = device.name;
            deviceDetails.innerHTML = `
                <div class="device-detail">
                    <span class="label">Type:</span>
                    <span class="value">${device.type.toUpperCase()}</span>
                </div>
                <div class="device-detail">
                    <span class="label">Status:</span>
                    <span class="value" style="color: ${device.status === 'online' ? '#00ff41' : '#ff0000'}">
                        ${statusIcon} ${device.status.toUpperCase()}
                    </span>
                </div>
                <div class="device-detail">
                    <span class="label">Position:</span>
                    <span class="value">(${device.position.x.toFixed(1)}, ${device.position.y.toFixed(1)}, ${device.position.z.toFixed(1)})</span>
                </div>
                <div class="device-detail">
                    <span class="label">IP Address:</span>
                    <span class="value">192.168.0.${device.id}</span>
                </div>
            `;
            
            deviceInfo.classList.add('show');
        }

        // Control functions
        function refreshData() {
            updateStatistics(sampleNetworkData);
        }

        function resetView() {
            // Reset view logic would go here
            console.log('Reset view');
        }

        function focusOnSelected() {
            if (selectedDevice) {
                console.log('Focus on device:', selectedDevice.name);
            }
        }

        function simulateStatusChange() {
            if (selectedDevice) {
                selectedDevice.status = selectedDevice.status === 'online' ? 'offline' : 'online';
                updateStatistics(sampleNetworkData);
                showDeviceInfo(selectedDevice);
            }
        }

        function adjustBrightness() {
            console.log('Adjust brightness');
        }

        function adjustContrast() {
            console.log('Adjust contrast');
        }

        // Update statistics
        function updateStatistics(data) {
            document.getElementById('total-devices').textContent = data.devices.length;
            document.getElementById('online-devices').textContent = data.devices.filter(d => d.status === 'online').length;
            document.getElementById('total-connections').textContent = data.connections.length;
            document.getElementById('total-traffic').textContent = Math.floor(Math.random() * 100 + 50);
        }

        // Update time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            document.getElementById('current-time').textContent = timeString;
        }

        // Auto refresh
        function startAutoRefresh() {
            refreshInterval = setInterval(refreshData, 10000);
        }

        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }

        // Event listeners
        document.getElementById('auto-refresh').addEventListener('change', function() {
            if (this.checked) {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        });

        // Initialize
        updateStatistics(sampleNetworkData);
        setInterval(updateTime, 1000);
        initializeConsole();
    </script>
</body>
</html> 