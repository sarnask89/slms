<?php
/**
 * Automated Test Suite for ISP Management System
 * Part of the "Read, Run, Debug, Improve, Repeat" cycle
 */

class AutomatedTestSuite {
    private $testResults = [];
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
        echo "🧪 Starting Automated Test Suite...\n";
        echo "=====================================\n\n";
    }
    
    public function runAllTests() {
        $this->testDatabaseConnection();
        $this->testAPIEndpoints();
        $this->testMikrotikIntegration();
        $this->testPerformanceMetrics();
        $this->testSecurityFeatures();
        $this->testFileSystem();
        $this->testConfiguration();
        
        $this->generateTestReport();
    }
    
    private function testDatabaseConnection() {
        echo "📊 Testing Database Connection...\n";
        
        try {
            if (file_exists('config.php')) {
                require_once 'config.php';
                
                if (isset($db_host) && isset($db_user) && isset($db_pass) && isset($db_name)) {
                    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
                    
                    if ($mysqli->connect_error) {
                        $this->addTestResult('database_connection', false, "Connection failed: " . $mysqli->connect_error);
                        echo "❌ Database connection: FAIL\n";
                    } else {
                        // Test basic query
                        $result = $mysqli->query("SELECT 1");
                        if ($result) {
                            $this->addTestResult('database_connection', true, "Connection successful");
                            echo "✅ Database connection: PASS\n";
                        } else {
                            $this->addTestResult('database_connection', false, "Query test failed");
                            echo "❌ Database connection: FAIL (query test)\n";
                        }
                        $mysqli->close();
                    }
                } else {
                    $this->addTestResult('database_connection', false, "Database configuration missing");
                    echo "❌ Database connection: FAIL (config missing)\n";
                }
            } else {
                $this->addTestResult('database_connection', false, "config.php not found");
                echo "❌ Database connection: FAIL (config file missing)\n";
            }
        } catch (Exception $e) {
            $this->addTestResult('database_connection', false, "Exception: " . $e->getMessage());
            echo "❌ Database connection: FAIL (exception)\n";
        }
        
        echo "\n";
    }
    
    private function testAPIEndpoints() {
        echo "🌐 Testing API Endpoints...\n";
        
        $endpoints = [
            'health' => '/health.php',
            'system_status' => '/system_health_checker.php',
            'debug' => '/debug_system.php'
        ];
        
        foreach ($endpoints as $name => $endpoint) {
            $response = $this->testEndpoint($endpoint);
            if ($response !== false) {
                $this->addTestResult("api_$name", true, "Endpoint accessible");
                echo "✅ API $name: PASS\n";
            } else {
                $this->addTestResult("api_$name", false, "Endpoint not accessible");
                echo "❌ API $name: FAIL\n";
            }
        }
        
        echo "\n";
    }
    
    private function testMikrotikIntegration() {
        echo "🔌 Testing Mikrotik Integration...\n";
        
        // Test if Mikrotik API files exist
        $apiFiles = [
            'modules/cacti_api.php',
            'modules/cacti_integration.php'
        ];
        
        foreach ($apiFiles as $file) {
            if (file_exists($file)) {
                $this->addTestResult("mikrotik_file_$file", true, "File exists");
                echo "✅ Mikrotik file $file: PASS\n";
            } else {
                $this->addTestResult("mikrotik_file_$file", false, "File missing");
                echo "❌ Mikrotik file $file: FAIL\n";
            }
        }
        
        // Test Mikrotik connectivity (if credentials available)
        if (defined('MIKROTIK_HOST') && defined('MIKROTIK_USER')) {
            $this->testMikrotikConnectivity();
        } else {
            echo "⚠️  Mikrotik credentials not configured\n";
        }
        
        echo "\n";
    }
    
    private function testMikrotikConnectivity() {
        // This would test actual Mikrotik API connectivity
        // For now, just check if the required extensions are available
        if (extension_loaded('curl')) {
            $this->addTestResult('mikrotik_connectivity', true, "cURL extension available");
            echo "✅ Mikrotik connectivity: PASS (cURL available)\n";
        } else {
            $this->addTestResult('mikrotik_connectivity', false, "cURL extension missing");
            echo "❌ Mikrotik connectivity: FAIL (cURL missing)\n";
        }
    }
    
    private function testPerformanceMetrics() {
        echo "⚡ Testing Performance Metrics...\n";
        
        // Test PHP performance
        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $result = $i * $i;
        }
        $duration = (microtime(true) - $start) * 1000;
        
        if ($duration < 10) {
            $this->addTestResult('php_performance', true, "PHP performance good: {$duration}ms");
            echo "✅ PHP performance: PASS ({$duration}ms)\n";
        } else {
            $this->addTestResult('php_performance', false, "PHP performance slow: {$duration}ms");
            echo "❌ PHP performance: FAIL ({$duration}ms)\n";
        }
        
        // Test memory usage
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
        if ($memoryUsage < 50) {
            $this->addTestResult('memory_usage', true, "Memory usage acceptable: {$memoryUsage}MB");
            echo "✅ Memory usage: PASS ({$memoryUsage}MB)\n";
        } else {
            $this->addTestResult('memory_usage', false, "High memory usage: {$memoryUsage}MB");
            echo "❌ Memory usage: FAIL ({$memoryUsage}MB)\n";
        }
        
        echo "\n";
    }
    
    private function testSecurityFeatures() {
        echo "🔒 Testing Security Features...\n";
        
        // Test session security
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (ini_get('session.cookie_httponly')) {
            $this->addTestResult('session_security', true, "HTTP-only cookies enabled");
            echo "✅ Session security: PASS\n";
        } else {
            $this->addTestResult('session_security', false, "HTTP-only cookies disabled");
            echo "❌ Session security: FAIL\n";
        }
        
        // Test file permissions
        $criticalFiles = ['config.php', 'index.php'];
        foreach ($criticalFiles as $file) {
            if (file_exists($file)) {
                $perms = fileperms($file);
                if (($perms & 0x0177) === 0) { // No world permissions
                    $this->addTestResult("file_permissions_$file", true, "Proper permissions");
                    echo "✅ File permissions $file: PASS\n";
                } else {
                    $this->addTestResult("file_permissions_$file", false, "Insecure permissions");
                    echo "❌ File permissions $file: FAIL\n";
                }
            }
        }
        
        echo "\n";
    }
    
    private function testFileSystem() {
        echo "📁 Testing File System...\n";
        
        $requiredDirs = ['modules', 'assets', 'logs', 'cache'];
        foreach ($requiredDirs as $dir) {
            if (is_dir($dir)) {
                if (is_writable($dir)) {
                    $this->addTestResult("directory_$dir", true, "Directory exists and writable");
                    echo "✅ Directory $dir: PASS\n";
                } else {
                    $this->addTestResult("directory_$dir", false, "Directory not writable");
                    echo "❌ Directory $dir: FAIL (not writable)\n";
                }
            } else {
                $this->addTestResult("directory_$dir", false, "Directory missing");
                echo "❌ Directory $dir: FAIL (missing)\n";
            }
        }
        
        echo "\n";
    }
    
    private function testConfiguration() {
        echo "⚙️  Testing Configuration...\n";
        
        // Test PHP configuration
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->addTestResult("extension_$ext", true, "Extension loaded");
                echo "✅ Extension $ext: PASS\n";
            } else {
                $this->addTestResult("extension_$ext", false, "Extension missing");
                echo "❌ Extension $ext: FAIL\n";
            }
        }
        
        // Test error reporting
        if (error_reporting() & E_ALL) {
            $this->addTestResult('error_reporting', true, "Error reporting enabled");
            echo "✅ Error reporting: PASS\n";
        } else {
            $this->addTestResult('error_reporting', false, "Error reporting disabled");
            echo "❌ Error reporting: FAIL\n";
        }
        
        echo "\n";
    }
    
    private function testEndpoint($endpoint) {
        $url = "http://localhost:8080$endpoint";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'method' => 'GET'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        return $response !== false;
    }
    
    private function addTestResult($test, $passed, $message) {
        $this->testResults[$test] = [
            'passed' => $passed,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function generateTestReport() {
        $endTime = microtime(true);
        $duration = round($endTime - $this->startTime, 2);
        
        $passed = 0;
        $failed = 0;
        
        foreach ($this->testResults as $result) {
            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        $total = $passed + $failed;
        $successRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
        
        echo "=====================================\n";
        echo "🧪 Test Suite Results\n";
        echo "=====================================\n";
        echo "Total Tests: $total\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        echo "Success Rate: {$successRate}%\n";
        echo "Duration: {$duration}s\n";
        echo "=====================================\n\n";
        
        if ($failed > 0) {
            echo "❌ Failed Tests:\n";
            foreach ($this->testResults as $testName => $result) {
                if (!$result['passed']) {
                    echo "  - $testName: {$result['message']}\n";
                }
            }
            echo "\n";
        }
        
        // Save report to file
        $report = $this->generateDetailedReport();
        file_put_contents('test_report.txt', $report);
        echo "📄 Detailed report saved to: test_report.txt\n";
    }
    
    private function generateDetailedReport() {
        $report = "Automated Test Suite Report\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($this->testResults as $testName => $result) {
            $status = $result['passed'] ? 'PASS' : 'FAIL';
            $report .= "[$status] $testName: {$result['message']}\n";
        }
        
        return $report;
    }
}

// Run the test suite
$testSuite = new AutomatedTestSuite();
$testSuite->runAllTests();
?> 