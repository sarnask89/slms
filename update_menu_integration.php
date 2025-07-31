<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/modules/helpers/auth_helper.php';

// Require login and admin access
require_login();
require_admin();

$pdo = get_pdo();

// WebGL Interface Menu Items
$webglMenuItems = [
    // Main WebGL Interface
    [
        'label' => 'WebGL Console',
        'url' => '/webgl_demo_integrated.php',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 1,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-display', 'description' => '3D WebGL Network Management Interface'])
    ],
    
    // Network Management Section
    [
        'label' => 'Network Management',
        'url' => null,
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 10,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-hdd-network', 'description' => 'Network infrastructure management', 'is_section' => true])
    ],
    [
        'label' => 'Clients Management',
        'url' => '/webgl_demo_integrated.php?module=clients',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 11,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-people', 'description' => 'Manage client information and accounts'])
    ],
    [
        'label' => 'Device Monitoring',
        'url' => '/webgl_demo_integrated.php?module=devices',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 12,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-pc-display', 'description' => 'Monitor network devices and equipment'])
    ],
    [
        'label' => 'Network Configuration',
        'url' => '/webgl_demo_integrated.php?module=networks',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 13,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-diagram-3', 'description' => 'Configure network settings and topology'])
    ],
    [
        'label' => 'DHCP Management',
        'url' => '/webgl_demo_integrated.php?module=dhcp',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 14,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-wifi', 'description' => 'Manage DHCP clients and leases'])
    ],
    [
        'label' => 'SNMP Monitoring',
        'url' => '/webgl_demo_integrated.php?module=snmp',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 15,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-graph-up', 'description' => 'SNMP device monitoring and polling'])
    ],
    
    // Business Operations Section
    [
        'label' => 'Business Operations',
        'url' => null,
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 20,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-briefcase', 'description' => 'Business and financial management', 'is_section' => true])
    ],
    [
        'label' => 'Invoice Management',
        'url' => '/webgl_demo_integrated.php?module=invoices',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 21,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-receipt', 'description' => 'Generate and manage invoices'])
    ],
    [
        'label' => 'Payment Tracking',
        'url' => '/webgl_demo_integrated.php?module=payments',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 22,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-credit-card', 'description' => 'Track payments and transactions'])
    ],
    [
        'label' => 'Service Management',
        'url' => '/webgl_demo_integrated.php?module=services',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 23,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-gear', 'description' => 'Manage service offerings and packages'])
    ],
    [
        'label' => 'Tariff Configuration',
        'url' => '/webgl_demo_integrated.php?module=tariffs',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 24,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-calculator', 'description' => 'Configure pricing and tariffs'])
    ],
    [
        'label' => 'Package Management',
        'url' => '/webgl_demo_integrated.php?module=packages',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 25,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-box', 'description' => 'Manage service packages and bundles'])
    ],
    
    // System Administration Section
    [
        'label' => 'System Administration',
        'url' => null,
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 30,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-shield-check', 'description' => 'System administration and security', 'is_section' => true])
    ],
    [
        'label' => 'User Management',
        'url' => '/webgl_demo_integrated.php?module=users',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 31,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-person-badge', 'description' => 'Manage system users and accounts'])
    ],
    [
        'label' => 'Access Control',
        'url' => '/webgl_demo_integrated.php?module=access',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 32,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-lock', 'description' => 'Manage access permissions and roles'])
    ],
    [
        'label' => 'Activity Logs',
        'url' => '/webgl_demo_integrated.php?module=activity',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 33,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-journal-text', 'description' => 'View system activity and audit logs'])
    ],
    [
        'label' => 'User Profiles',
        'url' => '/webgl_demo_integrated.php?module=profile',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 34,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-person-circle', 'description' => 'Manage user profiles and settings'])
    ],
    [
        'label' => 'Admin Panel',
        'url' => '/admin_menu.php',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 35,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-tools', 'description' => 'Advanced system administration'])
    ],
    
    // Monitoring & Analytics Section
    [
        'label' => 'Monitoring & Analytics',
        'url' => null,
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 40,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-graph-up-arrow', 'description' => 'Network monitoring and analytics', 'is_section' => true])
    ],
    [
        'label' => 'Network Dashboard',
        'url' => '/webgl_demo_integrated.php?module=dashboard',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 41,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-speedometer2', 'description' => 'Real-time network dashboard'])
    ],
    [
        'label' => 'Advanced Graphing',
        'url' => '/webgl_demo_integrated.php?module=graphing',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 42,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-graph-up', 'description' => 'Advanced network graphing and charts'])
    ],
    [
        'label' => 'Bandwidth Reports',
        'url' => '/webgl_demo_integrated.php?module=reports',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 43,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-file-earmark-text', 'description' => 'Bandwidth usage reports'])
    ],
    [
        'label' => 'Network Alerts',
        'url' => '/webgl_demo_integrated.php?module=alerts',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 44,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-exclamation-triangle', 'description' => 'Network alerts and notifications'])
    ],
    [
        'label' => 'Capacity Planning',
        'url' => '/webgl_demo_integrated.php?module=capacity',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 45,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-cpu', 'description' => 'Network capacity planning and analysis'])
    ],
    
    // Integration Tools Section
    [
        'label' => 'Integration Tools',
        'url' => null,
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 50,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-plug', 'description' => 'Third-party integrations and tools', 'is_section' => true])
    ],
    [
        'label' => 'Cacti Integration',
        'url' => '/webgl_demo_integrated.php?module=cacti',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 51,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-graph-up', 'description' => 'Cacti monitoring integration'])
    ],
    [
        'label' => 'MikroTik API',
        'url' => '/webgl_demo_integrated.php?module=mikrotik',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 52,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-router', 'description' => 'MikroTik router API integration'])
    ],
    [
        'label' => 'MNDP Discovery',
        'url' => '/webgl_demo_integrated.php?module=mndp',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 53,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-search', 'description' => 'MikroTik Neighbor Discovery Protocol'])
    ],
    [
        'label' => 'Data Import',
        'url' => '/webgl_demo_integrated.php?module=import',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 54,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-upload', 'description' => 'Import data from external sources'])
    ],
    [
        'label' => 'Data Export',
        'url' => '/webgl_demo_integrated.php?module=export',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 55,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-download', 'description' => 'Export data to various formats'])
    ],
    
    // Development Tools Section
    [
        'label' => 'Development Tools',
        'url' => null,
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 60,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-code-slash', 'description' => 'Development and debugging tools', 'is_section' => true])
    ],
    [
        'label' => 'SQL Console',
        'url' => '/webgl_demo_integrated.php?module=sql',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 61,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-database', 'description' => 'Database SQL console'])
    ],
    [
        'label' => 'Debug Tools',
        'url' => '/webgl_demo_integrated.php?module=debug',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 62,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-bug', 'description' => 'System debugging tools'])
    ],
    [
        'label' => 'System Tests',
        'url' => '/webgl_demo_integrated.php?module=test',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 63,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-check-circle', 'description' => 'System testing and validation'])
    ],
    [
        'label' => 'Configuration',
        'url' => '/webgl_demo_integrated.php?module=config',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 64,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-gear-fill', 'description' => 'System configuration settings'])
    ],
    [
        'label' => 'Documentation',
        'url' => '/webgl_demo_integrated.php?module=docs',
        'type' => 'link',
        'script' => null,
        'parent_id' => null,
        'position' => 65,
        'enabled' => 1,
        'options' => json_encode(['icon' => 'bi-book', 'description' => 'System documentation and guides'])
    ]
];

// Function to add menu items
function addMenuItems($pdo, $items) {
    $added = 0;
    $updated = 0;
    
    foreach ($items as $item) {
        // Check if item already exists
        $stmt = $pdo->prepare("SELECT id FROM menu_items WHERE label = ? AND url = ?");
        $stmt->execute([$item['label'], $item['url']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing item
            $stmt = $pdo->prepare("UPDATE menu_items SET type = ?, script = ?, position = ?, enabled = ?, options = ? WHERE id = ?");
            $stmt->execute([
                $item['type'],
                $item['script'],
                $item['position'],
                $item['enabled'],
                $item['options'],
                $existing['id']
            ]);
            $updated++;
        } else {
            // Add new item
            $stmt = $pdo->prepare("INSERT INTO menu_items (label, url, type, script, parent_id, position, enabled, options) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $item['label'],
                $item['url'],
                $item['type'],
                $item['script'],
                $item['parent_id'],
                $item['position'],
                $item['enabled'],
                $item['options']
            ]);
            $added++;
        }
    }
    
    return ['added' => $added, 'updated' => $updated];
}

// Process the menu integration
try {
    $result = addMenuItems($pdo, $webglMenuItems);
    
    echo "<h2>Menu Integration Complete</h2>";
    echo "<p>Successfully processed menu items:</p>";
    echo "<ul>";
    echo "<li>Added: " . $result['added'] . " new menu items</li>";
    echo "<li>Updated: " . $result['updated'] . " existing menu items</li>";
    echo "</ul>";
    
    echo "<h3>WebGL Interface Features Added:</h3>";
    echo "<ul>";
    echo "<li><strong>WebGL Console:</strong> 3D network management interface</li>";
    echo "<li><strong>Network Management:</strong> Clients, devices, networks, DHCP, SNMP</li>";
    echo "<li><strong>Business Operations:</strong> Invoices, payments, services, tariffs, packages</li>";
    echo "<li><strong>System Administration:</strong> Users, access control, activity logs, profiles</li>";
    echo "<li><strong>Monitoring & Analytics:</strong> Dashboard, graphing, reports, alerts, capacity planning</li>";
    echo "<li><strong>Integration Tools:</strong> Cacti, MikroTik API, MNDP, data import/export</li>";
    echo "<li><strong>Development Tools:</strong> SQL console, debug tools, system tests, configuration, documentation</li>";
    echo "</ul>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Access the WebGL interface at: <a href='/webgl_demo_integrated.php'>/webgl_demo_integrated.php</a></li>";
    echo "<li>Test all module integrations</li>";
    echo "<li>Configure any additional settings as needed</li>";
    echo "<li>Review and customize the 3D visualizations</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>Error during menu integration: " . $e->getMessage() . "</p>";
}
?> 