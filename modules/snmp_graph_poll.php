<?php
require_once 'module_loader.php';

$pdo = get_pdo();
$oids = require __DIR__ . '/snmp_oid_helper.php';

// Get all devices and OIDs to poll (for demo: poll all devices and all OIDs)
$device_ips = $pdo->query("SELECT DISTINCT ip_address FROM skeleton_devices ORDER BY ip_address")->fetchAll(PDO::FETCH_COLUMN);

$all_oids = array_keys($oids);

$results = [];
foreach ($device_ips as $ip) {
    foreach ($all_oids as $oid) {
        $value = @snmpget($ip, 'public', $oid, 1000000, 1);
        if ($value !== false && stripos($value, 'No Such') === false) {
            // Clean up value (remove 'INTEGER: ', 'STRING: ', etc.)
            $value = trim(preg_replace('/^[A-Z]+: /', '', $value));
            $stmt = $pdo->prepare("INSERT INTO snmp_graph_data (device_ip, oid, value) VALUES (?, ?, ?)");
            $stmt->execute([$ip, $oid, $value]);
            $results[] = "Polled $ip $oid = $value";
        } else {
            $results[] = "Failed $ip $oid";
        }
    }
}
echo "<h3>SNMP Polling Results</h3>";
echo "<ul>";
foreach ($results as $line) {
    echo "<li>" . htmlspecialchars($line) . "</li>";
}
echo "</ul>";
echo '<p><a href="snmp_graph.php">Back to SNMP Graphing</a></p>'; 