<?php
/**
 * WebGL Integration Script for SLMS v1.1.0
 * 
 * This script integrates the new WebGL framework with all existing SLMS modules
 * and creates a unified system with enhanced 3D visualization capabilities.
 */

require_once 'config.php';

class WebGLIntegration {
    private $pdo;
    private $integrationLog = [];
    
    public function __construct() {
        $this->pdo = get_pdo();
        $this->log("Starting WebGL integration process...");
    }
    
    /**
     * Main integration process
     */
    public function integrate() {
        try {
            $this->log("=== WebGL Integration Process Started ===");
            
            // Step 1: Backup existing system
            $this->backupExistingSystem();
            
            // Step 2: Merge modules from slmsold
            $this->mergeExistingModules();
            
            // Step 3: Update database schema
            $this->updateDatabaseSchema();
            
            // Step 4: Create unified navigation
            $this->createUnifiedNavigation();
            
            // Step 5: Test integration
            $this->testIntegration();
            
            $this->log("=== WebGL Integration Process Completed Successfully ===");
            
            return true;
            
        } catch (Exception $e) {
            $this->log("ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Backup existing system
     */
    private function backupExistingSystem() {
        $this->log("Step 1: Creating backup of existing system...");
        
        $backupDir = 'backup_' . date('Y-m-d_H-i-s');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // Backup current modules
        if (is_dir('modules')) {
            $this->copyDirectory('modules', $backupDir . '/modules');
        }
        
        // Backup current assets
        if (is_dir('assets')) {
            $this->copyDirectory('assets', $backupDir . '/assets');
        }
        
        // Backup configuration
        if (file_exists('config.php')) {
            copy('config.php', $backupDir . '/config.php');
        }
        
        $this->log("Backup created in: $backupDir");
    }
    
    /**
     * Merge existing modules from slmsold
     */
    private function mergeExistingModules() {
        $this->log("Step 2: Merging existing modules from slmsold...");
        
        $sourceDir = '../slmsold/SLMS/html/slms/modules';
        $targetDir = 'modules';
        
        if (!is_dir($sourceDir)) {
            $this->log("WARNING: Source directory $sourceDir not found");
            return;
        }
        
        // Create target directory if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Copy all modules
        $this->copyDirectory($sourceDir, $targetDir);
        
        // Ensure WebGL module is present
        if (!file_exists($targetDir . '/webgl_network_viewer.php')) {
            copy('modules/webgl_network_viewer.php', $targetDir . '/webgl_network_viewer.php');
        }
        
        $this->log("Modules merged successfully");
    }
    
    /**
     * Update database schema for WebGL integration
     */
    private function updateDatabaseSchema() {
        $this->log("Step 3: Updating database schema...");
        
        try {
            // Add WebGL-specific columns to devices table
            $this->addWebGLColumns();
            
            // Create WebGL-specific tables
            $this->createWebGLTables();
            
            // Insert sample data
            $this->insertSampleData();
            
            $this->log("Database schema updated successfully");
            
        } catch (Exception $e) {
            $this->log("ERROR updating database: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Add WebGL-specific columns to devices table
     */
    private function addWebGLColumns() {
        $columns = [
            'position_x' => 'FLOAT DEFAULT 0',
            'position_y' => 'FLOAT DEFAULT 0', 
            'position_z' => 'FLOAT DEFAULT 0',
            'model' => 'VARCHAR(255)',
            'vendor' => 'VARCHAR(255)',
            'serial_number' => 'VARCHAR(255)',
            'firmware_version' => 'VARCHAR(255)',
            'location' => 'VARCHAR(255)',
            'description' => 'TEXT'
        ];
        
        foreach ($columns as $column => $definition) {
            try {
                $stmt = $this->pdo->prepare("ALTER TABLE devices ADD COLUMN $column $definition");
                $stmt->execute();
                $this->log("Added column: $column");
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                    $this->log("WARNING: Could not add column $column: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Create WebGL-specific tables
     */
    private function createWebGLTables() {
        // Create network_connections table
        $sql = "
        CREATE TABLE IF NOT EXISTS network_connections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            from_device_id INT,
            to_device_id INT,
            connection_type VARCHAR(50) DEFAULT 'ethernet',
            bandwidth INT DEFAULT 100,
            status VARCHAR(20) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (from_device_id) REFERENCES devices(id) ON DELETE CASCADE,
            FOREIGN KEY (to_device_id) REFERENCES devices(id) ON DELETE CASCADE
        )";
        
        $this->pdo->exec($sql);
        $this->log("Created network_connections table");
        
        // Create webgl_settings table
        $sql = "
        CREATE TABLE IF NOT EXISTS webgl_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_name VARCHAR(100) UNIQUE,
            setting_value TEXT,
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
        $this->log("Created webgl_settings table");
    }
    
    /**
     * Insert sample data for WebGL integration
     */
    private function insertSampleData() {
        // Insert sample devices with 3D positions
        $sampleDevices = [
            ['Router-01', 'router', '192.168.1.1', 0, 0, 10],
            ['Switch-01', 'switch', '192.168.1.10', -15, 0, 5],
            ['Switch-02', 'switch', '192.168.1.11', 15, 0, 5],
            ['Server-01', 'server', '192.168.1.100', -15, 15, 15],
            ['Server-02', 'server', '192.168.1.101', 15, 15, 15],
            ['Client-01', 'other', '192.168.1.200', -30, 0, 0],
            ['Client-02', 'other', '192.168.1.201', 30, 0, 0]
        ];
        
        foreach ($sampleDevices as $device) {
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO devices (name, type, ip_address, position_x, position_y, position_z, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'online', NOW())
                    ON DUPLICATE KEY UPDATE 
                    position_x = VALUES(position_x),
                    position_y = VALUES(position_y),
                    position_z = VALUES(position_z)
                ");
                $stmt->execute($device);
            } catch (PDOException $e) {
                $this->log("WARNING: Could not insert sample device: " . $e->getMessage());
            }
        }
        
        $this->log("Sample data inserted");
    }
    
    /**
     * Create unified navigation system
     */
    private function createUnifiedNavigation() {
        $this->log("Step 4: Creating unified navigation...");
        
        // Create enhanced admin menu with WebGL integration
        $this->createEnhancedAdminMenu();
        
        // Create WebGL dashboard integration
        $this->createWebGLDashboard();
        
        $this->log("Unified navigation created");
    }
    
    /**
     * Create enhanced admin menu
     */
    private function createEnhancedAdminMenu() {
        $menuContent = '<?php
/**
 * Enhanced Admin Menu with WebGL Integration
 * SLMS v1.1.0 - Unified Navigation System
 */

require_once "config.php";
require_once "helpers/auth_helper.php";

// Check authentication
if (!is_authenticated()) {
    header("Location: login.php");
    exit;
}

$user = get_current_user();
$accessLevel = get_user_access_level($user["id"]);

// Get system statistics
$stats = get_system_statistics();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLMS v1.1.0 - AI Service Network Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/style.css" rel="stylesheet">
    
    <style>
        .webgl-highlight {
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
            border: 1px solid #00ff00;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .feature-card {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
            border: 1px solid #333;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            border-color: #00ff00;
            transform: translateY(-2px);
        }
        
        .webgl-badge {
            background: linear-gradient(45deg, #00ff00, #008800);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 5px;
        }
    </style>
</head>
<body class="dark-theme">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-diagram-3"></i>
                    SLMS v1.1.0 - AI Service Network Management System
                    <span class="webgl-badge">WebGL Enhanced</span>
                </h1>
            </div>
        </div>
        
        <!-- WebGL Integration Highlight -->
        <div class="webgl-highlight">
            <div class="row">
                <div class="col-md-8">
                    <h4><i class="bi bi-cube"></i> New: 3D Network Visualization</h4>
                    <p>Experience your network infrastructure in immersive 3D with real-time monitoring, Cacti integration, and SNMP data visualization.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="webgl_demo.php" class="btn btn-success">
                        <i class="bi bi-play-circle"></i> Launch 3D Viewer
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- System Statistics -->
            <div class="col-md-3">
                <div class="feature-card">
                    <h5><i class="bi bi-graph-up"></i> System Statistics</h5>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4"><?php echo $stats["total_devices"]; ?></div>
                            <small>Total Devices</small>
                        </div>
                        <div class="col-6">
                            <div class="h4"><?php echo $stats["online_devices"]; ?></div>
                            <small>Online</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-md-9">
                <div class="feature-card">
                    <h5><i class="bi bi-lightning"></i> Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="modules/devices.php" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-hdd-network"></i> Manage Devices
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="modules/network_monitoring.php" class="btn btn-info w-100 mb-2">
                                <i class="bi bi-activity"></i> Network Monitoring
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="modules/cacti_integration.php" class="btn btn-warning w-100 mb-2">
                                <i class="bi bi-graph-up"></i> Cacti Integration
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="webgl_demo.php" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-cube"></i> 3D Viewer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Module Categories -->
        <div class="row">
            <!-- Network Management -->
            <div class="col-md-4">
                <div class="feature-card">
                    <h5><i class="bi bi-diagram-3"></i> Network Management</h5>
                    <ul class="list-unstyled">
                        <li><a href="modules/devices.php"><i class="bi bi-hdd"></i> Device Management</a></li>
                        <li><a href="modules/networks.php"><i class="bi bi-diagram-2"></i> Network Configuration</a></li>
                        <li><a href="modules/network_monitoring.php"><i class="bi bi-activity"></i> Network Monitoring</a></li>
                        <li><a href="modules/network_alerts.php"><i class="bi bi-exclamation-triangle"></i> Network Alerts</a></li>
                        <li><a href="webgl_demo.php"><i class="bi bi-cube"></i> 3D Network Viewer <span class="webgl-badge">NEW</span></a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Monitoring & Integration -->
            <div class="col-md-4">
                <div class="feature-card">
                    <h5><i class="bi bi-graph-up"></i> Monitoring & Integration</h5>
                    <ul class="list-unstyled">
                        <li><a href="modules/cacti_integration.php"><i class="bi bi-graph-up"></i> Cacti Integration</a></li>
                        <li><a href="modules/snmp_graph.php"><i class="bi bi-cpu"></i> SNMP Monitoring</a></li>
                        <li><a href="modules/bandwidth_reports.php"><i class="bi bi-speedometer2"></i> Bandwidth Reports</a></li>
                        <li><a href="modules/capacity_planning.php"><i class="bi bi-calendar-check"></i> Capacity Planning</a></li>
                        <li><a href="modules/advanced_graphing.php"><i class="bi bi-bar-chart"></i> Advanced Graphing</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- System Administration -->
            <div class="col-md-4">
                <div class="feature-card">
                    <h5><i class="bi bi-gear"></i> System Administration</h5>
                    <ul class="list-unstyled">
                        <li><a href="modules/user_management.php"><i class="bi bi-people"></i> User Management</a></li>
                        <li><a href="modules/access_level_manager.php"><i class="bi bi-shield-check"></i> Access Control</a></li>
                        <li><a href="modules/system_status.php"><i class="bi bi-info-circle"></i> System Status</a></li>
                        <li><a href="modules/activity_log.php"><i class="bi bi-journal-text"></i> Activity Log</a></li>
                        <li><a href="modules/dashboard_editor.php"><i class="bi bi-palette"></i> Dashboard Editor</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <p class="text-muted">
                    SLMS v1.1.0 - Enhanced with WebGL 3D Visualization | 
                    <a href="docs/WEBGL_INTEGRATION_PLAN.md">Integration Documentation</a>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';

        file_put_contents('admin_menu_enhanced.php', $menuContent);
        $this->log("Enhanced admin menu created: admin_menu_enhanced.php");
    }
    
    /**
     * Create WebGL dashboard integration
     */
    private function createWebGLDashboard() {
        $dashboardContent = '<?php
/**
 * WebGL Dashboard Integration
 * Provides quick access to 3D network visualization
 */

require_once "config.php";
require_once "helpers/auth_helper.php";

// Check authentication
if (!is_authenticated()) {
    header("Location: login.php");
    exit;
}

// Get network statistics for dashboard
function get_dashboard_stats() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_devices,
                SUM(CASE WHEN status = \'online\' THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN status = \'offline\' THEN 1 ELSE 0 END) as offline_devices
            FROM devices
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ["total_devices" => 0, "online_devices" => 0, "offline_devices" => 0];
    }
}

$stats = get_dashboard_stats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebGL Dashboard - SLMS v1.1.0</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/style.css" rel="stylesheet">
    
    <style>
        .dashboard-card {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
            border: 1px solid #333;
        }
        
        .webgl-preview {
            width: 100%;
            height: 300px;
            background: #1a1a1a;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00ff00;
            font-size: 1.2em;
        }
    </style>
</head>
<body class="dark-theme">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-cube"></i>
                    WebGL Dashboard - 3D Network Visualization
                </h1>
            </div>
        </div>
        
        <div class="row">
            <!-- Statistics -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h5><i class="bi bi-graph-up"></i> Network Statistics</h5>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4"><?php echo $stats["total_devices"]; ?></div>
                            <small>Total Devices</small>
                        </div>
                        <div class="col-6">
                            <div class="h4"><?php echo $stats["online_devices"]; ?></div>
                            <small>Online</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-md-9">
                <div class="dashboard-card">
                    <h5><i class="bi bi-lightning"></i> Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="webgl_demo.php" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-play-circle"></i> Launch 3D Viewer
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="webgl_demo_fallback.php" class="btn btn-secondary w-100 mb-2">
                                <i class="bi bi-arrow-down-circle"></i> 2D Fallback
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="modules/webgl_network_viewer.php" class="btn btn-info w-100 mb-2">
                                <i class="bi bi-gear"></i> API Interface
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="admin_menu_enhanced.php" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-house"></i> Main Menu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- WebGL Preview -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5><i class="bi bi-cube"></i> 3D Network Preview</h5>
                    <div class="webgl-preview">
                        <div class="text-center">
                            <i class="bi bi-cube" style="font-size: 3em; margin-bottom: 10px;"></i>
                            <br>
                            <strong>3D Network Visualization</strong>
                            <br>
                            <small>Click "Launch 3D Viewer" to experience immersive network monitoring</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';

        file_put_contents('webgl_dashboard.php', $dashboardContent);
        $this->log("WebGL dashboard created: webgl_dashboard.php");
    }
    
    /**
     * Test the integration
     */
    private function testIntegration() {
        $this->log("Step 5: Testing integration...");
        
        // Test database connectivity
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM devices");
            $stmt->execute();
            $deviceCount = $stmt->fetchColumn();
            $this->log("Database test passed. Found $deviceCount devices.");
        } catch (Exception $e) {
            $this->log("ERROR: Database test failed: " . $e->getMessage());
        }
        
        // Test WebGL API
        try {
            if (file_exists('modules/webgl_network_viewer.php')) {
                $this->log("WebGL API module found and ready.");
            } else {
                $this->log("WARNING: WebGL API module not found.");
            }
        } catch (Exception $e) {
            $this->log("ERROR: WebGL API test failed: " . $e->getMessage());
        }
        
        // Test file structure
        $requiredFiles = [
            'webgl_demo.php',
            'webgl_demo_fallback.php',
            'assets/webgl-network-viewer.js',
            'admin_menu_enhanced.php',
            'webgl_dashboard.php'
        ];
        
        foreach ($requiredFiles as $file) {
            if (file_exists($file)) {
                $this->log("✓ $file found");
            } else {
                $this->log("✗ $file missing");
            }
        }
        
        $this->log("Integration testing completed");
    }
    
    /**
     * Copy directory recursively
     */
    private function copyDirectory($source, $destination) {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $sourcePath = $source . '/' . $file;
                $destPath = $destination . '/' . $file;
                
                if (is_dir($sourcePath)) {
                    $this->copyDirectory($sourcePath, $destPath);
                } else {
                    copy($sourcePath, $destPath);
                }
            }
        }
        closedir($dir);
    }
    
    /**
     * Log message
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message";
        $this->integrationLog[] = $logMessage;
        echo $logMessage . "\n";
    }
    
    /**
     * Get integration log
     */
    public function getLog() {
        return $this->integrationLog;
    }
}

// Run integration if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $integration = new WebGLIntegration();
    $success = $integration->integrate();
    
    if ($success) {
        echo "\n🎉 Integration completed successfully!\n";
        echo "Next steps:\n";
        echo "1. Test the system: http://your-domain/webgl_demo.php\n";
        echo "2. Access enhanced menu: http://your-domain/admin_menu_enhanced.php\n";
        echo "3. View dashboard: http://your-domain/webgl_dashboard.php\n";
        echo "4. Create GitHub release for v1.1.0\n";
    } else {
        echo "\n❌ Integration failed. Check the log above for details.\n";
    }
}
?> 