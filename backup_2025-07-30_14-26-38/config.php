<?php
/**
 * SLMS Configuration File
 * Simple MySQL credentials for database connection
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'slmsdb');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Get PDO database connection
 */
function get_pdo() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
?> 