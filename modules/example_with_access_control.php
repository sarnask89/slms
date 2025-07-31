<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'module_loader.php';

require_once 'helpers/auth_helper.php';

// Require login
require_login();

$pdo = get_pdo();
$message = '';
$error = '';

// Example of using access level permissions
$canViewClients = has_access_permission('clients', 'view');
$canAddClients = has_access_permission('clients', 'add');
$canEditClients = has_access_permission('clients', 'edit');
$canDeleteClients = has_access_permission('clients', 'delete');

$canViewDevices = has_access_permission('devices', 'view');
$canConfigureDevices = has_access_permission('devices', 'configure');

$canViewFinancial = has_access_permission('financial', 'view');
$canExportFinancial = has_access_permission('financial', 'export');

$pageTitle = 'Przykład kontroli dostępu';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="lms-card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="lms-accent">
                        <i class="bi bi-shield-check"></i> Przykład kontroli dostępu
                    </h2>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Ta strona demonstruje jak używać nowego systemu poziomów dostępu do kontrolowania uprawnień użytkowników.
                </div>

                <!-- Current User Info -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-person"></i> Informacje o użytkowniku
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $currentUser = get_current_user_info();
                                $accessLevel = get_user_access_level();
                                ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Użytkownik:</strong> <?= htmlspecialchars($currentUser['username']) ?></p>
                                        <p><strong>Rola:</strong> <?= ucfirst($currentUser['role']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Poziom dostępu:</strong> 
                                            <?php if ($accessLevel): ?>
                                                <span class="badge bg-success"><?= htmlspecialchars($accessLevel['name']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Brak przypisanego poziomu</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>Opis poziomu:</strong> <?= htmlspecialchars($accessLevel['description'] ?? 'Brak opisu') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permission Examples -->
                <div class="row">
                    <!-- Client Management Permissions -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-people"></i> Zarządzanie klientami
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <span class="badge bg-<?= $canViewClients ? 'success' : 'secondary' ?>">
                                        <?= $canViewClients ? '✓' : '✗' ?> Przeglądanie klientów
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-<?= $canAddClients ? 'success' : 'secondary' ?>">
                                        <?= $canAddClients ? '✓' : '✗' ?> Dodawanie klientów
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-<?= $canEditClients ? 'success' : 'secondary' ?>">
                                        <?= $canEditClients ? '✓' : '✗' ?> Edycja klientów
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-<?= $canDeleteClients ? 'success' : 'secondary' ?>">
                                        <?= $canDeleteClients ? '✓' : '✗' ?> Usuwanie klientów
                                    </span>
                                </div>
                                
                                <hr>
                                
                                <div class="d-grid gap-2">
                                    <?php if ($canViewClients): ?>
                                        <a href="clients.php" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Przeglądaj klientów
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($canAddClients): ?>
                                        <a href="add_client.php" class="btn btn-sm btn-success">
                                            <i class="bi bi-plus-circle"></i> Dodaj klienta
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Device Management Permissions -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-hdd-network"></i> Zarządzanie urządzeniami
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <span class="badge bg-<?= $canViewDevices ? 'success' : 'secondary' ?>">
                                        <?= $canViewDevices ? '✓' : '✗' ?> Przeglądanie urządzeń
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-<?= $canConfigureDevices ? 'success' : 'secondary' ?>">
                                        <?= $canConfigureDevices ? '✓' : '✗' ?> Konfiguracja urządzeń
                                    </span>
                                </div>
                                
                                <hr>
                                
                                <div class="d-grid gap-2">
                                    <?php if ($canViewDevices): ?>
                                        <a href="devices.php" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Przeglądaj urządzenia
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($canConfigureDevices): ?>
                                        <a href="device_config.php" class="btn btn-sm btn-warning">
                                            <i class="bi bi-gear"></i> Konfiguruj urządzenia
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Management Permissions -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-cash-stack"></i> Zarządzanie finansami
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <span class="badge bg-<?= $canViewFinancial ? 'success' : 'secondary' ?>">
                                        <?= $canViewFinancial ? '✓' : '✗' ?> Przeglądanie finansów
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-<?= $canExportFinancial ? 'success' : 'secondary' ?>">
                                        <?= $canExportFinancial ? '✓' : '✗' ?> Eksport raportów
                                    </span>
                                </div>
                                
                                <hr>
                                
                                <div class="d-grid gap-2">
                                    <?php if ($canViewFinancial): ?>
                                        <a href="invoices.php" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Przeglądaj faktury
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($canExportFinancial): ?>
                                        <a href="financial_export.php" class="btn btn-sm btn-info">
                                            <i class="bi bi-download"></i> Eksportuj raporty
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Code Examples -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-code-slash"></i> Przykłady kodu
                                </h5>
                            </div>
                            <div class="card-body">
                                <h6>Sprawdzanie uprawnień:</h6>
                                <pre><code>// Sprawdź czy użytkownik może przeglądać klientów
if (has_access_permission('clients', 'view')) {
    // Pokaż listę klientów
}

// Sprawdź czy użytkownik może dodawać klientów
if (has_access_permission('clients', 'add')) {
    // Pokaż formularz dodawania
}

// Wymagaj konkretnego uprawnienia
require_access_permission('financial', 'export');
// Jeśli użytkownik nie ma uprawnienia, zostanie przekierowany do logowania</code></pre>

                                <h6 class="mt-3">Pobieranie informacji o użytkowniku:</h6>
                                <pre><code>// Pobierz poziom dostępu użytkownika
$accessLevel = get_user_access_level();

// Pobierz wszystkie uprawnienia użytkownika
$permissions = get_user_permissions();</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All User Permissions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-check"></i> Wszystkie uprawnienia użytkownika
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $allPermissions = get_user_permissions();
                                if (!empty($allPermissions)):
                                ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sekcja</th>
                                                    <th>Akcja</th>
                                                    <th>Opis</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($allPermissions as $perm): ?>
                                                    <tr>
                                                        <td><strong><?= ucfirst($perm['section']) ?></strong></td>
                                                        <td><?= ucfirst($perm['action']) ?></td>
                                                        <td>
                                                            <?php
                                                            $descriptions = [
                                                                'view' => 'Przeglądanie',
                                                                'add' => 'Dodawanie',
                                                                'edit' => 'Edycja',
                                                                'delete' => 'Usuwanie',
                                                                'export' => 'Eksport',
                                                                'configure' => 'Konfiguracja',
                                                                'monitor' => 'Monitoring',
                                                                'alerts' => 'Alerty',
                                                                'reports' => 'Raporty',
                                                                'permissions' => 'Zarządzanie uprawnieniami',
                                                                'backup' => 'Backup',
                                                                'logs' => 'Logi',
                                                                'maintenance' => 'Konserwacja',
                                                                'customize' => 'Dostosowywanie',
                                                                'assign' => 'Przypisywanie',
                                                                'dhcp' => 'Zarządzanie DHCP'
                                                            ];
                                                            echo $descriptions[$perm['action']] ?? ucfirst($perm['action']);
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Brak przypisanych uprawnień.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../partials/layout.php';
?> 