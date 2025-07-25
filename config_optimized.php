<?php
/**
 * sLMS Optimized Configuration
 * Enhanced configuration with performance optimizations
 * Based on PHP optimization best practices
 */

// Load the performance optimizer
require_once 'optimized_performance.php';

// Database configuration with optimization
$db_host = 'localhost';
$db_name = 'slmsdb';
$db_user = 'slms';
$db_pass = 'slms123';
$db_charset = 'utf8mb4';

// Performance-optimized PDO connection
function get_optimized_pdo() {
    global $db_host, $db_name, $db_user, $db_pass, $db_charset, $performance_optimizer;
    
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // Performance optimizations
        PDO::ATTR_PERSISTENT         => true, // Persistent connections
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    
    // Apply performance optimizations
    return $performance_optimizer->optimizeDatabaseQueries($pdo);
}

// Fallback to original function for compatibility
function get_pdo() {
    return get_optimized_pdo();
}

// Optimized base URL function with caching
function get_optimized_base_url($path = '') {
    global $performance_optimizer;
    
    $cache_key = 'base_url_' . md5($path);
    if ($cached = $performance_optimizer->cacheGet($cache_key)) {
        return $cached;
    }
    
    $url = base_url($path);
    $performance_optimizer->cacheSet($cache_key, $url, 3600); // Cache for 1 hour
    
    return $url;
}

// Enhanced base URL function with CDN support
function base_url($path = '') {
    global $performance_optimizer;
    
    // Check if CDN is enabled for static assets
    if (strpos($path, 'assets/') === 0 && $performance_optimizer->isCDNEnabled()) {
        return $performance_optimizer->getCDNUrl($path);
    }
    
    // Always calculate from the root directory, not relative to current script
    if (isset($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] !== 'Standard input code') {
        // Get the root directory by finding the common path
        $script_path = $_SERVER['SCRIPT_NAME'];
        $root_dir = '';
        
        // If we're in a subdirectory, calculate the root
        if (strpos($script_path, '/modules/') !== false || strpos($script_path, 'modules/') !== false) {
            $root_dir = dirname(dirname($script_path));
        } else {
            $root_dir = dirname($script_path);
        }
        
        // Remove trailing slash if present
        $root_dir = rtrim($root_dir, '/');
        // If root_dir is just '.', use empty string
        if ($root_dir === '.') {
            $root_dir = '';
        }
        // Return the base URL
        return $root_dir . '/' . ltrim($path, '/');
    } else {
        // Fallback for command line or when SCRIPT_NAME is not available
        return '/' . ltrim($path, '/');
    }
}

// Optimized configuration settings
$config = [
    // Database settings
    'db_host' => $db_host,
    'db_name' => $db_name,
    'db_user' => $db_user,
    'db_pass' => $db_pass,
    'db_charset' => $db_charset,
    
    // Performance settings
    'cache_enabled' => true,
    'cache_ttl' => 3600,
    'opcache_enabled' => extension_loaded('opcache'),
    'apcu_enabled' => extension_loaded('apcu'),
    
    // Cacti integration settings
    'cacti_url' => 'http://localhost:8081',
    'cacti_username' => 'admin',
    'cacti_password' => 'admin',
    
    // SNMP default settings
    'default_snmp_community' => 'public',
    'default_snmp_timeout' => 1000000,
    'default_snmp_retries' => 3,
    
    // System settings
    'system_name' => 'sLMS',
    'system_version' => '1.0.0',
    'timezone' => 'Europe/Warsaw',
    
    // Performance optimizations
    'memory_limit' => '256M',
    'max_execution_time' => 300,
    'upload_max_filesize' => '10M',
    'post_max_size' => '10M',
    
    // CDN settings
    'cdn_enabled' => false,
    'cdn_domain' => 'https://cdn.example.com',
    
    // Load balancer settings
    'load_balancer_enabled' => false,
    'server_id' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    
    // Monitoring settings
    'performance_monitoring' => true,
    'log_performance_metrics' => true,
    'cleanup_cache_automatically' => true
];

// Apply performance optimizations
if ($config['performance_monitoring']) {
    // Start profiling if available
    $performance_optimizer->startProfiling();
    
    // Set performance headers
    if (!headers_sent()) {
        header('X-Powered-By: sLMS Optimized');
        header('X-Performance-Optimized: true');
    }
}

// Set timezone
date_default_timezone_set($config['timezone']);

// Optimized helper functions
function get_optimized_config($key = null) {
    global $config;
    
    if ($key === null) {
        return $config;
    }
    
    return $config[$key] ?? null;
}

// Performance monitoring function
function monitor_performance($operation, $start_time = null) {
    global $performance_optimizer;
    
    if ($start_time === null) {
        $start_time = microtime(true);
    }
    
    $execution_time = microtime(true) - $start_time;
    $memory_usage = memory_get_usage(true);
    
    $metrics = [
        'operation' => $operation,
        'execution_time' => $execution_time,
        'memory_usage' => $memory_usage,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Log if performance monitoring is enabled
    if (get_optimized_config('log_performance_metrics')) {
        $log_entry = json_encode($metrics) . "\n";
        file_put_contents('logs/performance_metrics.log', $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    return $metrics;
}

// Optimized database helper functions
function get_optimized_menu_items_from_database() {
    return get_optimized_menu_items();
}

function get_optimized_dashboard_statistics() {
    return get_optimized_dashboard_stats();
}

// Async operations helper
function async_fetch_urls($urls, $callback = null) {
    global $performance_optimizer;
    return $performance_optimizer->asyncCurlRequests($urls, $callback);
}

// Cache management helpers
function cache_get($key) {
    global $performance_optimizer;
    return $performance_optimizer->cacheGet($key);
}

function cache_set($key, $value, $ttl = null) {
    global $performance_optimizer;
    return $performance_optimizer->cacheSet($key, $value, $ttl);
}

// Performance stats helper
function get_performance_stats() {
    global $performance_optimizer;
    return $performance_optimizer->getPerformanceStats();
}

// Cleanup function
function cleanup_performance_cache() {
    global $performance_optimizer;
    $performance_optimizer->cleanupCache();
}

// Register shutdown function for cleanup
register_shutdown_function(function() {
    global $performance_optimizer;
    
    // End profiling if started
    $performance_optimizer->endProfiling();
    
    // Cleanup cache
    if (get_optimized_config('cleanup_cache_automatically')) {
        cleanup_performance_cache();
    }
    
    // Log final performance metrics
    if (get_optimized_config('log_performance_metrics')) {
        log_performance_metrics();
    }
});

// Export configuration for backward compatibility
$db_host = $config['db_host'];
$db_name = $config['db_name'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];
$db_charset = $config['db_charset'];

$cacti_url = $config['cacti_url'];
$cacti_username = $config['cacti_username'];
$cacti_password = $config['cacti_password'];

$default_snmp_community = $config['default_snmp_community'];
$default_snmp_timeout = $config['default_snmp_timeout'];
$default_snmp_retries = $config['default_snmp_retries'];

$system_name = $config['system_name'];
$system_version = $config['system_version'];
$timezone = $config['timezone'];

?> 