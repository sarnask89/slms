<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mikrotik_api.php';

$pageTitle = 'Sprawdź Urządzenie';
$pdo = get_pdo();

$device_id = $_GET['id'] ?? null;
$device_type = $_GET['type'] ?? 'device';
$device = null;
$ping_result = null;
$arp_result = null;
$error = '';

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
    
    if ($check_type === 'ping') {
        // Try MikroTik API first, fallback to system ping
        $mikrotik_host = $_POST['mikrotik_host'] ?? '';
        $mikrotik_user = $_POST['mikrotik_user'] ?? '';
        $mikrotik_pass = $_POST['mikrotik_pass'] ?? '';
        
        // If no credentials provided, try to get them from the device's network skeleton device
        if (empty($mikrotik_host) && empty($mikrotik_user) && empty($mikrotik_pass) && $device['network_id']) {
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
            }
        }
        
        // Try multiple methods in order of preference
        $methods_tried = [];
        
        // Method 1: System ping (most reliable)
        $system_result = systemPing($ip, 4);
        $methods_tried[] = "System Ping";
        
        if ($system_result['success']) {
            $ping_result = [
                'method' => 'System Ping',
                'result' => $system_result,
                'success' => true
            ];
        } else {
            // Method 2: SSH to MikroTik (if credentials available)
            if ($mikrotik_host && $mikrotik_user && $mikrotik_pass) {
                $ssh_result = mikrotikSshCall($mikrotik_host, $mikrotik_user, $mikrotik_pass, "/ping address=$ip count=4", 22);
                $methods_tried[] = "MikroTik SSH";
                
                if (!empty($ssh_result) && strpos($ssh_result, 'timeout') === false) {
                    $ping_result = [
                        'method' => 'MikroTik SSH',
                        'result' => $ssh_result,
                        'success' => true
                    ];
                }
            }
            
            // Method 3: MikroTik API (if SSH failed or not available)
            if (!$ping_result && $mikrotik_host && $mikrotik_user && $mikrotik_pass) {
                try {
                    set_time_limit(30);
                    $api = new MikroTikAPI($mikrotik_host, $mikrotik_user, $mikrotik_pass, $api_port ?? 8728, $api_ssl ?? false);
                    $api_result = $api->ping($ip, 4);
                    $methods_tried[] = "MikroTik API";
                    
                    if (!isset($api_result['error'])) {
                        $ping_result = [
                            'method' => 'MikroTik API',
                            'result' => $api_result,
                            'success' => true
                        ];
                    }
                } catch (Exception $e) {
                    // API failed, continue to fallback
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
        // Try MikroTik API first, fallback to system arping
        $mikrotik_host = $_POST['mikrotik_host'] ?? '';
        $mikrotik_user = $_POST['mikrotik_user'] ?? '';
        $mikrotik_pass = $_POST['mikrotik_pass'] ?? '';
        $interface = $_POST['interface'] ?? '';
        
        // If no credentials provided, try to get them from the device's network skeleton device
        if (empty($mikrotik_host) && empty($mikrotik_user) && empty($mikrotik_pass) && $device['network_id']) {
            $network_stmt = $pdo->prepare("
                SELECT sd.ip_address, sd.api_username, sd.api_password, sd.api_port, sd.api_ssl, n.device_interface
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
                
                // Also get interface if not provided
                if (empty($interface) && $skeleton_device['device_interface']) {
                    $interface = $skeleton_device['device_interface'];
                }
            }
        }
        
        // If no interface provided, try to get it from the device's network
        if (empty($interface) && $device['network_id']) {
            $network_stmt = $pdo->prepare("SELECT device_interface FROM networks WHERE id = ?");
            $network_stmt->execute([$device['network_id']]);
            $network_interface = $network_stmt->fetchColumn();
            if ($network_interface) {
                $interface = $network_interface;
            }
        }
        
        // Validate interface name (MikroTik interface names should not contain spaces or special chars)
        if ($interface) {
            $interface = trim($interface);
            // Remove any quotes that might be in the interface name
            $interface = str_replace(['"', "'"], '', $interface);
        }
        
        // Use fast system ARP ping for reliable results
        $arp_result = [
            'method' => 'System ARP Ping',
            'result' => systemArpPing($ip, 2), // Reduced count for faster response
            'success' => true
        ];
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
              <p><strong>ID:</strong> <?= htmlspecialchars($device['id']) ?></p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Check Options -->
      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Ping Test</h5>
            </div>
            <div class="card-body">
              <?php 
              $auto_credentials = false;
              if (isset($device['network_id']) && $device['network_id']) {
                  $network_stmt = $pdo->prepare("
                      SELECT sd.ip_address, sd.api_username, sd.api_password 
                      FROM networks n 
                      JOIN skeleton_devices sd ON n.device_id = sd.id 
                      WHERE n.id = ? AND sd.api_username IS NOT NULL AND sd.api_password IS NOT NULL
                  ");
                  $network_stmt->execute([$device['network_id']]);
                  $auto_credentials = $network_stmt->fetch(PDO::FETCH_ASSOC);
              }
              ?>
              
              <form method="post">
                <input type="hidden" name="check_type" value="ping">
                
                <div class="mb-3">
                  <label class="form-label">MikroTik Router (opcjonalnie)</label>
                  <input type="text" name="mikrotik_host" class="form-control" placeholder="192.168.1.1">
                  <?php if ($auto_credentials): ?>
                    <div class="form-text text-success">✓ Auto-wykryto: <?= htmlspecialchars($auto_credentials['ip_address']) ?></div>
                  <?php endif; ?>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Użytkownik</label>
                  <input type="text" name="mikrotik_user" class="form-control" placeholder="admin">
                  <?php if ($auto_credentials): ?>
                    <div class="form-text text-success">✓ Auto-wykryto: <?= htmlspecialchars($auto_credentials['api_username']) ?></div>
                  <?php endif; ?>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Hasło</label>
                  <input type="password" name="mikrotik_pass" class="form-control">
                  <?php if ($auto_credentials): ?>
                    <div class="form-text text-success">✓ Auto-wykryto hasło z urządzenia szkieletowego</div>
                  <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary" id="pingBtn" onclick="showLoading(this, 'ping')">
                  <i class="bi bi-wifi"></i> Wykonaj Ping
                </button>
              </form>
              
              <?php if ($ping_result): ?>
                <div class="mt-3">
                  <h6>Wynik Ping (<?= $ping_result['method'] ?>):</h6>
                  <?php if (is_array($ping_result['result']) && isset($ping_result['result']['success'])): ?>
                    <div class="alert <?= $ping_result['result']['success'] ? 'alert-success' : 'alert-danger' ?>">
                      <strong>Status:</strong> <?= $ping_result['result']['success'] ? 'Online' : 'Offline' ?><br>
                      <strong>Wysłano:</strong> <?= $ping_result['result']['sent'] ?><br>
                      <strong>Otrzymano:</strong> <?= $ping_result['result']['received'] ?><br>
                      <strong>Strata:</strong> <?= $ping_result['result']['loss'] ?>%<br>
                      <?php if (isset($ping_result['result']['avg']) && $ping_result['result']['avg'] > 0): ?>
                        <strong>Średni czas:</strong> <?= $ping_result['result']['avg'] ?> ms<br>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <div class="alert alert-danger">
                      <strong>Błąd:</strong> <?= htmlspecialchars(is_string($ping_result['result']) ? $ping_result['result'] : 'Nieznany błąd') ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">ARP Ping Test</h5>
            </div>
            <div class="card-body">
              <?php 
              $auto_credentials_arp = false;
              if (isset($device['network_id']) && $device['network_id']) {
                  $network_stmt = $pdo->prepare("
                      SELECT sd.ip_address, sd.api_username, sd.api_password 
                      FROM networks n 
                      JOIN skeleton_devices sd ON n.device_id = sd.id 
                      WHERE n.id = ? AND sd.api_username IS NOT NULL AND sd.api_password IS NOT NULL
                  ");
                  $network_stmt->execute([$device['network_id']]);
                  $auto_credentials_arp = $network_stmt->fetch(PDO::FETCH_ASSOC);
              }
              ?>
              
              <form method="post">
                <input type="hidden" name="check_type" value="arp">
                
                <div class="mb-3">
                  <label class="form-label">MikroTik Router (opcjonalnie)</label>
                  <input type="text" name="mikrotik_host" class="form-control" placeholder="192.168.1.1">
                  <?php if ($auto_credentials_arp): ?>
                    <div class="form-text text-success">✓ Auto-wykryto: <?= htmlspecialchars($auto_credentials_arp['ip_address']) ?></div>
                  <?php endif; ?>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Użytkownik</label>
                  <input type="text" name="mikrotik_user" class="form-control" placeholder="admin">
                  <?php if ($auto_credentials_arp): ?>
                    <div class="form-text text-success">✓ Auto-wykryto: <?= htmlspecialchars($auto_credentials_arp['api_username']) ?></div>
                  <?php endif; ?>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Hasło</label>
                  <input type="password" name="mikrotik_pass" class="form-control">
                  <?php if ($auto_credentials_arp): ?>
                    <div class="form-text text-success">✓ Auto-wykryto hasło z urządzenia szkieletowego</div>
                  <?php endif; ?>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Interfejs (wymagany dla ARP ping)</label>
                  <?php 
                  $auto_interface = '';
                  if (isset($device['network_id']) && $device['network_id']) {
                      $network_stmt = $pdo->prepare("SELECT device_interface FROM networks WHERE id = ?");
                      $network_stmt->execute([$device['network_id']]);
                      $auto_interface = $network_stmt->fetchColumn();
                  }
                  ?>
                  <input type="text" name="interface" class="form-control" 
                         placeholder="ether1, wlan1, bridge1" 
                         value="<?= htmlspecialchars($auto_interface) ?>">
                  <small class="form-text text-muted">
                    <?php if ($auto_interface): ?>
                      <span class="text-success">✓ Auto-wykryto interfejs z sieci: <?= htmlspecialchars($auto_interface) ?></span><br>
                    <?php endif; ?>
                    Nazwa interfejsu na routerze MikroTik (np. ether1, wlan1, bridge1)
                  </small>
                </div>
                
                <button type="submit" class="btn btn-warning" id="arpBtn" onclick="showLoading(this, 'arp')">
                  <i class="bi bi-broadcast"></i> Wykonaj ARP Ping
                </button>
              </form>
              
              <?php if ($arp_result): ?>
                <div class="mt-3">
                  <h6>Wynik ARP Ping (<?= $arp_result['method'] ?>):</h6>
                  <?php if (is_array($arp_result['result']) && isset($arp_result['result']['success'])): ?>
                    <div class="alert <?= $arp_result['result']['success'] ? 'alert-success' : 'alert-danger' ?>">
                      <strong>Status:</strong> <?= $arp_result['result']['success'] ? 'Odpowiada' : 'Nie odpowiada' ?><br>
                      <strong>Wysłano:</strong> <?= $arp_result['result']['sent'] ?><br>
                      <strong>Odpowiedzi:</strong> <?= $arp_result['result']['received'] ?><br>
                    </div>
                  <?php else: ?>
                    <div class="alert alert-danger">
                      <strong>Błąd:</strong> <?= htmlspecialchars(is_string($arp_result['result']) ? $arp_result['result'] : 'Nieznany błąd') ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Raw Output -->
      <?php if ($ping_result || $arp_result): ?>
        <div class="card mt-4">
          <div class="card-header">
            <h5 class="mb-0">Szczegółowe wyniki</h5>
          </div>
          <div class="card-body">
            <?php if ($ping_result): ?>
              <h6>Ping Output:</h6>
              <pre class="bg-light p-3 rounded"><?= htmlspecialchars($ping_result['result']['output'] ?? $ping_result['result']) ?></pre>
            <?php endif; ?>
            
            <?php if ($arp_result): ?>
              <h6>ARP Ping Output:</h6>
              <pre class="bg-light p-3 rounded"><?= htmlspecialchars($arp_result['result']['output'] ?? $arp_result['result']) ?></pre>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
      
    <?php endif; ?>
  </div>
</div>
<script>
function showLoading(button, type) {
    // Disable button and show loading state
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sprawdzanie...';
    
    // Submit the form
    const form = button.closest('form');
    if (form) {
        form.submit();
    }
    
    // Show timeout warning
    setTimeout(function() {
        if (button.disabled) {
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sprawdzanie... (może potrwać do 30s)';
        }
    }, 5000);
    
    // Re-enable button after 35 seconds (in case of timeout)
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

// Show fallback message if API errors occurred
<?php if (isset($ping_result['api_error'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const pingForm = document.querySelector('form[action*="check_type=ping"]');
    if (pingForm) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-info mt-2';
        alert.innerHTML = '<small><strong>Info:</strong> Użyto systemowego ping (MikroTik API nie było dostępne: <?= addslashes($ping_result['api_error']) ?>)</small>';
        pingForm.appendChild(alert);
    }
});
<?php endif; ?>

<?php if (isset($arp_result['api_error'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const arpForm = document.querySelector('form[action*="check_type=arp"]');
    if (arpForm) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-info mt-2';
        alert.innerHTML = '<small><strong>Info:</strong> Użyto systemowego ARP ping (MikroTik API nie było dostępne: <?= addslashes($arp_result['api_error']) ?>)</small>';
        arpForm.appendChild(alert);
    }
});
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 