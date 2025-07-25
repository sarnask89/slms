<?php
// Sample configuration file for sLMS
// Copy this file to config.php and update with your database credentials

$db_host = 'localhost';
$db_name = 'slmsdb';
$db_user = 'your_username';
$db_pass = 'your_password';
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

// Base URL function to handle relative URLs correctly
function base_url($path = '') {
    // Always calculate from the root directory, not relative to current script
    if (isset($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] !== 'Standard input code') {
        // Get the root directory by finding the common path
        $script_path = $_SERVER['SCRIPT_NAME'];
        $root_dir = '';
        
        // If we're in a subdirectory, calculate the root
        if (strpos($script_path, '/modules/') !== false || strpos($script_path, 'modules/') !== false) {
            $root_dir = dirname(dirname($script_path));
        } else {
            $root_dir = dirname($script_path);
        }
        
        // Remove trailing slash if present
        $root_dir = rtrim($root_dir, '/');
        // If root_dir is just '.', use empty string
        if ($root_dir === '.') {
            $root_dir = '';
        }
        // Return the base URL
        return $root_dir . '/' . ltrim($path, '/');
    } else {
        // Fallback for command line or when SCRIPT_NAME is not available
        return '/' . ltrim($path, '/');
    }
}

// Optional: Cacti integration settings
$cacti_url = 'http://localhost:8081';
$cacti_username = 'admin';
$cacti_password = 'admin';

// Optional: SNMP default settings
$default_snmp_community = 'public';
$default_snmp_timeout = 1000000;
$default_snmp_retries = 3;

// Optional: System settings
$system_name = 'sLMS';
$system_version = '1.0.0';
$timezone = 'Europe/Warsaw';

// Set timezone
date_default_timezone_set($timezone);
?> 