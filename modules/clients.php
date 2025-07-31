<?php
require_once 'module_loader.php';

require_once __DIR__ . '/helpers/column_helper.php';
$pageTitle = 'Klienci';



$pdo = get_pdo();

// Initialize column configurations if table exists
if (column_configs_table_exists($pdo)) {
    $default_client_configs = [
        ['clients', 'id', 'identyfikator klienta', 'text', 1, 1, 1],
        ['clients', 'name', 'nazwa klienta', 'text', 1, 1, 2],
        ['clients', 'altname', 'alternatywna nazwa klienta', 'text', 0, 1, 3],
        ['clients', 'address', 'adres', 'textarea', 1, 1, 4],
        ['clients', 'post_name', 'nazwa korespondencyjna', 'text', 0, 1, 5],
        ['clients', 'post_address', 'adres korespondencyjny', 'textarea', 0, 1, 6],
        ['clients', 'location_name', 'nazwa lokalizacji', 'text', 0, 1, 7],
        ['clients', 'location_address', 'adres lokalizacyjny', 'textarea', 0, 1, 8],
        ['clients', 'email', 'e-mail', 'email', 1, 1, 9],
        ['clients', 'bankaccount', 'alternatywny rachunek bankowy', 'text', 0, 1, 10],
        ['clients', 'ten', 'NIP', 'text', 1, 1, 11],
        ['clients', 'ssn', 'PESEL', 'text', 0, 1, 12],
        ['clients', 'additional_info', 'informacje dodatkowe', 'textarea', 0, 1, 13],
        ['clients', 'notes', 'notatki', 'textarea', 0, 1, 14],
        ['clients', 'documentmemo', 'notatka na dokumentach', 'textarea', 0, 1, 15],
        ['clients', 'contact_info', 'informacje kontaktowe', 'text', 0, 1, 16],
    ];
    initialize_module_columns($pdo, 'clients', $default_client_configs);
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM clients WHERE id = ?');
    $stmt->execute([$_POST['delete_id']]);
    header('Location: clients.php');
    exit;
}
ob_start();
$clients = $pdo->query('
    SELECT c.*, COUNT(d.id) as device_count 
    FROM clients c 
    LEFT JOIN devices d ON c.id = d.client_id 
    GROUP BY c.id 
    ORDER BY c.first_name, c.last_name
')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="lms-accent">Klienci</h2>
      <div class="d-flex align-items-center">
        <?php if (column_configs_table_exists($pdo)): ?>
          <?= generate_multiselect_html($pdo, 'clients', 'qs-customer-properties') ?>
        <?php endif; ?>
        <a href="<?= base_url('modules/add_client.php') ?>" class="btn lms-btn-accent ms-3">Dodaj Klienta</a>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <?php if (column_configs_table_exists($pdo)): ?>
              <?php 
              $headers = generate_table_headers($pdo, 'clients', ['Urządzenia', 'Akcje']);
              foreach ($headers as $header): 
                echo $header;
              endforeach; 
              ?>
            <?php else: ?>
              <th>ID</th><th>Nazwa</th><th>E-mail</th><th>NIP</th><th>Adres</th><th>Urządzenia</th><th>Akcje</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clients as $client): ?>
            <tr>
              <?php if (column_configs_table_exists($pdo)): ?>
                <?php 
                $action_cells = [
                  '<span class="badge bg-info">' . $client['device_count'] . ' urządzeń</span>',
                  '<a href="' . base_url('modules/edit_client.php?id=' . $client['id']) . '" class="btn btn-sm btn-primary">Edytuj</a> ' .
                  '<a href="' . base_url('modules/client_devices.php?id=' . $client['id']) . '" class="btn btn-sm btn-info">Urządzenia</a> ' .
                  '<form method="post" action="' . base_url('modules/clients.php') . '" style="display:inline;" onsubmit="return confirm(\'Usunąć tego klienta?\');">' .
                  '<input type="hidden" name="delete_id" value="' . $client['id'] . '">' .
                  '<button type="submit" class="btn btn-sm btn-danger">Usuń</button></form>'
                ];
                $cells = generate_table_row($pdo, 'clients', $client, $action_cells);
                foreach ($cells as $cell): 
                  echo $cell;
                endforeach; 
                ?>
              <?php else: ?>
                <td><?= htmlspecialchars($client['id']) ?></td>
                <td>
                  <strong><?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?></strong>
                  <?php if (!empty($client['company_name'])): ?>
                    <br><small class="text-muted"><?= htmlspecialchars($client['company_name']) ?></small>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($client['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($client['ten'] ?? '') ?></td>
                <td>
                  <?= htmlspecialchars($client['address'] ?? '') ?>
                  <?php if (!empty($client['location_name'])): ?>
                    <br><small class="text-muted">Lok: <?= htmlspecialchars($client['location_name']) ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge bg-info"><?= $client['device_count'] ?> urządzeń</span>
                </td>
                <td>
                  <a href="<?= base_url('modules/edit_client.php?id=' . $client['id']) ?>" class="btn btn-sm btn-primary">Edytuj</a>
                  <a href="<?= base_url('modules/client_devices.php?id=' . $client['id']) ?>" class="btn btn-sm btn-info">Urządzenia</a>
                  <form method="post" action="<?= base_url('modules/clients.php') ?>" style="display:inline;" onsubmit="return confirm('Usunąć tego klienta?');">
                    <input type="hidden" name="delete_id" value="<?= $client['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                  </form>
                </td>
              <?php endif; ?>
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