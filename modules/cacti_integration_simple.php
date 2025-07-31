<?php
require_once 'module_loader.php';


// Simple Cacti Integration Module
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cacti Integration - Simple</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #1a1a1a; color: #ffffff; }
        .card { background-color: #2d2d2d; border: 1px solid #404040; }
        .card-header { background-color: #333333; border-bottom: 1px solid #404040; }
        .table { color: #ffffff; }
        .table th { background-color: #333333; }
        .table td { border-color: #404040; }
        .status-up { color: #28a745; }
        .status-down { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .cacti-frame { width: 100%; height: 600px; border: 1px solid #404040; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1><i class="bi bi-graph-up"></i> Cacti Integration - Simple</h1>
                <p class="text-muted">Integration with Cacti monitoring system</p>
            </div>
        </div>

        <div class="row">
            <!-- Cacti Status -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle"></i> Cacti Status</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Check if Cacti is accessible
                        $cactiUrl = 'http://localhost/cacti/';
                        $cactiResponse = @file_get_contents($cactiUrl);
                        
                        if ($cactiResponse !== false) {
                            echo '<div class="alert alert-success">';
                            echo '<i class="bi bi-check-circle"></i> <strong>Cacti is accessible</strong><br>';
                            echo '<small>URL: ' . htmlspecialchars($cactiUrl) . '</small>';
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-danger">';
                            echo '<i class="bi bi-x-circle"></i> <strong>Cacti is not accessible</strong><br>';
                            echo '<small>Check if Cacti is running and accessible</small>';
                            echo '</div>';
                        }
                        ?>
                        
                        <div class="mt-3">
                            <a href="<?php echo htmlspecialchars($cactiUrl); ?>" target="_blank" class="btn btn-primary">
                                <i class="bi bi-box-arrow-up-right"></i> Open Cacti
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Integration -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-hdd-network"></i> Device Integration</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $pdo = get_pdo();
                            $stmt = $pdo->query("SELECT id, name, ip_address, type, status FROM devices WHERE ip_address IS NOT NULL AND ip_address != '' LIMIT 10");
                            $devices = $stmt->fetchAll();
                            
                            if ($devices): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Device</th>
                                                <th>IP</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Cacti</th>
                                                <th>SNMP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($devices as $device): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($device['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($device['ip_address']); ?></td>
                                                    <td><?php echo htmlspecialchars($device['type']); ?></td>
                                                    <td>
                                                        <span class="status-<?php echo $device['status'] === 'online' ? 'up' : 'down'; ?>">
                                                            <i class="bi bi-circle-fill"></i> <?php echo htmlspecialchars($device['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-success" onclick="addToCacti('<?php echo htmlspecialchars($device['name']); ?>', '<?php echo htmlspecialchars($device['ip_address']); ?>')">
                                                            <i class="bi bi-plus"></i> Add
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-info" onclick="testSNMP('<?php echo htmlspecialchars($device['ip_address']); ?>')">
                                                            <i class="bi bi-cpu"></i> Test
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No devices with IP addresses found.</p>
                            <?php endif;
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cacti Integration Form -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-gear"></i> Add Device to Cacti</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="device_name" class="form-label">Device Name</label>
                                    <input type="text" class="form-control" id="device_name" name="device_name" value="<?php echo $_POST['device_name'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="device_ip" class="form-label">IP Address</label>
                                    <input type="text" class="form-control" id="device_ip" name="device_ip" value="<?php echo $_POST['device_ip'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="snmp_community" class="form-label">SNMP Community</label>
                                    <input type="text" class="form-control" id="snmp_community" name="snmp_community" value="<?php echo $_POST['snmp_community'] ?? 'public'; ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="device_type" class="form-label">Device Type</label>
                                    <select class="form-control" id="device_type" name="device_type">
                                        <option value="router">Router</option>
                                        <option value="switch">Switch</option>
                                        <option value="server">Server</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" name="action" value="test_snmp" class="btn btn-info">
                                    <i class="bi bi-cpu"></i> Test SNMP
                                </button>
                                <button type="submit" name="action" value="add_device" class="btn btn-success">
                                    <i class="bi bi-plus"></i> Add to Cacti
                                </button>
                            </div>
                        </form>

                        <?php if (isset($_POST['action'])): ?>
                            <hr>
                            <h6>Results:</h6>
                            <?php
                            $deviceName = $_POST['device_name'] ?? '';
                            $deviceIp = $_POST['device_ip'] ?? '';
                            $snmpCommunity = $_POST['snmp_community'] ?? '';
                            
                            if ($_POST['action'] === 'test_snmp' && $deviceIp && $snmpCommunity) {
                                $command = "snmpget -v 2c -c " . escapeshellarg($snmpCommunity) . " " . escapeshellarg($deviceIp) . " .1.3.6.1.2.1.1.1.0 2>&1";
                                $output = shell_exec($command);
                                
                                if ($output) {
                                    echo '<div class="alert alert-success">';
                                    echo '<strong>SNMP Test Successful:</strong><br>';
                                    echo '<pre>' . htmlspecialchars($output) . '</pre>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="alert alert-danger">';
                                    echo '<strong>SNMP Test Failed:</strong> No response from device';
                                    echo '</div>';
                                }
                            } elseif ($_POST['action'] === 'add_device') {
                                echo '<div class="alert alert-info">';
                                echo '<strong>Device Ready for Cacti:</strong><br>';
                                echo 'Name: ' . htmlspecialchars($deviceName) . '<br>';
                                echo 'IP: ' . htmlspecialchars($deviceIp) . '<br>';
                                echo 'SNMP Community: ' . htmlspecialchars($snmpCommunity) . '<br>';
                                echo '<br><strong>Next Steps:</strong><br>';
                                echo '1. Open Cacti in a new tab<br>';
                                echo '2. Go to Devices → Add<br>';
                                echo '3. Use the information above to add the device';
                                echo '</div>';
                            }
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cacti Dashboard Embed -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-graph-up"></i> Cacti Dashboard</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <a href="<?php echo htmlspecialchars($cactiUrl); ?>" target="_blank" class="btn btn-primary">
                                <i class="bi bi-box-arrow-up-right"></i> Open Cacti in New Tab
                            </a>
                        </div>
                        
                        <?php if ($cactiResponse !== false): ?>
                            <iframe src="<?php echo htmlspecialchars($cactiUrl); ?>" class="cacti-frame"></iframe>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> Cacti is not accessible. Please check if Cacti is running.
                            </div>
                        <?php endif; ?>
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
                <a href="snmp_monitoring_simple.php" class="btn btn-info">
                    <i class="bi bi-cpu"></i> SNMP Monitoring
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addToCacti(name, ip) {
            document.getElementById('device_name').value = name;
            document.getElementById('device_ip').value = ip;
            document.getElementById('snmp_community').value = 'public';
        }
        
        function testSNMP(ip) {
            document.getElementById('device_ip').value = ip;
            document.getElementById('snmp_community').value = 'public';
        }
    </script>
</body>
</html> 