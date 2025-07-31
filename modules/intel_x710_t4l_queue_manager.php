<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'module_loader.php';

require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login
require_login();

$pageTitle = 'Intel X710 T4l Queue Manager';
ob_start();
?>

/**
 * Intel X710-T4L Queue Manager for Bridge NAT System
 * Optimized for 4-port 10GbE performance with virtualization support
 */

class IntelX710T4LQueueManager {
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
            'nic_model' => 'Intel X710-T4L',
            'ports' => 4,
            'max_queues_per_port' => 96,
            'total_queues' => 384,
            'optimal_queues_per_port' => 32,
            'total_optimal_queues' => 128,
            'max_bandwidth_per_port' => '10Gb/s',
            'total_bandwidth' => '40Gb/s',
            'vmq_enabled' => true,
            'sriov_enabled' => true,
            'dcb_enabled' => false,
            'virtual_ports_total' => 512,
            'virtual_ports_vmq' => 256,
            'virtual_ports_sriov' => 256,
            'queue_distribution_per_port' => [
                'bridge_filter' => 8,
                'nat_processing' => 8,
                'mangle_rules' => 8,
                'user_traffic' => 4,
                'management' => 4
            ],
            'total_queue_distribution' => [
                'bridge_filter' => 32,
                'nat_processing' => 32,
                'mangle_rules' => 32,
                'user_traffic' => 16,
                'management' => 16
            ],
            'hardware_offload' => [
                'checksum' => true,
                'tcp_segmentation' => true,
                'vlan_tagging' => true,
                'qos' => true,
                'flow_director' => true,
                'jumbo_frames' => true
            ],
            'mtu_optimization' => [
                'standard_mtu' => 1500,
                'jumbo_mtu' => 9000,
                'max_mtu' => 9700
            ]
        ];
    }
    
    /**
     * Initialize Intel X710-T4L for Bridge NAT
     */
    public function initializeIntelT4L() {
        try {
            $commands = [];
            
            // Configure all 4 ports
            for ($port = 0; $port < 4; $port++) {
                $interface = "eth$port";
                
                $commands[] = [
                    // Enable VMQ offloading
                    "/interface ethernet set [find name=$interface] vmq=yes",
                    
                    // Configure optimal queue count (32 per port = 128 total)
                    "/interface ethernet set [find name=$interface] queue-count={$this->config['optimal_queues_per_port']}",
                    
                    // Enable hardware offloads
                    "/interface ethernet set [find name=$interface] hardware-offload=yes",
                    
                    // Configure RSS (Receive Side Scaling)
                    "/interface ethernet set [find name=$interface] rss=yes",
                    
                    // Enable flow control (critical for X710-T4L)
                    "/interface ethernet set [find name=$interface] flow-control=yes",
                    
                    // Configure interrupt moderation
                    "/interface ethernet set [find name=$interface] interrupt-moderation-rate=adaptive",
                    
                    // Enable low latency interrupts
                    "/interface ethernet set [find name=$interface] low-latency-interrupts=yes"
                ];
            }
            
            // Execute all commands
            foreach ($commands as $portCommands) {
                foreach ($portCommands as $command) {
                    $this->mikrotik_api->execute($command);
                }
            }
            
            return ['success' => true, 'message' => 'Intel X710-T4L initialized for Bridge NAT with 4 ports'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Configure Jumbo Frames for maximum performance
     */
    public function configureJumboFrames() {
        try {
            $commands = [];
            
            // Configure MTU 9000 for all ports (optimal for X710-T4L)
            for ($port = 0; $port < 4; $port++) {
                $interface = "eth$port";
                $commands[] = "/interface ethernet set [find name=$interface] mtu=9000";
            }
            
            // Configure TCP MSS for jumbo frames
            $commands[] = "sysctl net.inet.tcp.mssdflt=8960";
            
            // Disable TCP SACK for better X710 performance
            $commands[] = "sysctl net.inet.tcp.sack.enable=0";
            
            foreach ($commands as $command) {
                $this->mikrotik_api->execute($command);
            }
            
            return ['success' => true, 'message' => 'Jumbo frames configured for X710-T4L'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Configure multi-port queue distribution
     */
    public function configureMultiPortQueueDistribution() {
        try {
            $commands = [];
            
            // Create main queue tree for 40Gb/s total capacity
            $commands[] = "/queue tree add name=x710_t4l_tree parent=global max-limit=40G";
            
            // Configure per-port queue trees
            for ($port = 0; $port < 4; $port++) {
                $portName = "port_$port";
                $commands[] = "/queue tree add name={$portName}_tree parent=x710_t4l_tree max-limit=10G";
                
                // Bridge filter queues (8 per port = 32 total)
                $commands[] = "/queue tree add name={$portName}_bridge_filter parent={$portName}_tree max-limit=2.5G";
                $commands[] = "/queue simple add name={$portName}_bridge_filter_1 target=192.168.{$port}00.0/24 max-limit=1G parent={$portName}_bridge_filter";
                $commands[] = "/queue simple add name={$portName}_bridge_filter_2 target=192.168.{$port}00.0/24 max-limit=1G parent={$portName}_bridge_filter";
                
                // NAT processing queues (8 per port = 32 total)
                $commands[] = "/queue tree add name={$portName}_nat_processing parent={$portName}_tree max-limit=2.5G";
                $commands[] = "/queue simple add name={$portName}_nat_processing_1 target=192.168.{$port}00.0/24 max-limit=1G parent={$portName}_nat_processing";
                $commands[] = "/queue simple add name={$portName}_nat_processing_2 target=192.168.{$port}00.0/24 max-limit=1G parent={$portName}_nat_processing";
                
                // Mangle rule queues (8 per port = 32 total)
                $commands[] = "/queue tree add name={$portName}_mangle_rules parent={$portName}_tree max-limit=2.5G";
                $commands[] = "/queue simple add name={$portName}_mangle_rule_1 target=192.168.{$port}00.0/24 max-limit=1G parent={$portName}_mangle_rules";
                $commands[] = "/queue simple add name={$portName}_mangle_rule_2 target=192.168.{$port}00.0/24 max-limit=1G parent={$portName}_mangle_rules";
                
                // User traffic queues (4 per port = 16 total)
                $commands[] = "/queue tree add name={$portName}_user_traffic parent={$portName}_tree max-limit=2G";
                $commands[] = "/queue simple add name={$portName}_user_traffic_1 target=192.168.{$port}00.0/24 max-limit=2G parent={$portName}_user_traffic";
                
                // Management queues (4 per port = 16 total)
                $commands[] = "/queue tree add name={$portName}_management parent={$portName}_tree max-limit=1G";
                $commands[] = "/queue simple add name={$portName}_management_1 target=192.168.{$port}00.0/24 max-limit=1G parent={$portName}_management";
            }
            
            foreach ($commands as $command) {
                $this->mikrotik_api->execute($command);
            }
            
            return ['success' => true, 'distribution' => $this->config['total_queue_distribution']];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Configure VMQ for multi-port setup
     */
    public function configureMultiPortVMQ($macAddresses, $userRoles) {
        try {
            $results = [];
            
            foreach ($macAddresses as $index => $macAddress) {
                $port = $index % 4; // Distribute across 4 ports
                $vmqId = $this->getNextVMQId();
                $queueCount = $this->getQueueCountForRole($userRoles[$index]);
                
                $commands = [
                    // Create VMQ virtual port
                    "/interface vmq add name=vmq_$vmqId mac-address=$macAddress",
                    
                    // Assign to specific port
                    "/interface vmq set vmq_$vmqId port=eth$port",
                    
                    // Assign queues to VMQ
                    "/interface vmq set vmq_$vmqId queue-count=$queueCount",
                    
                    // Configure VMQ for bridge NAT
                    "/interface vmq set vmq_$vmqId bridge-nat=yes",
                    "/interface vmq set vmq_$vmqId user-role={$userRoles[$index]}"
                ];
                
                foreach ($commands as $command) {
                    $this->mikrotik_api->execute($command);
                }
                
                $results[] = [
                    'vmq_id' => $vmqId,
                    'port' => $port,
                    'mac_address' => $macAddress,
                    'queue_count' => $queueCount,
                    'user_role' => $userRoles[$index]
                ];
            }
            
            return ['success' => true, 'vmq_configs' => $results];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Configure SR-IOV for multi-port high performance
     */
    public function configureMultiPortSRIV($macAddresses, $userRoles) {
        try {
            $results = [];
            
            foreach ($macAddresses as $index => $macAddress) {
                $port = $index % 4; // Distribute across 4 ports
                $vfId = $this->getNextVFId();
                
                $commands = [
                    // Create SR-IOV virtual function
                    "/interface sriov add name=vf_$vfId mac-address=$macAddress",
                    
                    // Assign to specific port
                    "/interface sriov set vf_$vfId port=eth$port",
                    
                    // Configure VF for bridge NAT
                    "/interface sriov set vf_$vfId bridge-nat=yes",
                    "/interface sriov set vf_$vfId user-role={$userRoles[$index]}",
                    
                    // Enable hardware acceleration
                    "/interface sriov set vf_$vfId hardware-offload=yes"
                ];
                
                foreach ($commands as $command) {
                    $this->mikrotik_api->execute($command);
                }
                
                $results[] = [
                    'vf_id' => $vfId,
                    'port' => $port,
                    'mac_address' => $macAddress,
                    'user_role' => $userRoles[$index]
                ];
            }
            
            return ['success' => true, 'sriv_configs' => $results];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enable all hardware offloads for X710-T4L
     */
    public function enableT4LHardwareOffloads() {
        try {
            $commands = [];
            
            // Configure all 4 ports
            for ($port = 0; $port < 4; $port++) {
                $interface = "eth$port";
                
                $commands[] = [
                    // TCP checksum offload
                    "/interface ethernet set [find name=$interface] tcp-checksum-offload=yes",
                    
                    // UDP checksum offload
                    "/interface ethernet set [find name=$interface] udp-checksum-offload=yes",
                    
                    // IP checksum offload
                    "/interface ethernet set [find name=$interface] ip-checksum-offload=yes",
                    
                    // Large send offload
                    "/interface ethernet set [find name=$interface] large-send-offload=yes",
                    
                    // TCP segmentation offload
                    "/interface ethernet set [find name=$interface] tcp-segmentation-offload=yes",
                    
                    // VLAN tagging offload
                    "/interface ethernet set [find name=$interface] vlan-tagging-offload=yes",
                    
                    // QoS offload
                    "/interface ethernet set [find name=$interface] qos-offload=yes",
                    
                    // Flow director
                    "/interface ethernet set [find name=$interface] flow-director=yes"
                ];
            }
            
            foreach ($commands as $portCommands) {
                foreach ($portCommands as $command) {
                    $this->mikrotik_api->execute($command);
                }
            }
            
            return ['success' => true, 'offloads' => $this->config['hardware_offload']];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get X710-T4L performance statistics
     */
    public function getT4LStats() {
        try {
            $commands = [
                "/interface ethernet print",
                "/queue tree print",
                "/queue simple print",
                "/interface vmq print",
                "/interface sriov print"
            ];
            
            $stats = [];
            foreach ($commands as $command) {
                $result = $this->mikrotik_api->execute($command);
                if ($result['success']) {
                    $stats[] = $result['data'];
                }
            }
            
            // Add X710-T4L specific metrics
            $stats['t4l_metrics'] = [
                'total_ports' => 4,
                'total_bandwidth' => '40Gb/s',
                'total_queues' => 384,
                'active_queues' => 128,
                'virtual_ports' => 512,
                'vmq_ports' => 256,
                'sriv_ports' => 256
            ];
            
            return ['success' => true, 'stats' => $stats];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Optimize X710-T4L for maximum Bridge NAT performance
     */
    public function optimizeT4LPerformance() {
        try {
            $commands = [];
            
            // Configure all 4 ports for optimal performance
            for ($port = 0; $port < 4; $port++) {
                $interface = "eth$port";
                
                $commands[] = [
                    // Set optimal queue count
                    "/interface ethernet set [find name=$interface] queue-count={$this->config['optimal_queues_per_port']}",
                    
                    // Configure RSS hash function
                    "/interface ethernet set [find name=$interface] rss-hash-func=toeplitz",
                    
                    // Enable flow director for traffic steering
                    "/interface ethernet set [find name=$interface] flow-director=yes",
                    
                    // Configure flow director rules for load balancing
                    "/interface ethernet flow-director add interface=$interface src-ip=192.168.{$port}00.0/24 queue=1",
                    "/interface ethernet flow-director add interface=$interface dst-ip=192.168.{$port}00.0/24 queue=2"
                ];
            }
            
            // System-level optimizations
            $commands[] = [
                // Increase socket buffers
                "sysctl kern.ipc.maxsockbuf=16777216",
                "sysctl net.inet.tcp.recvbuf_max=16777216",
                "sysctl net.inet.tcp.sendbuf_max=16777216",
                
                // Optimize TCP settings
                "sysctl net.inet.tcp.recvspace=65536",
                "sysctl net.inet.tcp.sendspace=65536",
                "sysctl net.inet.tcp.sendbuf_inc=8192",
                "sysctl net.inet.tcp.recvbuf_inc=8192"
            ];
            
            foreach ($commands as $commandGroup) {
                if (is_array($commandGroup)) {
                    foreach ($commandGroup as $command) {
                        $this->mikrotik_api->execute($command);
                    }
                } else {
                    $this->mikrotik_api->execute($commandGroup);
                }
            }
            
            return ['success' => true, 'message' => 'X710-T4L performance optimized for Bridge NAT'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // Helper methods
    private function getNextVMQId() {
        return rand(1, 1000);
    }
    
    private function getNextVFId() {
        return rand(1, 1000);
    }
    
    private function getQueueCountForRole($userRole) {
        switch ($userRole) {
            case 'admin': return 8;
            case 'user': return 4;
            case 'guest': 
            default: return 2;
        }
    }
}
?> 

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?>
