<?php
/**
 * Bridge NAT/Mangle System Setup
 * Sets up the bridge-based traffic control system without DHCP dependency
 */

require_once 'config.php';

$pdo = get_pdo();
$errors = [];
$success = '';

// Create database tables
function createBridgeTables($pdo) {
    $sql = file_get_contents('sql/bridge_nat_schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(--|\/\*|DELIMITER)/', $statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        }
    }
}

try {
    createBridgeTables($pdo);
    $success = "Bridge NAT/Mangle database tables and defaults created successfully.";
} catch (Exception $e) {
    $errors[] = "Error creating bridge NAT tables: " . $e->getMessage();
}

// Output result
if (!empty($errors)) {
    echo "Setup failed:\n";
    foreach ($errors as $err) {
        echo " - $err\n";
    }
    exit(1);
} else {
    echo "$success\n";
    exit(0);
} 