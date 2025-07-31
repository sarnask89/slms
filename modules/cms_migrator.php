<?php
// Migrator statycznych plików do bazy CMS
require_once 'module_loader.php';


function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-a-z0-9]+~', '', strtolower($text));
    $text = trim($text, '-');
    return $text ?: 'page';
}



// Rekurencyjne skanowanie katalogu i migracja plików PHP/HTML
function scan_dir_recursive($dir, $exts = ['php','html']) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
        if (in_array($ext, $exts)) {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

$scan_root = '/var/www/html/';
$files = scan_dir_recursive($scan_root);


$pdo = get_pdo();
$migrated = 0;
foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Plik $file nie istnieje!\n";
        continue;
    }
    $content = file_get_contents($file);
    $title = basename($file);
    $slug = slugify(str_replace(['.php','.html'], '', $title));
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $type = ($ext === 'php') ? 'dynamic' : 'html';

    // Wykryj powiązania (include/require)
    preg_match_all('/(include|require)(_once)?\s*\(?["\']([^"\']+)["\']\)?;/', $content, $matches);
    $relations = $matches[3] ?? [];

    // Sprawdź, czy już istnieje taki slug
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cms_pages WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetchColumn() > 0) {
        echo "Pomijam (już istnieje): $file jako $slug\n";
        continue;
    }

    $meta = [
        'path' => $file,
        'detected_relations' => $relations,
        'frame' => 'default',
        'description' => '',
        'permissions' => 'public',
    ];
    $meta_json = json_encode($meta, JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("INSERT INTO cms_pages (title, slug, type, content, permissions, status, created_at) VALUES (?, ?, ?, ?, ?, 'published', NOW())");
    $stmt->execute([$title, $slug, $type, $content, $meta_json]);
    echo "Zmigrowano: $file jako $slug (typ: $type)\n";
    $migrated++;
}
echo "\nŁącznie zmigrowano: $migrated plików.\n";
