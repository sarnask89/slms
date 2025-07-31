<?php
/**
 * WebGL Interface Step-by-Step Testing Script
 * Tests every function systematically and provides detailed results
 */

// Start output buffering
ob_start();

// Include required files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/modules/helpers/auth_helper.php';

// Set content type
header('Content-Type: text/html; charset=utf-8');

// Test configuration
$testResults = [];
$startTime = microtime(true);

// Utility functions
function logTest($testName, $result, $details = '', $category = 'general') {
    global $testResults;
    $testResults[$category][$testName] = [
        'result' => $result,
        'details' => $details,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $status = $result === 'PASS' ? '✅' : ($result === 'FAIL' ? '❌' : '⚠️');
    echo "<div style='margin: 5px 0; padding: 10px; border-left: 4px solid " . 
         ($result === 'PASS' ? '#00ff00' : ($result === 'FAIL' ? '#ff0000' : '#ffff00')) . 
         "; background: #1a1a1a;'>";
    echo "<strong>{$status} {$testName}:</strong> {$result}";
    if ($details) echo "<br><small>{$details}</small>";
    echo "</div>";
}

function testAPIEndpoint($name, $url, $expectedSuccess = true) {
    $startTime = microtime(true);
    
    // Convert relative URLs to absolute URLs
    if (!preg_match('/^https?:\/\//', $url)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url = $protocol . '://' . $host . '/' . ltrim($url, '/');
    }
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $responseTime = (microtime(true) - $startTime) * 1000;
        
        if ($error) {
            logTest($name, 'FAIL', "cURL Error: {$error}");
            return false;
        }
        
        if ($httpCode !== 200) {
            logTest($name, 'FAIL', "HTTP {$httpCode}: {$response}");
            return false;
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            logTest($name, 'FAIL', "Invalid JSON: " . json_last_error_msg());
            return false;
        }
        
        if ($expectedSuccess && (!isset($data['success']) || !$data['success'])) {
            logTest($name, 'FAIL', "API returned error: " . ($data['message'] ?? 'Unknown error'));
            return false;
        }
        
        logTest($name, 'PASS', "Response time: {$responseTime}ms");
        return true;
        
    } catch (Exception $e) {
        logTest($name, 'FAIL', "Exception: " . $e->getMessage());
        return false;
    }
}

function testDatabaseConnection() {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->query("SELECT 1");
        $result = $stmt->fetch();
        
        if ($result) {
            logTest('Database Connection', 'PASS', 'Database connection successful');
            return true;
        } else {
            logTest('Database Connection', 'FAIL', 'Database query failed');
            return false;
        }
    } catch (Exception $e) {
        logTest('Database Connection', 'FAIL', 'Database error: ' . $e->getMessage());
        return false;
    }
}

function testFilePermissions() {
    $files = [
        'webgl_api.php' => 'API endpoint',
        'webgl_module_integration.php' => 'Module integration',
        'webgl_interface.js' => 'JavaScript interface',
        'webgl_demo_integrated.php' => 'Main interface',
        'config.php' => 'Configuration',
        'modules/helpers/auth_helper.php' => 'Authentication helper'
    ];
    
    $allPassed = true;
    
    foreach ($files as $file => $description) {
        if (file_exists($file)) {
            if (is_readable($file)) {
                logTest("File: {$description}", 'PASS', "File exists and readable");
            } else {
                logTest("File: {$description}", 'FAIL', "File exists but not readable");
                $allPassed = false;
            }
        } else {
            logTest("File: {$description}", 'FAIL', "File not found: {$file}");
            $allPassed = false;
        }
    }
    
    return $allPassed;
}

function testWebGLSupport() {
    // Test if WebGL is supported by checking browser capabilities
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (strpos($userAgent, 'Chrome') !== false) {
        logTest('WebGL Browser Support', 'PASS', 'Chrome detected - WebGL supported');
        return true;
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        logTest('WebGL Browser Support', 'PASS', 'Firefox detected - WebGL supported');
        return true;
    } elseif (strpos($userAgent, 'Safari') !== false) {
        logTest('WebGL Browser Support', 'PASS', 'Safari detected - WebGL supported');
        return true;
    } else {
        logTest('WebGL Browser Support', 'WARNING', 'Unknown browser - WebGL support uncertain');
        return false;
    }
}

function testPHPExtensions() {
    $requiredExtensions = [
        'pdo' => 'PDO Database',
        'pdo_mysql' => 'PDO MySQL',
        'json' => 'JSON',
        'curl' => 'cURL',
        'session' => 'Sessions'
    ];
    
    $allPassed = true;
    
    foreach ($requiredExtensions as $ext => $description) {
        if (extension_loaded($ext)) {
            logTest("PHP Extension: {$description}", 'PASS', "Extension loaded");
        } else {
            logTest("PHP Extension: {$description}", 'FAIL', "Extension not loaded");
            $allPassed = false;
        }
    }
    
    return $allPassed;
}

function testSLMSDatabaseTables() {
    try {
        $pdo = get_pdo();
        
        $requiredTables = [
            'clients' => 'Client management',
            'devices' => 'Device management', 
            'networks' => 'Network management',
            'invoices' => 'Invoice management',
            'users' => 'User management',
            'services' => 'Service management',
            'network_alerts' => 'Alert management',
            'menu_items' => 'Menu system'
        ];
        
        $allPassed = true;
        
        foreach ($requiredTables as $table => $description) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
                $count = $stmt->fetchColumn();
                logTest("Database Table: {$description}", 'PASS', "Table exists with {$count} records");
            } catch (Exception $e) {
                logTest("Database Table: {$description}", 'FAIL', "Table error: " . $e->getMessage());
                $allPassed = false;
            }
        }
        
        return $allPassed;
        
    } catch (Exception $e) {
        logTest('Database Tables', 'FAIL', 'Database connection failed: ' . $e->getMessage());
        return false;
    }
}

function testWebGLInterfaceFunctions() {
    // Test if the JavaScript interface file exists and is valid
    $jsFile = 'webgl_interface.js';
    
    if (!file_exists($jsFile)) {
        logTest('WebGL Interface File', 'FAIL', 'JavaScript interface file not found');
        return false;
    }
    
    $content = file_get_contents($jsFile);
    
    // Check for key functions
    $requiredFunctions = [
        'SLMSWebGLInterface',
        'initializeWebGL',
        'createNetworkVisualization',
        'animate',
        'loadModule',
        'updateSystemStats',
        'handleQuickAction'
    ];
    
    $allFound = true;
    
    foreach ($requiredFunctions as $function) {
        if (strpos($content, $function) !== false) {
            logTest("JavaScript Function: {$function}", 'PASS', "Function found in interface");
        } else {
            logTest("JavaScript Function: {$function}", 'FAIL', "Function not found in interface");
            $allFound = false;
        }
    }
    
    return $allFound;
}

function testAPIIntegration() {
    // Test APIs directly by including them
    $allPassed = true;
    
    // Test 1: System Status API
    try {
        $_GET['action'] = 'system_status';
        ob_start();
        include 'webgl_api.php';
        $response = ob_get_clean();
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            logTest('System Status API', 'PASS', 'API responded successfully');
        } else {
            logTest('System Status API', 'FAIL', 'API returned error: ' . ($data['message'] ?? 'Unknown error'));
            $allPassed = false;
        }
    } catch (Exception $e) {
        logTest('System Status API', 'FAIL', 'Exception: ' . $e->getMessage());
        $allPassed = false;
    }
    
    // Test 2: Get Stats API
    try {
        $_GET['action'] = 'get_stats';
        ob_start();
        include 'webgl_api.php';
        $response = ob_get_clean();
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            logTest('Get Statistics API', 'PASS', 'API responded successfully');
        } else {
            logTest('Get Statistics API', 'FAIL', 'API returned error: ' . ($data['message'] ?? 'Unknown error'));
            $allPassed = false;
        }
    } catch (Exception $e) {
        logTest('Get Statistics API', 'FAIL', 'Exception: ' . $e->getMessage());
        $allPassed = false;
    }
    
    // Test 3: Get Clients API
    try {
        $_GET['action'] = 'get_clients';
        ob_start();
        include 'webgl_api.php';
        $response = ob_get_clean();
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            logTest('Get Clients API', 'PASS', 'API responded successfully');
        } else {
            logTest('Get Clients API', 'FAIL', 'API returned error: ' . ($data['message'] ?? 'Unknown error'));
            $allPassed = false;
        }
    } catch (Exception $e) {
        logTest('Get Clients API', 'FAIL', 'Exception: ' . $e->getMessage());
        $allPassed = false;
    }
    
    // Test 4: Get Devices API
    try {
        $_GET['action'] = 'get_devices';
        ob_start();
        include 'webgl_api.php';
        $response = ob_get_clean();
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            logTest('Get Devices API', 'PASS', 'API responded successfully');
        } else {
            logTest('Get Devices API', 'FAIL', 'API returned error: ' . ($data['message'] ?? 'Unknown error'));
            $allPassed = false;
        }
    } catch (Exception $e) {
        logTest('Get Devices API', 'FAIL', 'Exception: ' . $e->getMessage());
        $allPassed = false;
    }
    
    // Test 5: Get Networks API
    try {
        $_GET['action'] = 'get_networks';
        ob_start();
        include 'webgl_api.php';
        $response = ob_get_clean();
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            logTest('Get Networks API', 'PASS', 'API responded successfully');
        } else {
            logTest('Get Networks API', 'FAIL', 'API returned error: ' . ($data['message'] ?? 'Unknown error'));
            $allPassed = false;
        }
    } catch (Exception $e) {
        logTest('Get Networks API', 'FAIL', 'Exception: ' . $e->getMessage());
        $allPassed = false;
    }
    
    // Test 6: Get Invoices API
    try {
        $_GET['action'] = 'get_invoices';
        ob_start();
        include 'webgl_api.php';
        $response = ob_get_clean();
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            logTest('Get Invoices API', 'PASS', 'API responded successfully');
        } else {
            logTest('Get Invoices API', 'FAIL', 'API returned error: ' . ($data['message'] ?? 'Unknown error'));
            $allPassed = false;
        }
    } catch (Exception $e) {
        logTest('Get Invoices API', 'FAIL', 'Exception: ' . $e->getMessage());
        $allPassed = false;
    }
    
    // Test 7: Get Users API
    try {
        $_GET['action'] = 'get_users';
        ob_start();
        include 'webgl_api.php';
        $response = ob_get_clean();
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            logTest('Get Users API', 'PASS', 'API responded successfully');
        } else {
            logTest('Get Users API', 'FAIL', 'API returned error: ' . ($data['message'] ?? 'Unknown error'));
            $allPassed = false;
        }
    } catch (Exception $e) {
        logTest('Get Users API', 'FAIL', 'Exception: ' . $e->getMessage());
        $allPassed = false;
    }
    
    // Test 8: Get Services API
    try {
        $_GET['action'] = 'get_services';
        ob_start();
        include 'webgl_api.php';
        $response = ob_get_clean();
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            logTest('Get Services API', 'PASS', 'API responded successfully');
        } else {
            logTest('Get Services API', 'FAIL', 'API returned error: ' . ($data['message'] ?? 'Unknown error'));
            $allPassed = false;
        }
    } catch (Exception $e) {
        logTest('Get Services API', 'FAIL', 'Exception: ' . $e->getMessage());
        $allPassed = false;
    }
    
    // Test 9: Get Alerts API
    try {
        $_GET['action'] = 'get_alerts';
        ob_start();
        include 'webgl_api.php';
        $response = ob_get_clean();
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            logTest('Get Alerts API', 'PASS', 'API responded successfully');
        } else {
            logTest('Get Alerts API', 'FAIL', 'API returned error: ' . ($data['message'] ?? 'Unknown error'));
            $allPassed = false;
        }
    } catch (Exception $e) {
        logTest('Get Alerts API', 'FAIL', 'Exception: ' . $e->getMessage());
        $allPassed = false;
    }
    
    // Test 10: Module Integration API
    try {
        $_GET['action'] = 'load_module';
        $_GET['module'] = 'clients';
        ob_start();
        include 'webgl_module_integration.php';
        $response = ob_get_clean();
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            logTest('Module Integration API', 'PASS', 'API responded successfully');
        } else {
            logTest('Module Integration API', 'FAIL', 'API returned error: ' . ($data['message'] ?? 'Unknown error'));
            $allPassed = false;
        }
    } catch (Exception $e) {
        logTest('Module Integration API', 'FAIL', 'Exception: ' . $e->getMessage());
        $allPassed = false;
    }
    
    return $allPassed;
}

function testPerformance() {
    // Test API response times
    $startTime = microtime(true);
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url = $protocol . '://' . $host . '/webgl_api.php?action=system_status';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $responseTime = (microtime(true) - $startTime) * 1000;
    
    if ($responseTime < 1000) {
        logTest('API Performance', 'PASS', "Response time: {$responseTime}ms");
        return true;
    } else {
        logTest('API Performance', 'WARNING', "Slow response time: {$responseTime}ms");
        return false;
    }
}

function generateTestReport() {
    global $testResults, $startTime;
    
    $totalTime = microtime(true) - $startTime;
    
    $totalTests = 0;
    $passedTests = 0;
    $failedTests = 0;
    $warningTests = 0;
    
    foreach ($testResults as $category => $tests) {
        foreach ($tests as $test) {
            $totalTests++;
            if ($test['result'] === 'PASS') $passedTests++;
            elseif ($test['result'] === 'FAIL') $failedTests++;
            elseif ($test['result'] === 'WARNING') $warningTests++;
        }
    }
    
    $successRate = $totalTests > 0 ? ($passedTests / $totalTests) * 100 : 0;
    
    echo "<div style='background: #2a2a2a; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h2>📊 Test Summary Report</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;'>";
    echo "<div style='background: #1a1a1a; padding: 15px; border-radius: 5px; text-align: center;'>";
    echo "<div style='font-size: 24px; color: #00ff00;'>{$totalTests}</div>";
    echo "<div style='font-size: 12px; color: #888;'>Total Tests</div>";
    echo "</div>";
    echo "<div style='background: #1a1a1a; padding: 15px; border-radius: 5px; text-align: center;'>";
    echo "<div style='font-size: 24px; color: #00ff00;'>{$passedTests}</div>";
    echo "<div style='font-size: 12px; color: #888;'>Passed</div>";
    echo "</div>";
    echo "<div style='background: #1a1a1a; padding: 15px; border-radius: 5px; text-align: center;'>";
    echo "<div style='font-size: 24px; color: #ff0000;'>{$failedTests}</div>";
    echo "<div style='font-size: 12px; color: #888;'>Failed</div>";
    echo "</div>";
    echo "<div style='background: #1a1a1a; padding: 15px; border-radius: 5px; text-align: center;'>";
    echo "<div style='font-size: 24px; color: #ffff00;'>{$warningTests}</div>";
    echo "<div style='font-size: 12px; color: #888;'>Warnings</div>";
    echo "</div>";
    echo "<div style='background: #1a1a1a; padding: 15px; border-radius: 5px; text-align: center;'>";
    echo "<div style='font-size: 24px; color: #00ff00;'>" . round($successRate, 1) . "%</div>";
    echo "<div style='font-size: 12px; color: #888;'>Success Rate</div>";
    echo "</div>";
    echo "<div style='background: #1a1a1a; padding: 15px; border-radius: 5px; text-align: center;'>";
    echo "<div style='font-size: 24px; color: #0080ff;'>" . round($totalTime, 2) . "s</div>";
    echo "<div style='font-size: 12px; color: #888;'>Test Time</div>";
    echo "</div>";
    echo "</div>";
    
    if ($failedTests === 0) {
        echo "<div style='background: #1a3a1a; padding: 15px; border-radius: 5px; border-left: 4px solid #00ff00;'>";
        echo "<strong>🎉 All critical tests passed! WebGL interface is ready for use.</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #3a1a1a; padding: 15px; border-radius: 5px; border-left: 4px solid #ff0000;'>";
        echo "<strong>⚠️ {$failedTests} tests failed - review required before deployment.</strong>";
        echo "</div>";
    }
    
    echo "</div>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebGL Interface Step-by-Step Testing</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #0a0a0a;
            color: #00ff00;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #00ff00;
            padding-bottom: 20px;
        }
        .test-section {
            background: #1a1a1a;
            border: 1px solid #333;
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
        }
        .test-section h2 {
            margin-top: 0;
            color: #00ff00;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #333;
            border-radius: 10px;
            overflow: hidden;
            margin: 15px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #00ff00, #00cc00);
            transition: width 0.5s ease;
        }
        .refresh-btn {
            background: #333;
            color: #00ff00;
            border: 2px solid #00ff00;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
            margin: 10px 5px;
        }
        .refresh-btn:hover {
            background: #00ff00;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 WebGL Interface Step-by-Step Testing</h1>
            <p>Comprehensive testing of all WebGL interface components</p>
            <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Tests</button>
            <button class="refresh-btn" onclick="window.open('webgl_comprehensive_test.html', '_blank')">🧪 Open Interactive Tests</button>
        </div>

        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
        </div>

        <?php
        // Run all tests
        echo "<div class='test-section'>";
        echo "<h2>🔧 System Prerequisites</h2>";
        
        $prerequisitesPassed = true;
        
        // Test PHP extensions
        if (!testPHPExtensions()) {
            $prerequisitesPassed = false;
        }
        
        // Test file permissions
        if (!testFilePermissions()) {
            $prerequisitesPassed = false;
        }
        
        // Test database connection
        if (!testDatabaseConnection()) {
            $prerequisitesPassed = false;
        }
        
        // Test WebGL browser support
        testWebGLSupport();
        
        echo "</div>";
        
        if ($prerequisitesPassed) {
            echo "<div class='test-section'>";
            echo "<h2>🗄️ Database Tests</h2>";
            testSLMSDatabaseTables();
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>🌐 API Tests</h2>";
            testAPIIntegration();
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>🎨 WebGL Interface Tests</h2>";
            testWebGLInterfaceFunctions();
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>📈 Performance Tests</h2>";
            testPerformance();
            echo "</div>";
        } else {
            echo "<div style='background: #3a1a1a; padding: 20px; border-radius: 5px; border-left: 4px solid #ff0000; margin: 20px 0;'>";
            echo "<strong>❌ Prerequisites failed - cannot continue with full testing.</strong>";
            echo "<br>Please fix the failed prerequisites before running the complete test suite.";
            echo "</div>";
        }
        
        // Generate final report
        generateTestReport();
        ?>
    </div>

    <script>
        // Update progress bar
        function updateProgress() {
            const totalTests = document.querySelectorAll('[style*="border-left"]').length;
            const passedTests = document.querySelectorAll('[style*="#00ff00"]').length;
            const progress = totalTests > 0 ? (passedTests / totalTests) * 100 : 0;
            
            document.getElementById('progress-fill').style.width = progress + '%';
        }
        
        // Update progress when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(updateProgress, 100);
        });
    </script>
</body>
</html> 