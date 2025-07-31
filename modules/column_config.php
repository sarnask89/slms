<?php
require_once 'module_loader.php';

$pageTitle = 'Zarządzanie Kolumnami';

$pdo = get_pdo();

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$message = '';

// Add new column configuration
if ($action === 'add' && isset($_POST['module_name'])) {
    $stmt = $pdo->prepare("INSERT INTO column_configs (module_name, field_name, field_label, field_type, is_visible, is_searchable, sort_order, options) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['module_name'],
        $_POST['field_name'],
        $_POST['field_label'],
        $_POST['field_type'],
        isset($_POST['is_visible']) ? 1 : 0,
        isset($_POST['is_searchable']) ? 1 : 0,
        $_POST['sort_order'] ?? 0,
        $_POST['options'] ?? null
    ]);
    $message = 'Konfiguracja kolumny dodana.';
}

// Edit column configuration
if ($action === 'edit' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE column_configs SET module_name=?, field_name=?, field_label=?, field_type=?, is_visible=?, is_searchable=?, sort_order=?, options=? WHERE id=?");
    $stmt->execute([
        $_POST['module_name'],
        $_POST['field_name'],
        $_POST['field_label'],
        $_POST['field_type'],
        isset($_POST['is_visible']) ? 1 : 0,
        isset($_POST['is_searchable']) ? 1 : 0,
        $_POST['sort_order'] ?? 0,
        $_POST['options'] ?? null,
        $_POST['id']
    ]);
    $message = 'Konfiguracja kolumny zaktualizowana.';
}

// Delete column configuration
if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM column_configs WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $message = 'Konfiguracja kolumny usunięta.';
}

// Move up/down
if (($action === 'moveup' || $action === 'movedown') && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT module_name, sort_order FROM column_configs WHERE id=?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item) {
        $module_name = $item['module_name'];
        $pos = $item['sort_order'];
        $cmp = $action === 'moveup' ? '<' : '>';
        $order = $action === 'moveup' ? 'DESC' : 'ASC';
        $stmt2 = $pdo->prepare("SELECT id, sort_order FROM column_configs WHERE module_name = ? AND sort_order $cmp ? ORDER BY sort_order $order LIMIT 1");
        $stmt2->execute([$module_name, $pos]);
        $swap = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($swap) {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE column_configs SET sort_order=? WHERE id=?")->execute([$swap['sort_order'], $id]);
            $pdo->prepare("UPDATE column_configs SET sort_order=? WHERE id=?")->execute([$pos, $swap['id']]);
            $pdo->commit();
            $message = 'Kolumna przeniesiona.';
        }
    }
}

// Initialize default configurations if table is empty
$stmt = $pdo->query("SELECT COUNT(*) FROM column_configs");
if ($stmt->fetchColumn() == 0) {
    $default_configs = [
        // Clients module
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
        
        // Devices module
        ['devices', 'id', 'ID urządzenia', 'text', 1, 1, 1],
        ['devices', 'name', 'nazwa urządzenia', 'text', 1, 1, 2],
        ['devices', 'type', 'typ urządzenia', 'text', 1, 1, 3],
        ['devices', 'ip_address', 'adres IP', 'text', 1, 1, 4],
        ['devices', 'mac_address', 'adres MAC', 'text', 1, 1, 5],
        ['devices', 'location', 'lokalizacja', 'text', 1, 1, 6],
        ['devices', 'client_id', 'klient', 'select', 1, 1, 7],
        ['devices', 'network_id', 'sieć', 'select', 1, 1, 8],
        
        // Networks module
        ['networks', 'id', 'ID sieci', 'text', 1, 1, 1],
        ['networks', 'name', 'nazwa sieci', 'text', 1, 1, 2],
        ['networks', 'subnet', 'podsieć', 'text', 1, 1, 3],
        ['networks', 'description', 'opis', 'textarea', 1, 1, 4],
        ['networks', 'device_interface', 'interfejs urządzenia', 'text', 1, 1, 5],
        ['networks', 'device_id', 'urządzenie', 'select', 1, 1, 6],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO column_configs (module_name, field_name, field_label, field_type, is_visible, is_searchable, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($default_configs as $config) {
        $stmt->execute($config);
    }
    $message = 'Domyślne konfiguracje kolumn zostały zainicjalizowane.';
}

// Fetch all column configurations
$stmt = $pdo->query("SELECT * FROM column_configs ORDER BY module_name, sort_order ASC");
$configs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by module
$modules = [];
foreach ($configs as $config) {
    $modules[$config['module_name']][] = $config;
}

// For edit form
$edit_config = null;
if ($action === 'editform' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM column_configs WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $edit_config = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Available modules
$available_modules = ['clients', 'devices', 'networks', 'skeleton_devices', 'services', 'tariffs', 'tv_packages', 'internet_packages', 'invoices', 'payments', 'users'];

// Field types
$field_types = ['text', 'textarea', 'email', 'number', 'date', 'select', 'checkbox', 'radio'];

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="lms-accent">Zarządzanie Kolumnami</h2>
      <a href="<?= base_url('admin_menu.php') ?>" class="btn btn-secondary">Powrót do Admin</a>
    </div>
    
    <?php if ($message): ?>
      <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <!-- Add/Edit Form -->
    <div class="card mb-4">
      <div class="card-header">
        <h5><?= $edit_config ? 'Edytuj Konfigurację Kolumny' : 'Dodaj Nową Konfigurację Kolumny' ?></h5>
      </div>
      <div class="card-body">
        <form method="post" class="row g-3">
          <input type="hidden" name="action" value="<?= $edit_config ? 'edit' : 'add' ?>">
          <?php if ($edit_config): ?>
            <input type="hidden" name="id" value="<?= $edit_config['id'] ?>">
          <?php endif; ?>
          
          <div class="col-md-3">
            <label class="form-label">Moduł</label>
            <select name="module_name" class="form-select" required>
              <option value="">Wybierz moduł</option>
              <?php foreach ($available_modules as $module): ?>
                <option value="<?= $module ?>" <?= ($edit_config['module_name'] ?? '') === $module ? 'selected' : '' ?>>
                  <?= ucfirst($module) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="col-md-3">
            <label class="form-label">Nazwa pola</label>
            <input type="text" name="field_name" class="form-control" required 
                   value="<?= htmlspecialchars($edit_config['field_name'] ?? '') ?>" 
                   placeholder="np. name, email, address">
          </div>
          
          <div class="col-md-3">
            <label class="form-label">Etykieta pola</label>
            <input type="text" name="field_label" class="form-control" required 
                   value="<?= htmlspecialchars($edit_config['field_label'] ?? '') ?>" 
                   placeholder="np. Nazwa, E-mail, Adres">
          </div>
          
          <div class="col-md-3">
            <label class="form-label">Typ pola</label>
            <select name="field_type" class="form-select" required>
              <?php foreach ($field_types as $type): ?>
                <option value="<?= $type ?>" <?= ($edit_config['field_type'] ?? '') === $type ? 'selected' : '' ?>>
                  <?= ucfirst($type) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="col-md-2">
            <label class="form-label">Sortowanie</label>
            <input type="number" name="sort_order" class="form-control" 
                   value="<?= $edit_config['sort_order'] ?? 0 ?>" min="0">
          </div>
          
          <div class="col-md-2">
            <label class="form-label">Opcje</label>
            <div class="form-check">
              <input type="checkbox" name="is_visible" class="form-check-input" 
                     <?= ($edit_config['is_visible'] ?? 1) ? 'checked' : '' ?>>
              <label class="form-check-label">Widoczne</label>
            </div>
            <div class="form-check">
              <input type="checkbox" name="is_searchable" class="form-check-input" 
                     <?= ($edit_config['is_searchable'] ?? 1) ? 'checked' : '' ?>>
              <label class="form-check-label">Wyszukiwane</label>
            </div>
          </div>
          
          <div class="col-md-12">
            <label class="form-label">Opcje dodatkowe (JSON)</label>
            <textarea name="options" class="form-control" rows="2" 
                      placeholder='{"placeholder": "Wprowadź tekst", "validation": "required"}'>
              <?= htmlspecialchars($edit_config['options'] ?? '') ?>
            </textarea>
            <small class="text-muted">Opcjonalne ustawienia w formacie JSON</small>
          </div>
          
          <div class="col-12">
            <button type="submit" class="btn lms-btn-accent">
              <?= $edit_config ? 'Zapisz zmiany' : 'Dodaj kolumnę' ?>
            </button>
            <?php if ($edit_config): ?>
              <a href="<?= base_url('modules/column_config.php') ?>" class="btn btn-secondary ms-2">Anuluj</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Column Configurations by Module -->
    <?php foreach ($modules as $module_name => $module_configs): ?>
      <div class="card mb-4">
        <div class="card-header">
          <h5>Moduł: <?= ucfirst($module_name) ?> (<?= count($module_configs) ?> kolumn)</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Sortowanie</th>
                  <th>Pole</th>
                  <th>Etykieta</th>
                  <th>Typ</th>
                  <th>Widoczne</th>
                  <th>Wyszukiwane</th>
                  <th>Akcje</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($module_configs as $config): ?>
                  <tr>
                    <td><?= $config['sort_order'] ?></td>
                    <td><code><?= htmlspecialchars($config['field_name']) ?></code></td>
                    <td><?= htmlspecialchars($config['field_label']) ?></td>
                    <td><span class="badge bg-info"><?= htmlspecialchars($config['field_type']) ?></span></td>
                    <td>
                      <?php if ($config['is_visible']): ?>
                        <span class="badge bg-success">Tak</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Nie</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($config['is_searchable']): ?>
                        <span class="badge bg-success">Tak</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Nie</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="?action=moveup&id=<?= $config['id'] ?>" class="btn btn-sm btn-secondary">↑</a>
                      <a href="?action=movedown&id=<?= $config['id'] ?>" class="btn btn-sm btn-secondary">↓</a>
                      <a href="?action=editform&id=<?= $config['id'] ?>" class="btn btn-sm btn-primary">Edytuj</a>
                      <a href="?action=delete&id=<?= $config['id'] ?>" class="btn btn-sm btn-danger" 
                         onclick="return confirm('Usunąć tę konfigurację kolumny?')">Usuń</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 