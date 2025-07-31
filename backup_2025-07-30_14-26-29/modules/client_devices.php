<?php
require_once __DIR__ . '/../config.php';

$pdo = get_pdo();
$client_id = $_GET['id'] ?? null;

if (!$client_id) {
    header('Location: clients.php');
    exit;
}

// Get client info
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    header('Location: clients.php');
    exit;
}

// Get client devices
$stmt = $pdo->prepare("
    SELECT d.*, n.name as network_name 
    FROM devices d 
    LEFT JOIN networks n ON d.network_id = n.id 
    WHERE d.client_id = ? 
    ORDER BY d.name
");
$stmt->execute([$client_id]);
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-hdd-network"></i> Urządzenia klienta: <?= htmlspecialchars($client['name']) ?>
          </h5>
          <div>
            <a href="<?= base_url('modules/clients.php') ?>" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left"></i> Powrót do klientów
            </a>
            <a href="<?= base_url('modules/add_device.php?client_id=' . $client_id) ?>" class="btn btn-primary btn-sm">
              <i class="bi bi-plus"></i> Dodaj urządzenie
            </a>
          </div>
        </div>
        <div class="card-body">
          <!-- Client Info -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                  <h6 class="mb-0">Informacje o kliencie</h6>
                </div>
                <div class="card-body">
                  <p><strong>Nazwa:</strong> <?= htmlspecialchars($client['name']) ?></p>
                  <p><strong>Adres:</strong> <?= htmlspecialchars($client['address']) ?></p>
                  <p><strong>Kontakt:</strong> <?= htmlspecialchars($client['contact_info']) ?></p>
                  <p><strong>Utworzono:</strong> <?= date('d.m.Y H:i', strtotime($client['created_at'])) ?></p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card border-success">
                <div class="card-header bg-success text-white">
                  <h6 class="mb-0">Statystyki urządzeń</h6>
                </div>
                <div class="card-body">
                  <div class="row text-center">
                    <div class="col-6">
                      <h4 class="text-primary"><?= count($devices) ?></h4>
                      <small>Wszystkie urządzenia</small>
                    </div>
                    <div class="col-6">
                      <h4 class="text-success"><?= count(array_filter($devices, function($d) { return $d['type'] === 'dhcp_client'; })) ?></h4>
                      <small>Klienci DHCP</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Devices Table -->
          <?php if (empty($devices)): ?>
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> Ten klient nie ma jeszcze przypisanych urządzeń.
              <a href="<?= base_url('modules/add_device.php?client_id=' . $client_id) ?>" class="alert-link">Dodaj pierwsze urządzenie</a>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead class="table-dark">
                  <tr>
                    <th>Nazwa</th>
                    <th>Typ</th>
                    <th>Adres IP</th>
                    <th>Adres MAC</th>
                    <th>Lokalizacja</th>
                    <th>Sieć</th>
                    <th>Status</th>
                    <th>Akcje</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($devices as $device): ?>
                    <tr>
                      <td>
                        <strong><?= htmlspecialchars($device['name']) ?></strong>
                      </td>
                      <td>
                        <?php if ($device['type'] === 'dhcp_client'): ?>
                          <span class="badge bg-success">DHCP Client</span>
                        <?php else: ?>
                          <span class="badge bg-secondary"><?= htmlspecialchars($device['type']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <code><?= htmlspecialchars($device['ip_address']) ?></code>
                      </td>
                      <td>
                        <code><?= htmlspecialchars($device['mac_address']) ?></code>
                      </td>
                      <td>
                        <small><?= htmlspecialchars($device['location']) ?></small>
                      </td>
                      <td>
                        <?php if ($device['network_name']): ?>
                          <span class="badge bg-info"><?= htmlspecialchars($device['network_name']) ?></span>
                        <?php else: ?>
                          <span class="badge bg-warning">Brak sieci</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge bg-success">Aktywny</span>
                      </td>
                      <td>
                        <div class="btn-group btn-group-sm" role="group">
                          <a href="<?= base_url('modules/edit_device.php?id=' . $device['id']) ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i>
                          </a>
                          <a href="<?= base_url('modules/check_device.php?id=' . $device['id'] . '&type=device') ?>" class="btn btn-info">
                            <i class="bi bi-wifi"></i>
                          </a>
                          <button type="button" class="btn btn-danger" onclick="deleteDevice(<?= $device['id'] ?>)">
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
          
          <!-- Quick Actions -->
          <div class="row mt-4">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Szybkie akcje</h6>
                </div>
                <div class="card-body">
                  <a href="<?= base_url('modules/import_dhcp_clients_improved.php') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-upload"></i> Import DHCP
                  </a>
                  <a href="<?= base_url('modules/dhcp_clients.php') ?>" class="btn btn-outline-info">
                    <i class="bi bi-wifi"></i> DHCP klienci
                  </a>
                  <a href="<?= base_url('modules/edit_client.php?id=' . $client_id) ?>" class="btn btn-outline-warning">
                    <i class="bi bi-pencil"></i> Edytuj klienta
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function deleteDevice(deviceId) {
    if (confirm('Czy na pewno chcesz usunąć to urządzenie?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('modules/devices.php') ?>';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_id';
        input.value = deviceId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Urządzenia klienta: ' . $client['name'];
include __DIR__ . '/../partials/layout.php';
?> 