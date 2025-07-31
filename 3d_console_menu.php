<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLMS v1.2.0 - 3D Console Menu Interface</title>
    
    <!-- Three.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-bg: #0a0a0a;
            --secondary-bg: #1a1a1a;
            --console-blue: #00d4ff;
            --console-green: #00ff88;
            --console-orange: #ff6b35;
            --console-purple: #8b5cf6;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --glow-blue: rgba(0, 212, 255, 0.6);
            --glow-green: rgba(0, 255, 136, 0.6);
            --console-dark: #0f0f0f;
            --console-border: #333333;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--primary-bg);
            color: var(--text-primary);
            font-family: 'Courier New', monospace;
            overflow: hidden;
            height: 100vh;
        }

        /* 3D Scene Container */
        #scene-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1;
        }

        /* 2D UI Overlay */
        .ui-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 10;
            pointer-events: none;
        }

        .ui-overlay > * {
            pointer-events: auto;
        }

        /* Console Header */
        .console-header {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--console-dark), var(--secondary-bg));
            border: 2px solid var(--console-blue);
            border-radius: 15px;
            padding: 20px 40px;
            box-shadow: 0 0 30px var(--glow-blue), inset 0 0 20px rgba(0, 212, 255, 0.1);
            text-align: center;
        }

        .console-title {
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: var(--console-blue);
            text-shadow: 0 0 15px var(--console-blue);
            margin-bottom: 5px;
        }

        .console-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            letter-spacing: 2px;
        }

        /* Console Status Panel */
        .console-status {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, var(--console-dark), var(--secondary-bg));
            border: 2px solid var(--console-green);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 0 30px var(--glow-green), inset 0 0 20px rgba(0, 255, 136, 0.1);
            min-width: 250px;
        }

        .status-title {
            font-size: 16px;
            font-weight: bold;
            color: var(--console-green);
            margin-bottom: 15px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .status-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .status-item {
            text-align: center;
            padding: 10px;
            background: rgba(0, 255, 136, 0.1);
            border-radius: 8px;
            border: 1px solid var(--console-green);
        }

        .status-value {
            font-size: 20px;
            font-weight: bold;
            color: var(--console-green);
            margin-bottom: 5px;
        }

        .status-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Console Footer */
        .console-footer {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--console-dark), var(--secondary-bg));
            border: 2px solid var(--console-orange);
            border-radius: 15px;
            padding: 15px 30px;
            box-shadow: 0 0 30px rgba(255, 107, 53, 0.6), inset 0 0 20px rgba(255, 107, 53, 0.1);
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .footer-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
        }

        .footer-item i {
            color: var(--console-orange);
        }

        /* Loading Animation */
        .loading-console {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--console-blue);
            font-size: 18px;
            text-align: center;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(0, 212, 255, 0.3);
            border-top: 3px solid var(--console-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Console Instructions */
        .console-instructions {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, var(--console-dark), var(--secondary-bg));
            border: 2px solid var(--console-purple);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 0 30px rgba(139, 92, 246, 0.6), inset 0 0 20px rgba(139, 92, 246, 0.1);
            max-width: 300px;
        }

        .instructions-title {
            color: var(--console-purple);
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .instructions-list {
            list-style: none;
            padding: 0;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .instructions-list li {
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }

        .instructions-list li::before {
            content: '▶';
            color: var(--console-purple);
            position: absolute;
            left: 0;
        }

        /* Console Menu Grid */
        .console-menu-grid {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            max-width: 800px;
            width: 100%;
            padding: 20px;
        }

        .console-button {
            background: linear-gradient(135deg, var(--console-dark), var(--secondary-bg));
            border: 2px solid var(--console-blue);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.3), inset 0 0 20px rgba(0, 212, 255, 0.05);
            position: relative;
            overflow: hidden;
        }

        .console-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px var(--glow-blue), inset 0 0 30px rgba(0, 212, 255, 0.1);
            border-color: var(--console-green);
        }

        .console-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 212, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .console-button:hover::before {
            left: 100%;
        }

        .button-icon {
            font-size: 36px;
            color: var(--console-blue);
            margin-bottom: 15px;
            text-shadow: 0 0 10px var(--console-blue);
        }

        .button-title {
            font-size: 16px;
            font-weight: bold;
            color: var(--text-primary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .button-description {
            font-size: 12px;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .console-menu-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .console-menu-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .console-header {
                padding: 15px 25px;
            }
            
            .console-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- 3D Scene Container -->
    <div id="scene-container">
        <div class="loading-console" id="loading">
            <div class="loading-spinner"></div>
            <div>Initializing Console Interface...</div>
        </div>
    </div>

    <!-- 2D UI Overlay -->
    <div class="ui-overlay">
        <!-- Console Header -->
        <div class="console-header">
            <div class="console-title">SLMS v1.2.0</div>
            <div class="console-subtitle">System Management Console</div>
        </div>

        <!-- Console Status Panel -->
        <div class="console-status">
            <div class="status-title">System Status</div>
            <div class="status-grid">
                <div class="status-item">
                    <div class="status-value" id="device-count">0</div>
                    <div class="status-label">Devices</div>
                </div>
                <div class="status-item">
                    <div class="status-value" id="client-count">0</div>
                    <div class="status-label">Clients</div>
                </div>
                <div class="status-item">
                    <div class="status-value" id="network-count">0</div>
                    <div class="status-label">Networks</div>
                </div>
                <div class="status-item">
                    <div class="status-value" id="user-count">0</div>
                    <div class="status-label">Users</div>
                </div>
            </div>
        </div>

        <!-- Console Menu Grid -->
        <div class="console-menu-grid">
            <div class="console-button" onclick="navigateTo('modules/devices.php')">
                <div class="button-icon">🖥️</div>
                <div class="button-title">Device Management</div>
                <div class="button-description">Manage network devices, monitor status, and configure settings</div>
            </div>

            <div class="console-button" onclick="navigateTo('modules/networks.php')">
                <div class="button-icon">🌐</div>
                <div class="button-title">Network Management</div>
                <div class="button-description">Configure networks, monitor traffic, and manage topology</div>
            </div>

            <div class="console-button" onclick="navigateTo('modules/clients.php')">
                <div class="button-icon">👥</div>
                <div class="button-title">Client Management</div>
                <div class="button-description">Manage client accounts, billing, and service packages</div>
            </div>

            <div class="console-button" onclick="navigateTo('modules/network_monitor.php')">
                <div class="button-icon">📊</div>
                <div class="button-title">System Monitoring</div>
                <div class="button-description">Real-time monitoring, alerts, and performance analytics</div>
            </div>

            <div class="console-button" onclick="navigateTo('webgl_demo.php')">
                <div class="button-icon">🎮</div>
                <div class="button-title">3D Visualization</div>
                <div class="button-description">Interactive 3D network visualization and exploration</div>
            </div>

            <div class="console-button" onclick="navigateTo('modules/settings.php')">
                <div class="button-icon">⚙️</div>
                <div class="button-title">System Settings</div>
                <div class="button-description">Configure system preferences, security, and user access</div>
            </div>

            <div class="console-button" onclick="navigateTo('modules/reports.php')">
                <div class="button-icon">📋</div>
                <div class="button-title">Reports & Analytics</div>
                <div class="button-description">Generate reports, analyze data, and export statistics</div>
            </div>

            <div class="console-button" onclick="navigateTo('admin_menu_enhanced.php')">
                <div class="button-icon">🔐</div>
                <div class="button-title">Admin Panel</div>
                <div class="button-description">Advanced administration tools and system management</div>
            </div>
        </div>

        <!-- Console Footer -->
        <div class="console-footer">
            <div class="footer-item">
                <i class="bi bi-circle-fill"></i>
                <span id="connection-status">Connected</span>
            </div>
            <div class="footer-item">
                <i class="bi bi-clock"></i>
                <span id="current-time"></span>
            </div>
            <div class="footer-item">
                <i class="bi bi-cpu"></i>
                <span id="system-status">Online</span>
            </div>
            <div class="footer-item">
                <i class="bi bi-shield-check"></i>
                <span id="security-status">Secure</span>
            </div>
        </div>

        <!-- Console Instructions -->
        <div class="console-instructions">
            <div class="instructions-title">Console Commands</div>
            <ul class="instructions-list">
                <li><strong>Click Buttons:</strong> Navigate to modules</li>
                <li><strong>Keyboard:</strong> 1-8 for quick access</li>
                <li><strong>Status Panel:</strong> Real-time system info</li>
                <li><strong>Footer:</strong> System status indicators</li>
            </ul>
        </div>
    </div>

    <script>
        // Global variables
        let scene, camera, renderer;
        let consoleButtons = [];
        let isInitialized = false;

        // Console button configurations
        const consoleConfigs = [
            {
                title: 'Device Management',
                icon: '🖥️',
                color: '#00d4ff',
                url: 'modules/devices.php',
                description: 'Manage network devices'
            },
            {
                title: 'Network Management',
                icon: '🌐',
                color: '#00ff88',
                url: 'modules/networks.php',
                description: 'Configure networks'
            },
            {
                title: 'Client Management',
                icon: '👥',
                color: '#ff6b35',
                url: 'modules/clients.php',
                description: 'Manage client accounts'
            },
            {
                title: 'System Monitoring',
                icon: '📊',
                color: '#8b5cf6',
                url: 'modules/network_monitor.php',
                description: 'Real-time monitoring'
            },
            {
                title: '3D Visualization',
                icon: '🎮',
                color: '#00d4ff',
                url: 'webgl_demo.php',
                description: 'Interactive 3D view'
            },
            {
                title: 'System Settings',
                icon: '⚙️',
                color: '#00ff88',
                url: 'modules/settings.php',
                description: 'Configure system'
            },
            {
                title: 'Reports & Analytics',
                icon: '📋',
                color: '#ff6b35',
                url: 'modules/reports.php',
                description: 'Generate reports'
            },
            {
                title: 'Admin Panel',
                icon: '🔐',
                color: '#8b5cf6',
                url: 'admin_menu_enhanced.php',
                description: 'Advanced admin tools'
            }
        ];

        // Initialize 3D Scene
        function init3DScene() {
            // Create scene
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x0a0a0a);

            // Create camera
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.set(0, 0, 5);

            // Create renderer
            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            document.getElementById('scene-container').appendChild(renderer.domElement);

            // Add lighting
            addLighting();

            // Create console background
            createConsoleBackground();

            // Start animation loop
            animate();

            // Hide loading screen
            document.getElementById('loading').style.display = 'none';
            isInitialized = true;
        }

        // Add lighting to the scene
        function addLighting() {
            // Ambient light
            const ambientLight = new THREE.AmbientLight(0x404040, 0.4);
            scene.add(ambientLight);

            // Directional light
            const directionalLight = new THREE.DirectionalLight(0x00d4ff, 0.6);
            directionalLight.position.set(5, 5, 5);
            directionalLight.castShadow = true;
            scene.add(directionalLight);

            // Point lights for accent
            const pointLight1 = new THREE.PointLight(0x00ff88, 0.3, 10);
            pointLight1.position.set(-3, 2, 3);
            scene.add(pointLight1);

            const pointLight2 = new THREE.PointLight(0xff6b35, 0.3, 10);
            pointLight2.position.set(3, 2, -3);
            scene.add(pointLight2);
        }

        // Create console background elements
        function createConsoleBackground() {
            // Create a subtle grid pattern
            const gridHelper = new THREE.GridHelper(20, 20, 0x333333, 0x222222);
            gridHelper.position.y = -2;
            scene.add(gridHelper);

            // Add some floating geometric elements for visual interest
            const geometries = [
                new THREE.BoxGeometry(0.5, 0.5, 0.5),
                new THREE.SphereGeometry(0.3, 16, 16),
                new THREE.CylinderGeometry(0.2, 0.2, 0.8, 8),
                new THREE.TorusGeometry(0.3, 0.1, 8, 16)
            ];

            const materials = [
                new THREE.MeshPhongMaterial({ color: 0x00d4ff, transparent: true, opacity: 0.3 }),
                new THREE.MeshPhongMaterial({ color: 0x00ff88, transparent: true, opacity: 0.3 }),
                new THREE.MeshPhongMaterial({ color: 0xff6b35, transparent: true, opacity: 0.3 }),
                new THREE.MeshPhongMaterial({ color: 0x8b5cf6, transparent: true, opacity: 0.3 })
            ];

            for (let i = 0; i < 8; i++) {
                const geometry = geometries[i % geometries.length];
                const material = materials[i % materials.length];
                const mesh = new THREE.Mesh(geometry, material);
                
                mesh.position.set(
                    (Math.random() - 0.5) * 10,
                    (Math.random() - 0.5) * 5 + 2,
                    (Math.random() - 0.5) * 10 - 5
                );
                
                mesh.rotation.set(
                    Math.random() * Math.PI,
                    Math.random() * Math.PI,
                    Math.random() * Math.PI
                );
                
                scene.add(mesh);
                consoleButtons.push(mesh);
            }
        }

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);

            // Subtle rotation of background elements
            consoleButtons.forEach((button, index) => {
                button.rotation.x += 0.005;
                button.rotation.y += 0.01;
                button.rotation.z += 0.003;
            });

            renderer.render(scene, camera);
        }

        // Handle window resize
        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        // Navigation function
        function navigateTo(url) {
            // Add click effect
            const button = event.currentTarget;
            button.style.transform = 'translateY(-2px) scale(0.98)';
            button.style.boxShadow = '0 5px 15px var(--glow-blue)';
            
            setTimeout(() => {
                button.style.transform = '';
                button.style.boxShadow = '';
                window.location.href = url;
            }, 200);
        }

        // Update status bar
        function updateStatusBar() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString();
        }

        // Simulate system statistics
        function updateSystemStats() {
            document.getElementById('device-count').textContent = Math.floor(Math.random() * 50) + 25;
            document.getElementById('client-count').textContent = Math.floor(Math.random() * 200) + 100;
            document.getElementById('network-count').textContent = Math.floor(Math.random() * 20) + 10;
            document.getElementById('user-count').textContent = Math.floor(Math.random() * 10) + 5;
        }

        // Event listeners
        window.addEventListener('resize', onWindowResize);

        // Keyboard shortcuts
        document.addEventListener('keydown', (event) => {
            switch(event.key) {
                case '1':
                    navigateTo('modules/devices.php');
                    break;
                case '2':
                    navigateTo('modules/networks.php');
                    break;
                case '3':
                    navigateTo('modules/clients.php');
                    break;
                case '4':
                    navigateTo('modules/network_monitor.php');
                    break;
                case '5':
                    navigateTo('webgl_demo.php');
                    break;
                case '6':
                    navigateTo('modules/settings.php');
                    break;
                case '7':
                    navigateTo('modules/reports.php');
                    break;
                case '8':
                    navigateTo('admin_menu_enhanced.php');
                    break;
            }
        });

        // Initialize everything
        window.addEventListener('load', () => {
            init3DScene();
            updateStatusBar();
            updateSystemStats();
            
            // Update status every second
            setInterval(updateStatusBar, 1000);
            
            // Update stats every 30 seconds
            setInterval(updateSystemStats, 30000);
        });
    </script>
</body>
</html> 