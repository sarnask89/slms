<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Device credentials
$host = '10.0.222.86';
$username = 'sarna';
$password = 'Loveganja151!';
$port = 8728;

try {
    // Create socket
    echo "Creating socket...\n";
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        throw new Exception("Failed to create socket: " . socket_strerror(socket_last_error()));
    }
    
    // Set timeout
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 5, 'usec' => 0));
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 5, 'usec' => 0));
    
    // Connect to device
    echo "Connecting to $host:$port...\n";
    $result = socket_connect($socket, $host, $port);
    if ($result === false) {
        throw new Exception("Failed to connect: " . socket_strerror(socket_last_error()));
    }
    echo "✅ Connected\n";
    
    // Send login command
    echo "Logging in as $username...\n";
    $command = "/login\n=name=$username\n=password=$password\n";
    $result = socket_write($socket, $command, strlen($command));
    if ($result === false) {
        throw new Exception("Failed to send login command: " . socket_strerror(socket_last_error()));
    }
    echo "✅ Sent login command ($result bytes)\n";
    
    // Read response
    echo "Reading login response...\n";
    $response = '';
    while (true) {
        $buffer = socket_read($socket, 2048, PHP_NORMAL_READ);
        if ($buffer === false) {
            throw new Exception("Failed to read response: " . socket_strerror(socket_last_error()));
        }
        if ($buffer === '') {
            break;
        }
        $response .= $buffer;
    }
    echo "✅ Login response:\n$response\n";
    
    // Test commands
    $commands = [
        '/system/identity/print',
        '/system/resource/print',
        '/system/health/print',
        '/interface/print',
        '/ip/dhcp-server/print',
        '/ip/dhcp-server/lease/print',
        '/ip/dns/print',
        '/queue/simple/print',
        '/system/clock/print'
    ];
    
    foreach ($commands as $command) {
        echo "\nTesting $command...\n";
        
        // Send command
        $command .= "\n";
        $result = socket_write($socket, $command, strlen($command));
        if ($result === false) {
            throw new Exception("Failed to send command: " . socket_strerror(socket_last_error()));
        }
        echo "✅ Sent command ($result bytes)\n";
        
        // Read response
        echo "Reading response...\n";
        $response = '';
        while (true) {
            $buffer = socket_read($socket, 2048, PHP_NORMAL_READ);
            if ($buffer === false) {
                throw new Exception("Failed to read response: " . socket_strerror(socket_last_error()));
            }
            if ($buffer === '') {
                break;
            }
            $response .= $buffer;
        }
        echo "✅ Response:\n$response\n";
    }
    
    // Close connection
    socket_close($socket);
    echo "\n✅ Disconnected from device\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    if (isset($socket)) {
        socket_close($socket);
    }
    exit(1);
}
