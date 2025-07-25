<?php
// sLMS Comprehensive Debug Script
// Usage: php debug_system.php

// Load config and helpers
require_once __DIR__ . '/config.php';

function section($title) {
    echo "\n==== $title ====" . PHP_EOL;
}

function ok($msg) { echo "✅ $msg\n"; }
function fail($msg) { echo "❌ $msg\n"; }
function info($msg) { echo "ℹ️  $msg\n"; }

// 1. Test database connection
section('Database Connection');
try {
    $pdo = get_pdo();
    $stmt = $pdo->query('SELECT 1');
    ok('Database connection successful');
} catch (Exception $e) {
    fail('Database connection failed: ' . $e->getMessage());
}

// 2. Check required files
section('Required Files');
$required = [
    'config.php',
    'modules/helpers/auth_helper.php',
    'modules/helpers/database_helper.php',
    'modules/cacti_api.php',
    'modules/bridge_nat_controller.php',
];
foreach ($required as $file) {
    if (file_exists($file)) {
        ok("$file found");
    } else {
        fail("$file missing");
    }
}

// 3. Check key classes/functions
section('Key Classes & Functions');
$checks = [
    'CactiAPI' => class_exists('CactiAPI', false) || (include_once 'modules/cacti_api.php') || class_exists('CactiAPI', false),
    'BridgeNATController' => class_exists('BridgeNATController', false) || (include_once 'modules/bridge_nat_controller.php') || class_exists('BridgeNATController', false),
    'get_pdo' => function_exists('get_pdo'),
    'get_menu_items_from_database' => function_exists('get_menu_items_from_database') || (include_once 'modules/helpers/database_helper.php') || function_exists('get_menu_items_from_database'),
    'is_logged_in' => function_exists('is_logged_in') || (include_once 'modules/helpers/auth_helper.php') || function_exists('is_logged_in'),
];
foreach ($checks as $name => $result) {
    if ($result) {
        ok("$name available");
    } else {
        fail("$name missing");
    }
}

// 4. Test Cacti API (mock mode)
section('Cacti API Integration');
try {
    $cacti = new CactiAPI();
    $status = $cacti->getStatus();
    ok('CactiAPI getStatus: ' . json_encode($status));
    $devices = $cacti->getDevices();
    ok('CactiAPI getDevices: ' . (is_array($devices) ? count($devices) . ' devices' : 'No devices'));
    if (method_exists($cacti, 'isMockMode') && $cacti->isMockMode()) {
        info('CactiAPI running in mock mode');
    }
} catch (Exception $e) {
    fail('CactiAPI test failed: ' . $e->getMessage());
}

// 5. Test Bridge NAT Controller (mock mode)
section('Bridge NAT Controller');
try {
    $bridge = new BridgeNATController(true);
    $stats = $bridge->getBridgeStats();
    ok('BridgeNATController getBridgeStats: ' . json_encode($stats));
} catch (Exception $e) {
    fail('BridgeNATController test failed: ' . $e->getMessage());
}

// 6. Test menu items from database
section('Menu Items');
try {
    $items = get_menu_items_from_database();
    ok('Menu items loaded: ' . (is_array($items) ? count($items) : 'none'));
} catch (Exception $e) {
    fail('Menu items test failed: ' . $e->getMessage());
}

// 7. System info
section('System Info');
echo 'PHP Version: ' . phpversion() . "\n";
echo 'OS: ' . PHP_OS . "\n";
echo 'Timezone: ' . date_default_timezone_get() . "\n";
echo 'Date: ' . date('Y-m-d H:i:s') . "\n";

// 8. File permissions check (logs, cache)
section('File Permissions');
$dirs = ['logs', 'cache', 'uploads'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            ok("$dir is writable");
        } else {
            fail("$dir is not writable");
        }
    } else {
        info("$dir directory not found");
    }
}

// 9. Extensions check
section('PHP Extensions');
$exts = ['pdo', 'pdo_mysql', 'curl', 'json', 'snmp'];
foreach ($exts as $ext) {
    if (extension_loaded($ext)) {
        ok("$ext loaded");
    } else {
        fail("$ext missing");
    }
}

echo "\nAll checks complete. Review output above for any errors or missing components.\n"; 