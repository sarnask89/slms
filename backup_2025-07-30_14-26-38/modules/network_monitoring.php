<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mikrotik_api.php';

$pageTitle = 'Network Monitoring';
$pdo = get_pdo();
$errors = [];
$success = '';

// Manual polling action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['poll_interfaces'])) {
    try {
        $stmt = $pdo->query("SELECT id, name, ip_address, api_username, api_password FROM skeleton_devices WHERE api_username IS NOT NULL AND api_password IS NOT NULL");
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $polled = 0;
        foreach ($devices as $device) {
            try {
                $api = new MikroTikAPI($device['ip_address'], $device['api_username'], $device['api_password']);
                $interfaces = $api->restGet('/interface/print');
                foreach ($interfaces as $iface) {
                    $stats = $api->restGet('/interface/monitor-traffic', [
                        'interface' => $iface['name'],
                        'once' => true
                    ]);
                    if (isset($stats[0]['rx-bits-per-second']) && isset($stats[0]['tx-bits-per-second'])) {
                        $stmt2 = $pdo->prepare("INSERT INTO interface_stats (device_id, interface_name, rx_bytes, tx_bytes, timestamp) VALUES (?, ?, ?, ?, NOW())");
                        $stmt2->execute([
                            $device['id'],
                            $iface['name'],
                            $stats[0]['rx-bits-per-second'],
                            $stats[0]['tx-bits-per-second']
                        ]);
                        $polled++;
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Error polling device {$device['name']}: " . $e->getMessage();
            }
        }
        $success = "Polled $polled interface stats.";
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Show recent stats
$stmt = $pdo->query("SELECT s.*, d.name as device_name FROM interface_stats s JOIN skeleton_devices d ON s.device_id = d.id ORDER BY s.timestamp DESC LIMIT 50");
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0"><i class="bi bi-activity"></i> Network Monitoring</h5>
        </div>
        <div class="card-body">
          <form method="post" class="mb-3">
            <button type="submit" name="poll_interfaces" class="btn btn-primary">
              <i class="bi bi-arrow-repeat"></i> Poll All Devices Now
            </button>
          </form>
          <?php if ($success): ?>
            <div class="alert alert-success"> <?= htmlspecialchars($success) ?> </div>
          <?php endif; ?>
          <?php if ($errors): ?>
            <div class="alert alert-danger">
              <?php foreach ($errors as $err): ?>
                <div><?= htmlspecialchars($err) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <h6>Recent Interface Stats</h6>
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th>Device</th>
                  <th>Interface</th>
                  <th>RX (bps)</th>
                  <th>TX (bps)</th>
                  <th>Timestamp</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($stats as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['device_name']) ?></td>
                    <td><?= htmlspecialchars($row['interface_name']) ?></td>
                    <td><?= number_format($row['rx_bytes']) ?></td>
                    <td><?= number_format($row['tx_bytes']) ?></td>
                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php'; 