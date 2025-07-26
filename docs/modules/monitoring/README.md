# 📊 Monitoring & Analytics Modules

## Overview
The Monitoring & Analytics modules provide real-time network monitoring, performance metrics, alerts, and comprehensive reporting capabilities for the AI SERVICE NETWORK MANAGEMENT SYSTEM.

---

## 📋 Available Modules

### 1. **SNMP Monitoring Module** (`snmp_graph.php`)
Comprehensive SNMP-based monitoring with graphing capabilities.

#### Features
- ✅ Real-time SNMP polling
- ✅ Interactive graphs (bandwidth, CPU, memory)
- ✅ Multi-vendor support
- ✅ Custom OID monitoring
- ✅ Historical data retention
- ✅ Threshold alerts

#### Installation
```bash
# Install SNMP PHP extension
sudo apt-get install php-snmp snmp snmp-mibs-downloader

# Create SNMP monitoring tables
CREATE TABLE snmp_devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hostname VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    snmp_version ENUM('1', '2c', '3') DEFAULT '2c',
    snmp_community VARCHAR(100),
    snmp_username VARCHAR(100),
    snmp_auth_protocol VARCHAR(10),
    snmp_auth_password VARCHAR(100),
    snmp_priv_protocol VARCHAR(10),
    snmp_priv_password VARCHAR(100),
    device_type VARCHAR(50),
    polling_interval INT DEFAULT 300,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_poll TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE snmp_data (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    device_id INT NOT NULL,
    oid VARCHAR(255) NOT NULL,
    value VARCHAR(1000),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES snmp_devices(id),
    INDEX idx_device_timestamp (device_id, timestamp),
    INDEX idx_oid (oid)
);
```

#### Configuration
```php
// config/snmp.php
return [
    'default_version' => '2c',
    'default_community' => 'public',
    'timeout' => 1000000, // microseconds
    'retries' => 3,
    'oid_cache' => true,
    'bulk_walk' => true,
    'max_repetitions' => 10,
    'graph_types' => [
        'bandwidth' => [
            'in_oid' => '.1.3.6.1.2.1.2.2.1.10',
            'out_oid' => '.1.3.6.1.2.1.2.2.1.16',
            'unit' => 'bps'
        ],
        'cpu' => [
            'oid' => '.1.3.6.1.4.1.2021.11.9.0',
            'unit' => 'percent'
        ],
        'memory' => [
            'total_oid' => '.1.3.6.1.4.1.2021.4.5.0',
            'used_oid' => '.1.3.6.1.4.1.2021.4.6.0',
            'unit' => 'bytes'
        ]
    ]
];
```

#### Usage Examples
```php
// Add SNMP device
$deviceId = addSNMPDevice([
    'hostname' => 'router.example.com',
    'ip_address' => '192.168.1.1',
    'snmp_version' => '2c',
    'snmp_community' => 'public',
    'device_type' => 'router'
]);

// Poll device
$data = pollSNMPDevice($deviceId);

// Get interface statistics
$interfaces = getSNMPInterfaces($deviceId);

// Generate bandwidth graph
$graph = generateBandwidthGraph($deviceId, $interfaceId, 'last_24_hours');
```

---

### 2. **Interface Monitoring Module** (`interface_monitoring.php`)
Detailed network interface monitoring and analysis.

#### Features
- ✅ Real-time interface status
- ✅ Traffic analysis
- ✅ Error and discard tracking
- ✅ Interface utilization
- ✅ Link status monitoring
- ✅ VLAN monitoring

#### Advanced Monitoring
```php
// Monitor interface health
$health = monitorInterfaceHealth($deviceId, $interfaceIndex);

// Track interface errors
$errors = getInterfaceErrors($deviceId, $interfaceIndex, [
    'period' => 'last_hour',
    'threshold' => 100
]);

// Calculate utilization
$utilization = calculateInterfaceUtilization($deviceId, $interfaceIndex);

// Set up alerts
setInterfaceAlert($deviceId, $interfaceIndex, [
    'metric' => 'utilization',
    'threshold' => 80,
    'action' => 'email'
]);
```

---

### 3. **Cacti Integration Module** (`cacti_integration.php`)
Seamless integration with Cacti monitoring system.

#### Features
- ✅ Device synchronization
- ✅ Graph embedding
- ✅ Data source management
- ✅ Template application
- ✅ User synchronization
- ✅ API wrapper

#### Installation
```bash
# Configure Cacti database access
CREATE USER 'aiservice_cacti'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT ON cacti.* TO 'aiservice_cacti'@'localhost';

# Install Cacti API dependencies
composer require cacti/api-client
```

#### Configuration
```php
// config/cacti.php
return [
    'url' => 'http://cacti.example.com',
    'api_endpoint' => '/api/v1',
    'username' => 'admin',
    'password' => 'admin_password',
    'api_token' => 'your_api_token',
    'sync_interval' => 300, // seconds
    'graph_cache' => true,
    'graph_width' => 700,
    'graph_height' => 300,
    'rra_id' => 1 // Round Robin Archive ID
];
```

#### Integration Examples
```php
// Sync device to Cacti
$cactiDeviceId = syncDeviceToCacti($deviceId);

// Get Cacti graphs
$graphs = getCactiGraphs($cactiDeviceId);

// Embed graph in dashboard
$graphUrl = getCactiGraphUrl($graphId, [
    'start' => '-1d',
    'end' => 'now',
    'width' => 800,
    'height' => 400
]);

// Import Cacti templates
importCactiTemplate('interface_traffic.xml');
```

---

### 4. **Network Monitoring Enhanced** (`network_monitoring_enhanced.php`)
Advanced network monitoring with AI-powered insights.

#### Features
- ✅ Predictive analytics
- ✅ Anomaly detection
- ✅ Network topology mapping
- ✅ Service dependency tracking
- ✅ Automated remediation
- ✅ Multi-site monitoring

#### AI-Powered Features
```php
// Anomaly detection
$anomalies = detectNetworkAnomalies([
    'sensitivity' => 'medium',
    'window' => '1h',
    'algorithms' => ['isolation_forest', 'lstm']
]);

// Predictive maintenance
$predictions = predictNetworkIssues([
    'horizon' => '24h',
    'confidence' => 0.8
]);

// Auto-remediation
if ($anomaly['severity'] === 'high') {
    executeRemediation($anomaly['remediation_script']);
}
```

---

### 5. **Bandwidth Reports Module** (`bandwidth_reports.php`)
Comprehensive bandwidth usage reporting and analysis.

#### Features
- ✅ Usage reports by client/device
- ✅ 95th percentile calculations
- ✅ Traffic pattern analysis
- ✅ Billing integration
- ✅ Scheduled reports
- ✅ Export capabilities

#### Report Types
```php
// Generate client bandwidth report
$report = generateBandwidthReport($clientId, [
    'period' => 'monthly',
    'include_graphs' => true,
    'format' => 'pdf'
]);

// Calculate 95th percentile
$percentile95 = calculate95thPercentile($deviceId, $interfaceId, 'monthly');

// Traffic analysis
$patterns = analyzeTrafficPatterns($deviceId, [
    'granularity' => 'hourly',
    'period' => 'last_week'
]);

// Scheduled reports
scheduleReport('bandwidth_summary', [
    'recipients' => ['admin@example.com'],
    'frequency' => 'monthly',
    'day' => 1
]);
```

---

### 6. **Queue Monitoring Module** (`queue_monitoring.php`)
MikroTik queue monitoring and management.

#### Features
- ✅ Simple and tree queue monitoring
- ✅ Real-time queue statistics
- ✅ Burst monitoring
- ✅ Queue optimization suggestions
- ✅ Historical analysis
- ✅ Client bandwidth tracking

#### Queue Management
```php
// Get queue statistics
$queues = getQueueStatistics($routerId);

// Monitor specific queue
$queueStats = monitorQueue($routerId, $queueName, [
    'metrics' => ['rate', 'packets', 'drops']
]);

// Analyze queue performance
$analysis = analyzeQueuePerformance($routerId, $queueName);

// Optimize queues
$suggestions = optimizeQueues($routerId);
foreach ($suggestions as $suggestion) {
    applyQueueOptimization($routerId, $suggestion);
}
```

---

### 7. **Network Alerts Module** (`network_alerts.php`)
Intelligent alerting system with multiple notification channels.

#### Features
- ✅ Multi-channel notifications (email, SMS, Slack)
- ✅ Alert escalation
- ✅ Maintenance windows
- ✅ Alert correlation
- ✅ Custom alert rules
- ✅ Alert history

#### Alert Configuration
```php
// Create alert rule
createAlertRule([
    'name' => 'High CPU Usage',
    'condition' => 'cpu_usage > 80',
    'duration' => '5m',
    'severity' => 'warning',
    'actions' => [
        ['type' => 'email', 'recipients' => ['ops@example.com']],
        ['type' => 'slack', 'channel' => '#alerts']
    ]
]);

// Set maintenance window
setMaintenanceWindow([
    'devices' => [$deviceId],
    'start' => '2025-01-15 02:00:00',
    'end' => '2025-01-15 04:00:00',
    'suppress_alerts' => true
]);

// Alert correlation
correlateAlerts([
    'window' => '5m',
    'group_by' => ['device', 'type'],
    'min_count' => 3
]);
```

---

### 8. **System Status Module** (`system_status.php`)
Comprehensive system health monitoring and status dashboard.

#### Features
- ✅ Service health checks
- ✅ Database performance monitoring
- ✅ API endpoint monitoring
- ✅ Resource utilization
- ✅ Component status
- ✅ Health score calculation

#### Health Monitoring
```php
// Get system health
$health = getSystemHealth();
// Returns: overall_score, components, issues

// Monitor specific component
$dbHealth = monitorComponent('database', [
    'checks' => ['connectivity', 'performance', 'replication']
]);

// Resource monitoring
$resources = getResourceUtilization();
// Returns: cpu, memory, disk, network

// Service checks
$services = checkServices([
    'web_server' => 'http://localhost',
    'api' => 'http://localhost/api/health',
    'database' => 'mysql://localhost:3306'
]);
```

---

## 📈 Advanced Analytics

### Predictive Analytics
```php
// Traffic prediction
$prediction = predictTraffic($deviceId, [
    'model' => 'arima',
    'horizon' => '7d',
    'confidence_interval' => 0.95
]);

// Capacity planning
$capacity = planCapacity([
    'growth_rate' => 0.1, // 10% monthly
    'planning_horizon' => '1y',
    'safety_margin' => 0.2
]);
```

### Custom Dashboards
```php
// Create monitoring dashboard
$dashboard = createDashboard([
    'name' => 'Network Overview',
    'widgets' => [
        ['type' => 'graph', 'metric' => 'bandwidth', 'devices' => 'all'],
        ['type' => 'gauge', 'metric' => 'cpu', 'threshold' => 80],
        ['type' => 'table', 'data' => 'top_talkers'],
        ['type' => 'map', 'show' => 'device_status']
    ]
]);
```

---

## 🔧 Performance Optimization

### Data Retention
```php
// config/monitoring_retention.php
return [
    'raw_data' => '7d',
    '5min_avg' => '30d',
    'hourly_avg' => '90d',
    'daily_avg' => '365d',
    'monthly_avg' => 'forever'
];

// Run data aggregation
php artisan monitoring:aggregate --interval=5min
php artisan monitoring:cleanup --older-than=7d
```

### Caching Strategy
```php
// Enable monitoring cache
Cache::remember("device_{$deviceId}_status", 300, function() use ($deviceId) {
    return getDeviceStatus($deviceId);
});

// Pre-generate common graphs
pregenerateGraphs(['bandwidth', 'cpu', 'memory'], 'last_24h');
```

---

## 🚨 Troubleshooting

### Common Issues

#### 1. SNMP Timeout
```bash
# Test SNMP connectivity
snmpwalk -v2c -c public 192.168.1.1 system

# Increase timeout in config
'timeout' => 5000000 // 5 seconds
```

#### 2. Graph Generation Issues
```php
// Check RRD files
$rrdPath = '/var/lib/cacti/rra/';
if (!is_writable($rrdPath)) {
    chmod($rrdPath, 0755);
}

// Rebuild poller cache
php /usr/share/cacti/cli/rebuild_poller_cache.php
```

#### 3. High Database Load
```sql
-- Optimize monitoring tables
OPTIMIZE TABLE snmp_data;
ALTER TABLE snmp_data PARTITION BY RANGE (UNIX_TIMESTAMP(timestamp)) (
    PARTITION p_2025_01 VALUES LESS THAN (UNIX_TIMESTAMP('2025-02-01'))
);
```

---

## 🔗 Related Modules
- [Device Management](../network/device-management.md)
- [Alert Management](../administration/alert-management.md)
- [Reporting System](../reporting/README.md)
- [API Integration](../api-integration/monitoring-api.md)

---

**Module Version**: 4.2.0  
**Last Updated**: January 2025  
**Maintainer**: Network Operations Team