<?php
require_once 'module_loader.php';


$pageTitle = 'System Status Dashboard';
$pdo = get_pdo();
$errors = [];
$success = '';

// System Status Class
class SystemStatus {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getOverallHealth() {
        $stats = [];
        
        // Device count
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM skeleton_devices");
        $stats['total_devices'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Devices with API credentials
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM skeleton_devices WHERE api_username IS NOT NULL AND api_password IS NOT NULL");
        $stats['configured_devices'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Recent interface stats
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM interface_stats WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stats['recent_stats'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Recent alerts
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM network_alerts WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stats['recent_alerts'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Calculate health score
        $health_score = 100;
        if ($stats['total_devices'] > 0) {
            $configured_ratio = $stats['configured_devices'] / $stats['total_devices'];
            $health_score -= (1 - $configured_ratio) * 30;
        }
        
        if ($stats['recent_alerts'] > 0) {
            $health_score -= min($stats['recent_alerts'] * 5, 30);
        }
        
        $stats['health_score'] = max(0, round($health_score));
        
        return $stats;
    }
    
    public function getDeviceStatus() {
        $stmt = $this->pdo->query("
            SELECT 
                d.id,
                d.name,
                d.ip_address,
                d.api_username,
                d.api_password,
                COUNT(s.id) as stats_count,
                MAX(s.timestamp) as last_poll
            FROM skeleton_devices d
            LEFT JOIN interface_stats s ON d.id = s.device_id
            GROUP BY d.id, d.name, d.ip_address, d.api_username, d.api_password
            ORDER BY d.name
        ");
        
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($devices as &$device) {
            $device['status'] = 'Unknown';
            $device['status_class'] = 'secondary';
            
            if (!$device['api_username'] || !$device['api_password']) {
                $device['status'] = 'Not Configured';
                $device['status_class'] = 'warning';
            } elseif (!$device['last_poll']) {
                $device['status'] = 'No Data';
                $device['status_class'] = 'info';
            } else {
                $last_poll = strtotime($device['last_poll']);
                $hours_ago = (time() - $last_poll) / 3600;
                
                if ($hours_ago < 1) {
                    $device['status'] = 'Online';
                    $device['status_class'] = 'success';
                } elseif ($hours_ago < 24) {
                    $device['status'] = 'Stale Data';
                    $device['status_class'] = 'warning';
                } else {
                    $device['status'] = 'Offline';
                    $device['status_class'] = 'danger';
                }
            }
        }
        
        return $devices;
    }
    
    public function getPerformanceMetrics() {
        $metrics = [];
        
        // Average bandwidth usage
        $stmt = $this->pdo->query("
            SELECT 
                AVG(rx_bytes) as avg_rx,
                AVG(tx_bytes) as avg_tx,
                MAX(rx_bytes) as peak_rx,
                MAX(tx_bytes) as peak_tx
            FROM interface_stats 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $bandwidth = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $metrics['avg_rx_mbps'] = round(($bandwidth['avg_rx'] * 8) / 1000000, 2);
        $metrics['avg_tx_mbps'] = round(($bandwidth['avg_tx'] * 8) / 1000000, 2);
        $metrics['peak_rx_mbps'] = round(($bandwidth['peak_rx'] * 8) / 1000000, 2);
        $metrics['peak_tx_mbps'] = round(($bandwidth['peak_tx'] * 8) / 1000000, 2);
        
        // Top interfaces
        $stmt = $this->pdo->query("
            SELECT 
                d.name as device_name,
                s.interface_name,
                AVG(s.rx_bytes + s.tx_bytes) as avg_total
            FROM interface_stats s
            JOIN skeleton_devices d ON s.device_id = d.id
            WHERE s.timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY s.device_id, s.interface_name
            ORDER BY avg_total DESC
            LIMIT 5
        ");
        $metrics['top_interfaces'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Data collection stats
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_records,
                COUNT(DISTINCT device_id) as active_devices,
                COUNT(DISTINCT interface_name) as active_interfaces,
                MIN(timestamp) as oldest_record,
                MAX(timestamp) as newest_record
            FROM interface_stats
        ");
        $metrics['data_stats'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $metrics;
    }
    
    public function getRecentActivity() {
        $activities = [];
        
        // Recent interface stats
        $stmt = $this->pdo->query("
            SELECT 
                d.name as device_name,
                s.interface_name,
                s.rx_bytes,
                s.tx_bytes,
                s.timestamp
            FROM interface_stats s
            JOIN skeleton_devices d ON s.device_id = d.id
            ORDER BY s.timestamp DESC
            LIMIT 10
        ");
        $activities['recent_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent alerts
        $stmt = $this->pdo->query("
            SELECT 
                d.name as device_name,
                na.interface_name,
                na.alert_type,
                na.timestamp
            FROM network_alerts na
            JOIN skeleton_devices d ON na.device_id = d.id
            ORDER BY na.timestamp DESC
            LIMIT 10
        ");
        $activities['recent_alerts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $activities;
    }
    
    public function getSystemInfo() {
        $info = [];
        
        // Database info
        $stmt = $this->pdo->query("SELECT VERSION() as mysql_version");
        $info['mysql_version'] = $stmt->fetch(PDO::FETCH_ASSOC)['mysql_version'];
        
        // PHP info
        $info['php_version'] = PHP_VERSION;
        $info['server_time'] = date('Y-m-d H:i:s');
        $info['timezone'] = date_default_timezone_get();
        
        // System resources
        $info['memory_usage'] = round(memory_get_usage(true) / 1024 / 1024, 2); // MB
        $info['memory_limit'] = ini_get('memory_limit');
        $info['max_execution_time'] = ini_get('max_execution_time');
        
        return $info;
    }
}

// Get system status
$statusSystem = new SystemStatus($pdo);
$health = $statusSystem->getOverallHealth();
$devices = $statusSystem->getDeviceStatus();
$metrics = $statusSystem->getPerformanceMetrics();
$activities = $statusSystem->getRecentActivity();
$systemInfo = $statusSystem->getSystemInfo();

ob_start();
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="bi bi-speedometer2"></i> System Status Dashboard
          </h5>
        </div>
        <div class="card-body">
          
          <!-- Health Overview -->
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="card text-center">
                <div class="card-body">
                  <h3 class="text-<?= $health['health_score'] >= 80 ? 'success' : ($health['health_score'] >= 60 ? 'warning' : 'danger') ?>">
                    <?= $health['health_score'] ?>%
                  </h3>
                  <p class="card-text">System Health</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card text-center">
                <div class="card-body">
                  <h3 class="text-primary"><?= $health['total_devices'] ?></h3>
                  <p class="card-text">Total Devices</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card text-center">
                <div class="card-body">
                  <h3 class="text-success"><?= $health['configured_devices'] ?></h3>
                  <p class="card-text">Configured Devices</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card text-center">
                <div class="card-body">
                  <h3 class="text-<?= $health['recent_alerts'] > 0 ? 'danger' : 'success' ?>"><?= $health['recent_alerts'] ?></h3>
                  <p class="card-text">Recent Alerts (24h)</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Performance Metrics -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Bandwidth Usage (24h)</h6>
                </div>
                <div class="card-body">
                  <div class="row text-center">
                    <div class="col-6">
                      <h5 class="text-primary"><?= $metrics['avg_rx_mbps'] ?> Mbps</h5>
                      <small class="text-muted">Average RX</small>
                    </div>
                    <div class="col-6">
                      <h5 class="text-success"><?= $metrics['avg_tx_mbps'] ?> Mbps</h5>
                      <small class="text-muted">Average TX</small>
                    </div>
                  </div>
                  <hr>
                  <div class="row text-center">
                    <div class="col-6">
                      <h6 class="text-warning"><?= $metrics['peak_rx_mbps'] ?> Mbps</h6>
                      <small class="text-muted">Peak RX</small>
                    </div>
                    <div class="col-6">
                      <h6 class="text-info"><?= $metrics['peak_tx_mbps'] ?> Mbps</h6>
                      <small class="text-muted">Peak TX</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Top Interfaces (24h)</h6>
                </div>
                <div class="card-body">
                  <?php if (!empty($metrics['top_interfaces'])): ?>
                    <div class="table-responsive">
                      <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>Device</th>
                            <th>Interface</th>
                            <th>Usage</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($metrics['top_interfaces'] as $iface): ?>
                            <tr>
                              <td><?= htmlspecialchars($iface['device_name']) ?></td>
                              <td><code><?= htmlspecialchars($iface['interface_name']) ?></code></td>
                              <td><?= round(($iface['avg_total'] * 8) / 1000000, 2) ?> Mbps</td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <p class="text-muted">No interface data available</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Device Status -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Device Status</h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                      <thead class="table-light">
                        <tr>
                          <th>Device</th>
                          <th>IP Address</th>
                          <th>Status</th>
                          <th>Last Poll</th>
                          <th>Stats Count</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($devices as $device): ?>
                          <tr>
                            <td><?= htmlspecialchars($device['name']) ?></td>
                            <td><code><?= htmlspecialchars($device['ip_address']) ?></code></td>
                            <td>
                              <span class="badge bg-<?= $device['status_class'] ?>">
                                <?= htmlspecialchars($device['status']) ?>
                              </span>
                            </td>
                            <td>
                              <?= $device['last_poll'] ? htmlspecialchars($device['last_poll']) : 'Never' ?>
                            </td>
                            <td><?= number_format($device['stats_count']) ?></td>
                            <td>
                              <a href="network_dashboard.php?device_id=<?= $device['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-graph-up"></i> View
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Recent Activity -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Recent Interface Stats</h6>
                </div>
                <div class="card-body">
                  <?php if (!empty($activities['recent_stats'])): ?>
                    <div class="table-responsive">
                      <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>Device</th>
                            <th>Interface</th>
                            <th>RX/TX</th>
                            <th>Time</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($activities['recent_stats'] as $stat): ?>
                            <tr>
                              <td><?= htmlspecialchars($stat['device_name']) ?></td>
                              <td><code><?= htmlspecialchars($stat['interface_name']) ?></code></td>
                              <td>
                                <?= round(($stat['rx_bytes'] * 8) / 1000000, 2) ?> / 
                                <?= round(($stat['tx_bytes'] * 8) / 1000000, 2) ?> Mbps
                              </td>
                              <td><?= htmlspecialchars($stat['timestamp']) ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <p class="text-muted">No recent stats available</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Recent Alerts</h6>
                </div>
                <div class="card-body">
                  <?php if (!empty($activities['recent_alerts'])): ?>
                    <div class="table-responsive">
                      <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>Device</th>
                            <th>Interface</th>
                            <th>Alert Type</th>
                            <th>Time</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($activities['recent_alerts'] as $alert): ?>
                            <tr>
                              <td><?= htmlspecialchars($alert['device_name']) ?></td>
                              <td><code><?= htmlspecialchars($alert['interface_name']) ?></code></td>
                              <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $alert['alert_type']))) ?></td>
                              <td><?= htmlspecialchars($alert['timestamp']) ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <p class="text-muted">No recent alerts</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          
          <!-- System Information -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">System Information</h6>
                </div>
                <div class="card-body">
                  <table class="table table-sm">
                    <tr>
                      <td><strong>PHP Version:</strong></td>
                      <td><?= htmlspecialchars($systemInfo['php_version']) ?></td>
                    </tr>
                    <tr>
                      <td><strong>MySQL Version:</strong></td>
                      <td><?= htmlspecialchars($systemInfo['mysql_version']) ?></td>
                    </tr>
                    <tr>
                      <td><strong>Server Time:</strong></td>
                      <td><?= htmlspecialchars($systemInfo['server_time']) ?></td>
                    </tr>
                    <tr>
                      <td><strong>Timezone:</strong></td>
                      <td><?= htmlspecialchars($systemInfo['timezone']) ?></td>
                    </tr>
                    <tr>
                      <td><strong>Memory Usage:</strong></td>
                      <td><?= $systemInfo['memory_usage'] ?> MB / <?= htmlspecialchars($systemInfo['memory_limit']) ?></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Data Statistics</h6>
                </div>
                <div class="card-body">
                  <table class="table table-sm">
                    <tr>
                      <td><strong>Total Records:</strong></td>
                      <td><?= number_format($metrics['data_stats']['total_records']) ?></td>
                    </tr>
                    <tr>
                      <td><strong>Active Devices:</strong></td>
                      <td><?= $metrics['data_stats']['active_devices'] ?></td>
                    </tr>
                    <tr>
                      <td><strong>Active Interfaces:</strong></td>
                      <td><?= $metrics['data_stats']['active_interfaces'] ?></td>
                    </tr>
                    <tr>
                      <td><strong>Oldest Record:</strong></td>
                      <td><?= $metrics['data_stats']['oldest_record'] ? htmlspecialchars($metrics['data_stats']['oldest_record']) : 'N/A' ?></td>
                    </tr>
                    <tr>
                      <td><strong>Newest Record:</strong></td>
                      <td><?= $metrics['data_stats']['newest_record'] ? htmlspecialchars($metrics['data_stats']['newest_record']) : 'N/A' ?></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Quick Actions -->
          <div class="row mt-4">
            <div class="col-md-6">
              <a href="network_monitoring_enhanced.php" class="btn btn-primary">
                <i class="bi bi-activity"></i> Enhanced Monitoring
              </a>
              <a href="network_alerts.php" class="btn btn-warning">
                <i class="bi bi-exclamation-triangle"></i> Network Alerts
              </a>
            </div>
            <div class="col-md-6">
              <a href="bandwidth_reports.php" class="btn btn-info">
                <i class="bi bi-file-earmark-text"></i> Bandwidth Reports
              </a>
              <a href="capacity_planning.php" class="btn btn-success">
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