-- Create tables for new modules

-- Mikrotik devices table
CREATE TABLE IF NOT EXISTS mikrotik_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(100),
    api_port INT DEFAULT 8728,
    api_ssl BOOLEAN DEFAULT FALSE,
    status ENUM('online', 'offline', 'error') DEFAULT 'offline',
    last_seen TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- DHCP servers table
CREATE TABLE IF NOT EXISTS dhcp_servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    subnet VARCHAR(45) NOT NULL,
    gateway VARCHAR(45),
    dns_servers TEXT,
    lease_time INT DEFAULT 86400,
    status ENUM('active', 'inactive', 'error') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- SNMP devices table
CREATE TABLE IF NOT EXISTS snmp_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT,
    ip_address VARCHAR(45) NOT NULL,
    community VARCHAR(100) NOT NULL,
    version ENUM('v1', 'v2c', 'v3') DEFAULT 'v2c',
    timeout INT DEFAULT 5,
    retries INT DEFAULT 3,
    status ENUM('online', 'offline', 'error') DEFAULT 'offline',
    last_poll TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- VLANs table
CREATE TABLE IF NOT EXISTS vlans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vlan_id INT NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    subnet VARCHAR(45),
    gateway VARCHAR(45),
    dhcp_server VARCHAR(45),
    status ENUM('active', 'inactive', 'error') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- IP ranges table
CREATE TABLE IF NOT EXISTS ip_ranges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    start_ip VARCHAR(45) NOT NULL,
    end_ip VARCHAR(45) NOT NULL,
    subnet_mask VARCHAR(45),
    gateway VARCHAR(45),
    dns_servers TEXT,
    purpose VARCHAR(255),
    status ENUM('active', 'inactive', 'error') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Scan jobs table
CREATE TABLE IF NOT EXISTS scan_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    targets TEXT NOT NULL,
    parameters JSON,
    status ENUM('pending', 'running', 'completed', 'failed', 'stopped') DEFAULT 'pending',
    progress INT DEFAULT 0,
    results JSON,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Client devices table
CREATE TABLE IF NOT EXISTS client_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45),
    mac_address VARCHAR(17),
    client_id INT,
    location VARCHAR(255),
    model VARCHAR(255),
    serial_number VARCHAR(255),
    purchase_date DATE,
    warranty_expiry DATE,
    status ENUM('active', 'inactive', 'maintenance', 'retired') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Core devices table
CREATE TABLE IF NOT EXISTS core_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45),
    mac_address VARCHAR(17),
    location VARCHAR(255),
    rack_position VARCHAR(50),
    model VARCHAR(255),
    serial_number VARCHAR(255),
    purchase_date DATE,
    warranty_expiry DATE,
    status ENUM('online', 'offline', 'maintenance', 'error') DEFAULT 'offline',
    uptime BIGINT DEFAULT 0,
    cpu_usage DECIMAL(5,2) DEFAULT 0,
    memory_usage DECIMAL(5,2) DEFAULT 0,
    temperature DECIMAL(5,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Network segments table
CREATE TABLE IF NOT EXISTS network_segments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    vlan_id INT,
    subnet VARCHAR(45) NOT NULL,
    gateway VARCHAR(45),
    dhcp_server VARCHAR(45),
    dns_servers TEXT,
    description TEXT,
    status ENUM('active', 'inactive', 'error') DEFAULT 'active',
    device_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Device categories table
CREATE TABLE IF NOT EXISTS device_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    parent_category INT,
    icon VARCHAR(100),
    color VARCHAR(7) DEFAULT '#007bff',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert some sample data
INSERT INTO device_categories (name, description, icon, color, sort_order) VALUES
('Routers', 'Network routers and gateways', 'router', '#007bff', 1),
('Switches', 'Network switches', 'switch', '#28a745', 2),
('Servers', 'Server equipment', 'server', '#ffc107', 3),
('Workstations', 'Client workstations', 'desktop', '#6c757d', 4),
('Mobile Devices', 'Mobile phones and tablets', 'mobile', '#e83e8c', 5);

-- Insert sample Mikrotik device
INSERT INTO mikrotik_devices (name, ip_address, username, api_port, status) VALUES
('Main Router', '192.168.1.1', 'admin', 8728, 'online');

-- Insert sample DHCP server
INSERT INTO dhcp_servers (name, ip_address, subnet, gateway, dns_servers) VALUES
('Main DHCP', '192.168.1.1', '192.168.1.0/24', '192.168.1.1', '8.8.8.8,8.8.4.4');

-- Insert sample VLAN
INSERT INTO vlans (vlan_id, name, description, subnet, gateway) VALUES
(10, 'Management', 'Management VLAN', '192.168.10.0/24', '192.168.10.1'),
(20, 'Clients', 'Client VLAN', '192.168.20.0/24', '192.168.20.1'),
(30, 'Servers', 'Server VLAN', '192.168.30.0/24', '192.168.30.1');

-- Insert sample IP range
INSERT INTO ip_ranges (name, start_ip, end_ip, subnet_mask, gateway, purpose) VALUES
('Client Range', '192.168.20.100', '192.168.20.200', '255.255.255.0', '192.168.20.1', 'Client devices'),
('Server Range', '192.168.30.10', '192.168.30.50', '255.255.255.0', '192.168.30.1', 'Server devices');

-- Insert sample client device
INSERT INTO client_devices (name, type, ip_address, client_id, location, model, status) VALUES
('Client PC 1', 'Workstation', '192.168.20.101', 1, 'Office 1', 'Dell OptiPlex', 'active'),
('Client PC 2', 'Workstation', '192.168.20.102', 2, 'Office 2', 'HP ProDesk', 'active');

-- Insert sample core device
INSERT INTO core_devices (name, type, ip_address, location, model, status, cpu_usage, memory_usage) VALUES
('Core Switch 1', 'Switch', '192.168.10.10', 'Server Room', 'Cisco Catalyst', 'online', 25.5, 45.2),
('Main Server', 'Server', '192.168.30.10', 'Server Room', 'Dell PowerEdge', 'online', 65.8, 78.3);

-- Insert sample network segment
INSERT INTO network_segments (name, type, vlan_id, subnet, gateway, description) VALUES
('Management Network', 'Management', 10, '192.168.10.0/24', '192.168.10.1', 'Network management segment'),
('Client Network', 'Client', 20, '192.168.20.0/24', '192.168.20.1', 'Client devices segment'),
('Server Network', 'Server', 30, '192.168.30.0/24', '192.168.30.1', 'Server devices segment'); 