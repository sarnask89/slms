<?php
/**
 * Cacti Integration Debug Script
 * Comprehensive diagnostic tool for Cacti integration issues
 */

require_once 'config.php';
require_once 'modules/cacti_api.php';

echo "================================================\n";
echo "           Cacti Integration Debug Report\n";
echo "================================================\n\n";

$issues = [];
$warnings = [];
$success = [];

// Test 1: Check PHP Extensions
echo "1. Checking PHP Extensions...\n";
if (!extension_loaded('curl')) {
    $issues[] = "cURL extension not loaded";
} else {
    $success[] = "cURL extension loaded";
}

if (!extension_loaded('json')) {
    $issues[] = "JSON extension not loaded";
} else {
    $success[] = "JSON extension loaded";
}

if (!extension_loaded('snmp')) {
    $warnings[] = "SNMP extension not loaded (optional but recommended)";
} else {
    $success[] = "SNMP extension loaded";
}

// Test 2: Check Configuration
echo "2. Checking Configuration...\n";
if (!file_exists('config.php')) {
    $issues[] = "config.php not found";
} else {
    $success[] = "config.php found";
}

if (!file_exists('modules/cacti_api.php')) {
    $issues[] = "modules/cacti_api.php not found";
} else {
    $success[] = "modules/cacti_api.php found";
}

// Test 3: Check Database Connection
echo "3. Testing Database Connection...\n";
try {
    $pdo = get_pdo();
    $success[] = "Database connection successful";
} catch (Exception $e) {
    $issues[] = "Database connection failed: " . $e->getMessage();
}

// Test 4: Test Cacti API Class
echo "4. Testing Cacti API Class...\n";
if (!class_exists('CactiAPI')) {
    $issues[] = "CactiAPI class not found";
} else {
    $success[] = "CactiAPI class found";
    
    try {
        $cacti_api = new CactiAPI();
        $success[] = "CactiAPI instance created successfully";
        
        // Test API methods
        $methods = ['getStatus', 'getVersion', 'getDevices'];
        foreach ($methods as $method) {
            if (method_exists($cacti_api, $method)) {
                $success[] = "Method $method exists";
            } else {
                $warnings[] = "Method $method not found";
            }
        }
    } catch (Exception $e) {
        $issues[] = "CactiAPI instantiation failed: " . $e->getMessage();
    }
}

// Test 5: Test Cacti Container Connectivity
echo "5. Testing Cacti Container Connectivity...\n";
$cacti_url = 'http://10.0.222.223:8081';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $cacti_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    $issues[] = "Cacti container connection failed: $error";
} else {
    if ($http_code == 200) {
        $success[] = "Cacti container responding (HTTP $http_code)";
        
        // Check if it's a real Cacti installation
        if (strpos($response, 'Cacti') !== false) {
            if (strpos($response, 'placeholder') !== false) {
                $warnings[] = "Cacti container is serving placeholder content (not real Cacti)";
            } else {
                $success[] = "Cacti container appears to be serving Cacti content";
            }
        } else {
            $warnings[] = "Cacti container response doesn't contain expected Cacti content";
        }
    } else {
        $issues[] = "Cacti container returned HTTP $http_code";
    }
}

// Test 6: Test API Endpoints
echo "6. Testing API Endpoints...\n";
$api_endpoints = [
    '/api/v1/status',
    '/api/v1/version',
    '/api/v1/devices'
];

foreach ($api_endpoints as $endpoint) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $cacti_url . $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 404) {
        $warnings[] = "API endpoint $endpoint not found (HTTP 404)";
    } elseif ($http_code == 200) {
        $success[] = "API endpoint $endpoint responding (HTTP 200)";
    } else {
        $warnings[] = "API endpoint $endpoint returned HTTP $http_code";
    }
}

// Test 7: Test SNMP Functionality
echo "7. Testing SNMP Functionality...\n";
if (function_exists('snmpget')) {
    $success[] = "snmpget function available";
    
    // Test SNMP to a common device
    $test_host = '127.0.0.1';
    $test_community = 'public';
    
    $snmp_result = @snmpget($test_host, $test_community, '.1.3.6.1.2.1.1.1.0');
    if ($snmp_result !== false) {
        $success[] = "SNMP test successful to $test_host";
    } else {
        $warnings[] = "SNMP test failed to $test_host (this is normal if no SNMP agent is running)";
    }
} else {
    $warnings[] = "snmpget function not available";
}

// Test 8: Check Helper Functions
echo "8. Checking Helper Functions...\n";
$helper_functions = [
    'cacti_add_device',
    'cacti_get_device_data',
    'cacti_get_graph_data',
    'cacti_check_status'
];

foreach ($helper_functions as $function) {
    if (function_exists($function)) {
        $success[] = "Helper function $function exists";
    } else {
        $warnings[] = "Helper function $function not found";
    }
}

// Generate Report
echo "\n================================================\n";
echo "                    SUMMARY\n";
echo "================================================\n\n";

echo "✅ SUCCESS (" . count($success) . "):\n";
foreach ($success as $item) {
    echo "   • $item\n";
}

if (!empty($warnings)) {
    echo "\n⚠️  WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $item) {
        echo "   • $item\n";
    }
}

if (!empty($issues)) {
    echo "\n❌ ISSUES (" . count($issues) . "):\n";
    foreach ($issues as $item) {
        echo "   • $item\n";
    }
}

echo "\n================================================\n";
echo "              RECOMMENDATIONS\n";
echo "================================================\n\n";

if (!empty($issues)) {
    echo "CRITICAL ISSUES TO FIX:\n";
    foreach ($issues as $issue) {
        echo "   • $issue\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "RECOMMENDED IMPROVEMENTS:\n";
    foreach ($warnings as $warning) {
        echo "   • $warning\n";
    }
    echo "\n";
}

// Specific recommendations
echo "NEXT STEPS:\n";
echo "1. Install a real Cacti instance or configure the container properly\n";
echo "2. Update the Cacti API endpoints to match your actual Cacti installation\n";
echo "3. Configure SNMP community strings for your network devices\n";
echo "4. Test the integration with actual network devices\n";
echo "\n";

echo "CURRENT STATUS: ";
if (empty($issues)) {
    echo "✅ READY FOR TESTING\n";
} else {
    echo "❌ NEEDS FIXES\n";
}

echo "\n================================================\n";
echo "Debug report completed.\n";
?> 