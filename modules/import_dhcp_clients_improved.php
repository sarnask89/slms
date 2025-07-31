<?php
require_once 'module_loader.php';

require_once __DIR__ . '/mikrotik_api.php';

$pageTitle = 'Import DHCP Clients (Improved)';
$pdo = get_pdo();

$import_results = [];
$errors = [];
$success_message = '';
$debug_info = [];

// Helper function to check if IP is in subnet
function ip_in_subnet($ip, $subnet) {
    if (empty($subnet) || strpos($subnet, '/') === false) {
        return false;
    }
    list($net, $mask) = explode('/', $subnet);
    if (!is_numeric($mask) || $mask < 0 || $mask > 32) {
        return false;
    }
    $ip_long = ip2long($ip);
    $net_long = ip2long($net);
    if ($ip_long === false || $net_long === false) {
        return false;
    }
    $mask_long = ~((1 << (32 - $mask)) - 1);
    return ($ip_long & $mask_long) === ($net_long & $mask_long);
}

// Handle import action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_dhcp'])) {
    try {
        // Get skeleton device info
        $stmt = $pdo->prepare("
            SELECT id, name, ip_address, api_username, api_password 
            FROM skeleton_devices 
            WHERE api_username IS NOT NULL AND api_password IS NOT NULL
            ORDER BY name
        ");
        $stmt->execute();
        $skeleton_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $debug_info[] = "Found " . count($skeleton_devices) . " skeleton devices with API credentials";
        
        if (empty($skeleton_devices)) {
            throw new Exception('No skeleton devices with API credentials found');
        }
        
        $total_clients_created = 0;
        $total_devices_created = 0;
        $clients_updated = 0;
        $devices_updated = 0;
        $skipped_duplicates = 0;
        $networks_assigned = 0;
        
        foreach ($skeleton_devices as $device) {
            $device_debug = [];
            $device_debug[] = "Processing device: {$device['name']} ({$device['ip_address']})";
            
            try {
                $api = new MikroTikAPI($device['ip_address'], $device['api_username'], $device['api_password']);
                
                // Get DHCP server leases using SSH fallback
                $leases = $api->execute('/ip/dhcp-server/lease/print');
                
                if (!$leases || empty($leases)) {
                    // Try SSH fallback
                    $ssh_output = $api->sshExecute('/ip dhcp-server lease print');
                    if ($ssh_output && !str_contains($ssh_output, 'error')) {
                        $leases = $api->parseDhcpLeasesFromSsh($ssh_output);
                    }
                }
                
                $device_debug[] = "Found " . count($leases ?? []) . " DHCP leases";
                
                if ($leases && is_array($leases)) {
                    foreach ($leases as $lease) {
                        if (isset($lease['mac-address']) && isset($lease['address']) && isset($lease['comment'])) {
                            $lease_debug = [];
                            $lease_debug[] = "Processing lease: {$lease['address']} ({$lease['mac-address']})";
                            
                            // Create unique client name from lease data
                            $comment_words = explode(' ', trim($lease['comment']));
                            $base_name = isset($comment_words[1]) ? $comment_words[1] : 'Unknown';
                            
                            // Skip if base name is too short or generic
                            if (strlen($base_name) < 2 || in_array(strtolower($base_name), ['unknown', 'router', 'switch', 'ap', 'access', 'point'])) {
                                $lease_debug[] = "Skipping generic device: $base_name";
                                $device_debug = array_merge($device_debug, $lease_debug);
                                continue;
                            }
                            
                            // Create unique client name using address/location info
                            $address_part = '';
                            if (isset($comment_words[2])) {
                                $address_part = ' ' . $comment_words[2];
                            }
                            if (isset($comment_words[3])) {
                                $address_part .= ' ' . $comment_words[3];
                            }
                            
                            $client_name = $base_name . $address_part;
                            
                            // If still too long, truncate but keep unique
                            if (strlen($client_name) > 50) {
                                $client_name = substr($base_name . ' ' . $comment_words[2], 0, 50);
                            }
                            
                            $lease_debug[] = "Client name: $client_name";
                            
                            // Check if client already exists by name (prevent duplicates)
                            $stmt = $pdo->prepare("SELECT id FROM clients WHERE CONCAT(first_name, ' ', last_name) = ?");
                            $stmt->execute([$client_name]);
                            $existing_client = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($existing_client) {
                                $client_id = $existing_client['id'];
                                $clients_updated++;
                                $lease_debug[] = "Using existing client ID: $client_id";
                            } else {
                                // Create new client with unique name
                                $stmt = $pdo->prepare("
                                    INSERT INTO clients (first_name, last_name, address, notes, created_at) 
                                    VALUES (?, ?, ?, ?, NOW())
                                ");
                                $first_name = $base_name;
                                $last_name = isset($comment_words[2]) ? $comment_words[2] : '';
                                $address = $lease['comment']; // Use full comment as address
                                $notes = "Imported from DHCP lease - MAC: " . $lease['mac-address'] . " - Device: " . $device['name'];
                                $stmt->execute([$first_name, $last_name, $address, $notes]);
                                $client_id = $pdo->lastInsertId();
                                $total_clients_created++;
                                $lease_debug[] = "Created new client ID: $client_id";
                            }
                            
                            // Check if device already exists by MAC address (prevent duplicates)
                            $stmt = $pdo->prepare("SELECT id, client_id, network_id FROM devices WHERE mac_address = ?");
                            $stmt->execute([$lease['mac-address']]);
                            $existing_device = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($existing_device) {
                                $lease_debug[] = "Device with MAC {$lease['mac-address']} already exists";
                                
                                // Only update if client_id is different or network_id is null
                                if ($existing_device['client_id'] != $client_id || $existing_device['network_id'] === null) {
                                    // Find the network associated with this skeleton device and IP range
                                    $network_id = null;
                                    $stmt = $pdo->prepare('SELECT id, subnet FROM networks WHERE device_id = ?');
                                    $stmt->execute([$device['id']]);
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $net) {
                                        if (ip_in_subnet($lease['address'], $net['subnet'])) {
                                            $network_id = $net['id'];
                                            break;
                                        }
                                    }
                                    
                                    if ($network_id) {
                                        $networks_assigned++;
                                        $lease_debug[] = "Assigned to network ID: $network_id";
                                    } else {
                                        $lease_debug[] = "No matching network found for IP {$lease['address']}";
                                    }
                                    
                                    // Update existing device
                                    $stmt = $pdo->prepare("
                                        UPDATE devices 
                                        SET client_id = ?, ip_address = ?, name = ?, network_id = ?
                                        WHERE id = ?
                                    ");
                                    $device_name = $client_name . " - " . $lease['mac-address'];
                                    $stmt->execute([
                                        $client_id,
                                        $lease['address'],
                                        $device_name,
                                        $network_id !== false && $network_id !== null && $network_id !== '' ? $network_id : null,
                                        $existing_device['id']
                                    ]);
                                    $devices_updated++;
                                    $lease_debug[] = "Updated existing device";
                                } else {
                                    $lease_debug[] = "Device already properly assigned, skipping update";
                                    $skipped_duplicates++;
                                }
                            } else {
                                // Create new device
                                $device_name = $client_name . " - " . $lease['mac-address'];
                                $device_type = 'other';
                                $location = $lease['comment'];
                                
                                // Find the network associated with this skeleton device and IP range
                                $network_id = null;
                                $stmt = $pdo->prepare('SELECT id, subnet FROM networks WHERE device_id = ?');
                                $stmt->execute([$device['id']]);
                                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $net) {
                                    if (ip_in_subnet($lease['address'], $net['subnet'])) {
                                        $network_id = $net['id'];
                                        break;
                                    }
                                }
                                
                                if ($network_id) {
                                    $networks_assigned++;
                                    $lease_debug[] = "Assigned to network ID: $network_id";
                                } else {
                                    $lease_debug[] = "No matching network found for IP {$lease['address']}";
                                }
                                
                                $stmt = $pdo->prepare("
                                    INSERT INTO devices (name, type, ip_address, mac_address, location, client_id, network_id, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                                ");
                                $stmt->execute([
                                    $device_name,
                                    $device_type,
                                    $lease['address'],
                                    $lease['mac-address'],
                                    $location,
                                    $client_id,
                                    $network_id !== false && $network_id !== null && $network_id !== '' ? $network_id : null
                                ]);
                                $total_devices_created++;
                                $lease_debug[] = "Created new device";
                            }
                            
                            $device_debug = array_merge($device_debug, $lease_debug);
                        }
                    }
                }
                
                $import_results[] = [
                    'device' => $device['name'],
                    'status' => 'success',
                    'message' => 'DHCP leases processed successfully'
                ];
                
            } catch (Exception $e) {
                $import_results[] = [
                    'device' => $device['name'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                $errors[] = "Error processing {$device['name']}: " . $e->getMessage();
            }
            
            $debug_info = array_merge($debug_info, $device_debug);
        }
        
        $success_message = "Import completed successfully! Created: $total_clients_created clients, $total_devices_created devices. Updated: $clients_updated clients, $devices_updated devices. Networks assigned: $networks_assigned. Skipped duplicates: $skipped_duplicates.";
        
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
            <i class="bi bi-upload"></i> Import DHCP Clients (Improved)
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
              <p class="text-muted">
                This improved version provides better network assignment and duplicate prevention:
              </p>
              <ul class="text-muted">
                <li><strong>Smart Network Assignment:</strong> Automatically assigns devices to correct networks based on IP ranges</li>
                <li><strong>Duplicate Prevention:</strong> Prevents duplicate clients and devices using MAC addresses and names</li>
                <li><strong>Enhanced Debugging:</strong> Detailed logging of all operations</li>
                <li><strong>Network Tracking:</strong> Shows how many devices were assigned to networks</li>
                <li><strong>Skip Logic:</strong> Skips generic devices (routers, switches, APs)</li>
                <li><strong>Update Logic:</strong> Only updates devices when necessary</li>
              </ul>
              
              <form method="post" onsubmit="return confirm('Do you want to import DHCP clients? This may update existing records.');">
                <button type="submit" name="import_dhcp" class="btn btn-primary">
                  <i class="bi bi-upload"></i> Import DHCP Clients
                </button>
              </form>
              <form method="post" class="d-inline-block ms-2" onsubmit="return confirm('Are you sure you want to delete ALL clients? This cannot be undone!');">
                <button type="submit" name="clear_clients" class="btn btn-danger">
                  <i class="bi bi-trash"></i> Clear All Clients
                </button>
              </form>
              <form method="post" class="d-inline-block ms-2" onsubmit="return confirm('Are you sure you want to delete ALL devices? This cannot be undone!');">
                <button type="submit" name="clear_devices" class="btn btn-danger">
                  <i class="bi bi-trash"></i> Clear All Devices
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
          
          <?php if (!empty($import_results)): ?>
            <div class="card mt-3">
              <div class="card-header">
                <h6 class="mb-0">Import Details</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>Device</th>
                        <th>Status</th>
                        <th>Message</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($import_results as $result): ?>
                        <tr>
                          <td><?= htmlspecialchars($result['device']) ?></td>
                          <td>
                            <?php if ($result['status'] === 'success'): ?>
                              <span class="badge bg-success">Success</span>
                            <?php else: ?>
                              <span class="badge bg-danger">Error</span>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars($result['message']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
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