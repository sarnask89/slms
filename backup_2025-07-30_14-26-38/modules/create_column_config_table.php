<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Tworzenie tabeli konfiguracji kolumn';

try {
    $pdo = get_pdo();
    
    // Create column_configs table
    $createTable = "
    CREATE TABLE IF NOT EXISTS column_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        module_name VARCHAR(50) NOT NULL,
        field_name VARCHAR(100) NOT NULL,
        field_label VARCHAR(255) NOT NULL,
        field_type VARCHAR(50) NOT NULL DEFAULT 'text',
        is_visible TINYINT(1) DEFAULT 1,
        is_searchable TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        options TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_module_name (module_name),
        INDEX idx_sort_order (sort_order),
        UNIQUE KEY unique_module_field (module_name, field_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTable);
    
    ob_start();
    ?>
    <div class="container">
        <div class="lms-card p-4 mt-4">
            <h2 class="lms-accent mb-4">Tabela konfiguracji kolumn została utworzona</h2>
            
            <div class="alert alert-success">
                <strong>Sukces!</strong> Tabela <code>column_configs</code> została pomyślnie utworzona.
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Struktura tabeli:</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>id</strong> - Unikalny identyfikator</li>
                        <li><strong>module_name</strong> - Nazwa modułu (np. 'clients', 'devices')</li>
                        <li><strong>field_name</strong> - Nazwa pola w bazie danych</li>
                        <li><strong>field_label</strong> - Etykieta wyświetlana użytkownikowi</li>
                        <li><strong>field_type</strong> - Typ pola (text, textarea, email, etc.)</li>
                        <li><strong>is_visible</strong> - Czy kolumna jest widoczna w tabeli</li>
                        <li><strong>is_searchable</strong> - Czy pole jest dostępne w wyszukiwaniu</li>
                        <li><strong>sort_order</strong> - Kolejność sortowania</li>
                        <li><strong>options</strong> - Dodatkowe opcje w formacie JSON</li>
                        <li><strong>created_at</strong> - Data utworzenia</li>
                        <li><strong>updated_at</strong> - Data ostatniej aktualizacji</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="<?= base_url('modules/column_config.php') ?>" class="btn lms-btn-accent">Przejdź do zarządzania kolumnami</a>
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