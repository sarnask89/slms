<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = get_pdo();
    
    // Read and execute SQL file
    $sql = file_get_contents(__DIR__ . '/sql/create_skeleton_devices_table.sql');
    $pdo->exec($sql);
    
    echo "✅ Skeleton devices table created successfully!\n";
    
    // Add a test device
    $stmt = $pdo->prepare("
        INSERT INTO skeleton_devices 
        (name, type, ip_address, username, password, port, ssl)
        VALUES 
        ('Test Router', 'mikrotik', '192.168.1.1', 'admin', 'password', 443, 1)
        ON DUPLICATE KEY UPDATE id=id
    ");
    $stmt->execute();
    
    echo "✅ Test device added successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
