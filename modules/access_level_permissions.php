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
$access_level_id = $_GET['access_level_id'] ?? null;
$format = $_GET['format'] ?? 'json';

if (!$access_level_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Access level ID is required']);
    exit;
}

// Define system sections and actions (same as in access_level_manager.php)
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

try {
    // Get access level info
    $stmt = $pdo->prepare("SELECT * FROM access_levels WHERE id = ?");
    $stmt->execute([$access_level_id]);
    $access_level = $stmt->fetch();
    
    if (!$access_level) {
        http_response_code(404);
        echo json_encode(['error' => 'Access level not found']);
        exit;
    }
    
    // Get permissions for this access level
    $stmt = $pdo->prepare("SELECT section, action FROM access_level_permissions WHERE access_level_id = ?");
    $stmt->execute([$access_level_id]);
    $permissions = $stmt->fetchAll();
    
    $permission_list = [];
    foreach ($permissions as $perm) {
        $permission_list[] = $perm['section'] . ':' . $perm['action'];
    }
    
    if ($format === 'html') {
        // Return HTML format for modal display
        ?>
        <div class="row">
            <div class="col-12">
                <h6><?= htmlspecialchars($access_level['name']) ?></h6>
                <p class="text-muted"><?= htmlspecialchars($access_level['description'] ?? 'Brak opisu') ?></p>
                
                <div class="row">
                    <?php foreach ($system_sections as $section_key => $section): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-folder"></i> <?= htmlspecialchars($section['name']) ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $section_permissions = 0;
                                    foreach ($section['actions'] as $action_key => $action_name): 
                                        $permission_key = $section_key . ':' . $action_key;
                                        if (in_array($permission_key, $permission_list)):
                                            $section_permissions++;
                                    ?>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span><?= htmlspecialchars($action_name) ?></span>
                                            <span class="badge bg-success">✓</span>
                                        </div>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    if ($section_permissions == 0):
                                    ?>
                                        <div class="text-muted">
                                            <small>Brak uprawnień</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-3">
                    <strong>Łącznie uprawnień:</strong> <?= count($permission_list) ?>
                </div>
            </div>
        </div>
        <?php
    } else {
        // Return JSON format for JavaScript
        $response = [
            'access_level' => $access_level,
            'sections' => $system_sections,
            'permissions' => $permission_list
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    if ($format === 'html') {
        echo '<div class="alert alert-danger">Błąd podczas ładowania uprawnień: ' . htmlspecialchars($e->getMessage()) . '</div>';
    } else {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?> 