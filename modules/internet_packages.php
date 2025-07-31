<?php
require_once 'module_loader.php';

$pageTitle = 'Internet';
$pdo = get_pdo();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM internet_packages WHERE id = ?');
        $stmt->execute([$_POST['delete_id']]);
        $success_message = 'Pakiet Internet został usunięty pomyślnie.';
    } catch (PDOException $e) {
        $error_message = 'Błąd podczas usuwania pakietu Internet: ' . $e->getMessage();
    }
}

ob_start();
$internet_packages = $pdo->query('SELECT * FROM internet_packages ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    
    <?php if (isset($success_message)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="lms-accent">Internet</h2>
      <a href="<?= base_url('modules/add_internet_package.php') ?>" class="btn lms-btn-accent">Dodaj Pakiet Internet</a>
    </div>
    
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nazwa</th>
            <th>Pakiet Internet</th>
            <th>Cena</th>
            <th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($internet_packages as $package): ?>
            <tr>
              <td><?= htmlspecialchars($package['id']) ?></td>
              <td><?= htmlspecialchars($package['name']) ?></td>
              <td><?= htmlspecialchars($package['internet_package']) ?></td>
              <td><?= number_format($package['price'], 2) ?> zł</td>
              <td>
                <a href="<?= base_url('modules/edit_internet_package.php?id=' . $package['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                <form method="post" action="<?= base_url('modules/internet_packages.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć ten pakiet Internet?');">
                  <input type="hidden" name="delete_id" value="<?= $package['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 