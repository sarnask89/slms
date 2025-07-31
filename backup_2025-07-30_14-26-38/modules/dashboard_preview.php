<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/cacti_api.php';

$pdo = get_pdo();
$dashboard_config = [];

// Load dashboard configuration
try {
    $stmt = $pdo->query("SELECT * FROM dashboard_config WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($config) {
        $dashboard_config = json_decode($config['config'], true) ?: [];
    }
} catch (Exception $e) {
    // Default configuration if none exists
    $dashboard_config = [
        'cacti' => [
            'devices' => true,
            'graphs' => true,
            'status' => true
        ],
        'snmp' => [
            'monitoring' => true,
            'graphs' => true,
            'alerts' => true
        ],
        'layout' => [
            'theme' => 'default',
            'columns' => 2,
            'refresh_interval' => 30
        ]
    ];
}

// Get Cacti data
$cacti_api = new CactiAPI();
$cacti_status = $cacti_api->getStatus();
$devices = [];

if ($cacti_status['success']) {
    try {
        $result = $cacti_api->getDevices();
        if (isset($result['devices'])) {
            $devices = $result['devices'];
        }
    } catch (Exception $e) {
        // Handle error silently
    }
}

// Get SNMP data
$snmp_devices = [];
try {
    $stmt = $pdo->query("SELECT * FROM skeleton_devices LIMIT 5");
    $snmp_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Handle error silently
}

$pageTitle = 'Dashboard Preview';
ob_start();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - sLMS</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
    <style>
        .dashboard-widget {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .widget-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-up { background-color: #28a745; }
        .status-down { background-color: #dc3545; }
        .status-warning { background-color: #ffc107; }
        .device-card {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        .refresh-info {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 1000;
        }
        .theme-default { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .theme-dark { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); }
        .theme-light { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .theme-blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../partials/layout.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-eye"></i> Dashboard Preview
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="dashboard_editor.php" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-palette"></i> Edit Dashboard
                            </a>
                            <a href="../admin_menu.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Admin
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Refresh Info -->
                <div class="refresh-info">
                    <i class="bi bi-arrow-clockwise"></i> Auto-refresh: <?php echo $dashboard_config['layout']['refresh_interval'] ?? 30; ?>s
                </div>

                <div class="row">
                    <?php if ($dashboard_config['cacti']['status'] ?? true): ?>
                    <!-- Cacti Status Widget -->
                    <div class="col-md-<?php echo 12 / ($dashboard_config['layout']['columns'] ?? 2); ?>">
                        <div class="dashboard-widget">
                            <div class="widget-header">
                                <h5><i class="bi bi-graph-up text-danger"></i> Cacti Status</h5>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="status-indicator <?php echo $cacti_status['success'] ? 'status-up' : 'status-down'; ?>"></span>
                                <strong><?php echo $cacti_status['success'] ? 'Cacti is running' : 'Cacti is not accessible'; ?></strong>
                            </div>
                            <?php if ($cacti_status['success'] && isset($cacti_status['data'])): ?>
                                <p class="text-muted mb-0">
                                    Version: <?php echo htmlspecialchars($cacti_status['data']['version'] ?? 'Unknown'); ?><br>
                                    Devices: <?php echo count($devices); ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted mb-0">
                                    Error: <?php echo htmlspecialchars($cacti_status['error'] ?? 'Connection failed'); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($dashboard_config['snmp']['monitoring'] ?? true): ?>
                    <!-- SNMP Monitoring Widget -->
                    <div class="col-md-<?php echo 12 / ($dashboard_config['layout']['columns'] ?? 2); ?>">
                        <div class="dashboard-widget">
                            <div class="widget-header">
                                <h5><i class="bi bi-activity text-warning"></i> SNMP Monitoring</h5>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="status-indicator status-up"></span>
                                <strong>SNMP Active</strong>
                            </div>
                            <p class="text-muted mb-0">
                                Monitored Devices: <?php echo count($snmp_devices); ?><br>
                                Last Update: <?php echo date('H:i:s'); ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <?php if ($dashboard_config['cacti']['devices'] ?? true): ?>
                    <!-- Cacti Devices Widget -->
                    <div class="col-md-<?php echo 12 / ($dashboard_config['layout']['columns'] ?? 2); ?>">
                        <div class="dashboard-widget">
                            <div class="widget-header">
                                <h5><i class="bi bi-hdd-network text-danger"></i> Cacti Devices</h5>
                            </div>
                            <?php if (empty($devices)): ?>
                                <p class="text-muted">No devices found in Cacti.</p>
                            <?php else: ?>
                                <?php foreach (array_slice($devices, 0, 3) as $device): ?>
                                    <div class="device-card">
                                        <div class="d-flex align-items-center">
                                            <span class="status-indicator <?php echo $device['status'] === 'up' ? 'status-up' : 'status-down'; ?>"></span>
                                            <div>
                                                <strong><?php echo htmlspecialchars($device['hostname']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($device['location'] ?? 'Unknown location'); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($devices) > 3): ?>
                                    <p class="text-muted small">... and <?php echo count($devices) - 3; ?> more devices</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($dashboard_config['snmp']['graphs'] ?? true): ?>
                    <!-- SNMP Graphs Widget -->
                    <div class="col-md-<?php echo 12 / ($dashboard_config['layout']['columns'] ?? 2); ?>">
                        <div class="dashboard-widget">
                            <div class="widget-header">
                                <h5><i class="bi bi-bar-chart text-warning"></i> SNMP Graphs</h5>
                            </div>
                            <div class="text-center py-4">
                                <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">SNMP Graph Data</p>
                                <div class="progress mb-2">
                                    <div class="progress-bar" role="progressbar" style="width: 75%"></div>
                                </div>
                                <small class="text-muted">CPU Usage: 75%</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <?php if ($dashboard_config['cacti']['graphs'] ?? true): ?>
                    <!-- Cacti Graphs Widget -->
                    <div class="col-md-<?php echo 12 / ($dashboard_config['layout']['columns'] ?? 2); ?>">
                        <div class="dashboard-widget">
                            <div class="widget-header">
                                <h5><i class="bi bi-graph-up text-danger"></i> Cacti Network Graphs</h5>
                            </div>
                            <div class="text-center py-4">
                                <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">Network Traffic Graphs</p>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-muted">Inbound</small><br>
                                        <strong>2.5 Mbps</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Outbound</small><br>
                                        <strong>1.8 Mbps</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($dashboard_config['snmp']['alerts'] ?? true): ?>
                    <!-- SNMP Alerts Widget -->
                    <div class="col-md-<?php echo 12 / ($dashboard_config['layout']['columns'] ?? 2); ?>">
                        <div class="dashboard-widget">
                            <div class="widget-header">
                                <h5><i class="bi bi-exclamation-triangle text-warning"></i> SNMP Alerts</h5>
                            </div>
                            <div class="alert alert-warning alert-sm">
                                <i class="bi bi-exclamation-triangle"></i>
                                High CPU usage detected on Router-01
                            </div>
                            <div class="alert alert-info alert-sm">
                                <i class="bi bi-info-circle"></i>
                                Interface eth0/1 status changed to up
                            </div>
                            <div class="alert alert-success alert-sm">
                                <i class="bi bi-check-circle"></i>
                                All systems operational
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Configuration Summary -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-gear"></i> Dashboard Configuration</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Theme:</strong> <?php echo ucfirst($dashboard_config['layout']['theme'] ?? 'default'); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Columns:</strong> <?php echo $dashboard_config['layout']['columns'] ?? 2; ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Refresh:</strong> <?php echo $dashboard_config['layout']['refresh_interval'] ?? 30; ?>s
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Components:</strong> 
                                        <?php 
                                        $enabled_components = 0;
                                        foreach ($dashboard_config['cacti'] ?? [] as $enabled) if ($enabled) $enabled_components++;
                                        foreach ($dashboard_config['snmp'] ?? [] as $enabled) if ($enabled) $enabled_components++;
                                        echo $enabled_components;
                                        ?> enabled
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh functionality
        const refreshInterval = <?php echo ($dashboard_config['layout']['refresh_interval'] ?? 30) * 1000; ?>;
        
        if (refreshInterval > 0) {
            setInterval(function() {
                location.reload();
            }, refreshInterval);
        }

        // Update refresh info
        let countdown = <?php echo $dashboard_config['layout']['refresh_interval'] ?? 30; ?>;
        setInterval(function() {
            countdown--;
            if (countdown <= 0) {
                countdown = <?php echo $dashboard_config['layout']['refresh_interval'] ?? 30; ?>;
            }
            document.querySelector('.refresh-info').innerHTML = 
                '<i class="bi bi-arrow-clockwise"></i> Auto-refresh: ' + countdown + 's';
        }, 1000);
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?> 