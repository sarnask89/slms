<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLMS v1.2.0 - Enhanced 3D Menu Framework</title>
    
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
            --accent-gold: #ffd700;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --glow-blue: rgba(0, 212, 255, 0.6);
            --glow-green: rgba(0, 255, 136, 0.6);
            --glow-gold: rgba(255, 215, 0, 0.6);
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
            border: 2px solid var(--accent-gold);
            border-radius: 15px;
            padding: 15px 30px;
            box-shadow: 0 0 20px var(--glow-gold);
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--accent-gold);
            text-shadow: 0 0 10px var(--accent-gold);
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
            color: var(--accent-gold);
            font-size: 18px;
            text-align: center;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 215, 0, 0.3);
            border-top: 3px solid var(--accent-gold);
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
            <div>Loading Enhanced 3D Menu Framework...</div>
        </div>
    </div>

    <!-- 2D UI Overlay -->
    <div class="ui-overlay">
        <!-- Header -->
        <div class="header-3d">
            <div class="header-title">SLMS v1.2.0 - Enhanced 3D Framework</div>
        </div>

        <!-- Control Panel -->
        <div class="control-panel">
            <div class="control-title">🎮 Controls</div>
            <div class="control-buttons">
                <button class="control-btn" onclick="resetView()">🔄 Reset</button>
                <button class="control-btn" onclick="toggleAutoRotate()">🔄 Auto</button>
                <button class="control-btn" onclick="changeTexture()">🎨 Texture</button>
                <button class="control-btn" onclick="toggleParticles()">✨ Particles</button>
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
                <span id="menu-status">Enhanced</span>
            </div>
        </div>

        <!-- Instructions -->
        <div class="instructions">
            <h4>🎯 How to Use</h4>
            <ul>
                <li><strong>Mouse:</strong> Click and drag to rotate view</li>
                <li><strong>Scroll:</strong> Zoom in/out</li>
                <li><strong>Click 3D Objects:</strong> Navigate to modules</li>
                <li><strong>Keyboard:</strong> 1-8 for quick access</li>
                <li><strong>Escape:</strong> Reset camera view</li>
            </ul>
        </div>
    </div>

    <script>
        // Global variables
        let scene, camera, renderer, controls;
        let menuObjects = [];
        let particles = [];
        let autoRotate = true;
        let showParticles = true;
        let currentTexture = 0;
        let clock = new THREE.Clock();

        // Menu item configurations
        const menuConfigs = [
            {
                title: 'Devices',
                icon: 'bi-hdd-network',
                color: '#00d4ff',
                url: 'modules/devices.php',
                description: 'Device Management',
                position: [-8, 0, 0],
                geometry: 'box'
            },
            {
                title: 'Networks',
                icon: 'bi-wifi',
                color: '#00ff88',
                url: 'modules/networks.php',
                description: 'Network Management',
                position: [-4, 0, 0],
                geometry: 'sphere'
            },
            {
                title: 'Clients',
                icon: 'bi-people',
                color: '#ff6b35',
                url: 'modules/clients.php',
                description: 'Client Management',
                position: [0, 0, 0],
                geometry: 'cylinder'
            },
            {
                title: 'Monitoring',
                icon: 'bi-graph-up',
                color: '#8b5cf6',
                url: 'modules/network_monitor.php',
                description: 'System Monitoring',
                position: [4, 0, 0],
                geometry: 'torus'
            },
            {
                title: '3D View',
                icon: 'bi-cube',
                color: '#00d4ff',
                url: 'webgl_demo.php',
                description: '3D Visualization',
                position: [8, 0, 0],
                geometry: 'octahedron'
            },
            {
                title: 'Settings',
                icon: 'bi-gear',
                color: '#00ff88',
                url: 'modules/settings.php',
                description: 'System Settings',
                position: [-6, -4, 0],
                geometry: 'box'
            },
            {
                title: 'Reports',
                icon: 'bi-file-earmark-text',
                color: '#ff6b35',
                url: 'modules/reports.php',
                description: 'Generate Reports',
                position: [0, -4, 0],
                geometry: 'sphere'
            },
            {
                title: 'Admin',
                icon: 'bi-shield-check',
                color: '#8b5cf6',
                url: 'admin_menu_enhanced.php',
                description: 'Admin Panel',
                position: [6, -4, 0],
                geometry: 'cylinder'
            }
        ];

        // Texture patterns
        const texturePatterns = [
            'metallic',
            'wooden',
            'crystal',
            'neon',
            'stone'
        ];

        // Initialize 3D Scene
        function init3DScene() {
            // Create scene
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x0a0a0a);

            // Create camera
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.set(0, 8, 15);

            // Create renderer
            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 1.2;
            document.getElementById('scene-container').appendChild(renderer.domElement);

            // Add controls
            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.autoRotate = autoRotate;
            controls.autoRotateSpeed = 0.5;

            // Add lighting
            addLighting();

            // Create particle system
            createParticleSystem();

            // Create menu objects
            createMenuObjects();

            // Start animation loop
            animate();

            // Hide loading screen
            document.getElementById('loading').style.display = 'none';
        }

        // Add lighting to the scene
        function addLighting() {
            // Ambient light
            const ambientLight = new THREE.AmbientLight(0x404040, 0.4);
            scene.add(ambientLight);

            // Directional light
            const directionalLight = new THREE.DirectionalLight(0x00d4ff, 1.0);
            directionalLight.position.set(10, 10, 5);
            directionalLight.castShadow = true;
            directionalLight.shadow.mapSize.width = 2048;
            directionalLight.shadow.mapSize.height = 2048;
            scene.add(directionalLight);

            // Point lights for accent
            const pointLight1 = new THREE.PointLight(0x00ff88, 0.8, 20);
            pointLight1.position.set(-5, 5, -5);
            scene.add(pointLight1);

            const pointLight2 = new THREE.PointLight(0xff6b35, 0.8, 20);
            pointLight2.position.set(5, 5, -5);
            scene.add(pointLight2);

            const pointLight3 = new THREE.PointLight(0x8b5cf6, 0.6, 15);
            pointLight3.position.set(0, -5, 5);
            scene.add(pointLight3);
        }

        // Create particle system
        function createParticleSystem() {
            const particleCount = 1000;
            const particleGeometry = new THREE.BufferGeometry();
            const positions = new Float32Array(particleCount * 3);
            const colors = new Float32Array(particleCount * 3);
            const sizes = new Float32Array(particleCount);

            for (let i = 0; i < particleCount * 3; i += 3) {
                positions[i] = (Math.random() - 0.5) * 50;
                positions[i + 1] = (Math.random() - 0.5) * 50;
                positions[i + 2] = (Math.random() - 0.5) * 50;

                colors[i] = Math.random() * 0.5 + 0.5;
                colors[i + 1] = Math.random() * 0.5 + 0.5;
                colors[i + 2] = 1;

                sizes[i / 3] = Math.random() * 3 + 1;
            }

            particleGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            particleGeometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
            particleGeometry.setAttribute('size', new THREE.BufferAttribute(sizes, 1));

            const particleMaterial = new THREE.PointsMaterial({
                size: 2,
                vertexColors: true,
                transparent: true,
                opacity: 0.8,
                blending: THREE.AdditiveBlending
            });

            const particleSystem = new THREE.Points(particleGeometry, particleMaterial);
            scene.add(particleSystem);
            particles.push(particleSystem);
        }

        // Create beautiful materials
        function createMaterial(color, pattern = 'metallic') {
            let material;
            
            switch(pattern) {
                case 'metallic':
                    material = new THREE.MeshPhongMaterial({
                        color: color,
                        shininess: 100,
                        specular: 0x444444,
                        transparent: true,
                        opacity: 0.9
                    });
                    break;
                case 'wooden':
                    material = new THREE.MeshPhongMaterial({
                        color: color,
                        shininess: 30,
                        specular: 0x222222,
                        transparent: true,
                        opacity: 0.8
                    });
                    break;
                case 'crystal':
                    material = new THREE.MeshPhongMaterial({
                        color: color,
                        shininess: 200,
                        specular: 0xffffff,
                        transparent: true,
                        opacity: 0.7,
                        side: THREE.DoubleSide
                    });
                    break;
                case 'neon':
                    material = new THREE.MeshPhongMaterial({
                        color: color,
                        shininess: 150,
                        specular: 0xffffff,
                        transparent: true,
                        opacity: 0.6,
                        emissive: color,
                        emissiveIntensity: 0.2
                    });
                    break;
                case 'stone':
                    material = new THREE.MeshPhongMaterial({
                        color: color,
                        shininess: 10,
                        specular: 0x111111,
                        transparent: true,
                        opacity: 0.9
                    });
                    break;
                default:
                    material = new THREE.MeshPhongMaterial({
                        color: color,
                        shininess: 100,
                        transparent: true,
                        opacity: 0.8
                    });
            }
            
            return material;
        }

        // Create menu objects
        function createMenuObjects() {
            // Clear existing objects
            menuObjects.forEach(obj => {
                scene.remove(obj.mesh);
                scene.remove(obj.text);
                if (obj.glow) scene.remove(obj.glow);
            });
            menuObjects = [];

            menuConfigs.forEach((config, index) => {
                // Create geometry based on type
                let geometry;
                switch(config.geometry) {
                    case 'sphere':
                        geometry = new THREE.SphereGeometry(1.5, 32, 32);
                        break;
                    case 'cylinder':
                        geometry = new THREE.CylinderGeometry(1, 1, 3, 16);
                        break;
                    case 'torus':
                        geometry = new THREE.TorusGeometry(1.5, 0.5, 16, 32);
                        break;
                    case 'octahedron':
                        geometry = new THREE.OctahedronGeometry(1.5);
                        break;
                    default:
                        geometry = new THREE.BoxGeometry(2, 2, 2);
                }

                // Create material with current texture pattern
                const material = createMaterial(config.color, texturePatterns[currentTexture]);

                const mesh = new THREE.Mesh(geometry, material);
                mesh.position.set(...config.position);
                mesh.castShadow = true;
                mesh.receiveShadow = true;

                // Add glow effect
                const glowGeometry = geometry.clone();
                const glowMaterial = new THREE.MeshBasicMaterial({
                    color: config.color,
                    transparent: true,
                    opacity: 0.3,
                    side: THREE.BackSide
                });
                const glow = new THREE.Mesh(glowGeometry, glowMaterial);
                glow.position.set(...config.position);
                glow.scale.set(1.2, 1.2, 1.2);
                scene.add(glow);

                // Add click event
                mesh.userData = {
                    config: config,
                    index: index,
                    isMenuItem: true
                };

                // Create text for menu item
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = 512;
                canvas.height = 256;

                // Draw text with better quality
                context.fillStyle = '#ffffff';
                context.font = 'bold 32px Arial';
                context.textAlign = 'center';
                context.fillText(config.title, 256, 80);
                context.font = '16px Arial';
                context.fillText(config.description, 256, 120);

                const texture = new THREE.CanvasTexture(canvas);
                const textMaterial = new THREE.MeshBasicMaterial({ 
                    map: texture,
                    transparent: true,
                    opacity: 0.9
                });

                const textGeometry = new THREE.PlaneGeometry(4, 2);
                const textMesh = new THREE.Mesh(textGeometry, textMaterial);
                textMesh.position.set(config.position[0], config.position[1] + 3, config.position[2]);
                textMesh.lookAt(camera.position);

                // Add to scene
                scene.add(mesh);
                scene.add(textMesh);

                menuObjects.push({
                    mesh: mesh,
                    text: textMesh,
                    glow: glow,
                    config: config,
                    index: index
                });
            });
        }

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);

            const delta = clock.getDelta();
            const time = Date.now() * 0.001;

            // Animate menu objects
            menuObjects.forEach((obj, index) => {
                // Add floating animation
                const floatOffset = Math.sin(time * 2 + index) * 0.3;
                obj.mesh.position.y = obj.config.position[1] + floatOffset;
                obj.text.position.y = obj.config.position[1] + floatOffset + 3;
                obj.glow.position.y = obj.config.position[1] + floatOffset;

                // Add rotation animation
                obj.mesh.rotation.y += delta * 0.5;
                obj.glow.rotation.y += delta * 0.5;

                // Update text to face camera
                obj.text.lookAt(camera.position);
            });

            // Animate particles
            if (showParticles) {
                particles.forEach(particleSystem => {
                    particleSystem.rotation.y += delta * 0.1;
                    particleSystem.rotation.x += delta * 0.05;
                });
            }

            // Update controls
            controls.autoRotate = autoRotate;
            controls.update();

            // Update status bar
            updateStatusBar();

            renderer.render(scene, camera);
        }

        // Handle window resize
        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        // Control functions
        function resetView() {
            camera.position.set(0, 8, 15);
            camera.lookAt(0, 0, 0);
            controls.reset();
        }

        function toggleAutoRotate() {
            autoRotate = !autoRotate;
            controls.autoRotate = autoRotate;
        }

        function changeTexture() {
            currentTexture = (currentTexture + 1) % texturePatterns.length;
            createMenuObjects();
        }

        function toggleParticles() {
            showParticles = !showParticles;
            particles.forEach(particle => {
                particle.visible = showParticles;
            });
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
                    // Add click effect
                    object.scale.set(1.2, 1.2, 1.2);
                    setTimeout(() => {
                        object.scale.set(1, 1, 1);
                        window.location.href = object.userData.config.url;
                    }, 200);
                }
            }
        }

        // Update status bar
        function updateStatusBar() {
            document.getElementById('current-time').textContent = new Date().toLocaleTimeString();
        }

        // Event listeners
        window.addEventListener('resize', onWindowResize);
        window.addEventListener('click', onMouseClick);

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
                case 't':
                case 'T':
                    changeTexture();
                    break;
                case 'p':
                case 'P':
                    toggleParticles();
                    break;
            }
        });

        // Initialize everything
        window.addEventListener('load', () => {
            init3DScene();
            updateStatusBar();
        });
    </script>
</body>
</html> 