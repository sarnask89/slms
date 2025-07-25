<?php
// Detailed OPcache Test Script
echo "<h1>Detailed OPcache Test</h1>";

echo "<h2>1. Extension Loading Status</h2>";
$extensions = get_loaded_extensions();
$opcache_loaded = in_array('Zend OPcache', $extensions) || in_array('opcache', $extensions);
echo "OPcache in loaded extensions: " . ($opcache_loaded ? 'YES' : 'NO') . "<br>";
echo "All loaded extensions containing 'opcache': ";
foreach ($extensions as $ext) {
    if (stripos($ext, 'opcache') !== false) {
        echo $ext . " ";
    }
}
echo "<br>";

echo "<h2>2. Function Availability</h2>";
$functions = [
    'opcache_get_configuration',
    'opcache_get_status',
    'opcache_reset',
    'opcache_invalidate',
    'opcache_compile_file'
];

foreach ($functions as $func) {
    echo $func . ": " . (function_exists($func) ? 'Available' : 'Not Available') . "<br>";
}

echo "<h2>3. OPcache Configuration</h2>";
if (function_exists('opcache_get_configuration')) {
    $config = opcache_get_configuration();
    echo "<pre>";
    print_r($config);
    echo "</pre>";
} else {
    echo "opcache_get_configuration function not available<br>";
}

echo "<h2>4. OPcache Status</h2>";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status) {
        echo "<pre>";
        print_r($status);
        echo "</pre>";
    } else {
        echo "opcache_get_status returned false<br>";
    }
} else {
    echo "opcache_get_status function not available<br>";
}

echo "<h2>5. PHP Configuration</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Zend Engine: " . zend_version() . "<br>";
echo "SAPI: " . php_sapi_name() . "<br>";
echo "PHP INI Location: " . php_ini_loaded_file() . "<br>";

echo "<h2>6. OPcache INI Settings</h2>";
$opcache_settings = [
    'opcache.enable',
    'opcache.enable_cli',
    'opcache.memory_consumption',
    'opcache.max_accelerated_files',
    'opcache.jit',
    'opcache.jit_buffer_size'
];

foreach ($opcache_settings as $setting) {
    $value = ini_get($setting);
    echo $setting . ": " . ($value === false ? 'Not Set' : $value) . "<br>";
}

echo "<h2>7. Performance Test</h2>";
$start_time = microtime(true);

// Load some files to test OPcache
for ($i = 0; $i < 100; $i++) {
    // This will test if OPcache is working
    $test_var = "test_value_{$i}";
}

$end_time = microtime(true);
$execution_time = ($end_time - $start_time) * 1000;
echo "Execution time for 100 iterations: " . round($execution_time, 4) . "ms<br>";

echo "<h2>8. Memory Usage</h2>";
echo "Current Memory Usage: " . round(memory_get_usage(true) / 1024 / 1024, 2) . "MB<br>";
echo "Peak Memory Usage: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . "MB<br>";

echo "<hr>";
echo "<p><strong>Generated:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 