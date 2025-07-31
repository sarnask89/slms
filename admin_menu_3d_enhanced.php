<?php
require_once 'config.php';

// Check database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbStatus = "Connected";
} catch(PDOException $e) {
    $dbStatus = "Error: " . $e->getMessage();
}

// Get system statistics
$stats = [];
try {
    // Device count
    $stmt = $pdo->query("SELECT COUNT(*) FROM devices");
    $stats['devices'] = $stmt->fetchColumn();
    
    // Client count
    $stmt = $pdo->query("SELECT COUNT(*) FROM clients");
    $stats['clients'] = $stmt->fetchColumn();
    
    // Network count
    $stmt = $pdo->query("SELECT COUNT(*) FROM networks");
    $stats['networks'] = $stmt->fetchColumn();
    
    // User count
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['users'] = $stmt->fetchColumn();
} catch(PDOException $e) {
    $stats = ['devices' => 0, 'clients' => 0, 'networks' => 0, 'users' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLMS v1.2.0 - Advanced 3D Admin Menu</title>
    
    <!-- Three.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- 3D Button System -->
    <script src="assets/3d-button-system.js"></script>
    
    <style>
        :root {
            --primary-bg: #0a0a0a;
            --secondary-bg: #1a1a1a;
            --accent-blue: #00d4ff;
            --accent-green: #00ff88;
            --accent-orange: #ff6b35;
            --accent-purple: #8b5cf6;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --glow-blue: rgba(0, 212, 255, 0.6);
            --glow-green: rgba(0, 255, 136, 0.6);
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

        /* Header */
        .header-3d {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(26, 26, 26, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--accent-blue);
            border-radius: 15px;
            padding: 15px 30px;
            box-shadow: 0 0 20px var(--glow-blue);
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--accent-blue);
            text-shadow: 0 0 10px var(--accent-blue);
        }

        /* Main Content Area */
        .main-content {
            position: absolute;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
        }

        /* Menu Grid */
        .menu-grid {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(15px);
            border: 2px solid var(--accent-blue);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 0 30px var(--glow-blue);
        }

        .menu-title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: var(--accent-blue);
            margin-bottom: 30px;
            text-shadow: 0 0 15px var(--accent-blue);
        }

        /* Stats Panel */
        .stats-panel {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(15px);
            border: 2px solid var(--accent-green);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 0 30px var(--glow-green);
            height: fit-content;
        }

        .stats-title {
            font-size: 20px;
            font-weight: bold;
            color: var(--accent-green);
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 0 10px var(--accent-green);
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 255, 136, 0.3);
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .stat-value {
            color: var(--accent-green);
            font-size: 18px;
            font-weight: bold;
        }

        /* Status Bar */
        .status-bar {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(26, 26, 26, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--accent-green);
            border-radius: 15px;
            padding: 15px 30px;
            box-shadow: 0 0 20px var(--glow-green);
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
        }

        .status-item i {
            color: var(--accent-green);
        }

        /* Loading Animation */
        .loading-3d {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--accent-blue);
            font-size: 18px;
            text-align: center;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(0, 212, 255, 0.3);
            border-top: 3px solid var(--accent-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .stats-panel {
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                width: 95%;
                top: 80px;
            }
            
            .menu-grid {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- 3D Scene Container -->
    <div id="scene-container">
        <div class="loading-3d" id="loading">
            <div class="loading-spinner"></div>
            <div>Loading 3D Environment...</div>
        </div>
    </div>

    <!-- 2D UI Overlay -->
    <div class="ui-overlay">
        <!-- Header -->
        <div class="header-3d">
            <div class="header-title">SLMS v1.2.0 - Advanced 3D Admin Menu</div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Menu Grid -->
            <div class="menu-grid">
                <div class="menu-title">🎮 Admin Control Center</div>
                <div id="button-container"></div>
            </div>

            <!-- Stats Panel -->
            <div class="stats-panel">
                <div class="stats-title">📊 System Statistics</div>
                <div class="stat-item">
                    <span class="stat-label">Devices</span>
                    <span class="stat-value"><?php echo $stats['devices']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Clients</span>
                    <span class="stat-value"><?php echo $stats['clients']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Networks</span>
                    <span class="stat-value"><?php echo $stats['networks']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Users</span>
                    <span class="stat-value"><?php echo $stats['users']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Database</span>
                    <span class="stat-value"><?php echo $dbStatus; ?></span>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <i class="bi bi-circle-fill"></i>
                <span id="connection-status">Connected</span>
            </div>
            <div class="status-item">
                <i class="bi bi-clock"></i>
                <span id="current-time"></span>
            </div>
            <div class="status-item">
                <i class="bi bi-cpu"></i>
                <span id="system-status">Online</span>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let scene, camera, renderer, controls;
        let menuObjects = [];
        let is3DReady = false;
        let buttonGrid;

        // Initialize 3D Scene
        function init3DScene() {
            // Create scene
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x0a0a0a);

            // Create camera
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.set(0, 5, 10);

            // Create renderer
            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            document.getElementById('scene-container').appendChild(renderer.domElement);

            // Add controls
            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.maxPolarAngle = Math.PI / 2;

            // Add lighting
            addLighting();

            // Add 3D menu elements
            create3DMenuElements();

            // Start animation loop
            animate();

            // Hide loading screen
            document.getElementById('loading').style.display = 'none';
            is3DReady = true;
        }

        // Add lighting to the scene
        function addLighting() {
            // Ambient light
            const ambientLight = new THREE.AmbientLight(0x404040, 0.3);
            scene.add(ambientLight);

            // Directional light
            const directionalLight = new THREE.DirectionalLight(0x00d4ff, 0.8);
            directionalLight.position.set(10, 10, 5);
            directionalLight.castShadow = true;
            directionalLight.shadow.mapSize.width = 2048;
            directionalLight.shadow.mapSize.height = 2048;
            scene.add(directionalLight);

            // Point lights for accent
            const pointLight1 = new THREE.PointLight(0x00ff88, 0.5, 20);
            pointLight1.position.set(-5, 5, -5);
            scene.add(pointLight1);

            const pointLight2 = new THREE.PointLight(0xff6b35, 0.5, 20);
            pointLight2.position.set(5, 5, -5);
            scene.add(pointLight2);
        }

        // Create 3D menu elements
        function create3DMenuElements() {
            // Create floating geometric shapes
            const geometries = [
                new THREE.BoxGeometry(2, 2, 2),
                new THREE.SphereGeometry(1.5, 32, 32),
                new THREE.CylinderGeometry(1, 1, 3, 32),
                new THREE.TorusGeometry(1.5, 0.5, 16, 100)
            ];

            const materials = [
                new THREE.MeshPhongMaterial({ color: 0x00d4ff, transparent: true, opacity: 0.8 }),
                new THREE.MeshPhongMaterial({ color: 0x00ff88, transparent: true, opacity: 0.8 }),
                new THREE.MeshPhongMaterial({ color: 0xff6b35, transparent: true, opacity: 0.8 }),
                new THREE.MeshPhongMaterial({ color: 0x8b5cf6, transparent: true, opacity: 0.8 })
            ];

            for (let i = 0; i < 8; i++) {
                const geometry = geometries[i % geometries.length];
                const material = materials[i % materials.length];
                const mesh = new THREE.Mesh(geometry, material);
                
                mesh.position.set(
                    (Math.random() - 0.5) * 20,
                    (Math.random() - 0.5) * 10 + 5,
                    (Math.random() - 0.5) * 20
                );
                
                mesh.castShadow = true;
                mesh.receiveShadow = true;
                
                // Add rotation animation
                mesh.userData = {
                    rotationSpeed: {
                        x: (Math.random() - 0.5) * 0.02,
                        y: (Math.random() - 0.5) * 0.02,
                        z: (Math.random() - 0.5) * 0.02
                    }
                };
                
                scene.add(mesh);
                menuObjects.push(mesh);
            }

            // Add particle system
            createParticleSystem();
        }

        // Create particle system
        function createParticleSystem() {
            const particleCount = 1000;
            const particles = new THREE.BufferGeometry();
            const positions = new Float32Array(particleCount * 3);
            const colors = new Float32Array(particleCount * 3);

            for (let i = 0; i < particleCount * 3; i += 3) {
                positions[i] = (Math.random() - 0.5) * 50;
                positions[i + 1] = (Math.random() - 0.5) * 50;
                positions[i + 2] = (Math.random() - 0.5) * 50;

                colors[i] = Math.random() * 0.5 + 0.5;
                colors[i + 1] = Math.random() * 0.5 + 0.5;
                colors[i + 2] = 1;
            }

            particles.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            particles.setAttribute('color', new THREE.BufferAttribute(colors, 3));

            const particleMaterial = new THREE.PointsMaterial({
                size: 0.1,
                vertexColors: true,
                transparent: true,
                opacity: 0.6
            });

            const particleSystem = new THREE.Points(particles, particleMaterial);
            scene.add(particleSystem);
            menuObjects.push(particleSystem);
        }

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);

            // Animate 3D objects
            menuObjects.forEach(obj => {
                if (obj.userData.rotationSpeed) {
                    obj.rotation.x += obj.userData.rotationSpeed.x;
                    obj.rotation.y += obj.userData.rotationSpeed.y;
                    obj.rotation.z += obj.userData.rotationSpeed.z;
                }
            });

            controls.update();
            renderer.render(scene, camera);
        }

        // Handle window resize
        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        // Create 3D buttons
        function create3DButtons() {
            const container = document.getElementById('button-container');
            buttonGrid = new ButtonGrid3D(container, { columns: 3, gap: 20 });

            // Define button configurations
            const buttonConfigs = [
                {
                    text: 'Devices',
                    icon: 'bi-hdd-network',
                    color: '#00d4ff',
                    onClick: () => window.location.href = 'modules/devices.php'
                },
                {
                    text: 'Networks',
                    icon: 'bi-wifi',
                    color: '#00ff88',
                    onClick: () => window.location.href = 'modules/networks.php'
                },
                {
                    text: 'Clients',
                    icon: 'bi-people',
                    color: '#ff6b35',
                    onClick: () => window.location.href = 'modules/clients.php'
                },
                {
                    text: 'Monitoring',
                    icon: 'bi-graph-up',
                    color: '#8b5cf6',
                    onClick: () => window.location.href = 'modules/network_monitor.php'
                },
                {
                    text: '3D View',
                    icon: 'bi-cube',
                    color: '#00d4ff',
                    onClick: () => window.location.href = 'webgl_demo.php'
                },
                {
                    text: 'Settings',
                    icon: 'bi-gear',
                    color: '#00ff88',
                    onClick: () => window.location.href = 'modules/settings.php'
                },
                {
                    text: 'Reports',
                    icon: 'bi-file-earmark-text',
                    color: '#ff6b35',
                    onClick: () => window.location.href = 'modules/reports.php'
                },
                {
                    text: 'User Management',
                    icon: 'bi-person-gear',
                    color: '#8b5cf6',
                    onClick: () => window.location.href = 'modules/users.php'
                },
                {
                    text: 'System Logs',
                    icon: 'bi-journal-text',
                    color: '#00d4ff',
                    onClick: () => window.location.href = 'modules/logs.php'
                },
                {
                    text: 'Backup',
                    icon: 'bi-cloud-arrow-up',
                    color: '#00ff88',
                    onClick: () => window.location.href = 'modules/backup.php'
                },
                {
                    text: 'Security',
                    icon: 'bi-shield-check',
                    color: '#ff6b35',
                    onClick: () => window.location.href = 'modules/security.php'
                },
                {
                    text: 'Help',
                    icon: 'bi-question-circle',
                    color: '#8b5cf6',
                    onClick: () => window.location.href = 'modules/help.php'
                }
            ];

            // Create buttons
            buttonConfigs.forEach(config => {
                buttonGrid.addButton({
                    text: config.text,
                    icon: config.icon,
                    color: config.color,
                    hoverColor: '#00ff88',
                    activeColor: '#ff6b35',
                    onClick: config.onClick,
                    size: { width: 180, height: 80 }
                });
            });
        }

        // Update status bar
        function updateStatusBar() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString();
        }

        // Initialize everything
        window.addEventListener('load', () => {
            init3DScene();
            create3DButtons();
            updateStatusBar();
            setInterval(updateStatusBar, 1000);
        });

        window.addEventListener('resize', onWindowResize);

        // Add keyboard shortcuts
        document.addEventListener('keydown', (event) => {
            switch(event.key) {
                case '1':
                    window.location.href = 'modules/devices.php';
                    break;
                case '2':
                    window.location.href = 'modules/networks.php';
                    break;
                case '3':
                    window.location.href = 'modules/clients.php';
                    break;
                case '4':
                    window.location.href = 'modules/network_monitor.php';
                    break;
                case '5':
                    window.location.href = 'webgl_demo.php';
                    break;
                case '6':
                    window.location.href = 'modules/settings.php';
                    break;
                case '7':
                    window.location.href = 'modules/reports.php';
                    break;
                case '8':
                    window.location.href = 'modules/users.php';
                    break;
                case 'Escape':
                    // Reset camera view
                    camera.position.set(0, 5, 10);
                    camera.lookAt(0, 0, 0);
                    break;
            }
        });
    </script>
</body>
</html> 