<?php
require_once __DIR__ . '/../config.php';
$pdo = get_pdo();
$pageTitle = 'Network Dashboard';

// Get all devices and interfaces
$devices = $pdo->query("SELECT id, name FROM skeleton_devices ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$selected_device = isset($_GET['device_id']) ? (int)$_GET['device_id'] : ($devices[0]['id'] ?? 0);
$ifaces = [];
if ($selected_device) {
    $stmt = $pdo->prepare("SELECT DISTINCT interface_name FROM interface_stats WHERE device_id = ? ORDER BY interface_name");
    $stmt->execute([$selected_device]);
    $ifaces = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
$selected_iface = isset($_GET['iface']) ? $_GET['iface'] : ($ifaces[0] ?? '');

ob_start();
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0"><i class="bi bi-graph-up"></i> Network Dashboard</h5>
        </div>
        <div class="card-body">
          <form method="get" class="row g-2 mb-3">
            <div class="col-md-4">
              <select name="device_id" class="form-select" onchange="this.form.submit()">
                <?php foreach ($devices as $dev): ?>
                  <option value="<?= $dev['id'] ?>" <?= $dev['id'] == $selected_device ? 'selected' : '' ?>><?= htmlspecialchars($dev['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <select name="iface" class="form-select" onchange="this.form.submit()">
                <?php foreach ($ifaces as $iface): ?>
                  <option value="<?= htmlspecialchars($iface) ?>" <?= $iface == $selected_iface ? 'selected' : '' ?>><?= htmlspecialchars($iface) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
          <div>
            <canvas id="ifaceGraph" height="100"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function fetchAndDraw() {
  fetch('network_monitoring_api.php?device_id=<?= $selected_device ?>&iface=<?= urlencode($selected_iface) ?>')
    .then(r => r.json())
    .then(data => {
      const ctx = document.getElementById('ifaceGraph').getContext('2d');
      if (window.ifaceChart) window.ifaceChart.destroy();
      window.ifaceChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.timestamps,
          datasets: [
            { label: 'RX (bps)', data: data.rx_bytes, borderColor: 'blue', fill: false },
            { label: 'TX (bps)', data: data.tx_bytes, borderColor: 'red', fill: false }
          ]
        },
        options: {
          responsive: true,
          plugins: { legend: { position: 'top' } },
          scales: { x: { display: true, title: { display: true, text: 'Time' } }, y: { beginAtZero: true } }
        }
      });
    });
}
fetchAndDraw();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php'; 