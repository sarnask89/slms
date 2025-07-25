<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Add Payment';
$pdo = get_pdo();
$invoices = $pdo->query('SELECT id FROM invoices')->fetchAll(PDO::FETCH_ASSOC);
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO payments (invoice_id, amount, payment_date, method) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $_POST['invoice_id'],
        $_POST['amount'],
        $_POST['payment_date'],
        $_POST['method']
    ]);
    header('Location: payments.php');
    exit;
}
ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 600px; margin: 0 auto;">
    <h2 class="lms-accent mb-4">Add Payment</h2>
    <form method="post">
      <div class="form-floating mb-3">
        <select class="form-select" id="invoice_id" name="invoice_id" required>
          <option value="" disabled selected>Select Invoice</option>
          <?php foreach ($invoices as $invoice): ?>
            <option value="<?= $invoice['id'] ?>">Invoice #<?= htmlspecialchars($invoice['id']) ?></option>
          <?php endforeach; ?>
        </select>
        <label for="invoice_id">Invoice</label>
      </div>
      <div class="form-floating mb-3">
        <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Amount" required>
        <label for="amount">Amount</label>
      </div>
      <div class="form-floating mb-3">
        <input type="date" class="form-control" id="payment_date" name="payment_date" required>
        <label for="payment_date">Payment Date</label>
      </div>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="method" name="method" placeholder="Method">
        <label for="method">Method</label>
      </div>
      <button type="submit" class="btn lms-btn-accent w-100">Add Payment</button>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php'; 