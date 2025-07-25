<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Edytuj Pakiet TV';
$pdo = get_pdo();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: tv_packages.php');
    exit;
}

$error = '';
$success = '';

// Get TV package data
$stmt = $pdo->prepare('SELECT * FROM tv_packages WHERE id = ?');
$stmt->execute([$id]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    header('Location: tv_packages.php');
    exit;
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $tv_package = trim($_POST['tv_package'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    
    // Validation
    if (empty($name)) {
        $error = 'Proszę podać nazwę pakietu.';
    } elseif (empty($tv_package)) {
        $error = 'Proszę podać opis pakietu TV.';
    } elseif ($price <= 0) {
        $error = 'Cena musi być większa od 0.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE tv_packages SET name = ?, tv_package = ?, price = ? WHERE id = ?');
            $stmt->execute([$name, $tv_package, $price, $id]);
            
            $success = 'Pakiet TV został zaktualizowany pomyślnie.';
            
            // Update package data
            $package['name'] = $name;
            $package['tv_package'] = $tv_package;
            $package['price'] = $price;
        } catch (PDOException $e) {
            $error = 'Błąd podczas aktualizacji pakietu TV: ' . $e->getMessage();
        }
    }
}

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 600px; margin: 0 auto;">
    <h2 class="lms-accent mb-4">Edytuj Pakiet TV</h2>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($package['name']) ?>" required>
        <label for="name">Nazwa Pakietu</label>
      </div>
      
      <div class="form-floating mb-3">
        <textarea class="form-control" id="tv_package" name="tv_package" style="height: 100px" required><?= htmlspecialchars($package['tv_package']) ?></textarea>
        <label for="tv_package">Pakiet TV</label>
      </div>
      
      <div class="form-floating mb-3">
        <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($package['price']) ?>" min="0.01" step="0.01" required>
        <label for="price">Cena (zł)</label>
      </div>
      
      <button type="submit" class="btn lms-btn-accent w-100">Zapisz Zmiany</button>
      <a href="<?= base_url('modules/tv_packages.php') ?>" class="btn btn-secondary w-100 mt-2">Anuluj</a>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 