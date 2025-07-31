<?php
if (session_status() === PHP_SESSION_NONE) {
    if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { session_start(); } }
}
require_once __DIR__ . '/../config.php';

// Check if this is initial setup (no users exist)
$pdo = get_pdo();
$stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
$userCount = $stmt->fetch()['user_count'];
$isSetup = $userCount == 0 || isset($_GET['setup']);

// If not setup, require admin access
if (!$isSetup) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit();
    }
}

$pdo = get_pdo();
$message = '';
$error = '';

// Handle form submissions
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_user') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validation
        if (empty($username) || empty($password) || empty($full_name)) {
            $error = 'Nazwa użytkownika, hasło i pełne imię są wymagane.';
        } elseif ($password !== $confirm_password) {
            $error = 'Hasła nie są identyczne.';
        } elseif (strlen($password) < 6) {
            $error = 'Hasło musi mieć co najmniej 6 znaków.';
        } else {
            try {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Nazwa użytkownika już istnieje.';
                } else {
                    // Create new user
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                    $stmt->execute([$username, $hashedPassword, $full_name, $email, $role, $is_active]);
                    
                    $message = 'Użytkownik został utworzony pomyślnie.';
                    if ($isSetup) {
                        $message .= ' Możesz teraz się zalogować i rozpocząć pracę z systemem.';
                        // Redirect to login after a short delay
                        header("Refresh: 3; URL=login.php");
                    }
                }
            } catch (Exception $e) {
                $error = 'Błąd podczas tworzenia użytkownika: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'edit_user') {
        $user_id = $_POST['user_id'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($user_id) || empty($full_name)) {
            $error = 'ID użytkownika i pełne imię są wymagane.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $role, $is_active, $user_id]);
                
                $message = 'Użytkownik został zaktualizowany pomyślnie.';
            } catch (Exception $e) {
                $error = 'Błąd podczas aktualizacji użytkownika: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_user') {
        $user_id = $_POST['user_id'] ?? '';
        
        if ($user_id == $_SESSION['user_id']) {
            $error = 'Nie możesz usunąć swojego własnego konta.';
        } elseif (empty($user_id)) {
            $error = 'ID użytkownika jest wymagane.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                $message = 'Użytkownik został usunięty pomyślnie.';
            } catch (Exception $e) {
                $error = 'Błąd podczas usuwania użytkownika: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'change_password') {
        $user_id = $_POST['user_id'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($user_id) || empty($new_password)) {
            $error = 'ID użytkownika i nowe hasło są wymagane.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Hasła nie są identyczne.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Hasło musi mieć co najmniej 6 znaków.';
        } else {
            try {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $user_id]);
                
                $message = 'Hasło zostało zmienione pomyślnie.';
            } catch (Exception $e) {
                $error = 'Błąd podczas zmiany hasła: ' . $e->getMessage();
            }
        }
    }
}

// Get all users
$users = [];
try {
    $stmt = $pdo->query("SELECT id, username, full_name, email, role, is_active, created_at, last_login FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Błąd podczas pobierania użytkowników: ' . $e->getMessage();
}

$pageTitle = 'Zarządzanie użytkownikami';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-people"></i> 
                    <?= $isSetup ? 'Konfiguracja systemu - Utwórz pierwszego użytkownika' : 'Zarządzanie użytkownikami' ?>
                </h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle"></i> Dodaj użytkownika
                </button>
            </div>
            
            <?php if ($isSetup): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Witamy w sLMS!</strong> To jest pierwsze uruchomienie systemu. 
                    Utwórz pierwszego użytkownika administratora, aby rozpocząć pracę z systemem.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
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
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nazwa użytkownika</th>
                                    <th>Pełne imię</th>
                                    <th>Email</th>
                                    <th>Rola</th>
                                    <th>Status</th>
                                    <th>Utworzono</th>
                                    <th>Ostatnie logowanie</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($user['username']) ?></strong>
                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <span class="badge bg-primary ms-1">Ty</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                                        <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'manager' ? 'warning' : 'info') ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                                                <?= $user['is_active'] ? 'Aktywny' : 'Nieaktywny' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Nigdy' ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-warning" onclick="changePassword(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                                    <i class="bi bi-key"></i>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button class="btn btn-outline-danger" onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
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
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i> Dodaj nowego użytkownika
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Nazwa użytkownika *</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Pełne imię *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Hasło *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Potwierdź hasło *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Rola</label>
                        <select class="form-select" id="role" name="role">
                            <option value="user" <?= !$isSetup ? 'selected' : '' ?>>Użytkownik</option>
                            <option value="manager">Menedżer</option>
                            <option value="admin" <?= $isSetup ? 'selected' : '' ?>>Administrator</option>
                        </select>
                        <?php if ($isSetup): ?>
                            <div class="form-text">Zalecane: Administrator dla pierwszego użytkownika</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Konto aktywne
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Dodaj użytkownika</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Edytuj użytkownika
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Nazwa użytkownika</label>
                        <input type="text" class="form-control" id="edit_username" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_full_name" class="form-label">Pełne imię *</label>
                        <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Rola</label>
                        <select class="form-select" id="edit_role" name="role">
                            <option value="user">Użytkownik</option>
                            <option value="manager">Menedżer</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">
                                Konto aktywne
                            </label>
                        </div>
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

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-key"></i> Zmień hasło
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="user_id" id="change_password_user_id">
                    
                    <div class="mb-3">
                        <label for="change_password_username" class="form-label">Użytkownik</label>
                        <input type="text" class="form-control" id="change_password_username" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nowe hasło *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="change_confirm_password" class="form-label">Potwierdź nowe hasło *</label>
                        <input type="password" class="form-control" id="change_confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-warning">Zmień hasło</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-danger"></i> Potwierdź usunięcie
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    
                    <p>Czy na pewno chcesz usunąć użytkownika <strong id="delete_username"></strong>?</p>
                    <p class="text-danger">Ta operacja jest nieodwracalna!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-danger">Usuń użytkownika</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_full_name').value = user.full_name;
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_is_active').checked = user.is_active == 1;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function changePassword(userId, username) {
    document.getElementById('change_password_user_id').value = userId;
    document.getElementById('change_password_username').value = username;
    
    new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
}

function deleteUser(userId, username) {
    document.getElementById('delete_user_id').value = userId;
    document.getElementById('delete_username').textContent = username;
    
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?> 