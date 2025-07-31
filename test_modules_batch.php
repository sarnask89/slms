<?php
/**
 * Test Modules in Batches
 * Tests modules in smaller groups for better debugging
 */

echo "🔍 Testing Modules in Batches...\n\n";

// Get all PHP files in modules directory
$modulesDir = 'modules';
$files = glob($modulesDir . '/*.php');

$totalFiles = count($files);
$workingModules = [];
$brokenModules = [];

echo "Found {$totalFiles} modules to test...\n\n";

// Test in batches of 10
$batchSize = 10;
$batches = array_chunk($files, $batchSize);

foreach ($batches as $batchIndex => $batch) {
    echo "=== BATCH " . ($batchIndex + 1) . " ===\n";
    
    foreach ($batch as $file) {
        $moduleName = basename($file);
        echo "Testing: {$moduleName}... ";
        
        // Simple test - just check if it can be included without fatal errors
        try {
            // Capture any output
            ob_start();
            
            // Include the file
            include $file;
            
            $output = ob_get_clean();
            
            if (!empty($output)) {
                echo "✅ WORKING\n";
                $workingModules[] = $moduleName;
            } else {
                echo "⚠️  NO OUTPUT\n";
                $brokenModules[] = $moduleName;
            }
            
        } catch (Throwable $e) {
            ob_end_clean();
            echo "❌ ERROR: " . substr($e->getMessage(), 0, 50) . "...\n";
            $brokenModules[] = $moduleName;
        }
    }
    
    echo "\n";
}

echo "📊 FINAL RESULTS:\n";
echo "Total Modules: {$totalFiles}\n";
echo "Working: " . count($workingModules) . "\n";
echo "Broken: " . count($brokenModules) . "\n";
echo "Success Rate: " . round((count($workingModules) / $totalFiles) * 100, 1) . "%\n\n";

if (!empty($workingModules)) {
    echo "✅ WORKING MODULES:\n";
    foreach ($workingModules as $module) {
        echo "  - {$module}\n";
    }
    echo "\n";
}

if (!empty($brokenModules)) {
    echo "❌ BROKEN MODULES:\n";
    foreach ($brokenModules as $module) {
        echo "  - {$module}\n";
    }
    echo "\n";
}

echo "🎉 Batch testing completed!\n";
?> 