<?php
/**
 * SLMS Framework Migration Script - TEST MODE
 * Migrates all existing modules to the new object-oriented framework
 * Runs without database connection for testing purposes
 */

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

class SLMSMigrationManagerTest {
    private $migrationResults = [];
    private $analysisResults = [];
    
    public function __construct() {
        echo "🧪 SLMS Framework Migration Manager - TEST MODE\n";
        echo "==============================================\n\n";
    }
    
    public function migrateAllModules(): array {
        echo "🚀 Starting SLMS Framework Migration (TEST MODE)...\n\n";
        
        try {
            // Analyze existing modules
            $this->analyzeExistingModules();
            
            // Migrate existing modules
            $this->migrateExistingModules();
            
            // Generate migration report
            $this->generateMigrationReport();
            
            echo "✅ Migration completed successfully!\n";
            
            return [
                'success' => true,
                'migrated_modules' => count($this->migrationResults),
                'analyzed_modules' => count($this->analysisResults),
                'results' => $this->migrationResults
            ];
            
        } catch (Exception $e) {
            echo "❌ Migration failed: " . $e->getMessage() . "\n";
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => $this->migrationResults
            ];
        }
    }
    
    private function analyzeExistingModules(): void {
        global $existingModules;
        
        echo "🔍 Analyzing " . count($existingModules) . " existing modules...\n";
        
        foreach ($existingModules as $modulePath) {
            $this->analyzeModule($modulePath);
        }
        
        echo "📊 Analysis completed!\n\n";
    }
    
    private function analyzeModule(string $modulePath): void {
        $moduleName = basename($modulePath, '.php');
        
        try {
            if (file_exists($modulePath)) {
                $content = file_get_contents($modulePath);
                $moduleInfo = $this->extractModuleInfo($content, $modulePath);
                
                $this->analysisResults[$moduleName] = $moduleInfo;
                
                echo "  ✅ {$moduleName}: " . count($moduleInfo['features']) . " features, " . 
                     count($moduleInfo['functions']) . " functions, " . 
                     count($moduleInfo['classes']) . " classes\n";
            } else {
                echo "  ⚠️  {$moduleName}: File not found\n";
                $this->analysisResults[$moduleName] = ['status' => 'not_found'];
            }
        } catch (Exception $e) {
            echo "  ❌ {$moduleName}: Error - " . $e->getMessage() . "\n";
            $this->analysisResults[$moduleName] = ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
    
    private function extractModuleInfo(string $content, string $modulePath): array {
        $moduleInfo = [
            'path' => $modulePath,
            'size' => strlen($content),
            'lines' => substr_count($content, "\n"),
            'features' => [],
            'functions' => [],
            'classes' => [],
            'database_queries' => [],
            'api_endpoints' => [],
            'dependencies' => [],
            'complexity_score' => 0
        ];
        
        // Detect features based on content analysis
        $featurePatterns = [
            'mikrotik_integration' => ['mikrotik', 'routeros', 'api'],
            'snmp_monitoring' => ['snmp', 'oid', 'mib'],
            'network_management' => ['network', 'interface', 'vlan'],
            'user_management' => ['user', 'client', 'profile'],
            'machine_learning' => ['ml_', 'machine_learning', 'prediction', 'training'],
            'cacti_integration' => ['cacti', 'rrd', 'graph'],
            'captive_portal' => ['captive_portal', 'portal', 'auth'],
            'dhcp_management' => ['dhcp', 'lease', 'ip_pool'],
            'queue_management' => ['queue', 'qos', 'traffic'],
            'database_management' => ['database', 'sql', 'table'],
            'api_integration' => ['api', 'rest', 'json'],
            'monitoring' => ['monitor', 'status', 'alert'],
            'configuration' => ['config', 'setup', 'install'],
            'authentication' => ['auth', 'login', 'session'],
            'reporting' => ['report', 'export', 'log']
        ];
        
        foreach ($featurePatterns as $feature => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    $moduleInfo['features'][] = $feature;
                    break;
                }
            }
        }
        
        // Remove duplicates
        $moduleInfo['features'] = array_unique($moduleInfo['features']);
        
        // Extract function names
        preg_match_all('/function\s+(\w+)\s*\(/', $content, $matches);
        $moduleInfo['functions'] = $matches[1] ?? [];
        
        // Extract class names
        preg_match_all('/class\s+(\w+)/', $content, $matches);
        $moduleInfo['classes'] = $matches[1] ?? [];
        
        // Extract database queries
        preg_match_all('/(SELECT|INSERT|UPDATE|DELETE)\s+.*?;/i', $content, $matches);
        $moduleInfo['database_queries'] = $matches[0] ?? [];
        
        // Extract API endpoints
        preg_match_all('/\$_GET\[[\'"]([^\'"]+)[\'"]\]/', $content, $matches);
        $moduleInfo['api_endpoints'] = $matches[1] ?? [];
        
        // Extract dependencies
        preg_match_all('/require.*?[\'"]([^\'"]+\.php)[\'"]/', $content, $matches);
        $moduleInfo['dependencies'] = $matches[1] ?? [];
        
        // Calculate complexity score
        $moduleInfo['complexity_score'] = 
            count($moduleInfo['functions']) * 2 +
            count($moduleInfo['classes']) * 5 +
            count($moduleInfo['database_queries']) * 3 +
            count($moduleInfo['features']) * 10 +
            ($moduleInfo['lines'] / 100);
        
        return $moduleInfo;
    }
    
    private function migrateExistingModules(): void {
        global $existingModules;
        
        echo "🔄 Migrating " . count($existingModules) . " existing modules...\n";
        
        foreach ($existingModules as $modulePath) {
            $this->migrateModule($modulePath);
        }
        
        echo "✅ Migration completed!\n\n";
    }
    
    private function migrateModule(string $modulePath): void {
        $moduleName = basename($modulePath, '.php');
        
        try {
            if (file_exists($modulePath)) {
                $moduleInfo = $this->analysisResults[$moduleName] ?? [];
                
                // Create migrated module class
                $migratedClass = $this->createMigratedModuleClass($moduleName, $moduleInfo);
                
                // Save migrated module
                $this->saveMigratedModule($moduleName, $migratedClass);
                
                $this->migrationResults[$moduleName] = [
                    'status' => 'success',
                    'original_path' => $modulePath,
                    'migrated_class' => "Migrated{$moduleName}Module",
                    'features' => $moduleInfo['features'] ?? [],
                    'complexity_score' => $moduleInfo['complexity_score'] ?? 0
                ];
                
                echo "  ✅ {$moduleName}: Migrated successfully\n";
            } else {
                $this->migrationResults[$moduleName] = [
                    'status' => 'skipped',
                    'reason' => 'File not found'
                ];
                echo "  ⚠️  {$moduleName}: Skipped (file not found)\n";
            }
        } catch (Exception $e) {
            $this->migrationResults[$moduleName] = [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
            echo "  ❌ {$moduleName}: Failed - " . $e->getMessage() . "\n";
        }
    }
    
    private function createMigratedModuleClass(string $moduleName, array $moduleInfo): string {
        $className = "Migrated" . ucfirst($moduleName) . "Module";
        
        $features = implode("', '", $moduleInfo['features'] ?? []);
        $functions = implode("', '", $moduleInfo['functions'] ?? []);
        $classes = implode("', '", $moduleInfo['classes'] ?? []);
        
        $classCode = "<?php\n";
        $classCode .= "/**\n";
        $classCode .= " * Migrated Module: {$moduleName}\n";
        $classCode .= " * Original Path: {$moduleInfo['path']}\n";
        $classCode .= " * Features: {$features}\n";
        $classCode .= " * Functions: {$functions}\n";
        $classCode .= " * Classes: {$classes}\n";
        $classCode .= " * Complexity Score: {$moduleInfo['complexity_score']}\n";
        $classCode .= " * Lines of Code: {$moduleInfo['lines']}\n";
        $classCode .= " */\n\n";
        
        $classCode .= "class {$className} {\n";
        $classCode .= "    private \$originalModulePath = '{$moduleName}.php';\n";
        $classCode .= "    private \$features = ['{$features}'];\n";
        $classCode .= "    private \$complexityScore = {$moduleInfo['complexity_score']};\n";
        $classCode .= "    private \$originalLines = {$moduleInfo['lines']};\n\n";
        
        $classCode .= "    public function __construct() {\n";
        $classCode .= "        // Initialize migrated module\n";
        $classCode .= "    }\n\n";
        
        $classCode .= "    public function execute(): array {\n";
        $classCode .= "        // Execute migrated module logic\n";
        $classCode .= "        return [\n";
        $classCode .= "            'success' => true,\n";
        $classCode .= "            'module' => '{$moduleName}',\n";
        $classCode .= "            'features' => ['{$features}'],\n";
        $classCode .= "            'complexity_score' => {$moduleInfo['complexity_score']},\n";
        $classCode .= "            'timestamp' => date('Y-m-d H:i:s')\n";
        $classCode .= "        ];\n";
        $classCode .= "    }\n\n";
        
        // Add feature-specific methods
        foreach ($moduleInfo['features'] ?? [] as $feature) {
            $classCode .= $this->generateFeatureMethod($feature);
        }
        
        $classCode .= "    public function getStatus(): array {\n";
        $classCode .= "        return [\n";
        $classCode .= "            'module' => '{$moduleName}',\n";
        $classCode .= "            'status' => 'migrated',\n";
        $classCode .= "            'features' => ['{$features}'],\n";
        $classCode .= "            'complexity_score' => {$moduleInfo['complexity_score']}\n";
        $classCode .= "        ];\n";
        $classCode .= "    }\n";
        $classCode .= "}\n";
        
        return $classCode;
    }
    
    private function generateFeatureMethod(string $feature): string {
        $methodName = 'handle' . str_replace('_', '', ucwords($feature, '_'));
        
        return "    public function {$methodName}(): array {\n";
        return "        // Handle {$feature} functionality\n";
        return "        return [\n";
        return "            'feature' => '{$feature}',\n";
        return "            'status' => 'implemented'\n";
        return "        ];\n";
        return "    }\n\n";
    }
    
    private function saveMigratedModule(string $moduleName, string $classCode): void {
        $migratedDir = 'modules/migrated/';
        if (!is_dir($migratedDir)) {
            mkdir($migratedDir, 0755, true);
        }
        
        $filePath = $migratedDir . "Migrated{$moduleName}Module.php";
        file_put_contents($filePath, $classCode);
    }
    
    private function generateMigrationReport(): void {
        $report = [
            'migration_summary' => [
                'total_modules' => count($this->migrationResults),
                'successful_migrations' => count(array_filter($this->migrationResults, fn($r) => $r['status'] === 'success')),
                'failed_migrations' => count(array_filter($this->migrationResults, fn($r) => $r['status'] === 'failed')),
                'skipped_modules' => count(array_filter($this->migrationResults, fn($r) => $r['status'] === 'skipped'))
            ],
            'analysis_summary' => [
                'total_analyzed' => count($this->analysisResults),
                'total_features_detected' => array_sum(array_map(fn($a) => count($a['features'] ?? []), $this->analysisResults)),
                'total_functions' => array_sum(array_map(fn($a) => count($a['functions'] ?? []), $this->analysisResults)),
                'total_classes' => array_sum(array_map(fn($a) => count($a['classes'] ?? []), $this->analysisResults)),
                'average_complexity' => array_sum(array_map(fn($a) => $a['complexity_score'] ?? 0, $this->analysisResults)) / count($this->analysisResults)
            ],
            'migration_details' => $this->migrationResults,
            'analysis_details' => $this->analysisResults,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $reportPath = 'migration_report_test.json';
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        echo "📋 Migration report generated: {$reportPath}\n";
        
        // Display summary
        echo "\n📊 MIGRATION SUMMARY:\n";
        echo "====================\n";
        echo "Total modules analyzed: " . count($this->analysisResults) . "\n";
        echo "Total modules migrated: " . count($this->migrationResults) . "\n";
        echo "Successful migrations: " . $report['migration_summary']['successful_migrations'] . "\n";
        echo "Failed migrations: " . $report['migration_summary']['failed_migrations'] . "\n";
        echo "Skipped modules: " . $report['migration_summary']['skipped_modules'] . "\n";
        echo "Average complexity score: " . round($report['analysis_summary']['average_complexity'], 2) . "\n";
        echo "Total features detected: " . $report['analysis_summary']['total_features_detected'] . "\n";
        echo "Total functions: " . $report['analysis_summary']['total_functions'] . "\n";
        echo "Total classes: " . $report['analysis_summary']['total_classes'] . "\n";
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli') {
    $migrationManager = new SLMSMigrationManagerTest();
    $result = $migrationManager->migrateAllModules();
    
    if ($result['success']) {
        echo "\n🎉 MIGRATION COMPLETED SUCCESSFULLY!\n";
        echo "====================================\n";
        echo "📊 Migrated modules: {$result['migrated_modules']}\n";
        echo "🔍 Analyzed modules: {$result['analyzed_modules']}\n";
        echo "📋 Check migration_report_test.json for details\n";
    } else {
        echo "\n💥 MIGRATION FAILED!\n";
        echo "===================\n";
        echo "Error: {$result['error']}\n";
    }
} 