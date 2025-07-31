<?php
/**
 * SLMS Core Framework v1.2.0
 * Object-Oriented Framework for System Lifecycle Management
 * 
 * This framework provides the foundation for all SLMS modules,
 * ensuring proper integration between old and new components.
 */

// Core Framework Interfaces
interface SLMSModuleInterface {
    public function initialize(): void;
    public function execute(): array;
    public function getStatus(): array;
    public function getVersion(): string;
}

interface SLMSDatabaseInterface {
    public function connect(): void;
    public function query(string $sql, array $params = []): array;
    public function execute(string $sql, array $params = []): bool;
    public function getLastInsertId(): int;
}

interface SLMSLoggerInterface {
    public function log(string $message, string $level = 'INFO'): void;
    public function error(string $message): void;
    public function warning(string $message): void;
    public function debug(string $message): void;
}

// Core Framework Classes
abstract class SLMSModule implements SLMSModuleInterface {
    protected $pdo;
    protected $logger;
    protected $config;
    protected $moduleName;
    protected $version;
    protected $dependencies = [];
    
    public function __construct(SLMSDatabaseInterface $database, SLMSLoggerInterface $logger, array $config = []) {
        $this->pdo = $database;
        $this->logger = $logger;
        $this->config = $config;
        $this->moduleName = static::class;
        $this->version = '1.2.0';
    }
    
    abstract protected function performModuleOperation(): array;
    
    public function initialize(): void {
        $this->logger->log("Initializing {$this->moduleName} v{$this->version}");
        $this->validateDependencies();
        $this->createModuleTables();
    }
    
    public function execute(): array {
        $this->logger->log("Executing {$this->moduleName}");
        return $this->performModuleOperation();
    }
    
    public function getStatus(): array {
        return [
            'module' => $this->moduleName,
            'version' => $this->version,
            'status' => 'active',
            'dependencies' => $this->dependencies,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    public function getVersion(): string {
        return $this->version;
    }
    
    protected function validateDependencies(): void {
        foreach ($this->dependencies as $dependency) {
            if (!class_exists($dependency)) {
                throw new Exception("Missing dependency: {$dependency}");
            }
        }
    }
    
    protected function createModuleTables(): void {
        // Override in child classes to create specific tables
    }
    
    protected function getConfig(string $key, $default = null) {
        return $this->config[$key] ?? $default;
    }
}

class SLMSDatabase implements SLMSDatabaseInterface {
    private $connection;
    private $config;
    
    public function __construct(array $config) {
        $this->config = $config;
    }
    
    public function connect(): void {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function query(string $sql, array $params = []): array {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function execute(string $sql, array $params = []): bool {
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function getLastInsertId(): int {
        return $this->connection->lastInsertId();
    }
}

class SLMSLogger implements SLMSLoggerInterface {
    private $logFile;
    private $logLevel;
    
    public function __construct(string $logFile = '/var/log/slms/slms_core.log', string $logLevel = 'INFO') {
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public function log(string $message, string $level = 'INFO'): void {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        error_log($logMessage, 3, $this->logFile);
        
        // Also output to console for debugging
        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        }
    }
    
    public function error(string $message): void {
        $this->log($message, 'ERROR');
    }
    
    public function warning(string $message): void {
        $this->log($message, 'WARNING');
    }
    
    public function debug(string $message): void {
        if ($this->logLevel === 'DEBUG') {
            $this->log($message, 'DEBUG');
        }
    }
}

// Module Registry and Manager
class SLMSModuleRegistry {
    private $modules = [];
    private $database;
    private $logger;
    private $config;
    
    public function __construct(SLMSDatabaseInterface $database, SLMSLoggerInterface $logger, array $config = []) {
        $this->database = $database;
        $this->logger = $logger;
        $this->config = $config;
    }
    
    public function registerModule(string $name, SLMSModuleInterface $module): void {
        $this->modules[$name] = $module;
        $this->logger->log("Registered module: {$name}");
    }
    
    public function getModule(string $name): ?SLMSModuleInterface {
        return $this->modules[$name] ?? null;
    }
    
    public function getAllModules(): array {
        return $this->modules;
    }
    
    public function executeModule(string $name): array {
        $module = $this->getModule($name);
        if (!$module) {
            throw new Exception("Module not found: {$name}");
        }
        
        return $module->execute();
    }
    
    public function executeAllModules(): array {
        $results = [];
        foreach ($this->modules as $name => $module) {
            try {
                $results[$name] = $module->execute();
            } catch (Exception $e) {
                $this->logger->error("Module {$name} failed: " . $e->getMessage());
                $results[$name] = ['error' => $e->getMessage()];
            }
        }
        return $results;
    }
    
    public function getSystemStatus(): array {
        $status = [];
        foreach ($this->modules as $name => $module) {
            $status[$name] = $module->getStatus();
        }
        return $status;
    }
}

// Enhanced Network Discovery Module (Migrated)
class EnhancedNetworkDiscoveryModule extends SLMSModule {
    private $discoveryEngine;
    
    public function __construct(SLMSDatabaseInterface $database, SLMSLoggerInterface $logger, array $config = []) {
        parent::__construct($database, $logger, $config);
        $this->dependencies = ['NetworkDiscovery'];
        $this->discoveryEngine = new NetworkDiscovery();
    }
    
    protected function performModuleOperation(): array {
        $this->logger->log("Starting enhanced network discovery");
        
        try {
            // Run discovery scan
            $stats = $this->discoveryEngine->runDiscoveryScan();
            
            // Get discovered devices with real hostnames
            $devices = $this->discoveryEngine->getDiscoveredDevices();
            
            // Get device statistics
            $deviceStats = $this->discoveryEngine->getDeviceStatistics();
            
            return [
                'success' => true,
                'devices_discovered' => count($devices),
                'statistics' => $deviceStats,
                'devices' => $devices,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $this->logger->error("Network discovery failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    protected function createModuleTables(): void {
        // Create enhanced discovery tables
        $tables = [
            'enhanced_discovered_devices' => "
                CREATE TABLE IF NOT EXISTS enhanced_discovered_devices (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    hostname VARCHAR(255) NOT NULL,
                    real_hostname VARCHAR(255),
                    ip_address VARCHAR(45) NOT NULL,
                    mac_address VARCHAR(17),
                    device_type VARCHAR(50),
                    vendor VARCHAR(100),
                    model VARCHAR(100),
                    os_version VARCHAR(100),
                    status VARCHAR(20) DEFAULT 'online',
                    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    snmp_community VARCHAR(50),
                    mndp_data TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'enhanced_network_interfaces' => "
                CREATE TABLE IF NOT EXISTS enhanced_network_interfaces (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    device_id INTEGER,
                    interface_name VARCHAR(100) NOT NULL,
                    real_interface_name VARCHAR(100),
                    interface_type VARCHAR(50),
                    speed INTEGER,
                    duplex VARCHAR(20),
                    status VARCHAR(20),
                    ip_address VARCHAR(45),
                    mac_address VARCHAR(17),
                    description TEXT,
                    transfer_rx BIGINT DEFAULT 0,
                    transfer_tx BIGINT DEFAULT 0,
                    transfer_rx_rate FLOAT DEFAULT 0,
                    transfer_tx_rate FLOAT DEFAULT 0,
                    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (device_id) REFERENCES enhanced_discovered_devices(id)
                )
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            try {
                $this->pdo->execute($sql);
                $this->logger->log("Created table: {$tableName}");
            } catch (Exception $e) {
                $this->logger->error("Failed to create table {$tableName}: " . $e->getMessage());
            }
        }
    }
}

// Enhanced AI Research Module (Migrated)
class EnhancedAIResearchModule extends SLMSModule {
    private $researchEngine;
    
    public function __construct(SLMSDatabaseInterface $database, SLMSLoggerInterface $logger, array $config = []) {
        parent::__construct($database, $logger, $config);
        $this->dependencies = ['AIResearchEngine'];
        $this->researchEngine = new AIResearchEngine();
    }
    
    protected function performModuleOperation(): array {
        $this->logger->log("Starting enhanced AI research");
        
        try {
            // Get current network data
            $networkData = $this->getCurrentNetworkData();
            
            // Conduct AI research
            $researchResults = $this->researchEngine->conductAIResearch($networkData);
            
            // Get research statistics
            $researchStats = $this->researchEngine->getResearchStatistics();
            
            return [
                'success' => true,
                'research_findings' => count($researchResults),
                'statistics' => $researchStats,
                'results' => $researchResults,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $this->logger->error("AI research failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getCurrentNetworkData(): array {
        try {
            $devices = $this->pdo->query("SELECT * FROM enhanced_discovered_devices ORDER BY hostname");
            $interfaces = $this->pdo->query("SELECT * FROM enhanced_network_interfaces ORDER BY device_id, interface_name");
            
            return [
                'devices' => $devices,
                'interfaces' => $interfaces
            ];
        } catch (Exception $e) {
            $this->logger->error("Failed to get network data: " . $e->getMessage());
            return [];
        }
    }
    
    protected function createModuleTables(): void {
        // Create enhanced research tables
        $tables = [
            'enhanced_ai_research_findings' => "
                CREATE TABLE IF NOT EXISTS enhanced_ai_research_findings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    research_type VARCHAR(100) NOT NULL,
                    ai_provider VARCHAR(50) NOT NULL,
                    model_used VARCHAR(100),
                    research_query TEXT NOT NULL,
                    research_response TEXT NOT NULL,
                    confidence_score FLOAT DEFAULT 0.0,
                    relevance_score FLOAT DEFAULT 0.0,
                    implementation_status VARCHAR(20) DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'enhanced_adaptation_recommendations' => "
                CREATE TABLE IF NOT EXISTS enhanced_adaptation_recommendations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    recommendation_type VARCHAR(100) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT NOT NULL,
                    priority VARCHAR(20) DEFAULT 'medium',
                    impact_score FLOAT DEFAULT 0.0,
                    implementation_complexity VARCHAR(20) DEFAULT 'medium',
                    ai_source VARCHAR(50),
                    status VARCHAR(20) DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    implemented_at TIMESTAMP NULL
                )
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            try {
                $this->pdo->execute($sql);
                $this->logger->log("Created table: {$tableName}");
            } catch (Exception $e) {
                $this->logger->error("Failed to create table {$tableName}: " . $e->getMessage());
            }
        }
    }
}

// Migrated Legacy Modules
class MigratedMikrotikAPIModule extends SLMSModule {
    private $mikrotikAPI;
    
    public function __construct(SLMSDatabaseInterface $database, SLMSLoggerInterface $logger, array $config = []) {
        parent::__construct($database, $logger, $config);
        $this->dependencies = ['MikrotikAPI'];
        $this->mikrotikAPI = new MikrotikAPI();
    }
    
    protected function performModuleOperation(): array {
        $this->logger->log("Executing migrated Mikrotik API module");
        
        try {
            // Migrate existing Mikrotik API functionality
            $result = $this->mikrotikAPI->getSystemResources();
            
            return [
                'success' => true,
                'mikrotik_data' => $result,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $this->logger->error("Mikrotik API failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

class MigratedSNMPModule extends SLMSModule {
    private $snmpHelper;
    
    public function __construct(SLMSDatabaseInterface $database, SLMSLoggerInterface $logger, array $config = []) {
        parent::__construct($database, $logger, $config);
        $this->dependencies = ['SNMPOIDHelper'];
        $this->snmpHelper = new SNMPOIDHelper();
    }
    
    protected function performModuleOperation(): array {
        $this->logger->log("Executing migrated SNMP module");
        
        try {
            // Migrate existing SNMP functionality
            $devices = $this->getSNMPDevices();
            $results = [];
            
            foreach ($devices as $device) {
                $snmpData = $this->snmpHelper->getDeviceInfo($device['ip_address']);
                $results[] = $snmpData;
            }
            
            return [
                'success' => true,
                'snmp_devices' => count($results),
                'data' => $results,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $this->logger->error("SNMP module failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getSNMPDevices(): array {
        return $this->pdo->query("SELECT * FROM discovered_devices WHERE device_type IN ('router', 'switch')");
    }
}

// Main SLMS Framework Manager
class SLMSFrameworkManager {
    private $registry;
    private $database;
    private $logger;
    private $config;
    
    public function __construct(array $config = []) {
        $this->config = $config;
        $this->logger = new SLMSLogger();
        $this->database = new SLMSDatabase($config['database'] ?? []);
        $this->registry = new SLMSModuleRegistry($this->database, $this->logger, $config);
        
        $this->initializeFramework();
    }
    
    private function initializeFramework(): void {
        $this->logger->log("Initializing SLMS Framework v1.2.0");
        
        try {
            $this->database->connect();
            $this->registerAllModules();
            $this->logger->log("SLMS Framework initialized successfully");
        } catch (Exception $e) {
            $this->logger->error("Framework initialization failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function registerAllModules(): void {
        // Register enhanced modules
        $this->registry->registerModule('enhanced_network_discovery', 
            new EnhancedNetworkDiscoveryModule($this->database, $this->logger, $this->config));
        
        $this->registry->registerModule('enhanced_ai_research', 
            new EnhancedAIResearchModule($this->database, $this->logger, $this->config));
        
        // Register migrated legacy modules
        $this->registry->registerModule('migrated_mikrotik_api', 
            new MigratedMikrotikAPIModule($this->database, $this->logger, $this->config));
        
        $this->registry->registerModule('migrated_snmp', 
            new MigratedSNMPModule($this->database, $this->logger, $this->config));
        
        $this->logger->log("All modules registered successfully");
    }
    
    public function executeAllModules(): array {
        return $this->registry->executeAllModules();
    }
    
    public function executeModule(string $moduleName): array {
        return $this->registry->executeModule($moduleName);
    }
    
    public function getSystemStatus(): array {
        return $this->registry->getSystemStatus();
    }
    
    public function getModule(string $moduleName): ?SLMSModuleInterface {
        return $this->registry->getModule($moduleName);
    }
    
    public function getAllModules(): array {
        return $this->registry->getAllModules();
    }
}

// Framework initialization function
function initializeSLMSFramework(array $config = []): SLMSFrameworkManager {
    return new SLMSFrameworkManager($config);
} 