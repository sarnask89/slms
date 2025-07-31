<?php
require_once 'module_loader.php';

$pageTitle = 'Taryfy';
$pdo = get_pdo();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM tariffs WHERE id = ?');
        $stmt->execute([$_POST['delete_id']]);
        $success_message = 'Taryfa została usunięta pomyślnie.';
    } catch (PDOException $e) {
        $error_message = 'Błąd podczas usuwania taryfy: ' . $e->getMessage();
    }
}

ob_start();
$tariffs = $pdo->query('SELECT * FROM tariffs ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
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
      <h2 class="lms-accent">Taryfy</h2>
      <a href="<?= base_url('modules/add_tariff.php') ?>" class="btn lms-btn-accent">Dodaj Taryfę</a>
    </div>
    
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nazwa</th>
            <th>Prędkość Upload</th>
            <th>Prędkość Download</th>
            <th>Telewizja</th>
            <th>Internet</th>
            <th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tariffs as $tariff): ?>
            <tr>
              <td><?= htmlspecialchars($tariff['id']) ?></td>
              <td><?= htmlspecialchars($tariff['name']) ?></td>
              <td><?= htmlspecialchars($tariff['upload_speed']) ?> Mbps</td>
              <td><?= htmlspecialchars($tariff['download_speed']) ?> Mbps</td>
              <td>
                <?php if ($tariff['tv_included']): ?>
                  <span class="badge bg-success">✓</span>
                <?php else: ?>
                  <span class="badge bg-secondary">-</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($tariff['internet_included']): ?>
                  <span class="badge bg-success">✓</span>
                <?php else: ?>
                  <span class="badge bg-secondary">-</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= base_url('modules/edit_tariff.php?id=' . $tariff['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                <form method="post" action="<?= base_url('modules/tariffs.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tę taryfę?');">
                  <input type="hidden" name="delete_id" value="<?= $tariff['id'] ?>">
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