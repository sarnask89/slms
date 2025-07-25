<?php
header('Content-Type: text/plain');

echo "=== PHP Debug Information ===\n\n";

// Basic PHP Info
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Server Protocol: " . $_SERVER['SERVER_PROTOCOL'] . "\n";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";

// PHP Configuration
echo "=== PHP Configuration ===\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n\n";

// Extensions
echo "=== Loaded Extensions ===\n";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo $ext . "\n";
}

// Server Variables
echo "\n=== Server Variables ===\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";

// Test File Operations
echo "\n=== File Operations ===\n";
echo "Current working directory: " . getcwd() . "\n";
echo "Can write to current directory: " . (is_writable('.') ? 'Yes' : 'No') . "\n";
?> 