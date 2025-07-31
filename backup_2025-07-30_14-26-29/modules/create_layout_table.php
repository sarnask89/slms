<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Tworzenie tabeli ustawień układu';

try {
    $pdo = get_pdo();
    
    // Create layout_settings table
    $createTable = "
    CREATE TABLE IF NOT EXISTS layout_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_setting_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTable);
    
    // Insert default layout settings
    $defaultLayout = [
        'menu_position' => 'top',
        'menu_style' => 'horizontal',
        'sidebar_width' => '250px',
        'header_height' => '60px',
        'color_scheme' => 'default',
        'font_size' => 'medium',
        'show_breadcrumbs' => true,
        'show_search' => true,
        'show_user_menu' => true,
        'footer_text' => '© ' . date('Y') . ' sLMS System'
    ];
    
    $defaultLayoutJson = json_encode($defaultLayout);
    
    // Check if default layout exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM layout_settings WHERE setting_key = 'main_layout'");
    $stmt->execute();
    
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO layout_settings (setting_key, setting_value) VALUES ('main_layout', ?)");
        $stmt->execute([$defaultLayoutJson]);
        $message = "Tabela layout_settings została utworzona i domyślne ustawienia zostały dodane.";
    } else {
        $message = "Tabela layout_settings została utworzona. Domyślne ustawienia już istnieją.";
    }
    
    ob_start();
    ?>
    <div class="container">
        <div class="lms-card p-4 mt-4">
            <h2 class="lms-accent mb-4">Tabela ustawień układu została utworzona</h2>
            
            <div class="alert alert-success">
                <strong>Sukces!</strong> <?= htmlspecialchars($message) ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Struktura tabeli layout_settings:</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>id</strong> - Unikalny identyfikator</li>
                        <li><strong>setting_key</strong> - Klucz ustawienia (np. 'main_layout')</li>
                        <li><strong>setting_value</strong> - Wartość ustawienia w formacie JSON</li>
                        <li><strong>created_at</strong> - Data utworzenia</li>
                        <li><strong>updated_at</strong> - Data ostatniej aktualizacji</li>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Domyślne ustawienia układu:</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>menu_position</strong> - top (menu górne)</li>
                        <li><strong>menu_style</strong> - horizontal (poziome)</li>
                        <li><strong>sidebar_width</strong> - 250px</li>
                        <li><strong>header_height</strong> - 60px</li>
                        <li><strong>color_scheme</strong> - default (niebieski)</li>
                        <li><strong>font_size</strong> - medium</li>
                        <li><strong>show_breadcrumbs</strong> - true</li>
                        <li><strong>show_search</strong> - true</li>
                        <li><strong>show_user_menu</strong> - true</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="<?= base_url('modules/layout_manager.php') ?>" class="btn lms-btn-accent">Przejdź do zarządzania układem</a>
                <a href="<?= base_url('admin_menu.php') ?>" class="btn btn-secondary ms-2">Powrót do Admin</a>
            </div>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
    include __DIR__ . '/../partials/layout.php';
    
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
?> 