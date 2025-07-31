<?php
// Common MikroTik OIDs for SNMP graphing/monitoring
// Format: [ 'oid' => [ 'name' => ..., 'desc' => ..., 'category' => ... ] ]
return [
    // System
    '1.3.6.1.4.1.14988.1.1.7.1.0' => [ 'name' => 'CPU Load', 'desc' => 'CPU load (%)', 'category' => 'system' ],
    '1.3.6.1.4.1.14988.1.1.7.2.0' => [ 'name' => 'Memory Free', 'desc' => 'Free memory (bytes)', 'category' => 'system' ],
    '1.3.6.1.4.1.14988.1.1.7.3.0' => [ 'name' => 'Memory Total', 'desc' => 'Total memory (bytes)', 'category' => 'system' ],
    '1.3.6.1.4.1.14988.1.1.7.4.0' => [ 'name' => 'Uptime', 'desc' => 'System uptime (TimeTicks)', 'category' => 'system' ],
    
    // Health
    '1.3.6.1.4.1.14988.1.1.3.1.0' => [ 'name' => 'Board Temperature', 'desc' => 'Temperature (°C)', 'category' => 'health' ],
    '1.3.6.1.4.1.14988.1.1.3.2.0' => [ 'name' => 'Board Voltage', 'desc' => 'Voltage (V)', 'category' => 'health' ],
    '1.3.6.1.4.1.14988.1.1.3.3.0' => [ 'name' => 'Fan Speed', 'desc' => 'Fan speed (RPM)', 'category' => 'health' ],
    '1.3.6.1.4.1.14988.1.1.3.4.0' => [ 'name' => 'PSU Voltage', 'desc' => 'PSU voltage (V)', 'category' => 'health' ],
    
    // Wireless (first entry, index 1)
    '1.3.6.1.4.1.14988.1.1.1.1.2.1' => [ 'name' => 'Wireless TX Rate', 'desc' => 'TX rate (bps)', 'category' => 'wireless' ],
    '1.3.6.1.4.1.14988.1.1.1.1.3.1' => [ 'name' => 'Wireless RX Rate', 'desc' => 'RX rate (bps)', 'category' => 'wireless' ],
    '1.3.6.1.4.1.14988.1.1.1.1.4.1' => [ 'name' => 'Wireless Signal', 'desc' => 'Signal strength (dBm)', 'category' => 'wireless' ],
    '1.3.6.1.4.1.14988.1.1.1.1.5.1' => [ 'name' => 'Wireless SSID', 'desc' => 'SSID', 'category' => 'wireless' ],
    '1.3.6.1.4.1.14988.1.1.1.1.6.1' => [ 'name' => 'Wireless BSSID', 'desc' => 'BSSID (MAC)', 'category' => 'wireless' ],
    
    // Interface Statistics (IF-MIB) - Common interface counters
    '1.3.6.1.2.1.2.2.1.10.1' => [ 'name' => 'Interface InOctets (ether1)', 'desc' => 'Incoming octets for interface 1', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.16.1' => [ 'name' => 'Interface OutOctets (ether1)', 'desc' => 'Outgoing octets for interface 1', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.10.2' => [ 'name' => 'Interface InOctets (ether2)', 'desc' => 'Incoming octets for interface 2', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.16.2' => [ 'name' => 'Interface OutOctets (ether2)', 'desc' => 'Outgoing octets for interface 2', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.10.3' => [ 'name' => 'Interface InOctets (ether3)', 'desc' => 'Incoming octets for interface 3', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.16.3' => [ 'name' => 'Interface OutOctets (ether3)', 'desc' => 'Outgoing octets for interface 3', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.10.4' => [ 'name' => 'Interface InOctets (ether4)', 'desc' => 'Incoming octets for interface 4', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.16.4' => [ 'name' => 'Interface OutOctets (ether4)', 'desc' => 'Outgoing octets for interface 4', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.10.5' => [ 'name' => 'Interface InOctets (ether5)', 'desc' => 'Incoming octets for interface 5', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.16.5' => [ 'name' => 'Interface OutOctets (ether5)', 'desc' => 'Outgoing octets for interface 5', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.10.6' => [ 'name' => 'Interface InOctets (ether6)', 'desc' => 'Incoming octets for interface 6', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.16.6' => [ 'name' => 'Interface OutOctets (ether6)', 'desc' => 'Outgoing octets for interface 6', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.10.7' => [ 'name' => 'Interface InOctets (ether7)', 'desc' => 'Incoming octets for interface 7', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.16.7' => [ 'name' => 'Interface OutOctets (ether7)', 'desc' => 'Outgoing octets for interface 7', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.10.8' => [ 'name' => 'Interface InOctets (ether8)', 'desc' => 'Incoming octets for interface 8', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.16.8' => [ 'name' => 'Interface OutOctets (ether8)', 'desc' => 'Outgoing octets for interface 8', 'category' => 'interface' ],
    
    // Interface Error Counters
    '1.3.6.1.2.1.2.2.1.13.1' => [ 'name' => 'Interface InErrors (ether1)', 'desc' => 'Incoming errors for interface 1', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.19.1' => [ 'name' => 'Interface OutErrors (ether1)', 'desc' => 'Outgoing errors for interface 1', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.13.2' => [ 'name' => 'Interface InErrors (ether2)', 'desc' => 'Incoming errors for interface 2', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.19.2' => [ 'name' => 'Interface OutErrors (ether2)', 'desc' => 'Outgoing errors for interface 2', 'category' => 'interface' ],
    
    // Interface Status
    '1.3.6.1.2.1.2.2.1.8.1' => [ 'name' => 'Interface Status (ether1)', 'desc' => 'Interface operational status (1=up, 2=down)', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.8.2' => [ 'name' => 'Interface Status (ether2)', 'desc' => 'Interface operational status (1=up, 2=down)', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.8.3' => [ 'name' => 'Interface Status (ether3)', 'desc' => 'Interface operational status (1=up, 2=down)', 'category' => 'interface' ],
    '1.3.6.1.2.1.2.2.1.8.4' => [ 'name' => 'Interface Status (ether4)', 'desc' => 'Interface operational status (1=up, 2=down)', 'category' => 'interface' ],
    
    // MikroTik Queue Statistics (Simple Queue)
    '1.3.6.1.4.1.14988.1.1.2.1.1.1.1' => [ 'name' => 'Queue Bytes In (queue1)', 'desc' => 'Bytes in for queue 1', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.1.1.2.1' => [ 'name' => 'Queue Bytes Out (queue1)', 'desc' => 'Bytes out for queue 1', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.1.1.3.1' => [ 'name' => 'Queue Packets In (queue1)', 'desc' => 'Packets in for queue 1', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.1.1.4.1' => [ 'name' => 'Queue Packets Out (queue1)', 'desc' => 'Packets out for queue 1', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.1.1.5.1' => [ 'name' => 'Queue Dropped (queue1)', 'desc' => 'Dropped packets for queue 1', 'category' => 'queue' ],
    
    '1.3.6.1.4.1.14988.1.1.2.1.1.1.2' => [ 'name' => 'Queue Bytes In (queue2)', 'desc' => 'Bytes in for queue 2', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.1.1.2.2' => [ 'name' => 'Queue Bytes Out (queue2)', 'desc' => 'Bytes out for queue 2', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.1.1.3.2' => [ 'name' => 'Queue Packets In (queue2)', 'desc' => 'Packets in for queue 2', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.1.1.4.2' => [ 'name' => 'Queue Packets Out (queue2)', 'desc' => 'Packets out for queue 2', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.1.1.5.2' => [ 'name' => 'Queue Dropped (queue2)', 'desc' => 'Dropped packets for queue 2', 'category' => 'queue' ],
    
    // MikroTik Queue Tree Statistics
    '1.3.6.1.4.1.14988.1.1.2.2.1.1.1' => [ 'name' => 'Queue Tree Bytes In (tree1)', 'desc' => 'Bytes in for queue tree 1', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.2.1.2.1' => [ 'name' => 'Queue Tree Bytes Out (tree1)', 'desc' => 'Bytes out for queue tree 1', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.2.1.3.1' => [ 'name' => 'Queue Tree Packets In (tree1)', 'desc' => 'Packets in for queue tree 1', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.2.1.4.1' => [ 'name' => 'Queue Tree Packets Out (tree1)', 'desc' => 'Packets out for queue tree 1', 'category' => 'queue' ],
    '1.3.6.1.4.1.14988.1.1.2.2.1.5.1' => [ 'name' => 'Queue Tree Dropped (tree1)', 'desc' => 'Dropped packets for queue tree 1', 'category' => 'queue' ],
]; 