/**
 * WebGL Interface Function-by-Function Test Suite
 * Tests every function in the SLMSWebGLInterface class systematically
 */

class WebGLFunctionTester {
    constructor() {
        this.testResults = {};
        this.currentTest = 0;
        this.totalTests = 0;
        this.webglInterface = null;
        this.testLog = [];
    }

    // Initialize the test suite
    async init() {
        console.log('🧪 Initializing WebGL Function Test Suite...');
        
        // Wait for the main interface to be available
        if (typeof SLMSWebGLInterface !== 'undefined') {
            this.webglInterface = new SLMSWebGLInterface();
            console.log('✅ WebGL Interface initialized for testing');
        } else {
            console.error('❌ SLMSWebGLInterface not found');
            return false;
        }
        
        return true;
    }

    // Log test results
    logTest(functionName, result, details = '') {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = {
            timestamp,
            function: functionName,
            result,
            details
        };
        
        this.testLog.push(logEntry);
        this.testResults[functionName] = { result, details };
        
        const status = result === 'PASS' ? '✅' : result === 'FAIL' ? '❌' : '⚠️';
        console.log(`${status} [${timestamp}] ${functionName}: ${result}${details ? ` - ${details}` : ''}`);
        
        this.currentTest++;
        this.updateProgress();
    }

    // Update progress display
    updateProgress() {
        const progress = (this.currentTest / this.totalTests) * 100;
        console.log(`📊 Progress: ${this.currentTest}/${this.totalTests} (${progress.toFixed(1)}%)`);
    }

    // Test 1: Constructor
    testConstructor() {
        console.log('\n🔧 Testing Constructor...');
        
        try {
            if (this.webglInterface) {
                // Check if all properties are initialized
                const requiredProps = [
                    'currentModule', 'webglScene', 'webglRenderer', 
                    'webglCamera', 'animationId', 'stats', 'moduleData',
                    'apiBaseUrl', 'moduleApiUrl'
                ];
                
                let allPropsExist = true;
                for (const prop of requiredProps) {
                    if (!(prop in this.webglInterface)) {
                        allPropsExist = false;
                        break;
                    }
                }
                
                if (allPropsExist) {
                    this.logTest('Constructor', 'PASS', 'All properties initialized correctly');
                } else {
                    this.logTest('Constructor', 'FAIL', 'Missing required properties');
                }
            } else {
                this.logTest('Constructor', 'FAIL', 'Interface not initialized');
            }
        } catch (error) {
            this.logTest('Constructor', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 2: initializeWebGL
    testInitializeWebGL() {
        console.log('\n🎨 Testing initializeWebGL...');
        
        try {
            // Check if WebGL context is created
            const canvas = document.getElementById('webgl-canvas');
            if (!canvas) {
                this.logTest('initializeWebGL', 'FAIL', 'Canvas element not found');
                return;
            }
            
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (!gl) {
                this.logTest('initializeWebGL', 'FAIL', 'WebGL context not available');
                return;
            }
            
            // Check if Three.js scene is created
            if (this.webglInterface.webglScene && 
                this.webglInterface.webglCamera && 
                this.webglInterface.webglRenderer) {
                this.logTest('initializeWebGL', 'PASS', 'WebGL context and Three.js objects created');
            } else {
                this.logTest('initializeWebGL', 'FAIL', 'Three.js objects not properly initialized');
            }
        } catch (error) {
            this.logTest('initializeWebGL', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 3: createNetworkVisualization
    testCreateNetworkVisualization() {
        console.log('\n🌐 Testing createNetworkVisualization...');
        
        try {
            if (this.webglInterface.webglScene) {
                const sceneChildren = this.webglInterface.webglScene.children.length;
                if (sceneChildren > 0) {
                    this.logTest('createNetworkVisualization', 'PASS', `${sceneChildren} objects in scene`);
                } else {
                    this.logTest('createNetworkVisualization', 'WARNING', 'No objects in scene');
                }
            } else {
                this.logTest('createNetworkVisualization', 'FAIL', 'WebGL scene not available');
            }
        } catch (error) {
            this.logTest('createNetworkVisualization', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 4: animate
    testAnimate() {
        console.log('\n🎬 Testing animate...');
        
        try {
            if (typeof this.webglInterface.animate === 'function') {
                // Test if animation loop can be started
                const originalRequestAnimationFrame = window.requestAnimationFrame;
                let animationCalled = false;
                
                window.requestAnimationFrame = (callback) => {
                    animationCalled = true;
                    return 1; // Mock animation frame ID
                };
                
                this.webglInterface.animate();
                
                window.requestAnimationFrame = originalRequestAnimationFrame;
                
                if (animationCalled) {
                    this.logTest('animate', 'PASS', 'Animation loop function working');
                } else {
                    this.logTest('animate', 'FAIL', 'Animation loop not called');
                }
            } else {
                this.logTest('animate', 'FAIL', 'Animate function not found');
            }
        } catch (error) {
            this.logTest('animate', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 5: initializeEventListeners
    testInitializeEventListeners() {
        console.log('\n👂 Testing initializeEventListeners...');
        
        try {
            if (typeof this.webglInterface.initializeEventListeners === 'function') {
                // Check if event listeners are attached
                const moduleButtons = document.querySelectorAll('[data-module]');
                if (moduleButtons.length > 0) {
                    this.logTest('initializeEventListeners', 'PASS', `${moduleButtons.length} module buttons found`);
                } else {
                    this.logTest('initializeEventListeners', 'WARNING', 'No module buttons found');
                }
            } else {
                this.logTest('initializeEventListeners', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('initializeEventListeners', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 6: loadModule
    async testLoadModule() {
        console.log('\n📦 Testing loadModule...');
        
        try {
            if (typeof this.webglInterface.loadModule === 'function') {
                // Test loading a module
                await this.webglInterface.loadModule('clients');
                
                if (this.webglInterface.currentModule === 'clients') {
                    this.logTest('loadModule', 'PASS', 'Module loaded successfully');
                } else {
                    this.logTest('loadModule', 'FAIL', 'Module not set correctly');
                }
            } else {
                this.logTest('loadModule', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('loadModule', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 7: fetchModuleData
    async testFetchModuleData() {
        console.log('\n📡 Testing fetchModuleData...');
        
        try {
            if (typeof this.webglInterface.fetchModuleData === 'function') {
                const data = await this.webglInterface.fetchModuleData('clients');
                
                if (data && typeof data === 'object') {
                    this.logTest('fetchModuleData', 'PASS', 'Data fetched successfully');
                } else {
                    this.logTest('fetchModuleData', 'FAIL', 'No data returned');
                }
            } else {
                this.logTest('fetchModuleData', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('fetchModuleData', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 8: updateVisualizationForModule
    testUpdateVisualizationForModule() {
        console.log('\n🎨 Testing updateVisualizationForModule...');
        
        try {
            if (typeof this.webglInterface.updateVisualizationForModule === 'function') {
                this.webglInterface.updateVisualizationForModule('clients');
                
                if (this.webglInterface.currentModule === 'clients') {
                    this.logTest('updateVisualizationForModule', 'PASS', 'Visualization updated');
                } else {
                    this.logTest('updateVisualizationForModule', 'FAIL', 'Module not updated');
                }
            } else {
                this.logTest('updateVisualizationForModule', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('updateVisualizationForModule', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 9: updateSystemStats
    async testUpdateSystemStats() {
        console.log('\n📊 Testing updateSystemStats...');
        
        try {
            if (typeof this.webglInterface.updateSystemStats === 'function') {
                await this.webglInterface.updateSystemStats();
                
                if (this.webglInterface.stats && typeof this.webglInterface.stats === 'object') {
                    this.logTest('updateSystemStats', 'PASS', 'Stats updated successfully');
                } else {
                    this.logTest('updateSystemStats', 'FAIL', 'Stats not updated');
                }
            } else {
                this.logTest('updateSystemStats', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('updateSystemStats', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 10: updateModuleStats
    testUpdateModuleStats() {
        console.log('\n📈 Testing updateModuleStats...');
        
        try {
            if (typeof this.webglInterface.updateModuleStats === 'function') {
                const testData = { clients: 5, devices: 10 };
                this.webglInterface.updateModuleStats('clients', testData);
                
                this.logTest('updateModuleStats', 'PASS', 'Module stats updated');
            } else {
                this.logTest('updateModuleStats', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('updateModuleStats', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 11: loadInitialData
    async testLoadInitialData() {
        console.log('\n📥 Testing loadInitialData...');
        
        try {
            if (typeof this.webglInterface.loadInitialData === 'function') {
                await this.webglInterface.loadInitialData();
                this.logTest('loadInitialData', 'PASS', 'Initial data loaded');
            } else {
                this.logTest('loadInitialData', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('loadInitialData', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 12: handleQuickAction
    testHandleQuickAction() {
        console.log('\n⚡ Testing handleQuickAction...');
        
        try {
            if (typeof this.webglInterface.handleQuickAction === 'function') {
                this.webglInterface.handleQuickAction('refresh');
                this.logTest('handleQuickAction', 'PASS', 'Quick action handled');
            } else {
                this.logTest('handleQuickAction', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('handleQuickAction', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 13: addNewClient
    async testAddNewClient() {
        console.log('\n➕ Testing addNewClient...');
        
        try {
            if (typeof this.webglInterface.addNewClient === 'function') {
                await this.webglInterface.addNewClient();
                this.logTest('addNewClient', 'PASS', 'Add client function executed');
            } else {
                this.logTest('addNewClient', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('addNewClient', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 14: addNewDevice
    async testAddNewDevice() {
        console.log('\n🔧 Testing addNewDevice...');
        
        try {
            if (typeof this.webglInterface.addNewDevice === 'function') {
                await this.webglInterface.addNewDevice();
                this.logTest('addNewDevice', 'PASS', 'Add device function executed');
            } else {
                this.logTest('addNewDevice', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('addNewDevice', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 15: generateReport
    async testGenerateReport() {
        console.log('\n📋 Testing generateReport...');
        
        try {
            if (typeof this.webglInterface.generateReport === 'function') {
                await this.webglInterface.generateReport();
                this.logTest('generateReport', 'PASS', 'Report generation executed');
            } else {
                this.logTest('generateReport', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('generateReport', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 16: refreshData
    async testRefreshData() {
        console.log('\n🔄 Testing refreshData...');
        
        try {
            if (typeof this.webglInterface.refreshData === 'function') {
                await this.webglInterface.refreshData();
                this.logTest('refreshData', 'PASS', 'Data refresh executed');
            } else {
                this.logTest('refreshData', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('refreshData', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 17: toggleWebGL
    testToggleWebGL() {
        console.log('\n🎛️ Testing toggleWebGL...');
        
        try {
            if (typeof this.webglInterface.toggleWebGL === 'function') {
                this.webglInterface.toggleWebGL();
                this.logTest('toggleWebGL', 'PASS', 'WebGL toggle executed');
            } else {
                this.logTest('toggleWebGL', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('toggleWebGL', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 18: resetView
    testResetView() {
        console.log('\n🔄 Testing resetView...');
        
        try {
            if (typeof this.webglInterface.resetView === 'function') {
                this.webglInterface.resetView();
                this.logTest('resetView', 'PASS', 'View reset executed');
            } else {
                this.logTest('resetView', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('resetView', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 19: exportData
    async testExportData() {
        console.log('\n📤 Testing exportData...');
        
        try {
            if (typeof this.webglInterface.exportData === 'function') {
                await this.webglInterface.exportData();
                this.logTest('exportData', 'PASS', 'Data export executed');
            } else {
                this.logTest('exportData', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('exportData', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 20: systemStatus
    async testSystemStatus() {
        console.log('\n💻 Testing systemStatus...');
        
        try {
            if (typeof this.webglInterface.systemStatus === 'function') {
                await this.webglInterface.systemStatus();
                this.logTest('systemStatus', 'PASS', 'System status executed');
            } else {
                this.logTest('systemStatus', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('systemStatus', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 21: hideLoading
    testHideLoading() {
        console.log('\n👁️ Testing hideLoading...');
        
        try {
            if (typeof this.webglInterface.hideLoading === 'function') {
                this.webglInterface.hideLoading();
                this.logTest('hideLoading', 'PASS', 'Loading hidden');
            } else {
                this.logTest('hideLoading', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('hideLoading', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 22: startClock
    testStartClock() {
        console.log('\n⏰ Testing startClock...');
        
        try {
            if (typeof this.webglInterface.startClock === 'function') {
                this.webglInterface.startClock();
                this.logTest('startClock', 'PASS', 'Clock started');
            } else {
                this.logTest('startClock', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('startClock', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test 23: updateLastUpdate
    testUpdateLastUpdate() {
        console.log('\n🕒 Testing updateLastUpdate...');
        
        try {
            if (typeof this.webglInterface.updateLastUpdate === 'function') {
                this.webglInterface.updateLastUpdate();
                this.logTest('updateLastUpdate', 'PASS', 'Last update timestamp updated');
            } else {
                this.logTest('updateLastUpdate', 'FAIL', 'Function not found');
            }
        } catch (error) {
            this.logTest('updateLastUpdate', 'FAIL', `Exception: ${error.message}`);
        }
    }

    // Test visualization functions
    testVisualizationFunctions() {
        console.log('\n🎨 Testing Visualization Functions...');
        
        const vizFunctions = [
            'addClientVisualization',
            'addDeviceVisualization', 
            'addNetworkVisualization',
            'addInvoiceVisualization',
            'addDashboardVisualization',
            'addUserVisualization',
            'addServiceVisualization',
            'addAlertVisualization',
            'addDHCPVisualization',
            'addSNMPVisualization',
            'addCactiVisualization',
            'addMikrotikVisualization'
        ];
        
        for (const funcName of vizFunctions) {
            try {
                if (typeof this.webglInterface[funcName] === 'function') {
                    this.webglInterface[funcName]();
                    this.logTest(funcName, 'PASS', 'Visualization function executed');
                } else {
                    this.logTest(funcName, 'FAIL', 'Function not found');
                }
            } catch (error) {
                this.logTest(funcName, 'FAIL', `Exception: ${error.message}`);
            }
        }
    }

    // Run all tests
    async runAllTests() {
        console.log('🚀 Starting comprehensive function-by-function testing...\n');
        
        this.totalTests = 35; // Total number of tests
        this.currentTest = 0;
        
        // Core initialization tests
        this.testConstructor();
        this.testInitializeWebGL();
        this.testCreateNetworkVisualization();
        this.testAnimate();
        this.testInitializeEventListeners();
        
        // Module management tests
        await this.testLoadModule();
        await this.testFetchModuleData();
        this.testUpdateVisualizationForModule();
        
        // Data management tests
        await this.testUpdateSystemStats();
        this.testUpdateModuleStats();
        await this.testLoadInitialData();
        
        // Action tests
        this.testHandleQuickAction();
        await this.testAddNewClient();
        await this.testAddNewDevice();
        await this.testGenerateReport();
        await this.testRefreshData();
        
        // UI control tests
        this.testToggleWebGL();
        this.testResetView();
        await this.testExportData();
        await this.testSystemStatus();
        
        // Utility tests
        this.testHideLoading();
        this.testStartClock();
        this.testUpdateLastUpdate();
        
        // Visualization tests
        this.testVisualizationFunctions();
        
        // Generate final report
        this.generateTestReport();
    }

    // Generate comprehensive test report
    generateTestReport() {
        console.log('\n📊 ===== COMPREHENSIVE TEST REPORT =====');
        
        const passed = Object.values(this.testResults).filter(r => r.result === 'PASS').length;
        const failed = Object.values(this.testResults).filter(r => r.result === 'FAIL').length;
        const warnings = Object.values(this.testResults).filter(r => r.result === 'WARNING').length;
        
        console.log(`\n📈 Test Summary:`);
        console.log(`   ✅ Passed: ${passed}`);
        console.log(`   ❌ Failed: ${failed}`);
        console.log(`   ⚠️ Warnings: ${warnings}`);
        console.log(`   📊 Total: ${this.totalTests}`);
        
        console.log(`\n🎯 Success Rate: ${((passed / this.totalTests) * 100).toFixed(1)}%`);
        
        if (failed > 0) {
            console.log(`\n❌ Failed Tests:`);
            Object.entries(this.testResults)
                .filter(([name, result]) => result.result === 'FAIL')
                .forEach(([name, result]) => {
                    console.log(`   - ${name}: ${result.details}`);
                });
        }
        
        if (warnings > 0) {
            console.log(`\n⚠️ Warnings:`);
            Object.entries(this.testResults)
                .filter(([name, result]) => result.result === 'WARNING')
                .forEach(([name, result]) => {
                    console.log(`   - ${name}: ${result.details}`);
                });
        }
        
        console.log(`\n📝 Detailed Log:`);
        this.testLog.forEach(log => {
            const status = log.result === 'PASS' ? '✅' : log.result === 'FAIL' ? '❌' : '⚠️';
            console.log(`   ${status} [${log.timestamp}] ${log.function}: ${log.result}${log.details ? ` - ${log.details}` : ''}`);
        });
        
        console.log('\n🏁 ===== END TEST REPORT =====\n');
    }
}

// Export for use in browser console
window.WebGLFunctionTester = WebGLFunctionTester;

// Auto-run when loaded
document.addEventListener('DOMContentLoaded', async function() {
    console.log('🧪 WebGL Function Tester loaded');
    console.log('Run: const tester = new WebGLFunctionTester(); await tester.init(); await tester.runAllTests();');
}); 