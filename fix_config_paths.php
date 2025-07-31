<?php
/**
 * Fix Config Paths Script
 * Updates all modules to use the correct config.php path
 */

echo "🔧 Fixing config.php paths in all modules...\n";

// Get all PHP files in modules directory
$modulesDir = 'modules';
$files = glob($modulesDir . '/*.php');

$fixedCount = 0;
$totalFiles = count($files);

echo "Found {$totalFiles} PHP files to check...\n\n";

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Fix different patterns of config.php includes
    $patterns = [
        "require_once __DIR__ . '/../config.php';",
        "require_once __DIR__ . \"/../config.php\";",
        "require_once '../config.php';",
        "require_once \"../config.php\";",
        "include __DIR__ . '/../config.php';",
        "include __DIR__ . \"/../config.php\";",
        "include '../config.php';",
        "include \"../config.php\";"
    ];
    
    $fixed = false;
    foreach ($patterns as $pattern) {
        if (strpos($content, $pattern) !== false) {
            $content = str_replace($pattern, "require_once __DIR__ . '/config.php';", $content);
            $fixed = true;
        }
    }
    
    if ($fixed) {
        file_put_contents($file, $content);
        echo "✅ Fixed: " . basename($file) . "\n";
        $fixedCount++;
    }
}

echo "\n🎉 Config path fixing completed!\n";
echo "📊 Summary:\n";
echo "   - Total files checked: {$totalFiles}\n";
echo "   - Files fixed: {$fixedCount}\n";
echo "   - Files unchanged: " . ($totalFiles - $fixedCount) . "\n";

if ($fixedCount > 0) {
    echo "\n✅ All modules now use the correct config.php path!\n";
} else {
    echo "\nℹ️  No files needed fixing.\n";
}
?> 