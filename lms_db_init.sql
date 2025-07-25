-- Create interface_stats table for network monitoring
CREATE TABLE IF NOT EXISTS interface_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    interface_name VARCHAR(64) NOT NULL,
    rx_bytes BIGINT UNSIGNED NOT NULL,
    tx_bytes BIGINT UNSIGNED NOT NULL,
    rx_packets BIGINT UNSIGNED DEFAULT 0,
    tx_packets BIGINT UNSIGNED DEFAULT 0,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX(device_id, interface_name, timestamp)
);

-- Create network_alerts table for alert system
CREATE TABLE IF NOT EXISTS network_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    interface_name VARCHAR(64) NOT NULL,
    alert_type VARCHAR(32) NOT NULL,
    details JSON,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX(device_id, interface_name, timestamp),
    INDEX(alert_type, timestamp)
);

-- Table for discovered devices (SNMP/MNDP)
CREATE TABLE IF NOT EXISTS discovered_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    sys_name VARCHAR(255),
    sys_descr TEXT,
    method ENUM('SNMP','MNDP') NOT NULL,
    discovered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    imported BOOLEAN DEFAULT FALSE,
    UNIQUE KEY unique_ip_method (ip_address, method)
); 