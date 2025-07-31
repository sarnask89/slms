<?php
// config.php
// Centralized database configuration for sLMS

// Database credentials
$db_host = 'localhost';
$db_name = 'slmsdb';
$db_user = 'slms';
$db_pass = 'mlss15gent001';
$db_charset = 'utf8mb4';

// Returns a PDO connection
function get_pdo() {
    global $db_host, $db_name, $db_user, $db_pass, $db_charset;
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, $db_user, $db_pass, $options);
}

// Usage: include 'config.php'; $pdo = get_pdo(); 