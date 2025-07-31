<?php
require_once 'module_loader.php';


// Simple SNMP Monitoring Module
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SNMP Monitoring - Simple</title>
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
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1><i class="bi bi-cpu"></i> SNMP Monitoring - Simple</h1>
                <p class="text-muted">Real-time SNMP monitoring for network devices</p>
            </div>
        </div>

        <div class="row">
            <!-- SNMP Test Section -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-gear"></i> SNMP Test</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="host" class="form-label">Host/IP Address</label>
                                <input type="text" class="form-control" id="host" name="host" value="<?php echo $_POST['host'] ?? '127.0.0.1'; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="community" class="form-label">Community String</label>
                                <input type="text" class="form-control" id="community" name="community" value="<?php echo $_POST['community'] ?? 'public'; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="oid" class="form-label">OID</label>
                                <input type="text" class="form-control" id="oid" name="oid" value="<?php echo $_POST['oid'] ?? '.1.3.6.1.2.1.1.1.0'; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test SNMP</button>
                        </form>

                        <?php if ($_POST): ?>
                            <hr>
                            <h6>SNMP Test Results:</h6>
                            <?php
                            $host = $_POST['host'] ?? '';
                            $community = $_POST['community'] ?? '';
                            $oid = $_POST['oid'] ?? '';
                            
                            if ($host && $community && $oid) {
                                $command = "snmpget -v 2c -c " . escapeshellarg($community) . " " . escapeshellarg($host) . " " . escapeshellarg($oid) . " 2>&1";
                                $output = shell_exec($command);
                                
                                if ($output) {
                                    echo '<div class="alert alert-success">';
                                    echo '<strong>Success:</strong><br>';
                                    echo '<pre>' . htmlspecialchars($output) . '</pre>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="alert alert-danger">';
                                    echo '<strong>Error:</strong> No response from SNMP agent';
                                    echo '</div>';
                                }
                            }
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Device Monitoring -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-hdd-network"></i> Device Status</h5>
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
                                                        <button class="btn btn-sm btn-outline-primary" onclick="testSNMP('<?php echo htmlspecialchars($device['ip_address']); ?>')">
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

        <!-- SNMP Walk Results -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-list-ul"></i> SNMP Walk Results</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="walk_host" placeholder="Host" value="<?php echo $_POST['walk_host'] ?? '127.0.0.1'; ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="walk_community" placeholder="Community" value="<?php echo $_POST['walk_community'] ?? 'public'; ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="walk_oid" placeholder="OID (optional)" value="<?php echo $_POST['walk_oid'] ?? '.1.3.6.1.2.1.1'; ?>">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" name="action" value="walk" class="btn btn-secondary">SNMP Walk</button>
                                <button type="submit" name="action" value="system" class="btn btn-info">System Info</button>
                                <button type="submit" name="action" value="interfaces" class="btn btn-warning">Interfaces</button>
                            </div>
                        </form>

                        <?php if (isset($_POST['action']) && $_POST['action'] === 'walk'): ?>
                            <hr>
                            <h6>SNMP Walk Results:</h6>
                            <?php
                            $host = $_POST['walk_host'] ?? '';
                            $community = $_POST['walk_community'] ?? '';
                            $oid = $_POST['walk_oid'] ?? '.1.3.6.1.2.1.1';
                            
                            if ($host && $community) {
                                $command = "snmpwalk -v 2c -c " . escapeshellarg($community) . " " . escapeshellarg($host) . " " . escapeshellarg($oid) . " 2>&1";
                                $output = shell_exec($command);
                                
                                if ($output) {
                                    echo '<div class="alert alert-info">';
                                    echo '<pre style="max-height: 300px; overflow-y: auto;">' . htmlspecialchars($output) . '</pre>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="alert alert-danger">No response from SNMP agent</div>';
                                }
                            }
                            ?>
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
                <a href="cacti_integration.php" class="btn btn-success">
                    <i class="bi bi-graph-up"></i> Cacti Integration
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testSNMP(ip) {
            document.getElementById('host').value = ip;
            document.getElementById('community').value = 'public';
            document.getElementById('oid').value = '.1.3.6.1.2.1.1.1.0';
        }
    </script>
</body>
</html> 