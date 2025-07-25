<?php
// Test Xdebug with web server
echo "<h1>Xdebug Web Test</h1>";
echo "<h2>Configuration</h2>";

if (extension_loaded('xdebug')) {
    echo "<p>✅ <strong>Xdebug is loaded</strong></p>";
    echo "<p><strong>Version:</strong> " . phpversion('xdebug') . "</p>";
    echo "<p><strong>Mode:</strong> " . ini_get('xdebug.mode') . "</p>";
    echo "<p><strong>Client Host:</strong> " . ini_get('xdebug.client_host') . "</p>";
    echo "<p><strong>Client Port:</strong> " . ini_get('xdebug.client_port') . "</p>";
    echo "<p><strong>IDE Key:</strong> " . ini_get('xdebug.idekey') . "</p>";
} else {
    echo "<p>❌ <strong>Xdebug is not loaded</strong></p>";
}

echo "<h2>Test Variables</h2>";
$test_var = "Hello Xdebug!";
$test_array = ['key1' => 'value1', 'key2' => 'value2'];
$test_object = new stdClass();
$test_object->property = "test value";

echo "<p>Test variable: $test_var</p>";
echo "<p>Test array: " . print_r($test_array, true) . "</p>";
echo "<p>Test object: " . print_r($test_object, true) . "</p>";

echo "<h2>Breakpoint Test</h2>";
echo "<p>You can set a breakpoint on the next line:</p>";
$breakpoint_test = "This line can have a breakpoint";
echo "<p>Breakpoint test: $breakpoint_test</p>";

echo "<h2>Function Test</h2>";
function test_function($param) {
    $local_var = "Local variable";
    echo "<p>Function parameter: $param</p>";
    echo "<p>Local variable: $local_var</p>";
    return "Function result";
}

$result = test_function("Test parameter");
echo "<p>Function result: $result</p>";

echo "<h2>Xdebug Info</h2>";
if (function_exists('xdebug_info')) {
    $info = xdebug_info();
    echo "<pre>";
    print_r($info);
    echo "</pre>";
}

echo "<h2>Debug Instructions</h2>";
echo "<p>To debug this page:</p>";
echo "<ol>";
echo "<li>Install the Xdebug extension in your IDE (VS Code, PHPStorm, etc.)</li>";
echo "<li>Configure your IDE to listen on port 9003</li>";
echo "<li>Set breakpoints in your code</li>";
echo "<li>Access this page through your web browser</li>";
echo "<li>The debugger should connect and stop at your breakpoints</li>";
echo "</ol>";

echo "<p><strong>Note:</strong> The debugger will try to connect to 10.0.222.223:9003</p>";
?> 