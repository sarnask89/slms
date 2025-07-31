<?php
// modules/helpers/database_helper.php
// Database helper functions for sLMS system

require_once __DIR__ . '/../../config.php';

/**
 * Get menu items from database with hierarchical structure
 * @return array Array of menu items organized in tree structure
 */
function get_menu_items_from_database() {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT id, label, url, icon, type, parent_id, position, enabled, options 
            FROM menu_items 
            WHERE enabled = 1 
            ORDER BY position ASC, id ASC
        ");
        $stmt->execute();
        $all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Build hierarchical structure
        $menu_tree = [];
        $children = [];
        
        foreach ($all_items as $item) {
            if ($item['parent_id'] === null || $item['parent_id'] == 0) {
                // This is a parent item (no parent_id or parent_id is 0)
                $menu_tree[] = $item;
            } else {
                // This is a child item
                $children[$item['parent_id']][] = $item;
            }
        }
        
        // Add children to their parents
        foreach ($menu_tree as &$parent) {
            $parent['children'] = isset($children[$parent['id']]) ? $children[$parent['id']] : [];
        }
        
        return $menu_tree;
    } catch (PDOException $e) {
        error_log("Error fetching menu items: " . $e->getMessage());
        return [];
    }
}

/**
 * Get layout settings from database
 * @param PDO $pdo Database connection
 * @return array Array of layout settings
 */
function get_layout_settings($pdo = null) {
    if ($pdo === null) {
        $pdo = get_pdo();
    }
    
    $default_layout = [
        'menu_position' => 'left',
        'menu_style' => 'vertical',
        'sidebar_width' => '250px',
        'header_height' => '60px',
        'color_scheme' => 'default',
        'font_size' => 'medium',
        'show_breadcrumbs' => true,
        'show_search' => true,
        'show_user_menu' => true,
        'footer_text' => '© ' . date('Y') . ' sLMS System'
    ];
    
    try {
        // Check if table exists first
        $stmt = $pdo->query("SHOW TABLES LIKE 'layout_settings'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("SELECT setting_key, setting_value, setting_type FROM layout_settings");
            $stmt->execute();
            $settings = $default_layout;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $value = $row['setting_value'];
                switch ($row['setting_type']) {
                    case 'boolean':
                        $value = (bool)$value;
                        break;
                    case 'integer':
                        $value = (int)$value;
                        break;
                    case 'json':
                        $value = json_decode($value, true);
                        break;
                }
                $settings[$row['setting_key']] = $value;
            }
            return $settings;
        }
    } catch (PDOException $e) {
        error_log("Error fetching layout settings: " . $e->getMessage());
    }
    
    return $default_layout;
}

/**
 * Get column configuration for a table
 * @param string $table_name Table name
 * @return array Array of column configurations
 */
function get_column_config($table_name) {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT column_name, display_name, visible, order_position, sortable, filterable, width 
            FROM column_config 
            WHERE table_name = ? AND visible = 1 
            ORDER BY order_position ASC
        ");
        $stmt->execute([$table_name]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching column config: " . $e->getMessage());
        return [];
    }
}

/**
 * Get footer text from layout settings
 * @param PDO $pdo Database connection
 * @return string Footer text
 */
function get_footer_text($pdo = null) {
    $settings = get_layout_settings($pdo);
    return $settings['footer_text'] ?? '© 2024 sLMS System. Wszystkie prawa zastrzeżone.';
}

/**
 * Get clients from database
 * @param int $limit Limit of records to return
 * @param int $offset Offset for pagination
 * @return array Array of clients
 */
function get_clients($limit = 50, $offset = 0) {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, phone, email, address, city, postal_code, company_name, status, created_at 
            FROM clients 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching clients: " . $e->getMessage());
        return [];
    }
}

/**
 * Get devices from database
 * @param int $limit Limit of records to return
 * @param int $offset Offset for pagination
 * @return array Array of devices
 */
function get_devices($limit = 50, $offset = 0) {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT d.*, c.first_name, c.last_name, n.name as network_name 
            FROM devices d 
            LEFT JOIN clients c ON d.client_id = c.id 
            LEFT JOIN networks n ON d.network_id = n.id 
            ORDER BY d.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching devices: " . $e->getMessage());
        return [];
    }
}

/**
 * Get networks from database
 * @param int $limit Limit of records to return
 * @param int $offset Offset for pagination
 * @return array Array of networks
 */
function get_networks($limit = 50, $offset = 0) {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT id, name, network_address, gateway, dns_servers, dhcp_range_start, dhcp_range_end, description, created_at 
            FROM networks 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching networks: " . $e->getMessage());
        return [];
    }
}

/**
 * Get services from database
 * @param int $limit Limit of records to return
 * @param int $offset Offset for pagination
 * @return array Array of services
 */
function get_services($limit = 50, $offset = 0) {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT s.*, c.first_name, c.last_name 
            FROM services s 
            LEFT JOIN clients c ON s.client_id = c.id 
            ORDER BY s.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching services: " . $e->getMessage());
        return [];
    }
}

/**
 * Get users from database
 * @param int $limit Limit of records to return
 * @param int $offset Offset for pagination
 * @return array Array of users
 */
function get_users($limit = 50, $offset = 0) {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, first_name, last_name, is_active, created_at 
            FROM users 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching users: " . $e->getMessage());
        return [];
    }
}

/**
 * Get statistics for dashboard
 * @return array Array of statistics
 */
function get_dashboard_stats() {
    try {
        $pdo = get_pdo();
        $stats = [];
        
        // Count clients
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients WHERE status = 'active'");
        $stats['active_clients'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count devices
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM devices WHERE status = 'online'");
        $stats['online_devices'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count networks
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM networks");
        $stats['networks'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count services
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM services");
        $stats['services'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count invoices
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM invoices WHERE status = 'unpaid'");
        $stats['unpaid_invoices'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        return [
            'active_clients' => 0,
            'online_devices' => 0,
            'networks' => 0,
            'services' => 0,
            'unpaid_invoices' => 0
        ];
    }
}

/**
 * Search clients by term
 * @param string $term Search term
 * @param int $limit Limit of records to return
 * @return array Array of matching clients
 */
function search_clients($term, $limit = 20) {
    try {
        $pdo = get_pdo();
        $search_term = "%{$term}%";
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, phone, email, company_name, status 
            FROM clients 
            WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR company_name LIKE ? 
            ORDER BY first_name, last_name 
            LIMIT ?
        ");
        $stmt->execute([$search_term, $search_term, $search_term, $search_term, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error searching clients: " . $e->getMessage());
        return [];
    }
}

/**
 * Get client by ID
 * @param int $id Client ID
 * @return array|null Client data or null if not found
 */
function get_client_by_id($id) {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT * FROM clients WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching client: " . $e->getMessage());
        return null;
    }
}

/**
 * Update layout setting
 * @param string $key Setting key
 * @param mixed $value Setting value
 * @param string $type Setting type
 * @return bool Success status
 */
function update_layout_setting($key, $value, $type = 'string') {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            INSERT INTO layout_settings (setting_key, setting_value, setting_type) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        return $stmt->execute([$key, $value, $type]);
    } catch (PDOException $e) {
        error_log("Error updating layout setting: " . $e->getMessage());
        return false;
    }
}
?> 