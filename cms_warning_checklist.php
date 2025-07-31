<?php
// Skrypt generujący checklistę i sugestie poprawek dla ostrzeżeń z debug_report

$report = file_get_contents(__DIR__ . '/cms_debug_report.txt');
$lines = explode("\n", $report);

$checklist = [];
foreach ($lines as $line) {
    if (preg_match('/! (.+) in (.+)/', $line, $m)) {
        $desc = trim($m[1]);
        $file = trim($m[2]);
        $suggestion = '';
        if (stripos($desc, 'Unsafe session handling') !== false) {
            $suggestion = 'Dodaj sprawdzanie czy sesja jest już uruchomiona (session_status()), rozważ zabezpieczenia przed fixation.';
        } elseif (stripos($desc, 'Missing request method check') !== false) {
            $suggestion = 'Dodaj sprawdzanie $_SERVER["REQUEST_METHOD"] (np. if ($_SERVER["REQUEST_METHOD"] === "POST")) na początku pliku.';
        } elseif (stripos($desc, 'Missing request method check') !== false) {
            $suggestion = 'Dodaj sprawdzanie metody żądania (GET/POST) na początku pliku.';
        } else {
            $suggestion = 'Przejrzyj kod i popraw zgodnie z dobrymi praktykami bezpieczeństwa.';
        }
        $checklist[] = [
            'file' => $file,
            'desc' => $desc,
            'suggestion' => $suggestion
        ];
    }
}

if (empty($checklist)) {
    echo "Brak ostrzeżeń wymagających interwencji!\n";
    exit;
}

foreach ($checklist as $item) {
    echo "Plik: {$item['file']}\n";
    echo "Ostrzeżenie: {$item['desc']}\n";
    echo "Sugestia: {$item['suggestion']}\n";
    echo str_repeat('-', 40) . "\n";
}
?>
