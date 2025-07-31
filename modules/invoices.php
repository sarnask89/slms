<?php
require_once 'module_loader.php';

$pageTitle = 'Faktury';
$pdo = get_pdo();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM invoices WHERE id = ?');
    $stmt->execute([$_POST['delete_id']]);
    header('Location: invoices.php');
    exit;
}

ob_start();
$invoices = $pdo->query('SELECT invoices.*, clients.name AS client_name FROM invoices LEFT JOIN clients ON invoices.client_id = clients.id')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="lms-accent">Faktury</h2>
      <a href="<?= base_url('modules/add_invoice.php') ?>" class="btn lms-btn-accent">Dodaj Fakturę</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th><th>Klient</th><th>Data Wystawienia</th><th>Termin Płatności</th><th>Kwota</th><th>Status</th><th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoices as $invoice): ?>
            <tr>
              <td><?= htmlspecialchars($invoice['id']) ?></td>
              <td><?= htmlspecialchars($invoice['client_name']) ?></td>
              <td><?= htmlspecialchars($invoice['date_issued']) ?></td>
              <td><?= htmlspecialchars($invoice['due_date']) ?></td>
              <td><?= htmlspecialchars($invoice['total_amount']) ?></td>
              <td><?= htmlspecialchars($invoice['status']) ?></td>
              <td>
                <a href="<?= base_url('modules/edit_invoice.php?id=' . $invoice['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                <form method="post" action="<?= base_url('modules/invoices.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tę fakturę?');">
                  <input type="hidden" name="delete_id" value="<?= $invoice['id'] ?>">
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