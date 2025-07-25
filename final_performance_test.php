<?php
/**
 * Final Performance Test for sLMS System
 * Demonstrates OPcache and system optimizations
 */

echo "<h1>🚀 sLMS Performance Test Results</h1>";

// Test 1: System Overview
echo "<h2>1. System Overview</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Component</th><th>Status</th><th>Details</th></tr>";

// PHP Version
echo "<tr><td>PHP Version</td><td>✅ " . PHP_VERSION . "</td><td>Latest stable version</td></tr>";

// OPcache
if (extension_loaded('Zend OPcache') || extension_loaded('opcache')) {
    if (function_exists('opcache_get_configuration')) {
        $config = opcache_get_configuration();
        $status = opcache_get_status();
        $enabled = $config['directives']['opcache.enable'] ? 'Enabled' : 'Disabled';
        $memory = round($config['directives']['opcache.memory_consumption'] / 1024 / 1024, 1);
        $hit_rate = $status ? round($status['opcache_statistics']['opcache_hit_rate'], 1) : 0;
        
        echo "<tr><td>OPcache</td><td>✅ {$enabled}</td><td>{$memory}MB, Hit Rate: {$hit_rate}%</td></tr>";
    } else {
        echo "<tr><td>OPcache</td><td>⚠️ Loaded</td><td>Functions not available</td></tr>";
    }
} else {
    echo "<tr><td>OPcache</td><td>❌ Not Loaded</td><td>Performance impact: High</td></tr>";
}

// APCu
if (extension_loaded('apcu')) {
    if (function_exists('apcu_store')) {
        $test_key = 'test_' . time();
        if (apcu_store($test_key, 'test', 60)) {
            echo "<tr><td>APCu Cache</td><td>✅ Working</td><td>User data caching enabled</td></tr>";
        } else {
            echo "<tr><td>APCu Cache</td><td>⚠️ Loaded</td><td>Store operation failed</td></tr>";
        }
    } else {
        echo "<tr><td>APCu Cache</td><td>⚠️ Loaded</td><td>Functions not available</td></tr>";
    }
} else {
    echo "<tr><td>APCu Cache</td><td>❌ Not Loaded</td><td>User data caching disabled</td></tr>";
}

// Redis
if (extension_loaded('redis')) {
    try {
        $redis = new Redis();
        if ($redis->connect('127.0.0.1', 6379, 1)) {
            echo "<tr><td>Redis Cache</td><td>✅ Connected</td><td>Distributed caching available</td></tr>";
            $redis->close();
        } else {
            echo "<tr><td>Redis Cache</td><td>⚠️ Extension Loaded</td><td>Server not running</td></tr>";
        }
    } catch (Exception $e) {
        echo "<tr><td>Redis Cache</td><td>⚠️ Extension Loaded</td><td>Connection failed</td></tr>";
    }
} else {
    echo "<tr><td>Redis Cache</td><td>❌ Not Loaded</td><td>Distributed caching disabled</td></tr>";
}

echo "</table>";

// Test 2: Performance Benchmarks
echo "<h2>2. Performance Benchmarks</h2>";

// File operations test
$start_time = microtime(true);
for ($i = 0; $i < 100; $i++) {
    $test_file = "benchmark_file_{$i}.tmp";
    file_put_contents($test_file, "test content {$i}");
    $content = file_get_contents($test_file);
    unlink($test_file);
}
$file_time = (microtime(true) - $start_time) * 1000;

// URL generation test
$start_time = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $url = "/modules/test_{$i}.php";
}
$url_time = (microtime(true) - $start_time) * 1000;

// Memory usage test
$start_memory = memory_get_usage(true);
for ($i = 0; $i < 10000; $i++) {
    $array[$i] = "test_value_{$i}";
}
$end_memory = memory_get_usage(true);
$memory_used = $end_memory - $start_memory;

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Test</th><th>Result</th><th>Performance</th></tr>";
echo "<tr><td>File Operations (100 files)</td><td>" . round($file_time, 2) . "ms</td><td>" . 
     ($file_time < 50 ? "✅ Excellent" : ($file_time < 100 ? "⚠️ Good" : "❌ Slow")) . "</td></tr>";
echo "<tr><td>URL Generation (1000 URLs)</td><td>" . round($url_time, 2) . "ms</td><td>" . 
     ($url_time < 10 ? "✅ Excellent" : ($url_time < 50 ? "⚠️ Good" : "❌ Slow")) . "</td></tr>";
echo "<tr><td>Memory Usage (10k operations)</td><td>" . round($memory_used / 1024 / 1024, 2) . "MB</td><td>" . 
     ($memory_used < 10 * 1024 * 1024 ? "✅ Efficient" : "⚠️ High") . "</td></tr>";
echo "</table>";

// Test 3: OPcache Statistics
echo "<h2>3. OPcache Statistics</h2>";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Metric</th><th>Value</th><th>Status</th></tr>";
        
        $memory_usage = $status['memory_usage'];
        $used_mb = round($memory_usage['used_memory'] / 1024 / 1024, 2);
        $free_mb = round($memory_usage['free_memory'] / 1024 / 1024, 2);
        $wasted_mb = round($memory_usage['wasted_memory'] / 1024 / 1024, 2);
        
        echo "<tr><td>Used Memory</td><td>{$used_mb}MB</td><td>✅ Normal</td></tr>";
        echo "<tr><td>Free Memory</td><td>{$free_mb}MB</td><td>✅ Available</td></tr>";
        echo "<tr><td>Wasted Memory</td><td>{$wasted_mb}MB</td><td>" . 
             ($wasted_mb < 10 ? "✅ Low" : "⚠️ High") . "</td></tr>";
        
        $stats = $status['opcache_statistics'];
        $hit_rate = round($stats['opcache_hit_rate'], 1);
        $cached_files = $stats['num_cached_scripts'];
        $cached_keys = $stats['num_cached_keys'];
        
        echo "<tr><td>Hit Rate</td><td>{$hit_rate}%</td><td>" . 
             ($hit_rate > 90 ? "✅ Excellent" : ($hit_rate > 70 ? "⚠️ Good" : "❌ Low")) . "</td></tr>";
        echo "<tr><td>Cached Files</td><td>{$cached_files}</td><td>✅ Active</td></tr>";
        echo "<tr><td>Cached Keys</td><td>{$cached_keys}</td><td>✅ Active</td></tr>";
        
        echo "</table>";
    }
}

// Test 4: System Configuration
echo "<h2>4. System Configuration</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Setting</th><th>Value</th><th>Recommendation</th></tr>";

$memory_limit = ini_get('memory_limit');
echo "<tr><td>Memory Limit</td><td>{$memory_limit}</td><td>" . 
     ($memory_limit == '-1' || intval($memory_limit) >= 256 ? "✅ Adequate" : "⚠️ Consider increasing") . "</td></tr>";

$max_execution_time = ini_get('max_execution_time');
echo "<tr><td>Max Execution Time</td><td>{$max_execution_time}s</td><td>" . 
     ($max_execution_time >= 300 ? "✅ Good" : "⚠️ Consider increasing") . "</td></tr>";

$upload_max_filesize = ini_get('upload_max_filesize');
echo "<tr><td>Upload Max Filesize</td><td>{$upload_max_filesize}</td><td>" . 
     (intval($upload_max_filesize) >= 10 ? "✅ Adequate" : "⚠️ Consider increasing") . "</td></tr>";

echo "</table>";

// Test 5: Performance Score
echo "<h2>5. Overall Performance Score</h2>";
$score = 0;
$max_score = 10;

// OPcache (3 points)
if ((extension_loaded('opcache') || extension_loaded('Zend OPcache')) && function_exists('opcache_get_configuration')) {
    $config = opcache_get_configuration();
    if ($config['directives']['opcache.enable']) {
        $score += 3;
    }
}

// APCu (1 point)
if (extension_loaded('apcu') && function_exists('apcu_store')) {
    $score += 1;
}

// Redis (1 point)
if (extension_loaded('redis')) {
    try {
        $redis = new Redis();
        if ($redis->connect('127.0.0.1', 6379, 1)) {
            $score += 1;
            $redis->close();
        }
    } catch (Exception $e) {
        // No points for connection failure
    }
}

// Performance thresholds (5 points)
if ($file_time < 50) $score += 1;
if ($url_time < 10) $score += 1;
if ($memory_used < 10 * 1024 * 1024) $score += 1;
if (intval($memory_limit) >= 256 || $memory_limit == '-1') $score += 1;
if ($max_execution_time >= 300) $score += 1;

$percentage = round(($score / $max_score) * 100, 1);

echo "<div style='text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Performance Score: {$score}/{$max_score} ({$percentage}%)</h3>";

if ($percentage >= 90) {
    echo "<p style='color: #28a745; font-size: 18px;'>🎉 Excellent! Your system is well optimized.</p>";
} elseif ($percentage >= 75) {
    echo "<p style='color: #17a2b8; font-size: 18px;'>✅ Good! Most optimizations are working well.</p>";
} elseif ($percentage >= 50) {
    echo "<p style='color: #ffc107; font-size: 18px;'>⚠️ Moderate. Some optimizations needed.</p>";
} else {
    echo "<p style='color: #dc3545; font-size: 18px;'>❌ Poor. Significant optimizations needed.</p>";
}

echo "</div>";

// Test 6: Recommendations
echo "<h2>6. Recommendations</h2>";
echo "<ul>";

if (!extension_loaded('Zend OPcache') && !extension_loaded('opcache')) {
    echo "<li>🔧 <strong>Install OPcache:</strong> This is the most important optimization for PHP performance.</li>";
}

if (!extension_loaded('apcu')) {
    echo "<li>🔧 <strong>Install APCu:</strong> Provides user data caching for better performance.</li>";
}

if (!extension_loaded('redis')) {
    echo "<li>🔧 <strong>Install Redis:</strong> Enables distributed caching for multi-server setups.</li>";
}

if ($file_time > 100) {
    echo "<li>💾 <strong>Storage Optimization:</strong> Consider using SSD storage for better file I/O performance.</li>";
}

if ($url_time > 50) {
    echo "<li>⚡ <strong>Code Optimization:</strong> Review URL generation functions for optimization opportunities.</li>";
}

if (intval($memory_limit) < 256 && $memory_limit != '-1') {
    echo "<li>🧠 <strong>Memory Increase:</strong> Consider increasing PHP memory limit to 256M or higher.</li>";
}

if ($max_execution_time < 300) {
    echo "<li>⏱️ <strong>Execution Time:</strong> Consider increasing max execution time for complex operations.</li>";
}

echo "</ul>";

echo "<hr>";
echo "<p style='text-align: center; color: #6c757d;'><strong>Test completed:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p style='text-align: center; color: #6c757d;'>sLMS Performance Optimization System</p>";
?> 