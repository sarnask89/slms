<?php
require_once 'module_loader.php';


$pdo = get_pdo();
$menu_config = [];

// Load menu configuration
try {
    $stmt = $pdo->query("SELECT * FROM menu_config WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($config) {
        $menu_config = json_decode($config['config'], true) ?: [];
    }
} catch (Exception $e) {
    // Create menu_config table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_config (
        id INT PRIMARY KEY AUTO_INCREMENT,
        config JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Default configuration
    $menu_config = [
        'structure' => [
            'show_snmp' => true,
            'show_dhcp' => true,
            'show_devices' => true,
            'show_clients' => true,
            'show_networks' => true,
            'show_packages' => true,
            'show_dashboard' => true,
            'show_theme' => true,
            'show_menu_editor' => true,
            'show_system' => true
        ],
        'behavior' => 'expanded',
        'style' => 'tree'
    ];
}

// Get menu items
$stmt = $pdo->query("SELECT * FROM menu_items WHERE enabled = 1 ORDER BY parent_id, position ASC");
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build menu tree
$menu_tree = [];
$children = [];
foreach ($menu_items as $item) {
    $menu_tree[$item['id']] = $item;
    $children[$item['parent_id']][] = $item['id'];
}

// Function to render menu tree
function renderMenuTree($parent_id, $menu_tree, $children, $style = 'tree', $level = 0) {
    if (empty($children[$parent_id])) return '';
    
    $html = '';
    if ($style === 'tree') {
        $html .= '<ul class="list-unstyled">';
    } elseif ($style === 'accordion') {
        $html .= '<div class="accordion" id="menuAccordion">';
    }
    
    foreach ($children[$parent_id] as $id) {
        $item = $menu_tree[$id];
        $has_children = !empty($children[$id]);
        $icon = $item['icon'] ? "bi-{$item['icon']}" : 'bi-circle';
        
        if ($style === 'tree') {
            $html .= '<li class="mb-1">';
            $html .= '<div class="d-flex align-items-center">';
            $html .= '<i class="bi ' . $icon . ' me-2"></i>';
            $html .= '<span class="text-primary">' . htmlspecialchars($item['label']) . '</span>';
            if ($has_children) {
                $html .= '<button class="btn btn-sm btn-outline-secondary ms-auto" onclick="toggleSubmenu(' . $item['id'] . ')">';
                $html .= '<i class="bi bi-chevron-down"></i>';
                $html .= '</button>';
            }
            $html .= '</div>';
            if ($has_children) {
                $html .= '<div id="submenu-' . $item['id'] . '" class="ms-3 mt-1">';
                $html .= renderMenuTree($id, $menu_tree, $children, $style, $level + 1);
                $html .= '</div>';
            }
            $html .= '</li>';
        } elseif ($style === 'accordion') {
            $html .= '<div class="accordion-item">';
            $html .= '<h2 class="accordion-header" id="heading' . $item['id'] . '">';
            $html .= '<button class="accordion-button ' . ($level > 0 ? 'collapsed' : '') . '" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $item['id'] . '">';
            $html .= '<i class="bi ' . $icon . ' me-2"></i>';
            $html .= htmlspecialchars($item['label']);
            $html .= '</button>';
            $html .= '</h2>';
            if ($has_children) {
                $html .= '<div id="collapse' . $item['id'] . '" class="accordion-collapse collapse ' . ($level === 0 ? 'show' : '') . '" data-bs-parent="#menuAccordion">';
                $html .= '<div class="accordion-body">';
                $html .= renderMenuTree($id, $menu_tree, $children, $style, $level + 1);
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
        } else { // flat
            $html .= '<div class="mb-2">';
            $html .= '<i class="bi ' . $icon . ' me-2"></i>';
            $html .= '<span class="text-primary">' . htmlspecialchars($item['label']) . '</span>';
            $html .= '</div>';
            if ($has_children) {
                $html .= renderMenuTree($id, $menu_tree, $children, $style, $level + 1);
            }
        }
    }
    
    if ($style === 'tree') {
        $html .= '</ul>';
    } elseif ($style === 'accordion') {
        $html .= '</div>';
    }
    
    return $html;
}

$pageTitle = 'Menu Preview';
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
        .menu-preview-container {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            background: white;
            min-height: 400px;
        }
        .menu-style-selector {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .menu-item {
            transition: all 0.2s ease;
        }
        .menu-item:hover {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 2px 4px;
        }
        .menu-item i {
            width: 20px;
            text-align: center;
        }
        .submenu {
            margin-left: 20px;
            border-left: 2px solid #e9ecef;
            padding-left: 10px;
        }
        .accordion-button:not(.collapsed) {
            background-color: #e7f1ff;
            color: #0c63e4;
        }
        .menu-config-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        .config-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .config-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../partials/layout.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-eye"></i> Menu Preview
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="menu_editor.php" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit Menu
                            </a>
                            <a href="../admin_menu.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Admin
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Menu Style Selector -->
                    <div class="col-md-4">
                        <div class="menu-style-selector">
                            <h5><i class="bi bi-gear"></i> Menu Style</h5>
                            <div class="mb-3">
                                <label class="form-label">Display Style:</label>
                                <select class="form-select" id="menuStyle" onchange="changeMenuStyle()">
                                    <option value="tree" <?php echo ($menu_config['style'] ?? 'tree') === 'tree' ? 'selected' : ''; ?>>Tree Structure</option>
                                    <option value="accordion" <?php echo ($menu_config['style'] ?? 'tree') === 'accordion' ? 'selected' : ''; ?>>Accordion</option>
                                    <option value="flat" <?php echo ($menu_config['style'] ?? 'tree') === 'flat' ? 'selected' : ''; ?>>Flat List</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Default Behavior:</label>
                                <select class="form-select" id="menuBehavior" onchange="changeMenuBehavior()">
                                    <option value="expanded" <?php echo ($menu_config['behavior'] ?? 'expanded') === 'expanded' ? 'selected' : ''; ?>>Always Expanded</option>
                                    <option value="collapsed" <?php echo ($menu_config['behavior'] ?? 'expanded') === 'collapsed' ? 'selected' : ''; ?>>Collapsed by Default</option>
                                    <option value="remember" <?php echo ($menu_config['behavior'] ?? 'expanded') === 'remember' ? 'selected' : ''; ?>>Remember State</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Visible Sections:</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showSnmp" <?php echo ($menu_config['structure']['show_snmp'] ?? true) ? 'checked' : ''; ?> onchange="updateMenuVisibility()">
                                    <label class="form-check-label" for="showSnmp">SNMP & Monitoring</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showDhcp" <?php echo ($menu_config['structure']['show_dhcp'] ?? true) ? 'checked' : ''; ?> onchange="updateMenuVisibility()">
                                    <label class="form-check-label" for="showDhcp">DHCP Import</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showDevices" <?php echo ($menu_config['structure']['show_devices'] ?? true) ? 'checked' : ''; ?> onchange="updateMenuVisibility()">
                                    <label class="form-check-label" for="showDevices">Device Management</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showClients" <?php echo ($menu_config['structure']['show_clients'] ?? true) ? 'checked' : ''; ?> onchange="updateMenuVisibility()">
                                    <label class="form-check-label" for="showClients">Client Management</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Preview -->
                    <div class="col-md-8">
                        <div class="menu-preview-container">
                            <h5 class="mb-3"><i class="bi bi-list"></i> Menu Preview</h5>
                            <div id="menuPreview">
                                <?php echo renderMenuTree(null, $menu_tree, $children, $menu_config['style'] ?? 'tree'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuration Summary -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="menu-config-summary">
                            <h5><i class="bi bi-info-circle"></i> Menu Configuration Summary</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="config-item">
                                        <span><strong>Style:</strong></span>
                                        <span class="badge bg-primary"><?php echo ucfirst($menu_config['style'] ?? 'tree'); ?></span>
                                    </div>
                                    <div class="config-item">
                                        <span><strong>Behavior:</strong></span>
                                        <span class="badge bg-info"><?php echo ucfirst($menu_config['behavior'] ?? 'expanded'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="config-item">
                                        <span><strong>Total Items:</strong></span>
                                        <span class="badge bg-secondary"><?php echo count($menu_items); ?></span>
                                    </div>
                                    <div class="config-item">
                                        <span><strong>Enabled Items:</strong></span>
                                        <span class="badge bg-success"><?php echo count(array_filter($menu_items, fn($item) => $item['enabled'] == 1)); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="config-item">
                                        <span><strong>Visible Sections:</strong></span>
                                        <span class="badge bg-warning"><?php echo count(array_filter($menu_config['structure'] ?? [])); ?></span>
                                    </div>
                                    <div class="config-item">
                                        <span><strong>Last Updated:</strong></span>
                                        <span class="text-muted"><?php echo date('Y-m-d H:i:s'); ?></span>
                                    </div>
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
        // Menu data
        const menuItems = <?php echo json_encode($menu_items); ?>;
        const menuConfig = <?php echo json_encode($menu_config); ?>;
        
        // Change menu style
        function changeMenuStyle() {
            const style = document.getElementById('menuStyle').value;
            const preview = document.getElementById('menuPreview');
            
            // Re-render menu with new style
            preview.innerHTML = renderMenuTree(null, menuItems, buildChildrenMap(), style);
            
            // Update accordion functionality if needed
            if (style === 'accordion') {
                initializeAccordion();
            }
        }
        
        // Change menu behavior
        function changeMenuBehavior() {
            const behavior = document.getElementById('menuBehavior').value;
            console.log('Menu behavior changed to:', behavior);
            // This would typically save to database and apply to actual menu
        }
        
        // Update menu visibility
        function updateMenuVisibility() {
            const showSnmp = document.getElementById('showSnmp').checked;
            const showDhcp = document.getElementById('showDhcp').checked;
            const showDevices = document.getElementById('showDevices').checked;
            const showClients = document.getElementById('showClients').checked;
            
            // Filter menu items based on visibility settings
            const filteredItems = menuItems.filter(item => {
                const label = item.label.toLowerCase();
                if (label.includes('snmp') || label.includes('monitoring')) return showSnmp;
                if (label.includes('dhcp')) return showDhcp;
                if (label.includes('device')) return showDevices;
                if (label.includes('client')) return showClients;
                return true; // Show other items by default
            });
            
            // Re-render menu with filtered items
            const preview = document.getElementById('menuPreview');
            const style = document.getElementById('menuStyle').value;
            preview.innerHTML = renderMenuTree(null, filteredItems, buildChildrenMap(filteredItems), style);
        }
        
        // Build children map
        function buildChildrenMap(items = menuItems) {
            const children = {};
            items.forEach(item => {
                if (!children[item.parent_id]) {
                    children[item.parent_id] = [];
                }
                children[item.parent_id].push(item.id);
            });
            return children;
        }
        
        // Render menu tree (JavaScript version)
        function renderMenuTree(parentId, items, children, style = 'tree', level = 0) {
            if (!children[parentId]) return '';
            
            let html = '';
            if (style === 'tree') {
                html += '<ul class="list-unstyled">';
            } else if (style === 'accordion') {
                html += '<div class="accordion" id="menuAccordion">';
            }
            
            children[parentId].forEach(id => {
                const item = items.find(item => item.id == id);
                if (!item) return;
                
                const hasChildren = children[id] && children[id].length > 0;
                const icon = item.icon ? `bi-${item.icon}` : 'bi-circle';
                
                if (style === 'tree') {
                    html += '<li class="mb-1">';
                    html += '<div class="d-flex align-items-center menu-item">';
                    html += `<i class="bi ${icon} me-2"></i>`;
                    html += `<span class="text-primary">${item.label}</span>`;
                    if (hasChildren) {
                        html += `<button class="btn btn-sm btn-outline-secondary ms-auto" onclick="toggleSubmenu(${item.id})">`;
                        html += '<i class="bi bi-chevron-down"></i>';
                        html += '</button>';
                    }
                    html += '</div>';
                    if (hasChildren) {
                        html += `<div id="submenu-${item.id}" class="ms-3 mt-1 submenu">`;
                        html += renderMenuTree(item.id, items, children, style, level + 1);
                        html += '</div>';
                    }
                    html += '</li>';
                } else if (style === 'accordion') {
                    html += '<div class="accordion-item">';
                    html += `<h2 class="accordion-header" id="heading${item.id}">`;
                    html += `<button class="accordion-button ${level > 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${item.id}">`;
                    html += `<i class="bi ${icon} me-2"></i>`;
                    html += item.label;
                    html += '</button>';
                    html += '</h2>';
                    if (hasChildren) {
                        html += `<div id="collapse${item.id}" class="accordion-collapse collapse ${level === 0 ? 'show' : ''}" data-bs-parent="#menuAccordion">`;
                        html += '<div class="accordion-body">';
                        html += renderMenuTree(item.id, items, children, style, level + 1);
                        html += '</div>';
                        html += '</div>';
                    }
                    html += '</div>';
                } else { // flat
                    html += '<div class="mb-2 menu-item">';
                    html += `<i class="bi ${icon} me-2"></i>`;
                    html += `<span class="text-primary">${item.label}</span>`;
                    html += '</div>';
                    if (hasChildren) {
                        html += renderMenuTree(item.id, items, children, style, level + 1);
                    }
                }
            });
            
            if (style === 'tree') {
                html += '</ul>';
            } else if (style === 'accordion') {
                html += '</div>';
            }
            
            return html;
        }
        
        // Toggle submenu
        function toggleSubmenu(id) {
            const submenu = document.getElementById(`submenu-${id}`);
            const button = submenu.previousElementSibling.querySelector('button');
            const icon = button.querySelector('i');
            
            if (submenu.style.display === 'none') {
                submenu.style.display = 'block';
                icon.className = 'bi bi-chevron-up';
            } else {
                submenu.style.display = 'none';
                icon.className = 'bi bi-chevron-down';
            }
        }
        
        // Initialize accordion
        function initializeAccordion() {
            // Bootstrap accordion is automatically initialized
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial state based on behavior
            const behavior = document.getElementById('menuBehavior').value;
            if (behavior === 'collapsed') {
                // Collapse all submenus initially
                document.querySelectorAll('.submenu').forEach(submenu => {
                    submenu.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
require_once '../partials/layout.php';
?> 