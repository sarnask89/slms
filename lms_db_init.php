<?php
// slms_db_init.php
// Script to initialize sLMS database with tables and sample data

require_once __DIR__ . '/config.php';

function prompt($prompt_text) {
    if (php_sapi_name() === 'cli') {
        echo $prompt_text;
        return trim(fgets(STDIN));
    }
    return null;
}

// Default credentials (for web server usage)
$default = [
    'host' => $db_host,
    'db' => $db_name,
    'user' => $db_user,
    'pass' => $db_pass,
    'charset' => $db_charset,
];

if (php_sapi_name() === 'cli') {
    echo "--- sLMS Database Initialization Script ---\n";
    $host = prompt("MySQL Host [{$default['host']}]: ") ?: $default['host'];
    $db   = prompt("Database Name [{$default['db']}]: ") ?: $default['db'];
    $user = prompt("Username [{$default['user']}]: ") ?: $default['user'];
    $pass = prompt("Password [{$default['pass']}]: ") ?: $default['pass'];
    $charset = $default['charset'];
    // Override global vars for get_pdo
    $GLOBALS['db_host'] = $host;
    $GLOBALS['db_name'] = $db;
    $GLOBALS['db_user'] = $user;
    $GLOBALS['db_pass'] = $pass;
    $GLOBALS['db_charset'] = $charset;
}

try {
    $pdo = get_pdo();
    echo "\nConnected to database successfully!\n";

    // Drop tables in reverse dependency order
    $drop = "
    SET FOREIGN_KEY_CHECKS = 0;
    DROP TABLE IF EXISTS payments;
    DROP TABLE IF EXISTS invoice_items;
    DROP TABLE IF EXISTS invoices;
    DROP TABLE IF EXISTS devices;
    DROP TABLE IF EXISTS networks;
    DROP TABLE IF EXISTS vlans;
    DROP TABLE IF EXISTS clients;
    DROP TABLE IF EXISTS users;
    DROP TABLE IF EXISTS menu_items;
    DROP TABLE IF EXISTS layout_settings;
    DROP TABLE IF EXISTS column_config;
    DROP TABLE IF EXISTS streets;
    DROP TABLE IF EXISTS cities;
    SET FOREIGN_KEY_CHECKS = 1;
    ";
    foreach (array_filter(array_map('trim', explode(';', $drop))) as $stmt) {
        if ($stmt) {
            $pdo->exec($stmt);
        }
    }

    // SQL statements for tables and sample data
    $sql = "
    -- Layout Settings Table
    CREATE TABLE IF NOT EXISTS layout_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) UNIQUE NOT NULL,
        setting_value TEXT,
        setting_type ENUM('string', 'boolean', 'integer', 'json') DEFAULT 'string',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Column Configuration Table
    CREATE TABLE IF NOT EXISTS column_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        table_name VARCHAR(50) NOT NULL,
        column_name VARCHAR(50) NOT NULL,
        display_name VARCHAR(100) NOT NULL,
        visible BOOLEAN DEFAULT TRUE,
        order_position INT DEFAULT 0,
        width VARCHAR(20),
        sortable BOOLEAN DEFAULT TRUE,
        filterable BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_table_column (table_name, column_name)
    );

    -- Menu Items Table
    CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        label VARCHAR(100) NOT NULL,
        url VARCHAR(255),
        icon VARCHAR(50) DEFAULT 'bi-circle',
        type ENUM('link','script') DEFAULT 'link',
        script TEXT,
        parent_id INT DEFAULT NULL,
        position INT DEFAULT 0,
        enabled TINYINT(1) DEFAULT 1,
        options TEXT DEFAULT NULL,
        FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE
    );

    -- Users Table
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE,
        role VARCHAR(50) DEFAULT 'user',
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Clients Table
    CREATE TABLE IF NOT EXISTS clients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        pesel VARCHAR(11) UNIQUE,
        phone VARCHAR(20),
        email VARCHAR(100),
        address TEXT,
        city VARCHAR(100),
        postal_code VARCHAR(10),
        company_name VARCHAR(255),
        nip VARCHAR(10),
        notes TEXT,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Networks Table
    CREATE TABLE IF NOT EXISTS networks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        network_address VARCHAR(18) NOT NULL,
        gateway VARCHAR(15),
        dns_servers TEXT,
        dhcp_range_start VARCHAR(15),
        dhcp_range_end VARCHAR(15),
        vlan_id INT,
        description TEXT,
        device_interface VARCHAR(100),
        device_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- VLANs Table
    CREATE TABLE IF NOT EXISTS vlans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vlan_id INT UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        network_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE SET NULL
    );

    -- Skeleton Devices Table
    CREATE TABLE IF NOT EXISTS skeleton_devices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        type VARCHAR(100) NOT NULL,
        ip_address VARCHAR(45) UNIQUE,
        mac_address VARCHAR(17) UNIQUE,
        location VARCHAR(255),
        model VARCHAR(255),
        manufacturer VARCHAR(255),
        status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
        description TEXT,
        api_username VARCHAR(100),
        api_password VARCHAR(255),
        api_port INT DEFAULT 8728,
        api_ssl BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Devices Table
    CREATE TABLE IF NOT EXISTS devices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        type ENUM('router', 'switch', 'server', 'access_point', 'firewall', 'other') NOT NULL,
        model VARCHAR(100),
        ip_address VARCHAR(15),
        mac_address VARCHAR(17),
        location TEXT,
        client_id INT,
        network_id INT,
        status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline',
        last_seen TIMESTAMP NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
        FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE SET NULL
    );

    -- Services Table
    CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type ENUM('internet', 'tv', 'phone', 'other') NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        speed_download INT,
        speed_upload INT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Client Services Table (Many-to-Many relationship)
    CREATE TABLE IF NOT EXISTS client_services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        service_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE,
        status ENUM('active', 'suspended', 'cancelled') DEFAULT 'active',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    );

    -- Tariffs Table
    CREATE TABLE IF NOT EXISTS tariffs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        upload_speed INT NOT NULL,
        download_speed INT NOT NULL,
        tv_included BOOLEAN DEFAULT FALSE,
        internet_included BOOLEAN DEFAULT TRUE,
        price DECIMAL(10,2) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- TV Packages Table
    CREATE TABLE IF NOT EXISTS tv_packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        tv_package VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        channels_count INT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Internet Packages Table
    CREATE TABLE IF NOT EXISTS internet_packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        internet_package VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        speed_download INT,
        speed_upload INT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Invoices Table
    CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        invoice_number VARCHAR(50) UNIQUE NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
        issue_date DATE NOT NULL,
        due_date DATE NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
    );

    -- Invoice Items Table
    CREATE TABLE IF NOT EXISTS invoice_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT NOT NULL,
        description TEXT NOT NULL,
        quantity INT DEFAULT 1,
        unit_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
    );

    -- Payments Table
    CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_date DATE NOT NULL,
        payment_method ENUM('cash', 'transfer', 'card', 'other') NOT NULL,
        reference_number VARCHAR(50),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
    );

    -- Cities Table
    CREATE TABLE IF NOT EXISTS cities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        region VARCHAR(100),
        country VARCHAR(100) DEFAULT 'Poland',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Streets Table
    CREATE TABLE IF NOT EXISTS streets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        city_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
    );

    -- Insert Layout Settings
    INSERT IGNORE INTO layout_settings (setting_key, setting_value, setting_type) VALUES
    ('menu_position', 'left', 'string'),
    ('show_logo', '1', 'boolean'),
    ('primary_color', '#007bff', 'string'),
    ('secondary_color', '#6c757d', 'string'),
    ('font_family', 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif', 'string'),
    ('font_size', '14px', 'string'),
    ('custom_css', '', 'string'),
    ('footer_text', '© 2024 sLMS System. Wszystkie prawa zastrzeżone.', 'string'),
    ('auto_refresh_interval', '300000', 'integer'),
    ('enable_keyboard_shortcuts', '1', 'boolean');

    -- Insert Column Configuration
    INSERT IGNORE INTO column_config (table_name, column_name, display_name, visible, order_position, sortable, filterable) VALUES
    ('clients', 'id', 'ID', 1, 1, 1, 0),
    ('clients', 'first_name', 'Imię', 1, 2, 1, 1),
    ('clients', 'last_name', 'Nazwisko', 1, 3, 1, 1),
    ('clients', 'phone', 'Telefon', 1, 4, 0, 1),
    ('clients', 'email', 'Email', 1, 5, 0, 1),
    ('clients', 'status', 'Status', 1, 6, 1, 1),
    ('clients', 'created_at', 'Data dodania', 1, 7, 1, 0),
    ('devices', 'id', 'ID', 1, 1, 1, 0),
    ('devices', 'name', 'Nazwa', 1, 2, 1, 1),
    ('devices', 'type', 'Typ', 1, 3, 1, 1),
    ('devices', 'ip_address', 'Adres IP', 1, 4, 0, 1),
    ('devices', 'status', 'Status', 1, 5, 1, 1),
    ('devices', 'location', 'Lokalizacja', 1, 6, 0, 1),
    ('devices', 'last_seen', 'Ostatnio widziany', 1, 7, 1, 0),
    ('networks', 'id', 'ID', 1, 1, 1, 0),
    ('networks', 'name', 'Nazwa', 1, 2, 1, 1),
    ('networks', 'network_address', 'Adres sieci', 1, 3, 0, 1),
    ('networks', 'gateway', 'Brama', 1, 4, 0, 1),
    ('networks', 'vlan_id', 'VLAN ID', 1, 5, 1, 1),
    ('networks', 'description', 'Opis', 1, 6, 0, 1);

    -- Insert Menu Items
    INSERT IGNORE INTO menu_items (label, url, icon, parent_id, position, enabled) VALUES
    ('Panel główny', 'index.php', 'bi-house', NULL, 1, 1),
    ('Klienci', 'modules/clients.php', 'bi-people', NULL, 2, 1),
    ('Urządzenia', 'modules/devices.php', 'bi-pc-display', NULL, 3, 1),
    ('Urządzenia szkieletowe', 'modules/skeleton_devices.php', 'bi-hdd-network', NULL, 4, 1),
    ('Sieci', 'modules/networks.php', 'bi-diagram-3', NULL, 5, 1),
    ('Usługi', 'modules/services.php', 'bi-gear', NULL, 6, 1),
    ('Taryfy', 'modules/tariffs.php', 'bi-currency-dollar', NULL, 7, 1),
    ('Telewizja', 'modules/tv_packages.php', 'bi-tv', NULL, 8, 1),
    ('Internet', 'modules/internet_packages.php', 'bi-wifi', NULL, 9, 1),
    ('Faktury', 'modules/invoices.php', 'bi-receipt', NULL, 10, 1),
    ('Płatności', 'modules/payments.php', 'bi-credit-card', NULL, 11, 1),
    ('Użytkownicy', 'modules/users.php', 'bi-person-badge', NULL, 12, 1),
    ('Podręcznik', 'modules/manual.php', 'bi-book', NULL, 13, 1),
    ('Administracja', 'admin_menu.php', 'bi-tools', NULL, 99, 1);

    -- Insert Sample Users
    INSERT IGNORE INTO users (username, password_hash, email, role, first_name, last_name) VALUES
    ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@slms.local', 'admin', 'Administrator', 'Systemu'),
    ('user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user@slms.local', 'user', 'Użytkownik', 'Testowy'),
    ('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager@slms.local', 'manager', 'Kierownik', 'Działu');

    -- Insert Sample Clients
    INSERT IGNORE INTO clients (first_name, last_name, phone, email, address, city, postal_code, company_name, status) VALUES
    ('Jan', 'Kowalski', '+48 123 456 789', 'jan.kowalski@example.com', 'ul. Kwiatowa 1', 'Warszawa', '00-001', 'Kowalski Sp. z o.o.', 'active'),
    ('Anna', 'Nowak', '+48 987 654 321', 'anna.nowak@example.com', 'ul. Słoneczna 15', 'Kraków', '30-001', 'Nowak Consulting', 'active'),
    ('Piotr', 'Wiśniewski', '+48 555 123 456', 'piotr.wisniewski@example.com', 'ul. Długa 42', 'Gdańsk', '80-001', 'Wiśniewski IT', 'active'),
    ('Maria', 'Wójcik', '+48 777 888 999', 'maria.wojcik@example.com', 'ul. Krótka 7', 'Wrocław', '50-001', 'Wójcik Solutions', 'active');

    -- Insert Sample Networks
    INSERT IGNORE INTO networks (name, network_address, gateway, dns_servers, dhcp_range_start, dhcp_range_end, description) VALUES
    ('Sieć główna', '192.168.1.0/24', '192.168.1.1', '8.8.8.8,8.8.4.4', '192.168.1.100', '192.168.1.200', 'Główna sieć biurowa'),
    ('Sieć gości', '192.168.2.0/24', '192.168.2.1', '8.8.8.8,8.8.4.4', '192.168.2.100', '192.168.2.200', 'Sieć dla gości'),
    ('Sieć IoT', '192.168.3.0/24', '192.168.3.1', '8.8.8.8,8.8.4.4', '192.168.3.100', '192.168.3.200', 'Sieć dla urządzeń IoT'),
    ('Sieć zarządzania', '10.0.0.0/24', '10.0.0.1', '8.8.8.8,8.8.4.4', '10.0.0.100', '10.0.0.200', 'Sieć zarządzania urządzeniami');

    -- Insert Sample VLANs
    INSERT IGNORE INTO vlans (vlan_id, name, description) VALUES
    (10, 'VLAN Główna', 'Główna sieć VLAN'),
    (20, 'VLAN Goście', 'Sieć VLAN dla gości'),
    (30, 'VLAN IoT', 'Sieć VLAN dla urządzeń IoT'),
    (40, 'VLAN Zarządzanie', 'Sieć VLAN zarządzania');

    -- Insert Sample Skeleton Devices
    INSERT IGNORE INTO skeleton_devices (name, type, ip_address, mac_address, location, model, manufacturer, status, description) VALUES
    ('Router Główny', 'router', '192.168.1.1', '00:11:22:33:44:01', 'Serwerownia', 'RB4011', 'MikroTik', 'active', 'Główny router szkieletowy'),
    ('Switch Dystrybucyjny 1', 'switch', '192.168.1.2', '00:11:22:33:44:02', 'Piętro 1', 'CRS326', 'MikroTik', 'active', 'Przełącznik dystrybucyjny piętro 1'),
    ('Switch Dystrybucyjny 2', 'switch', '192.168.1.3', '00:11:22:33:44:03', 'Piętro 2', 'CRS326', 'MikroTik', 'active', 'Przełącznik dystrybucyjny piętro 2'),
    ('Switch Dostępowy 1', 'switch', '192.168.1.4', '00:11:22:33:44:04', 'Sala 101', 'CSS326', 'MikroTik', 'active', 'Przełącznik dostępowy sala 101'),
    ('Kontroler WiFi', 'controller', '192.168.1.6', '00:11:22:33:44:06', 'Pokój IT', 'cAP ac', 'MikroTik', 'active', 'Kontroler sieci bezprzewodowej'),
    ('Zapora sieciowa', 'firewall', '192.168.1.7', '00:11:22:33:44:07', 'Pokój bezpieczeństwa', 'hAP ac²', 'MikroTik', 'active', 'Zapora sieciowa'),
    ('UPS 1', 'ups', '192.168.1.9', '00:11:22:33:44:09', 'Serwerownia', 'Smart-UPS 1500', 'APC', 'active', 'Zasilacz UPS główny');

    -- Insert Sample Services
    INSERT IGNORE INTO services (name, type, description, price, speed_download, speed_upload) VALUES
    ('Internet Podstawowy', 'internet', 'Podstawowy pakiet internetowy', 39.99, 100, 10),
    ('Internet Standard', 'internet', 'Standardowy pakiet internetowy', 59.99, 200, 20),
    ('Internet Premium', 'internet', 'Premium pakiet internetowy', 89.99, 500, 50),
    ('TV Podstawowy', 'tv', 'Podstawowy pakiet telewizyjny', 29.99, NULL, NULL),
    ('TV Standard', 'tv', 'Standardowy pakiet telewizyjny', 49.99, NULL, NULL),
    ('TV Premium', 'tv', 'Premium pakiet telewizyjny', 79.99, NULL, NULL);

    -- Insert Sample Client Services
    INSERT IGNORE INTO client_services (client_id, service_id, start_date, status) VALUES
    (1, 1, CURDATE(), 'active'),
    (1, 4, CURDATE(), 'active'),
    (2, 2, CURDATE(), 'active'),
    (3, 3, CURDATE(), 'active'),
    (4, 1, CURDATE(), 'active'),
    (4, 5, CURDATE(), 'active');

    -- Insert Sample Tariffs
    INSERT IGNORE INTO tariffs (name, upload_speed, download_speed, tv_included, internet_included, price) VALUES
    ('Podstawowy', 10, 100, FALSE, TRUE, 39.99),
    ('Standard', 20, 200, FALSE, TRUE, 59.99),
    ('Premium', 50, 500, TRUE, TRUE, 89.99),
    ('Ultra', 100, 1000, TRUE, TRUE, 129.99);

    -- Insert Sample TV Packages
    INSERT IGNORE INTO tv_packages (name, tv_package, price, channels_count) VALUES
    ('Podstawowy TV', 'Kanały podstawowe', 29.99, 30),
    ('Standard TV', 'Kanały podstawowe + sport', 49.99, 50),
    ('Premium TV', 'Wszystkie kanały + HBO', 79.99, 100),
    ('Ultra TV', 'Wszystkie kanały + premium', 99.99, 150);

    -- Insert Sample Internet Packages
    INSERT IGNORE INTO internet_packages (name, internet_package, price, speed_download, speed_upload) VALUES
    ('Internet Podstawowy', '100/10 Mbps', 39.99, 100, 10),
    ('Internet Standard', '200/20 Mbps', 59.99, 200, 20),
    ('Internet Premium', '500/50 Mbps', 89.99, 500, 50),
    ('Internet Ultra', '1000/100 Mbps', 129.99, 1000, 100);

    -- Insert Sample Invoices
    INSERT IGNORE INTO invoices (client_id, invoice_number, amount, status, issue_date, due_date) VALUES
    (1, 'INV-2024-001', 69.98, 'paid', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY)),
    (2, 'INV-2024-002', 59.99, 'sent', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY)),
    (3, 'INV-2024-003', 89.99, 'draft', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY)),
    (4, 'INV-2024-004', 129.98, 'sent', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY));

    -- Insert Sample Invoice Items
    INSERT IGNORE INTO invoice_items (invoice_id, description, quantity, unit_price, total_price) VALUES
    (1, 'Internet Podstawowy', 1, 39.99, 39.99),
    (1, 'TV Podstawowy', 1, 29.99, 29.99),
    (2, 'Internet Standard', 1, 59.99, 59.99),
    (3, 'Internet Premium', 1, 89.99, 89.99),
    (4, 'Internet Podstawowy', 1, 39.99, 39.99),
    (4, 'TV Standard', 1, 49.99, 49.99),
    (4, 'Dodatkowe usługi', 1, 40.00, 40.00);

    -- Insert Sample Payments
    INSERT IGNORE INTO payments (invoice_id, amount, payment_date, payment_method, reference_number) VALUES
    (1, 69.98, CURDATE(), 'transfer', 'TRF-2024-001'),
    (2, 59.99, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'card', 'CARD-2024-001');

    -- Insert Sample Cities
    INSERT IGNORE INTO cities (name, region, country) VALUES
    ('Warszawa', 'Mazowieckie', 'Poland'),
    ('Kraków', 'Małopolskie', 'Poland'),
    ('Gdańsk', 'Pomorskie', 'Poland'),
    ('Wrocław', 'Dolnośląskie', 'Poland'),
    ('Poznań', 'Wielkopolskie', 'Poland'),
    ('Łódź', 'Łódzkie', 'Poland');

    -- Insert Sample Streets
    INSERT IGNORE INTO streets (name, city_id) VALUES
    ('Kwiatowa', 1),
    ('Słoneczna', 2),
    ('Długa', 3),
    ('Krótka', 4),
    ('Główna', 5),
    ('Zielona', 6),
    ('Czerwona', 1),
    ('Niebieska', 2);
    ";

    // Split and execute each statement
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        if ($stmt) {
            $pdo->exec($stmt);
        }
    }

    echo "\n✅ sLMS Database initialized successfully!\n";
    echo "📊 Tables created: layout_settings, column_config, menu_items, users, clients, networks, vlans, skeleton_devices, devices, services, client_services, tariffs, tv_packages, internet_packages, invoices, invoice_items, payments, cities, streets\n";
    echo "📝 Sample data inserted for all tables\n";
    echo "⚙️ Layout settings configured for sLMS system\n";
    echo "🎨 Column configuration set up for data tables\n";
    echo "🔗 Menu structure created with proper navigation\n";
    
    // Display some statistics
    $stats = $pdo->query("SELECT 
        (SELECT COUNT(*) FROM clients) as clients_count,
        (SELECT COUNT(*) FROM devices) as devices_count,
        (SELECT COUNT(*) FROM networks) as networks_count,
        (SELECT COUNT(*) FROM services) as services_count,
        (SELECT COUNT(*) FROM users) as users_count")->fetch();
    
    echo "\n📈 Database Statistics:\n";
    echo "   👥 Clients: {$stats['clients_count']}\n";
    echo "   💻 Devices: {$stats['devices_count']}\n";
    echo "   🌐 Networks: {$stats['networks_count']}\n";
    echo "   ⚙️ Services: {$stats['services_count']}\n";
    echo "   👤 Users: {$stats['users_count']}\n";
    
    echo "\n🚀 sLMS System is ready to use!\n";
    echo "   Default admin credentials: admin / password\n";
    echo "   Access the system at: http://localhost/slms/\n";

} catch (PDOException $e) {
    echo "\n❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Update menu_items table to fix any URL issues
$pdo->exec("UPDATE menu_items SET url = REPLACE(url, '/modules/modules/', '/modules/') WHERE url LIKE '/modules/modules/%';");
echo "\n🔧 Menu URLs cleaned up successfully!\n";
?> 