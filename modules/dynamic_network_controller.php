<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'module_loader.php';

require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login
require_login();

$pageTitle = 'Dynamic Network Controller';
ob_start();
?>

/**
 * Dynamic Network Controller
 * Manages DHCP leases and NAT rules based on captive portal authentication
 * Handles users without static leases or ARP entries
 */

class DynamicNetworkController {
    private $pdo;
    private $mikrotik_api;
    private $config;
    
    public function __construct() {
        $this->pdo = $this->getDatabaseConnection();
        $this->mikrotik_api = $this->getMikrotikAPI();
        $this->config = $this->loadConfig();
    }
    
    // Database connection
    private function getDatabaseConnection() {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=slms", "username", "password");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    // Mikrotik API connection
    private function getMikrotikAPI() {
        // Initialize Mikrotik API connection
        require_once 'modules/mikrotik_api.php';
        return new MikrotikAPI([
            'host' => '192.168.1.1',
            'username' => 'admin',
            'password' => 'password',
            'port' => 8728
        ]);
    }
    
    // Load configuration
    private function loadConfig() {
        return [
            'dhcp_pool_start' => '192.168.100.100',
            'dhcp_pool_end' => '192.168.100.200',
            'gateway' => '192.168.100.1',
            'dns_servers' => ['8.8.8.8', '8.8.4.4'],
            'lease_time' => 3600, // 1 hour
            'nat_chain' => 'captive_portal_nat',
            'bridge_name' => 'bridge1',
            'vlan_interface' => 'vlan100'
        ];
    }
    
    /**
     * Process new user connection
     * Assigns IP, creates NAT rules, and manages access
     */
    public function processNewConnection($macAddress, $username = null, $userRole = 'guest') {
        try {
            // 1. Check if user already has a lease
            $existingLease = $this->getExistingLease($macAddress);
            if ($existingLease) {
                return $this->reactivateLease($existingLease, $username, $userRole);
            }
            
            // 2. Find available IP address
            $availableIP = $this->findAvailableIP();
            if (!$availableIP) {
                throw new Exception("No available IP addresses in DHCP pool");
            }
            
            // 3. Create DHCP lease
            $leaseId = $this->createDHCPLease($macAddress, $availableIP, $username);
            
            // 4. Create NAT rules based on user role
            $this->createNATRules($macAddress, $availableIP, $userRole);
            
            // 5. Create bridge filter rules
            $this->createBridgeFilters($macAddress, $availableIP, $userRole);
            
            // 6. Log the connection
            $this->logConnection($macAddress, $availableIP, $username, $userRole, $leaseId);
            
            return [
                'success' => true,
                'ip_address' => $availableIP,
                'gateway' => $this->config['gateway'],
                'dns_servers' => $this->config['dns_servers'],
                'lease_time' => $this->config['lease_time'],
                'user_role' => $userRole
            ];
            
        } catch (Exception $e) {
            error_log("Dynamic Network Controller Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Find available IP address in DHCP pool
     */
    private function findAvailableIP() {
        $startIP = ip2long($this->config['dhcp_pool_start']);
        $endIP = ip2long($this->config['dhcp_pool_end']);
        
        // Get all active leases
        $activeLeases = $this->getActiveLeases();
        $usedIPs = array_column($activeLeases, 'ip_address');
        
        // Find first available IP
        for ($ip = $startIP; $ip <= $endIP; $ip++) {
            $ipString = long2ip($ip);
            if (!in_array($ipString, $usedIPs)) {
                return $ipString;
            }
        }
        
        return null;
    }
    
    /**
     * Create DHCP lease on Mikrotik
     */
    private function createDHCPLease($macAddress, $ipAddress, $username = null) {
        $leaseData = [
            'address' => $ipAddress,
            'mac-address' => $macAddress,
            'client-id' => $macAddress,
            'server' => 'dhcp1',
            'comment' => $username ? "User: $username" : "Dynamic lease"
        ];
        
        $response = $this->mikrotik_api->add('/ip/dhcp-server/lease', $leaseData);
        
        if ($response['success']) {
            // Store in database
            $stmt = $this->pdo->prepare("
                INSERT INTO dynamic_leases (mac_address, ip_address, username, lease_id, created_at, expires_at)
                VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND))
            ");
            $stmt->execute([
                $macAddress,
                $ipAddress,
                $username,
                $response['data']['ret'],
                $this->config['lease_time']
            ]);
            
            return $response['data']['ret'];
        }
        
        throw new Exception("Failed to create DHCP lease: " . $response['error']);
    }
    
    /**
     * Create NAT rules based on user role
     */
    private function createNATRules($macAddress, $ipAddress, $userRole) {
        $natRules = $this->getNATRulesForRole($userRole);
        
        foreach ($natRules as $rule) {
            $ruleData = [
                'chain' => $this->config['nat_chain'],
                'src-address' => $ipAddress,
                'action' => $rule['action'],
                'comment' => "Dynamic NAT for $macAddress ($userRole)"
            ];
            
            // Add role-specific conditions
            if (isset($rule['dst-port'])) {
                $ruleData['dst-port'] = $rule['dst-port'];
            }
            if (isset($rule['protocol'])) {
                $ruleData['protocol'] = $rule['protocol'];
            }
            if (isset($rule['to-addresses'])) {
                $ruleData['to-addresses'] = $rule['to-addresses'];
            }
            
            $this->mikrotik_api->add('/ip/firewall/nat', $ruleData);
        }
    }
    
    /**
     * Get NAT rules based on user role
     */
    private function getNATRulesForRole($userRole) {
        $rules = [
            'guest' => [
                [
                    'action' => 'masquerade',
                    'comment' => 'Basic internet access'
                ],
                [
                    'action' => 'redirect',
                    'dst-port' => '80',
                    'protocol' => 'tcp',
                    'to-addresses' => $this->config['gateway'],
                    'to-ports' => '8080',
                    'comment' => 'Redirect HTTP to captive portal'
                ]
            ],
            'user' => [
                [
                    'action' => 'masquerade',
                    'comment' => 'Full internet access'
                ]
            ],
            'admin' => [
                [
                    'action' => 'masquerade',
                    'comment' => 'Unrestricted access'
                ]
            ]
        ];
        
        return $rules[$userRole] ?? $rules['guest'];
    }
    
    /**
     * Create bridge filter rules
     */
    private function createBridgeFilters($macAddress, $ipAddress, $userRole) {
        $filterRules = $this->getBridgeFiltersForRole($userRole);
        
        foreach ($filterRules as $rule) {
            $ruleData = [
                'chain' => 'forward',
                'src-mac-address' => $macAddress,
                'action' => $rule['action'],
                'comment' => "Bridge filter for $macAddress ($userRole)"
            ];
            
            // Add role-specific conditions
            if (isset($rule['dst-address'])) {
                $ruleData['dst-address'] = $rule['dst-address'];
            }
            if (isset($rule['protocol'])) {
                $ruleData['protocol'] = $rule['protocol'];
            }
            if (isset($rule['dst-port'])) {
                $ruleData['dst-port'] = $rule['dst-port'];
            }
            
            $this->mikrotik_api->add('/interface/bridge/filter', $ruleData);
        }
    }
    
    /**
     * Get bridge filter rules based on user role
     */
    private function getBridgeFiltersForRole($userRole) {
        $rules = [
            'guest' => [
                [
                    'action' => 'accept',
                    'dst-address' => '192.168.100.1',
                    'comment' => 'Allow access to gateway'
                ],
                [
                    'action' => 'accept',
                    'dst-address' => '8.8.8.8',
                    'protocol' => 'udp',
                    'dst-port' => '53',
                    'comment' => 'Allow DNS'
                ],
                [
                    'action' => 'drop',
                    'comment' => 'Block all other traffic'
                ]
            ],
            'user' => [
                [
                    'action' => 'accept',
                    'comment' => 'Allow all traffic'
                ]
            ],
            'admin' => [
                [
                    'action' => 'accept',
                    'comment' => 'Unrestricted access'
                ]
            ]
        ];
        
        return $rules[$userRole] ?? $rules['guest'];
    }
    
    /**
     * Handle captive portal authentication
     */
    public function handlePortalAuthentication($macAddress, $username, $password) {
        // Authenticate user
        $user = $this->authenticateUser($username, $password);
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        // Update existing lease or create new one
        $existingLease = $this->getExistingLease($macAddress);
        if ($existingLease) {
            // Update lease with user info
            $this->updateLeaseUser($existingLease['id'], $username, $user['role']);
            
            // Update NAT rules for new role
            $this->updateNATRules($macAddress, $existingLease['ip_address'], $user['role']);
            
            // Update bridge filters for new role
            $this->updateBridgeFilters($macAddress, $user['role']);
            
            return [
                'success' => true,
                'ip_address' => $existingLease['ip_address'],
                'user_role' => $user['role'],
                'message' => 'Authentication successful'
            ];
        } else {
            // Create new connection
            return $this->processNewConnection($macAddress, $username, $user['role']);
        }
    }
    
    /**
     * Authenticate user
     */
    private function authenticateUser($username, $password) {
        $stmt = $this->pdo->prepare("
            SELECT id, username, password_hash, role, max_bandwidth, allowed_domains
            FROM captive_portal_users 
            WHERE username = ? AND status = 'active'
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return null;
    }
    
    /**
     * Get existing lease for MAC address
     */
    private function getExistingLease($macAddress) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM dynamic_leases 
            WHERE mac_address = ? AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$macAddress]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all active leases
     */
    private function getActiveLeases() {
        $stmt = $this->pdo->query("
            SELECT * FROM dynamic_leases 
            WHERE expires_at > NOW()
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Reactivate existing lease
     */
    private function reactivateLease($lease, $username, $userRole) {
        // Extend lease time
        $stmt = $this->pdo->prepare("
            UPDATE dynamic_leases 
            SET expires_at = DATE_ADD(NOW(), INTERVAL ? SECOND),
                username = COALESCE(?, username),
                last_activity = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$this->config['lease_time'], $username, $lease['id']]);
        
        return [
            'success' => true,
            'ip_address' => $lease['ip_address'],
            'gateway' => $this->config['gateway'],
            'dns_servers' => $this->config['dns_servers'],
            'lease_time' => $this->config['lease_time'],
            'user_role' => $userRole,
            'message' => 'Lease reactivated'
        ];
    }
    
    /**
     * Update lease user information
     */
    private function updateLeaseUser($leaseId, $username, $userRole) {
        $stmt = $this->pdo->prepare("
            UPDATE dynamic_leases 
            SET username = ?, user_role = ?, last_activity = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$username, $userRole, $leaseId]);
    }
    
    /**
     * Update NAT rules for user
     */
    private function updateNATRules($macAddress, $ipAddress, $userRole) {
        // Remove old rules
        $this->removeNATRules($macAddress);
        
        // Create new rules
        $this->createNATRules($macAddress, $ipAddress, $userRole);
    }
    
    /**
     * Update bridge filters for user
     */
    private function updateBridgeFilters($macAddress, $userRole) {
        // Remove old filters
        $this->removeBridgeFilters($macAddress);
        
        // Create new filters
        $lease = $this->getExistingLease($macAddress);
        if ($lease) {
            $this->createBridgeFilters($macAddress, $lease['ip_address'], $userRole);
        }
    }
    
    /**
     * Remove NAT rules for MAC address
     */
    private function removeNATRules($macAddress) {
        // Get rules with comment containing MAC address
        $rules = $this->mikrotik_api->query('/ip/firewall/nat', [
            '?comment' => "*$macAddress*"
        ]);
        
        foreach ($rules as $rule) {
            $this->mikrotik_api->remove('/ip/firewall/nat', $rule['.id']);
        }
    }
    
    /**
     * Remove bridge filters for MAC address
     */
    private function removeBridgeFilters($macAddress) {
        // Get filters with comment containing MAC address
        $filters = $this->mikrotik_api->query('/interface/bridge/filter', [
            '?comment' => "*$macAddress*"
        ]);
        
        foreach ($filters as $filter) {
            $this->mikrotik_api->remove('/interface/bridge/filter', $filter['.id']);
        }
    }
    
    /**
     * Log connection
     */
    private function logConnection($macAddress, $ipAddress, $username, $userRole, $leaseId) {
        $stmt = $this->pdo->prepare("
            INSERT INTO dynamic_connection_logs (mac_address, ip_address, username, user_role, lease_id, action, created_at)
            VALUES (?, ?, ?, ?, ?, 'connection', NOW())
        ");
        $stmt->execute([$macAddress, $ipAddress, $username, $userRole, $leaseId]);
    }
    
    /**
     * Cleanup expired leases
     */
    public function cleanupExpiredLeases() {
        // Get expired leases
        $stmt = $this->pdo->prepare("
            SELECT * FROM dynamic_leases 
            WHERE expires_at <= NOW()
        ");
        $stmt->execute();
        $expiredLeases = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($expiredLeases as $lease) {
            // Remove from Mikrotik
            $this->removeDHCPLease($lease['lease_id']);
            $this->removeNATRules($lease['mac_address']);
            $this->removeBridgeFilters($lease['mac_address']);
            
            // Mark as expired in database
            $stmt = $this->pdo->prepare("
                UPDATE dynamic_leases 
                SET status = 'expired', expired_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$lease['id']]);
        }
        
        return count($expiredLeases);
    }
    
    /**
     * Remove DHCP lease from Mikrotik
     */
    private function removeDHCPLease($leaseId) {
        $this->mikrotik_api->remove('/ip/dhcp-server/lease', $leaseId);
    }
    
    /**
     * Get connection statistics
     */
    public function getConnectionStats() {
        $stats = [
            'total_leases' => 0,
            'active_leases' => 0,
            'expired_leases' => 0,
            'users_by_role' => [],
            'bandwidth_usage' => []
        ];
        
        // Total leases
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM dynamic_leases");
        $stats['total_leases'] = $stmt->fetchColumn();
        
        // Active leases
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM dynamic_leases WHERE expires_at > NOW()");
        $stats['active_leases'] = $stmt->fetchColumn();
        
        // Expired leases
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM dynamic_leases WHERE expires_at <= NOW()");
        $stats['expired_leases'] = $stmt->fetchColumn();
        
        // Users by role
        $stmt = $this->pdo->query("
            SELECT user_role, COUNT(*) as count 
            FROM dynamic_leases 
            WHERE expires_at > NOW() 
            GROUP BY user_role
        ");
        $stats['users_by_role'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return $stats;
    }
}

// API endpoint for dynamic network control
if (isset($_GET['api']) && $_GET['api'] === 'dynamic_network') {
    header('Content-Type: application/json');
    
    $controller = new DynamicNetworkController();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'connect':
            $macAddress = $_POST['mac_address'] ?? '';
            $username = $_POST['username'] ?? null;
            $userRole = $_POST['user_role'] ?? 'guest';
            
            if (empty($macAddress)) {
                echo json_encode(['success' => false, 'error' => 'MAC address required']);
                exit;
            }
            
            $result = $controller->processNewConnection($macAddress, $username, $userRole);
            echo json_encode($result);
            break;
            
        case 'authenticate':
            $macAddress = $_POST['mac_address'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($macAddress) || empty($username) || empty($password)) {
                echo json_encode(['success' => false, 'error' => 'All fields required']);
                exit;
            }
            
            $result = $controller->handlePortalAuthentication($macAddress, $username, $password);
            echo json_encode($result);
            break;
            
        case 'cleanup':
            $count = $controller->cleanupExpiredLeases();
            echo json_encode(['success' => true, 'cleaned' => $count]);
            break;
            
        case 'stats':
            $stats = $controller->getConnectionStats();
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?> 

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?>
