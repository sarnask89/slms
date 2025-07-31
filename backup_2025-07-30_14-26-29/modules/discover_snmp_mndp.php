<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'SNMP/MNDP Discovery';
$pdo = get_pdo();
$discovered = [];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['discover_devices'])) {
    $range = $_POST['ip_range'] ?? '';
    $community = $_POST['snmp_community'] ?? 'public';
    $do_mndp = !empty($_POST['enable_mndp']);
    // SNMP Discovery
    if ($range) {
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
                    // Insert into DB if not exists
                    $stmt = $pdo->prepare("INSERT IGNORE INTO discovered_devices (ip_address, sys_name, sys_descr, method) VALUES (?, ?, ?, 'SNMP')");
                    $stmt->execute([$ip, trim(str_replace('STRING: ', '', $sysName)), trim(str_replace('STRING: ', '', $sysDescr))]);
                }
            }
        } else {
            $errors[] = 'Invalid IP range format. Use CIDR notation (e.g. 192.168.1.0/24).';
        }
    }
    // MNDP Discovery (Enhanced)
    if ($do_mndp) {
        try {
            require_once __DIR__ . '/mndp_enhanced.php';
            $mndp = new MNDPEnhanced();
            $mndp_devices = $mndp->discover(5); // 5 second timeout
            
            foreach ($mndp_devices as $device) {
                if (!in_array($device['ip'], array_column($discovered, 'ip'))) {
                    $discovered[] = [
                        'ip' => $device['ip'],
                        'sysDescr' => "MNDP: {$device['platform']} {$device['version_info']} (MAC: {$device['mac_address']})",
                        'sysName' => $device['identity'] ?: $device['platform'],
                        'method' => 'MNDP'
                    ];
                    // Insert into DB if not exists
                    $stmt = $pdo->prepare("INSERT IGNORE INTO discovered_devices (ip_address, sys_name, sys_descr, method) VALUES (?, ?, ?, 'MNDP')");
                    $stmt->execute([
                        $device['ip'], 
                        $device['identity'] ?: $device['platform'],
                        "MNDP: {$device['platform']} {$device['version_info']} (MAC: {$device['mac_address']})"
                    ]);
                }
            }
        } catch (Exception $e) {
            $errors[] = "MNDP discovery failed: " . $e->getMessage();
        }
    }
}
ob_start();
?>
<div class="container py-4">
  <h2>SNMP/MNDP Discovery</h2>
  <form method="post" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label">IP Range (CIDR)</label>
      <input type="text" name="ip_range" class="form-control" placeholder="192.168.1.0/24" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">SNMP Community</label>
      <input type="text" name="snmp_community" class="form-control" value="public">
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="enable_mndp" id="enable_mndp">
        <label class="form-check-label" for="enable_mndp">Enable MNDP</label>
      </div>
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="submit" name="discover_devices" class="btn btn-primary">Discover</button>
    </div>
  </form>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div>
  <?php endif; ?>
  <?php if ($discovered): ?>
    <h4>Discovered Devices</h4>
    <table class="table table-bordered">
      <thead><tr><th>IP</th><th>sysName</th><th>sysDescr</th><th>Method</th></tr></thead>
      <tbody>
        <?php foreach ($discovered as $dev): ?>
          <tr>
            <td><?php echo htmlspecialchars($dev['ip']); ?></td>
            <td><?php echo htmlspecialchars($dev['sysName']); ?></td>
            <td><?php echo htmlspecialchars($dev['sysDescr']); ?></td>
            <td><?php echo htmlspecialchars($dev['method']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php'; 