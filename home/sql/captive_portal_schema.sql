-- Captive Portal Database Schema
-- For VLAN-based walled garden portal system

-- VLANs table
CREATE TABLE IF NOT EXISTS vlans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vlan_id INT NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    network_address VARCHAR(50) NOT NULL,
    gateway VARCHAR(15) NOT NULL,
    captive_portal_enabled BOOLEAN DEFAULT FALSE,
    captive_portal_url VARCHAR(255),
    walled_garden_domains JSON,
    session_timeout INT DEFAULT 3600, -- seconds
    max_bandwidth INT DEFAULT 10, -- Mbps
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vlan_id (vlan_id),
    INDEX idx_status (status)
);

-- Captive portal sessions
CREATE TABLE IF NOT EXISTS captive_portal_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vlan_id INT NOT NULL,
    mac_address VARCHAR(17) NOT NULL,
    ip_address VARCHAR(15) NOT NULL,
    username VARCHAR(100),
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    bytes_in BIGINT DEFAULT 0,
    bytes_out BIGINT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'disconnected', 'expired', 'blocked') DEFAULT 'active',
    FOREIGN KEY (vlan_id) REFERENCES vlans(id) ON DELETE CASCADE,
    INDEX idx_vlan_id (vlan_id),
    INDEX idx_mac_address (mac_address),
    INDEX idx_ip_address (ip_address),
    INDEX idx_active (active),
    INDEX idx_login_time (login_time)
);

-- Captive portal users
CREATE TABLE IF NOT EXISTS captive_portal_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    full_name VARCHAR(200),
    role ENUM('admin', 'user', 'guest') DEFAULT 'guest',
    vlan_id INT,
    max_bandwidth INT DEFAULT 10, -- Mbps
    session_timeout INT DEFAULT 3600, -- seconds
    allowed_domains JSON,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vlan_id) REFERENCES vlans(id) ON DELETE SET NULL,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- Captive portal access logs
CREATE TABLE IF NOT EXISTS captive_portal_access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vlan_id INT NOT NULL,
    session_id INT,
    mac_address VARCHAR(17) NOT NULL,
    ip_address VARCHAR(15) NOT NULL,
    username VARCHAR(100),
    action ENUM('login', 'logout', 'block', 'timeout', 'bandwidth_exceeded') NOT NULL,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vlan_id) REFERENCES vlans(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES captive_portal_sessions(id) ON DELETE SET NULL,
    INDEX idx_vlan_id (vlan_id),
    INDEX idx_session_id (session_id),
    INDEX idx_mac_address (mac_address),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Captive portal settings
CREATE TABLE IF NOT EXISTS captive_portal_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO captive_portal_settings (setting_key, setting_value, setting_type, description) VALUES
('portal_title', 'Welcome to Our Network', 'string', 'Main title displayed on captive portal'),
('portal_subtitle', 'Please login to access the internet', 'string', 'Subtitle displayed on captive portal'),
('company_name', 'Your ISP Name', 'string', 'Company name for branding'),
('logo_url', '/assets/images/logo.png', 'string', 'URL to company logo'),
('default_session_timeout', '3600', 'integer', 'Default session timeout in seconds'),
('max_login_attempts', '3', 'integer', 'Maximum login attempts before lockout'),
('lockout_time', '900', 'integer', 'Lockout time in seconds after max attempts'),
('default_walled_garden_domains', '["google.com","gmail.com","yahoo.com","hotmail.com","outlook.com","facebook.com","twitter.com","linkedin.com","github.com","stackoverflow.com"]', 'json', 'Default allowed domains without authentication'),
('enable_social_login', 'false', 'boolean', 'Enable social media login options'),
('enable_guest_access', 'true', 'boolean', 'Enable guest access without registration'),
('enable_bandwidth_monitoring', 'true', 'boolean', 'Enable bandwidth usage monitoring'),
('enable_session_logging', 'true', 'boolean', 'Enable detailed session logging');

-- Insert sample VLANs
INSERT INTO vlans (vlan_id, name, description, network_address, gateway, captive_portal_enabled, captive_portal_url, walled_garden_domains, session_timeout, max_bandwidth) VALUES
(100, 'Guest Network', 'Public guest network with captive portal', '192.168.100.0/24', '192.168.100.1', TRUE, '/modules/captive_portal.php', '["google.com","gmail.com","facebook.com","twitter.com"]', 3600, 5),
(200, 'Hotel Network', 'Hotel guest network with premium access', '192.168.200.0/24', '192.168.200.1', TRUE, '/modules/captive_portal.php', '["google.com","gmail.com","yahoo.com","hotmail.com","outlook.com","facebook.com","twitter.com","linkedin.com"]', 7200, 10),
(300, 'Office Network', 'Internal office network', '192.168.300.0/24', '192.168.300.1', FALSE, NULL, NULL, 86400, 100);

-- Insert sample users
INSERT INTO captive_portal_users (username, password_hash, email, full_name, role, vlan_id, max_bandwidth, allowed_domains) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'System Administrator', 'admin', NULL, 100, '["*"]'),
('guest', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guest@example.com', 'Guest User', 'guest', 100, 5, '["google.com","gmail.com","facebook.com"]'),
('user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user@example.com', 'Regular User', 'user', 200, 10, '["google.com","gmail.com","yahoo.com","hotmail.com","outlook.com","facebook.com","twitter.com","linkedin.com"]');

-- Create views for easier querying
CREATE VIEW v_active_sessions AS
SELECT 
    cs.id,
    cs.vlan_id,
    v.name as vlan_name,
    cs.mac_address,
    cs.ip_address,
    cs.username,
    cs.login_time,
    cs.last_activity,
    cs.bytes_in,
    cs.bytes_out,
    cs.status,
    TIMESTAMPDIFF(MINUTE, cs.login_time, NOW()) as session_duration_minutes
FROM captive_portal_sessions cs
JOIN vlans v ON cs.vlan_id = v.id
WHERE cs.active = TRUE;

CREATE VIEW v_vlan_statistics AS
SELECT 
    v.id,
    v.vlan_id,
    v.name,
    v.network_address,
    v.captive_portal_enabled,
    COUNT(cs.id) as total_sessions,
    COUNT(CASE WHEN cs.active = 1 THEN 1 END) as active_sessions,
    SUM(cs.bytes_in) as total_bytes_in,
    SUM(cs.bytes_out) as total_bytes_out,
    AVG(TIMESTAMPDIFF(MINUTE, cs.login_time, COALESCE(cs.logout_time, NOW()))) as avg_session_duration
FROM vlans v
LEFT JOIN captive_portal_sessions cs ON v.id = cs.vlan_id
GROUP BY v.id;

-- Create stored procedures for common operations
DELIMITER //

CREATE PROCEDURE sp_cleanup_expired_sessions()
BEGIN
    UPDATE captive_portal_sessions cs
    JOIN vlans v ON cs.vlan_id = v.id
    SET cs.active = FALSE, cs.status = 'expired', cs.logout_time = NOW()
    WHERE cs.active = TRUE 
    AND TIMESTAMPDIFF(SECOND, cs.login_time, NOW()) > v.session_timeout;
END //

CREATE PROCEDURE sp_get_user_sessions(IN p_username VARCHAR(100))
BEGIN
    SELECT 
        cs.*,
        v.name as vlan_name,
        v.network_address
    FROM captive_portal_sessions cs
    JOIN vlans v ON cs.vlan_id = v.id
    WHERE cs.username = p_username
    ORDER BY cs.login_time DESC;
END //

CREATE PROCEDURE sp_disconnect_user(IN p_username VARCHAR(100), IN p_vlan_id INT)
BEGIN
    UPDATE captive_portal_sessions 
    SET active = FALSE, status = 'disconnected', logout_time = NOW()
    WHERE username = p_username AND vlan_id = p_vlan_id AND active = TRUE;
END //

DELIMITER ;

-- Create triggers for automatic updates
DELIMITER //

CREATE TRIGGER tr_session_activity_update
BEFORE UPDATE ON captive_portal_sessions
FOR EACH ROW
BEGIN
    SET NEW.last_activity = NOW();
END //

CREATE TRIGGER tr_access_log_insert
AFTER INSERT ON captive_portal_sessions
FOR EACH ROW
BEGIN
    INSERT INTO captive_portal_access_logs (vlan_id, session_id, mac_address, ip_address, username, action, details)
    VALUES (NEW.vlan_id, NEW.id, NEW.mac_address, NEW.ip_address, NEW.username, 'login', 
            JSON_OBJECT('login_time', NEW.login_time, 'session_timeout', 
                       (SELECT session_timeout FROM vlans WHERE id = NEW.vlan_id)));
END //

DELIMITER ;

-- Create indexes for better performance
CREATE INDEX idx_sessions_vlan_active ON captive_portal_sessions(vlan_id, active);
CREATE INDEX idx_sessions_username_active ON captive_portal_sessions(username, active);
CREATE INDEX idx_access_logs_vlan_action ON captive_portal_access_logs(vlan_id, action);
CREATE INDEX idx_access_logs_created_at ON captive_portal_access_logs(created_at);
CREATE INDEX idx_users_username_status ON captive_portal_users(username, status); 