<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Płatności';
$pdo = get_pdo();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM payments WHERE id = ?');
    $stmt->execute([$_POST['delete_id']]);
    header('Location: payments.php');
    exit;
}

ob_start();
$payments = $pdo->query('SELECT * FROM payments')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="lms-accent">Płatności</h2>
      <a href="<?= base_url('modules/add_payment.php') ?>" class="btn lms-btn-accent">Dodaj Płatność</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th><th>Faktura</th><th>Kwota</th><th>Data Płatności</th><th>Metoda</th><th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment): ?>
            <tr>
              <td><?= htmlspecialchars($payment['id']) ?></td>
              <td><?= htmlspecialchars($payment['invoice_id']) ?></td>
              <td><?= htmlspecialchars($payment['amount']) ?></td>
              <td><?= htmlspecialchars($payment['payment_date']) ?></td>
              <td><?= htmlspecialchars($payment['method']) ?></td>
              <td>
                <a href="<?= base_url('modules/edit_payment.php?id=' . $payment['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                <form method="post" action="<?= base_url('modules/payments.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tę płatność?');">
                  <input type="hidden" name="delete_id" value="<?= $payment['id'] ?>">
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