<?php
// Device credentials
$host = '10.0.222.86';
$port = 8728;

// Try to connect
echo "Testing connection to $host:$port...\n";
$socket = @fsockopen($host, $port, $errno, $errstr, 5);

if ($socket) {
    echo "✅ Successfully connected!\n";
    fclose($socket);
} else {
    echo "❌ Connection failed: $errstr ($errno)\n";
}
