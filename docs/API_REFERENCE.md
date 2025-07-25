# sLMS System API Reference

## Overview

This document provides technical reference for developers working with the sLMS (LAN Management System) API. It covers database functions, layout management, and system integration.

## Database API

### Connection Management

#### `get_pdo()`
Returns a PDO database connection with configured settings.

```php
// Usage
$pdo = get_pdo();

// Returns: PDO instance
```

**Configuration:**
```php
$db_host = 'localhost';
$db_name = 'slmsdb';
$db_user = 'slms';
$db_pass = 'slms123';
$db_charset = 'utf8mb4';
```

**PDO Options:**
- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
- `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`
- `PDO::ATTR_EMULATE_PREPARES => false`

### URL Management

#### `base_url($path = '')`
Generates base URL for the application, handling different deployment scenarios.

```php
// Usage
$url = base_url('modules/clients.php');

// Returns: '/path/to/app/modules/clients.php'
```

**Parameters:**
- `$path` (string): Relative path to append to base URL

**Features:**
- Automatic root directory detection
- Handles subdirectory deployments
- Removes trailing slashes
- Fallback for command line execution

## Layout Management API

### Layout Settings

#### `get_layout_settings($pdo)`
Retrieves current layout configuration from database.

```php
// Usage
$settings = get_layout_settings($pdo);

// Returns: Array with layout configuration
```

**Return Structure:**
```php
[
    'menu_position' => 'left|top|both',
    'show_logo' => true|false,
    'primary_color' => '#007bff',
    'secondary_color' => '#6c757d',
    'font_family' => 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif',
    'font_size' => '14px',
    'custom_css' => '/* custom styles */'
]
```

#### `generate_layout_css($settings)`
Generates CSS based on layout settings.

```php
// Usage
$css = generate_layout_css($settings);

// Returns: CSS string
```

**Generated CSS Variables:**
```css
:root {
    --slms-primary: #007bff;
    --slms-secondary: #6c757d;
    --slms-success: #28a745;
    --slms-danger: #dc3545;
    --slms-warning: #ffc107;
    --slms-info: #17a2b8;
    --slms-light: #f8f9fa;
    --slms-dark: #343a40;
    --slms-background: #ffffff;
    --slms-text: #212529;
    --slms-border: #dee2e6;
    --slms-font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    --slms-font-size: 14px;
}
```

### Menu Management

#### `get_menu_items_from_database()`
Retrieves menu structure from database.

```php
// Usage
$menu_items = get_menu_items_from_database();

// Returns: Array of menu items
```

**Menu Item Structure:**
```php
[
    [
        'id' => 1,
        'label' => 'Panel główny',
        'url' => 'index.php',
        'icon' => 'bi-house',
        'order' => 1,
        'parent_id' => null,
        'active' => true
    ],
    // ... more items
]
```

#### `get_footer_text($pdo)`
Retrieves footer text from database.

```php
// Usage
$footer_text = get_footer_text($pdo);

// Returns: String with footer content
```

## Frame Layout API

### Frame Communication

The frame-based layout system uses `postMessage` API for inter-frame communication.

#### Navigation Messages

```javascript
// Send navigation request
window.parent.postMessage({
    type: 'navigate',
    url: 'modules/clients.php',
    frame: 'content'
}, '*');

// Receive navigation request
window.addEventListener('message', function(event) {
    if (event.data.type === 'navigate') {
        navigateTo(event.data.url, event.data.frame);
    }
});
```

#### Message Types

**Navigation:**
```javascript
{
    type: 'navigate',
    url: 'modules/clients.php',
    frame: 'content'
}
```

**Refresh:**
```javascript
{
    type: 'refresh'
}
```

**Error:**
```javascript
{
    type: 'error',
    message: 'Error description',
    code: 404
}
```

### Content Loading

#### AJAX Content Loading

```javascript
function loadContent(url) {
    fetch(url)
        .then(response => response.text())
        .then(html => {
            // Extract main content
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const content = doc.querySelector('main') || doc.querySelector('.container');
            
            // Insert content
            contentArea.innerHTML = content.innerHTML;
            
            // Reinitialize components
            initializeBootstrapComponents();
        })
        .catch(error => {
            contentArea.innerHTML = `
                <div class="error-message">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i>
                    <h5>Error Loading Content</h5>
                    <button onclick="loadContent()">Retry</button>
                </div>
            `;
        });
}
```

## Database Schema

### Core Tables

#### `clients`
```sql
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    pesel VARCHAR(11) UNIQUE,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `devices`
```sql
CREATE TABLE devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('router', 'switch', 'server', 'other') NOT NULL,
    model VARCHAR(100),
    ip_address VARCHAR(15),
    mac_address VARCHAR(17),
    location TEXT,
    client_id INT,
    status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
);
```

#### `networks`
```sql
CREATE TABLE networks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    network_address VARCHAR(18) NOT NULL,
    gateway VARCHAR(15),
    dns_servers TEXT,
    dhcp_range_start VARCHAR(15),
    dhcp_range_end VARCHAR(15),
    vlan_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `services`
```sql
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('internet', 'tv', 'phone', 'other') NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    speed_download INT,
    speed_upload INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `invoices`
```sql
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('draft', 'sent', 'paid', 'overdue') DEFAULT 'draft',
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

#### `payments`
```sql
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'transfer', 'card', 'other') NOT NULL,
    reference_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);
```

### Configuration Tables

#### `layout_settings`
```sql
CREATE TABLE layout_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'boolean', 'integer', 'json') DEFAULT 'string',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `menu_items`
```sql
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    label VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(50),
    order_position INT DEFAULT 0,
    parent_id INT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE
);
```

#### `column_config`
```sql
CREATE TABLE column_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    table_name VARCHAR(50) NOT NULL,
    column_name VARCHAR(50) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    visible BOOLEAN DEFAULT TRUE,
    order_position INT DEFAULT 0,
    width VARCHAR(20),
    sortable BOOLEAN DEFAULT TRUE,
    filterable BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_table_column (table_name, column_name)
);
```

## Error Handling

### Database Errors

```php
try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    return ['error' => 'Database connection failed'];
}
```

### AJAX Error Handling

```javascript
function handleAjaxError(xhr, status, error) {
    console.error('AJAX Error:', status, error);
    
    let errorMessage = 'An error occurred while processing your request.';
    
    if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
    }
    
    showNotification(errorMessage, 'error');
}
```

## Security

### Input Validation

```php
function validateInput($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        if (!isset($data[$field]) || empty($data[$field])) {
            if (strpos($rule, 'required') !== false) {
                $errors[$field] = "Field $field is required.";
            }
            continue;
        }
        
        $value = $data[$field];
        
        if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = "Invalid email format.";
        }
        
        if (strpos($rule, 'min:') !== false) {
            preg_match('/min:(\d+)/', $rule, $matches);
            $min = $matches[1];
            if (strlen($value) < $min) {
                $errors[$field] = "Field $field must be at least $min characters.";
            }
        }
    }
    
    return $errors;
}
```

### SQL Injection Prevention

```php
// Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM clients WHERE last_name LIKE ?");
$stmt->execute(['%' . $search . '%']);

// Never use direct variable interpolation
// WRONG: $query = "SELECT * FROM clients WHERE last_name = '$last_name'";
```

### XSS Prevention

```php
// Always escape output
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// For JSON output
header('Content-Type: application/json');
echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
```

## Performance Optimization

### Database Optimization

```php
// Use indexes for frequently queried columns
CREATE INDEX idx_clients_last_name ON clients(last_name);
CREATE INDEX idx_devices_status ON devices(status);
CREATE INDEX idx_invoices_status ON invoices(status);

// Use LIMIT for large result sets
$stmt = $pdo->prepare("SELECT * FROM clients LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
```

### Caching

```php
// Simple caching for layout settings
function get_layout_settings_cached($pdo) {
    $cache_file = 'cache/layout_settings.json';
    
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 300) {
        return json_decode(file_get_contents($cache_file), true);
    }
    
    $settings = get_layout_settings($pdo);
    file_put_contents($cache_file, json_encode($settings));
    
    return $settings;
}
```

## Integration Examples

### Adding New Module

```php
<?php
// modules/new_module.php
require_once __DIR__ . '/../config.php';

$pageTitle = 'New Module';
ob_start();
?>

<div class="container">
    <h1>New Module</h1>
    <!-- Module content -->
</div>

<?php
$content = ob_get_clean();
require_once '../partials/layout.php';
?>
```

### Custom API Endpoint

```php
<?php
// api/clients.php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $pdo = get_pdo();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $stmt = $pdo->query("SELECT * FROM clients ORDER BY last_name");
            $clients = $stmt->fetchAll();
            echo json_encode($clients);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO clients (first_name, last_name, email) VALUES (?, ?, ?)");
            $stmt->execute([$data['first_name'], $data['last_name'], $data['email']]);
            echo json_encode(['id' => $pdo->lastInsertId()]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
```

## Testing

### Unit Testing Example

```php
<?php
// tests/DatabaseTest.php
class DatabaseTest extends PHPUnit\Framework\TestCase {
    public function testGetPdo() {
        $pdo = get_pdo();
        $this->assertInstanceOf(PDO::class, $pdo);
    }
    
    public function testBaseUrl() {
        $url = base_url('modules/clients.php');
        $this->assertStringContainsString('modules/clients.php', $url);
    }
}
?>
```

## Deployment

### Production Configuration

```php
// config.php (production)
error_reporting(0);
ini_set('display_errors', 0);

// Enable caching
ini_set('opcache.enable', 1);
ini_set('opcache.memory_consumption', 128);
```

### Environment Variables

```bash
# .env
DB_HOST=localhost
DB_NAME=slmsdb
DB_USER=slms
DB_PASS=secure_password
DB_CHARSET=utf8mb4
```

---

**Version**: 1.0  
**Last Updated**: 2024  
**Author**: sLMS Development Team 