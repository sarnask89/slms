<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Bandwidth Utilization Reports';
$pdo = get_pdo();
$errors = [];
$success = '';
$reports = [];

// Report Generation Class
class BandwidthReports {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function generateDailyReport($device_id = null, $date = null) {
        if (!$date) $date = date('Y-m-d');
        
        $where_conditions = ["DATE(timestamp) = ?"];
        $params = [$date];
        
        if ($device_id) {
            $where_conditions[] = "device_id = ?";
            $params[] = $device_id;
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        $stmt = $this->pdo->prepare("
            SELECT 
                d.name as device_name,
                s.interface_name,
                AVG(s.rx_bytes) as avg_rx_bytes,
                AVG(s.tx_bytes) as avg_tx_bytes,
                MAX(s.rx_bytes) as max_rx_bytes,
                MAX(s.tx_bytes) as max_tx_bytes,
                MIN(s.rx_bytes) as min_rx_bytes,
                MIN(s.tx_bytes) as min_tx_bytes,
                COUNT(*) as samples
            FROM interface_stats s
            JOIN skeleton_devices d ON s.device_id = d.id
            $where_clause
            GROUP BY s.device_id, s.interface_name
            ORDER BY d.name, s.interface_name
        ");
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function generateWeeklyReport($device_id = null, $week_start = null) {
        if (!$week_start) $week_start = date('Y-m-d', strtotime('monday this week'));
        
        $where_conditions = ["DATE(timestamp) >= ? AND DATE(timestamp) <= DATE_ADD(?, INTERVAL 6 DAY)"];
        $params = [$week_start, $week_start];
        
        if ($device_id) {
            $where_conditions[] = "device_id = ?";
            $params[] = $device_id;
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        $stmt = $this->pdo->prepare("
            SELECT 
                d.name as device_name,
                s.interface_name,
                DATE(s.timestamp) as date,
                AVG(s.rx_bytes) as avg_rx_bytes,
                AVG(s.tx_bytes) as avg_tx_bytes,
                MAX(s.rx_bytes) as max_rx_bytes,
                MAX(s.tx_bytes) as max_tx_bytes,
                COUNT(*) as samples
            FROM interface_stats s
            JOIN skeleton_devices d ON s.device_id = d.id
            $where_clause
            GROUP BY s.device_id, s.interface_name, DATE(s.timestamp)
            ORDER BY d.name, s.interface_name, date
        ");
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function generateMonthlyReport($device_id = null, $month = null) {
        if (!$month) $month = date('Y-m');
        
        $where_conditions = ["DATE_FORMAT(timestamp, '%Y-%m') = ?"];
        $params = [$month];
        
        if ($device_id) {
            $where_conditions[] = "device_id = ?";
            $params[] = $device_id;
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        $stmt = $this->pdo->prepare("
            SELECT 
                d.name as device_name,
                s.interface_name,
                DATE(s.timestamp) as date,
                AVG(s.rx_bytes) as avg_rx_bytes,
                AVG(s.tx_bytes) as avg_tx_bytes,
                MAX(s.rx_bytes) as max_rx_bytes,
                MAX(s.tx_bytes) as max_tx_bytes,
                COUNT(*) as samples
            FROM interface_stats s
            JOIN skeleton_devices d ON s.device_id = d.id
            $where_clause
            GROUP BY s.device_id, s.interface_name, DATE(s.timestamp)
            ORDER BY d.name, s.interface_name, date
        ");
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTopInterfaces($limit = 10, $period = '24h') {
        $time_condition = match($period) {
            '24h' => "timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            '7d' => "timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            '30d' => "timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            default => "timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        };
        
        $stmt = $this->pdo->prepare("
            SELECT 
                d.name as device_name,
                s.interface_name,
                AVG(s.rx_bytes) as avg_rx_mbps,
                AVG(s.tx_bytes) as avg_tx_mbps,
                MAX(s.rx_bytes) as peak_rx_mbps,
                MAX(s.tx_bytes) as peak_tx_mbps,
                COUNT(*) as samples
            FROM interface_stats s
            JOIN skeleton_devices d ON s.device_id = d.id
            WHERE $time_condition
            GROUP BY s.device_id, s.interface_name
            ORDER BY (AVG(s.rx_bytes) + AVG(s.tx_bytes)) DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function exportToCSV($data, $filename) {
        if (empty($data)) return false;
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, array_keys($data[0]));
        
        // Data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        return true;
    }
}

// Handle report generation
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportSystem = new BandwidthReports($pdo);
    
    if (isset($_POST['generate_daily'])) {
        $device_id = !empty($_POST['device_id']) ? (int)$_POST['device_id'] : null;
        $date = $_POST['date'] ?? date('Y-m-d');
        
        $reports['daily'] = $reportSystem->generateDailyReport($device_id, $date);
        $success = "Daily report generated for " . count($reports['daily']) . " interfaces";
        
    } elseif (isset($_POST['generate_weekly'])) {
        $device_id = !empty($_POST['device_id']) ? (int)$_POST['device_id'] : null;
        $week_start = $_POST['week_start'] ?? date('Y-m-d', strtotime('monday this week'));
        
        $reports['weekly'] = $reportSystem->generateWeeklyReport($device_id, $week_start);
        $success = "Weekly report generated for " . count($reports['weekly']) . " records";
        
    } elseif (isset($_POST['generate_monthly'])) {
        $device_id = !empty($_POST['device_id']) ? (int)$_POST['device_id'] : null;
        $month = $_POST['month'] ?? date('Y-m');
        
        $reports['monthly'] = $reportSystem->generateMonthlyReport($device_id, $month);
        $success = "Monthly report generated for " . count($reports['monthly']) . " records";
        
    } elseif (isset($_POST['get_top_interfaces'])) {
        $period = $_POST['period'] ?? '24h';
        $limit = (int)($_POST['limit'] ?? 10);
        
        $reports['top_interfaces'] = $reportSystem->getTopInterfaces($limit, $period);
        $success = "Top interfaces report generated for $period period";
    }
}

// Get devices for filters
$devices = $pdo->query("SELECT id, name FROM skeleton_devices ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="bi bi-file-earmark-text"></i> Bandwidth Utilization Reports
          </h5>
        </div>
        <div class="card-body">
          
          <!-- Report Generation Forms -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Daily Report</h6>
                </div>
                <div class="card-body">
                  <form method="post" class="row g-2">
                    <div class="col-md-6">
                      <label class="form-label">Device</label>
                      <select name="device_id" class="form-select">
                        <option value="">All Devices</option>
                        <?php foreach ($devices as $dev): ?>
                          <option value="<?= $dev['id'] ?>"><?= htmlspecialchars($dev['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date</label>
                      <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">&nbsp;</label>
                      <button type="submit" name="generate_daily" class="btn btn-primary w-100">
                        <i class="bi bi-calendar-day"></i> Generate
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Weekly Report</h6>
                </div>
                <div class="card-body">
                  <form method="post" class="row g-2">
                    <div class="col-md-6">
                      <label class="form-label">Device</label>
                      <select name="device_id" class="form-select">
                        <option value="">All Devices</option>
                        <?php foreach ($devices as $dev): ?>
                          <option value="<?= $dev['id'] ?>"><?= htmlspecialchars($dev['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Week Start</label>
                      <input type="date" name="week_start" class="form-control" value="<?= date('Y-m-d', strtotime('monday this week')) ?>">
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">&nbsp;</label>
                      <button type="submit" name="generate_weekly" class="btn btn-info w-100">
                        <i class="bi bi-calendar-week"></i> Generate
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Monthly Report</h6>
                </div>
                <div class="card-body">
                  <form method="post" class="row g-2">
                    <div class="col-md-6">
                      <label class="form-label">Device</label>
                      <select name="device_id" class="form-select">
                        <option value="">All Devices</option>
                        <?php foreach ($devices as $dev): ?>
                          <option value="<?= $dev['id'] ?>"><?= htmlspecialchars($dev['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Month</label>
                      <input type="month" name="month" class="form-control" value="<?= date('Y-m') ?>">
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">&nbsp;</label>
                      <button type="submit" name="generate_monthly" class="btn btn-success w-100">
                        <i class="bi bi-calendar-month"></i> Generate
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Top Interfaces</h6>
                </div>
                <div class="card-body">
                  <form method="post" class="row g-2">
                    <div class="col-md-4">
                      <label class="form-label">Period</label>
                      <select name="period" class="form-select">
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Limit</label>
                      <select name="limit" class="form-select">
                        <option value="10">Top 10</option>
                        <option value="20">Top 20</option>
                        <option value="50">Top 50</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">&nbsp;</label>
                      <button type="submit" name="get_top_interfaces" class="btn btn-warning w-100">
                        <i class="bi bi-trophy"></i> Generate
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
          
          <!-- Report Results -->
          <?php foreach ($reports as $type => $data): ?>
            <?php if (!empty($data)): ?>
              <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="mb-0"><?= ucfirst($type) ?> Report (<?= count($data) ?> records)</h6>
                  <button class="btn btn-sm btn-outline-secondary" onclick="exportToCSV('<?= $type ?>')">
                    <i class="bi bi-download"></i> Export CSV
                  </button>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover" id="table-<?= $type ?>">
                      <thead class="table-light">
                        <tr>
                          <?php foreach (array_keys($data[0]) as $header): ?>
                            <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $header))) ?></th>
                          <?php endforeach; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($data as $row): ?>
                          <tr>
                            <?php foreach ($row as $value): ?>
                              <td>
                                <?php if (str_contains($value, '_mbps') || str_contains($value, '_bytes')): ?>
                                  <?= number_format($value) ?>
                                <?php else: ?>
                                  <?= htmlspecialchars($value) ?>
                                <?php endif; ?>
                              </td>
                            <?php endforeach; ?>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
          
          <!-- Quick Actions -->
          <div class="row mt-4">
            <div class="col-md-6">
              <a href="network_dashboard.php" class="btn btn-primary">
                <i class="bi bi-graph-up"></i> Network Dashboard
              </a>
              <a href="network_monitoring_enhanced.php" class="btn btn-secondary">
                <i class="bi bi-activity"></i> Enhanced Monitoring
              </a>
            </div>
            <div class="col-md-6">
              <a href="network_alerts.php" class="btn btn-warning">
                <i class="bi bi-exclamation-triangle"></i> Network Alerts
              </a>
              <a href="capacity_planning.php" class="btn btn-info">
                <i class="bi bi-calculator"></i> Capacity Planning
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function exportToCSV(type) {
    const table = document.getElementById('table-' + type);
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        
        for (let j = 0; j < cols.length; j++) {
            rowData.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        
        csv.push(rowData.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', type + '_report_' + new Date().toISOString().slice(0,10) + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 