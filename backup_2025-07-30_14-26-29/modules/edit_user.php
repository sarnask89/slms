<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require admin access
require_admin();

$pdo = get_pdo();
$message = '';
$error = '';

// Get user ID from URL
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header('Location: users.php');
    exit();
}

// Handle form submission
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $new_password = trim($_POST['new_password'] ?? '');
    
    if (empty($username)) {
        $error = 'Username is required.';
    } else {
        try {
            // Check if username already exists (excluding current user)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $user_id]);
            if ($stmt->rowCount() > 0) {
                $error = 'Username already exists.';
            } else {
                // Update user
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ?, password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$username, $email, $role, $is_active, $hashed_password, $user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$username, $email, $role, $is_active, $user_id]);
                }
                
                $message = 'User updated successfully!';
                
                // Log the activity
                log_activity('user_updated', "Updated user: $username (ID: $user_id)");
            }
        } catch (Exception $e) {
            $error = 'Error updating user: ' . $e->getMessage();
        }
    }
}

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: users.php');
        exit();
    }
} catch (Exception $e) {
    $error = 'Error loading user: ' . $e->getMessage();
}

// Start output buffering for layout system
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-person-gear"></i> Edit User
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="users.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" name="role" id="role">
                                    <option value="viewer" <?php echo $user['role'] === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                           <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active Account
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               placeholder="Leave blank to keep current password">
                        <div class="form-text">Minimum 6 characters. Leave blank to keep current password.</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update User
                        </button>
                        <a href="users.php" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> User Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>User ID:</strong><br>
                    <span class="text-muted"><?php echo $user['id']; ?></span>
                </div>
                
                <div class="mb-3">
                    <strong>Created:</strong><br>
                    <span class="text-muted"><?php echo $user['created_at']; ?></span>
                </div>
                
                <div class="mb-3">
                    <strong>Last Updated:</strong><br>
                    <span class="text-muted"><?php echo $user['updated_at']; ?></span>
                </div>
                
                <div class="mb-3">
                    <strong>Last Login:</strong><br>
                    <span class="text-muted">
                        <?php echo $user['last_login'] ? $user['last_login'] : 'Never'; ?>
                    </span>
                </div>
                
                <div class="mb-3">
                    <strong>Current Status:</strong><br>
                    <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
                
                <div class="mb-3">
                    <strong>Current Role:</strong><br>
                    <span class="badge bg-<?php 
                        echo $user['role'] === 'admin' ? 'danger' : 
                            ($user['role'] === 'manager' ? 'warning' : 
                            ($user['role'] === 'user' ? 'primary' : 'secondary')); 
                    ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield"></i> Security</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Warning:</strong> Changing user roles may affect system access and permissions.
                </div>
                
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="resetPassword()">
                        <i class="bi bi-key"></i> Reset Password
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="toggleUserStatus()">
                        <i class="bi bi-toggle-on"></i> Toggle Status
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetPassword() {
    if (confirm('Are you sure you want to reset the password for this user?')) {
        document.getElementById('new_password').value = 'password123';
        document.getElementById('new_password').focus();
        alert('Password has been set to "password123". Please save the form to apply the change.');
    }
}

function toggleUserStatus() {
    const statusCheckbox = document.getElementById('is_active');
    statusCheckbox.checked = !statusCheckbox.checked;
    alert('User status toggled. Please save the form to apply the change.');
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?> 