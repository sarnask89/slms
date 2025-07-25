<?php


require_once __DIR__ . '/config.php';
require_once __DIR__ . '/modules/mikrotik_api.php';

// SNMP Support Class (same as in enhanced monitoring)
class SNMPMonitor {
    private $host;
    private $community;
    private $timeout = 1000000;
    private $retries = 3;
    
    public function __construct($host, $community = 'public') {
        $this->host = $host;
        $this->community = $community;
    }
    
    public function getInterfaceStats() {
        if (!extension_loaded('snmp')) {
            throw new Exception('SNMP extension not loaded');
        }
        
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
        snmp_set_quick_print(true);
        
        $interfaces = [];
        
        try {
            // Get interface names
            $ifNames = snmprealwalk($this->host, $this->community, '1.3.6.1.2.1.2.2.1.2');
            $ifInOctets = snmprealwalk($this->host, $this->community, '1.3.6.1.2.1.2.2.1.10');
            $ifOutOctets = snmprealwalk($this->host, $this->community, '1.3.6.1.2.1.2.2.1.16');
            
            foreach ($ifNames as $oid => $name) {
                $index = explode('.', $oid);
                $index = end($index);
                
                $inOctets = isset($ifInOctets["1.3.6.1.2.1.2.2.1.10.$index"]) ? 
                    $ifInOctets["1.3.6.1.2.1.2.2.1.10.$index"] : 0;
                $outOctets = isset($ifOutOctets["1.3.6.1.2.1.2.2.1.16.$index"]) ? 
                    $ifOutOctets["1.3.6.1.2.1.2.2.1.16.$index"] : 0;
                
                $interfaces[] = [
                    'name' => $name,
                    'rx_bytes' => $inOctets,
                    'tx_bytes' => $outOctets
                ];
            }
        } catch (Exception $e) {
            throw new Exception("SNMP error: " . $e->getMessage());
        }
        
        return $interfaces;
    }
}

// Logging function
function log_message($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n";
    
    // Also write to log file if configured
    $log_file = __DIR__ . '/logs/interface_polling.log';
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

try {
    log_message("Starting automatic interface polling...");
    
    $pdo = get_pdo();
    
    // Get all devices with API credentials
    $stmt = $pdo->query("SELECT id, name, ip_address, api_username, api_password FROM skeleton_devices WHERE api_username IS NOT NULL AND api_password IS NOT NULL");
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    log_message("Found " . count($devices) . " devices to poll");
    
    if (empty($devices)) {
        log_message("No devices found with API credentials");
        exit(0);
    }
    
    $total_polled = 0;
    $rest_api_polled = 0;
    $snmp_polled = 0;
    $errors = [];
    
    foreach ($devices as $device) {
        log_message("Polling device: {$device['name']} ({$device['ip_address']})");
        
        try {
            // Try REST API first
            $api = new MikroTikAPI($device['ip_address'], $device['api_username'], $device['api_password']);
            
            try {
                $interfaces = $api->restGet('/interface/print');
                log_message("Found " . count($interfaces) . " interfaces via REST API");
                
                foreach ($interfaces as $iface) {
                    try {
                        $stats = $api->restGet('/interface/monitor-traffic', [
                            'interface' => $iface['name'],
                            'once' => true
                        ]);
                        
                        if (isset($stats[0]['rx-bits-per-second']) && isset($stats[0]['tx-bits-per-second'])) {
                            $stmt2 = $pdo->prepare("INSERT INTO interface_stats (device_id, interface_name, rx_bytes, tx_bytes, timestamp) VALUES (?, ?, ?, ?, NOW())");
                            $stmt2->execute([
                                $device['id'],
                                $iface['name'],
                                $stats[0]['rx-bits-per-second'],
                                $stats[0]['tx-bits-per-second']
                            ]);
                            $rest_api_polled++;
                            log_message("Polled interface {$iface['name']} via REST API");
                        }
                    } catch (Exception $e) {
                        log_message("Error polling interface {$iface['name']}: " . $e->getMessage());
                    }
                }
            } catch (Exception $e) {
                log_message("REST API failed: " . $e->getMessage());
                
                // Fallback to SNMP
                try {
                    $snmp = new SNMPMonitor($device['ip_address']);
                    $snmp_interfaces = $snmp->getInterfaceStats();
                    log_message("Found " . count($snmp_interfaces) . " interfaces via SNMP");
                    
                    foreach ($snmp_interfaces as $iface) {
                        $stmt2 = $pdo->prepare("INSERT INTO interface_stats (device_id, interface_name, rx_bytes, tx_bytes, timestamp) VALUES (?, ?, ?, ?, NOW())");
                        $stmt2->execute([
                            $device['id'],
                            $iface['name'],
                            $iface['rx_bytes'],
                            $iface['tx_bytes']
                        ]);
                        $snmp_polled++;
                        log_message("Polled interface {$iface['name']} via SNMP");
                    }
                } catch (Exception $snmp_error) {
                    log_message("SNMP also failed: " . $snmp_error->getMessage());
                    $errors[] = "Error processing {$device['name']}: Both REST API and SNMP failed";
                }
            }
            
        } catch (Exception $e) {
            $error_msg = "Error processing {$device['name']}: " . $e->getMessage();
            log_message($error_msg);
            $errors[] = $error_msg;
        }
    }
    
    $total_polled = $rest_api_polled + $snmp_polled;
    
    log_message("Polling completed! Total interfaces polled: $total_polled (REST API: $rest_api_polled, SNMP: $snmp_polled)");
    
    if (!empty($errors)) {
        log_message("Errors encountered: " . count($errors));
        foreach ($errors as $error) {
            log_message("  - $error");
        }
    }
    
    // Clean up old data (keep last 30 days)
    $stmt = $pdo->prepare("DELETE FROM interface_stats WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    if ($deleted > 0) {
        log_message("Cleaned up $deleted old records (older than 30 days)");
    }
    
    log_message("Automatic polling finished successfully");
    
} catch (Exception $e) {
    log_message("Fatal error: " . $e->getMessage());
    exit(1);
} 