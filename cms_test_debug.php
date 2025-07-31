<?php
// Skrypt do automatycznego testowania i debugowania wszystkich stron CMS
require_once __DIR__ . '/config.php';

$pdo = get_pdo();
$pages = $pdo->query("SELECT slug, title, type FROM cms_pages WHERE status = 'published'")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
foreach ($pages as $page) {
    $slug = $page['slug'];
    $type = $page['type'];
    $url = "/cms_loader.php?page=$slug";
    
    // Test lokalny przez include (nie HTTP!)
    $_GET['page'] = $slug;
    ob_start();
    try {
        include __DIR__ . '/cms_loader.php';
        $output = ob_get_clean();
        if (strpos($output, 'Fatal error') !== false || strpos($output, 'Parse error') !== false) {
            $errors[] = "Błąd PHP na stronie: $slug ($type)";
        }
    } catch (Throwable $e) {
        $errors[] = "Wyjątek na stronie $slug: " . $e->getMessage();
        ob_end_clean();
    }
}

if (empty($errors)) {
    echo "Wszystkie strony zostały poprawnie wyrenderowane!\n";
} else {
    echo "\nWykryto błędy:\n";
    foreach ($errors as $err) {
        echo $err . "\n";
    }
}
?>
