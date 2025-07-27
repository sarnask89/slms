<?php
/**
 * WebGL Integration Test Script
 * Tests the WebGL migration components and verifies functionality
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/modules/helpers/auth_helper.php';

// Skip login for testing purposes
// require_login();

$pageTitle = 'WebGL Integration Test - AI SERVICE NETWORK MANAGEMENT SYSTEM';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card dark-card">
                <div class="card-header">
                    <h2><i class="bi bi-check-circle"></i> WebGL Integration Test Results</h2>
                    <p class="mb-0">Testing WebGL migration components and functionality</p>
                </div>
                <div class="card-body">
                    
                    <!-- Test Results -->
                    <div class="row">
                        <div class="col-md-6">
                            <h4><i class="bi bi-list-check"></i> Component Tests</h4>
                            <div id="test-results">
                                <!-- Test results will be populated here -->
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h4><i class="bi bi-gear"></i> System Information</h4>
                            <div id="system-info">
                                <!-- System info will be populated here -->
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- WebGL Demo Link -->
                    <div class="row">
                        <div class="col-12">
                            <h4><i class="bi bi-play-circle"></i> WebGL Demo</h4>
                            <p>Click the button below to test the 3D network visualization:</p>
                            <a href="webgl_demo.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-diagram-3"></i> Launch WebGL Demo
                            </a>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- API Test -->
                    <div class="row">
                        <div class="col-12">
                            <h4><i class="bi bi-code-slash"></i> API Test</h4>
                            <button class="btn btn-secondary" onclick="testAPI()">
                                <i class="bi bi-arrow-clockwise"></i> Test WebGL API
                            </button>
                            <div id="api-test-results" class="mt-3">
                                <!-- API test results will be shown here -->
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Test results container
let testResults = [];
let systemInfo = {};

// Run tests on page load
document.addEventListener('DOMContentLoaded', function() {
    runTests();
    displayResults();
});

// Run all tests
function runTests() {
    console.log('Running WebGL integration tests...');
    
    // Test 1: Check if Three.js is available
    testResults.push({
        name: 'Three.js Library',
        status: typeof THREE !== 'undefined' ? 'PASS' : 'FAIL',
        message: typeof THREE !== 'undefined' ? 'Three.js library loaded successfully' : 'Three.js library not found'
    });
    
    // Test 2: Check if WebGL is supported
    testResults.push({
        name: 'WebGL Support',
        status: checkWebGLSupport() ? 'PASS' : 'FAIL',
        message: checkWebGLSupport() ? 'WebGL is supported by this browser' : 'WebGL is not supported by this browser'
    });
    
    // Test 3: Check if NetworkTopologyViewer class exists
    testResults.push({
        name: 'NetworkTopologyViewer Class',
        status: typeof NetworkTopologyViewer !== 'undefined' ? 'PASS' : 'FAIL',
        message: typeof NetworkTopologyViewer !== 'undefined' ? 'NetworkTopologyViewer class loaded' : 'NetworkTopologyViewer class not found'
    });
    
    // Test 4: Check browser compatibility
    testResults.push({
        name: 'Browser Compatibility',
        status: checkBrowserCompatibility() ? 'PASS' : 'WARNING',
        message: getBrowserInfo()
    });
    
    // Test 5: Check screen resolution
    testResults.push({
        name: 'Screen Resolution',
        status: 'INFO',
        message: `Screen: ${screen.width}x${screen.height}, Viewport: ${window.innerWidth}x${window.innerHeight}`
    });
    
    // Test 6: Check memory availability
    testResults.push({
        name: 'Memory Information',
        status: 'INFO',
        message: getMemoryInfo()
    });
    
    // Collect system information
    collectSystemInfo();
}

// Check WebGL support
function checkWebGLSupport() {
    try {
        const canvas = document.createElement('canvas');
        return !!(window.WebGLRenderingContext && 
                 (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
    } catch (e) {
        return false;
    }
}

// Check browser compatibility
function checkBrowserCompatibility() {
    const userAgent = navigator.userAgent;
    const isChrome = userAgent.includes('Chrome') && !userAgent.includes('Edge');
    const isFirefox = userAgent.includes('Firefox');
    const isSafari = userAgent.includes('Safari') && !userAgent.includes('Chrome');
    const isEdge = userAgent.includes('Edge');
    
    return isChrome || isFirefox || isSafari || isEdge;
}

// Get browser information
function getBrowserInfo() {
    const userAgent = navigator.userAgent;
    let browser = 'Unknown';
    let version = 'Unknown';
    
    if (userAgent.includes('Chrome') && !userAgent.includes('Edge')) {
        browser = 'Chrome';
        version = userAgent.match(/Chrome\/(\d+)/)?.[1] || 'Unknown';
    } else if (userAgent.includes('Firefox')) {
        browser = 'Firefox';
        version = userAgent.match(/Firefox\/(\d+)/)?.[1] || 'Unknown';
    } else if (userAgent.includes('Safari') && !userAgent.includes('Chrome')) {
        browser = 'Safari';
        version = userAgent.match(/Version\/(\d+)/)?.[1] || 'Unknown';
    } else if (userAgent.includes('Edge')) {
        browser = 'Edge';
        version = userAgent.match(/Edge\/(\d+)/)?.[1] || 'Unknown';
    }
    
    return `${browser} ${version}`;
}

// Get memory information
function getMemoryInfo() {
    if (navigator.deviceMemory) {
        return `Device Memory: ${navigator.deviceMemory}GB`;
    } else if (navigator.hardwareConcurrency) {
        return `CPU Cores: ${navigator.hardwareConcurrency}`;
    } else {
        return 'Memory information not available';
    }
}

// Collect system information
function collectSystemInfo() {
    systemInfo = {
        userAgent: navigator.userAgent,
        platform: navigator.platform,
        language: navigator.language,
        cookieEnabled: navigator.cookieEnabled,
        onLine: navigator.onLine,
        deviceMemory: navigator.deviceMemory || 'Not available',
        hardwareConcurrency: navigator.hardwareConcurrency || 'Not available',
        maxTouchPoints: navigator.maxTouchPoints || 'Not available',
        screenWidth: screen.width,
        screenHeight: screen.height,
        windowWidth: window.innerWidth,
        windowHeight: window.innerHeight,
        colorDepth: screen.colorDepth,
        pixelDepth: screen.pixelDepth
    };
}

// Display test results
function displayResults() {
    const resultsContainer = document.getElementById('test-results');
    const systemContainer = document.getElementById('system-info');
    
    // Display test results
    let resultsHTML = '';
    testResults.forEach(test => {
        const statusClass = test.status === 'PASS' ? 'text-success' : 
                           test.status === 'FAIL' ? 'text-danger' : 
                           test.status === 'WARNING' ? 'text-warning' : 'text-info';
        
        resultsHTML += `
            <div class="mb-2">
                <strong>${test.name}:</strong> 
                <span class="${statusClass}">
                    <i class="bi ${test.status === 'PASS' ? 'bi-check-circle' : 
                                   test.status === 'FAIL' ? 'bi-x-circle' : 
                                   test.status === 'WARNING' ? 'bi-exclamation-triangle' : 'bi-info-circle'}"></i>
                    ${test.status}
                </span>
                <br>
                <small class="text-muted">${test.message}</small>
            </div>
        `;
    });
    
    resultsContainer.innerHTML = resultsHTML;
    
    // Display system information
    let systemHTML = '';
    for (const [key, value] of Object.entries(systemInfo)) {
        const formattedKey = key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
        systemHTML += `
            <div class="mb-1">
                <strong>${formattedKey}:</strong> ${value}
            </div>
        `;
    }
    
    systemContainer.innerHTML = systemHTML;
}

// Test API functionality
async function testAPI() {
    const resultsContainer = document.getElementById('api-test-results');
    resultsContainer.innerHTML = '<div class="text-info"><i class="bi bi-hourglass-split"></i> Testing API...</div>';
    
    try {
        // Test the WebGL API endpoint
        const response = await fetch('modules/webgl_network_viewer.php?action=network_data');
        
        if (response.ok) {
            const data = await response.json();
            resultsContainer.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> API Test PASSED
                    <br>
                    <small>Response: ${JSON.stringify(data, null, 2)}</small>
                </div>
            `;
        } else {
            resultsContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> API Test FAILED
                    <br>
                    <small>Status: ${response.status} ${response.statusText}</small>
                </div>
            `;
        }
    } catch (error) {
        resultsContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-x-circle"></i> API Test ERROR
                <br>
                <small>Error: ${error.message}</small>
            </div>
        `;
    }
}

// Export test results for debugging
window.getTestResults = function() {
    return {
        tests: testResults,
        system: systemInfo
    };
};
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/partials/layout.php';
?> 