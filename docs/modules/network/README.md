# 🌐 Network Infrastructure Modules

## Overview
The Network Infrastructure modules provide comprehensive network management capabilities including device management, DHCP configuration, VLAN management, IP address allocation, and advanced network features for the AI SERVICE NETWORK MANAGEMENT SYSTEM.

---

## 📋 Available Modules

### 1. **Networks Module** (`networks.php`)
Core network management and configuration interface.

#### Features
- ✅ Network creation and management
- ✅ Subnet calculation and validation
- ✅ VLAN assignment
- ✅ Gateway configuration
- ✅ Network visualization
- ✅ IP pool management

#### Installation
```bash
# Create networks table
CREATE TABLE networks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    network VARCHAR(18) NOT NULL,
    netmask VARCHAR(15) NOT NULL,
    gateway VARCHAR(15),
    vlan_id INT,
    description TEXT,
    location VARCHAR(100),
    dns_primary VARCHAR(15),
    dns_secondary VARCHAR(15),
    dhcp_enabled BOOLEAN DEFAULT FALSE,
    dhcp_start VARCHAR(15),
    dhcp_end VARCHAR(15),
    status ENUM('active', 'inactive', 'planned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_network (network, netmask),
    INDEX idx_vlan (vlan_id)
);
```

#### Configuration
```php
// config/networks.php
return [
    'default_netmask' => '255.255.255.0',
    'reserved_ips' => 5, // Reserve first 5 IPs
    'auto_gateway' => true, // Auto-assign .1 as gateway
    'vlan_range' => [1, 4094],
    'private_networks' => [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16'
    ],
    'enable_ipv6' => false
];
```

#### Network Management
```php
// Create network
$networkId = createNetwork([
    'name' => 'Office LAN',
    'network' => '192.168.1.0',
    'netmask' => '255.255.255.0',
    'gateway' => '192.168.1.1',
    'vlan_id' => 100,
    'dhcp_enabled' => true,
    'dhcp_start' => '192.168.1.100',
    'dhcp_end' => '192.168.1.200'
]);

// Calculate subnet info
$subnetInfo = calculateSubnet('192.168.1.0', '255.255.255.0');
// Returns: network, broadcast, first_ip, last_ip, total_hosts

// Check IP availability
$available = isIPAvailable('192.168.1.50', $networkId);

// Get network utilization
$utilization = getNetworkUtilization($networkId);
```

---

### 2. **Devices Module** (`devices.php`)
Network device management and monitoring.

#### Features
- ✅ Device inventory management
- ✅ Device type categorization
- ✅ Location tracking
- ✅ Configuration backup
- ✅ Firmware management
- ✅ Device relationships

#### Installation
```bash
# Create devices table
CREATE TABLE devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hostname VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    mac_address VARCHAR(17),
    device_type ENUM('router', 'switch', 'ap', 'server', 'firewall', 'other'),
    manufacturer VARCHAR(50),
    model VARCHAR(50),
    serial_number VARCHAR(100),
    firmware_version VARCHAR(50),
    location VARCHAR(255),
    rack_position VARCHAR(20),
    parent_device_id INT,
    network_id INT,
    snmp_enabled BOOLEAN DEFAULT FALSE,
    snmp_community VARCHAR(100),
    ssh_enabled BOOLEAN DEFAULT FALSE,
    ssh_port INT DEFAULT 22,
    username VARCHAR(50),
    password VARCHAR(255),
    status ENUM('online', 'offline', 'maintenance', 'retired') DEFAULT 'online',
    last_seen TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (network_id) REFERENCES networks(id),
    FOREIGN KEY (parent_device_id) REFERENCES devices(id),
    INDEX idx_ip (ip_address),
    INDEX idx_type (device_type),
    INDEX idx_status (status)
);
```

#### Device Operations
```php
// Add device
$deviceId = addDevice([
    'hostname' => 'core-switch-01',
    'ip_address' => '192.168.1.2',
    'device_type' => 'switch',
    'manufacturer' => 'Cisco',
    'model' => 'Catalyst 3850',
    'location' => 'Server Room A',
    'snmp_enabled' => true,
    'snmp_community' => 'public'
]);

// Backup device configuration
$backup = backupDeviceConfig($deviceId);
saveConfigBackup($deviceId, $backup);

// Check device connectivity
$status = checkDeviceStatus($deviceId);

// Get device neighbors (CDP/LLDP)
$neighbors = getDeviceNeighbors($deviceId);
```

---

### 3. **Add/Edit Device Modules**
Device creation and modification interfaces.

#### **Add Device Module** (`add_device.php`)
##### Features
- ✅ Device discovery wizard
- ✅ Auto-detection via SNMP
- ✅ Template-based configuration
- ✅ Validation and testing
- ✅ Bulk device import

##### Device Discovery
```php
// Auto-discover device
$deviceInfo = discoverDevice('192.168.1.1', [
    'snmp_community' => 'public',
    'snmp_version' => '2c'
]);

// Apply device template
$template = getDeviceTemplate('mikrotik_router');
applyTemplate($deviceId, $template);

// Test device connectivity
$tests = testDevice($deviceId, [
    'ping' => true,
    'snmp' => true,
    'ssh' => true
]);
```

#### **Edit Device Module** (`edit_device.php`)
##### Features
- ✅ Configuration management
- ✅ Firmware updates
- ✅ Port management
- ✅ Service association
- ✅ Change tracking

---

### 4. **DHCP Management** (`dhcp_clients.php`, `dhcp_clients_v7.php`)
DHCP server integration and client management.

#### Features
- ✅ DHCP lease management
- ✅ Static reservations
- ✅ Pool configuration
- ✅ Option sets
- ✅ Lease history
- ✅ RouterOS v7 support

#### DHCP Configuration
```php
// config/dhcp.php
return [
    'servers' => [
        [
            'name' => 'Main DHCP',
            'type' => 'mikrotik',
            'host' => '192.168.1.1',
            'api_port' => 8728
        ],
        [
            'name' => 'Backup DHCP',
            'type' => 'isc-dhcp',
            'config_file' => '/etc/dhcp/dhcpd.conf'
        ]
    ],
    'sync_interval' => 300,
    'lease_time' => 86400,
    'option_sets' => [
        'default' => [
            'domain-name-servers' => '8.8.8.8, 8.8.4.4',
            'domain-name' => 'example.local',
            'ntp-servers' => '192.168.1.1'
        ]
    ]
];
```

#### DHCP Operations
```php
// Get active leases
$leases = getDHCPLeases($serverId);

// Create static reservation
createDHCPReservation([
    'mac_address' => '00:11:22:33:44:55',
    'ip_address' => '192.168.1.100',
    'hostname' => 'printer-01',
    'description' => 'Office Printer'
]);

// Configure DHCP pool
configureDHCPPool($serverId, [
    'name' => 'LAN_Pool',
    'network' => '192.168.1.0/24',
    'range_start' => '192.168.1.100',
    'range_end' => '192.168.1.200',
    'lease_time' => '1d'
]);
```

---

### 5. **Bridge & NAT Controller** (`bridge_nat_controller.php`)
Advanced bridge and NAT configuration management.

#### Features
- ✅ Bridge interface management
- ✅ NAT rule configuration
- ✅ Port forwarding
- ✅ Traffic shaping
- ✅ Firewall integration
- ✅ Performance optimization

#### Bridge Configuration
```php
// Create bridge
$bridgeId = createBridge([
    'name' => 'br0',
    'interfaces' => ['eth0', 'eth1'],
    'stp' => true,
    'priority' => 32768
]);

// Configure NAT
configureNAT([
    'type' => 'masquerade',
    'out_interface' => 'eth0',
    'source' => '192.168.1.0/24'
]);

// Add port forwarding
addPortForward([
    'protocol' => 'tcp',
    'dst_port' => 80,
    'to_address' => '192.168.1.100',
    'to_port' => 80,
    'comment' => 'Web Server'
]);
```

---

### 6. **Dynamic Network Controller** (`dynamic_network_controller.php`)
Dynamic network provisioning and SDN features.

#### Features
- ✅ Dynamic VLAN provisioning
- ✅ Network segmentation
- ✅ QoS policies
- ✅ Traffic isolation
- ✅ API-driven networking
- ✅ Network automation

#### Dynamic Provisioning
```php
// Provision network dynamically
$network = provisionNetwork([
    'type' => 'customer',
    'bandwidth' => '100M',
    'isolation' => true,
    'services' => ['internet', 'voip']
]);

// Apply QoS policy
applyQoSPolicy($networkId, [
    'guaranteed_bandwidth' => '50M',
    'max_bandwidth' => '100M',
    'priority' => 'high'
]);

// Create isolated segment
$segment = createNetworkSegment([
    'parent_network' => $networkId,
    'vlan_id' => 'auto',
    'firewall_policy' => 'strict'
]);
```

---

### 7. **Queue Management Modules**
Advanced queue management for bandwidth control.

#### **Enhanced Queue Manager** (`enhanced_queue_manager.php`)
##### Features
- ✅ HTB queue management
- ✅ PCQ optimization
- ✅ Burst configuration
- ✅ Priority queuing
- ✅ Queue statistics
- ✅ Auto-optimization

##### Queue Configuration
```php
// Create HTB queue
$queueId = createHTBQueue([
    'name' => 'Customer_100M',
    'parent' => 'global',
    'limit_at' => '50M',
    'max_limit' => '100M',
    'burst_limit' => '120M',
    'burst_threshold' => '75M',
    'burst_time' => '10s',
    'priority' => 4
]);

// Configure PCQ
configurePCQ([
    'name' => 'pcq-download',
    'rate' => '10M',
    'classifier' => 'dst-address',
    'perconnection_classifier' => 'both-addresses'
]);
```

#### **Intel X710 Queue Manager** (`intel_x710_queue_manager.php`)
##### Features
- ✅ Hardware queue optimization
- ✅ RSS configuration
- ✅ Flow director setup
- ✅ SR-IOV management
- ✅ Performance tuning

---

### 8. **Captive Portal Modules**
Guest access and authentication portal.

#### **Captive Portal** (`captive_portal.php`)
##### Features
- ✅ Guest authentication
- ✅ Voucher system
- ✅ Social login
- ✅ Bandwidth limiting
- ✅ Session management
- ✅ Custom branding

##### Portal Configuration
```php
// config/captive_portal.php
return [
    'authentication_methods' => ['voucher', 'social', 'sms'],
    'session_timeout' => 3600,
    'idle_timeout' => 1800,
    'redirect_url' => 'http://example.com',
    'terms_required' => true,
    'bandwidth_limits' => [
        'guest' => '5M/5M',
        'registered' => '10M/10M',
        'premium' => '50M/50M'
    ]
];
```

#### **VLAN Captive Portal** (`vlan_captive_portal.php`)
##### Features
- ✅ VLAN-based isolation
- ✅ Dynamic VLAN assignment
- ✅ 802.1X integration
- ✅ MAC authentication
- ✅ RADIUS support

---

## 🔧 Advanced Features

### Network Automation
```php
// Automate network provisioning
$automation = new NetworkAutomation();
$automation->provision([
    'template' => 'customer_network',
    'parameters' => [
        'customer_id' => 12345,
        'bandwidth' => '1G',
        'services' => ['internet', 'mpls']
    ]
]);

// Schedule network changes
scheduleNetworkChange([
    'change_type' => 'maintenance',
    'devices' => [$deviceId],
    'actions' => ['backup_config', 'update_firmware', 'reboot'],
    'scheduled_at' => '2025-01-20 02:00:00'
]);
```

### Network Discovery
```php
// Discover network topology
$topology = discoverNetworkTopology('192.168.0.0/16');

// Map network connections
$connections = mapNetworkConnections($topology);

// Generate network diagram
$diagram = generateNetworkDiagram($topology, 'svg');
```

---

## 📊 Monitoring & Analytics

### Network Statistics
```php
// Get network statistics
$stats = getNetworkStatistics($networkId, [
    'period' => 'last_24h',
    'metrics' => ['traffic', 'errors', 'utilization']
]);

// Analyze traffic patterns
$patterns = analyzeTrafficPatterns($networkId);

// Predict network growth
$prediction = predictNetworkGrowth($networkId, '6_months');
```

---

## 🚨 Troubleshooting

### Common Issues

#### 1. VLAN Configuration Issues
```bash
# Verify VLAN configuration
show vlan brief

# Check trunk ports
show interfaces trunk

# Test VLAN connectivity
ping -I vlan100 192.168.100.1
```

#### 2. DHCP Not Working
```php
// Debug DHCP server
$debug = debugDHCPServer($serverId);

// Check DHCP conflicts
$conflicts = checkDHCPConflicts($networkId);

// Verify DHCP relay
verifyDHCPRelay($deviceId);
```

#### 3. Network Performance Issues
```sql
-- Analyze network performance
SELECT 
    AVG(utilization) as avg_utilization,
    MAX(utilization) as peak_utilization,
    COUNT(CASE WHEN errors > 0 THEN 1 END) as error_count
FROM network_statistics
WHERE network_id = ? AND timestamp > NOW() - INTERVAL 1 DAY;
```

---

## 🔗 Related Modules
- [Device Monitoring](../monitoring/device-monitoring.md)
- [SNMP Configuration](../monitoring/snmp-monitoring.md)
- [Firewall Management](../security/firewall.md)
- [IP Address Management](./ipam.md)

---

**Module Version**: 5.0.0  
**Last Updated**: January 2025  
**Maintainer**: Network Engineering Team