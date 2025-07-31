<?php
/**
 * SLMS Translation Helper Script
 * 
 * This script helps with the Polish localization process by:
 * - Extracting translatable strings from PHP files
 * - Comparing existing translations
 * - Generating translation reports
 * - Creating translation templates
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../helpers/localization.php';

class TranslationHelper
{
    private $sourceDir;
    private $localeDir;
    private $extractedStrings = [];
    private $existingTranslations = [];

    public function __construct()
    {
        $this->sourceDir = __DIR__ . '/../modules';
        $this->localeDir = __DIR__ . '/../locale';
    }

    /**
     * Extract translatable strings from PHP files
     */
    public function extractStrings($directory = null)
    {
        if ($directory === null) {
            $directory = $this->sourceDir;
        }

        $files = $this->scanDirectory($directory, 'php');
        
        foreach ($files as $file) {
            $this->extractFromFile($file);
        }

        return $this->extractedStrings;
    }

    /**
     * Scan directory for files with specific extension
     */
    private function scanDirectory($dir, $extension)
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === $extension) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Extract translatable strings from a single file
     */
    private function extractFromFile($file)
    {
        $content = file_get_contents($file);
        if ($content === false) {
            return;
        }

        // Extract strings from various patterns
        $patterns = [
            // Page titles
            '/\$pageTitle\s*=\s*[\'"]([^\'"]+)[\'"]/',
            // Echo statements
            '/echo\s+[\'"]([^\'"]+)[\'"]/',
            // HTML content
            '/<label[^>]*>([^<]+)<\/label>/',
            '/<h[1-6][^>]*>([^<]+)<\/h[1-6]>/',
            '/<th[^>]*>([^<]+)<\/th>/',
            '/<td[^>]*>([^<]+)<\/td>/',
            '/<button[^>]*>([^<]+)<\/button>/',
            '/<a[^>]*>([^<]+)<\/a>/',
            // Form elements
            '/placeholder=[\'"]([^\'"]+)[\'"]/',
            '/title=[\'"]([^\'"]+)[\'"]/',
            // JavaScript strings
            '/alert\([\'"]([^\'"]+)[\'"]/',
            '/confirm\([\'"]([^\'"]+)[\'"]/',
            // PHP strings
            '/[\'"]([A-Z][a-z\s]+)[\'"]/', // Capitalized strings
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $match) {
                    $match = trim($match);
                    if (strlen($match) > 2 && !is_numeric($match)) {
                        $this->extractedStrings[$match] = [
                            'file' => $file,
                            'context' => $this->getContext($content, $match)
                        ];
                    }
                }
            }
        }
    }

    /**
     * Get context around a string
     */
    private function getContext($content, $string, $contextLength = 100)
    {
        $pos = strpos($content, $string);
        if ($pos === false) {
            return '';
        }

        $start = max(0, $pos - $contextLength);
        $end = min(strlen($content), $pos + strlen($string) + $contextLength);
        
        return substr($content, $start, $end - $start);
    }

    /**
     * Load existing translations
     */
    public function loadExistingTranslations()
    {
        $poFile = $this->localeDir . '/pl/LC_MESSAGES/slms.po';
        
        if (file_exists($poFile)) {
            $content = file_get_contents($poFile);
            $this->parsePoFile($content);
        }
    }

    /**
     * Parse PO file content
     */
    private function parsePoFile($content)
    {
        $lines = explode("\n", $content);
        $msgid = null;
        $msgstr = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (strpos($line, 'msgid "') === 0) {
                $msgid = substr($line, 7, -1);
            } elseif (strpos($line, 'msgstr "') === 0) {
                $msgstr = substr($line, 8, -1);
                
                if ($msgid !== null && $msgstr !== null && $msgid !== '') {
                    $this->existingTranslations[$msgid] = $msgstr;
                }
                
                $msgid = null;
                $msgstr = null;
            }
        }
    }

    /**
     * Generate translation report
     */
    public function generateReport()
    {
        $this->loadExistingTranslations();
        
        $report = [
            'total_strings' => count($this->extractedStrings),
            'translated_strings' => 0,
            'untranslated_strings' => [],
            'translation_coverage' => 0,
            'by_module' => []
        ];

        foreach ($this->extractedStrings as $string => $info) {
            if (isset($this->existingTranslations[$string])) {
                $report['translated_strings']++;
            } else {
                $report['untranslated_strings'][] = [
                    'string' => $string,
                    'file' => $info['file'],
                    'context' => $info['context']
                ];
            }

            // Group by module
            $module = basename(dirname($info['file']));
            if (!isset($report['by_module'][$module])) {
                $report['by_module'][$module] = [
                    'total' => 0,
                    'translated' => 0
                ];
            }
            
            $report['by_module'][$module]['total']++;
            if (isset($this->existingTranslations[$string])) {
                $report['by_module'][$module]['translated']++;
            }
        }

        if ($report['total_strings'] > 0) {
            $report['translation_coverage'] = round(
                ($report['translated_strings'] / $report['total_strings']) * 100, 
                2
            );
        }

        return $report;
    }

    /**
     * Generate missing translations template
     */
    public function generateMissingTranslationsTemplate()
    {
        $this->loadExistingTranslations();
        $missing = [];

        foreach ($this->extractedStrings as $string => $info) {
            if (!isset($this->existingTranslations[$string])) {
                $missing[] = $string;
            }
        }

        $template = "# Missing translations for SLMS\n";
        $template .= "# Generated on: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($missing as $string) {
            $template .= "msgid \"" . addslashes($string) . "\"\n";
            $template .= "msgstr \"\"\n\n";
        }

        return $template;
    }

    /**
     * Generate translation statistics
     */
    public function generateStatistics()
    {
        $report = $this->generateReport();
        
        echo "=== SLMS Translation Statistics ===\n\n";
        echo "Total translatable strings: " . $report['total_strings'] . "\n";
        echo "Translated strings: " . $report['translated_strings'] . "\n";
        echo "Translation coverage: " . $report['translation_coverage'] . "%\n\n";
        
        echo "=== By Module ===\n";
        foreach ($report['by_module'] as $module => $stats) {
            $coverage = $stats['total'] > 0 ? round(($stats['translated'] / $stats['total']) * 100, 2) : 0;
            echo sprintf("%-20s: %d/%d (%s%%)\n", 
                $module, 
                $stats['translated'], 
                $stats['total'], 
                $coverage
            );
        }
        
        echo "\n=== Missing Translations ===\n";
        foreach ($report['untranslated_strings'] as $item) {
            echo "File: " . $item['file'] . "\n";
            echo "String: " . $item['string'] . "\n";
            echo "Context: " . substr($item['context'], 0, 100) . "...\n";
            echo "---\n";
        }
    }

    /**
     * Create translation worklist
     */
    public function createWorklist()
    {
        $report = $this->generateReport();
        $worklist = [];

        foreach ($report['untranslated_strings'] as $item) {
            $module = basename(dirname($item['file']));
            if (!isset($worklist[$module])) {
                $worklist[$module] = [];
            }
            $worklist[$module][] = $item;
        }

        return $worklist;
    }

    /**
     * Export worklist to CSV
     */
    public function exportWorklistToCSV($filename = null)
    {
        if ($filename === null) {
            $filename = 'translation_worklist_' . date('Y-m-d') . '.csv';
        }

        $worklist = $this->createWorklist();
        
        $csv = "Module,File,String,Context\n";
        
        foreach ($worklist as $module => $items) {
            foreach ($items as $item) {
                $csv .= sprintf('"%s","%s","%s","%s"' . "\n",
                    $module,
                    basename($item['file']),
                    str_replace('"', '""', $item['string']),
                    str_replace('"', '""', substr($item['context'], 0, 200))
                );
            }
        }

        file_put_contents($filename, $csv);
        return $filename;
    }
}

// CLI interface
if (php_sapi_name() === 'cli') {
    $helper = new TranslationHelper();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'extract':
                $strings = $helper->extractStrings();
                echo "Extracted " . count($strings) . " translatable strings\n";
                break;
                
            case 'report':
                $helper->generateStatistics();
                break;
                
            case 'template':
                $template = $helper->generateMissingTranslationsTemplate();
                file_put_contents('missing_translations.po', $template);
                echo "Generated missing_translations.po\n";
                break;
                
            case 'csv':
                $filename = $helper->exportWorklistToCSV();
                echo "Exported worklist to $filename\n";
                break;
                
            case 'help':
            default:
                echo "SLMS Translation Helper\n\n";
                echo "Usage: php translation_helper.php <command>\n\n";
                echo "Commands:\n";
                echo "  extract  - Extract translatable strings from PHP files\n";
                echo "  report   - Generate translation statistics report\n";
                echo "  template - Generate missing translations template\n";
                echo "  csv      - Export worklist to CSV file\n";
                echo "  help     - Show this help message\n";
                break;
        }
    } else {
        echo "Please specify a command. Use 'help' for available commands.\n";
    }
}
?> 