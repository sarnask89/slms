// OrbitControls Compatibility Wrapper
// This file provides a compatibility layer for ES6 module OrbitControls

(function() {
    'use strict';
    
    // Check if THREE is available
    if (typeof THREE === 'undefined') {
        console.error('THREE.js must be loaded before OrbitControls');
        return;
    }
    
    // Import the ES6 module version
    import('./OrbitControls.js').then(module => {
        // Make OrbitControls available globally
        window.THREE.OrbitControls = module.OrbitControls;
        console.log('OrbitControls loaded successfully via ES6 module');
    }).catch(error => {
        console.error('Failed to load OrbitControls module:', error);
        
        // Fallback: Create a basic OrbitControls implementation
        console.log('Creating fallback OrbitControls...');
        createFallbackOrbitControls();
    });
    
    function createFallbackOrbitControls() {
        // Basic OrbitControls implementation for compatibility
        THREE.OrbitControls = function(object, domElement) {
            this.object = object;
            this.domElement = domElement;
            
            // Basic properties
            this.enabled = true;
            this.target = new THREE.Vector3();
            this.minDistance = 0;
            this.maxDistance = Infinity;
            this.enableDamping = false;
            this.dampingFactor = 0.05;
            this.enableZoom = true;
            this.zoomSpeed = 1.0;
            this.enableRotate = true;
            this.rotateSpeed = 1.0;
            this.enablePan = true;
            this.panSpeed = 1.0;
            this.screenSpacePanning = true;
            this.minPolarAngle = 0;
            this.maxPolarAngle = Math.PI;
            this.minAzimuthAngle = -Infinity;
            this.maxAzimuthAngle = Infinity;
            
            // Event listeners
            this.addEventListener = function(type, listener) {
                if (!this._listeners) this._listeners = {};
                if (!this._listeners[type]) this._listeners[type] = [];
                this._listeners[type].push(listener);
            };
            
            this.removeEventListener = function(type, listener) {
                if (!this._listeners || !this._listeners[type]) return;
                const index = this._listeners[type].indexOf(listener);
                if (index !== -1) this._listeners[type].splice(index, 1);
            };
            
            this.dispatchEvent = function(event) {
                if (!this._listeners || !this._listeners[event.type]) return;
                this._listeners[event.type].forEach(listener => listener(event));
            };
            
            // Basic update method
            this.update = function() {
                // Basic orbit controls logic would go here
                // For now, just a placeholder
            };
            
            // Initialize basic controls
            this.init();
        };
        
        THREE.OrbitControls.prototype.init = function() {
            console.log('Fallback OrbitControls initialized');
        };
        
        console.log('Fallback OrbitControls created');
    }
})(); 