<?php
// Restore Normal Authentication Script
// This script will restore normal login functionality

echo "=== sLMS Restore Normal Authentication ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$backup_file = 'modules/login.php.backup';
$login_file = 'modules/login.php';

if (file_exists($backup_file)) {
    // Restore the original login file
    if (copy($backup_file, $login_file)) {
        echo "✅ SUCCESS: Normal authentication restored!\n";
        echo "Original login functionality restored.\n\n";
        
        echo "=== Access Information ===\n";
        echo "Main URL: http://10.0.222.223/\n";
        echo "Login URL: http://10.0.222.223/modules/login.php\n";
        echo "Admin Panel: http://10.0.222.223/admin_menu.php\n\n";
        
        echo "=== Default Credentials ===\n";
        echo "Admin: admin / admin123\n";
        echo "Manager: manager / manager123\n";
        echo "User: user / user123\n\n";
        
        echo "⚠️ IMPORTANT: Users will now need to log in with credentials.\n";
        echo "✅ Full access mode has been disabled.\n";
        
        // Optionally remove the backup file
        echo "\nDo you want to remove the backup file? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 'y') {
            unlink($backup_file);
            echo "✅ Backup file removed.\n";
        } else {
            echo "ℹ️ Backup file kept at: $backup_file\n";
        }
        
    } else {
        echo "❌ ERROR: Failed to restore login file.\n";
        echo "Please manually copy $backup_file to $login_file\n";
    }
} else {
    echo "❌ ERROR: Backup file not found: $backup_file\n";
    echo "You may need to manually restore the login functionality.\n";
}

echo "\n=== Script completed ===\n";
?> 