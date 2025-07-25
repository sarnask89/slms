<?php
require_once __DIR__ . '/config.php';
$pdo = get_pdo();
$results = [];

function create_table($pdo, $sql, $name) {
    try {
        $pdo->exec($sql);
        return "$name: OK";
    } catch (Exception $e) {
        return "$name: ERROR - " . $e->getMessage();
    }
}

$results[] = create_table($pdo, "CREATE TABLE IF NOT EXISTS interface_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    interface_name VARCHAR(64) NOT NULL,
    rx_bytes BIGINT UNSIGNED NOT NULL,
    tx_bytes BIGINT UNSIGNED NOT NULL,
    rx_packets BIGINT UNSIGNED DEFAULT 0,
    tx_packets BIGINT UNSIGNED DEFAULT 0,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX(device_id, interface_name, timestamp)
);", 'interface_stats');

$results[] = create_table($pdo, "CREATE TABLE IF NOT EXISTS network_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    interface_name VARCHAR(64) NOT NULL,
    alert_type VARCHAR(32) NOT NULL,
    details JSON,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX(device_id, interface_name, timestamp),
    INDEX(alert_type, timestamp)
);", 'network_alerts');

$results[] = create_table($pdo, "CREATE TABLE IF NOT EXISTS discovered_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    sys_name VARCHAR(255),
    sys_descr TEXT,
    method ENUM('SNMP','MNDP') NOT NULL,
    discovered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    imported BOOLEAN DEFAULT FALSE,
    UNIQUE KEY unique_ip_method (ip_address, method)
);", 'discovered_devices');

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create SNMP/MNDP Tables</title>
    <style>body{font-family:sans-serif;} table{border-collapse:collapse;} td,th{border:1px solid #ccc;padding:4px 8px;}</style>
</head>
<body>
<h2>Create SNMP/MNDP Tables</h2>
<ul>
<?php foreach ($results as $res): ?>
    <li><?= htmlspecialchars($res) ?></li>
<?php endforeach; ?>
</ul>
<p><a href="test_snmp_mndp_system.php">Re-run system test</a></p>
</body>
</html> 