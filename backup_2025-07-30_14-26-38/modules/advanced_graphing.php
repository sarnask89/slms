<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Advanced Network Graphing';
$pdo = get_pdo();
$errors = [];
$success = '';

// Get devices and interfaces for selection
$devices = $pdo->query("SELECT id, name FROM skeleton_devices ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get available interfaces for selected device
$interfaces = [];
if (isset($_GET['device_id']) && $_GET['device_id']) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT interface_name 
        FROM interface_stats 
        WHERE device_id = ? 
        ORDER BY interface_name
    ");
    $stmt->execute([$_GET['device_id']]);
    $interfaces = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

ob_start();
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="bi bi-graph-up-arrow"></i> Advanced Network Graphing
          </h5>
        </div>
        <div class="card-body">
          
          <!-- Chart Controls -->
          <div class="row mb-4">
            <div class="col-md-3">
              <label class="form-label">Device</label>
              <select id="device-select" class="form-select" onchange="loadInterfaces()">
                <option value="">Select Device</option>
                <?php foreach ($devices as $dev): ?>
                  <option value="<?= $dev['id'] ?>" <?= (isset($_GET['device_id']) && $_GET['device_id'] == $dev['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($dev['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Interface</label>
              <select id="interface-select" class="form-select" onchange="updateChart()">
                <option value="">Select Interface</option>
                <?php foreach ($interfaces as $iface): ?>
                  <option value="<?= htmlspecialchars($iface) ?>" <?= (isset($_GET['iface']) && $_GET['iface'] === $iface) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($iface) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Time Range</label>
              <select id="time-range" class="form-select" onchange="updateChart()">
                <option value="1h">Last Hour</option>
                <option value="6h">Last 6 Hours</option>
                <option value="24h" selected>Last 24 Hours</option>
                <option value="7d">Last 7 Days</option>
                <option value="30d">Last 30 Days</option>
                <option value="custom">Custom Range</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Chart Type</label>
              <select id="chart-type" class="form-select" onchange="updateChart()">
                <option value="line">Line Chart</option>
                <option value="area">Area Chart</option>
                <option value="bar">Bar Chart</option>
                <option value="scatter">Scatter Plot</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <div>
                <button class="btn btn-primary" onclick="updateChart()">
                  <i class="bi bi-arrow-clockwise"></i> Update
                </button>
                <button class="btn btn-success" onclick="startRealTime()">
                  <i class="bi bi-play-circle"></i> Real-time
                </button>
              </div>
            </div>
          </div>
          
          <!-- Custom Date Range (hidden by default) -->
          <div id="custom-date-range" class="row mb-4" style="display: none;">
            <div class="col-md-3">
              <label class="form-label">Start Date</label>
              <input type="datetime-local" id="start-date" class="form-control" onchange="updateChart()">
            </div>
            <div class="col-md-3">
              <label class="form-label">End Date</label>
              <input type="datetime-local" id="end-date" class="form-control" onchange="updateChart()">
            </div>
            <div class="col-md-6">
              <label class="form-label">&nbsp;</label>
              <div>
                <button class="btn btn-secondary" onclick="setQuickRange('today')">Today</button>
                <button class="btn btn-secondary" onclick="setQuickRange('yesterday')">Yesterday</button>
                <button class="btn btn-secondary" onclick="setQuickRange('week')">This Week</button>
                <button class="btn btn-secondary" onclick="setQuickRange('month')">This Month</button>
              </div>
            </div>
          </div>
          
          <!-- Chart Container -->
          <div class="row">
            <div class="col-md-8">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Interface Statistics</h6>
                </div>
                <div class="card-body">
                  <canvas id="mainChart" width="400" height="200"></canvas>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Statistics Summary</h6>
                </div>
                <div class="card-body">
                  <div id="stats-summary">
                    <p class="text-muted">Select device and interface to view statistics</p>
                  </div>
                </div>
              </div>
              
              <div class="card mt-3">
                <div class="card-header">
                  <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                  <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="exportChart()">
                    <i class="bi bi-download"></i> Export Chart
                  </button>
                  <button class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="printChart()">
                    <i class="bi bi-printer"></i> Print Chart
                  </button>
                  <button class="btn btn-outline-info btn-sm w-100 mb-2" onclick="shareChart()">
                    <i class="bi bi-share"></i> Share Chart
                  </button>
                  <button class="btn btn-outline-warning btn-sm w-100" onclick="resetChart()">
                    <i class="bi bi-arrow-clockwise"></i> Reset View
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Additional Charts -->
          <div class="row mt-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Bandwidth Distribution</h6>
                </div>
                <div class="card-body">
                  <canvas id="distributionChart" width="400" height="200"></canvas>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Peak Usage Analysis</h6>
                </div>
                <div class="card-body">
                  <canvas id="peakChart" width="400" height="200"></canvas>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Quick Actions -->
          <div class="row mt-4">
            <div class="col-md-6">
              <a href="network_dashboard.php" class="btn btn-primary">
                <i class="bi bi-graph-up"></i> Basic Dashboard
              </a>
              <a href="network_monitoring_enhanced.php" class="btn btn-secondary">
                <i class="bi bi-activity"></i> Enhanced Monitoring
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let mainChart, distributionChart, peakChart;
let realTimeInterval = null;

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadInterfaces();
});

function initializeCharts() {
    // Main chart
    const mainCtx = document.getElementById('mainChart').getContext('2d');
    mainChart = new Chart(mainCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'RX (Mbps)',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'TX (Mbps)',
                data: [],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Bandwidth (Mbps)'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Interface Bandwidth Usage'
                }
            }
        }
    });
    
    // Distribution chart
    const distCtx = document.getElementById('distributionChart').getContext('2d');
    distributionChart = new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['RX', 'TX'],
            datasets: [{
                data: [0, 0],
                backgroundColor: ['rgba(75, 192, 192, 0.8)', 'rgba(255, 99, 132, 0.8)']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Traffic Distribution'
                }
            }
        }
    });
    
    // Peak chart
    const peakCtx = document.getElementById('peakChart').getContext('2d');
    peakChart = new Chart(peakCtx, {
        type: 'bar',
        data: {
            labels: ['Peak RX', 'Peak TX', 'Average RX', 'Average TX'],
            datasets: [{
                label: 'Mbps',
                data: [0, 0, 0, 0],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(75, 192, 192, 0.4)',
                    'rgba(255, 99, 132, 0.4)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Peak vs Average Usage'
                }
            }
        }
    });
}

function loadInterfaces() {
    const deviceId = document.getElementById('device-select').value;
    if (!deviceId) {
        document.getElementById('interface-select').innerHTML = '<option value="">Select Interface</option>';
        return;
    }
    
    fetch(`network_monitoring_api.php?device_id=${deviceId}&action=interfaces`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('interface-select');
            select.innerHTML = '<option value="">Select Interface</option>';
            data.forEach(iface => {
                select.innerHTML += `<option value="${iface}">${iface}</option>`;
            });
        })
        .catch(error => console.error('Error loading interfaces:', error));
}

function updateChart() {
    const deviceId = document.getElementById('device-select').value;
    const interface = document.getElementById('interface-select').value;
    const timeRange = document.getElementById('time-range').value;
    const chartType = document.getElementById('chart-type').value;
    
    if (!deviceId || !interface) {
        alert('Please select both device and interface');
        return;
    }
    
    let url = `network_monitoring_api.php?device_id=${deviceId}&iface=${encodeURIComponent(interface)}`;
    
    if (timeRange === 'custom') {
        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;
        if (startDate && endDate) {
            url += `&start_date=${startDate}&end_date=${endDate}`;
        }
    } else {
        url += `&time_range=${timeRange}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            updateMainChart(data, chartType);
            updateDistributionChart(data);
            updatePeakChart(data);
            updateStatsSummary(data);
        })
        .catch(error => {
            console.error('Error updating chart:', error);
            alert('Error loading chart data');
        });
}

function updateMainChart(data, chartType) {
    mainChart.config.type = chartType;
    
    mainChart.data.labels = data.timestamps;
    mainChart.data.datasets[0].data = data.rx_bytes.map(b => (b * 8) / 1000000); // Convert to Mbps
    mainChart.data.datasets[1].data = data.tx_bytes.map(b => (b * 8) / 1000000);
    
    mainChart.update();
}

function updateDistributionChart(data) {
    const totalRx = data.rx_bytes.reduce((a, b) => a + b, 0);
    const totalTx = data.tx_bytes.reduce((a, b) => a + b, 0);
    
    distributionChart.data.datasets[0].data = [totalRx, totalTx];
    distributionChart.update();
}

function updatePeakChart(data) {
    const rxMbps = data.rx_bytes.map(b => (b * 8) / 1000000);
    const txMbps = data.tx_bytes.map(b => (b * 8) / 1000000);
    
    const peakRx = Math.max(...rxMbps);
    const peakTx = Math.max(...txMbps);
    const avgRx = rxMbps.reduce((a, b) => a + b, 0) / rxMbps.length;
    const avgTx = txMbps.reduce((a, b) => a + b, 0) / txMbps.length;
    
    peakChart.data.datasets[0].data = [peakRx, peakTx, avgRx, avgTx];
    peakChart.update();
}

function updateStatsSummary(data) {
    const rxMbps = data.rx_bytes.map(b => (b * 8) / 1000000);
    const txMbps = data.tx_bytes.map(b => (b * 8) / 1000000);
    
    const avgRx = (rxMbps.reduce((a, b) => a + b, 0) / rxMbps.length).toFixed(2);
    const avgTx = (txMbps.reduce((a, b) => a + b, 0) / txMbps.length).toFixed(2);
    const peakRx = Math.max(...rxMbps).toFixed(2);
    const peakTx = Math.max(...txMbps).toFixed(2);
    const totalRx = (rxMbps.reduce((a, b) => a + b, 0) / 1024).toFixed(2); // GB
    const totalTx = (txMbps.reduce((a, b) => a + b, 0) / 1024).toFixed(2);
    
    document.getElementById('stats-summary').innerHTML = `
        <div class="row text-center">
            <div class="col-6">
                <h6>RX</h6>
                <p class="mb-1"><strong>${avgRx}</strong> Mbps avg</p>
                <p class="mb-1"><strong>${peakRx}</strong> Mbps peak</p>
                <p class="mb-1"><strong>${totalRx}</strong> GB total</p>
            </div>
            <div class="col-6">
                <h6>TX</h6>
                <p class="mb-1"><strong>${avgTx}</strong> Mbps avg</p>
                <p class="mb-1"><strong>${peakTx}</strong> Mbps peak</p>
                <p class="mb-1"><strong>${totalTx}</strong> GB total</p>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <small class="text-muted">Data points: ${data.timestamps.length}</small>
        </div>
    `;
}

function startRealTime() {
    if (realTimeInterval) {
        clearInterval(realTimeInterval);
        realTimeInterval = null;
        document.querySelector('button[onclick="startRealTime()"]').innerHTML = '<i class="bi bi-play-circle"></i> Real-time';
        return;
    }
    
    document.querySelector('button[onclick="startRealTime()"]').innerHTML = '<i class="bi bi-stop-circle"></i> Stop';
    realTimeInterval = setInterval(updateChart, 30000); // Update every 30 seconds
}

function setQuickRange(range) {
    const now = new Date();
    let start, end;
    
    switch(range) {
        case 'today':
            start = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            end = now;
            break;
        case 'yesterday':
            start = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
            end = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            break;
        case 'week':
            start = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
            end = now;
            break;
        case 'month':
            start = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 30);
            end = now;
            break;
    }
    
    document.getElementById('start-date').value = start.toISOString().slice(0, 16);
    document.getElementById('end-date').value = end.toISOString().slice(0, 16);
    updateChart();
}

function exportChart() {
    const canvas = document.getElementById('mainChart');
    const link = document.createElement('a');
    link.download = 'network_chart.png';
    link.href = canvas.toDataURL();
    link.click();
}

function printChart() {
    window.print();
}

function shareChart() {
    const deviceId = document.getElementById('device-select').value;
    const interface = document.getElementById('interface-select').value;
    const url = `${window.location.origin}${window.location.pathname}?device_id=${deviceId}&iface=${encodeURIComponent(interface)}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Network Chart',
            text: 'Check out this network interface chart',
            url: url
        });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            alert('Chart URL copied to clipboard!');
        });
    }
}

function resetChart() {
    document.getElementById('time-range').value = '24h';
    document.getElementById('chart-type').value = 'line';
    document.getElementById('custom-date-range').style.display = 'none';
    updateChart();
}

// Handle custom time range visibility
document.getElementById('time-range').addEventListener('change', function() {
    const customRange = document.getElementById('custom-date-range');
    if (this.value === 'custom') {
        customRange.style.display = 'block';
    } else {
        customRange.style.display = 'none';
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 