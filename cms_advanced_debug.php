<?php
// Zaawansowany skrypt do testowania i debugowania stron CMS
require_once __DIR__ . '/config.php';

$pdo = get_pdo();
$pages = $pdo->query("SELECT slug, title, type FROM cms_pages WHERE status = 'published'")->fetchAll(PDO::FETCH_ASSOC);

$log_file = __DIR__ . '/cms_debug_report.txt';
file_put_contents($log_file, "=== CMS DEBUG REPORT ===\n" . date('Y-m-d H:i:s') . "\n\n");

function log_error($msg) {
    global $log_file;
    file_put_contents($log_file, $msg . "\n", FILE_APPEND);
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    log_error("[PHP ERROR] $errstr in $errfile:$errline");
    return true;
});

set_exception_handler(function($e) {
    log_error("[EXCEPTION] " . $e->getMessage() . "\n" . $e->getTraceAsString());
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        log_error("[FATAL] {$error['message']} in {$error['file']}:{$error['line']}");
    }
});

$errors = [];
foreach ($pages as $page) {
    $slug = $page['slug'];
    $type = $page['type'];
    $_GET['page'] = $slug;
    ob_start();
    try {
        include __DIR__ . '/cms_loader.php';
        $output = ob_get_clean();
        if (preg_match('/(Fatal error|Parse error|SQLSTATE|Warning|Notice|Exception)/i', $output)) {
            $msg = "[OUTPUT ERROR] Strona: $slug ($type)\n$output\n";
            $errors[] = $msg;
            log_error($msg);
        }
    } catch (Throwable $e) {
        $msg = "[THROWABLE] Strona: $slug ($type)\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
        $errors[] = $msg;
        log_error($msg);
        ob_end_clean();
    }
}

if (empty($errors)) {
    echo "Wszystkie strony zostały poprawnie wyrenderowane!\n";
    log_error("Wszystkie strony zostały poprawnie wyrenderowane!\n");
} else {
    echo "\nWykryto błędy! Szczegóły w $log_file\n";
    foreach ($errors as $err) {
        echo $err . "\n";
    }
}
?>
