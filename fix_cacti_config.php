<?php
/**
 * Cacti Configuration Fix Script
 * Helps fix common Cacti pre-installation issues
 */

echo "🔧 Cacti Configuration Fix Script\n";
echo "================================\n\n";

// Check current PHP settings
echo "📊 Current PHP Settings:\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "Timezone: " . ini_get('date.timezone') . "\n\n";

// Check MySQL connection
echo "🔍 Checking MySQL Connection...\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=cacti', 'cactiuser', 'cactipassword');
    echo "✅ MySQL connection successful\n";
    
    // Check timezone access
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM mysql.time_zone_name LIMIT 1");
    if ($stmt) {
        echo "✅ Timezone database access OK\n";
    } else {
        echo "❌ Timezone database access failed\n";
    }
    
} catch (PDOException $e) {
    echo "❌ MySQL connection failed: " . $e->getMessage() . "\n";
}

echo "\n📋 Required Manual Steps:\n";
echo "========================\n\n";

echo "1. PHP Configuration (/etc/php/8.2/apache2/php.ini):\n";
echo "   - Set memory_limit = 800M\n";
echo "   - Set max_execution_time = 60\n\n";

echo "2. MySQL TimeZone Access:\n";
echo "   mysql -u root -p\n";
echo "   USE mysql;\n";
echo "   GRANT SELECT ON time_zone_name TO 'cactiuser'@'localhost';\n";
echo "   FLUSH PRIVILEGES;\n";
echo "   mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p mysql\n\n";

echo "3. MySQL Performance Settings (/etc/mysql/mariadb.conf.d/50-server.cnf):\n";
echo "   - character-set-server = utf8mb4\n";
echo "   - collation-server = utf8mb4_unicode_ci\n";
echo "   - max_connections = 200\n";
echo "   - max_heap_table_size = 256M\n";
echo "   - tmp_table_size = 256M\n";
echo "   - innodb_buffer_pool_size = 512M\n\n";

echo "4. Restart Services:\n";
echo "   sudo systemctl restart apache2\n";
echo "   sudo systemctl restart mariadb\n\n";

echo "5. Test Cacti Installation:\n";
echo "   http://localhost/cacti/install/\n\n";

echo "🎯 After completing these steps, run the Cacti pre-installation check again.\n";
?> 