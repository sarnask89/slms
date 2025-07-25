<?php
// test_all_php_files.php

set_time_limit(0);
error_reporting(E_ALL);

$dirs = [
    __DIR__ . '/modules',
    __DIR__ . '/modules/helpers',
    __DIR__ // root slms directory
];

$tested = [];
$results = [];

function test_file($file) {
    ob_start();
    $result = [
        'file' => $file,
        'status' => 'OK',
        'error' => ''
    ];
    try {
        // Isolate include to avoid variable pollution
        include_once $file;
    } catch (Throwable $e) {
        $result['status'] = 'ERROR';
        $result['error'] = $e->getMessage();
    }
    $output = ob_get_clean();
    if (!empty($output)) {
        $result['status'] = $result['status'] === 'OK' ? 'WARNING' : $result['status'];
        $result['error'] .= "\nOutput: " . $output;
    }
    return $result;
}

// Gather all PHP files
foreach ($dirs as $dir) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $real = $file->getRealPath();
            if (!in_array($real, $tested)) {
                $tested[] = $real;
            }
        }
    }
}

// Test each file
foreach ($tested as $phpfile) {
    $results[] = test_file($phpfile);
}

// Output results
echo "PHP Module Test Results\n";
echo "======================\n";
$ok = $warn = $err = 0;
foreach ($results as $res) {
    if ($res['status'] === 'OK') $ok++;
    elseif ($res['status'] === 'WARNING') $warn++;
    else $err++;
    echo "[{$res['status']}] {$res['file']}\n";
    if ($res['error']) {
        echo "    {$res['error']}\n";
    }
}
echo "\nSummary: {$ok} OK, {$warn} Warnings, {$err} Errors, " . count($results) . " files tested.\n";
?>
