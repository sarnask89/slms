<?php
session_start();
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Current Time: " . date("Y-m-d H:i:s") . "\n";
echo "Server Software: " . $_SERVER["SERVER_SOFTWARE"] . "\n";
?>
