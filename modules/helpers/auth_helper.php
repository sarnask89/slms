<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    // In full access mode, always consider user as logged in
    if (!isset($_SESSION['user_id'])) {
        // Auto-create admin session
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        $_SESSION['login_time'] = time();
        $_SESSION['auto_login'] = true;
    }
    return true;
}

/**
 * Require login - redirect to login page if not logged in
 * MODIFIED: Now allows full access without authentication
 */
function require_login() {
    // Auto-login functionality - automatically log in as admin if not logged in
    if (!is_logged_in()) {
        // Create admin session automatically
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        $_SESSION['login_time'] = time();
        $_SESSION['auto_login'] = true;
        
        // Log the auto-login activity
        try {
            $pdo = get_pdo();
            $logStmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $logStmt->execute([1, 'auto_login', 'Automatic login - full access mode', $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        } catch (Exception $e) {
            // Ignore logging errors
        }
    }
}

/**
 * Check if user has specific role
 */
function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Require specific role - redirect to login if not authorized
 * MODIFIED: Now allows all roles in full access mode
 */
function require_role($role) {
    require_login();
    // In full access mode, always allow access
    return true;
}

/**
 * Check if user has admin role
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
 */
function get_current_user_info() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ];
}

/**
 * Log user activity
 */
function log_activity($action, $details = '') {
    if (!is_logged_in()) {
        return false;
    }
    
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if user has permission for specific module (auth helper version)
 */
function has_permission_auth($module, $permission = 'read') {
    if (!is_logged_in()) {
        return false;
    }
    
    // Admin has all permissions
    if (is_admin()) {
        return true;
    }
    
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT permission FROM user_permissions WHERE user_id = ? AND module = ?");
        $stmt->execute([$_SESSION['user_id'], $module]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return false;
        }
        
        $userPermission = $result['permission'];
        
        // Permission hierarchy: read < write < admin
        $permissionLevels = ['read' => 1, 'write' => 2, 'admin' => 3];
        $requiredLevel = $permissionLevels[$permission] ?? 1;
        $userLevel = $permissionLevels[$userPermission] ?? 0;
        
        return $userLevel >= $requiredLevel;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if user has access level permission for specific section and action
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
        $stmt = $pdo->prepare("SELECT id FROM access_level_permissions WHERE access_level_id = ? AND section = ? AND action = ?");
        $stmt->execute([$user['access_level_id'], $section, $action]);
        
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Require access level permission for specific section and action
 */
function require_access_permission($section, $action) {
    require_login();
    if (!has_access_permission($section, $action)) {
        header('Location: ' . base_url('modules/login.php'));
        exit();
    }
}

/**
 * Get user's access level information (auth helper version)
 */
function get_user_access_level_auth() {
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
        return null;
    }
}

/**
 * Get all permissions for user's access level
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
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Require permission for specific module
 */
function require_permission($module, $permission = 'read') {
    require_login();
    if (!has_permission_auth($module, $permission)) {
        header('Location: ' . base_url('modules/login.php'));
        exit();
    }
}
?> 