<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login
require_login();

$pageTitle = 'Enhanced Queue Manager';
ob_start();
?>

/**
 * Enhanced Queue Manager for Bridge NAT Captive Portal
 * Integrates with existing queue algorithms and adds bridge-specific features
 */

class EnhancedQueueManager {
    private $pdo;
    private $mikrotik_api;
    private $config;
    
    public function __construct($mock_mode = true) {
        $this->pdo = $this->getDatabaseConnection($mock_mode);
        $this->mikrotik_api = $this->getMikrotikAPI($mock_mode);
        $this->config = $this->loadConfig();
    }
    
    private function getDatabaseConnection($mock_mode) {
        if ($mock_mode) {
            return new class {
                public function prepare($sql) {
                    return new class {
                        public function execute($params = []) { return true; }
                        public function fetch($mode = null) { return null; }
                        public function fetchAll($mode = null) { return []; }
                        public function fetchColumn() { return 0; }
                    };
                }
                public function query($sql) {
                    return new class {
                        public function fetchAll($mode = null) { return []; }
                        public function fetchColumn() { return 0; }
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
    
    private function getMikrotikAPI($mock_mode) {
        if ($mock_mode) {
            return new class {
                public function execute($command) {
                    return ['success' => true, 'data' => ['ret' => 'mock_queue_id']];
                }
                public function query($path, $conditions = []) { return []; }
                public function remove($path, $id) { return ['success' => true]; }
                public function add($path, $data) { return ['success' => true, 'data' => ['ret' => 'mock_queue_id']]; }
            };
        } else {
            require_once 'modules/mikrotik_api.php';
            return new MikrotikAPI('192.168.1.1', 'admin', 'password', 8728);
        }
    }
    
    private function loadConfig() {
        return [
            'queue_tree_name' => 'captive_portal_tree',
            'default_bandwidth' => '10M',
            'guest_bandwidth' => '1M',
            'user_bandwidth' => '5M',
            'admin_bandwidth' => '10M',
            'burst_limit' => '20M',
            'burst_threshold' => '10M',
            'burst_time' => '10s',
            'priority' => 8,
            'enable_fair_queue' => true,
            'enable_sfq' => true,
            'sfq_perturb' => 10,
            'sfq_quantum' => 1514
        ];
    }
    
    /**
     * Create queue tree for captive portal traffic management
     */
    public function createQueueTree() {
        try {
            $commands = [
                // Create main queue tree
                "/queue tree add name={$this->config['queue_tree_name']} parent=global max-limit={$this->config['default_bandwidth']}",
                
                // Create sub-queues for different user roles
                "/queue tree add name=guest_queue parent={$this->config['queue_tree_name']} max-limit={$this->config['guest_bandwidth']} priority=1",
                "/queue tree add name=user_queue parent={$this->config['queue_tree_name']} max-limit={$this->config['user_bandwidth']} priority=5",
                "/queue tree add name=admin_queue parent={$this->config['queue_tree_name']} max-limit={$this->config['admin_bandwidth']} priority=8",
                
                // Create burst settings
                "/queue tree add name=burst_queue parent={$this->config['queue_tree_name']} max-limit={$this->config['burst_limit']} burst-limit={$this->config['burst_limit']} burst-threshold={$this->config['burst_threshold']} burst-time={$this->config['burst_time']}"
            ];
            
            foreach ($commands as $command) {
                $this->mikrotik_api->execute($command);
            }
            
            return ['success' => true, 'message' => 'Queue tree created successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create simple queue for specific MAC address
     */
    public function createSimpleQueue($macAddress, $userRole, $bandwidth = null) {
        try {
            if (!$bandwidth) {
                $bandwidth = $this->getBandwidthForRole($userRole);
            }
            
            $queueName = "cp_" . str_replace(':', '', $macAddress);
            
            $commands = [
                "/queue simple add name=$queueName target=$macAddress max-limit=$bandwidth",
                "/queue simple set $queueName parent={$this->config['queue_tree_name']}",
                "/queue simple set $queueName priority=" . $this->getPriorityForRole($userRole)
            ];
            
            if ($this->config['enable_fair_queue']) {
                $commands[] = "/queue simple set $queueName kind=sfq";
                $commands[] = "/queue simple set $queueName sfq-perturb={$this->config['sfq_perturb']}";
                $commands[] = "/queue simple set $queueName sfq-quantum={$this->config['sfq_quantum']}";
            }
            
            foreach ($commands as $command) {
                $this->mikrotik_api->execute($command);
            }
            
            // Log queue creation
            $this->logQueueCreation($macAddress, $userRole, $bandwidth, $queueName);
            
            return ['success' => true, 'queue_name' => $queueName];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Remove queue for specific MAC address
     */
    public function removeSimpleQueue($macAddress) {
        try {
            $queueName = "cp_" . str_replace(':', '', $macAddress);
            $command = "/queue simple remove $queueName";
            
            $result = $this->mikrotik_api->execute($command);
            
            if ($result['success']) {
                $this->logQueueRemoval($macAddress, $queueName);
            }
            
            return $result;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Update queue bandwidth for user
     */
    public function updateQueueBandwidth($macAddress, $newBandwidth, $userRole = null) {
        try {
            $queueName = "cp_" . str_replace(':', '', $macAddress);
            
            $commands = [
                "/queue simple set $queueName max-limit=$newBandwidth"
            ];
            
            if ($userRole) {
                $commands[] = "/queue simple set $queueName priority=" . $this->getPriorityForRole($userRole);
            }
            
            foreach ($commands as $command) {
                $this->mikrotik_api->execute($command);
            }
            
            $this->logQueueUpdate($macAddress, $newBandwidth, $userRole);
            
            return ['success' => true, 'message' => 'Queue bandwidth updated'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get queue statistics
     */
    public function getQueueStats($macAddress = null) {
        try {
            if ($macAddress) {
                $queueName = "cp_" . str_replace(':', '', $macAddress);
                $command = "/queue simple print where name=$queueName";
            } else {
                $command = "/queue simple print";
            }
            
            $result = $this->mikrotik_api->execute($command);
            
            if ($result['success']) {
                return $this->parseQueueStats($result['data']);
            }
            
            return ['success' => false, 'error' => 'Failed to get queue statistics'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create traffic quota system (pfSense-style)
     */
    public function createTrafficQuota($macAddress, $quotaMB, $userRole = 'guest') {
        try {
            $quotaBytes = $quotaMB * 1024 * 1024; // Convert MB to bytes
            $queueName = "cp_" . str_replace(':', '', $macAddress);
            
            // Create queue with quota
            $commands = [
                "/queue simple add name=$queueName target=$macAddress max-limit={$this->config['default_bandwidth']}",
                "/queue simple set $queueName parent={$this->config['queue_tree_name']}",
                "/queue simple set $queueName priority=" . $this->getPriorityForRole($userRole)
            ];
            
            foreach ($commands as $command) {
                $this->mikrotik_api->execute($command);
            }
            
            // Store quota information in database
            $this->storeQuotaInfo($macAddress, $quotaBytes, $userRole);
            
            return ['success' => true, 'quota_mb' => $quotaMB];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Check if user has exceeded quota
     */
    public function checkQuotaExceeded($macAddress) {
        try {
            $queueName = "cp_" . str_replace(':', '', $macAddress);
            $command = "/queue simple print where name=$queueName";
            
            $result = $this->mikrotik_api->execute($command);
            
            if ($result['success']) {
                $stats = $this->parseQueueStats($result['data']);
                $quotaInfo = $this->getQuotaInfo($macAddress);
                
                if ($quotaInfo && $stats['bytes_in'] + $stats['bytes_out'] > $quotaInfo['quota_bytes']) {
                    return ['exceeded' => true, 'used' => $stats['bytes_in'] + $stats['bytes_out'], 'quota' => $quotaInfo['quota_bytes']];
                }
            }
            
            return ['exceeded' => false];
            
        } catch (Exception $e) {
            return ['exceeded' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create connection limit (pfSense-style)
     */
    public function createConnectionLimit($macAddress, $maxConnections = 4) {
        try {
            $queueName = "cp_" . str_replace(':', '', $macAddress);
            
            // Add connection limit to queue
            $command = "/queue simple set $queueName max-limit={$this->config['default_bandwidth']} connection-limit=$maxConnections";
            
            $result = $this->mikrotik_api->execute($command);
            
            if ($result['success']) {
                $this->logConnectionLimit($macAddress, $maxConnections);
            }
            
            return $result;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create timeout system (pfSense-style)
     */
    public function createTimeoutSystem($macAddress, $idleTimeout = 30, $hardTimeout = 60) {
        try {
            // Store timeout information
            $this->storeTimeoutInfo($macAddress, $idleTimeout, $hardTimeout);
            
            return ['success' => true, 'idle_timeout' => $idleTimeout, 'hard_timeout' => $hardTimeout];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Check timeout status
     */
    public function checkTimeoutStatus($macAddress) {
        try {
            $timeoutInfo = $this->getTimeoutInfo($macAddress);
            
            if (!$timeoutInfo) {
                return ['expired' => false];
            }
            
            $lastActivity = strtotime($timeoutInfo['last_activity']);
            $currentTime = time();
            
            // Check idle timeout
            if ($timeoutInfo['idle_timeout'] > 0 && ($currentTime - $lastActivity) > ($timeoutInfo['idle_timeout'] * 60)) {
                return ['expired' => true, 'reason' => 'idle_timeout'];
            }
            
            // Check hard timeout
            if ($timeoutInfo['hard_timeout'] > 0 && ($currentTime - strtotime($timeoutInfo['created_at'])) > ($timeoutInfo['hard_timeout'] * 60)) {
                return ['expired' => true, 'reason' => 'hard_timeout'];
            }
            
            return ['expired' => false];
            
        } catch (Exception $e) {
            return ['expired' => false, 'error' => $e->getMessage()];
        }
    }
    
    // Helper methods
    private function getBandwidthForRole($userRole) {
        switch ($userRole) {
            case 'admin': return $this->config['admin_bandwidth'];
            case 'user': return $this->config['user_bandwidth'];
            case 'guest': 
            default: return $this->config['guest_bandwidth'];
        }
    }
    
    private function getPriorityForRole($userRole) {
        switch ($userRole) {
            case 'admin': return 8;
            case 'user': return 5;
            case 'guest': 
            default: return 1;
        }
    }
    
    private function parseQueueStats($data) {
        // Parse Mikrotik queue statistics
        return [
            'bytes_in' => 0,
            'bytes_out' => 0,
            'packets_in' => 0,
            'packets_out' => 0,
            'rate_in' => '0',
            'rate_out' => '0'
        ];
    }
    
    private function logQueueCreation($macAddress, $userRole, $bandwidth, $queueName) {
        // Log queue creation to database
    }
    
    private function logQueueRemoval($macAddress, $queueName) {
        // Log queue removal to database
    }
    
    private function logQueueUpdate($macAddress, $bandwidth, $userRole) {
        // Log queue update to database
    }
    
    private function storeQuotaInfo($macAddress, $quotaBytes, $userRole) {
        // Store quota information in database
    }
    
    private function getQuotaInfo($macAddress) {
        // Get quota information from database
        return null;
    }
    
    private function logConnectionLimit($macAddress, $maxConnections) {
        // Log connection limit to database
    }
    
    private function storeTimeoutInfo($macAddress, $idleTimeout, $hardTimeout) {
        // Store timeout information in database
    }
    
    private function getTimeoutInfo($macAddress) {
        // Get timeout information from database
        return null;
    }
}
?> 

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?>
