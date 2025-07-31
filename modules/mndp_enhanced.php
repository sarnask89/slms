<?php
/**
 * Enhanced MNDP (Mikrotik Neighbour Discovery Protocol) Implementation
 * Based on MAC-Telnet source code documentation
 */

class MNDPEnhanced {
    const MNDP_PORT = 5678;
    const MNDP_VERSION = 1;
    const MNDP_TTL = 255;
    
    private $socket;
    private $discovered_devices = [];
    
    public function __construct() {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($this->socket === false) {
            throw new Exception("Failed to create UDP socket");
        }
        
        // Set broadcast option
        socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, 1);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        // Bind to any address on MNDP port
        if (!socket_bind($this->socket, '0.0.0.0', self::MNDP_PORT)) {
            throw new Exception("Failed to bind to MNDP port");
        }
    }
    
    /**
     * Send MNDP discovery packet
     */
    public function sendDiscovery() {
        // MNDP packet: 4-byte unsigned int = 0
        $packet = pack('N', 0);
        socket_sendto($this->socket, $packet, strlen($packet), 0, '255.255.255.255', self::MNDP_PORT);
    }
    
    /**
     * Listen for MNDP responses
     */
    public function listenForResponses($timeout = 5) {
        $start_time = time();
        $this->discovered_devices = [];
        
        while (time() - $start_time < $timeout) {
            $from = '';
            $port = 0;
            $buf = '';
            
            // Non-blocking receive
            $r = @socket_recvfrom($this->socket, $buf, 2048, MSG_DONTWAIT, $from, $port);
            
            if ($r && $from) {
                $device_info = $this->parseMNDPResponse($buf, $from);
                if ($device_info) {
                    $this->discovered_devices[] = $device_info;
                }
            }
            
            usleep(100000); // 100ms delay
        }
        
        return $this->discovered_devices;
    }
    
    /**
     * Parse MNDP response packet
     * Based on MAC-Telnet MNDP packet structure
     */
    private function parseMNDPResponse($data, $source_ip) {
        if (strlen($data) < 4) {
            return null;
        }
        
        // Parse MNDP header (4 bytes)
        $header = unpack('N', substr($data, 0, 4));
        $version = $header[1];
        
        // Extract device information from packet
        $device_info = [
            'ip' => $source_ip,
            'method' => 'MNDP',
            'discovered_at' => date('Y-m-d H:i:s'),
            'version' => $version,
            'mac_address' => '',
            'identity' => '',
            'platform' => '',
            'board_name' => '',
            'version_info' => '',
            'uptime' => '',
            'software_id' => '',
            'interface_name' => ''
        ];
        
        // Parse additional fields if available
        $offset = 4;
        while ($offset < strlen($data)) {
            if ($offset + 2 > strlen($data)) break;
            
            $field_header = unpack('n', substr($data, $offset, 2));
            $field_type = $field_header[1];
            $offset += 2;
            
            if ($offset + 2 > strlen($data)) break;
            
            $field_length = unpack('n', substr($data, $offset, 2));
            $length = $field_length[1];
            $offset += 2;
            
            if ($offset + $length > strlen($data)) break;
            
            $field_value = substr($data, $offset, $length);
            $offset += $length;
            
            // Map field types to device info
            switch ($field_type) {
                case 1: // MAC Address
                    $device_info['mac_address'] = $this->formatMacAddress($field_value);
                    break;
                case 2: // Identity
                    $device_info['identity'] = $field_value;
                    break;
                case 3: // Platform
                    $device_info['platform'] = $field_value;
                    break;
                case 4: // Board Name
                    $device_info['board_name'] = $field_value;
                    break;
                case 5: // Version Info
                    $device_info['version_info'] = $field_value;
                    break;
                case 6: // Uptime
                    $device_info['uptime'] = $field_value;
                    break;
                case 7: // Software ID
                    $device_info['software_id'] = $field_value;
                    break;
                case 8: // Interface Name
                    $device_info['interface_name'] = $field_value;
                    break;
            }
        }
        
        return $device_info;
    }
    
    /**
     * Format MAC address from binary data
     */
    private function formatMacAddress($mac_binary) {
        if (strlen($mac_binary) !== 6) {
            return '';
        }
        
        $mac_parts = unpack('C6', $mac_binary);
        return sprintf('%02X:%02X:%02X:%02X:%02X:%02X', 
            $mac_parts[1], $mac_parts[2], $mac_parts[3],
            $mac_parts[4], $mac_parts[5], $mac_parts[6]);
    }
    
    /**
     * Discover devices using MNDP
     */
    public function discover($timeout = 5) {
        try {
            $this->sendDiscovery();
            return $this->listenForResponses($timeout);
        } catch (Exception $e) {
            throw new Exception("MNDP discovery failed: " . $e->getMessage());
        }
    }
    
    /**
     * Clean up socket
     */
    public function __destruct() {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
}

// Usage example:
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    try {
        $mndp = new MNDPEnhanced();
        $devices = $mndp->discover(10); // 10 second timeout
        
        echo "<h2>MNDP Discovery Results</h2>";
        echo "<p>Found " . count($devices) . " devices</p>";
        
        if (!empty($devices)) {
            echo "<table border='1'>";
            echo "<tr><th>IP</th><th>MAC</th><th>Identity</th><th>Platform</th><th>Version</th></tr>";
            foreach ($devices as $device) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($device['ip']) . "</td>";
                echo "<td>" . htmlspecialchars($device['mac_address']) . "</td>";
                echo "<td>" . htmlspecialchars($device['identity']) . "</td>";
                echo "<td>" . htmlspecialchars($device['platform']) . "</td>";
                echo "<td>" . htmlspecialchars($device['version_info']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?> 