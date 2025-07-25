<?php
/**
 * Comprehensive Debugging and Optimization Tool
 * For Network Monitoring System
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/debug_optimization.log');

class SystemDebugger {
    private $startTime;
    private $memoryStart;
    private $issues = [];
    private $optimizations = [];
    
    public function __construct() {
        $this->startTime = microtime(true);
        $this->memoryStart = memory_get_usage();
    }
    
    /**
     * Check PHP configuration and environment
     */
    public function checkEnvironment() {
        echo "<h2>🔍 Environment Check</h2>";
        
        // PHP Version
        $phpVersion = phpversion();
        echo "<p><strong>PHP Version:</strong> $phpVersion</p>";
        
        // Required extensions
        $requiredExtensions = ['mysqli', 'curl', 'json', 'snmp', 'redis'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                echo "<p>✅ $ext extension: Loaded</p>";
            } else {
                echo "<p>❌ $ext extension: Missing</p>";
                $this->issues[] = "Missing required extension: $ext";
            }
        }
        
        // Memory limits
        $memoryLimit = ini_get('memory_limit');
        $maxExecutionTime = ini_get('max_execution_time');
        echo "<p><strong>Memory Limit:</strong> $memoryLimit</p>";
        echo "<p><strong>Max Execution Time:</strong> $maxExecutionTime seconds</p>";
        
        if ($maxExecutionTime < 300) {
            $this->optimizations[] = "Consider increasing max_execution_time for long-running operations";
        }
    }
    
    /**
     * Check database connectivity and performance
     */
    public function checkDatabase() {
        echo "<h2>🗄️ Database Check</h2>";
        
        try {
            // Check if config file exists
            if (file_exists('config.php')) {
                include 'config.php';
                
                if (isset($db_host) && isset($db_user) && isset($db_pass) && isset($db_name)) {
                    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
                    
                    if ($mysqli->connect_error) {
                        echo "<p>❌ Database connection failed: " . $mysqli->connect_error . "</p>";
                        $this->issues[] = "Database connection failed";
                    } else {
                        echo "<p>✅ Database connection successful</p>";
                        
                        // Check database performance
                        $result = $mysqli->query("SHOW VARIABLES LIKE 'max_connections'");
                        if ($result) {
                            $row = $result->fetch_assoc();
                            echo "<p><strong>Max Connections:</strong> " . $row['Value'] . "</p>";
                        }
                        
                        // Check for slow queries
                        $result = $mysqli->query("SHOW VARIABLES LIKE 'slow_query_log'");
                        if ($result) {
                            $row = $result->fetch_assoc();
                            if ($row['Value'] == 'OFF') {
                                $this->optimizations[] = "Enable slow query log for performance monitoring";
                            }
                        }
                        
                        $mysqli->close();
                    }
                } else {
                    echo "<p>❌ Database configuration incomplete</p>";
                    $this->issues[] = "Database configuration missing";
                }
            } else {
                echo "<p>❌ Config file not found</p>";
                $this->issues[] = "Config file missing";
            }
        } catch (Exception $e) {
            echo "<p>❌ Database check error: " . $e->getMessage() . "</p>";
            $this->issues[] = "Database check failed: " . $e->getMessage();
        }
    }
    
    /**
     * Check file system and permissions
     */
    public function checkFileSystem() {
        echo "<h2>📁 File System Check</h2>";
        
        $directories = ['logs', 'modules', 'assets', 'partials'];
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                if (is_writable($dir)) {
                    echo "<p>✅ Directory $dir: Exists and writable</p>";
                } else {
                    echo "<p>⚠️ Directory $dir: Exists but not writable</p>";
                    $this->issues[] = "Directory $dir not writable";
                }
            } else {
                echo "<p>❌ Directory $dir: Missing</p>";
                $this->issues[] = "Directory $dir missing";
            }
        }
        
        // Check log file permissions
        if (file_exists('logs/debug_optimization.log')) {
            if (is_writable('logs/debug_optimization.log')) {
                echo "<p>✅ Log file: Writable</p>";
            } else {
                echo "<p>❌ Log file: Not writable</p>";
                $this->issues[] = "Log file not writable";
            }
        }
    }
    
    /**
     * Check Redis connectivity
     */
    public function checkRedis() {
        echo "<h2>🔴 Redis Check</h2>";
        
        if (extension_loaded('redis')) {
            try {
                $redis = new Redis();
                $redis->connect('127.0.0.1', 6379);
                
                if ($redis->ping()) {
                    echo "<p>✅ Redis connection successful</p>";
                    
                    // Check Redis memory usage
                    $info = $redis->info('memory');
                    echo "<p><strong>Redis Memory Usage:</strong> " . number_format($info['used_memory_human']) . "</p>";
                    
                    // Check Redis keys
                    $keys = $redis->keys('*');
                    echo "<p><strong>Total Keys:</strong> " . count($keys) . "</p>";
                    
                    if (count($keys) > 10000) {
                        $this->optimizations[] = "Consider Redis key expiration for better memory management";
                    }
                    
                } else {
                    echo "<p>❌ Redis ping failed</p>";
                    $this->issues[] = "Redis ping failed";
                }
                
                $redis->close();
            } catch (Exception $e) {
                echo "<p>❌ Redis connection failed: " . $e->getMessage() . "</p>";
                $this->issues[] = "Redis connection failed";
            }
        } else {
            echo "<p>❌ Redis extension not loaded</p>";
            $this->issues[] = "Redis extension missing";
        }
    }
    
    /**
     * Check SNMP functionality
     */
    public function checkSNMP() {
        echo "<h2>📡 SNMP Check</h2>";
        
        if (extension_loaded('snmp')) {
            echo "<p>✅ SNMP extension loaded</p>";
            
            // Check SNMP version support
            $snmpVersions = ['1', '2c', '3'];
            foreach ($snmpVersions as $version) {
                if (function_exists("snmp2_get")) {
                    echo "<p>✅ SNMP v$version support available</p>";
                }
            }
        } else {
            echo "<p>❌ SNMP extension not loaded</p>";
            $this->issues[] = "SNMP extension missing";
        }
    }
    
    /**
     * Performance analysis
     */
    public function analyzePerformance() {
        echo "<h2>⚡ Performance Analysis</h2>";
        
        $endTime = microtime(true);
        $executionTime = $endTime - $this->startTime;
        $memoryUsed = memory_get_usage() - $this->memoryStart;
        $peakMemory = memory_get_peak_usage(true);
        
        echo "<p><strong>Execution Time:</strong> " . number_format($executionTime, 4) . " seconds</p>";
        echo "<p><strong>Memory Used:</strong> " . number_format($memoryUsed) . " bytes</p>";
        echo "<p><strong>Peak Memory:</strong> " . number_format($peakMemory) . " bytes</p>";
        
        if ($executionTime > 5) {
            $this->optimizations[] = "Script execution time is high, consider optimizing database queries";
        }
        
        if ($peakMemory > 50 * 1024 * 1024) { // 50MB
            $this->optimizations[] = "High memory usage detected, consider implementing memory management";
        }
    }
    
    /**
     * Generate optimization recommendations
     */
    public function generateRecommendations() {
        echo "<h2>🚀 Optimization Recommendations</h2>";
        
        if (empty($this->optimizations)) {
            echo "<p>✅ No optimization recommendations at this time.</p>";
        } else {
            echo "<ul>";
            foreach ($this->optimizations as $optimization) {
                echo "<li>💡 $optimization</li>";
            }
            echo "</ul>";
        }
    }
    
    /**
     * Generate issue report
     */
    public function generateIssueReport() {
        echo "<h2>⚠️ Issues Found</h2>";
        
        if (empty($this->issues)) {
            echo "<p>✅ No critical issues found.</p>";
        } else {
            echo "<ul>";
            foreach ($this->issues as $issue) {
                echo "<li>❌ $issue</li>";
            }
            echo "</ul>";
        }
    }
    
    /**
     * Run all checks
     */
    public function runAllChecks() {
        echo "<h1>🔧 System Debug & Optimization Report</h1>";
        echo "<p><em>Generated on: " . date('Y-m-d H:i:s') . "</em></p>";
        
        $this->checkEnvironment();
        $this->checkDatabase();
        $this->checkFileSystem();
        $this->checkRedis();
        $this->checkSNMP();
        $this->analyzePerformance();
        $this->generateIssueReport();
        $this->generateRecommendations();
        
        echo "<h2>📊 Summary</h2>";
        echo "<p><strong>Total Issues:</strong> " . count($this->issues) . "</p>";
        echo "<p><strong>Optimization Suggestions:</strong> " . count($this->optimizations) . "</p>";
    }
}

// Create logs directory if it doesn't exist
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

// Run the debugger
$debugger = new SystemDebugger();
$debugger->runAllChecks();
?>