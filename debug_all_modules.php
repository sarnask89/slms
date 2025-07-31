<?php
/**
 * Debug All Modules Script
 * Tests and debugs all modules in the system
 */

echo "🔍 Testing and Debugging All Modules...\n\n";

// Get all PHP files in modules directory
$modulesDir = 'modules';
$files = glob($modulesDir . '/*.php');

$totalFiles = count($files);
$workingModules = [];
$brokenModules = [];
$errorDetails = [];

echo "Found {$totalFiles} modules to test...\n\n";

foreach ($files as $file) {
    $moduleName = basename($file);
    echo "Testing: {$moduleName}... ";
    
    // Test module with error reporting
    ob_start();
    $errorOutput = '';
    
    // Capture errors
    set_error_handler(function($severity, $message, $file, $line) use (&$errorOutput) {
        $errorOutput .= "Error: {$message} in {$file} on line {$line}\n";
    });
    
    try {
        // Test if module can be included without fatal errors
        $result = include $file;
        
        // Check if module produced any output
        $output = ob_get_clean();
        
        if (empty($errorOutput) && !empty($output)) {
            echo "✅ WORKING\n";
            $workingModules[] = $moduleName;
        } elseif (empty($errorOutput) && empty($output)) {
            echo "⚠️  NO OUTPUT\n";
            $brokenModules[] = $moduleName;
            $errorDetails[$moduleName] = "Module loaded but produced no output";
        } else {
            echo "❌ BROKEN\n";
            $brokenModules[] = $moduleName;
            $errorDetails[$moduleName] = $errorOutput;
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ EXCEPTION\n";
        $brokenModules[] = $moduleName;
        $errorDetails[$moduleName] = "Exception: " . $e->getMessage();
    } catch (Error $e) {
        ob_end_clean();
        echo "❌ FATAL ERROR\n";
        $brokenModules[] = $moduleName;
        $errorDetails[$moduleName] = "Fatal Error: " . $e->getMessage();
    }
    
    // Restore error handler
    restore_error_handler();
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 TEST RESULTS SUMMARY\n";
echo str_repeat("=", 60) . "\n";
echo "Total Modules: {$totalFiles}\n";
echo "Working Modules: " . count($workingModules) . "\n";
echo "Broken Modules: " . count($brokenModules) . "\n";
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
    
    echo "🔧 ERROR DETAILS:\n";
    foreach ($errorDetails as $module => $error) {
        echo "\n--- {$module} ---\n";
        echo $error . "\n";
    }
}

// Test web access for working modules
echo "\n" . str_repeat("=", 60) . "\n";
echo "🌐 WEB ACCESS TESTING\n";
echo str_repeat("=", 60) . "\n";

$webWorking = [];
$webBroken = [];

foreach ($workingModules as $module) {
    $url = "http://localhost/modules/{$module}";
    echo "Testing web access: {$module}... ";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false && !empty($response)) {
        echo "✅ WEB ACCESS OK\n";
        $webWorking[] = $module;
    } else {
        echo "❌ WEB ACCESS FAILED\n";
        $webBroken[] = $module;
    }
}

echo "\n📊 WEB ACCESS RESULTS:\n";
echo "Web Access Working: " . count($webWorking) . "\n";
echo "Web Access Failed: " . count($webBroken) . "\n";

if (!empty($webWorking)) {
    echo "\n✅ WEB ACCESS WORKING:\n";
    foreach ($webWorking as $module) {
        echo "  - http://localhost/modules/{$module}\n";
    }
}

if (!empty($webBroken)) {
    echo "\n❌ WEB ACCESS FAILED:\n";
    foreach ($webBroken as $module) {
        echo "  - {$module}\n";
    }
}

echo "\n🎉 Module testing completed!\n";
?>
