<?php
/**
 * Performance Benchmark Script for ISP Management System
 * Part of the "Read, Run, Debug, Improve, Repeat" cycle
 */

class PerformanceBenchmark {
    private $benchmarks = [];
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
        echo "⚡ Starting Performance Benchmark...\n";
        echo "=====================================\n\n";
    }
    
    public function runAllBenchmarks() {
        $this->benchmarkDatabaseQueries();
        $this->benchmarkAPICalls();
        $this->benchmarkFileOperations();
        $this->benchmarkMemoryUsage();
        $this->benchmarkCPUUsage();
        $this->benchmarkNetworkLatency();
        
        $this->generateBenchmarkReport();
    }
    
    public function benchmarkDatabaseQueries() {
        echo "📊 Benchmarking Database Queries...\n";
        
        try {
            if (file_exists('config.php')) {
                require_once 'config.php';
                
                if (isset($db_host) && isset($db_user) && isset($db_pass) && isset($db_name)) {
                    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
                    
                    if (!$mysqli->connect_error) {
                        // Test simple query
                        $start = microtime(true);
                        $result = $mysqli->query("SELECT 1");
                        $duration = (microtime(true) - $start) * 1000;
                        $this->addBenchmark('db_simple_query', $duration, 'Simple SELECT query');
                        echo "  Simple query: {$duration}ms\n";
                        
                        // Test table count query
                        $start = microtime(true);
                        $result = $mysqli->query("SELECT COUNT(*) as count FROM information_schema.tables");
                        $duration = (microtime(true) - $start) * 1000;
                        $this->addBenchmark('db_count_query', $duration, 'COUNT query');
                        echo "  Count query: {$duration}ms\n";
                        
                        // Test complex query (if tables exist)
                        $start = microtime(true);
                        $result = $mysqli->query("SHOW TABLES");
                        $duration = (microtime(true) - $start) * 1000;
                        $this->addBenchmark('db_show_tables', $duration, 'SHOW TABLES query');
                        echo "  Show tables: {$duration}ms\n";
                        
                        $mysqli->close();
                    } else {
                        echo "  ❌ Database connection failed\n";
                    }
                } else {
                    echo "  ❌ Database configuration missing\n";
                }
            } else {
                echo "  ❌ Config file not found\n";
            }
        } catch (Exception $e) {
            echo "  ❌ Database benchmark error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    public function benchmarkAPICalls() {
        echo "🌐 Benchmarking API Calls...\n";
        
        $endpoints = [
            'health' => '/health.php',
            'system_status' => '/system_health_checker.php',
            'debug' => '/debug_system.php'
        ];
        
        foreach ($endpoints as $name => $endpoint) {
            $duration = $this->benchmarkEndpoint($endpoint);
            if ($duration !== false) {
                $this->addBenchmark("api_$name", $duration, "API endpoint: $endpoint");
                echo "  $name: {$duration}ms\n";
            } else {
                echo "  $name: ❌ Failed\n";
            }
        }
        
        echo "\n";
    }
    
    public function benchmarkFileOperations() {
        echo "📁 Benchmarking File Operations...\n";
        
        // Test file read
        $start = microtime(true);
        $content = file_get_contents('config.php');
        $duration = (microtime(true) - $start) * 1000;
        $this->addBenchmark('file_read', $duration, 'File read operation');
        echo "  File read: {$duration}ms\n";
        
        // Test file write
        $testFile = 'cache/benchmark_test.txt';
        $start = microtime(true);
        file_put_contents($testFile, 'Benchmark test content');
        $duration = (microtime(true) - $start) * 1000;
        $this->addBenchmark('file_write', $duration, 'File write operation');
        echo "  File write: {$duration}ms\n";
        
        // Test file delete
        $start = microtime(true);
        unlink($testFile);
        $duration = (microtime(true) - $start) * 1000;
        $this->addBenchmark('file_delete', $duration, 'File delete operation');
        echo "  File delete: {$duration}ms\n";
        
        echo "\n";
    }
    
    public function benchmarkMemoryUsage() {
        echo "🧠 Benchmarking Memory Usage...\n";
        
        $initialMemory = memory_get_usage(true);
        
        // Test memory allocation
        $start = microtime(true);
        $array = [];
        for ($i = 0; $i < 10000; $i++) {
            $array[] = "test_data_$i";
        }
        $duration = (microtime(true) - $start) * 1000;
        
        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024; // MB
        
        $this->addBenchmark('memory_allocation', $duration, "Memory allocation: {$memoryUsed}MB");
        echo "  Memory allocation: {$duration}ms ({$memoryUsed}MB)\n";
        
        // Test memory peak
        $peakMemory = memory_get_peak_usage(true) / 1024 / 1024; // MB
        $this->addBenchmark('memory_peak', $peakMemory, "Peak memory usage: {$peakMemory}MB");
        echo "  Peak memory: {$peakMemory}MB\n";
        
        echo "\n";
    }
    
    public function benchmarkCPUUsage() {
        echo "🖥️  Benchmarking CPU Usage...\n";
        
        // Test CPU-intensive operation
        $start = microtime(true);
        $result = 0;
        for ($i = 0; $i < 1000000; $i++) {
            $result += sqrt($i);
        }
        $duration = (microtime(true) - $start) * 1000;
        
        $this->addBenchmark('cpu_intensive', $duration, 'CPU-intensive calculation');
        echo "  CPU calculation: {$duration}ms\n";
        
        // Test string operations
        $start = microtime(true);
        $string = '';
        for ($i = 0; $i < 10000; $i++) {
            $string .= "test_string_$i";
        }
        $duration = (microtime(true) - $start) * 1000;
        
        $this->addBenchmark('string_operations', $duration, 'String concatenation');
        echo "  String operations: {$duration}ms\n";
        
        echo "\n";
    }
    
    public function benchmarkNetworkLatency() {
        echo "🌍 Benchmarking Network Latency...\n";
        
        // Test localhost latency
        $start = microtime(true);
        $response = @file_get_contents('http://localhost:8080/');
        $duration = (microtime(true) - $start) * 1000;
        
        if ($response !== false) {
            $this->addBenchmark('network_localhost', $duration, 'Localhost response time');
            echo "  Localhost: {$duration}ms\n";
        } else {
            echo "  Localhost: ❌ Failed\n";
        }
        
        // Test DNS resolution
        $start = microtime(true);
        $ip = gethostbyname('google.com');
        $duration = (microtime(true) - $start) * 1000;
        
        if ($ip !== 'google.com') {
            $this->addBenchmark('dns_resolution', $duration, 'DNS resolution time');
            echo "  DNS resolution: {$duration}ms\n";
        } else {
            echo "  DNS resolution: ❌ Failed\n";
        }
        
        echo "\n";
    }
    
    private function benchmarkEndpoint($endpoint) {
        $url = "http://localhost:8080$endpoint";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET'
            ]
        ]);
        
        $start = microtime(true);
        $response = @file_get_contents($url, false, $context);
        $duration = (microtime(true) - $start) * 1000;
        
        return $response !== false ? $duration : false;
    }
    
    private function addBenchmark($name, $value, $description) {
        $this->benchmarks[$name] = [
            'value' => $value,
            'description' => $description,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function generateBenchmarkReport() {
        $endTime = microtime(true);
        $totalDuration = round($endTime - $this->startTime, 2);
        
        echo "=====================================\n";
        echo "⚡ Performance Benchmark Results\n";
        echo "=====================================\n";
        echo "Total Duration: {$totalDuration}s\n";
        echo "Benchmarks: " . count($this->benchmarks) . "\n\n";
        
        // Group benchmarks by category
        $categories = [
            'Database' => ['db_'],
            'API' => ['api_'],
            'File' => ['file_'],
            'Memory' => ['memory_'],
            'CPU' => ['cpu_'],
            'Network' => ['network_', 'dns_']
        ];
        
        foreach ($categories as $category => $prefixes) {
            echo "$category Benchmarks:\n";
            echo str_repeat('-', strlen($category) + 12) . "\n";
            
            foreach ($this->benchmarks as $name => $benchmark) {
                foreach ($prefixes as $prefix) {
                    if (strpos($name, $prefix) === 0) {
                        $value = $benchmark['value'];
                        $unit = $this->getUnit($name);
                        echo "  " . str_pad($name, 20) . ": " . str_pad(number_format($value, 2), 8) . " $unit\n";
                        break;
                    }
                }
            }
            echo "\n";
        }
        
        // Performance analysis
        $this->analyzePerformance();
        
        // Save detailed report
        $report = $this->generateDetailedReport();
        file_put_contents('performance_report.txt', $report);
        echo "📄 Detailed report saved to: performance_report.txt\n";
    }
    
    private function getUnit($benchmarkName) {
        if (strpos($benchmarkName, 'memory_') === 0) {
            return 'MB';
        } elseif (strpos($benchmarkName, 'dns_') === 0 || strpos($benchmarkName, 'network_') === 0) {
            return 'ms';
        } else {
            return 'ms';
        }
    }
    
    private function analyzePerformance() {
        echo "Performance Analysis:\n";
        echo "====================\n";
        
        // Find slowest operations
        $slowest = [];
        foreach ($this->benchmarks as $name => $benchmark) {
            if (strpos($name, 'memory_') === 0) continue; // Skip memory benchmarks
            $slowest[$name] = $benchmark['value'];
        }
        
        arsort($slowest);
        $topSlowest = array_slice($slowest, 0, 3, true);
        
        echo "Slowest Operations:\n";
        foreach ($topSlowest as $name => $value) {
            echo "  - $name: {$value}ms\n";
        }
        
        // Performance recommendations
        echo "\nRecommendations:\n";
        foreach ($topSlowest as $name => $value) {
            if ($value > 1000) {
                echo "  ⚠️  $name is very slow ({$value}ms) - Consider optimization\n";
            } elseif ($value > 500) {
                echo "  ⚠️  $name is slow ({$value}ms) - Monitor performance\n";
            }
        }
        
        echo "\n";
    }
    
    private function generateDetailedReport() {
        $report = "Performance Benchmark Report\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($this->benchmarks as $name => $benchmark) {
            $unit = $this->getUnit($name);
            $report .= "$name: {$benchmark['value']} $unit - {$benchmark['description']}\n";
        }
        
        return $report;
    }
}

// Run the benchmark
$benchmark = new PerformanceBenchmark();
$benchmark->runAllBenchmarks();
?> 