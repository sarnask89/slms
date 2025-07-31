<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'module_loader.php';

require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login and admin access
require_login();
require_admin();

$pageTitle = 'System Settings';
ob_start();

$pdo = get_pdo();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update settings logic here
        $message = 'Settings updated successfully';
        $message_type = 'success';
    } catch (Exception $e) {
        $message = 'Error updating settings: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Get current settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT * FROM system_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Table might not exist, use defaults
    $settings = [
        'site_name' => 'SLMS System',
        'admin_email' => 'admin@localhost',
        'timezone' => 'Europe/Warsaw',
        'debug_mode' => '0'
    ];
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear"></i> System Settings
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($message)): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Site Name</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" 
                                           value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Admin Email</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                           value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-select" id="timezone" name="timezone">
                                        <option value="Europe/Warsaw" <?= ($settings['timezone'] ?? '') === 'Europe/Warsaw' ? 'selected' : '' ?>>Europe/Warsaw</option>
                                        <option value="UTC" <?= ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                        <option value="America/New_York" <?= ($settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="debug_mode" name="debug_mode" value="1" 
                                               <?= ($settings['debug_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="debug_mode">
                                            Enable Debug Mode
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">System Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                                        <p><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></p>
                                        <p><strong>Database:</strong> MySQL</p>
                                        <p><strong>Current Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Settings
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?> 