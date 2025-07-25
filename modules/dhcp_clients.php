<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mikrotik_api.php';

$pdo = get_pdo();
$clients = [];
$error = null;

// Get all skeleton devices (x86 MikroTik devices)
$stmt = $pdo->prepare("
    SELECT id, name, ip_address, api_username, api_password 
    FROM skeleton_devices 
    WHERE api_username IS NOT NULL AND api_password IS NOT NULL
    ORDER BY name
");
$stmt->execute();
$skeleton_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process each skeleton device to get DHCP leases
foreach ($skeleton_devices as $device) {
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
        
        if ($leases && is_array($leases)) {
            foreach ($leases as $lease) {
                if (isset($lease['mac-address']) && isset($lease['address']) && isset($lease['comment'])) {
                    // Extract 2nd word from comment as client name
                    $comment_words = explode(' ', trim($lease['comment']));
                    $client_name = isset($comment_words[1]) ? $comment_words[1] : 'Unknown';
                    
                    $clients[] = [
                        'device_name' => $device['name'],
                        'device_ip' => $device['ip_address'],
                        'client_name' => $client_name,
                        'mac_address' => $lease['mac-address'],
                        'ip_address' => $lease['address'],
                        'comment' => $lease['comment'],
                        'status' => $lease['status'] ?? 'unknown',
                        'last_seen' => $lease['last-seen'] ?? 'unknown'
                    ];
                }
            }
        }
    } catch (Exception $e) {
        $error = "Error connecting to {$device['name']} ({$device['ip_address']}): " . $e->getMessage();
    }
}

// Sort clients by device name, then by client name
usort($clients, function($a, $b) {
    if ($a['device_name'] !== $b['device_name']) {
        return strcmp($a['device_name'], $b['device_name']);
    }
    return strcmp($a['client_name'], $b['client_name']);
});

ob_start();
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-people"></i> Lista klientów DHCP
          </h5>
          <div>
            <span class="badge bg-primary"><?= count($clients) ?> klientów</span>
            <span class="badge bg-info"><?= count($skeleton_devices) ?> urządzeń</span>
          </div>
        </div>
        <div class="card-body">
          <?php if ($error): ?>
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>
          
          <?php if (empty($clients)): ?>
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> Brak aktywnych klientów DHCP lub problem z połączeniem do urządzeń.
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead class="table-dark">
                  <tr>
                    <th>Urządzenie</th>
                    <th>Nazwa klienta</th>
                    <th>Adres MAC</th>
                    <th>Adres IP</th>
                    <th>Status</th>
                    <th>Ostatnio widziany</th>
                    <th>Pełny komentarz</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($clients as $client): ?>
                    <tr>
                      <td>
                        <strong><?= htmlspecialchars($client['device_name']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($client['device_ip']) ?></small>
                      </td>
                      <td>
                        <span class="badge bg-success"><?= htmlspecialchars($client['client_name']) ?></span>
                      </td>
                      <td>
                        <code><?= htmlspecialchars($client['mac_address']) ?></code>
                      </td>
                      <td>
                        <span class="badge bg-primary"><?= htmlspecialchars($client['ip_address']) ?></span>
                      </td>
                      <td>
                        <?php if ($client['status'] === 'bound'): ?>
                          <span class="badge bg-success">Aktywny</span>
                        <?php elseif ($client['status'] === 'offline'): ?>
                          <span class="badge bg-danger">Offline</span>
                        <?php else: ?>
                          <span class="badge bg-warning"><?= htmlspecialchars($client['status']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <small><?= htmlspecialchars($client['last_seen']) ?></small>
                      </td>
                      <td>
                        <small class="text-muted"><?= htmlspecialchars($client['comment']) ?></small>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            
            <!-- Summary by device -->
            <div class="row mt-4">
              <div class="col-12">
                <h6>Podsumowanie według urządzeń:</h6>
                <div class="row">
                  <?php 
                  $device_summary = [];
                  foreach ($clients as $client) {
                      $device_key = $client['device_name'];
                      if (!isset($device_summary[$device_key])) {
                          $device_summary[$device_key] = [
                              'device_ip' => $client['device_ip'],
                              'total_clients' => 0,
                              'active_clients' => 0,
                              'offline_clients' => 0
                          ];
                      }
                      $device_summary[$device_key]['total_clients']++;
                      if ($client['status'] === 'bound') {
                          $device_summary[$device_key]['active_clients']++;
                      } elseif ($client['status'] === 'offline') {
                          $device_summary[$device_key]['offline_clients']++;
                      }
                  }
                  ?>
                  
                  <?php foreach ($device_summary as $device_name => $summary): ?>
                    <div class="col-md-4 mb-3">
                      <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                          <h6 class="mb-0"><?= htmlspecialchars($device_name) ?></h6>
                        </div>
                        <div class="card-body">
                          <p class="mb-1"><small class="text-muted"><?= htmlspecialchars($summary['device_ip']) ?></small></p>
                          <div class="d-flex justify-content-between">
                            <span class="badge bg-success"><?= $summary['active_clients'] ?> aktywnych</span>
                            <span class="badge bg-danger"><?= $summary['offline_clients'] ?> offline</span>
                            <span class="badge bg-info"><?= $summary['total_clients'] ?> łącznie</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 