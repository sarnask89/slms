<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Edytuj Usługę';
$pdo = get_pdo();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: services.php');
    exit;
}

$clients = $pdo->query('SELECT id, name FROM clients ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$error = '';
$success = '';

// Get service data
$stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
$stmt->execute([$id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    header('Location: services.php');
    exit;
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? '';
    $service_type = $_POST['service_type'] ?? '';
    
    // Validation
    if (empty($client_id)) {
        $error = 'Proszę wybrać klienta.';
    } elseif (empty($service_type)) {
        $error = 'Proszę wybrać typ usługi.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE services SET client_id = ?, service_type = ? WHERE id = ?');
            $stmt->execute([$client_id, $service_type, $id]);
            
            $success = 'Usługa została zaktualizowana pomyślnie.';
            
            // Update service data
            $service['client_id'] = $client_id;
            $service['service_type'] = $service_type;
        } catch (PDOException $e) {
            $error = 'Błąd podczas aktualizacji usługi: ' . $e->getMessage();
        }
    }
}

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 600px; margin: 0 auto;">
    <h2 class="lms-accent mb-4">Edytuj Usługę</h2>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post">
      <div class="form-floating mb-3">
        <select class="form-select" id="client_id" name="client_id" required>
          <option value="" disabled>Wybierz Klienta</option>
          <?php foreach ($clients as $client): ?>
            <option value="<?= $client['id'] ?>"<?= $service['client_id'] == $client['id'] ? ' selected' : '' ?>><?= htmlspecialchars($client['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <label for="client_id">Klient</label>
      </div>
      
      <div class="form-floating mb-3">
        <select class="form-select" id="service_type" name="service_type" required>
          <option value="" disabled>Wybierz Typ Usługi</option>
          <option value="internet"<?= $service['service_type'] === 'internet' ? ' selected' : '' ?>>Internet</option>
          <option value="tv"<?= $service['service_type'] === 'tv' ? ' selected' : '' ?>>Telewizja</option>
        </select>
        <label for="service_type">Typ Usługi</label>
      </div>
      
      <button type="submit" class="btn lms-btn-accent w-100">Zapisz Zmiany</button>
      <a href="<?= base_url('modules/services.php') ?>" class="btn btn-secondary w-100 mt-2">Anuluj</a>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 