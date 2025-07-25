<?php
/**
 * sLMS Apache Setup Script
 * This script configures Apache to run sLMS properly
 */

echo "=== sLMS Apache Setup Script ===\n\n";

// Check if running as root
if (posix_getuid() !== 0) {
    echo "❌ This script must be run as root (use sudo)\n";
    exit(1);
}

echo "✅ Running as root\n";

// Test PHP configuration
echo "\n=== Testing PHP Configuration ===\n";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'snmp'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext extension loaded\n";
    } else {
        echo "❌ $ext extension missing\n";
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "\n❌ Missing required PHP extensions: " . implode(', ', $missing_extensions) . "\n";
    echo "Please install them with: sudo apt-get install php-" . implode(' php-', $missing_extensions) . "\n";
    exit(1);
}

// Test database connection
echo "\n=== Testing Database Connection ===\n";
try {
    require_once __DIR__ . '/config.php';
    $pdo = get_pdo();
    echo "✅ Database connection successful\n";
    
    // Test basic query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Database query successful (users: {$result['count']})\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Create Apache configuration
echo "\n=== Creating Apache Configuration ===\n";

$apache_config = '<VirtualHost *:80>
    ServerName slms.local
    ServerAlias 10.0.222.223
    DocumentRoot /var/www/html/slms
    
    <Directory /var/www/html/slms>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
        
        # PHP configuration
        <FilesMatch \.php$>
            SetHandler application/x-httpd-php
        </FilesMatch>
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/slms_error.log
    CustomLog ${APACHE_LOG_DIR}/slms_access.log combined
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # PHP settings
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value memory_limit 256M
    php_value session.gc_maxlifetime 3600
    php_value session.cookie_lifetime 3600
</VirtualHost>';

file_put_contents('/etc/apache2/sites-available/slms.conf', $apache_config);
echo "✅ Apache configuration created\n";

// Create .htaccess file
echo "\n=== Creating .htaccess File ===\n";

$htaccess = '# sLMS Apache Configuration
# Security and URL Rewriting Rules

# Enable rewrite engine
RewriteEngine On

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Block access to sensitive files
<FilesMatch "\.(env|log|sql|bak|backup|old|tmp|temp|cache)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Block access to configuration files
<FilesMatch "^(config\.php|\.env|\.htaccess|\.gitignore)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Block access to helper files directly
<FilesMatch "^(database_helper|auth_helper|layout_helper|column_helper)\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>

# PHP settings
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value memory_limit 256M
    php_value session.gc_maxlifetime 3600
    php_value session.cookie_lifetime 3600
</IfModule>

# Default index
DirectoryIndex index.php

# Error pages
ErrorDocument 404 /modules/error_404.php
ErrorDocument 403 /modules/error_403.php
ErrorDocument 500 /modules/error_500.php';

file_put_contents(__DIR__ . '/.htaccess', $htaccess);
echo "✅ .htaccess file created\n";

// Enable Apache modules
echo "\n=== Enabling Apache Modules ===\n";
$modules = ['rewrite', 'headers'];
foreach ($modules as $module) {
    exec("a2enmod $module", $output, $return);
    if ($return === 0) {
        echo "✅ Module $module enabled\n";
    } else {
        echo "❌ Failed to enable module $module\n";
    }
}

// Enable sLMS site
echo "\n=== Enabling sLMS Site ===\n";
exec("a2ensite slms.conf", $output, $return);
if ($return === 0) {
    echo "✅ sLMS site enabled\n";
} else {
    echo "❌ Failed to enable sLMS site\n";
}

// Disable default site
echo "\n=== Disabling Default Site ===\n";
exec("a2dissite 000-default.conf", $output, $return);
if ($return === 0) {
    echo "✅ Default site disabled\n";
} else {
    echo "⚠️ Could not disable default site (may already be disabled)\n";
}

// Test Apache configuration
echo "\n=== Testing Apache Configuration ===\n";
exec("apache2ctl configtest", $output, $return);
if ($return === 0) {
    echo "✅ Apache configuration is valid\n";
} else {
    echo "❌ Apache configuration has errors:\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
    exit(1);
}

// Restart Apache
echo "\n=== Restarting Apache ===\n";
exec("systemctl restart apache2", $output, $return);
if ($return === 0) {
    echo "✅ Apache restarted successfully\n";
} else {
    echo "❌ Failed to restart Apache\n";
    exit(1);
}

// Wait a moment for Apache to start
sleep(2);

// Test the system
echo "\n=== Testing sLMS System ===\n";

// Test if Apache is responding
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'method' => 'GET',
        'header' => "Host: 10.0.222.223\r\n"
    ]
]);

$response = @file_get_contents('http://10.0.222.223/', false, $context);
if ($response !== false) {
    echo "✅ sLMS system is responding\n";
} else {
    echo "⚠️ sLMS system may not be responding (this is normal if no index.php exists)\n";
}

// Test with curl
exec("curl -s -o /dev/null -w '%{http_code}' http://10.0.222.223/", $output, $return);
$http_code = implode('', $output);
if ($http_code == '200' || $http_code == '302' || $http_code == '404') {
    echo "✅ HTTP response code: $http_code\n";
} else {
    echo "⚠️ HTTP response code: $http_code (may indicate configuration issue)\n";
}

echo "\n=== Setup Complete ===\n";
echo "✅ sLMS Apache setup completed successfully!\n\n";

echo "=== Access Information ===\n";
echo "🌐 Main URL: http://10.0.222.223/\n";
echo "🔧 Admin Panel: http://10.0.222.223/admin_menu.php\n";
echo "👤 Login: http://10.0.222.223/modules/login.php\n\n";

echo "=== Default Credentials ===\n";
echo "Username: admin\n";
echo "Password: admin123\n\n";

echo "=== Next Steps ===\n";
echo "1. Access the system at http://10.0.222.223/\n";
echo "2. Login with admin/admin123\n";
echo "3. Configure your system settings\n";
echo "4. Add your first clients and devices\n\n";

echo "=== Troubleshooting ===\n";
echo "If you encounter issues:\n";
echo "- Check Apache logs: sudo tail -f /var/log/apache2/slms_error.log\n";
echo "- Check Apache status: sudo systemctl status apache2\n";
echo "- Test PHP: php -v\n";
echo "- Test database: php -r \"require 'config.php'; get_pdo(); echo 'DB OK';\n\n";

echo "🎉 sLMS is now running on Apache!\n";
?> 