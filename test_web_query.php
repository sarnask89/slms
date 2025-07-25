<?php
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain');

try {
    $pdo = get_pdo();
    echo "Database connection successful\n";
    echo "Database name: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n";
    
    // Test the exact query from edit_device.php
    $id = 513;
    $stmt = $pdo->prepare('SELECT * FROM devices WHERE id = ?');
    echo "Query prepared successfully\n";
    
    $stmt->execute([$id]);
    echo "Query executed successfully\n";
    
    $device = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Data fetched successfully\n";
    
    if ($device) {
        echo "Device found: " . $device['name'] . "\n";
        echo "All columns: " . implode(', ', array_keys($device)) . "\n";
    } else {
        echo "Device not found\n";
    }
    
    // Test if the name column exists
    $columns = $pdo->query("SHOW COLUMNS FROM devices")->fetchAll(PDO::FETCH_COLUMN);
    echo "Available columns: " . implode(', ', $columns) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 