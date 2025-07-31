<?php
require_once 'module_loader.php';

$pageTitle = 'Dodaj Pakiet Internet';
$pdo = get_pdo();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $internet_package = trim($_POST['internet_package'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    
    // Validation
    if (empty($name)) {
        $error = 'Proszę podać nazwę pakietu.';
    } elseif (empty($internet_package)) {
        $error = 'Proszę podać opis pakietu Internet.';
    } elseif ($price <= 0) {
        $error = 'Cena musi być większa od 0.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO internet_packages (name, internet_package, price) VALUES (?, ?, ?)');
            $stmt->execute([$name, $internet_package, $price]);
            
            $success = 'Pakiet Internet został dodany pomyślnie.';
            
            // Clear form
            $name = $internet_package = '';
            $price = 0;
        } catch (PDOException $e) {
            $error = 'Błąd podczas dodawania pakietu Internet: ' . $e->getMessage();
        }
    }
}

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 600px; margin: 0 auto;">
    <h2 class="lms-accent mb-4">Dodaj Pakiet Internet</h2>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
        <label for="name">Nazwa Pakietu</label>
      </div>
      
      <div class="form-floating mb-3">
        <textarea class="form-control" id="internet_package" name="internet_package" style="height: 100px" required><?= htmlspecialchars($internet_package ?? '') ?></textarea>
        <label for="internet_package">Pakiet Internet</label>
      </div>
      
      <div class="form-floating mb-3">
        <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($price ?? '') ?>" min="0.01" step="0.01" required>
        <label for="price">Cena (zł)</label>
      </div>
      
      <button type="submit" class="btn lms-btn-accent w-100">Dodaj Pakiet Internet</button>
      <a href="<?= base_url('modules/internet_packages.php') ?>" class="btn btn-secondary w-100 mt-2">Anuluj</a>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 