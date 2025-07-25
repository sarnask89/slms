<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login
require_login();

$pageTitle = 'Network Monitoring Api';
ob_start();
?>

require_once __DIR__ . '/../config.php';
$pdo = get_pdo();
header('Content-Type: application/json');
$device_id = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
$iface = isset($_GET['iface']) ? $_GET['iface'] : '';
if (!$device_id || !$iface) {
    echo json_encode(['error' => 'Missing device_id or iface']);
    exit;
}
$stmt = $pdo->prepare("SELECT timestamp, rx_bytes, tx_bytes FROM interface_stats WHERE device_id = ? AND interface_name = ? ORDER BY timestamp ASC");
$stmt->execute([$device_id, $iface]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$timestamps = array_column($rows, 'timestamp');
$rx = array_column($rows, 'rx_bytes');
$tx = array_column($rows, 'tx_bytes');
echo json_encode([
    'timestamps' => $timestamps,
    'rx_bytes' => $rx,
    'tx_bytes' => $tx
]); 

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?>
