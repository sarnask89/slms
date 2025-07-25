<?php
require_once __DIR__ . '/../config.php';
$pdo = get_pdo();

// Handle form
$device_ip = $_POST['device_ip'] ?? '';
$interface_index = $_POST['interface_index'] ?? '1';
$time_range = $_POST['time_range'] ?? '1h';

// Get available device IPs from skeleton_devices
$device_ips = $pdo->query("SELECT DISTINCT ip_address FROM skeleton_devices ORDER BY ip_address")->fetchAll(PDO::FETCH_COLUMN);

// Interface OIDs for the selected interface
$in_octets_oid = "1.3.6.1.2.1.2.2.1.10.$interface_index";
$out_octets_oid = "1.3.6.1.2.1.2.2.1.16.$interface_index";
$in_errors_oid = "1.3.6.1.2.1.2.2.1.13.$interface_index";
$out_errors_oid = "1.3.6.1.2.1.2.2.1.19.$interface_index";

// Fetch data for graphs
$data_points = [];
if ($device_ip) {
    // Calculate time limit based on selected range
    $time_limit = '';
    switch ($time_range) {
        case '1h': $time_limit = 'AND polled_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)'; break;
        case '6h': $time_limit = 'AND polled_at >= DATE_SUB(NOW(), INTERVAL 6 HOUR)'; break;
        case '24h': $time_limit = 'AND polled_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)'; break;
        case '7d': $time_limit = 'AND polled_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'; break;
        default: $time_limit = 'AND polled_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)';
    }
    
    // Get interface data
    $stmt = $pdo->prepare("
        SELECT 
            UNIX_TIMESTAMP(polled_at) as ts,
            oid,
            value
        FROM snmp_graph_data 
        WHERE device_ip = ? 
        AND oid IN (?, ?, ?, ?)
        $time_limit
        ORDER BY polled_at ASC
    ");
    $stmt->execute([$device_ip, $in_octets_oid, $out_octets_oid, $in_errors_oid, $out_errors_oid]);
    $raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize data by timestamp
    $organized_data = [];
    foreach ($raw_data as $row) {
        $ts = $row['ts'];
        if (!isset($organized_data[$ts])) {
            $organized_data[$ts] = [
                'ts' => $ts,
                'in_octets' => null,
                'out_octets' => null,
                'in_errors' => null,
                'out_errors' => null
            ];
        }
        
        switch ($row['oid']) {
            case $in_octets_oid: $organized_data[$ts]['in_octets'] = $row['value']; break;
            case $out_octets_oid: $organized_data[$ts]['out_octets'] = $row['value']; break;
            case $in_errors_oid: $organized_data[$ts]['in_errors'] = $row['value']; break;
            case $out_errors_oid: $organized_data[$ts]['out_errors'] = $row['value']; break;
        }
    }
    
    $data_points = array_values($organized_data);
}

ob_start();
?>
<div class="container py-4">
  <h2>Interface Monitoring</h2>
  
  <form method="post" class="row g-3 mb-4">
    <div class="col-md-3">
      <label class="form-label">Device IP</label>
      <select name="device_ip" class="form-select" required>
        <option value="">-- Select Device --</option>
        <?php foreach ($device_ips as $ip): ?>
          <option value="<?= htmlspecialchars($ip) ?>" <?= $device_ip === $ip ? 'selected' : '' ?>><?= htmlspecialchars($ip) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Interface</label>
      <select name="interface_index" class="form-select">
        <?php for ($i = 1; $i <= 8; $i++): ?>
          <option value="<?= $i ?>" <?= $interface_index == $i ? 'selected' : '' ?>>ether<?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Time Range</label>
      <select name="time_range" class="form-select">
        <option value="1h" <?= $time_range === '1h' ? 'selected' : '' ?>>Last Hour</option>
        <option value="6h" <?= $time_range === '6h' ? 'selected' : '' ?>>Last 6 Hours</option>
        <option value="24h" <?= $time_range === '24h' ? 'selected' : '' ?>>Last 24 Hours</option>
        <option value="7d" <?= $time_range === '7d' ? 'selected' : '' ?>>Last 7 Days</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">&nbsp;</label>
      <div>
        <button type="submit" class="btn btn-primary">Show Graphs</button>
        <a href="snmp_graph_poll.php" class="btn btn-success">Poll Data</a>
      </div>
    </div>
  </form>

  <?php if ($device_ip && !empty($data_points)): ?>
    <div class="row">
      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header">
            <h5>Interface Traffic (ether<?= $interface_index ?>)</h5>
          </div>
          <div class="card-body">
            <canvas id="trafficGraph" height="200"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header">
            <h5>Interface Errors (ether<?= $interface_index ?>)</h5>
          </div>
          <div class="card-body">
            <canvas id="errorsGraph" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h5>Interface Statistics</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="text-center">
                  <h4 class="text-primary"><?= number_format($data_points[count($data_points)-1]['in_octets'] ?? 0) ?></h4>
                  <p class="text-muted">Total In Octets</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="text-center">
                  <h4 class="text-success"><?= number_format($data_points[count($data_points)-1]['out_octets'] ?? 0) ?></h4>
                  <p class="text-muted">Total Out Octets</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="text-center">
                  <h4 class="text-warning"><?= number_format($data_points[count($data_points)-1]['in_errors'] ?? 0) ?></h4>
                  <p class="text-muted">In Errors</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="text-center">
                  <h4 class="text-danger"><?= number_format($data_points[count($data_points)-1]['out_errors'] ?? 0) ?></h4>
                  <p class="text-muted">Out Errors</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      const dataPoints = <?= json_encode($data_points) ?>;
      
      // Traffic Graph
      const trafficCtx = document.getElementById('trafficGraph').getContext('2d');
      new Chart(trafficCtx, {
        type: 'line',
        data: {
          labels: dataPoints.map(dp => new Date(dp.ts * 1000).toLocaleTimeString()),
          datasets: [{
            label: 'In Octets',
            data: dataPoints.map(dp => dp.in_octets),
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            fill: false
          }, {
            label: 'Out Octets',
            data: dataPoints.map(dp => dp.out_octets),
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            fill: false
          }]
        },
        options: {
          responsive: true,
          plugins: {
            title: { display: true, text: 'Interface Traffic' }
          },
          scales: {
            y: { beginAtZero: true }
          }
        }
      });
      
      // Errors Graph
      const errorsCtx = document.getElementById('errorsGraph').getContext('2d');
      new Chart(errorsCtx, {
        type: 'line',
        data: {
          labels: dataPoints.map(dp => new Date(dp.ts * 1000).toLocaleTimeString()),
          datasets: [{
            label: 'In Errors',
            data: dataPoints.map(dp => dp.in_errors),
            borderColor: 'rgba(255, 206, 86, 1)',
            backgroundColor: 'rgba(255, 206, 86, 0.1)',
            fill: false
          }, {
            label: 'Out Errors',
            data: dataPoints.map(dp => dp.out_errors),
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            fill: false
          }]
        },
        options: {
          responsive: true,
          plugins: {
            title: { display: true, text: 'Interface Errors' }
          },
          scales: {
            y: { beginAtZero: true }
          }
        }
      });
    </script>
  <?php elseif ($device_ip): ?>
    <div class="alert alert-info">
      No interface data available. Click "Poll Data" to collect SNMP interface statistics first.
    </div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php'; 