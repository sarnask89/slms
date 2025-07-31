<?php
require_once 'module_loader.php';

$pageTitle = 'Wyszukiwanie';
$pdo = get_pdo();

$query = $_GET['query'] ?? '';
$results = [];

if ($query) {
    // Search in clients
    $stmt = $pdo->prepare("SELECT id, name, address, contact_info as details, 'client' as type FROM clients WHERE name LIKE ? OR address LIKE ? OR contact_info LIKE ?");
    $search_term = "%$query%";
    $stmt->execute([$search_term, $search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Search in devices
    $stmt = $pdo->prepare("SELECT id, name, type as device_type, ip_address, mac_address, location as details, 'device' as type FROM devices WHERE name LIKE ? OR type LIKE ? OR ip_address LIKE ? OR mac_address LIKE ? OR location LIKE ?");
    $stmt->execute([$search_term, $search_term, $search_term, $search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Search in skeleton_devices
    $stmt = $pdo->prepare("SELECT id, name, type as device_type, ip_address, mac_address, location as details, model, manufacturer, status, 'skeleton_device' as type FROM skeleton_devices WHERE name LIKE ? OR type LIKE ? OR ip_address LIKE ? OR mac_address LIKE ? OR location LIKE ? OR model LIKE ? OR manufacturer LIKE ?");
    $stmt->execute([$search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Search in networks
    $stmt = $pdo->prepare("SELECT id, name, subnet, description as details, 'network' as type FROM networks WHERE name LIKE ? OR subnet LIKE ? OR description LIKE ?");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Search in invoices
    $stmt = $pdo->prepare("SELECT i.id, i.invoice_number as name, i.status, CONCAT('Klient: ', CONCAT(c.first_name, ' ', c.last_name), ', Kwota: ', i.amount, ' zł') as details, 'invoice' as type FROM invoices i LEFT JOIN clients c ON i.client_id = c.id WHERE i.invoice_number LIKE ? OR i.status LIKE ? OR CONCAT(c.first_name, ' ', c.last_name) LIKE ?");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Search in payments
    $stmt = $pdo->prepare("SELECT id, CONCAT('Płatność #', id) as name, method, CONCAT('Kwota: ', amount, ' zł, Data: ', payment_date) as details, 'payment' as type FROM payments WHERE method LIKE ? OR amount LIKE ? OR payment_date LIKE ?");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Search in users
    $stmt = $pdo->prepare("SELECT id, username as name, role, email as details, 'user' as type FROM users WHERE username LIKE ? OR email LIKE ? OR role LIKE ?");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Search in services
    $stmt = $pdo->prepare("SELECT s.id, s.service_type, CONCAT(s.service_type, ' - ', CONCAT(c.first_name, ' ', c.last_name)) as name, CONCAT('Klient: ', CONCAT(c.first_name, ' ', c.last_name), ', Status: ', s.status) as details, 'service' as type FROM services s LEFT JOIN clients c ON s.client_id = c.id WHERE s.service_type LIKE ? OR s.status LIKE ? OR CONCAT(c.first_name, ' ', c.last_name) LIKE ?");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Search in tariffs
    $stmt = $pdo->prepare("SELECT id, name, CONCAT('Upload: ', upload_speed, ' Mbps, Download: ', download_speed, ' Mbps') as details, 'tariff' as type FROM tariffs WHERE name LIKE ? OR upload_speed LIKE ? OR download_speed LIKE ?");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Search in tv_packages
    $stmt = $pdo->prepare("SELECT id, name, description as details, 'tv_package' as type FROM tv_packages WHERE name LIKE ? OR description LIKE ?");
    $stmt->execute([$search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Search in internet_packages
    $stmt = $pdo->prepare("SELECT id, name, internet_package as details, 'internet_package' as type FROM internet_packages WHERE name LIKE ? OR internet_package LIKE ?");
    $stmt->execute([$search_term, $search_term]);
    $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
}

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <h2 class="lms-accent mb-4">Wyszukiwanie</h2>
    
    <form method="get" class="mb-4">
      <div class="input-group">
        <input type="text" name="query" class="form-control" placeholder="Wpisz szukaną frazę..." value="<?= htmlspecialchars($query) ?>" required>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search"></i> Szukaj
        </button>
      </div>
    </form>
    
    <?php if ($query): ?>
      <h4>Wyniki wyszukiwania dla "<?= htmlspecialchars($query) ?>" (<?= count($results) ?> wyników)</h4>
      
      <?php if (empty($results)): ?>
        <div class="alert alert-info">
          <i class="bi bi-info-circle"></i> Nie znaleziono żadnych wyników dla podanej frazy.
        </div>
      <?php else: ?>
        <div class="row">
          <?php foreach ($results as $result): ?>
            <div class="col-md-6 col-lg-4 mb-3">
              <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="mb-0">
                    <?php
                    $type_icons = [
                        'client' => 'bi-people',
                        'device' => 'bi-hdd-network',
                        'skeleton_device' => 'bi-server',
                        'network' => 'bi-diagram-3',
                        'invoice' => 'bi-receipt',
                        'payment' => 'bi-credit-card',
                        'user' => 'bi-person',
                        'service' => 'bi-gear',
                        'tariff' => 'bi-speedometer2',
                        'tv_package' => 'bi-tv',
                        'internet_package' => 'bi-wifi'
                    ];
                    $icon = $type_icons[$result['type']] ?? 'bi-question-circle';
                    ?>
                    <i class="bi <?= $icon ?>"></i>
                    <?= ucfirst($result['type']) ?>
                  </h6>
                  <span class="badge bg-secondary"><?= $result['id'] ?></span>
                </div>
                <div class="card-body">
                  <h6 class="card-title"><?= htmlspecialchars($result['name']) ?></h6>
                  
                  <?php if ($result['type'] === 'client'): ?>
                    <p class="card-text">
                      <strong>Adres:</strong> <?= htmlspecialchars($result['address']) ?><br>
                      <strong>Kontakt:</strong> <?= htmlspecialchars($result['details']) ?>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_client.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <form method="post" action="<?= base_url('modules/clients.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tego klienta?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                    
                  <?php elseif ($result['type'] === 'device'): ?>
                    <p class="card-text">
                      <strong>Typ:</strong> <?= htmlspecialchars($result['device_type']) ?><br>
                      <strong>IP:</strong> <?= htmlspecialchars($result['ip_address']) ?><br>
                      <strong>MAC:</strong> <?= htmlspecialchars($result['mac_address']) ?><br>
                      <strong>Lokalizacja:</strong> <?= htmlspecialchars($result['details']) ?>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_device.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <a href="<?= base_url('modules/check_device.php?id=' . $result['id']) ?>" class="btn btn-sm btn-info">Sprawdź</a>
                      <form method="post" action="<?= base_url('modules/devices.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć to urządzenie?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                    
                  <?php elseif ($result['type'] === 'skeleton_device'): ?>
                    <p class="card-text">
                      <strong>Typ:</strong> <?= htmlspecialchars($result['device_type']) ?><br>
                      <strong>IP:</strong> <?= htmlspecialchars($result['ip_address']) ?><br>
                      <strong>MAC:</strong> <?= htmlspecialchars($result['mac_address']) ?><br>
                      <strong>Lokalizacja:</strong> <?= htmlspecialchars($result['details']) ?><br>
                      <strong>Model:</strong> <?= htmlspecialchars($result['model']) ?><br>
                      <strong>Producent:</strong> <?= htmlspecialchars($result['manufacturer']) ?><br>
                      <strong>Status:</strong> 
                      <?php
                      $status_class = match($result['status']) {
                          'active' => 'success',
                          'inactive' => 'warning',
                          'maintenance' => 'info',
                          default => 'secondary'
                      };
                      $status_text = match($result['status']) {
                          'active' => 'Aktywne',
                          'inactive' => 'Nieaktywne',
                          'maintenance' => 'Konserwacja',
                          default => 'Nieznany'
                      };
                      ?>
                      <span class="badge bg-<?= $status_class ?>"><?= $status_text ?></span>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_skeleton_device.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <a href="<?= base_url('modules/check_device.php?id=' . $result['id'] . '&type=skeleton') ?>" class="btn btn-sm btn-info">Sprawdź</a>
                      <form method="post" action="<?= base_url('modules/skeleton_devices.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć to urządzenie szkieletowe?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                    
                  <?php elseif ($result['type'] === 'network'): ?>
                    <p class="card-text">
                      <strong>Podsieć:</strong> <?= htmlspecialchars($result['subnet']) ?><br>
                      <strong>Opis:</strong> <?= htmlspecialchars($result['details']) ?>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_network.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <form method="post" action="<?= base_url('modules/networks.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tę sieć?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                    
                  <?php elseif ($result['type'] === 'invoice'): ?>
                    <p class="card-text">
                      <strong>Status:</strong> <?= htmlspecialchars($result['status']) ?><br>
                      <strong>Szczegóły:</strong> <?= htmlspecialchars($result['details']) ?>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_invoice.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <form method="post" action="<?= base_url('modules/invoices.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tę fakturę?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                    
                  <?php elseif ($result['type'] === 'payment'): ?>
                    <p class="card-text">
                      <strong>Metoda:</strong> <?= htmlspecialchars($result['method']) ?><br>
                      <strong>Szczegóły:</strong> <?= htmlspecialchars($result['details']) ?>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_payment.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <form method="post" action="<?= base_url('modules/payments.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tę płatność?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                    
                  <?php elseif ($result['type'] === 'user'): ?>
                    <p class="card-text">
                      <strong>Rola:</strong> <?= htmlspecialchars($result['role']) ?><br>
                      <strong>Email:</strong> <?= htmlspecialchars($result['details']) ?>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_user.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <form method="post" action="<?= base_url('modules/users.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tego użytkownika?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                    
                  <?php elseif ($result['type'] === 'service'): ?>
                    <p class="card-text">
                      <strong>Typ:</strong> <?= htmlspecialchars($result['service_type']) ?><br>
                      <strong>Szczegóły:</strong> <?= htmlspecialchars($result['details']) ?>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_service.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <form method="post" action="<?= base_url('modules/services.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tę usługę?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                    
                  <?php elseif ($result['type'] === 'tariff'): ?>
                    <p class="card-text">
                      <strong>Szczegóły:</strong> <?= htmlspecialchars($result['details']) ?>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_tariff.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <form method="post" action="<?= base_url('modules/tariffs.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tę taryfę?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                    
                  <?php elseif ($result['type'] === 'tv_package'): ?>
                    <p class="card-text">
                      <strong>Szczegóły:</strong> <?= htmlspecialchars($result['details']) ?>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_tv_package.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <form method="post" action="<?= base_url('modules/tv_packages.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć ten pakiet TV?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                    
                  <?php elseif ($result['type'] === 'internet_package'): ?>
                    <p class="card-text">
                      <strong>Szczegóły:</strong> <?= htmlspecialchars($result['details']) ?>
                    </p>
                    <div class="btn-group" role="group">
                      <a href="<?= base_url('modules/edit_internet_package.php?id=' . $result['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <form method="post" action="<?= base_url('modules/internet_packages.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć ten pakiet Internet?');">
                        <input type="hidden" name="delete_id" value="<?= $result['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="alert alert-info">
        <h5>Jak wyszukiwać:</h5>
        <ul class="mb-0">
          <li><strong>Klienci:</strong> nazwa, adres, informacje kontaktowe</li>
          <li><strong>Urządzenia klienckie:</strong> nazwa, typ, adres IP, adres MAC, lokalizacja</li>
          <li><strong>Urządzenia szkieletowe:</strong> nazwa, typ, model, producent, lokalizacja</li>
          <li><strong>Sieci:</strong> nazwa, podsieć, opis</li>
          <li><strong>Usługi:</strong> typ usługi (internet/tv), nazwa klienta</li>
          <li><strong>Taryfy:</strong> nazwa, prędkości</li>
          <li><strong>Pakiety TV:</strong> nazwa, opis pakietu</li>
          <li><strong>Pakiety Internet:</strong> nazwa, opis pakietu</li>
          <li><strong>Faktury:</strong> numer, status, nazwa klienta</li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 