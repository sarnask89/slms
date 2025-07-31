/**
 * Futuristic WebGL Network Topology Viewer
 * Enhanced with sci-fi lighting, shadows, and particle effects
 */

class NetworkTopologyViewer {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.options = {
            width: window.innerWidth,
            height: window.innerHeight,
            backgroundColor: 0x000000,
            deviceColors: {
                router: 0x00d4ff,
                switch: 0x00ff88,
                other: 0xff6b35,
                server: 0x8b5cf6,
                offline: 0x666666
            },
            ...options
        };

        this.devices = new Map();
        this.connections = new Map();
        this.particles = [];
        this.isAnimating = false;
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();

        this.init();
        this.setupEventListeners();
    }

    init() {
        // Create Three.js scene with enhanced settings
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(this.options.backgroundColor);
        this.scene.fog = new THREE.Fog(this.options.backgroundColor, 50, 200);

        // Create camera with better positioning
        this.camera = new THREE.PerspectiveCamera(
            75,
            this.options.width / this.options.height,
            0.1,
            1000
        );
        this.camera.position.set(30, 30, 50);

        // Create renderer with enhanced settings
        this.renderer = new THREE.WebGLRenderer({ 
            antialias: true,
            alpha: true,
            powerPreference: "high-performance"
        });
        this.renderer.setSize(this.options.width, this.options.height);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.2;
        this.container.appendChild(this.renderer.domElement);

        // Add enhanced lighting
        this.setupLighting();

        // Add controls
        this.setupControls();

        // Add grid helper
        this.addGridHelper();

        // Add ambient particles
        this.addAmbientParticles();

        // Start animation loop
        this.animate();
    }

    setupLighting() {
        // Ambient light for base illumination
        const ambientLight = new THREE.AmbientLight(0x404040, 0.4);
        this.scene.add(ambientLight);

        // Main directional light with shadows
        const directionalLight = new THREE.DirectionalLight(0xffffff, 1.2);
        directionalLight.position.set(20, 30, 20);
        directionalLight.castShadow = true;
        directionalLight.shadow.mapSize.width = 2048;
        directionalLight.shadow.mapSize.height = 2048;
        directionalLight.shadow.camera.near = 0.5;
        directionalLight.shadow.camera.far = 500;
        directionalLight.shadow.camera.left = -50;
        directionalLight.shadow.camera.right = 50;
        directionalLight.shadow.camera.top = 50;
        directionalLight.shadow.camera.bottom = -50;
        this.scene.add(directionalLight);

        // Colored point lights for dramatic effect
        const blueLight = new THREE.PointLight(0x00d4ff, 0.8, 100);
        blueLight.position.set(-30, 20, 30);
        this.scene.add(blueLight);

        const greenLight = new THREE.PointLight(0x00ff88, 0.6, 80);
        greenLight.position.set(30, -20, -30);
        this.scene.add(greenLight);

        const purpleLight = new THREE.PointLight(0x8b5cf6, 0.5, 60);
        purpleLight.position.set(0, 40, 0);
        this.scene.add(purpleLight);

        // Add light helpers for debugging (optional)
        // this.scene.add(new THREE.DirectionalLightHelper(directionalLight, 5));
        // this.scene.add(new THREE.PointLightHelper(blueLight, 2));
    }

    setupControls() {
        // Orbit controls for camera manipulation
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        this.controls.screenSpacePanning = false;
        this.controls.minDistance = 20;
        this.controls.maxDistance = 200;
        this.controls.maxPolarAngle = Math.PI / 2;
    }

    addGridHelper() {
        // Enhanced grid with glow effect
        const gridHelper = new THREE.GridHelper(100, 20, 0x00d4ff, 0x004466);
        gridHelper.material.opacity = 0.3;
        gridHelper.material.transparent = true;
        this.scene.add(gridHelper);

        // Add grid glow
        const gridGlow = new THREE.GridHelper(100, 20, 0x00d4ff, 0x00d4ff);
        gridGlow.material.opacity = 0.1;
        gridGlow.material.transparent = true;
        gridGlow.position.y = 0.1;
        this.scene.add(gridGlow);
    }

    addAmbientParticles() {
        // Create ambient floating particles
        const particleCount = 100;
        const particleGeometry = new THREE.BufferGeometry();
        const particlePositions = new Float32Array(particleCount * 3);
        const particleColors = new Float32Array(particleCount * 3);

        for (let i = 0; i < particleCount; i++) {
            particlePositions[i * 3] = (Math.random() - 0.5) * 200;
            particlePositions[i * 3 + 1] = Math.random() * 100;
            particlePositions[i * 3 + 2] = (Math.random() - 0.5) * 200;

            const color = new THREE.Color();
            color.setHSL(Math.random() * 0.1 + 0.6, 0.8, 0.5);
            particleColors[i * 3] = color.r;
            particleColors[i * 3 + 1] = color.g;
            particleColors[i * 3 + 2] = color.b;
        }

        particleGeometry.setAttribute('position', new THREE.BufferAttribute(particlePositions, 3));
        particleGeometry.setAttribute('color', new THREE.BufferAttribute(particleColors, 3));

        const particleMaterial = new THREE.PointsMaterial({
            size: 2,
            vertexColors: true,
            transparent: true,
            opacity: 0.6
        });

        this.ambientParticles = new THREE.Points(particleGeometry, particleMaterial);
        this.scene.add(this.ambientParticles);
    }

    // Device Management with enhanced 3D models
    addDevice(deviceData) {
        const { id, name, type, x, y, z, status = 'online' } = deviceData;
        
        // Create enhanced device geometry based on type
        let geometry, material;
        
        switch (type) {
            case 'router':
                geometry = new THREE.CylinderGeometry(2, 2, 4, 8);
                break;
            case 'switch':
                geometry = new THREE.BoxGeometry(3, 2, 3);
                break;
            case 'server':
                geometry = new THREE.BoxGeometry(4, 3, 2);
                break;
            default: // other
                geometry = new THREE.SphereGeometry(1.5, 16, 16);
        }

        // Create enhanced material with glow effect
        const color = status === 'offline' ? 
            this.options.deviceColors.offline : 
            this.options.deviceColors[type] || this.options.deviceColors.other;
            
        material = new THREE.MeshPhongMaterial({ 
            color: color,
            transparent: true,
            opacity: 0.9,
            shininess: 100,
            specular: 0x444444
        });

        // Create mesh
        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(x, y, z);
        mesh.userData = { id, name, type, status };
        mesh.castShadow = true;
        mesh.receiveShadow = true;

        // Add device to scene and store reference
        this.scene.add(mesh);
        this.devices.set(id, mesh);

        // Add device glow
        this.addDeviceGlow(mesh, color);

        // Add device label
        this.addDeviceLabel(mesh, name);

        // Add status indicator
        this.addStatusIndicator(mesh, status);

        return mesh;
    }

    addDeviceGlow(mesh, color) {
        // Create glow effect around device
        const glowGeometry = mesh.geometry.clone();
        const glowMaterial = new THREE.MeshBasicMaterial({
            color: color,
            transparent: true,
            opacity: 0.3,
            side: THREE.BackSide
        });
        
        const glowMesh = new THREE.Mesh(glowGeometry, glowMaterial);
        glowMesh.scale.multiplyScalar(1.2);
        mesh.add(glowMesh);
        
        // Animate glow
        const animateGlow = () => {
            glowMesh.material.opacity = 0.2 + Math.sin(Date.now() * 0.003) * 0.1;
            requestAnimationFrame(animateGlow);
        };
        animateGlow();
    }

    addDeviceLabel(mesh, name) {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.width = 256;
        canvas.height = 64;

        // Draw label background with glow
        context.fillStyle = 'rgba(0, 0, 0, 0.8)';
        context.fillRect(0, 0, canvas.width, canvas.height);
        
        // Add glow effect
        context.shadowColor = '#00d4ff';
        context.shadowBlur = 10;
        context.fillStyle = '#00d4ff';
        context.fillRect(2, 2, canvas.width - 4, canvas.height - 4);

        // Draw text
        context.fillStyle = 'white';
        context.font = 'bold 20px Arial';
        context.textAlign = 'center';
        context.shadowBlur = 5;
        context.fillText(name, canvas.width / 2, canvas.height / 2 + 8);

        // Create texture and material
        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.SpriteMaterial({ 
            map: texture,
            transparent: true,
            opacity: 0.9
        });

        // Create sprite
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(12, 3, 1);
        sprite.position.set(0, 8, 0);

        mesh.add(sprite);
    }

    updateDeviceStatus(deviceId, status) {
        const device = this.devices.get(deviceId);
        if (!device) return;

        const type = device.userData.type;
        const color = status === 'offline' ? 
            this.options.deviceColors.offline : 
            this.options.deviceColors[type] || this.options.deviceColors.other;

        device.material.color.setHex(color);
        device.userData.status = status;

        // Update status indicator
        this.addStatusIndicator(device, status);
    }

    addStatusIndicator(device, status) {
        // Remove existing status indicator
        device.children.forEach(child => {
            if (child.userData.isStatusIndicator) {
                device.remove(child);
            }
        });

        // Create status indicator
        const indicatorGeometry = new THREE.SphereGeometry(0.3, 8, 8);
        const indicatorColor = status === 'online' ? 0x00ff88 : 0xff4444;
        const indicatorMaterial = new THREE.MeshBasicMaterial({ 
            color: indicatorColor,
            transparent: true,
            opacity: 0.8
        });
        
        const indicator = new THREE.Mesh(indicatorGeometry, indicatorMaterial);
        indicator.position.set(0, 6, 0);
        indicator.userData.isStatusIndicator = true;
        
        // Animate status indicator
        const animateIndicator = () => {
            indicator.material.opacity = 0.4 + Math.sin(Date.now() * 0.01) * 0.4;
            indicator.scale.setScalar(0.8 + Math.sin(Date.now() * 0.01) * 0.2);
            requestAnimationFrame(animateIndicator);
        };
        animateIndicator();
        
        device.add(indicator);
    }

    addConnection(fromDeviceId, toDeviceId, bandwidth = 1000) {
        const fromDevice = this.devices.get(fromDeviceId);
        const toDevice = this.devices.get(toDeviceId);
        
        if (!fromDevice || !toDevice) return;

        // Create connection line with enhanced styling
        const points = [
            fromDevice.position,
            toDevice.position
        ];
        
        const lineGeometry = new THREE.BufferGeometry().setFromPoints(points);
        const lineMaterial = new THREE.LineBasicMaterial({
            color: 0x00d4ff,
            transparent: true,
            opacity: 0.6,
            linewidth: 2
        });
        
        const line = new THREE.Line(lineGeometry, lineMaterial);
        this.scene.add(line);
        
        // Store connection reference
        const connectionKey = `${fromDeviceId}-${toDeviceId}`;
        this.connections.set(connectionKey, line);

        // Add traffic particles
        this.addTrafficParticles(fromDevice.position, toDevice.position, bandwidth);
    }

    addTrafficParticles(fromPos, toPos, bandwidth) {
        const particleCount = Math.min(bandwidth / 100, 20);
        const particleGeometry = new THREE.BufferGeometry();
        const particlePositions = new Float32Array(particleCount * 3);
        const particleColors = new Float32Array(particleCount * 3);

        for (let i = 0; i < particleCount; i++) {
            particlePositions[i * 3] = fromPos.x;
            particlePositions[i * 3 + 1] = fromPos.y;
            particlePositions[i * 3 + 2] = fromPos.z;

            particleColors[i * 3] = 0.0;     // R
            particleColors[i * 3 + 1] = 0.8; // G
            particleColors[i * 3 + 2] = 1.0; // B
        }

        particleGeometry.setAttribute('position', new THREE.BufferAttribute(particlePositions, 3));
        particleGeometry.setAttribute('color', new THREE.BufferAttribute(particleColors, 3));

        const particleMaterial = new THREE.PointsMaterial({
            size: 3,
            vertexColors: true,
            transparent: true,
            opacity: 0.8
        });

        const particles = new THREE.Points(particleGeometry, particleMaterial);
        this.scene.add(particles);

        // Store particle data for animation
        this.particles.push({
            particles: particles,
            fromPos: fromPos.clone(),
            toPos: toPos.clone(),
            speed: 0.02 + Math.random() * 0.03,
            progress: Math.random()
        });
    }

    animate() {
        requestAnimationFrame(() => this.animate());

        // Update controls
        this.controls.update();

        // Animate particles
        this.animateParticles();

        // Animate ambient particles
        this.animateAmbientParticles();

        // Animate devices
        this.animateDevices();

        // Render scene
        this.renderer.render(this.scene, this.camera);
    }

    animateParticles() {
        this.particles.forEach(particleData => {
            const positions = particleData.particles.geometry.attributes.position.array;
            
            for (let i = 0; i < positions.length; i += 3) {
                particleData.progress += particleData.speed;
                if (particleData.progress > 1) {
                    particleData.progress = 0;
                }

                const t = particleData.progress;
                positions[i] = particleData.fromPos.x + (particleData.toPos.x - particleData.fromPos.x) * t;
                positions[i + 1] = particleData.fromPos.y + (particleData.toPos.y - particleData.fromPos.y) * t;
                positions[i + 2] = particleData.fromPos.z + (particleData.toPos.z - particleData.fromPos.z) * t;
            }
            
            particleData.particles.geometry.attributes.position.needsUpdate = true;
        });
    }

    animateAmbientParticles() {
        if (this.ambientParticles) {
            this.ambientParticles.rotation.y += 0.001;
            this.ambientParticles.rotation.x += 0.0005;
        }
    }

    animateDevices() {
        this.devices.forEach(device => {
            // Gentle floating animation
            device.position.y += Math.sin(Date.now() * 0.001 + device.userData.id) * 0.01;
            device.rotation.y += 0.005;
        });
    }

    setupEventListeners() {
        // Mouse events for device selection
        this.renderer.domElement.addEventListener('click', (event) => this.onMouseClick(event));
        this.renderer.domElement.addEventListener('mousemove', (event) => this.onMouseMove(event));
        
        // Window resize
        window.addEventListener('resize', () => this.onWindowResize());
    }

    onMouseClick(event) {
        this.updateMousePosition(event);
        this.raycaster.setFromCamera(this.mouse, this.camera);
        
        const intersects = this.raycaster.intersectObjects(this.scene.children, true);
        
        for (const intersect of intersects) {
            const device = this.findDeviceMesh(intersect.object);
            if (device) {
                this.onDeviceClick(device);
                break;
            }
        }
    }

    onMouseMove(event) {
        this.updateMousePosition(event);
        this.raycaster.setFromCamera(this.mouse, this.camera);
        
        const intersects = this.raycaster.intersectObjects(this.scene.children, true);
        
        // Update cursor style
        let foundDevice = false;
        for (const intersect of intersects) {
            const device = this.findDeviceMesh(intersect.object);
            if (device) {
                this.renderer.domElement.style.cursor = 'pointer';
                foundDevice = true;
                break;
            }
        }
        
        if (!foundDevice) {
            this.renderer.domElement.style.cursor = 'default';
        }
    }

    updateMousePosition(event) {
        const rect = this.renderer.domElement.getBoundingClientRect();
        this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
    }

    findDeviceMesh(object) {
        while (object && !object.userData.id) {
            object = object.parent;
        }
        return object && object.userData.id ? object : null;
    }

    onDeviceClick(device) {
        // Dispatch custom event for device selection
        const event = new CustomEvent('deviceSelected', {
            detail: {
                id: device.userData.id,
                name: device.userData.name,
                type: device.userData.type,
                status: device.userData.status,
                position: device.position
            }
        });
        this.renderer.domElement.dispatchEvent(event);
    }

    onWindowResize() {
        this.camera.aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
    }

    loadNetworkData(networkData) {
        this.clearNetwork();
        
        // Add devices
        networkData.devices.forEach(device => {
            this.addDevice(device);
        });
        
        // Add connections
        networkData.connections.forEach(connection => {
            this.addConnection(connection.from, connection.to, connection.bandwidth);
        });
    }

    clearNetwork() {
        // Remove all devices
        this.devices.forEach(device => {
            this.scene.remove(device);
        });
        this.devices.clear();
        
        // Remove all connections
        this.connections.forEach(connection => {
            this.scene.remove(connection);
        });
        this.connections.clear();
        
        // Clear particles
        this.particles.forEach(particleData => {
            this.scene.remove(particleData.particles);
        });
        this.particles = [];
    }

    getDeviceById(id) {
        return this.devices.get(id);
    }

    getConnectionById(fromId, toId) {
        return this.connections.get(`${fromId}-${toId}`);
    }

    setCameraPosition(x, y, z) {
        this.camera.position.set(x, y, z);
        this.controls.update();
    }

    focusOnDevice(deviceId) {
        const device = this.devices.get(deviceId);
        if (device) {
            const distance = 20;
            const direction = new THREE.Vector3().subVectors(this.camera.position, device.position).normalize();
            this.camera.position.copy(device.position).add(direction.multiplyScalar(distance));
            this.controls.target.copy(device.position);
            this.controls.update();
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NetworkTopologyViewer;
} 