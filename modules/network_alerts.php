<?php
require_once 'module_loader.php';

require_once __DIR__ . '/mikrotik_api.php';

$pageTitle = 'Network Alerts & Monitoring';
$pdo = get_pdo();
$errors = [];
$success = '';
$alerts = [];

// Alert Configuration Class
class NetworkAlerts {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function checkHighUsage($device_id, $interface_name, $threshold_mbps = 100) {
        $stmt = $this->pdo->prepare("
            SELECT rx_bytes, tx_bytes, timestamp 
            FROM interface_stats 
            WHERE device_id = ? AND interface_name = ? 
            ORDER BY timestamp DESC 
            LIMIT 1
        ");
        $stmt->execute([$device_id, $interface_name]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stats) {
            $rx_mbps = ($stats['rx_bytes'] * 8) / 1000000; // Convert to Mbps
            $tx_mbps = ($stats['tx_bytes'] * 8) / 1000000;
            
            if ($rx_mbps > $threshold_mbps || $tx_mbps > $threshold_mbps) {
                return [
                    'alert' => true,
                    'type' => 'high_usage',
                    'rx_mbps' => round($rx_mbps, 2),
                    'tx_mbps' => round($tx_mbps, 2),
                    'threshold' => $threshold_mbps,
                    'timestamp' => $stats['timestamp']
                ];
            }
        }
        
        return ['alert' => false];
    }
    
    public function checkInterfaceStatus($device_id, $interface_name) {
        try {
            $stmt = $this->pdo->prepare("SELECT ip_address, api_username, api_password FROM skeleton_devices WHERE id = ?");
            $stmt->execute([$device_id]);
            $device = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$device) {
                return ['alert' => true, 'type' => 'device_not_found'];
            }
            
            $api = new MikroTikAPI($device['ip_address'], $device['api_username'], $device['api_password']);
            $interfaces = $api->restGet('/interface/print');
            
            foreach ($interfaces as $iface) {
                if ($iface['name'] === $interface_name) {
                    if ($iface['disabled'] === 'true') {
                        return ['alert' => true, 'type' => 'interface_disabled', 'interface' => $iface];
                    }
                    if ($iface['running'] === 'false') {
                        return ['alert' => true, 'type' => 'interface_down', 'interface' => $iface];
                    }
                    return ['alert' => false, 'interface' => $iface];
                }
            }
            
            return ['alert' => true, 'type' => 'interface_not_found'];
            
        } catch (Exception $e) {
            return ['alert' => true, 'type' => 'connection_error', 'error' => $e->getMessage()];
        }
    }
    
    public function sendEmailAlert($to, $subject, $message) {
        $headers = "From: LMS Alerts <noreply@lms.local>\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
    
    public function logAlert($device_id, $interface_name, $alert_type, $details) {
        $stmt = $this->pdo->prepare("
            INSERT INTO network_alerts (device_id, interface_name, alert_type, details, timestamp) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$device_id, $interface_name, $alert_type, json_encode($details)]);
    }
}

// Handle alert configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_alerts'])) {
        $alertSystem = new NetworkAlerts($pdo);
        
        // Get all devices and interfaces
        $stmt = $pdo->query("
            SELECT DISTINCT s.device_id, s.interface_name, d.name as device_name 
            FROM interface_stats s 
            JOIN skeleton_devices d ON s.device_id = d.id 
            ORDER BY d.name, s.interface_name
        ");
        $interfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($interfaces as $iface) {
            // Check high usage
            $usage_alert = $alertSystem->checkHighUsage($iface['device_id'], $iface['interface_name'], 50);
            if ($usage_alert['alert']) {
                $alerts[] = [
                    'device' => $iface['device_name'],
                    'interface' => $iface['interface_name'],
                    'type' => 'High Usage',
                    'details' => "RX: {$usage_alert['rx_mbps']} Mbps, TX: {$usage_alert['tx_mbps']} Mbps",
                    'severity' => 'warning'
                ];
            }
            
            // Check interface status
            $status_alert = $alertSystem->checkInterfaceStatus($iface['device_id'], $iface['interface_name']);
            if ($status_alert['alert']) {
                $alerts[] = [
                    'device' => $iface['device_name'],
                    'interface' => $iface['interface_name'],
                    'type' => 'Interface Status',
                    'details' => ucfirst(str_replace('_', ' ', $status_alert['type'])),
                    'severity' => 'critical'
                ];
            }
        }
        
        $success = "Alert check completed. Found " . count($alerts) . " alerts.";
        
    } elseif (isset($_POST['send_test_email'])) {
        $alertSystem = new NetworkAlerts($pdo);
        $email = $_POST['test_email'] ?? 'admin@example.com';
        
        $subject = "LMS Network Alert Test";
        $message = "
        <h2>Network Alert Test</h2>
        <p>This is a test email from your LMS Network Monitoring System.</p>
        <p>Time: " . date('Y-m-d H:i:s') . "</p>
        <p>If you received this email, the alert system is working correctly.</p>
        ";
        
        if ($alertSystem->sendEmailAlert($email, $subject, $message)) {
            $success = "Test email sent successfully to $email";
        } else {
            $errors[] = "Failed to send test email to $email";
        }
    }
}

// Get recent alerts from database
$stmt = $pdo->query("
    SELECT na.*, d.name as device_name 
    FROM network_alerts na 
    JOIN skeleton_devices d ON na.device_id = d.id 
    ORDER BY na.timestamp DESC 
    LIMIT 50
");
$recent_alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="bi bi-exclamation-triangle"></i> Network Alerts & Monitoring
          </h5>
        </div>
        <div class="card-body">
          
          <!-- Alert Actions -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Check Current Alerts</h6>
                </div>
                <div class="card-body">
                  <form method="post">
                    <button type="submit" name="check_alerts" class="btn btn-primary">
                      <i class="bi bi-search"></i> Check All Interfaces
                    </button>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Test Email Alerts</h6>
                </div>
                <div class="card-body">
                  <form method="post" class="row g-2">
                    <div class="col-8">
                      <input type="email" name="test_email" class="form-control" placeholder="admin@example.com" required>
                    </div>
                    <div class="col-4">
                      <button type="submit" name="send_test_email" class="btn btn-warning">
                        <i class="bi bi-envelope"></i> Send Test
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Results -->
          <?php if (!empty($success)): ?>
            <div class="alert alert-success">
              <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <h6><i class="bi bi-exclamation-triangle"></i> Errors:</h6>
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          
          <!-- Current Alerts -->
          <?php if (!empty($alerts)): ?>
            <div class="card mb-4">
              <div class="card-header">
                <h6 class="mb-0">Current Alerts (<?= count($alerts) ?>)</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-sm table-bordered">
                    <thead class="table-light">
                      <tr>
                        <th>Device</th>
                        <th>Interface</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Severity</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($alerts as $alert): ?>
                        <tr class="<?= $alert['severity'] === 'critical' ? 'table-danger' : 'table-warning' ?>">
                          <td><?= htmlspecialchars($alert['device']) ?></td>
                          <td><code><?= htmlspecialchars($alert['interface']) ?></code></td>
                          <td><?= htmlspecialchars($alert['type']) ?></td>
                          <td><?= htmlspecialchars($alert['details']) ?></td>
                          <td>
                            <span class="badge bg-<?= $alert['severity'] === 'critical' ? 'danger' : 'warning' ?>">
                              <?= ucfirst($alert['severity']) ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php endif; ?>
          
          <!-- Recent Alerts History -->
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">Recent Alert History</h6>
            </div>
            <div class="card-body">
              <?php if (empty($recent_alerts)): ?>
                <p class="text-muted">No recent alerts found.</p>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered table-hover">
                    <thead class="table-light">
                      <tr>
                        <th>Device</th>
                        <th>Interface</th>
                        <th>Alert Type</th>
                        <th>Details</th>
                        <th>Timestamp</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($recent_alerts as $alert): ?>
                        <tr>
                          <td><?= htmlspecialchars($alert['device_name']) ?></td>
                          <td><code><?= htmlspecialchars($alert['interface_name']) ?></code></td>
                          <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $alert['alert_type']))) ?></td>
                          <td>
                            <small class="text-muted">
                              <?= htmlspecialchars(substr($alert['details'], 0, 100)) ?>
                              <?= strlen($alert['details']) > 100 ? '...' : '' ?>
                            </small>
                          </td>
                          <td><?= htmlspecialchars($alert['timestamp']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
          
          <!-- Quick Actions -->
          <div class="row mt-4">
            <div class="col-md-6">
              <a href="network_monitoring_enhanced.php" class="btn btn-primary">
                <i class="bi bi-activity"></i> Enhanced Monitoring
              </a>
              <a href="network_dashboard.php" class="btn btn-success">
                <i class="bi bi-graph-up"></i> Network Dashboard
              </a>
            </div>
            <div class="col-md-6">
              <a href="bandwidth_reports.php" class="btn btn-info">
                <i class="bi bi-file-earmark-text"></i> Bandwidth Reports
              </a>
              <a href="capacity_planning.php" class="btn btn-warning">
                <i class="bi bi-calculator"></i> Capacity Planning
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 