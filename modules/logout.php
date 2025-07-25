<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

// Log the logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = get_pdo();
        $logStmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $logStmt->execute([$_SESSION['user_id'], 'logout', 'User logged out', $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    } catch (Exception $e) {
        // Ignore logging errors during logout
    }
}

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?> 