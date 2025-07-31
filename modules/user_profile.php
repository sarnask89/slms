<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'module_loader.php';

require_once 'helpers/auth_helper.php';

// Require login
require_login();

$pdo = get_pdo();
$currentUser = get_current_user_info();
$message = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Wszystkie pola hasła są wymagane.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Nowe hasła nie są identyczne.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Nowe hasło musi mieć co najmniej 6 znaków.';
        } else {
            try {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$currentUser['id']]);
                $userData = $stmt->fetch();
                
                if (!$userData || !password_verify($current_password, $userData['password'])) {
                    $error = 'Aktualne hasło jest nieprawidłowe.';
                } else {
                    // Update password
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $currentUser['id']]);
                    
                    $message = 'Hasło zostało zmienione pomyślnie.';
                }
            } catch (Exception $e) {
                $error = 'Błąd podczas zmiany hasła: ' . $e->getMessage();
            }
        }
    }
}

// Get user details
$userDetails = null;
try {
    $stmt = $pdo->prepare("
        SELECT u.*, al.name as access_level_name, al.description as access_level_description
        FROM users u
        LEFT JOIN access_levels al ON u.access_level_id = al.id
        WHERE u.id = ?
    ");
    $stmt->execute([$currentUser['id']]);
    $userDetails = $stmt->fetch();
} catch (Exception $e) {
    $error = 'Błąd podczas pobierania danych użytkownika: ' . $e->getMessage();
}

// Get user permissions
$userPermissions = [];
try {
    $stmt = $pdo->prepare("
        SELECT alp.section, alp.action
        FROM access_level_permissions alp
        JOIN users u ON alp.access_level_id = u.access_level_id
        WHERE u.id = ?
        ORDER BY alp.section, alp.action
    ");
    $stmt->execute([$currentUser['id']]);
    $userPermissions = $stmt->fetchAll();
} catch (Exception $e) {
    // Permissions table might not exist yet
}

// Get recent activity
$recentActivity = [];
try {
    $stmt = $pdo->prepare("
        SELECT action, details, created_at 
        FROM user_activity_log 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
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
            <div class="lms-card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="lms-accent">
                        <i class="bi bi-person-circle"></i> Profil użytkownika
                    </h2>
                    <a href="profile.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Powrót
                    </a>
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
                    <!-- User Information -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-person"></i> Informacje o użytkowniku
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($userDetails): ?>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Nazwa użytkownika:</strong></div>
                                        <div class="col-sm-8"><?= htmlspecialchars($userDetails['username']) ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Pełne imię:</strong></div>
                                        <div class="col-sm-8"><?= htmlspecialchars($userDetails['full_name'] ?? '-') ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Email:</strong></div>
                                        <div class="col-sm-8"><?= htmlspecialchars($userDetails['email'] ?? '-') ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Rola:</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-<?= $userDetails['role'] === 'admin' ? 'danger' : ($userDetails['role'] === 'manager' ? 'warning' : 'info') ?>">
                                                <?= ucfirst($userDetails['role']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Poziom dostępu:</strong></div>
                                        <div class="col-sm-8">
                                            <?php if ($userDetails['access_level_name']): ?>
                                                <span class="badge bg-success"><?= htmlspecialchars($userDetails['access_level_name']) ?></span>
                                                <small class="text-muted d-block mt-1"><?= htmlspecialchars($userDetails['access_level_description'] ?? '') ?></small>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Brak przypisanego poziomu</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Status:</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-<?= $userDetails['is_active'] ? 'success' : 'secondary' ?>">
                                                <?= $userDetails['is_active'] ? 'Aktywny' : 'Nieaktywny' ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Ostatnie logowanie:</strong></div>
                                        <div class="col-sm-8">
                                            <?= $userDetails['last_login'] ? date('d.m.Y H:i', strtotime($userDetails['last_login'])) : 'Nigdy' ?>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Konto utworzone:</strong></div>
                                        <div class="col-sm-8"><?= date('d.m.Y H:i', strtotime($userDetails['created_at'])) ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Nie udało się załadować danych użytkownika.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Access Permissions -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-shield-check"></i> Uprawnienia dostępu
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($userPermissions)): ?>
                                    <div class="mb-3">
                                        <strong>Łącznie uprawnień:</strong> <?= count($userPermissions) ?>
                                    </div>
                                    <div class="row">
                                        <?php
                                        $sections = [];
                                        foreach ($userPermissions as $perm) {
                                            $sections[$perm['section']][] = $perm['action'];
                                        }
                                        ?>
                                        <?php foreach ($sections as $section => $actions): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card border-primary">
                                                    <div class="card-header bg-primary text-white">
                                                        <h6 class="mb-0"><?= ucfirst($section) ?></h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php foreach ($actions as $action): ?>
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <span><?= ucfirst($action) ?></span>
                                                                <span class="badge bg-success">✓</span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> Brak przypisanych uprawnień lub poziom dostępu nie jest skonfigurowany.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history"></i> Ostatnia aktywność
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recentActivity)): ?>
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
                                                        <td><?= htmlspecialchars($activity['action']) ?></td>
                                                        <td><?= htmlspecialchars($activity['details'] ?? '-') ?></td>
                                                        <td><?= date('d.m.Y H:i', strtotime($activity['created_at'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> Brak zarejestrowanej aktywności.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-key"></i> Zmień hasło
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Aktualne hasło</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Nowe hasło</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Potwierdź nowe hasło</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Zmień hasło
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-lightning"></i> Szybkie akcje
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="dashboard.php" class="btn btn-outline-primary">
                                        <i class="bi bi-speedometer2"></i> Przejdź do dashboard
                                    </a>
                                    <a href="clients.php" class="btn btn-outline-success">
                                        <i class="bi bi-people"></i> Zarządzaj klientami
                                    </a>
                                    <a href="devices.php" class="btn btn-outline-info">
                                        <i class="bi bi-hdd-network"></i> Zarządzaj urządzeniami
                                    </a>
                                    <a href="logout.php" class="btn btn-outline-danger">
                                        <i class="bi bi-box-arrow-right"></i> Wyloguj się
                                    </a>
                                </div>
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