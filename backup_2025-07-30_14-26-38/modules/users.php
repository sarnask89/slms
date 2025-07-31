<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Użytkownicy';
$pdo = get_pdo();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$_POST['delete_id']]);
    header('Location: users.php');
    exit;
}

ob_start();
$users = $pdo->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="lms-accent">Użytkownicy</h2>
      <a href="<?= base_url('modules/add_user.php') ?>" class="btn lms-btn-accent">Dodaj Użytkownika</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th><th>Nazwa użytkownika</th><th>Email</th><th>Rola</th><th>Utworzono</th><th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?= htmlspecialchars($user['id']) ?></td>
              <td><?= htmlspecialchars($user['username']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><?= htmlspecialchars($user['role']) ?></td>
              <td><?= htmlspecialchars($user['created_at']) ?></td>
              <td>
                <a href="<?= base_url('modules/edit_user.php?id=' . $user['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                <form method="post" action="<?= base_url('modules/users.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tego użytkownika?');">
                  <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
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