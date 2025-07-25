<?php
/**
 * sLMS Performance Testing Script
 * Demonstrates and benchmarks the optimization improvements
 */

// Start performance monitoring
$start_time = microtime(true);

// Load optimized configuration
require_once 'config_optimized.php';

echo "<!DOCTYPE html>\n";
echo "<html lang='en'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>sLMS Performance Test</title>\n";
echo "    <style>\n";
echo "        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }\n";
echo "        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo "        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }\n";
echo "        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }\n";
echo "        .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }\n";
echo "        .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }\n";
echo "        .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }\n";
echo "        .performance { background-color: #e2e3e5; border-color: #d6d8db; color: #383d41; }\n";
echo "        pre { background-color: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }\n";
echo "        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }\n";
echo "        h2 { color: #555; margin-top: 30px; }\n";
echo "        .metric { font-weight: bold; color: #007bff; }\n";
echo "        .benchmark { font-family: monospace; background: #f8f9fa; padding: 5px; border-radius: 3px; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";
echo "<div class='container'>\n";
echo "<h1>🚀 sLMS Performance Optimization Test</h1>\n";
echo "<p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>\n";

// Test 1: System Information
echo "<div class='test-section info'>\n";
echo "<h2>📋 System Information</h2>\n";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>\n";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>\n";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>\n";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . " seconds</p>\n";
echo "</div>\n";

// Test 2: Performance Optimizations Status
echo "<div class='test-section performance'>\n";
echo "<h2>⚡ Performance Optimizations Status</h2>\n";

$stats = get_performance_stats();
echo "<p><strong>OPcache Enabled:</strong> " . ($stats['opcache_enabled'] ? '✅ Yes' : '❌ No') . "</p>\n";
echo "<p><strong>APCu Enabled:</strong> " . ($stats['apcu_enabled'] ? '✅ Yes' : '❌ No') . "</p>\n";
echo "<p><strong>Redis Enabled:</strong> " . ($stats['redis_enabled'] ? '✅ Yes' : '❌ No') . "</p>\n";
echo "<p><strong>Cache Driver:</strong> " . ($stats['cache_driver'] ?? 'file') . "</p>\n";
echo "<p><strong>Memory Usage:</strong> " . number_format($stats['memory_usage'] / 1024 / 1024, 2) . " MB</p>\n";
echo "<p><strong>Peak Memory:</strong> " . number_format($stats['peak_memory'] / 1024 / 1024, 2) . " MB</p>\n";

if (isset($stats['server_info'])) {
    echo "<p><strong>Server ID:</strong> " . $stats['server_info']['server_id'] . "</p>\n";
    if (isset($stats['server_info']['load_average'])) {
        $load = $stats['server_info']['load_average'];
        echo "<p><strong>Load Average:</strong> " . implode(', ', $load) . "</p>\n";
    }
}

echo "</div>\n";

// Test 3: Database Performance Test
echo "<div class='test-section'>\n";
echo "<h2>🗄️ Database Performance Test</h2>\n";

try {
    $db_start = microtime(true);
    $pdo = get_optimized_pdo();
    $db_connect_time = microtime(true) - $db_start;
    
    echo "<p class='success'>✅ Database connection successful</p>\n";
    echo "<p><strong>Connection Time:</strong> <span class='benchmark'>" . number_format($db_connect_time * 1000, 2) . " ms</span></p>\n";
    
    // Test optimized queries
    $query_start = microtime(true);
    $users = $performance_optimizer->getOptimizedUsers(10, 0);
    $query_time = microtime(true) - $query_start;
    
    echo "<p class='success'>✅ Optimized users query successful</p>\n";
    echo "<p><strong>Query Time:</strong> <span class='benchmark'>" . number_format($query_time * 1000, 2) . " ms</span></p>\n";
    echo "<p><strong>Users Retrieved:</strong> " . count($users) . "</p>\n";
    
    // Test menu items with caching
    $menu_start = microtime(true);
    $menu_items = get_optimized_menu_items_from_database();
    $menu_time = microtime(true) - $menu_start;
    
    echo "<p class='success'>✅ Optimized menu items query successful</p>\n";
    echo "<p><strong>Menu Query Time:</strong> <span class='benchmark'>" . number_format($menu_time * 1000, 2) . " ms</span></p>\n";
    echo "<p><strong>Menu Items:</strong> " . count($menu_items) . "</p>\n";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database test failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "</div>\n";

// Test 4: Caching Performance Test
echo "<div class='test-section'>\n";
echo "<h2>💾 Caching Performance Test</h2>\n";

// Test cache set
$cache_start = microtime(true);
$test_data = ['test' => 'data', 'timestamp' => time()];
$cache_set_result = cache_set('test_performance', $test_data, 60);
$cache_set_time = microtime(true) - $cache_start;

echo "<p><strong>Cache Set Time:</strong> <span class='benchmark'>" . number_format($cache_set_time * 1000, 2) . " ms</span></p>\n";
echo "<p><strong>Cache Set Result:</strong> " . ($cache_set_result ? '✅ Success' : '❌ Failed') . "</p>\n";

// Test cache get
$cache_get_start = microtime(true);
$cached_data = cache_get('test_performance');
$cache_get_time = microtime(true) - $cache_get_start;

echo "<p><strong>Cache Get Time:</strong> <span class='benchmark'>" . number_format($cache_get_time * 1000, 2) . " ms</span></p>\n";
echo "<p><strong>Cache Get Result:</strong> " . ($cached_data ? '✅ Success' : '❌ Failed') . "</p>\n";

if ($cached_data) {
    echo "<p><strong>Cached Data:</strong> " . htmlspecialchars(json_encode($cached_data)) . "</p>\n";
}

echo "</div>\n";

// Test 5: Async Operations Test
echo "<div class='test-section'>\n";
echo "<h2>🔄 Async Operations Test</h2>\n";

if (extension_loaded('curl')) {
    $urls = [
        'http://httpbin.org/delay/1',
        'http://httpbin.org/delay/1',
        'http://httpbin.org/delay/1'
    ];
    
    $async_start = microtime(true);
    $async_results = async_fetch_urls($urls, function($url, $response) {
        echo "<p>✅ Async request completed: " . htmlspecialchars($url) . "</p>\n";
    });
    $async_time = microtime(true) - $async_start;
    
    echo "<p><strong>Async Requests Time:</strong> <span class='benchmark'>" . number_format($async_time * 1000, 2) . " ms</span></p>\n";
    echo "<p><strong>Sequential Time (estimated):</strong> <span class='benchmark'>~3000 ms</span></p>\n";
    echo "<p><strong>Performance Improvement:</strong> <span class='metric'>" . number_format((3 / $async_time), 1) . "x faster</span></p>\n";
} else {
    echo "<p class='warning'>⚠️ cURL extension not available - async operations disabled</p>\n";
}

echo "</div>\n";

// Test 6: URL Generation Performance
echo "<div class='test-section'>\n";
echo "<h2>🔗 URL Generation Performance Test</h2>\n";

$url_start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $url = base_url('modules/test.php');
}
$url_time = microtime(true) - $url_start;

echo "<p><strong>1000 URL Generations:</strong> <span class='benchmark'>" . number_format($url_time * 1000, 2) . " ms</span></p>\n";
echo "<p><strong>Average per URL:</strong> <span class='benchmark'>" . number_format(($url_time / 1000) * 1000, 4) . " ms</span></p>\n";

// Test optimized URL generation
$opt_url_start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $url = get_optimized_base_url('modules/test.php');
}
$opt_url_time = microtime(true) - $opt_url_start;

echo "<p><strong>1000 Optimized URL Generations:</strong> <span class='benchmark'>" . number_format($opt_url_time * 1000, 2) . " ms</span></p>\n";
echo "<p><strong>Optimization Improvement:</strong> <span class='metric'>" . number_format($url_time / $opt_url_time, 1) . "x faster</span></p>\n";

echo "</div>\n";

// Test 7: Memory Usage Test
echo "<div class='test-section'>\n";
echo "<h2>🧠 Memory Usage Test</h2>\n";

$initial_memory = memory_get_usage(true);
$memory_usage_mb = $initial_memory / 1024 / 1024;

echo "<p><strong>Initial Memory Usage:</strong> <span class='benchmark'>" . number_format($memory_usage_mb, 2) . " MB</span></p>\n";

// Simulate some operations
for ($i = 0; $i < 1000; $i++) {
    $test_array[] = "test_data_" . $i;
}

$peak_memory = memory_get_peak_usage(true);
$peak_memory_mb = $peak_memory / 1024 / 1024;

echo "<p><strong>Peak Memory Usage:</strong> <span class='benchmark'>" . number_format($peak_memory_mb, 2) . " MB</span></p>\n";
echo "<p><strong>Memory Increase:</strong> <span class='benchmark'>" . number_format($peak_memory_mb - $memory_usage_mb, 2) . " MB</span></p>\n";

echo "</div>\n";

// Test 8: Overall Performance Summary
echo "<div class='test-section performance'>\n";
echo "<h2>📊 Overall Performance Summary</h2>\n";

$total_time = microtime(true) - $start_time;
$total_memory = memory_get_peak_usage(true) / 1024 / 1024;

echo "<p><strong>Total Execution Time:</strong> <span class='metric'>" . number_format($total_time * 1000, 2) . " ms</span></p>\n";
echo "<p><strong>Total Memory Usage:</strong> <span class='metric'>" . number_format($total_memory, 2) . " MB</span></p>\n";
echo "<p><strong>Performance Score:</strong> <span class='metric'>" . calculatePerformanceScore($total_time, $total_memory) . "/100</span></p>\n";

// Performance recommendations
echo "<h3>🎯 Performance Recommendations</h3>\n";
if (!$stats['opcache_enabled']) {
    echo "<p class='warning'>⚠️ Enable OPcache for better performance</p>\n";
}
if (!$stats['apcu_enabled']) {
    echo "<p class='warning'>⚠️ Enable APCu for better caching</p>\n";
}
if ($total_time > 1.0) {
    echo "<p class='warning'>⚠️ Consider optimizing database queries</p>\n";
}
if ($total_memory > 50) {
    echo "<p class='warning'>⚠️ Consider reducing memory usage</p>\n";
}

echo "</div>\n";

// Performance monitoring results
echo "<div class='test-section info'>\n";
echo "<h2>📈 Performance Monitoring</h2>\n";

$monitoring_data = monitor_performance('performance_test', $start_time);
echo "<p><strong>Operation:</strong> " . $monitoring_data['operation'] . "</p>\n";
echo "<p><strong>Execution Time:</strong> " . number_format($monitoring_data['execution_time'] * 1000, 2) . " ms</p>\n";
echo "<p><strong>Memory Usage:</strong> " . number_format($monitoring_data['memory_usage'] / 1024 / 1024, 2) . " MB</p>\n";
echo "<p><strong>Timestamp:</strong> " . $monitoring_data['timestamp'] . "</p>\n";

echo "</div>\n";

echo "</div>\n";
echo "</body>\n";
echo "</html>\n";

// Helper function to calculate performance score
function calculatePerformanceScore($execution_time, $memory_usage) {
    // Score based on execution time (lower is better)
    $time_score = max(0, 100 - ($execution_time * 50));
    
    // Score based on memory usage (lower is better)
    $memory_score = max(0, 100 - ($memory_usage * 2));
    
    // Average score
    return round(($time_score + $memory_score) / 2);
}

?> 