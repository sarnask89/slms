<?php
/**
 * Adaptive AI API
 * Handles GUI modifications and user behavior analysis
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/modules/config.php';

class AdaptiveAIAPI {
    private $pdo;
    private $config;
    
    public function __construct() {
        try {
            $this->pdo = get_pdo();
            $this->config = [
                'learning_enabled' => true,
                'auto_modify' => true,
                'max_modifications' => 50
            ];
            $this->createTables();
        } catch (Exception $e) {
            $this->sendError('Database connection failed: ' . $e->getMessage());
        }
    }
    
    private function createTables() {
        // User behavior tracking
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS user_behavior (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255),
                action_type VARCHAR(50),
                element_id VARCHAR(255),
                element_type VARCHAR(50),
                coordinates JSON,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                page_url VARCHAR(500),
                user_agent TEXT
            )
        ");
        
        // GUI modifications
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS gui_modifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                modification_type VARCHAR(100),
                target_element VARCHAR(255),
                modification_data JSON,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                session_id VARCHAR(255),
                user_feedback INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE
            )
        ");
        
        // Learning patterns
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS learning_patterns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                pattern_type VARCHAR(100),
                pattern_data JSON,
                confidence FLOAT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                frequency INT DEFAULT 1
            )
        ");
        
        // User preferences
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS user_preferences (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255),
                preference_key VARCHAR(100),
                preference_value JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // For GET requests, get action from URL
        if ($method === 'GET') {
            $action = $_GET['action'] ?? '';
        } else {
            // For POST requests, get action from JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
        }
        
        try {
            switch ($action) {
                case 'track_behavior':
                    $this->handleTrackBehavior();
                    break;
                case 'apply_modification':
                    $this->handleApplyModification();
                    break;
                case 'get_modifications':
                    $this->handleGetModifications();
                    break;
                case 'analyze_patterns':
                    $this->handleAnalyzePatterns();
                    break;
                case 'suggest_improvements':
                    $this->handleSuggestImprovements();
                    break;
                case 'save_preference':
                    $this->handleSavePreference();
                    break;
                case 'get_preferences':
                    $this->handleGetPreferences();
                    break;
                case 'remove_modification':
                    $this->handleRemoveModification();
                    break;
                case 'reset_modifications':
                    $this->handleResetModifications();
                    break;
                default:
                    $this->sendError('Invalid action');
            }
        } catch (Exception $e) {
            $this->sendError('API Error: ' . $e->getMessage());
        }
    }
    
    private function handleTrackBehavior() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action_type'])) {
            $this->sendError('Action type is required');
        }
        
        $sessionId = $input['session_id'] ?? session_id();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO user_behavior 
            (session_id, action_type, element_id, element_type, coordinates, page_url, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $sessionId,
            $input['action_type'],
            $input['element_id'] ?? null,
            $input['element_type'] ?? null,
            json_encode($input['coordinates'] ?? []),
            $input['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? '',
            $input['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        // Analyze for patterns
        $this->analyzeBehaviorPatterns($sessionId);
        
        $this->sendSuccess(['tracked' => true]);
    }
    
    private function handleApplyModification() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['modification_type'])) {
            $this->sendError('Modification type is required');
        }
        
        $sessionId = $input['session_id'] ?? session_id();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO gui_modifications 
            (modification_type, target_element, modification_data, session_id)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $input['modification_type'],
            $input['target_element'] ?? null,
            json_encode($input['modification_data'] ?? []),
            $sessionId
        ]);
        
        $modificationId = $this->pdo->lastInsertId();
        
        // Generate modification code
        $modificationCode = $this->generateModificationCode($input);
        
        $this->sendSuccess([
            'modification_id' => $modificationId,
            'modification_code' => $modificationCode,
            'applied' => true
        ]);
    }
    
    private function handleGetModifications() {
        $sessionId = $_GET['session_id'] ?? session_id();
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM gui_modifications 
            WHERE session_id = ? AND is_active = TRUE 
            ORDER BY applied_at DESC
        ");
        $stmt->execute([$sessionId]);
        $modifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendSuccess(['modifications' => $modifications]);
    }
    
    private function handleAnalyzePatterns() {
        $sessionId = $_GET['session_id'] ?? session_id();
        
        // Analyze recent behavior
        $stmt = $this->pdo->prepare("
            SELECT action_type, element_type, COUNT(*) as frequency
            FROM user_behavior 
            WHERE session_id = ? 
            AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            GROUP BY action_type, element_type
            ORDER BY frequency DESC
        ");
        $stmt->execute([$sessionId]);
        $patterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Detect frustration patterns
        $frustrationPatterns = $this->detectFrustrationPatterns($sessionId);
        
        // Detect efficiency opportunities
        $efficiencyOpportunities = $this->detectEfficiencyOpportunities($sessionId);
        
        $this->sendSuccess([
            'patterns' => $patterns,
            'frustration_patterns' => $frustrationPatterns,
            'efficiency_opportunities' => $efficiencyOpportunities
        ]);
    }
    
    private function handleSuggestImprovements() {
        $sessionId = $_GET['session_id'] ?? session_id();
        
        $suggestions = [];
        
        // Analyze behavior for suggestions
        $recentBehavior = $this->getRecentBehavior($sessionId);
        
        // Check for repetitive actions
        $repetitiveActions = $this->findRepetitiveActions($recentBehavior);
        if (!empty($repetitiveActions)) {
            $suggestions[] = [
                'type' => 'shortcut',
                'title' => 'Add Keyboard Shortcuts',
                'description' => 'I noticed you perform the same actions frequently. Would you like keyboard shortcuts?',
                'priority' => 'high'
            ];
        }
        
        // Check for navigation issues
        $navigationIssues = $this->findNavigationIssues($recentBehavior);
        if (!empty($navigationIssues)) {
            $suggestions[] = [
                'type' => 'navigation',
                'title' => 'Improve Navigation',
                'description' => 'I see you navigate between pages frequently. Should I add a navigation menu?',
                'priority' => 'medium'
            ];
        }
        
        // Check for accessibility issues
        $accessibilityIssues = $this->findAccessibilityIssues($recentBehavior);
        if (!empty($accessibilityIssues)) {
            $suggestions[] = [
                'type' => 'accessibility',
                'title' => 'Improve Accessibility',
                'description' => 'I detected some accessibility issues. Should I make the interface more accessible?',
                'priority' => 'high'
            ];
        }
        
        $this->sendSuccess(['suggestions' => $suggestions]);
    }
    
    private function handleSavePreference() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['key']) || !isset($input['value'])) {
            $this->sendError('Preference key and value are required');
        }
        
        $sessionId = $input['session_id'] ?? session_id();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO user_preferences (session_id, preference_key, preference_value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            preference_value = VALUES(preference_value),
            updated_at = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([
            $sessionId,
            $input['key'],
            json_encode($input['value'])
        ]);
        
        $this->sendSuccess(['saved' => true]);
    }
    
    private function handleGetPreferences() {
        $sessionId = $_GET['session_id'] ?? session_id();
        
        $stmt = $this->pdo->prepare("
            SELECT preference_key, preference_value 
            FROM user_preferences 
            WHERE session_id = ?
        ");
        $stmt->execute([$sessionId]);
        $preferences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $formattedPreferences = [];
        foreach ($preferences as $pref) {
            $formattedPreferences[$pref['preference_key']] = json_decode($pref['preference_value'], true);
        }
        
        $this->sendSuccess(['preferences' => $formattedPreferences]);
    }
    
    private function handleRemoveModification() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['modification_id'])) {
            $this->sendError('Modification ID is required');
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE gui_modifications 
            SET is_active = FALSE 
            WHERE id = ?
        ");
        $stmt->execute([$input['modification_id']]);
        
        $this->sendSuccess(['removed' => true]);
    }
    
    private function handleResetModifications() {
        $sessionId = $_GET['session_id'] ?? session_id();
        
        $stmt = $this->pdo->prepare("
            UPDATE gui_modifications 
            SET is_active = FALSE 
            WHERE session_id = ?
        ");
        $stmt->execute([$sessionId]);
        
        $this->sendSuccess(['reset' => true]);
    }
    
    private function analyzeBehaviorPatterns($sessionId) {
        // Analyze recent behavior for patterns
        $stmt = $this->pdo->prepare("
            SELECT action_type, element_type, COUNT(*) as frequency
            FROM user_behavior 
            WHERE session_id = ? 
            AND timestamp > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            GROUP BY action_type, element_type
            HAVING frequency > 3
        ");
        $stmt->execute([$sessionId]);
        $patterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($patterns as $pattern) {
            $this->savePattern($pattern);
        }
    }
    
    private function savePattern($pattern) {
        $stmt = $this->pdo->prepare("
            INSERT INTO learning_patterns (pattern_type, pattern_data, confidence, frequency)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            frequency = frequency + 1,
            last_seen = CURRENT_TIMESTAMP,
            confidence = confidence + 0.1
        ");
        
        $stmt->execute([
            'user_behavior',
            json_encode($pattern),
            min(1.0, $pattern['frequency'] / 10),
            $pattern['frequency']
        ]);
    }
    
    private function detectFrustrationPatterns($sessionId) {
        // Detect rapid clicking, errors, etc.
        $stmt = $this->pdo->prepare("
            SELECT action_type, COUNT(*) as frequency
            FROM user_behavior 
            WHERE session_id = ? 
            AND timestamp > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
            AND action_type IN ('error', 'rapid_click', 'form_error')
            GROUP BY action_type
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function detectEfficiencyOpportunities($sessionId) {
        // Find repetitive actions that could be optimized
        $stmt = $this->pdo->prepare("
            SELECT element_id, element_type, COUNT(*) as frequency
            FROM user_behavior 
            WHERE session_id = ? 
            AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            AND action_type = 'click'
            GROUP BY element_id, element_type
            HAVING frequency > 5
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getRecentBehavior($sessionId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM user_behavior 
            WHERE session_id = ? 
            ORDER BY timestamp DESC 
            LIMIT 100
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function findRepetitiveActions($behavior) {
        $actionCounts = [];
        foreach ($behavior as $action) {
            $key = $action['action_type'] . '_' . $action['element_type'];
            $actionCounts[$key] = ($actionCounts[$key] ?? 0) + 1;
        }
        
        return array_filter($actionCounts, function($count) {
            return $count > 3;
        });
    }
    
    private function findNavigationIssues($behavior) {
        $navigationActions = array_filter($behavior, function($action) {
            return in_array($action['action_type'], ['scroll', 'page_change']);
        });
        
        return count($navigationActions) > 10;
    }
    
    private function findAccessibilityIssues($behavior) {
        // Check for small element clicks, rapid scrolling, etc.
        $accessibilityIssues = [];
        
        foreach ($behavior as $action) {
            if ($action['action_type'] === 'click' && isset($action['coordinates'])) {
                $coords = json_decode($action['coordinates'], true);
                if (isset($coords['element_size'])) {
                    $size = $coords['element_size'];
                    if ($size['width'] < 44 || $size['height'] < 44) {
                        $accessibilityIssues[] = 'small_elements';
                    }
                }
            }
        }
        
        return array_unique($accessibilityIssues);
    }
    
    private function generateModificationCode($modification) {
        switch ($modification['modification_type']) {
            case 'resize':
                return $this->generateResizeCode($modification);
            case 'reposition':
                return $this->generateRepositionCode($modification);
            case 'add_button':
                return $this->generateAddButtonCode($modification);
            case 'change_theme':
                return $this->generateThemeCode($modification);
            case 'add_shortcut':
                return $this->generateShortcutCode($modification);
            default:
                return 'console.log("Modification applied: ' . $modification['modification_type'] . '");';
        }
    }
    
    private function generateResizeCode($modification) {
        return "
            const elements = document.querySelectorAll('button, input, select, textarea');
            elements.forEach(element => {
                const currentSize = parseFloat(getComputedStyle(element).fontSize);
                element.style.fontSize = (currentSize * 1.2) + 'px';
                element.style.padding = '12px 16px';
            });
        ";
    }
    
    private function generateRepositionCode($modification) {
        return "
            const elements = document.querySelectorAll('button, input, a');
            elements.forEach(element => {
                const rect = element.getBoundingClientRect();
                if (rect.top > window.innerHeight * 0.8) {
                    element.style.position = 'relative';
                    element.style.top = '-50px';
                }
            });
        ";
    }
    
    private function generateAddButtonCode($modification) {
        return "
            const button = document.createElement('button');
            button.textContent = '🚀 Quick Action';
            button.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; background: #667eea; color: white; border: none; padding: 10px 15px; border-radius: 20px; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.2);';
            document.body.appendChild(button);
        ";
    }
    
    private function generateThemeCode($modification) {
        return "
            document.documentElement.style.setProperty('--primary-color', '#667eea');
            document.documentElement.style.setProperty('--secondary-color', '#764ba2');
        ";
    }
    
    private function generateShortcutCode($modification) {
        return "
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'h') {
                    e.preventDefault();
                    alert('Help: Ctrl+H for help, Ctrl+S to save, Ctrl+R to reset');
                }
            });
        ";
    }
    
    private function sendSuccess($data) {
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit();
    }
    
    private function sendError($message) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit();
    }
}

// Initialize and handle request
try {
    $api = new AdaptiveAIAPI();
    $api->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 