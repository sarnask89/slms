<?php
if (session_status() === PHP_SESSION_NONE) {
    if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { 
        session_start(); 
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require login - redirect to login page if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . base_url('modules/login.php'));
        exit();
    }
}

/**
 * Check if user has specific role
 * @param string $role
 * @return bool
 */
function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Require specific role - redirect to login if not authorized
 * @param string $role
 */
function require_role($role) {
    require_login();
    if (!has_role($role)) {
        header('Location: ' . base_url('modules/error_403.php'));
        exit();
    }
}

/**
 * Check if user has admin role
 * @return bool
 */
function is_admin() {
    return has_role('admin');
}

/**
 * Require admin role
 */
function require_admin() {
    require_role('admin');
}

/**
 * Get current user info
 * @return array|null
 */
function get_current_user_info() {
    if (!is_logged_in()) {
        return null;
    }
    
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT u.*, al.name as access_level_name 
            FROM users u 
            LEFT JOIN access_levels al ON u.access_level_id = al.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting user info: " . $e->getMessage());
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'Unknown',
            'role' => $_SESSION['role'] ?? 'user'
        ];
    }
}

/**
 * Check if user has access level permission for specific section and action
 * @param string $section
 * @param string $action
 * @return bool
 */
function has_access_permission($section, $action) {
    if (!is_logged_in()) {
        return false;
    }
    
    // Admin has all permissions
    if (is_admin()) {
        return true;
    }
    
    try {
        $pdo = get_pdo();
        
        // Get user's access level
        $stmt = $pdo->prepare("SELECT access_level_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user || !$user['access_level_id']) {
            return false;
        }
        
        // Check if access level has the specific permission
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM access_level_permissions 
            WHERE access_level_id = ? AND section = ? AND action = ?
        ");
        $stmt->execute([$user['access_level_id'], $section, $action]);
        
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking permission: " . $e->getMessage());
        return false;
    }
}

/**
 * Require access level permission for specific section and action
 * @param string $section
 * @param string $action
 */
function require_access_permission($section, $action) {
    require_login();
    if (!has_access_permission($section, $action)) {
        header('Location: ' . base_url('modules/error_403.php'));
        exit();
    }
}

/**
 * Get user's access level information
 * @return array|null
 */
function get_user_access_level() {
    if (!is_logged_in()) {
        return null;
    }
    
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT al.* 
            FROM access_levels al 
            JOIN users u ON al.id = u.access_level_id 
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting access level: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all permissions for user's access level
 * @return array
 */
function get_user_permissions() {
    if (!is_logged_in()) {
        return [];
    }
    
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT alp.section, alp.action
            FROM access_level_permissions alp
            JOIN users u ON alp.access_level_id = u.access_level_id
            WHERE u.id = ?
            ORDER BY alp.section, alp.action
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting permissions: " . $e->getMessage());
        return [];
    }
}

/**
 * Log user activity
 * @param string $action
 * @param string $details
 * @return bool
 */
function log_activity($action, $details = '') {
    if (!is_logged_in()) {
        return false;
    }
    
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            INSERT INTO user_activity_log (user_id, action, details, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}
?> 