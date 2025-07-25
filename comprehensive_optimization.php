<?php
/**
 * Comprehensive Optimization Script
 * Runs all debugging and optimization tools for the network monitoring system
 */

// Include all optimization tools
require_once 'debug_optimization_tool.php';
require_once 'performance_optimizer.php';
require_once 'error_monitor.php';
require_once 'system_health_checker.php';

class ComprehensiveOptimizer {
    private $startTime;
    private $results = [];
    private $summary = [];
    
    public function __construct() {
        $this->startTime = microtime(true);
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Comprehensive System Optimization Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
                .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
                .critical { border-left: 5px solid #dc3545; background-color: #f8d7da; }
                .warning { border-left: 5px solid #ffc107; background-color: #fff3cd; }
                .success { border-left: 5px solid #28a745; background-color: #d4edda; }
                .info { border-left: 5px solid #17a2b8; background-color: #d1ecf1; }
                h1, h2 { color: #333; }
                .progress { width: 100%; background-color: #f0f0f0; border-radius: 5px; margin: 10px 0; }
                .progress-bar { height: 20px; background-color: #007bff; border-radius: 5px; transition: width 0.3s; }
                .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
                .stat-card { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; }
                .stat-value { font-size: 24px; font-weight: bold; color: #007bff; }
                .stat-label { color: #666; margin-top: 5px; }
                pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
                .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
                .btn:hover { background: #0056b3; }
            </style>
        </head>
        <body>
        <div class='container'>";
    }
    
    /**
     * Run comprehensive optimization
     */
    public function runComprehensiveOptimization() {
        echo "<h1>🚀 Comprehensive System Optimization</h1>";
        echo "<p><em>Started at: " . date('Y-m-d H:i:s') . "</em></p>";
        
        $this->showProgress(0, "Initializing optimization process...");
        
        // Step 1: System Health Check
        $this->showProgress(10, "Running system health check...");
        $this->runHealthCheck();
        
        // Step 2: Debug Analysis
        $this->showProgress(30, "Running debug analysis...");
        $this->runDebugAnalysis();
        
        // Step 3: Performance Optimization
        $this->showProgress(50, "Running performance optimization...");
        $this->runPerformanceOptimization();
        
        // Step 4: Error Monitoring
        $this->showProgress(70, "Setting up error monitoring...");
        $this->setupErrorMonitoring();
        
        // Step 5: Database Optimization
        $this->showProgress(85, "Optimizing database...");
        $this->optimizeDatabase();
        
        // Step 6: Final Analysis
        $this->showProgress(95, "Generating final report...");
        $this->generateFinalReport();
        
        $this->showProgress(100, "Optimization completed!");
        
        $this->displayResults();
    }
    
    /**
     * Run system health check
     */
    private function runHealthCheck() {
        echo "<div class='section info'>";
        echo "<h2>🏥 System Health Check</h2>";
        
        ob_start();
        $healthChecker = new SystemHealthChecker();
        $healthChecker->generateRecommendations();
        $healthReport = $healthChecker->generateHealthReport();
        $healthOutput = ob_get_clean();
        
        echo $healthOutput;
        
        $this->results['health_check'] = $healthReport;
        $this->summary['health_status'] = $healthReport['overall_status'];
        $this->summary['critical_issues'] = count($healthReport['critical_issues']);
        $this->summary['warnings'] = count($healthReport['warnings']);
        
        echo "</div>";
    }
    
    /**
     * Run debug analysis
     */
    private function runDebugAnalysis() {
        echo "<div class='section info'>";
        echo "<h2>🔍 Debug Analysis</h2>";
        
        ob_start();
        $debugger = new SystemDebugger();
        $debugOutput = ob_get_clean();
        
        echo $debugOutput;
        
        $this->results['debug_analysis'] = 'completed';
        echo "</div>";
    }
    
    /**
     * Run performance optimization
     */
    private function runPerformanceOptimization() {
        echo "<div class='section info'>";
        echo "<h2>⚡ Performance Optimization</h2>";
        
        ob_start();
        try {
            $optimizer = new PerformanceOptimizer();
            $optimizer->runAllOptimizations();
        } catch (Exception $e) {
            echo "Performance optimization error: " . $e->getMessage();
        }
        $optimizationOutput = ob_get_clean();
        
        echo "<pre>" . htmlspecialchars($optimizationOutput) . "</pre>";
        
        $this->results['performance_optimization'] = 'completed';
        echo "</div>";
    }
    
    /**
     * Setup error monitoring
     */
    private function setupErrorMonitoring() {
        echo "<div class='section info'>";
        echo "<h2>📊 Error Monitoring Setup</h2>";
        
        try {
            $errorMonitor = new ErrorMonitor();
            $errorReport = $errorMonitor->generateErrorReport();
            $logStats = $errorMonitor->getLogStatistics();
            
            echo "<p>✅ Error monitoring system initialized</p>";
            echo "<p><strong>Error Count:</strong> " . $errorReport['error_count'] . "</p>";
            echo "<p><strong>Warning Count:</strong> " . $errorReport['warning_count'] . "</p>";
            echo "<p><strong>Memory Usage:</strong> " . number_format($errorReport['memory_usage'] / 1024 / 1024, 2) . " MB</p>";
            
            $this->results['error_monitoring'] = $errorReport;
            $this->summary['errors'] = $errorReport['error_count'];
            $this->summary['warnings'] = $errorReport['warning_count'];
            
        } catch (Exception $e) {
            echo "<p class='critical'>❌ Error monitoring setup failed: " . $e->getMessage() . "</p>";
        }
        
        echo "</div>";
    }
    
    /**
     * Optimize database
     */
    private function optimizeDatabase() {
        echo "<div class='section info'>";
        echo "<h2>🗄️ Database Optimization</h2>";
        
        try {
            if (file_exists('config.php')) {
                include 'config.php';
                
                if (isset($db_host) && isset($db_user) && isset($db_pass) && isset($db_name)) {
                    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
                    
                    if (!$mysqli->connect_error) {
                        // Optimize tables
                        $tables = ['devices', 'clients', 'networks', 'interfaces', 'snmp_data', 'monitoring_data'];
                        foreach ($tables as $table) {
                            $result = $mysqli->query("SHOW TABLES LIKE '$table'");
                            if ($result && $result->num_rows > 0) {
                                $mysqli->query("OPTIMIZE TABLE $table");
                                echo "<p>✅ Optimized table: $table</p>";
                            }
                        }
                        
                        // Create indexes
                        $this->createDatabaseIndexes($mysqli);
                        
                        $mysqli->close();
                        echo "<p>✅ Database optimization completed</p>";
                    } else {
                        echo "<p class='critical'>❌ Database connection failed</p>";
                    }
                } else {
                    echo "<p class='warning'>⚠️ Database configuration not found</p>";
                }
            } else {
                echo "<p class='warning'>⚠️ Config file not found</p>";
            }
        } catch (Exception $e) {
            echo "<p class='critical'>❌ Database optimization error: " . $e->getMessage() . "</p>";
        }
        
        echo "</div>";
    }
    
    /**
     * Create database indexes
     */
    private function createDatabaseIndexes($mysqli) {
        $indexes = [
            'devices' => ['status', 'device_type', 'location'],
            'interfaces' => ['device_id', 'status', 'interface_type'],
            'snmp_data' => ['device_id', 'timestamp', 'oid'],
            'monitoring_data' => ['device_id', 'timestamp', 'metric_name']
        ];
        
        foreach ($indexes as $table => $columns) {
            $result = $mysqli->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                foreach ($columns as $column) {
                    $indexName = "idx_{$table}_{$column}";
                    try {
                        $mysqli->query("CREATE INDEX $indexName ON $table ($column)");
                        echo "<p>✅ Created index: $indexName</p>";
                    } catch (Exception $e) {
                        // Index might already exist
                        echo "<p>ℹ️ Index $indexName might already exist</p>";
                    }
                }
            }
        }
    }
    
    /**
     * Generate final report
     */
    private function generateFinalReport() {
        $endTime = microtime(true);
        $executionTime = $endTime - $this->startTime;
        
        $this->summary['execution_time'] = round($executionTime, 2);
        $this->summary['memory_peak'] = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        $this->summary['optimization_status'] = 'completed';
        
        echo "<div class='section success'>";
        echo "<h2>📋 Final Report</h2>";
        echo "<p><strong>Total Execution Time:</strong> " . $this->summary['execution_time'] . " seconds</p>";
        echo "<p><strong>Peak Memory Usage:</strong> " . $this->summary['memory_peak'] . " MB</p>";
        echo "<p><strong>Overall Status:</strong> " . strtoupper($this->summary['health_status']) . "</p>";
        echo "</div>";
    }
    
    /**
     * Display results
     */
    private function displayResults() {
        echo "<div class='section'>";
        echo "<h2>📊 Optimization Summary</h2>";
        
        echo "<div class='stats'>";
        echo "<div class='stat-card'>";
        echo "<div class='stat-value'>" . $this->summary['execution_time'] . "s</div>";
        echo "<div class='stat-label'>Execution Time</div>";
        echo "</div>";
        
        echo "<div class='stat-card'>";
        echo "<div class='stat-value'>" . $this->summary['memory_peak'] . "MB</div>";
        echo "<div class='stat-label'>Peak Memory</div>";
        echo "</div>";
        
        echo "<div class='stat-card'>";
        echo "<div class='stat-value'>" . $this->summary['critical_issues'] . "</div>";
        echo "<div class='stat-label'>Critical Issues</div>";
        echo "</div>";
        
        echo "<div class='stat-card'>";
        echo "<div class='stat-value'>" . $this->summary['warnings'] . "</div>";
        echo "<div class='stat-label'>Warnings</div>";
        echo "</div>";
        echo "</div>";
        
        echo "<h3>🔧 Recommendations</h3>";
        echo "<ul>";
        
        if ($this->summary['critical_issues'] > 0) {
            echo "<li class='critical'>Address critical issues immediately</li>";
        }
        
        if ($this->summary['warnings'] > 0) {
            echo "<li class='warning'>Monitor and resolve warnings</li>";
        }
        
        if ($this->summary['execution_time'] > 30) {
            echo "<li class='warning'>Consider optimizing slow operations</li>";
        }
        
        if ($this->summary['memory_peak'] > 100) {
            echo "<li class='warning'>Monitor memory usage and optimize if needed</li>";
        }
        
        echo "<li class='success'>Set up regular maintenance schedule</li>";
        echo "<li class='success'>Monitor system performance regularly</li>";
        echo "<li class='success'>Keep logs clean and rotated</li>";
        echo "</ul>";
        
        echo "<h3>📈 Next Steps</h3>";
        echo "<div class='btn-group'>";
        echo "<a href='debug_optimization_tool.php' class='btn'>Run Debug Tool</a>";
        echo "<a href='performance_optimizer.php' class='btn'>Run Performance Optimizer</a>";
        echo "<a href='system_health_checker.php' class='btn'>Run Health Check</a>";
        echo "<a href='error_monitor.php' class='btn'>View Error Logs</a>";
        echo "</div>";
        
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2>📝 Detailed Results</h2>";
        echo "<pre>" . json_encode($this->results, JSON_PRETTY_PRINT) . "</pre>";
        echo "</div>";
        
        echo "</div></body></html>";
    }
    
    /**
     * Show progress
     */
    private function showProgress($percentage, $message) {
        echo "<div class='progress'>";
        echo "<div class='progress-bar' style='width: {$percentage}%'></div>";
        echo "</div>";
        echo "<p><strong>{$percentage}%</strong> - $message</p>";
        ob_flush();
        flush();
    }
}

// Run comprehensive optimization
$comprehensiveOptimizer = new ComprehensiveOptimizer();
$comprehensiveOptimizer->runComprehensiveOptimization();
?>