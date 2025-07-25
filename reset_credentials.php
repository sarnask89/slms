<?php
// Reset Credentials Script for sLMS
// This script will reset the admin user password to a secure default

require_once 'config.php';

try {
    $pdo = get_pdo();
    
    echo "=== sLMS Credentials Reset ===\n";
    echo "Time: " . date('Y-m-d H:i:s') . "\n\n";
    
    // New admin credentials
    $admin_username = 'admin';
    $admin_password = 'admin123';
    $admin_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE username = ?");
    $stmt->execute([$admin_username]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Update admin password
        $update_stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE username = ?");
        $result = $update_stmt->execute([$admin_password_hash, $admin_username]);
        
        if ($result) {
            echo "✅ SUCCESS: Admin credentials reset successfully!\n";
            echo "Username: $admin_username\n";
            echo "Password: $admin_password\n";
            echo "Role: " . $user['role'] . "\n";
        } else {
            echo "❌ ERROR: Failed to update admin password\n";
        }
    } else {
        // Create admin user if it doesn't exist
        $insert_stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, role, first_name, last_name, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $result = $insert_stmt->execute([
            $admin_username,
            $admin_password_hash,
            'admin@slms.local',
            'admin',
            'System',
            'Administrator',
            1
        ]);
        
        if ($result) {
            echo "✅ SUCCESS: Admin user created successfully!\n";
            echo "Username: $admin_username\n";
            echo "Password: $admin_password\n";
            echo "Role: admin\n";
        } else {
            echo "❌ ERROR: Failed to create admin user\n";
        }
    }
    
    // Also reset other users if needed
    $other_users = [
        ['username' => 'manager', 'password' => 'manager123', 'role' => 'manager'],
        ['username' => 'user', 'password' => 'user123', 'role' => 'user']
    ];
    
    echo "\n=== Resetting Other Users ===\n";
    
    foreach ($other_users as $user_data) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user_data['username']]);
        $existing_user = $stmt->fetch();
        
        if ($existing_user) {
            $password_hash = password_hash($user_data['password'], PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE username = ?");
            $result = $update_stmt->execute([$password_hash, $user_data['username']]);
            
            if ($result) {
                echo "✅ {$user_data['username']}: Password reset to {$user_data['password']}\n";
            } else {
                echo "❌ {$user_data['username']}: Failed to reset password\n";
            }
        } else {
            echo "⚠️ {$user_data['username']}: User not found, skipping\n";
        }
    }
    
    echo "\n=== Final User List ===\n";
    $stmt = $pdo->query("SELECT username, role, is_active FROM users ORDER BY username");
    while ($row = $stmt->fetch()) {
        $status = $row['is_active'] ? 'active' : 'inactive';
        echo "{$row['username']} - {$row['role']} ($status)\n";
    }
    
    echo "\n=== Access Information ===\n";
    echo "Main URL: http://10.0.222.223/\n";
    echo "Login URL: http://10.0.222.223/modules/login.php\n";
    echo "Admin Panel: http://10.0.222.223/admin_menu.php\n";
    
    echo "\n=== Default Credentials ===\n";
    echo "Admin: admin / admin123\n";
    echo "Manager: manager / manager123\n";
    echo "User: user / user123\n";
    
    echo "\n✅ Credentials reset completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?> 