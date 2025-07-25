<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/routeros_api.class.php';

// Device credentials
$host = '10.0.222.86';
$username = 'sarna';
$password = 'Loveganja151!';
$port = 8728;

// Create API object
$api = new RouterosAPI();
$api->debug = true;

// Connect to the device
if ($api->connect($host, $username, $password, $port)) {
    echo "✅ Connected to $host:$port\n";
    
    // Test commands
    $tests = [
        [
            'name' => 'System Identity',
            'command' => '/system/identity/print'
        ],
        [
            'name' => 'System Resources',
            'command' => '/system/resource/print'
        ],
        [
            'name' => 'System Health',
            'command' => '/system/health/print'
        ],
        [
            'name' => 'Interfaces',
            'command' => '/interface/print'
        ],
        [
            'name' => 'DHCP Server',
            'command' => '/ip/dhcp-server/print'
        ],
        [
            'name' => 'DHCP Leases',
            'command' => '/ip/dhcp-server/lease/print'
        ],
        [
            'name' => 'DNS Settings',
            'command' => '/ip/dns/print'
        ],
        [
            'name' => 'Queue Tree',
            'command' => '/queue/simple/print'
        ],
        [
            'name' => 'System Clock',
            'command' => '/system/clock/print'
        ]
    ];
    
    // Run tests
    foreach ($tests as $test) {
        echo "\nTesting {$test['name']}...\n";
        $result = $api->comm($test['command']);
        if ($result !== false) {
            echo "✅ Success! Response:\n";
            print_r($result);
        } else {
            echo "❌ Failed to get {$test['name']}\n";
        }
    }
    
    // Disconnect
    $api->disconnect();
    echo "\n✅ Disconnected from device\n";
} else {
    echo "❌ Failed to connect to $host:$port\n";
}
