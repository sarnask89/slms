<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'module_loader.php';

require_once __DIR__ . '/../modules/helpers/auth_helper.php';
require_once __DIR__ . '/cacti_api.php';

// Require login
require_login();

// Initialize Cacti API
$cacti_api = new CactiAPI();
$is_mock_mode = $cacti_api->isMockMode();

// Handle form submissions
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_device':
                $hostname = $_POST['hostname'] ?? '';
                $community = $_POST['community'] ?? 'public';
                $version = $_POST['version'] ?? '2';
                
                if ($hostname) {
                    $result = cacti_add_device($hostname, $community, $version);
                    if ($result['success']) {
                        $success_message = "Device $hostname added successfully to Cacti!";
                    } else {
                        $error_message = "Failed to add device: " . $result['error'];
                    }
                }
                break;
        }
    }
}

// Get current devices
$devices = [];
$cacti_status = ['success' => false, 'error' => 'Connection failed'];

try {
    $result = $cacti_api->getDevices();
    if (isset($result['devices'])) {
        $devices = $result['devices'];
        $cacti_status = ['success' => true];
    }
} catch (Exception $e) {
    $error_message = "Failed to get devices: " . $e->getMessage();
}

// Get Cacti status
if (!isset($cacti_status['success'])) {
    $cacti_status = cacti_check_status();
}

// Test direct SNMP connectivity
$snmp_test_result = null;
if (isset($_POST['test_snmp'])) {
    $test_host = $_POST['test_host'] ?? '10.0.222.86';
    $test_community = $_POST['test_community'] ?? 'public';
    
    $snmp_result = @snmpget($test_host, $test_community, '.1.3.6.1.2.1.1.1.0');
    if ($snmp_result !== false) {
        $snmp_test_result = ['success' => true, 'data' => $snmp_result];
    } else {
        $snmp_test_result = ['success' => false, 'error' => 'SNMP connection failed'];
    }
}

$pageTitle = 'Cacti Integration';
ob_start();
?>

<style>
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    .status-up { background-color: #28a745; }
    .status-down { background-color: #dc3545; }
    .status-warning { background-color: #ffc107; }
    .device-card {
        transition: transform 0.2s;
    }
    .device-card:hover {
        transform: translateY(-2px);
    }
    .snmp-test-result {
        font-family: monospace;
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-graph-up"></i> Cacti Integration
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="http://localhost:8081" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right"></i> Open Cacti
                        </a>
                        <a href="../test_cacti_integration.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-gear"></i> Test Integration
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Alert -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Cacti Status -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-info-circle"></i> Cacti Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($is_mock_mode): ?>
                                <div class="alert alert-warning mb-3">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Mock Mode Active:</strong> Running with simulated Cacti data for testing purposes.
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($cacti_status['success']): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="status-indicator status-up"></span>
                                    <strong>Cacti is running</strong>
                                    <?php if ($is_mock_mode): ?>
                                        <span class="badge bg-warning ms-2">Mock Mode</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted mb-0">
                                    API is accessible and responding to requests.
                                    <?php if ($is_mock_mode): ?>
                                        <br><small>Using simulated data for demonstration.</small>
                                    <?php endif; ?>
                                </p>
                            <?php else: ?>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="status-indicator status-down"></span>
                                    <strong>Cacti is not accessible</strong>
                                </div>
                                <p class="text-muted mb-0">
                                    Error: <?php echo htmlspecialchars($cacti_status['error']); ?>
                                </p>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong>Troubleshooting:</strong><br>
                                        1. Check if Cacti is running: <code>sudo systemctl status cacti</code><br>
                                        2. Check Cacti logs: <code>sudo tail -f /var/log/cacti/cacti.log</code><br>
                                        3. Restart Cacti: <code>sudo systemctl restart cacti</code>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-plus-circle"></i> Add New Device
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="add_device">
                                <div class="mb-3">
                                    <label for="hostname" class="form-label">Device Hostname/IP</label>
                                    <input type="text" class="form-control" id="hostname" name="hostname" 
                                           placeholder="10.0.222.86" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="community" class="form-label">SNMP Community</label>
                                        <input type="text" class="form-control" id="community" name="community" 
                                               value="public" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="version" class="form-label">SNMP Version</label>
                                        <select class="form-select" id="version" name="version">
                                            <option value="1">SNMP v1</option>
                                            <option value="2" selected>SNMP v2c</option>
                                            <option value="3">SNMP v3</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="bi bi-plus"></i> Add Device
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SNMP Test -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-wifi"></i> SNMP Connectivity Test
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="test_snmp" value="1">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="test_host" class="form-label">Device IP</label>
                                        <input type="text" class="form-control" id="test_host" name="test_host" 
                                               value="10.0.222.86" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="test_community" class="form-label">SNMP Community</label>
                                        <input type="text" class="form-control" id="test_community" name="test_community" 
                                               value="public" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-outline-primary d-block">
                                            <i class="bi bi-search"></i> Test SNMP
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <?php if ($snmp_test_result): ?>
                                <div class="snmp-test-result">
                                    <?php if ($snmp_test_result['success']): ?>
                                        <div class="text-success">
                                            <i class="bi bi-check-circle"></i> <strong>SNMP Test Successful</strong>
                                        </div>
                                        <div class="mt-2">
                                            <strong>System Description:</strong><br>
                                            <?php echo htmlspecialchars($snmp_test_result['data']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-danger">
                                            <i class="bi bi-x-circle"></i> <strong>SNMP Test Failed</strong>
                                        </div>
                                        <div class="mt-2">
                                            <strong>Error:</strong> <?php echo htmlspecialchars($snmp_test_result['error']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Devices List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-hdd-network"></i> Monitored Devices
                        <span class="badge bg-secondary ms-2"><?php echo count($devices); ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($devices)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-hdd-network text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">
                                <?php if (!$cacti_status['success']): ?>
                                    No devices found. Cacti is not accessible. Please check the status above.
                                <?php else: ?>
                                    No devices found. Add your first device above.
                                <?php endif; ?>
                            </p>
                            <?php if (!$cacti_status['success']): ?>
                                <div class="mt-3">
                                    <a href="../test_cacti_integration.php" class="btn btn-outline-primary">
                                        <i class="bi bi-gear"></i> Run Diagnostic Test
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($devices as $device): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card device-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="status-indicator <?php echo $device['status'] === 'up' ? 'status-up' : 'status-down'; ?>"></span>
                                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($device['hostname']); ?></h6>
                                            </div>
                                            <p class="card-text text-muted small">
                                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($device['location'] ?? 'Unknown'); ?><br>
                                                <i class="bi bi-tag"></i> <?php echo htmlspecialchars($device['sysName'] ?? $device['hostname']); ?><br>
                                                <i class="bi bi-clock"></i> Last poll: <?php echo $device['last_polled'] ?? 'Never'; ?>
                                            </p>
                                            <div class="btn-group btn-group-sm w-100">
                                                <a href="cacti_device_details.php?hostname=<?php echo urlencode($device['hostname']); ?>" 
                                                   class="btn btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Details
                                                </a>
                                                <a href="http://localhost:8081/cacti/graph_view.php?action=preview&local_graph_id=<?php echo $device['id']; ?>" 
                                                   target="_blank" class="btn btn-outline-secondary">
                                                    <i class="bi bi-box-arrow-up-right"></i> Cacti
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?> 