<?php
if (session_status() === PHP_SESSION_NONE) {
    if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { session_start(); } }
}
require_once __DIR__ . '/../config.php';

$pageTitle = 'Urządzenia Szkieletowe';
$pdo = get_pdo();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM skeleton_devices WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: skeleton_devices.php");
    exit;
}

// Handle quick test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_id'])) {
    $test_id = $_POST['test_id'];
    $stmt = $pdo->prepare("SELECT * FROM skeleton_devices WHERE id = ?");
    $stmt->execute([$test_id]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($device) {
        // Perform quick connectivity test
        $ping_result = shell_exec("ping -c 2 " . escapeshellarg($device['ip_address']) . " 2>&1");
        $ping_success = strpos($ping_result, '0% packet loss') !== false;
        
        // Test API port if credentials are available
        $api_test = '';
        if (!empty($device['api_username']) && !empty($device['api_password'])) {
            $api_port = 8728;
            $connection = @fsockopen($device['ip_address'], $api_port, $errno, $errstr, 3);
            if ($connection) {
                $api_test = '✓ API port accessible';
                fclose($connection);
            } else {
                $api_test = '✗ API port not accessible';
            }
        }
        
        $test_result = [
            'device' => $device['name'],
            'ip' => $device['ip_address'],
            'ping' => $ping_success ? '✓ Online' : '✗ Offline',
            'ping_details' => $ping_result,
            'api' => $api_test
        ];
        
        // Store result in session for display
        $_SESSION['test_result'] = $test_result;
    }
    
    header("Location: skeleton_devices.php");
    exit;
}

// Get all skeleton devices with search
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(name LIKE ? OR ip_address LIKE ? OR mac_address LIKE ? OR location LIKE ? OR model LIKE ? OR manufacturer LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param, $search_param]);
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$stmt = $pdo->prepare("
    SELECT * FROM skeleton_devices 
    $where_clause 
    ORDER BY name ASC
");
$stmt->execute($params);
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get status counts for filter
$status_counts = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM skeleton_devices 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="lms-accent">Urządzenia Szkieletowe</h2>
      <a href="<?= base_url('modules/add_skeleton_device.php') ?>" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Dodaj Urządzenie
      </a>
    </div>

    <!-- Test Result Display -->
    <?php
    if (isset($_SESSION['test_result'])) {
        $result = $_SESSION['test_result'];
        $alert_class = strpos($result['ping'], '✓') !== false ? 'alert-success' : 'alert-danger';
        ?>
        <div class="alert <?= $alert_class ?> alert-dismissible fade show" role="alert">
          <h6 class="alert-heading">Test Result: <?= htmlspecialchars($result['device']) ?> (<?= htmlspecialchars($result['ip']) ?>)</h6>
          <p class="mb-1"><strong>Ping:</strong> <?= htmlspecialchars($result['ping']) ?></p>
          <?php if ($result['api']): ?>
            <p class="mb-1"><strong>API:</strong> <?= htmlspecialchars($result['api']) ?></p>
          <?php endif; ?>
          <details class="mt-2">
            <summary>Ping Details</summary>
            <pre class="mt-2 mb-0 small"><?= htmlspecialchars($result['ping_details']) ?></pre>
          </details>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php
        unset($_SESSION['test_result']);
    }
    ?>

    <!-- Search and Filter -->
    <div class="row mb-4">
      <div class="col-md-6">
        <form method="get" class="d-flex">
          <input type="text" name="search" class="form-control me-2" placeholder="Szukaj urządzeń..." value="<?= htmlspecialchars($search) ?>">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-search"></i>
          </button>
        </form>
      </div>
      <div class="col-md-6">
        <div class="btn-group" role="group">
          <a href="?<?= $search ? 'search=' . urlencode($search) . '&' : '' ?>" class="btn btn-outline-secondary <?= !$status_filter ? 'active' : '' ?>">
            Wszystkie (<?= array_sum($status_counts) ?>)
          </a>
          <a href="?status=active<?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn btn-outline-success <?= $status_filter === 'active' ? 'active' : '' ?>">
            Aktywne (<?= $status_counts['active'] ?? 0 ?>)
          </a>
          <a href="?status=inactive<?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn btn-outline-warning <?= $status_filter === 'inactive' ? 'active' : '' ?>">
            Nieaktywne (<?= $status_counts['inactive'] ?? 0 ?>)
          </a>
          <a href="?status=maintenance<?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn btn-outline-info <?= $status_filter === 'maintenance' ? 'active' : '' ?>">
            Konserwacja (<?= $status_counts['maintenance'] ?? 0 ?>)
          </a>
        </div>
      </div>
    </div>

    <!-- Devices Table -->
    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>Nazwa</th>
            <th>Typ</th>
            <th>IP</th>
            <th>MAC</th>
            <th>Lokalizacja</th>
            <th>Model</th>
            <th>Producent</th>
            <th>Status</th>
            <th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($devices as $device): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($device['name']) ?></strong>
                <?php if ($device['description']): ?>
                  <br><small class="text-muted"><?= htmlspecialchars($device['description']) ?></small>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge bg-secondary"><?= htmlspecialchars($device['type']) ?></span>
              </td>
              <td>
                <code><?= htmlspecialchars($device['ip_address']) ?></code>
              </td>
              <td>
                <code><?= htmlspecialchars($device['mac_address']) ?></code>
              </td>
              <td><?= htmlspecialchars($device['location']) ?></td>
              <td><?= htmlspecialchars($device['model']) ?></td>
              <td><?= htmlspecialchars($device['manufacturer']) ?></td>
              <td>
                <?php
                $status_class = match($device['status']) {
                    'active' => 'success',
                    'inactive' => 'warning',
                    'maintenance' => 'info',
                    default => 'secondary'
                };
                $status_text = match($device['status']) {
                    'active' => 'Aktywne',
                    'inactive' => 'Nieaktywne',
                    'maintenance' => 'Konserwacja',
                    default => 'Nieznany'
                };
                ?>
                <span class="badge bg-<?= $status_class ?>"><?= $status_text ?></span>
                <?php if ($device['api_username'] && $device['api_password']): ?>
                  <br><small class="text-success">✓ API skonfigurowane</small>
                <?php else: ?>
                  <br><small class="text-muted">- API nie skonfigurowane</small>
                <?php endif; ?>
              </td>
              <td>
                <div class="btn-group" role="group">
                  <a href="<?= base_url('modules/edit_skeleton_device.php?id=' . $device['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                  <a href="<?= base_url('modules/check_device.php?id=' . $device['id'] . '&type=skeleton') ?>" class="btn btn-sm btn-info">Sprawdź</a>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Testować połączenie z urządzeniem <?= htmlspecialchars($device['name']) ?>?');">
                    <input type="hidden" name="test_id" value="<?= $device['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-warning" title="Szybki test połączenia">
                      <i class="bi bi-wifi"></i> Test
                    </button>
                  </form>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Usunąć to urządzenie szkieletowe?');">
                    <input type="hidden" name="delete_id" value="<?= $device['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (empty($devices)): ?>
      <div class="text-center py-4">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <h4 class="text-muted mt-3">Brak urządzeń szkieletowych</h4>
        <p class="text-muted">Dodaj pierwsze urządzenie szkieletowe, aby rozpocząć.</p>
        <a href="<?= base_url('modules/add_skeleton_device.php') ?>" class="btn btn-primary">
          <i class="bi bi-plus-circle"></i> Dodaj Urządzenie
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 