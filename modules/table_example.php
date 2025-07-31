<?php
require_once 'module_loader.php';


$pdo = get_pdo();

// Get sample data
try {
    $stmt = $pdo->prepare("SELECT * FROM clients LIMIT 10");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clients = [
        ['id' => 1, 'name' => 'Acme Corp', 'address' => '123 Main St', 'contact_info' => 'acme@example.com'],
        ['id' => 2, 'name' => 'Beta Ltd', 'address' => '456 Side Ave', 'contact_info' => 'beta@example.com'],
        ['id' => 3, 'name' => 'Gamma LLC', 'address' => '789 Market Rd', 'contact_info' => 'gamma@example.com'],
    ];
}

$pageTitle = 'Przykład tabeli';
$content = ob_start();
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2><i class="bi bi-people me-2"></i>Lista klientów</h2>
            <p class="text-muted">Przykład szybkiego ładowania tabeli w układzie z ramkami</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" onclick="refreshTable()">
                <i class="bi bi-arrow-clockwise me-2"></i>Odśwież
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Klienci (<?= count($clients) ?>)</h5>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" placeholder="Szukaj klientów...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Adres</th>
                            <th>Kontakt</th>
                            <th>Data utworzenia</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $client['id'] ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($client['name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($client['address']) ?></td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($client['contact_info']) ?>">
                                        <?= htmlspecialchars($client['contact_info']) ?>
                                    </a>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= isset($client['created_at']) ? date('d.m.Y H:i', strtotime($client['created_at'])) : 'N/A' ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" title="Edytuj">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-info" title="Szczegóły">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" title="Usuń">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col">
                    <small class="text-muted">
                        Pokazano <?= count($clients) ?> z <?= count($clients) ?> klientów
                    </small>
                </div>
                <div class="col-auto">
                    <nav aria-label="Nawigacja strony">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Poprzednia</a>
                            </li>
                            <li class="page-item active">
                                <a class="page-link" href="#">1</a>
                            </li>
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Następna</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacje o wydajności</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li><i class="bi bi-check-circle text-success me-2"></i>Ładowanie tylko zawartości tabeli</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Menu pozostaje w pamięci</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Szybkie przełączanie między stronami</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Automatyczne odświeżanie co 5 minut</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-keyboard me-2"></i>Skróty klawiszowe</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li><kbd>Ctrl + R</kbd> - Odśwież tabelę</li>
                        <li><kbd>Ctrl + F</kbd> - Fokus na wyszukiwanie</li>
                        <li><kbd>Ctrl + K</kbd> - Wyszukiwanie globalne</li>
                        <li><kbd>Ctrl + 1-9</kbd> - Szybka nawigacja</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshTable() {
    // Show loading state
    const tbody = document.querySelector('tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Odświeżanie danych...
            </td>
        </tr>
    `;
    
    // Simulate refresh
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Auto-refresh every 30 seconds for demo
setInterval(() => {
    console.log('Auto-refresh in 30 seconds...');
}, 30000);
</script>

<?php
$content = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../partials/layout.php';
?> 