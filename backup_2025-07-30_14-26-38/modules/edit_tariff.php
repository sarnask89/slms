<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Edytuj Taryfę';
$pdo = get_pdo();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: tariffs.php');
    exit;
}

$error = '';
$success = '';

// Get tariff data
$stmt = $pdo->prepare('SELECT * FROM tariffs WHERE id = ?');
$stmt->execute([$id]);
$tariff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tariff) {
    header('Location: tariffs.php');
    exit;
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $upload_speed = (int)($_POST['upload_speed'] ?? 0);
    $download_speed = (int)($_POST['download_speed'] ?? 0);
    $tv_included = isset($_POST['tv_included']) ? 1 : 0;
    $internet_included = isset($_POST['internet_included']) ? 1 : 0;
    
    // Validation
    if (empty($name)) {
        $error = 'Proszę podać nazwę taryfy.';
    } elseif ($upload_speed <= 0) {
        $error = 'Prędkość upload musi być większa od 0.';
    } elseif ($download_speed <= 0) {
        $error = 'Prędkość download musi być większa od 0.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE tariffs SET name = ?, upload_speed = ?, download_speed = ?, tv_included = ?, internet_included = ? WHERE id = ?');
            $stmt->execute([$name, $upload_speed, $download_speed, $tv_included, $internet_included, $id]);
            
            $success = 'Taryfa została zaktualizowana pomyślnie.';
            
            // Update tariff data
            $tariff['name'] = $name;
            $tariff['upload_speed'] = $upload_speed;
            $tariff['download_speed'] = $download_speed;
            $tariff['tv_included'] = $tv_included;
            $tariff['internet_included'] = $internet_included;
        } catch (PDOException $e) {
            $error = 'Błąd podczas aktualizacji taryfy: ' . $e->getMessage();
        }
    }
}

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 600px; margin: 0 auto;">
    <h2 class="lms-accent mb-4">Edytuj Taryfę</h2>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($tariff['name']) ?>" required>
        <label for="name">Nazwa Taryfy</label>
      </div>
      
      <div class="form-floating mb-3">
        <input type="number" class="form-control" id="upload_speed" name="upload_speed" value="<?= htmlspecialchars($tariff['upload_speed']) ?>" min="1" required>
        <label for="upload_speed">Prędkość Upload (Mbps)</label>
      </div>
      
      <div class="form-floating mb-3">
        <input type="number" class="form-control" id="download_speed" name="download_speed" value="<?= htmlspecialchars($tariff['download_speed']) ?>" min="1" required>
        <label for="download_speed">Prędkość Download (Mbps)</label>
      </div>
      
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="tv_included" name="tv_included"<?= $tariff['tv_included'] ? ' checked' : '' ?>>
        <label class="form-check-label" for="tv_included">
          Telewizja w pakiecie
        </label>
      </div>
      
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="internet_included" name="internet_included"<?= $tariff['internet_included'] ? ' checked' : '' ?>>
        <label class="form-check-label" for="internet_included">
          Internet w pakiecie
        </label>
      </div>
      
      <button type="submit" class="btn lms-btn-accent w-100">Zapisz Zmiany</button>
      <a href="<?= base_url('modules/tariffs.php') ?>" class="btn btn-secondary w-100 mt-2">Anuluj</a>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 