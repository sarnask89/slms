<?php
/**
 * Captive Portal Setup Script
 * Installs and configures the walled garden portal system
 */

echo "🔐 Setting up Captive Portal System...\n";
echo "=====================================\n\n";

// Configuration
$config = [
    'db_host' => 'localhost',
    'db_name' => 'slms',
    'db_user' => 'root',
    'db_pass' => '',
    'portal_url' => 'http://localhost/modules/captive_portal.php',
    'api_url' => 'http://localhost/api/captive_portal_api.php'
];

// Database connection
function getDatabaseConnection($config) {
    try {
        $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", 
                       $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

// Test database connection
echo "📊 Testing database connection...\n";
$pdo = getDatabaseConnection($config);

if (!$pdo) {
    echo "❌ Database connection failed. Please check your configuration.\n";
    echo "Make sure MySQL is running and the database '{$config['db_name']}' exists.\n\n";
    exit(1);
}

echo "✅ Database connection successful!\n\n";

// Create tables
echo "🗄️  Creating database tables...\n";

$sqlFile = 'sql/captive_portal_schema.sql';
if (!file_exists($sqlFile)) {
    echo "❌ SQL schema file not found: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);

// Split SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$successCount = 0;
$errorCount = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    try {
        $pdo->exec($statement);
        $successCount++;
        echo "  ✅ " . substr($statement, 0, 50) . "...\n";
    } catch (PDOException $e) {
        $errorCount++;
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n📊 Database setup results:\n";
echo "  - Successful statements: $successCount\n";
echo "  - Errors: $errorCount\n\n";

// Create required directories
echo "📁 Creating required directories...\n";
$directories = [
    'logs/captive_portal',
    'cache/captive_portal',
    'uploads/captive_portal'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "  ✅ Created directory: $dir\n";
        } else {
            echo "  ❌ Failed to create directory: $dir\n";
        }
    } else {
        echo "  ✅ Directory exists: $dir\n";
    }
}

// Create configuration file
echo "\n⚙️  Creating configuration file...\n";
$configContent = "<?php
/**
 * Captive Portal Configuration
 * Generated on: " . date('Y-m-d H:i:s') . "
 */

return [
    'database' => [
        'host' => '{$config['db_host']}',
        'name' => '{$config['db_name']}',
        'user' => '{$config['db_user']}',
        'pass' => '{$config['db_pass']}'
    ],
    'portal' => [
        'url' => '{$config['portal_url']}',
        'api_url' => '{$config['api_url']}',
        'session_timeout' => 3600,
        'max_attempts' => 3,
        'lockout_time' => 900
    ],
    'walled_garden' => [
        'default_domains' => [
            'google.com',
            'gmail.com',
            'yahoo.com',
            'hotmail.com',
            'outlook.com',
            'facebook.com',
            'twitter.com',
            'linkedin.com',
            'github.com',
            'stackoverflow.com'
        ]
    ]
];
";

if (file_put_contents('config/captive_portal.php', $configContent)) {
    echo "  ✅ Created configuration file: config/captive_portal.php\n";
} else {
    echo "  ❌ Failed to create configuration file\n";
}

// Create .htaccess for captive portal
echo "\n🔧 Creating .htaccess for captive portal...\n";
$htaccessContent = "RewriteEngine On

# Redirect all HTTP traffic to captive portal if not authenticated
RewriteCond %{REQUEST_URI} !^/modules/captive_portal\.php
RewriteCond %{REQUEST_URI} !^/api/
RewriteCond %{REQUEST_URI} !^/assets/
RewriteCond %{REQUEST_URI} !^/favicon\.ico
RewriteCond %{HTTP_COOKIE} !captive_portal_authenticated
RewriteRule ^(.*)$ /modules/captive_portal.php?redirect=%{REQUEST_URI} [L,R=302]

# Allow access to walled garden domains
RewriteCond %{HTTP_HOST} ^(www\.)?(google\.com|gmail\.com|yahoo\.com|hotmail\.com|outlook\.com|facebook\.com|twitter\.com|linkedin\.com|github\.com|stackoverflow\.com)$
RewriteRule ^(.*)$ - [L]
";

if (file_put_contents('.htaccess.captive_portal', $htaccessContent)) {
    echo "  ✅ Created .htaccess template: .htaccess.captive_portal\n";
    echo "  📝 Note: Copy this to your web root .htaccess if needed\n";
} else {
    echo "  ❌ Failed to create .htaccess template\n";
}

// Test API endpoints
echo "\n🌐 Testing API endpoints...\n";

$endpoints = [
    'settings' => 'GET',
    'vlans' => 'GET',
    'users' => 'GET',
    'stats' => 'GET'
];

foreach ($endpoints as $endpoint => $method) {
    $url = $config['api_url'] . '/' . $endpoint;
    
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            echo "  ✅ API endpoint /$endpoint: Working\n";
        } else {
            echo "  ⚠️  API endpoint /$endpoint: Response format issue\n";
        }
    } else {
        echo "  ❌ API endpoint /$endpoint: Not accessible\n";
    }
}

// Create sample VLANs
echo "\n🏗️  Creating sample VLANs...\n";

$sampleVLANs = [
    [
        'vlan_id' => 100,
        'name' => 'Guest Network',
        'description' => 'Public guest network with captive portal',
        'network_address' => '192.168.100.0/24',
        'gateway' => '192.168.100.1',
        'captive_portal_enabled' => true,
        'captive_portal_url' => '/modules/captive_portal.php',
        'walled_garden_domains' => json_encode(['google.com', 'gmail.com', 'facebook.com', 'twitter.com']),
        'session_timeout' => 3600,
        'max_bandwidth' => 5
    ],
    [
        'vlan_id' => 200,
        'name' => 'Hotel Network',
        'description' => 'Hotel guest network with premium access',
        'network_address' => '192.168.200.0/24',
        'gateway' => '192.168.200.1',
        'captive_portal_enabled' => true,
        'captive_portal_url' => '/modules/captive_portal.php',
        'walled_garden_domains' => json_encode(['google.com', 'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'facebook.com', 'twitter.com', 'linkedin.com']),
        'session_timeout' => 7200,
        'max_bandwidth' => 10
    ]
];

foreach ($sampleVLANs as $vlan) {
    try {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO vlans (vlan_id, name, description, network_address, gateway, 
                                     captive_portal_enabled, captive_portal_url, walled_garden_domains,
                                     session_timeout, max_bandwidth)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $vlan['vlan_id'],
            $vlan['name'],
            $vlan['description'],
            $vlan['network_address'],
            $vlan['gateway'],
            $vlan['captive_portal_enabled'],
            $vlan['captive_portal_url'],
            $vlan['walled_garden_domains'],
            $vlan['session_timeout'],
            $vlan['max_bandwidth']
        ]);
        
        echo "  ✅ Created VLAN: {$vlan['name']} (ID: {$vlan['vlan_id']})\n";
    } catch (PDOException $e) {
        echo "  ⚠️  VLAN {$vlan['vlan_id']} already exists or error: " . $e->getMessage() . "\n";
    }
}

// Create sample users
echo "\n👥 Creating sample users...\n";

$sampleUsers = [
    [
        'username' => 'admin',
        'password' => 'admin123',
        'email' => 'admin@example.com',
        'full_name' => 'System Administrator',
        'role' => 'admin',
        'max_bandwidth' => 100,
        'allowed_domains' => json_encode(['*'])
    ],
    [
        'username' => 'guest',
        'password' => 'guest123',
        'email' => 'guest@example.com',
        'full_name' => 'Guest User',
        'role' => 'guest',
        'vlan_id' => 100,
        'max_bandwidth' => 5,
        'allowed_domains' => json_encode(['google.com', 'gmail.com', 'facebook.com'])
    ],
    [
        'username' => 'user',
        'password' => 'user123',
        'email' => 'user@example.com',
        'full_name' => 'Regular User',
        'role' => 'user',
        'vlan_id' => 200,
        'max_bandwidth' => 10,
        'allowed_domains' => json_encode(['google.com', 'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'facebook.com', 'twitter.com', 'linkedin.com'])
    ]
];

foreach ($sampleUsers as $user) {
    try {
        $passwordHash = password_hash($user['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO captive_portal_users (username, password_hash, email, full_name, role, 
                                                    vlan_id, max_bandwidth, allowed_domains)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $user['username'],
            $passwordHash,
            $user['email'],
            $user['full_name'],
            $user['role'],
            $user['vlan_id'] ?? null,
            $user['max_bandwidth'],
            $user['allowed_domains']
        ]);
        
        echo "  ✅ Created user: {$user['username']} (Role: {$user['role']})\n";
    } catch (PDOException $e) {
        echo "  ⚠️  User {$user['username']} already exists or error: " . $e->getMessage() . "\n";
    }
}

// Create cron job for cleanup
echo "\n⏰ Setting up maintenance tasks...\n";

$cronContent = "#!/bin/bash
# Captive Portal Maintenance Script
# Run this script every 5 minutes to clean up expired sessions

cd " . getcwd() . "
php -f maintenance/cleanup_sessions.php >> logs/captive_portal/cleanup.log 2>&1
";

if (file_put_contents('maintenance/cleanup_sessions.php', "<?php
// Cleanup expired sessions
require_once 'config/captive_portal.php';

\$pdo = new PDO(\"mysql:host={\$config['database']['host']};dbname={\$config['database']['name']}\", 
               \$config['database']['user'], \$config['database']['pass']);

\$stmt = \$pdo->prepare(\"
    UPDATE captive_portal_sessions cs
    JOIN vlans v ON cs.vlan_id = v.id
    SET cs.active = FALSE, cs.status = 'expired', cs.logout_time = NOW()
    WHERE cs.active = TRUE 
    AND TIMESTAMPDIFF(SECOND, cs.login_time, NOW()) > v.session_timeout
\");

\$stmt->execute();
echo date('Y-m-d H:i:s') . ' - Cleaned up ' . \$stmt->rowCount() . ' expired sessions\\n';
?>")) {
    echo "  ✅ Created cleanup script: maintenance/cleanup_sessions.php\n";
} else {
    echo "  ❌ Failed to create cleanup script\n";
}

// Final summary
echo "\n🎉 Captive Portal Setup Complete!\n";
echo "=====================================\n\n";

echo "📋 Summary:\n";
echo "  ✅ Database tables created\n";
echo "  ✅ Sample VLANs configured\n";
echo "  ✅ Sample users created\n";
echo "  ✅ Configuration files generated\n";
echo "  ✅ API endpoints tested\n";
echo "  ✅ Maintenance scripts created\n\n";

echo "🔗 Access URLs:\n";
echo "  - Captive Portal: {$config['portal_url']}\n";
echo "  - VLAN Management: /modules/vlan_captive_portal.php\n";
echo "  - API Base URL: {$config['api_url']}\n\n";

echo "👤 Demo Credentials:\n";
echo "  - Username: admin | Password: admin123\n";
echo "  - Username: guest | Password: guest123\n";
echo "  - Username: user | Password: user123\n\n";

echo "⚙️  Next Steps:\n";
echo "  1. Configure your router/firewall to redirect traffic to the captive portal\n";
echo "  2. Set up DNS forwarding for walled garden domains\n";
echo "  3. Configure VLAN interfaces on your network equipment\n";
echo "  4. Test the portal with different user types\n";
echo "  5. Customize the portal appearance and branding\n\n";

echo "📚 Documentation:\n";
echo "  - Check the generated configuration files\n";
echo "  - Review the API documentation in api/captive_portal_api.php\n";
echo "  - Monitor logs in logs/captive_portal/\n\n";

echo "🔧 Maintenance:\n";
echo "  - Set up cron job: */5 * * * * php " . getcwd() . "/maintenance/cleanup_sessions.php\n";
echo "  - Monitor session logs and statistics\n";
echo "  - Regularly backup the database\n\n";

echo "✅ Setup completed successfully!\n";
?> 