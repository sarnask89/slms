<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Aktualizacja struktury klientów';

try {
    $pdo = get_pdo();
    
    // Add missing columns to clients table
    $alterQueries = [
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS altname VARCHAR(255) AFTER name",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS post_name VARCHAR(255) AFTER address",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS post_address TEXT AFTER post_name",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS location_name VARCHAR(255) AFTER post_address",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS location_address TEXT AFTER location_name",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS email VARCHAR(255) AFTER location_address",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS bankaccount VARCHAR(50) AFTER email",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS ten VARCHAR(20) AFTER bankaccount",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS ssn VARCHAR(20) AFTER ten",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS additional_info TEXT AFTER ssn",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS notes TEXT AFTER additional_info",
        "ALTER TABLE clients ADD COLUMN IF NOT EXISTS documentmemo TEXT AFTER notes"
    ];
    
    $successCount = 0;
    $errorCount = 0;
    $messages = [];
    
    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
            $successCount++;
            $messages[] = "✓ " . substr($query, 0, 50) . "...";
        } catch (PDOException $e) {
            $errorCount++;
            $messages[] = "✗ Błąd: " . $e->getMessage();
        }
    }
    
    ob_start();
    ?>
    <div class="container">
        <div class="lms-card p-4 mt-4">
            <h2 class="lms-accent mb-4">Aktualizacja struktury bazy danych klientów</h2>
            
            <div class="alert alert-info">
                <strong>Status:</strong> <?= $successCount ?> operacji zakończonych sukcesem, <?= $errorCount ?> błędów
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Szczegóły operacji:</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <?php foreach ($messages as $message): ?>
                            <li class="mb-2"><?= htmlspecialchars($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="mt-4">
                <h5>Dodane pola:</h5>
                <ul>
                    <li><strong>altname</strong> - Alternatywna nazwa klienta</li>
                    <li><strong>post_name</strong> - Nazwa korespondencyjna</li>
                    <li><strong>post_address</strong> - Adres korespondencyjny</li>
                    <li><strong>location_name</strong> - Nazwa lokalizacji</li>
                    <li><strong>location_address</strong> - Adres lokalizacyjny</li>
                    <li><strong>email</strong> - E-mail</li>
                    <li><strong>bankaccount</strong> - Alternatywny rachunek bankowy</li>
                    <li><strong>ten</strong> - NIP</li>
                    <li><strong>ssn</strong> - PESEL</li>
                    <li><strong>additional_info</strong> - Informacje dodatkowe</li>
                    <li><strong>notes</strong> - Notatki</li>
                    <li><strong>documentmemo</strong> - Notatka na dokumentach</li>
                </ul>
            </div>
            
            <div class="mt-4">
                <a href="<?= base_url('modules/clients.php') ?>" class="btn lms-btn-accent">Powrót do listy klientów</a>
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