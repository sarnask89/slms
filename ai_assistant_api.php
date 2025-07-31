<?php
/**
 * AI Assistant API Backend
 * Handles AI assistant requests and integrates with local ML models
 */

// Start output buffering
ob_start();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include configuration
require_once __DIR__ . '/modules/config.php';

// AI Assistant API Class
class AIAssistantAPI {
    private $pdo;
    private $config;
    private $localModel;
    
    public function __construct() {
        try {
            $this->pdo = get_pdo();
            $this->config = [
                'model_type' => 'local', // 'local', 'api', 'hybrid'
                'local_model_path' => __DIR__ . '/ai_models/',
                'api_key' => null,
                'max_tokens' => 500,
                'temperature' => 0.7
            ];
            $this->initializeLocalModel();
        } catch (Exception $e) {
            $this->sendError('Database connection failed: ' . $e->getMessage());
        }
    }
    
    private function initializeLocalModel() {
        // Try to connect to LocalAI first
        if ($this->connectToLocalAI()) {
            $this->config['model_type'] = 'localai';
            return;
        }
        
        // Fallback to local model files
        $modelPath = $this->config['local_model_path'];
        if (is_dir($modelPath)) {
            // Load local model files
            $this->loadLocalModelFiles($modelPath);
        }
    }
    
    private function connectToLocalAI() {
        try {
            $localaiUrl = 'http://localhost:8080';
            
            // Use cURL instead of file_get_contents for better error handling
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $localaiUrl . '/v1/models');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($response !== false && $httpCode === 200) {
                $models = json_decode($response, true);
                if ($models && isset($models['data'])) {
                    $this->localModel = [
                        'type' => 'localai',
                        'url' => $localaiUrl,
                        'models' => $models['data'],
                        'available' => true
                    ];
                    return true;
                }
            }
        } catch (Exception $e) {
            error_log('LocalAI connection failed: ' . $e->getMessage());
        }
        
        return false;
    }
    
    private function loadLocalModelFiles($path) {
        // This would load actual ML model files
        // For now, we'll use a simple rule-based system
        $this->localModel = [
            'type' => 'rule_based',
            'rules' => $this->loadResponseRules(),
            'context' => []
        ];
    }
    
    private function loadResponseRules() {
        return [
            'greetings' => [
                'patterns' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon'],
                'responses' => [
                    'Hello! I\'m your AI assistant. How can I help you today?',
                    'Hi there! I\'m here to assist you. What can I help you with?',
                    'Hello! I\'m ready to help. What would you like to know?'
                ]
            ],
            'help' => [
                'patterns' => ['help', 'what can you do', 'assist', 'support'],
                'responses' => [
                    'I can help you with:\n• Understanding webpage content\n• Answering questions\n• Providing summaries\n• Explaining concepts\n• Assisting with tasks\n\nWhat would you like help with?',
                    'I\'m your AI assistant! I can:\n• Analyze page content\n• Answer your questions\n• Provide explanations\n• Help with navigation\n• Summarize information\n\nHow can I assist you?'
                ]
            ],
            'page_analysis' => [
                'patterns' => ['what is this page', 'explain this page', 'page content', 'what is this about'],
                'responses' => [
                    'I can analyze this page for you. Let me examine the content and provide you with a summary.',
                    'I\'ll help you understand this page. Let me break down the key information for you.'
                ]
            ],
            'summarize' => [
                'patterns' => ['summarize', 'summary', 'brief', 'overview'],
                'responses' => [
                    'I\'ll provide you with a concise summary of the key points from this page.',
                    'Let me summarize the main content for you in a clear and organized way.'
                ]
            ]
        ];
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
                case 'chat':
                    $this->handleChat();
                    break;
                case 'analyze_page':
                    $this->handlePageAnalysis();
                    break;
                case 'summarize':
                    $this->handleSummarize();
                    break;
                case 'get_suggestions':
                    $this->handleGetSuggestions();
                    break;
                case 'save_conversation':
                    $this->handleSaveConversation();
                    break;
                case 'get_conversation_history':
                    $this->handleGetConversationHistory();
                    break;
                case 'model_status':
                    $this->handleModelStatus();
                    break;
                case 'documentation':
                    $this->handleDocumentation();
                    break;
                case 'generate_code':
                    $this->handleGenerateCode();
                    break;
                case 'analyze_module':
                    $this->handleAnalyzeModule();
                    break;
                case 'generate_module':
                    $this->handleGenerateModule();
                    break;
                case 'integrate_api':
                    $this->handleIntegrateAPI();
                    break;
                default:
                    $this->sendError('Invalid action');
            }
        } catch (Exception $e) {
            $this->sendError('API Error: ' . $e->getMessage());
        }
    }
    
    private function handleChat() {
        // Get input from the already parsed data in handleRequest
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['message'])) {
            $this->sendError('Message is required');
        }
        
        $message = $input['message'];
        $context = $input['context'] ?? [];
        $conversationId = $input['conversation_id'] ?? null;
        
        // Generate response
        $response = $this->generateResponse($message, $context);
        
        // Save to database if conversation ID provided
        if ($conversationId) {
            $this->saveMessage($conversationId, 'user', $message);
            $this->saveMessage($conversationId, 'assistant', $response);
        }
        
        $this->sendSuccess([
            'response' => $response,
            'conversation_id' => $conversationId,
            'model_type' => $this->config['model_type'],
            'timestamp' => date('c')
        ]);
    }
    
    private function handlePageAnalysis() {
        $input = json_decode(file_get_contents('php://input'), true);
        $url = $input['url'] ?? '';
        $content = $input['content'] ?? '';
        
        if (empty($content)) {
            $this->sendError('Page content is required');
        }
        
        $analysis = $this->analyzePageContent($content, $url);
        
        $this->sendSuccess([
            'analysis' => $analysis,
            'url' => $url,
            'timestamp' => date('c')
        ]);
    }
    
    private function handleSummarize() {
        $input = json_decode(file_get_contents('php://input'), true);
        $content = $input['content'] ?? '';
        $maxLength = $input['max_length'] ?? 200;
        
        if (empty($content)) {
            $this->sendError('Content is required');
        }
        
        $summary = $this->generateSummary($content, $maxLength);
        
        $this->sendSuccess([
            'summary' => $summary,
            'original_length' => strlen($content),
            'summary_length' => strlen($summary),
            'timestamp' => date('c')
        ]);
    }
    
    private function handleGetSuggestions() {
        $input = json_decode(file_get_contents('php://input'), true);
        $context = $input['context'] ?? [];
        
        $suggestions = $this->generateSuggestions($context);
        
        $this->sendSuccess([
            'suggestions' => $suggestions,
            'timestamp' => date('c')
        ]);
    }
    
    private function handleSaveConversation() {
        $input = json_decode(file_get_contents('php://input'), true);
        $conversation = $input['conversation'] ?? [];
        $url = $input['url'] ?? '';
        
        if (empty($conversation)) {
            $this->sendError('Conversation data is required');
        }
        
        $conversationId = $this->saveConversation($conversation, $url);
        
        $this->sendSuccess([
            'conversation_id' => $conversationId,
            'timestamp' => date('c')
        ]);
    }
    
    private function handleGetConversationHistory() {
        $conversationId = $_GET['conversation_id'] ?? null;
        
        if (!$conversationId) {
            $this->sendError('Conversation ID is required');
        }
        
        $history = $this->getConversationHistory($conversationId);
        
        $this->sendSuccess([
            'conversation' => $history,
            'timestamp' => date('c')
        ]);
    }
    
    private function handleModelStatus() {
        $status = [
            'model_type' => $this->config['model_type'],
            'local_model_loaded' => $this->localModel !== null,
            'database_connected' => $this->pdo !== null,
            'api_version' => '1.0.0',
            'timestamp' => date('c')
        ];
        
        // Add LocalAI specific information
        if ($this->localModel && $this->localModel['type'] === 'localai') {
            $status['localai'] = [
                'url' => $this->localModel['url'],
                'models_available' => count($this->localModel['models']),
                'models' => array_map(function($model) {
                    return $model['id'];
                }, $this->localModel['models'])
            ];
        }
        
        $this->sendSuccess($status);
    }
    
    private function handleDocumentation() {
        $documentation = [
            'api_name' => 'AI Assistant API',
            'version' => '1.0.0',
            'description' => 'AI-powered assistant API for webpage analysis and intelligent responses',
            'base_url' => 'http://localhost/ai_assistant_api.php',
            'endpoints' => [
                [
                    'action' => 'chat',
                    'method' => 'POST',
                    'description' => 'Send a message to the AI assistant and get a response',
                    'parameters' => [
                        'message' => 'string (required) - The user message',
                        'context' => 'object (optional) - Page context information',
                        'conversation_id' => 'string (optional) - Conversation ID for history'
                    ],
                    'example' => [
                        'action' => 'chat',
                        'message' => 'What is this page about?',
                        'context' => ['url' => 'http://example.com', 'title' => 'Example Page']
                    ]
                ],
                [
                    'action' => 'analyze_page',
                    'method' => 'POST',
                    'description' => 'Analyze webpage content and extract key information',
                    'parameters' => [
                        'url' => 'string (optional) - The page URL',
                        'content' => 'string (required) - The page content to analyze'
                    ],
                    'example' => [
                        'action' => 'analyze_page',
                        'url' => 'http://example.com',
                        'content' => '<html>...</html>'
                    ]
                ],
                [
                    'action' => 'summarize',
                    'method' => 'POST',
                    'description' => 'Generate a summary of provided content',
                    'parameters' => [
                        'content' => 'string (required) - Content to summarize',
                        'max_length' => 'integer (optional) - Maximum summary length (default: 200)'
                    ],
                    'example' => [
                        'action' => 'summarize',
                        'content' => 'Long text content...',
                        'max_length' => 150
                    ]
                ],
                [
                    'action' => 'get_suggestions',
                    'method' => 'POST',
                    'description' => 'Get AI-powered suggestions based on context',
                    'parameters' => [
                        'context' => 'object (optional) - Context information for suggestions'
                    ],
                    'example' => [
                        'action' => 'get_suggestions',
                        'context' => ['page_type' => 'dashboard', 'user_role' => 'admin']
                    ]
                ],
                [
                    'action' => 'save_conversation',
                    'method' => 'POST',
                    'description' => 'Save a conversation to the database',
                    'parameters' => [
                        'conversation' => 'array (required) - Conversation data',
                        'url' => 'string (optional) - Associated URL'
                    ],
                    'example' => [
                        'action' => 'save_conversation',
                        'conversation' => ['messages' => []],
                        'url' => 'http://example.com'
                    ]
                ],
                [
                    'action' => 'get_conversation_history',
                    'method' => 'POST',
                    'description' => 'Retrieve conversation history by ID',
                    'parameters' => [
                        'conversation_id' => 'string (required) - Conversation ID'
                    ],
                    'example' => [
                        'action' => 'get_conversation_history',
                        'conversation_id' => 'conv_123456'
                    ]
                ],
                [
                    'action' => 'model_status',
                    'method' => 'GET',
                    'description' => 'Get current model status and configuration',
                    'parameters' => [],
                    'example' => [
                        'action' => 'model_status'
                    ]
                ],
                [
                    'action' => 'documentation',
                    'method' => 'GET',
                    'description' => 'Get API documentation (this endpoint)',
                    'parameters' => [],
                    'example' => [
                        'action' => 'documentation'
                    ]
                ],
                [
                    'action' => 'generate_code',
                    'method' => 'POST',
                    'description' => 'Generate code in various programming languages',
                    'parameters' => [
                        'language' => 'string (required) - Programming language (php, java, python, javascript, bash)',
                        'description' => 'string (required) - Code description and requirements',
                        'requirements' => 'array (optional) - Additional requirements'
                    ],
                    'example' => [
                        'action' => 'generate_code',
                        'language' => 'php',
                        'description' => 'Klasa PHP do obsługi użytkowników z bazą danych',
                        'requirements' => ['database', 'validation', 'logging']
                    ]
                ],
                [
                    'action' => 'analyze_module',
                    'method' => 'POST',
                    'description' => 'Analyze existing module structure and content',
                    'parameters' => [
                        'module_path' => 'string (optional) - Path to module file',
                        'module_name' => 'string (optional) - Name of module to analyze'
                    ],
                    'example' => [
                        'action' => 'analyze_module',
                        'module_path' => 'modules/user_management.php',
                        'module_name' => 'user_management'
                    ]
                ],
                [
                    'action' => 'generate_module',
                    'method' => 'POST',
                    'description' => 'Generate new module based on type and description',
                    'parameters' => [
                        'module_type' => 'string (required) - Type of module (monitoring, api, dashboard, user_management, custom)',
                        'description' => 'string (required) - Module functionality description',
                        'requirements' => 'array (optional) - Additional requirements',
                        'base_module' => 'string (optional) - Base module to extend'
                    ],
                    'example' => [
                        'action' => 'generate_module',
                        'module_type' => 'monitoring',
                        'description' => 'Moduł monitoringu systemu z alertami i wykresami',
                        'requirements' => ['database', 'email_alerts', 'charts']
                    ]
                ],
                [
                    'action' => 'integrate_api',
                    'method' => 'POST',
                    'description' => 'Generate API integration for existing module',
                    'parameters' => [
                        'api_url' => 'string (required) - External API URL',
                        'api_type' => 'string (optional) - API type (rest, graphql, soap, custom)',
                        'target_module' => 'string (required) - Target module name',
                        'endpoints' => 'array (optional) - Specific API endpoints'
                    ],
                    'example' => [
                        'action' => 'integrate_api',
                        'api_url' => 'https://api.example.com/v1',
                        'api_type' => 'rest',
                        'target_module' => 'user_management',
                        'endpoints' => ['/users', '/users/{id}']
                    ]
                ]
            ],
            'response_format' => [
                'success' => 'boolean - Whether the request was successful',
                'data' => 'object - Response data (on success)',
                'error' => 'string - Error message (on failure)'
            ],
            'authentication' => 'None required for basic usage',
            'rate_limiting' => 'None currently implemented',
            'supported_models' => [
                'localai' => 'LocalAI integration for enhanced responses',
                'rule_based' => 'Fallback rule-based response system'
            ],
            'examples' => [
                'curl_chat' => 'curl -X POST "http://localhost/ai_assistant_api.php" -H "Content-Type: application/json" -d \'{"action":"chat","message":"Hello"}\'',
                'curl_status' => 'curl "http://localhost/ai_assistant_api.php?action=model_status"',
                'curl_docs' => 'curl "http://localhost/ai_assistant_api.php?action=documentation"'
            ],
            'timestamp' => date('c')
        ];
        
        $this->sendSuccess($documentation);
    }
    
    private function handleGenerateCode() {
        $input = json_decode(file_get_contents('php://input'), true);
        $language = $input['language'] ?? 'php';
        $description = $input['description'] ?? '';
        $requirements = $input['requirements'] ?? [];
        
        if (empty($description)) {
            $this->sendError('Opis kodu jest wymagany');
        }
        
        $generatedCode = $this->generateCode($language, $description, $requirements);
        
        $this->sendSuccess([
            'code' => $generatedCode,
            'language' => $language,
            'description' => $description,
            'suggestions' => $this->generateCodeSuggestions($language, $description),
            'timestamp' => date('c')
        ]);
    }
    
    private function generateCode($language, $description, $requirements) {
        $language = strtolower($language);
        
        switch ($language) {
            case 'php':
                return $this->generatePHPCode($description, $requirements);
            case 'java':
                return $this->generateJavaCode($description, $requirements);
            case 'bash':
            case 'shell':
                return $this->generateBashScript($description, $requirements);
            case 'python':
                return $this->generatePythonCode($description, $requirements);
            case 'javascript':
            case 'js':
                return $this->generateJavaScriptCode($description, $requirements);
            default:
                return $this->generateGenericCode($language, $description, $requirements);
        }
    }
    
    private function generatePHPCode($description, $requirements) {
        $code = "<?php\n";
        $code .= "/**\n";
        $code .= " * Generated PHP Code\n";
        $code .= " * Description: " . $description . "\n";
        $code .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
        $code .= " */\n\n";
        
        if (strpos(strtolower($description), 'class') !== false) {
            $className = $this->extractClassName($description);
            $code .= "class {$className} {\n";
            $code .= "    private \$pdo;\n\n";
            $code .= "    public function __construct(\$pdo) {\n";
            $code .= "        \$this->pdo = \$pdo;\n";
            $code .= "    }\n\n";
            $code .= "    public function execute() {\n";
            $code .= "        // TODO: Implement functionality\n";
            $code .= "        return true;\n";
            $code .= "    }\n";
            $code .= "}\n";
        } elseif (strpos(strtolower($description), 'function') !== false) {
            $functionName = $this->extractFunctionName($description);
            $code .= "function {$functionName}(\$params = []) {\n";
            $code .= "    // TODO: Implement functionality\n";
            $code .= "    return \$params;\n";
            $code .= "}\n";
        } else {
            $code .= "// Main execution code\n";
            $code .= "\$result = null;\n\n";
            $code .= "try {\n";
            $code .= "    // TODO: Implement functionality based on description\n";
            $code .= "    \$result = 'Success';\n";
            $code .= "} catch (Exception \$e) {\n";
            $code .= "    error_log('Error: ' . \$e->getMessage());\n";
            $code .= "    \$result = 'Error';\n";
            $code .= "}\n\n";
            $code .= "return \$result;\n";
        }
        
        return $code;
    }
    
    private function generateJavaCode($description, $requirements) {
        $className = $this->extractClassName($description) ?: 'GeneratedClass';
        
        $code = "import java.util.*;\n";
        $code .= "import java.sql.*;\n\n";
        $code .= "/**\n";
        $code .= " * Generated Java Code\n";
        $code .= " * Description: " . $description . "\n";
        $code .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
        $code .= " */\n";
        $code .= "public class {$className} {\n";
        $code .= "    private Connection connection;\n\n";
        $code .= "    public {$className}(Connection connection) {\n";
        $code .= "        this.connection = connection;\n";
        $code .= "    }\n\n";
        $code .= "    public boolean execute() {\n";
        $code .= "        try {\n";
        $code .= "            // TODO: Implement functionality\n";
        $code .= "            return true;\n";
        $code .= "        } catch (Exception e) {\n";
        $code .= "            System.err.println(\"Error: \" + e.getMessage());\n";
        $code .= "            return false;\n";
        $code .= "        }\n";
        $code .= "    }\n\n";
        $code .= "    public static void main(String[] args) {\n";
        $code .= "        // TODO: Initialize and run\n";
        $code .= "        System.out.println(\"Generated Java code ready\");\n";
        $code .= "    }\n";
        $code .= "}\n";
        
        return $code;
    }
    
    private function generateBashScript($description, $requirements) {
        $scriptName = $this->extractScriptName($description) ?: 'generated_script';
        
        $code = "#!/bin/bash\n";
        $code .= "# Generated Bash Script\n";
        $code .= "# Description: " . $description . "\n";
        $code .= "# Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $code .= "set -e  # Exit on error\n\n";
        $code .= "# Configuration\n";
        $code .= "SCRIPT_NAME=\"$scriptName\"\n";
        $code .= "LOG_FILE=\"/var/log/\${SCRIPT_NAME}.log\"\n\n";
        $code .= "# Logging function\n";
        $code .= "log() {\n";
        $code .= "    echo \"[\$(date '+%Y-%m-%d %H:%M:%S')] \$1\" | tee -a \"\$LOG_FILE\"\n";
        $code .= "}\n\n";
        $code .= "# Main execution\n";
        $code .= "main() {\n";
        $code .= "    log \"Starting \$SCRIPT_NAME\"\n";
        $code .= "    \n";
        $code .= "    # TODO: Implement functionality based on description\n";
        $code .= "    \n";
        $code .= "    log \"\$SCRIPT_NAME completed successfully\"\n";
        $code .= "}\n\n";
        $code .= "# Error handling\n";
        $code .= "trap 'log \"Error occurred. Exiting.\"; exit 1' ERR\n\n";
        $code .= "# Run main function\n";
        $code .= "main \"\$@\"\n";
        
        return $code;
    }
    
    private function generatePythonCode($description, $requirements) {
        $className = $this->extractClassName($description) ?: 'GeneratedClass';
        
        $code = "#!/usr/bin/env python3\n";
        $code .= "# -*- coding: utf-8 -*-\n";
        $code .= "\"\"\"\n";
        $code .= "Generated Python Code\n";
        $code .= "Description: " . $description . "\n";
        $code .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $code .= "\"\"\"\n\n";
        $code .= "import sys\n";
        $code .= "import logging\n";
        $code .= "from typing import Dict, Any, Optional\n\n";
        $code .= "# Configure logging\n";
        $code .= "logging.basicConfig(level=logging.INFO)\n";
        $code .= "logger = logging.getLogger(__name__)\n\n";
        $code .= "class {$className}:\n";
        $code .= "    def __init__(self, config: Optional[Dict[str, Any]] = None):\n";
        $code .= "        self.config = config or {}\n";
        $code .= "        logger.info(f\"Initialized {$className}\")\n\n";
        $code .= "    def execute(self) -> bool:\n";
        $code .= "        try:\n";
        $code .= "            # TODO: Implement functionality\n";
        $code .= "            logger.info(\"Executing functionality\")\n";
        $code .= "            return True\n";
        $code .= "        except Exception as e:\n";
        $code .= "            logger.error(f\"Error: {e}\")\n";
        $code .= "            return False\n\n";
        $code .= "def main():\n";
        $code .= "    \"\"\"Main execution function\"\"\"\n";
        $code .= "    try:\n";
        $code .= "        instance = {$className}()\n";
        $code .= "        success = instance.execute()\n";
        $code .= "        if success:\n";
        $code .= "            print(\"Generated Python code executed successfully\")\n";
        $code .= "        else:\n";
        $code .= "            print(\"Generated Python code failed\")\n";
        $code .= "    except Exception as e:\n";
        $code .= "        print(f\"Error: {e}\")\n";
        $code .= "        sys.exit(1)\n\n";
        $code .= "if __name__ == \"__main__\":\n";
        $code .= "    main()\n";
        
        return $code;
    }
    
    private function generateJavaScriptCode($description, $requirements) {
        $className = $this->extractClassName($description) ?: 'GeneratedClass';
        
        $code = "/**\n";
        $code .= " * Generated JavaScript Code\n";
        $code .= " * Description: " . $description . "\n";
        $code .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
        $code .= " */\n\n";
        $code .= "class {$className} {\n";
        $code .= "    constructor(config = {}) {\n";
        $code .= "        this.config = config;\n";
        $code .= "        this.logger = console;\n";
        $code .= "    }\n\n";
        $code .= "    async execute() {\n";
        $code .= "        try {\n";
        $code .= "            // TODO: Implement functionality\n";
        $code .= "            this.logger.log('Executing functionality');\n";
        $code .= "            return true;\n";
        $code .= "        } catch (error) {\n";
        $code .= "            this.logger.error('Error:', error);\n";
        $code .= "            return false;\n";
        $code .= "        }\n";
        $code .= "    }\n\n";
        $code .= "    static async create(config = {}) {\n";
        $code .= "        const instance = new {$className}(config);\n";
        $code .= "        return instance;\n";
        $code .= "    }\n";
        $code .= "}\n\n";
        $code .= "// Usage example\n";
        $code .= "async function main() {\n";
        $code .= "    try {\n";
        $code .= "        const instance = await {$className}.create();\n";
        $code .= "        const result = await instance.execute();\n";
        $code .= "        console.log('Generated JavaScript code executed:', result);\n";
        $code .= "    } catch (error) {\n";
        $code .= "        console.error('Error:', error);\n";
        $code .= "    }\n";
        $code .= "}\n\n";
        $code .= "// Export for module usage\n";
        $code .= "if (typeof module !== 'undefined' && module.exports) {\n";
        $code .= "    module.exports = {$className};\n";
        $code .= "}\n";
        
        return $code;
    }
    
    private function generateGenericCode($language, $description, $requirements) {
        return "// Generated {$language} Code\n";
        $code .= "// Description: " . $description . "\n";
        $code .= "// Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $code .= "// TODO: Implement {$language} code based on description\n";
        $code .= "// This is a generic template for {$language}\n\n";
        $code .= "function main() {\n";
        $code .= "    // TODO: Add your {$language} implementation here\n";
        $code .= "    console.log('Generated {$language} code template');\n";
        $code .= "}\n\n";
        $code .= "main();\n";
        
        return $code;
    }
    
    private function extractClassName($description) {
        // Simple class name extraction
        if (preg_match('/class\s+(\w+)/i', $description, $matches)) {
            return ucfirst($matches[1]);
        }
        if (preg_match('/(\w+)\s+class/i', $description, $matches)) {
            return ucfirst($matches[1]);
        }
        return null;
    }
    
    private function extractFunctionName($description) {
        // Simple function name extraction
        if (preg_match('/function\s+(\w+)/i', $description, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(\w+)\s+function/i', $description, $matches)) {
            return $matches[1];
        }
        return 'generated_function';
    }
    
    private function extractScriptName($description) {
        // Simple script name extraction
        if (preg_match('/script\s+(\w+)/i', $description, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(\w+)\s+script/i', $description, $matches)) {
            return $matches[1];
        }
        return 'generated_script';
    }
    
    private function generateCodeSuggestions($language, $description) {
        $suggestions = [];
        
        switch (strtolower($language)) {
            case 'php':
                $suggestions = [
                    'Dodaj obsługę błędów try-catch',
                    'Użyj prepared statements dla zapytań SQL',
                    'Dodaj walidację danych wejściowych',
                    'Zaimplementuj logowanie błędów',
                    'Dodaj dokumentację PHPDoc'
                ];
                break;
            case 'java':
                $suggestions = [
                    'Dodaj obsługę wyjątków',
                    'Użyj connection pooling',
                    'Zaimplementuj proper resource management',
                    'Dodaj unit tests',
                    'Użyj dependency injection'
                ];
                break;
            case 'bash':
            case 'shell':
                $suggestions = [
                    'Dodaj sprawdzanie uprawnień',
                    'Użyj set -e dla bezpieczeństwa',
                    'Dodaj logowanie do pliku',
                    'Zaimplementuj backup przed zmianami',
                    'Dodaj sprawdzanie zależności'
                ];
                break;
            case 'python':
                $suggestions = [
                    'Dodaj type hints',
                    'Użyj context managers',
                    'Zaimplementuj proper error handling',
                    'Dodaj docstrings',
                    'Użyj virtual environment'
                ];
                break;
            case 'javascript':
            case 'js':
                $suggestions = [
                    'Dodaj async/await handling',
                    'Użyj proper error handling',
                    'Zaimplementuj input validation',
                    'Dodaj JSDoc documentation',
                    'Użyj ES6+ features'
                ];
                break;
        }
        
        return $suggestions;
    }
    
    private function handleAnalyzeModule() {
        $input = json_decode(file_get_contents('php://input'), true);
        $modulePath = $input['module_path'] ?? '';
        $moduleName = $input['module_name'] ?? '';
        
        if (empty($modulePath) && empty($moduleName)) {
            $this->sendError('Ścieżka lub nazwa modułu jest wymagana');
        }
        
        $analysis = $this->analyzeModule($modulePath, $moduleName);
        
        $this->sendSuccess([
            'analysis' => $analysis,
            'module_name' => $moduleName,
            'module_path' => $modulePath,
            'timestamp' => date('c')
        ]);
    }
    
    private function handleGenerateModule() {
        $input = json_decode(file_get_contents('php://input'), true);
        $moduleType = $input['module_type'] ?? 'custom';
        $description = $input['description'] ?? '';
        $requirements = $input['requirements'] ?? [];
        $baseModule = $input['base_module'] ?? null;
        
        if (empty($description)) {
            $this->sendError('Opis modułu jest wymagany');
        }
        
        $generatedModule = $this->generateModule($moduleType, $description, $requirements, $baseModule);
        
        $this->sendSuccess([
            'module_code' => $generatedModule['code'],
            'module_name' => $generatedModule['name'],
            'module_type' => $moduleType,
            'suggestions' => $generatedModule['suggestions'],
            'timestamp' => date('c')
        ]);
    }
    
    private function handleIntegrateAPI() {
        $input = json_decode(file_get_contents('php://input'), true);
        $apiUrl = $input['api_url'] ?? '';
        $apiType = $input['api_type'] ?? 'rest';
        $targetModule = $input['target_module'] ?? '';
        $endpoints = $input['endpoints'] ?? [];
        
        if (empty($apiUrl) || empty($targetModule)) {
            $this->sendError('URL API i moduł docelowy są wymagane');
        }
        
        $integration = $this->generateAPIIntegration($apiUrl, $apiType, $targetModule, $endpoints);
        
        $this->sendSuccess([
            'integration_code' => $integration['code'],
            'api_url' => $apiUrl,
            'target_module' => $targetModule,
            'endpoints' => $integration['endpoints'],
            'suggestions' => $integration['suggestions'],
            'timestamp' => date('c')
        ]);
    }
    
    private function analyzeModule($modulePath, $moduleName) {
        $analysis = [
            'name' => $moduleName,
            'path' => $modulePath,
            'type' => $this->detectModuleType($moduleName),
            'complexity' => 'medium',
            'functions' => [],
            'classes' => [],
            'dependencies' => [],
            'suggestions' => []
        ];
        
        // Try to read module content
        if (!empty($modulePath) && file_exists($modulePath)) {
            $content = file_get_contents($modulePath);
            $analysis = array_merge($analysis, $this->analyzeModuleContent($content));
        }
        
        // Generate suggestions based on module type
        $analysis['suggestions'] = $this->generateModuleSuggestions($analysis);
        
        return $analysis;
    }
    
    private function detectModuleType($moduleName) {
        $lowerName = strtolower($moduleName);
        
        if (strpos($lowerName, 'monitoring') !== false) return 'monitoring';
        if (strpos($lowerName, 'api') !== false) return 'api';
        if (strpos($lowerName, 'dashboard') !== false) return 'dashboard';
        if (strpos($lowerName, 'user') !== false) return 'user_management';
        if (strpos($lowerName, 'network') !== false) return 'network';
        if (strpos($lowerName, 'integration') !== false) return 'integration';
        if (strpos($lowerName, 'report') !== false) return 'reporting';
        
        return 'custom';
    }
    
    private function analyzeModuleContent($content) {
        $analysis = [
            'functions' => [],
            'classes' => [],
            'dependencies' => [],
            'complexity' => 'low'
        ];
        
        // Extract functions
        preg_match_all('/function\s+(\w+)\s*\(/', $content, $matches);
        $analysis['functions'] = $matches[1] ?? [];
        
        // Extract classes
        preg_match_all('/class\s+(\w+)/', $content, $matches);
        $analysis['classes'] = $matches[1] ?? [];
        
        // Extract dependencies
        preg_match_all('/require.*?[\'"]([^\'"]+)[\'"]/', $content, $matches);
        $analysis['dependencies'] = array_unique($matches[1] ?? []);
        
        // Calculate complexity
        $lineCount = substr_count($content, "\n");
        if ($lineCount > 500) $analysis['complexity'] = 'high';
        elseif ($lineCount > 200) $analysis['complexity'] = 'medium';
        else $analysis['complexity'] = 'low';
        
        return $analysis;
    }
    
    private function generateModuleSuggestions($analysis) {
        $suggestions = [];
        
        switch ($analysis['type']) {
            case 'monitoring':
                $suggestions = [
                    'Dodaj alerty i powiadomienia',
                    'Zaimplementuj wykresy i statystyki',
                    'Dodaj konfigurację progów',
                    'Zaimplementuj logowanie zdarzeń'
                ];
                break;
            case 'api':
                $suggestions = [
                    'Dodaj walidację danych wejściowych',
                    'Zaimplementuj rate limiting',
                    'Dodaj dokumentację API',
                    'Zaimplementuj obsługę błędów'
                ];
                break;
            case 'dashboard':
                $suggestions = [
                    'Dodaj interaktywne wykresy',
                    'Zaimplementuj filtrowanie danych',
                    'Dodaj eksport do PDF/Excel',
                    'Zaimplementuj personalizację widoków'
                ];
                break;
            case 'user_management':
                $suggestions = [
                    'Dodaj system uprawnień',
                    'Zaimplementuj audyt użytkowników',
                    'Dodaj resetowanie hasła',
                    'Zaimplementuj blokowanie kont'
                ];
                break;
            default:
                $suggestions = [
                    'Dodaj obsługę błędów',
                    'Zaimplementuj logowanie',
                    'Dodaj walidację danych',
                    'Zoptymalizuj wydajność'
                ];
        }
        
        return $suggestions;
    }
    
    private function generateModule($moduleType, $description, $requirements, $baseModule = null) {
        $moduleName = $this->generateModuleName($moduleType, $description);
        
        $code = $this->generateModuleCode($moduleType, $description, $requirements, $baseModule);
        $suggestions = $this->generateModuleSuggestions(['type' => $moduleType]);
        
        return [
            'name' => $moduleName,
            'code' => $code,
            'suggestions' => $suggestions
        ];
    }
    
    private function generateModuleName($moduleType, $description) {
        $words = explode(' ', strtolower($description));
        $keyWords = array_slice($words, 0, 3);
        return $moduleType . '_' . implode('_', $keyWords) . '.php';
    }
    
    private function generateModuleCode($moduleType, $description, $requirements, $baseModule) {
        $code = "<?php\n";
        $code .= "/**\n";
        $code .= " * Generated Module: {$moduleType}\n";
        $code .= " * Description: {$description}\n";
        $code .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
        $code .= " */\n\n";
        
        switch ($moduleType) {
            case 'monitoring':
                $code .= $this->generateMonitoringModule($description, $requirements);
                break;
            case 'api':
                $code .= $this->generateAPIModule($description, $requirements);
                break;
            case 'dashboard':
                $code .= $this->generateDashboardModule($description, $requirements);
                break;
            case 'user_management':
                $code .= $this->generateUserManagementModule($description, $requirements);
                break;
            default:
                $code .= $this->generateCustomModule($description, $requirements);
        }
        
        return $code;
    }
    
    private function generateMonitoringModule($description, $requirements) {
        return "
class MonitoringModule {
    private \$pdo;
    private \$config;
    
    public function __construct(\$pdo, \$config = []) {
        \$this->pdo = \$pdo;
        \$this->config = \$config;
    }
    
    public function checkSystemStatus() {
        // TODO: Implement system monitoring
        return ['status' => 'ok', 'timestamp' => date('c')];
    }
    
    public function getMetrics() {
        // TODO: Implement metrics collection
        return ['cpu' => 0, 'memory' => 0, 'disk' => 0];
    }
    
    public function sendAlert(\$message, \$level = 'info') {
        // TODO: Implement alert system
        error_log(\"[\$level] \$message\");
    }
}

// Usage example
\$monitor = new MonitoringModule(\$pdo);
\$status = \$monitor->checkSystemStatus();
";
    }
    
    private function generateAPIModule($description, $requirements) {
        return "
class APIModule {
    private \$pdo;
    private \$endpoints = [];
    
    public function __construct(\$pdo) {
        \$this->pdo = \$pdo;
        \$this->registerEndpoints();
    }
    
    private function registerEndpoints() {
        \$this->endpoints = [
            'GET /api/status' => 'getStatus',
            'POST /api/data' => 'postData',
            'PUT /api/update' => 'updateData',
            'DELETE /api/remove' => 'deleteData'
        ];
    }
    
    public function handleRequest(\$method, \$path, \$data = null) {
        \$key = \"\$method \$path\";
        if (isset(\$this->endpoints[\$key])) {
            \$method = \$this->endpoints[\$key];
            return \$this->\$method(\$data);
        }
        return ['error' => 'Endpoint not found'];
    }
    
    private function getStatus() {
        return ['status' => 'ok', 'timestamp' => date('c')];
    }
    
    private function postData(\$data) {
        // TODO: Implement data posting
        return ['success' => true, 'id' => uniqid()];
    }
    
    private function updateData(\$data) {
        // TODO: Implement data update
        return ['success' => true];
    }
    
    private function deleteData(\$data) {
        // TODO: Implement data deletion
        return ['success' => true];
    }
}
";
    }
    
    private function generateDashboardModule($description, $requirements) {
        return "
class DashboardModule {
    private \$pdo;
    private \$widgets = [];
    
    public function __construct(\$pdo) {
        \$this->pdo = \$pdo;
        \$this->initializeWidgets();
    }
    
    private function initializeWidgets() {
        \$this->widgets = [
            'system_status' => 'System Status Widget',
            'performance_chart' => 'Performance Chart',
            'recent_activity' => 'Recent Activity',
            'quick_stats' => 'Quick Statistics'
        ];
    }
    
    public function getDashboardData() {
        return [
            'widgets' => \$this->widgets,
            'data' => [
                'system_status' => \$this->getSystemStatus(),
                'performance' => \$this->getPerformanceData(),
                'activity' => \$this->getRecentActivity(),
                'stats' => \$this->getQuickStats()
            ]
        ];
    }
    
    private function getSystemStatus() {
        // TODO: Implement system status
        return ['status' => 'operational'];
    }
    
    private function getPerformanceData() {
        // TODO: Implement performance data
        return ['cpu' => 25, 'memory' => 60, 'disk' => 45];
    }
    
    private function getRecentActivity() {
        // TODO: Implement recent activity
        return [];
    }
    
    private function getQuickStats() {
        // TODO: Implement quick stats
        return ['users' => 0, 'devices' => 0, 'alerts' => 0];
    }
}
";
    }
    
    private function generateUserManagementModule($description, $requirements) {
        return "
class UserManagementModule {
    private \$pdo;
    
    public function __construct(\$pdo) {
        \$this->pdo = \$pdo;
    }
    
    public function createUser(\$userData) {
        // TODO: Implement user creation
        \$sql = \"INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())\";
        // \$stmt = \$this->pdo->prepare(\$sql);
        // return \$stmt->execute([\$userData['username'], \$userData['email'], password_hash(\$userData['password'], PASSWORD_DEFAULT)]);
        return ['success' => true, 'user_id' => uniqid()];
    }
    
    public function updateUser(\$userId, \$userData) {
        // TODO: Implement user update
        return ['success' => true];
    }
    
    public function deleteUser(\$userId) {
        // TODO: Implement user deletion
        return ['success' => true];
    }
    
    public function getUser(\$userId) {
        // TODO: Implement user retrieval
        return ['id' => \$userId, 'username' => 'test_user', 'email' => 'test@example.com'];
    }
    
    public function listUsers(\$filters = []) {
        // TODO: Implement user listing
        return [];
    }
}
";
    }
    
    private function generateCustomModule($description, $requirements) {
        return "
class CustomModule {
    private \$pdo;
    private \$config;
    
    public function __construct(\$pdo, \$config = []) {
        \$this->pdo = \$pdo;
        \$this->config = \$config;
    }
    
    public function execute(\$params = []) {
        // TODO: Implement custom functionality based on description
        // Description: {$description}
        
        return ['success' => true, 'message' => 'Custom module executed'];
    }
    
    public function getConfig() {
        return \$this->config;
    }
    
    public function setConfig(\$config) {
        \$this->config = array_merge(\$this->config, \$config);
    }
}
";
    }
    
    private function generateAPIIntegration($apiUrl, $apiType, $targetModule, $endpoints) {
        $integrationName = $targetModule . '_api_integration';
        
        $code = "<?php\n";
        $code .= "/**\n";
        $code .= " * API Integration for {$targetModule}\n";
        $code .= " * API URL: {$apiUrl}\n";
        $code .= " * API Type: {$apiType}\n";
        $code .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
        $code .= " */\n\n";
        
        $code .= "
class {$integrationName} {
    private \$apiUrl = '{$apiUrl}';
    private \$apiType = '{$apiType}';
    private \$apiKey = null;
    
    public function __construct(\$apiKey = null) {
        \$this->apiKey = \$apiKey;
    }
    
    public function makeRequest(\$endpoint, \$method = 'GET', \$data = null) {
        \$url = \$this->apiUrl . \$endpoint;
        \$headers = ['Content-Type: application/json'];
        
        if (\$this->apiKey) {
            \$headers[] = 'Authorization: Bearer ' . \$this->apiKey;
        }
        
        \$ch = curl_init();
        curl_setopt(\$ch, CURLOPT_URL, \$url);
        curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(\$ch, CURLOPT_HTTPHEADER, \$headers);
        curl_setopt(\$ch, CURLOPT_CUSTOMREQUEST, \$method);
        
        if (\$data && in_array(\$method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt(\$ch, CURLOPT_POSTFIELDS, json_encode(\$data));
        }
        
        \$response = curl_exec(\$ch);
        \$httpCode = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
        curl_close(\$ch);
        
        return [
            'status_code' => \$httpCode,
            'data' => json_decode(\$response, true),
            'raw_response' => \$response
        ];
    }
    
    // Generated endpoints based on common patterns
    public function getData(\$id = null) {
        \$endpoint = \$id ? '/data/' . \$id : '/data';
        return \$this->makeRequest(\$endpoint, 'GET');
    }
    
    public function createData(\$data) {
        return \$this->makeRequest('/data', 'POST', \$data);
    }
    
    public function updateData(\$id, \$data) {
        return \$this->makeRequest('/data/' . \$id, 'PUT', \$data);
    }
    
    public function deleteData(\$id) {
        return \$this->makeRequest('/data/' . \$id, 'DELETE');
    }
    
    public function getStatus() {
        return \$this->makeRequest('/status', 'GET');
    }
}
";
        
        $generatedEndpoints = [
            'GET /data' => 'getData()',
            'POST /data' => 'createData($data)',
            'PUT /data/{id}' => 'updateData($id, $data)',
            'DELETE /data/{id}' => 'deleteData($id)',
            'GET /status' => 'getStatus()'
        ];
        
        $suggestions = [
            'Dodaj obsługę błędów i retry logic',
            'Zaimplementuj cache dla często używanych endpointów',
            'Dodaj rate limiting',
            'Zaimplementuj logowanie requestów',
            'Dodaj walidację odpowiedzi API'
        ];
        
        return [
            'code' => $code,
            'endpoints' => $generatedEndpoints,
            'suggestions' => $suggestions
        ];
    }
    
    private function generateResponse($message, $context = []) {
        // Try local model first
        if ($this->localModel && $this->config['model_type'] === 'local') {
            return $this->generateLocalResponse($message, $context);
        }
        
        // Fallback to rule-based response
        return $this->generateRuleBasedResponse($message, $context);
    }
    
    private function generateLocalResponse($message, $context) {
        // Try LocalAI first
        if ($this->localModel && $this->localModel['type'] === 'localai') {
            return $this->generateLocalAIResponse($message, $context);
        }
        
        // Fallback to rule-based system
        return $this->generateRuleBasedResponse($message, $context);
    }
    
    private function generateLocalAIResponse($message, $context) {
        try {
            $url = $this->localModel['url'] . '/v1/chat/completions';
            
            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful AI assistant that helps users understand webpage content and provides relevant assistance. Be concise, helpful, and context-aware.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Page context: {$context['title']} ({$context['url']})\n\nUser question: {$message}"
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ];
            
            $options = [
                'http' => [
                    'header' => "Content-type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode($data)
                ]
            ];
            
            $context_stream = stream_context_create($options);
            $response = file_get_contents($url, false, $context_stream);
            
            if ($response !== false) {
                $result = json_decode($response, true);
                if ($result && isset($result['choices'][0]['message']['content'])) {
                    return $result['choices'][0]['message']['content'];
                }
            }
        } catch (Exception $e) {
            error_log('LocalAI API call failed: ' . $e->getMessage());
        }
        
        // Fallback to rule-based response
        return $this->generateRuleBasedResponse($message, $context);
    }
    
    private function generateRuleBasedResponse($message, $context) {
        $lowerMessage = strtolower($message);
        
        // Check language context
        $language = $context['language'] ?? 'en';
        $isPolish = $language === 'pl';
        
        // Polish language responses
        if ($isPolish) {
            return $this->generatePolishResponse($message, $context);
        }
        
        // Check against rules if local model is available
        if ($this->localModel && isset($this->localModel['rules'])) {
            foreach ($this->localModel['rules'] as $rule) {
                foreach ($rule['patterns'] as $pattern) {
                    if (strpos($lowerMessage, $pattern) !== false) {
                        $responses = $rule['responses'];
                        return $responses[array_rand($responses)];
                    }
                }
            }
        }
        
        // Generate contextual response
        return $this->generateContextualResponse($message, $context);
    }
    
    private function generateContextualResponse($message, $context) {
        $pageTitle = $context['title'] ?? 'this page';
        $pageUrl = $context['url'] ?? '';
        
        return "I understand you're asking about \"{$message}\". 

Based on the context of {$pageTitle}, I can help you with that. The page contains various information that might be relevant to your question.

Could you provide more specific details about what you'd like to know? I'm here to help explain, summarize, or assist with any questions you have about the content.";
    }
    
    private function generatePolishResponse($message, $context) {
        $lowerMessage = strtolower($message);
        $pageTitle = $context['title'] ?? 'tej strony';
        $pageUrl = $context['url'] ?? '';
        
        // Polish language patterns and responses
        $polishPatterns = [
            'pomoc' => [
                'Mogę Ci pomóc w wielu zadaniach! Oto co potrafię:',
                '• Analizować treści stron internetowych',
                '• Generować podsumowania',
                '• Odpowiadać na pytania o system',
                '• Pomagać w nawigacji',
                '',
                'W czym konkretnie mogę Ci dzisiaj pomóc?'
            ],
            'status' => [
                'Status systemu:',
                '• Serwer: Działa poprawnie',
                '• Baza danych: Połączona',
                '• Modele AI: Aktywne',
                '• API: Dostępne',
                '',
                'Wszystko działa bez problemów!'
            ],
            'analiz' => [
                'Chętnie przeanalizuję treść dla Ciebie!',
                '',
                'Aby uzyskać najlepszą analizę, podaj mi:',
                '• URL strony do analizy',
                '• Lub wklej treść, którą chcesz przeanalizować',
                '',
                'Mogę wyciągnąć kluczowe informacje, podsumowania i wnioski.'
            ],
            'podsumowanie' => [
                'Potrafię tworzyć podsumowania różnych treści!',
                '',
                'Po prostu:',
                '• Wklej tekst do podsumowania',
                '• Lub podaj URL strony',
                '',
                'Wygeneruję dla Ciebie zwięzłe i czytelne podsumowanie.'
            ],
            'jak' => [
                'Oto jak mogę Ci pomóc:',
                '',
                '1. **Pytania ogólne** - Odpowiadam na różne pytania',
                '2. **Analiza treści** - Analizuję strony i dokumenty',
                '3. **Podsumowania** - Tworzę skróty długich tekstów',
                '4. **Pomoc systemowa** - Informacje o statusie i funkcjach',
                '',
                'Po prostu zadaj mi pytanie!'
            ],
            'co potrafisz' => [
                'Oto moje możliwości:',
                '',
                '🤖 **Asystent AI** - Pomagam w różnych zadaniach',
                '📊 **Analiza** - Analizuję treści i dane',
                '📝 **Podsumowania** - Tworzę skróty tekstów',
                '🔍 **Wyszukiwanie** - Pomagam znaleźć informacje',
                '💬 **Rozmowa** - Prowadzę naturalne konwersacje',
                '💻 **Generowanie kodu** - Tworzę skrypty w różnych językach',
                '',
                'W czym mogę Ci dzisiaj pomóc?'
            ],
            'kod' => [
                'Potrafię generować kod w różnych językach programowania!',
                '',
                '**Obsługiwane języki:**',
                '• PHP - Skrypty webowe i aplikacje',
                '• Java - Aplikacje enterprise',
                '• Python - Skrypty i automatyzacja',
                '• JavaScript - Frontend i Node.js',
                '• Bash/Shell - Skrypty systemowe',
                '',
                '**Przykłady użycia:**',
                '• "Wygeneruj skrypt PHP do obsługi bazy danych"',
                '• "Utwórz skrypt bash do backupu"',
                '• "Napisz klasę Java do połączenia z API"',
                '',
                'Po prostu opisz, co chcesz zrobić!'
            ],
            'skrypt' => [
                'Chętnie wygeneruję skrypt dla Ciebie!',
                '',
                '**Typy skryptów:**',
                '• Skrypty systemowe (Bash/Shell)',
                '• Skrypty PHP do automatyzacji',
                '• Skrypty Python do analizy danych',
                '• Skrypty JavaScript do automatyzacji',
                '',
                '**Przykłady:**',
                '• "Skrypt do monitorowania systemu"',
                '• "Skrypt do backupu bazy danych"',
                '• "Skrypt do automatycznego deploy"',
                '• "Skrypt do analizy logów"',
                '',
                'Opisz dokładnie, co ma robić skrypt!'
            ],
            'java' => [
                'Potrafię generować kod Java!',
                '',
                '**Możliwości Java:**',
                '• Klasy i interfejsy',
                '• Połączenia z bazą danych',
                '• Obsługa wyjątków',
                '• Aplikacje webowe',
                '• API REST',
                '',
                '**Przykłady:**',
                '• "Klasa Java do obsługi użytkowników"',
                '• "Java API do zarządzania urządzeniami"',
                '• "Java servlet do przetwarzania danych"',
                '',
                'Opisz funkcjonalność, którą chcesz zaimplementować!'
            ],
            'php' => [
                'Potrafię generować kod PHP!',
                '',
                '**Możliwości PHP:**',
                '• Klasy i funkcje',
                '• Połączenia z bazą danych',
                '• API REST',
                '• Skrypty automatyzacji',
                '• Aplikacje webowe',
                '',
                '**Przykłady:**',
                '• "Klasa PHP do zarządzania klientami"',
                '• "Funkcja PHP do walidacji danych"',
                '• "Skrypt PHP do importu danych"',
                '',
                'Opisz, co ma robić kod PHP!'
            ]
        ];
        
        // Check for specific patterns
        foreach ($polishPatterns as $pattern => $response) {
            if (strpos($lowerMessage, $pattern) !== false) {
                return implode("\n", $response);
            }
        }
        
        // Default Polish response
        return "Rozumiem, że pytasz o \"{$message}\". 

Na podstawie kontekstu {$pageTitle}, mogę Ci w tym pomóc. Strona zawiera różne informacje, które mogą być istotne dla Twojego pytania.

Czy możesz podać więcej szczegółów o tym, co chciałbyś wiedzieć? Jestem tutaj, aby wyjaśnić, podsumować lub pomóc w każdym pytaniu dotyczącym treści.";
    }
    
    private function analyzePageContent($content, $url) {
        // Simple page analysis
        $words = str_word_count($content);
        $sentences = preg_match_all('/[.!?]+/', $content);
        $paragraphs = substr_count($content, "\n\n") + 1;
        
        // Detect page type
        $pageType = $this->detectPageType($content, $url);
        
        // Extract key topics
        $topics = $this->extractKeyTopics($content);
        
        return [
            'word_count' => $words,
            'sentence_count' => $sentences,
            'paragraph_count' => $paragraphs,
            'page_type' => $pageType,
            'key_topics' => $topics,
            'reading_time' => ceil($words / 200), // Average reading speed
            'summary' => $this->generateSummary($content, 150)
        ];
    }
    
    private function detectPageType($content, $url) {
        $lowerContent = strtolower($content);
        $lowerUrl = strtolower($url);
        
        if (strpos($lowerUrl, 'login') !== false || strpos($lowerContent, 'login') !== false) {
            return 'authentication';
        }
        if (strpos($lowerUrl, 'admin') !== false || strpos($lowerContent, 'dashboard') !== false) {
            return 'administrative';
        }
        if (strpos($lowerUrl, 'product') !== false || strpos($lowerContent, 'buy') !== false) {
            return 'e-commerce';
        }
        if (strpos($lowerUrl, 'blog') !== false || strpos($lowerContent, 'article') !== false) {
            return 'content/article';
        }
        if (strpos($lowerUrl, 'contact') !== false || strpos($lowerContent, 'about') !== false) {
            return 'informational';
        }
        
        return 'general';
    }
    
    private function extractKeyTopics($content) {
        // Simple keyword extraction
        $words = str_word_count(strtolower($content), 1);
        $wordCount = array_count_values($words);
        arsort($wordCount);
        
        // Filter out common words
        $commonWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'this', 'that', 'these', 'those', 'a', 'an'];
        
        $topics = [];
        foreach ($wordCount as $word => $count) {
            if (!in_array($word, $commonWords) && strlen($word) > 3 && $count > 2) {
                $topics[] = $word;
                if (count($topics) >= 5) break;
            }
        }
        
        return $topics;
    }
    
    private function generateSummary($content, $maxLength = 200) {
        // Simple summary generation
        $sentences = preg_split('/[.!?]+/', $content);
        $summary = '';
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($summary . $sentence) < $maxLength && !empty($sentence)) {
                $summary .= $sentence . '. ';
            } else {
                break;
            }
        }
        
        return trim($summary);
    }
    
    private function generateSuggestions($context) {
        $suggestions = [
            'Help me with this page',
            'Explain this content',
            'Summarize this page',
            'What can you do?'
        ];
        
        // Add contextual suggestions based on page type
        $pageType = $this->detectPageType($context['content'] ?? '', $context['url'] ?? '');
        
        switch ($pageType) {
            case 'e-commerce':
                $suggestions[] = 'Tell me about the products';
                $suggestions[] = 'Help me find what I need';
                break;
            case 'content/article':
                $suggestions[] = 'Explain the main points';
                $suggestions[] = 'What are the key takeaways?';
                break;
            case 'administrative':
                $suggestions[] = 'Help me navigate this interface';
                $suggestions[] = 'Explain the features';
                break;
        }
        
        return array_slice($suggestions, 0, 6);
    }
    
    private function saveConversation($conversation, $url) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ai_conversations (url, created_at) 
                VALUES (?, NOW())
            ");
            $stmt->execute([$url]);
            $conversationId = $this->pdo->lastInsertId();
            
            // Save messages
            foreach ($conversation as $message) {
                $this->saveMessage($conversationId, $message['type'], $message['content']);
            }
            
            return $conversationId;
        } catch (Exception $e) {
            throw new Exception('Failed to save conversation: ' . $e->getMessage());
        }
    }
    
    private function saveMessage($conversationId, $type, $content) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ai_messages (conversation_id, type, content, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$conversationId, $type, $content]);
        } catch (Exception $e) {
            // Log error but don't fail the request
            error_log('Failed to save message: ' . $e->getMessage());
        }
    }
    
    private function getConversationHistory($conversationId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT type, content, created_at 
                FROM ai_messages 
                WHERE conversation_id = ? 
                ORDER BY created_at ASC
            ");
            $stmt->execute([$conversationId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to get conversation history: ' . $e->getMessage());
        }
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
    
    public function createAITables() {
        try {
            // Conversations table
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS ai_conversations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    url VARCHAR(500),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_url (url),
                    INDEX idx_created_at (created_at)
                )
            ");
            
            // Messages table
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS ai_messages (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    conversation_id INT,
                    type ENUM('user', 'assistant') NOT NULL,
                    content TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE,
                    INDEX idx_conversation_id (conversation_id),
                    INDEX idx_created_at (created_at)
                )
            ");
            
            return true;
        } catch (Exception $e) {
            error_log('Failed to create AI tables: ' . $e->getMessage());
            return false;
        }
    }
}

// Initialize and handle request
try {
    $api = new AIAssistantAPI();
    
    // Create tables if they don't exist
    $api->createAITables();
    
    // Handle the request
    $api->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 