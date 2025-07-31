<?php
/**
 * SLMS System Integrity Check
 * Verifies all migrations, database integrity, and system health
 */

require_once 'modules/config.php';

echo "<h1>🔍 SLMS System Integrity Check</h1>\n";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Database Connection Test
echo "<h2>📊 Database Connection Test</h2>\n";
try {
    $pdo = get_pdo();
    $success[] = "Database connection successful";
    echo "<div style='color: green;'>✅ Database connection successful</div>\n";
} catch (Exception $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
    echo "<div style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</div>\n";
    exit;
}

// 2. Required Tables Check
echo "<h2>📋 Required Tables Check</h2>\n";
$required_tables = [
    'devices',
    'network_connections', 
    'webgl_settings',
    'users',
    'clients',
    'networks',
    'access_levels',
    'user_activity_log'
];

foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $success[] = "Table '$table' exists";
            echo "<div style='color: green;'>✅ Table '$table' exists</div>\n";
        } else {
            $errors[] = "Table '$table' missing";
            echo "<div style='color: red;'>❌ Table '$table' missing</div>\n";
        }
    } catch (Exception $e) {
        $errors[] = "Error checking table '$table': " . $e->getMessage();
        echo "<div style='color: red;'>❌ Error checking table '$table': " . $e->getMessage() . "</div>\n";
    }
}

// 3. WebGL Columns Check
echo "<h2>🎮 WebGL Columns Check</h2>\n";
$webgl_columns = ['position_x', 'position_y', 'position_z'];
foreach ($webgl_columns as $column) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM devices LIKE '$column'");
        if ($stmt->rowCount() > 0) {
            $success[] = "WebGL column '$column' exists in devices table";
            echo "<div style='color: green;'>✅ WebGL column '$column' exists in devices table</div>\n";
        } else {
            $errors[] = "WebGL column '$column' missing from devices table";
            echo "<div style='color: red;'>❌ WebGL column '$column' missing from devices table</div>\n";
        }
    } catch (Exception $e) {
        $errors[] = "Error checking WebGL column '$column': " . $e->getMessage();
        echo "<div style='color: red;'>❌ Error checking WebGL column '$column': " . $e->getMessage() . "</div>\n";
    }
}

// 4. File System Check
echo "<h2>📁 File System Check</h2>\n";
$required_files = [
    'webgl_demo.php',
    'continuous_improvement_loop.php',
    'integrate_webgl_with_existing_modules.php',
    'migrate_to_framework_final.php',
    'assets/webgl-network-viewer.js'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        $success[] = "File '$file' exists";
        echo "<div style='color: green;'>✅ File '$file' exists</div>\n";
    } else {
        $warnings[] = "File '$file' missing";
        echo "<div style='color: orange;'>⚠️ File '$file' missing</div>\n";
    }
}

// 5. PHP Syntax Check
echo "<h2>🔧 PHP Syntax Check</h2>\n";
$php_files = [
    'webgl_demo.php',
    'continuous_improvement_loop.php',
    'integrate_webgl_with_existing_modules.php',
    'migrate_to_framework_final.php'
];

foreach ($php_files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            $success[] = "PHP syntax check passed for '$file'";
            echo "<div style='color: green;'>✅ PHP syntax check passed for '$file'</div>\n";
        } else {
            $errors[] = "PHP syntax error in '$file': " . $output;
            echo "<div style='color: red;'>❌ PHP syntax error in '$file': " . htmlspecialchars($output) . "</div>\n";
        }
    }
}

// 6. Database Data Integrity
echo "<h2>🔍 Database Data Integrity</h2>\n";
try {
    // Check if there are any devices
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM devices");
    $device_count = $stmt->fetch()['count'];
    if ($device_count > 0) {
        $success[] = "Devices table contains $device_count records";
        echo "<div style='color: green;'>✅ Devices table contains $device_count records</div>\n";
    } else {
        $warnings[] = "Devices table is empty";
        echo "<div style='color: orange;'>⚠️ Devices table is empty</div>\n";
    }
    
    // Check if there are any network connections
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM network_connections");
    $connection_count = $stmt->fetch()['count'];
    if ($connection_count > 0) {
        $success[] = "Network connections table contains $connection_count records";
        echo "<div style='color: green;'>✅ Network connections table contains $connection_count records</div>\n";
    } else {
        $warnings[] = "Network connections table is empty";
        echo "<div style='color: orange;'>⚠️ Network connections table is empty</div>\n";
    }
    
} catch (Exception $e) {
    $errors[] = "Database data integrity check failed: " . $e->getMessage();
    echo "<div style='color: red;'>❌ Database data integrity check failed: " . $e->getMessage() . "</div>\n";
}

// 7. Permissions Check
echo "<h2>🔐 Permissions Check</h2>\n";
$directories = ['logs', 'cache'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            $success[] = "Directory '$dir' is writable";
            echo "<div style='color: green;'>✅ Directory '$dir' is writable</div>\n";
        } else {
            $warnings[] = "Directory '$dir' is not writable";
            echo "<div style='color: orange;'>⚠️ Directory '$dir' is not writable</div>\n";
        }
    } else {
        $warnings[] = "Directory '$dir' does not exist";
        echo "<div style='color: orange;'>⚠️ Directory '$dir' does not exist</div>\n";
    }
}

// 8. Summary
echo "<h2>📊 Summary</h2>\n";
echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>\n";
echo "<strong>✅ Successes: " . count($success) . "</strong><br>\n";
foreach ($success as $msg) {
    echo "• $msg<br>\n";
}
echo "</div>\n";

if (!empty($warnings)) {
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>⚠️ Warnings: " . count($warnings) . "</strong><br>\n";
    foreach ($warnings as $msg) {
        echo "• $msg<br>\n";
    }
    echo "</div>\n";
}

if (!empty($errors)) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>❌ Errors: " . count($errors) . "</strong><br>\n";
    foreach ($errors as $msg) {
        echo "• $msg<br>\n";
    }
    echo "</div>\n";
}

// Overall Status
if (empty($errors)) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>🎉 System Integrity Check PASSED</h3>\n";
    echo "All critical components are working correctly.\n";
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>⚠️ System Integrity Check FAILED</h3>\n";
    echo "Please fix the errors above before proceeding.\n";
    echo "</div>\n";
}

echo "</div>\n";
?> 