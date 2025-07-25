<?php
header('Content-Type: text/plain');
echo "PHP Test Output\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
?> 