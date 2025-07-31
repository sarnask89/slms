<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Edytuj Urządzenie';
$pdo = get_pdo();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: devices.php'); exit; }
// Fetch device
$stmt = $pdo->prepare('SELECT * FROM devices WHERE id = ?');
$stmt->execute([$id]);
$device = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$device) { header('Location: devices.php'); exit; }
$clients = $pdo->query('SELECT id, CONCAT(COALESCE(company_name, CONCAT(first_name, " ", last_name)), " (", COALESCE(phone, email), ")") as name FROM clients')->fetchAll(PDO::FETCH_ASSOC);
$networks = $pdo->query('SELECT * FROM networks')->fetchAll(PDO::FETCH_ASSOC);
$selected_network_id = $_POST['network_id'] ?? $_GET['network_id'] ?? $device['network_id'];
$error = '';
// Helper to get all IPs in a subnet (optimized for large subnets)
function get_ips_in_subnet($subnet) {
    list($net, $mask) = explode('/', $subnet);
    $ip = ip2long($net);
    $mask = (int)$mask;
    $num_ips = pow(2, 32 - $mask);
    $max_ips = 1000; // Limit to 1000 IPs for dropdown
    $step = 1;
    if ($num_ips > $max_ips) {
        $step = ceil($num_ips / $max_ips);
    }
    $ips = [];
    for ($i = 1; $i < $num_ips - 1 && count($ips) < $max_ips; $i += $step) {
        $ips[] = long2ip($ip + $i);
    }
    return $ips;
}
// Get used IPs for the selected network (exclude this device's current IP)
$used_ips = [];
if ($selected_network_id) {
    $stmt = $pdo->prepare('SELECT ip_address FROM devices WHERE network_id = ? AND id != ?');
    $stmt->execute([$selected_network_id, $id]);
    $used_ips = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'ip_address');
}
// Get available IPs for the selected network
$available_ips = [];
$default_ip = '';
if ($selected_network_id) {
    foreach ($networks as $net) {
        if ($net['id'] == $selected_network_id) {
            $all_ips = get_ips_in_subnet($net['subnet']);
            $available_ips = array_values(array_diff($all_ips, $used_ips));
            if ($device['ip_address'] && !in_array($device['ip_address'], $available_ips)) {
                array_unshift($available_ips, $device['ip_address']);
            }
            $default_ip = $device['ip_address'] ?? ($available_ips[0] ?? '');
            break;
        }
    }
}
// Handle update
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare('UPDATE devices SET name=?, type=?, ip_address=?, mac_address=?, location=?, client_id=?, network_id=? WHERE id=?');
        $stmt->execute([
            $_POST['name'],
            $_POST['type'],
            $_POST['ip_address'],
            $_POST['mac_address'],
            $_POST['location'],
            $_POST['client_id'],
            $_POST['network_id'],
            $id
        ]);
        header('Location: devices.php');
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = 'Ten adres IP lub MAC jest już używany.';
        } else {
            $error = 'Błąd: ' . $e->getMessage();
        }
    }
}
ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 600px; margin: 0 auto;">
    <h2 class="lms-accent mb-4">Edytuj Urządzenie</h2>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="name" name="name" placeholder="Nazwa" required value="<?= htmlspecialchars($_POST['name'] ?? $device['name']) ?>">
        <label for="name">Nazwa</label>
      </div>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="type" name="type" placeholder="Typ" value="<?= htmlspecialchars($_POST['type'] ?? $device['type']) ?>">
        <label for="type">Typ</label>
      </div>
      <div class="form-floating mb-3">
        <select class="form-select" id="network_id" name="network_id" onchange="this.form.submit()" required>
          <?php foreach ($networks as $net): ?>
            <option value="<?= $net['id'] ?>"<?= $selected_network_id == $net['id'] ? ' selected' : '' ?>><?= htmlspecialchars($net['name']) ?> (<?= htmlspecialchars($net['subnet']) ?>)</option>
          <?php endforeach; ?>
        </select>
        <label for="network_id">Sieć</label>
      </div>
      <script>
      document.getElementById('network_id').addEventListener('change', function() {
          const ipField = document.getElementById('ip_address');
          if (ipField.tagName === 'INPUT') {
              ipField.value = '';
          }
      });
      </script>
      <?php 
      $total_ips = 0;
      if ($selected_network_id) {
          foreach ($networks as $net) {
              if ($net['id'] == $selected_network_id) {
                  list($net_addr, $mask) = explode('/', $net['subnet']);
                  $total_ips = pow(2, 32 - (int)$mask) - 2;
                  break;
              }
          }
      }
      ?>
      <div class="form-floating mb-3">
        <?php if ($total_ips > 1000): ?>
          <input type="text" class="form-control" id="ip_address" name="ip_address" 
                 placeholder="Wprowadź adres IP" required 
                 value="<?= htmlspecialchars($_POST['ip_address'] ?? $default_ip) ?>"
                 pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"
                 title="Wprowadź prawidłowy adres IP">
          <label for="ip_address">Adres IP (<?= number_format($total_ips) ?> dostępnych)</label>
          <div class="form-text">
            <small class="text-muted">
              Wykryto dużą sieć (<?= number_format($total_ips) ?> adresów IP). 
              Wprowadź adres IP ręcznie lub wybierz z przykładów poniżej.
            </small>
          </div>
        <?php else: ?>
          <select class="form-select" id="ip_address" name="ip_address" required>
            <?php foreach ($available_ips as $ip): ?>
              <option value="<?= $ip ?>"<?= (($_POST['ip_address'] ?? $default_ip) == $ip ? ' selected' : '') ?>><?= $ip ?></option>
            <?php endforeach; ?>
          </select>
          <label for="ip_address">Adres IP (<?= count($available_ips) ?> dostępnych)</label>
        <?php endif; ?>
      </div>
      <?php if ($total_ips > 1000): ?>
        <div class="mb-3">
          <label class="form-label">Przykładowe dostępne adresy IP:</label>
          <div class="row">
            <?php 
            $sample_count = min(10, count($available_ips));
            $sample_ips = array_slice($available_ips, 0, $sample_count);
            ?>
            <?php foreach ($sample_ips as $ip): ?>
              <div class="col-md-3 mb-1">
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" 
                        onclick="document.getElementById('ip_address').value='<?= $ip ?>'">
                  <?= $ip ?>
                </button>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if (count($available_ips) > $sample_count): ?>
            <small class="text-muted">... i <?= number_format(count($available_ips) - $sample_count) ?> więcej</small>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="mac_address" name="mac_address" placeholder="Adres MAC" value="<?= htmlspecialchars($_POST['mac_address'] ?? $device['mac_address']) ?>">
        <label for="mac_address">Adres MAC</label>
      </div>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="location" name="location" placeholder="Lokalizacja" value="<?= htmlspecialchars($_POST['location'] ?? $device['location']) ?>">
        <label for="location">Lokalizacja</label>
      </div>
      <div class="form-floating mb-3">
        <select class="form-select" id="client_id" name="client_id" required>
          <option value="" disabled>Wybierz Klienta</option>
          <?php foreach ($clients as $client): ?>
            <option value="<?= $client['id'] ?>"<?= (($_POST['client_id'] ?? $device['client_id']) == $client['id']) ? ' selected' : '' ?>><?= htmlspecialchars($client['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <label for="client_id">Klient</label>
      </div>
      <button type="submit" class="btn lms-btn-accent w-100">Aktualizuj Urządzenie</button>
      <a href="<?= base_url('modules/devices.php') ?>" class="btn btn-secondary w-100 mt-2">Anuluj</a>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php'; 