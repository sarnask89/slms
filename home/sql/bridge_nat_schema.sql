-- Bridge NAT/Mangle Controller Database Schema
-- Manages traffic control through bridged interfaces without DHCP

-- Bridge access table
CREATE TABLE IF NOT EXISTS bridge_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    username VARCHAR(100),
    user_role ENUM('guest', 'user', 'admin') DEFAULT 'guest',
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    expired_at TIMESTAMP NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    bandwidth_limit INT DEFAULT 0, -- Mbps, 0 = unlimited
    allowed_domains JSON,
    INDEX idx_mac_address (mac_address),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at),
    UNIQUE KEY unique_active_mac (mac_address, status)
);

-- Bridge connection logs
CREATE TABLE IF NOT EXISTS bridge_connection_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    username VARCHAR(100),
    user_role ENUM('guest', 'user', 'admin'),
    action ENUM('connection', 'authentication', 'disconnection', 'expiry', 'revocation') NOT NULL,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mac_address (mac_address),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Bridge filter rules mapping
CREATE TABLE IF NOT EXISTS bridge_filter_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    action ENUM('accept', 'drop', 'redirect') NOT NULL,
    rule_data JSON, -- Rule configuration
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    removed_at TIMESTAMP NULL,
    INDEX idx_mac_address (mac_address),
    INDEX idx_action (action),
    INDEX idx_removed_at (removed_at)
);

-- Bridge NAT rules mapping
CREATE TABLE IF NOT EXISTS bridge_nat_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    action ENUM('masquerade', 'redirect', 'dst-nat') NOT NULL,
    rule_data JSON, -- Rule configuration
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    removed_at TIMESTAMP NULL,
    INDEX idx_mac_address (mac_address),
    INDEX idx_action (action),
    INDEX idx_removed_at (removed_at)
);

-- Bridge mangle rules mapping
CREATE TABLE IF NOT EXISTS bridge_mangle_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    action ENUM('mark-connection', 'mark-packet', 'mark-routing') NOT NULL,
    rule_data JSON, -- Rule configuration
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    removed_at TIMESTAMP NULL,
    INDEX idx_mac_address (mac_address),
    INDEX idx_action (action),
    INDEX idx_removed_at (removed_at)
);

-- Bridge bandwidth usage tracking
CREATE TABLE IF NOT EXISTS bridge_bandwidth_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    username VARCHAR(100),
    bytes_in BIGINT DEFAULT 0,
    bytes_out BIGINT DEFAULT 0,
    session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_end TIMESTAMP NULL,
    INDEX idx_mac_address (mac_address),
    INDEX idx_session_start (session_start)
);

-- Bridge network policies
CREATE TABLE IF NOT EXISTS bridge_network_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_name VARCHAR(100) NOT NULL UNIQUE,
    user_role ENUM('guest', 'user', 'admin') NOT NULL,
    bandwidth_limit INT DEFAULT 0, -- Mbps
    session_timeout INT DEFAULT 3600, -- seconds
    allowed_domains JSON,
    bridge_filters JSON,
    nat_rules JSON,
    mangle_rules JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_role (user_role),
    INDEX idx_is_active (is_active)
);

-- Insert default bridge policies
INSERT INTO bridge_network_policies (policy_name, user_role, bandwidth_limit, session_timeout, allowed_domains, bridge_filters, nat_rules, mangle_rules) VALUES
('Guest Bridge Policy', 'guest', 5, 3600, 
 '["google.com","gmail.com","facebook.com","twitter.com"]',
 '[{"action":"accept","dst-address":"192.168.100.1","comment":"Allow access to gateway"},{"action":"accept","dst-address":"8.8.8.8","protocol":"udp","dst-port":"53","comment":"Allow DNS"},{"action":"drop","comment":"Block all other traffic"}]',
 '[{"action":"masquerade","comment":"Basic internet access"},{"action":"redirect","dst-port":"80","protocol":"tcp","to-ports":"8080","comment":"Redirect HTTP to captive portal"}]',
 '[{"action":"mark-connection","new-connection-mark":"guest_connection","comment":"Mark guest connections"},{"action":"mark-packet","connection-mark":"guest_connection","new-packet-mark":"guest_packet","comment":"Mark guest packets"}]'),
 
('User Bridge Policy', 'user', 10, 7200,
 '["google.com","gmail.com","yahoo.com","hotmail.com","outlook.com","facebook.com","twitter.com","linkedin.com"]',
 '[{"action":"accept","comment":"Allow all traffic"}]',
 '[{"action":"masquerade","comment":"Full internet access"}]',
 '[{"action":"mark-connection","new-connection-mark":"user_connection","comment":"Mark user connections"},{"action":"mark-packet","connection-mark":"user_connection","new-packet-mark":"user_packet","comment":"Mark user packets"}]'),
 
('Admin Bridge Policy', 'admin', 100, 86400,
 '["*"]',
 '[{"action":"accept","comment":"Unrestricted access"}]',
 '[{"action":"masquerade","comment":"Unrestricted access"}]',
 '[{"action":"mark-connection","new-connection-mark":"admin_connection","comment":"Mark admin connections"},{"action":"mark-packet","connection-mark":"admin_connection","new-packet-mark":"admin_packet","comment":"Mark admin packets"}]');

-- Bridge configuration
CREATE TABLE IF NOT EXISTS bridge_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    config_type ENUM('string', 'integer', 'boolean', 'json', 'ip') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default bridge configuration
INSERT INTO bridge_config (config_key, config_value, config_type, description) VALUES
('bridge_name', 'bridge1', 'string', 'Bridge interface name'),
('interface1', 'eth0', 'string', 'First bridged interface'),
('interface2', 'eth1', 'string', 'Second bridged interface'),
('captive_portal_ip', '192.168.100.1', 'ip', 'Captive portal IP address'),
('captive_portal_port', '8080', 'integer', 'Captive portal port'),
('dns_servers', '["8.8.8.8","8.8.4.4"]', 'json', 'DNS servers'),
('nat_chain', 'bridge_nat', 'string', 'NAT chain name'),
('mangle_chain', 'bridge_mangle', 'string', 'Mangle chain name'),
('bridge_filter_chain', 'bridge_filter', 'string', 'Bridge filter chain name'),
('session_timeout', '3600', 'integer', 'Default session timeout in seconds'),
('enable_bandwidth_monitoring', 'true', 'boolean', 'Enable bandwidth usage tracking'),
('enable_connection_tracking', 'true', 'boolean', 'Enable connection tracking'),
('mikrotik_host', '192.168.1.1', 'ip', 'Mikrotik router IP'),
('mikrotik_port', '8728', 'integer', 'Mikrotik API port'),
('auto_cleanup_interval', '300', 'integer', 'Auto cleanup interval in seconds');

-- Create views for easier querying
CREATE VIEW v_active_bridge_connections AS
SELECT 
    ba.id,
    ba.mac_address,
    ba.username,
    ba.user_role,
    ba.created_at,
    ba.expires_at,
    ba.last_activity,
    ba.bandwidth_limit,
    ba.allowed_domains,
    TIMESTAMPDIFF(SECOND, NOW(), ba.expires_at) as seconds_remaining,
    TIMESTAMPDIFF(MINUTE, ba.created_at, NOW()) as session_duration_minutes
FROM bridge_access ba
WHERE ba.status = 'active' AND ba.expires_at > NOW();

CREATE VIEW v_bridge_connection_statistics AS
SELECT 
    ba.user_role,
    COUNT(*) as total_connections,
    COUNT(CASE WHEN ba.expires_at > NOW() THEN 1 END) as active_connections,
    COUNT(CASE WHEN ba.expires_at <= NOW() THEN 1 END) as expired_connections,
    AVG(TIMESTAMPDIFF(MINUTE, ba.created_at, COALESCE(ba.expired_at, NOW()))) as avg_session_duration,
    SUM(ba.bandwidth_limit) as total_bandwidth_limit
FROM bridge_access ba
GROUP BY ba.user_role;

-- Create stored procedures
DELIMITER //

CREATE PROCEDURE sp_cleanup_expired_bridge_access()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE access_id_var INT;
    DECLARE mac_address_var VARCHAR(17);
    DECLARE cur CURSOR FOR 
        SELECT ba.id, ba.mac_address 
        FROM bridge_access ba 
        WHERE ba.expires_at <= NOW() AND ba.status = 'active';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO access_id_var, mac_address_var;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Mark as expired
        UPDATE bridge_access 
        SET status = 'expired', expired_at = NOW() 
        WHERE id = access_id_var;
        
        -- Log the expiry
        INSERT INTO bridge_connection_logs (mac_address, action, details)
        VALUES (mac_address_var, 'expiry', 
               JSON_OBJECT('reason', 'automatic_expiry', 'access_id', access_id_var));
        
    END LOOP;
    
    CLOSE cur;
END //

CREATE PROCEDURE sp_get_user_bridge_connections(IN p_username VARCHAR(100))
BEGIN
    SELECT 
        ba.*,
        bcl.action as last_action,
        bcl.created_at as last_action_time
    FROM bridge_access ba
    LEFT JOIN bridge_connection_logs bcl ON ba.mac_address = bcl.mac_address
    WHERE ba.username = p_username
    ORDER BY ba.created_at DESC;
END //

CREATE PROCEDURE sp_revoke_user_bridge_access(IN p_mac_address VARCHAR(17))
BEGIN
    UPDATE bridge_access 
    SET status = 'revoked', expired_at = NOW() 
    WHERE mac_address = p_mac_address AND status = 'active';
    
    INSERT INTO bridge_connection_logs (mac_address, action, details)
    VALUES (p_mac_address, 'revocation', 
           JSON_OBJECT('reason', 'manual_revocation', 'revoked_by', USER()));
END //

DELIMITER ;

-- Create triggers
DELIMITER //

CREATE TRIGGER tr_bridge_access_activity_update
BEFORE UPDATE ON bridge_access
FOR EACH ROW
BEGIN
    SET NEW.last_activity = NOW();
END //

CREATE TRIGGER tr_bridge_connection_log_insert
AFTER INSERT ON bridge_connection_logs
FOR EACH ROW
BEGIN
    -- Update bandwidth usage if it's a connection
    IF NEW.action = 'connection' THEN
        INSERT INTO bridge_bandwidth_usage (mac_address, username, session_start)
        VALUES (NEW.mac_address, NEW.username, NEW.created_at)
        ON DUPLICATE KEY UPDATE session_start = NEW.created_at;
    END IF;
    
    -- End bandwidth session if it's a disconnection
    IF NEW.action IN ('disconnection', 'expiry', 'revocation') THEN
        UPDATE bridge_bandwidth_usage 
        SET session_end = NEW.created_at
        WHERE mac_address = NEW.mac_address AND session_end IS NULL;
    END IF;
END //

DELIMITER ;

-- Create indexes for better performance
CREATE INDEX idx_bridge_access_mac_status ON bridge_access(mac_address, status);
CREATE INDEX idx_bridge_access_expires_status ON bridge_access(expires_at, status);
CREATE INDEX idx_bridge_logs_mac_action ON bridge_connection_logs(mac_address, action);
CREATE INDEX idx_bridge_logs_created_action ON bridge_connection_logs(created_at, action);
CREATE INDEX idx_bridge_filters_mac_active ON bridge_filter_rules(mac_address, removed_at);
CREATE INDEX idx_bridge_nat_mac_active ON bridge_nat_rules(mac_address, removed_at);
CREATE INDEX idx_bridge_mangle_mac_active ON bridge_mangle_rules(mac_address, removed_at);
CREATE INDEX idx_bridge_bandwidth_mac_session ON bridge_bandwidth_usage(mac_address, session_start); 