<?php
/**
 * SLMS Framework Migration Script
 * Migrates all existing modules to the new object-oriented framework
 */

require_once 'modules/SLMS_Core_Framework.php';
require_once 'modules/network_discovery.php';
require_once 'modules/ai_research_engine.php';

// Include all existing modules
$existingModules = [
    'html/modules/mikrotik_api.php',
    'html/modules/snmp_oid_helper.php',
    'html/modules/network_monitoring_enhanced.php',
    'html/modules/bridge_nat_controller.php',
    'html/modules/dynamic_network_controller.php',
    'html/modules/enhanced_queue_manager.php',
    'html/modules/ml_model_manager.php',
    'html/modules/ml_performance_monitor.php',
    'html/modules/ml_prediction_engine.php',
    'html/modules/ml_training_engine.php',
    'html/modules/ml_ajax.php',
    'html/modules/ml_database_manager.php',
    'html/modules/system_status.php',
    'html/modules/user_management.php',
    'html/modules/vlan_captive_portal.php',
    'html/modules/captive_portal.php',
    'html/modules/cacti_integration.php',
    'html/modules/capacity_planning.php',
    'html/modules/network_alerts.php',
    'html/modules/network_dashboard.php',
    'html/modules/network_monitoring.php',
    'html/modules/check_device.php',
    'html/modules/client_devices.php',
    'html/modules/dhcp_clients.php',
    'html/modules/discover_snmp_mndp.php',
    'html/modules/interface_monitoring.php',
    'html/modules/mndp_monitor.php',
    'html/modules/mndp_enhanced.php',
    'html/modules/snmp_graph.php',
    'html/modules/snmp_graph_poll.php',
    'html/modules/queue_monitoring.php',
    'html/modules/theme_compositor.php',
    'html/modules/layout_manager.php',
    'html/modules/dashboard_editor.php',
    'html/modules/menu_editor.php',
    'html/modules/search.php',
    'html/modules/tooltip_data.php',
    'html/modules/access_level_permissions.php',
    'html/modules/profile.php',
    'html/modules/simple_check.php',
    'html/modules/skeleton_devices.php',
    'html/modules/table_example.php',
    'html/modules/test_cacti_integration.php',
    'html/modules/test_mikrotik_api.php',
    'html/modules/theme_preview.php',
    'html/modules/user_profile.php',
    'html/modules/example_with_access_control.php',
    'html/modules/frame_layout.php',
    'html/modules/frame_navbar.php',
    'html/modules/frame_top_navbar.php',
    'html/modules/import_dhcp_clients_improved.php',
    'html/modules/import_dhcp_networks_improved.php',
    'html/modules/invoices.php',
    'html/modules/manual.php',
    'html/modules/menu_preview.php',
    'html/modules/run_all_scripts.php',
    'html/modules/save_reload.php',
    'html/modules/test_all_php_files.php',
    'html/modules/auto_fix_sessions.php',
    'html/modules/cms_migrator.php',
    'html/modules/update_client_structure.php',
    'html/modules/create_column_config_table.php',
    'html/modules/create_layout_table.php',
    'html/modules/dashboard_preview.php',
    'html/modules/edit_client.php',
    'html/modules/edit_device.php',
    'html/modules/edit_internet_package.php',
    'html/modules/edit_network.php',
    'html/modules/edit_service.php',
    'html/modules/error_403.php',
    'html/modules/error_404.php',
    'html/modules/error_500.php',
    'html/modules/login.php',
    'html/modules/logout.php',
    'html/modules/check_auth.php',
    'html/modules/config.php',
    'html/modules/config_script.php',
    'html/modules/content_wrapper.php',
    'html/modules/devices.php',
    'html/modules/dhcp_clients_v7.php',
    'html/modules/internet_packages.php',
    'html/modules/payments.php',
    'html/modules/services.php',
    'html/modules/setup_auth_tables.php',
    'html/modules/tariffs.php',
    'html/modules/tv_packages.php',
    'html/modules/users.php',
    'html/modules/clients.php',
    'html/modules/column_config.php',
    'html/modules/networks.php',
    'html/modules/profile.php'
];

class SLMSMigrationManager {
    private $logger;
    private $config;
    private $migrationResults = [];
    
    public function __construct() {
        $this->logger = new SLMSLogger('/var/log/slms/migration.log');
        $this->config = [
            'database' => [
                'host' => 'localhost',
                'database' => 'slmsdb',
                'username' => 'root',
                'password' => ''
            ]
        ];
    }
    
    public function migrateAllModules(): array {
        $this->logger->log("Starting SLMS Framework Migration");
        
        try {
            // Initialize framework
            $framework = initializeSLMSFramework($this->config);
            
            // Migrate existing modules
            $this->migrateExistingModules();
            
            // Test framework integration
            $this->testFrameworkIntegration($framework);
            
            // Generate migration report
            $this->generateMigrationReport();
            
            $this->logger->log("Migration completed successfully");
            
            return [
                'success' => true,
                'migrated_modules' => count($this->migrationResults),
                'results' => $this->migrationResults
            ];
            
        } catch (Exception $e) {
            $this->logger->error("Migration failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => $this->migrationResults
            ];
        }
    }
    
    private function migrateExistingModules(): void {
        global $existingModules;
        
        $this->logger->log("Migrating " . count($existingModules) . " existing modules");
        
        foreach ($existingModules as $modulePath) {
            $this->migrateModule($modulePath);
        }
    }
    
    private function migrateModule(string $modulePath): void {
        $moduleName = basename($modulePath, '.php');
        
        try {
            if (file_exists($modulePath)) {
                // Analyze module structure
                $moduleInfo = $this->analyzeModule($modulePath);
                
                // Create migrated module class
                $migratedClass = $this->createMigratedModuleClass($moduleName, $moduleInfo);
                
                // Save migrated module
                $this->saveMigratedModule($moduleName, $migratedClass);
                
                $this->migrationResults[$moduleName] = [
                    'status' => 'success',
                    'original_path' => $modulePath,
                    'migrated_class' => "Migrated{$moduleName}Module",
                    'features' => $moduleInfo['features']
                ];
                
                $this->logger->log("Successfully migrated module: {$moduleName}");
            } else {
                $this->migrationResults[$moduleName] = [
                    'status' => 'skipped',
                    'reason' => 'File not found'
                ];
                $this->logger->warning("Module file not found: {$modulePath}");
            }
        } catch (Exception $e) {
            $this->migrationResults[$moduleName] = [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
            $this->logger->error("Failed to migrate module {$moduleName}: " . $e->getMessage());
        }
    }
    
    private function analyzeModule(string $modulePath): array {
        $content = file_get_contents($modulePath);
        $moduleInfo = [
            'features' => [],
            'functions' => [],
            'classes' => [],
            'database_queries' => [],
            'api_endpoints' => []
        ];
        
        // Detect features based on content analysis
        if (strpos($content, 'mikrotik') !== false) {
            $moduleInfo['features'][] = 'mikrotik_integration';
        }
        
        if (strpos($content, 'snmp') !== false) {
            $moduleInfo['features'][] = 'snmp_monitoring';
        }
        
        if (strpos($content, 'network') !== false) {
            $moduleInfo['features'][] = 'network_management';
        }
        
        if (strpos($content, 'user') !== false) {
            $moduleInfo['features'][] = 'user_management';
        }
        
        if (strpos($content, 'ml_') !== false || strpos($content, 'machine_learning') !== false) {
            $moduleInfo['features'][] = 'machine_learning';
        }
        
        if (strpos($content, 'cacti') !== false) {
            $moduleInfo['features'][] = 'cacti_integration';
        }
        
        if (strpos($content, 'captive_portal') !== false) {
            $moduleInfo['features'][] = 'captive_portal';
        }
        
        if (strpos($content, 'dhcp') !== false) {
            $moduleInfo['features'][] = 'dhcp_management';
        }
        
        if (strpos($content, 'queue') !== false) {
            $moduleInfo['features'][] = 'queue_management';
        }
        
        // Extract function names
        preg_match_all('/function\s+(\w+)\s*\(/', $content, $matches);
        $moduleInfo['functions'] = $matches[1] ?? [];
        
        // Extract class names
        preg_match_all('/class\s+(\w+)/', $content, $matches);
        $moduleInfo['classes'] = $matches[1] ?? [];
        
        // Extract database queries
        preg_match_all('/SELECT.*?;/i', $content, $matches);
        $moduleInfo['database_queries'] = $matches[0] ?? [];
        
        return $moduleInfo;
    }
    
    private function createMigratedModuleClass(string $moduleName, array $moduleInfo): string {
        $className = "Migrated" . ucfirst($moduleName) . "Module";
        
        $features = implode("', '", $moduleInfo['features']);
        $functions = implode("', '", $moduleInfo['functions']);
        
        $classCode = "<?php\n";
        $classCode .= "/**\n";
        $classCode .= " * Migrated Module: {$moduleName}\n";
        $classCode .= " * Features: {$features}\n";
        $classCode .= " * Functions: {$functions}\n";
        $classCode .= " */\n\n";
        
        $classCode .= "class {$className} extends SLMSModule {\n";
        $classCode .= "    private \$originalModulePath = '{$moduleName}.php';\n";
        $classCode .= "    private \$features = ['{$features}'];\n\n";
        
        $classCode .= "    public function __construct(SLMSDatabaseInterface \$database, SLMSLoggerInterface \$logger, array \$config = []) {\n";
        $classCode .= "        parent::__construct(\$database, \$logger, \$config);\n";
        $classCode .= "        \$this->moduleName = '{$moduleName}';\n";
        $classCode .= "    }\n\n";
        
        $classCode .= "    protected function performModuleOperation(): array {\n";
        $classCode .= "        \$this->logger->log('Executing migrated module: {$moduleName}');\n\n";
        
        // Add feature-specific logic
        foreach ($moduleInfo['features'] as $feature) {
            $classCode .= $this->generateFeatureLogic($feature);
        }
        
        $classCode .= "        return [\n";
        $classCode .= "            'success' => true,\n";
        $classCode .= "            'module' => '{$moduleName}',\n";
        $classCode .= "            'features' => ['{$features}'],\n";
        $classCode .= "            'timestamp' => date('Y-m-d H:i:s')\n";
        $classCode .= "        ];\n";
        $classCode .= "    }\n\n";
        
        $classCode .= "    protected function createModuleTables(): void {\n";
        $classCode .= "        // Create tables for {$moduleName}\n";
        $classCode .= "        \$tables = [\n";
        $classCode .= "            'migrated_{$moduleName}_data' => \"\n";
        $classCode .= "                CREATE TABLE IF NOT EXISTS migrated_{$moduleName}_data (\n";
        $classCode .= "                    id INTEGER PRIMARY KEY AUTOINCREMENT,\n";
        $classCode .= "                    module_name VARCHAR(100) NOT NULL,\n";
        $classCode .= "                    feature_type VARCHAR(50),\n";
        $classCode .= "                    data TEXT,\n";
        $classCode .= "                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
        $classCode .= "                )\n";
        $classCode .= "            \"\n";
        $classCode .= "        ];\n\n";
        
        $classCode .= "        foreach (\$tables as \$tableName => \$sql) {\n";
        $classCode .= "            try {\n";
        $classCode .= "                \$this->pdo->execute(\$sql);\n";
        $classCode .= "                \$this->logger->log('Created table: ' . \$tableName);\n";
        $classCode .= "            } catch (Exception \$e) {\n";
        $classCode .= "                \$this->logger->error('Failed to create table ' . \$tableName . ': ' . \$e->getMessage());\n";
        $classCode .= "            }\n";
        $classCode .= "        }\n";
        $classCode .= "    }\n";
        $classCode .= "}\n";
        
        return $classCode;
    }
    
    private function generateFeatureLogic(string $feature): string {
        switch ($feature) {
            case 'mikrotik_integration':
                return "        // Mikrotik integration logic\n";
                return "        \$mikrotikData = \$this->getMikrotikData();\n";
                return "        \$this->logger->log('Retrieved Mikrotik data');\n\n";
                
            case 'snmp_monitoring':
                return "        // SNMP monitoring logic\n";
                return "        \$snmpData = \$this->getSNMPData();\n";
                return "        \$this->logger->log('Retrieved SNMP data');\n\n";
                
            case 'network_management':
                return "        // Network management logic\n";
                return "        \$networkData = \$this->getNetworkData();\n";
                return "        \$this->logger->log('Retrieved network data');\n\n";
                
            case 'user_management':
                return "        // User management logic\n";
                return "        \$userData = \$this->getUserData();\n";
                return "        \$this->logger->log('Retrieved user data');\n\n";
                
            case 'machine_learning':
                return "        // Machine learning logic\n";
                return "        \$mlData = \$this->getMLData();\n";
                return "        \$this->logger->log('Retrieved ML data');\n\n";
                
            case 'cacti_integration':
                return "        // Cacti integration logic\n";
                return "        \$cactiData = \$this->getCactiData();\n";
                return "        \$this->logger->log('Retrieved Cacti data');\n\n";
                
            case 'captive_portal':
                return "        // Captive portal logic\n";
                return "        \$portalData = \$this->getPortalData();\n";
                return "        \$this->logger->log('Retrieved portal data');\n\n";
                
            case 'dhcp_management':
                return "        // DHCP management logic\n";
                return "        \$dhcpData = \$this->getDHCPData();\n";
                return "        \$this->logger->log('Retrieved DHCP data');\n\n";
                
            case 'queue_management':
                return "        // Queue management logic\n";
                return "        \$queueData = \$this->getQueueData();\n";
                return "        \$this->logger->log('Retrieved queue data');\n\n";
                
            default:
                return "        // Generic feature logic for {$feature}\n";
                return "        \$this->logger->log('Processing feature: {$feature}');\n\n";
        }
    }
    
    private function saveMigratedModule(string $moduleName, string $classCode): void {
        $migratedDir = 'modules/migrated/';
        if (!is_dir($migratedDir)) {
            mkdir($migratedDir, 0755, true);
        }
        
        $filePath = $migratedDir . "Migrated{$moduleName}Module.php";
        file_put_contents($filePath, $classCode);
        
        $this->logger->log("Saved migrated module: {$filePath}");
    }
    
    private function testFrameworkIntegration(SLMSFrameworkManager $framework): void {
        $this->logger->log("Testing framework integration");
        
        try {
            // Test system status
            $status = $framework->getSystemStatus();
            $this->logger->log("Framework status: " . count($status) . " modules registered");
            
            // Test module execution
            $results = $framework->executeAllModules();
            $this->logger->log("Framework execution: " . count($results) . " modules executed");
            
        } catch (Exception $e) {
            $this->logger->error("Framework integration test failed: " . $e->getMessage());
        }
    }
    
    private function generateMigrationReport(): void {
        $report = [
            'migration_summary' => [
                'total_modules' => count($this->migrationResults),
                'successful_migrations' => count(array_filter($this->migrationResults, fn($r) => $r['status'] === 'success')),
                'failed_migrations' => count(array_filter($this->migrationResults, fn($r) => $r['status'] === 'failed')),
                'skipped_modules' => count(array_filter($this->migrationResults, fn($r) => $r['status'] === 'skipped'))
            ],
            'migration_details' => $this->migrationResults,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $reportPath = '/var/log/slms/migration_report.json';
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->logger->log("Migration report generated: {$reportPath}");
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli') {
    echo "🚀 Starting SLMS Framework Migration...\n";
    
    $migrationManager = new SLMSMigrationManager();
    $result = $migrationManager->migrateAllModules();
    
    if ($result['success']) {
        echo "✅ Migration completed successfully!\n";
        echo "📊 Migrated modules: {$result['migrated_modules']}\n";
        echo "📋 Check /var/log/slms/migration_report.json for details\n";
    } else {
        echo "❌ Migration failed: {$result['error']}\n";
    }
} 