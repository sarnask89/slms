<?php
require_once 'module_loader.php';


$pdo = get_pdo();
$message = '';
$themes = [];

// Create themes table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS themes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        config JSON NOT NULL,
        is_active BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
    // Table already exists
}

// Load existing themes
try {
    $stmt = $pdo->query("SELECT * FROM themes ORDER BY name ASC");
    $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Handle error silently
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save_theme':
                $theme_name = $_POST['theme_name'] ?? 'Untitled Theme';
                $theme_description = $_POST['theme_description'] ?? '';
                
                $theme_config = [
                    'layout' => [
                        'type' => $_POST['layout_type'] ?? 'top',
                        'sidebar_width' => $_POST['sidebar_width'] ?? '250px',
                        'header_height' => $_POST['header_height'] ?? '60px'
                    ],
                    'colors' => [
                        'primary' => $_POST['primary_color'] ?? '#007bff',
                        'secondary' => $_POST['secondary_color'] ?? '#6c757d',
                        'success' => $_POST['success_color'] ?? '#28a745',
                        'danger' => $_POST['danger_color'] ?? '#dc3545',
                        'warning' => $_POST['warning_color'] ?? '#ffc107',
                        'info' => $_POST['info_color'] ?? '#17a2b8',
                        'light' => $_POST['light_color'] ?? '#f8f9fa',
                        'dark' => $_POST['dark_color'] ?? '#343a40'
                    ],
                    'typography' => [
                        'font_family' => $_POST['font_family'] ?? 'Arial, sans-serif',
                        'font_size' => $_POST['font_size'] ?? '14px',
                        'line_height' => $_POST['line_height'] ?? '1.5'
                    ],
                    'menu' => [
                        'style' => $_POST['menu_style'] ?? 'tree',
                        'behavior' => $_POST['menu_behavior'] ?? 'expanded',
                        'background' => $_POST['menu_background'] ?? '#f8f9fa',
                        'text_color' => $_POST['menu_text_color'] ?? '#212529'
                    ],
                    'components' => [
                        'border_radius' => $_POST['border_radius'] ?? '4px',
                        'box_shadow' => $_POST['box_shadow'] ?? '0 2px 4px rgba(0,0,0,0.1)',
                        'transition_speed' => $_POST['transition_speed'] ?? '0.2s'
                    ]
                ];
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO themes (name, description, config) VALUES (?, ?, ?)");
                    $stmt->execute([$theme_name, $theme_description, json_encode($theme_config)]);
                    $message = 'Theme saved successfully!';
                    
                    // Reload themes
                    $stmt = $pdo->query("SELECT * FROM themes ORDER BY name ASC");
                    $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $message = 'Error saving theme: ' . $e->getMessage();
                }
                break;

            case 'delete_theme':
                $theme_id = $_POST['theme_id'] ?? 0;
                try {
                    $stmt = $pdo->prepare("DELETE FROM themes WHERE id = ?");
                    $stmt->execute([$theme_id]);
                    $message = 'Theme deleted successfully!';
                    
                    // Reload themes
                    $stmt = $pdo->query("SELECT * FROM themes ORDER BY name ASC");
                    $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $message = 'Error deleting theme: ' . $e->getMessage();
                }
                break;

            case 'activate_theme':
                $theme_id = $_POST['theme_id'] ?? 0;
                try {
                    // Deactivate all themes
                    $pdo->exec("UPDATE themes SET is_active = FALSE");
                    
                    // Activate selected theme
                    $stmt = $pdo->prepare("UPDATE themes SET is_active = TRUE WHERE id = ?");
                    $stmt->execute([$theme_id]);
                    $message = 'Theme activated successfully!';
                } catch (Exception $e) {
                    $message = 'Error activating theme: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get active theme
$active_theme = null;
try {
    $stmt = $pdo->query("SELECT * FROM themes WHERE is_active = TRUE LIMIT 1");
    $active_theme = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // No active theme
}

$pageTitle = 'Theme Compositor';
ob_start();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - sLMS</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎨</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
    <style>
        .theme-preview {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            background: white;
            transition: all 0.3s ease;
        }
        .theme-preview:hover {
            border-color: #007bff;
            box-shadow: 0 4px 8px rgba(0,123,255,0.1);
        }
        .theme-preview.active {
            border-color: #28a745;
            background: #f8fff9;
        }
        .color-picker {
            width: 50px;
            height: 40px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            display: inline-block;
            margin-right: 10px;
            border: 1px solid #dee2e6;
        }
        .theme-selector {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .config-section {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        .live-preview {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            background: white;
            min-height: 300px;
        }
    </style>
</head>
<body>
    <!-- Theme Selector Dropdown -->
    <div class="theme-selector">
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-palette me-2"></i>
            <strong>Theme</strong>
        </div>
        <select class="form-select form-select-sm" id="themeSelector" onchange="changeTheme(this.value)">
            <option value="">Default Theme</option>
            <?php foreach ($themes as $theme): ?>
                <option value="<?php echo $theme['id']; ?>" <?php echo $theme['is_active'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($theme['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php include '../partials/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../partials/layout.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-palette"></i> Theme Compositor
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="theme_preview.php" class="btn btn-sm btn-outline-primary">
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

                <div class="row">
                    <!-- Theme Configuration -->
                    <div class="col-md-8">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="save_theme">
                            
                            <!-- Theme Information -->
                            <div class="config-section">
                                <h4><i class="bi bi-info-circle"></i> Theme Information</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="theme_name" class="form-label">Theme Name</label>
                                            <input type="text" class="form-control" id="theme_name" name="theme_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="theme_description" class="form-label">Description</label>
                                            <input type="text" class="form-control" id="theme_description" name="theme_description">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Layout Configuration -->
                            <div class="config-section">
                                <h4><i class="bi bi-layout-text-window"></i> Layout Settings</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="layout_type" class="form-label">Layout Type</label>
                                            <select class="form-select" id="layout_type" name="layout_type">
                                                <option value="top">Top Menu</option>
                                                <option value="left">Left Menu</option>
                                                <option value="sidebar">Sidebar</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="sidebar_width" class="form-label">Sidebar Width</label>
                                            <input type="text" class="form-control" id="sidebar_width" name="sidebar_width" value="250px">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="header_height" class="form-label">Header Height</label>
                                            <input type="text" class="form-control" id="header_height" name="header_height" value="60px">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Color Scheme -->
                            <div class="config-section">
                                <h4><i class="bi bi-palette"></i> Color Scheme</h4>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="primary_color" class="form-label">Primary Color</label>
                                            <div class="d-flex align-items-center">
                                                <span class="color-preview" id="primary_preview"></span>
                                                <input type="color" class="color-picker" id="primary_color" name="primary_color" value="#007bff" onchange="updateColorPreview('primary')">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="secondary_color" class="form-label">Secondary Color</label>
                                            <div class="d-flex align-items-center">
                                                <span class="color-preview" id="secondary_preview"></span>
                                                <input type="color" class="color-picker" id="secondary_color" name="secondary_color" value="#6c757d" onchange="updateColorPreview('secondary')">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="success_color" class="form-label">Success Color</label>
                                            <div class="d-flex align-items-center">
                                                <span class="color-preview" id="success_preview"></span>
                                                <input type="color" class="color-picker" id="success_color" name="success_color" value="#28a745" onchange="updateColorPreview('success')">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="danger_color" class="form-label">Danger Color</label>
                                            <div class="d-flex align-items-center">
                                                <span class="color-preview" id="danger_preview"></span>
                                                <input type="color" class="color-picker" id="danger_color" name="danger_color" value="#dc3545" onchange="updateColorPreview('danger')">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Menu Configuration -->
                            <div class="config-section">
                                <h4><i class="bi bi-list"></i> Menu Settings</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="menu_style" class="form-label">Menu Style</label>
                                            <select class="form-select" id="menu_style" name="menu_style">
                                                <option value="tree">Tree</option>
                                                <option value="dropdown">Dropdown</option>
                                                <option value="accordion">Accordion</option>
                                                <option value="tabs">Tabs</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="menu_behavior" class="form-label">Menu Behavior</label>
                                            <select class="form-select" id="menu_behavior" name="menu_behavior">
                                                <option value="expanded">Always Expanded</option>
                                                <option value="collapsed">Collapsed by Default</option>
                                                <option value="remember">Remember State</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="menu_background" class="form-label">Menu Background</label>
                                            <div class="d-flex align-items-center">
                                                <span class="color-preview" id="menu_bg_preview"></span>
                                                <input type="color" class="color-picker" id="menu_background" name="menu_background" value="#f8f9fa" onchange="updateColorPreview('menu_bg')">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-save"></i> Save Theme
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Theme Gallery -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-images"></i> Saved Themes</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($themes)): ?>
                                    <p class="text-muted">No themes saved yet.</p>
                                <?php else: ?>
                                    <?php foreach ($themes as $theme): ?>
                                        <div class="theme-preview <?php echo $theme['is_active'] ? 'active' : ''; ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($theme['name']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($theme['description']); ?></small>
                                                    <br>
                                                    <small class="text-muted">Created: <?php echo date('M j, Y', strtotime($theme['created_at'])); ?></small>
                                                </div>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" onclick="activateTheme(<?php echo $theme['id']; ?>)">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" onclick="deleteTheme(<?php echo $theme['id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize color previews
        function updateColorPreview(type) {
            const color = document.getElementById(type + '_color').value;
            document.getElementById(type + '_preview').style.backgroundColor = color;
        }
        
        // Initialize all color previews
        document.addEventListener('DOMContentLoaded', function() {
            updateColorPreview('primary');
            updateColorPreview('secondary');
            updateColorPreview('success');
            updateColorPreview('danger');
            updateColorPreview('menu_bg');
        });
        
        // Change theme
        function changeTheme(themeId) {
            if (themeId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="activate_theme">
                    <input type="hidden" name="theme_id" value="${themeId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Activate theme
        function activateTheme(themeId) {
            if (confirm('Activate this theme?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="activate_theme">
                    <input type="hidden" name="theme_id" value="${themeId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Delete theme
        function deleteTheme(themeId) {
            if (confirm('Delete this theme? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_theme">
                    <input type="hidden" name="theme_id" value="${themeId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
require_once '../partials/layout.php';
?> 