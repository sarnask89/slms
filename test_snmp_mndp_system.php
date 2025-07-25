<?php
require_once __DIR__ . '/config.php';
$pdo = get_pdo();

function check_file($path) {
    return file_exists($path) && is_readable($path);
}

function check_admin_menu_links() {
    $menu = file_get_contents('admin_menu.php');
    $checks = [
        'network_monitoring_enhanced.php' => strpos($menu, 'network_monitoring_enhanced.php') !== false,
        'discover_snmp_mndp.php' => strpos($menu, 'discover_snmp_mndp.php') !== false,
        'mndp_monitor.php' => strpos($menu, 'mndp_monitor.php') !== false,
    ];
    return $checks;
}

function check_table($pdo, $table) {
    try {
        $pdo->query("SELECT 1 FROM $table LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function count_table($pdo, $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

$results = [];
$results['modules'] = [
    'network_monitoring_enhanced.php' => check_file('modules/network_monitoring_enhanced.php'),
    'discover_snmp_mndp.php' => check_file('modules/discover_snmp_mndp.php'),
    'mndp_monitor.php' => check_file('modules/mndp_monitor.php'),
];
$results['admin_menu'] = check_admin_menu_links();
$results['db_tables'] = [
    'discovered_devices' => check_table($pdo, 'discovered_devices'),
    'interface_stats' => check_table($pdo, 'interface_stats'),
];
$results['db_counts'] = [
    'discovered_devices' => count_table($pdo, 'discovered_devices'),
    'interface_stats' => count_table($pdo, 'interface_stats'),
];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SNMP/MNDP System Test</title>
    <style>body{font-family:sans-serif;} table{border-collapse:collapse;} td,th{border:1px solid #ccc;padding:4px 8px;}</style>
</head>
<body>
<h2>SNMP/MNDP System Test Results</h2>
<table>
<tr><th>Check</th><th>Status</th><th>Details</th></tr>
<tr><td>Module: network_monitoring_enhanced.php</td><td><?= $results['modules']['network_monitoring_enhanced.php'] ? 'OK' : 'Missing' ?></td><td></td></tr>
<tr><td>Module: discover_snmp_mndp.php</td><td><?= $results['modules']['discover_snmp_mndp.php'] ? 'OK' : 'Missing' ?></td><td></td></tr>
<tr><td>Module: mndp_monitor.php</td><td><?= $results['modules']['mndp_monitor.php'] ? 'OK' : 'Missing' ?></td><td></td></tr>
<tr><td>Admin menu: SNMP Monitoring</td><td><?= $results['admin_menu']['network_monitoring_enhanced.php'] ? 'OK' : 'Missing' ?></td><td></td></tr>
<tr><td>Admin menu: SNMP/MNDP Discovery</td><td><?= $results['admin_menu']['discover_snmp_mndp.php'] ? 'OK' : 'Missing' ?></td><td></td></tr>
<tr><td>Admin menu: MNDP Monitor</td><td><?= $results['admin_menu']['mndp_monitor.php'] ? 'OK' : 'Missing' ?></td><td></td></tr>
<tr><td>DB Table: discovered_devices</td><td><?= $results['db_tables']['discovered_devices'] ? 'OK' : 'Missing' ?></td><td>Rows: <?= $results['db_counts']['discovered_devices'] !== false ? $results['db_counts']['discovered_devices'] : 'N/A' ?></td></tr>
<tr><td>DB Table: interface_stats</td><td><?= $results['db_tables']['interface_stats'] ? 'OK' : 'Missing' ?></td><td>Rows: <?= $results['db_counts']['interface_stats'] !== false ? $results['db_counts']['interface_stats'] : 'N/A' ?></td></tr>
</table>
</body>
</html> 