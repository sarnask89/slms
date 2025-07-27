<?php
// Minimalistyczny loader CMS do dynamicznego wyświetlania stron/modułów
require_once __DIR__ . '/config.php';

$slug = $_GET['page'] ?? 'index'; // np. ?page=about

$pdo = get_pdo();
$stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$page) {
    http_response_code(404);
    echo "Strona nie znaleziona.";
    exit;
}

echo "<h1>" . htmlspecialchars($page['title']) . "</h1>";

if ($page['type'] === 'dynamic') {
    // UWAGA: eval wykonuje kod PHP z bazy – używaj tylko w zaufanym środowisku!
    eval('?>' . $page['content']);
} else {
    echo $page['content'];
}
?>
