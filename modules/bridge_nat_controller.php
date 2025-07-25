<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login
require_login();

$pageTitle = 'Bridge Nat Controller';
ob_start();
?>

/**
 * Bridge NAT/Mangle Controller
 * Controls traffic flow through bridged interfaces using NAT and mangle rules
 * No DHCP dependency - pure traffic control via bridge filters and NAT
 */

class BridgeNATController {
    private $pdo;
    private $mikrotik_api;
    private $config;
    private $mock_mode;
    
    public function __construct($mock_mode = true) {
        $this->mock_mode = $mock_mode;
        $this->pdo = $this->getDatabaseConnection();
        $this->mikrotik_api = $this->getMikrotikAPI();
        $this->config = $this->loadConfig();
    }
    
    // Database connection
    private function getDatabaseConnection() {
        if ($this->mock_mode) {
            // Return a mock PDO for testing
            return new class {
                public function prepare($sql) {
                    return new class {
                        public function execute($params = []) {
                            return true;
                        }
                        public function fetch($mode = null) {
                            return null;
                        }
                        public function fetchAll($mode = null) {
                            return [];
                        }
                        public function fetchColumn() {
                            return 0;
                        }
                    };
                }
                public function query($sql) {
                    return new class {
                        public function fetchAll($mode = null) {
                            return [];
                        }
                        public function fetchColumn() {
                            return 0;
                        }
                    };
                }
            };
        } else {
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=slms", "username", "password");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
            } catch (PDOException $e) {
                return null;
            }
        }
    }
    
    // Mikrotik API connection
    private function getMikrotikAPI() {
        if ($this->mock_mode) {
            // Return a mock API for testing
            return new class {
                public function execute($command) {
                    return ['success' => true, 'data' => ['ret' => 'mock_rule_id']];
                }
                public function query($path, $conditions = []) {
                    return [];
                }
                public function remove($path, $id) {
                    return ['success' => true];
                }
                public function add($path, $data) {
                    return ['success' => true, 'data' => ['ret' => 'mock_rule_id']];
                }
            };
        } else {
            require_once 'modules/mikrotik_api.php';
            return new MikrotikAPI('192.168.1.1', 'admin', 'password', 8728);
        }
    }
    
    // Load configuration
    private function loadConfig() {
        return [
            'bridge_name' => 'bridge1',
            'interface1' => 'eth0',  // First bridged interface
            'interface2' => 'eth1',  // Second bridged interface
            'captive_portal_ip' => '192.168.100.1',
            'captive_portal_port' => '8080',
            'dns_servers' => ['8.8.8.8', '8.8.4.4'],
            'nat_chain' => 'bridge_nat',
            'mangle_chain' => 'bridge_mangle',
            'bridge_filter_chain' => 'bridge_filter',
            'session_timeout' => 3600, // 1 hour
            'enable_bandwidth_monitoring' => true,
            'enable_connection_tracking' => true
        ];
    }
    
    /**
     * Initialize bridge NAT/mangle system
     * Sets up the basic bridge filtering and NAT chains
     */
    public function initializeBridgeSystem() {
        try {
            // 1. Create bridge filter chain
            $this->createBridgeFilterChain();
            
            // 2. Create NAT chain for bridge traffic
            $this->createNATChain();
            
            // 3. Create mangle chain for connection marking
            $this->createMangleChain();
            
            // 4. Set up basic bridge filtering rules
            $this->setupBasicBridgeFilters();
            
            // 5. Enable bridge-netfilter
            $this->enableBridgeNetfilter();
            
            return ['success' => true, 'message' => 'Bridge NAT system initialized'];
            
        } catch (Exception $e) {
            error_log("Bridge NAT Initialization Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create bridge filter chain
     */
    private function createBridgeFilterChain() {
        // Create bridge filter table and chain
        $commands = [
            "/interface/bridge/filter/add chain=forward name={$this->config['bridge_filter_chain']}",
            "/interface/bridge/filter/add chain=input name={$this->config['bridge_filter_chain']}_input",
            "/interface/bridge/filter/add chain=output name={$this->config['bridge_filter_chain']}_output"
        ];
        
        foreach ($commands as $command) {
            $this->mikrotik_api->execute($command);
        }
    }
    
    /**
     * Create NAT chain for bridge traffic
     */
    private function createNATChain() {
        $commands = [
            "/ip/firewall/nat/add chain={$this->config['nat_chain']} action=masquerade comment='Bridge NAT chain'",
            "/ip/firewall/nat/add chain={$this->config['nat_chain']} action=redirect dst-port=80 protocol=tcp to-ports={$this->config['captive_portal_port']} comment='HTTP redirect to captive portal'"
        ];
        
        foreach ($commands as $command) {
            $this->mikrotik_api->execute($command);
        }
    }
    
    /**
     * Create mangle chain for connection marking
     */
    private function createMangleChain() {
        $commands = [
            "/ip/firewall/mangle/add chain=prerouting name={$this->config['mangle_chain']} action=mark-connection new-connection-mark=captive_portal comment='Mark captive portal connections'",
            "/ip/firewall/mangle/add chain=prerouting connection-mark=captive_portal action=mark-packet new-packet-mark=captive_portal_packet comment='Mark captive portal packets'"
        ];
        
        foreach ($commands as $command) {
            $this->mikrotik_api->execute($command);
        }
    }
    
    /**
     * Set up basic bridge filtering rules
     */
    private function setupBasicBridgeFilters() {
        // Allow ARP traffic
        $this->mikrotik_api->execute("/interface/bridge/filter/add chain=forward protocol=arp action=accept comment='Allow ARP'");
        
        // Allow DNS traffic
        foreach ($this->config['dns_servers'] as $dns) {
            $this->mikrotik_api->execute("/interface/bridge/filter/add chain=forward dst-address=$dns protocol=udp dst-port=53 action=accept comment='Allow DNS to $dns'");
        }
        
        // Allow captive portal access
        $this->mikrotik_api->execute("/interface/bridge/filter/add chain=forward dst-address={$this->config['captive_portal_ip']} action=accept comment='Allow captive portal access'");
        
        // Drop all other traffic by default (will be overridden by authenticated users)
        $this->mikrotik_api->execute("/interface/bridge/filter/add chain=forward action=drop comment='Default drop for unauthenticated users'");
    }
    
    /**
     * Enable bridge-netfilter for iptables integration
     */
    private function enableBridgeNetfilter() {
        // Enable bridge-netfilter module
        $this->mikrotik_api->execute("/system/resource/irq/print");
        
        // Set bridge settings
        $this->mikrotik_api->execute("/interface/bridge/settings/set use-ip-firewall=yes use-ip-firewall-for-vlan=yes");
    }
    
    /**
     * Process new user connection via bridge filtering
     * No DHCP - pure traffic control
     */
    public function processBridgeConnection($macAddress, $username = null, $userRole = 'guest') {
        try {
            // 1. Check if user already has bridge access
            $existingAccess = $this->getExistingBridgeAccess($macAddress);
            if ($existingAccess) {
                return $this->reactivateBridgeAccess($existingAccess, $username, $userRole);
            }
            
            // 2. Create bridge filter rules for user
            $filterRules = $this->createBridgeFiltersForUser($macAddress, $userRole);
            
            // 3. Create NAT rules for user
            $natRules = $this->createNATRulesForUser($macAddress, $userRole);
            
            // 4. Create mangle rules for user
            $mangleRules = $this->createMangleRulesForUser($macAddress, $userRole);
            
            // 5. Log the bridge connection
            $this->logBridgeConnection($macAddress, $username, $userRole, $filterRules, $natRules, $mangleRules);
            
            return [
                'success' => true,
                'mac_address' => $macAddress,
                'user_role' => $userRole,
                'filter_rules' => $filterRules,
                'nat_rules' => $natRules,
                'mangle_rules' => $mangleRules,
                'session_timeout' => $this->config['session_timeout']
            ];
            
        } catch (Exception $e) {
            error_log("Bridge Connection Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create bridge filter rules for user based on role
     */
    private function createBridgeFiltersForUser($macAddress, $userRole) {
        $rules = [];
        
        switch ($userRole) {
            case 'guest':
                // Limited access for guests
                $rules[] = $this->addBridgeFilter($macAddress, 'accept', [
                    'dst-address' => '192.168.100.1',
                    'comment' => "Guest access to gateway for $macAddress"
                ]);
                
                $rules[] = $this->addBridgeFilter($macAddress, 'accept', [
                    'dst-address' => '8.8.8.8',
                    'protocol' => 'udp',
                    'dst-port' => '53',
                    'comment' => "Guest DNS access for $macAddress"
                ]);
                
                // Allow specific domains for guests
                $guestDomains = ['google.com', 'gmail.com', 'facebook.com'];
                foreach ($guestDomains as $domain) {
                    $rules[] = $this->addBridgeFilter($macAddress, 'accept', [
                        'dst-address' => $this->resolveDomain($domain),
                        'comment' => "Guest access to $domain for $macAddress"
                    ]);
                }
                
                // Drop all other traffic
                $rules[] = $this->addBridgeFilter($macAddress, 'drop', [
                    'comment' => "Guest traffic drop for $macAddress"
                ]);
                break;
                
            case 'user':
                // Full access for authenticated users
                $rules[] = $this->addBridgeFilter($macAddress, 'accept', [
                    'comment' => "Full access for user $macAddress"
                ]);
                break;
                
            case 'admin':
                // Unrestricted access for admins
                $rules[] = $this->addBridgeFilter($macAddress, 'accept', [
                    'comment' => "Admin unrestricted access for $macAddress"
                ]);
                break;
        }
        
        return $rules;
    }
    
    /**
     * Add bridge filter rule
     */
    private function addBridgeFilter($macAddress, $action, $conditions) {
        $command = "/interface/bridge/filter/add chain=forward action=$action src-mac-address=$macAddress";
        
        foreach ($conditions as $key => $value) {
            if ($key !== 'comment') {
                $command .= " $key=$value";
            }
        }
        
        if (isset($conditions['comment'])) {
            $command .= " comment=\"{$conditions['comment']}\"";
        }
        
        $response = $this->mikrotik_api->execute($command);
        
        // Store in database
        $stmt = $this->pdo->prepare("
            INSERT INTO bridge_filter_rules (mac_address, action, rule_data, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$macAddress, $action, json_encode($conditions)]);
        
        return [
            'type' => 'bridge_filter',
            'action' => $action,
            'conditions' => $conditions,
            'response' => $response
        ];
    }
    
    /**
     * Create NAT rules for user
     */
    private function createNATRulesForUser($macAddress, $userRole) {
        $rules = [];
        
        switch ($userRole) {
            case 'guest':
                // Limited NAT for guests
                $rules[] = $this->addNATRule($macAddress, 'masquerade', [
                    'comment' => "Guest NAT for $macAddress"
                ]);
                
                // Redirect HTTP to captive portal
                $rules[] = $this->addNATRule($macAddress, 'redirect', [
                    'dst-port' => '80',
                    'protocol' => 'tcp',
                    'to-ports' => $this->config['captive_portal_port'],
                    'comment' => "Guest HTTP redirect for $macAddress"
                ]);
                break;
                
            case 'user':
            case 'admin':
                // Full NAT access
                $rules[] = $this->addNATRule($macAddress, 'masquerade', [
                    'comment' => "Full NAT for $macAddress ($userRole)"
                ]);
                break;
        }
        
        return $rules;
    }
    
    /**
     * Add NAT rule
     */
    private function addNATRule($macAddress, $action, $conditions) {
        $command = "/ip/firewall/nat/add chain={$this->config['nat_chain']} action=$action";
        
        foreach ($conditions as $key => $value) {
            if ($key !== 'comment') {
                $command .= " $key=$value";
            }
        }
        
        if (isset($conditions['comment'])) {
            $command .= " comment=\"{$conditions['comment']}\"";
        }
        
        $response = $this->mikrotik_api->execute($command);
        
        // Store in database
        $stmt = $this->pdo->prepare("
            INSERT INTO bridge_nat_rules (mac_address, action, rule_data, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$macAddress, $action, json_encode($conditions)]);
        
        return [
            'type' => 'nat',
            'action' => $action,
            'conditions' => $conditions,
            'response' => $response
        ];
    }
    
    /**
     * Create mangle rules for user
     */
    private function createMangleRulesForUser($macAddress, $userRole) {
        $rules = [];
        
        // Mark connections based on user role
        $rules[] = $this->addMangleRule($macAddress, 'mark-connection', [
            'new-connection-mark' => "user_$userRole",
            'comment' => "Mark connections for $macAddress ($userRole)"
        ]);
        
        // Mark packets for bandwidth monitoring
        if ($this->config['enable_bandwidth_monitoring']) {
            $rules[] = $this->addMangleRule($macAddress, 'mark-packet', [
                'connection-mark' => "user_$userRole",
                'new-packet-mark' => "user_$userRole" . "_packet",
                'comment' => "Mark packets for bandwidth monitoring - $macAddress"
            ]);
        }
        
        return $rules;
    }
    
    /**
     * Add mangle rule
     */
    private function addMangleRule($macAddress, $action, $conditions) {
        $command = "/ip/firewall/mangle/add chain=prerouting action=$action";
        
        foreach ($conditions as $key => $value) {
            if ($key !== 'comment') {
                $command .= " $key=$value";
            }
        }
        
        if (isset($conditions['comment'])) {
            $command .= " comment=\"{$conditions['comment']}\"";
        }
        
        $response = $this->mikrotik_api->execute($command);
        
        // Store in database
        $stmt = $this->pdo->prepare("
            INSERT INTO bridge_mangle_rules (mac_address, action, rule_data, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$macAddress, $action, json_encode($conditions)]);
        
        return [
            'type' => 'mangle',
            'action' => $action,
            'conditions' => $conditions,
            'response' => $response
        ];
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
        
        // Update existing bridge access or create new one
        $existingAccess = $this->getExistingBridgeAccess($macAddress);
        if ($existingAccess) {
            // Update access with new user info
            $this->updateBridgeAccess($existingAccess['id'], $username, $user['role']);
            
            // Update rules for new role
            $this->updateBridgeRules($macAddress, $user['role']);
            
            return [
                'success' => true,
                'user_role' => $user['role'],
                'message' => 'Authentication successful - bridge access updated'
            ];
        } else {
            // Create new bridge connection
            return $this->processBridgeConnection($macAddress, $username, $user['role']);
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
     * Get existing bridge access for MAC address
     */
    private function getExistingBridgeAccess($macAddress) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM bridge_access 
            WHERE mac_address = ? AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$macAddress]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Reactivate existing bridge access
     */
    private function reactivateBridgeAccess($access, $username, $userRole) {
        // Extend access time
        $stmt = $this->pdo->prepare("
            UPDATE bridge_access 
            SET expires_at = DATE_ADD(NOW(), INTERVAL ? SECOND),
                username = COALESCE(?, username),
                last_activity = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$this->config['session_timeout'], $username, $access['id']]);
        
        return [
            'success' => true,
            'mac_address' => $access['mac_address'],
            'user_role' => $userRole,
            'message' => 'Bridge access reactivated'
        ];
    }
    
    /**
     * Update bridge access user information
     */
    private function updateBridgeAccess($accessId, $username, $userRole) {
        $stmt = $this->pdo->prepare("
            UPDATE bridge_access 
            SET username = ?, user_role = ?, last_activity = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$username, $userRole, $accessId]);
    }
    
    /**
     * Update bridge rules for user
     */
    private function updateBridgeRules($macAddress, $userRole) {
        // Remove old rules
        $this->removeBridgeRules($macAddress);
        
        // Create new rules
        $this->createBridgeFiltersForUser($macAddress, $userRole);
        $this->createNATRulesForUser($macAddress, $userRole);
        $this->createMangleRulesForUser($macAddress, $userRole);
    }
    
    /**
     * Remove bridge rules for MAC address
     */
    private function removeBridgeRules($macAddress) {
        // Remove bridge filters
        $filters = $this->mikrotik_api->query('/interface/bridge/filter', [
            '?comment' => "*$macAddress*"
        ]);
        
        foreach ($filters as $filter) {
            $this->mikrotik_api->remove('/interface/bridge/filter', $filter['.id']);
        }
        
        // Remove NAT rules
        $natRules = $this->mikrotik_api->query('/ip/firewall/nat', [
            '?comment' => "*$macAddress*"
        ]);
        
        foreach ($natRules as $rule) {
            $this->mikrotik_api->remove('/ip/firewall/nat', $rule['.id']);
        }
        
        // Remove mangle rules
        $mangleRules = $this->mikrotik_api->query('/ip/firewall/mangle', [
            '?comment' => "*$macAddress*"
        ]);
        
        foreach ($mangleRules as $rule) {
            $this->mikrotik_api->remove('/ip/firewall/mangle', $rule['.id']);
        }
    }
    
    /**
     * Log bridge connection
     */
    private function logBridgeConnection($macAddress, $username, $userRole, $filterRules, $natRules, $mangleRules) {
        // Store bridge access record
        $stmt = $this->pdo->prepare("
            INSERT INTO bridge_access (mac_address, username, user_role, expires_at, created_at)
            VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), NOW())
        ");
        $stmt->execute([$macAddress, $username, $userRole, $this->config['session_timeout']]);
        
        // Log the connection
        $stmt = $this->pdo->prepare("
            INSERT INTO bridge_connection_logs (mac_address, username, user_role, action, details, created_at)
            VALUES (?, ?, ?, 'connection', ?, NOW())
        ");
        $stmt->execute([$macAddress, $username, $userRole, json_encode([
            'filter_rules' => $filterRules,
            'nat_rules' => $natRules,
            'mangle_rules' => $mangleRules
        ])]);
    }
    
    /**
     * Resolve domain to IP address
     */
    private function resolveDomain($domain) {
        $ip = gethostbyname($domain);
        return $ip !== $domain ? $ip : null;
    }
    
    /**
     * Cleanup expired bridge access
     */
    public function cleanupExpiredAccess() {
        // Get expired access
        $stmt = $this->pdo->prepare("
            SELECT * FROM bridge_access 
            WHERE expires_at <= NOW()
        ");
        $stmt->execute();
        $expiredAccess = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($expiredAccess as $access) {
            // Remove bridge rules
            $this->removeBridgeRules($access['mac_address']);
            
            // Mark as expired
            $stmt = $this->pdo->prepare("
                UPDATE bridge_access 
                SET status = 'expired', expired_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$access['id']]);
            
            // Log the expiry
            $stmt = $this->pdo->prepare("
                INSERT INTO bridge_connection_logs (mac_address, username, user_role, action, details, created_at)
                VALUES (?, ?, ?, 'expiry', ?, NOW())
            ");
            $stmt->execute([$access['mac_address'], $access['username'], $access['user_role'], 
                          json_encode(['reason' => 'automatic_expiry'])]);
        }
        
        return count($expiredAccess);
    }
    
    /**
     * Get bridge access statistics
     */
    public function getBridgeStats() {
        $stats = [
            'total_access' => 0,
            'active_access' => 0,
            'expired_access' => 0,
            'users_by_role' => [],
            'bridge_rules' => []
        ];
        
        // Total access
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM bridge_access");
        $stats['total_access'] = $stmt->fetchColumn();
        
        // Active access
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM bridge_access WHERE expires_at > NOW()");
        $stats['active_access'] = $stmt->fetchColumn();
        
        // Expired access
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM bridge_access WHERE expires_at <= NOW()");
        $stats['expired_access'] = $stmt->fetchColumn();
        
        // Users by role
        $stmt = $this->pdo->query("
            SELECT user_role, COUNT(*) as count 
            FROM bridge_access 
            WHERE expires_at > NOW() 
            GROUP BY user_role
        ");
        $stats['users_by_role'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Bridge rules count
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM bridge_filter_rules WHERE removed_at IS NULL");
        $stats['bridge_rules']['filters'] = $stmt->fetchColumn();
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM bridge_nat_rules WHERE removed_at IS NULL");
        $stats['bridge_rules']['nat'] = $stmt->fetchColumn();
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM bridge_mangle_rules WHERE removed_at IS NULL");
        $stats['bridge_rules']['mangle'] = $stmt->fetchColumn();
        
        return $stats;
    }
}

// API endpoint for bridge NAT control
if (isset($_GET['api']) && $_GET['api'] === 'bridge_nat') {
    header('Content-Type: application/json');
    
    $controller = new BridgeNATController();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'initialize':
            $result = $controller->initializeBridgeSystem();
            echo json_encode($result);
            break;
            
        case 'connect':
            $macAddress = $_POST['mac_address'] ?? '';
            $username = $_POST['username'] ?? null;
            $userRole = $_POST['user_role'] ?? 'guest';
            
            if (empty($macAddress)) {
                echo json_encode(['success' => false, 'error' => 'MAC address required']);
                exit;
            }
            
            $result = $controller->processBridgeConnection($macAddress, $username, $userRole);
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
            $count = $controller->cleanupExpiredAccess();
            echo json_encode(['success' => true, 'cleaned' => $count]);
            break;
            
        case 'stats':
            $stats = $controller->getBridgeStats();
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
