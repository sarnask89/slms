<?php
/**
 * System Health Checker
 * Comprehensive health monitoring for network monitoring system
 */

class SystemHealthChecker {
    private $healthStatus = [];
    private $criticalIssues = [];
    private $warnings = [];
    private $recommendations = [];
    
    public function __construct() {
        $this->runAllHealthChecks();
    }
    
    /**
     * Run all health checks
     */
    private function runAllHealthChecks() {
        $this->checkSystemResources();
        $this->checkDatabaseHealth();
        $this->checkNetworkServices();
        $this->checkApplicationHealth();
        $this->checkSecurityHealth();
        $this->checkPerformanceHealth();
    }
    
    /**
     * Check system resources
     */
    private function checkSystemResources() {
        $this->healthStatus['system_resources'] = [];
        
        // CPU Usage
        $cpuUsage = $this->getCPUUsage();
        $this->healthStatus['system_resources']['cpu'] = [
            'usage' => $cpuUsage,
            'status' => $cpuUsage > 90 ? 'critical' : ($cpuUsage > 70 ? 'warning' : 'healthy')
        ];
        
        if ($cpuUsage > 90) {
            $this->criticalIssues[] = "High CPU usage: {$cpuUsage}%";
        } elseif ($cpuUsage > 70) {
            $this->warnings[] = "Elevated CPU usage: {$cpuUsage}%";
        }
        
        // Memory Usage
        $memoryUsage = $this->getMemoryUsage();
        $this->healthStatus['system_resources']['memory'] = [
            'usage' => $memoryUsage,
            'status' => $memoryUsage > 90 ? 'critical' : ($memoryUsage > 80 ? 'warning' : 'healthy')
        ];
        
        if ($memoryUsage > 90) {
            $this->criticalIssues[] = "High memory usage: {$memoryUsage}%";
        } elseif ($memoryUsage > 80) {
            $this->warnings[] = "Elevated memory usage: {$memoryUsage}%";
        }
        
        // Disk Usage
        $diskUsage = $this->getDiskUsage();
        $this->healthStatus['system_resources']['disk'] = [
            'usage' => $diskUsage,
            'status' => $diskUsage > 95 ? 'critical' : ($diskUsage > 85 ? 'warning' : 'healthy')
        ];
        
        if ($diskUsage > 95) {
            $this->criticalIssues[] = "Critical disk usage: {$diskUsage}%";
        } elseif ($diskUsage > 85) {
            $this->warnings[] = "High disk usage: {$diskUsage}%";
        }
        
        // Load Average
        $loadAverage = $this->getLoadAverage();
        $this->healthStatus['system_resources']['load_average'] = [
            'value' => $loadAverage,
            'status' => $loadAverage > 5 ? 'critical' : ($loadAverage > 2 ? 'warning' : 'healthy')
        ];
        
        if ($loadAverage > 5) {
            $this->criticalIssues[] = "High system load: {$loadAverage}";
        } elseif ($loadAverage > 2) {
            $this->warnings[] = "Elevated system load: {$loadAverage}";
        }
    }
    
    /**
     * Check database health
     */
    private function checkDatabaseHealth() {
        $this->healthStatus['database'] = [];
        
        try {
            if (file_exists('config.php')) {
                include 'config.php';
                
                if (isset($db_host) && isset($db_user) && isset($db_pass) && isset($db_name)) {
                    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
                    
                    if ($mysqli->connect_error) {
                        $this->healthStatus['database']['connection'] = 'critical';
                        $this->criticalIssues[] = "Database connection failed: " . $mysqli->connect_error;
                    } else {
                        $this->healthStatus['database']['connection'] = 'healthy';
                        
                        // Check database performance
                        $result = $mysqli->query("SHOW STATUS LIKE 'Threads_connected'");
                        if ($result) {
                            $row = $result->fetch_assoc();
                            $connections = $row['Value'];
                            $this->healthStatus['database']['connections'] = $connections;
                            
                            if ($connections > 100) {
                                $this->warnings[] = "High database connections: {$connections}";
                            }
                        }
                        
                        // Check slow queries
                        $result = $mysqli->query("SHOW STATUS LIKE 'Slow_queries'");
                        if ($result) {
                            $row = $result->fetch_assoc();
                            $slowQueries = $row['Value'];
                            $this->healthStatus['database']['slow_queries'] = $slowQueries;
                            
                            if ($slowQueries > 10) {
                                $this->warnings[] = "Multiple slow queries detected: {$slowQueries}";
                            }
                        }
                        
                        $mysqli->close();
                    }
                } else {
                    $this->healthStatus['database']['connection'] = 'critical';
                    $this->criticalIssues[] = "Database configuration incomplete";
                }
            } else {
                $this->healthStatus['database']['connection'] = 'critical';
                $this->criticalIssues[] = "Database configuration file missing";
            }
        } catch (Exception $e) {
            $this->healthStatus['database']['connection'] = 'critical';
            $this->criticalIssues[] = "Database check error: " . $e->getMessage();
        }
    }
    
    /**
     * Check network services
     */
    private function checkNetworkServices() {
        $this->healthStatus['network_services'] = [];
        
        $services = [
            'redis' => ['host' => '127.0.0.1', 'port' => 6379],
            'mysql' => ['host' => '127.0.0.1', 'port' => 3306],
            'apache' => ['host' => '127.0.0.1', 'port' => 80],
            'nginx' => ['host' => '127.0.0.1', 'port' => 80]
        ];
        
        foreach ($services as $service => $config) {
            $status = $this->checkService($config['host'], $config['port']);
            $this->healthStatus['network_services'][$service] = $status;
            
            if ($status === 'down') {
                $this->criticalIssues[] = "Service {$service} is down";
            }
        }
    }
    
    /**
     * Check application health
     */
    private function checkApplicationHealth() {
        $this->healthStatus['application'] = [];
        
        // Check required directories
        $directories = ['logs', 'modules', 'assets', 'partials', 'cache'];
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                $this->healthStatus['application']['directories'][$dir] = 'missing';
                $this->warnings[] = "Required directory missing: {$dir}";
            } else {
                $this->healthStatus['application']['directories'][$dir] = 'exists';
            }
        }
        
        // Check log file sizes
        $logFiles = ['logs/error_monitor.log', 'logs/debug_optimization.log'];
        foreach ($logFiles as $logFile) {
            if (file_exists($logFile)) {
                $size = filesize($logFile);
                $sizeMB = round($size / 1024 / 1024, 2);
                $this->healthStatus['application']['log_files'][$logFile] = $sizeMB;
                
                if ($sizeMB > 100) {
                    $this->warnings[] = "Large log file: {$logFile} ({$sizeMB}MB)";
                }
            }
        }
        
        // Check PHP configuration
        $this->healthStatus['application']['php'] = [
            'version' => phpversion(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'error_reporting' => ini_get('error_reporting')
        ];
    }
    
    /**
     * Check security health
     */
    private function checkSecurityHealth() {
        $this->healthStatus['security'] = [];
        
        // Check file permissions
        $criticalFiles = ['config.php', 'index.php'];
        foreach ($criticalFiles as $file) {
            if (file_exists($file)) {
                $perms = fileperms($file);
                $perms = substr(sprintf('%o', $perms), -4);
                $this->healthStatus['security']['file_permissions'][$file] = $perms;
                
                if ($perms != '0644' && $perms != '0600') {
                    $this->warnings[] = "Insecure file permissions: {$file} ({$perms})";
                }
            }
        }
        
        // Check for exposed sensitive files
        $sensitiveFiles = ['.env', 'config.php', 'database.php'];
        foreach ($sensitiveFiles as $file) {
            if (file_exists($file) && $this->isFileAccessible($file)) {
                $this->criticalIssues[] = "Sensitive file accessible via web: {$file}";
            }
        }
        
        // Check SSL/TLS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $this->healthStatus['security']['ssl'] = 'enabled';
        } else {
            $this->healthStatus['security']['ssl'] = 'disabled';
            $this->warnings[] = "SSL/TLS not enabled";
        }
    }
    
    /**
     * Check performance health
     */
    private function checkPerformanceHealth() {
        $this->healthStatus['performance'] = [];
        
        // Check response time
        $startTime = microtime(true);
        // Simulate a simple operation
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $this->healthStatus['performance']['response_time'] = round($responseTime, 2);
        
        if ($responseTime > 1000) {
            $this->warnings[] = "Slow response time: {$responseTime}ms";
        }
        
        // Check memory efficiency
        $memoryUsage = memory_get_usage(true);
        $this->healthStatus['performance']['memory_usage'] = round($memoryUsage / 1024 / 1024, 2);
        
        if ($memoryUsage > 50 * 1024 * 1024) { // 50MB
            $this->warnings[] = "High memory usage: " . round($memoryUsage / 1024 / 1024, 2) . "MB";
        }
    }
    
    /**
     * Get CPU usage
     */
    private function getCPUUsage() {
        $load = sys_getloadavg();
        return round($load[0] * 100 / 4, 2); // Assuming 4 cores
    }
    
    /**
     * Get memory usage
     */
    private function getMemoryUsage() {
        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        $memory_usage = $mem[2]/$mem[1]*100;
        return round($memory_usage, 2);
    }
    
    /**
     * Get disk usage
     */
    private function getDiskUsage() {
        $totalSpace = disk_total_space('.');
        $freeSpace = disk_free_space('.');
        $usedSpace = $totalSpace - $freeSpace;
        return round(($usedSpace / $totalSpace) * 100, 2);
    }
    
    /**
     * Get load average
     */
    private function getLoadAverage() {
        $load = sys_getloadavg();
        return $load[0];
    }
    
    /**
     * Check service status
     */
    private function checkService($host, $port) {
        $connection = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($connection) {
            fclose($connection);
            return 'up';
        }
        return 'down';
    }
    
    /**
     * Check if file is accessible via web
     */
    private function isFileAccessible($file) {
        if (!file_exists($file)) {
            return false;
        }
        
        $webRoot = $_SERVER['DOCUMENT_ROOT'] ?? '.';
        $filePath = realpath($file);
        $webRootPath = realpath($webRoot);
        
        return strpos($filePath, $webRootPath) === 0;
    }
    
    /**
     * Generate health report
     */
    public function generateHealthReport() {
        $overallStatus = 'healthy';
        
        if (!empty($this->criticalIssues)) {
            $overallStatus = 'critical';
        } elseif (!empty($this->warnings)) {
            $overallStatus = 'warning';
        }
        
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_status' => $overallStatus,
            'critical_issues' => $this->criticalIssues,
            'warnings' => $this->warnings,
            'recommendations' => $this->recommendations,
            'health_status' => $this->healthStatus
        ];
        
        return $report;
    }
    
    /**
     * Generate recommendations
     */
    public function generateRecommendations() {
        if (!empty($this->criticalIssues)) {
            $this->recommendations[] = "Address critical issues immediately to prevent system failure";
        }
        
        if (!empty($this->warnings)) {
            $this->recommendations[] = "Monitor warnings and address them before they become critical";
        }
        
        // System resource recommendations
        if (isset($this->healthStatus['system_resources']['cpu']['usage']) && 
            $this->healthStatus['system_resources']['cpu']['usage'] > 70) {
            $this->recommendations[] = "Consider CPU optimization or scaling";
        }
        
        if (isset($this->healthStatus['system_resources']['memory']['usage']) && 
            $this->healthStatus['system_resources']['memory']['usage'] > 80) {
            $this->recommendations[] = "Consider memory optimization or increasing RAM";
        }
        
        if (isset($this->healthStatus['system_resources']['disk']['usage']) && 
            $this->healthStatus['system_resources']['disk']['usage'] > 85) {
            $this->recommendations[] = "Consider disk cleanup or storage expansion";
        }
        
        // Database recommendations
        if (isset($this->healthStatus['database']['slow_queries']) && 
            $this->healthStatus['database']['slow_queries'] > 5) {
            $this->recommendations[] = "Optimize database queries and add indexes";
        }
        
        // Security recommendations
        if (isset($this->healthStatus['security']['ssl']) && 
            $this->healthStatus['security']['ssl'] === 'disabled') {
            $this->recommendations[] = "Enable SSL/TLS for secure communication";
        }
    }
    
    /**
     * Display health report
     */
    public function displayHealthReport() {
        $report = $this->generateHealthReport();
        
        echo "<h1>🏥 System Health Report</h1>";
        echo "<p><strong>Generated:</strong> " . $report['timestamp'] . "</p>";
        echo "<p><strong>Overall Status:</strong> <span style='color: " . 
             ($report['overall_status'] === 'healthy' ? 'green' : 
              ($report['overall_status'] === 'warning' ? 'orange' : 'red')) . 
             ";'>" . strtoupper($report['overall_status']) . "</span></p>";
        
        if (!empty($report['critical_issues'])) {
            echo "<h2>🚨 Critical Issues</h2><ul>";
            foreach ($report['critical_issues'] as $issue) {
                echo "<li>$issue</li>";
            }
            echo "</ul>";
        }
        
        if (!empty($report['warnings'])) {
            echo "<h2>⚠️ Warnings</h2><ul>";
            foreach ($report['warnings'] as $warning) {
                echo "<li>$warning</li>";
            }
            echo "</ul>";
        }
        
        if (!empty($report['recommendations'])) {
            echo "<h2>💡 Recommendations</h2><ul>";
            foreach ($report['recommendations'] as $rec) {
                echo "<li>$rec</li>";
            }
            echo "</ul>";
        }
        
        echo "<h2>📊 Detailed Status</h2>";
        echo "<pre>" . json_encode($report['health_status'], JSON_PRETTY_PRINT) . "</pre>";
    }
}

// Run health check
$healthChecker = new SystemHealthChecker();
$healthChecker->generateRecommendations();
$healthChecker->displayHealthReport();
?>