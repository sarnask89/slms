<?php
require_once __DIR__ . '/config.php';
$pdo = get_pdo();

$sql = "CREATE TABLE IF NOT EXISTS snmp_graph_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_ip VARCHAR(45) NOT NULL,
    oid VARCHAR(255) NOT NULL,
    value VARCHAR(255) NOT NULL,
    polled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(device_ip, oid, polled_at)
);";

try {
    $pdo->exec($sql);
    echo '<h3>snmp_graph_data table created or already exists.</h3>';
} catch (Exception $e) {
    echo '<h3 style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</h3>';
}

echo '<p><a href="admin_menu.php">Back to Admin Menu</a></p>'; 