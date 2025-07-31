<?php
/**
 * Helper Functions for SLMS
 * Provides common utility functions used across the system
 */

/**
 * Get base URL for the application
 */
function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = dirname($_SERVER['SCRIPT_NAME']);
    
    // Remove trailing slash if present
    $base = rtrim($base, '/');
    
    // If we're in a subdirectory, make sure we have the right path
    if (strpos($base, '/modules') !== false) {
        $base = dirname($base);
    }
    
    return $protocol . '://' . $host . $base . '/' . ltrim($path, '/');
}

/**
 * Get current URL
 */
function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    
    return $protocol . '://' . $host . $uri;
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Get asset URL
 */
function asset_url($path) {
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Get module URL
 */
function module_url($path) {
    return base_url('modules/' . ltrim($path, '/'));
}

/**
 * Check if user is authenticated
 */
function is_authenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function get_current_user_data() {
    if (!is_authenticated()) {
        return null;
    }
    
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get user access level
 */
function get_user_access_level($userId) {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT access_level FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? $result['access_level'] : 'user';
    } catch (Exception $e) {
        return 'user';
    }
}

/**
 * Check if user has basic permission (simplified version)
 */
function has_basic_permission($permission) {
    $user = get_current_user_data();
    if (!$user) {
        return false;
    }
    
    $accessLevel = get_user_access_level($user['id']);
    
    // Admin has all permissions
    if ($accessLevel === 'admin') {
        return true;
    }
    
    // Add more permission logic here as needed
    return false;
}

/**
 * Format date
 */
function format_date($date, $format = 'Y-m-d H:i:s') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Sanitize output
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get system statistics
 */
function get_system_statistics() {
    try {
        $pdo = get_pdo();
        
        $stats = [];
        
        // Device count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM devices");
        $stats['total_devices'] = $stmt->fetch()['count'];
        
        // Online devices
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM devices WHERE status = 'online'");
        $stats['online_devices'] = $stmt->fetch()['count'];
        
        // User count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'];
        
        // Client count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients");
        $stats['total_clients'] = $stmt->fetch()['count'];
        
        return $stats;
    } catch (Exception $e) {
        return [
            'total_devices' => 0,
            'online_devices' => 0,
            'total_users' => 0,
            'total_clients' => 0
        ];
    }
}
?> 