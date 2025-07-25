<?php
// Authentication Status Check Script
// This script shows the current authentication status

echo "=== sLMS Authentication Status ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$login_file = 'modules/login.php';
$backup_file = 'modules/login.php.backup';

// Check if authentication is in full access mode
$login_content = file_get_contents($login_file);
$is_full_access = strpos($login_content, 'Auto-Login System') !== false;

echo "=== Current Status ===\n";
if ($is_full_access) {
    echo "✅ Authentication: FULL ACCESS MODE\n";
    echo "📝 Status: All users can access the system without login\n";
    echo "🔓 Mode: Auto-login as admin for all visitors\n";
} else {
    echo "🔒 Authentication: NORMAL MODE\n";
    echo "📝 Status: Users must log in with credentials\n";
}

echo "\n=== File Status ===\n";
echo "Login file: " . (file_exists($login_file) ? "✅ Exists" : "❌ Missing") . "\n";
echo "Backup file: " . (file_exists($backup_file) ? "✅ Exists" : "❌ Missing") . "\n";

echo "\n=== Access URLs ===\n";
echo "Main URL: http://10.0.222.223/\n";
echo "Admin Panel: http://10.0.222.223/admin_menu.php\n";
echo "Clients: http://10.0.222.223/modules/clients.php\n";
echo "Devices: http://10.0.222.223/modules/devices.php\n";

if ($is_full_access) {
    echo "\n=== Full Access Mode Active ===\n";
    echo "✅ All pages accessible without login\n";
    echo "✅ Auto-login as admin for all visitors\n";
    echo "✅ No authentication barriers\n";
    echo "✅ Full system functionality available\n";
} else {
    echo "\n=== Normal Authentication Mode ===\n";
    echo "🔒 Login required for access\n";
    echo "🔒 Credentials needed\n";
}

echo "\n=== User Database Status ===\n";
try {
    require_once 'config.php';
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $active_users = $stmt->fetch()['count'];
    echo "Active users in database: $active_users\n";
    
    // Show current session info
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['username'])) {
        echo "Current session user: " . $_SESSION['username'] . " (role: " . ($_SESSION['role'] ?? 'unknown') . ")\n";
    }
} catch (Exception $e) {
    echo "❌ Cannot check database: " . $e->getMessage() . "\n";
}

echo "\n=== Script completed ===\n";
?> 