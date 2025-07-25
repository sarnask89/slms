<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mikrotik_api.php';

$pageTitle = 'Import DHCP Networks (Improved)';
$pdo = get_pdo();

$import_results = [];
$errors = [];
$success_message = '';
$debug_info = [];

// Handle import request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_networks'])) {
    try {
        // Get all skeleton devices with API credentials
        $stmt = $pdo->query("
            SELECT id, name, ip_address, api_username, api_password, api_port, api_ssl 
            FROM skeleton_devices 
            WHERE api_username IS NOT NULL AND api_password IS NOT NULL
        ");
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $debug_info[] = "Found " . count($devices) . " skeleton devices with API credentials";
        
        if (empty($devices)) {
            throw new Exception('No skeleton devices found with API credentials. Please add devices in the skeleton devices section first.');
        }
        
        $total_networks_created = 0;
        $networks_updated = 0;
        
        foreach ($devices as $device) {
            $device_debug = [];
            $device_debug[] = "Processing device: {$device['name']} ({$device['ip_address']})";
            
            try {
                $dhcp_networks = [];
                $addresses = [];
                
                // Try REST API first (modern approach)
                try {
                    $device_debug[] = "Attempting REST API connection...";
                    
                    // Use the modern REST API approach
                    $api_url = "https://{$device['ip_address']}:{$device['api_port']}/rest/ip/dhcp-server/network";
                    $auth = base64_encode("{$device['api_username']}:{$device['api_password']}");
                    
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'header' => [
                                "Authorization: Basic $auth",
                                "Content-Type: application/json",
                                "User-Agent: sLMS/1.0"
                            ],
                            'timeout' => 10,
                            'ignore_errors' => true
                        ],
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false
                        ]
                    ]);
                    
                    $response = file_get_contents($api_url, false, $context);
                    $http_code = $http_response_header[0] ?? '';
                    
                    $device_debug[] = "REST API Response Code: $http_code";
                    $device_debug[] = "REST API Response: " . substr($response, 0, 200) . "...";
                    
                    if ($response !== false && strpos($http_code, '200') !== false) {
                        $dhcp_data = json_decode($response, true);
                        if (is_array($dhcp_data)) {
                            foreach ($dhcp_data as $network) {
                                if (isset($network['address'])) {
                                    $dhcp_networks[] = [
                                        'address' => $network['address'],
                                        'gateway' => $network['gateway'] ?? null,
                                        'dns1' => $network['dns-server'] ?? null,
                                        'dns2' => null,
                                        'domain' => $network['domain'] ?? null,
                                        'comment' => $network['comment'] ?? null
                                    ];
                                }
                            }
                        }
                        $device_debug[] = "REST API: Found " . count($dhcp_networks) . " DHCP networks";
                    } else {
                        throw new Exception("REST API failed: $http_code");
                    }
                    
                } catch (Exception $e) {
                    $device_debug[] = "REST API failed: " . $e->getMessage();
                    $device_debug[] = "Falling back to SSH method...";
                    
                    // Fallback to SSH method
                    try {
                        $dhcp_networks_output = mikrotikSshCall(
                            $device['ip_address'], 
                            $device['api_username'], 
                            $device['api_password'], 
                            "/ip/dhcp-server/network/print",
                            22
                        );
                        
                        $addresses_output = mikrotikSshCall(
                            $device['ip_address'], 
                            $device['api_username'], 
                            $device['api_password'], 
                            "/ip/address/print",
                            22
                        );
                        
                        $device_debug[] = "SSH DHCP Output: " . substr($dhcp_networks_output, 0, 200) . "...";
                        $device_debug[] = "SSH Addresses Output: " . substr($addresses_output, 0, 200) . "...";
                        
                        // Parse the outputs
                        $dhcp_networks = parseDhcpNetworksFromSsh($dhcp_networks_output);
                        $addresses = parseAddressesFromSsh($addresses_output);
                        
                        $device_debug[] = "SSH: Found " . count($dhcp_networks) . " DHCP networks";
                        $device_debug[] = "SSH: Found " . count($addresses) . " addresses";
                        
                    } catch (Exception $ssh_error) {
                        $device_debug[] = "SSH also failed: " . $ssh_error->getMessage();
                        throw new Exception("Both REST API and SSH failed. Check device connectivity and credentials.");
                    }
                }
                
                if (empty($dhcp_networks)) {
                    $import_results[] = [
                        'device' => $device['name'],
                        'status' => 'warning',
                        'message' => 'No DHCP networks found on this device'
                    ];
                    $debug_info = array_merge($debug_info, $device_debug);
                    continue;
                }
                
                $device_debug[] = "Processing " . count($dhcp_networks) . " DHCP networks...";
                
                foreach ($dhcp_networks as $dhcp_network) {
                    if (!isset($dhcp_network['address'])) {
                        $device_debug[] = "Skipping network without address field";
                        continue;
                    }
                    
                    $device_debug[] = "Processing network: {$dhcp_network['address']}";
                    
                    // Parse network address (e.g., "192.168.1.0/24")
                    $network_parts = explode('/', $dhcp_network['address']);
                    if (count($network_parts) !== 2) {
                        $device_debug[] = "Invalid network format: {$dhcp_network['address']}";
                        continue;
                    }
                    
                    $network_address = $network_parts[0];
                    $subnet_mask = $network_parts[1];
                    $subnet = $dhcp_network['address']; // Full subnet like "192.168.1.0/24"
                    
                                         // Use comment as interface, fallback to detected interface
                     $interface = $dhcp_network['comment'] ?? 'unknown';
                     
                     // If no comment, try to find interface from addresses
                     if ($interface === 'unknown' || empty($interface)) {
                         foreach ($addresses as $address) {
                             if (isset($address['network']) && $address['network'] === $network_address) {
                                 $interface = $address['interface'];
                                 break;
                             }
                         }
                     }
                    
                                         $device_debug[] = "Network: $subnet, Interface: $interface (from comment: " . ($dhcp_network['comment'] ?? 'none') . ")";
                    
                    // Check if network already exists
                    $stmt = $pdo->prepare("
                        SELECT id FROM networks 
                        WHERE subnet = ? AND device_id = ?
                    ");
                    $stmt->execute([$subnet, $device['id']]);
                    $existing_network = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existing_network) {
                        // Update existing network
                        $stmt = $pdo->prepare("
                            UPDATE networks 
                            SET name = ?, description = ?, device_interface = ?, gateway = ?
                            WHERE id = ?
                        ");
                        $network_name = "DHCP Network $subnet";
                        $description = "Interface: " . $interface . 
                                     ", Gateway: " . ($dhcp_network['gateway'] ?? 'N/A') . 
                                     ", DNS1: " . ($dhcp_network['dns1'] ?? 'N/A') . 
                                     ", DNS2: " . ($dhcp_network['dns2'] ?? 'N/A') . 
                                     ", Domain: " . ($dhcp_network['domain'] ?? 'N/A');
                        $stmt->execute([
                            $network_name,
                            $description,
                            $interface,
                            $dhcp_network['gateway'],
                            $existing_network['id']
                        ]);
                        $networks_updated++;
                        $device_debug[] = "Updated existing network: $network_name";
                    } else {
                        // Create new network
                        $stmt = $pdo->prepare("
                            INSERT INTO networks (name, subnet, description, device_id, device_interface, gateway, network_address) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $network_name = "DHCP Network $subnet";
                        $description = "Interface: " . $interface . 
                                     ", Gateway: " . ($dhcp_network['gateway'] ?? 'N/A') . 
                                     ", DNS1: " . ($dhcp_network['dns1'] ?? 'N/A') . 
                                     ", DNS2: " . ($dhcp_network['dns2'] ?? 'N/A') . 
                                     ", Domain: " . ($dhcp_network['domain'] ?? 'N/A');
                        $stmt->execute([
                            $network_name,
                            $subnet,
                            $description,
                            $device['id'],
                            $interface,
                            $dhcp_network['gateway'],
                            $network_address
                        ]);
                        $total_networks_created++;
                        $device_debug[] = "Created new network: $network_name";
                    }
                }
                
                $import_results[] = [
                    'device' => $device['name'],
                    'status' => 'success',
                    'message' => "Processed " . count($dhcp_networks) . " DHCP networks successfully"
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
        
        $success_message = "Import completed successfully! Created: $total_networks_created networks. Updated: $networks_updated networks.";
        
    } catch (Exception $e) {
        $errors[] = "Import failed: " . $e->getMessage();
    }
}

// Get current statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM networks");
$total_networks = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM skeleton_devices WHERE api_username IS NOT NULL AND api_password IS NOT NULL");
$total_devices = $stmt->fetchColumn();

ob_start();
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="bi bi-diagram-3"></i> Import DHCP Networks (Improved)
          </h5>
        </div>
        <div class="card-body">
          <!-- Statistics -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card bg-primary text-white">
                <div class="card-body text-center">
                  <h4><?= $total_networks ?></h4>
                  <small>Total Networks</small>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card bg-info text-white">
                <div class="card-body text-center">
                  <h4><?= $total_devices ?></h4>
                  <small>Devices with API Access</small>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Import Form -->
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">Import DHCP Networks from MikroTik Devices</h6>
            </div>
            <div class="card-body">
              <p class="text-muted">
                This improved version uses modern REST API with SSH fallback and provides detailed debugging information.
              </p>
              <ul class="text-muted">
                <li>Uses MikroTik REST API (modern approach)</li>
                <li>Falls back to SSH if REST API fails</li>
                <li>Creates network entries with proper addressing</li>
                <li><strong>Uses network comment as interface name</strong></li>
                <li>Falls back to detected interface if no comment</li>
                <li>Includes gateway, DNS, and domain information</li>
                <li>Updates existing networks if they already exist</li>
                <li>Provides detailed debug information</li>
              </ul>
              
              <form method="post" onsubmit="return confirm('Do you want to import DHCP networks? This may update existing network records.');">
                <button type="submit" name="import_networks" class="btn btn-primary">
                  <i class="bi bi-upload"></i> Import DHCP Networks
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
                            <?php elseif ($result['status'] === 'warning'): ?>
                              <span class="badge bg-warning">Warning</span>
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
              <a href="<?= base_url('modules/networks.php') ?>" class="btn btn-secondary">
                <i class="bi bi-list"></i> View All Networks
              </a>
            </div>
            <div class="col-md-6">
              <a href="<?= base_url('modules/import_dhcp_clients_improved.php') ?>" class="btn btn-success">
                <i class="bi bi-arrow-right"></i> Next: Import DHCP Clients
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