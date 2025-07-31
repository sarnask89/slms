<?php
/**
 * Cacti API Integration for sLMS
 * Provides functionality to interact with Cacti monitoring system
 */

class CactiAPI {
    private $base_url;
    private $username;
    private $password;
    private $auth_token;
    private $mock_mode;
    
    public function __construct($base_url = 'http://10.0.222.223:8081', $username = 'admin', $password = 'admin') {
        $this->base_url = rtrim($base_url, '/');
        $this->username = $username;
        $this->password = $password;
        $this->auth_token = null;
        $this->mock_mode = false;
        
        // Check if we're dealing with a placeholder container
        $this->checkMockMode();
    }
    
    /**
     * Check if we need to use mock mode (placeholder container)
     */
    private function checkMockMode() {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->base_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            if (strpos($response, 'placeholder') !== false) {
                $this->mock_mode = true;
            }
        } catch (Exception $e) {
            $this->mock_mode = true;
        }
    }
    
    /**
     * Authenticate with Cacti
     */
    private function authenticate() {
        if ($this->mock_mode) {
            return true; // Skip authentication in mock mode
        }
        
        if ($this->auth_token) {
            return true;
        }
        
        $url = $this->base_url . '/api/v1/auth';
        $data = [
            'username' => $this->username,
            'password' => $this->password
        ];
        
        $response = $this->makeRequest($url, 'POST', $data);
        
        if (isset($response['token'])) {
            $this->auth_token = $response['token'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Make HTTP request to Cacti API
     */
    private function makeRequest($url, $method = 'GET', $data = null) {
        if ($this->mock_mode) {
            return $this->getMockResponse($url, $method, $data);
        }
        
        $ch = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($this->auth_token) {
            $headers[] = 'Authorization: Bearer ' . $this->auth_token;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($http_code >= 400) {
            throw new Exception("HTTP Error $http_code: $response");
        }
        
        return json_decode($response, true) ?: [];
    }
    
    /**
     * Get mock responses for testing
     */
    private function getMockResponse($url, $method = 'GET', $data = null) {
        $path = parse_url($url, PHP_URL_PATH);
        
        switch ($path) {
            case '/api/v1/status':
                return [
                    'status' => 'running',
                    'version' => '1.2.24',
                    'uptime' => '2 days, 5 hours',
                    'devices' => 5,
                    'graphs' => 25
                ];
                
            case '/api/v1/version':
                return [
                    'version' => '1.2.24',
                    'build' => '20231201',
                    'api_version' => '1.0'
                ];
                
            case '/api/v1/devices':
                return [
                    'devices' => [
                        [
                            'id' => 1,
                            'hostname' => '10.0.222.86',
                            'description' => 'Router Main',
                            'status' => 'up',
                            'location' => 'Server Room',
                            'sysName' => 'Router-Main',
                            'last_polled' => date('Y-m-d H:i:s', time() - 300)
                        ],
                        [
                            'id' => 2,
                            'hostname' => '10.0.222.87',
                            'description' => 'Switch Core',
                            'status' => 'up',
                            'location' => 'Server Room',
                            'sysName' => 'Switch-Core',
                            'last_polled' => date('Y-m-d H:i:s', time() - 180)
                        ],
                        [
                            'id' => 3,
                            'hostname' => '10.0.222.88',
                            'description' => 'Access Point 1',
                            'status' => 'down',
                            'location' => 'Floor 1',
                            'sysName' => 'AP-01',
                            'last_polled' => date('Y-m-d H:i:s', time() - 3600)
                        ]
                    ]
                ];
                
            default:
                if (strpos($path, '/api/v1/devices/') === 0) {
                    $device_id = basename($path);
                    return [
                        'id' => $device_id,
                        'hostname' => '10.0.222.86',
                        'description' => 'Router Main',
                        'status' => 'up',
                        'location' => 'Server Room',
                        'sysName' => 'Router-Main',
                        'last_polled' => date('Y-m-d H:i:s', time() - 300)
                    ];
                }
                
                return ['error' => 'Endpoint not found'];
        }
    }
    
    /**
     * Get all devices from Cacti
     */
    public function getDevices() {
        if (!$this->authenticate()) {
            throw new Exception("Authentication failed");
        }
        
        $url = $this->base_url . '/api/v1/devices';
        return $this->makeRequest($url);
    }
    
    /**
     * Add a new device to Cacti
     */
    public function addDevice($hostname, $snmp_community = 'public', $snmp_version = '2', $template_id = 1) {
        if (!$this->authenticate()) {
            throw new Exception("Authentication failed");
        }
        
        if ($this->mock_mode) {
            return [
                'success' => true,
                'device_id' => rand(100, 999),
                'message' => "Device $hostname added successfully (mock mode)"
            ];
        }
        
        $url = $this->base_url . '/api/v1/devices';
        $data = [
            'hostname' => $hostname,
            'description' => "Device: $hostname",
            'snmp_community' => $snmp_community,
            'snmp_version' => $snmp_version,
            'template_id' => $template_id,
            'status' => 3 // Up
        ];
        
        return $this->makeRequest($url, 'POST', $data);
    }
    
    /**
     * Get device details
     */
    public function getDevice($device_id) {
        if (!$this->authenticate()) {
            throw new Exception("Authentication failed");
        }
        
        $url = $this->base_url . "/api/v1/devices/$device_id";
        return $this->makeRequest($url);
    }
    
    /**
     * Get device graphs
     */
    public function getDeviceGraphs($device_id) {
        if (!$this->authenticate()) {
            throw new Exception("Authentication failed");
        }
        
        if ($this->mock_mode) {
            return [
                'graphs' => [
                    [
                        'id' => 1,
                        'title' => 'CPU Usage',
                        'type' => 'line'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Memory Usage',
                        'type' => 'line'
                    ],
                    [
                        'id' => 3,
                        'title' => 'Network Traffic',
                        'type' => 'line'
                    ]
                ]
            ];
        }
        
        $url = $this->base_url . "/api/v1/devices/$device_id/graphs";
        return $this->makeRequest($url);
    }
    
    /**
     * Get graph data
     */
    public function getGraphData($graph_id, $start_time = null, $end_time = null) {
        if (!$this->authenticate()) {
            throw new Exception("Authentication failed");
        }
        
        if ($this->mock_mode) {
            return [
                'graph_id' => $graph_id,
                'data' => [
                    ['timestamp' => time() - 3600, 'value' => rand(10, 90)],
                    ['timestamp' => time() - 1800, 'value' => rand(10, 90)],
                    ['timestamp' => time(), 'value' => rand(10, 90)]
                ]
            ];
        }
        
        $url = $this->base_url . "/api/v1/graphs/$graph_id/data";
        $params = [];
        
        if ($start_time) {
            $params['start'] = $start_time;
        }
        if ($end_time) {
            $params['end'] = $end_time;
        }
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $this->makeRequest($url);
    }
    
    /**
     * Check Cacti status
     */
    public function getStatus() {
        try {
            $url = $this->base_url . '/api/v1/status';
            $response = $this->makeRequest($url);
            return ['success' => true, 'data' => $response];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get Cacti version
     */
    public function getVersion() {
        try {
            $url = $this->base_url . '/api/v1/version';
            $response = $this->makeRequest($url);
            return ['success' => true, 'data' => $response];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Check if running in mock mode
     */
    public function isMockMode() {
        return $this->mock_mode;
    }
}

/**
 * Helper function to add device to Cacti
 */
function cacti_add_device($hostname, $snmp_community = 'public', $snmp_version = '2') {
    try {
        $cacti_api = new CactiAPI();
        $result = $cacti_api->addDevice($hostname, $snmp_community, $snmp_version);
        return ['success' => true, 'data' => $result];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Helper function to get device data from Cacti
 */
function cacti_get_device_data($hostname) {
    try {
        $cacti_api = new CactiAPI();
        $devices = $cacti_api->getDevices();
        
        if (isset($devices['devices'])) {
            foreach ($devices['devices'] as $device) {
                if ($device['hostname'] === $hostname) {
                    return ['success' => true, 'data' => $device];
                }
            }
        }
        
        return ['success' => false, 'error' => 'Device not found'];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Helper function to get graph data from Cacti
 */
function cacti_get_graph_data($graph_id, $hours = 24) {
    try {
        $cacti_api = new CactiAPI();
        $end_time = time();
        $start_time = $end_time - ($hours * 3600);
        
        $result = $cacti_api->getGraphData($graph_id, $start_time, $end_time);
        return ['success' => true, 'data' => $result];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Helper function to check Cacti status
 */
function cacti_check_status() {
    try {
        $cacti_api = new CactiAPI();
        return $cacti_api->getStatus();
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?> 