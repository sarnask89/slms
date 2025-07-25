<?php
/**
 * sLMS Layout System Fix Script
 * Automatically fixes module files to use the proper layout system
 */

echo "================================================\n";
echo "           sLMS Layout System Fix\n";
echo "================================================\n\n";

// Get all PHP files in modules directory
$modules_dir = 'modules/';
$files = glob($modules_dir . '*.php');

$fixed_count = 0;
$skipped_count = 0;

foreach ($files as $file) {
    echo "Processing: $file\n";
    
    $content = file_get_contents($file);
    $original_content = $content;
    
    // Skip files that already use the layout system properly
    if (strpos($content, '$content = ob_get_clean();') !== false) {
        echo "  ✓ Already uses layout system - skipping\n";
        $skipped_count++;
        continue;
    }
    
    // Skip files that are just empty or have minimal content
    if (strlen(trim($content)) < 50) {
        echo "  ⚠ Empty or minimal file - skipping\n";
        $skipped_count++;
        continue;
    }
    
    // Check if file has HTML content
    if (strpos($content, '<!DOCTYPE html>') !== false || 
        strpos($content, '<html') !== false ||
        strpos($content, '<body') !== false) {
        
        echo "  ✗ Contains HTML - needs manual fix\n";
        $skipped_count++;
        continue;
    }
    
    // Check if file has PHP content that could be converted
    if (strpos($content, '<?php') !== false && 
        (strpos($content, 'echo') !== false || 
         strpos($content, 'print') !== false ||
         strpos($content, '<?=') !== false)) {
        
        // Add session_start and requires at the beginning
        $new_content = "<?php\n";
        $new_content .= "session_start();\n";
        $new_content .= "require_once __DIR__ . '/../config.php';\n";
        $new_content .= "require_once __DIR__ . '/../modules/helpers/auth_helper.php';\n\n";
        $new_content .= "// Require login\n";
        $new_content .= "require_login();\n\n";
        
        // Extract page title if it exists
        $page_title = 'Module';
        if (preg_match('/\$pageTitle\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            $page_title = $matches[1];
        } else {
            // Try to extract from filename
            $filename = basename($file, '.php');
            $page_title = ucwords(str_replace('_', ' ', $filename));
        }
        
        $new_content .= "\$pageTitle = '$page_title';\n";
        $new_content .= "ob_start();\n";
        $new_content .= "?>\n\n";
        
        // Add the original content (remove the opening <?php if it exists)
        $content = preg_replace('/^<\?php\s*/', '', $content);
        $new_content .= $content;
        
        // Add the closing pattern
        $new_content .= "\n\n<?php\n";
        $new_content .= "\$content = ob_get_clean();\n";
        $new_content .= "require_once __DIR__ . '/../partials/layout.php';\n";
        $new_content .= "?>\n";
        
        // Write the fixed content
        if (file_put_contents($file, $new_content)) {
            echo "  ✓ Fixed layout system\n";
            $fixed_count++;
        } else {
            echo "  ✗ Failed to write file\n";
        }
    } else {
        echo "  ⚠ No content to convert - skipping\n";
        $skipped_count++;
    }
}

echo "\n================================================\n";
echo "Fix Summary:\n";
echo "  Fixed: $fixed_count files\n";
echo "  Skipped: $skipped_count files\n";
echo "================================================\n";

// Now let's also fix some specific files that need manual attention
echo "\nFixing specific files that need manual attention...\n";

// Fix activity_log.php specifically
$activity_log_file = 'modules/activity_log.php';
if (file_exists($activity_log_file)) {
    $content = file_get_contents($activity_log_file);
    
    // Check if it already uses the layout system
    if (strpos($content, '$content = ob_get_clean();') === false) {
        echo "Fixing activity_log.php...\n";
        
        // This file is complex, so we'll create a simple wrapper
        $new_content = "<?php\n";
        $new_content .= "session_start();\n";
        $new_content .= "require_once __DIR__ . '/../config.php';\n";
        $new_content .= "require_once __DIR__ . '/../modules/helpers/auth_helper.php';\n\n";
        $new_content .= "// Require admin access\n";
        $new_content .= "require_admin();\n\n";
        $new_content .= "\$pageTitle = 'Dziennik aktywności';\n";
        $new_content .= "ob_start();\n\n";
        
        // Include the original file content
        $new_content .= "// Include the original activity log content\n";
        $new_content .= "include __DIR__ . '/activity_log_content.php';\n\n";
        
        $new_content .= "\$content = ob_get_clean();\n";
        $new_content .= "require_once __DIR__ . '/../partials/layout.php';\n";
        $new_content .= "?>\n";
        
        // Save the original content to a new file
        file_put_contents('modules/activity_log_content.php', $content);
        
        // Write the new wrapper
        if (file_put_contents($activity_log_file, $new_content)) {
            echo "  ✓ Fixed activity_log.php\n";
            $fixed_count++;
        }
    }
}

echo "\n================================================\n";
echo "Layout system fix completed!\n";
echo "================================================\n";
?> 