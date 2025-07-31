<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Capacity Planning & Analysis';
$pdo = get_pdo();
$errors = [];
$success = '';
$analysis = [];

// Capacity Planning Class
class CapacityPlanning {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function analyzeGrowthTrends($device_id = null, $days = 30) {
        $where_conditions = ["timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)"];
        $params = [$days];
        
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
                AVG(s.rx_bytes) as avg_rx_mbps,
                AVG(s.tx_bytes) as avg_tx_mbps,
                MAX(s.rx_bytes) as peak_rx_mbps,
                MAX(s.tx_bytes) as peak_tx_mbps,
                COUNT(*) as samples
            FROM interface_stats s
            JOIN skeleton_devices d ON s.device_id = d.id
            $where_clause
            GROUP BY s.device_id, s.interface_name, DATE(s.timestamp)
            ORDER BY s.device_id, s.interface_name, date
        ");
        $stmt->execute($params);
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate growth trends
        $trends = [];
        $current_group = null;
        $daily_totals = [];
        
        foreach ($data as $row) {
            $key = $row['device_name'] . '|' . $row['interface_name'];
            
            if ($current_group !== $key) {
                $current_group = $key;
                $daily_totals[$key] = [];
            }
            
            $daily_totals[$key][$row['date']] = $row['avg_rx_mbps'] + $row['avg_tx_mbps'];
        }
        
        // Calculate growth rate for each interface
        foreach ($daily_totals as $key => $dates) {
            if (count($dates) < 2) continue;
            
            $dates_sorted = array_keys($dates);
            sort($dates_sorted);
            
            $first_date = $dates_sorted[0];
            $last_date = end($dates_sorted);
            $first_value = $dates[$first_date];
            $last_value = $dates[$last_date];
            
            $days_between = (strtotime($last_date) - strtotime($first_date)) / (24 * 3600);
            $growth_rate = $days_between > 0 ? (($last_value - $first_value) / $days_between) : 0;
            
            list($device_name, $interface_name) = explode('|', $key);
            
            $trends[] = [
                'device_name' => $device_name,
                'interface_name' => $interface_name,
                'first_date' => $first_date,
                'last_date' => $last_date,
                'first_value' => round($first_value, 2),
                'last_value' => round($last_value, 2),
                'growth_rate' => round($growth_rate, 2),
                'growth_percentage' => round((($last_value - $first_value) / $first_value) * 100, 2),
                'days_analyzed' => $days_between
            ];
        }
        
        return $trends;
    }
    
    public function predictCapacityNeeds($device_id = null, $months_ahead = 6) {
        $trends = $this->analyzeGrowthTrends($device_id, 90); // Use 90 days for prediction
        $predictions = [];
        
        foreach ($trends as $trend) {
            $current_capacity = $trend['last_value'];
            $growth_rate = $trend['growth_rate'];
            
            // Predict future capacity needs
            $predicted_capacity = $current_capacity + ($growth_rate * ($months_ahead * 30));
            $capacity_increase = $predicted_capacity - $current_capacity;
            $increase_percentage = ($capacity_increase / $current_capacity) * 100;
            
            // Determine recommendation
            $recommendation = 'Monitor';
            $priority = 'low';
            
            if ($increase_percentage > 50) {
                $recommendation = 'Upgrade Recommended';
                $priority = 'high';
            } elseif ($increase_percentage > 25) {
                $recommendation = 'Plan for Upgrade';
                $priority = 'medium';
            }
            
            $predictions[] = [
                'device_name' => $trend['device_name'],
                'interface_name' => $trend['interface_name'],
                'current_capacity' => round($current_capacity, 2),
                'predicted_capacity' => round($predicted_capacity, 2),
                'capacity_increase' => round($capacity_increase, 2),
                'increase_percentage' => round($increase_percentage, 2),
                'months_ahead' => $months_ahead,
                'recommendation' => $recommendation,
                'priority' => $priority
            ];
        }
        
        return $predictions;
    }
    
    public function getUtilizationAnalysis($device_id = null) {
        $where_conditions = ["timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)"];
        $params = [];
        
        if ($device_id) {
            $where_conditions[] = "device_id = ?";
            $params[] = $device_id;
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
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
            $where_clause
            GROUP BY s.device_id, s.interface_name
            ORDER BY (AVG(s.rx_bytes) + AVG(s.tx_bytes)) DESC
        ");
        $stmt->execute($params);
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $analysis = [];
        
        foreach ($data as $row) {
            $total_avg = $row['avg_rx_mbps'] + $row['avg_tx_mbps'];
            $total_peak = $row['peak_rx_mbps'] + $row['peak_tx_mbps'];
            
            // Assume 1 Gbps = 1000 Mbps for capacity calculation
            $assumed_capacity = 1000; // 1 Gbps
            $utilization_avg = ($total_avg / $assumed_capacity) * 100;
            $utilization_peak = ($total_peak / $assumed_capacity) * 100;
            
            $status = 'Normal';
            $status_class = 'success';
            
            if ($utilization_peak > 90) {
                $status = 'Critical';
                $status_class = 'danger';
            } elseif ($utilization_peak > 70) {
                $status = 'High';
                $status_class = 'warning';
            } elseif ($utilization_avg > 50) {
                $status = 'Moderate';
                $status_class = 'info';
            }
            
            $analysis[] = [
                'device_name' => $row['device_name'],
                'interface_name' => $row['interface_name'],
                'avg_utilization' => round($utilization_avg, 2),
                'peak_utilization' => round($utilization_peak, 2),
                'avg_rx_mbps' => round($row['avg_rx_mbps'], 2),
                'avg_tx_mbps' => round($row['avg_tx_mbps'], 2),
                'peak_rx_mbps' => round($row['peak_rx_mbps'], 2),
                'peak_tx_mbps' => round($row['peak_tx_mbps'], 2),
                'status' => $status,
                'status_class' => $status_class
            ];
        }
        
        return $analysis;
    }
    
    public function generateRecommendations($device_id = null) {
        $utilization = $this->getUtilizationAnalysis($device_id);
        $predictions = $this->predictCapacityNeeds($device_id, 6);
        
        $recommendations = [];
        
        // High utilization recommendations
        foreach ($utilization as $util) {
            if ($util['peak_utilization'] > 80) {
                $recommendations[] = [
                    'type' => 'High Utilization',
                    'device' => $util['device_name'],
                    'interface' => $util['interface_name'],
                    'issue' => "Peak utilization at {$util['peak_utilization']}%",
                    'recommendation' => 'Consider bandwidth upgrade or traffic optimization',
                    'priority' => 'high',
                    'estimated_cost' => '$500-2000'
                ];
            }
        }
        
        // Growth-based recommendations
        foreach ($predictions as $pred) {
            if ($pred['priority'] === 'high') {
                $recommendations[] = [
                    'type' => 'Growth Prediction',
                    'device' => $pred['device_name'],
                    'interface' => $pred['interface_name'],
                    'issue' => "Predicted {$pred['increase_percentage']}% capacity increase in 6 months",
                    'recommendation' => 'Plan infrastructure upgrade',
                    'priority' => 'medium',
                    'estimated_cost' => '$1000-5000'
                ];
            }
        }
        
        return $recommendations;
    }
}

// Handle analysis requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $capacitySystem = new CapacityPlanning($pdo);
    
    if (isset($_POST['analyze_growth'])) {
        $device_id = !empty($_POST['device_id']) ? (int)$_POST['device_id'] : null;
        $days = (int)($_POST['days'] ?? 30);
        
        $analysis['growth_trends'] = $capacitySystem->analyzeGrowthTrends($device_id, $days);
        $success = "Growth analysis completed for " . count($analysis['growth_trends']) . " interfaces";
        
    } elseif (isset($_POST['predict_capacity'])) {
        $device_id = !empty($_POST['device_id']) ? (int)$_POST['device_id'] : null;
        $months = (int)($_POST['months'] ?? 6);
        
        $analysis['predictions'] = $capacitySystem->predictCapacityNeeds($device_id, $months);
        $success = "Capacity predictions generated for " . count($analysis['predictions']) . " interfaces";
        
    } elseif (isset($_POST['analyze_utilization'])) {
        $device_id = !empty($_POST['device_id']) ? (int)$_POST['device_id'] : null;
        
        $analysis['utilization'] = $capacitySystem->getUtilizationAnalysis($device_id);
        $success = "Utilization analysis completed for " . count($analysis['utilization']) . " interfaces";
        
    } elseif (isset($_POST['generate_recommendations'])) {
        $device_id = !empty($_POST['device_id']) ? (int)$_POST['device_id'] : null;
        
        $analysis['recommendations'] = $capacitySystem->generateRecommendations($device_id);
        $success = "Generated " . count($analysis['recommendations']) . " recommendations";
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
            <i class="bi bi-calculator"></i> Capacity Planning & Analysis
          </h5>
        </div>
        <div class="card-body">
          
          <!-- Analysis Tools -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Growth Trend Analysis</h6>
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
                      <label class="form-label">Days</label>
                      <select name="days" class="form-select">
                        <option value="7">7 Days</option>
                        <option value="30" selected>30 Days</option>
                        <option value="90">90 Days</option>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">&nbsp;</label>
                      <button type="submit" name="analyze_growth" class="btn btn-primary w-100">
                        <i class="bi bi-graph-up-arrow"></i> Analyze
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Capacity Prediction</h6>
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
                      <label class="form-label">Months Ahead</label>
                      <select name="months" class="form-select">
                        <option value="3">3 Months</option>
                        <option value="6" selected>6 Months</option>
                        <option value="12">12 Months</option>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">&nbsp;</label>
                      <button type="submit" name="predict_capacity" class="btn btn-info w-100">
                        <i class="bi bi-calculator"></i> Predict
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
                  <h6 class="mb-0">Utilization Analysis</h6>
                </div>
                <div class="card-body">
                  <form method="post" class="row g-2">
                    <div class="col-md-8">
                      <label class="form-label">Device</label>
                      <select name="device_id" class="form-select">
                        <option value="">All Devices</option>
                        <?php foreach ($devices as $dev): ?>
                          <option value="<?= $dev['id'] ?>"><?= htmlspecialchars($dev['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">&nbsp;</label>
                      <button type="submit" name="analyze_utilization" class="btn btn-success w-100">
                        <i class="bi bi-speedometer2"></i> Analyze
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Generate Recommendations</h6>
                </div>
                <div class="card-body">
                  <form method="post" class="row g-2">
                    <div class="col-md-8">
                      <label class="form-label">Device</label>
                      <select name="device_id" class="form-select">
                        <option value="">All Devices</option>
                        <?php foreach ($devices as $dev): ?>
                          <option value="<?= $dev['id'] ?>"><?= htmlspecialchars($dev['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">&nbsp;</label>
                      <button type="submit" name="generate_recommendations" class="btn btn-warning w-100">
                        <i class="bi bi-lightbulb"></i> Generate
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
          
          <!-- Analysis Results -->
          <?php foreach ($analysis as $type => $data): ?>
            <?php if (!empty($data)): ?>
              <div class="card mb-4">
                <div class="card-header">
                  <h6 class="mb-0"><?= ucwords(str_replace('_', ' ', $type)) ?> (<?= count($data) ?> results)</h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                      <thead class="table-light">
                        <tr>
                          <?php foreach (array_keys($data[0]) as $header): ?>
                            <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $header))) ?></th>
                          <?php endforeach; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($data as $row): ?>
                          <tr class="<?= isset($row['status_class']) ? 'table-' . $row['status_class'] : '' ?>">
                            <?php foreach ($row as $key => $value): ?>
                              <td>
                                <?php if ($key === 'priority'): ?>
                                  <span class="badge bg-<?= $value === 'high' ? 'danger' : ($value === 'medium' ? 'warning' : 'success') ?>">
                                    <?= ucfirst($value) ?>
                                  </span>
                                <?php elseif ($key === 'status'): ?>
                                  <span class="badge bg-<?= $row['status_class'] ?>">
                                    <?= htmlspecialchars($value) ?>
                                  </span>
                                <?php elseif (is_numeric($value) && str_contains($key, 'percentage')): ?>
                                  <?= htmlspecialchars($value) ?>%
                                <?php elseif (is_numeric($value) && str_contains($key, 'mbps')): ?>
                                  <?= number_format($value, 2) ?> Mbps
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
              <a href="bandwidth_reports.php" class="btn btn-secondary">
                <i class="bi bi-file-earmark-text"></i> Bandwidth Reports
              </a>
            </div>
            <div class="col-md-6">
              <a href="network_alerts.php" class="btn btn-warning">
                <i class="bi bi-exclamation-triangle"></i> Network Alerts
              </a>
              <a href="network_monitoring_enhanced.php" class="btn btn-info">
                <i class="bi bi-activity"></i> Enhanced Monitoring
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