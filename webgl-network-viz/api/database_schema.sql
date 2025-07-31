-- Network Scanner Database Schema
-- Comprehensive database for storing network device information, interfaces, connections, and scan results

-- Enable foreign key constraints
PRAGMA foreign_keys = ON;

-- ============================================================================
-- CORE TABLES
-- ============================================================================

-- Network devices table
CREATE TABLE IF NOT EXISTS devices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_id VARCHAR(255) UNIQUE NOT NULL,  -- Unique identifier (mndp_192.168.1.1, snmp_192.168.1.1)
    ip_address VARCHAR(45) NOT NULL,         -- IPv4 or IPv6 address
    mac_address VARCHAR(17),                 -- MAC address (XX:XX:XX:XX:XX:XX)
    hostname VARCHAR(255),
    device_type VARCHAR(50) NOT NULL,        -- router, switch, server, firewall, client
    vendor VARCHAR(100),                     -- MikroTik, Cisco, HP, etc.
    model VARCHAR(255),
    firmware_version VARCHAR(255),
    status VARCHAR(20) DEFAULT 'unknown',    -- online, offline, warning, error
    snmp_community VARCHAR(255),
    snmp_version INTEGER DEFAULT 2,
    location TEXT,
    description TEXT,
    discovery_method VARCHAR(20),            -- mndp, snmp, cdp, lldp, ping
    first_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Network interfaces table
CREATE TABLE IF NOT EXISTS interfaces (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_id INTEGER NOT NULL,
    interface_name VARCHAR(255) NOT NULL,
    interface_index INTEGER,
    description TEXT,
    mac_address VARCHAR(17),
    ip_address VARCHAR(45),
    subnet_mask VARCHAR(45),
    speed INTEGER,                           -- Speed in Mbps
    status VARCHAR(20) DEFAULT 'unknown',    -- up, down, testing, unknown
    admin_status VARCHAR(20) DEFAULT 'unknown', -- up, down, testing
    interface_type VARCHAR(50) DEFAULT 'ethernet', -- ethernet, wireless, fiber, etc.
    mtu INTEGER,
    bandwidth INTEGER,                       -- Bandwidth in Mbps
    duplex VARCHAR(10),                      -- full, half, auto
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);

-- Network connections table
CREATE TABLE IF NOT EXISTS connections (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    source_device_id INTEGER NOT NULL,
    source_interface_id INTEGER,
    target_device_id INTEGER NOT NULL,
    target_interface_id INTEGER,
    connection_type VARCHAR(50) DEFAULT 'ethernet', -- ethernet, fiber, wireless, virtual
    bandwidth INTEGER,                       -- Bandwidth in Mbps
    status VARCHAR(20) DEFAULT 'active',     -- active, inactive, error
    discovery_method VARCHAR(20),            -- snmp, arp, mac_table, manual
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (source_device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (source_interface_id) REFERENCES interfaces(id) ON DELETE SET NULL,
    FOREIGN KEY (target_device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (target_interface_id) REFERENCES interfaces(id) ON DELETE SET NULL
);

-- ============================================================================
-- SCANNING AND MONITORING TABLES
-- ============================================================================

-- Scan sessions table
CREATE TABLE IF NOT EXISTS scan_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_name VARCHAR(255),
    scan_type VARCHAR(50) NOT NULL,          -- mndp, snmp, cdp, lldp, full
    network_range VARCHAR(255) NOT NULL,     -- 192.168.1.0/24
    status VARCHAR(20) DEFAULT 'running',    -- running, completed, failed, cancelled
    devices_discovered INTEGER DEFAULT 0,
    connections_discovered INTEGER DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Scan results table
CREATE TABLE IF NOT EXISTS scan_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    scan_session_id INTEGER NOT NULL,
    device_id INTEGER,
    discovery_method VARCHAR(20) NOT NULL,   -- mndp, snmp, cdp, lldp
    ip_address VARCHAR(45) NOT NULL,
    response_time INTEGER,                   -- Response time in milliseconds
    success BOOLEAN DEFAULT 1,
    error_message TEXT,
    raw_response TEXT,                       -- Raw response data (JSON)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scan_session_id) REFERENCES scan_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE SET NULL
);

-- Device monitoring table
CREATE TABLE IF NOT EXISTS device_monitoring (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_id INTEGER NOT NULL,
    cpu_usage FLOAT,                         -- CPU usage percentage
    memory_usage FLOAT,                      -- Memory usage percentage
    temperature FLOAT,                       -- Temperature in Celsius
    uptime INTEGER,                          -- Uptime in seconds
    interface_count INTEGER,                 -- Number of interfaces
    active_connections INTEGER,              -- Number of active connections
    packet_loss FLOAT,                       -- Packet loss percentage
    latency INTEGER,                         -- Latency in milliseconds
    bandwidth_in INTEGER,                    -- Incoming bandwidth in Mbps
    bandwidth_out INTEGER,                   -- Outgoing bandwidth in Mbps
    monitored_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);

-- ============================================================================
-- MIKROTIK SPECIFIC TABLES
-- ============================================================================

-- MikroTik specific device information
CREATE TABLE IF NOT EXISTS mikrotik_devices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_id INTEGER NOT NULL UNIQUE,
    board_name VARCHAR(255),
    version VARCHAR(255),
    build_time VARCHAR(255),
    factory_software VARCHAR(255),
    free_memory INTEGER,                     -- Free memory in bytes
    total_memory INTEGER,                    -- Total memory in bytes
    cpu_load INTEGER,                        -- CPU load percentage
    cpu_count INTEGER,                       -- Number of CPU cores
    architecture VARCHAR(50),                -- CPU architecture
    platform VARCHAR(100),                   -- Hardware platform
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);

-- MikroTik wireless interfaces
CREATE TABLE IF NOT EXISTS mikrotik_wireless (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_id INTEGER NOT NULL,
    interface_name VARCHAR(255) NOT NULL,
    mode VARCHAR(50),                        -- ap-bridge, station, etc.
    band VARCHAR(20),                        -- 2ghz, 5ghz
    frequency INTEGER,                       -- Frequency in MHz
    channel_width VARCHAR(20),               -- 20, 40, 80 MHz
    ssid VARCHAR(255),
    security VARCHAR(50),                    -- wpa2, wpa3, none
    clients_connected INTEGER DEFAULT 0,
    signal_strength INTEGER,                 -- Signal strength in dBm
    noise_floor INTEGER,                     -- Noise floor in dBm
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);

-- ============================================================================
-- CISCO SPECIFIC TABLES
-- ============================================================================

-- Cisco specific device information
CREATE TABLE IF NOT EXISTS cisco_devices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_id INTEGER NOT NULL UNIQUE,
    ios_version VARCHAR(255),
    ios_image VARCHAR(255),
    uptime_seconds INTEGER,
    serial_number VARCHAR(255),
    chassis_type VARCHAR(255),
    memory_used INTEGER,                     -- Used memory in bytes
    memory_free INTEGER,                     -- Free memory in bytes
    flash_memory INTEGER,                    -- Flash memory in bytes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);

-- ============================================================================
-- CONFIGURATION AND SETTINGS TABLES
-- ============================================================================

-- Scanner configuration
CREATE TABLE IF NOT EXISTS scanner_config (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    config_key VARCHAR(255) UNIQUE NOT NULL,
    config_value TEXT,
    config_type VARCHAR(50) DEFAULT 'string', -- string, integer, boolean, json
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Network ranges for scanning
CREATE TABLE IF NOT EXISTS network_ranges (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    network_range VARCHAR(255) NOT NULL,     -- 192.168.1.0/24
    description TEXT,
    enabled BOOLEAN DEFAULT 1,
    scan_priority INTEGER DEFAULT 1,         -- Priority for scanning (1=highest)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SNMP communities
CREATE TABLE IF NOT EXISTS snmp_communities (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    community_name VARCHAR(255) NOT NULL,
    community_type VARCHAR(20) DEFAULT 'public', -- public, private, custom
    description TEXT,
    enabled BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- LOGGING AND AUDIT TABLES
-- ============================================================================

-- System logs
CREATE TABLE IF NOT EXISTS system_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    log_level VARCHAR(10) NOT NULL,          -- DEBUG, INFO, WARNING, ERROR
    log_source VARCHAR(100) NOT NULL,        -- mndp_scanner, snmp_scanner, etc.
    message TEXT NOT NULL,
    details TEXT,                            -- Additional details (JSON)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Device events
CREATE TABLE IF NOT EXISTS device_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_id INTEGER,
    event_type VARCHAR(50) NOT NULL,         -- discovered, status_change, error, etc.
    event_message TEXT NOT NULL,
    event_data TEXT,                         -- Event data (JSON)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE SET NULL
);

-- ============================================================================
-- INDEXES FOR PERFORMANCE
-- ============================================================================

-- Device indexes
CREATE INDEX IF NOT EXISTS idx_devices_ip_address ON devices(ip_address);
CREATE INDEX IF NOT EXISTS idx_devices_mac_address ON devices(mac_address);
CREATE INDEX IF NOT EXISTS idx_devices_device_type ON devices(device_type);
CREATE INDEX IF NOT EXISTS idx_devices_vendor ON devices(vendor);
CREATE INDEX IF NOT EXISTS idx_devices_status ON devices(status);
CREATE INDEX IF NOT EXISTS idx_devices_last_seen ON devices(last_seen);

-- Interface indexes
CREATE INDEX IF NOT EXISTS idx_interfaces_device_id ON interfaces(device_id);
CREATE INDEX IF NOT EXISTS idx_interfaces_ip_address ON interfaces(ip_address);
CREATE INDEX IF NOT EXISTS idx_interfaces_mac_address ON interfaces(mac_address);
CREATE INDEX IF NOT EXISTS idx_interfaces_status ON interfaces(status);

-- Connection indexes
CREATE INDEX IF NOT EXISTS idx_connections_source_device ON connections(source_device_id);
CREATE INDEX IF NOT EXISTS idx_connections_target_device ON connections(target_device_id);
CREATE INDEX IF NOT EXISTS idx_connections_status ON connections(status);

-- Scan indexes
CREATE INDEX IF NOT EXISTS idx_scan_sessions_status ON scan_sessions(status);
CREATE INDEX IF NOT EXISTS idx_scan_sessions_started_at ON scan_sessions(started_at);
CREATE INDEX IF NOT EXISTS idx_scan_results_scan_session ON scan_results(scan_session_id);
CREATE INDEX IF NOT EXISTS idx_scan_results_ip_address ON scan_results(ip_address);

-- Monitoring indexes
CREATE INDEX IF NOT EXISTS idx_device_monitoring_device_id ON device_monitoring(device_id);
CREATE INDEX IF NOT EXISTS idx_device_monitoring_monitored_at ON device_monitoring(monitored_at);

-- Log indexes
CREATE INDEX IF NOT EXISTS idx_system_logs_level ON system_logs(log_level);
CREATE INDEX IF NOT EXISTS idx_system_logs_source ON system_logs(log_source);
CREATE INDEX IF NOT EXISTS idx_system_logs_created_at ON system_logs(created_at);

-- ============================================================================
-- TRIGGERS FOR AUTOMATIC UPDATES
-- ============================================================================

-- Update device updated_at timestamp
CREATE TRIGGER IF NOT EXISTS update_devices_updated_at
    AFTER UPDATE ON devices
    FOR EACH ROW
BEGIN
    UPDATE devices SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Update interface updated_at timestamp
CREATE TRIGGER IF NOT EXISTS update_interfaces_updated_at
    AFTER UPDATE ON interfaces
    FOR EACH ROW
BEGIN
    UPDATE interfaces SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Update connection updated_at timestamp
CREATE TRIGGER IF NOT EXISTS update_connections_updated_at
    AFTER UPDATE ON connections
    FOR EACH ROW
BEGIN
    UPDATE connections SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Update MikroTik device updated_at timestamp
CREATE TRIGGER IF NOT EXISTS update_mikrotik_devices_updated_at
    AFTER UPDATE ON mikrotik_devices
    FOR EACH ROW
BEGIN
    UPDATE mikrotik_devices SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Update Cisco device updated_at timestamp
CREATE TRIGGER IF NOT EXISTS update_cisco_devices_updated_at
    AFTER UPDATE ON cisco_devices
    FOR EACH ROW
BEGIN
    UPDATE cisco_devices SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- ============================================================================
-- INITIAL DATA
-- ============================================================================

-- Insert default scanner configuration
INSERT OR IGNORE INTO scanner_config (config_key, config_value, config_type, description) VALUES
('scan_interval', '300', 'integer', 'Scan interval in seconds'),
('mndp_enabled', 'true', 'boolean', 'Enable MNDP scanning'),
('snmp_enabled', 'true', 'boolean', 'Enable SNMP scanning'),
('cdp_enabled', 'true', 'boolean', 'Enable CDP scanning'),
('lldp_enabled', 'true', 'boolean', 'Enable LLDP scanning'),
('snmp_timeout', '5', 'integer', 'SNMP timeout in seconds'),
('snmp_retries', '3', 'integer', 'SNMP retry attempts'),
('max_concurrent_scans', '10', 'integer', 'Maximum concurrent scan threads'),
('log_level', 'INFO', 'string', 'Logging level'),
('database_cleanup_days', '30', 'integer', 'Days to keep old data');

-- Insert default network ranges
INSERT OR IGNORE INTO network_ranges (network_range, description, scan_priority) VALUES
('192.168.1.0/24', 'Default LAN network', 1),
('10.0.0.0/24', 'Default management network', 2),
('172.16.0.0/24', 'Default infrastructure network', 3);

-- Insert default SNMP communities
INSERT OR IGNORE INTO snmp_communities (community_name, community_type, description) VALUES
('public', 'public', 'Default public community'),
('private', 'private', 'Default private community'),
('community', 'custom', 'Custom community string');

-- ============================================================================
-- VIEWS FOR COMMON QUERIES
-- ============================================================================

-- Device summary view
CREATE VIEW IF NOT EXISTS device_summary AS
SELECT 
    d.id,
    d.device_id,
    d.ip_address,
    d.hostname,
    d.device_type,
    d.vendor,
    d.model,
    d.status,
    d.last_seen,
    COUNT(i.id) as interface_count,
    COUNT(CASE WHEN i.status = 'up' THEN 1 END) as active_interfaces,
    COUNT(c.id) as connection_count
FROM devices d
LEFT JOIN interfaces i ON d.id = i.device_id
LEFT JOIN connections c ON d.id = c.source_device_id OR d.id = c.target_device_id
GROUP BY d.id;

-- Network topology view
CREATE VIEW IF NOT EXISTS network_topology AS
SELECT 
    c.id as connection_id,
    sd.device_id as source_device_id,
    sd.hostname as source_hostname,
    sd.ip_address as source_ip,
    si.interface_name as source_interface,
    td.device_id as target_device_id,
    td.hostname as target_hostname,
    td.ip_address as target_ip,
    ti.interface_name as target_interface,
    c.connection_type,
    c.bandwidth,
    c.status,
    c.created_at
FROM connections c
JOIN devices sd ON c.source_device_id = sd.id
JOIN devices td ON c.target_device_id = td.id
LEFT JOIN interfaces si ON c.source_interface_id = si.id
LEFT JOIN interfaces ti ON c.target_interface_id = ti.id;

-- Recent discoveries view
CREATE VIEW IF NOT EXISTS recent_discoveries AS
SELECT 
    d.device_id,
    d.ip_address,
    d.hostname,
    d.device_type,
    d.vendor,
    d.discovery_method,
    d.first_seen,
    d.last_seen,
    sr.response_time,
    sr.success
FROM devices d
LEFT JOIN scan_results sr ON d.ip_address = sr.ip_address
WHERE d.last_seen >= datetime('now', '-24 hours')
ORDER BY d.last_seen DESC;

-- ============================================================================
-- STORED PROCEDURES (SQLite doesn't support stored procedures, but we can create functions)
-- ============================================================================

-- Note: SQLite doesn't support stored procedures, but we can create helper functions
-- in the application code that use these prepared statements

-- Clean up old data
-- DELETE FROM system_logs WHERE created_at < datetime('now', '-30 days');
-- DELETE FROM device_monitoring WHERE monitored_at < datetime('now', '-7 days');
-- DELETE FROM scan_results WHERE created_at < datetime('now', '-90 days');

-- Update device status based on last seen
-- UPDATE devices SET status = 'offline' WHERE last_seen < datetime('now', '-5 minutes');

-- ============================================================================
-- COMMENTS AND DOCUMENTATION
-- ============================================================================

/*
Network Scanner Database Schema

This database is designed to store comprehensive network device information
discovered through various protocols including MNDP, SNMP, CDP, and LLDP.

Key Features:
- Device discovery and monitoring
- Interface and connection mapping
- Vendor-specific information (MikroTik, Cisco)
- Scan session tracking
- Performance monitoring
- Comprehensive logging

Usage:
1. Run this schema to create the database
2. Use the NetworkScannerDaemon to populate data
3. Connect WebGL interface via API endpoints

Tables Overview:
- devices: Core device information
- interfaces: Network interface details
- connections: Device interconnections
- scan_sessions: Scan operation tracking
- scan_results: Individual scan results
- device_monitoring: Performance metrics
- mikrotik_devices: MikroTik-specific data
- cisco_devices: Cisco-specific data
- system_logs: Application logging
- device_events: Device state changes

API Endpoints:
- GET /api/devices - List all devices
- GET /api/devices/{id} - Get specific device
- GET /api/interfaces - List all interfaces
- GET /api/connections - List all connections
- GET /api/topology - Get network topology
- POST /api/scan - Trigger new scan
- GET /api/status - Get scanner status
*/ 