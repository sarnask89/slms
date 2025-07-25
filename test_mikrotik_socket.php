<?php
// Device credentials
$host = '10.0.222.86';
$username = 'sarna';
$password = 'Loveganja151!';
$port = 8728;

// Function to write length
function write_len($len) {
    if ($len < 0x80) {
        return chr($len);
    } else if ($len < 0x4000) {
        $len |= 0x8000;
        return pack('n', $len);
    } else if ($len < 0x200000) {
        $len |= 0xC00000;
        return pack('N', $len) >> 1;
    } else if ($len < 0x10000000) {
        $len |= 0xE0000000;
        return pack('N', $len);
    } else {
        return chr(0xF0) . pack('N', $len);
    }
}

// Function to write word
function write_word($word) {
    $data = '';
    $data .= write_len(strlen($word));
    $data .= $word;
    return $data;
}

// Function to read length
function read_len($socket) {
    $byte = ord(fread($socket, 1));
    if (($byte & 0x80) == 0x00) {
        return $byte;
    } else if (($byte & 0xC0) == 0x80) {
        $byte &= ~0xC0;
        $length = ($byte << 8) + ord(fread($socket, 1));
        return $length;
    } else if (($byte & 0xE0) == 0xC0) {
        $byte &= ~0xE0;
        $length = ($byte << 16) + (ord(fread($socket, 1)) << 8) + ord(fread($socket, 1));
        return $length;
    } else if (($byte & 0xF0) == 0xE0) {
        $byte &= ~0xF0;
        $length = ($byte << 24) + (ord(fread($socket, 1)) << 16) + (ord(fread($socket, 1)) << 8) + ord(fread($socket, 1));
        return $length;
    } else {
        $length = ord(fread($socket, 1)) << 24;
        $length += ord(fread($socket, 1)) << 16;
        $length += ord(fread($socket, 1)) << 8;
        $length += ord(fread($socket, 1));
        return $length;
    }
}

// Function to read word
function read_word($socket) {
    $length = read_len($socket);
    if ($length > 0) {
        return fread($socket, $length);
    }
    return null;
}

// Connect to device
$socket = fsockopen($host, $port, $errno, $errstr, 10);
if (!$socket) {
    die("❌ Failed to connect: $errstr ($errno)\n");
}

echo "✅ Connected to $host:$port\n";

// Send login command
$data = write_word('/login');
$data .= write_word('=name=' . $username);
$data .= write_word('=password=' . $password);
fwrite($socket, $data);

// Read response
$response = '';
while (true) {
    $word = read_word($socket);
    if ($word === null) break;
    $response .= $word . "\n";
    if ($word == '!done') break;
}

echo "Login response:\n$response\n";

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
    $data = write_word($command);
    fwrite($socket, $data);
    
    // Read response
    $response = '';
    while (true) {
        $word = read_word($socket);
        if ($word === null) break;
        $response .= $word . "\n";
        if ($word == '!done') break;
    }
    
    echo "Response:\n$response\n";
}

// Close connection
fclose($socket);
echo "\n✅ Disconnected from device\n";
