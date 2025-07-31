<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mikrotik_api.php';

$pageTitle = 'Import DHCP Clients (Improved, API/SSH, Preview/Import/Update)';
$pdo = get_pdo();

$import_results = [];
$errors = [];
$success_message = '';
$debug_info = [];
$preview_table = [];

// Helper: check if IP is in subnet
function ip_in_subnet($ip, $subnet) {
    if (empty($subnet) || strpos($subnet, '/') === false) return false;
    list($net, $mask) = explode('/', $subnet);
    if (!is_numeric($mask) || $mask < 0 || $mask > 32) return false;
    $ip_long = ip2long($ip);
    $net_long = ip2long($net);
    if ($ip_long === false || $net_long === false) return false;
    $mask_long = ~((1 << (32 - $mask)) - 1);
    return ($ip_long & $mask_long) === ($net_long & $mask_long);
}

// Helper: get all clients/devices/networks in memory for fast lookup
function get_lookup_tables($pdo) {
    $clients = [];
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM clients");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $clients[trim($row['first_name'] . ' ' . $row['last_name'])] = $row['id'];
    }
    $devices = [];
    $stmt = $pdo->query("SELECT id, mac_address FROM devices");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $devices[strtolower($row['mac_address'])] = $row['id'];
    }
    $networks = [];
    $stmt = $pdo->query("SELECT id, subnet, device_id FROM networks");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $networks[] = $row;
    }
    return [$clients, $devices, $networks];
}

// Main import logic
$mode = $_POST['mode'] ?? 'import_update'; // preview, import, update, import_update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_dhcp'])) {
    try {
        // Get skeleton devices
        $stmt = $pdo->prepare("SELECT id, name, ip_address, api_username, api_password FROM skeleton_devices WHERE api_username IS NOT NULL AND api_password IS NOT NULL ORDER BY name");
        $stmt->execute();
        $skeleton_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $debug_info[] = "Found " . count($skeleton_devices) . " skeleton devices with API credentials";
        if (empty($skeleton_devices)) throw new Exception('No skeleton devices with API credentials found');

        // Lookup tables
        list($clients, $devices, $networks) = get_lookup_tables($pdo);
        $created_clients = $created_devices = $updated_clients = $updated_devices = $skipped = $errors_count = 0;
        $actions = [];

        foreach ($skeleton_devices as $device) {
            $device_debug = [];
            $device_debug[] = "Processing device: {$device['name']} ({$device['ip_address']})";
            $leases = [];
            // Try API first
            try {
                $api = new MikroTikAPI($device['ip_address'], $device['api_username'], $device['api_password']);
                $api_leases = $api->getDhcpLeases();
                if (is_array($api_leases) && !isset($api_leases['error']) && count($api_leases) > 0) {
                    $leases = $api_leases;
                    $device_debug[] = "Fetched " . count($leases) . " leases via API.";
                }
            } catch (Exception $e) {
                $device_debug[] = "API error: " . $e->getMessage();
            }
            // Fallback to SSH if needed
            if (empty($leases)) {
                $ssh_output = $api->sshExecute('/ip dhcp-server lease print');
                if ($ssh_output && !str_contains($ssh_output, 'error')) {
                    $leases = $api->parseDhcpLeasesFromSsh($ssh_output);
                    $device_debug[] = "Fetched " . count($leases) . " leases via SSH.";
                } else {
                    $device_debug[] = "SSH fallback failed.";
                }
            }
            if (empty($leases)) {
                $device_debug[] = "No leases found for device.";
                $import_results[] = ['device' => $device['name'], 'status' => 'error', 'message' => 'No leases found'];
                $errors[] = "No leases found for device {$device['name']}";
                $errors_count++;
                $debug_info = array_merge($debug_info, $device_debug);
                continue;
            }
            foreach ($leases as $lease) {
                $lease_debug = [];
                $mac = strtolower($lease['mac-address'] ?? '');
                $ip = $lease['address'] ?? '';
                $comment = trim($lease['comment'] ?? '');
                $status = $lease['status'] ?? '';
                if (!$mac || !$ip) {
                    $lease_debug[] = "Skipping lease with missing MAC or IP.";
                    $skipped++;
                    continue;
                }
                // Skip generic comments
                $skip_names = ['unknown', 'router', 'switch', 'ap', 'access', 'point'];
                $comment_words = explode(' ', strtolower($comment));
                if (isset($comment_words[0]) && in_array($comment_words[0], $skip_names)) {
                    $lease_debug[] = "Skipping generic device: $comment";
                    $skipped++;
                    continue;
                }
                // Build client name
                $base_name = $comment_words[0] ?? 'Unknown';
                $address_part = $comment_words[1] ?? '';
                $client_name = trim($base_name . ' ' . $address_part);
                if (strlen($client_name) < 2) $client_name = $mac;
                // Find or create client
                $client_id = $clients[$client_name] ?? null;
                $client_action = '';
                if (!$client_id && ($mode === 'import' || $mode === 'import_update')) {
                    if ($mode !== 'preview') {
                        $stmt = $pdo->prepare("INSERT INTO clients (first_name, last_name, address, notes, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->execute([$base_name, $address_part, $comment, "Imported from DHCP lease - MAC: $mac - Device: {$device['name']}"]);
                        $client_id = $pdo->lastInsertId();
                        $clients[$client_name] = $client_id;
                        $created_clients++;
                        $client_action = 'created';
                    } else {
                        $client_action = 'would create';
                    }
                } elseif ($client_id && ($mode === 'update' || $mode === 'import_update')) {
                    // Optionally update client info here if needed
                    $client_action = 'exists';
                }
                // Find or create/update device
                $device_id = $devices[$mac] ?? null;
                $device_action = '';
                // Find network
                $network_id = null;
                foreach ($networks as $net) {
                    if ($net['device_id'] == $device['id'] && ip_in_subnet($ip, $net['subnet'])) {
                        $network_id = $net['id'];
                        break;
                    }
                }
                if (!$device_id && ($mode === 'import' || $mode === 'import_update')) {
                    if ($mode !== 'preview') {
                        $stmt = $pdo->prepare("INSERT INTO devices (name, type, ip_address, mac_address, location, client_id, network_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                        $dev_name = $client_name . ' - ' . $mac;
                        $stmt->execute([$dev_name, 'other', $ip, $mac, $comment, $client_id, $network_id]);
                        $device_id = $pdo->lastInsertId();
                        $devices[$mac] = $device_id;
                        $created_devices++;
                        $device_action = 'created';
                    } else {
                        $device_action = 'would create';
                    }
                } elseif ($device_id && ($mode === 'update' || $mode === 'import_update')) {
                    // Optionally update device info if needed
                    if ($mode !== 'preview') {
                        $stmt = $pdo->prepare("UPDATE devices SET client_id = ?, ip_address = ?, name = ?, network_id = ? WHERE id = ?");
                        $dev_name = $client_name . ' - ' . $mac;
                        $stmt->execute([$client_id, $ip, $dev_name, $network_id, $device_id]);
                        $updated_devices++;
                        $device_action = 'updated';
                    } else {
                        $device_action = 'would update';
                    }
                } else {
                    $device_action = 'skipped';
                    $skipped++;
                }
                $actions[] = [
                    'device' => $device['name'],
                    'mac' => $mac,
                    'ip' => $ip,
                    'comment' => $comment,
                    'client_action' => $client_action,
                    'device_action' => $device_action,
                    'network_id' => $network_id,
                ];
            }
            $debug_info = array_merge($debug_info, $device_debug);
        }
        // Build preview/summary table
        $preview_table = $actions;
        $success_message = "Mode: $mode. Created: $created_clients clients, $created_devices devices. Updated: $updated_clients clients, $updated_devices devices. Skipped: $skipped. Errors: $errors_count.";
    } catch (Exception $e) {
        $errors[] = "Import failed: " . $e->getMessage();
    }
}

// Handle clear clients/devices actions
if (isset($_POST['clear_clients'])) {
    $pdo->exec('DELETE FROM clients');
    $pdo->exec('ALTER TABLE clients AUTO_INCREMENT = 1');
    $success_message = 'All clients have been deleted.';
}
if (isset($_POST['clear_devices'])) {
    $pdo->exec('DELETE FROM devices');
    $pdo->exec('ALTER TABLE devices AUTO_INCREMENT = 1');
    $success_message = 'All devices have been deleted.';
}

// Get current statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
$total_clients = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) as total FROM devices");
$total_devices = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) as total FROM devices WHERE client_id IS NOT NULL");
$assigned_devices = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) as total FROM devices WHERE network_id IS NOT NULL");
$networked_devices = $stmt->fetchColumn();

ob_start();
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="bi bi-upload"></i> Import DHCP Clients (Improved, API/SSH, Preview/Import/Update)
          </h5>
        </div>
        <div class="card-body">
          <!-- Statistics -->
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="card bg-primary text-white">
                <div class="card-body text-center">
                  <h4><?= $total_clients ?></h4>
                  <small>Total Clients</small>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-success text-white">
                <div class="card-body text-center">
                  <h4><?= $total_devices ?></h4>
                  <small>Total Devices</small>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-info text-white">
                <div class="card-body text-center">
                  <h4><?= $assigned_devices ?></h4>
                  <small>Assigned Devices</small>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-warning text-white">
                <div class="card-body text-center">
                  <h4><?= $networked_devices ?></h4>
                  <small>Networked Devices</small>
                </div>
              </div>
            </div>
          </div>
          <!-- Import Form -->
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">Import DHCP Clients from MikroTik Devices</h6>
            </div>
            <div class="card-body">
              <form method="post" onsubmit="return confirm('Proceed with selected mode?');">
                <div class="mb-3">
                  <label class="form-label">Mode:</label>
                  <div>
                    <label class="me-3"><input type="radio" name="mode" value="preview" <?= ($mode==='preview')?'checked':'' ?>> Preview</label>
                    <label class="me-3"><input type="radio" name="mode" value="import" <?= ($mode==='import')?'checked':'' ?>> Import Only</label>
                    <label class="me-3"><input type="radio" name="mode" value="update" <?= ($mode==='update')?'checked':'' ?>> Update Only</label>
                    <label class="me-3"><input type="radio" name="mode" value="import_update" <?= ($mode==='import_update')?'checked':'' ?>> Import + Update</label>
                  </div>
                </div>
                <button type="submit" name="import_dhcp" class="btn btn-primary">
                  <i class="bi bi-upload"></i> Run Import
                </button>
              </form>
            </div>
          </div>
          <!-- Results -->
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success mt-3">
              <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mt-3">
              <h6><i class="bi bi-exclamation-triangle"></i> Errors during import:</h6>
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          <!-- Preview Table -->
          <?php if (!empty($preview_table)): ?>
            <div class="card mt-3">
              <div class="card-header">
                <h6 class="mb-0">Preview / Import Actions</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>Device</th>
                        <th>MAC</th>
                        <th>IP</th>
                        <th>Comment</th>
                        <th>Client Action</th>
                        <th>Device Action</th>
                        <th>Network ID</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($preview_table as $row): ?>
                        <tr>
                          <td><?= htmlspecialchars($row['device']) ?></td>
                          <td><?= htmlspecialchars($row['mac']) ?></td>
                          <td><?= htmlspecialchars($row['ip']) ?></td>
                          <td><?= htmlspecialchars($row['comment']) ?></td>
                          <td><?= htmlspecialchars($row['client_action']) ?></td>
                          <td><?= htmlspecialchars($row['device_action']) ?></td>
                          <td><?= htmlspecialchars($row['network_id']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <!-- Debug Information -->
          <?php if (!empty($debug_info)): ?>
            <div class="card mt-3">
              <div class="card-header">
                <h6 class="mb-0">Debug Information</h6>
              </div>
              <div class="card-body">
                <div class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                  <?php foreach ($debug_info as $info): ?>
                    <div class="mb-1"><small><?= htmlspecialchars($info) ?></small></div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <!-- Quick Actions -->
          <div class="row mt-4">
            <div class="col-md-6">
              <a href="<?= base_url('modules/clients.php') ?>" class="btn btn-secondary">
                <i class="bi bi-people"></i> View All Clients
              </a>
              <a href="<?= base_url('modules/devices.php') ?>" class="btn btn-success">
                <i class="bi bi-hdd-network"></i> View All Devices
              </a>
            </div>
            <div class="col-md-6">
              <a href="<?= base_url('modules/networks.php') ?>" class="btn btn-info">
                <i class="bi bi-diagram-3"></i> View Networks
              </a>
              <a href="<?= base_url('modules/import_dhcp_networks_improved.php') ?>" class="btn btn-warning">
                <i class="bi bi-arrow-left"></i> Import Networks First
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 