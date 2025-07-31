<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'module_loader.php';

require_once 'helpers/auth_helper.php';

// Require admin access
require_login();
require_admin();

$pdo = get_pdo();
$message = '';
$error = '';

// Define system sections and actions
$system_sections = [
    'dashboard' => [
        'name' => 'Dashboard',
        'description' => 'Main dashboard and overview',
        'actions' => [
            'view' => 'View dashboard',
            'customize' => 'Customize dashboard layout',
            'export' => 'Export dashboard data'
        ]
    ],
    'clients' => [
        'name' => 'Client Management',
        'description' => 'Manage client accounts and information',
        'actions' => [
            'view' => 'View client list',
            'add' => 'Add new clients',
            'edit' => 'Edit client information',
            'delete' => 'Delete clients',
            'export' => 'Export client data'
        ]
    ],
    'devices' => [
        'name' => 'Device Management',
        'description' => 'Manage network devices and equipment',
        'actions' => [
            'view' => 'View device list',
            'add' => 'Add new devices',
            'edit' => 'Edit device information',
            'delete' => 'Delete devices',
            'monitor' => 'Monitor device status',
            'configure' => 'Configure devices'
        ]
    ],
    'networks' => [
        'name' => 'Network Management',
        'description' => 'Manage network configurations and DHCP',
        'actions' => [
            'view' => 'View network list',
            'add' => 'Add new networks',
            'edit' => 'Edit network settings',
            'delete' => 'Delete networks',
            'dhcp' => 'Manage DHCP settings'
        ]
    ],
    'services' => [
        'name' => 'Services & Packages',
        'description' => 'Manage internet packages and services',
        'actions' => [
            'view' => 'View services list',
            'add' => 'Add new services',
            'edit' => 'Edit service information',
            'delete' => 'Delete services',
            'assign' => 'Assign services to clients'
        ]
    ],
    'financial' => [
        'name' => 'Financial Management',
        'description' => 'Manage invoices, payments, and billing',
        'actions' => [
            'view' => 'View financial data',
            'add' => 'Add invoices/payments',
            'edit' => 'Edit financial records',
            'delete' => 'Delete financial records',
            'export' => 'Export financial reports'
        ]
    ],
    'monitoring' => [
        'name' => 'Network Monitoring',
        'description' => 'Cacti integration and SNMP monitoring',
        'actions' => [
            'view' => 'View monitoring data',
            'configure' => 'Configure monitoring',
            'alerts' => 'Manage alerts',
            'reports' => 'Generate reports'
        ]
    ],
    'users' => [
        'name' => 'User Management',
        'description' => 'Manage system users and access levels',
        'actions' => [
            'view' => 'View user list',
            'add' => 'Add new users',
            'edit' => 'Edit user information',
            'delete' => 'Delete users',
            'permissions' => 'Manage user permissions'
        ]
    ],
    'system' => [
        'name' => 'System Administration',
        'description' => 'System configuration and maintenance',
        'actions' => [
            'view' => 'View system status',
            'configure' => 'Configure system settings',
            'backup' => 'Manage backups',
            'logs' => 'View system logs',
            'maintenance' => 'Perform maintenance'
        ]
    ]
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_access_level') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($name)) {
            $error = 'Nazwa poziomu dostępu jest wymagana.';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Create access level
                $stmt = $pdo->prepare("INSERT INTO access_levels (name, description, created_by) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $_SESSION['user_id']]);
                $access_level_id = $pdo->lastInsertId();
                
                // Add permissions
                foreach ($permissions as $section => $actions) {
                    foreach ($actions as $action_name => $enabled) {
                        if ($enabled) {
                            $stmt = $pdo->prepare("INSERT INTO access_level_permissions (access_level_id, section, action) VALUES (?, ?, ?)");
                            $stmt->execute([$access_level_id, $section, $action_name]);
                        }
                    }
                }
                
                $pdo->commit();
                $message = 'Poziom dostępu został utworzony pomyślnie.';
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Błąd podczas tworzenia poziomu dostępu: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'edit_access_level') {
        $access_level_id = $_POST['access_level_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($access_level_id) || empty($name)) {
            $error = 'ID poziomu dostępu i nazwa są wymagane.';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Update access level
                $stmt = $pdo->prepare("UPDATE access_levels SET name = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$name, $description, $access_level_id]);
                
                // Remove existing permissions
                $stmt = $pdo->prepare("DELETE FROM access_level_permissions WHERE access_level_id = ?");
                $stmt->execute([$access_level_id]);
                
                // Add new permissions
                foreach ($permissions as $section => $actions) {
                    foreach ($actions as $action_name => $enabled) {
                        if ($enabled) {
                            $stmt = $pdo->prepare("INSERT INTO access_level_permissions (access_level_id, section, action) VALUES (?, ?, ?)");
                            $stmt->execute([$access_level_id, $section, $action_name]);
                        }
                    }
                }
                
                $pdo->commit();
                $message = 'Poziom dostępu został zaktualizowany pomyślnie.';
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Błąd podczas aktualizacji poziomu dostępu: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_access_level') {
        $access_level_id = $_POST['access_level_id'] ?? '';
        
        if (empty($access_level_id)) {
            $error = 'ID poziomu dostępu jest wymagane.';
        } else {
            try {
                // Check if access level is in use
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE access_level_id = ?");
                $stmt->execute([$access_level_id]);
                $user_count = $stmt->fetchColumn();
                
                if ($user_count > 0) {
                    $error = 'Nie można usunąć poziomu dostępu, który jest używany przez ' . $user_count . ' użytkowników.';
                } else {
                    $pdo->beginTransaction();
                    
                    // Delete permissions
                    $stmt = $pdo->prepare("DELETE FROM access_level_permissions WHERE access_level_id = ?");
                    $stmt->execute([$access_level_id]);
                    
                    // Delete access level
                    $stmt = $pdo->prepare("DELETE FROM access_levels WHERE id = ?");
                    $stmt->execute([$access_level_id]);
                    
                    $pdo->commit();
                    $message = 'Poziom dostępu został usunięty pomyślnie.';
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Błąd podczas usuwania poziomu dostępu: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'assign_access_level') {
        $user_id = $_POST['user_id'] ?? '';
        $access_level_id = $_POST['access_level_id'] ?? '';
        
        if (empty($user_id) || empty($access_level_id)) {
            $error = 'ID użytkownika i poziomu dostępu są wymagane.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET access_level_id = ? WHERE id = ?");
                $stmt->execute([$access_level_id, $user_id]);
                
                $message = 'Poziom dostępu został przypisany użytkownikowi pomyślnie.';
            } catch (Exception $e) {
                $error = 'Błąd podczas przypisywania poziomu dostępu: ' . $e->getMessage();
            }
        }
    }
}

// Get all access levels
$access_levels = [];
try {
    $stmt = $pdo->query("
        SELECT al.*, 
               COUNT(alp.id) as permission_count,
               COUNT(u.id) as user_count
        FROM access_levels al
        LEFT JOIN access_level_permissions alp ON al.id = alp.access_level_id
        LEFT JOIN users u ON al.id = u.access_level_id
        GROUP BY al.id
        ORDER BY al.created_at DESC
    ");
    $access_levels = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Błąd podczas pobierania poziomów dostępu: ' . $e->getMessage();
}

// Get all users for assignment
$users = [];
try {
    $stmt = $pdo->query("
        SELECT u.*, al.name as access_level_name
        FROM users u
        LEFT JOIN access_levels al ON u.access_level_id = al.id
        ORDER BY u.username
    ");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Błąd podczas pobierania użytkowników: ' . $e->getMessage();
}

$pageTitle = 'Zarządzanie poziomami dostępu';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="lms-card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="lms-accent">
                        <i class="bi bi-shield-lock"></i> Zarządzanie poziomami dostępu
                    </h2>
                    <button class="btn lms-btn-accent" data-bs-toggle="modal" data-bs-target="#createAccessLevelModal">
                        <i class="bi bi-plus-circle"></i> Nowy poziom dostępu
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Access Levels List -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4><i class="bi bi-list-ul"></i> Poziomy dostępu</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nazwa</th>
                                        <th>Opis</th>
                                        <th>Uprawnienia</th>
                                        <th>Użytkownicy</th>
                                        <th>Utworzono</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($access_levels as $level): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($level['name']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($level['description'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= $level['permission_count'] ?> uprawnień</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $level['user_count'] ?> użytkowników</span>
                                            </td>
                                            <td><?= date('d.m.Y H:i', strtotime($level['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-outline-primary" onclick="editAccessLevel(<?= htmlspecialchars(json_encode($level)) ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="viewPermissions(<?= $level['id'] ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <?php if ($level['user_count'] == 0): ?>
                                                        <button class="btn btn-outline-danger" onclick="deleteAccessLevel(<?= $level['id'] ?>, '<?= htmlspecialchars($level['name']) ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- User Assignment -->
                <div class="row">
                    <div class="col-12">
                        <h4><i class="bi bi-people"></i> Przypisywanie poziomów dostępu</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Użytkownik</th>
                                        <th>Email</th>
                                        <th>Rola</th>
                                        <th>Obecny poziom dostępu</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-primary ms-1">Ty</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'manager' ? 'warning' : 'info') ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['access_level_name']): ?>
                                                    <span class="badge bg-success"><?= htmlspecialchars($user['access_level_name']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Brak</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="assignAccessLevel(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', <?= $user['access_level_id'] ?? 'null' ?>)">
                                                    <i class="bi bi-link"></i> Przypisz
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Access Level Modal -->
<div class="modal fade" id="createAccessLevelModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="create_access_level">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Nowy poziom dostępu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nazwa poziomu dostępu *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">Opis</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="mt-4 mb-3">Uprawnienia</h6>
                    <div class="row">
                        <?php foreach ($system_sections as $section_key => $section): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="bi bi-folder"></i> <?= htmlspecialchars($section['name']) ?>
                                        </h6>
                                        <small class="text-muted"><?= htmlspecialchars($section['description']) ?></small>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($section['actions'] as $action_key => $action_name): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="permissions[<?= $section_key ?>][<?= $action_key ?>]" 
                                                       value="1" id="perm_<?= $section_key ?>_<?= $action_key ?>">
                                                <label class="form-check-label" for="perm_<?= $section_key ?>_<?= $action_key ?>">
                                                    <?= htmlspecialchars($action_name) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Utwórz poziom dostępu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Access Level Modal -->
<div class="modal fade" id="editAccessLevelModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="edit_access_level">
                <input type="hidden" name="access_level_id" id="edit_access_level_id">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i> Edytuj poziom dostępu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Nazwa poziomu dostępu *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_description" class="form-label">Opis</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="mt-4 mb-3">Uprawnienia</h6>
                    <div class="row" id="edit_permissions_container">
                        <!-- Permissions will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Access Level Modal -->
<div class="modal fade" id="assignAccessLevelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="assign_access_level">
                <input type="hidden" name="user_id" id="assign_user_id">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-link"></i> Przypisz poziom dostępu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assign_access_level_id" class="form-label">Poziom dostępu</label>
                        <select class="form-select" id="assign_access_level_id" name="access_level_id" required>
                            <option value="">Wybierz poziom dostępu</option>
                            <?php foreach ($access_levels as $level): ?>
                                <option value="<?= $level['id'] ?>"><?= htmlspecialchars($level['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Przypisz</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Permissions Modal -->
<div class="modal fade" id="viewPermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye"></i> Szczegóły uprawnień
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="permissions_details">
                <!-- Permissions details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function editAccessLevel(level) {
    document.getElementById('edit_access_level_id').value = level.id;
    document.getElementById('edit_name').value = level.name;
    document.getElementById('edit_description').value = level.description || '';
    
    // Load permissions for this access level
    loadAccessLevelPermissions(level.id);
    
    new bootstrap.Modal(document.getElementById('editAccessLevelModal')).show();
}

function loadAccessLevelPermissions(accessLevelId) {
    fetch(`access_level_permissions.php?access_level_id=${accessLevelId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('edit_permissions_container');
            container.innerHTML = '';
            
            Object.keys(data.sections).forEach(sectionKey => {
                const section = data.sections[sectionKey];
                const sectionDiv = document.createElement('div');
                sectionDiv.className = 'col-md-6 mb-4';
                sectionDiv.innerHTML = `
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-folder"></i> ${section.name}
                            </h6>
                            <small class="text-muted">${section.description}</small>
                        </div>
                        <div class="card-body">
                            ${Object.keys(section.actions).map(actionKey => {
                                const action = section.actions[actionKey];
                                const checked = data.permissions.includes(`${sectionKey}:${actionKey}`) ? 'checked' : '';
                                return `
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="permissions[${sectionKey}][${actionKey}]" 
                                               value="1" id="edit_perm_${sectionKey}_${actionKey}" ${checked}>
                                        <label class="form-check-label" for="edit_perm_${sectionKey}_${actionKey}">
                                            ${action}
                                        </label>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
                container.appendChild(sectionDiv);
            });
        })
        .catch(error => {
            console.error('Error loading permissions:', error);
            alert('Błąd podczas ładowania uprawnień.');
        });
}

function assignAccessLevel(userId, username, currentAccessLevelId) {
    document.getElementById('assign_user_id').value = userId;
    document.getElementById('assign_access_level_id').value = currentAccessLevelId || '';
    
    new bootstrap.Modal(document.getElementById('assignAccessLevelModal')).show();
}

function viewPermissions(accessLevelId) {
    fetch(`access_level_permissions.php?access_level_id=${accessLevelId}&format=html`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('permissions_details').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewPermissionsModal')).show();
        })
        .catch(error => {
            console.error('Error loading permissions:', error);
            alert('Błąd podczas ładowania szczegółów uprawnień.');
        });
}

function deleteAccessLevel(accessLevelId, name) {
    if (confirm(`Czy na pewno chcesz usunąć poziom dostępu "${name}"?`)) {
        const form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_access_level">
            <input type="hidden" name="access_level_id" value="${accessLevelId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
$content = ob_get_clean();
include '../partials/layout.php';
?> 