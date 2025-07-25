<?php
/**
 * Captive Portal API
 * RESTful API endpoints for captive portal management
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

session_start();

// Database connection
function getDatabaseConnection() {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=slms", "username", "password");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

// API Response helper
function apiResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));
$endpoint = end($pathParts);

// Handle preflight requests
if ($method === 'OPTIONS') {
    apiResponse(true, null, 'OK', 200);
}

try {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        apiResponse(false, null, 'Database connection failed', 500);
    }

    switch ($endpoint) {
        case 'auth':
            handleAuth($method, $pdo);
            break;
            
        case 'sessions':
            handleSessions($method, $pdo);
            break;
            
        case 'vlans':
            handleVLANs($method, $pdo);
            break;
            
        case 'users':
            handleUsers($method, $pdo);
            break;
            
        case 'stats':
            handleStats($method, $pdo);
            break;
            
        case 'settings':
            handleSettings($method, $pdo);
            break;
            
        default:
            apiResponse(false, null, 'Endpoint not found', 404);
    }
} catch (Exception $e) {
    apiResponse(false, null, 'Server error: ' . $e->getMessage(), 500);
}

// Authentication endpoint
function handleAuth($method, $pdo) {
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['username']) || !isset($input['password'])) {
                apiResponse(false, null, 'Username and password required', 400);
            }
            
            // Authenticate user
            $stmt = $pdo->prepare("
                SELECT id, username, password_hash, role, vlan_id, max_bandwidth, allowed_domains, status
                FROM captive_portal_users 
                WHERE username = ? AND status = 'active'
            ");
            $stmt->execute([$input['username']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($input['password'], $user['password_hash'])) {
                apiResponse(false, null, 'Invalid credentials', 401);
            }
            
            // Create session
            $sessionData = [
                'vlan_id' => $user['vlan_id'],
                'mac_address' => $input['mac_address'] ?? '00:00:00:00:00:00',
                'ip_address' => $input['ip_address'] ?? $_SERVER['REMOTE_ADDR'],
                'username' => $user['username']
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO captive_portal_sessions (vlan_id, mac_address, ip_address, username)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $sessionData['vlan_id'],
                $sessionData['mac_address'],
                $sessionData['ip_address'],
                $sessionData['username']
            ]);
            
            $sessionId = $pdo->lastInsertId();
            
            // Return session info
            apiResponse(true, [
                'session_id' => $sessionId,
                'username' => $user['username'],
                'role' => $user['role'],
                'max_bandwidth' => $user['max_bandwidth'],
                'allowed_domains' => json_decode($user['allowed_domains'], true),
                'login_time' => date('Y-m-d H:i:s')
            ], 'Login successful');
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['session_id'])) {
                apiResponse(false, null, 'Session ID required', 400);
            }
            
            // End session
            $stmt = $pdo->prepare("
                UPDATE captive_portal_sessions 
                SET active = FALSE, status = 'disconnected', logout_time = NOW()
                WHERE id = ? AND active = TRUE
            ");
            $stmt->execute([$input['session_id']]);
            
            apiResponse(true, null, 'Logout successful');
            break;
            
        default:
            apiResponse(false, null, 'Method not allowed', 405);
    }
}

// Sessions endpoint
function handleSessions($method, $pdo) {
    switch ($method) {
        case 'GET':
            $vlanId = $_GET['vlan_id'] ?? null;
            
            if ($vlanId) {
                $stmt = $pdo->prepare("
                    SELECT cs.*, v.name as vlan_name
                    FROM captive_portal_sessions cs
                    JOIN vlans v ON cs.vlan_id = v.id
                    WHERE cs.vlan_id = ? AND cs.active = TRUE
                    ORDER BY cs.login_time DESC
                ");
                $stmt->execute([$vlanId]);
            } else {
                $stmt = $pdo->query("
                    SELECT cs.*, v.name as vlan_name
                    FROM captive_portal_sessions cs
                    JOIN vlans v ON cs.vlan_id = v.id
                    WHERE cs.active = TRUE
                    ORDER BY cs.login_time DESC
                ");
            }
            
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            apiResponse(true, $sessions, 'Sessions retrieved successfully');
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['session_id'])) {
                apiResponse(false, null, 'Session ID required', 400);
            }
            
            $stmt = $pdo->prepare("
                UPDATE captive_portal_sessions 
                SET active = FALSE, status = 'disconnected', logout_time = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$input['session_id']]);
            
            apiResponse(true, null, 'Session disconnected successfully');
            break;
            
        default:
            apiResponse(false, null, 'Method not allowed', 405);
    }
}

// VLANs endpoint
function handleVLANs($method, $pdo) {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("
                SELECT v.*, 
                       COUNT(cs.id) as active_sessions,
                       SUM(cs.bytes_in) as total_bytes_in,
                       SUM(cs.bytes_out) as total_bytes_out
                FROM vlans v
                LEFT JOIN captive_portal_sessions cs ON v.id = cs.vlan_id AND cs.active = TRUE
                GROUP BY v.id
                ORDER BY v.vlan_id
            ");
            $vlans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            apiResponse(true, $vlans, 'VLANs retrieved successfully');
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required = ['vlan_id', 'name', 'network_address', 'gateway'];
            foreach ($required as $field) {
                if (!isset($input[$field])) {
                    apiResponse(false, null, "Field '$field' is required", 400);
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO vlans (vlan_id, name, description, network_address, gateway, 
                                 captive_portal_enabled, captive_portal_url, walled_garden_domains,
                                 session_timeout, max_bandwidth)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $input['vlan_id'],
                $input['name'],
                $input['description'] ?? '',
                $input['network_address'],
                $input['gateway'],
                $input['captive_portal_enabled'] ?? false,
                $input['captive_portal_url'] ?? null,
                json_encode($input['walled_garden_domains'] ?? []),
                $input['session_timeout'] ?? 3600,
                $input['max_bandwidth'] ?? 10
            ]);
            
            $vlanId = $pdo->lastInsertId();
            apiResponse(true, ['id' => $vlanId], 'VLAN created successfully');
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                apiResponse(false, null, 'VLAN ID required', 400);
            }
            
            $stmt = $pdo->prepare("
                UPDATE vlans SET
                    name = ?, description = ?, network_address = ?, gateway = ?,
                    captive_portal_enabled = ?, captive_portal_url = ?, walled_garden_domains = ?,
                    session_timeout = ?, max_bandwidth = ?, status = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $input['name'],
                $input['description'] ?? '',
                $input['network_address'],
                $input['gateway'],
                $input['captive_portal_enabled'] ?? false,
                $input['captive_portal_url'] ?? null,
                json_encode($input['walled_garden_domains'] ?? []),
                $input['session_timeout'] ?? 3600,
                $input['max_bandwidth'] ?? 10,
                $input['status'] ?? 'active',
                $input['id']
            ]);
            
            apiResponse(true, null, 'VLAN updated successfully');
            break;
            
        default:
            apiResponse(false, null, 'Method not allowed', 405);
    }
}

// Users endpoint
function handleUsers($method, $pdo) {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("
                SELECT id, username, email, full_name, role, vlan_id, 
                       max_bandwidth, session_timeout, allowed_domains, status, created_at
                FROM captive_portal_users
                ORDER BY username
            ");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            apiResponse(true, $users, 'Users retrieved successfully');
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['username']) || !isset($input['password'])) {
                apiResponse(false, null, 'Username and password required', 400);
            }
            
            $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO captive_portal_users (username, password_hash, email, full_name, role, 
                                                 vlan_id, max_bandwidth, session_timeout, allowed_domains)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $input['username'],
                $passwordHash,
                $input['email'] ?? null,
                $input['full_name'] ?? null,
                $input['role'] ?? 'guest',
                $input['vlan_id'] ?? null,
                $input['max_bandwidth'] ?? 10,
                $input['session_timeout'] ?? 3600,
                json_encode($input['allowed_domains'] ?? [])
            ]);
            
            $userId = $pdo->lastInsertId();
            apiResponse(true, ['id' => $userId], 'User created successfully');
            break;
            
        default:
            apiResponse(false, null, 'Method not allowed', 405);
    }
}

// Statistics endpoint
function handleStats($method, $pdo) {
    if ($method !== 'GET') {
        apiResponse(false, null, 'Method not allowed', 405);
    }
    
    $vlanId = $_GET['vlan_id'] ?? null;
    
    if ($vlanId) {
        // Get stats for specific VLAN
        $stmt = $pdo->prepare("
            SELECT 
                v.name as vlan_name,
                COUNT(cs.id) as total_sessions,
                COUNT(CASE WHEN cs.active = 1 THEN 1 END) as active_sessions,
                SUM(cs.bytes_in) as total_bytes_in,
                SUM(cs.bytes_out) as total_bytes_out,
                AVG(TIMESTAMPDIFF(MINUTE, cs.login_time, COALESCE(cs.logout_time, NOW()))) as avg_session_duration
            FROM vlans v
            LEFT JOIN captive_portal_sessions cs ON v.id = cs.vlan_id
            WHERE v.id = ?
            GROUP BY v.id
        ");
        $stmt->execute([$vlanId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Get overall stats
        $stmt = $pdo->query("
            SELECT 
                COUNT(DISTINCT v.id) as total_vlans,
                COUNT(CASE WHEN v.captive_portal_enabled = 1 THEN 1 END) as active_portals,
                COUNT(cs.id) as total_sessions,
                COUNT(CASE WHEN cs.active = 1 THEN 1 END) as active_sessions,
                SUM(cs.bytes_in) as total_bytes_in,
                SUM(cs.bytes_out) as total_bytes_out
            FROM vlans v
            LEFT JOIN captive_portal_sessions cs ON v.id = cs.vlan_id
        ");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    apiResponse(true, $stats, 'Statistics retrieved successfully');
}

// Settings endpoint
function handleSettings($method, $pdo) {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("SELECT setting_key, setting_value, setting_type, description FROM captive_portal_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert to key-value pairs
            $settingsArray = [];
            foreach ($settings as $setting) {
                $value = $setting['setting_value'];
                if ($setting['setting_type'] === 'json') {
                    $value = json_decode($value, true);
                } elseif ($setting['setting_type'] === 'integer') {
                    $value = (int)$value;
                } elseif ($setting['setting_type'] === 'boolean') {
                    $value = $value === 'true';
                }
                $settingsArray[$setting['setting_key']] = $value;
            }
            
            apiResponse(true, $settingsArray, 'Settings retrieved successfully');
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['setting_key']) || !isset($input['setting_value'])) {
                apiResponse(false, null, 'Setting key and value required', 400);
            }
            
            $stmt = $pdo->prepare("
                UPDATE captive_portal_settings 
                SET setting_value = ?, updated_at = NOW()
                WHERE setting_key = ?
            ");
            $stmt->execute([$input['setting_value'], $input['setting_key']]);
            
            apiResponse(true, null, 'Setting updated successfully');
            break;
            
        default:
            apiResponse(false, null, 'Method not allowed', 405);
    }
}
?> 