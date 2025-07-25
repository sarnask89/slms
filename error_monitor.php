<?php
/**
 * Error Monitor and Logging System
 * Provides comprehensive error tracking and monitoring
 */

class ErrorMonitor {
    private $logFile;
    private $errorCount = 0;
    private $warningCount = 0;
    private $criticalErrors = [];
    private $performanceIssues = [];
    
    public function __construct($logFile = 'logs/error_monitor.log') {
        $this->logFile = $logFile;
        $this->ensureLogDirectory();
        $this->setErrorHandlers();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Set up error handlers
     */
    private function setErrorHandlers() {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        $errorType = $this->getErrorType($errno);
        $message = "[$errorType] $errstr in $errfile on line $errline";
        
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                $this->logCritical($message);
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                $this->logWarning($message);
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $this->logInfo($message);
                break;
            default:
                $this->logError($message);
        }
        
        return true;
    }
    
    /**
     * Handle exceptions
     */
    public function handleException($exception) {
        $message = "[EXCEPTION] " . $exception->getMessage() . 
                   " in " . $exception->getFile() . 
                   " on line " . $exception->getLine() . 
                   "\nStack trace: " . $exception->getTraceAsString();
        
        $this->logCritical($message);
    }
    
    /**
     * Handle fatal errors
     */
    public function handleFatalError() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $message = "[FATAL ERROR] " . $error['message'] . 
                       " in " . $error['file'] . 
                       " on line " . $error['line'];
            
            $this->logCritical($message);
        }
    }
    
    /**
     * Get error type string
     */
    private function getErrorType($errno) {
        switch ($errno) {
            case E_ERROR: return 'E_ERROR';
            case E_WARNING: return 'E_WARNING';
            case E_PARSE: return 'E_PARSE';
            case E_NOTICE: return 'E_NOTICE';
            case E_CORE_ERROR: return 'E_CORE_ERROR';
            case E_CORE_WARNING: return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: return 'E_COMPILE_WARNING';
            case E_USER_ERROR: return 'E_USER_ERROR';
            case E_USER_WARNING: return 'E_USER_WARNING';
            case E_USER_NOTICE: return 'E_USER_NOTICE';
            case E_STRICT: return 'E_STRICT';
            case E_RECOVERABLE_ERROR: return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: return 'E_DEPRECATED';
            case E_USER_DEPRECATED: return 'E_USER_DEPRECATED';
            default: return 'UNKNOWN';
        }
    }
    
    /**
     * Log critical error
     */
    public function logCritical($message) {
        $this->log('CRITICAL', $message);
        $this->criticalErrors[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message
        ];
        $this->errorCount++;
        
        // Send alert for critical errors
        $this->sendAlert($message, 'CRITICAL');
    }
    
    /**
     * Log error
     */
    public function logError($message) {
        $this->log('ERROR', $message);
        $this->errorCount++;
    }
    
    /**
     * Log warning
     */
    public function logWarning($message) {
        $this->log('WARNING', $message);
        $this->warningCount++;
    }
    
    /**
     * Log info
     */
    public function logInfo($message) {
        $this->log('INFO', $message);
    }
    
    /**
     * Log debug
     */
    public function logDebug($message) {
        $this->log('DEBUG', $message);
    }
    
    /**
     * Write to log file
     */
    private function log($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Monitor database performance
     */
    public function monitorDatabasePerformance($query, $executionTime) {
        if ($executionTime > 5.0) {
            $message = "Slow database query detected: $query (${executionTime}s)";
            $this->logWarning($message);
            $this->performanceIssues[] = [
                'type' => 'slow_query',
                'query' => $query,
                'execution_time' => $executionTime,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Monitor memory usage
     */
    public function monitorMemoryUsage() {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        
        $usagePercentage = ($memoryUsage / $memoryLimitBytes) * 100;
        
        if ($usagePercentage > 80) {
            $message = "High memory usage: " . number_format($memoryUsage / 1024 / 1024, 2) . 
                      " MB (" . number_format($usagePercentage, 1) . "% of limit)";
            $this->logWarning($message);
        }
    }
    
    /**
     * Monitor disk space
     */
    public function monitorDiskSpace($path = '.') {
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercentage = ($usedSpace / $totalSpace) * 100;
        
        if ($usagePercentage > 90) {
            $message = "Low disk space: " . number_format($freeSpace / 1024 / 1024 / 1024, 2) . 
                      " GB free (" . number_format($usagePercentage, 1) . "% used)";
            $this->logWarning($message);
        }
    }
    
    /**
     * Monitor network connectivity
     */
    public function monitorNetworkConnectivity($hosts = ['8.8.8.8', '1.1.1.1']) {
        foreach ($hosts as $host) {
            $startTime = microtime(true);
            $connection = @fsockopen($host, 80, $errno, $errstr, 5);
            $endTime = microtime(true);
            
            if ($connection) {
                fclose($connection);
                $responseTime = ($endTime - $startTime) * 1000;
                
                if ($responseTime > 1000) { // 1 second
                    $message = "Slow network response to $host: " . number_format($responseTime, 2) . "ms";
                    $this->logWarning($message);
                }
            } else {
                $message = "Network connectivity issue: Cannot reach $host ($errstr)";
                $this->logError($message);
            }
        }
    }
    
    /**
     * Send alert
     */
    private function sendAlert($message, $level) {
        // Implement alert mechanism (email, SMS, webhook, etc.)
        $alertData = [
            'level' => $level,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'server' => $_SERVER['SERVER_NAME'] ?? 'unknown'
        ];
        
        // Log alert
        $this->log('ALERT', json_encode($alertData));
        
        // You can implement email, webhook, or other alert mechanisms here
        // Example: mail('admin@example.com', "System Alert: $level", $message);
    }
    
    /**
     * Convert memory limit string to bytes
     */
    private function convertToBytes($memoryLimit) {
        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int)substr($memoryLimit, 0, -1);
        
        switch ($unit) {
            case 'k': return $value * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'g': return $value * 1024 * 1024 * 1024;
            default: return $value;
        }
    }
    
    /**
     * Generate error report
     */
    public function generateErrorReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error_count' => $this->errorCount,
            'warning_count' => $this->warningCount,
            'critical_errors' => $this->criticalErrors,
            'performance_issues' => $this->performanceIssues,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
        
        return $report;
    }
    
    /**
     * Clean old log entries
     */
    public function cleanOldLogs($days = 30) {
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        $logContent = file_get_contents($this->logFile);
        $lines = explode(PHP_EOL, $logContent);
        $filteredLines = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                $logTime = strtotime($matches[1]);
                if ($logTime > $cutoffTime) {
                    $filteredLines[] = $line;
                }
            }
        }
        
        file_put_contents($this->logFile, implode(PHP_EOL, $filteredLines));
        $this->logInfo("Cleaned log entries older than $days days");
    }
    
    /**
     * Get log statistics
     */
    public function getLogStatistics() {
        $logContent = file_get_contents($this->logFile);
        $lines = explode(PHP_EOL, $logContent);
        
        $stats = [
            'total_entries' => count($lines),
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'info' => 0,
            'debug' => 0
        ];
        
        foreach ($lines as $line) {
            if (strpos($line, '[CRITICAL]') !== false) $stats['critical']++;
            elseif (strpos($line, '[ERROR]') !== false) $stats['error']++;
            elseif (strpos($line, '[WARNING]') !== false) $stats['warning']++;
            elseif (strpos($line, '[INFO]') !== false) $stats['info']++;
            elseif (strpos($line, '[DEBUG]') !== false) $stats['debug']++;
        }
        
        return $stats;
    }
}

// Initialize error monitor
$errorMonitor = new ErrorMonitor();

// Example usage
$errorMonitor->logInfo("Error monitoring system initialized");
$errorMonitor->monitorMemoryUsage();
$errorMonitor->monitorDiskSpace();
$errorMonitor->monitorNetworkConnectivity();

// Generate report
$report = $errorMonitor->generateErrorReport();
echo "Error Report: " . json_encode($report, JSON_PRETTY_PRINT) . "\n";

// Get statistics
$stats = $errorMonitor->getLogStatistics();
echo "Log Statistics: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n";
?>