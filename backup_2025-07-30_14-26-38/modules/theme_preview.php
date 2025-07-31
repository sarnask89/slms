<?php
require_once __DIR__ . '/../config.php';

$pdo = get_pdo();
$active_theme = null;

// Get active theme
try {
    $stmt = $pdo->query("SELECT * FROM themes WHERE is_active = TRUE LIMIT 1");
    $active_theme = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // No active theme
}

// Default theme configuration
$default_theme = [
    'layout' => [
        'type' => 'top',
        'sidebar_width' => '250px',
        'header_height' => '60px'
    ],
    'colors' => [
        'primary' => '#007bff',
        'secondary' => '#6c757d',
        'success' => '#28a745',
        'danger' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8',
        'light' => '#f8f9fa',
        'dark' => '#343a40'
    ],
    'typography' => [
        'font_family' => 'Arial, sans-serif',
        'font_size' => '14px',
        'line_height' => '1.5'
    ],
    'menu' => [
        'style' => 'tree',
        'behavior' => 'expanded',
        'background' => '#f8f9fa',
        'text_color' => '#212529'
    ],
    'components' => [
        'border_radius' => '4px',
        'box_shadow' => '0 2px 4px rgba(0,0,0,0.1)',
        'transition_speed' => '0.2s'
    ]
];

$theme_config = $active_theme ? json_decode($active_theme['config'], true) : $default_theme;

$pageTitle = 'Theme Preview';
ob_start();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - sLMS</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>👁️</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
    <style>
        .theme-preview-container {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background: white;
            overflow: hidden;
        }
        .preview-header {
            background: <?php echo $theme_config['colors']['primary']; ?>;
            color: white;
            padding: 15px 20px;
            height: <?php echo $theme_config['layout']['header_height']; ?>;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .preview-sidebar {
            background: <?php echo $theme_config['menu']['background']; ?>;
            color: <?php echo $theme_config['menu']['text_color']; ?>;
            width: <?php echo $theme_config['layout']['type'] === 'left' ? $theme_config['layout']['sidebar_width'] : '0'; ?>;
            min-height: 400px;
            padding: 20px;
            border-right: 1px solid #dee2e6;
            display: <?php echo $theme_config['layout']['type'] === 'left' ? 'block' : 'none'; ?>;
        }
        .preview-content {
            padding: 20px;
            font-family: <?php echo $theme_config['typography']['font_family']; ?>;
            font-size: <?php echo $theme_config['typography']['font_size']; ?>;
            line-height: <?php echo $theme_config['typography']['line_height']; ?>;
        }
        .preview-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: <?php echo $theme_config['components']['border_radius']; ?>;
            box-shadow: <?php echo $theme_config['components']['box_shadow']; ?>;
            padding: 20px;
            margin-bottom: 20px;
            transition: all <?php echo $theme_config['components']['transition_speed']; ?> ease;
        }
        .preview-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .preview-button {
            background: <?php echo $theme_config['colors']['primary']; ?>;
            color: white;
            border: none;
            border-radius: <?php echo $theme_config['components']['border_radius']; ?>;
            padding: 8px 16px;
            margin: 5px;
            transition: all <?php echo $theme_config['components']['transition_speed']; ?> ease;
        }
        .preview-button:hover {
            background: <?php echo $theme_config['colors']['secondary']; ?>;
            transform: translateY(-1px);
        }
        .preview-button.success {
            background: <?php echo $theme_config['colors']['success']; ?>;
        }
        .preview-button.danger {
            background: <?php echo $theme_config['colors']['danger']; ?>;
        }
        .preview-button.warning {
            background: <?php echo $theme_config['colors']['warning']; ?>;
        }
        .preview-button.info {
            background: <?php echo $theme_config['colors']['info']; ?>;
        }
        .theme-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .color-swatch {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            display: inline-block;
            margin: 2px;
            border: 1px solid #dee2e6;
        }
        .menu-item {
            padding: 8px 12px;
            margin: 2px 0;
            border-radius: <?php echo $theme_config['components']['border_radius']; ?>;
            transition: all <?php echo $theme_config['components']['transition_speed']; ?> ease;
        }
        .menu-item:hover {
            background: rgba(0,0,0,0.1);
        }
        .menu-item.active {
            background: <?php echo $theme_config['colors']['primary']; ?>;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../partials/layout.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-eye"></i> Theme Preview
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="theme_compositor.php" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-palette"></i> Edit Theme
                            </a>
                            <a href="../admin_menu.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Admin
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Theme Information -->
                <div class="theme-info">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="bi bi-info-circle"></i> Current Theme</h5>
                            <p class="mb-1">
                                <strong>Name:</strong> 
                                <?php echo $active_theme ? htmlspecialchars($active_theme['name']) : 'Default Theme'; ?>
                            </p>
                            <p class="mb-1">
                                <strong>Layout:</strong> 
                                <?php echo ucfirst($theme_config['layout']['type']); ?> Menu
                            </p>
                            <p class="mb-1">
                                <strong>Menu Style:</strong> 
                                <?php echo ucfirst($theme_config['menu']['style']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="bi bi-palette"></i> Color Palette</h5>
                            <div>
                                <span class="color-swatch" style="background: <?php echo $theme_config['colors']['primary']; ?>;" title="Primary"></span>
                                <span class="color-swatch" style="background: <?php echo $theme_config['colors']['secondary']; ?>;" title="Secondary"></span>
                                <span class="color-swatch" style="background: <?php echo $theme_config['colors']['success']; ?>;" title="Success"></span>
                                <span class="color-swatch" style="background: <?php echo $theme_config['colors']['danger']; ?>;" title="Danger"></span>
                                <span class="color-swatch" style="background: <?php echo $theme_config['colors']['warning']; ?>;" title="Warning"></span>
                                <span class="color-swatch" style="background: <?php echo $theme_config['colors']['info']; ?>;" title="Info"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Theme Preview -->
                <div class="theme-preview-container">
                    <!-- Header -->
                    <div class="preview-header">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-gear me-2"></i>
                            <strong>sLMS System</strong>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle me-2"></i>
                            <span>Admin User</span>
                        </div>
                    </div>

                    <div class="d-flex">
                        <!-- Sidebar (if left layout) -->
                        <?php if ($theme_config['layout']['type'] === 'left'): ?>
                        <div class="preview-sidebar">
                            <h6 class="mb-3"><i class="bi bi-list"></i> Navigation</h6>
                            <div class="menu-item active">
                                <i class="bi bi-house me-2"></i> Dashboard
                            </div>
                            <div class="menu-item">
                                <i class="bi bi-people me-2"></i> Clients
                            </div>
                            <div class="menu-item">
                                <i class="bi bi-hdd-network me-2"></i> Devices
                            </div>
                            <div class="menu-item">
                                <i class="bi bi-diagram-3 me-2"></i> Networks
                            </div>
                            <div class="menu-item">
                                <i class="bi bi-activity me-2"></i> Monitoring
                            </div>
                            <div class="menu-item">
                                <i class="bi bi-gear me-2"></i> Settings
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Main Content -->
                        <div class="preview-content flex-grow-1">
                            <h4 class="mb-4">Theme Preview</h4>
                            
                            <!-- Sample Cards -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="preview-card">
                                        <h5><i class="bi bi-graph-up"></i> System Statistics</h5>
                                        <p>This card demonstrates the theme's card styling with hover effects and shadows.</p>
                                        <div class="d-flex gap-2">
                                            <button class="preview-button">Primary</button>
                                            <button class="preview-button success">Success</button>
                                            <button class="preview-button danger">Danger</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="preview-card">
                                        <h5><i class="bi bi-people"></i> User Management</h5>
                                        <p>Another example card showing different button styles and color schemes.</p>
                                        <div class="d-flex gap-2">
                                            <button class="preview-button warning">Warning</button>
                                            <button class="preview-button info">Info</button>
                                            <button class="preview-button">Default</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sample Table -->
                            <div class="preview-card">
                                <h5><i class="bi bi-table"></i> Sample Data Table</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Device 1</td>
                                                <td><span class="badge bg-success">Online</span></td>
                                                <td>
                                                    <button class="preview-button btn-sm">Edit</button>
                                                    <button class="preview-button danger btn-sm">Delete</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Device 2</td>
                                                <td><span class="badge bg-warning">Warning</span></td>
                                                <td>
                                                    <button class="preview-button btn-sm">Edit</button>
                                                    <button class="preview-button danger btn-sm">Delete</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Device 3</td>
                                                <td><span class="badge bg-danger">Offline</span></td>
                                                <td>
                                                    <button class="preview-button btn-sm">Edit</button>
                                                    <button class="preview-button danger btn-sm">Delete</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Sample Forms -->
                            <div class="preview-card">
                                <h5><i class="bi bi-pencil"></i> Sample Form</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control" placeholder="Enter name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" placeholder="Enter email">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" rows="3" placeholder="Enter message"></textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="preview-button">Submit</button>
                                    <button class="preview-button" style="background: <?php echo $theme_config['colors']['secondary']; ?>;">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Theme Configuration Summary -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="theme-info">
                            <h5><i class="bi bi-gear"></i> Theme Configuration</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Font Family:</strong><br>
                                    <small class="text-muted"><?php echo $theme_config['typography']['font_family']; ?></small>
                                </div>
                                <div class="col-md-3">
                                    <strong>Font Size:</strong><br>
                                    <small class="text-muted"><?php echo $theme_config['typography']['font_size']; ?></small>
                                </div>
                                <div class="col-md-3">
                                    <strong>Border Radius:</strong><br>
                                    <small class="text-muted"><?php echo $theme_config['components']['border_radius']; ?></small>
                                </div>
                                <div class="col-md-3">
                                    <strong>Transition Speed:</strong><br>
                                    <small class="text-muted"><?php echo $theme_config['components']['transition_speed']; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add some interactive elements to the preview
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers to menu items
            document.querySelectorAll('.menu-item').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Add hover effects to buttons
            document.querySelectorAll('.preview-button').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?> 