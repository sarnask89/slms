<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login
require_login();

$pageTitle = 'Mikrotik Api';
ob_start();
?>

// MikroTik API Helper Class
class MikroTikAPI {
    private $host;
    private $username;
    private $password;
    private $port;
    private $ssl;
    
    public function __construct($host, $username, $password, $port = 8728, $ssl = false) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->ssl = $ssl;
    }
    
    // Execute a command on MikroTik
    public function execute($command) {
        $socket = $this->connect();
        if (!$socket) {
            return ['error' => 'Nie można połączyć się z routerem MikroTik'];
        }
        
        try {
            $this->login($socket);
            $response = $this->sendCommand($socket, $command);
            fclose($socket);
            
            // Check for error responses
            if (strpos($response, '!trap') !== false) {
                return ['error' => 'Router returned error: ' . $response];
            }
            
            return $response;
        } catch (Exception $e) {
            if ($socket) {
                fclose($socket);
            }
            return ['error' => 'Błąd komunikacji z routerem: ' . $e->getMessage()];
        }
    }
    
    // Ping a specific IP
    public function ping($ip, $count = 4) {
        $command = "/ping address=$ip count=$count";
        return $this->execute($command);
    }
    
    // ARP ping a specific IP
    public function arpPing($ip, $interface, $count = 4) {
        $command = "/ping address=$ip count=$count arp-ping=yes interface=$interface";
        return $this->execute($command);
    }
    
    // Get available interfaces
    public function getInterfaces() {
        $command = "/interface/print";
        return $this->execute($command);
    }
    
    // Get interface details
    public function getInterfaceInfo($interface) {
        $command = "/interface/print where name=$interface";
        return $this->execute($command);
    }
    
    // Get ARP table
    public function getArpTable() {
        $command = "/ip/arp/print";
        return $this->execute($command);
    }
    
    // Get DHCP leases
    public function getDhcpLeases() {
        $command = "/ip/dhcp-server/lease/print";
        return $this->execute($command);
    }
    
    public function getDhcpNetworks() {
        $command = "/ip/dhcp-server/network/print";
        $response = $this->execute($command);
        return $this->parseApiResponse($response);
    }
    
    public function getDhcpServers() {
        $command = "/ip/dhcp-server/print";
        $response = $this->execute($command);
        return $this->parseApiResponse($response);
    }
    
    public function getAddresses() {
        $command = "/ip/address/print";
        $response = $this->execute($command);
        return $this->parseApiResponse($response);
    }
    
    public function parseApiResponse($response) {
        if (is_array($response) && isset($response['error'])) {
            return $response; // Return error as is
        }
        
        if (!is_string($response)) {
            return ['error' => 'Invalid response format'];
        }
        
        $result = [];
        $lines = explode("\n", $response);
        $current_item = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Skip control lines
            if (strpos($line, '!') === 0) {
                if (strpos($line, '!re') === 0) {
                    // Start of a new item
                    if (!empty($current_item)) {
                        $result[] = $current_item;
                    }
                    $current_item = [];
                }
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $current_item[$key] = $value;
                }
            }
        }
        
        // Add the last item if exists
        if (!empty($current_item)) {
            $result[] = $current_item;
        }
        
        return $result;
    }
    
    public function sshExecute($command) {
        $ssh_cmd = "sshpass -p '{$this->password}' ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no {$this->username}@{$this->host} '{$command}' 2>/dev/null";
        $output = shell_exec($ssh_cmd);
        return $output;
    }
    
    public function parseDhcpLeasesFromSsh($ssh_output) {
        $leases = [];
        $lines = explode("\n", $ssh_output);
        $current_comment = '';
        $in_data_section = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Skip header lines
            if (strpos($line, 'Flags:') === 0 || strpos($line, 'Columns:') === 0 || strpos($line, '#') === 0) {
                continue;
            }
            
            // Check if line starts with comment
            if (strpos($line, ';;;') === 0) {
                $current_comment = trim(substr($line, 3));
                continue;
            }
            
            // Parse lease line (format: number IP MAC or status IP MAC)
            if (preg_match('/^\s*(\d+)\s+(\d+\.\d+\.\d+\.\d+)\s+([0-9A-Fa-f:]+)$/', $line, $matches)) {
                $status = 'bound';
                $ip = $matches[2];
                $mac = $matches[3];
                
                $leases[] = [
                    'status' => $status,
                    'address' => $ip,
                    'mac-address' => $mac,
                    'comment' => $current_comment ?: 'Unknown'
                ];
                
                $current_comment = ''; // Reset for next lease
            } elseif (preg_match('/^([A-ZX])\s+(\d+\.\d+\.\d+\.\d+)\s+([0-9A-Fa-f:]+)$/', $line, $matches)) {
                $status = $matches[1];
                $ip = $matches[2];
                $mac = $matches[3];
                
                $leases[] = [
                    'status' => $status,
                    'address' => $ip,
                    'mac-address' => $mac,
                    'comment' => $current_comment ?: 'Unknown'
                ];
                
                $current_comment = ''; // Reset for next lease
            }
        }
        
        return $leases;
    }
    
    private function connect() {
        $protocol = $this->ssl ? 'ssl://' : 'tcp://';
        $socket = @fsockopen($protocol . $this->host, $this->port, $errno, $errstr, 10);
        
        if (!$socket) {
            error_log("MikroTik API connection failed: $errstr ($errno) to $this->host:$this->port");
            return false;
        }
        
        // Set socket timeout to prevent hanging
        stream_set_timeout($socket, 10); // 10 seconds timeout
        
        return $socket;
    }
    
    private function login($socket) {
        // Send login request
        $login = "/login";
        $this->sendRequest($socket, $login);
        $response = $this->readResponse($socket);
        
        // Extract challenge
        if (preg_match('/!re challenge=([^ ]+)/', $response, $matches)) {
            $challenge = $matches[1];
            
            // Generate response
            $response = md5(chr(0) . $this->password . pack('H*', $challenge));
            
            // Send login with response
            $login = "/login name=" . $this->username . " response=00" . $response;
            $this->sendRequest($socket, $login);
            $login_response = $this->readResponse($socket);
            
            // Check if login was successful
            if (strpos($login_response, '!done') === false) {
                throw new Exception('Login failed: ' . $login_response);
            }
        } else {
            // Try simple login without challenge (for older firmware or different auth)
            $login = "/login name=" . $this->username . " password=" . $this->password;
            $this->sendRequest($socket, $login);
            $login_response = $this->readResponse($socket);
            
            // Check if login was successful
            if (strpos($login_response, '!done') === false && strpos($login_response, '!trap') !== false) {
                throw new Exception('Simple login failed: ' . $login_response);
            }
        }
    }
    
    private function sendCommand($socket, $command) {
        $this->sendRequest($socket, $command);
        return $this->readResponse($socket);
    }
    
    private function sendRequest($socket, $command) {
        $length = strlen($command);
        $data = pack('N', $length) . $command;
        $written = fwrite($socket, $data);
        
        if ($written === false || $written != strlen($data)) {
            throw new Exception('Nie można wysłać polecenia do routera');
        }
    }
    
    private function readResponse($socket) {
        $response = '';
        $timeout = time() + 15; // 15 seconds timeout
        
        while (true) {
            // Check for timeout
            if (time() > $timeout) {
                error_log("MikroTik API read timeout for $this->host:$this->port");
                break;
            }
            
            $length_data = fread($socket, 4);
            if ($length_data === false || strlen($length_data) < 4) {
                break;
            }
            
            $unpacked = unpack('N', $length_data);
            if ($unpacked === false) {
                break;
            }
            
            $length = $unpacked[1];
            if ($length == 0) break;
            
            $data = fread($socket, $length);
            if ($data === false) {
                break;
            }
            
            $response .= $data;
        }
        
        return $response;
    }
}

// Alternative method using SSH for MikroTik commands
function mikrotikSshCall($host, $username, $password, $command, $port = 22) {
    // Escape the command for shell
    $escaped_command = escapeshellarg($command);
    $escaped_host = escapeshellarg($host);
    $escaped_user = escapeshellarg($username);
    
    // Use sshpass directly for faster execution - removed BatchMode=yes which can cause issues
    $sshpass_command = "timeout 8 sshpass -p " . escapeshellarg($password) . " ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ConnectTimeout=5 -p $port $escaped_user@$escaped_host $escaped_command 2>&1";
    $output = shell_exec($sshpass_command);
    
    // If sshpass fails, try without password (for key-based auth)
    if (empty($output) || strpos($output, 'Permission denied') !== false) {
        $ssh_command = "timeout 8 ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ConnectTimeout=5 -p $port $escaped_user@$escaped_host $escaped_command 2>&1";
        $output = shell_exec($ssh_command);
    }
    
    return $output;
}

// Get available interfaces from MikroTik device
function getMikrotikInterfaces($host, $username, $password, $port = 8728) {
    $command = "/interface/print";
    $output = mikrotikSshCall($host, $username, $password, $command, $port);
    
    $interfaces = [];
    $lines = explode("\n", $output);
    
    foreach ($lines as $line) {
        if (preg_match('/^\s*(\d+)\s+([^\s]+)\s+([^\s]+)\s+([^\s]+)/', $line, $matches)) {
            $interfaces[] = [
                'id' => $matches[1],
                'name' => $matches[2],
                'type' => $matches[3],
                'status' => $matches[4]
            ];
        }
    }
    
    return $interfaces;
}

// Get DHCP networks from MikroTik device
function getMikrotikDhcpNetworks($host, $username, $password, $port = 22) {
    $command = "/ip/dhcp-server/network/print";
    $output = mikrotikSshCall($host, $username, $password, $command, $port);
    
    $networks = [];
    $lines = explode("\n", $output);
    $current_network = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Skip header lines
        if (strpos($line, 'Flags:') === 0 || strpos($line, 'Columns:') === 0 || strpos($line, '#') === 0) {
            continue;
        }
        
        // Check if line starts with comment
        if (strpos($line, ';;;') === 0) {
            if (!empty($current_network)) {
                $networks[] = $current_network;
            }
            $current_network = ['comment' => trim(substr($line, 3))];
            continue;
        }
        
        // Parse network line (format: number address gateway dns1 dns2 domain)
        if (preg_match('/^\s*(\d+)\s+(\d+\.\d+\.\d+\.\d+\/\d+)\s+(\d+\.\d+\.\d+\.\d+)\s+(\d+\.\d+\.\d+\.\d+)\s+(\d+\.\d+\.\d+\.\d+)\s+([^\s]*)/', $line, $matches)) {
            $current_network['id'] = $matches[1];
            $current_network['address'] = $matches[2];
            $current_network['gateway'] = $matches[3];
            $current_network['dns1'] = $matches[4];
            $current_network['dns2'] = $matches[5];
            $current_network['domain'] = $matches[6];
        }
    }
    
    // Add the last network
    if (!empty($current_network)) {
        $networks[] = $current_network;
    }
    
    return $networks;
}

// Get IP addresses and their interfaces from MikroTik device
function getMikrotikAddresses($host, $username, $password, $port = 22) {
    $command = "/ip/address/print";
    $output = mikrotikSshCall($host, $username, $password, $command, $port);
    
    $addresses = [];
    $lines = explode("\n", $output);
    $current_address = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Skip header lines
        if (strpos($line, 'Flags:') === 0 || strpos($line, 'Columns:') === 0 || strpos($line, '#') === 0) {
            continue;
        }
        
        // Check if line starts with comment
        if (strpos($line, ';;;') === 0) {
            if (!empty($current_address)) {
                $addresses[] = $current_address;
            }
            $current_address = ['comment' => trim(substr($line, 3))];
            continue;
        }
        
        // Parse address line (format: number address network interface)
        if (preg_match('/^\s*(\d+)\s+(\d+\.\d+\.\d+\.\d+\/\d+)\s+(\d+\.\d+\.\d+\.\d+)\s+([^\s]+)/', $line, $matches)) {
            $current_address['id'] = $matches[1];
            $current_address['address'] = $matches[2];
            $current_address['network'] = $matches[3];
            $current_address['interface'] = $matches[4];
        }
    }
    
    // Add the last address
    if (!empty($current_address)) {
        $addresses[] = $current_address;
    }
    
    return $addresses;
}

// Alternative method using shell_exec for MikroTik API
function mikrotikApiCall($host, $username, $password, $command) {
    $api_script = "#!/bin/bash
# MikroTik API call script
HOST='$host'
USER='$username'
PASS='$password'
CMD='$command'

# Create API call
echo \"\$CMD\" | ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 \$USER@\$HOST 2>/dev/null
";
    
    $temp_file = tempnam('/tmp', 'mikrotik_');
    file_put_contents($temp_file, $api_script);
    chmod($temp_file, 0755);
    
    $output = shell_exec($temp_file . ' 2>&1');
    unlink($temp_file);
    
    return $output;
}

// Simple ping function using system ping
function systemPing($ip, $count = 4) {
    $command = "timeout 10 ping -c $count -W 2 $ip 2>&1";
    $output = shell_exec($command);
    
    // Parse ping results
    $lines = explode("\n", $output);
    $result = [
        'success' => false,
        'sent' => 0,
        'received' => 0,
        'loss' => 100,
        'min' => 0,
        'avg' => 0,
        'max' => 0,
        'output' => $output
    ];
    
    foreach ($lines as $line) {
        if (preg_match('/(\d+) packets transmitted, (\d+) received/', $line, $matches)) {
            $result['sent'] = (int)$matches[1];
            $result['received'] = (int)$matches[2];
            $result['loss'] = $result['sent'] > 0 ? (($result['sent'] - $result['received']) / $result['sent']) * 100 : 100;
            $result['success'] = $result['received'] > 0;
        }
        
        if (preg_match('/rtt min\/avg\/max\/mdev = ([\d.]+)\/([\d.]+)\/([\d.]+)\/([\d.]+) ms/', $line, $matches)) {
            $result['min'] = (float)$matches[1];
            $result['avg'] = (float)$matches[2];
            $result['max'] = (float)$matches[3];
        }
    }
    
    return $result;
}

// ARP ping using faster alternative methods
function systemArpPing($ip, $count = 4) {
    // Try nmap first (fastest and most reliable)
    $command = "timeout 5 nmap -sn -PR $ip 2>&1";
    $output = shell_exec($command);
    
    // If nmap shows host is up, consider it successful
    if (strpos($output, 'Host is up') !== false) {
        return [
            'success' => true,
            'sent' => 1,
            'received' => 1,
            'output' => $output
        ];
    }
    
    // Try ping with shorter timeout
    $command = "timeout 5 ping -c 2 -W 1 $ip 2>&1";
    $output = shell_exec($command);
    
    $result = [
        'success' => false,
        'sent' => 0,
        'received' => 0,
        'output' => $output
    ];
    
    // Parse ping output
    if (preg_match('/(\d+) packets transmitted, (\d+) received/', $output, $matches)) {
        $result['sent'] = (int)$matches[1];
        $result['received'] = (int)$matches[2];
        $result['success'] = $result['received'] > 0;
    }
    
    return $result;
}

// Parse DHCP networks from SSH output
function parseDhcpNetworksFromSsh($ssh_output) {
    $networks = [];
    $lines = explode("\n", $ssh_output);
    $current_comment = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Skip header lines
        if (strpos($line, 'Columns:') === 0 || strpos($line, '#') === 0) {
            continue;
        }
        
        // Check if line starts with comment
        if (strpos($line, ';;;') === 0) {
            $current_comment = trim(substr($line, 3));
            continue;
        }
        
        // Parse network line (format: number address gateway dns-server)
        if (preg_match('/^\s*(\d+)\s+(\d+\.\d+\.\d+\.\d+\/\d+)\s+(\d+\.\d+\.\d+\.\d+)\s+(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
            $networks[] = [
                'id' => $matches[1],
                'address' => $matches[2],
                'gateway' => $matches[3],
                'dns1' => $matches[4],
                'dns2' => null,
                'domain' => null,
                'comment' => $current_comment
            ];
            $current_comment = ''; // Reset comment for next network
        }
    }
    
    return $networks;
}

// Parse IP addresses from SSH output
function parseAddressesFromSsh($ssh_output) {
    $addresses = [];
    $lines = explode("\n", $ssh_output);
    $current_address = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Skip header lines
        if (strpos($line, 'Flags:') === 0 || strpos($line, 'Columns:') === 0 || strpos($line, '#') === 0) {
            continue;
        }
        
        // Check if line starts with comment
        if (strpos($line, ';;;') === 0) {
            if (!empty($current_address)) {
                $addresses[] = $current_address;
            }
            $current_address = ['comment' => trim(substr($line, 3))];
            continue;
        }
        
        // Parse address line (format: number address network interface)
        if (preg_match('/^\s*(\d+)\s+(\d+\.\d+\.\d+\.\d+\/\d+)\s+(\d+\.\d+\.\d+\.\d+)\s+([^\s]+)/', $line, $matches)) {
            $current_address['id'] = $matches[1];
            $current_address['address'] = $matches[2];
            $current_address['network'] = $matches[3];
            $current_address['interface'] = $matches[4];
        }
    }
    
    // Add the last address
    if (!empty($current_address)) {
        $addresses[] = $current_address;
    }
    
    return $addresses;
}
?> 

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?>
