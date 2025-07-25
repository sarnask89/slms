<?php
/**
 * sLMS Performance Optimization System
 * Implements advanced PHP optimization techniques for maximum performance
 * Based on industry best practices and optimization strategies
 */

class SLMSPerformanceOptimizer {
    
    private $cache_enabled = false;
    private $opcache_enabled = false;
    private $apcu_enabled = false;
    private $redis_enabled = false;
    private $cache_driver = 'file';
    private $cache_path = 'cache/';
    private $cache_ttl = 3600; // 1 hour default
    
    public function __construct() {
        $this->initializeCache();
        $this->checkExtensions();
        $this->optimizePHP();
    }
    
    /**
     * 1. OPCODE CACHING - Check and enable opcode caching
     */
    private function checkExtensions() {
        // Check OPcache
        if (extension_loaded('opcache')) {
            $this->opcache_enabled = true;
            $this->optimizeOPcache();
        }
        
        // Check APCu
        if (extension_loaded('apcu') && ini_get('apc.enabled')) {
            $this->apcu_enabled = true;
        }
        
        // Check Redis
        if (extension_loaded('redis')) {
            $this->redis_enabled = true;
            $this->cache_driver = 'redis';
        }
        
        // Check cURL for async operations
        if (!extension_loaded('curl')) {
            error_log('sLMS Performance: cURL extension not loaded - async operations disabled');
        }
    }
    
    /**
     * Optimize OPcache settings
     */
    private function optimizeOPcache() {
        if (function_exists('opcache_get_configuration')) {
            $config = opcache_get_configuration();
            if ($config && $config['directives']['opcache.enable']) {
                // OPcache is enabled, optimize settings
                ini_set('opcache.memory_consumption', 256);
                ini_set('opcache.interned_strings_buffer', 16);
                ini_set('opcache.max_accelerated_files', 10000);
                ini_set('opcache.revalidate_freq', 60);
                ini_set('opcache.fast_shutdown', 1);
            }
        }
    }
    
    /**
     * 2. DATABASE QUERY OPTIMIZATION
     */
    public function optimizeDatabaseQueries($pdo) {
        // Enable query logging for optimization
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database indexes for better performance
        $this->createDatabaseIndexes($pdo);
        
        return $pdo;
    }
    
    /**
     * Create optimized database indexes
     */
    private function createDatabaseIndexes($pdo) {
        $indexes = [
            'users' => [
                'CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)',
                'CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)',
                'CREATE INDEX IF NOT EXISTS idx_users_active ON users(is_active)'
            ],
            'clients' => [
                'CREATE INDEX IF NOT EXISTS idx_clients_email ON clients(email)',
                'CREATE INDEX IF NOT EXISTS idx_clients_status ON clients(status)',
                'CREATE INDEX IF NOT EXISTS idx_clients_created ON clients(created_at)'
            ],
            'devices' => [
                'CREATE INDEX IF NOT EXISTS idx_devices_client ON devices(client_id)',
                'CREATE INDEX IF NOT EXISTS idx_devices_network ON devices(network_id)',
                'CREATE INDEX IF NOT EXISTS idx_devices_status ON devices(status)'
            ],
            'menu_items' => [
                'CREATE INDEX IF NOT EXISTS idx_menu_position ON menu_items(position)',
                'CREATE INDEX IF NOT EXISTS idx_menu_enabled ON menu_items(enabled)',
                'CREATE INDEX IF NOT EXISTS idx_menu_parent ON menu_items(parent_id)'
            ]
        ];
        
        foreach ($indexes as $table => $table_indexes) {
            foreach ($table_indexes as $index_sql) {
                try {
                    $pdo->exec($index_sql);
                } catch (PDOException $e) {
                    // Index might already exist, continue
                }
            }
        }
    }
    
    /**
     * 3. IMPLEMENT CACHING SYSTEM
     */
    private function initializeCache() {
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cache_path)) {
            mkdir($this->cache_path, 0755, true);
        }
        
        // Set cache headers
        if (!headers_sent()) {
            header('Cache-Control: public, max-age=3600');
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
        }
    }
    
    /**
     * Cache management functions
     */
    public function cacheGet($key) {
        if ($this->apcu_enabled) {
            return apcu_fetch($key);
        } elseif ($this->redis_enabled) {
            try {
                $redis = new Redis();
                $redis->connect('127.0.0.1', 6379);
                return $redis->get($key);
            } catch (Exception $e) {
                // Fallback to file cache if Redis fails
                error_log("Redis connection failed, falling back to file cache: " . $e->getMessage());
                return $this->fileCacheGet($key);
            }
        } else {
            return $this->fileCacheGet($key);
        }
    }
    
    public function cacheSet($key, $value, $ttl = null) {
        $ttl = $ttl ?: $this->cache_ttl;
        
        if ($this->apcu_enabled) {
            return apcu_store($key, $value, $ttl);
        } elseif ($this->redis_enabled) {
            try {
                $redis = new Redis();
                $redis->connect('127.0.0.1', 6379);
                return $redis->setex($key, $ttl, $value);
            } catch (Exception $e) {
                // Fallback to file cache if Redis fails
                error_log("Redis connection failed, falling back to file cache: " . $e->getMessage());
                return $this->fileCacheSet($key, $value, $ttl);
            }
        } else {
            return $this->fileCacheSet($key, $value, $ttl);
        }
    }
    
    private function fileCacheGet($key) {
        $filename = $this->cache_path . md5($key) . '.cache';
        if (file_exists($filename) && (time() - filemtime($filename)) < $this->cache_ttl) {
            return unserialize(file_get_contents($filename));
        }
        return false;
    }
    
    private function fileCacheSet($key, $value, $ttl) {
        $filename = $this->cache_path . md5($key) . '.cache';
        return file_put_contents($filename, serialize($value));
    }
    
    /**
     * 4. ASYNCHRONOUS OPERATIONS
     */
    public function asyncCurlRequests($urls, $callback = null) {
        if (!extension_loaded('curl')) {
            return $this->sequentialRequests($urls, $callback);
        }
        
        $multi_handler = curl_multi_init();
        $curl_handles = [];
        $results = [];
        
        // Initialize all cURL handles
        foreach ($urls as $url) {
            $curl_handles[$url] = curl_init($url);
            curl_setopt($curl_handles[$url], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_handles[$url], CURLOPT_TIMEOUT, 30);
            curl_setopt($curl_handles[$url], CURLOPT_FOLLOWLOCATION, true);
            curl_multi_add_handle($multi_handler, $curl_handles[$url]);
        }
        
        // Execute all requests
        do {
            $status = curl_multi_exec($multi_handler, $running);
            if ($running) {
                curl_multi_select($multi_handler);
            }
        } while ($running && $status == CURLM_OK);
        
        // Collect results
        foreach ($urls as $url) {
            $response = curl_multi_getcontent($curl_handles[$url]);
            $results[$url] = $response;
            
            if ($callback && is_callable($callback)) {
                $callback($url, $response);
            }
            
            curl_multi_remove_handle($multi_handler, $curl_handles[$url]);
            curl_close($curl_handles[$url]);
        }
        
        curl_multi_close($multi_handler);
        return $results;
    }
    
    private function sequentialRequests($urls, $callback = null) {
        $results = [];
        foreach ($urls as $url) {
            $response = file_get_contents($url);
            $results[$url] = $response;
            
            if ($callback && is_callable($callback)) {
                $callback($url, $response);
            }
        }
        return $results;
    }
    
    /**
     * 5. OPTIMIZED DATABASE QUERIES
     */
    public function getOptimizedUsers($limit = 50, $offset = 0) {
        $cache_key = "users_{$limit}_{$offset}";
        
        // Check cache first
        if ($cached = $this->cacheGet($cache_key)) {
            return $cached;
        }
        
        // Optimized query - only select needed columns
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, is_active, created_at 
            FROM users 
            WHERE is_active = 1 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cache results
        $this->cacheSet($cache_key, $results, 300); // 5 minutes
        
        return $results;
    }
    
    public function getOptimizedClients($limit = 50, $offset = 0) {
        $cache_key = "clients_{$limit}_{$offset}";
        
        if ($cached = $this->cacheGet($cache_key)) {
            return $cached;
        }
        
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, email, phone, status, created_at 
            FROM clients 
            WHERE status = 'active' 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->cacheSet($cache_key, $results, 300);
        
        return $results;
    }
    
    /**
     * 6. SERVER CONFIGURATION OPTIMIZATION
     */
    private function optimizePHP() {
        // Optimize PHP settings for performance
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 300);
        ini_set('max_input_time', 300);
        ini_set('post_max_size', '10M');
        ini_set('upload_max_filesize', '10M');
        
        // Enable output buffering
        if (!ob_get_level()) {
            ob_start();
        }
        
        // Set timezone
        date_default_timezone_set('Europe/Warsaw');
        
        // Optimize session handling
        ini_set('session.gc_maxlifetime', 3600);
        ini_set('session.cookie_lifetime', 3600);
    }
    
    /**
     * 7. CONTENT DELIVERY NETWORK (CDN) SUPPORT
     */
    public function getCDNUrl($path) {
        $cdn_domain = 'https://cdn.example.com'; // Configure your CDN domain
        return $cdn_domain . '/' . ltrim($path, '/');
    }
    
    public function isCDNEnabled() {
        return !empty($this->getCDNUrl(''));
    }
    
    /**
     * 8. CODE PROFILING
     */
    public function startProfiling() {
        if (function_exists('xhprof_enable')) {
            xhprof_enable();
            return true;
        }
        return false;
    }
    
    public function endProfiling() {
        if (function_exists('xhprof_disable')) {
            return xhprof_disable();
        }
        return null;
    }
    
    /**
     * 9. LOAD BALANCER SUPPORT
     */
    public function getServerInfo() {
        return [
            'server_id' => $_SERVER['SERVER_NAME'] ?? 'unknown',
            'load_average' => sys_getloadavg(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'uptime' => $this->getUptime()
        ];
    }
    
    private function getUptime() {
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            return explode(' ', $uptime)[0];
        }
        return 0;
    }
    
    /**
     * PERFORMANCE MONITORING
     */
    public function getPerformanceStats() {
        return [
            'cache_enabled' => $this->cache_enabled,
            'opcache_enabled' => $this->opcache_enabled,
            'apcu_enabled' => $this->apcu_enabled,
            'redis_enabled' => $this->redis_enabled,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'server_info' => $this->getServerInfo()
        ];
    }
    
    /**
     * CLEANUP AND MAINTENANCE
     */
    public function cleanupCache() {
        if ($this->cache_driver === 'file') {
            $files = glob($this->cache_path . '*.cache');
            foreach ($files as $file) {
                if (time() - filemtime($file) > $this->cache_ttl) {
                    unlink($file);
                }
            }
        }
    }
}

// Initialize the performance optimizer
$performance_optimizer = new SLMSPerformanceOptimizer();

// Example usage functions
function get_optimized_menu_items() {
    global $performance_optimizer;
    
    $cache_key = 'menu_items_optimized';
    if ($cached = $performance_optimizer->cacheGet($cache_key)) {
        return $cached;
    }
    
    $pdo = get_pdo();
    $stmt = $pdo->prepare("
        SELECT id, label, url, icon, type, parent_id, position 
        FROM menu_items 
        WHERE enabled = 1 
        ORDER BY position ASC, id ASC
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build hierarchical structure
    $menu_tree = [];
    $children = [];
    
    foreach ($results as $item) {
        if ($item['parent_id'] === null || $item['parent_id'] == 0) {
            $menu_tree[] = $item;
        } else {
            $children[$item['parent_id']][] = $item;
        }
    }
    
    foreach ($menu_tree as &$parent) {
        $parent['children'] = isset($children[$parent['id']]) ? $children[$parent['id']] : [];
    }
    
    $performance_optimizer->cacheSet($cache_key, $menu_tree, 1800); // 30 minutes
    
    return $menu_tree;
}

function get_optimized_dashboard_stats() {
    global $performance_optimizer;
    
    $cache_key = 'dashboard_stats';
    if ($cached = $performance_optimizer->cacheGet($cache_key)) {
        return $cached;
    }
    
    $pdo = get_pdo();
    
    $stats = [
        'users' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn(),
        'clients' => $pdo->query("SELECT COUNT(*) FROM clients WHERE status = 'active'")->fetchColumn(),
        'devices' => $pdo->query("SELECT COUNT(*) FROM devices WHERE status = 'active'")->fetchColumn(),
        'networks' => $pdo->query("SELECT COUNT(*) FROM networks")->fetchColumn(),
        'services' => $pdo->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn(),
        'invoices' => $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'pending'")->fetchColumn()
    ];
    
    $performance_optimizer->cacheSet($cache_key, $stats, 300); // 5 minutes
    
    return $stats;
}

// Performance monitoring
function log_performance_metrics() {
    global $performance_optimizer;
    
    $stats = $performance_optimizer->getPerformanceStats();
    $log_entry = date('Y-m-d H:i:s') . ' - ' . json_encode($stats) . "\n";
    
    file_put_contents('logs/performance.log', $log_entry, FILE_APPEND | LOCK_EX);
}

// Auto-cleanup on shutdown
register_shutdown_function(function() {
    global $performance_optimizer;
    $performance_optimizer->cleanupCache();
    log_performance_metrics();
});

?> 