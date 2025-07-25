<?php
if (session_status() === PHP_SESSION_NONE) {
    if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { session_start(); } }
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers/auth_helper.php';

// Require login
require_login();

$pdo = get_pdo();
$message = '';
$error = '';

// Get current user data
$currentUser = get_current_user();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$currentUser['id']]);
$userData = $stmt->fetch();

// Handle form submissions
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($full_name)) {
            $error = 'Pełne imię jest wymagane.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $currentUser['id']]);
                
                $message = 'Profil został zaktualizowany pomyślnie.';
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$currentUser['id']]);
                $userData = $stmt->fetch();
            } catch (Exception $e) {
                $error = 'Błąd podczas aktualizacji profilu: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Wszystkie pola hasła są wymagane.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Nowe hasła nie są identyczne.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Nowe hasło musi mieć co najmniej 6 znaków.';
        } elseif (!password_verify($current_password, $userData['password'])) {
            $error = 'Aktualne hasło jest nieprawidłowe.';
        } else {
            try {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $currentUser['id']]);
                
                $message = 'Hasło zostało zmienione pomyślnie.';
            } catch (Exception $e) {
                $error = 'Błąd podczas zmiany hasła: ' . $e->getMessage();
            }
        }
    }
}

// Get user permissions
$permissions = [];
try {
    $stmt = $pdo->prepare("SELECT module, permission FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$currentUser['id']]);
    $permissions = $stmt->fetchAll();
} catch (Exception $e) {
    // Permissions table might not exist yet
}

// Get recent activity
$recentActivity = [];
try {
    $stmt = $pdo->prepare("SELECT action, details, created_at FROM user_activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$currentUser['id']]);
    $recentActivity = $stmt->fetchAll();
} catch (Exception $e) {
    // Activity log table might not exist yet
}

$pageTitle = 'Profil użytkownika';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-person-circle"></i> Profil użytkownika
                </h1>
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
            
            <div class="row">
                <!-- Profile Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person"></i> Informacje o profilu
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Nazwa użytkownika</label>
                                    <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($userData['username']) ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Pełne imię *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?= htmlspecialchars($userData['full_name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($userData['email'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Rola</label>
                                    <input type="text" class="form-control" id="role" 
                                           value="<?= ucfirst($userData['role']) ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <input type="text" class="form-control" id="status" 
                                           value="<?= $userData['is_active'] ? 'Aktywny' : 'Nieaktywny' ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="created_at" class="form-label">Data utworzenia</label>
                                    <input type="text" class="form-control" id="created_at" 
                                           value="<?= date('d.m.Y H:i', strtotime($userData['created_at'])) ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="last_login" class="form-label">Ostatnie logowanie</label>
                                    <input type="text" class="form-control" id="last_login" 
                                           value="<?= $userData['last_login'] ? date('d.m.Y H:i', strtotime($userData['last_login'])) : 'Nigdy' ?>" readonly>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Zapisz zmiany
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-key"></i> Zmień hasło
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Aktualne hasło *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nowe hasło *</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">Hasło musi mieć co najmniej 6 znaków.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Potwierdź nowe hasło *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-key"></i> Zmień hasło
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- User Permissions -->
                    <?php if (!empty($permissions)): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-shield"></i> Uprawnienia
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($permissions as $perm): ?>
                                    <div class="col-md-6 mb-2">
                                        <span class="badge bg-<?= $perm['permission'] === 'admin' ? 'danger' : ($perm['permission'] === 'write' ? 'warning' : 'info') ?>">
                                            <?= ucfirst($perm['module']) ?>: <?= ucfirst($perm['permission']) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <?php if (!empty($recentActivity)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history"></i> Ostatnia aktywność
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Akcja</th>
                                            <th>Szczegóły</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentActivity as $activity): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?= htmlspecialchars($activity['action']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($activity['details']) ?></td>
                                                <td><?= date('d.m.Y H:i', strtotime($activity['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?> 