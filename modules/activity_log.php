<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'module_loader.php';

require_once 'helpers/auth_helper.php';

// Require admin access
require_admin();

$pdo = get_pdo();
$message = '';
$error = '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Filtering
$user_filter = $_GET['user'] ?? '';
$action_filter = $_GET['action'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($user_filter) {
    $where_conditions[] = "u.username LIKE ?";
    $params[] = "%$user_filter%";
}

if ($action_filter) {
    $where_conditions[] = "l.action LIKE ?";
    $params[] = "%$action_filter%";
}

if ($date_from) {
    $where_conditions[] = "l.created_at >= ?";
    $params[] = $date_from . " 00:00:00";
}

if ($date_to) {
    $where_conditions[] = "l.created_at <= ?";
    $params[] = $date_to . " 23:59:59";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_query = "SELECT COUNT(*) FROM user_activity_log l 
                LEFT JOIN users u ON l.user_id = u.id 
                $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get activity logs
$query = "SELECT l.*, u.username, u.full_name, u.role 
          FROM user_activity_log l 
          LEFT JOIN users u ON l.user_id = u.id 
          $where_clause 
          ORDER BY l.created_at DESC 
          LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$activities = $stmt->fetchAll();

// Get unique actions for filter dropdown
$actions = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT action FROM user_activity_log ORDER BY action");
    $actions = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Table might not exist
}

// Get users for filter dropdown
$users = [];
try {
    $stmt = $pdo->query("SELECT id, username, full_name FROM users ORDER BY username");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    // Table might not exist
}

$pageTitle = 'Dziennik aktywności';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-clock-history"></i> Dziennik aktywności
                </h1>
                <div>
                    <button class="btn btn-outline-secondary" onclick="exportLog()">
                        <i class="bi bi-download"></i> Eksportuj
                    </button>
                    <button class="btn btn-outline-danger" onclick="clearLog()">
                        <i class="bi bi-trash"></i> Wyczyść dziennik
                    </button>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i> Filtry
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="user" class="form-label">Użytkownik</label>
                            <select class="form-select" id="user" name="user">
                                <option value="">Wszyscy użytkownicy</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user['username']) ?>" 
                                            <?= $user_filter === $user['username'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['username']) ?> 
                                        (<?= htmlspecialchars($user['full_name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="action" class="form-label">Akcja</label>
                            <select class="form-select" id="action" name="action">
                                <option value="">Wszystkie akcje</option>
                                <?php foreach ($actions as $action): ?>
                                    <option value="<?= htmlspecialchars($action) ?>" 
                                            <?= $action_filter === $action ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $action))) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Od daty</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="<?= htmlspecialchars($date_from) ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Do daty</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="<?= htmlspecialchars($date_to) ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filtruj
                                </button>
                                <a href="?" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Wyczyść
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-primary"><?= number_format($total_records) ?></h4>
                            <small class="text-muted">Wszystkie wpisy</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-success"><?= count(array_filter($activities, fn($a) => $a['action'] === 'login')) ?></h4>
                            <small class="text-muted">Logowania dzisiaj</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-warning"><?= count(array_unique(array_column($activities, 'user_id'))) ?></h4>
                            <small class="text-muted">Aktywni użytkownicy</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-info"><?= count($actions) ?></h4>
                            <small class="text-muted">Typy akcji</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Activity Log Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list"></i> Lista aktywności
                        <span class="badge bg-secondary ms-2"><?= $total_records ?> wpisów</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($activities)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">Brak wpisów w dzienniku aktywności</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Użytkownik</th>
                                        <th>Akcja</th>
                                        <th>Szczegóły</th>
                                        <th>IP</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><?= $activity['id'] ?></td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($activity['username'] ?? 'Usunięty użytkownik') ?></strong>
                                                    <?php if ($activity['full_name']): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($activity['full_name']) ?></small>
                                                    <?php endif; ?>
                                                    <?php if ($activity['role']): ?>
                                                        <span class="badge bg-<?= $activity['role'] === 'admin' ? 'danger' : ($activity['role'] === 'manager' ? 'warning' : 'info') ?> ms-1">
                                                            <?= ucfirst($activity['role']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= getActionBadgeColor($activity['action']) ?>">
                                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $activity['action']))) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($activity['details']): ?>
                                                    <span title="<?= htmlspecialchars($activity['details']) ?>">
                                                        <?= htmlspecialchars(substr($activity['details'], 0, 50)) ?>
                                                        <?= strlen($activity['details']) > 50 ? '...' : '' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($activity['ip_address'] ?? '-') ?></code>
                                            </td>
                                            <td>
                                                <div>
                                                    <div><?= date('d.m.Y', strtotime($activity['created_at'])) ?></div>
                                                    <small class="text-muted"><?= date('H:i:s', strtotime($activity['created_at'])) ?></small>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Activity log pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                                <i class="bi bi-chevron-left"></i> Poprzednia
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                                Następna <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportLog() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.location.href = '?' + params.toString();
}

function clearLog() {
    if (confirm('Czy na pewno chcesz wyczyścić cały dziennik aktywności? Ta operacja jest nieodwracalna!')) {
        if (confirm('OSTRZEŻENIE: To usunie wszystkie wpisy z dziennika aktywności. Czy na pewno chcesz kontynuować?')) {
            window.location.href = '?action=clear_log';
        }
    }
}
</script>

<?php
function getActionBadgeColor($action) {
    switch ($action) {
        case 'login':
            return 'success';
        case 'logout':
            return 'secondary';
        case 'error':
        case 'failed_login':
            return 'danger';
        case 'warning':
            return 'warning';
        case 'profile_update':
        case 'password_change':
            return 'info';
        default:
            return 'primary';
    }
}

$content = ob_get_clean();
require_once '../partials/layout.php';
?> 