<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers/auth_helper.php';
require_once __DIR__ . '/helpers/request_helper.php';

// Require login
require_login();

// Initialize variables
$message = '';
$error = '';
$invoices = [];

try {
$pdo = get_pdo();

    // Handle form submissions
    if (is_post_request()) {
        $action = get_post_param('action');
        
        switch ($action) {
            case 'delete':
                $invoice_id = get_post_param('invoice_id');
                if ($invoice_id) {
                    $stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
                    if ($stmt->execute([$invoice_id])) {
                        $message = 'Faktura została usunięta.';
                        log_activity('delete_invoice', "Deleted invoice #$invoice_id");
                    } else {
                        $error = 'Nie udało się usunąć faktury.';
                    }
                }
                break;
                
            case 'mark_paid':
                $invoice_id = get_post_param('invoice_id');
                if ($invoice_id) {
                    $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid', paid_at = CURRENT_TIMESTAMP WHERE id = ?");
                    if ($stmt->execute([$invoice_id])) {
                        $message = 'Faktura została oznaczona jako opłacona.';
                        log_activity('mark_invoice_paid', "Marked invoice #$invoice_id as paid");
                    } else {
                        $error = 'Nie udało się zaktualizować statusu faktury.';
                    }
                }
                break;
        }
    }
    
    // Get all invoices with client names
    $stmt = $pdo->query("
        SELECT i.*, 
               CONCAT(c.first_name, ' ', c.last_name) as client_name,
               c.company_name
        FROM invoices i
        LEFT JOIN clients c ON i.client_id = c.id
        ORDER BY i.created_at DESC
    ");
    $invoices = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Błąd bazy danych: ' . $e->getMessage();
}

$pageTitle = 'Faktury';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt"></i> Faktury
                    </h5>
                    <a href="<?= base_url('modules/add_invoice.php') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nowa faktura
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
                    <?php endif; ?>
                    
    <div class="table-responsive">
                        <table class="table table-striped table-hover">
        <thead>
          <tr>
                                    <th>Numer</th>
                                    <th>Klient</th>
                                    <th>Data wystawienia</th>
                                    <th>Termin płatności</th>
                                    <th>Kwota</th>
                                    <th>Status</th>
                                    <th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoices as $invoice): ?>
            <tr>
                                        <td><?= htmlspecialchars($invoice['number']) ?></td>
                                        <td>
                                            <?php if ($invoice['company_name']): ?>
                                                <?= htmlspecialchars($invoice['company_name']) ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($invoice['client_name']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($invoice['created_at'])) ?></td>
                                        <td><?= date('d.m.Y', strtotime($invoice['due_date'])) ?></td>
                                        <td><?= number_format($invoice['total_amount'], 2, ',', ' ') ?> zł</td>
                                        <td>
                                            <?php if ($invoice['status'] === 'paid'): ?>
                                                <span class="badge bg-success">Opłacona</span>
                                            <?php elseif (strtotime($invoice['due_date']) < time()): ?>
                                                <span class="badge bg-danger">Zaległa</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Oczekująca</span>
                                            <?php endif; ?>
                                        </td>
              <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('modules/edit_invoice.php?id=' . $invoice['id']) ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Edytuj">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <?php if ($invoice['status'] !== 'paid'): ?>
                                                    <form method="post" class="d-inline" 
                                                          onsubmit="return confirm('Czy na pewno chcesz oznaczyć tę fakturę jako opłaconą?');">
                                                        <input type="hidden" name="action" value="mark_paid">
                                                        <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-success" title="Oznacz jako opłaconą">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <form method="post" class="d-inline" 
                                                      onsubmit="return confirm('Czy na pewno chcesz usunąć tę fakturę?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger" title="Usuń">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                </form>
                                            </div>
              </td>
            </tr>
          <?php endforeach; ?>
                                
                                <?php if (empty($invoices)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            Brak faktur w systemie.
                                        </td>
                                    </tr>
                                <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php'; 
?> 