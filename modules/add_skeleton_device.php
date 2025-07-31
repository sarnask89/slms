<?php
require_once 'module_loader.php';


$pageTitle = 'Dodaj Urządzenie Szkieletowe';
$pdo = get_pdo();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $ip_address = trim($_POST['ip_address'] ?? '');
    $mac_address = trim($_POST['mac_address'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $manufacturer = trim($_POST['manufacturer'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $description = trim($_POST['description'] ?? '');
    $api_username = trim($_POST['api_username'] ?? '');
    $api_password = trim($_POST['api_password'] ?? '');
    $api_port = trim($_POST['api_port'] ?? '8728');
    $api_ssl = isset($_POST['api_ssl']) ? 1 : 0;

    // Validation
    if (empty($name)) {
        $error = 'Nazwa urządzenia jest wymagana.';
    } elseif (empty($type)) {
        $error = 'Typ urządzenia jest wymagany.';
    } elseif (!empty($ip_address) && !filter_var($ip_address, FILTER_VALIDATE_IP)) {
        $error = 'Nieprawidłowy adres IP.';
    } elseif (!empty($mac_address) && !preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $mac_address)) {
        $error = 'Nieprawidłowy adres MAC (format: XX:XX:XX:XX:XX:XX).';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO skeleton_devices (name, type, ip_address, mac_address, location, model, manufacturer, status, description, api_username, api_password, api_port, api_ssl)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $type, $ip_address ?: null, $mac_address ?: null, $location, $model, $manufacturer, $status, $description, $api_username ?: null, $api_password ?: null, $api_port, $api_ssl]);
            
            $success = 'Urządzenie szkieletowe zostało dodane pomyślnie.';
            
            // Clear form
            $name = $type = $ip_address = $mac_address = $location = $model = $manufacturer = $description = $api_username = $api_password = '';
            $status = 'active';
            $api_port = '8728';
            $api_ssl = 0;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Urządzenie z tym adresem IP lub MAC już istnieje.';
            } else {
                $error = 'Błąd podczas dodawania urządzenia: ' . $e->getMessage();
            }
        }
    }
}

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="lms-accent">Dodaj Urządzenie Szkieletowe</h2>
      <a href="<?= base_url('modules/skeleton_devices.php') ?>" class="btn btn-secondary">Powrót do listy</a>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="needs-validation" novalidate>
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="name" class="form-label">Nazwa urządzenia *</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
            <div class="invalid-feedback">Nazwa urządzenia jest wymagana.</div>
          </div>

          <div class="mb-3">
            <label for="type" class="form-label">Typ urządzenia *</label>
            <select class="form-select" id="type" name="type" required>
              <option value="">Wybierz typ...</option>
              <option value="router" <?= ($type ?? '') === 'router' ? 'selected' : '' ?>>Router</option>
              <option value="switch" <?= ($type ?? '') === 'switch' ? 'selected' : '' ?>>Switch</option>
              <option value="firewall" <?= ($type ?? '') === 'firewall' ? 'selected' : '' ?>>Firewall</option>
              <option value="controller" <?= ($type ?? '') === 'controller' ? 'selected' : '' ?>>Controller</option>
              <option value="ups" <?= ($type ?? '') === 'ups' ? 'selected' : '' ?>>UPS</option>
              <option value="server" <?= ($type ?? '') === 'server' ? 'selected' : '' ?>>Server</option>
              <option value="storage" <?= ($type ?? '') === 'storage' ? 'selected' : '' ?>>Storage</option>
              <option value="other" <?= ($type ?? '') === 'other' ? 'selected' : '' ?>>Inne</option>
            </select>
            <div class="invalid-feedback">Typ urządzenia jest wymagany.</div>
          </div>

          <div class="mb-3">
            <label for="ip_address" class="form-label">Adres IP</label>
            <input type="text" class="form-control" id="ip_address" name="ip_address" value="<?= htmlspecialchars($ip_address ?? '') ?>" placeholder="192.168.1.1">
            <div class="form-text">Opcjonalny adres IP urządzenia</div>
          </div>

          <div class="mb-3">
            <label for="mac_address" class="form-label">Adres MAC</label>
            <input type="text" class="form-control" id="mac_address" name="mac_address" value="<?= htmlspecialchars($mac_address ?? '') ?>" placeholder="00:11:22:33:44:55">
            <div class="form-text">Opcjonalny adres MAC urządzenia</div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            <label for="location" class="form-label">Lokalizacja</label>
            <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($location ?? '') ?>" placeholder="Data Center, Room 101, etc.">
          </div>

          <div class="mb-3">
            <label for="model" class="form-label">Model</label>
            <input type="text" class="form-control" id="model" name="model" value="<?= htmlspecialchars($model ?? '') ?>" placeholder="RB4011, CRS326, etc.">
          </div>

          <div class="mb-3">
            <label for="manufacturer" class="form-label">Producent</label>
            <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="<?= htmlspecialchars($manufacturer ?? '') ?>" placeholder="MikroTik, Cisco, APC, etc.">
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="active" <?= ($status ?? 'active') === 'active' ? 'selected' : '' ?>>Aktywne</option>
              <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Nieaktywne</option>
              <option value="maintenance" <?= ($status ?? '') === 'maintenance' ? 'selected' : '' ?>>Konserwacja</option>
            </select>
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Opis</label>
        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Dodatkowy opis urządzenia..."><?= htmlspecialchars($description ?? '') ?></textarea>
      </div>

      <!-- API Configuration Section -->
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0">Konfiguracja API MikroTik</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="api_username" class="form-label">Nazwa użytkownika API</label>
                <input type="text" class="form-control" id="api_username" name="api_username" value="<?= htmlspecialchars($api_username ?? '') ?>" placeholder="admin">
                <div class="form-text">Nazwa użytkownika do API MikroTik</div>
              </div>

              <div class="mb-3">
                <label for="api_password" class="form-label">Hasło API</label>
                <input type="password" class="form-control" id="api_password" name="api_password" value="<?= htmlspecialchars($api_password ?? '') ?>" placeholder="Hasło">
                <div class="form-text">Hasło do API MikroTik</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="api_port" class="form-label">Port API</label>
                <input type="number" class="form-control" id="api_port" name="api_port" value="<?= htmlspecialchars($api_port ?? '8728') ?>" min="1" max="65535">
                <div class="form-text">Port API (8728 dla TCP, 8729 dla SSL)</div>
              </div>

              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="api_ssl" name="api_ssl" <?= ($api_ssl ?? 0) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="api_ssl">
                    Użyj SSL/TLS
                  </label>
                  <div class="form-text">Zaznacz dla połączenia SSL (port 8729)</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between">
        <a href="<?= base_url('modules/skeleton_devices.php') ?>" class="btn btn-secondary">Anuluj</a>
        <button type="submit" class="btn btn-success">
          <i class="bi bi-plus-circle"></i> Dodaj Urządzenie
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Form validation
(function() {
  'use strict';
  window.addEventListener('load', function() {
    var forms = document.getElementsByClassName('needs-validation');
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 