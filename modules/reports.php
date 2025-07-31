<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'module_loader.php';

require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login
require_login();

$pageTitle = 'Reports';
ob_start();

// Redirect to bandwidth reports for now
header('Location: bandwidth_reports.php');
exit;
?> 