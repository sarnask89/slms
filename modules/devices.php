<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Urządzenia Klienckie';
$pdo = get_pdo();
// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM devices WHERE id = ?');
    $stmt->execute([$_POST['delete_id']]);
    header('Location: devices.php');
    exit;
}
ob_start();
$devices = $pdo->query('
    SELECT d.*, CONCAT(c.first_name, " ", c.last_name) as client_name 
    FROM devices d 
    LEFT JOIN clients c ON d.client_id = c.id 
    ORDER BY d.name
')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="lms-accent">Urządzenia Klienckie</h2>
      <a href="<?= base_url('modules/add_device.php') ?>" class="btn lms-btn-accent">Dodaj Urządzenie</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th><th>Nazwa</th><th>Typ</th><th>Adres IP</th><th>Adres MAC</th><th>Lokalizacja</th><th>Klient</th><th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($devices as $device): ?>
            <tr>
              <td><?= htmlspecialchars($device['id']) ?></td>
              <td><?= htmlspecialchars($device['name']) ?></td>
              <td><?= htmlspecialchars($device['type']) ?></td>
              <td><?= htmlspecialchars($device['ip_address']) ?></td>
              <td><?= htmlspecialchars($device['mac_address']) ?></td>
              <td><?= htmlspecialchars($device['location']) ?></td>
              <td>
                <?php if ($device['client_name']): ?>
                  <span class="badge bg-primary"><?= htmlspecialchars($device['client_name']) ?></span>
                <?php else: ?>
                  <span class="badge bg-warning">Nieprzypisane</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= base_url('modules/edit_device.php?id=' . $device['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                <a href="<?= base_url('modules/check_device.php?id=' . $device['id'] . '&type=device') ?>" class="btn btn-sm btn-info">Sprawdź</a>
                <form method="post" action="<?= base_url('modules/devices.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć to urządzenie?');">
                  <input type="hidden" name="delete_id" value="<?= $device['id'] ?>">
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