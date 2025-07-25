-- USERS & AUTH
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'manager', 'user', 'viewer') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    access_level_id INT DEFAULT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS access_levels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS access_level_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    access_level_id INT NOT NULL,
    section VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    FOREIGN KEY (access_level_id) REFERENCES access_levels(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100),
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- CLIENTS
CREATE TABLE IF NOT EXISTS clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    altname VARCHAR(100),
    address TEXT,
    post_name VARCHAR(100),
    post_address TEXT,
    location_name VARCHAR(100),
    location_address TEXT,
    email VARCHAR(100),
    bankaccount VARCHAR(50),
    ten VARCHAR(20),
    ssn VARCHAR(20),
    additional_info TEXT,
    notes TEXT,
    documentmemo TEXT,
    contact_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- DEVICES
CREATE TABLE IF NOT EXISTS devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50),
    ip_address VARCHAR(45),
    mac_address VARCHAR(20),
    location VARCHAR(100),
    client_id INT,
    network_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ip (ip_address),
    UNIQUE KEY unique_mac (mac_address),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE SET NULL
);

-- SKELETON DEVICES
CREATE TABLE IF NOT EXISTS skeleton_devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50),
    ip_address VARCHAR(45),
    mac_address VARCHAR(20),
    location VARCHAR(100),
    model VARCHAR(100),
    manufacturer VARCHAR(100),
    status ENUM('active','inactive','maintenance') DEFAULT 'active',
    description TEXT,
    api_username VARCHAR(50),
    api_password VARCHAR(100),
    api_port INT DEFAULT 8728,
    api_ssl BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ip (ip_address),
    UNIQUE KEY unique_mac (mac_address)
);

-- NETWORKS
CREATE TABLE IF NOT EXISTS networks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    subnet VARCHAR(50),
    description TEXT,
    device_id INT,
    device_interface VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES skeleton_devices(id) ON DELETE SET NULL
);

-- INTERNET PACKAGES
CREATE TABLE IF NOT EXISTS internet_packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    internet_package TEXT,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TV PACKAGES
CREATE TABLE IF NOT EXISTS tv_packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    tv_package TEXT,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TARIFFS
CREATE TABLE IF NOT EXISTS tariffs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    upload_speed INT NOT NULL,
    download_speed INT NOT NULL,
    tv_included BOOLEAN DEFAULT 0,
    internet_included BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SERVICES
CREATE TABLE IF NOT EXISTS services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    service_type ENUM('internet','tv') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- INVOICES
CREATE TABLE IF NOT EXISTS invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    date_issued DATE NOT NULL,
    due_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('unpaid','paid') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- PAYMENTS
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- INTERFACE STATS (for monitoring/graphing)
CREATE TABLE IF NOT EXISTS interface_stats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    device_id INT NOT NULL,
    interface_name VARCHAR(100) NOT NULL,
    rx_bytes BIGINT DEFAULT 0,
    tx_bytes BIGINT DEFAULT 0,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (device_id) REFERENCES skeleton_devices(id) ON DELETE CASCADE
);

-- MENU ITEMS (for dynamic menu)
CREATE TABLE IF NOT EXISTS menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    label VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(50),
    type VARCHAR(20) DEFAULT 'link',
    position INT DEFAULT 0,
    enabled BOOLEAN DEFAULT 1,
    parent_id INT DEFAULT NULL,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE SET NULL
);

-- BRIDGE NAT/MANGLE (for bridge_nat_controller.php)
CREATE TABLE IF NOT EXISTS bridge_access (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mac_address VARCHAR(20) NOT NULL,
    username VARCHAR(50),
    user_role VARCHAR(20),
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity DATETIME,
    status ENUM('active','expired') DEFAULT 'active',
    expired_at DATETIME
);

CREATE TABLE IF NOT EXISTS bridge_filter_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mac_address VARCHAR(20) NOT NULL,
    action VARCHAR(20),
    rule_data TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    removed_at DATETIME
);

CREATE TABLE IF NOT EXISTS bridge_nat_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mac_address VARCHAR(20) NOT NULL,
    action VARCHAR(20),
    rule_data TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    removed_at DATETIME
);

CREATE TABLE IF NOT EXISTS bridge_mangle_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mac_address VARCHAR(20) NOT NULL,
    action VARCHAR(20),
    rule_data TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    removed_at DATETIME
);

CREATE TABLE IF NOT EXISTS bridge_connection_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mac_address VARCHAR(20),
    username VARCHAR(50),
    user_role VARCHAR(20),
    action VARCHAR(50),
    details TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- CAPTIVE PORTAL USERS (for captive portal auth)
CREATE TABLE IF NOT EXISTS captive_portal_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','user','guest') DEFAULT 'user',
    max_bandwidth INT DEFAULT NULL,
    allowed_domains TEXT,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- COLUMN CONFIG (for table column customization)
CREATE TABLE IF NOT EXISTS column_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_name VARCHAR(50) NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_label VARCHAR(255) NOT NULL,
    field_type VARCHAR(50) NOT NULL DEFAULT 'text',
    is_visible TINYINT(1) DEFAULT 1,
    is_searchable TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    options TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_name (module_name),
    INDEX idx_sort_order (sort_order),
    UNIQUE KEY unique_module_field (module_name, field_name)
);

-- LAYOUT SETTINGS (for theme/layout customization)
CREATE TABLE IF NOT EXISTS layout_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
);

-- Add any additional tables as needed for your custom modules
