<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Sieci';
$pdo = get_pdo();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $network_id = $_POST['delete_id'];
    
    // Check if there are devices associated with this network
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM devices WHERE network_id = ?');
    $stmt->execute([$network_id]);
    $device_count = $stmt->fetchColumn();
    
    if ($device_count > 0) {
        // Cannot delete network with associated devices
        $error_message = "Nie można usunąć sieci - jest powiązana z $device_count urządzeniem/ami. Najpierw usuń lub przenieś urządzenia.";
    } else {
        // Safe to delete - no associated devices
        try {
            $stmt = $pdo->prepare('DELETE FROM networks WHERE id = ?');
            $stmt->execute([$network_id]);
            $success_message = 'Sieć została usunięta pomyślnie.';
        } catch (PDOException $e) {
            $error_message = 'Błąd podczas usuwania sieci: ' . $e->getMessage();
        }
    }
}

ob_start();
$networks = $pdo->query('
    SELECT n.*, sd.name as device_name, sd.ip_address as device_ip,
           (SELECT COUNT(*) FROM devices WHERE network_id = n.id) as device_count
    FROM networks n 
    LEFT JOIN skeleton_devices sd ON n.device_id = sd.id 
    ORDER BY n.name
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
      <h2 class="lms-accent">Sieci</h2>
      <a href="<?= base_url('modules/add_network.php') ?>" class="btn lms-btn-accent">Dodaj Sieć</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th><th>Nazwa</th><th>Podsieć</th><th>Urządzenia</th><th>Urządzenie</th><th>Interfejs</th><th>Opis</th><th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($networks as $network): ?>
            <tr>
              <td><?= htmlspecialchars($network['id']) ?></td>
              <td><?= htmlspecialchars($network['name']) ?></td>
              <td><?= htmlspecialchars($network['subnet']) ?></td>
              <td>
                <?php if ($network['device_count'] > 0): ?>
                  <span class="badge bg-warning"><?= $network['device_count'] ?></span>
                <?php else: ?>
                  <span class="badge bg-secondary">0</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($network['device_name']): ?>
                  <strong><?= htmlspecialchars($network['device_name']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($network['device_ip']) ?></small>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($network['device_interface']): ?>
                  <code><?= htmlspecialchars($network['device_interface']) ?></code>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($network['description']) ?></td>
              <td>
                <a href="<?= base_url('modules/edit_network.php?id=' . $network['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                <?php if ($network['device_count'] > 0): ?>
                  <button type="button" class="btn btn-sm btn-danger" disabled title="Nie można usunąć - <?= $network['device_count'] ?> urządzenie/ń powiązane">
                    Usuń (<?= $network['device_count'] ?>)
                  </button>
                  <small class="text-muted d-block">Usuń urządzenia najpierw</small>
                <?php else: ?>
                  <form method="post" action="<?= base_url('modules/networks.php') ?>" style="display:inline;" onsubmit="return confirm('Czy na pewno chcesz usunąć sieć \'<?= addslashes($network['name']) ?>\'?');">
                    <input type="hidden" name="delete_id" value="<?= $network['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                  </form>
                <?php endif; ?>
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