<?php
require_once 'module_loader.php';


$pdo = get_pdo();
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_menu_item':
                $stmt = $pdo->prepare("INSERT INTO menu_items (label, url, type, parent_id, position, enabled, icon, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['label'],
                    $_POST['url'],
                    $_POST['type'] ?? 'link',
                    $_POST['parent_id'] ?: null,
                    $_POST['position'] ?? 0,
                    isset($_POST['enabled']) ? 1 : 0,
                    $_POST['icon'] ?? '',
                    $_POST['description'] ?? ''
                ]);
                $message = 'Menu item added successfully!';
                break;

            case 'update_menu_item':
                $stmt = $pdo->prepare("UPDATE menu_items SET label=?, url=?, type=?, parent_id=?, position=?, enabled=?, icon=?, description=? WHERE id=?");
                $stmt->execute([
                    $_POST['label'],
                    $_POST['url'],
                    $_POST['type'] ?? 'link',
                    $_POST['parent_id'] ?: null,
                    $_POST['position'] ?? 0,
                    isset($_POST['enabled']) ? 1 : 0,
                    $_POST['icon'] ?? '',
                    $_POST['description'] ?? '',
                    $_POST['id']
                ]);
                $message = 'Menu item updated successfully!';
                break;

            case 'delete_menu_item':
                $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id=?");
                $stmt->execute([$_POST['id']]);
                $message = 'Menu item deleted successfully!';
                break;

            case 'bulk_update':
                if (isset($_POST['menu_items']) && is_array($_POST['menu_items'])) {
                    foreach ($_POST['menu_items'] as $item) {
                        $stmt = $pdo->prepare("UPDATE menu_items SET enabled=?, position=?, parent_id=? WHERE id=?");
                        $stmt->execute([
                            isset($item['enabled']) ? 1 : 0,
                            $item['position'] ?? 0,
                            $item['parent_id'] ?: null,
                            $item['id']
                        ]);
                    }
                    $message = 'Menu items updated successfully!';
                }
                break;
        }
    }
}

// Get all menu items
$stmt = $pdo->query("SELECT * FROM menu_items ORDER BY parent_id, position ASC");
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build menu tree
$menu_tree = [];
$children = [];
foreach ($menu_items as $item) {
    $menu_tree[$item['id']] = $item;
    $children[$item['parent_id']][] = $item['id'];
}

// Function to render menu tree
function renderMenuTree($parent_id, $menu_tree, $children, $level = 0) {
    if (empty($children[$parent_id])) return '';
    
    $html = '';
    foreach ($children[$parent_id] as $id) {
        $item = $menu_tree[$id];
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        $has_children = !empty($children[$id]);
        
        $html .= '<tr data-id="' . $item['id'] . '" data-parent="' . $item['parent_id'] . '">';
        $html .= '<td>' . $indent . '<i class="bi bi-' . ($item['icon'] ?: 'circle') . '"></i> ' . htmlspecialchars($item['label']) . '</td>';
        $html .= '<td>' . htmlspecialchars($item['url']) . '</td>';
        $html .= '<td>' . htmlspecialchars($item['type']) . '</td>';
        $html .= '<td>' . ($item['enabled'] ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>') . '</td>';
        $html .= '<td>' . $item['position'] . '</td>';
        $html .= '<td>';
        $html .= '<div class="btn-group btn-group-sm">';
        $html .= '<button type="button" class="btn btn-primary" onclick="editMenuItem(' . $item['id'] . ')"><i class="bi bi-pencil"></i></button>';
        $html .= '<button type="button" class="btn btn-danger" onclick="deleteMenuItem(' . $item['id'] . ')"><i class="bi bi-trash"></i></button>';
        if ($has_children) {
            $html .= '<button type="button" class="btn btn-info" onclick="toggleChildren(' . $item['id'] . ')"><i class="bi bi-chevron-down"></i></button>';
        }
        $html .= '</div>';
        $html .= '</td>';
        $html .= '</tr>';
        
        if ($has_children) {
            $html .= renderMenuTree($id, $menu_tree, $children, $level + 1);
        }
    }
    return $html;
}

$pageTitle = 'Menu Editor';
ob_start();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - sLMS</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📋</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
    <style>
        .menu-item-row {
            transition: background-color 0.2s;
        }
        .menu-item-row:hover {
            background-color: #f8f9fa;
        }
        .menu-item-row.dragging {
            opacity: 0.5;
        }
        .menu-item-row.drop-target {
            background-color: #e3f2fd;
            border: 2px dashed #2196f3;
        }
        .menu-tree-indent {
            display: inline-block;
            width: 20px;
        }
        .menu-item-actions {
            white-space: nowrap;
        }
        .menu-preview {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background: #f8f9fa;
            max-height: 400px;
            overflow-y: auto;
        }
        .menu-item-form {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .icon-preview {
            font-size: 1.2em;
            margin-right: 8px;
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
                        <i class="bi bi-list-nested"></i> Menu Editor
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="showAddForm()">
                                <i class="bi bi-plus"></i> Add Menu Item
                            </button>
                            <a href="menu_preview.php" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i> Preview
                            </a>
                            <a href="../admin_menu.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Admin
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

                <div class="row">
                    <!-- Menu Items List -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-list"></i> Menu Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Label</th>
                                                <th>URL</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Position</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="menuItemsTable">
                                            <?php echo renderMenuTree(null, $menu_tree, $children); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Preview -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-eye"></i> Menu Preview</h5>
                            </div>
                            <div class="card-body">
                                <div class="menu-preview" id="menuPreview">
                                    <!-- Menu preview will be generated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add/Edit Menu Item Form -->
                <div class="menu-item-form" id="menuItemForm" style="display: none;">
                    <h4 id="formTitle">Add Menu Item</h4>
                    <form method="POST" action="">
                        <input type="hidden" name="action" id="formAction" value="add_menu_item">
                        <input type="hidden" name="id" id="formId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="label" class="form-label">Label</label>
                                    <input type="text" class="form-control" id="label" name="label" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="url" class="form-label">URL</label>
                                    <input type="text" class="form-control" id="url" name="url" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="link">Link</option>
                                        <option value="section">Section</option>
                                        <option value="divider">Divider</option>
                                        <option value="script">Script</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="parent_id" class="form-label">Parent</label>
                                    <select class="form-select" id="parent_id" name="parent_id">
                                        <option value="">No Parent (Top Level)</option>
                                        <?php foreach ($menu_items as $item): ?>
                                            <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['label']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="position" class="form-label">Position</label>
                                    <input type="number" class="form-control" id="position" name="position" value="0" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="icon" class="form-label">Icon (Bootstrap Icons)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-circle" id="iconPreview"></i></span>
                                        <input type="text" class="form-control" id="icon" name="icon" placeholder="e.g., gear, person, house">
                                    </div>
                                    <small class="form-text text-muted">Enter Bootstrap Icons class name (without 'bi-')</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="description" name="description">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enabled" name="enabled" checked>
                                <label class="form-check-label" for="enabled">
                                    Enabled
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Save
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="hideForm()">
                                <i class="bi bi-x"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menu item data
        const menuItems = <?php echo json_encode($menu_items); ?>;
        
        // Show add form
        function showAddForm() {
            document.getElementById('formTitle').textContent = 'Add Menu Item';
            document.getElementById('formAction').value = 'add_menu_item';
            document.getElementById('formId').value = '';
            document.getElementById('menuItemForm').style.display = 'block';
            resetForm();
        }
        
        // Edit menu item
        function editMenuItem(id) {
            const item = menuItems.find(item => item.id == id);
            if (item) {
                document.getElementById('formTitle').textContent = 'Edit Menu Item';
                document.getElementById('formAction').value = 'update_menu_item';
                document.getElementById('formId').value = item.id;
                document.getElementById('label').value = item.label;
                document.getElementById('url').value = item.url;
                document.getElementById('type').value = item.type;
                document.getElementById('parent_id').value = item.parent_id || '';
                document.getElementById('position').value = item.position;
                document.getElementById('icon').value = item.icon;
                document.getElementById('description').value = item.description;
                document.getElementById('enabled').checked = item.enabled == 1;
                updateIconPreview();
                document.getElementById('menuItemForm').style.display = 'block';
            }
        }
        
        // Delete menu item
        function deleteMenuItem(id) {
            if (confirm('Are you sure you want to delete this menu item?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_menu_item">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Hide form
        function hideForm() {
            document.getElementById('menuItemForm').style.display = 'none';
        }
        
        // Reset form
        function resetForm() {
            document.getElementById('label').value = '';
            document.getElementById('url').value = '';
            document.getElementById('type').value = 'link';
            document.getElementById('parent_id').value = '';
            document.getElementById('position').value = '0';
            document.getElementById('icon').value = '';
            document.getElementById('description').value = '';
            document.getElementById('enabled').checked = true;
            updateIconPreview();
        }
        
        // Update icon preview
        function updateIconPreview() {
            const icon = document.getElementById('icon').value || 'circle';
            document.getElementById('iconPreview').className = `bi bi-${icon}`;
        }
        
        // Toggle children visibility
        function toggleChildren(id) {
            const rows = document.querySelectorAll(`tr[data-parent="${id}"]`);
            rows.forEach(row => {
                row.style.display = row.style.display === 'none' ? '' : 'none';
            });
        }
        
        // Generate menu preview
        function generateMenuPreview() {
            const preview = document.getElementById('menuPreview');
            const tree = buildMenuTree();
            preview.innerHTML = renderMenuPreview(tree);
        }
        
        // Build menu tree
        function buildMenuTree() {
            const tree = [];
            const children = {};
            
            menuItems.forEach(item => {
                if (!children[item.parent_id]) {
                    children[item.parent_id] = [];
                }
                children[item.parent_id].push(item);
            });
            
            function buildNode(parentId) {
                const items = children[parentId] || [];
                return items.map(item => ({
                    ...item,
                    children: buildNode(item.id)
                }));
            }
            
            return buildNode(null);
        }
        
        // Render menu preview
        function renderMenuPreview(items, level = 0) {
            let html = '<ul class="list-unstyled">';
            items.forEach(item => {
                if (item.enabled == 1) {
                    const indent = '&nbsp;'.repeat(level * 4);
                    const icon = item.icon ? `bi-${item.icon}` : 'bi-circle';
                    html += `
                        <li class="mb-1">
                            ${indent}<i class="bi ${icon}"></i> 
                            <span class="text-primary">${item.label}</span>
                            ${item.children && item.children.length > 0 ? renderMenuPreview(item.children, level + 1) : ''}
                        </li>
                    `;
                }
            });
            html += '</ul>';
            return html;
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Icon preview update
            document.getElementById('icon').addEventListener('input', updateIconPreview);
            
            // Generate initial preview
            generateMenuPreview();
            
            // Update preview when form is submitted
            document.querySelector('form').addEventListener('submit', function() {
                setTimeout(generateMenuPreview, 100);
            });
        });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
require_once '../partials/layout.php';
?> 