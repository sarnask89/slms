<?php
/**
 * SLMS Localization Helper
 * 
 * Provides internationalization (i18n) support for the SLMS system.
 * Supports multiple languages with fallback to English.
 */

class Localization
{
    private static $instance = null;
    private $translations = [];
    private $currentLanguage = 'en';
    private $fallbackLanguage = 'en';
    private $localePath;
    private $loadedDomains = [];

    private function __construct()
    {
        $this->localePath = __DIR__ . '/../locale';
        $this->currentLanguage = $this->detectLanguage();
        $this->loadTranslations();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Detect user's preferred language
     */
    private function detectLanguage()
    {
        // Check session first
        if (isset($_SESSION['language'])) {
            return $_SESSION['language'];
        }

        // Check browser language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($browserLanguages as $lang) {
                $lang = trim(explode(';', $lang)[0]);
                if (in_array($lang, ['pl', 'pl-PL', 'pl_PL'])) {
                    return 'pl';
                }
            }
        }

        // Default to English
        return 'en';
    }

    /**
     * Set current language
     */
    public function setLanguage($language)
    {
        $this->currentLanguage = $language;
        $_SESSION['language'] = $language;
        $this->loadTranslations();
    }

    /**
     * Get current language
     */
    public function getLanguage()
    {
        return $this->currentLanguage;
    }

    /**
     * Get available languages
     */
    public function getAvailableLanguages()
    {
        return [
            'en' => 'English',
            'pl' => 'Polski'
        ];
    }

    /**
     * Load translations for current language
     */
    private function loadTranslations()
    {
        $this->translations = [];
        $this->loadedDomains = [];

        // Load main SLMS translations
        $this->loadDomain('slms');
    }

    /**
     * Load translations for a specific domain
     */
    private function loadDomain($domain)
    {
        if (in_array($domain, $this->loadedDomains)) {
            return;
        }

        $poFile = $this->localePath . '/' . $this->currentLanguage . '/LC_MESSAGES/' . $domain . '.po';
        $moFile = $this->localePath . '/' . $this->currentLanguage . '/LC_MESSAGES/' . $domain . '.mo';

        // Try to load compiled .mo file first
        if (file_exists($moFile)) {
            $this->loadMoFile($moFile, $domain);
        }
        // Fallback to .po file
        elseif (file_exists($poFile)) {
            $this->loadPoFile($poFile, $domain);
        }

        $this->loadedDomains[] = $domain;
    }

    /**
     * Load translations from .mo file
     */
    private function loadMoFile($file, $domain)
    {
        // Simple .mo file parser
        $content = file_get_contents($file);
        if ($content === false) {
            return;
        }

        // Parse .mo file structure
        $this->parseMoFile($content, $domain);
    }

    /**
     * Load translations from .po file
     */
    private function loadPoFile($file, $domain)
    {
        $content = file_get_contents($file);
        if ($content === false) {
            return;
        }

        // Parse .po file
        $this->parsePoFile($content, $domain);
    }

    /**
     * Parse .mo file content
     */
    private function parseMoFile($content, $domain)
    {
        // This is a simplified .mo parser
        // In production, you might want to use a proper gettext library
        
        $translations = [];
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
                    $translations[$msgid] = $msgstr;
                }
                
                $msgid = null;
                $msgstr = null;
            }
        }
        
        $this->translations[$domain] = $translations;
    }

    /**
     * Parse .po file content
     */
    private function parsePoFile($content, $domain)
    {
        $translations = [];
        $lines = explode("\n", $content);
        
        $msgid = null;
        $msgstr = null;
        $inMsgid = false;
        $inMsgstr = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            if (strpos($line, 'msgid "') === 0) {
                $msgid = substr($line, 7, -1);
                $inMsgid = true;
                $inMsgstr = false;
            } elseif (strpos($line, 'msgstr "') === 0) {
                $msgstr = substr($line, 8, -1);
                $inMsgid = false;
                $inMsgstr = true;
            } elseif ($inMsgid && strpos($line, '"') === 0) {
                $msgid .= substr($line, 1, -1);
            } elseif ($inMsgstr && strpos($line, '"') === 0) {
                $msgstr .= substr($line, 1, -1);
            } elseif (empty($line)) {
                // End of message
                if ($msgid !== null && $msgstr !== null && $msgid !== '') {
                    $translations[$msgid] = $msgstr;
                }
                $msgid = null;
                $msgstr = null;
                $inMsgid = false;
                $inMsgstr = false;
            }
        }
        
        // Handle last message
        if ($msgid !== null && $msgstr !== null && $msgid !== '') {
            $translations[$msgid] = $msgstr;
        }
        
        $this->translations[$domain] = $translations;
    }

    /**
     * Translate a string
     */
    public function translate($string, $domain = 'slms', $params = [])
    {
        // Load domain if not loaded
        if (!isset($this->translations[$domain])) {
            $this->loadDomain($domain);
        }

        // Get translation
        $translated = $string;
        if (isset($this->translations[$domain][$string])) {
            $translated = $this->translations[$domain][$string];
        }

        // Replace parameters
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $translated = str_replace('{' . $key . '}', $value, $translated);
            }
        }

        return $translated;
    }

    /**
     * Alias for translate method
     */
    public function t($string, $domain = 'slms', $params = [])
    {
        return $this->translate($string, $domain, $params);
    }

    /**
     * Get plural form
     */
    public function ngettext($singular, $plural, $count, $domain = 'slms')
    {
        $string = $count == 1 ? $singular : $plural;
        return $this->translate($string, $domain, ['count' => $count]);
    }

    /**
     * Format date according to current locale
     */
    public function formatDate($date, $format = null)
    {
        if ($format === null) {
            $format = $this->currentLanguage === 'pl' ? 'd.m.Y' : 'Y-m-d';
        }

        if (is_string($date)) {
            $date = new DateTime($date);
        }

        return $date->format($format);
    }

    /**
     * Format time according to current locale
     */
    public function formatTime($time, $format = null)
    {
        if ($format === null) {
            $format = $this->currentLanguage === 'pl' ? 'H:i:s' : 'H:i:s';
        }

        if (is_string($time)) {
            $time = new DateTime($time);
        }

        return $time->format($format);
    }

    /**
     * Format datetime according to current locale
     */
    public function formatDateTime($datetime, $format = null)
    {
        if ($format === null) {
            $format = $this->currentLanguage === 'pl' ? 'd.m.Y H:i:s' : 'Y-m-d H:i:s';
        }

        if (is_string($datetime)) {
            $datetime = new DateTime($datetime);
        }

        return $datetime->format($format);
    }

    /**
     * Format number according to current locale
     */
    public function formatNumber($number, $decimals = 2)
    {
        if ($this->currentLanguage === 'pl') {
            return number_format($number, $decimals, ',', ' ');
        } else {
            return number_format($number, $decimals, '.', ',');
        }
    }

    /**
     * Format currency according to current locale
     */
    public function formatCurrency($amount, $currency = 'PLN')
    {
        $formatted = $this->formatNumber($amount, 2);
        
        if ($this->currentLanguage === 'pl') {
            return $formatted . ' ' . $currency;
        } else {
            return $currency . ' ' . $formatted;
        }
    }

    /**
     * Get month name
     */
    public function getMonthName($month)
    {
        $months = [
            'en' => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ],
            'pl' => [
                1 => 'Styczeń', 2 => 'Luty', 3 => 'Marzec', 4 => 'Kwiecień',
                5 => 'Maj', 6 => 'Czerwiec', 7 => 'Lipiec', 8 => 'Sierpień',
                9 => 'Wrzesień', 10 => 'Październik', 11 => 'Listopad', 12 => 'Grudzień'
            ]
        ];

        return $months[$this->currentLanguage][$month] ?? $months['en'][$month];
    }

    /**
     * Get day name
     */
    public function getDayName($day)
    {
        $days = [
            'en' => [
                1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
                5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
            ],
            'pl' => [
                1 => 'Poniedziałek', 2 => 'Wtorek', 3 => 'Środa', 4 => 'Czwartek',
                5 => 'Piątek', 6 => 'Sobota', 7 => 'Niedziela'
            ]
        ];

        return $days[$this->currentLanguage][$day] ?? $days['en'][$day];
    }

    /**
     * Generate language selector HTML
     */
    public function getLanguageSelector()
    {
        $html = '<div class="language-selector">';
        $html .= '<select onchange="changeLanguage(this.value)">';
        
        foreach ($this->getAvailableLanguages() as $code => $name) {
            $selected = $code === $this->currentLanguage ? ' selected' : '';
            $html .= '<option value="' . $code . '"' . $selected . '>' . $name . '</option>';
        }
        
        $html .= '</select>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Get translation statistics
     */
    public function getTranslationStats($domain = 'slms')
    {
        if (!isset($this->translations[$domain])) {
            return ['total' => 0, 'translated' => 0, 'percentage' => 0];
        }

        $total = count($this->translations[$domain]);
        $translated = 0;

        foreach ($this->translations[$domain] as $original => $translation) {
            if (!empty($translation) && $translation !== $original) {
                $translated++;
            }
        }

        $percentage = $total > 0 ? round(($translated / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'translated' => $translated,
            'percentage' => $percentage
        ];
    }
}

// Global function for easy translation
function __($string, $domain = 'slms', $params = [])
{
    return Localization::getInstance()->translate($string, $domain, $params);
}

// Global function for plural forms
function _n($singular, $plural, $count, $domain = 'slms')
{
    return Localization::getInstance()->ngettext($singular, $plural, $count, $domain);
}

// Global function for date formatting
function format_date($date, $format = null)
{
    return Localization::getInstance()->formatDate($date, $format);
}

// Global function for number formatting
function format_number($number, $decimals = 2)
{
    return Localization::getInstance()->formatNumber($number, $decimals);
}

// Global function for currency formatting
function format_currency($amount, $currency = 'PLN')
{
    return Localization::getInstance()->formatCurrency($amount, $currency);
} 