<?php
// Skrypt automatycznie naprawiający błędy wykryte w cms_debug_report.txt

$report = file_get_contents(__DIR__ . '/cms_debug_report.txt');
$pdo = null;
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
    $pdo = get_pdo();
}

function fix_permissions($path, $mode = 0644, $is_dir = false) {
    if (!file_exists($path)) return false;
    if ($is_dir) $mode = 0755;
    return chmod($path, $mode);
}

$actions = [];
$lines = explode("\n", $report);
foreach ($lines as $line) {
    // Napraw brakujące kolumny
    if (preg_match('/✗ Missing columns in (\w+): (.+)/', $line, $m)) {
        $table = $m[1];
        $cols = array_map('trim', explode(',', $m[2]));
        foreach ($cols as $col) {
            // Prosta heurystyka typu
            $type = 'VARCHAR(255)';
            if (strpos($col, 'id') !== false) $type = 'INT';
            if (strpos($col, 'at') !== false) $type = 'TIMESTAMP NULL DEFAULT NULL';
            if (strpos($col, 'amount') !== false || strpos($col, 'total') !== false) $type = 'DECIMAL(10,2) DEFAULT 0';
            if (strpos($col, 'password') !== false) $type = 'VARCHAR(255)';
            $sql = "ALTER TABLE `$table` ADD COLUMN `$col` $type;";
            $actions[] = $sql;
            if ($pdo) {
                try {
                    $pdo->exec($sql);
                    echo "Dodano kolumnę $col do $table\n";
                } catch (Exception $e) {
                    echo "Błąd przy dodawaniu $col do $table: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    // Napraw uprawnienia
    if (preg_match('/✗ Incorrect permissions \((\d+)\) for (.+)/', $line, $m)) {
        $perm = octdec($m[1]);
        $path = __DIR__ . '/' . trim($m[2]);
        $is_dir = is_dir($path);
        $target_perm = $is_dir ? 0755 : 0644;
        if (fix_permissions($path, $target_perm, $is_dir)) {
            echo "Naprawiono uprawnienia dla $path na " . decoct($target_perm) . "\n";
        } else {
            echo "Nie udało się naprawić uprawnień dla $path\n";
        }
    }
}

echo "\nWszystkie możliwe naprawy zostały wykonane. Sprawdź ponownie debug_report!\n";
?>
