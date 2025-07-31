<?php
/**
 * AI Research Engine - SLMS v1.2.0
 * Integration with LM Studio and DeepSeek for Advanced Network Research
 * 
 * Features:
 * - LM Studio local AI integration
 * - DeepSeek API integration
 * - Network pattern analysis
 * - Predictive maintenance
 * - Automated adaptation recommendations
 * - Research-driven improvements
 */

class AIResearchEngine {
    private $pdo;
    private $config;
    private $lmStudioConfig;
    private $deepSeekConfig;
    private $researchCache = [];
    private $lastResearchTime = 0;
    
    public function __construct() {
        $this->pdo = get_pdo();
        $this->loadConfigurations();
        $this->initializeResearchEngine();
    }
    
    /**
     * Load AI research configurations
     */
    private function loadConfigurations() {
        $this->config = [
            'research_interval' => 1800, // 30 minutes
            'cache_duration' => 3600, // 1 hour
            'max_research_items' => 100,
            'enable_local_ai' => true,
            'enable_cloud_ai' => true,
            'research_priority' => 'network_optimization'
        ];
        
        $this->lmStudioConfig = [
            'api_url' => 'http://localhost:1234/v1',
            'model' => 'local-model',
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'timeout' => 30
        ];
        
        $this->deepSeekConfig = [
            'api_url' => 'https://api.deepseek.com/v1',
            'api_key' => getenv('DEEPSEEK_API_KEY') ?: '',
            'model' => 'deepseek-chat',
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'timeout' => 30
        ];
    }
    
    /**
     * Initialize research engine
     */
    private function initializeResearchEngine() {
        $this->createResearchTables();
        $this->log('🧠 AI Research Engine initialized with LM Studio and DeepSeek integration');
    }
    
    /**
     * Create research database tables
     */
    private function createResearchTables() {
        $tables = [
            'ai_research_findings' => "
                CREATE TABLE IF NOT EXISTS ai_research_findings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    research_type VARCHAR(100) NOT NULL,
                    ai_provider VARCHAR(50) NOT NULL,
                    model_used VARCHAR(100),
                    research_query TEXT NOT NULL,
                    research_response TEXT NOT NULL,
                    confidence_score FLOAT DEFAULT 0.0,
                    relevance_score FLOAT DEFAULT 0.0,
                    implementation_status VARCHAR(20) DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'network_patterns' => "
                CREATE TABLE IF NOT EXISTS network_patterns (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    pattern_type VARCHAR(100) NOT NULL,
                    pattern_data TEXT NOT NULL,
                    confidence FLOAT DEFAULT 0.0,
                    frequency INTEGER DEFAULT 1,
                    first_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'adaptation_recommendations' => "
                CREATE TABLE IF NOT EXISTS adaptation_recommendations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    recommendation_type VARCHAR(100) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT NOT NULL,
                    priority VARCHAR(20) DEFAULT 'medium',
                    impact_score FLOAT DEFAULT 0.0,
                    implementation_complexity VARCHAR(20) DEFAULT 'medium',
                    ai_source VARCHAR(50),
                    status VARCHAR(20) DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    implemented_at TIMESTAMP NULL
                )
            ",
            'research_metrics' => "
                CREATE TABLE IF NOT EXISTS research_metrics (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    metric_name VARCHAR(100) NOT NULL,
                    metric_value FLOAT NOT NULL,
                    metric_unit VARCHAR(20),
                    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    context TEXT
                )
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            try {
                $this->pdo->exec($sql);
                $this->log("✅ Research table {$tableName} created/verified");
            } catch (Exception $e) {
                $this->log("❌ Failed to create research table {$tableName}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Conduct comprehensive AI research
     */
    public function conductAIResearch($networkData = null) {
        $this->log('🧠 Starting comprehensive AI research...');
        
        if (!$networkData) {
            $networkData = $this->getCurrentNetworkData();
        }
        
        $researchResults = [
            'lm_studio' => [],
            'deepseek' => [],
            'patterns' => [],
            'recommendations' => []
        ];
        
        // Research with LM Studio (local AI)
        if ($this->config['enable_local_ai']) {
            $researchResults['lm_studio'] = $this->researchWithLMStudio($networkData);
        }
        
        // Research with DeepSeek (cloud AI)
        if ($this->config['enable_cloud_ai'] && $this->deepSeekConfig['api_key']) {
            $researchResults['deepseek'] = $this->researchWithDeepSeek($networkData);
        }
        
        // Analyze network patterns
        $researchResults['patterns'] = $this->analyzeNetworkPatterns($networkData);
        
        // Generate adaptation recommendations
        $researchResults['recommendations'] = $this->generateAdaptationRecommendations($researchResults);
        
        // Store research findings
        $this->storeResearchFindings($researchResults);
        
        // Update research metrics
        $this->updateResearchMetrics($researchResults);
        
        $this->log('✅ AI research completed successfully');
        
        return $researchResults;
    }
    
    /**
     * Research with LM Studio (local AI)
     */
    private function researchWithLMStudio($networkData) {
        $this->log('🔬 Conducting research with LM Studio...');
        
        $researchQueries = [
            'network_optimization' => $this->generateNetworkOptimizationQuery($networkData),
            'security_analysis' => $this->generateSecurityAnalysisQuery($networkData),
            'performance_improvement' => $this->generatePerformanceQuery($networkData),
            'capacity_planning' => $this->generateCapacityPlanningQuery($networkData)
        ];
        
        $results = [];
        
        foreach ($researchQueries as $type => $query) {
            try {
                $response = $this->queryLMStudio($query);
                if ($response) {
                    $results[$type] = [
                        'query' => $query,
                        'response' => $response,
                        'confidence' => $this->calculateConfidence($response),
                        'timestamp' => time()
                    ];
                }
            } catch (Exception $e) {
                $this->log("❌ LM Studio research failed for {$type}: " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Query LM Studio API
     */
    private function queryLMStudio($query) {
        $url = $this->lmStudioConfig['api_url'] . '/chat/completions';
        
        $data = [
            'model' => $this->lmStudioConfig['model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert network engineer and AI research assistant. Provide detailed, actionable insights for network optimization and improvement.'
                ],
                [
                    'role' => 'user',
                    'content' => $query
                ]
            ],
            'temperature' => $this->lmStudioConfig['temperature'],
            'max_tokens' => $this->lmStudioConfig['max_tokens']
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data),
                'timeout' => $this->lmStudioConfig['timeout']
            ]
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('Failed to connect to LM Studio API');
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }
        
        return null;
    }
    
    /**
     * Research with DeepSeek (cloud AI)
     */
    private function researchWithDeepSeek($networkData) {
        $this->log('🔬 Conducting research with DeepSeek...');
        
        $researchQueries = [
            'advanced_optimization' => $this->generateAdvancedOptimizationQuery($networkData),
            'future_trends' => $this->generateFutureTrendsQuery($networkData),
            'best_practices' => $this->generateBestPracticesQuery($networkData),
            'emerging_threats' => $this->generateEmergingThreatsQuery($networkData)
        ];
        
        $results = [];
        
        foreach ($researchQueries as $type => $query) {
            try {
                $response = $this->queryDeepSeek($query);
                if ($response) {
                    $results[$type] = [
                        'query' => $query,
                        'response' => $response,
                        'confidence' => $this->calculateConfidence($response),
                        'timestamp' => time()
                    ];
                }
            } catch (Exception $e) {
                $this->log("❌ DeepSeek research failed for {$type}: " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Query DeepSeek API
     */
    private function queryDeepSeek($query) {
        $url = $this->deepSeekConfig['api_url'] . '/chat/completions';
        
        $data = [
            'model' => $this->deepSeekConfig['model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert network engineer and AI research assistant. Provide detailed, actionable insights for network optimization and improvement.'
                ],
                [
                    'role' => 'user',
                    'content' => $query
                ]
            ],
            'temperature' => $this->deepSeekConfig['temperature'],
            'max_tokens' => $this->deepSeekConfig['max_tokens']
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\nAuthorization: Bearer " . $this->deepSeekConfig['api_key'] . "\r\n",
                'method' => 'POST',
                'content' => json_encode($data),
                'timeout' => $this->deepSeekConfig['timeout']
            ]
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('Failed to connect to DeepSeek API');
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }
        
        return null;
    }
    
    /**
     * Generate research queries
     */
    private function generateNetworkOptimizationQuery($networkData) {
        $deviceCount = count($networkData['devices'] ?? []);
        $interfaceCount = count($networkData['interfaces'] ?? []);
        
        return "Analyze this network topology and provide specific optimization recommendations:

Network Statistics:
- Devices: {$deviceCount}
- Interfaces: {$interfaceCount}
- Device Types: " . implode(', ', array_unique(array_column($networkData['devices'] ?? [], 'device_type'))) . "

Current Network Data:
" . json_encode($networkData, JSON_PRETTY_PRINT) . "

Please provide:
1. Performance optimization strategies
2. Bandwidth utilization improvements
3. Network topology recommendations
4. Specific configuration changes
5. Monitoring and alerting suggestions";
    }
    
    private function generateSecurityAnalysisQuery($networkData) {
        return "Conduct a comprehensive security analysis of this network:

Network Data:
" . json_encode($networkData, JSON_PRETTY_PRINT) . "

Please analyze:
1. Potential security vulnerabilities
2. Access control recommendations
3. Network segmentation strategies
4. Monitoring and detection improvements
5. Incident response planning";
    }
    
    private function generatePerformanceQuery($networkData) {
        return "Analyze network performance and provide improvement recommendations:

Network Performance Data:
" . json_encode($networkData, JSON_PRETTY_PRINT) . "

Please provide:
1. Performance bottleneck identification
2. Capacity planning recommendations
3. QoS optimization strategies
4. Traffic engineering suggestions
5. Performance monitoring improvements";
    }
    
    private function generateCapacityPlanningQuery($networkData) {
        return "Provide capacity planning and scaling recommendations:

Current Network Capacity:
" . json_encode($networkData, JSON_PRETTY_PRINT) . "

Please analyze:
1. Current capacity utilization
2. Growth projections and recommendations
3. Infrastructure scaling strategies
4. Technology upgrade paths
5. Cost optimization suggestions";
    }
    
    private function generateAdvancedOptimizationQuery($networkData) {
        return "Provide advanced network optimization strategies using cutting-edge technologies:

Network Data:
" . json_encode($networkData, JSON_PRETTY_PRINT) . "

Please recommend:
1. SDN/NFV implementation strategies
2. AI/ML integration opportunities
3. Automation and orchestration improvements
4. Cloud integration strategies
5. Next-generation networking technologies";
    }
    
    private function generateFutureTrendsQuery($networkData) {
        return "Analyze current network trends and provide future-proofing recommendations:

Current Network:
" . json_encode($networkData, JSON_PRETTY_PRINT) . "

Please provide:
1. Emerging technology trends
2. Industry best practices
3. Future-proofing strategies
4. Technology adoption roadmap
5. Competitive advantage opportunities";
    }
    
    private function generateBestPracticesQuery($networkData) {
        return "Provide industry best practices and standards compliance recommendations:

Network Configuration:
" . json_encode($networkData, JSON_PRETTY_PRINT) . "

Please recommend:
1. Industry best practices
2. Standards compliance (ISO, NIST, etc.)
3. Documentation standards
4. Change management procedures
5. Quality assurance processes";
    }
    
    private function generateEmergingThreatsQuery($networkData) {
        return "Analyze emerging cybersecurity threats and provide mitigation strategies:

Network Security Context:
" . json_encode($networkData, JSON_PRETTY_PRINT) . "

Please analyze:
1. Emerging threat vectors
2. Advanced persistent threats
3. Zero-day vulnerability mitigation
4. Threat intelligence integration
5. Proactive defense strategies";
    }
    
    /**
     * Analyze network patterns
     */
    private function analyzeNetworkPatterns($networkData) {
        $this->log('🔍 Analyzing network patterns...');
        
        $patterns = [
            'traffic_patterns' => $this->analyzeTrafficPatterns($networkData),
            'device_patterns' => $this->analyzeDevicePatterns($networkData),
            'performance_patterns' => $this->analyzePerformancePatterns($networkData),
            'security_patterns' => $this->analyzeSecurityPatterns($networkData)
        ];
        
        // Store patterns in database
        foreach ($patterns as $type => $pattern) {
            if (!empty($pattern)) {
                $this->storeNetworkPattern($type, $pattern);
            }
        }
        
        return $patterns;
    }
    
    /**
     * Analyze traffic patterns
     */
    private function analyzeTrafficPatterns($networkData) {
        $interfaces = $networkData['interfaces'] ?? [];
        $patterns = [];
        
        // Analyze bandwidth utilization
        $totalBandwidth = 0;
        $utilizedBandwidth = 0;
        
        foreach ($interfaces as $interface) {
            $speed = $interface['speed'] ?? 0;
            $rxRate = $interface['transfer_rx_rate'] ?? 0;
            $txRate = $interface['transfer_tx_rate'] ?? 0;
            
            $totalBandwidth += $speed;
            $utilizedBandwidth += ($rxRate + $txRate);
        }
        
        if ($totalBandwidth > 0) {
            $utilization = ($utilizedBandwidth / $totalBandwidth) * 100;
            $patterns['bandwidth_utilization'] = round($utilization, 2);
        }
        
        // Analyze traffic distribution
        $trafficByDevice = [];
        foreach ($interfaces as $interface) {
            $device = $interface['device_hostname'] ?? 'unknown';
            $traffic = ($interface['transfer_rx_rate'] ?? 0) + ($interface['transfer_tx_rate'] ?? 0);
            
            if (!isset($trafficByDevice[$device])) {
                $trafficByDevice[$device] = 0;
            }
            $trafficByDevice[$device] += $traffic;
        }
        
        $patterns['traffic_distribution'] = $trafficByDevice;
        
        return $patterns;
    }
    
    /**
     * Analyze device patterns
     */
    private function analyzeDevicePatterns($networkData) {
        $devices = $networkData['devices'] ?? [];
        $patterns = [];
        
        // Device type distribution
        $deviceTypes = [];
        foreach ($devices as $device) {
            $type = $device['device_type'] ?? 'unknown';
            if (!isset($deviceTypes[$type])) {
                $deviceTypes[$type] = 0;
            }
            $deviceTypes[$type]++;
        }
        
        $patterns['device_type_distribution'] = $deviceTypes;
        
        // Vendor distribution
        $vendors = [];
        foreach ($devices as $device) {
            $vendor = $device['vendor'] ?? 'unknown';
            if (!isset($vendors[$vendor])) {
                $vendors[$vendor] = 0;
            }
            $vendors[$vendor]++;
        }
        
        $patterns['vendor_distribution'] = $vendors;
        
        return $patterns;
    }
    
    /**
     * Analyze performance patterns
     */
    private function analyzePerformancePatterns($networkData) {
        $interfaces = $networkData['interfaces'] ?? [];
        $patterns = [];
        
        // Interface performance analysis
        $performanceScores = [];
        foreach ($interfaces as $interface) {
            $speed = $interface['speed'] ?? 0;
            $rxRate = $interface['transfer_rx_rate'] ?? 0;
            $txRate = $interface['transfer_tx_rate'] ?? 0;
            
            if ($speed > 0) {
                $utilization = (($rxRate + $txRate) / $speed) * 100;
                $performanceScores[] = $utilization;
            }
        }
        
        if (!empty($performanceScores)) {
            $patterns['average_utilization'] = array_sum($performanceScores) / count($performanceScores);
            $patterns['max_utilization'] = max($performanceScores);
            $patterns['min_utilization'] = min($performanceScores);
        }
        
        return $patterns;
    }
    
    /**
     * Analyze security patterns
     */
    private function analyzeSecurityPatterns($networkData) {
        $devices = $networkData['devices'] ?? [];
        $patterns = [];
        
        // Device status analysis
        $onlineDevices = 0;
        $offlineDevices = 0;
        
        foreach ($devices as $device) {
            if (($device['status'] ?? '') === 'online') {
                $onlineDevices++;
            } else {
                $offlineDevices++;
            }
        }
        
        $patterns['device_availability'] = [
            'online' => $onlineDevices,
            'offline' => $offlineDevices,
            'availability_rate' => $onlineDevices > 0 ? ($onlineDevices / ($onlineDevices + $offlineDevices)) * 100 : 0
        ];
        
        return $patterns;
    }
    
    /**
     * Generate adaptation recommendations
     */
    private function generateAdaptationRecommendations($researchResults) {
        $this->log('💡 Generating adaptation recommendations...');
        
        $recommendations = [];
        
        // Process LM Studio recommendations
        if (!empty($researchResults['lm_studio'])) {
            foreach ($researchResults['lm_studio'] as $type => $result) {
                $recommendations = array_merge($recommendations, 
                    $this->extractRecommendations($result['response'], 'LM Studio', $type));
            }
        }
        
        // Process DeepSeek recommendations
        if (!empty($researchResults['deepseek'])) {
            foreach ($researchResults['deepseek'] as $type => $result) {
                $recommendations = array_merge($recommendations, 
                    $this->extractRecommendations($result['response'], 'DeepSeek', $type));
            }
        }
        
        // Process pattern-based recommendations
        if (!empty($researchResults['patterns'])) {
            $recommendations = array_merge($recommendations, 
                $this->generatePatternBasedRecommendations($researchResults['patterns']));
        }
        
        // Store recommendations
        foreach ($recommendations as $recommendation) {
            $this->storeAdaptationRecommendation($recommendation);
        }
        
        return $recommendations;
    }
    
    /**
     * Extract recommendations from AI responses
     */
    private function extractRecommendations($response, $source, $type) {
        $recommendations = [];
        
        // Simple extraction - in a real implementation, you'd use more sophisticated NLP
        $lines = explode("\n", $response);
        $currentRecommendation = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Look for numbered or bulleted recommendations
            if (preg_match('/^(\d+\.|\*|\-)\s*(.+)$/', $line, $matches)) {
                if ($currentRecommendation) {
                    $recommendations[] = $currentRecommendation;
                }
                
                $currentRecommendation = [
                    'title' => $matches[2],
                    'description' => $matches[2],
                    'type' => $type,
                    'source' => $source,
                    'priority' => 'medium',
                    'impact_score' => 0.5
                ];
            } elseif ($currentRecommendation && !empty($line)) {
                $currentRecommendation['description'] .= ' ' . $line;
            }
        }
        
        if ($currentRecommendation) {
            $recommendations[] = $currentRecommendation;
        }
        
        return $recommendations;
    }
    
    /**
     * Generate pattern-based recommendations
     */
    private function generatePatternBasedRecommendations($patterns) {
        $recommendations = [];
        
        // Traffic pattern recommendations
        if (isset($patterns['traffic_patterns']['bandwidth_utilization'])) {
            $utilization = $patterns['traffic_patterns']['bandwidth_utilization'];
            
            if ($utilization > 80) {
                $recommendations[] = [
                    'title' => 'High Bandwidth Utilization Detected',
                    'description' => "Current bandwidth utilization is {$utilization}%. Consider upgrading links or implementing traffic shaping.",
                    'type' => 'performance',
                    'source' => 'Pattern Analysis',
                    'priority' => 'high',
                    'impact_score' => 0.8
                ];
            }
        }
        
        // Device availability recommendations
        if (isset($patterns['security_patterns']['device_availability'])) {
            $availability = $patterns['security_patterns']['device_availability']['availability_rate'];
            
            if ($availability < 95) {
                $recommendations[] = [
                    'title' => 'Low Device Availability',
                    'description' => "Device availability is {$availability}%. Investigate offline devices and improve monitoring.",
                    'type' => 'security',
                    'source' => 'Pattern Analysis',
                    'priority' => 'high',
                    'impact_score' => 0.9
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Store research findings
     */
    private function storeResearchFindings($researchResults) {
        foreach (['lm_studio', 'deepseek'] as $provider) {
            if (!empty($researchResults[$provider])) {
                foreach ($researchResults[$provider] as $type => $result) {
                    try {
                        $stmt = $this->pdo->prepare("
                            INSERT INTO ai_research_findings 
                            (research_type, ai_provider, model_used, research_query, research_response, confidence_score)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $type,
                            $provider,
                            $provider === 'lm_studio' ? $this->lmStudioConfig['model'] : $this->deepSeekConfig['model'],
                            $result['query'],
                            $result['response'],
                            $result['confidence']
                        ]);
                    } catch (Exception $e) {
                        $this->log("❌ Failed to store research finding: " . $e->getMessage());
                    }
                }
            }
        }
    }
    
    /**
     * Store network pattern
     */
    private function storeNetworkPattern($type, $pattern) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT OR REPLACE INTO network_patterns 
                (pattern_type, pattern_data, confidence, frequency, last_seen)
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
            ");
            
            $stmt->execute([
                $type,
                json_encode($pattern),
                0.8, // Default confidence
                1
            ]);
        } catch (Exception $e) {
            $this->log("❌ Failed to store network pattern: " . $e->getMessage());
        }
    }
    
    /**
     * Store adaptation recommendation
     */
    private function storeAdaptationRecommendation($recommendation) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO adaptation_recommendations 
                (recommendation_type, title, description, priority, impact_score, ai_source)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $recommendation['type'],
                $recommendation['title'],
                $recommendation['description'],
                $recommendation['priority'],
                $recommendation['impact_score'],
                $recommendation['source']
            ]);
        } catch (Exception $e) {
            $this->log("❌ Failed to store adaptation recommendation: " . $e->getMessage());
        }
    }
    
    /**
     * Update research metrics
     */
    private function updateResearchMetrics($researchResults) {
        $metrics = [
            'total_research_items' => count($researchResults['lm_studio']) + count($researchResults['deepseek']),
            'average_confidence' => $this->calculateAverageConfidence($researchResults),
            'recommendations_generated' => count($researchResults['recommendations']),
            'patterns_identified' => count($researchResults['patterns'])
        ];
        
        foreach ($metrics as $name => $value) {
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO research_metrics (metric_name, metric_value, timestamp)
                    VALUES (?, ?, CURRENT_TIMESTAMP)
                ");
                $stmt->execute([$name, $value]);
            } catch (Exception $e) {
                $this->log("❌ Failed to store research metric: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Calculate confidence score
     */
    private function calculateConfidence($response) {
        // Simple confidence calculation based on response length and content
        $length = strlen($response);
        $confidence = min(1.0, $length / 1000); // Normalize to 0-1
        
        // Boost confidence if response contains specific keywords
        $keywords = ['recommend', 'optimize', 'improve', 'implement', 'configure'];
        foreach ($keywords as $keyword) {
            if (stripos($response, $keyword) !== false) {
                $confidence += 0.1;
            }
        }
        
        return min(1.0, $confidence);
    }
    
    /**
     * Calculate average confidence
     */
    private function calculateAverageConfidence($researchResults) {
        $confidences = [];
        
        foreach (['lm_studio', 'deepseek'] as $provider) {
            if (!empty($researchResults[$provider])) {
                foreach ($researchResults[$provider] as $result) {
                    $confidences[] = $result['confidence'];
                }
            }
        }
        
        return !empty($confidences) ? array_sum($confidences) / count($confidences) : 0;
    }
    
    /**
     * Get current network data
     */
    private function getCurrentNetworkData() {
        try {
            $data = [
                'devices' => [],
                'interfaces' => [],
                'transfer_stats' => []
            ];
            
            // Get devices
            $stmt = $this->pdo->query("SELECT * FROM discovered_devices ORDER BY hostname");
            $data['devices'] = $stmt->fetchAll();
            
            // Get interfaces
            $stmt = $this->pdo->query("SELECT * FROM network_interfaces ORDER BY device_id, interface_name");
            $data['interfaces'] = $stmt->fetchAll();
            
            return $data;
        } catch (Exception $e) {
            $this->log("❌ Failed to get current network data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get research statistics
     */
    public function getResearchStatistics() {
        try {
            $stats = [];
            
            $stats['total_findings'] = $this->pdo->query("SELECT COUNT(*) FROM ai_research_findings")->fetchColumn();
            $stats['total_recommendations'] = $this->pdo->query("SELECT COUNT(*) FROM adaptation_recommendations")->fetchColumn();
            $stats['implemented_recommendations'] = $this->pdo->query("SELECT COUNT(*) FROM adaptation_recommendations WHERE status = 'implemented'")->fetchColumn();
            $stats['total_patterns'] = $this->pdo->query("SELECT COUNT(*) FROM network_patterns")->fetchColumn();
            
            return $stats;
        } catch (Exception $e) {
            $this->log("❌ Failed to get research statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log message
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        
        error_log($logMessage, 3, '/var/log/slms/ai_research.log');
        echo $logMessage;
    }
} 