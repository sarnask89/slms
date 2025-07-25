<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/modules/helpers/auth_helper.php';
require_once __DIR__ . '/modules/helpers/request_helper.php';

class SystemTester {
    private $pdo;
    private $results = [];
    private $errors = [];
    private $warnings = [];
    
    public function __construct() {
        try {
            $this->pdo = get_pdo();
            $this->log('success', 'Database connection established');
        } catch (Exception $e) {
            $this->log('error', 'Database connection failed: ' . $e->getMessage());
        }
    }
    
    public function runAllTests() {
        echo "Starting system tests...\n\n";
        
        // Test core components
        $this->testCoreComponents();
        
        // Test database tables
        $this->testDatabaseTables();
        
        // Test authentication system
        $this->testAuthSystem();
        
        // Test all modules
        $this->testModules();
        
        // Test file permissions
        $this->testFilePermissions();
        
        // Display results
        $this->displayResults();
    }
    
    private function testCoreComponents() {
        echo "Testing core components...\n\n";
        
        // Test config.php exists
        if (file_exists(__DIR__ . '/config.php')) {
            $this->log('success', 'config.php exists');
        } else {
            $this->log('error', 'config.php missing');
        }
        
        // Test database connection function
        if (function_exists('get_pdo')) {
            $this->log('success', 'Database connection function works');
        } else {
            $this->log('error', 'Database connection function missing');
        }
        
        // Test base_url function
        if (function_exists('base_url')) {
            $this->log('success', 'base_url function exists');
        } else {
            $this->log('error', 'base_url function missing');
        }
        
        // Test required directories exist
        $required_dirs = ['modules', 'assets', 'partials', 'sql'];
        foreach ($required_dirs as $dir) {
            if (is_dir(__DIR__ . '/' . $dir)) {
                $this->log('success', $dir . ' directory exists');
            } else {
                $this->log('error', $dir . ' directory missing');
            }
        }
    }
    
    private function testDatabaseTables() {
        echo "Testing database tables...\n\n";
        
        try {
            // Test users table
            $this->testTable('users', [
                'id', 'username', 'password', 'full_name', 'email', 'role',
                'is_active', 'access_level_id', 'last_login', 'created_at', 'updated_at'
            ]);
            
            // Test access_levels table
            $this->testTable('access_levels', [
                'id', 'name', 'description', 'created_by', 'created_at', 'updated_at'
            ]);
            
            // Test access_level_permissions table
            $this->testTable('access_level_permissions', [
                'id', 'access_level_id', 'section', 'action', 'created_at'
            ]);
            
            // Test clients table
            $this->testTable('clients', [
                'id', 'first_name', 'last_name', 'company_name', 'email', 'phone',
                'address', 'created_at', 'updated_at'
            ]);
            
            // Test devices table
            $this->testTable('devices', [
                'id', 'name', 'type', 'ip_address', 'mac_address', 'status',
                'created_at', 'updated_at'
            ]);
            
            // Test networks table
            $this->testTable('networks', [
                'id', 'name', 'subnet', 'gateway', 'vlan_id', 'created_at', 'updated_at'
            ]);
            
            // Test services table
            $this->testTable('services', [
                'id', 'name', 'type', 'description', 'price', 'created_at', 'updated_at'
            ]);
            
            // Test invoices table
            $this->testTable('invoices', [
                'id', 'number', 'client_id', 'total_amount', 'status', 'due_date',
                'paid_at', 'created_at', 'updated_at'
            ]);
            
            // Test payments table
            $this->testTable('payments', [
                'id', 'invoice_id', 'amount', 'payment_date', 'payment_method',
                'created_at', 'updated_at'
            ]);
            
        } catch (Exception $e) {
            $this->log('error', 'Database test failed: ' . $e->getMessage());
        }
    }
    
    private function testTable($table, $required_columns) {
        try {
            // Check if table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $this->log('success', "Table $table exists");
                
                // Get columns
                $stmt = $this->pdo->query("SHOW COLUMNS FROM $table");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Check required columns
                $missing = array_diff($required_columns, $columns);
                if (empty($missing)) {
                    $this->log('success', "All required columns present in $table");
                } else {
                    $this->log('error', "Missing columns in $table: " . implode(', ', $missing));
                }
            } else {
                $this->log('error', "Table $table missing");
            }
        } catch (Exception $e) {
            $this->log('error', "Error testing table $table: " . $e->getMessage());
        }
    }
    
    private function testAuthSystem() {
        echo "Testing authentication system...\n\n";
        
        // Test auth helper functions
        $auth_functions = [
            'is_logged_in',
            'require_login',
            'has_role',
            'require_role',
            'is_admin',
            'require_admin',
            'get_current_user_info',
            'has_access_permission'
        ];
        
        foreach ($auth_functions as $function) {
            if (function_exists($function)) {
                $this->log('success', "Auth function $function exists");
            } else {
                $this->log('error', "Auth function $function missing");
            }
        }
        
        // Test admin user exists
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            if ($stmt->fetchColumn() > 0) {
                $this->log('success', 'Admin user exists');
            } else {
                $this->log('error', 'No admin user found');
            }
        } catch (Exception $e) {
            $this->log('error', 'Error checking admin user: ' . $e->getMessage());
        }
    }
    
    private function testModules() {
        echo "Testing all modules...\n\n";
        
        $modules_dir = __DIR__ . '/modules';
        $this->scanDirectory($modules_dir);
    }
    
    private function scanDirectory($dir) {
        $files = glob($dir . '/*.php');
        foreach ($files as $file) {
            $this->testPhpFile($file);
        }
        
        // Scan subdirectories
        $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
        foreach ($subdirs as $subdir) {
            $this->scanDirectory($subdir);
        }
    }
    
    private function testPhpFile($file) {
        $relative_path = str_replace(__DIR__ . '/', '', $file);
        
        // Test syntax
        $output = [];
        $return_var = 0;
        exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            $this->log('success', "Syntax OK: $relative_path");
            
            // Test file contents
            $contents = file_get_contents($file);
            
            // Check for correct path usage
            if (strpos($contents, "require_once __DIR__") !== false ||
                !preg_match("/require(_once)?\s+['\"]\.\.?\//", $contents)) {
                $this->log('success', "Correct path usage in $relative_path");
            } else {
                $this->log('warning', "Relative paths found in $relative_path");
            }
            
            // Check for proper session handling
            if (strpos($contents, 'session_start') !== false) {
                if (strpos($contents, 'session_status()') !== false &&
                    strpos($contents, 'php_sapi_name()') !== false) {
                    $this->log('success', "Proper session handling in $relative_path");
                } else {
                    $this->log('warning', "Unsafe session handling in $relative_path");
                }
            }
            
            // Check for proper request method checking
            if (strpos($contents, '$_POST') !== false || 
                strpos($contents, '$_GET') !== false) {
                if (strpos($contents, 'REQUEST_METHOD') !== false ||
                    strpos($contents, 'is_post_request()') !== false ||
                    strpos($contents, 'is_get_request()') !== false) {
                    $this->log('success', "Proper request method checking in $relative_path");
                } else {
                    $this->log('warning', "Missing request method check in $relative_path");
                }
            }
            
        } else {
            $this->log('error', "Syntax error in $relative_path: " . implode("\n", $output));
        }
    }
    
    private function testFilePermissions() {
        echo "\nTesting file permissions...\n\n";
        
        // Test config.php permissions (should be 644)
        $config_perms = fileperms(__DIR__ . '/config.php') & 0777;
        if ($config_perms === 0644) {
            $this->log('success', 'Correct permissions (0644) for config.php');
        } else {
            $this->log('error', sprintf('Incorrect permissions (%o) for config.php', $config_perms));
        }
        
        // Test directory permissions (should be 755)
        $dirs = ['modules', 'assets', 'partials', 'sql'];
        foreach ($dirs as $dir) {
            $perms = fileperms(__DIR__ . '/' . $dir) & 0777;
            if ($perms === 0755) {
                $this->log('success', "Correct permissions (0755) for $dir");
            } else {
                $this->log('error', sprintf('Incorrect permissions (%o) for %s', $perms, $dir));
            }
        }
    }
    
    private function log($type, $message) {
        switch ($type) {
            case 'success':
                $this->results[] = ['type' => 'success', 'message' => $message];
                break;
            case 'error':
                $this->errors[] = $message;
                $this->results[] = ['type' => 'error', 'message' => $message];
                break;
            case 'warning':
                $this->warnings[] = $message;
                $this->results[] = ['type' => 'warning', 'message' => $message];
                break;
        }
    }
    
    private function displayResults() {
        echo "\nTest Results:\n";
        echo "=============\n\n";
        
        foreach ($this->results as $result) {
            $symbol = $result['type'] === 'success' ? '✓' : ($result['type'] === 'error' ? '✗' : '!');
            echo "$symbol {$result['message']}\n";
        }
        
        echo "\nSummary:\n";
        echo "--------\n";
        $successes = count(array_filter($this->results, fn($r) => $r['type'] === 'success'));
        $warnings = count($this->warnings);
        $errors = count($this->errors);
        echo "Successes: $successes\n";
        echo "Warnings:  $warnings\n";
        echo "Errors:    $errors\n\n";
        
        if (!empty($this->errors)) {
            echo "Critical Errors:\n";
            echo "---------------\n";
            foreach ($this->errors as $error) {
                echo "✗ $error\n";
            }
            echo "\n";
        }
    }
}

$tester = new SystemTester();
$tester->runAllTests(); 