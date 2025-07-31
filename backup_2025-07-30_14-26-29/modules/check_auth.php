<?php
// check_auth.php - Simple authentication check and redirect
require_once __DIR__ . '/../config.php';

try {
    $pdo = get_pdo();
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $usersTableExists = $stmt->rowCount() > 0;
    
    if (!$usersTableExists) {
        // Redirect to setup
        header('Location: setup_auth_tables.php');
        exit();
    }
    
    // Check if any users exist
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $userCount = $stmt->fetch()['user_count'];
    
    if ($userCount == 0) {
        // No users exist, redirect to user management for setup
        header('Location: user_management.php?setup=1');
        exit();
    }
    
    // Check if user is logged in
    if (session_status() === PHP_SESSION_NONE) {
        if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { session_start(); } }
    }
    if (isset($_SESSION['user_id'])) {
        // User is logged in, redirect to main page
        header('Location: ../index.php');
        exit();
    } else {
        // User is not logged in, redirect to login
        header('Location: login.php');
        exit();
    }
    
} catch (Exception $e) {
    // Database error, redirect to setup
    header('Location: setup_auth_tables.php');
    exit();
}
?> 