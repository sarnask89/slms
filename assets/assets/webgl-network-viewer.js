/**
 * WebGL Network Viewer - SLMS v1.2.0
 * Research-First Network Visualization with Three.js
 * 
 * Features:
 * - 3D network topology visualization
 * - Real-time device status updates
 * - Interactive device selection
 * - Research-driven visual enhancements
 * - Adaptive rendering based on network size
 * - Lightning effects on connections
 * - Smooth animations and transitions
 */

class NetworkTopologyViewer {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        
        // Configuration
        this.config = {
            backgroundColor: options.backgroundColor || 0x000000,
            deviceColors: options.deviceColors || {
                router: 0x00d4ff,
                switch: 0x00ff88,
                server: 0x8b5cf6,
                other: 0xff6b35,
                mikrotik: 0xff6b35,
                offline: 0x666666
            },
            deviceSize: options.deviceSize || 2,
            connectionWidth: options.connectionWidth || 2,
            animationSpeed: options.animationSpeed || 0.005,
            researchMode: options.researchMode || true,
            lightningEnabled: options.lightningEnabled || true,
            lightningSpeed: options.lightningSpeed || 0.02,
            lightningIntensity: options.lightningIntensity || 0.8
        };
        
        // Three.js components
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        
        // Network data
        this.devices = new Map();
        this.connections = new Map();
        this.deviceMeshes = new Map();
        this.connectionLines = new Map();
        this.lightningEffects = new Map();
        
        // Animation data
        this.animationTime = 0;
        this.lightningTime = 0;
        this.devicePulseTime = 0;
        
        // Research data
        this.researchData = {
            findings: 0,
            adaptations: 0,
            performance: 0,
            lastUpdate: new Date()
        };
        
        // Initialize
        this.init();
    }
    
    /**
     * Initialize the 3D scene
     */
    init() {
        this.createScene();
        this.createCamera();
        this.createRenderer();
        this.createControls();
        this.createLights();
        this.createGrid();
        this.createLightningSystem();
        this.animate();
        
        // Handle window resize
        window.addEventListener('resize', () => this.onWindowResize());
    }
    
    /**
     * Create Three.js scene
     */
    createScene() {
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(this.config.backgroundColor);
        this.scene.fog = new THREE.Fog(this.config.backgroundColor, 80, 300);
    }
    
    /**
     * Create camera
     */
    createCamera() {
        this.camera = new THREE.PerspectiveCamera(
            75,
            this.container.clientWidth / this.container.clientHeight,
            0.1,
            1000
        );
        this.camera.position.set(0, 0, 50);
    }
    
    /**
     * Create WebGL renderer
     */
    createRenderer() {
        this.renderer = new THREE.WebGLRenderer({ 
            antialias: true,
            alpha: true,
            powerPreference: "high-performance"
        });
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.2;
        this.container.appendChild(this.renderer.domElement);
    }
    
    /**
     * Create orbit controls
     */
    createControls() {
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        this.controls.screenSpacePanning = false;
        this.controls.minDistance = 10;
        this.controls.maxDistance = 200;
        this.controls.maxPolarAngle = Math.PI / 2;
        this.controls.autoRotate = false;
        this.controls.autoRotateSpeed = 0.5;
    }
    
    /**
     * Create lighting system
     */
    createLights() {
        // Ambient light
        const ambientLight = new THREE.AmbientLight(0x404040, 0.3);
        this.scene.add(ambientLight);
        
        // Directional light
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(50, 50, 50);
        directionalLight.castShadow = true;
        directionalLight.shadow.mapSize.width = 2048;
        directionalLight.shadow.mapSize.height = 2048;
        directionalLight.shadow.camera.near = 0.5;
        directionalLight.shadow.camera.far = 500;
        this.scene.add(directionalLight);
        
        // Point light for research glow
        const researchLight = new THREE.PointLight(0x00d4ff, 0.6, 100);
        researchLight.position.set(0, 0, 0);
        this.scene.add(researchLight);
        
        // Additional point lights for dynamic lighting
        this.light1 = new THREE.PointLight(0x00ff88, 0.4, 80);
        this.light1.position.set(30, 20, 30);
        this.scene.add(this.light1);
        
        this.light2 = new THREE.PointLight(0xff6b35, 0.4, 80);
        this.light2.position.set(-30, -20, -30);
        this.scene.add(this.light2);
    }
    
    /**
     * Create grid
     */
    createGrid() {
        const gridHelper = new THREE.GridHelper(100, 20, 0x444444, 0x222222);
        gridHelper.material.opacity = 0.3;
        gridHelper.material.transparent = true;
        this.scene.add(gridHelper);
    }
    
    /**
     * Create lightning system
     */
    createLightningSystem() {
        this.lightningGroup = new THREE.Group();
        this.scene.add(this.lightningGroup);
    }
    
    /**
     * Load network data
     */
    loadNetworkData(data) {
        this.clearNetwork();
        
        // Load devices
        if (data.devices) {
            data.devices.forEach(device => {
                this.addDevice(device);
            });
        }
        
        // Load connections
        if (data.connections) {
            data.connections.forEach(connection => {
                this.addConnection(connection);
            });
        }
        
        // Update research data
        this.updateResearchData(data);
    }
    
    /**
     * Add device to the scene
     */
    addDevice(device) {
        const geometry = this.createDeviceGeometry(device.type);
        const material = this.createDeviceMaterial(device);
        const mesh = new THREE.Mesh(geometry, material);
        
        // Position device
        mesh.position.set(device.x, device.y, device.z);
        
        // Enable shadows
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        
        // Store device data
        mesh.userData = {
            id: device.id,
            name: device.name,
            type: device.type,
            status: device.status,
            vendor: device.vendor,
            originalColor: this.config.deviceColors[device.status === 'offline' ? 'offline' : device.type] || this.config.deviceColors.other
        };
        
        // Add to scene
        this.scene.add(mesh);
        this.devices.set(device.id, device);
        this.deviceMeshes.set(device.id, mesh);
        
        // Add click event
        this.addDeviceInteraction(mesh);
        
        return mesh;
    }
    
    /**
     * Create device geometry based on type
     */
    createDeviceGeometry(type) {
        switch (type) {
            case 'router':
                return new THREE.CylinderGeometry(this.config.deviceSize, this.config.deviceSize, this.config.deviceSize * 2, 8);
            case 'switch':
                return new THREE.BoxGeometry(this.config.deviceSize * 2, this.config.deviceSize, this.config.deviceSize * 2);
            case 'server':
                return new THREE.BoxGeometry(this.config.deviceSize * 3, this.config.deviceSize * 2, this.config.deviceSize * 2);
            case 'mikrotik':
                return new THREE.ConeGeometry(this.config.deviceSize, this.config.deviceSize * 2, 8);
            default:
                return new THREE.SphereGeometry(this.config.deviceSize, 8, 6);
        }
    }
    
    /**
     * Create device material
     */
    createDeviceMaterial(device) {
        const color = this.config.deviceColors[device.status === 'offline' ? 'offline' : device.type] || this.config.deviceColors.other;
        
        const material = new THREE.MeshPhongMaterial({
            color: color,
            shininess: 100,
            transparent: true,
            opacity: 0.9,
            emissive: new THREE.Color(color),
            emissiveIntensity: 0.1
        });
        
        // Add research glow effect
        if (this.config.researchMode && device.status === 'online') {
            material.emissiveIntensity = 0.2;
        }
        
        return material;
    }
    
    /**
     * Add device interaction
     */
    addDeviceInteraction(mesh) {
        const raycaster = new THREE.Raycaster();
        const mouse = new THREE.Vector2();
        
        this.renderer.domElement.addEventListener('click', (event) => {
            mouse.x = (event.clientX / this.renderer.domElement.clientWidth) * 2 - 1;
            mouse.y = -(event.clientY / this.renderer.domElement.clientHeight) * 2 + 1;
            
            raycaster.setFromCamera(mouse, this.camera);
            const intersects = raycaster.intersectObjects(this.scene.children);
            
            if (intersects.length > 0) {
                const intersectedMesh = intersects[0].object;
                if (this.deviceMeshes.has(intersectedMesh.userData.id)) {
                    this.selectDevice(intersectedMesh.userData.id);
                }
            }
        });
    }
    
    /**
     * Add connection between devices with lightning effects
     */
    addConnection(connection) {
        const fromDevice = this.deviceMeshes.get(connection.from);
        const toDevice = this.deviceMeshes.get(connection.to);
        
        if (fromDevice && toDevice) {
            // Create base connection line
            const geometry = new THREE.BufferGeometry().setFromPoints([
                fromDevice.position,
                toDevice.position
            ]);
            
            const material = new THREE.LineBasicMaterial({
                color: 0x00d4ff,
                transparent: true,
                opacity: 0.4,
                linewidth: this.config.connectionWidth
            });
            
            const line = new THREE.Line(geometry, material);
            this.scene.add(line);
            this.connections.set(`${connection.from}-${connection.to}`, connection);
            this.connectionLines.set(`${connection.from}-${connection.to}`, line);
            
            // Create lightning effect
            if (this.config.lightningEnabled) {
                this.createLightningEffect(fromDevice.position, toDevice.position, `${connection.from}-${connection.to}`);
            }
        }
    }
    
    /**
     * Create lightning effect between two points
     */
    createLightningEffect(startPoint, endPoint, connectionId) {
        const lightningGeometry = new THREE.BufferGeometry();
        const lightningMaterial = new THREE.LineBasicMaterial({
            color: 0x00ffff,
            transparent: true,
            opacity: 0,
            linewidth: 3
        });
        
        const lightningLine = new THREE.Line(lightningGeometry, lightningMaterial);
        this.lightningGroup.add(lightningLine);
        this.lightningEffects.set(connectionId, {
            line: lightningLine,
            startPoint: startPoint,
            endPoint: endPoint,
            lastFlash: 0,
            flashDuration: 0.1
        });
    }
    
    /**
     * Update lightning effects
     */
    updateLightningEffects() {
        this.lightningTime += this.config.lightningSpeed;
        
        this.lightningEffects.forEach((effect, connectionId) => {
            const time = this.lightningTime + connectionId.length * 0.1; // Offset per connection
            
            // Create lightning flash
            if (Math.random() < 0.02) { // 2% chance per frame
                effect.lastFlash = time;
                effect.flashDuration = 0.05 + Math.random() * 0.1;
            }
            
            // Update lightning visibility
            const timeSinceFlash = time - effect.lastFlash;
            if (timeSinceFlash < effect.flashDuration) {
                effect.line.material.opacity = this.config.lightningIntensity * (1 - timeSinceFlash / effect.flashDuration);
                
                // Create lightning path with random points
                const points = this.generateLightningPath(effect.startPoint, effect.endPoint, 5);
                effect.line.geometry.setFromPoints(points);
            } else {
                effect.line.material.opacity = 0;
            }
        });
    }
    
    /**
     * Generate lightning path with random points
     */
    generateLightningPath(start, end, segments) {
        const points = [start.clone()];
        const direction = end.clone().sub(start);
        const segmentLength = direction.length() / segments;
        
        for (let i = 1; i < segments; i++) {
            const t = i / segments;
            const basePoint = start.clone().add(direction.clone().multiplyScalar(t));
            
            // Add random offset
            const offset = new THREE.Vector3(
                (Math.random() - 0.5) * segmentLength * 0.3,
                (Math.random() - 0.5) * segmentLength * 0.3,
                (Math.random() - 0.5) * segmentLength * 0.3
            );
            
            points.push(basePoint.add(offset));
        }
        
        points.push(end.clone());
        return points;
    }
    
    /**
     * Select device
     */
    selectDevice(deviceId) {
        // Reset all device materials
        this.deviceMeshes.forEach((mesh, id) => {
            const device = this.devices.get(id);
            mesh.material = this.createDeviceMaterial(device);
        });
        
        // Highlight selected device
        const selectedMesh = this.deviceMeshes.get(deviceId);
        if (selectedMesh) {
            selectedMesh.material.emissive = new THREE.Color(0xffffff);
            selectedMesh.material.emissiveIntensity = 0.8;
            selectedMesh.material.color = new THREE.Color(0xffff00);
        }
        
        // Trigger device info display
        if (typeof showDeviceInfo === 'function') {
            const device = this.devices.get(deviceId);
            if (device) {
                showDeviceInfo(device);
            }
        }
    }
    
    /**
     * Update device status
     */
    updateDeviceStatus(deviceId, status) {
        const device = this.devices.get(deviceId);
        const mesh = this.deviceMeshes.get(deviceId);
        
        if (device && mesh) {
            device.status = status;
            mesh.userData.status = status;
            mesh.material = this.createDeviceMaterial(device);
        }
    }
    
    /**
     * Focus on device
     */
    focusOnDevice(deviceId) {
        const mesh = this.deviceMeshes.get(deviceId);
        if (mesh) {
            this.camera.position.copy(mesh.position);
            this.camera.position.add(new THREE.Vector3(10, 10, 10));
            this.controls.target.copy(mesh.position);
        }
    }
    
    /**
     * Set camera position
     */
    setCameraPosition(x, y, z) {
        this.camera.position.set(x, y, z);
        this.controls.target.set(0, 0, 0);
    }
    
    /**
     * Get device by ID
     */
    getDeviceById(deviceId) {
        return this.deviceMeshes.get(deviceId);
    }
    
    /**
     * Update research data
     */
    updateResearchData(data) {
        this.researchData.findings = data.devices ? data.devices.length : 0;
        this.researchData.adaptations = data.connections ? data.connections.length : 0;
        this.researchData.performance = this.calculatePerformance();
        this.researchData.lastUpdate = new Date();
    }
    
    /**
     * Calculate performance metrics
     */
    calculatePerformance() {
        const deviceCount = this.devices.size;
        const connectionCount = this.connections.size;
        
        // Simple performance calculation based on network complexity
        let performance = 100;
        
        if (deviceCount > 50) performance -= 20;
        if (deviceCount > 100) performance -= 30;
        if (connectionCount > 100) performance -= 15;
        
        return Math.max(0, performance);
    }
    
    /**
     * Clear network
     */
    clearNetwork() {
        // Remove device meshes
        this.deviceMeshes.forEach(mesh => {
            this.scene.remove(mesh);
        });
        
        // Remove connection lines
        this.connectionLines.forEach(line => {
            this.scene.remove(line);
        });
        
        // Remove lightning effects
        this.lightningEffects.forEach(effect => {
            this.lightningGroup.remove(effect.line);
        });
        
        // Clear maps
        this.devices.clear();
        this.connections.clear();
        this.deviceMeshes.clear();
        this.connectionLines.clear();
        this.lightningEffects.clear();
    }
    
    /**
     * Handle window resize
     */
    onWindowResize() {
        this.camera.aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
    }
    
    /**
     * Animation loop
     */
    animate() {
        requestAnimationFrame(() => this.animate());
        
        // Update time
        this.animationTime += this.config.animationSpeed;
        this.devicePulseTime += this.config.animationSpeed * 2;
        
        // Update controls
        this.controls.update();
        
        // Update lightning effects
        this.updateLightningEffects();
        
        // Animate devices (subtle rotation and pulsing)
        this.deviceMeshes.forEach(mesh => {
            if (mesh.userData.status === 'online') {
                // Subtle rotation
                mesh.rotation.y += this.config.animationSpeed;
                
                // Pulsing effect
                const pulse = 1 + 0.1 * Math.sin(this.devicePulseTime + mesh.userData.id);
                mesh.scale.setScalar(pulse);
                
                // Dynamic emissive intensity
                const emissiveIntensity = 0.1 + 0.1 * Math.sin(this.animationTime + mesh.userData.id);
                mesh.material.emissiveIntensity = emissiveIntensity;
            }
        });
        
        // Animate connections (flow effect)
        this.connectionLines.forEach(line => {
            const time = this.animationTime;
            line.material.opacity = 0.3 + 0.2 * Math.sin(time * 3);
        });
        
        // Animate lights
        this.light1.position.x = 30 * Math.cos(this.animationTime * 0.5);
        this.light1.position.z = 30 * Math.sin(this.animationTime * 0.5);
        
        this.light2.position.x = -30 * Math.cos(this.animationTime * 0.3);
        this.light2.position.z = -30 * Math.sin(this.animationTime * 0.3);
        
        // Render scene
        this.renderer.render(this.scene, this.camera);
    }
    
    /**
     * Get research data
     */
    getResearchData() {
        return this.researchData;
    }
    
    /**
     * Enable research mode
     */
    enableResearchMode() {
        this.config.researchMode = true;
        this.updateDeviceMaterials();
    }
    
    /**
     * Disable research mode
     */
    disableResearchMode() {
        this.config.researchMode = false;
        this.updateDeviceMaterials();
    }
    
    /**
     * Update device materials
     */
    updateDeviceMaterials() {
        this.deviceMeshes.forEach((mesh, id) => {
            const device = this.devices.get(id);
            mesh.material = this.createDeviceMaterial(device);
        });
    }
    
    /**
     * Export network data
     */
    exportNetworkData() {
        return {
            devices: Array.from(this.devices.values()),
            connections: Array.from(this.connections.values()),
            research: this.researchData,
            timestamp: new Date().toISOString()
        };
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NetworkTopologyViewer;
} 