<?php
require_once 'module_loader.php';

$pageTitle = 'Usługi';
$pdo = get_pdo();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
        $stmt->execute([$_POST['delete_id']]);
        $success_message = 'Usługa została usunięta pomyślnie.';
    } catch (PDOException $e) {
        $error_message = 'Błąd podczas usuwania usługi: ' . $e->getMessage();
    }
}

ob_start();
$services = $pdo->query('
    SELECT s.*, CONCAT(c.first_name, " ", c.last_name) as client_name 
    FROM services s 
    JOIN clients c ON s.client_id = c.id 
    ORDER BY s.created_at DESC
')->fetchAll(PDO::FETCH_ASSOC);
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
      <h2 class="lms-accent">Usługi</h2>
      <a href="<?= base_url('modules/add_service.php') ?>" class="btn lms-btn-accent">Dodaj Usługę</a>
    </div>
    
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Klient</th>
            <th>Telewizja</th>
            <th>Internet</th>
            <th>Data utworzenia</th>
            <th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($services as $service): ?>
            <tr>
              <td><?= htmlspecialchars($service['id']) ?></td>
              <td><?= htmlspecialchars($service['client_name']) ?></td>
              <td>
                <?php if ($service['service_type'] === 'tv'): ?>
                  <span class="badge bg-success">✓</span>
                <?php else: ?>
                  <span class="badge bg-secondary">-</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($service['service_type'] === 'internet'): ?>
                  <span class="badge bg-success">✓</span>
                <?php else: ?>
                  <span class="badge bg-secondary">-</span>
                <?php endif; ?>
              </td>
              <td><?= date('d.m.Y H:i', strtotime($service['created_at'])) ?></td>
              <td>
                <a href="<?= base_url('modules/edit_service.php?id=' . $service['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                <form method="post" action="<?= base_url('modules/services.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tę usługę?');">
                  <input type="hidden" name="delete_id" value="<?= $service['id'] ?>">
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