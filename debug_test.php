<?php
/**
 * Comprehensive PHP Debug Test Script
 * Tests Xdebug configuration and debugging capabilities
 * 
 * @author sLMS Development Team
 * @version 1.0
 * @date 2024-12-19
 */

declare(strict_types=1);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PHP Debug Test Suite ===\n\n";

// Test 1: Xdebug Configuration
echo "1. Xdebug Configuration Test\n";
echo "============================\n";

if (extension_loaded('xdebug')) {
    echo "✅ Xdebug is loaded\n";
    echo "Version: " . phpversion('xdebug') . "\n";
    echo "Mode: " . ini_get('xdebug.mode') . "\n";
    echo "Client Host: " . ini_get('xdebug.client_host') . "\n";
    echo "Client Port: " . ini_get('xdebug.client_port') . "\n";
    echo "IDE Key: " . ini_get('xdebug.idekey') . "\n";
    echo "Start with Request: " . ini_get('xdebug.start_with_request') . "\n";
    echo "Log: " . ini_get('xdebug.log') . "\n";
} else {
    echo "❌ Xdebug is not loaded\n";
}

echo "\n";

// Test 2: PHP Environment
echo "2. PHP Environment Test\n";
echo "======================\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'CLI') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'CLI') . "\n";
echo "Current Working Directory: " . getcwd() . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";

echo "\n";

// Test 3: Variable Debugging
echo "3. Variable Debugging Test\n";
echo "==========================\n";

// Test variables for debugging
$test_var = "Hello Debug!";
$test_array = [
    'key1' => 'value1', 
    'key2' => 'value2',
    'nested' => [
        'level1' => 'data1',
        'level2' => 'data2'
    ]
];
$test_object = new stdClass();
$test_object->property = "test value";
$test_object->method = function() { return "closure result"; };

echo "Test variable: $test_var\n";
echo "Test array: " . print_r($test_array, true);
echo "Test object: " . print_r($test_object, true);

echo "\n";

// Test 4: Function Debugging
echo "4. Function Debugging Test\n";
echo "==========================\n";

/**
 * Test function for breakpoints
 * 
 * @param string $param Input parameter
 * @param array $options Additional options
 * @return array Function result
 */
function test_function(string $param, array $options = []): array
{
    $local_var = "Local variable";
    $local_array = ['local' => 'data'];
    
    echo "Function parameter: $param\n";
    echo "Local variable: $local_var\n";
    echo "Options: " . print_r($options, true);
    
    // This is a good place to set a breakpoint
    $result = [
        'input' => $param,
        'local_var' => $local_var,
        'local_array' => $local_array,
        'options' => $options,
        'timestamp' => time()
    ];
    
    return $result;
}

// Test function calls
$result1 = test_function("Test parameter 1");
$result2 = test_function("Test parameter 2", ['option1' => 'value1', 'option2' => 'value2']);

echo "Function result 1: " . print_r($result1, true);
echo "Function result 2: " . print_r($result2, true);

echo "\n";

// Test 5: Class Debugging
echo "5. Class Debugging Test\n";
echo "======================\n";

/**
 * Test class for debugging
 */
class DebugTestClass
{
    private string $private_property;
    protected string $protected_property;
    public string $public_property;
    
    public function __construct(string $value = "default")
    {
        $this->private_property = $value;
        $this->protected_property = $value . "_protected";
        $this->public_property = $value . "_public";
    }
    
    /**
     * Test method for debugging
     * 
     * @param string $param Method parameter
     * @return array Method result
     */
    public function testMethod(string $param): array
    {
        $local_var = "Method local variable";
        
        // Good breakpoint location
        $result = [
            'param' => $param,
            'local_var' => $local_var,
            'private_property' => $this->private_property,
            'protected_property' => $this->protected_property,
            'public_property' => $this->public_property
        ];
        
        return $result;
    }
    
    /**
     * Method with exception for testing
     * 
     * @param bool $throw_exception Whether to throw an exception
     * @return string Result
     * @throws Exception When requested
     */
    public function methodWithException(bool $throw_exception = false): string
    {
        if ($throw_exception) {
            throw new Exception("Test exception for debugging");
        }
        
        return "No exception thrown";
    }
}

// Test class instantiation and methods
$test_class = new DebugTestClass("test_value");
$class_result = $test_class->testMethod("class_parameter");

echo "Class test result: " . print_r($class_result, true);

// Test exception handling
try {
    $exception_result = $test_class->methodWithException(true);
} catch (Exception $e) {
    echo "Caught exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 6: Database Connection Test (if available)
echo "6. Database Connection Test\n";
echo "===========================\n";

if (file_exists('config.php')) {
    try {
        require_once 'config.php';
        if (function_exists('get_pdo')) {
            $pdo = get_pdo();
            $stmt = $pdo->query('SELECT 1 as test');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Database connection successful: " . print_r($result, true);
        } else {
            echo "⚠️  get_pdo() function not available\n";
        }
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️  config.php not found\n";
}

echo "\n";

// Test 7: Performance Test
echo "7. Performance Test\n";
echo "===================\n";

$start_time = microtime(true);
$start_memory = memory_get_usage();

// Simulate some work
for ($i = 0; $i < 1000; $i++) {
    $test_array[] = "item_$i";
}

$end_time = microtime(true);
$end_memory = memory_get_usage();

$execution_time = ($end_time - $start_time) * 1000; // milliseconds
$memory_used = $end_memory - $start_memory;

echo "Execution time: {$execution_time}ms\n";
echo "Memory used: {$memory_used} bytes\n";
echo "Array size: " . count($test_array) . " items\n";

echo "\n";

// Test 8: Breakpoint Instructions
echo "8. Debug Instructions\n";
echo "=====================\n";
echo "To test debugging:\n";
echo "1. Set breakpoints in this file (click in the gutter)\n";
echo "2. Start debugging session in VS Code (F5)\n";
echo "3. Select 'Listen for Xdebug'\n";
echo "4. Access this file via web server or run directly\n";
echo "5. Check debug console for variables\n";
echo "6. Use step over (F10), step into (F11), step out (Shift+F11)\n";

echo "\n";

// Test 9: Xdebug Info (if available)
echo "9. Xdebug Information\n";
echo "=====================\n";

if (extension_loaded('xdebug')) {
    // Use phpinfo() to get Xdebug information
    echo "Xdebug is loaded. Use phpinfo() for detailed information.\n";
    echo "Xdebug version: " . phpversion('xdebug') . "\n";
    echo "Xdebug mode: " . ini_get('xdebug.mode') . "\n";
} else {
    echo "Xdebug is not loaded\n";
}

echo "\n";

// Test 10: Final Status
echo "10. Final Status\n";
echo "================\n";

$status = [
    'xdebug_loaded' => extension_loaded('xdebug'),
    'xdebug_version' => phpversion('xdebug'),
    'xdebug_mode' => ini_get('xdebug.mode'),
    'php_version' => PHP_VERSION,
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'error_reporting' => ini_get('error_reporting'),
    'display_errors' => ini_get('display_errors')
];

echo "Final Status:\n";
foreach ($status as $key => $value) {
    echo "  $key: $value\n";
}

echo "\n=== Debug Test Complete ===\n";
echo "If you see this message, the script executed successfully.\n";
echo "Check the debug console in VS Code for detailed variable inspection.\n"; 