<?php
session_start();
require_once 'module_loader.php';

require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login
require_login();

$pageTitle = 'Dhcp Clients V7';
ob_start();
?>

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'module_loader.php';

require_once __DIR__ . '/mikrotik_rest_api_v7.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$pdo = get_pdo();
$dhcp_clients = [];
$devices = [];
$errors = [];
$debug_info = [];
$success_messages = [];

// Get skeleton devices for connection testing
$stmt = $pdo->prepare("SELECT * FROM skeleton_devices WHERE type = 'mikrotik' ORDER BY name");
$stmt->execute();
$skeleton_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['test_connection'])) {
        $device_id = $_POST['device_id'] ?? null;
        if ($device_id) {
            $stmt = $pdo->prepare("SELECT * FROM skeleton_devices WHERE id = ?");
            $stmt->execute([$device_id]);
            $device = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($device) {
                try {
                    $api = new MikroTikRestAPIv7(
                        $device['ip_address'],
                        $device['username'] ?? 'admin',
                        $device['password'] ?? '',
                        $device['port'] ?? 443,
                        $device['ssl'] ?? true
                    );
                    
                    $test_result = $api->testConnection();
                    if ($test_result['success']) {
                        $success_messages[] = "✅ Connection successful to {$device['name']} ({$device['ip_address']})";
                        
                        // Get DHCP info
                        $dhcp_info = $api->getDhcpInfo();
                        $debug_info[] = "Found " . count($dhcp_info['leases']) . " DHCP leases";
                        $debug_info[] = "Found " . count($dhcp_info['networks']) . " DHCP networks";
                        $debug_info[] = "Found " . count($dhcp_info['servers']) . " DHCP servers";
                        
                        // Store DHCP info in session for display
                        $_SESSION['dhcp_info'] = $dhcp_info;
                        $_SESSION['current_device'] = $device;
                        
                    } else {
                        $errors[] = "❌ Connection failed: " . $test_result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = "❌ Error: " . $e->getMessage();
                }
            }
        }
    }
    
    if (isset($_POST['import_networks'])) {
        $device_id = $_POST['device_id'] ?? null;
        if ($device_id) {
            $stmt = $pdo->prepare("SELECT * FROM skeleton_devices WHERE id = ?");
            $stmt->execute([$device_id]);
            $device = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($device) {
                try {
                    $api = new MikroTikRestAPIv7(
                        $device['ip_address'],
                        $device['username'] ?? 'admin',
                        $device['password'] ?? '',
                        $device['port'] ?? 443,
                        $device['ssl'] ?? true
                    );
                    
                    $import_manager = new DhcpImportManager($pdo, $api);
                    $results = $import_manager->importDhcpNetworks($device_id);
                    
                    if ($results['success']) {
                        $success_messages[] = "✅ Networks imported successfully";
                        $success_messages[] = "Created: " . $results['networks_created'] . " networks";
                        $success_messages[] = "Updated: " . $results['networks_updated'] . " networks";
                    } else {
                        $errors[] = "❌ Network import failed";
                        foreach ($results['errors'] as $error) {
                            $errors[] = "Error: " . $error;
                        }
                    }
                    
                    foreach ($results['debug_info'] as $info) {
                        $debug_info[] = $info;
                    }
                    
                } catch (Exception $e) {
                    $errors[] = "❌ Error: " . $e->getMessage();
                }
            }
        }
    }
    
    if (isset($_POST['import_leases'])) {
        $device_id = $_POST['device_id'] ?? null;
        if ($device_id) {
            $stmt = $pdo->prepare("SELECT * FROM skeleton_devices WHERE id = ?");
            $stmt->execute([$device_id]);
            $device = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($device) {
                try {
                    $api = new MikroTikRestAPIv7(
                        $device['ip_address'],
                        $device['username'] ?? 'admin',
                        $device['password'] ?? '',
                        $device['port'] ?? 443,
                        $device['ssl'] ?? true
                    );
                    
                    $import_manager = new DhcpImportManager($pdo, $api);
                    $results = $import_manager->importDhcpLeases($device_id);
                    
                    if ($results['success']) {
                        $success_messages[] = "✅ DHCP leases imported successfully";
                        $success_messages[] = "Created: " . $results['clients_created'] . " clients";
                        $success_messages[] = "Created: " . $results['devices_created'] . " devices";
                        $success_messages[] = "Updated: " . $results['clients_updated'] . " clients";
                        $success_messages[] = "Updated: " . $results['devices_updated'] . " devices";
                    } else {
                        $errors[] = "❌ DHCP lease import failed";
                        foreach ($results['errors'] as $error) {
                            $errors[] = "Error: " . $error;
                        }
                    }
                    
                    foreach ($results['debug_info'] as $info) {
                        $debug_info[] = $info;
                    }
                    
                } catch (Exception $e) {
                    $errors[] = "❌ Error: " . $e->getMessage();
                }
            }
        }
    }
    
    if (isset($_POST['refresh_leases'])) {
        $device_id = $_POST['device_id'] ?? null;
        if ($device_id) {
            $stmt = $pdo->prepare("SELECT * FROM skeleton_devices WHERE id = ?");
            $stmt->execute([$device_id]);
            $device = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($device) {
                try {
                    $api = new MikroTikRestAPIv7(
                        $device['ip_address'],
                        $device['username'] ?? 'admin',
                        $device['password'] ?? '',
                        $device['port'] ?? 443,
                        $device['ssl'] ?? true
                    );
                    
                    $dhcp_info = $api->getDhcpInfo();
                    $_SESSION['dhcp_info'] = $dhcp_info;
                    $_SESSION['current_device'] = $device;
                    
                    $success_messages[] = "✅ DHCP information refreshed successfully";
                    $debug_info[] = "Found " . count($dhcp_info['leases']) . " DHCP leases";
                    $debug_info[] = "Found " . count($dhcp_info['networks']) . " DHCP networks";
                    $debug_info[] = "Found " . count($dhcp_info['servers']) . " DHCP servers";
                    
                } catch (Exception $e) {
                    $errors[] = "❌ Error refreshing DHCP info: " . $e->getMessage();
                }
            }
        }
    }
}

// Get current DHCP info from session if available
$dhcp_info = $_SESSION['dhcp_info'] ?? null;
$current_device = $_SESSION['current_device'] ?? null;

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM clients");
$stmt->execute();
$client_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM devices");
$stmt->execute();
$device_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM devices WHERE client_id IS NOT NULL");
$stmt->execute();
$assigned_device_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM devices WHERE network_id IS NOT NULL");
$stmt->execute();
$networked_device_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

include '../partials/layout.php';
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-wifi"></i> DHCP Clients Management (REST API v7)
          </h5>
          <div>
            <span class="badge bg-primary"><?= $client_count ?> clients</span>
            <span class="badge bg-info"><?= $device_count ?> devices</span>
            <span class="badge bg-success"><?= $assigned_device_count ?> assigned</span>
            <span class="badge bg-warning"><?= $networked_device_count ?> networked</span>
          </div>
        </div>
        <div class="card-body">
          
          <!-- Success Messages -->
          <?php if (!empty($success_messages)): ?>
            <div class="card mb-4">
              <div class="card-header">
                <h6 class="mb-0">Success</h6>
              </div>
              <div class="card-body">
                <?php foreach ($success_messages as $message): ?>
                  <div class="alert alert-success mb-2">
                    <?= htmlspecialchars($message) ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          
          <!-- Device Selection -->
          <div class="card mb-4">
            <div class="card-header">
              <h6 class="mb-0">MikroTik Device Selection</h6>
            </div>
            <div class="card-body">
              <form method="post" class="row g-3">
                <div class="col-md-6">
                  <label for="device_id" class="form-label">Select MikroTik Device</label>
                  <select name="device_id" id="device_id" class="form-select" required>
                    <option value="">Choose a device...</option>
                    <?php foreach ($skeleton_devices as $device): ?>
                      <option value="<?= $device['id'] ?>" <?= ($current_device && $current_device['id'] == $device['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($device['name']) ?> (<?= $device['ip_address'] ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                  <button type="submit" name="test_connection" class="btn btn-primary me-2">
                    <i class="bi bi-wifi"></i> Test Connection
                  </button>
                  <button type="submit" name="refresh_leases" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                  </button>
                  <button type="submit" name="import_networks" class="btn btn-info me-2">
                    <i class="bi bi-diagram-3"></i> Import Networks
                  </button>
                  <button type="submit" name="import_leases" class="btn btn-success">
                    <i class="bi bi-people"></i> Import Leases
                  </button>
                </div>
              </form>
            </div>
          </div>
          
          <!-- Debug Information -->
          <?php if (!empty($debug_info)): ?>
            <div class="card mb-4">
              <div class="card-header">
                <h6 class="mb-0">Debug Information</h6>
              </div>
              <div class="card-body">
                <?php foreach ($debug_info as $info): ?>
                  <div class="alert alert-info mb-2">
                    <?= htmlspecialchars($info) ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          
          <!-- Errors -->
          <?php if (!empty($errors)): ?>
            <div class="card mb-4">
              <div class="card-header">
                <h6 class="mb-0">Errors</h6>
              </div>
              <div class="card-body">
                <?php foreach ($errors as $error): ?>
                  <div class="alert alert-danger mb-2">
                    <?= htmlspecialchars($error) ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          
          <!-- DHCP Information Display -->
          <?php if ($dhcp_info): ?>
            <!-- DHCP Servers -->
            <div class="card mb-4">
              <div class="card-header">
                <h6 class="mb-0">DHCP Servers</h6>
              </div>
              <div class="card-body">
                <?php if (!empty($dhcp_info['servers'])): ?>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>Interface</th>
                          <th>Address Pool</th>
                          <th>Lease Time</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($dhcp_info['servers'] as $server): ?>
                          <tr>
                            <td><?= htmlspecialchars($server['name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($server['interface'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($server['address-pool'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($server['lease-time'] ?? 'N/A') ?></td>
                            <td>
                              <span class="badge bg-success">Active</span>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning">No DHCP servers found</div>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- DHCP Networks -->
            <div class="card mb-4">
              <div class="card-header">
                <h6 class="mb-0">DHCP Networks</h6>
              </div>
              <div class="card-body">
                <?php if (!empty($dhcp_info['networks'])): ?>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Address</th>
                          <th>Gateway</th>
                          <th>DNS Server</th>
                          <th>Domain</th>
                          <th>Comment</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($dhcp_info['networks'] as $network): ?>
                          <tr>
                            <td><?= htmlspecialchars($network['address'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($network['gateway'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($network['dns-server'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($network['domain'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($network['comment'] ?? 'N/A') ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning">No DHCP networks found</div>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- DHCP Leases -->
            <div class="card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">DHCP Leases</h6>
                <div>
                  <span class="badge bg-primary"><?= count($dhcp_info['leases']) ?> leases</span>
                </div>
              </div>
              <div class="card-body">
                <?php if (!empty($dhcp_info['leases'])): ?>
                  <div class="table-responsive">
                    <table class="table table-striped" id="leases-table">
                      <thead>
                        <tr>
                          <th>Address</th>
                          <th>MAC Address</th>
                          <th>Client ID</th>
                          <th>Status</th>
                          <th>Server</th>
                          <th>Last Seen</th>
                          <th>Expires After</th>
                          <th>Comment</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($dhcp_info['leases'] as $lease): ?>
                          <tr>
                            <td>
                              <code><?= htmlspecialchars($lease['address'] ?? 'N/A') ?></code>
                            </td>
                            <td>
                              <code><?= htmlspecialchars($lease['mac-address'] ?? 'N/A') ?></code>
                            </td>
                            <td><?= htmlspecialchars($lease['client-id'] ?? 'N/A') ?></td>
                            <td>
                              <?php
                              $status = $lease['status'] ?? 'unknown';
                              $status_class = $status === 'bound' ? 'success' : ($status === 'offered' ? 'warning' : 'secondary');
                              ?>
                              <span class="badge bg-<?= $status_class ?>"><?= htmlspecialchars($status) ?></span>
                            </td>
                            <td><?= htmlspecialchars($lease['server'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($lease['last-seen'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($lease['expires-after'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($lease['comment'] ?? 'N/A') ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning">No DHCP leases found</div>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- System Statistics -->
            <?php if (!empty($dhcp_info['statistics'])): ?>
              <div class="card mb-4">
                <div class="card-header">
                  <h6 class="mb-0">System Statistics</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-3">
                      <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                          <h4><?= number_format($dhcp_info['statistics']['uptime'] ?? 0) ?></h4>
                          <small>Uptime (seconds)</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="card bg-success text-white">
                        <div class="card-body text-center">
                          <h4><?= number_format($dhcp_info['statistics']['cpu_load'] ?? 0, 1) ?>%</h4>
                          <small>CPU Load</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="card bg-info text-white">
                        <div class="card-body text-center">
                          <h4><?= number_format(($dhcp_info['statistics']['free_memory'] ?? 0) / 1024 / 1024, 1) ?> MB</h4>
                          <small>Free Memory</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                          <h4><?= number_format(($dhcp_info['statistics']['free_hdd_space'] ?? 0) / 1024 / 1024, 1) ?> MB</h4>
                          <small>Free HDD Space</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> Select a MikroTik device and test connection to view DHCP information.
            </div>
          <?php endif; ?>
          
          <!-- Quick Actions -->
          <div class="row mt-4">
            <div class="col-md-6">
              <a href="/slms/modules/clients.php" class="btn btn-secondary">
                <i class="bi bi-people"></i> View All Clients
              </a>
              <a href="/slms/modules/devices.php" class="btn btn-success">
                <i class="bi bi-hdd-network"></i> View All Devices
              </a>
            </div>
            <div class="col-md-6">
              <a href="/slms/modules/networks.php" class="btn btn-info">
                <i class="bi bi-diagram-3"></i> View Networks
              </a>
              <a href="/slms/modules/skeleton_devices.php" class="btn btn-warning">
                <i class="bi bi-gear"></i> Manage Devices
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Add search functionality for DHCP leases table
$(document).ready(function() {
    // Add search input
    if ($('#leases-table').length) {
        $('#leases-table').before(`
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="lease-search" class="form-control" placeholder="Search leases...">
                </div>
                <div class="col-md-6">
                    <button id="clear-search" class="btn btn-secondary">Clear Search</button>
                </div>
            </div>
        `);
        
        // Search functionality
        $('#lease-search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#leases-table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Clear search
        $('#clear-search').click(function() {
            $('#lease-search').val('');
            $('#leases-table tbody tr').show();
        });
    }
});
</script>


<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?>
