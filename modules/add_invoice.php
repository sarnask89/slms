<?php
require_once 'module_loader.php';

$pageTitle = 'Add Invoice';
$pdo = get_pdo();
$clients = $pdo->query('SELECT id, name FROM clients')->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO invoices (client_id, date_issued, due_date, total_amount, status) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        $_POST['client_id'],
        $_POST['date_issued'],
        $_POST['due_date'],
        $_POST['total_amount'],
        $_POST['status']
    ]);
    header('Location: invoices.php');
    exit;
}
ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 600px; margin: 0 auto;">
    <h2 class="lms-accent mb-4">Add Invoice</h2>
    <form method="post">
      <div class="form-floating mb-3">
        <select class="form-select" id="client_id" name="client_id" required>
          <option value="" disabled selected>Select Client</option>
          <?php foreach ($clients as $client): ?>
            <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <label for="client_id">Client</label>
      </div>
      <div class="form-floating mb-3">
        <input type="date" class="form-control" id="date_issued" name="date_issued" required>
        <label for="date_issued">Date Issued</label>
      </div>
      <div class="form-floating mb-3">
        <input type="date" class="form-control" id="due_date" name="due_date" required>
        <label for="due_date">Due Date</label>
      </div>
      <div class="form-floating mb-3">
        <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" placeholder="Total Amount" required>
        <label for="total_amount">Total Amount</label>
      </div>
      <div class="form-floating mb-3">
        <select class="form-select" id="status" name="status" required>
          <option value="unpaid">Unpaid</option>
          <option value="paid">Paid</option>
        </select>
        <label for="status">Status</label>
      </div>
      <button type="submit" class="btn lms-btn-accent w-100">Add Invoice</button>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php'; 