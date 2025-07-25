<?php
// Test file for sLMS system debugging
// This file will test various components and provide debugging information

// Start output buffering to capture all output
ob_start();

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>\n";
echo "<html lang='en'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>sLMS System Test</title>\n";
echo "    <style>\n";
echo "        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }\n";
echo "        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo "        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }\n";
echo "        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }\n";
echo "        .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }\n";
echo "        .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }\n";
echo "        .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }\n";
echo "        pre { background-color: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }\n";
echo "        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }\n";
echo "        h2 { color: #555; margin-top: 30px; }\n";
echo "        .status { font-weight: bold; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";
echo "<div class='container'>\n";
echo "<h1>🔧 sLMS System Test & Debug Report</h1>\n";
echo "<p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>\n";

// Test 1: PHP Version and Extensions
echo "<div class='test-section info'>\n";
echo "<h2>📋 System Information</h2>\n";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>\n";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>\n";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</p>\n";
echo "<p><strong>Script Path:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "</p>\n";
echo "</div>\n";

// Test 2: Required PHP Extensions
echo "<div class='test-section'>\n";
echo "<h2>🔌 PHP Extensions Check</h2>\n";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ $ext - Available</p>\n";
    } else {
        echo "<p class='error'>❌ $ext - Missing</p>\n";
        $missing_extensions[] = $ext;
    }
}

if (empty($missing_extensions)) {
    echo "<p class='success'><strong>All required extensions are available!</strong></p>\n";
} else {
    echo "<p class='error'><strong>Missing extensions:</strong> " . implode(', ', $missing_extensions) . "</p>\n";
}

// PHP Engine Compatibility Test
echo "<h3>🔧 PHP Engine Compatibility</h3>\n";
echo "<p><strong>PHP SAPI:</strong> " . php_sapi_name() . "</p>\n";
echo "<p><strong>Zend Engine Version:</strong> " . zend_version() . "</p>\n";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>\n";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . " seconds</p>\n";
echo "<p><strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "</p>\n";
echo "<p><strong>Post Max Size:</strong> " . ini_get('post_max_size') . "</p>\n";

// Check for JIT warnings
if (function_exists('opcache_get_status')) {
    $opcache_status = opcache_get_status();
    if ($opcache_status && isset($opcache_status['jit'])) {
        echo "<p><strong>JIT Status:</strong> " . ($opcache_status['jit']['enabled'] ? 'Enabled' : 'Disabled') . "</p>\n";
    }
}

echo "</div>\n";

// Test 3: Configuration File
echo "<div class='test-section'>\n";
echo "<h2>⚙️ Configuration Test</h2>\n";

if (file_exists('config.php')) {
    echo "<p class='success'>✅ config.php file exists</p>\n";
    
    // Include config file
    try {
        require_once 'config.php';
        echo "<p class='success'>✅ config.php loaded successfully</p>\n";
        
        // Test if database variables are defined
        $db_vars = ['db_host', 'db_name', 'db_user', 'db_pass', 'db_charset'];
        $missing_vars = [];
        
        foreach ($db_vars as $var) {
            if (defined($var) || isset($$var)) {
                echo "<p class='success'>✅ $var - Defined</p>\n";
            } else {
                echo "<p class='error'>❌ $var - Not defined</p>\n";
                $missing_vars[] = $var;
            }
        }
        
        if (empty($missing_vars)) {
            echo "<p class='success'><strong>All database variables are properly configured!</strong></p>\n";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error loading config.php: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
} else {
    echo "<p class='error'>❌ config.php file not found</p>\n";
}
echo "</div>\n";

// Test 4: Database Connection
echo "<div class='test-section'>\n";
echo "<h2>🗄️ Database Connection Test</h2>\n";

if (function_exists('get_pdo')) {
    try {
        $pdo = get_pdo();
        echo "<p class='success'>✅ Database connection successful</p>\n";
        
        // Test a simple query
        $stmt = $pdo->query("SELECT VERSION() as version");
        $result = $stmt->fetch();
        echo "<p class='success'>✅ Database query test successful</p>\n";
        echo "<p><strong>MySQL Version:</strong> " . htmlspecialchars($result['version']) . "</p>\n";
        
        // Test if we can access the database
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p class='success'>✅ Database access confirmed</p>\n";
        echo "<p><strong>Tables found:</strong> " . count($tables) . "</p>\n";
        
        if (count($tables) > 0) {
            echo "<p><strong>Sample tables:</strong> " . implode(', ', array_slice($tables, 0, 5));
            if (count($tables) > 5) {
                echo " (and " . (count($tables) - 5) . " more)";
            }
            echo "</p>\n";
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
} else {
    echo "<p class='error'>❌ get_pdo() function not available</p>\n";
}
echo "</div>\n";

// Test 5: File Permissions
echo "<div class='test-section'>\n";
echo "<h2>📁 File Permissions Test</h2>\n";

$test_dirs = ['logs', 'cache', 'uploads'];
foreach ($test_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p class='success'>✅ $dir/ - Writable</p>\n";
        } else {
            echo "<p class='error'>❌ $dir/ - Not writable</p>\n";
        }
    } else {
        echo "<p class='warning'>⚠️ $dir/ - Directory not found</p>\n";
    }
}

// Test config file permissions
if (file_exists('config.php')) {
    if (is_readable('config.php')) {
        echo "<p class='success'>✅ config.php - Readable</p>\n";
    } else {
        echo "<p class='error'>❌ config.php - Not readable</p>\n";
    }
}
echo "</div>\n";

// Test 6: URL Functions
echo "<div class='test-section'>\n";
echo "<h2>🔗 URL Functions Test</h2>\n";

if (function_exists('base_url')) {
    echo "<p class='success'>✅ base_url() function available</p>\n";
    
    $test_urls = ['', 'modules/', 'assets/style.css'];
    foreach ($test_urls as $path) {
        $url = base_url($path);
        echo "<p><strong>base_url('$path'):</strong> $url</p>\n";
    }
} else {
    echo "<p class='error'>❌ base_url() function not available</p>\n";
}
echo "</div>\n";

// Test 7: Module System
echo "<div class='test-section'>\n";
echo "<h2>📦 Module System Test</h2>\n";

if (is_dir('modules')) {
    echo "<p class='success'>✅ modules/ directory exists</p>\n";
    
    $module_files = glob('modules/*.php');
    echo "<p><strong>PHP modules found:</strong> " . count($module_files) . "</p>\n";
    
    if (count($module_files) > 0) {
        echo "<p><strong>Sample modules:</strong> " . implode(', ', array_slice(array_map('basename', $module_files), 0, 5));
        if (count($module_files) > 5) {
            echo " (and " . (count($module_files) - 5) . " more)";
        }
        echo "</p>\n";
    }
} else {
    echo "<p class='error'>❌ modules/ directory not found</p>\n";
}
echo "</div>\n";

// Test 8: Error Logging
echo "<div class='test-section'>\n";
echo "<h2>📝 Error Logging Test</h2>\n";

$log_dir = 'logs';
if (is_dir($log_dir) && is_writable($log_dir)) {
    echo "<p class='success'>✅ Logs directory is writable</p>\n";
    
    // Try to write a test log entry
    $test_log_file = $log_dir . '/test.log';
    $log_message = "Test log entry - " . date('Y-m-d H:i:s') . " - System test completed\n";
    
    if (file_put_contents($test_log_file, $log_message, FILE_APPEND | LOCK_EX) !== false) {
        echo "<p class='success'>✅ Test log entry written successfully</p>\n";
    } else {
        echo "<p class='error'>❌ Failed to write test log entry</p>\n";
    }
} else {
    echo "<p class='error'>❌ Logs directory not writable</p>\n";
}
echo "</div>\n";

// Test 9: Apache Configuration
echo "<div class='test-section'>\n";
echo "<h2>🌐 Apache Configuration Test</h2>\n";

// Check if .htaccess exists
if (file_exists('.htaccess')) {
    echo "<p class='success'>✅ .htaccess file exists</p>\n";
} else {
    echo "<p class='warning'>⚠️ .htaccess file not found</p>\n";
}

// Check Apache modules
if (function_exists('apache_get_modules')) {
    $apache_modules = apache_get_modules();
    if ($apache_modules !== false) {
        $required_modules = ['mod_rewrite', 'mod_php'];
        foreach ($required_modules as $module) {
            if (in_array($module, $apache_modules)) {
                echo "<p class='success'>✅ $module - Available</p>\n";
            } else {
                echo "<p class='warning'>⚠️ $module - Not available</p>\n";
            }
        }
    } else {
        echo "<p class='warning'>⚠️ Cannot determine Apache modules</p>\n";
    }
} else {
    echo "<p class='info'>ℹ️ apache_get_modules() function not available (this is normal in some environments)</p>\n";
    
    // Alternative check for Apache modules
    if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
        echo "<p class='success'>✅ Apache server detected</p>\n";
    } else {
        echo "<p class='info'>ℹ️ Server type: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>\n";
    }
}

// Additional web server information
echo "<h3>🔍 Web Server Details</h3>\n";
echo "<p><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'Not set') . "</p>\n";
echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "</p>\n";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "</p>\n";
echo "<p><strong>REMOTE_ADDR:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'Not set') . "</p>\n";
echo "<p><strong>HTTP_USER_AGENT:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Not set') . "</p>\n";

echo "</div>\n";

// Summary
echo "<div class='test-section info'>\n";
echo "<h2>📊 Test Summary</h2>\n";
echo "<p><strong>Test completed successfully!</strong></p>\n";
echo "<p>This test file has verified the basic functionality of your sLMS system.</p>\n";
echo "<p>If you see any errors above, please address them before proceeding with development.</p>\n";
echo "</div>\n";

echo "</div>\n";
echo "</body>\n";
echo "</html>\n";

// Flush the output buffer
ob_end_flush();
?> 