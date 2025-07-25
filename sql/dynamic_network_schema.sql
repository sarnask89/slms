-- Dynamic Network Controller Database Schema
-- Manages DHCP leases and NAT rules dynamically

-- Dynamic leases table
CREATE TABLE IF NOT EXISTS dynamic_leases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    ip_address VARCHAR(15) NOT NULL,
    username VARCHAR(100),
    user_role ENUM('guest', 'user', 'admin') DEFAULT 'guest',
    lease_id VARCHAR(50), -- Mikrotik lease ID
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    expired_at TIMESTAMP NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    bandwidth_limit INT DEFAULT 0, -- Mbps, 0 = unlimited
    allowed_domains JSON,
    INDEX idx_mac_address (mac_address),
    INDEX idx_ip_address (ip_address),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at),
    UNIQUE KEY unique_active_mac (mac_address, status)
);

-- Dynamic connection logs
CREATE TABLE IF NOT EXISTS dynamic_connection_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    ip_address VARCHAR(15) NOT NULL,
    username VARCHAR(100),
    user_role ENUM('guest', 'user', 'admin'),
    lease_id VARCHAR(50),
    action ENUM('connection', 'authentication', 'disconnection', 'expiry', 'revocation') NOT NULL,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mac_address (mac_address),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- NAT rules mapping
CREATE TABLE IF NOT EXISTS dynamic_nat_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    ip_address VARCHAR(15) NOT NULL,
    user_role ENUM('guest', 'user', 'admin') NOT NULL,
    rule_id VARCHAR(50), -- Mikrotik rule ID
    rule_type ENUM('masquerade', 'redirect', 'dst-nat') NOT NULL,
    rule_data JSON, -- Rule configuration
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    removed_at TIMESTAMP NULL,
    INDEX idx_mac_address (mac_address),
    INDEX idx_rule_id (rule_id),
    INDEX idx_rule_type (rule_type)
);

-- Bridge filter rules mapping
CREATE TABLE IF NOT EXISTS dynamic_bridge_filters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    ip_address VARCHAR(15) NOT NULL,
    user_role ENUM('guest', 'user', 'admin') NOT NULL,
    filter_id VARCHAR(50), -- Mikrotik filter ID
    filter_type ENUM('accept', 'drop', 'redirect') NOT NULL,
    filter_data JSON, -- Filter configuration
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    removed_at TIMESTAMP NULL,
    INDEX idx_mac_address (mac_address),
    INDEX idx_filter_id (filter_id),
    INDEX idx_filter_type (filter_type)
);

-- Bandwidth usage tracking
CREATE TABLE IF NOT EXISTS dynamic_bandwidth_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    ip_address VARCHAR(15) NOT NULL,
    username VARCHAR(100),
    bytes_in BIGINT DEFAULT 0,
    bytes_out BIGINT DEFAULT 0,
    session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_end TIMESTAMP NULL,
    INDEX idx_mac_address (mac_address),
    INDEX idx_session_start (session_start)
);

-- Network policies
CREATE TABLE IF NOT EXISTS dynamic_network_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_name VARCHAR(100) NOT NULL UNIQUE,
    user_role ENUM('guest', 'user', 'admin') NOT NULL,
    bandwidth_limit INT DEFAULT 0, -- Mbps
    session_timeout INT DEFAULT 3600, -- seconds
    allowed_domains JSON,
    nat_rules JSON,
    bridge_filters JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_role (user_role),
    INDEX idx_is_active (is_active)
);

-- Insert default policies
INSERT INTO dynamic_network_policies (policy_name, user_role, bandwidth_limit, session_timeout, allowed_domains, nat_rules, bridge_filters) VALUES
('Guest Policy', 'guest', 5, 3600, 
 '["google.com","gmail.com","facebook.com","twitter.com"]',
 '[{"action":"masquerade","comment":"Basic internet access"},{"action":"redirect","dst-port":"80","protocol":"tcp","to-addresses":"192.168.100.1","to-ports":"8080","comment":"Redirect HTTP to captive portal"}]',
 '[{"action":"accept","dst-address":"192.168.100.1","comment":"Allow access to gateway"},{"action":"accept","dst-address":"8.8.8.8","protocol":"udp","dst-port":"53","comment":"Allow DNS"},{"action":"drop","comment":"Block all other traffic"}]'),
 
('User Policy', 'user', 10, 7200,
 '["google.com","gmail.com","yahoo.com","hotmail.com","outlook.com","facebook.com","twitter.com","linkedin.com"]',
 '[{"action":"masquerade","comment":"Full internet access"}]',
 '[{"action":"accept","comment":"Allow all traffic"}]'),
 
('Admin Policy', 'admin', 100, 86400,
 '["*"]',
 '[{"action":"masquerade","comment":"Unrestricted access"}]',
 '[{"action":"accept","comment":"Unrestricted access"}]');

-- Network configuration
CREATE TABLE IF NOT EXISTS dynamic_network_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    config_type ENUM('string', 'integer', 'boolean', 'json', 'ip') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default configuration
INSERT INTO dynamic_network_config (config_key, config_value, config_type, description) VALUES
('dhcp_pool_start', '192.168.100.100', 'ip', 'DHCP pool start address'),
('dhcp_pool_end', '192.168.100.200', 'ip', 'DHCP pool end address'),
('gateway', '192.168.100.1', 'ip', 'Network gateway address'),
('dns_servers', '["8.8.8.8","8.8.4.4"]', 'json', 'DNS servers'),
('lease_time', '3600', 'integer', 'Default lease time in seconds'),
('nat_chain', 'captive_portal_nat', 'string', 'NAT chain name'),
('bridge_name', 'bridge1', 'string', 'Bridge interface name'),
('vlan_interface', 'vlan100', 'string', 'VLAN interface name'),
('mikrotik_host', '192.168.1.1', 'ip', 'Mikrotik router IP'),
('mikrotik_port', '8728', 'integer', 'Mikrotik API port'),
('enable_bandwidth_monitoring', 'true', 'boolean', 'Enable bandwidth usage tracking'),
('enable_session_logging', 'true', 'boolean', 'Enable detailed session logging'),
('auto_cleanup_interval', '300', 'integer', 'Auto cleanup interval in seconds');

-- Create views for easier querying
CREATE VIEW v_active_connections AS
SELECT 
    dl.id,
    dl.mac_address,
    dl.ip_address,
    dl.username,
    dl.user_role,
    dl.lease_id,
    dl.created_at,
    dl.expires_at,
    dl.last_activity,
    dl.bandwidth_limit,
    dl.allowed_domains,
    TIMESTAMPDIFF(SECOND, NOW(), dl.expires_at) as seconds_remaining,
    TIMESTAMPDIFF(MINUTE, dl.created_at, NOW()) as session_duration_minutes
FROM dynamic_leases dl
WHERE dl.status = 'active' AND dl.expires_at > NOW();

CREATE VIEW v_connection_statistics AS
SELECT 
    dl.user_role,
    COUNT(*) as total_connections,
    COUNT(CASE WHEN dl.expires_at > NOW() THEN 1 END) as active_connections,
    COUNT(CASE WHEN dl.expires_at <= NOW() THEN 1 END) as expired_connections,
    AVG(TIMESTAMPDIFF(MINUTE, dl.created_at, COALESCE(dl.expired_at, NOW()))) as avg_session_duration,
    SUM(dl.bandwidth_limit) as total_bandwidth_limit
FROM dynamic_leases dl
GROUP BY dl.user_role;

-- Create stored procedures
DELIMITER //

CREATE PROCEDURE sp_cleanup_expired_leases()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE lease_id_var VARCHAR(50);
    DECLARE mac_address_var VARCHAR(17);
    DECLARE cur CURSOR FOR 
        SELECT dl.lease_id, dl.mac_address 
        FROM dynamic_leases dl 
        WHERE dl.expires_at <= NOW() AND dl.status = 'active';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO lease_id_var, mac_address_var;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Mark as expired
        UPDATE dynamic_leases 
        SET status = 'expired', expired_at = NOW() 
        WHERE lease_id = lease_id_var;
        
        -- Log the expiry
        INSERT INTO dynamic_connection_logs (mac_address, ip_address, action, details)
        SELECT mac_address, ip_address, 'expiry', 
               JSON_OBJECT('lease_id', lease_id_var, 'reason', 'automatic_expiry')
        FROM dynamic_leases 
        WHERE lease_id = lease_id_var;
        
    END LOOP;
    
    CLOSE cur;
END //

CREATE PROCEDURE sp_get_user_connections(IN p_username VARCHAR(100))
BEGIN
    SELECT 
        dl.*,
        dcl.action as last_action,
        dcl.created_at as last_action_time
    FROM dynamic_leases dl
    LEFT JOIN dynamic_connection_logs dcl ON dl.mac_address = dcl.mac_address
    WHERE dl.username = p_username
    ORDER BY dl.created_at DESC;
END //

CREATE PROCEDURE sp_revoke_user_access(IN p_mac_address VARCHAR(17))
BEGIN
    UPDATE dynamic_leases 
    SET status = 'revoked', expired_at = NOW() 
    WHERE mac_address = p_mac_address AND status = 'active';
    
    INSERT INTO dynamic_connection_logs (mac_address, ip_address, action, details)
    SELECT mac_address, ip_address, 'revocation', 
           JSON_OBJECT('reason', 'manual_revocation', 'revoked_by', USER())
    FROM dynamic_leases 
    WHERE mac_address = p_mac_address;
END //

DELIMITER ;

-- Create triggers
DELIMITER //

CREATE TRIGGER tr_lease_activity_update
BEFORE UPDATE ON dynamic_leases
FOR EACH ROW
BEGIN
    SET NEW.last_activity = NOW();
END //

CREATE TRIGGER tr_connection_log_insert
AFTER INSERT ON dynamic_connection_logs
FOR EACH ROW
BEGIN
    -- Update bandwidth usage if it's a connection
    IF NEW.action = 'connection' THEN
        INSERT INTO dynamic_bandwidth_usage (mac_address, ip_address, username, session_start)
        VALUES (NEW.mac_address, NEW.ip_address, NEW.username, NEW.created_at)
        ON DUPLICATE KEY UPDATE session_start = NEW.created_at;
    END IF;
    
    -- End bandwidth session if it's a disconnection
    IF NEW.action IN ('disconnection', 'expiry', 'revocation') THEN
        UPDATE dynamic_bandwidth_usage 
        SET session_end = NEW.created_at
        WHERE mac_address = NEW.mac_address AND session_end IS NULL;
    END IF;
END //

DELIMITER ;

-- Create indexes for better performance
CREATE INDEX idx_leases_mac_status ON dynamic_leases(mac_address, status);
CREATE INDEX idx_leases_expires_status ON dynamic_leases(expires_at, status);
CREATE INDEX idx_logs_mac_action ON dynamic_connection_logs(mac_address, action);
CREATE INDEX idx_logs_created_action ON dynamic_connection_logs(created_at, action);
CREATE INDEX idx_nat_rules_mac_active ON dynamic_nat_rules(mac_address, removed_at);
CREATE INDEX idx_bridge_filters_mac_active ON dynamic_bridge_filters(mac_address, removed_at);
CREATE INDEX idx_bandwidth_mac_session ON dynamic_bandwidth_usage(mac_address, session_start); 