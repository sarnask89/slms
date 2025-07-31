<?php
require_once __DIR__ . '/../config.php';
$pdo = get_pdo();
$oids = require __DIR__ . '/snmp_oid_helper.php';

// Handle form
$device_ip = $_POST['device_ip'] ?? '';
$oid = $_POST['oid'] ?? '';
$custom_oid = $_POST['custom_oid'] ?? '';
$selected_category = $_POST['category'] ?? '';
$selected_oid = $oid === 'custom' ? $custom_oid : $oid;

// Get available device IPs from skeleton_devices
$device_ips = $pdo->query("SELECT DISTINCT ip_address FROM skeleton_devices ORDER BY ip_address")->fetchAll(PDO::FETCH_COLUMN);

// Group OIDs by category
$categories = [];
foreach ($oids as $oid_val => $info) {
    $cat = $info['category'];
    if (!isset($categories[$cat])) {
        $categories[$cat] = [];
    }
    $categories[$cat][$oid_val] = $info;
}

// Filter OIDs by selected category
$filtered_oids = $selected_category ? ($categories[$selected_category] ?? []) : $oids;

// Fetch data for graph
$data_points = [];
if ($device_ip && $selected_oid) {
    $stmt = $pdo->prepare("SELECT UNIX_TIMESTAMP(polled_at) as ts, value FROM snmp_graph_data WHERE device_ip = ? AND oid = ? ORDER BY polled_at ASC");
    $stmt->execute([$device_ip, $selected_oid]);
    $data_points = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

ob_start();
?>
<div class="container py-4">
  <h2>SNMP Graphing & Monitoring</h2>
  
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Quick Categories</h5>
        </div>
        <div class="card-body">
          <div class="btn-group" role="group">
            <a href="?category=" class="btn btn-outline-primary <?= !$selected_category ? 'active' : '' ?>">All</a>
            <a href="?category=interface" class="btn btn-outline-primary <?= $selected_category === 'interface' ? 'active' : '' ?>">Interfaces</a>
            <a href="?category=queue" class="btn btn-outline-primary <?= $selected_category === 'queue' ? 'active' : '' ?>">Queues</a>
            <a href="?category=system" class="btn btn-outline-primary <?= $selected_category === 'system' ? 'active' : '' ?>">System</a>
            <a href="?category=health" class="btn btn-outline-primary <?= $selected_category === 'health' ? 'active' : '' ?>">Health</a>
            <a href="?category=wireless" class="btn btn-outline-primary <?= $selected_category === 'wireless' ? 'active' : '' ?>">Wireless</a>
          </div>
        </div>
      </div>
    </div>
  </div>

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
      <label class="form-label">Category</label>
      <select name="category" class="form-select" id="category-select">
        <option value="">-- All Categories --</option>
        <?php foreach (array_keys($categories) as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>" <?= $selected_category === $cat ? 'selected' : '' ?>><?= ucfirst(htmlspecialchars($cat)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">SNMP OID</label>
      <select name="oid" class="form-select" id="oid-select">
        <option value="">-- Select OID --</option>
        <?php foreach ($filtered_oids as $oid_val => $info): ?>
          <option value="<?= htmlspecialchars($oid_val) ?>" <?= $oid === $oid_val ? 'selected' : '' ?>><?= htmlspecialchars($info['name']) ?></option>
        <?php endforeach; ?>
        <option value="custom" <?= $oid === 'custom' ? 'selected' : '' ?>>Custom OID...</option>
      </select>
    </div>
    <div class="col-md-2" id="custom-oid-group" style="display: <?= $oid === 'custom' ? 'block' : 'none' ?>;">
      <label class="form-label">Custom OID</label>
      <input type="text" name="custom_oid" class="form-control" value="<?= htmlspecialchars($custom_oid) ?>" placeholder="e.g. 1.3.6.1.2.1.2.2.1.10.1">
    </div>
    <div class="col-md-12">
      <button type="submit" class="btn btn-primary">Show Graph</button>
      <a href="snmp_graph_poll.php" class="btn btn-success">Poll SNMP Data</a>
    </div>
  </form>

  <?php if ($device_ip && $selected_oid): ?>
    <div class="card mb-4">
      <div class="card-header">
        <strong>Device:</strong> <?= htmlspecialchars($device_ip) ?>
        <br><strong>OID:</strong> <?= htmlspecialchars($selected_oid) ?>
        <?php if (isset($oids[$selected_oid])): ?>
          <br><strong>Name:</strong> <?= htmlspecialchars($oids[$selected_oid]['name']) ?>
          <br><strong>Description:</strong> <?= htmlspecialchars($oids[$selected_oid]['desc']) ?>
          <br><strong>Category:</strong> <?= ucfirst(htmlspecialchars($oids[$selected_oid]['category'])) ?>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if (empty($data_points)): ?>
          <div class="alert alert-info">
            No data available for this OID. Click "Poll SNMP Data" to collect data first.
          </div>
        <?php else: ?>
          <canvas id="snmpGraph" height="100"></canvas>
        <?php endif; ?>
      </div>
    </div>
    
    <?php if (!empty($data_points)): ?>
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <script>
        const dataPoints = <?= json_encode($data_points) ?>;
        const ctx = document.getElementById('snmpGraph').getContext('2d');
        const chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: dataPoints.map(dp => new Date(dp.ts * 1000).toLocaleString()),
            datasets: [{
              label: 'SNMP Value',
              data: dataPoints.map(dp => parseFloat(dp.value)),
              borderColor: 'rgba(54, 162, 235, 1)',
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
              fill: true,
              tension: 0.1
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { display: true },
              title: { display: true, text: 'SNMP OID Value Over Time' }
            },
            scales: {
              x: { display: true, title: { display: true, text: 'Time' } },
              y: { display: true, title: { display: true, text: 'Value' } }
            }
          }
        });
      </script>
    <?php endif; ?>
  <?php endif; ?>

  <script>
    // Show/hide custom OID field
    document.getElementById('oid-select').addEventListener('change', function() {
      document.getElementById('custom-oid-group').style.display = this.value === 'custom' ? 'block' : 'none';
    });
    
    // Filter OIDs by category
    document.getElementById('category-select').addEventListener('change', function() {
      const category = this.value;
      const oidSelect = document.getElementById('oid-select');
      const currentValue = oidSelect.value;
      
      // Clear current options
      oidSelect.innerHTML = '<option value="">-- Select OID --</option>';
      
      // Add filtered options
      const categories = <?= json_encode($categories) ?>;
      const filteredOids = category ? (categories[category] || {}) : <?= json_encode($oids) ?>;
      
      Object.keys(filteredOids).forEach(oid => {
        const option = document.createElement('option');
        option.value = oid;
        option.textContent = filteredOids[oid].name;
        if (oid === currentValue) option.selected = true;
        oidSelect.appendChild(option);
      });
      
      // Add custom option
      const customOption = document.createElement('option');
      customOption.value = 'custom';
      customOption.textContent = 'Custom OID...';
      if (currentValue === 'custom') customOption.selected = true;
      oidSelect.appendChild(customOption);
    });
  </script>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php'; 