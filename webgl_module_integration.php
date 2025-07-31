<?php
// Start output buffering to prevent header issues
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/modules/helpers/auth_helper.php';

// Require login
require_login();

// Clear any output that might have been generated
ob_clean();

header('Content-Type: application/json');

// Module definitions
$moduleDefinitions = [
    'dashboard' => [
        'table' => 'dashboard_stats',
        'fields' => ['id'],
        'required_fields' => [],
        'functions' => ['list', 'stats', 'overview', 'alerts', 'performance']
    ],
    'clients' => [
        'table' => 'clients',
        'fields' => ['id', 'name', 'first_name', 'last_name', 'pesel', 'phone', 'email', 'address', 'city', 'postal_code', 'company_name', 'nip', 'notes', 'status', 'created_at', 'updated_at'],
        'required_fields' => ['name'],
        'functions' => ['list', 'add', 'edit', 'delete', 'search', 'export', 'import']
    ],
    'devices' => [
        'table' => 'devices',
        'fields' => ['id', 'name', 'type', 'model', 'ip_address', 'mac_address', 'location', 'client_id', 'network_id', 'status', 'last_seen', 'notes', 'created_at', 'updated_at', 'position_x', 'position_y', 'position_z', 'device_type', 'vendor', 'serial_number', 'firmware_version', 'description'],
        'required_fields' => ['name', 'type'],
        'functions' => ['list', 'add', 'edit', 'delete', 'monitor', 'ping', 'snmp', 'export', 'import']
    ],
    'networks' => [
        'table' => 'networks',
        'fields' => ['id', 'name', 'subnet', 'gateway', 'dns', 'vlan', 'created_at', 'active'],
        'required_fields' => ['name', 'subnet'],
        'functions' => ['list', 'add', 'edit', 'delete', 'scan', 'dhcp', 'export', 'import']
    ],
    'invoices' => [
        'table' => 'invoices',
        'fields' => ['id', 'client_id', 'amount', 'status', 'due_date', 'created_at'],
        'required_fields' => ['client_id', 'amount'],
        'functions' => ['list', 'add', 'edit', 'delete', 'generate', 'send', 'export', 'import']
    ],
    'users' => [
        'table' => 'users',
        'fields' => ['id', 'username', 'email', 'role', 'status', 'created_at'],
        'required_fields' => ['username', 'email'],
        'functions' => ['list', 'add', 'edit', 'delete', 'login', 'logout', 'export', 'import']
    ],
    'services' => [
        'table' => 'services',
        'fields' => ['id', 'name', 'description', 'price', 'status', 'created_at'],
        'required_fields' => ['name', 'price'],
        'functions' => ['list', 'add', 'edit', 'delete', 'activate', 'deactivate', 'export', 'import']
    ],
    'alerts' => [
        'table' => 'network_alerts',
        'fields' => ['id', 'device_id', 'alert_type', 'details', 'timestamp'],
        'required_fields' => ['device_id', 'alert_type'],
        'functions' => ['list', 'add', 'edit', 'delete', 'acknowledge', 'resolve', 'export', 'import']
    ],
    'mikrotik' => [
        'table' => 'mikrotik_devices',
        'fields' => ['id', 'name', 'ip_address', 'username', 'api_port', 'api_ssl', 'status', 'last_seen', 'notes', 'created_at', 'updated_at'],
        'required_fields' => ['name', 'ip_address'],
        'functions' => ['list', 'add', 'edit', 'delete', 'connect', 'backup', 'monitor_mikrotik', 'export', 'import']
    ],
    'dhcp' => [
        'table' => 'dhcp_servers',
        'fields' => ['id', 'name', 'ip_address', 'subnet', 'gateway', 'dns_servers', 'lease_time', 'status', 'created_at', 'updated_at'],
        'required_fields' => ['name', 'ip_address', 'subnet'],
        'functions' => ['list', 'add', 'edit', 'delete', 'leases', 'reservations', 'export', 'import']
    ],
    'snmp' => [
        'table' => 'snmp_devices',
        'fields' => ['id', 'device_id', 'ip_address', 'community', 'version', 'timeout', 'retries', 'status', 'last_poll', 'created_at', 'updated_at'],
        'required_fields' => ['ip_address', 'community'],
        'functions' => ['list', 'add', 'edit', 'delete', 'poll', 'walk', 'trap', 'export', 'import']
    ],
    'vlans' => [
        'table' => 'vlans',
        'fields' => ['id', 'vlan_id', 'name', 'description', 'subnet', 'gateway', 'dhcp_server', 'status', 'created_at', 'updated_at'],
        'required_fields' => ['vlan_id', 'name'],
        'functions' => ['list', 'add', 'edit', 'delete', 'scan_vlan', 'monitor_vlan', 'export', 'import']
    ],
    'ip_ranges' => [
        'table' => 'ip_ranges',
        'fields' => ['id', 'name', 'start_ip', 'end_ip', 'subnet_mask', 'gateway', 'dns_servers', 'purpose', 'status', 'created_at', 'updated_at'],
        'required_fields' => ['name', 'start_ip', 'end_ip'],
        'functions' => ['list', 'add', 'edit', 'delete', 'ping_range', 'export', 'import']
    ],
    'scan_jobs' => [
        'table' => 'scan_jobs',
        'fields' => ['id', 'name', 'type', 'targets', 'parameters', 'status', 'progress', 'results', 'started_at', 'completed_at', 'created_at'],
        'required_fields' => ['name', 'type', 'targets'],
        'functions' => ['list', 'add', 'edit', 'delete', 'start', 'stop', 'results', 'export', 'import']
    ],
    'client_devices' => [
        'table' => 'client_devices',
        'fields' => ['id', 'name', 'type', 'ip_address', 'mac_address', 'client_id', 'location', 'model', 'serial_number', 'purchase_date', 'warranty_expiry', 'status', 'notes', 'created_at', 'updated_at'],
        'required_fields' => ['name', 'type', 'ip_address'],
        'functions' => ['list', 'add', 'edit', 'delete', 'ping_range', 'monitor_vlan', 'backup', 'export', 'import']
    ],
    'core_devices' => [
        'table' => 'core_devices',
        'fields' => ['id', 'name', 'type', 'ip_address', 'mac_address', 'location', 'rack_position', 'model', 'serial_number', 'purchase_date', 'warranty_expiry', 'status', 'uptime', 'cpu_usage', 'memory_usage', 'temperature', 'notes', 'created_at', 'updated_at'],
        'required_fields' => ['name', 'type', 'ip_address'],
        'functions' => ['list', 'add', 'edit', 'delete', 'ping_range', 'monitor_vlan', 'backup', 'config', 'export', 'import']
    ],
    'network_segments' => [
        'table' => 'network_segments',
        'fields' => ['id', 'name', 'type', 'vlan_id', 'subnet', 'gateway', 'dhcp_server', 'dns_servers', 'description', 'status', 'device_count', 'created_at', 'updated_at'],
        'required_fields' => ['name', 'type', 'subnet'],
        'functions' => ['list', 'add', 'edit', 'delete', 'scan_vlan', 'monitor_vlan', 'devices', 'export', 'import']
    ],
    'device_categories' => [
        'table' => 'device_categories',
        'fields' => ['id', 'name', 'description', 'parent_category', 'icon', 'color', 'sort_order', 'created_at', 'updated_at'],
        'required_fields' => ['name'],
        'functions' => ['list', 'add', 'edit', 'delete', 'devices', 'export', 'import']
    ]
];

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$module = $_GET['module'] ?? $_POST['module'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    $pdo = get_pdo();
    
    switch ($action) {
        case 'load_module':
            $response = loadModule($module);
            break;
            
        case 'get_module_functions':
            $response = getModuleFunctions($module);
            break;
            
        case 'execute_module_function':
            $response = executeModuleFunction($module, $_POST);
            break;
            
        case 'get_module_data':
            $response = getModuleData($module, $pdo);
            break;
            
        case 'list':
            $response = executeModuleFunction($module, ['function' => 'list']);
            break;
            
        case 'update_module_data':
            $response = updateModuleData($module, $pdo, $_POST);
            break;
            
        case 'delete_module_data':
            $response = deleteModuleData($module, $pdo, $_GET['id']);
            break;
            
        case 'import_module_data':
            $response = importModuleData($module, $pdo, $_POST);
            break;
            
        case 'export_module_data':
            $response = exportModuleData($module, $pdo, $_GET['format'] ?? 'json');
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Unknown action'];
    }
    
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

// Clear any output and send JSON response
ob_clean();
echo json_encode($response);
ob_end_flush();
exit;



function loadModule($moduleName) {
    global $moduleDefinitions;
    
    if (!isset($moduleDefinitions[$moduleName])) {
        return ['success' => false, 'message' => 'Module not found'];
    }
    
    $module = $moduleDefinitions[$moduleName];
    
    return [
        'success' => true,
        'data' => [
            'name' => $moduleName,
            'table' => $module['table'],
            'fields' => $module['fields'],
            'required_fields' => $module['required_fields'],
            'functions' => $module['functions']
        ]
    ];
}

function getModuleFunctions($moduleName) {
    global $moduleDefinitions;
    
    if (!isset($moduleDefinitions[$moduleName])) {
        return ['success' => false, 'message' => 'Module not found'];
    }
    
    $module = $moduleDefinitions[$moduleName];
    
    return [
        'success' => true,
        'data' => [
            'module' => $moduleName,
            'functions' => $module['functions']
        ]
    ];
}

function executeModuleFunction($moduleName, $data) {
    global $moduleDefinitions;
    
    if (!isset($moduleDefinitions[$moduleName])) {
        return ['success' => false, 'message' => 'Module not found'];
    }
    
    $function = $data['function'] ?? '';
    $module = $moduleDefinitions[$moduleName];
    
    // Debug: log what we're checking
    error_log("DEBUG: Module: $moduleName, Function: $function, Available functions: " . implode(',', $module['functions']));
    
    if (!in_array($function, $module['functions'])) {
        return ['success' => false, 'message' => 'Function not available for this module'];
    }
    
    try {
        $pdo = get_pdo();
        
        switch ($function) {
            case 'list':
                return listModuleData($pdo, $moduleName, $module);
            case 'add':
                return addModuleData($pdo, $moduleName, $module, $data);
            case 'edit':
                return editModuleData($pdo, $moduleName, $module, $data);
            case 'delete':
                return deleteModuleData($pdo, $moduleName, $data['id']);
            case 'search':
                return searchModuleData($pdo, $moduleName, $module, $data);
            case 'export':
                return exportModuleData($moduleName, $pdo, $data['format'] ?? 'json');
            case 'import':
                return importModuleData($moduleName, $pdo, $data);
            // Mikrotik specific functions
            case 'connect':
                return connectMikrotik($pdo, $moduleName, $data);
            case 'backup':
                return backupMikrotik($pdo, $moduleName, $data);
            case 'monitor_mikrotik':
                return monitorMikrotik($pdo, $moduleName, $data);
            // DHCP specific functions
            case 'leases':
                return getDHCPLeases($pdo, $moduleName, $data);
            case 'reservations':
                return getDHCPReservations($pdo, $moduleName, $data);
            // SNMP specific functions
            case 'poll':
                return pollSNMP($pdo, $moduleName, $data);
            case 'walk':
                return walkSNMP($pdo, $moduleName, $data);
            case 'trap':
                return configureSNMPTrap($pdo, $moduleName, $data);
            // VLAN specific functions
            case 'scan_vlan':
                return scanNetwork($pdo, $moduleName, $data);
            case 'monitor_vlan':
                return monitorNetwork($pdo, $moduleName, $data);
            // IP Range specific functions
            case 'ping_range':
                return pingRange($pdo, $moduleName, $data);
            // Scan Jobs specific functions
            case 'start':
                return startScanJob($pdo, $moduleName, $data);
            case 'stop':
                return stopScanJob($pdo, $moduleName, $data);
            case 'results':
                return getScanResults($pdo, $moduleName, $data);
            // Dashboard specific functions
            case 'stats':
            case 'overview':
            case 'alerts':
            case 'performance':
                return getDashboardStats($pdo, $moduleName, $data);
            default:
                return ['success' => false, 'message' => 'Function not implemented'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error executing function: ' . $e->getMessage()];
    }
}

function getModuleData($moduleName, $pdo) {
    global $moduleDefinitions;
    
    if (!isset($moduleDefinitions[$moduleName])) {
        return ['success' => false, 'message' => 'Module not found'];
    }
    
    $module = $moduleDefinitions[$moduleName];
    $table = $module['table'];
    
    try {
        $stmt = $pdo->query("SELECT * FROM $table ORDER BY created_at DESC LIMIT 100");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting module data: ' . $e->getMessage()];
    }
}

function updateModuleData($moduleName, $pdo, $data) {
    global $moduleDefinitions;
    
    if (!isset($moduleDefinitions[$moduleName])) {
        return ['success' => false, 'message' => 'Module not found'];
    }
    
    $module = $moduleDefinitions[$moduleName];
    $table = $module['table'];
    $id = $data['id'] ?? 0;
    
    if (!$id) {
        return ['success' => false, 'message' => 'ID is required'];
    }
    
    try {
        $fields = [];
        $values = [];
        
        foreach ($module['fields'] as $field) {
            if ($field !== 'id' && isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        $values[] = $id;
        
        $sql = "UPDATE $table SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        return [
            'success' => true,
            'message' => 'Data updated successfully'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating data: ' . $e->getMessage()];
    }
}

function deleteModuleData($moduleName, $pdo, $id) {
    global $moduleDefinitions;
    
    if (!isset($moduleDefinitions[$moduleName])) {
        return ['success' => false, 'message' => 'Module not found'];
    }
    
    $module = $moduleDefinitions[$moduleName];
    $table = $module['table'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        
        return [
            'success' => true,
            'message' => 'Data deleted successfully'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error deleting data: ' . $e->getMessage()];
    }
}

function listModuleData($pdo, $moduleName, $module) {
    $table = $module['table'];
    
    try {
        // Special handling for dashboard module
        if ($moduleName === 'dashboard') {
            return getDashboardStats($pdo, $moduleName, []);
        }
        
        $stmt = $pdo->query("SELECT * FROM $table ORDER BY created_at DESC LIMIT 100");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error listing data: ' . $e->getMessage()];
    }
}

function addModuleData($pdo, $moduleName, $module, $data) {
    $table = $module['table'];
    
    // Validate required fields
    foreach ($module['required_fields'] as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return ['success' => false, 'message' => "Field '$field' is required"];
        }
    }
    
    try {
        $fields = [];
        $placeholders = [];
        $values = [];
        
        foreach ($module['fields'] as $field) {
            if ($field !== 'id' && isset($data[$field])) {
                $fields[] = $field;
                $placeholders[] = '?';
                $values[] = $data[$field];
            }
        }
        
        // Add created_at if not provided
        if (!in_array('created_at', $fields)) {
            $fields[] = 'created_at';
            $placeholders[] = 'NOW()';
        }
        
        $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        return [
            'success' => true,
            'message' => 'Data added successfully',
            'id' => $pdo->lastInsertId()
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error adding data: ' . $e->getMessage()];
    }
}

function editModuleData($pdo, $moduleName, $module, $data) {
    return updateModuleData($moduleName, $pdo, $data);
}

function searchModuleData($pdo, $moduleName, $module, $data) {
    $table = $module['table'];
    $search = $data['search'] ?? '';
    $field = $data['field'] ?? 'name';
    
    if (!in_array($field, $module['fields'])) {
        return ['success' => false, 'message' => 'Invalid search field'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE $field LIKE ? ORDER BY created_at DESC LIMIT 50");
        $stmt->execute(['%' . $search . '%']);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error searching data: ' . $e->getMessage()];
    }
}

function importModuleData($moduleName, $pdo, $data) {
    global $moduleDefinitions;
    
    if (!isset($moduleDefinitions[$moduleName])) {
        return ['success' => false, 'message' => 'Module not found'];
    }
    
    $module = $moduleDefinitions[$moduleName];
    $table = $module['table'];
    $importData = $data['import_data'] ?? [];
    
    if (empty($importData)) {
        return ['success' => false, 'message' => 'No data to import'];
    }
    
    try {
        $pdo->beginTransaction();
        
        $imported = 0;
        foreach ($importData as $row) {
            $fields = [];
            $placeholders = [];
            $values = [];
            
            foreach ($module['fields'] as $field) {
                if ($field !== 'id' && isset($row[$field])) {
                    $fields[] = $field;
                    $placeholders[] = '?';
                    $values[] = $row[$field];
                }
            }
            
            if (!empty($fields)) {
                $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($values);
                $imported++;
            }
        }
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => "Successfully imported $imported records"
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Error importing data: ' . $e->getMessage()];
    }
}

function exportModuleData($moduleName, $pdo, $format = 'json') {
    global $moduleDefinitions;
    
    if (!isset($moduleDefinitions[$moduleName])) {
        return ['success' => false, 'message' => 'Module not found'];
    }
    
    $module = $moduleDefinitions[$moduleName];
    $table = $module['table'];
    
    try {
        $stmt = $pdo->query("SELECT * FROM $table ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        switch ($format) {
            case 'json':
                return [
                    'success' => true,
                    'data' => $data,
                    'format' => 'json',
                    'filename' => $moduleName . '_export_' . date('Y-m-d_H-i-s') . '.json'
                ];
            case 'csv':
                $csv = '';
                if (!empty($data)) {
                    $csv .= implode(',', array_keys($data[0])) . "\n";
                    foreach ($data as $row) {
                        $csv .= implode(',', array_map('addslashes', $row)) . "\n";
                    }
                }
                return [
                    'success' => true,
                    'data' => $csv,
                    'format' => 'csv',
                    'filename' => $moduleName . '_export_' . date('Y-m-d_H-i-s') . '.csv'
                ];
            default:
                return ['success' => false, 'message' => 'Unsupported export format'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error exporting data: ' . $e->getMessage()];
    }
}

// Mikrotik Functions
function connectMikrotik($pdo, $moduleName, $data) {
    $deviceId = $data['device_id'] ?? null;
    if (!$deviceId) {
        return ['success' => false, 'message' => 'Device ID required'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM mikrotik_devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }
        
        // Simulate connection test
        $connected = @fsockopen($device['ip_address'], $device['api_port'] ?? 8728, $errno, $errstr, 5);
        $status = $connected ? 'connected' : 'failed';
        
        if ($connected) {
            fclose($connected);
        }
        
        // Update device status
        $updateStmt = $pdo->prepare("UPDATE mikrotik_devices SET status = ?, last_seen = NOW() WHERE id = ?");
        $updateStmt->execute([$status, $deviceId]);
        
        return [
            'success' => true,
            'data' => [
                'device' => $device,
                'connection_status' => $status,
                'error' => $connected ? null : $errstr
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Connection error: ' . $e->getMessage()];
    }
}

function backupMikrotik($pdo, $moduleName, $data) {
    $deviceId = $data['device_id'] ?? null;
    if (!$deviceId) {
        return ['success' => false, 'message' => 'Device ID required'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM mikrotik_devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }
        
        // Simulate backup creation
        $backupName = 'backup_' . $device['name'] . '_' . date('Y-m-d_H-i-s') . '.rsc';
        $backupPath = '/var/www/html/backups/' . $backupName;
        
        // Create backup directory if it doesn't exist
        if (!is_dir('/var/www/html/backups')) {
            mkdir('/var/www/html/backups', 0755, true);
        }
        
        // Simulate backup file creation
        file_put_contents($backupPath, "# Mikrotik backup for {$device['name']}\n# Generated: " . date('Y-m-d H:i:s'));
        
        return [
            'success' => true,
            'data' => [
                'device' => $device,
                'backup_file' => $backupName,
                'backup_path' => $backupPath,
                'backup_size' => filesize($backupPath)
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Backup error: ' . $e->getMessage()];
    }
}

function monitorMikrotik($pdo, $moduleName, $data) {
    $deviceId = $data['device_id'] ?? null;
    if (!$deviceId) {
        return ['success' => false, 'message' => 'Device ID required'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM mikrotik_devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }
        
        // Simulate monitoring data
        $monitoringData = [
            'cpu_usage' => rand(10, 90),
            'memory_usage' => rand(20, 80),
            'uptime' => rand(1, 365) . ' days',
            'active_connections' => rand(50, 500),
            'traffic_in' => rand(1000, 10000) . ' Mbps',
            'traffic_out' => rand(1000, 10000) . ' Mbps'
        ];
        
        return [
            'success' => true,
            'data' => [
                'device' => $device,
                'monitoring' => $monitoringData
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Monitoring error: ' . $e->getMessage()];
    }
}

// DHCP Functions
function getDHCPLeases($pdo, $moduleName, $data) {
    $serverId = $data['server_id'] ?? null;
    if (!$serverId) {
        return ['success' => false, 'message' => 'Server ID required'];
    }
    
    try {
        // Simulate DHCP leases data
        $leases = [];
        for ($i = 1; $i <= 10; $i++) {
            $leases[] = [
                'id' => $i,
                'mac_address' => sprintf('00:1B:44:11:3A:%02X', $i),
                'ip_address' => "192.168.1." . (100 + $i),
                'hostname' => "client-" . $i,
                'lease_time' => rand(3600, 86400),
                'expires' => date('Y-m-d H:i:s', time() + rand(3600, 86400)),
                'status' => rand(0, 1) ? 'active' : 'expired'
            ];
        }
        
        return [
            'success' => true,
            'data' => $leases
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting DHCP leases: ' . $e->getMessage()];
    }
}

function getDHCPReservations($pdo, $moduleName, $data) {
    $serverId = $data['server_id'] ?? null;
    if (!$serverId) {
        return ['success' => false, 'message' => 'Server ID required'];
    }
    
    try {
        // Simulate DHCP reservations data
        $reservations = [];
        for ($i = 1; $i <= 5; $i++) {
            $reservations[] = [
                'id' => $i,
                'mac_address' => sprintf('00:1B:44:11:3A:%02X', $i),
                'ip_address' => "192.168.1." . (200 + $i),
                'hostname' => "reserved-" . $i,
                'description' => "Reserved device " . $i,
                'created_at' => date('Y-m-d H:i:s', time() - rand(86400, 2592000))
            ];
        }
        
        return [
            'success' => true,
            'data' => $reservations
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting DHCP reservations: ' . $e->getMessage()];
    }
}

// SNMP Functions
function pollSNMP($pdo, $moduleName, $data) {
    $deviceId = $data['device_id'] ?? null;
    if (!$deviceId) {
        return ['success' => false, 'message' => 'Device ID required'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM snmp_devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }
        
        // Simulate SNMP polling
        $snmpData = [
            'system_name' => 'Router-' . rand(1, 100),
            'system_description' => 'Cisco Router',
            'uptime' => rand(1000000, 9999999),
            'cpu_usage' => rand(5, 95),
            'memory_usage' => rand(10, 90),
            'interface_count' => rand(4, 24),
            'last_poll' => date('Y-m-d H:i:s')
        ];
        
        // Update last poll time
        $updateStmt = $pdo->prepare("UPDATE snmp_devices SET last_poll = NOW() WHERE id = ?");
        $updateStmt->execute([$deviceId]);
        
        return [
            'success' => true,
            'data' => [
                'device' => $device,
                'snmp_data' => $snmpData
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'SNMP polling error: ' . $e->getMessage()];
    }
}

function walkSNMP($pdo, $moduleName, $data) {
    $deviceId = $data['device_id'] ?? null;
    $oid = $data['oid'] ?? '1.3.6.1.2.1.1';
    if (!$deviceId) {
        return ['success' => false, 'message' => 'Device ID required'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM snmp_devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }
        
        // Simulate SNMP walk results
        $walkResults = [];
        for ($i = 1; $i <= 10; $i++) {
            $walkResults[] = [
                'oid' => $oid . '.' . $i,
                'value' => 'Value-' . $i,
                'type' => 'STRING'
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'device' => $device,
                'oid' => $oid,
                'results' => $walkResults
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'SNMP walk error: ' . $e->getMessage()];
    }
}

function configureSNMPTrap($pdo, $moduleName, $data) {
    $deviceId = $data['device_id'] ?? null;
    $trapServer = $data['trap_server'] ?? null;
    if (!$deviceId || !$trapServer) {
        return ['success' => false, 'message' => 'Device ID and trap server required'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM snmp_devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }
        
        // Simulate SNMP trap configuration
        $trapConfig = [
            'trap_server' => $trapServer,
            'community' => $device['community'],
            'version' => $device['version'],
            'enabled' => true,
            'configured_at' => date('Y-m-d H:i:s')
        ];
        
        return [
            'success' => true,
            'data' => [
                'device' => $device,
                'trap_config' => $trapConfig
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'SNMP trap configuration error: ' . $e->getMessage()];
    }
}

// Network Functions
function scanNetwork($pdo, $moduleName, $data) {
    $targets = $data['targets'] ?? null;
    if (!$targets) {
        return ['success' => false, 'message' => 'Scan targets required'];
    }
    
    try {
        // Simulate network scan
        $scanResults = [];
        $targetArray = is_array($targets) ? $targets : explode(',', $targets);
        
        foreach ($targetArray as $target) {
            $target = trim($target);
            $scanResults[] = [
                'target' => $target,
                'status' => rand(0, 1) ? 'online' : 'offline',
                'response_time' => rand(1, 100) . 'ms',
                'ports' => rand(1, 10),
                'services' => ['SSH', 'HTTP', 'SNMP'],
                'scanned_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'targets' => $targetArray,
                'results' => $scanResults,
                'scan_time' => date('Y-m-d H:i:s')
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Network scan error: ' . $e->getMessage()];
    }
}

function monitorNetwork($pdo, $moduleName, $data) {
    $networkId = $data['network_id'] ?? null;
    if (!$networkId) {
        return ['success' => false, 'message' => 'Network ID required'];
    }
    
    try {
        // Simulate network monitoring
        $monitoringData = [
            'network_id' => $networkId,
            'total_devices' => rand(10, 100),
            'online_devices' => rand(8, 95),
            'bandwidth_usage' => rand(20, 80) . '%',
            'packet_loss' => rand(0, 5) . '%',
            'latency' => rand(1, 50) . 'ms',
            'last_check' => date('Y-m-d H:i:s')
        ];
        
        return [
            'success' => true,
            'data' => $monitoringData
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Network monitoring error: ' . $e->getMessage()];
    }
}

// IP Range Functions
function pingRange($pdo, $moduleName, $data) {
    $rangeId = $data['range_id'] ?? null;
    if (!$rangeId) {
        return ['success' => false, 'message' => 'Range ID required'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM ip_ranges WHERE id = ?");
        $stmt->execute([$rangeId]);
        $range = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$range) {
            return ['success' => false, 'message' => 'IP range not found'];
        }
        
        // Simulate ping results
        $pingResults = [];
        $startIp = ip2long($range['start_ip']);
        $endIp = ip2long($range['end_ip']);
        
        for ($i = 0; $i < 10; $i++) {
            $ip = long2ip($startIp + $i);
            $pingResults[] = [
                'ip' => $ip,
                'status' => rand(0, 1) ? 'reachable' : 'unreachable',
                'response_time' => rand(1, 100) . 'ms',
                'ttl' => rand(32, 255)
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'range' => $range,
                'ping_results' => $pingResults
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Ping range error: ' . $e->getMessage()];
    }
}

// Scan Job Functions
function startScanJob($pdo, $moduleName, $data) {
    $jobName = $data['name'] ?? null;
    $scanType = $data['type'] ?? null;
    $targets = $data['targets'] ?? null;
    
    if (!$jobName || !$scanType || !$targets) {
        return ['success' => false, 'message' => 'Job name, type and targets required'];
    }
    
    try {
        // Create scan job
        $stmt = $pdo->prepare("INSERT INTO scan_jobs (name, type, targets, parameters, status, started_at, created_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $parameters = json_encode($data['parameters'] ?? []);
        $stmt->execute([$jobName, $scanType, $targets, $parameters, 'running']);
        $jobId = $pdo->lastInsertId();
        
        return [
            'success' => true,
            'data' => [
                'job_id' => $jobId,
                'job_name' => $jobName,
                'status' => 'started',
                'started_at' => date('Y-m-d H:i:s')
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error starting scan job: ' . $e->getMessage()];
    }
}

function stopScanJob($pdo, $moduleName, $data) {
    $jobId = $data['job_id'] ?? null;
    if (!$jobId) {
        return ['success' => false, 'message' => 'Job ID required'];
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE scan_jobs SET status = 'stopped', completed_at = NOW() WHERE id = ?");
        $stmt->execute([$jobId]);
        
        return [
            'success' => true,
            'data' => [
                'job_id' => $jobId,
                'status' => 'stopped',
                'stopped_at' => date('Y-m-d H:i:s')
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error stopping scan job: ' . $e->getMessage()];
    }
}

function getScanResults($pdo, $moduleName, $data) {
    try {
        $jobId = $data['job_id'] ?? 0;
        
        if (!$jobId) {
            return ['success' => false, 'message' => 'Job ID is required'];
        }
        
        $stmt = $pdo->prepare("SELECT * FROM scan_jobs WHERE id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$job) {
            return ['success' => false, 'message' => 'Scan job not found'];
        }
        
        return [
            'success' => true,
            'data' => $job,
            'results' => json_decode($job['results'] ?? '[]', true)
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting scan results: ' . $e->getMessage()];
    }
}

function getDashboardStats($pdo, $moduleName, $data) {
    try {
        // Get system statistics
        $stats = [];
        
        // Count clients
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients");
            $stats['total_clients'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $stats['total_clients'] = 0;
        }
        
        // Count devices
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM devices");
            $stats['total_devices'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $stats['total_devices'] = 0;
        }
        
        // Count active networks
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM networks WHERE active = 1");
            $stats['active_networks'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $stats['active_networks'] = 0;
        }
        
        // System status
        $stats['system_status'] = 'Online';
        $stats['last_updated'] = date('Y-m-d H:i:s');
        
        // Recent alerts
        try {
            $stmt = $pdo->query("SELECT * FROM network_alerts ORDER BY timestamp DESC LIMIT 5");
            $stats['recent_alerts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stats['recent_alerts'] = [];
        }
        
        // Performance metrics
        $stats['performance'] = [
            'cpu_usage' => rand(20, 80),
            'memory_usage' => rand(30, 90),
            'network_traffic' => rand(100, 1000),
            'active_connections' => rand(50, 200)
        ];
        
        return [
            'success' => true,
            'data' => $stats,
            'message' => 'Dashboard statistics retrieved successfully'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting dashboard stats: ' . $e->getMessage()];
    }
}
?> 