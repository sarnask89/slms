<?php
/**
 * Simple OPcache Test Script
 * Tests basic OPcache functionality and system performance
 */

echo "=== Simple OPcache Test ===\n\n";

// Test 1: Basic PHP Info
echo "1. PHP Information:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Zend Engine: " . zend_version() . "\n";
echo "SAPI: " . php_sapi_name() . "\n";

// Test 2: OPcache Status
echo "\n2. OPcache Status:\n";
if (extension_loaded('opcache') || extension_loaded('Zend OPcache')) {
    echo "✓ OPcache extension: Loaded\n";
    
    if (function_exists('opcache_get_configuration')) {
        $config = opcache_get_configuration();
        echo "✓ OPcache functions: Available\n";
        echo "OPcache Enabled: " . ($config['directives']['opcache.enable'] ? 'Yes' : 'No') . "\n";
        echo "Memory Consumption: " . $config['directives']['opcache.memory_consumption'] . "MB\n";
        echo "Max Accelerated Files: " . $config['directives']['opcache.max_accelerated_files'] . "\n";
        
        if (function_exists('opcache_get_status')) {
            $status = opcache_get_status();
            if ($status) {
                echo "Memory Usage: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . "MB\n";
                echo "Hit Rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
                echo "Cached Files: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
            }
        }
    } else {
        echo "✗ OPcache functions: Not available\n";
    }
} else {
    echo "✗ OPcache extension: Not loaded\n";
}

// Test 3: APCu Status
echo "\n3. APCu Status:\n";
if (extension_loaded('apcu')) {
    echo "✓ APCu extension: Loaded\n";
    
    if (function_exists('apcu_store')) {
        echo "✓ APCu functions: Available\n";
        
        // Test basic APCu functionality
        $test_key = 'test_' . time();
        $test_value = 'test_value_' . time();
        
        if (apcu_store($test_key, $test_value, 60)) {
            $retrieved = apcu_fetch($test_key);
            if ($retrieved === $test_value) {
                echo "✓ APCu cache: Working\n";
            } else {
                echo "⚠ APCu cache: Inconsistent\n";
            }
        } else {
            echo "✗ APCu cache: Failed to store\n";
        }
        
        // Try to get APCu info
        if (function_exists('apcu_cache_info')) {
            $info = apcu_cache_info();
            if ($info) {
                echo "APCu Memory Usage: " . round($info['mem_size'] / 1024 / 1024, 2) . "MB\n";
                if ($info['nhits'] + $info['nmisses'] > 0) {
                    $hit_rate = round($info['nhits'] / ($info['nhits'] + $info['nmisses']) * 100, 2);
                    echo "APCu Hit Rate: {$hit_rate}%\n";
                }
            }
        }
    } else {
        echo "✗ APCu functions: Not available\n";
    }
} else {
    echo "✗ APCu extension: Not loaded\n";
}

// Test 4: Redis Status
echo "\n4. Redis Status:\n";
if (extension_loaded('redis')) {
    echo "✓ Redis extension: Loaded\n";
    
    if (class_exists('Redis')) {
        echo "✓ Redis class: Available\n";
        
        try {
            $redis = new Redis();
            $connected = $redis->connect('127.0.0.1', 6379, 1);
            
            if ($connected) {
                echo "✓ Redis connection: Successful\n";
                
                $test_key = 'test_redis_' . time();
                $test_value = 'redis_test_' . time();
                
                if ($redis->setex($test_key, 60, $test_value)) {
                    $retrieved = $redis->get($test_key);
                    if ($retrieved === $test_value) {
                        echo "✓ Redis cache: Working\n";
                    } else {
                        echo "⚠ Redis cache: Inconsistent\n";
                    }
                }
                
                $redis->close();
            } else {
                echo "⚠ Redis: Connection failed (server may not be running)\n";
            }
        } catch (Exception $e) {
            echo "⚠ Redis: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ Redis class: Not available\n";
    }
} else {
    echo "✗ Redis extension: Not loaded\n";
}

// Test 5: System Performance
echo "\n5. System Performance:\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . " seconds\n";
echo "Current Memory Usage: " . round(memory_get_usage(true) / 1024 / 1024, 2) . "MB\n";

// Test 6: File Operations Performance
echo "\n6. File Operations Performance:\n";
$start_time = microtime(true);

for ($i = 0; $i < 50; $i++) {
    $test_file = "test_file_{$i}.tmp";
    file_put_contents($test_file, "test content {$i}");
    $content = file_get_contents($test_file);
    unlink($test_file);
}

$end_time = microtime(true);
$file_ops_time = ($end_time - $start_time) * 1000;
echo "File operations (50 files): " . round($file_ops_time, 2) . "ms\n";

// Test 7: Simple URL Generation
echo "\n7. URL Generation Performance:\n";
$start_time = microtime(true);

for ($i = 0; $i < 500; $i++) {
    $url = "/modules/test_{$i}.php";
}

$end_time = microtime(true);
$url_gen_time = ($end_time - $start_time) * 1000;
echo "URL generation (500 URLs): " . round($url_gen_time, 2) . "ms\n";

// Test 8: Performance Score
echo "\n8. Performance Score:\n";
$score = 0;
$max_score = 8;

if ((extension_loaded('opcache') || extension_loaded('Zend OPcache')) && function_exists('opcache_get_configuration')) {
    $config = opcache_get_configuration();
    if ($config['directives']['opcache.enable']) {
        $score += 2;
    }
}

if (extension_loaded('apcu')) {
    $score += 1;
}

if (extension_loaded('redis')) {
    $score += 1;
}

if ($file_ops_time < 50) $score += 1;
if ($url_gen_time < 25) $score += 1;

// Check if we can connect to Redis
if (extension_loaded('redis')) {
    try {
        $redis = new Redis();
        if ($redis->connect('127.0.0.1', 6379, 1)) {
            $score += 1;
            $redis->close();
        }
    } catch (Exception $e) {
        // No points for Redis connection failure
    }
}

// Check if APCu is working
if (extension_loaded('apcu') && function_exists('apcu_store')) {
    $test_key = 'score_test_' . time();
    if (apcu_store($test_key, 'test', 60)) {
        $score += 1;
    }
}

$percentage = round(($score / $max_score) * 100, 1);
echo "Performance Score: {$score}/{$max_score} ({$percentage}%)\n";

if ($percentage >= 90) {
    echo "🎉 Excellent! System is well optimized.\n";
} elseif ($percentage >= 75) {
    echo "✅ Good! Most optimizations are working.\n";
} elseif ($percentage >= 50) {
    echo "⚠️  Moderate. Some optimizations needed.\n";
} else {
    echo "❌ Poor. Significant optimizations needed.\n";
}

echo "\n=== Test Complete ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
?> 