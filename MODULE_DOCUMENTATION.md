# sLMS Module Documentation

## 📋 Table of Contents
1. [Client Management](#client-management)
2. [Device Management](#device-management)
3. [Network Management](#network-management)
4. [Machine Learning System](#machine-learning-system)
5. [User Management](#user-management)
6. [DHCP Management](#dhcp-management)
7. [Billing System](#billing-system)
8. [Monitoring & Analytics](#monitoring--analytics)

---

## 🔧 Client Management

### Overview
The client management module handles all aspects of customer information, service assignments, and relationship management.

### Database Schema
```sql
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE client_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    service_type ENUM('internet', 'tv', 'phone', 'custom') NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    start_date DATE NOT NULL,
    end_date DATE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE client_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    device_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);
```

### API Endpoints

#### GET /api/clients
List all clients with optional filtering.
```bash
curl "http://your-domain/api/clients?status=active&limit=10&offset=0"
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "status": "active",
            "services_count": 2,
            "devices_count": 1,
            "created_at": "2024-01-01T00:00:00Z"
        }
    ],
    "total": 150,
    "limit": 10,
    "offset": 0
}
```

#### POST /api/clients
Create a new client.
```bash
curl -X POST "http://your-domain/api/clients" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567891",
    "address": "123 Main St, City, State",
    "services": [
        {
            "service_type": "internet",
            "service_name": "Premium Internet",
            "price": 99.99
        }
    ]
  }'
```

#### PUT /api/clients/{id}
Update client information.
```bash
curl -X PUT "http://your-domain/api/clients/1" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe Updated",
    "email": "john.updated@example.com"
  }'
```

#### DELETE /api/clients/{id}
Delete a client (soft delete).
```bash
curl -X DELETE "http://your-domain/api/clients/1"
```

### PHP Functions

#### ClientManager Class
```php
class ClientManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new client
     */
    public function createClient($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Insert client
            $sql = "INSERT INTO clients (name, email, phone, address) VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['address']]);
            $clientId = $this->pdo->lastInsertId();
            
            // Add services if provided
            if (isset($data['services'])) {
                foreach ($data['services'] as $service) {
                    $this->addClientService($clientId, $service);
                }
            }
            
            $this->pdo->commit();
            return ['success' => true, 'client_id' => $clientId];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get client details with services and devices
     */
    public function getClient($id) {
        $sql = "SELECT c.*, 
                       COUNT(DISTINCT cs.id) as services_count,
                       COUNT(DISTINCT cd.id) as devices_count
                FROM clients c
                LEFT JOIN client_services cs ON c.id = cs.client_id AND cs.status = 'active'
                LEFT JOIN client_devices cd ON c.id = cd.client_id AND cd.status = 'active'
                WHERE c.id = ?
                GROUP BY c.id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * List clients with filtering
     */
    public function getClients($filters = [], $limit = 10, $offset = 0) {
        $where = [];
        $params = [];
        
        if (isset($filters['status'])) {
            $where[] = "c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['search'])) {
            $where[] = "(c.name LIKE ? OR c.email LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT c.*, 
                       COUNT(DISTINCT cs.id) as services_count,
                       COUNT(DISTINCT cd.id) as devices_count
                FROM clients c
                LEFT JOIN client_services cs ON c.id = cs.client_id AND cs.status = 'active'
                LEFT JOIN client_devices cd ON c.id = cd.client_id AND cd.status = 'active'
                $whereClause
                GROUP BY c.id
                ORDER BY c.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

---

## 🔧 Device Management

### Overview
The device management module handles network device inventory, configuration, and monitoring.

### Database Schema
```sql
CREATE TABLE devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    mac_address VARCHAR(17),
    type ENUM('mikrotik', 'cisco', 'ubiquiti', 'other') NOT NULL,
    model VARCHAR(255),
    firmware_version VARCHAR(100),
    status ENUM('online', 'offline', 'maintenance', 'error') DEFAULT 'offline',
    last_seen TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE device_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    username VARCHAR(100),
    password VARCHAR(255),
    encryption_type ENUM('plain', 'encrypted') DEFAULT 'encrypted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);

CREATE TABLE device_interfaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    interface_name VARCHAR(100) NOT NULL,
    interface_type ENUM('ethernet', 'wifi', 'vlan', 'bridge') NOT NULL,
    ip_address VARCHAR(45),
    mac_address VARCHAR(17),
    status ENUM('up', 'down', 'disabled') DEFAULT 'down',
    speed INT,
    duplex ENUM('half', 'full', 'auto') DEFAULT 'auto',
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);
```

### API Endpoints

#### GET /api/devices
List all devices with status and monitoring information.
```bash
curl "http://your-domain/api/devices?status=online&type=mikrotik"
```

#### POST /api/devices
Add a new device.
```bash
curl -X POST "http://your-domain/api/devices" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Router-01",
    "ip_address": "192.168.1.1",
    "type": "mikrotik",
    "model": "RB4011",
    "credentials": {
        "username": "admin",
        "password": "password123"
    }
  }'
```

#### GET /api/devices/{id}/status
Get real-time device status and statistics.
```bash
curl "http://your-domain/api/devices/1/status"
```

### PHP Functions

#### DeviceManager Class
```php
class DeviceManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Add new device
     */
    public function addDevice($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Insert device
            $sql = "INSERT INTO devices (name, ip_address, mac_address, type, model) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['ip_address'],
                $data['mac_address'] ?? null,
                $data['type'],
                $data['model'] ?? null
            ]);
            $deviceId = $this->pdo->lastInsertId();
            
            // Add credentials if provided
            if (isset($data['credentials'])) {
                $this->addDeviceCredentials($deviceId, $data['credentials']);
            }
            
            $this->pdo->commit();
            return ['success' => true, 'device_id' => $deviceId];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Check device connectivity
     */
    public function checkDevice($deviceId) {
        $device = $this->getDevice($deviceId);
        if (!$device) {
            return ['success' => false, 'error' => 'Device not found'];
        }
        
        // Test connectivity based on device type
        switch ($device['type']) {
            case 'mikrotik':
                return $this->checkMikroTikDevice($device);
            case 'cisco':
                return $this->checkCiscoDevice($device);
            default:
                return $this->checkGenericDevice($device);
        }
    }
    
    /**
     * Get device statistics
     */
    public function getDeviceStats($deviceId) {
        $sql = "SELECT d.*, 
                       COUNT(di.id) as interface_count,
                       SUM(CASE WHEN di.status = 'up' THEN 1 ELSE 0 END) as active_interfaces
                FROM devices d
                LEFT JOIN device_interfaces di ON d.id = di.device_id
                WHERE d.id = ?
                GROUP BY d.id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$deviceId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
```

---

## 🔧 Network Management

### Overview
The network management module handles network infrastructure, DHCP configuration, and traffic monitoring.

### Database Schema
```sql
CREATE TABLE networks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    network_address VARCHAR(45) NOT NULL,
    subnet_mask VARCHAR(45) NOT NULL,
    gateway VARCHAR(45),
    dns_servers TEXT,
    vlan_id INT,
    description TEXT,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE dhcp_pools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    network_id INT NOT NULL,
    pool_name VARCHAR(255) NOT NULL,
    start_address VARCHAR(45) NOT NULL,
    end_address VARCHAR(45) NOT NULL,
    lease_time INT DEFAULT 86400,
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (network_id) REFERENCES networks(id) ON DELETE CASCADE
);

CREATE TABLE dhcp_leases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pool_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    mac_address VARCHAR(17) NOT NULL,
    client_id VARCHAR(255),
    hostname VARCHAR(255),
    lease_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lease_end TIMESTAMP,
    status ENUM('active', 'expired', 'released') DEFAULT 'active',
    FOREIGN KEY (pool_id) REFERENCES dhcp_pools(id) ON DELETE CASCADE
);
```

### API Endpoints

#### GET /api/networks
List all networks with DHCP information.
```bash
curl "http://your-domain/api/networks?status=active"
```

#### POST /api/networks
Create a new network with DHCP configuration.
```bash
curl -X POST "http://your-domain/api/networks" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "LAN-192.168.1.0",
    "network_address": "192.168.1.0",
    "subnet_mask": "255.255.255.0",
    "gateway": "192.168.1.1",
    "dns_servers": "8.8.8.8,8.8.4.4",
    "dhcp_pools": [
        {
            "pool_name": "main-pool",
            "start_address": "192.168.1.100",
            "end_address": "192.168.1.200",
            "lease_time": 86400
        }
    ]
  }'
```

#### GET /api/networks/{id}/dhcp
Get DHCP leases for a network.
```bash
curl "http://your-domain/api/networks/1/dhcp"
```

### PHP Functions

#### NetworkManager Class
```php
class NetworkManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Create network with DHCP pools
     */
    public function createNetwork($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Insert network
            $sql = "INSERT INTO networks (name, network_address, subnet_mask, gateway, dns_servers, vlan_id, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['network_address'],
                $data['subnet_mask'],
                $data['gateway'] ?? null,
                $data['dns_servers'] ?? null,
                $data['vlan_id'] ?? null,
                $data['description'] ?? null
            ]);
            $networkId = $this->pdo->lastInsertId();
            
            // Add DHCP pools if provided
            if (isset($data['dhcp_pools'])) {
                foreach ($data['dhcp_pools'] as $pool) {
                    $this->addDhcpPool($networkId, $pool);
                }
            }
            
            $this->pdo->commit();
            return ['success' => true, 'network_id' => $networkId];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get network statistics
     */
    public function getNetworkStats($networkId) {
        $sql = "SELECT n.*,
                       COUNT(dp.id) as pool_count,
                       COUNT(dl.id) as lease_count,
                       SUM(CASE WHEN dl.status = 'active' THEN 1 ELSE 0 END) as active_leases
                FROM networks n
                LEFT JOIN dhcp_pools dp ON n.id = dp.network_id
                LEFT JOIN dhcp_leases dl ON dp.id = dl.pool_id
                WHERE n.id = ?
                GROUP BY n.id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$networkId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
```

---

## 🤖 Machine Learning System

### Overview
The ML system provides predictive analytics, anomaly detection, and automated network optimization.

### Database Schema
```sql
CREATE TABLE ml_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('classification', 'regression', 'clustering', 'anomaly_detection') NOT NULL,
    algorithm VARCHAR(100) NOT NULL,
    status ENUM('draft', 'training', 'active', 'inactive', 'error') DEFAULT 'draft',
    accuracy DECIMAL(5,4),
    precision_score DECIMAL(5,4),
    recall_score DECIMAL(5,4),
    f1_score DECIMAL(5,4),
    parameters JSON,
    model_file_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_training TIMESTAMP NULL,
    created_by INT
);

CREATE TABLE ml_training_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT NOT NULL,
    status ENUM('pending', 'running', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    training_data_source VARCHAR(255),
    validation_split DECIMAL(3,2) DEFAULT 0.2,
    epochs INT DEFAULT 100,
    batch_size INT DEFAULT 32,
    learning_rate DECIMAL(10,6) DEFAULT 0.001,
    accuracy DECIMAL(5,4),
    precision_score DECIMAL(5,4),
    recall_score DECIMAL(5,4),
    f1_score DECIMAL(5,4),
    training_log TEXT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    duration_seconds DECIMAL(10,2),
    FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE
);

CREATE TABLE ml_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT NOT NULL,
    input_data JSON NOT NULL,
    prediction_result JSON NOT NULL,
    confidence DECIMAL(5,4),
    prediction_time DECIMAL(10,6),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE
);
```

### API Endpoints

#### GET /api/ml/models
List all ML models with performance metrics.
```bash
curl "http://your-domain/api/ml/models?status=active"
```

#### POST /api/ml/models
Create a new ML model.
```bash
curl -X POST "http://your-domain/api/ml/models" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Network Anomaly Detection",
    "type": "anomaly_detection",
    "algorithm": "isolation_forest",
    "parameters": {
        "n_estimators": 100,
        "contamination": 0.1,
        "random_state": 42
    }
  }'
```

#### POST /api/ml/models/{id}/train
Train a model with data.
```bash
curl -X POST "http://your-domain/api/ml/models/1/train" \
  -H "Content-Type: application/json" \
  -d '{
    "training_data": {
        "features": ["bandwidth", "latency", "packet_loss"],
        "data": [
            [1000000000, 10, 0.1],
            [500000000, 50, 2.5],
            [750000000, 25, 0.5]
        ]
    }
  }'
```

#### POST /api/ml/models/{id}/predict
Make predictions using a trained model.
```bash
curl -X POST "http://your-domain/api/ml/models/1/predict" \
  -H "Content-Type: application/json" \
  -d '{
    "input_data": {
        "bandwidth": 800000000,
        "latency": 30,
        "packet_loss": 0.2
    }
  }'
```

### PHP Functions

#### MLModelManager Class
```php
class MLModelManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Create new ML model
     */
    public function createModel($data) {
        try {
            $sql = "INSERT INTO ml_models (name, type, algorithm, parameters, created_by) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['type'],
                $data['algorithm'],
                json_encode($data['parameters'] ?? []),
                $_SESSION['user_id'] ?? 1
            ]);
            
            return ['success' => true, 'model_id' => $this->pdo->lastInsertId()];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Train model
     */
    public function trainModel($modelId, $trainingData) {
        try {
            // Create training session
            $sql = "INSERT INTO ml_training_sessions (model_id, status, training_data_source) 
                    VALUES (?, 'pending', ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$modelId, 'manual']);
            $sessionId = $this->pdo->lastInsertId();
            
            // Update model status
            $this->updateModelStatus($modelId, 'training');
            
            // Start training process (this would typically be done in a background job)
            $trainingResult = $this->executeTraining($modelId, $sessionId, $trainingData);
            
            if ($trainingResult['success']) {
                $this->updateModelStatus($modelId, 'active');
                $this->updateModelMetrics($modelId, $trainingResult);
            } else {
                $this->updateModelStatus($modelId, 'error');
            }
            
            return $trainingResult;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Make prediction
     */
    public function predict($modelId, $inputData) {
        try {
            $startTime = microtime(true);
            
            // Get model details
            $model = $this->getModel($modelId);
            if (!$model || $model['status'] !== 'active') {
                return ['success' => false, 'error' => 'Model not available'];
            }
            
            // Make prediction (simulated)
            $prediction = $this->executePrediction($model, $inputData);
            
            $endTime = microtime(true);
            $predictionTime = $endTime - $startTime;
            
            // Save prediction
            $this->savePrediction($modelId, $inputData, $prediction, $predictionTime);
            
            return [
                'success' => true,
                'prediction' => $prediction,
                'prediction_time' => $predictionTime
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
```

---

## 👥 User Management

### Overview
The user management module handles user accounts, permissions, and access control.

### Database Schema
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    role ENUM('admin', 'manager', 'user', 'viewer') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    granted BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_permission (user_id, resource, action)
);

CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### API Endpoints

#### POST /api/auth/login
User authentication.
```bash
curl -X POST "http://your-domain/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "password123"
  }'
```

#### GET /api/users
List all users (admin only).
```bash
curl "http://your-domain/api/users?role=manager"
```

#### POST /api/users
Create new user (admin only).
```bash
curl -X POST "http://your-domain/api/users" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "newuser",
    "email": "user@example.com",
    "password": "password123",
    "role": "user",
    "permissions": [
        {"resource": "clients", "action": "read"},
        {"resource": "devices", "action": "read"}
    ]
  }'
```

### PHP Functions

#### UserManager Class
```php
class UserManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ? AND status = 'active'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Create session
            $sessionId = $this->createSession($user['id']);
            
            return [
                'success' => true,
                'user' => $user,
                'session_id' => $sessionId
            ];
        }
        
        return ['success' => false, 'error' => 'Invalid credentials'];
    }
    
    /**
     * Check user permissions
     */
    public function checkPermission($userId, $resource, $action) {
        $sql = "SELECT up.granted, u.role 
                FROM users u
                LEFT JOIN user_permissions up ON u.id = up.user_id 
                    AND up.resource = ? AND up.action = ?
                WHERE u.id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$resource, $action, $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Admin has all permissions
        if ($result['role'] === 'admin') {
            return true;
        }
        
        // Check specific permission
        return $result['granted'] ?? false;
    }
    
    /**
     * Create new user
     */
    public function createUser($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, role) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['username'],
                $data['email'],
                $passwordHash,
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['role'] ?? 'user'
            ]);
            $userId = $this->pdo->lastInsertId();
            
            // Add permissions if provided
            if (isset($data['permissions'])) {
                foreach ($data['permissions'] as $permission) {
                    $this->addPermission($userId, $permission['resource'], $permission['action']);
                }
            }
            
            $this->pdo->commit();
            return ['success' => true, 'user_id' => $userId];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
```

---

## 📊 Monitoring & Analytics

### Overview
The monitoring module provides real-time system monitoring, performance analytics, and reporting.

### Database Schema
```sql
CREATE TABLE system_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,6) NOT NULL,
    metric_unit VARCHAR(20),
    category VARCHAR(50),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric_time (metric_name, timestamp)
);

CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM('info', 'warning', 'error', 'critical') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    source VARCHAR(100),
    acknowledged BOOLEAN DEFAULT FALSE,
    acknowledged_by INT,
    acknowledged_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id)
);

CREATE TABLE performance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    response_time DECIMAL(10,6) NOT NULL,
    status_code INT NOT NULL,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### API Endpoints

#### GET /api/monitoring/metrics
Get system metrics.
```bash
curl "http://your-domain/api/monitoring/metrics?category=system&limit=100"
```

#### GET /api/monitoring/alerts
Get active alerts.
```bash
curl "http://your-domain/api/monitoring/alerts?type=critical"
```

#### POST /api/monitoring/alerts/{id}/acknowledge
Acknowledge an alert.
```bash
curl -X POST "http://your-domain/api/monitoring/alerts/1/acknowledge"
```

### PHP Functions

#### MonitoringManager Class
```php
class MonitoringManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Record system metric
     */
    public function recordMetric($name, $value, $unit = null, $category = 'system') {
        $sql = "INSERT INTO system_metrics (metric_name, metric_value, metric_unit, category) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$name, $value, $unit, $category]);
    }
    
    /**
     * Get system health status
     */
    public function getSystemHealth() {
        $metrics = [
            'cpu_usage' => $this->getLatestMetric('cpu_usage'),
            'memory_usage' => $this->getLatestMetric('memory_usage'),
            'disk_usage' => $this->getLatestMetric('disk_usage'),
            'network_traffic' => $this->getLatestMetric('network_traffic'),
            'active_users' => $this->getActiveUserCount(),
            'active_devices' => $this->getActiveDeviceCount()
        ];
        
        $health = 'healthy';
        foreach ($metrics as $metric) {
            if ($metric && $metric['metric_value'] > 90) {
                $health = 'warning';
            }
            if ($metric && $metric['metric_value'] > 95) {
                $health = 'critical';
                break;
            }
        }
        
        return [
            'status' => $health,
            'metrics' => $metrics,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Create alert
     */
    public function createAlert($type, $title, $message, $source = null) {
        $sql = "INSERT INTO alerts (alert_type, title, message, source) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$type, $title, $message, $source]);
    }
    
    /**
     * Get performance statistics
     */
    public function getPerformanceStats($timeframe = '24h') {
        $sql = "SELECT 
                    AVG(response_time) as avg_response_time,
                    MAX(response_time) as max_response_time,
                    COUNT(*) as total_requests,
                    COUNT(CASE WHEN status_code >= 400 THEN 1 END) as error_count
                FROM performance_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$timeframe]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
```

---

## 📋 Integration Examples

### Webhook Integration
```php
// Send webhook notification
function sendWebhook($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// Example: Notify when new client is added
$webhookData = [
    'event' => 'client.created',
    'client_id' => $clientId,
    'client_name' => $clientName,
    'timestamp' => date('Y-m-d H:i:s')
];

sendWebhook('https://your-webhook-url.com/notify', $webhookData);
```

### External API Integration
```php
// Example: Integrate with external monitoring service
function sendToExternalMonitoring($data) {
    $apiKey = 'your-api-key';
    $endpoint = 'https://api.external-monitoring.com/metrics';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
```

---

## 🔧 Configuration Files

### Main Configuration (`config.php`)
```php
<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'slmsdb';
$db_user = 'slms';
$db_pass = 'your_secure_password';
$db_charset = 'utf8mb4';

// System configuration
$system_name = 'sLMS';
$system_url = 'http://your-domain.com';
$timezone = 'UTC';
$debug_mode = false;

// Security configuration
$session_timeout = 3600;
$max_login_attempts = 5;
$password_min_length = 8;
$csrf_token_timeout = 1800;

// ML system configuration
$ml_enabled = true;
$ml_max_training_jobs = 5;
$ml_prediction_batch_size = 100;
$ml_model_retention_days = 30;

// Monitoring configuration
$monitoring_enabled = true;
$metrics_collection_interval = 60;
$alert_check_interval = 300;
$performance_logging = true;

// API configuration
$api_rate_limit = 1000; // requests per hour
$api_key_expiration_days = 365;
$webhook_timeout = 30;

// File upload configuration
$max_upload_size = '10M';
$allowed_file_types = ['jpg', 'png', 'pdf', 'txt'];
$upload_directory = '/var/www/html/uploads/';

// Cache configuration
$cache_enabled = true;
$cache_driver = 'file'; // file, redis, memcached
$cache_ttl = 3600;

// Logging configuration
$log_level = 'INFO'; // DEBUG, INFO, WARNING, ERROR
$log_file = '/var/www/html/logs/slms.log';
$log_max_size = '10M';
$log_rotation = 7; // days

// Email configuration
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_username = 'your-email@gmail.com';
$smtp_password = 'your-app-password';
$smtp_encryption = 'tls';

// Backup configuration
$backup_enabled = true;
$backup_schedule = '0 2 * * *'; // Daily at 2 AM
$backup_retention_days = 30;
$backup_directory = '/backup/slms/';

// Performance configuration
$opcache_enabled = true;
$opcache_memory_consumption = 128;
$opcache_max_accelerated_files = 4000;
$opcache_revalidate_freq = 2;

// Session configuration
$session_handler = 'files'; // files, redis, memcached
$session_lifetime = 3600;
$session_cookie_secure = true;
$session_cookie_httponly = true;

// Database connection function
function get_pdo() {
    global $db_host, $db_name, $db_user, $db_pass, $db_charset;
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, $db_user, $db_pass, $options);
}
?>
```

---

**Version**: 2.0  
**Last Updated**: 2024  
**For complete system documentation**: See SLMS_COMPREHENSIVE_README.md 