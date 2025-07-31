<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebGL Debug</title>
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: monospace;
            margin: 0;
            padding: 20px;
        }
        #webgl-container {
            width: 100%;
            height: 400px;
            border: 2px solid #00d4ff;
            margin: 20px 0;
        }
        .debug-info {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            color: #ff4444;
        }
        .success {
            color: #00ff88;
        }
    </style>
</head>
<body>
    <h1>WebGL Debug Console</h1>
    
    <div class="debug-info">
        <h3>Debug Information:</h3>
        <div id="debug-output"></div>
    </div>
    
    <div id="webgl-container"></div>
    
    <button onclick="testWebGL()">Test WebGL</button>
    <button onclick="testThreeJS()">Test Three.js</button>
    <button onclick="testNetworkViewer()">Test Network Viewer</button>
    
    <!-- Three.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    
    <!-- WebGL Network Viewer -->
    <script src="assets/webgl-network-viewer.js"></script>
    
    <script>
        function log(message, type = 'info') {
            const output = document.getElementById('debug-output');
            const timestamp = new Date().toLocaleTimeString();
            const className = type === 'error' ? 'error' : type === 'success' ? 'success' : '';
            output.innerHTML += `<div class="${className}">[${timestamp}] ${message}</div>`;
            console.log(`[${timestamp}] ${message}`);
        }
        
        function testWebGL() {
            log('Testing WebGL support...');
            
            if (!window.WebGLRenderingContext) {
                log('WebGL is not supported in this browser', 'error');
                return;
            }
            
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            
            if (!gl) {
                log('WebGL context could not be created', 'error');
                return;
            }
            
            log('WebGL is supported and working', 'success');
            log(`WebGL Version: ${gl.getParameter(gl.VERSION)}`);
            log(`WebGL Vendor: ${gl.getParameter(gl.VENDOR)}`);
            log(`WebGL Renderer: ${gl.getParameter(gl.RENDERER)}`);
        }
        
        function testThreeJS() {
            log('Testing Three.js...');
            
            if (typeof THREE === 'undefined') {
                log('Three.js is not loaded', 'error');
                return;
            }
            
            log('Three.js is loaded', 'success');
            log(`Three.js Version: ${THREE.REVISION}`);
            
            try {
                const scene = new THREE.Scene();
                const camera = new THREE.PerspectiveCamera(75, 1, 0.1, 1000);
                const renderer = new THREE.WebGLRenderer();
                
                log('Three.js scene, camera, and renderer created successfully', 'success');
            } catch (error) {
                log(`Three.js test failed: ${error.message}`, 'error');
            }
        }
        
        function testNetworkViewer() {
            log('Testing NetworkTopologyViewer...');
            
            if (typeof NetworkTopologyViewer === 'undefined') {
                log('NetworkTopologyViewer class is not loaded', 'error');
                return;
            }
            
            log('NetworkTopologyViewer class is loaded', 'success');
            
            try {
                const viewer = new NetworkTopologyViewer('webgl-container', {
                    backgroundColor: 0x000000,
                    lightningEnabled: true
                });
                
                log('NetworkTopologyViewer instance created successfully', 'success');
                
                // Test with sample data
                const sampleData = {
                    devices: [
                        { id: 1, name: 'Test Router', type: 'router', x: 0, y: 0, z: 0, status: 'online' }
                    ],
                    connections: []
                };
                
                viewer.loadNetworkData(sampleData);
                log('Sample data loaded successfully', 'success');
                
            } catch (error) {
                log(`NetworkTopologyViewer test failed: ${error.message}`, 'error');
                log(`Error stack: ${error.stack}`, 'error');
            }
        }
        
        // Auto-run tests on page load
        window.addEventListener('load', () => {
            log('Page loaded, running tests...');
            setTimeout(() => {
                testWebGL();
                setTimeout(() => {
                    testThreeJS();
                    setTimeout(() => {
                        testNetworkViewer();
                    }, 500);
                }, 500);
            }, 500);
        });
    </script>
</body>
</html> 