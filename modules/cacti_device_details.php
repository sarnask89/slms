<?php
require_once 'module_loader.php';

require_once __DIR__ . '/../modules/helpers/auth_helper.php';
require_once __DIR__ . '/cacti_api.php';

// Require login
require_login();

$pdo = get_pdo();
$message = '';
$device_data = null;
$graphs = [];
$cacti_status = ['success' => false, 'message' => 'Cacti API not available'];

// Get hostname from query parameter
$hostname = $_GET['hostname'] ?? '';
$device_id = $_GET['device_id'] ?? '';

if (empty($hostname) && empty($device_id)) {
    $message = 'No device specified. Please provide hostname or device_id parameter.';
} else {
    try {
        // Initialize Cacti API
        $cacti_api = new CactiAPI();
        $cacti_status = $cacti_api->getStatus();
        
        if ($cacti_status['success']) {
            // Get device data
            if (!empty($device_id)) {
                $device_data = $cacti_api->getDevice($device_id);
            } else {
                // Search for device by hostname
                $devices = $cacti_api->getDevices();
                if (isset($devices['devices'])) {
                    foreach ($devices['devices'] as $device) {
                        if ($device['hostname'] === $hostname) {
                            $device_data = $device;
                            $device_id = $device['id'];
                            break;
                        }
                    }
                }
            }
            
            // Get device graphs if device found
            if ($device_data && isset($device_id)) {
                $graphs_response = $cacti_api->getDeviceGraphs($device_id);
                if (isset($graphs_response['graphs'])) {
                    $graphs = $graphs_response['graphs'];
                }
            }
        }
    } catch (Exception $e) {
        $message = 'Error accessing Cacti API: ' . $e->getMessage();
    }
}

// Set page title
$pageTitle = 'Cacti Device Details';

// Start output buffering for layout system
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-hdd-network"></i> Cacti Device Details
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="cacti_integration.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Cacti
            </a>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Cacti Status -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Cacti Status
                </h5>
            </div>
            <div class="card-body">
                <?php if ($cacti_status['success']): ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="status-indicator status-up"></span>
                        <strong>Cacti is running</strong>
                    </div>
                    <p class="text-muted mb-0">
                        API is accessible and responding correctly.
                        <?php if ($cacti_api->isMockMode()): ?>
                            <span class="badge bg-warning">Mock Mode</span>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="status-indicator status-down"></span>
                        <strong>Cacti is not accessible</strong>
                    </div>
                    <p class="text-muted mb-0">
                        <?php echo htmlspecialchars($cacti_status['message'] ?? 'Unknown error'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($device_data): ?>
    <!-- Device Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-hdd-network"></i> Device Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Hostname:</strong></td>
                            <td><?php echo htmlspecialchars($device_data['hostname'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>IP Address:</strong></td>
                            <td><?php echo htmlspecialchars($device_data['ip_address'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <?php 
                                $status = $device_data['status'] ?? 'unknown';
                                $status_class = $status === 'up' ? 'success' : ($status === 'down' ? 'danger' : 'warning');
                                ?>
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>SNMP Community:</strong></td>
                            <td><?php echo htmlspecialchars($device_data['snmp_community'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>SNMP Version:</strong></td>
                            <td><?php echo htmlspecialchars($device_data['snmp_version'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Template:</strong></td>
                            <td><?php echo htmlspecialchars($device_data['template_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Last Polled:</strong></td>
                            <td><?php echo htmlspecialchars($device_data['last_polled'] ?? 'N/A'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-activity"></i> Device Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-primary"><?php echo count($graphs); ?></h4>
                                <small class="text-muted">Total Graphs</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-success"><?php echo $device_data['data_sources'] ?? 0; ?></h4>
                                <small class="text-muted">Data Sources</small>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-info"><?php echo $device_data['interfaces'] ?? 0; ?></h4>
                                <small class="text-muted">Interfaces</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-warning"><?php echo $device_data['uptime'] ?? 'N/A'; ?></h4>
                                <small class="text-muted">Uptime</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Graphs -->
    <?php if (!empty($graphs)): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up"></i> Device Graphs (<?php echo count($graphs); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($graphs as $graph): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <?php echo htmlspecialchars($graph['title'] ?? 'Untitled Graph'); ?>
                                            </h6>
                                            <p class="card-text text-muted">
                                                <?php echo htmlspecialchars($graph['description'] ?? 'No description available'); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    Last Updated: <?php echo htmlspecialchars($graph['last_updated'] ?? 'N/A'); ?>
                                                </small>
                                                <span class="badge bg-<?php echo ($graph['status'] ?? 'unknown') === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($graph['status'] ?? 'unknown'); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-grid">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewGraph('<?php echo $graph['id']; ?>', '<?php echo htmlspecialchars($graph['title']); ?>')">
                                                    <i class="bi bi-eye"></i> View Graph
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No Graphs Available</h5>
                        <p class="text-muted">This device doesn't have any graphs configured yet.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Device Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear"></i> Device Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-primary w-100" onclick="pingDevice('<?php echo htmlspecialchars($device_data['hostname']); ?>')">
                                <i class="bi bi-wifi"></i> Ping Device
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-success w-100" onclick="refreshDevice('<?php echo $device_id; ?>')">
                                <i class="bi bi-arrow-clockwise"></i> Refresh Data
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info w-100" onclick="viewSNMP('<?php echo htmlspecialchars($device_data['hostname']); ?>')">
                                <i class="bi bi-search"></i> SNMP Query
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-warning w-100" onclick="editDevice('<?php echo $device_id; ?>')">
                                <i class="bi bi-pencil"></i> Edit Device
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- No Device Found -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Device Not Found</h5>
                    <p class="text-muted">
                        The device with hostname "<?php echo htmlspecialchars($hostname); ?>" was not found in Cacti.
                    </p>
                    <div class="mt-3">
                        <a href="cacti_integration.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Back to Cacti Integration
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Graph Modal -->
<div class="modal fade" id="graphModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="graphModalTitle">Graph Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="graphModalContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading graph data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}
.status-indicator.status-up {
    background-color: #28a745;
}
.status-indicator.status-down {
    background-color: #dc3545;
}
.status-indicator.status-unknown {
    background-color: #ffc107;
}
</style>

<script>
function viewGraph(graphId, graphTitle) {
    document.getElementById('graphModalTitle').textContent = graphTitle;
    document.getElementById('graphModalContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading graph data...</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('graphModal'));
    modal.show();
    
    // Simulate loading graph data (in real implementation, this would fetch from Cacti API)
    setTimeout(() => {
        document.getElementById('graphModalContent').innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Graph ID: ${graphId}<br>
                Title: ${graphTitle}<br><br>
                <strong>Note:</strong> This is a mock implementation. In a real Cacti integration, 
                this would display the actual graph image or embed the Cacti graph URL.
            </div>
            <div class="text-center">
                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmOWZhIi8+CiAgPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzZjNzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkdyYXBoIFBsYWNlaG9sZGVyPC90ZXh0Pgo8L3N2Zz4K" 
                     alt="Graph Placeholder" class="img-fluid" style="max-width: 100%; height: auto;">
            </div>
        `;
    }, 1000);
}

function pingDevice(hostname) {
    alert(`Pinging device: ${hostname}\n\nThis would perform an actual ping test in a real implementation.`);
}

function refreshDevice(deviceId) {
    alert(`Refreshing device data for ID: ${deviceId}\n\nThis would trigger a Cacti poller refresh in a real implementation.`);
}

function viewSNMP(hostname) {
    alert(`SNMP Query for device: ${hostname}\n\nThis would perform SNMP queries in a real implementation.`);
}

function editDevice(deviceId) {
    alert(`Edit device with ID: ${deviceId}\n\nThis would open the Cacti device edit page in a real implementation.`);
}
</script>

<?php
$content = ob_get_clean();
require_once '../partials/layout.php';
?> 