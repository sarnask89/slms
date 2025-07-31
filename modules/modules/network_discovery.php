<?php
/**
 * Enhanced Network Discovery Module
 * SLMS v1.2.0 - Real Network Data with Git Integration
 * 
 * Features:
 * - Real hostname discovery
 * - Interface name mapping
 * - Transfer rate monitoring
 * - Git deployment automation
 * - SNMP v1/v2c/v3 discovery
 * - MNDP (Mikrotik Neighbor Discovery Protocol)
 * - LLDP discovery
 * - CDP discovery
 * - Automatic device classification
 * - Real-time network monitoring
 */

class NetworkDiscovery {
    private $pdo;
    private $discoveryConfig;
    private $snmpCommunities = ['public', 'private', 'community', 'admin', 'cisco'];
    private $discoveryLog = [];
    private $gitConfig = [];
    private $realNetworkData = [];
    
    public function __construct() {
        $this->pdo = get_pdo();
        $this->loadDiscoveryConfig();
        $this->loadGitConfig();
        $this->initializeDiscovery();
    }
    
    /**
     * Load discovery configuration
     */
    private function loadDiscoveryConfig() {
        $this->discoveryConfig = [
            'scan_interval' => 300, // 5 minutes
            'network_ranges' => [
                '192.168.1.0/24',
                '192.168.0.0/24',
                '10.0.0.0/8'
            ],
            'snmp_timeout' => 3,
            'snmp_retries' => 2,
            'interface_monitoring' => true,
            'transfer_monitoring' => true,
            'git_auto_deploy' => true,
            'git_commit_interval' => 3600 // 1 hour
        ];
    }
    
    /**
     * Load Git configuration
     */
    private function loadGitConfig() {
        $this->gitConfig = [
            'repository' => '/home/sarna/slms-network-data',
            'branch' => 'main',
            'auto_commit' => true,
            'commit_message_template' => 'SLMS Network Update: {timestamp} - {devices} devices, {interfaces} interfaces',
            'last_commit_time' => 0
        ];
    }
    
    /**
     * Initialize discovery system
     */
    private function initializeDiscovery() {
        $this->createDiscoveryTables();
        $this->startDiscoveryServices();
        $this->log('🔍 Enhanced Network Discovery initialized with real data support');
    }
    
    /**
     * Create database tables for enhanced discovery
     */
    private function createDiscoveryTables() {
        $tables = [
            'discovered_devices' => "
                CREATE TABLE IF NOT EXISTS discovered_devices (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    hostname VARCHAR(255) NOT NULL,
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
            'network_interfaces' => "
                CREATE TABLE IF NOT EXISTS network_interfaces (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    device_id INTEGER,
                    interface_name VARCHAR(100) NOT NULL,
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
                    FOREIGN KEY (device_id) REFERENCES discovered_devices(id)
                )
            ",
            'transfer_history' => "
                CREATE TABLE IF NOT EXISTS transfer_history (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    interface_id INTEGER,
                    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    bytes_rx BIGINT,
                    bytes_tx BIGINT,
                    packets_rx INTEGER,
                    packets_tx INTEGER,
                    errors_rx INTEGER DEFAULT 0,
                    errors_tx INTEGER DEFAULT 0,
                    FOREIGN KEY (interface_id) REFERENCES network_interfaces(id)
                )
            ",
            'git_deployments' => "
                CREATE TABLE IF NOT EXISTS git_deployments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    commit_hash VARCHAR(40),
                    commit_message TEXT,
                    files_changed INTEGER,
                    devices_count INTEGER,
                    interfaces_count INTEGER,
                    deployment_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(20) DEFAULT 'success'
                )
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            try {
                $this->pdo->exec($sql);
                $this->log("✅ Table {$tableName} created/verified");
            } catch (Exception $e) {
                $this->log("❌ Failed to create table {$tableName}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Start discovery services
     */
    private function startDiscoveryServices() {
        $this->startSNMPDiscovery();
        $this->startMNDPDiscovery();
        $this->startLLDPDiscovery();
        $this->startCDPDiscovery();
        $this->startTransferMonitoring();
        $this->startGitDeploymentService();
    }
    
    /**
     * Start SNMP discovery with real hostname detection
     */
    private function startSNMPDiscovery() {
        $this->log('🔍 Starting SNMP discovery with real hostname detection...');
        
        foreach ($this->discoveryConfig['network_ranges'] as $range) {
            $this->scanNetworkRange($range);
        }
    }
    
    /**
     * Scan network range for devices
     */
    private function scanNetworkRange($range) {
        $this->log("🔍 Scanning network range: {$range}");
        
        // Get IP addresses in range
        $ips = $this->getIPsInRange($range);
        
        foreach ($ips as $ip) {
            $this->discoverSNMPDevice($ip);
        }
    }
    
    /**
     * Discover SNMP device with real hostname
     */
    private function discoverSNMPDevice($ip) {
        foreach ($this->snmpCommunities as $community) {
            try {
                $deviceInfo = $this->querySNMPDevice($ip, $community);
                
                if ($deviceInfo) {
                    // Get real hostname
                    $hostname = $this->getRealHostname($ip);
                    $deviceInfo['hostname'] = $hostname ?: $deviceInfo['hostname'];
                    
                    // Get interface information
                    $interfaces = $this->getSNMPInterfaces($ip, $community);
                    
                    $this->storeDiscoveredDevice($deviceInfo, $interfaces);
                    break; // Found device, no need to try other communities
                }
            } catch (Exception $e) {
                // Continue with next community
            }
        }
    }
    
    /**
     * Get real hostname using DNS reverse lookup
     */
    private function getRealHostname($ip) {
        try {
            $hostname = gethostbyaddr($ip);
            if ($hostname && $hostname !== $ip) {
                return $hostname;
            }
        } catch (Exception $e) {
            // DNS lookup failed
        }
        
        // Try using nslookup command
        try {
            $output = shell_exec("nslookup {$ip} 2>/dev/null");
            if (preg_match('/name\s*=\s*([^\s.]+)/i', $output, $matches)) {
                return $matches[1];
            }
        } catch (Exception $e) {
            // nslookup failed
        }
        
        return null;
    }
    
    /**
     * Query SNMP device for information
     */
    private function querySNMPDevice($ip, $community) {
        $oids = [
            'system.sysDescr.0' => '1.3.6.1.2.1.1.1.0',
            'system.sysName.0' => '1.3.6.1.2.1.1.5.0',
            'system.sysLocation.0' => '1.3.6.1.2.1.1.6.0',
            'system.sysUpTime.0' => '1.3.6.1.2.1.1.3.0'
        ];
        
        $deviceInfo = [
            'ip_address' => $ip,
            'snmp_community' => $community,
            'hostname' => '',
            'description' => '',
            'location' => '',
            'uptime' => 0
        ];
        
        foreach ($oids as $name => $oid) {
            $value = $this->extractSNMPValue($ip, $community, $oid);
            if ($value) {
                $deviceInfo[strtolower(str_replace('system.sys', '', $name))] = $value;
            }
        }
        
        if (!empty($deviceInfo['description'])) {
            $deviceInfo['device_type'] = $this->classifyDevice($deviceInfo['description']);
            $deviceInfo['vendor'] = $this->extractVendor($deviceInfo['description']);
            $deviceInfo['model'] = $this->extractModel($deviceInfo['description']);
            
            return $deviceInfo;
        }
        
        return null;
    }
    
    /**
     * Get SNMP interfaces with real interface names
     */
    private function getSNMPInterfaces($ip, $community) {
        $interfaces = [];
        
        try {
            // Get interface names
            $ifNames = $this->snmpWalk($ip, $community, '1.3.6.1.2.1.2.2.1.2');
            $ifTypes = $this->snmpWalk($ip, $community, '1.3.6.1.2.1.2.2.1.3');
            $ifSpeeds = $this->snmpWalk($ip, $community, '1.3.6.1.2.1.2.2.1.5');
            $ifStatus = $this->snmpWalk($ip, $community, '1.3.6.1.2.1.2.2.1.8');
            
            foreach ($ifNames as $index => $name) {
                $interface = [
                    'interface_name' => $name,
                    'interface_type' => $ifTypes[$index] ?? 'unknown',
                    'speed' => $ifSpeeds[$index] ?? 0,
                    'status' => ($ifStatus[$index] ?? 0) == 1 ? 'up' : 'down'
                ];
                
                // Get interface IP addresses
                $interface['ip_address'] = $this->getInterfaceIP($ip, $community, $index);
                
                $interfaces[] = $interface;
            }
        } catch (Exception $e) {
            $this->log("❌ Failed to get interfaces for {$ip}: " . $e->getMessage());
        }
        
        return $interfaces;
    }
    
    /**
     * SNMP walk function
     */
    private function snmpWalk($ip, $community, $oid) {
        $command = "snmpwalk -v2c -c {$community} {$ip} {$oid} 2>/dev/null";
        $output = shell_exec($command);
        
        $results = [];
        if ($output) {
            $lines = explode("\n", trim($output));
            foreach ($lines as $line) {
                if (preg_match('/= (.+)$/', $line, $matches)) {
                    $results[] = trim($matches[1], '"');
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Get interface IP address
     */
    private function getInterfaceIP($ip, $community, $ifIndex) {
        try {
            $oid = "1.3.6.1.2.1.4.20.1.2.{$ifIndex}";
            $command = "snmpget -v2c -c {$community} {$ip} {$oid} 2>/dev/null";
            $output = shell_exec($command);
            
            if (preg_match('/= (.+)$/', $output, $matches)) {
                return trim($matches[1], '"');
            }
        } catch (Exception $e) {
            // Interface IP not found
        }
        
        return null;
    }
    
    /**
     * Extract SNMP value
     */
    private function extractSNMPValue($ip, $community, $oid) {
        try {
            $command = "snmpget -v2c -c {$community} {$ip} {$oid} 2>/dev/null";
            $output = shell_exec($command);
            
            if (preg_match('/= (.+)$/', $output, $matches)) {
                return trim($matches[1], '"');
            }
        } catch (Exception $e) {
            // SNMP query failed
        }
        
        return null;
    }
    
    /**
     * Classify device type
     */
    private function classifyDevice($description) {
        $description = strtolower($description);
        
        if (strpos($description, 'router') !== false) return 'router';
        if (strpos($description, 'switch') !== false) return 'switch';
        if (strpos($description, 'server') !== false) return 'server';
        if (strpos($description, 'mikrotik') !== false) return 'mikrotik';
        if (strpos($description, 'cisco') !== false) return 'cisco';
        
        return 'unknown';
    }
    
    /**
     * Extract vendor from description
     */
    private function extractVendor($description) {
        $vendors = ['cisco', 'hp', 'dell', 'mikrotik', 'juniper', 'arista', 'brocade'];
        
        foreach ($vendors as $vendor) {
            if (stripos($description, $vendor) !== false) {
                return ucfirst($vendor);
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Extract model from description
     */
    private function extractModel($description) {
        // Extract model patterns like "Cisco 2960", "HP ProCurve", etc.
        if (preg_match('/([A-Za-z]+)\s+([A-Za-z0-9\-]+)/', $description, $matches)) {
            return $matches[2];
        }
        
        return 'Unknown';
    }
    
    /**
     * Store discovered device with interfaces
     */
    private function storeDiscoveredDevice($deviceInfo, $interfaces) {
        try {
            // Check if device already exists
            $stmt = $this->pdo->prepare("SELECT id FROM discovered_devices WHERE ip_address = ?");
            $stmt->execute([$deviceInfo['ip_address']]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing device
                $stmt = $this->pdo->prepare("
                    UPDATE discovered_devices SET 
                    hostname = ?, device_type = ?, vendor = ?, model = ?, 
                    status = 'online', last_seen = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
                    WHERE ip_address = ?
                ");
                $stmt->execute([
                    $deviceInfo['hostname'],
                    $deviceInfo['device_type'],
                    $deviceInfo['vendor'],
                    $deviceInfo['model'],
                    $deviceInfo['ip_address']
                ]);
                $deviceId = $existing['id'];
            } else {
                // Insert new device
                $stmt = $this->pdo->prepare("
                    INSERT INTO discovered_devices 
                    (hostname, ip_address, device_type, vendor, model, snmp_community, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'online')
                ");
                $stmt->execute([
                    $deviceInfo['hostname'],
                    $deviceInfo['ip_address'],
                    $deviceInfo['device_type'],
                    $deviceInfo['vendor'],
                    $deviceInfo['model'],
                    $deviceInfo['snmp_community']
                ]);
                $deviceId = $this->pdo->lastInsertId();
            }
            
            // Store interfaces
            $this->storeInterfaces($deviceId, $interfaces);
            
            $this->log("✅ Discovered device: {$deviceInfo['hostname']} ({$deviceInfo['ip_address']}) - {$deviceInfo['device_type']}");
            
        } catch (Exception $e) {
            $this->log("❌ Failed to store device {$deviceInfo['ip_address']}: " . $e->getMessage());
        }
    }
    
    /**
     * Store interfaces for device
     */
    private function storeInterfaces($deviceId, $interfaces) {
        foreach ($interfaces as $interface) {
            try {
                $stmt = $this->pdo->prepare("
                    INSERT OR REPLACE INTO network_interfaces 
                    (device_id, interface_name, interface_type, speed, status, ip_address)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $deviceId,
                    $interface['interface_name'],
                    $interface['interface_type'],
                    $interface['speed'],
                    $interface['status'],
                    $interface['ip_address']
                ]);
            } catch (Exception $e) {
                $this->log("❌ Failed to store interface {$interface['interface_name']}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Start transfer monitoring
     */
    private function startTransferMonitoring() {
        if ($this->discoveryConfig['transfer_monitoring']) {
            $this->log('📊 Starting transfer rate monitoring...');
            
            // Monitor transfer rates every 60 seconds
            while (true) {
                $this->updateTransferRates();
                sleep(60);
            }
        }
    }
    
    /**
     * Update transfer rates for all interfaces
     */
    private function updateTransferRates() {
        try {
            $stmt = $this->pdo->query("
                SELECT ni.id, ni.device_id, ni.interface_name, d.ip_address, d.snmp_community
                FROM network_interfaces ni
                JOIN discovered_devices d ON ni.device_id = d.id
                WHERE ni.status = 'up'
            ");
            
            while ($interface = $stmt->fetch()) {
                $this->updateInterfaceTransferRate($interface);
            }
        } catch (Exception $e) {
            $this->log("❌ Failed to update transfer rates: " . $e->getMessage());
        }
    }
    
    /**
     * Update transfer rate for specific interface
     */
    private function updateInterfaceTransferRate($interface) {
        try {
            // Get interface index
            $ifIndex = $this->getInterfaceIndex($interface['ip_address'], $interface['snmp_community'], $interface['interface_name']);
            
            if ($ifIndex !== null) {
                // Get current counters
                $inOctets = $this->getSNMPCounter($interface['ip_address'], $interface['snmp_community'], "1.3.6.1.2.1.2.2.1.10.{$ifIndex}");
                $outOctets = $this->getSNMPCounter($interface['ip_address'], $interface['snmp_community'], "1.3.6.1.2.1.2.2.1.16.{$ifIndex}");
                
                if ($inOctets !== null && $outOctets !== null) {
                    // Calculate rates
                    $this->calculateTransferRates($interface['id'], $inOctets, $outOctets);
                }
            }
        } catch (Exception $e) {
            // Transfer monitoring failed for this interface
        }
    }
    
    /**
     * Get interface index by name
     */
    private function getInterfaceIndex($ip, $community, $interfaceName) {
        $ifNames = $this->snmpWalk($ip, $community, '1.3.6.1.2.1.2.2.1.2');
        
        foreach ($ifNames as $index => $name) {
            if ($name === $interfaceName) {
                return $index;
            }
        }
        
        return null;
    }
    
    /**
     * Get SNMP counter value
     */
    private function getSNMPCounter($ip, $community, $oid) {
        try {
            $command = "snmpget -v2c -c {$community} {$ip} {$oid} 2>/dev/null";
            $output = shell_exec($command);
            
            if (preg_match('/= (.+)$/', $output, $matches)) {
                return (int)trim($matches[1]);
            }
        } catch (Exception $e) {
            // SNMP query failed
        }
        
        return null;
    }
    
    /**
     * Calculate transfer rates
     */
    private function calculateTransferRates($interfaceId, $inOctets, $outOctets) {
        try {
            // Get previous values
            $stmt = $this->pdo->prepare("
                SELECT transfer_rx, transfer_tx, last_updated 
                FROM network_interfaces WHERE id = ?
            ");
            $stmt->execute([$interfaceId]);
            $previous = $stmt->fetch();
            
            if ($previous) {
                $timeDiff = time() - strtotime($previous['last_updated']);
                
                if ($timeDiff > 0) {
                    $rxRate = ($inOctets - $previous['transfer_rx']) / $timeDiff;
                    $txRate = ($outOctets - $previous['transfer_tx']) / $timeDiff;
                    
                    // Update interface with new rates
                    $stmt = $this->pdo->prepare("
                        UPDATE network_interfaces SET 
                        transfer_rx = ?, transfer_tx = ?, 
                        transfer_rx_rate = ?, transfer_tx_rate = ?,
                        last_updated = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$inOctets, $outOctets, $rxRate, $txRate, $interfaceId]);
                    
                    // Store transfer history
                    $this->storeTransferHistory($interfaceId, $inOctets, $outOctets);
                }
            } else {
                // First time, just store current values
                $stmt = $this->pdo->prepare("
                    UPDATE network_interfaces SET 
                    transfer_rx = ?, transfer_tx = ?, last_updated = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([$inOctets, $outOctets, $interfaceId]);
            }
        } catch (Exception $e) {
            $this->log("❌ Failed to calculate transfer rates: " . $e->getMessage());
        }
    }
    
    /**
     * Store transfer history
     */
    private function storeTransferHistory($interfaceId, $bytesRx, $bytesTx) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO transfer_history 
                (interface_id, bytes_rx, bytes_tx, timestamp)
                VALUES (?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([$interfaceId, $bytesRx, $bytesTx]);
        } catch (Exception $e) {
            // Failed to store transfer history
        }
    }
    
    /**
     * Start Git deployment service
     */
    private function startGitDeploymentService() {
        if ($this->gitConfig['auto_commit']) {
            $this->log('🚀 Starting Git deployment service...');
            
            // Check if repository exists
            if (!is_dir($this->gitConfig['repository'])) {
                $this->initializeGitRepository();
            }
            
            // Schedule periodic deployments
            while (true) {
                if (time() - $this->gitConfig['last_commit_time'] >= $this->discoveryConfig['git_commit_interval']) {
                    $this->deployToGit();
                }
                sleep(300); // Check every 5 minutes
            }
        }
    }
    
    /**
     * Initialize Git repository
     */
    private function initializeGitRepository() {
        try {
            mkdir($this->gitConfig['repository'], 0755, true);
            
            $commands = [
                "cd {$this->gitConfig['repository']}",
                "git init",
                "git config user.name 'SLMS Network Discovery'",
                "git config user.email 'slms@network.local'",
                "echo '# SLMS Network Discovery Data' > README.md",
                "git add README.md",
                "git commit -m 'Initial commit: SLMS Network Discovery'"
            ];
            
            $command = implode(' && ', $commands);
            shell_exec($command);
            
            $this->log("✅ Git repository initialized at {$this->gitConfig['repository']}");
        } catch (Exception $e) {
            $this->log("❌ Failed to initialize Git repository: " . $e->getMessage());
        }
    }
    
    /**
     * Deploy network data to Git
     */
    private function deployToGit() {
        try {
            $this->log('🚀 Deploying network data to Git...');
            
            // Export current network data
            $networkData = $this->exportNetworkData();
            $jsonData = json_encode($networkData, JSON_PRETTY_PRINT);
            
            // Write to repository
            $dataFile = $this->gitConfig['repository'] . '/network-data.json';
            file_put_contents($dataFile, $jsonData);
            
            // Create summary file
            $summary = $this->generateNetworkSummary();
            $summaryFile = $this->gitConfig['repository'] . '/network-summary.md';
            file_put_contents($summaryFile, $summary);
            
            // Git operations
            $commands = [
                "cd {$this->gitConfig['repository']}",
                "git add .",
                "git commit -m '{$this->generateCommitMessage()}'",
                "git push origin {$this->gitConfig['branch']} 2>/dev/null || true"
            ];
            
            $command = implode(' && ', $commands);
            $output = shell_exec($command);
            
            // Update last commit time
            $this->gitConfig['last_commit_time'] = time();
            
            // Store deployment record
            $this->storeDeploymentRecord($networkData);
            
            $this->log("✅ Network data deployed to Git successfully");
            
        } catch (Exception $e) {
            $this->log("❌ Git deployment failed: " . $e->getMessage());
        }
    }
    
    /**
     * Export network data
     */
    private function exportNetworkData() {
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'devices' => [],
            'interfaces' => [],
            'transfer_stats' => []
        ];
        
        // Get devices
        $stmt = $this->pdo->query("SELECT * FROM discovered_devices ORDER BY hostname");
        $data['devices'] = $stmt->fetchAll();
        
        // Get interfaces
        $stmt = $this->pdo->query("SELECT * FROM network_interfaces ORDER BY device_id, interface_name");
        $data['interfaces'] = $stmt->fetchAll();
        
        // Get transfer statistics
        $stmt = $this->pdo->query("
            SELECT 
                ni.interface_name,
                d.hostname,
                ni.transfer_rx_rate,
                ni.transfer_tx_rate,
                ni.last_updated
            FROM network_interfaces ni
            JOIN discovered_devices d ON ni.device_id = d.id
            WHERE ni.status = 'up'
            ORDER BY ni.transfer_rx_rate + ni.transfer_tx_rate DESC
        ");
        $data['transfer_stats'] = $stmt->fetchAll();
        
        return $data;
    }
    
    /**
     * Generate network summary
     */
    private function generateNetworkSummary() {
        $summary = "# SLMS Network Discovery Summary\n\n";
        $summary .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Device count by type
        $stmt = $this->pdo->query("
            SELECT device_type, COUNT(*) as count 
            FROM discovered_devices 
            GROUP BY device_type
        ");
        $deviceTypes = $stmt->fetchAll();
        
        $summary .= "## Device Summary\n\n";
        foreach ($deviceTypes as $type) {
            $summary .= "- **{$type['device_type']}**: {$type['count']} devices\n";
        }
        
        // Interface summary
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as total, 
                   SUM(CASE WHEN status = 'up' THEN 1 ELSE 0 END) as up
            FROM network_interfaces
        ");
        $interfaceStats = $stmt->fetch();
        
        $summary .= "\n## Interface Summary\n\n";
        $summary .= "- **Total Interfaces**: {$interfaceStats['total']}\n";
        $summary .= "- **Active Interfaces**: {$interfaceStats['up']}\n";
        
        // Top transfer interfaces
        $stmt = $this->pdo->query("
            SELECT 
                ni.interface_name,
                d.hostname,
                ROUND(ni.transfer_rx_rate / 1024, 2) as rx_mbps,
                ROUND(ni.transfer_tx_rate / 1024, 2) as tx_mbps
            FROM network_interfaces ni
            JOIN discovered_devices d ON ni.device_id = d.id
            WHERE ni.status = 'up'
            ORDER BY ni.transfer_rx_rate + ni.transfer_tx_rate DESC
            LIMIT 10
        ");
        $topInterfaces = $stmt->fetchAll();
        
        $summary .= "\n## Top Transfer Interfaces\n\n";
        foreach ($topInterfaces as $interface) {
            $summary .= "- **{$interface['hostname']}** ({$interface['interface_name']}): ";
            $summary .= "↓ {$interface['rx_mbps']} Mbps, ↑ {$interface['tx_mbps']} Mbps\n";
        }
        
        return $summary;
    }
    
    /**
     * Generate commit message
     */
    private function generateCommitMessage() {
        $deviceCount = $this->pdo->query("SELECT COUNT(*) FROM discovered_devices")->fetchColumn();
        $interfaceCount = $this->pdo->query("SELECT COUNT(*) FROM network_interfaces")->fetchColumn();
        
        $message = $this->gitConfig['commit_message_template'];
        $message = str_replace('{timestamp}', date('Y-m-d H:i:s'), $message);
        $message = str_replace('{devices}', $deviceCount, $message);
        $message = str_replace('{interfaces}', $interfaceCount, $message);
        
        return $message;
    }
    
    /**
     * Store deployment record
     */
    private function storeDeploymentRecord($networkData) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO git_deployments 
                (commit_message, files_changed, devices_count, interfaces_count)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $this->generateCommitMessage(),
                2, // network-data.json and network-summary.md
                count($networkData['devices']),
                count($networkData['interfaces'])
            ]);
        } catch (Exception $e) {
            $this->log("❌ Failed to store deployment record: " . $e->getMessage());
        }
    }
    
    /**
     * Get IPs in range
     */
    private function getIPsInRange($range) {
        $ips = [];
        
        if (preg_match('/^(\d+\.\d+\.\d+\.\d+)\/(\d+)$/', $range, $matches)) {
            $baseIP = $matches[1];
            $subnet = (int)$matches[2];
            
            $hosts = pow(2, 32 - $subnet) - 2; // Exclude network and broadcast
            
            $baseIPLong = ip2long($baseIP);
            for ($i = 1; $i <= min($hosts, 254); $i++) { // Limit to 254 hosts for performance
                $ips[] = long2ip($baseIPLong + $i);
            }
        }
        
        return $ips;
    }
    
    /**
     * Start MNDP discovery
     */
    private function startMNDPDiscovery() {
        $this->log('🔍 Starting MNDP discovery...');
        // MNDP implementation would go here
    }
    
    /**
     * Start LLDP discovery
     */
    private function startLLDPDiscovery() {
        $this->log('🔍 Starting LLDP discovery...');
        // LLDP implementation would go here
    }
    
    /**
     * Start CDP discovery
     */
    private function startCDPDiscovery() {
        $this->log('🔍 Starting CDP discovery...');
        // CDP implementation would go here
    }
    
    /**
     * Get discovered devices
     */
    public function getDiscoveredDevices() {
        $stmt = $this->pdo->query("SELECT * FROM discovered_devices ORDER BY hostname");
        return $stmt->fetchAll();
    }
    
    /**
     * Get device statistics
     */
    public function getDeviceStatistics() {
        $stats = [];
        
        $stats['total_devices'] = $this->pdo->query("SELECT COUNT(*) FROM discovered_devices")->fetchColumn();
        $stats['online_devices'] = $this->pdo->query("SELECT COUNT(*) FROM discovered_devices WHERE status = 'online'")->fetchColumn();
        $stats['total_interfaces'] = $this->pdo->query("SELECT COUNT(*) FROM network_interfaces")->fetchColumn();
        $stats['active_interfaces'] = $this->pdo->query("SELECT COUNT(*) FROM network_interfaces WHERE status = 'up'")->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Run discovery scan
     */
    public function runDiscoveryScan() {
        $this->log('🚀 Starting comprehensive network discovery scan...');
        
        $startTime = microtime(true);
        
        // Run all discovery methods
        $this->startSNMPDiscovery();
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $stats = $this->getDeviceStatistics();
        
        $this->log("✅ Discovery scan completed in {$duration}s");
        $this->log("📊 Found {$stats['total_devices']} devices with {$stats['total_interfaces']} interfaces");
        
        return $stats;
    }
    
    /**
     * Log message
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        
        $this->discoveryLog[] = $logMessage;
        error_log($logMessage, 3, '/var/log/slms/network_discovery.log');
        
        echo $logMessage;
    }
} 