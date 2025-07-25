<?php
require_once "config.php";
require_once "modules/helpers/auth_helper.php";
require_once "modules/helpers/database_helper.php";

header("Content-Type: text/html; charset=utf-8");
echo "<html><head><title>SLMS Page Test Results</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .test-group { margin-bottom: 20px; }
</style></head><body>";

echo "<h1>SLMS Page Test Results</h1>";

function test_page($url, $description) {
    echo "<div class=\"test-group\">";
    echo "<h3>Testing: $description</h3>";
    
    $ch = curl_init("http://localhost/slms/" . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "URL: $url<br>";
    echo "HTTP Response Code: " . $httpCode . "<br>";
    
    if ($httpCode == 200) {
        echo "<span class=\"success\">✓ Page accessible</span><br>";
    } else {
        echo "<span class=\"error\">× Error accessing page (HTTP $httpCode)</span><br>";
    }
    
    if (strpos($response, "PHP Notice") !== false || strpos($response, "PHP Warning") !== false || strpos($response, "PHP Error") !== false) {
        echo "<span class=\"warning\">⚠ PHP Notices/Warnings detected</span><br>";
    }
    
    echo "</div>";
}

// Test main pages
$pages_to_test = [
    "index.php" => "Main Dashboard",
    "modules/clients.php" => "Clients Management",
    "modules/devices.php" => "Devices Management",
    "modules/skeleton_devices.php" => "Skeleton Devices",
    "modules/networks.php" => "Networks Management",
    "modules/services.php" => "Services Management",
    "modules/internet_packages.php" => "Internet Packages",
    "modules/tv_packages.php" => "TV Packages",
    "modules/tariffs.php" => "Tariffs Management",
    "modules/payments.php" => "Payments Management",
    "modules/invoices.php" => "Invoices Management",
    "modules/users.php" => "Users Management"
];

foreach ($pages_to_test as $url => $description) {
    test_page($url, $description);
}

echo "</body></html>";
?>
