<?php
/**
 * Fix Module Includes Script
 * Adds helper functions include to all modules
 */

echo "🔧 Adding helper includes to all modules...\n";

// Get all PHP files in modules directory
$modulesDir = 'modules';
$files = glob($modulesDir . '/*.php');

$fixedCount = 0;
$totalFiles = count($files);

echo "Found {$totalFiles} PHP files to check...\n\n";

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Check if helper functions are already included
    if (strpos($content, 'helpers/functions.php') !== false) {
        continue;
    }
    
    // Add helper include after the config include
    $configPattern = "require_once 'config.php';";
    $helperInclude = "require_once 'config.php';\nrequire_once __DIR__ . '/../helpers/functions.php';";
    
    if (strpos($content, $configPattern) !== false) {
        $content = str_replace($configPattern, $helperInclude, $content);
        file_put_contents($file, $content);
        echo "✅ Fixed: " . basename($file) . "\n";
        $fixedCount++;
    }
}

echo "\n🎉 Helper includes fixing completed!\n";
echo "📊 Summary:\n";
echo "   - Total files checked: {$totalFiles}\n";
echo "   - Files fixed: {$fixedCount}\n";
echo "   - Files unchanged: " . ($totalFiles - $fixedCount) . "\n";

if ($fixedCount > 0) {
    echo "\n✅ All modules now include helper functions!\n";
} else {
    echo "\nℹ️  No files needed fixing.\n";
}
?> 