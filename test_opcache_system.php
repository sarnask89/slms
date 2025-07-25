<?php
/**
 * OPcache and System Performance Test Script
 * Tests OPcache configuration and sLMS system performance
 */

echo "=== sLMS OPcache and System Performance Test ===\n\n";

// Test 1: Check PHP Version and Extensions
echo "1. PHP Version and Extensions:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Zend Engine: " . zend_version() . "\n";

// Check if OPcache is loaded
if (extension_loaded('opcache')) {
    echo "✓ OPcache: Loaded\n";
} else {
    echo "✗ OPcache: Not loaded\n";
}

// Check if APCu is loaded
if (extension_loaded('apcu')) {
    echo "✓ APCu: Loaded\n";
} else {
    echo "✗ APCu: Not loaded\n";
}

// Check if Redis is loaded
if (extension_loaded('redis')) {
    echo "✓ Redis: Loaded\n";
} else {
    echo "✗ Redis: Not loaded\n";
}

echo "\n";

// Test 2: OPcache Configuration
echo "2. OPcache Configuration:\n";
if (function_exists('opcache_get_configuration')) {
    $config = opcache_get_configuration();
    $status = opcache_get_status();
    
    echo "OPcache Enabled: " . ($config['directives']['opcache.enable'] ? 'Yes' : 'No') . "\n";
    echo "Memory Consumption: " . $config['directives']['opcache.memory_consumption'] . "MB\n";
    echo "Max Accelerated Files: " . $config['directives']['opcache.max_accelerated_files'] . "\n";
    echo "JIT Enabled: " . ($config['directives']['opcache.jit'] ? 'Yes' : 'No') . "\n";
    echo "JIT Buffer Size: " . $config['directives']['opcache.jit_buffer_size'] . "\n";
    
    if ($status) {
        echo "Memory Usage: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . "MB / " . 
             round($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . "MB\n";
        echo "Hit Rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
        echo "Cached Files: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    }
} else {
    echo "✗ OPcache functions not available\n";
}

echo "\n";

// Test 3: Load sLMS Configuration
echo "3. sLMS Configuration Test:\n";
try {
    // Load optimized config directly without the main config to avoid conflicts
    if (file_exists('config_optimized.php')) {
        require_once 'config_optimized.php';
        echo "✓ Optimized config: Loaded\n";
    } else {
        echo "⚠ Optimized config: Not found\n";
        // Fallback to main config
        require_once 'config.php';
        echo "✓ Main config.php: Loaded (fallback)\n";
    }
    
    if (file_exists('optimized_performance.php')) {
        require_once 'optimized_performance.php';
        echo "✓ Performance optimizer: Loaded\n";
    } else {
        echo "⚠ Performance optimizer: Not found\n";
    }
    
} catch (Exception $e) {
    echo "✗ Configuration error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Database Connection
echo "4. Database Connection Test:\n";
try {
    $pdo = get_pdo();
    echo "✓ Database connection: Successful\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Database query: Successful (" . $result['count'] . " tables found)\n";
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Performance Optimizer
echo "5. Performance Optimizer Test:\n";
if (class_exists('SLMSPerformanceOptimizer')) {
    try {
        $optimizer = new SLMSPerformanceOptimizer();
        echo "✓ Performance optimizer: Initialized\n";
        
        // Test caching
        $test_key = 'test_opcache_' . time();
        $test_value = 'test_value_' . time();
        
        $optimizer->cacheSet($test_key, $test_value, 60);
        $cached_value = $optimizer->cacheGet($test_key);
        
        if ($cached_value === $test_value) {
            echo "✓ Caching system: Working\n";
        } else {
            echo "⚠ Caching system: Inconsistent\n";
        }
        
        // Test optimized functions
        $users = $optimizer->getOptimizedUsers(5);
        echo "✓ Optimized users query: " . count($users) . " users retrieved\n";
        
    } catch (Exception $e) {
        echo "✗ Performance optimizer error: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠ Performance optimizer: Not available\n";
}

echo "\n";

// Test 6: APCu Cache Test
echo "6. APCu Cache Test:\n";
if (function_exists('apcu_store')) {
    try {
        $apcu_key = 'test_apcu_' . time();
        $apcu_value = 'apcu_test_' . time();
        
        apcu_store($apcu_key, $apcu_value, 60);
        $retrieved = apcu_fetch($apcu_key);
        
        if ($retrieved === $apcu_value) {
            echo "✓ APCu cache: Working\n";
        } else {
            echo "⚠ APCu cache: Inconsistent\n";
        }
        
        // Get APCu info
        $apcu_info = apcu_cache_info();
        echo "APCu Memory Usage: " . round($apcu_info['mem_size'] / 1024 / 1024, 2) . "MB\n";
        echo "APCu Hit Rate: " . round($apcu_info['nhits'] / ($apcu_info['nhits'] + $apcu_info['nmisses']) * 100, 2) . "%\n";
        
    } catch (Exception $e) {
        echo "✗ APCu error: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠ APCu: Not available\n";
}

echo "\n";

// Test 7: Redis Cache Test
echo "7. Redis Cache Test:\n";
if (class_exists('Redis')) {
    try {
        $redis = new Redis();
        $connected = $redis->connect('127.0.0.1', 6379, 1);
        
        if ($connected) {
            echo "✓ Redis connection: Successful\n";
            
            $redis_key = 'test_redis_' . time();
            $redis_value = 'redis_test_' . time();
            
            $redis->setex($redis_key, 60, $redis_value);
            $retrieved = $redis->get($redis_key);
            
            if ($retrieved === $redis_value) {
                echo "✓ Redis cache: Working\n";
            } else {
                echo "⚠ Redis cache: Inconsistent\n";
            }
            
            $redis->close();
        } else {
            echo "⚠ Redis: Connection failed (Redis server may not be running)\n";
        }
        
    } catch (Exception $e) {
        echo "⚠ Redis: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠ Redis: Not available\n";
}

echo "\n";

// Test 8: System Performance Metrics
echo "8. System Performance Metrics:\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . " seconds\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";

// Memory usage
$memory_usage = memory_get_usage(true);
$peak_memory = memory_get_peak_usage(true);
echo "Current Memory Usage: " . round($memory_usage / 1024 / 1024, 2) . "MB\n";
echo "Peak Memory Usage: " . round($peak_memory / 1024 / 1024, 2) . "MB\n";

echo "\n";

// Test 9: File System Performance
echo "9. File System Performance Test:\n";
$start_time = microtime(true);

// Test file operations
for ($i = 0; $i < 100; $i++) {
    $test_file = "test_file_{$i}.tmp";
    file_put_contents($test_file, "test content {$i}");
    $content = file_get_contents($test_file);
    unlink($test_file);
}

$end_time = microtime(true);
$file_ops_time = ($end_time - $start_time) * 1000;
echo "File operations (100 files): " . round($file_ops_time, 2) . "ms\n";

echo "\n";

// Test 10: URL Generation Performance
echo "10. URL Generation Performance Test:\n";
$start_time = microtime(true);

for ($i = 0; $i < 1000; $i++) {
    $url = base_url("modules/test_{$i}.php");
}

$end_time = microtime(true);
$url_gen_time = ($end_time - $start_time) * 1000;
echo "URL generation (1000 URLs): " . round($url_gen_time, 2) . "ms\n";

echo "\n";

// Test 11: Menu Loading Performance
echo "11. Menu Loading Performance Test:\n";
if (function_exists('get_menu_items_from_database')) {
    $start_time = microtime(true);
    
    for ($i = 0; $i < 10; $i++) {
        $menu_items = get_menu_items_from_database();
    }
    
    $end_time = microtime(true);
    $menu_time = ($end_time - $start_time) * 1000;
    echo "Menu loading (10 iterations): " . round($menu_time, 2) . "ms\n";
    echo "Menu items loaded: " . count($menu_items) . "\n";
} else {
    echo "⚠ Menu function: Not available\n";
}

echo "\n";

// Test 12: Overall Performance Score
echo "12. Performance Score:\n";
$score = 0;
$max_score = 12;

// Check OPcache
if (extension_loaded('opcache') && function_exists('opcache_get_configuration')) {
    $config = opcache_get_configuration();
    if ($config['directives']['opcache.enable']) {
        $score += 2;
    }
}

// Check APCu
if (extension_loaded('apcu')) {
    $score += 1;
}

// Check Redis
if (class_exists('Redis')) {
    $score += 1;
}

// Check database
try {
    $pdo = get_pdo();
    $score += 2;
} catch (Exception $e) {
    // No points for database failure
}

// Check performance optimizer
if (class_exists('SLMSPerformanceOptimizer')) {
    $score += 2;
}

// Check optimized config
if (file_exists('config_optimized.php')) {
    $score += 1;
}

// Performance thresholds
if ($file_ops_time < 100) $score += 1;
if ($url_gen_time < 50) $score += 1;
if (isset($menu_time) && $menu_time < 100) $score += 1;

$percentage = round(($score / $max_score) * 100, 1);
echo "Performance Score: {$score}/{$max_score} ({$percentage}%)\n";

if ($percentage >= 90) {
    echo "🎉 Excellent performance! Your system is well optimized.\n";
} elseif ($percentage >= 75) {
    echo "✅ Good performance! Some optimizations are working well.\n";
} elseif ($percentage >= 50) {
    echo "⚠️  Moderate performance. Consider additional optimizations.\n";
} else {
    echo "❌ Poor performance. Significant optimizations needed.\n";
}

echo "\n";

// Test 13: Recommendations
echo "13. Recommendations:\n";
if (!extension_loaded('opcache')) {
    echo "- Install and enable OPcache extension\n";
}
if (!extension_loaded('apcu')) {
    echo "- Install and enable APCu extension\n";
}
if (!class_exists('Redis')) {
    echo "- Install and enable Redis extension\n";
}
if (!file_exists('config_optimized.php')) {
    echo "- Use optimized configuration file\n";
}
if ($file_ops_time > 100) {
    echo "- Consider using SSD storage for better file I/O performance\n";
}
if ($url_gen_time > 50) {
    echo "- Optimize URL generation functions\n";
}

echo "\n=== Test Complete ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
?> 