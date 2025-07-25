<?php
/**
 * sLMS Access Level Tables Setup Script
 * Creates tables for access level management system
 */

echo "================================================\n";
echo "           sLMS Access Level Tables Setup\n";
echo "================================================\n\n";

// Include configuration
require_once 'config.php';

try {
    $pdo = get_pdo();
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Create access_levels table
try {
    echo "Creating access_levels table...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS access_levels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($sql);
    echo "✓ access_levels table created\n";
} catch (Exception $e) {
    echo "✗ Error creating access_levels table: " . $e->getMessage() . "\n";
    exit(1);
}

// Create access_level_permissions table
try {
    echo "Creating access_level_permissions table...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS access_level_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        access_level_id INT NOT NULL,
        section VARCHAR(50) NOT NULL,
        action VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (access_level_id) REFERENCES access_levels(id) ON DELETE CASCADE,
        UNIQUE KEY unique_permission (access_level_id, section, action)
    )";
    
    $pdo->exec($sql);
    echo "✓ access_level_permissions table created\n";
} catch (Exception $e) {
    echo "✗ Error creating access_level_permissions table: " . $e->getMessage() . "\n";
    exit(1);
}

// Add access_level_id column to users table if it doesn't exist
try {
    echo "Checking users table for access_level_id column...\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'access_level_id'");
    if ($stmt->rowCount() == 0) {
        echo "Adding access_level_id column to users table...\n";
        
        $sql = "ALTER TABLE users ADD COLUMN access_level_id INT NULL AFTER role,
                ADD FOREIGN KEY (access_level_id) REFERENCES access_levels(id) ON DELETE SET NULL";
        
        $pdo->exec($sql);
        echo "✓ access_level_id column added to users table\n";
    } else {
        echo "✓ access_level_id column already exists in users table\n";
    }
} catch (Exception $e) {
    echo "✗ Error modifying users table: " . $e->getMessage() . "\n";
    exit(1);
}

// Create default access levels
try {
    echo "Creating default access levels...\n";
    
    $default_levels = [
        [
            'name' => 'Administrator',
            'description' => 'Full system access with all permissions',
            'permissions' => [
                'dashboard' => ['view', 'customize', 'export'],
                'clients' => ['view', 'add', 'edit', 'delete', 'export'],
                'devices' => ['view', 'add', 'edit', 'delete', 'monitor', 'configure'],
                'networks' => ['view', 'add', 'edit', 'delete', 'dhcp'],
                'services' => ['view', 'add', 'edit', 'delete', 'assign'],
                'financial' => ['view', 'add', 'edit', 'delete', 'export'],
                'monitoring' => ['view', 'configure', 'alerts', 'reports'],
                'users' => ['view', 'add', 'edit', 'delete', 'permissions'],
                'system' => ['view', 'configure', 'backup', 'logs', 'maintenance']
            ]
        ],
        [
            'name' => 'Manager',
            'description' => 'Manager access with write permissions to most modules',
            'permissions' => [
                'dashboard' => ['view', 'customize'],
                'clients' => ['view', 'add', 'edit', 'export'],
                'devices' => ['view', 'add', 'edit', 'monitor'],
                'networks' => ['view', 'add', 'edit'],
                'services' => ['view', 'add', 'edit', 'assign'],
                'financial' => ['view', 'add', 'edit', 'export'],
                'monitoring' => ['view', 'alerts', 'reports'],
                'users' => ['view'],
                'system' => ['view']
            ]
        ],
        [
            'name' => 'User',
            'description' => 'Standard user access with read permissions',
            'permissions' => [
                'dashboard' => ['view'],
                'clients' => ['view'],
                'devices' => ['view'],
                'networks' => ['view'],
                'services' => ['view'],
                'financial' => ['view'],
                'monitoring' => ['view'],
                'users' => ['view'],
                'system' => ['view']
            ]
        ],
        [
            'name' => 'Viewer',
            'description' => 'Read-only access to basic modules',
            'permissions' => [
                'dashboard' => ['view'],
                'clients' => ['view'],
                'devices' => ['view'],
                'networks' => ['view'],
                'services' => ['view'],
                'financial' => ['view'],
                'monitoring' => ['view']
            ]
        ]
    ];
    
    foreach ($default_levels as $level) {
        // Check if access level already exists
        $stmt = $pdo->prepare("SELECT id FROM access_levels WHERE name = ?");
        $stmt->execute([$level['name']]);
        
        if ($stmt->rowCount() == 0) {
            // Create access level
            $stmt = $pdo->prepare("INSERT INTO access_levels (name, description, created_by) VALUES (?, ?, ?)");
            $stmt->execute([$level['name'], $level['description'], 1]); // created_by = 1 (admin)
            $access_level_id = $pdo->lastInsertId();
            
            // Add permissions
            foreach ($level['permissions'] as $section => $actions) {
                foreach ($actions as $action) {
                    $stmt = $pdo->prepare("INSERT INTO access_level_permissions (access_level_id, section, action) VALUES (?, ?, ?)");
                    $stmt->execute([$access_level_id, $section, $action]);
                }
            }
            
            echo "✓ Created access level: {$level['name']}\n";
        } else {
            echo "⚠ Access level '{$level['name']}' already exists\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error creating default access levels: " . $e->getMessage() . "\n";
}

// Assign default access levels to existing users based on their roles
try {
    echo "Assigning access levels to existing users...\n";
    
    // Get access level IDs
    $stmt = $pdo->query("SELECT id, name FROM access_levels");
    $access_levels = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Update users based on their roles
    $role_mapping = [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'user' => 'User',
        'viewer' => 'Viewer'
    ];
    
    foreach ($role_mapping as $role => $level_name) {
        if (isset($access_levels[$level_name])) {
            $stmt = $pdo->prepare("UPDATE users SET access_level_id = ? WHERE role = ? AND access_level_id IS NULL");
            $stmt->execute([$access_levels[$level_name], $role]);
            $affected = $stmt->rowCount();
            if ($affected > 0) {
                echo "✓ Assigned '{$level_name}' level to {$affected} users with role '{$role}'\n";
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Error assigning access levels: " . $e->getMessage() . "\n";
}

echo "\n================================================\n";
echo "Access Level Tables Setup Complete!\n";
echo "================================================\n\n";

echo "📋 Created Tables:\n";
echo "   - access_levels: Stores access level definitions\n";
echo "   - access_level_permissions: Stores permissions for each access level\n";
echo "   - Modified users table: Added access_level_id column\n\n";

echo "🔐 Default Access Levels:\n";
echo "   - Administrator: Full system access\n";
echo "   - Manager: Write access to most modules\n";
echo "   - User: Read access to all modules\n";
echo "   - Viewer: Read-only access to basic modules\n\n";

echo "🌐 Access URLs:\n";
echo "   Access Level Manager: http://10.0.222.223:8000/modules/access_level_manager.php\n";
echo "   User Management: http://10.0.222.223:8000/modules/user_management.php\n\n";

echo "✅ Setup completed successfully!\n";
echo "You can now manage access levels through the Access Level Manager.\n";
?> 