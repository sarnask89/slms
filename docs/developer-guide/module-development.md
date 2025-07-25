# 🛠️ Module Development Guide

## Overview

This guide provides comprehensive information for developers on how to create, extend, and maintain modules for the sLMS system.

## 🏗️ Module Architecture

### Module Structure

Each sLMS module follows a consistent structure:

```
modules/
├── your_module.php          # Main module file
├── your_module/
│   ├── config.php           # Module configuration
│   ├── helpers/             # Helper functions
│   │   ├── database.php     # Database operations
│   │   ├── validation.php   # Input validation
│   │   └── utils.php        # Utility functions
│   ├── templates/           # HTML templates
│   │   ├── list.php         # List view template
│   │   ├── form.php         # Form template
│   │   └── detail.php       # Detail view template
│   └── assets/              # Module-specific assets
│       ├── css/
│       ├── js/
│       └── images/
```

### Basic Module Template

```php
<?php
/**
 * Your Module Name
 * 
 * Description of what this module does
 * 
 * @package sLMS
 * @subpackage Modules
 * @author Your Name
 * @version 1.0.0
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require appropriate permissions
require_login();
require_access_permission('your_section', 'read');

$pdo = get_pdo();
$pageTitle = 'Your Module Title';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Handle add operation
                break;
            case 'edit':
                // Handle edit operation
                break;
            case 'delete':
                // Handle delete operation
                break;
        }
    }
}

// Get data for display
$data = []; // Your data retrieval logic here

// Start output buffering
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
    <link href="/assets/tooltip-system.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">
                        <i class="bi bi-gear"></i> <?php echo $pageTitle; ?>
                    </h1>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus"></i> Add New
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="exportData()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Main content area -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Data List</h5>
                    </div>
                    <div class="card-body">
                        <!-- Your content here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <!-- Your form fields here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/multiselect.js"></script>
    <script src="/assets/tooltip-system.js"></script>
    
    <script>
    // Your JavaScript code here
    
    function exportData() {
        // Export functionality
    }
    
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        if (window.slmsTooltips) {
            // Add tooltips to your elements
            const elements = document.querySelectorAll('[data-tooltip-id]');
            elements.forEach(element => {
                const tooltipId = element.getAttribute('data-tooltip-id');
                window.slmsTooltips.addTooltip(element, tooltipId);
            });
        }
    });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?>
```

## 🗄️ Database Integration

### Database Helper Functions

Create a database helper for your module:

```php
<?php
// modules/your_module/helpers/database.php

/**
 * Database helper functions for Your Module
 */

/**
 * Get all items with pagination
 */
function get_items($pdo, $page = 1, $limit = 20, $search = '') {
    $offset = ($page - 1) * $limit;
    
    $where_clause = '';
    $params = [];
    
    if (!empty($search)) {
        $where_clause = 'WHERE name LIKE ? OR description LIKE ?';
        $params = ["%$search%", "%$search%"];
    }
    
    $sql = "SELECT * FROM your_table $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get total count for pagination
 */
function get_items_count($pdo, $search = '') {
    $where_clause = '';
    $params = [];
    
    if (!empty($search)) {
        $where_clause = 'WHERE name LIKE ? OR description LIKE ?';
        $params = ["%$search%", "%$search%"];
    }
    
    $sql = "SELECT COUNT(*) FROM your_table $where_clause";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchColumn();
}

/**
 * Get single item by ID
 */
function get_item($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM your_table WHERE id = ?");
    $stmt->execute([$id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Add new item
 */
function add_item($pdo, $data) {
    $stmt = $pdo->prepare("
        INSERT INTO your_table (name, description, status, created_at, updated_at) 
        VALUES (?, ?, ?, NOW(), NOW())
    ");
    
    return $stmt->execute([
        $data['name'],
        $data['description'],
        $data['status'] ?? 'active'
    ]);
}

/**
 * Update item
 */
function update_item($pdo, $id, $data) {
    $stmt = $pdo->prepare("
        UPDATE your_table 
        SET name = ?, description = ?, status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    
    return $stmt->execute([
        $data['name'],
        $data['description'],
        $data['status'],
        $id
    ]);
}

/**
 * Delete item
 */
function delete_item($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM your_table WHERE id = ?");
    return $stmt->execute([$id]);
}
```

### Database Schema

Create your database table:

```sql
-- Create table for your module
CREATE TABLE your_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_name (name),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 🔐 Authentication and Authorization

### Permission System Integration

```php
<?php
// Check permissions before operations
require_access_permission('your_section', 'read');

// For write operations
if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
    require_access_permission('your_section', 'write');
}

// For delete operations
if ($_POST['action'] === 'delete') {
    require_access_permission('your_section', 'delete');
}

// Get current user info
$current_user = get_current_user_info();
```

### Access Level Configuration

Add your module to the access level system:

```php
// In setup_access_level_tables.php or similar
$sections = [
    'your_section' => [
        'name' => 'Your Module',
        'description' => 'Manage your module data',
        'actions' => [
            'read' => 'View items',
            'write' => 'Add/Edit items',
            'delete' => 'Delete items',
            'export' => 'Export data'
        ]
    ]
];
```

## 🎨 User Interface Development

### Bootstrap Integration

```php
<!-- Responsive table -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['id']); ?></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $item['status'] === 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($item['created_at'])); ?></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="editItem(<?php echo $item['id']; ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteItem(<?php echo $item['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
```

### Form Validation

```php
<?php
// modules/your_module/helpers/validation.php

/**
 * Validate form data
 */
function validate_item_data($data) {
    $errors = [];
    
    // Validate name
    if (empty($data['name'])) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($data['name']) > 255) {
        $errors['name'] = 'Name must be less than 255 characters';
    }
    
    // Validate description
    if (!empty($data['description']) && strlen($data['description']) > 1000) {
        $errors['description'] = 'Description must be less than 1000 characters';
    }
    
    // Validate status
    $allowed_statuses = ['active', 'inactive', 'pending'];
    if (!empty($data['status']) && !in_array($data['status'], $allowed_statuses)) {
        $errors['status'] = 'Invalid status value';
    }
    
    return $errors;
}

/**
 * Sanitize input data
 */
function sanitize_item_data($data) {
    return [
        'name' => trim(strip_tags($data['name'] ?? '')),
        'description' => trim(strip_tags($data['description'] ?? '')),
        'status' => $data['status'] ?? 'active'
    ];
}
```

### JavaScript Integration

```javascript
// Module-specific JavaScript
class YourModule {
    constructor() {
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeTooltips();
    }
    
    bindEvents() {
        // Form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
        });
        
        // Delete buttons
        document.querySelectorAll('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', this.handleDelete.bind(this));
        });
    }
    
    handleFormSubmit(event) {
        const form = event.target;
        const formData = new FormData(form);
        
        // Validate form
        if (!this.validateForm(formData)) {
            event.preventDefault();
            return false;
        }
    }
    
    validateForm(formData) {
        const name = formData.get('name');
        if (!name || name.trim().length === 0) {
            this.showError('Name is required');
            return false;
        }
        
        return true;
    }
    
    handleDelete(event) {
        const id = event.target.dataset.id;
        if (confirm('Are you sure you want to delete this item?')) {
            this.deleteItem(id);
        }
    }
    
    async deleteItem(id) {
        try {
            const response = await fetch(`/modules/your_module.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&id=${id}`
            });
            
            if (response.ok) {
                location.reload();
            } else {
                this.showError('Failed to delete item');
            }
        } catch (error) {
            this.showError('Network error occurred');
        }
    }
    
    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.card'));
    }
    
    initializeTooltips() {
        if (window.slmsTooltips) {
            // Add tooltips to form fields
            const fields = document.querySelectorAll('input, select, textarea');
            fields.forEach(field => {
                const tooltipId = field.getAttribute('data-tooltip-id');
                if (tooltipId) {
                    window.slmsTooltips.addTooltip(field, tooltipId);
                }
            });
        }
    }
}

// Initialize module
document.addEventListener('DOMContentLoaded', () => {
    new YourModule();
});
```

## 🔧 API Development

### RESTful API Endpoints

```php
<?php
// modules/api/your_module_api.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

// Check authentication
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$pdo = get_pdo();
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Route to appropriate handler
switch ($method) {
    case 'GET':
        if (isset($path_parts[3])) {
            get_item_api($pdo, $path_parts[3]);
        } else {
            get_items_api($pdo);
        }
        break;
        
    case 'POST':
        create_item_api($pdo);
        break;
        
    case 'PUT':
        if (isset($path_parts[3])) {
            update_item_api($pdo, $path_parts[3]);
        }
        break;
        
    case 'DELETE':
        if (isset($path_parts[3])) {
            delete_item_api($pdo, $path_parts[3]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function get_items_api($pdo) {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 20;
    $search = $_GET['search'] ?? '';
    
    $items = get_items($pdo, $page, $limit, $search);
    $total = get_items_count($pdo, $search);
    
    echo json_encode([
        'data' => $items,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function get_item_api($pdo, $id) {
    $item = get_item($pdo, $id);
    
    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found']);
        return;
    }
    
    echo json_encode(['data' => $item]);
}

function create_item_api($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $data = sanitize_item_data($input);
    $errors = validate_item_data($data);
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['errors' => $errors]);
        return;
    }
    
    if (add_item($pdo, $data)) {
        $id = $pdo->lastInsertId();
        $item = get_item($pdo, $id);
        
        http_response_code(201);
        echo json_encode(['data' => $item]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create item']);
    }
}

function update_item_api($pdo, $id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $data = sanitize_item_data($input);
    $errors = validate_item_data($data);
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['errors' => $errors]);
        return;
    }
    
    if (update_item($pdo, $id, $data)) {
        $item = get_item($pdo, $id);
        echo json_encode(['data' => $item]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update item']);
    }
}

function delete_item_api($pdo, $id) {
    if (delete_item($pdo, $id)) {
        http_response_code(204);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete item']);
    }
}
```

## 📊 Reporting and Analytics

### Data Export

```php
<?php
// Export functionality
function export_items($pdo, $format = 'csv') {
    $items = get_items($pdo, 1, 10000); // Get all items
    
    switch ($format) {
        case 'csv':
            export_csv($items);
            break;
        case 'json':
            export_json($items);
            break;
        case 'xml':
            export_xml($items);
            break;
        default:
            throw new Exception('Unsupported export format');
    }
}

function export_csv($items) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="items_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Headers
    fputcsv($output, ['ID', 'Name', 'Description', 'Status', 'Created']);
    
    // Data
    foreach ($items as $item) {
        fputcsv($output, [
            $item['id'],
            $item['name'],
            $item['description'],
            $item['status'],
            $item['created_at']
        ]);
    }
    
    fclose($output);
}
```

### Analytics Integration

```php
<?php
// Analytics helper
function get_analytics($pdo) {
    $stats = [];
    
    // Total items
    $stmt = $pdo->query("SELECT COUNT(*) FROM your_table");
    $stats['total_items'] = $stmt->fetchColumn();
    
    // Items by status
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM your_table 
        GROUP BY status
    ");
    $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Items created this month
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM your_table 
        WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
    ");
    $stats['this_month'] = $stmt->fetchColumn();
    
    // Growth trend
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM your_table 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stats['growth_trend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $stats;
}
```

## 🧪 Testing

### Unit Testing

```php
<?php
// tests/YourModuleTest.php

use PHPUnit\Framework\TestCase;

class YourModuleTest extends TestCase
{
    private $pdo;
    
    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->createTestTable();
    }
    
    private function createTestTable()
    {
        $this->pdo->exec("
            CREATE TABLE your_table (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                status TEXT DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    public function testAddItem()
    {
        $data = [
            'name' => 'Test Item',
            'description' => 'Test Description',
            'status' => 'active'
        ];
        
        $result = add_item($this->pdo, $data);
        $this->assertTrue($result);
        
        $item = get_item($this->pdo, 1);
        $this->assertEquals('Test Item', $item['name']);
    }
    
    public function testValidateItemData()
    {
        $data = ['name' => ''];
        $errors = validate_item_data($data);
        
        $this->assertArrayHasKey('name', $errors);
        $this->assertEquals('Name is required', $errors['name']);
    }
}
```

### Integration Testing

```php
<?php
// tests/Integration/YourModuleIntegrationTest.php

class YourModuleIntegrationTest extends TestCase
{
    public function testApiEndpoints()
    {
        // Test GET /api/your_module
        $response = $this->makeRequest('GET', '/api/your_module');
        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('data', $response['body']);
        
        // Test POST /api/your_module
        $data = ['name' => 'Test Item', 'description' => 'Test'];
        $response = $this->makeRequest('POST', '/api/your_module', $data);
        $this->assertEquals(201, $response['status']);
    }
    
    private function makeRequest($method, $url, $data = null)
    {
        // Implementation for making HTTP requests
    }
}
```

## 📚 Documentation

### Module Documentation

```php
<?php
/**
 * Your Module Documentation
 * 
 * This module provides functionality for managing [describe what it manages].
 * 
 * ## Features
 * - Feature 1: Description
 * - Feature 2: Description
 * - Feature 3: Description
 * 
 * ## Usage
 * 
 * ### Basic Usage
 * ```php
 * // Get all items
 * $items = get_items($pdo);
 * 
 * // Add new item
 * $data = ['name' => 'New Item', 'description' => 'Description'];
 * add_item($pdo, $data);
 * ```
 * 
 * ### API Endpoints
 * - `GET /api/your_module` - Get all items
 * - `GET /api/your_module/{id}` - Get specific item
 * - `POST /api/your_module` - Create new item
 * - `PUT /api/your_module/{id}` - Update item
 * - `DELETE /api/your_module/{id}` - Delete item
 * 
 * ## Configuration
 * 
 * Add to config.php:
 * ```php
 * define('YOUR_MODULE_ENABLED', true);
 * define('YOUR_MODULE_MAX_ITEMS', 1000);
 * ```
 * 
 * ## Dependencies
 * - sLMS Core
 * - Bootstrap 5
 * - jQuery (optional)
 * 
 * @package sLMS
 * @subpackage Modules
 * @version 1.0.0
 */
```

## 🚀 Deployment

### Module Installation

```bash
#!/bin/bash
# install_module.sh

MODULE_NAME="your_module"
MODULE_DIR="/var/www/html/slms/modules/$MODULE_NAME"

# Create module directory
mkdir -p $MODULE_DIR/{helpers,templates,assets/{css,js,images}}

# Copy module files
cp your_module.php /var/www/html/slms/modules/
cp -r your_module/* $MODULE_DIR/

# Set permissions
chown -R www-data:www-data $MODULE_DIR
chmod -R 755 $MODULE_DIR

# Create database table
mysql -u slms_user -p$DB_PASS slms < database/schema.sql

# Add to menu
php /var/www/html/slms/modules/menu_editor.php --add "$MODULE_NAME"

echo "Module $MODULE_NAME installed successfully"
```

### Version Management

```php
<?php
// Version information
define('YOUR_MODULE_VERSION', '1.0.0');
define('YOUR_MODULE_MIN_SLMS_VERSION', '1.0.0');

// Check compatibility
function check_compatibility() {
    if (version_compare(SLMS_VERSION, YOUR_MODULE_MIN_SLMS_VERSION, '<')) {
        throw new Exception('sLMS version ' . YOUR_MODULE_MIN_SLMS_VERSION . ' or higher required');
    }
}

// Update function
function update_module() {
    $current_version = get_option('your_module_version', '0.0.0');
    
    if (version_compare($current_version, '1.0.0', '<')) {
        // Perform update to 1.0.0
        update_to_1_0_0();
    }
    
    update_option('your_module_version', YOUR_MODULE_VERSION);
}

function update_to_1_0_0() {
    global $pdo;
    
    // Add new columns
    $pdo->exec("ALTER TABLE your_table ADD COLUMN new_field VARCHAR(255)");
    
    // Update existing data
    $pdo->exec("UPDATE your_table SET new_field = 'default_value' WHERE new_field IS NULL");
}
```

---

**Last Updated**: July 20, 2025  
**Version**: sLMS v1.0 Module Development Guide  
**Status**: ✅ **Active** 