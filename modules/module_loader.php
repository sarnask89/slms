<?php
/**
 * Module Loader for SLMS
 * Handles proper initialization of modules with session management
 */

// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/helpers/database_helper.php';
require_once __DIR__ . '/helpers/auth_helper.php';

/**
 * Load a module with proper initialization
 */
function load_module($module_name, $page_title = '') {
    global $pageTitle;
    
    // Set page title
    $pageTitle = $page_title ?: ucfirst(str_replace('_', ' ', $module_name));
    
    // Start output buffering to prevent header issues
    ob_start();
    
    // Include the module file
    $module_file = __DIR__ . '/' . $module_name . '.php';
    if (file_exists($module_file)) {
        include $module_file;
    } else {
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-danger">';
        echo '<h4>Module Not Found</h4>';
        echo '<p>The module "' . htmlspecialchars($module_name) . '" could not be found.</p>';
        echo '<a href="' . base_url('admin_menu_enhanced.php') . '" class="btn btn-primary">Return to Admin Menu</a>';
        echo '</div>';
        echo '</div>';
    }
    
    // Get the content
    $content = ob_get_clean();
    
    // Include the layout
    include __DIR__ . '/../partials/layout.php';
}

/**
 * Load module content without layout (for AJAX calls)
 */
function load_module_content($module_name) {
    $module_file = __DIR__ . '/' . $module_name . '.php';
    if (file_exists($module_file)) {
        include $module_file;
    } else {
        echo '<div class="alert alert-danger">Module not found: ' . htmlspecialchars($module_name) . '</div>';
    }
}

/**
 * Check if module exists
 */
function module_exists($module_name) {
    return file_exists(__DIR__ . '/' . $module_name . '.php');
}

/**
 * Get list of available modules
 */
function get_available_modules() {
    $modules = [];
    $files = glob(__DIR__ . '/*.php');
    
    foreach ($files as $file) {
        $filename = basename($file, '.php');
        if ($filename !== 'module_loader' && $filename !== 'index') {
            $modules[] = $filename;
        }
    }
    
    return $modules;
}

/**
 * Initialize module environment
 */
function init_module_environment() {
    // Ensure database connection
    try {
        $pdo = get_pdo();
    } catch (Exception $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
    
    // Set default timezone
    if (!ini_get('date.timezone')) {
        date_default_timezone_set('Europe/Warsaw');
    }
    
    // Set error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Set character encoding
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
}

// Initialize the environment
init_module_environment();
?> 