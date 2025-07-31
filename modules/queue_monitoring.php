<?php
require_once 'module_loader.php';

$pdo = get_pdo();

// Handle form
$device_ip = $_POST['device_ip'] ?? '';
$queue_index = $_POST['queue_index'] ?? '1';
$queue_type = $_POST['queue_type'] ?? 'simple';
$time_range = $_POST['time_range'] ?? '1h';

// Get available device IPs from skeleton_devices
$device_ips = $pdo->query("SELECT DISTINCT ip_address FROM skeleton_devices ORDER BY ip_address")->fetchAll(PDO::FETCH_COLUMN);

// Queue OIDs based on type
if ($queue_type === 'simple') {
    $bytes_in_oid = "1.3.6.1.4.1.14988.1.1.2.1.1.1.$queue_index";
    $bytes_out_oid = "1.3.6.1.4.1.14988.1.1.2.1.1.2.$queue_index";
    $packets_in_oid = "1.3.6.1.4.1.14988.1.1.2.1.1.3.$queue_index";
    $packets_out_oid = "1.3.6.1.4.1.14988.1.1.2.1.1.4.$queue_index";
    $dropped_oid = "1.3.6.1.4.1.14988.1.1.2.1.1.5.$queue_index";
} else { // queue tree
    $bytes_in_oid = "1.3.6.1.4.1.14988.1.1.2.2.1.1.$queue_index";
    $bytes_out_oid = "1.3.6.1.4.1.14988.1.1.2.2.1.2.$queue_index";
    $packets_in_oid = "1.3.6.1.4.1.14988.1.1.2.2.1.3.$queue_index";
    $packets_out_oid = "1.3.6.1.4.1.14988.1.1.2.2.1.4.$queue_index";
    $dropped_oid = "1.3.6.1.4.1.14988.1.1.2.2.1.5.$queue_index";
}

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
    
    // Get queue data
    $stmt = $pdo->prepare("
        SELECT 
            UNIX_TIMESTAMP(polled_at) as ts,
            oid,
            value
        FROM snmp_graph_data 
        WHERE device_ip = ? 
        AND oid IN (?, ?, ?, ?, ?)
        $time_limit
        ORDER BY polled_at ASC
    ");
    $stmt->execute([$device_ip, $bytes_in_oid, $bytes_out_oid, $packets_in_oid, $packets_out_oid, $dropped_oid]);
    $raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize data by timestamp
    $organized_data = [];
    foreach ($raw_data as $row) {
        $ts = $row['ts'];
        if (!isset($organized_data[$ts])) {
            $organized_data[$ts] = [
                'ts' => $ts,
                'bytes_in' => null,
                'bytes_out' => null,
                'packets_in' => null,
                'packets_out' => null,
                'dropped' => null
            ];
        }
        
        switch ($row['oid']) {
            case $bytes_in_oid: $organized_data[$ts]['bytes_in'] = $row['value']; break;
            case $bytes_out_oid: $organized_data[$ts]['bytes_out'] = $row['value']; break;
            case $packets_in_oid: $organized_data[$ts]['packets_in'] = $row['value']; break;
            case $packets_out_oid: $organized_data[$ts]['packets_out'] = $row['value']; break;
            case $dropped_oid: $organized_data[$ts]['dropped'] = $row['value']; break;
        }
    }
    
    $data_points = array_values($organized_data);
}

ob_start();
?>
<div class="container py-4">
  <h2>Queue Monitoring</h2>
  
  <form method="post" class="row g-3 mb-4">
    <div class="col-md-2">
      <label class="form-label">Device IP</label>
      <select name="device_ip" class="form-select" required>
        <option value="">-- Select Device --</option>
        <?php foreach ($device_ips as $ip): ?>
          <option value="<?= htmlspecialchars($ip) ?>" <?= $device_ip === $ip ? 'selected' : '' ?>><?= htmlspecialchars($ip) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Queue Type</label>
      <select name="queue_type" class="form-select">
        <option value="simple" <?= $queue_type === 'simple' ? 'selected' : '' ?>>Simple Queue</option>
        <option value="tree" <?= $queue_type === 'tree' ? 'selected' : '' ?>>Queue Tree</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Queue Index</label>
      <select name="queue_index" class="form-select">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <option value="<?= $i ?>" <?= $queue_index == $i ? 'selected' : '' ?>><?= ucfirst($queue_type) ?> <?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Time Range</label>
      <select name="time_range" class="form-select">
        <option value="1h" <?= $time_range === '1h' ? 'selected' : '' ?>>Last Hour</option>
        <option value="6h" <?= $time_range === '6h' ? 'selected' : '' ?>>Last 6 Hours</option>
        <option value="24h" <?= $time_range === '24h' ? 'selected' : '' ?>>Last 24 Hours</option>
        <option value="7d" <?= $time_range === '7d' ? 'selected' : '' ?>>Last 7 Days</option>
      </select>
    </div>
    <div class="col-md-4">
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
            <h5>Queue Bandwidth (<?= ucfirst($queue_type) ?> Queue <?= $queue_index ?>)</h5>
          </div>
          <div class="card-body">
            <canvas id="bandwidthGraph" height="200"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header">
            <h5>Queue Packets (<?= ucfirst($queue_type) ?> Queue <?= $queue_index ?>)</h5>
          </div>
          <div class="card-body">
            <canvas id="packetsGraph" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h5>Queue Statistics</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-2">
                <div class="text-center">
                  <h4 class="text-primary"><?= number_format($data_points[count($data_points)-1]['bytes_in'] ?? 0) ?></h4>
                  <p class="text-muted">Bytes In</p>
                </div>
              </div>
              <div class="col-md-2">
                <div class="text-center">
                  <h4 class="text-success"><?= number_format($data_points[count($data_points)-1]['bytes_out'] ?? 0) ?></h4>
                  <p class="text-muted">Bytes Out</p>
                </div>
              </div>
              <div class="col-md-2">
                <div class="text-center">
                  <h4 class="text-info"><?= number_format($data_points[count($data_points)-1]['packets_in'] ?? 0) ?></h4>
                  <p class="text-muted">Packets In</p>
                </div>
              </div>
              <div class="col-md-2">
                <div class="text-center">
                  <h4 class="text-warning"><?= number_format($data_points[count($data_points)-1]['packets_out'] ?? 0) ?></h4>
                  <p class="text-muted">Packets Out</p>
                </div>
              </div>
              <div class="col-md-2">
                <div class="text-center">
                  <h4 class="text-danger"><?= number_format($data_points[count($data_points)-1]['dropped'] ?? 0) ?></h4>
                  <p class="text-muted">Dropped</p>
                </div>
              </div>
              <div class="col-md-2">
                <div class="text-center">
                  <?php 
                  $total_in = $data_points[count($data_points)-1]['packets_in'] ?? 0;
                  $dropped = $data_points[count($data_points)-1]['dropped'] ?? 0;
                  $drop_rate = $total_in > 0 ? ($dropped / $total_in) * 100 : 0;
                  ?>
                  <h4 class="<?= $drop_rate > 5 ? 'text-danger' : 'text-success' ?>"><?= number_format($drop_rate, 2) ?>%</h4>
                  <p class="text-muted">Drop Rate</p>
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
      
      // Bandwidth Graph
      const bandwidthCtx = document.getElementById('bandwidthGraph').getContext('2d');
      new Chart(bandwidthCtx, {
        type: 'line',
        data: {
          labels: dataPoints.map(dp => new Date(dp.ts * 1000).toLocaleTimeString()),
          datasets: [{
            label: 'Bytes In',
            data: dataPoints.map(dp => dp.bytes_in),
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            fill: false
          }, {
            label: 'Bytes Out',
            data: dataPoints.map(dp => dp.bytes_out),
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            fill: false
          }]
        },
        options: {
          responsive: true,
          plugins: {
            title: { display: true, text: 'Queue Bandwidth' }
          },
          scales: {
            y: { beginAtZero: true }
          }
        }
      });
      
      // Packets Graph
      const packetsCtx = document.getElementById('packetsGraph').getContext('2d');
      new Chart(packetsCtx, {
        type: 'line',
        data: {
          labels: dataPoints.map(dp => new Date(dp.ts * 1000).toLocaleTimeString()),
          datasets: [{
            label: 'Packets In',
            data: dataPoints.map(dp => dp.packets_in),
            borderColor: 'rgba(255, 159, 64, 1)',
            backgroundColor: 'rgba(255, 159, 64, 0.1)',
            fill: false
          }, {
            label: 'Packets Out',
            data: dataPoints.map(dp => dp.packets_out),
            borderColor: 'rgba(153, 102, 255, 1)',
            backgroundColor: 'rgba(153, 102, 255, 0.1)',
            fill: false
          }, {
            label: 'Dropped',
            data: dataPoints.map(dp => dp.dropped),
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            fill: false
          }]
        },
        options: {
          responsive: true,
          plugins: {
            title: { display: true, text: 'Queue Packets' }
          },
          scales: {
            y: { beginAtZero: true }
          }
        }
      });
    </script>
  <?php elseif ($device_ip): ?>
    <div class="alert alert-info">
      No queue data available. Click "Poll Data" to collect SNMP queue statistics first.
    </div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php'; 