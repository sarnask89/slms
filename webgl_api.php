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

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    $pdo = get_pdo();
    
    switch ($action) {
        case 'get_stats':
            $response = getSystemStats($pdo);
            break;
            
        case 'get_clients':
            $response = getClients($pdo);
            break;
            
        case 'get_devices':
            $response = getDevices($pdo);
            break;
            
        case 'get_networks':
            $response = getNetworks($pdo);
            break;
            
        case 'get_invoices':
            $response = getInvoices($pdo);
            break;
            
        case 'get_users':
            $response = getUsers($pdo);
            break;
            
        case 'get_services':
            $response = getServices($pdo);
            break;
            
        case 'get_alerts':
            $response = getAlerts($pdo);
            break;
            
        case 'add_client':
            $response = addClient($pdo, $_POST);
            break;
            
        case 'add_device':
            $response = addDevice($pdo, $_POST);
            break;
            
        case 'update_client':
            $response = updateClient($pdo, $_POST);
            break;
            
        case 'update_device':
            $response = updateDevice($pdo, $_POST);
            break;
            
        case 'delete_client':
            $response = deleteClient($pdo, $_GET['id']);
            break;
            
        case 'delete_device':
            $response = deleteDevice($pdo, $_GET['id']);
            break;
            
        case 'get_module_data':
            $response = getModuleData($pdo, $_GET['module']);
            break;
            
        case 'export_data':
            $response = exportData($pdo, $_GET['type']);
            break;
            
        case 'system_status':
            $response = getSystemStatus();
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

// Function implementations
function getSystemStats($pdo) {
    try {
        // Get client count - try different status columns
        $clientCount = 0;
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients WHERE status = 'active'");
            $clientCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (Exception $e) {
            // If status column doesn't exist, just count all clients
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients");
        $clientCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        }
        
        // Get device count - try different status columns
        $deviceCount = 0;
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM devices WHERE status = 'online'");
            $deviceCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (Exception $e) {
            // If status column doesn't exist, just count all devices
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM devices");
        $deviceCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        }
        
        // Get network count - try different status columns
        $networkCount = 0;
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM networks WHERE status = 'active'");
            $networkCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (Exception $e) {
            // If status column doesn't exist, just count all networks
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM networks");
        $networkCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        }
        
        // Get alert count
        $alertCount = 0;
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM network_alerts WHERE status = 'active'");
            $alertCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (Exception $e) {
            // If status column doesn't exist, just count all alerts
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM network_alerts");
        $alertCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        }
        
        return [
            'success' => true,
            'data' => [
                'clients' => $clientCount,
                'devices' => $deviceCount,
                'networks' => $networkCount,
                'alerts' => $alertCount
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting stats: ' . $e->getMessage()];
    }
}

function getClients($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM clients ORDER BY name LIMIT 100");
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $clients
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting clients: ' . $e->getMessage()];
    }
}

function getDevices($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM devices ORDER BY name LIMIT 100");
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $devices
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting devices: ' . $e->getMessage()];
    }
}

function getNetworks($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM networks ORDER BY name LIMIT 100");
        $networks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $networks
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting networks: ' . $e->getMessage()];
    }
}

function getInvoices($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM invoices ORDER BY issue_date DESC LIMIT 100");
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $invoices
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting invoices: ' . $e->getMessage()];
    }
}

function getUsers($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY username LIMIT 100");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $users
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting users: ' . $e->getMessage()];
    }
}

function getServices($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM services ORDER BY name LIMIT 100");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $services
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting services: ' . $e->getMessage()];
    }
}

function getAlerts($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM network_alerts ORDER BY timestamp DESC LIMIT 50");
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $alerts
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting alerts: ' . $e->getMessage()];
    }
}

function addClient($pdo, $data) {
    try {
        $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['address'] ?? ''
        ]);
        
        return [
            'success' => true,
            'message' => 'Client added successfully',
            'id' => $pdo->lastInsertId()
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error adding client: ' . $e->getMessage()];
    }
}

function addDevice($pdo, $data) {
    try {
        $stmt = $pdo->prepare("INSERT INTO devices (name, type, ip_address, mac_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $data['name'] ?? '',
            $data['type'] ?? '',
            $data['ip_address'] ?? '',
            $data['mac_address'] ?? ''
        ]);
        
        return [
            'success' => true,
            'message' => 'Device added successfully',
            'id' => $pdo->lastInsertId()
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error adding device: ' . $e->getMessage()];
    }
}

function updateClient($pdo, $data) {
    try {
        $stmt = $pdo->prepare("UPDATE clients SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['address'] ?? '',
            $data['id'] ?? 0
        ]);
        
        return [
            'success' => true,
            'message' => 'Client updated successfully'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating client: ' . $e->getMessage()];
    }
}

function updateDevice($pdo, $data) {
    try {
        $stmt = $pdo->prepare("UPDATE devices SET name = ?, type = ?, ip_address = ?, mac_address = ? WHERE id = ?");
        $stmt->execute([
            $data['name'] ?? '',
            $data['type'] ?? '',
            $data['ip_address'] ?? '',
            $data['mac_address'] ?? '',
            $data['id'] ?? 0
        ]);
        
        return [
            'success' => true,
            'message' => 'Device updated successfully'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating device: ' . $e->getMessage()];
    }
}

function deleteClient($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        
        return [
            'success' => true,
            'message' => 'Client deleted successfully'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error deleting client: ' . $e->getMessage()];
    }
}

function deleteDevice($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM devices WHERE id = ?");
        $stmt->execute([$id]);
        
        return [
            'success' => true,
            'message' => 'Device deleted successfully'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error deleting device: ' . $e->getMessage()];
    }
}

function getModuleData($pdo, $module) {
    try {
        switch ($module) {
            case 'clients':
                return getClients($pdo);
            case 'devices':
                return getDevices($pdo);
            case 'networks':
                return getNetworks($pdo);
            case 'invoices':
                return getInvoices($pdo);
            case 'users':
                return getUsers($pdo);
            case 'services':
                return getServices($pdo);
            case 'alerts':
                return getAlerts($pdo);
            default:
                return ['success' => false, 'message' => 'Unknown module'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting module data: ' . $e->getMessage()];
    }
}

function exportData($pdo, $type) {
    try {
        switch ($type) {
            case 'clients':
                $stmt = $pdo->query("SELECT * FROM clients");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'devices':
                $stmt = $pdo->query("SELECT * FROM devices");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'networks':
                $stmt = $pdo->query("SELECT * FROM networks");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            default:
                return ['success' => false, 'message' => 'Unknown export type'];
        }
        
        return [
            'success' => true,
            'data' => $data,
            'filename' => $type . '_export_' . date('Y-m-d_H-i-s') . '.json'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error exporting data: ' . $e->getMessage()];
    }
}

function getSystemStatus() {
    try {
        $status = [
            'webgl_support' => extension_loaded('opengl'),
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'database_connection' => 'connected',
            'session_status' => session_status(),
            'server_time' => date('Y-m-d H:i:s'),
            'uptime' => 'operational'
        ];
        
        return [
            'success' => true,
            'data' => $status
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error getting system status: ' . $e->getMessage()];
    }
}
?> 