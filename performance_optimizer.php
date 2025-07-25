<?php
/**
 * Performance Optimizer for Network Monitoring System
 * Implements caching, query optimization, and memory management
 */

class PerformanceOptimizer {
    private $redis;
    private $mysqli;
    private $cacheEnabled = false;
    private $queryStats = [];
    
    public function __construct() {
        $this->initializeRedis();
        $this->initializeDatabase();
    }
    
    /**
     * Initialize Redis connection for caching
     */
    private function initializeRedis() {
        if (extension_loaded('redis')) {
            try {
                $this->redis = new Redis();
                $this->redis->connect('127.0.0.1', 6379);
                
                if ($this->redis->ping()) {
                    $this->cacheEnabled = true;
                    echo "✅ Redis cache enabled\n";
                }
            } catch (Exception $e) {
                echo "⚠️ Redis not available: " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * Initialize database connection
     */
    private function initializeDatabase() {
        if (file_exists('config.php')) {
            include 'config.php';
            
            if (isset($db_host) && isset($db_user) && isset($db_pass) && isset($db_name)) {
                $this->mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
                
                if ($this->mysqli->connect_error) {
                    throw new Exception("Database connection failed: " . $this->mysqli->connect_error);
                }
                
                // Set optimal MySQL settings
                $this->mysqli->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
                $this->mysqli->query("SET SESSION wait_timeout = 600");
                $this->mysqli->query("SET SESSION interactive_timeout = 600");
                
                echo "✅ Database connection established\n";
            }
        }
    }
    
    /**
     * Optimize database tables
     */
    public function optimizeDatabaseTables() {
        echo "\n🔧 Optimizing Database Tables...\n";
        
        $tables = [
            'devices', 'clients', 'networks', 'interfaces', 
            'snmp_data', 'monitoring_data', 'alerts', 'logs'
        ];
        
        foreach ($tables as $table) {
            $result = $this->mysqli->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                $this->mysqli->query("OPTIMIZE TABLE $table");
                echo "✅ Optimized table: $table\n";
            }
        }
    }
    
    /**
     * Create database indexes for better performance
     */
    public function createDatabaseIndexes() {
        echo "\n📊 Creating Database Indexes...\n";
        
        $indexes = [
            'devices' => [
                'idx_status' => 'status',
                'idx_type' => 'device_type',
                'idx_location' => 'location'
            ],
            'interfaces' => [
                'idx_device_id' => 'device_id',
                'idx_status' => 'status',
                'idx_type' => 'interface_type'
            ],
            'snmp_data' => [
                'idx_device_id' => 'device_id',
                'idx_timestamp' => 'timestamp',
                'idx_oid' => 'oid'
            ],
            'monitoring_data' => [
                'idx_device_id' => 'device_id',
                'idx_timestamp' => 'timestamp',
                'idx_metric' => 'metric_name'
            ]
        ];
        
        foreach ($indexes as $table => $tableIndexes) {
            $result = $this->mysqli->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                foreach ($tableIndexes as $indexName => $column) {
                    try {
                        $this->mysqli->query("CREATE INDEX $indexName ON $table ($column)");
                        echo "✅ Created index $indexName on $table.$column\n";
                    } catch (Exception $e) {
                        // Index might already exist
                        echo "ℹ️ Index $indexName might already exist\n";
                    }
                }
            }
        }
    }
    
    /**
     * Implement query caching
     */
    public function cacheQuery($query, $params = [], $ttl = 300) {
        if (!$this->cacheEnabled) {
            return $this->executeQuery($query, $params);
        }
        
        $cacheKey = 'query:' . md5($query . serialize($params));
        
        // Try to get from cache
        $cached = $this->redis->get($cacheKey);
        if ($cached !== false) {
            $this->queryStats['cache_hits']++;
            return json_decode($cached, true);
        }
        
        // Execute query and cache result
        $result = $this->executeQuery($query, $params);
        $this->redis->setex($cacheKey, $ttl, json_encode($result));
        $this->queryStats['cache_misses']++;
        
        return $result;
    }
    
    /**
     * Execute optimized query with prepared statements
     */
    private function executeQuery($query, $params = []) {
        $startTime = microtime(true);
        
        $stmt = $this->mysqli->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $types = str_repeat('s', count($params)); // Assume all strings for now
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            $executionTime = microtime(true) - $startTime;
            $this->queryStats['total_time'] += $executionTime;
            $this->queryStats['query_count']++;
            
            if ($executionTime > 1.0) {
                echo "⚠️ Slow query detected: " . number_format($executionTime, 4) . "s\n";
            }
            
            return $data;
        }
        
        return false;
    }
    
    /**
     * Optimize SNMP polling
     */
    public function optimizeSNMPPolling() {
        echo "\n📡 Optimizing SNMP Polling...\n";
        
        // Implement batch SNMP requests
        $devices = $this->cacheQuery("SELECT id, ip_address, snmp_community FROM devices WHERE status = 'active' LIMIT 10");
        
        if ($devices) {
            foreach ($devices as $device) {
                $this->batchSNMPRequest($device);
            }
        }
    }
    
    /**
     * Batch SNMP requests for better performance
     */
    private function batchSNMPRequest($device) {
        $oids = [
            '1.3.6.1.2.1.1.1.0', // System description
            '1.3.6.1.2.1.1.3.0', // Uptime
            '1.3.6.1.2.1.2.2.1.2', // Interface descriptions
            '1.3.6.1.2.1.2.2.1.10', // Interface in octets
            '1.3.6.1.2.1.2.2.1.16'  // Interface out octets
        ];
        
        $results = [];
        foreach ($oids as $oid) {
            try {
                $value = snmp2_get($device['ip_address'], $device['snmp_community'], $oid);
                if ($value !== false) {
                    $results[$oid] = $value;
                }
            } catch (Exception $e) {
                // Handle SNMP errors gracefully
            }
        }
        
        // Cache SNMP results
        if ($this->cacheEnabled && !empty($results)) {
            $cacheKey = 'snmp:' . $device['id'] . ':' . time();
            $this->redis->setex($cacheKey, 60, json_encode($results));
        }
        
        return $results;
    }
    
    /**
     * Implement memory management
     */
    public function manageMemory() {
        echo "\n🧠 Memory Management...\n";
        
        $currentMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        
        echo "Current Memory: " . number_format($currentMemory / 1024 / 1024, 2) . " MB\n";
        echo "Peak Memory: " . number_format($peakMemory / 1024 / 1024, 2) . " MB\n";
        
        // Clear cache if memory usage is high
        if ($currentMemory > 50 * 1024 * 1024) { // 50MB
            if ($this->cacheEnabled) {
                $this->redis->flushDB();
                echo "✅ Cleared cache due to high memory usage\n";
            }
        }
        
        // Force garbage collection
        gc_collect_cycles();
        echo "✅ Garbage collection completed\n";
    }
    
    /**
     * Optimize file operations
     */
    public function optimizeFileOperations() {
        echo "\n📁 Optimizing File Operations...\n";
        
        // Implement log rotation
        $logFiles = ['logs/debug_optimization.log', 'logs/system.log', 'logs/error.log'];
        
        foreach ($logFiles as $logFile) {
            if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) { // 10MB
                $backupFile = $logFile . '.' . date('Y-m-d-H-i-s');
                rename($logFile, $backupFile);
                echo "✅ Rotated log file: $logFile\n";
            }
        }
        
        // Clean old cache files
        $this->cleanOldCacheFiles();
    }
    
    /**
     * Clean old cache files
     */
    private function cleanOldCacheFiles() {
        $cacheDir = 'cache/';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*');
            $now = time();
            
            foreach ($files as $file) {
                if (is_file($file) && ($now - filemtime($file)) > 86400) { // 24 hours
                    unlink($file);
                    echo "✅ Cleaned old cache file: $file\n";
                }
            }
        }
    }
    
    /**
     * Generate performance report
     */
    public function generatePerformanceReport() {
        echo "\n📊 Performance Report\n";
        echo "===================\n";
        
        if (!empty($this->queryStats)) {
            echo "Query Statistics:\n";
            echo "- Total Queries: " . $this->queryStats['query_count'] . "\n";
            echo "- Total Time: " . number_format($this->queryStats['total_time'], 4) . "s\n";
            echo "- Average Time: " . number_format($this->queryStats['total_time'] / $this->queryStats['query_count'], 4) . "s\n";
            
            if (isset($this->queryStats['cache_hits'])) {
                echo "- Cache Hits: " . $this->queryStats['cache_hits'] . "\n";
                echo "- Cache Misses: " . $this->queryStats['cache_misses'] . "\n";
                $hitRate = ($this->queryStats['cache_hits'] / ($this->queryStats['cache_hits'] + $this->queryStats['cache_misses'])) * 100;
                echo "- Cache Hit Rate: " . number_format($hitRate, 2) . "%\n";
            }
        }
        
        $memoryUsage = memory_get_usage(true);
        echo "Memory Usage: " . number_format($memoryUsage / 1024 / 1024, 2) . " MB\n";
        
        echo "Cache Status: " . ($this->cacheEnabled ? "Enabled" : "Disabled") . "\n";
    }
    
    /**
     * Run all optimizations
     */
    public function runAllOptimizations() {
        echo "🚀 Starting Performance Optimization...\n";
        
        $this->optimizeDatabaseTables();
        $this->createDatabaseIndexes();
        $this->optimizeSNMPPolling();
        $this->optimizeFileOperations();
        $this->manageMemory();
        $this->generatePerformanceReport();
        
        echo "\n✅ Performance optimization completed!\n";
    }
}

// Initialize and run optimizer
try {
    $optimizer = new PerformanceOptimizer();
    $optimizer->runAllOptimizations();
} catch (Exception $e) {
    echo "❌ Error during optimization: " . $e->getMessage() . "\n";
}
?>