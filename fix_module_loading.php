<?php
/**
 * Module Loading Fix Script
 * Updates all modules to use the new module loader system
 */

require_once 'config.php';

echo "<h1>🔧 Module Loading Fix Script</h1>\n";
echo "<p>Fixing module loading issues...</p>\n";

// Get all PHP files in modules directory
$modules_dir = __DIR__ . '/modules/';
$files = glob($modules_dir . '*.php');

$fixed_count = 0;
$skipped_count = 0;

foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip certain files
    if (in_array($filename, ['module_loader.php', 'index.php', 'config.php'])) {
        $skipped_count++;
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Check if file already uses module_loader
    if (strpos($content, "require_once 'module_loader.php'") !== false) {
        $skipped_count++;
        continue;
    }
    
    // Check if file has PHP opening tag and requires
    if (strpos($content, '<?php') === false) {
        $skipped_count++;
        continue;
    }
    
    // Replace the old include pattern with module_loader
    $old_pattern = "require_once 'config.php';";
    $new_pattern = "require_once 'module_loader.php';";
    
    if (strpos($content, $old_pattern) !== false) {
        $content = str_replace($old_pattern, $new_pattern, $content);
        
        // Also replace other common patterns
        $content = str_replace("require_once __DIR__ . '/../config.php';", $new_pattern, $content);
        $content = str_replace("require_once __DIR__ . '/../helpers/functions.php';", "", $content);
        
        // Write the updated content back
        if (file_put_contents($file, $content)) {
            $fixed_count++;
            echo "<p>✅ Fixed: $filename</p>\n";
        } else {
            echo "<p>❌ Failed to write: $filename</p>\n";
        }
    } else {
        $skipped_count++;
    }
}

echo "<h2>📊 Summary</h2>\n";
echo "<p><strong>Fixed:</strong> $fixed_count modules</p>\n";
echo "<p><strong>Skipped:</strong> $skipped_count modules</p>\n";
echo "<p><strong>Total processed:</strong> " . count($files) . " files</p>\n";

// Test a few modules
echo "<h2>🧪 Testing Modules</h2>\n";

$test_modules = ['devices', 'clients', 'networks'];
foreach ($test_modules as $module) {
    $test_file = $modules_dir . $module . '.php';
    if (file_exists($test_file)) {
        $content = file_get_contents($test_file);
        if (strpos($content, "require_once 'module_loader.php'") !== false) {
            echo "<p>✅ $module.php - Using module loader</p>\n";
        } else {
            echo "<p>❌ $module.php - Still needs fixing</p>\n";
        }
    } else {
        echo "<p>⚠️ $module.php - File not found</p>\n";
    }
}

echo "<h2>🎉 Module Loading Fix Complete!</h2>\n";
echo "<p>All modules should now load properly without session errors.</p>\n";
echo "<p><a href='modules/'>View Modules Directory</a></p>\n";
echo "<p><a href='admin_menu_enhanced.php'>Return to Admin Menu</a></p>\n";
?> 