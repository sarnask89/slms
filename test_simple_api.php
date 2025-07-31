<?php
/**
 * Simple API Test - Isolate configuration issues
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Test database connection
try {
    // Try different config files
    $configFiles = [
        __DIR__ . '/modules/config.php',
        __DIR__ . '/config.php'
    ];
    
    $pdo = null;
    $configUsed = null;
    
    foreach ($configFiles as $configFile) {
        if (file_exists($configFile)) {
            try {
                // Include config file
                require_once $configFile;
                
                // Test database connection
                $pdo = get_pdo();
                $configUsed = $configFile;
                break;
            } catch (Exception $e) {
                error_log("Failed to use config: $configFile - " . $e->getMessage());
                continue;
            }
        }
    }
    
    if ($pdo) {
        // Test database query
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'message' => 'API is working',
                'database' => 'Connected',
                'config_used' => basename($configUsed),
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'API Error: ' . $e->getMessage()
    ]);
}
?> 