<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login
require_login();

$pdo = get_pdo();
$message = '';
$dashboard_config = [];

// Load existing dashboard configuration
try {
    $stmt = $pdo->query("SELECT * FROM dashboard_config WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($config) {
        $dashboard_config = json_decode($config['config'], true) ?: [];
    }
} catch (Exception $e) {
    // Create dashboard_config table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS dashboard_config (
        id INT PRIMARY KEY AUTO_INCREMENT,
        config JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
}

// Handle form submission
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_config = [
        'cacti' => [
            'devices' => isset($_POST['cacti_devices']),
            'graphs' => isset($_POST['cacti_graphs']),
            'status' => isset($_POST['cacti_status'])
        ],
        'snmp' => [
            'monitoring' => isset($_POST['snmp_monitoring']),
            'graphs' => isset($_POST['snmp_graphs']),
            'alerts' => isset($_POST['snmp_alerts'])
        ],
        'layout' => [
            'theme' => $_POST['theme'] ?? 'default',
            'columns' => $_POST['columns'] ?? 2,
            'refresh_interval' => $_POST['refresh_interval'] ?? 30
        ]
    ];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO dashboard_config (id, config) VALUES (1, ?) 
                              ON DUPLICATE KEY UPDATE config = ?, updated_at = CURRENT_TIMESTAMP");
        $config_json = json_encode($new_config);
        $stmt->execute([$config_json, $config_json]);
        
        $dashboard_config = $new_config;
        $message = 'Dashboard configuration saved successfully!';
    } catch (Exception $e) {
        $message = 'Error saving configuration: ' . $e->getMessage();
    }
}

// Get Cacti status (if Cacti API is available)
$cacti_status = ['success' => false, 'message' => 'Cacti API not available'];
try {
    if (file_exists(__DIR__ . '/cacti_api.php')) {
        require_once __DIR__ . '/cacti_api.php';
        $cacti_api = new CactiAPI();
        $cacti_status = $cacti_api->getStatus();
    }
} catch (Exception $e) {
    $cacti_status = ['success' => false, 'message' => 'Cacti connection failed'];
}

// Set page title
$pageTitle = 'Dashboard Editor';

// Start output buffering for layout system
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-palette"></i> Theme & Dashboard Editor
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="dashboard_preview.php" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye"></i> Preview
            </a>
            <a href="../admin_menu.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Admin
            </a>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="">
    <div class="row">
        <!-- Configuration Panel -->
        <div class="col-md-8">
            <!-- Theme Configuration -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-palette text-primary"></i> Theme Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="theme" class="form-label">Color Theme</label>
                            <select class="form-select" name="theme" id="theme">
                                <option value="default" <?php echo ($dashboard_config['layout']['theme'] ?? 'default') === 'default' ? 'selected' : ''; ?>>Default (Blue)</option>
                                <option value="dark" <?php echo ($dashboard_config['layout']['theme'] ?? 'default') === 'dark' ? 'selected' : ''; ?>>Dark Theme</option>
                                <option value="light" <?php echo ($dashboard_config['layout']['theme'] ?? 'default') === 'light' ? 'selected' : ''; ?>>Light Theme</option>
                                <option value="green" <?php echo ($dashboard_config['layout']['theme'] ?? 'default') === 'green' ? 'selected' : ''; ?>>Green Theme</option>
                                <option value="purple" <?php echo ($dashboard_config['layout']['theme'] ?? 'default') === 'purple' ? 'selected' : ''; ?>>Purple Theme</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="columns" class="form-label">Dashboard Layout</label>
                            <select class="form-select" name="columns" id="columns">
                                <option value="1" <?php echo ($dashboard_config['layout']['columns'] ?? 2) == 1 ? 'selected' : ''; ?>>Single Column</option>
                                <option value="2" <?php echo ($dashboard_config['layout']['columns'] ?? 2) == 2 ? 'selected' : ''; ?>>Two Columns</option>
                                <option value="3" <?php echo ($dashboard_config['layout']['columns'] ?? 2) == 3 ? 'selected' : ''; ?>>Three Columns</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="refresh_interval" class="form-label">Auto Refresh Interval</label>
                            <select class="form-select" name="refresh_interval" id="refresh_interval">
                                <option value="15" <?php echo ($dashboard_config['layout']['refresh_interval'] ?? 30) == 15 ? 'selected' : ''; ?>>15 seconds</option>
                                <option value="30" <?php echo ($dashboard_config['layout']['refresh_interval'] ?? 30) == 30 ? 'selected' : ''; ?>>30 seconds</option>
                                <option value="60" <?php echo ($dashboard_config['layout']['refresh_interval'] ?? 30) == 60 ? 'selected' : ''; ?>>1 minute</option>
                                <option value="300" <?php echo ($dashboard_config['layout']['refresh_interval'] ?? 30) == 300 ? 'selected' : ''; ?>>5 minutes</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Theme Preview</label>
                            <div class="d-flex gap-2">
                                <div class="theme-preview bg-primary" data-theme="default" style="background: linear-gradient(45deg, #007bff, #0056b3) !important;"></div>
                                <div class="theme-preview bg-dark" data-theme="dark" style="background: linear-gradient(45deg, #343a40, #212529) !important;"></div>
                                <div class="theme-preview bg-light" data-theme="light" style="background: linear-gradient(45deg, #f8f9fa, #e9ecef) !important;"></div>
                                <div class="theme-preview" data-theme="green" style="background: linear-gradient(45deg, #28a745, #1e7e34) !important;"></div>
                                <div class="theme-preview" data-theme="purple" style="background: linear-gradient(45deg, #6f42c1, #5a2d91) !important;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cacti Content Configuration -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up text-danger"></i> Cacti Integration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cacti_devices" id="cacti_devices" 
                                       <?php echo ($dashboard_config['cacti']['devices'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="cacti_devices">
                                    <strong>Device List</strong>
                                    <br><small class="text-muted">Show monitored devices from Cacti</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cacti_graphs" id="cacti_graphs" 
                                       <?php echo ($dashboard_config['cacti']['graphs'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="cacti_graphs">
                                    <strong>Network Graphs</strong>
                                    <br><small class="text-muted">Display Cacti network graphs</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cacti_status" id="cacti_status" 
                                       <?php echo ($dashboard_config['cacti']['status'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="cacti_status">
                                    <strong>System Status</strong>
                                    <br><small class="text-muted">Show Cacti system status</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Cacti Status: 
                            <?php if ($cacti_status['success']): ?>
                                <span class="text-success">Connected</span>
                            <?php else: ?>
                                <span class="text-danger">Not Connected</span>
                                <?php if (isset($cacti_status['message'])): ?>
                                    <br><small><?php echo htmlspecialchars($cacti_status['message']); ?></small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- SNMP Content Configuration -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-activity text-warning"></i> SNMP Monitoring</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="snmp_monitoring" id="snmp_monitoring" 
                                       <?php echo ($dashboard_config['snmp']['monitoring'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="snmp_monitoring">
                                    <strong>SNMP Monitoring</strong>
                                    <br><small class="text-muted">Real-time SNMP device monitoring</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="snmp_graphs" id="snmp_graphs" 
                                       <?php echo ($dashboard_config['snmp']['graphs'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="snmp_graphs">
                                    <strong>SNMP Graphs</strong>
                                    <br><small class="text-muted">SNMP data visualization</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="snmp_alerts" id="snmp_alerts" 
                                       <?php echo ($dashboard_config['snmp']['alerts'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="snmp_alerts">
                                    <strong>SNMP Alerts</strong>
                                    <br><small class="text-muted">SNMP alert notifications</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-save"></i> Save Theme & Dashboard Configuration
                </button>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-eye"></i> Live Preview</h5>
                </div>
                <div class="card-body">
                    <h6>Theme Preview:</h6>
                    <div class="preview-card enabled" id="preview-theme">
                        <i class="bi bi-palette"></i> 
                        <strong><?php echo ucfirst($dashboard_config['layout']['theme'] ?? 'default'); ?> Theme</strong>
                        <br><small class="text-muted"><?php echo ($dashboard_config['layout']['columns'] ?? 2); ?> columns layout</small>
                    </div>

                    <h6 class="mt-3">Cacti Components:</h6>
                    <div class="preview-card <?php echo ($dashboard_config['cacti']['devices'] ?? true) ? 'enabled' : 'disabled'; ?>" id="preview-cacti-devices">
                        <i class="bi bi-hdd-network"></i> Device List
                    </div>
                    <div class="preview-card <?php echo ($dashboard_config['cacti']['graphs'] ?? true) ? 'enabled' : 'disabled'; ?>" id="preview-cacti-graphs">
                        <i class="bi bi-graph-up"></i> Network Graphs
                    </div>
                    <div class="preview-card <?php echo ($dashboard_config['cacti']['status'] ?? true) ? 'enabled' : 'disabled'; ?>" id="preview-cacti-status">
                        <i class="bi bi-info-circle"></i> System Status
                    </div>

                    <h6 class="mt-3">SNMP Components:</h6>
                    <div class="preview-card <?php echo ($dashboard_config['snmp']['monitoring'] ?? true) ? 'enabled' : 'disabled'; ?>" id="preview-snmp-monitoring">
                        <i class="bi bi-activity"></i> SNMP Monitoring
                    </div>
                    <div class="preview-card <?php echo ($dashboard_config['snmp']['graphs'] ?? true) ? 'enabled' : 'disabled'; ?>" id="preview-snmp-graphs">
                        <i class="bi bi-bar-chart"></i> SNMP Graphs
                    </div>
                    <div class="preview-card <?php echo ($dashboard_config['snmp']['alerts'] ?? true) ? 'enabled' : 'disabled'; ?>" id="preview-snmp-alerts">
                        <i class="bi bi-exclamation-triangle"></i> SNMP Alerts
                    </div>

                    <div class="mt-3">
                        <a href="dashboard_preview.php" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-eye"></i> Full Preview
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.preview-card {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin: 10px 0;
    background: white;
    transition: all 0.3s ease;
}
.preview-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1);
}
.preview-card.enabled {
    border-color: #28a745;
    background: #f8fff9;
}
.preview-card.disabled {
    border-color: #dc3545;
    background: #fff8f8;
    opacity: 0.6;
}
.theme-preview {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}
.theme-preview:hover {
    border-color: #007bff;
    transform: scale(1.1);
}
.theme-preview.selected {
    border-color: #28a745;
    transform: scale(1.1);
}
</style>

<script>
// Live preview updates
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const previewId = 'preview-' + this.name.replace('_', '-');
            const previewCard = document.getElementById(previewId);
            if (previewCard) {
                if (this.checked) {
                    previewCard.classList.remove('disabled');
                    previewCard.classList.add('enabled');
                } else {
                    previewCard.classList.remove('enabled');
                    previewCard.classList.add('disabled');
                }
            }
        });
    });

    // Theme preview selection
    const themeSelect = document.getElementById('theme');
    const themePreviews = document.querySelectorAll('.theme-preview');
    
    themePreviews.forEach(preview => {
        preview.addEventListener('click', function() {
            const theme = this.dataset.theme;
            themeSelect.value = theme;
            
            // Update preview selection
            themePreviews.forEach(p => p.classList.remove('selected'));
            this.classList.add('selected');
            
            // Update preview card
            const previewCard = document.getElementById('preview-theme');
            if (previewCard) {
                const strong = previewCard.querySelector('strong');
                if (strong) {
                    strong.textContent = theme.charAt(0).toUpperCase() + theme.slice(1) + ' Theme';
                }
            }
        });
    });

    // Initialize theme preview selection
    const currentTheme = themeSelect.value;
    const currentPreview = document.querySelector(`[data-theme="${currentTheme}"]`);
    if (currentPreview) {
        currentPreview.classList.add('selected');
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?> 