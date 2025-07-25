<?php
/**
 * sLMS Default Credentials Setup Script
 * Creates default admin user and sets up initial authentication
 */

echo "================================================\n";
echo "           sLMS Default Credentials Setup\n";
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

// Check if users table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "✗ Users table does not exist. Creating it...\n";
        
        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            role ENUM('admin', 'manager', 'user', 'viewer') DEFAULT 'user',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "✓ Users table created\n";
    } else {
        echo "✓ Users table exists\n";
    }
} catch (Exception $e) {
    echo "✗ Error creating users table: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if user_activity_log table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_activity_log'");
    if ($stmt->rowCount() == 0) {
        echo "Creating user_activity_log table...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS user_activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            details TEXT,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $pdo->exec($sql);
        echo "✓ User activity log table created\n";
    } else {
        echo "✓ User activity log table exists\n";
    }
} catch (Exception $e) {
    echo "✗ Error creating user_activity_log table: " . $e->getMessage() . "\n";
}

// Check if user_permissions table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_permissions'");
    if ($stmt->rowCount() == 0) {
        echo "Creating user_permissions table...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS user_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            module VARCHAR(50) NOT NULL,
            permission ENUM('read', 'write', 'admin') DEFAULT 'read',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_module (user_id, module)
        )";
        
        $pdo->exec($sql);
        echo "✓ User permissions table created\n";
    } else {
        echo "✓ User permissions table exists\n";
    }
} catch (Exception $e) {
    echo "✗ Error creating user_permissions table: " . $e->getMessage() . "\n";
}

// Check if admin user already exists
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "⚠ Admin user already exists\n";
        echo "Do you want to reset the admin password? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) !== 'y') {
            echo "Password reset cancelled\n";
            exit(0);
        }
        
        // Update existing admin password
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, role = 'admin', is_active = TRUE WHERE username = 'admin'");
        $stmt->execute([$hashed_password]);
        echo "✓ Admin password updated\n";
    } else {
        // Create new admin user
        echo "Creating default admin user...\n";
        
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, role, is_active) VALUES (?, ?, ?, 'admin', TRUE)");
        $stmt->execute(['admin', $hashed_password, 'admin@slms.local']);
        
        $admin_id = $pdo->lastInsertId();
        echo "✓ Admin user created with ID: $admin_id\n";
        
        // Add admin permissions for all modules
        $modules = [
            'dashboard', 'clients', 'devices', 'networks', 'services', 
            'users', 'reports', 'settings', 'cacti_integration', 'snmp_monitoring',
            'admin_menu', 'activity_log', 'user_management'
        ];
        
        foreach ($modules as $module) {
            $stmt = $pdo->prepare("INSERT INTO user_permissions (user_id, module, permission) VALUES (?, ?, 'admin')");
            $stmt->execute([$admin_id, $module]);
        }
        echo "✓ Admin permissions set for all modules\n";
    }
} catch (Exception $e) {
    echo "✗ Error creating admin user: " . $e->getMessage() . "\n";
    exit(1);
}

// Create additional default users
$default_users = [
    [
        'username' => 'manager',
        'password' => 'manager123',
        'email' => 'manager@slms.local',
        'role' => 'manager'
    ],
    [
        'username' => 'user',
        'password' => 'user123',
        'email' => 'user@slms.local',
        'role' => 'user'
    ],
    [
        'username' => 'viewer',
        'password' => 'viewer123',
        'email' => 'viewer@slms.local',
        'role' => 'viewer'
    ]
];

foreach ($default_users as $user) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user['username']]);
        
        if ($stmt->rowCount() == 0) {
            $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, role, is_active) VALUES (?, ?, ?, ?, TRUE)");
            $stmt->execute([$user['username'], $hashed_password, $user['email'], $user['role']]);
            
            $user_id = $pdo->lastInsertId();
            echo "✓ User '{$user['username']}' created with ID: $user_id\n";
            
            // Set permissions based on role
            $permission = 'read';
            if ($user['role'] === 'manager') {
                $permission = 'write';
            } elseif ($user['role'] === 'admin') {
                $permission = 'admin';
            }
            
            // Add permissions for basic modules
            $basic_modules = ['dashboard', 'clients', 'devices', 'networks', 'services'];
            foreach ($basic_modules as $module) {
                $stmt = $pdo->prepare("INSERT INTO user_permissions (user_id, module, permission) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $module, $permission]);
            }
        } else {
            echo "⚠ User '{$user['username']}' already exists\n";
        }
    } catch (Exception $e) {
        echo "✗ Error creating user '{$user['username']}': " . $e->getMessage() . "\n";
    }
}

echo "\n================================================\n";
echo "Default Credentials Setup Complete!\n";
echo "================================================\n\n";

echo "🔐 Default Login Credentials:\n\n";

echo "👑 Administrator:\n";
echo "   Username: admin\n";
echo "   Password: admin123\n";
echo "   Role: Full system access\n\n";

echo "👨‍💼 Manager:\n";
echo "   Username: manager\n";
echo "   Password: manager123\n";
echo "   Role: Write access to basic modules\n\n";

echo "👤 User:\n";
echo "   Username: user\n";
echo "   Password: user123\n";
echo "   Role: Read access to basic modules\n\n";

echo "👁️ Viewer:\n";
echo "   Username: viewer\n";
echo "   Password: viewer123\n";
echo "   Role: Read-only access to basic modules\n\n";

echo "🌐 Access URLs:\n";
echo "   sLMS System: http://10.0.222.223:8000\n";
echo "   Cacti Integration: http://10.0.222.223:8000/modules/cacti_integration.php\n";
echo "   Admin Menu: http://10.0.222.223:8000/admin_menu.php\n\n";

echo "⚠️  SECURITY WARNING:\n";
echo "   - Change default passwords immediately after first login\n";
echo "   - Delete or disable unused accounts\n";
echo "   - Use strong passwords in production\n\n";

echo "✅ Setup completed successfully!\n";
echo "You can now log in to your sLMS system.\n";
?> 