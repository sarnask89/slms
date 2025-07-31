<?php
require_once 'module_loader.php';

require_once __DIR__ . '/mikrotik_api.php';

$pageTitle = 'Network Monitoring (Enhanced)';
$pdo = get_pdo();
$errors = [];
$success = '';
$debug_info = [];

// SNMP Support Class
class SNMPMonitor {
    private $host;
    private $community;
    private $timeout = 1000000;
    private $retries = 3;
    
    public function __construct($host, $community = 'public') {
        $this->host = $host;
        $this->community = $community;
    }
    
    public function getInterfaceStats() {
        $interfaces = [];
        
        try {
            // Set SNMP timeout and retries
            snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
            snmp_set_quick_print(true);
            
            // Get interface names with error handling
            $ifNames = @snmprealwalk($this->host, $this->community, '1.3.6.1.2.1.2.2.1.2', 1000000, 1);
            if ($ifNames === false) {
                throw new Exception("No response from {$this->host} for interface names");
            }
            
            $ifInOctets = @snmprealwalk($this->host, $this->community, '1.3.6.1.2.1.2.2.1.10', 1000000, 1);
            if ($ifInOctets === false) {
                $ifInOctets = [];
            }
            
            $ifOutOctets = @snmprealwalk($this->host, $this->community, '1.3.6.1.2.1.2.2.1.16', 1000000, 1);
            if ($ifOutOctets === false) {
                $ifOutOctets = [];
            }
            
            foreach ($ifNames as $oid => $name) {
                $index = explode('.', $oid);
                $index = end($index);
                
                $inOctets = isset($ifInOctets["1.3.6.1.2.1.2.2.1.10.$index"]) ? 
                    $ifInOctets["1.3.6.1.2.1.2.2.1.10.$index"] : 0;
                $outOctets = isset($ifOutOctets["1.3.6.1.2.1.2.2.1.16.$index"]) ? 
                    $ifOutOctets["1.3.6.1.2.1.2.2.1.16.$index"] : 0;
                
                $interfaces[] = [
                    'name' => $name,
                    'rx_bytes' => $inOctets,
                    'tx_bytes' => $outOctets
                ];
            }
        } catch (Exception $e) {
            throw new Exception("SNMP error for {$this->host}: " . $e->getMessage());
        }
        
        return $interfaces;
    }
}

// Handle manual polling action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['poll_interfaces'])) {
    try {
        $stmt = $pdo->query("SELECT id, name, ip_address, api_username, api_password FROM skeleton_devices WHERE api_username IS NOT NULL AND api_password IS NOT NULL");
        $skeleton_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $debug_info[] = "Found " . count($skeleton_devices) . " skeleton devices with API credentials";
        
        if (empty($skeleton_devices)) {
            throw new Exception('No skeleton devices with API credentials found');
        }
        
        $total_polled = 0;
        $rest_api_polled = 0;
        $snmp_polled = 0;
        
        foreach ($skeleton_devices as $device) {
            $device_debug = [];
            $device_debug[] = "Processing device: {$device['name']} ({$device['ip_address']})";
            $device_debug[] = "API credentials: username={$device['api_username']}, password=" . str_repeat('*', strlen($device['api_password']));
            
            try {
                // Try MikroTik API first
                $device_debug[] = "Attempting MikroTik API connection...";
                $api = new MikroTikAPI($device['ip_address'], $device['api_username'], $device['api_password']);
                
                try {
                    // Get interfaces via MikroTik API
                    $device_debug[] = "Requesting interface list...";
                    $interfaces_response = $api->getInterfaces();
                    
                    if (isset($interfaces_response['error'])) {
                        throw new Exception($interfaces_response['error']);
                    }
                    
                    $device_debug[] = "Raw interface response: " . substr($interfaces_response, 0, 200) . "...";
                    
                    // Parse the interface response
                    $interfaces = $api->parseApiResponse($interfaces_response);
                    if (empty($interfaces)) {
                        throw new Exception('No interfaces found or failed to parse response');
                    }
                    
                    $device_debug[] = "Found " . count($interfaces) . " interfaces via MikroTik API";
                    
                    foreach ($interfaces as $iface) {
                        if (!isset($iface['name'])) {
                            $device_debug[] = "Skipping interface without name: " . json_encode($iface);
                            continue; // Skip interfaces without name
                        }
                        
                        try {
                            // Get interface traffic stats
                            $device_debug[] = "Getting traffic stats for interface: {$iface['name']}";
                            $stats = $api->execute("/interface/monitor-traffic interface={$iface['name']} once=");
                            $parsed_stats = $api->parseApiResponse($stats);
                            
                            if (!empty($parsed_stats) && isset($parsed_stats[0]['rx-bits-per-second']) && isset($parsed_stats[0]['tx-bits-per-second'])) {
                                $stmt2 = $pdo->prepare("INSERT INTO interface_stats (device_id, interface_name, rx_bytes, tx_bytes, timestamp) VALUES (?, ?, ?, ?, NOW())");
                                $stmt2->execute([
                                    $device['id'],
                                    $iface['name'],
                                    $parsed_stats[0]['rx-bits-per-second'],
                                    $parsed_stats[0]['tx-bits-per-second']
                                ]);
                                $rest_api_polled++;
                                $device_debug[] = "✅ Polled interface {$iface['name']} via MikroTik API";
                            } else {
                                $device_debug[] = "⚠️ No traffic stats available for interface {$iface['name']}";
                            }
                        } catch (Exception $e) {
                            $device_debug[] = "❌ Error polling interface {$iface['name']}: " . $e->getMessage();
                        }
                    }
                } catch (Exception $e) {
                    $device_debug[] = "❌ MikroTik API failed: " . $e->getMessage();
                    
                    // Fallback to SNMP
                    try {
                        $device_debug[] = "🔄 Attempting SNMP fallback...";
                        $snmp = new SNMPMonitor($device['ip_address']);
                        $snmp_interfaces = $snmp->getInterfaceStats();
                        $device_debug[] = "Found " . count($snmp_interfaces) . " interfaces via SNMP";
                        
                        foreach ($snmp_interfaces as $iface) {
                            $stmt2 = $pdo->prepare("INSERT INTO interface_stats (device_id, interface_name, rx_bytes, tx_bytes, timestamp) VALUES (?, ?, ?, ?, NOW())");
                            $stmt2->execute([
                                $device['id'],
                                $iface['name'],
                                $iface['rx_bytes'],
                                $iface['tx_bytes']
                            ]);
                            $snmp_polled++;
                            $device_debug[] = "✅ Polled interface {$iface['name']} via SNMP";
                        }
                    } catch (Exception $snmp_error) {
                        $device_debug[] = "❌ SNMP also failed: " . $snmp_error->getMessage();
                        $errors[] = "Error processing {$device['name']}: Both MikroTik API and SNMP failed";
                    }
                }
                
                $total_polled += $rest_api_polled + $snmp_polled;
                
            } catch (Exception $e) {
                $device_debug[] = "❌ Failed to create API connection: " . $e->getMessage();
                $errors[] = "Error processing {$device['name']}: " . $e->getMessage();
            }
            
            $debug_info = array_merge($debug_info, $device_debug);
        }
        
        $success = "Polling completed! Total interfaces polled: $total_polled (REST API: $rest_api_polled, SNMP: $snmp_polled)";
        
    } catch (Exception $e) {
        $errors[] = "Polling failed: " . $e->getMessage();
    }
}

// Handle device discovery
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['discover_devices'])) {
    $range = $_POST['ip_range'] ?? '';
    $community = $_POST['snmp_community'] ?? 'public';
    $do_mndp = !empty($_POST['enable_mndp']);
    $discovered = [];
    $errors = [];
    // SNMP Discovery
    if ($range) {
        // Parse CIDR or range
        if (strpos($range, '/') !== false) {
            list($net, $mask) = explode('/', $range);
            $ip_long = ip2long($net);
            $num_ips = pow(2, 32 - (int)$mask);
            for ($i = 1; $i < $num_ips - 1; $i++) {
                $ip = long2ip($ip_long + $i);
                $sysDescr = @snmpget($ip, $community, '1.3.6.1.2.1.1.1.0', 1000000, 1);
                if ($sysDescr !== false && stripos($sysDescr, 'No Such') === false) {
                    $sysName = @snmpget($ip, $community, '1.3.6.1.2.1.1.5.0', 1000000, 1);
                    $discovered[] = [
                        'ip' => $ip,
                        'sysDescr' => trim(str_replace('STRING: ', '', $sysDescr)),
                        'sysName' => trim(str_replace('STRING: ', '', $sysName)),
                        'method' => 'SNMP'
                    ];
                }
            }
        } else {
            $errors[] = 'Invalid IP range format. Use CIDR notation (e.g. 192.168.1.0/24).';
        }
    }
    // MNDP Discovery (UDP 5678)
    if ($do_mndp) {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
        $mndp_packet = hex2bin('01000000000000000000000000000000');
        socket_sendto($socket, $mndp_packet, strlen($mndp_packet), 0, '255.255.255.255', 5678);
        $start = time();
        while (time() - $start < 2) {
            $from = '';
            $port = 0;
            $buf = '';
            $r = @socket_recvfrom($socket, $buf, 2048, MSG_DONTWAIT, $from, $port);
            if ($r && $from) {
                if (!in_array($from, array_column($discovered, 'ip'))) {
                    $discovered[] = [
                        'ip' => $from,
                        'sysDescr' => 'Mikrotik MNDP',
                        'sysName' => '',
                        'method' => 'MNDP'
                    ];
                }
            }
        }
        socket_close($socket);
    }
    $discovery_results = $discovered;
}

// Show recent stats with enhanced filtering
$device_filter = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
$iface_filter = isset($_GET['interface']) ? $_GET['interface'] : '';

$where_conditions = [];
$params = [];

if ($device_filter) {
    $where_conditions[] = "s.device_id = ?";
    $params[] = $device_filter;
}

if ($iface_filter) {
    $where_conditions[] = "s.interface_name LIKE ?";
    $params[] = "%$iface_filter%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$stmt = $pdo->prepare("
    SELECT s.*, d.name as device_name 
    FROM interface_stats s 
    JOIN skeleton_devices d ON s.device_id = d.id 
    $where_clause 
    ORDER BY s.timestamp DESC 
    LIMIT 100
");
$stmt->execute($params);
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get devices for filter
$devices = $pdo->query("SELECT id, name FROM skeleton_devices ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="bi bi-activity"></i> Network Monitoring (Enhanced)
          </h5>
        </div>
        <div class="card-body">
          <!-- Polling Form -->
          <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0">Interface Polling</h6>
              <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#discoverModal">
                <i class="bi bi-search"></i> Discover Devices
              </button>
            </div>
            <div class="card-body">
              <p class="text-muted">
                This enhanced version supports both REST API and SNMP fallback for collecting interface statistics.
              </p>
              <form method="post" class="mb-3">
                <button type="submit" name="poll_interfaces" class="btn btn-primary">
                  <i class="bi bi-arrow-repeat"></i> Poll All Devices Now
                </button>
              </form>
            </div>
          </div>
          <!-- Discover Modal -->
          <div class="modal fade" id="discoverModal" tabindex="-1" aria-labelledby="discoverModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="post">
                  <div class="modal-header">
                    <h5 class="modal-title" id="discoverModalLabel">Discover SNMP/MNDP Devices</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label for="ip_range" class="form-label">IP Range (CIDR, e.g. 192.168.1.0/24)</label>
                      <input type="text" class="form-control" id="ip_range" name="ip_range" required>
                    </div>
                    <div class="mb-3">
                      <label for="snmp_community" class="form-label">SNMP Community</label>
                      <input type="text" class="form-control" id="snmp_community" name="snmp_community" value="public">
                    </div>
                    <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" id="enable_mndp" name="enable_mndp" value="1">
                      <label class="form-check-label" for="enable_mndp">Enable Mikrotik Neighbour Discovery (MNDP)</label>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="discover_devices" class="btn btn-info">Discover</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          
          <!-- Results -->
          <?php if (!empty($success)): ?>
            <div class="alert alert-success">
              <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <h6><i class="bi bi-exclamation-triangle"></i> Errors during polling:</h6>
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          
          <?php if (isset($discovery_results)): ?>
            <div class="card mt-3">
              <div class="card-header">
                <h6 class="mb-0">Discovery Results (<?= count($discovery_results) ?> devices found)</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <thead class="table-light">
                      <tr>
                        <th>IP Address</th>
                        <th>System Name</th>
                        <th>Description</th>
                        <th>Method</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($discovery_results as $dev): ?>
                        <tr>
                          <td><?= htmlspecialchars($dev['ip']) ?></td>
                          <td><?= htmlspecialchars($dev['sysName']) ?></td>
                          <td><?= htmlspecialchars($dev['sysDescr']) ?></td>
                          <td><?= htmlspecialchars($dev['method']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php endif; ?>
          
          <!-- Filters -->
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0">Filters</h6>
            </div>
            <div class="card-body">
              <form method="get" class="row g-2">
                <div class="col-md-4">
                  <label class="form-label">Device</label>
                  <select name="device_id" class="form-select">
                    <option value="">All Devices</option>
                    <?php foreach ($devices as $dev): ?>
                      <option value="<?= $dev['id'] ?>" <?= $dev['id'] == $device_filter ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dev['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Interface</label>
                  <input type="text" name="interface" class="form-control" value="<?= htmlspecialchars($iface_filter) ?>" placeholder="Filter by interface name">
                </div>
                <div class="col-md-4">
                  <label class="form-label">&nbsp;</label>
                  <div>
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <a href="?" class="btn btn-outline-secondary">Clear</a>
                  </div>
                </div>
              </form>
            </div>
          </div>
          
          <!-- Stats Table -->
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">Recent Interface Statistics (<?= count($stats) ?> records)</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover">
                  <thead class="table-light">
                    <tr>
                      <th>Device</th>
                      <th>Interface</th>
                      <th>RX (bps)</th>
                      <th>TX (bps)</th>
                      <th>Timestamp</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stats as $row): ?>
                      <tr>
                        <td><?= htmlspecialchars($row['device_name']) ?></td>
                        <td><code><?= htmlspecialchars($row['interface_name']) ?></code></td>
                        <td><?= number_format($row['rx_bytes']) ?></td>
                        <td><?= number_format($row['tx_bytes']) ?></td>
                        <td><?= htmlspecialchars($row['timestamp']) ?></td>
                        <td>
                          <a href="network_dashboard.php?device_id=<?= $row['device_id'] ?>&iface=<?= urlencode($row['interface_name']) ?>" 
                             class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-graph-up"></i> Graph
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          
          <!-- Debug Information -->
          <?php if (!empty($debug_info)): ?>
            <div class="card mt-3">
              <div class="card-header">
                <h6 class="mb-0">Debug Information</h6>
              </div>
              <div class="card-body">
                <div class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">
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
              <a href="network_dashboard.php" class="btn btn-success">
                <i class="bi bi-graph-up"></i> View Dashboard
              </a>
              <a href="devices.php" class="btn btn-secondary">
                <i class="bi bi-hdd-network"></i> Manage Devices
              </a>
            </div>
            <div class="col-md-6">
              <a href="networks.php" class="btn btn-info">
                <i class="bi bi-diagram-3"></i> View Networks
              </a>
              <a href="network_monitoring.php" class="btn btn-warning">
                <i class="bi bi-arrow-left"></i> Basic Version
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