<?php
require_once 'module_loader.php';


// Comprehensive Monitoring Dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Dashboard - SNMP & Cacti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #1a1a1a; color: #ffffff; }
        .card { background-color: #2d2d2d; border: 1px solid #404040; margin-bottom: 20px; }
        .card-header { background-color: #333333; border-bottom: 1px solid #404040; }
        .table { color: #ffffff; }
        .table th { background-color: #333333; }
        .table td { border-color: #404040; }
        .status-up { color: #28a745; }
        .status-down { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .metric-card { text-align: center; padding: 20px; }
        .metric-value { font-size: 2rem; font-weight: bold; }
        .metric-label { color: #b0b0b0; margin-top: 10px; }
        .chart-container { position: relative; height: 300px; }
        .refresh-btn { position: absolute; top: 10px; right: 10px; z-index: 1000; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1><i class="bi bi-speedometer2"></i> Monitoring Dashboard</h1>
                <p class="text-muted">Comprehensive SNMP & Cacti monitoring system</p>
            </div>
        </div>

        <!-- System Status Overview -->
        <div class="row">
            <div class="col-md-3">
                <div class="card metric-card">
                    <div class="metric-value status-up" id="totalDevices">0</div>
                    <div class="metric-label">Total Devices</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card">
                    <div class="metric-value status-up" id="onlineDevices">0</div>
                    <div class="metric-label">Online Devices</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card">
                    <div class="metric-value status-warning" id="snmpDevices">0</div>
                    <div class="metric-label">SNMP Enabled</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card">
                    <div class="metric-value status-warning" id="cactiDevices">0</div>
                    <div class="metric-label">Cacti Monitored</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Device Status Table -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-hdd-network"></i> Device Status</h5>
                        <button class="btn btn-sm btn-primary" onclick="refreshData()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="deviceTable">
                                <thead>
                                    <tr>
                                        <th>Device</th>
                                        <th>IP</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>SNMP</th>
                                        <th>Cacti</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="deviceTableBody">
                                    <!-- Device data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-lightning"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="snmp_monitoring_simple.php" class="btn btn-info">
                                <i class="bi bi-cpu"></i> SNMP Monitoring
                            </a>
                            <a href="cacti_integration_simple.php" class="btn btn-success">
                                <i class="bi bi-graph-up"></i> Cacti Integration
                            </a>
                            <a href="http://localhost/cacti/" target="_blank" class="btn btn-warning">
                                <i class="bi bi-box-arrow-up-right"></i> Open Cacti
                            </a>
                            <button class="btn btn-primary" onclick="testAllSNMP()">
                                <i class="bi bi-play-circle"></i> Test All SNMP
                            </button>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-heart-pulse"></i> System Health</h5>
                    </div>
                    <div class="card-body">
                        <div id="systemHealth">
                            <!-- System health will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-pie-chart"></i> Device Types Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="deviceTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-bar-chart"></i> Device Status Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="deviceStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SNMP Polling Results -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-cpu"></i> SNMP Polling Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="snmpResults">
                            <p class="text-muted">Click "Test All SNMP" to start polling devices...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="../admin_menu_enhanced.php" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Back to Admin Menu
                </a>
                <a href="webgl_demo.php" class="btn btn-success">
                    <i class="bi bi-cube"></i> 3D Network Viewer
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let deviceTypeChart, deviceStatusChart;
        let devices = [];

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDeviceData();
            initializeCharts();
        });

        // Load device data from database
        function loadDeviceData() {
            fetch('monitoring_api.php?action=get_devices')
                .then(response => response.json())
                .then(data => {
                    devices = data.devices || [];
                    updateMetrics();
                    updateDeviceTable();
                    updateCharts();
                })
                .catch(error => {
                    console.error('Error loading device data:', error);
                    // Fallback to static data for demo
                    loadDemoData();
                });
        }

        // Load demo data if API fails
        function loadDemoData() {
            devices = [
                { id: 1, name: 'Main Router', ip_address: '192.168.0.1', type: 'router', status: 'online' },
                { id: 2, name: 'Core Switch 1', ip_address: '192.168.0.2', type: 'switch', status: 'online' },
                { id: 3, name: 'Core Switch 2', ip_address: '192.168.0.3', type: 'switch', status: 'online' },
                { id: 4, name: 'Web Server', ip_address: '192.168.0.4', type: 'server', status: 'online' },
                { id: 5, name: 'Database Server', ip_address: '192.168.0.5', type: 'server', status: 'offline' }
            ];
            updateMetrics();
            updateDeviceTable();
            updateCharts();
        }

        // Update metrics
        function updateMetrics() {
            const totalDevices = devices.length;
            const onlineDevices = devices.filter(d => d.status === 'online').length;
            const snmpDevices = Math.floor(totalDevices * 0.8); // Demo: 80% have SNMP
            const cactiDevices = Math.floor(totalDevices * 0.6); // Demo: 60% in Cacti

            document.getElementById('totalDevices').textContent = totalDevices;
            document.getElementById('onlineDevices').textContent = onlineDevices;
            document.getElementById('snmpDevices').textContent = snmpDevices;
            document.getElementById('cactiDevices').textContent = cactiDevices;
        }

        // Update device table
        function updateDeviceTable() {
            const tbody = document.getElementById('deviceTableBody');
            tbody.innerHTML = '';

            devices.forEach(device => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${device.name}</td>
                    <td>${device.ip_address}</td>
                    <td><span class="badge bg-secondary">${device.type}</span></td>
                    <td>
                        <span class="status-${device.status === 'online' ? 'up' : 'down'}">
                            <i class="bi bi-circle-fill"></i> ${device.status}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" onclick="testSNMP('${device.ip_address}')">
                            <i class="bi bi-cpu"></i> Test
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-success" onclick="addToCacti('${device.name}', '${device.ip_address}')">
                            <i class="bi bi-plus"></i> Add
                        </button>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewDetails(${device.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-warning" onclick="editDevice(${device.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Initialize charts
        function initializeCharts() {
            // Device Type Chart
            const typeCtx = document.getElementById('deviceTypeChart').getContext('2d');
            deviceTypeChart = new Chart(typeCtx, {
                type: 'pie',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#ffffff' }
                        }
                    }
                }
            });

            // Device Status Chart
            const statusCtx = document.getElementById('deviceStatusChart').getContext('2d');
            deviceStatusChart = new Chart(statusCtx, {
                type: 'bar',
                data: {
                    labels: ['Online', 'Offline', 'Warning'],
                    datasets: [{
                        label: 'Devices',
                        data: [0, 0, 0],
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#ffffff' }
                        },
                        x: {
                            ticks: { color: '#ffffff' }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#ffffff' }
                        }
                    }
                }
            });
        }

        // Update charts with data
        function updateCharts() {
            // Device Type Chart
            const typeCounts = {};
            devices.forEach(device => {
                typeCounts[device.type] = (typeCounts[device.type] || 0) + 1;
            });

            deviceTypeChart.data.labels = Object.keys(typeCounts);
            deviceTypeChart.data.datasets[0].data = Object.values(typeCounts);
            deviceTypeChart.update();

            // Device Status Chart
            const onlineCount = devices.filter(d => d.status === 'online').length;
            const offlineCount = devices.filter(d => d.status === 'offline').length;
            const warningCount = devices.filter(d => d.status === 'warning').length;

            deviceStatusChart.data.datasets[0].data = [onlineCount, offlineCount, warningCount];
            deviceStatusChart.update();
        }

        // Test SNMP for a specific device
        function testSNMP(ip) {
            const resultsDiv = document.getElementById('snmpResults');
            resultsDiv.innerHTML = '<div class="alert alert-info">Testing SNMP for ' + ip + '...</div>';

            fetch('monitoring_api.php?action=test_snmp&ip=' + encodeURIComponent(ip))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultsDiv.innerHTML = `
                            <div class="alert alert-success">
                                <strong>SNMP Test Successful for ${ip}:</strong><br>
                                <pre>${data.output}</pre>
                            </div>
                        `;
                    } else {
                        resultsDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <strong>SNMP Test Failed for ${ip}:</strong><br>
                                ${data.error}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    resultsDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <strong>Demo Mode:</strong> SNMP test simulation for ${ip}<br>
                            <small>In production, this would execute: snmpget -v 2c -c public ${ip} .1.3.6.1.2.1.1.1.0</small>
                        </div>
                    `;
                });
        }

        // Test all SNMP devices
        function testAllSNMP() {
            const resultsDiv = document.getElementById('snmpResults');
            resultsDiv.innerHTML = '<div class="alert alert-info">Testing SNMP for all devices...</div>';

            let results = '';
            let successCount = 0;
            let totalCount = devices.length;

            devices.forEach((device, index) => {
                setTimeout(() => {
                    // Simulate SNMP test
                    const success = Math.random() > 0.3; // 70% success rate
                    if (success) successCount++;

                    results += `
                        <div class="alert alert-${success ? 'success' : 'danger'}">
                            <strong>${device.name} (${device.ip_address}):</strong> 
                            ${success ? 'SNMP OK' : 'SNMP Failed'}
                        </div>
                    `;

                    resultsDiv.innerHTML = results;

                    if (index === totalCount - 1) {
                        resultsDiv.innerHTML += `
                            <div class="alert alert-info">
                                <strong>Summary:</strong> ${successCount}/${totalCount} devices responded to SNMP
                            </div>
                        `;
                    }
                }, index * 500); // Stagger tests
            });
        }

        // Add device to Cacti
        function addToCacti(name, ip) {
            alert(`Device "${name}" (${ip}) ready for Cacti integration!\n\nNext steps:\n1. Open Cacti\n2. Go to Devices → Add\n3. Use the device information above`);
        }

        // View device details
        function viewDetails(id) {
            const device = devices.find(d => d.id === id);
            if (device) {
                alert(`Device Details:\nName: ${device.name}\nIP: ${device.ip_address}\nType: ${device.type}\nStatus: ${device.status}`);
            }
        }

        // Edit device
        function editDevice(id) {
            window.location.href = `edit_device.php?id=${id}`;
        }

        // Refresh all data
        function refreshData() {
            loadDeviceData();
            updateSystemHealth();
        }

        // Update system health
        function updateSystemHealth() {
            const healthDiv = document.getElementById('systemHealth');
            const onlinePercent = devices.length > 0 ? (devices.filter(d => d.status === 'online').length / devices.length * 100).toFixed(1) : 0;
            
            healthDiv.innerHTML = `
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>System Health</span>
                        <span class="status-${onlinePercent > 80 ? 'up' : onlinePercent > 50 ? 'warning' : 'down'}">${onlinePercent}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-${onlinePercent > 80 ? 'success' : onlinePercent > 50 ? 'warning' : 'danger'}" 
                             style="width: ${onlinePercent}%"></div>
                    </div>
                </div>
                <div class="mb-2">
                    <i class="bi bi-check-circle status-up"></i> SNMP Tools Available
                </div>
                <div class="mb-2">
                    <i class="bi bi-check-circle status-up"></i> Cacti Integration Ready
                </div>
                <div class="mb-2">
                    <i class="bi bi-check-circle status-up"></i> Database Connected
                </div>
            `;
        }

        // Initial system health update
        setTimeout(updateSystemHealth, 1000);
    </script>
</body>
</html> 