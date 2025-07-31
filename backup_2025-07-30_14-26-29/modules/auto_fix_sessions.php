<?php
// auto_fix_sessions.php
// Recursively scans PHP files and inserts session_status() check before session_start() if missing.

$root = __DIR__;
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

$fixed = 0;
$skipped = 0;

foreach ($rii as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
        $path = $file->getRealPath();
        $code = file_get_contents($path);
        // Skip if already protected
        if (preg_match('/session_status\s*\(\s*\)\s*===\s*PHP_SESSION_NONE\s*\)\s*{\s*session_start\s*\(/s', $code)) {
            $skipped++;
            continue;
        }
        // Find unprotected session_start()
        $pattern = '/(^|[^\w])session_start\s*\(\s*\)\s*;/m';
        if (preg_match_all($pattern, $code, $matches, PREG_OFFSET_CAPTURE)) {
            $offset = 0;
            foreach ($matches[0] as $match) {
                $pos = $match[1] + $offset;
                // Insert protection before session_start()
                $protect = "if (session_status() === PHP_SESSION_NONE) {\n   if (session_status() === PHP_SESSION_NONE) {
    session_start();
}\n}";
                // Remove the originalif (session_status() === PHP_SESSION_NONE) {
    session_start();
}
                $code = substr_replace($code, $protect, $pos, strlen($match[0]));
                $offset += strlen($protect) - strlen($match[0]);
            }
            file_put_contents($path, $code);
            echo "[FIXED] $path\n";
            $fixed++;
        } else {
            $skipped++;
        }
    }
}
echo "\nDone. $fixed files fixed, $skipped skipped (already safe or no session_start).\n";
