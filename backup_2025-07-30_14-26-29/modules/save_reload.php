<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Zapisz/Przeładuj';
$pdo = get_pdo();

$message = '';
$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $menu_id = $_POST['menu_id'] ?? '';
    
    if ($action && $menu_id) {
        // Get menu item details
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$menu_id]);
        $menu_item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($menu_item) {
            if ($action === 'save') {
                // Generate files
                $fg_template = $menu_item['file_generator_template'] ?? '';
                $fg_path = $menu_item['file_generator_path'] ?? '';
                
                if ($fg_template && $fg_path) {
                    try {
                        $devices = $pdo->query('SELECT * FROM devices')->fetchAll(PDO::FETCH_ASSOC);
                        $full_content = '';
                        foreach ($devices as $dev) {
                            $content = $fg_template;
                            foreach ($dev as $k => $v) {
                                $content = str_replace('{$'.$k.'}', $v, $content);
                            }
                            $full_content .= $content . "\n";
                        }
                        if (!preg_match('#^/#', $fg_path)) $fg_path = __DIR__ . '/../' . $fg_path;
                        file_put_contents($fg_path, $full_content);
                        $message = "Wygenerowano plik z " . count($devices) . " wpisami urządzeń.";
                    } catch (Exception $e) {
                        $error = "Błąd podczas generowania pliku: " . $e->getMessage();
                    }
                } else {
                    $error = "Brak szablonu lub ścieżki pliku dla tego elementu menu.";
                }
            } elseif ($action === 'reload') {
                // Execute script after generating files
                $script = $menu_item['script'] ?? '';
                
                if ($script) {
                    try {
                        // First generate files if template exists
                        $fg_template = $menu_item['file_generator_template'] ?? '';
                        $fg_path = $menu_item['file_generator_path'] ?? '';
                        
                        if ($fg_template && $fg_path) {
                            $devices = $pdo->query('SELECT * FROM devices')->fetchAll(PDO::FETCH_ASSOC);
                            $full_content = '';
                            foreach ($devices as $dev) {
                                $content = $fg_template;
                                foreach ($dev as $k => $v) {
                                    $content = str_replace('{$'.$k.'}', $v, $content);
                                }
                                $full_content .= $content . "\n";
                            }
                            if (!preg_match('#^/#', $fg_path)) $fg_path = __DIR__ . '/../' . $fg_path;
                            file_put_contents($fg_path, $full_content);
                        }
                        
                        // Then execute the script
                        $output = shell_exec($script . ' 2>&1');
                        $message = "Skrypt wykonany pomyślnie. Wygenerowano pliki i uruchomiono skrypt.";
                        if ($output) {
                            $message .= "<br><strong>Wynik skryptu:</strong><br><pre>" . htmlspecialchars($output) . "</pre>";
                        }
                    } catch (Exception $e) {
                        $error = "Błąd podczas wykonywania skryptu: " . $e->getMessage();
                    }
                } else {
                    $error = "Brak skryptu do wykonania dla tego elementu menu.";
                }
            }
        } else {
            $error = "Nie znaleziono elementu menu.";
        }
    }
}

// Get all menu items that have file generator templates or scripts
$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE (file_generator_template IS NOT NULL AND file_generator_template != '') OR (script IS NOT NULL AND script != '') ORDER BY position ASC");
$stmt->execute();
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <h2 class="lms-accent mb-4">Zapisz/Przeładuj</h2>
    
    <?php if ($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="row">
      <?php foreach ($menu_items as $item): ?>
        <div class="col-md-6 mb-3">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0"><?= htmlspecialchars($item['label']) ?></h5>
            </div>
            <div class="card-body">
              <?php if ($item['file_generator_template']): ?>
                <p><strong>Szablon:</strong> <?= htmlspecialchars(substr($item['file_generator_template'], 0, 50)) ?>...</p>
                <p><strong>Ścieżka:</strong> <?= htmlspecialchars($item['file_generator_path']) ?></p>
              <?php endif; ?>
              
              <?php if ($item['script']): ?>
                <p><strong>Skrypt:</strong> <?= htmlspecialchars(substr($item['script'], 0, 50)) ?>...</p>
              <?php endif; ?>
              
              <div class="btn-group" role="group">
                <?php if ($item['file_generator_template']): ?>
                  <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                    <button type="submit" class="btn btn-success btn-sm">Zapisz</button>
                  </form>
                <?php endif; ?>
                
                <?php if ($item['script']): ?>
                  <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="reload">
                    <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Przeładuj</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <?php if (empty($menu_items)): ?>
      <div class="alert alert-info">
        Brak elementów menu z szablonami generatora plików lub skryptami. 
        <a href="/admin_menu.php">Przejdź do administracji menu</a> aby dodać szablony i skrypty.
      </div>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 