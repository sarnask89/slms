<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLMS v1.2.0 - Rotating 3D Menu System</title>
    
    <!-- Three.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
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

        /* Control Panel */
        .control-panel {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(26, 26, 26, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--accent-green);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 0 20px var(--glow-green);
            min-width: 200px;
        }

        .control-title {
            font-size: 16px;
            font-weight: bold;
            color: var(--accent-green);
            margin-bottom: 15px;
            text-align: center;
        }

        .control-group {
            margin-bottom: 15px;
        }

        .control-group label {
            display: block;
            color: var(--text-secondary);
            font-size: 12px;
            margin-bottom: 5px;
        }

        .control-group input[type="range"] {
            width: 100%;
            background: var(--secondary-bg);
            border: 1px solid var(--accent-green);
            border-radius: 5px;
            height: 6px;
            outline: none;
        }

        .control-group input[type="range"]::-webkit-slider-thumb {
            appearance: none;
            width: 16px;
            height: 16px;
            background: var(--accent-green);
            border-radius: 50%;
            cursor: pointer;
        }

        .control-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .control-btn {
            background: var(--accent-blue);
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            color: white;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .control-btn:hover {
            background: var(--accent-green);
            transform: scale(1.05);
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

        /* Instructions */
        .instructions {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(26, 26, 26, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--accent-orange);
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 0 20px rgba(255, 107, 53, 0.6);
            max-width: 300px;
        }

        .instructions h4 {
            color: var(--accent-orange);
            margin-bottom: 10px;
            font-size: 14px;
        }

        .instructions ul {
            list-style: none;
            padding: 0;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .instructions li {
            margin-bottom: 5px;
            padding-left: 15px;
            position: relative;
        }

        .instructions li::before {
            content: '▶';
            color: var(--accent-orange);
            position: absolute;
            left: 0;
        }
    </style>
</head>
<body>
    <!-- 3D Scene Container -->
    <div id="scene-container">
        <div class="loading-3d" id="loading">
            <div class="loading-spinner"></div>
            <div>Loading Rotating 3D Menu...</div>
        </div>
    </div>

    <!-- 2D UI Overlay -->
    <div class="ui-overlay">
        <!-- Header -->
        <div class="header-3d">
            <div class="header-title">SLMS v1.2.0 - Rotating 3D Menu</div>
        </div>

        <!-- Control Panel -->
        <div class="control-panel">
            <div class="control-title">🎮 Menu Controls</div>
            
            <div class="control-group">
                <label>Rotation Speed</label>
                <input type="range" id="rotation-speed" min="0" max="2" value="0.5" step="0.1">
            </div>
            
            <div class="control-group">
                <label>Menu Radius</label>
                <input type="range" id="menu-radius" min="5" max="15" value="10" step="1">
            </div>
            
            <div class="control-group">
                <label>Item Height</label>
                <input type="range" id="item-height" min="1" max="5" value="2" step="0.5">
            </div>
            
            <div class="control-buttons">
                <button class="control-btn" onclick="toggleRotation()">⏸️ Pause</button>
                <button class="control-btn" onclick="resetView()">🔄 Reset</button>
                <button class="control-btn" onclick="toggleAutoRotate()">🔄 Auto</button>
                <button class="control-btn" onclick="changeMenuStyle()">🎨 Style</button>
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
            <div class="status-item">
                <i class="bi bi-gear"></i>
                <span id="menu-status">Rotating</span>
            </div>
        </div>

        <!-- Instructions -->
        <div class="instructions">
            <h4>🎯 How to Use</h4>
            <ul>
                <li><strong>Mouse:</strong> Click and drag to rotate view</li>
                <li><strong>Scroll:</strong> Zoom in/out</li>
                <li><strong>Click Menu Items:</strong> Navigate to modules</li>
                <li><strong>Keyboard:</strong> 1-8 for quick access</li>
                <li><strong>Escape:</strong> Reset camera view</li>
            </ul>
        </div>
    </div>

    <script>
        // Global variables
        let scene, camera, renderer, controls;
        let menuItems = [];
        let isRotating = true;
        let autoRotate = true;
        let rotationSpeed = 0.5;
        let menuRadius = 10;
        let itemHeight = 2;
        let currentStyle = 0;
        let selectedItem = null;

        // Menu item configurations
        const menuConfigs = [
            {
                title: 'Devices',
                icon: 'bi-hdd-network',
                color: '#00d4ff',
                url: 'modules/devices.php',
                description: 'Device Management'
            },
            {
                title: 'Networks',
                icon: 'bi-wifi',
                color: '#00ff88',
                url: 'modules/networks.php',
                description: 'Network Management'
            },
            {
                title: 'Clients',
                icon: 'bi-people',
                color: '#ff6b35',
                url: 'modules/clients.php',
                description: 'Client Management'
            },
            {
                title: 'Monitoring',
                icon: 'bi-graph-up',
                color: '#8b5cf6',
                url: 'modules/network_monitor.php',
                description: 'System Monitoring'
            },
            {
                title: '3D View',
                icon: 'bi-cube',
                color: '#00d4ff',
                url: 'webgl_demo.php',
                description: '3D Visualization'
            },
            {
                title: 'Settings',
                icon: 'bi-gear',
                color: '#00ff88',
                url: 'modules/settings.php',
                description: 'System Settings'
            },
            {
                title: 'Reports',
                icon: 'bi-file-earmark-text',
                color: '#ff6b35',
                url: 'modules/reports.php',
                description: 'Generate Reports'
            },
            {
                title: 'Admin',
                icon: 'bi-shield-check',
                color: '#8b5cf6',
                url: 'admin_menu_enhanced.php',
                description: 'Admin Panel'
            }
        ];

        // Initialize 3D Scene
        function init3DScene() {
            // Create scene
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x0a0a0a);

            // Create camera
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.set(0, 5, 15);

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
            controls.autoRotate = autoRotate;
            controls.autoRotateSpeed = rotationSpeed;

            // Add lighting
            addLighting();

            // Create rotating menu
            createRotatingMenu();

            // Start animation loop
            animate();

            // Hide loading screen
            document.getElementById('loading').style.display = 'none';
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

        // Create rotating menu items
        function createRotatingMenu() {
            // Clear existing menu items
            menuItems.forEach(item => {
                scene.remove(item.mesh);
                scene.remove(item.text);
            });
            menuItems = [];

            const itemCount = menuConfigs.length;
            const angleStep = (2 * Math.PI) / itemCount;

            menuConfigs.forEach((config, index) => {
                const angle = index * angleStep;
                const x = Math.cos(angle) * menuRadius;
                const z = Math.sin(angle) * menuRadius;

                // Create menu item geometry
                const geometry = new THREE.BoxGeometry(3, itemHeight, 1);
                const material = new THREE.MeshPhongMaterial({ 
                    color: config.color,
                    transparent: true,
                    opacity: 0.8
                });

                const mesh = new THREE.Mesh(geometry, material);
                mesh.position.set(x, 0, z);
                mesh.castShadow = true;
                mesh.receiveShadow = true;

                // Add click event
                mesh.userData = {
                    config: config,
                    index: index,
                    isMenuItem: true
                };

                // Create text for menu item
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = 256;
                canvas.height = 128;

                // Draw text
                context.fillStyle = '#ffffff';
                context.font = 'bold 24px Arial';
                context.textAlign = 'center';
                context.fillText(config.title, 128, 40);
                context.fillText(config.description, 128, 70);

                const texture = new THREE.CanvasTexture(canvas);
                const textMaterial = new THREE.MeshBasicMaterial({ 
                    map: texture,
                    transparent: true,
                    opacity: 0.9
                });

                const textGeometry = new THREE.PlaneGeometry(2.5, 1);
                const textMesh = new THREE.Mesh(textGeometry, textMaterial);
                textMesh.position.set(x, itemHeight + 0.5, z);
                textMesh.lookAt(camera.position);

                // Add to scene
                scene.add(mesh);
                scene.add(textMesh);

                menuItems.push({
                    mesh: mesh,
                    text: textMesh,
                    config: config,
                    index: index
                });
            });
        }

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);

            // Rotate menu items
            if (isRotating) {
                menuItems.forEach((item, index) => {
                    const time = Date.now() * 0.001 * rotationSpeed;
                    const angle = (index * (2 * Math.PI) / menuItems.length) + time;
                    const x = Math.cos(angle) * menuRadius;
                    const z = Math.sin(angle) * menuRadius;

                    item.mesh.position.x = x;
                    item.mesh.position.z = z;
                    item.text.position.x = x;
                    item.text.position.z = z;
                    item.text.lookAt(camera.position);

                    // Add floating animation
                    item.mesh.position.y = Math.sin(time * 2 + index) * 0.5;
                    item.text.position.y = item.mesh.position.y + itemHeight + 0.5;
                });
            }

            // Update controls
            controls.autoRotate = autoRotate;
            controls.autoRotateSpeed = rotationSpeed;
            controls.update();

            renderer.render(scene, camera);
        }

        // Handle window resize
        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        // Control functions
        function toggleRotation() {
            isRotating = !isRotating;
            document.getElementById('menu-status').textContent = isRotating ? 'Rotating' : 'Paused';
        }

        function resetView() {
            camera.position.set(0, 5, 15);
            camera.lookAt(0, 0, 0);
            controls.reset();
        }

        function toggleAutoRotate() {
            autoRotate = !autoRotate;
            controls.autoRotate = autoRotate;
        }

        function changeMenuStyle() {
            currentStyle = (currentStyle + 1) % 3;
            
            switch(currentStyle) {
                case 0: // Box style
                    menuItems.forEach(item => {
                        item.mesh.geometry = new THREE.BoxGeometry(3, itemHeight, 1);
                    });
                    break;
                case 1: // Cylinder style
                    menuItems.forEach(item => {
                        item.mesh.geometry = new THREE.CylinderGeometry(1, 1, itemHeight, 8);
                    });
                    break;
                case 2: // Sphere style
                    menuItems.forEach(item => {
                        item.mesh.geometry = new THREE.SphereGeometry(1.5, 16, 16);
                    });
                    break;
            }
        }

        // Handle mouse clicks
        function onMouseClick(event) {
            const mouse = new THREE.Vector2();
            mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

            const raycaster = new THREE.Raycaster();
            raycaster.setFromCamera(mouse, camera);

            const intersects = raycaster.intersectObjects(scene.children);

            if (intersects.length > 0) {
                const object = intersects[0].object;
                if (object.userData.isMenuItem) {
                    // Navigate to the module
                    window.location.href = object.userData.config.url;
                }
            }
        }

        // Update status bar
        function updateStatusBar() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString();
        }

        // Event listeners
        window.addEventListener('resize', onWindowResize);
        window.addEventListener('click', onMouseClick);

        // Control event listeners
        document.getElementById('rotation-speed').addEventListener('input', function(e) {
            rotationSpeed = parseFloat(e.target.value);
        });

        document.getElementById('menu-radius').addEventListener('input', function(e) {
            menuRadius = parseFloat(e.target.value);
            createRotatingMenu();
        });

        document.getElementById('item-height').addEventListener('input', function(e) {
            itemHeight = parseFloat(e.target.value);
            createRotatingMenu();
        });

        // Keyboard shortcuts
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
                    window.location.href = 'admin_menu_enhanced.php';
                    break;
                case 'Escape':
                    resetView();
                    break;
                case ' ':
                    toggleRotation();
                    break;
            }
        });

        // Initialize everything
        window.addEventListener('load', () => {
            init3DScene();
            updateStatusBar();
            setInterval(updateStatusBar, 1000);
        });
    </script>
</body>
</html> 