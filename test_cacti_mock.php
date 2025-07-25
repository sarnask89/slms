<?php
/**
 * Test Cacti Mock Mode
 */

require_once 'config.php';
require_once 'modules/cacti_api.php';

echo "Testing Cacti Mock Mode...\n\n";

try {
    $cacti_api = new CactiAPI();
    
    echo "Mock Mode: " . ($cacti_api->isMockMode() ? "YES" : "NO") . "\n\n";
    
    // Test status
    echo "1. Testing Status API...\n";
    $status = $cacti_api->getStatus();
    echo "Status: " . ($status['success'] ? "SUCCESS" : "FAILED") . "\n";
    if ($status['success']) {
        echo "Data: " . json_encode($status['data'], JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";
    
    // Test version
    echo "2. Testing Version API...\n";
    $version = $cacti_api->getVersion();
    echo "Version: " . ($version['success'] ? "SUCCESS" : "FAILED") . "\n";
    if ($version['success']) {
        echo "Data: " . json_encode($version['data'], JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";
    
    // Test devices
    echo "3. Testing Devices API...\n";
    $devices = $cacti_api->getDevices();
    echo "Devices: " . (isset($devices['devices']) ? "SUCCESS" : "FAILED") . "\n";
    if (isset($devices['devices'])) {
        echo "Found " . count($devices['devices']) . " devices:\n";
        foreach ($devices['devices'] as $device) {
            echo "  - " . $device['hostname'] . " (" . $device['status'] . ")\n";
        }
    }
    echo "\n";
    
    // Test adding device
    echo "4. Testing Add Device...\n";
    $result = cacti_add_device('192.168.1.100', 'public', '2');
    echo "Add Device: " . ($result['success'] ? "SUCCESS" : "FAILED") . "\n";
    if ($result['success']) {
        echo "Result: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";
    
    echo "✅ All tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 