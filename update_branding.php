#!/usr/bin/env php
<?php
/**
 * Branding Update Script
 * Updates all references from sLMS to AI SERVICE NETWORK MANAGEMENT SYSTEM
 */

echo "=== Branding Update Script ===\n";
echo "Updating from 'sLMS' to 'AI SERVICE NETWORK MANAGEMENT SYSTEM'\n\n";

// Define replacements
$replacements = [
    // Main branding
    'sLMS System' => 'AI SERVICE NETWORK MANAGEMENT SYSTEM',
    'sLMS system' => 'AI SERVICE NETWORK MANAGEMENT SYSTEM',
    'SLMS System' => 'AI SERVICE NETWORK MANAGEMENT SYSTEM',
    ' - sLMS' => ' - AI SERVICE NETWORK MANAGEMENT SYSTEM',
    'sLMS ' => 'AI SERVICE NETWORK MANAGEMENT SYSTEM ',
    ' sLMS' => ' AI SERVICE NETWORK MANAGEMENT SYSTEM',
    'sLMS' => 'AI SERVICE NETWORK MANAGEMENT SYSTEM',
    'SLMS' => 'AI SERVICE NETWORK MANAGEMENT SYSTEM',
    
    // Full names
    'System Local Management System' => 'AI SERVICE NETWORK MANAGEMENT SYSTEM',
    'Service Level Management System' => 'AI SERVICE NETWORK MANAGEMENT SYSTEM',
    
    // Comments and documentation
    'for sLMS' => 'for AI SERVICE NETWORK MANAGEMENT SYSTEM',
    'of sLMS' => 'of AI SERVICE NETWORK MANAGEMENT SYSTEM',
    'to sLMS' => 'to AI SERVICE NETWORK MANAGEMENT SYSTEM',
    'in sLMS' => 'in AI SERVICE NETWORK MANAGEMENT SYSTEM',
    
    // Window class names (preserve functionality)
    'slmsTooltips' => 'aiServiceTooltips',
    'SLMSTooltipSystem' => 'AIServiceTooltipSystem',
];

// Files/patterns to exclude
$excludePatterns = [
    '/vendor/',
    '/node_modules/',
    '/.git/',
    '/cache/',
    '/logs/',
    '.jpg',
    '.png',
    '.gif',
    '.ico',
    '.svg',
    'update_branding.php', // Don't update this script itself
];

// Database-related strings to preserve (for compatibility)
$preserveStrings = [
    'slmsdb',
    "'slms'@'localhost'",
    '"slms"@"localhost"',
    'slms@localhost',
    '/slms/', // URL paths
    'github.com/sarnask89/slms', // Git URLs
];

// Function to check if file should be excluded
function shouldExclude($filepath, $excludePatterns) {
    foreach ($excludePatterns as $pattern) {
        if (strpos($filepath, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

// Function to check if content contains preserved strings
function containsPreservedString($content, $preserveStrings) {
    foreach ($preserveStrings as $string) {
        if (strpos($content, $string) !== false) {
            return true;
        }
    }
    return false;
}

// Function to update file content
function updateFileContent($filepath, $replacements, $preserveStrings) {
    $content = file_get_contents($filepath);
    $originalContent = $content;
    
    // Apply replacements
    foreach ($replacements as $old => $new) {
        // Skip if this would affect preserved strings
        $testContent = str_replace($old, $new, $content);
        $skipReplacement = false;
        
        // Check if replacement would affect preserved strings
        foreach ($preserveStrings as $preserved) {
            if (strpos($content, $preserved) !== false && 
                strpos($testContent, $preserved) === false) {
                $skipReplacement = true;
                break;
            }
        }
        
        if (!$skipReplacement) {
            $content = $testContent;
        }
    }
    
    // Only write if content changed
    if ($content !== $originalContent) {
        file_put_contents($filepath, $content);
        return true;
    }
    
    return false;
}

// Get all PHP and documentation files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__),
    RecursiveIteratorIterator::SELF_FIRST
);

$updatedFiles = [];
$skippedFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $filepath = $file->getPathname();
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        
        // Check if file should be processed
        if (in_array($extension, ['php', 'md', 'txt', 'html', 'js', 'css', 'yml', 'yaml', 'json'])) {
            if (!shouldExclude($filepath, $excludePatterns)) {
                if (updateFileContent($filepath, $replacements, $preserveStrings)) {
                    $updatedFiles[] = $filepath;
                    echo "✓ Updated: " . basename($filepath) . "\n";
                }
            } else {
                $skippedFiles[] = $filepath;
            }
        }
    }
}

echo "\n=== Update Summary ===\n";
echo "Total files updated: " . count($updatedFiles) . "\n";
echo "Files skipped: " . count($skippedFiles) . "\n";

if (count($updatedFiles) > 0) {
    echo "\nUpdated files:\n";
    foreach ($updatedFiles as $file) {
        echo "  - " . str_replace(__DIR__ . '/', '', $file) . "\n";
    }
}

echo "\n✅ Branding update complete!\n";
echo "\nNote: Database names and certain paths were preserved for compatibility.\n";
echo "Please manually review and test the application after these changes.\n";