<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = get_pdo();
    echo "Database connection successful\n";
    
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
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?> 