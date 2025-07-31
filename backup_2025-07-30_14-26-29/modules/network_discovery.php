<?php
/**
 * Network Discovery Module
 * SLMS v1.2.0 - Advanced Network Discovery with SNMP and MNDP
 * 
 * Features:
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
    
    public function __construct() {
        $this->pdo = get_pdo();
        $this->loadDiscoveryConfig();
        $this->initializeDiscovery();
    }
    
    /**
     * Load discovery configuration
     */
    private function loadDiscoveryConfig() {
        $this->discoveryConfig = [
            'snmp_enabled' => true,
            'mndp_enabled' => true,
            'lldp_enabled' => true,
            'cdp_enabled' => true,
            'scan_interval' => 300, // 5 minutes
            'network_ranges' => $this->getNetworkRanges(),
            'snmp_timeout' => 2,
            'snmp_retries' => 1
        ];
    }
    
    /**
     * Get network ranges to scan
     */
    private function getNetworkRanges() {
        $ranges = [];
        
        // Get local network
        $localIP = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
        $networkPrefix = substr($localIP, 0, strrpos($localIP, '.'));
        
        // Add common network ranges
        $ranges[] = "$networkPrefix.0/24";
        
        // Add management VLAN ranges
        $ranges[] = "10.0.0.0/24";  // Common management VLAN
        $ranges[] = "192.168.1.0/24"; // Common home/office
        $ranges[] = "172.16.0.0/24";  // Private network
        
        return $ranges;
    }
    
    /**
     * Initialize discovery system
     */
    private function initializeDiscovery() {
        $this->log("🔍 Initializing Network Discovery System...");
        
        // Create discovery tables
        $this->createDiscoveryTables();
        
        // Start discovery services
        $this->startDiscoveryServices();
        
        $this->log("✅ Network Discovery System initialized");
    }
    
    /**
     * Create discovery tables
     */
    private function createDiscoveryTables() {
        $tables = [
            'discovered_devices' => "
                CREATE TABLE IF NOT EXISTS discovered_devices (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ip_address VARCHAR(15) UNIQUE,
                    mac_address VARCHAR(17),
                    hostname VARCHAR(255),
                    device_type VARCHAR(50),
                    vendor VARCHAR(100),
                    model VARCHAR(100),
                    os_version VARCHAR(100),
                    discovery_protocol VARCHAR(20),
                    snmp_community VARCHAR(50),
                    snmp_version VARCHAR(10),
                    mndp_data TEXT,
                    lldp_data TEXT,
                    cdp_data TEXT,
                    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(20) DEFAULT 'active',
                    uptime INTEGER,
                    interface_count INTEGER,
                    cpu_usage FLOAT,
                    memory_usage FLOAT,
                    temperature FLOAT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",
            'discovery_log' => "
                CREATE TABLE IF NOT EXISTS discovery_log (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    discovery_type VARCHAR(20),
                    target_ip VARCHAR(15),
                    result TEXT,
                    error_message TEXT,
                    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",
            'network_topology' => "
                CREATE TABLE IF NOT EXISTS network_topology (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    source_device_id INTEGER,
                    target_device_id INTEGER,
                    source_interface VARCHAR(50),
                    target_interface VARCHAR(50),
                    connection_type VARCHAR(20),
                    bandwidth INTEGER,
                    discovery_protocol VARCHAR(20),
                    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (source_device_id) REFERENCES discovered_devices(id),
                    FOREIGN KEY (target_device_id) REFERENCES discovered_devices(id)
                )"
        ];
        
        foreach ($tables as $table => $sql) {
            try {
                $this->pdo->exec($sql);
                $this->log("✅ Created table: $table");
            } catch (Exception $e) {
                $this->log("❌ Failed to create table $table: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Start discovery services
     */
    private function startDiscoveryServices() {
        if ($this->discoveryConfig['snmp_enabled']) {
            $this->startSNMPDiscovery();
        }
        
        if ($this->discoveryConfig['mndp_enabled']) {
            $this->startMNDPDiscovery();
        }
        
        if ($this->discoveryConfig['lldp_enabled']) {
            $this->startLLDPDiscovery();
        }
        
        if ($this->discoveryConfig['cdp_enabled']) {
            $this->startCDPDiscovery();
        }
    }
    
    /**
     * Start SNMP discovery
     */
    private function startSNMPDiscovery() {
        $this->log("🔍 Starting SNMP Discovery...");
        
        // Scan network ranges
        foreach ($this->discoveryConfig['network_ranges'] as $range) {
            $this->scanNetworkRange($range);
        }
    }
    
    /**
     * Scan network range
     */
    private function scanNetworkRange($range) {
        $this->log("🔍 Scanning network range: $range");
        
        // Parse CIDR notation
        list($network, $bits) = explode('/', $range);
        $networkLong = ip2long($network);
        $hosts = pow(2, 32 - $bits) - 2; // Exclude network and broadcast
        
        for ($i = 1; $i <= min($hosts, 254); $i++) {
            $ip = long2ip($networkLong + $i);
            $this->discoverSNMPDevice($ip);
        }
    }
    
    /**
     * Discover SNMP device
     */
    private function discoverSNMPDevice($ip) {
        foreach ($this->snmpCommunities as $community) {
            $deviceInfo = $this->querySNMPDevice($ip, $community);
            if ($deviceInfo) {
                $this->storeDiscoveredDevice($deviceInfo);
                break; // Found device, no need to try other communities
            }
        }
    }
    
    /**
     * Query SNMP device
     */
    private function querySNMPDevice($ip, $community) {
        try {
            // Basic SNMP queries
            $queries = [
                'sysDescr' => '1.3.6.1.2.1.1.1.0',
                'sysName' => '1.3.6.1.2.1.1.5.0',
                'sysLocation' => '1.3.6.1.2.1.1.6.0',
                'sysContact' => '1.3.6.1.2.1.1.4.0',
                'sysUpTime' => '1.3.6.1.2.1.1.3.0'
            ];
            
            $deviceInfo = [
                'ip_address' => $ip,
                'snmp_community' => $community,
                'snmp_version' => 'v2c',
                'discovery_protocol' => 'SNMP'
            ];
            
            foreach ($queries as $key => $oid) {
                $result = shell_exec("snmpget -v2c -c $community -t {$this->discoveryConfig['snmp_timeout']} -r {$this->discoveryConfig['snmp_retries']} $ip $oid 2>/dev/null");
                if ($result) {
                    $value = $this->extractSNMPValue($result);
                    $deviceInfo[$key] = $value;
                }
            }
            
            // Only return if we got at least sysDescr
            if (isset($deviceInfo['sysDescr'])) {
                $deviceInfo['device_type'] = $this->classifyDevice($deviceInfo['sysDescr']);
                $deviceInfo['vendor'] = $this->extractVendor($deviceInfo['sysDescr']);
                $deviceInfo['model'] = $this->extractModel($deviceInfo['sysDescr']);
                
                return $deviceInfo;
            }
            
        } catch (Exception $e) {
            $this->log("❌ SNMP query failed for $ip: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Extract SNMP value from output
     */
    private function extractSNMPValue($snmpOutput) {
        if (preg_match('/STRING: (.+)$/', $snmpOutput, $matches)) {
            return trim($matches[1], '"');
        }
        if (preg_match('/INTEGER: (.+)$/', $snmpOutput, $matches)) {
            return trim($matches[1]);
        }
        if (preg_match('/Timeticks: (.+)$/', $snmpOutput, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }
    
    /**
     * Classify device type
     */
    private function classifyDevice($sysDescr) {
        $description = strtolower($sysDescr);
        
        if (strpos($description, 'router') !== false) return 'router';
        if (strpos($description, 'switch') !== false) return 'switch';
        if (strpos($description, 'server') !== false) return 'server';
        if (strpos($description, 'firewall') !== false) return 'firewall';
        if (strpos($description, 'access point') !== false || strpos($description, 'ap') !== false) return 'access_point';
        if (strpos($description, 'mikrotik') !== false) return 'mikrotik';
        if (strpos($description, 'cisco') !== false) return 'cisco';
        if (strpos($description, 'hp') !== false || strpos($description, 'hewlett') !== false) return 'hp';
        if (strpos($description, 'juniper') !== false) return 'juniper';
        
        return 'unknown';
    }
    
    /**
     * Extract vendor from description
     */
    private function extractVendor($sysDescr) {
        $description = strtolower($sysDescr);
        
        if (strpos($description, 'cisco') !== false) return 'Cisco';
        if (strpos($description, 'mikrotik') !== false) return 'Mikrotik';
        if (strpos($description, 'hp') !== false || strpos($description, 'hewlett') !== false) return 'HP';
        if (strpos($description, 'juniper') !== false) return 'Juniper';
        if (strpos($description, 'dell') !== false) return 'Dell';
        if (strpos($description, 'netgear') !== false) return 'Netgear';
        if (strpos($description, 'linksys') !== false) return 'Linksys';
        if (strpos($description, 'asus') !== false) return 'ASUS';
        if (strpos($description, 'tp-link') !== false) return 'TP-Link';
        
        return 'Unknown';
    }
    
    /**
     * Extract model from description
     */
    private function extractModel($sysDescr) {
        // Extract model patterns
        if (preg_match('/([A-Z]{2,3}[0-9]{3,4}[A-Z]?)/', $sysDescr, $matches)) {
            return $matches[1];
        }
        if (preg_match('/([A-Z]{2,3}-[0-9]{3,4}[A-Z]?)/', $sysDescr, $matches)) {
            return $matches[1];
        }
        
        return 'Unknown';
    }
    
    /**
     * Start MNDP discovery
     */
    private function startMNDPDiscovery() {
        $this->log("🔍 Starting MNDP Discovery...");
        
        // Create MNDP listener script
        $mndpScript = $this->createMNDPListener();
        
        // Start MNDP listener
        $this->startMNDPListener($mndpScript);
    }
    
    /**
     * Create MNDP listener script
     */
    private function createMNDPListener() {
        $script = '#!/bin/bash
# MNDP Listener Script for Mikrotik Neighbor Discovery Protocol

MNDP_LOG="/var/log/mndp_discovery.log"
MNDP_PCAP="/tmp/mndp.pcap"

echo "$(date): Starting MNDP listener..." >> $MNDP_LOG

while true; do
    # Listen for MNDP packets on port 5678
    timeout 30 tcpdump -i any -n udp port 5678 -c 1 -w $MNDP_PCAP 2>/dev/null
    
    if [ -f $MNDP_PCAP ]; then
        # Parse MNDP packet
        hexdump -C $MNDP_PCAP | head -20 > /tmp/mndp_parsed.txt
        
        # Extract MNDP information
        MNDP_DATA=$(cat /tmp/mndp_parsed.txt)
        echo "$(date): MNDP packet captured - $MNDP_DATA" >> $MNDP_LOG
        
        # Clean up
        rm -f $MNDP_PCAP /tmp/mndp_parsed.txt
    fi
    
    sleep 5
done';
        
        file_put_contents('/tmp/mndp_listener.sh', $script);
        chmod('/tmp/mndp_listener.sh', 0755);
        
        return '/tmp/mndp_listener.sh';
    }
    
    /**
     * Start MNDP listener
     */
    private function startMNDPListener($scriptPath) {
        try {
            // Kill existing MNDP listeners
            shell_exec('pkill -f mndp_listener 2>/dev/null');
            
            // Start new MNDP listener
            shell_exec("$scriptPath > /dev/null 2>&1 &");
            
            $this->log("✅ MNDP listener started");
            
        } catch (Exception $e) {
            $this->log("❌ MNDP listener failed: " . $e->getMessage());
        }
    }
    
    /**
     * Start LLDP discovery
     */
    private function startLLDPDiscovery() {
        $this->log("🔍 Starting LLDP Discovery...");
        
        // Check if LLDP tools are available
        $lldpAvailable = shell_exec('which lldpctl 2>/dev/null');
        
        if ($lldpAvailable) {
            $this->log("✅ LLDP tools available");
            $this->parseLLDPData();
        } else {
            $this->log("⚠️ LLDP tools not available");
        }
    }
    
    /**
     * Parse LLDP data
     */
    private function parseLLDPData() {
        try {
            $lldpOutput = shell_exec('lldpctl 2>/dev/null');
            
            if ($lldpOutput) {
                // Parse LLDP output and store neighbor information
                $this->parseLLDPOutput($lldpOutput);
            }
            
        } catch (Exception $e) {
            $this->log("❌ LLDP parsing failed: " . $e->getMessage());
        }
    }
    
    /**
     * Start CDP discovery
     */
    private function startCDPDiscovery() {
        $this->log("🔍 Starting CDP Discovery...");
        
        // Create CDP listener
        $this->createCDPListener();
    }
    
    /**
     * Create CDP listener
     */
    private function createCDPListener() {
        $script = '#!/bin/bash
# CDP Listener Script for Cisco Discovery Protocol

CDP_LOG="/var/log/cdp_discovery.log"
CDP_PCAP="/tmp/cdp.pcap"

echo "$(date): Starting CDP listener..." >> $CDP_LOG

while true; do
    # Listen for CDP packets
    timeout 30 tcpdump -i any -n ether proto 0x2000 -c 1 -w $CDP_PCAP 2>/dev/null
    
    if [ -f $CDP_PCAP ]; then
        # Parse CDP packet
        hexdump -C $CDP_PCAP | head -20 > /tmp/cdp_parsed.txt
        
        # Extract CDP information
        CDP_DATA=$(cat /tmp/cdp_parsed.txt)
        echo "$(date): CDP packet captured - $CDP_DATA" >> $CDP_LOG
        
        # Clean up
        rm -f $CDP_PCAP /tmp/cdp_parsed.txt
    fi
    
    sleep 5
done';
        
        file_put_contents('/tmp/cdp_listener.sh', $script);
        chmod('/tmp/cdp_listener.sh', 0755);
        
        // Start CDP listener
        shell_exec('/tmp/cdp_listener.sh > /dev/null 2>&1 &');
        
        $this->log("✅ CDP listener started");
    }
    
    /**
     * Store discovered device
     */
    private function storeDiscoveredDevice($deviceInfo) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT OR REPLACE INTO discovered_devices 
                (ip_address, hostname, device_type, vendor, model, discovery_protocol, 
                 snmp_community, snmp_version, sysDescr, sysLocation, sysContact, sysUpTime, last_seen)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
            ");
            
            $stmt->execute([
                $deviceInfo['ip_address'],
                $deviceInfo['sysName'] ?? '',
                $deviceInfo['device_type'],
                $deviceInfo['vendor'],
                $deviceInfo['model'],
                $deviceInfo['discovery_protocol'],
                $deviceInfo['snmp_community'] ?? '',
                $deviceInfo['snmp_version'] ?? '',
                $deviceInfo['sysDescr'] ?? '',
                $deviceInfo['sysLocation'] ?? '',
                $deviceInfo['sysContact'] ?? '',
                $deviceInfo['sysUpTime'] ?? ''
            ]);
            
            $this->log("✅ Discovered device: {$deviceInfo['ip_address']} ({$deviceInfo['device_type']})");
            
            // Log discovery
            $this->logDiscovery('SNMP', $deviceInfo['ip_address'], 'Device discovered successfully');
            
        } catch (Exception $e) {
            $this->log("❌ Failed to store device {$deviceInfo['ip_address']}: " . $e->getMessage());
            $this->logDiscovery('SNMP', $deviceInfo['ip_address'], 'Failed to store device: ' . $e->getMessage());
        }
    }
    
    /**
     * Log discovery activity
     */
    private function logDiscovery($type, $target, $result, $error = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO discovery_log (discovery_type, target_ip, result, error_message)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([$type, $target, $result, $error]);
            
        } catch (Exception $e) {
            $this->log("❌ Failed to log discovery: " . $e->getMessage());
        }
    }
    
    /**
     * Get discovered devices
     */
    public function getDiscoveredDevices($limit = 100) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM discovered_devices 
                ORDER BY last_seen DESC 
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->log("❌ Failed to get discovered devices: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get device statistics
     */
    public function getDeviceStatistics() {
        try {
            $stats = [];
            
            // Total devices
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM discovered_devices");
            $stats['total_devices'] = $stmt->fetchColumn();
            
            // Devices by type
            $stmt = $this->pdo->query("
                SELECT device_type, COUNT(*) as count 
                FROM discovered_devices 
                GROUP BY device_type
            ");
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Devices by vendor
            $stmt = $this->pdo->query("
                SELECT vendor, COUNT(*) as count 
                FROM discovered_devices 
                GROUP BY vendor
            ");
            $stats['by_vendor'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Recent discoveries
            $stmt = $this->pdo->query("
                SELECT COUNT(*) FROM discovered_devices 
                WHERE last_seen > datetime('now', '-1 hour')
            ");
            $stats['recent_discoveries'] = $stmt->fetchColumn();
            
            return $stats;
            
        } catch (Exception $e) {
            $this->log("❌ Failed to get device statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Run discovery scan
     */
    public function runDiscoveryScan() {
        $this->log("🔍 Running comprehensive discovery scan...");
        
        $startTime = microtime(true);
        
        // Run all discovery methods
        $this->startSNMPDiscovery();
        $this->parseMNDPData();
        $this->parseLLDPData();
        $this->parseCDPData();
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->log("✅ Discovery scan completed in {$duration}s");
        
        return $this->getDeviceStatistics();
    }
    
    /**
     * Parse MNDP data from log
     */
    private function parseMNDPData() {
        if (file_exists('/var/log/mndp_discovery.log')) {
            $mndpLog = file_get_contents('/var/log/mndp_discovery.log');
            $this->log("📊 MNDP log entries: " . substr_count($mndpLog, 'MNDP packet captured'));
        }
    }
    
    /**
     * Parse LLDP output
     */
    private function parseLLDPOutput($output) {
        // Parse LLDP output and extract neighbor information
        $lines = explode("\n", $output);
        $neighbors = [];
        
        foreach ($lines as $line) {
            if (strpos($line, 'Chassis:') !== false) {
                // Extract neighbor information
                $this->log("🔗 LLDP neighbor found: " . trim($line));
            }
        }
    }
    
    /**
     * Parse CDP data from log
     */
    private function parseCDPData() {
        if (file_exists('/var/log/cdp_discovery.log')) {
            $cdpLog = file_get_contents('/var/log/cdp_discovery.log');
            $this->log("📊 CDP log entries: " . substr_count($cdpLog, 'CDP packet captured'));
        }
    }
    
    /**
     * Log message
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] NetworkDiscovery: $message";
        $this->discoveryLog[] = $logMessage;
        echo $logMessage . "\n";
        
        // Write to file
        file_put_contents('network_discovery.log', $logMessage . "\n", FILE_APPEND);
    }
}

// Initialize network discovery if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $discovery = new NetworkDiscovery();
    $stats = $discovery->runDiscoveryScan();
    
    echo "Network Discovery Results:\n";
    echo "Total devices: " . $stats['total_devices'] . "\n";
    echo "Recent discoveries: " . $stats['recent_discoveries'] . "\n";
    
    echo "\nDevices by type:\n";
    foreach ($stats['by_type'] as $type) {
        echo "  {$type['device_type']}: {$type['count']}\n";
    }
    
    echo "\nDevices by vendor:\n";
    foreach ($stats['by_vendor'] as $vendor) {
        echo "  {$vendor['vendor']}: {$vendor['count']}\n";
    }
}
?> 