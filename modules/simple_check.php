<?php
require_once 'module_loader.php';

require_once __DIR__ . '/mikrotik_api.php';

$pageTitle = 'Sprawdź Urządzenie';
$pdo = get_pdo();

$device_id = $_GET['id'] ?? null;
$device_type = $_GET['type'] ?? 'device';
$device = null;
$ping_result = null;
$arp_result = null;
$error = '';
$debug_info = [];

if ($device_id) {
    if ($device_type === 'skeleton') {
        $stmt = $pdo->prepare("SELECT * FROM skeleton_devices WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE id = ?");
    }
    $stmt->execute([$device_id]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$device) {
    $error = 'Urządzenie nie zostało znalezione.';
}

// Handle check requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $device) {
    $check_type = $_POST['check_type'] ?? '';
    $ip = $device['ip_address'];
    
    $debug_info[] = "Checking IP: $ip";
    $debug_info[] = "Check type: $check_type";
    
    if ($check_type === 'ping') {
        // Get skeleton device credentials
        $mikrotik_host = '';
        $mikrotik_user = '';
        $mikrotik_pass = '';
        $api_port = 8728;
        $api_ssl = false;
        
        if ($device['network_id']) {
            $network_stmt = $pdo->prepare("
                SELECT sd.ip_address, sd.api_username, sd.api_password, sd.api_port, sd.api_ssl 
                FROM networks n 
                JOIN skeleton_devices sd ON n.device_id = sd.id 
                WHERE n.id = ? AND sd.api_username IS NOT NULL AND sd.api_password IS NOT NULL
            ");
            $network_stmt->execute([$device['network_id']]);
            $skeleton_device = $network_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($skeleton_device) {
                $mikrotik_host = $skeleton_device['ip_address'];
                $mikrotik_user = $skeleton_device['api_username'];
                $mikrotik_pass = $skeleton_device['api_password'];
                $api_port = $skeleton_device['api_port'];
                $api_ssl = $skeleton_device['api_ssl'];
                
                $debug_info[] = "Found skeleton device: $mikrotik_host";
            }
        }
        
        // Try multiple methods in order of preference
        $methods_tried = [];
        
        // Method 1: System ping (most reliable)
        $debug_info[] = "Trying system ping...";
        $system_result = systemPing($ip, 4);
        $methods_tried[] = "System Ping";
        
        if ($system_result['success']) {
            $ping_result = [
                'method' => 'System Ping',
                'result' => $system_result,
                'success' => true
            ];
            $debug_info[] = "System ping successful";
        } else {
            $debug_info[] = "System ping failed";
            
            // Method 2: SSH to MikroTik (if credentials available)
            if ($mikrotik_host && $mikrotik_user && $mikrotik_pass) {
                $debug_info[] = "Trying SSH to MikroTik...";
                $ssh_result = mikrotikSshCall($mikrotik_host, $mikrotik_user, $mikrotik_pass, "/ping address=$ip count=4", $api_port ?? 8728);
                $methods_tried[] = "MikroTik SSH";
                
                if (!empty($ssh_result) && strpos($ssh_result, 'timeout') === false) {
                    $ping_result = [
                        'method' => 'MikroTik SSH',
                        'result' => $ssh_result,
                        'success' => true
                    ];
                    $debug_info[] = "SSH ping successful";
                } else {
                    $debug_info[] = "SSH ping failed: " . substr($ssh_result, 0, 100);
                }
            }
            
            // Method 3: MikroTik API (if SSH failed or not available)
            if (!$ping_result && $mikrotik_host && $mikrotik_user && $mikrotik_pass) {
                $debug_info[] = "Trying MikroTik API...";
                try {
                    set_time_limit(30);
                    $api = new MikroTikAPI($mikrotik_host, $mikrotik_user, $mikrotik_pass, $api_port, $api_ssl);
                    $api_result = $api->ping($ip, 4);
                    $methods_tried[] = "MikroTik API";
                    
                    if (isset($api_result['error'])) {
                        $debug_info[] = "API failed: " . $api_result['error'];
                    } else {
                        $ping_result = [
                            'method' => 'MikroTik API',
                            'result' => $api_result,
                            'success' => true
                        ];
                        $debug_info[] = "API ping successful";
                    }
                } catch (Exception $e) {
                    $debug_info[] = "API exception: " . $e->getMessage();
                }
            }
        }
        
        // If no method worked, use system ping result anyway
        if (!$ping_result) {
            $ping_result = [
                'method' => 'System Ping (fallback)',
                'result' => $system_result,
                'success' => false,
                'methods_tried' => $methods_tried
            ];
        }
        
    } elseif ($check_type === 'arp') {
        // Similar approach for ARP ping
        $interface = $_POST['interface'] ?? '';
        
        // Get interface from network if not provided
        if (empty($interface) && $device['network_id']) {
            $network_stmt = $pdo->prepare("SELECT device_interface FROM networks WHERE id = ?");
            $network_stmt->execute([$device['network_id']]);
            $interface = $network_stmt->fetchColumn();
        }
        
        $debug_info[] = "Interface: " . ($interface ?: 'Not specified');
        
        // Method 1: System arping
        $debug_info[] = "Trying system arping...";
        $system_result = systemArpPing($ip, 4);
        $methods_tried = ["System ARP Ping"];
        
        if ($system_result['success']) {
            $arp_result = [
                'method' => 'System ARP Ping',
                'result' => $system_result,
                'success' => true
            ];
            $debug_info[] = "System arping successful";
        } else {
            $debug_info[] = "System arping failed";
            
            // Method 2: SSH to MikroTik (if credentials and interface available)
            if ($mikrotik_host && $mikrotik_user && $mikrotik_pass && $interface) {
                $debug_info[] = "Trying SSH ARP ping...";
                
                // Get available interfaces for debugging
                $available_interfaces = getMikrotikInterfaces($mikrotik_host, $mikrotik_user, $mikrotik_pass, $api_port ?? 8728);
                $interface_names = array_column($available_interfaces, 'name');
                $debug_info[] = "Available interfaces: " . implode(', ', $interface_names);
                $debug_info[] = "Using interface: $interface";
                
                // Check if interface exists
                if (!in_array($interface, $interface_names)) {
                    $debug_info[] = "WARNING: Interface '$interface' not found on device!";
                }
                
                $ssh_result = mikrotikSshCall($mikrotik_host, $mikrotik_user, $mikrotik_pass, "/ping address=$ip count=4 arp-ping=yes interface=\"$interface\"", $api_port ?? 8728);
                $methods_tried[] = "MikroTik SSH ARP";
                
                if (!empty($ssh_result) && strpos($ssh_result, 'timeout') === false) {
                    $arp_result = [
                        'method' => 'MikroTik SSH ARP',
                        'result' => $ssh_result,
                        'success' => true
                    ];
                    $debug_info[] = "SSH ARP ping successful";
                } else {
                    $debug_info[] = "SSH ARP ping failed: " . substr($ssh_result, 0, 100);
                }
            }
        }
        
        // If no method worked, use system result anyway
        if (!$arp_result) {
            $arp_result = [
                'method' => 'System ARP Ping (fallback)',
                'result' => $system_result,
                'success' => false,
                'methods_tried' => $methods_tried
            ];
        }
    }
}

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="lms-accent">Sprawdź Urządzenie</h2>
      <a href="<?= base_url('modules/' . ($device_type === 'skeleton' ? 'skeleton_devices.php' : 'devices.php')) ?>" class="btn btn-secondary">Powrót do listy</a>
    </div>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($device): ?>
      <!-- Device Info -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Informacje o urządzeniu</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <p><strong>Nazwa:</strong> <?= htmlspecialchars($device['name']) ?></p>
              <p><strong>Typ:</strong> <?= htmlspecialchars($device['type']) ?></p>
              <p><strong>Adres IP:</strong> <?= htmlspecialchars($device['ip_address']) ?></p>
            </div>
            <div class="col-md-6">
              <p><strong>Adres MAC:</strong> <?= htmlspecialchars($device['mac_address']) ?></p>
              <p><strong>Lokalizacja:</strong> <?= htmlspecialchars($device['location']) ?></p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Check Forms -->
      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Ping</h5>
            </div>
            <div class="card-body">
              <form method="post">
                <input type="hidden" name="check_type" value="ping">
                <button type="submit" class="btn btn-primary w-100" onclick="showLoading(this, 'ping')">
                  <i class="bi bi-wifi"></i> Wykonaj Ping
                </button>
              </form>
              
              <?php if ($ping_result): ?>
                <div class="mt-3">
                  <h6>Wynik Ping:</h6>
                  <p><strong>Metoda:</strong> <?= htmlspecialchars($ping_result['method']) ?></p>
                  <p><strong>Status:</strong> 
                    <?php if ($ping_result['success']): ?>
                      <span class="badge bg-success">Sukces</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Błąd</span>
                    <?php endif; ?>
                  </p>
                  <pre class="bg-light p-2 rounded"><?= htmlspecialchars($ping_result['result']['output'] ?? $ping_result['result']) ?></pre>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">ARP Ping</h5>
            </div>
            <div class="card-body">
              <form method="post">
                <input type="hidden" name="check_type" value="arp">
                <button type="submit" class="btn btn-info w-100" onclick="showLoading(this, 'arp')">
                  <i class="bi bi-broadcast"></i> Wykonaj ARP Ping
                </button>
              </form>
              
              <?php if ($arp_result): ?>
                <div class="mt-3">
                  <h6>Wynik ARP Ping:</h6>
                  <p><strong>Metoda:</strong> <?= htmlspecialchars($arp_result['method']) ?></p>
                  <p><strong>Status:</strong> 
                    <?php if ($arp_result['success']): ?>
                      <span class="badge bg-success">Sukces</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Błąd</span>
                    <?php endif; ?>
                  </p>
                  <pre class="bg-light p-2 rounded"><?= htmlspecialchars($arp_result['result']['output'] ?? $arp_result['result']) ?></pre>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Debug Information -->
      <?php if (!empty($debug_info)): ?>
        <div class="card mt-4">
          <div class="card-header">
            <h5 class="mb-0">Informacje debugowania</h5>
          </div>
          <div class="card-body">
            <ul class="list-unstyled">
              <?php foreach ($debug_info as $info): ?>
                <li><code><?= htmlspecialchars($info) ?></code></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>
      
    <?php endif; ?>
  </div>
</div>

<script>
function showLoading(button, type) {
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sprawdzanie...';
    
    setTimeout(function() {
        if (button.disabled) {
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sprawdzanie... (może potrwać do 30s)';
        }
    }, 5000);
    
    setTimeout(function() {
        if (button.disabled) {
            button.disabled = false;
            if (type === 'ping') {
                button.innerHTML = '<i class="bi bi-wifi"></i> Wykonaj Ping';
            } else {
                button.innerHTML = '<i class="bi bi-broadcast"></i> Wykonaj ARP Ping';
            }
        }
    }, 35000);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 