<?php
require_once 'module_loader.php';


$pageTitle = 'Edytuj Sieć';
$pdo = get_pdo();

$network_id = $_GET['id'] ?? null;
$network = null;
$error = '';
$success = '';

if ($network_id) {
    $stmt = $pdo->prepare("SELECT * FROM networks WHERE id = ?");
    $stmt->execute([$network_id]);
    $network = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$network) {
    $error = 'Sieć nie została znaleziona.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $network) {
    $name = trim($_POST['name'] ?? '');
    $subnet = trim($_POST['subnet'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $device_id = trim($_POST['device_id'] ?? '');
    $device_interface = trim($_POST['device_interface'] ?? '');

    // Validation
    if (empty($name)) {
        $error = 'Nazwa sieci jest wymagana.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE networks SET name = ?, subnet = ?, description = ?, device_id = ?, device_interface = ? WHERE id = ?');
            $stmt->execute([
                $name,
                $subnet ?: null,
                $description ?: null,
                $device_id ?: null,
                $device_interface ?: null,
                $network_id
            ]);
            
            $success = 'Sieć została zaktualizowana pomyślnie.';
            
            // Refresh network data
            $stmt = $pdo->prepare("SELECT * FROM networks WHERE id = ?");
            $stmt->execute([$network_id]);
            $network = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = 'Błąd podczas aktualizacji sieci: ' . $e->getMessage();
        }
    }
}

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 600px; margin: 0 auto;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="lms-accent">Edytuj Sieć</h2>
      <a href="<?= base_url('modules/networks.php') ?>" class="btn btn-secondary">Powrót do listy</a>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($network): ?>
      <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
          <label for="name" class="form-label">Nazwa sieci *</label>
          <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($network['name']) ?>" placeholder="Nazwa sieci" required>
          <div class="invalid-feedback">Nazwa sieci jest wymagana.</div>
        </div>

        <div class="mb-3">
          <label for="subnet" class="form-label">Podsieć</label>
          <input type="text" class="form-control" id="subnet" name="subnet" value="<?= htmlspecialchars($network['subnet'] ?? '') ?>" placeholder="np. 192.168.1.0/24">
          <div class="form-text">Format CIDR (np. 192.168.1.0/24)</div>
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Opis</label>
          <textarea class="form-control" id="description" name="description" rows="3" placeholder="Dodatkowy opis sieci..."><?= htmlspecialchars($network['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label for="device_id" class="form-label">Urządzenie szkieletowe (dla ARP ping)</label>
          <select class="form-select" id="device_id" name="device_id">
            <option value="">Wybierz urządzenie...</option>
            <?php 
            $skeleton_devices = $pdo->query('SELECT id, name, type, ip_address FROM skeleton_devices WHERE status = "active" ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($skeleton_devices as $device): ?>
              <option value="<?= $device['id'] ?>" <?= ($network['device_id'] ?? '') == $device['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($device['name']) ?> (<?= htmlspecialchars($device['type']) ?>) - <?= htmlspecialchars($device['ip_address']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Wybierz urządzenie szkieletowe do obsługi ARP ping dla tej sieci</div>
        </div>

        <div class="mb-3">
          <label for="device_interface" class="form-label">Interfejs urządzenia</label>
          <input type="text" class="form-control" id="device_interface" name="device_interface" value="<?= htmlspecialchars($network['device_interface'] ?? '') ?>" placeholder="np. ether1, wlan1, bridge1">
          <div class="form-text">Nazwa interfejsu na urządzeniu (np. ether1, wlan1, bridge1)</div>
        </div>

        <div class="d-flex justify-content-between">
          <a href="<?= base_url('modules/networks.php') ?>" class="btn btn-secondary">Anuluj</a>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Zapisz Zmiany
          </button>
        </div>
      </form>
    <?php else: ?>
      <div class="alert alert-warning">
        Sieć nie została znaleziona. <a href="<?= base_url('modules/networks.php') ?>">Powrót do listy</a>
      </div>
    <?php endif; ?>
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