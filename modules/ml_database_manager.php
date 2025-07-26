<?php
/**
 * ML Database Manager
 * Handles all database operations for the ML system
 */

class MLDatabaseManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->createTables();
    }
    
    /**
     * Create ML system tables if they don't exist
     */
    private function createTables() {
        $sql = "
        -- ML Models table
        CREATE TABLE IF NOT EXISTS ml_models (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type ENUM('classification', 'regression', 'clustering', 'anomaly_detection', 'recommendation') NOT NULL,
            algorithm VARCHAR(100) NOT NULL,
            description TEXT,
            parameters JSON,
            status ENUM('draft', 'training', 'active', 'inactive', 'error') DEFAULT 'draft',
            accuracy DECIMAL(5,4) NULL,
            precision_score DECIMAL(5,4) NULL,
            recall_score DECIMAL(5,4) NULL,
            f1_score DECIMAL(5,4) NULL,
            model_file_path VARCHAR(500) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_training TIMESTAMP NULL,
            created_by INT,
            INDEX idx_status (status),
            INDEX idx_type (type),
            INDEX idx_algorithm (algorithm)
        );
        
        -- ML Training Sessions table
        CREATE TABLE IF NOT EXISTS ml_training_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            model_id INT NOT NULL,
            status ENUM('pending', 'running', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
            training_data_source VARCHAR(255),
            validation_split DECIMAL(3,2) DEFAULT 0.2,
            epochs INT DEFAULT 100,
            batch_size INT DEFAULT 32,
            learning_rate DECIMAL(10,8) DEFAULT 0.001,
            accuracy DECIMAL(5,4) NULL,
            precision_score DECIMAL(5,4) NULL,
            recall_score DECIMAL(5,4) NULL,
            f1_score DECIMAL(5,4) NULL,
            loss DECIMAL(10,6) NULL,
            training_log TEXT,
            started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            duration_seconds INT NULL,
            created_by INT,
            FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE,
            INDEX idx_model_id (model_id),
            INDEX idx_status (status),
            INDEX idx_started_at (started_at)
        );
        
        -- ML Predictions table
        CREATE TABLE IF NOT EXISTS ml_predictions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            model_id INT NOT NULL,
            input_data JSON NOT NULL,
            prediction_result JSON NOT NULL,
            confidence DECIMAL(5,4) NULL,
            prediction_time DECIMAL(10,6) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE,
            INDEX idx_model_id (model_id),
            INDEX idx_created_at (created_at)
        );
        
        -- ML Datasets table
        CREATE TABLE IF NOT EXISTS ml_datasets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            type ENUM('training', 'validation', 'test', 'custom') DEFAULT 'training',
            file_path VARCHAR(500) NOT NULL,
            file_size BIGINT,
            row_count INT,
            column_count INT,
            schema JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            INDEX idx_type (type),
            INDEX idx_created_at (created_at)
        );
        
        -- ML Performance Metrics table
        CREATE TABLE IF NOT EXISTS ml_performance_metrics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            model_id INT NOT NULL,
            training_session_id INT NULL,
            metric_type ENUM('accuracy', 'precision', 'recall', 'f1_score', 'loss', 'mae', 'mse', 'rmse') NOT NULL,
            metric_value DECIMAL(10,6) NOT NULL,
            recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE,
            FOREIGN KEY (training_session_id) REFERENCES ml_training_sessions(id) ON DELETE SET NULL,
            INDEX idx_model_id (model_id),
            INDEX idx_metric_type (metric_type),
            INDEX idx_recorded_at (recorded_at)
        );
        
        -- ML Model Versions table
        CREATE TABLE IF NOT EXISTS ml_model_versions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            model_id INT NOT NULL,
            version_number VARCHAR(50) NOT NULL,
            model_file_path VARCHAR(500) NOT NULL,
            accuracy DECIMAL(5,4) NULL,
            training_session_id INT NULL,
            is_active BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE,
            FOREIGN KEY (training_session_id) REFERENCES ml_training_sessions(id) ON DELETE SET NULL,
            UNIQUE KEY unique_model_version (model_id, version_number),
            INDEX idx_model_id (model_id),
            INDEX idx_is_active (is_active)
        );
        
        -- ML Automation Rules table
        CREATE TABLE IF NOT EXISTS ml_automation_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            model_id INT NOT NULL,
            trigger_type ENUM('schedule', 'performance_threshold', 'data_change', 'manual') NOT NULL,
            trigger_conditions JSON,
            action_type ENUM('retrain', 'deploy', 'notify', 'archive') NOT NULL,
            action_parameters JSON,
            is_active BOOLEAN DEFAULT TRUE,
            last_executed TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE,
            INDEX idx_model_id (model_id),
            INDEX idx_is_active (is_active),
            INDEX idx_trigger_type (trigger_type)
        );
        ";
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating ML tables: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new ML model
     */
    public function createModel($data) {
        try {
            $sql = "INSERT INTO ml_models (name, type, algorithm, description, parameters, created_by) 
                    VALUES (:name, :type, :algorithm, :description, :parameters, :created_by)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':type' => $data['type'],
                ':algorithm' => $data['algorithm'],
                ':description' => $data['description'],
                ':parameters' => $data['parameters'],
                ':created_by' => $_SESSION['user_id'] ?? 1
            ]);
            
            return ['success' => true, 'id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get all models
     */
    public function getAllModels() {
        try {
            $sql = "SELECT * FROM ml_models ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting models: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get model by ID
     */
    public function getModel($id) {
        try {
            $sql = "SELECT * FROM ml_models WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting model: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update model
     */
    public function updateModel($id, $data) {
        try {
            $sql = "UPDATE ml_models SET 
                    name = :name, 
                    description = :description, 
                    parameters = :parameters,
                    updated_at = CURRENT_TIMESTAMP 
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':parameters' => $data['parameters']
            ]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Delete model
     */
    public function deleteModel($id) {
        try {
            $sql = "DELETE FROM ml_models WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create training session
     */
    public function createTrainingSession($modelId, $data) {
        try {
            $sql = "INSERT INTO ml_training_sessions 
                    (model_id, training_data_source, validation_split, epochs, batch_size, learning_rate, created_by) 
                    VALUES (:model_id, :training_data_source, :validation_split, :epochs, :batch_size, :learning_rate, :created_by)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':model_id' => $modelId,
                ':training_data_source' => $data['training_data_source'],
                ':validation_split' => $data['validation_split'],
                ':epochs' => $data['epochs'],
                ':batch_size' => $data['batch_size'] ?? 32,
                ':learning_rate' => $data['learning_rate'] ?? 0.001,
                ':created_by' => $_SESSION['user_id'] ?? 1
            ]);
            
            return ['success' => true, 'id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Update training session
     */
    public function updateTrainingSession($id, $data) {
        try {
            $sql = "UPDATE ml_training_sessions SET 
                    status = :status,
                    accuracy = :accuracy,
                    precision_score = :precision_score,
                    recall_score = :recall_score,
                    f1_score = :f1_score,
                    loss = :loss,
                    training_log = :training_log,
                    completed_at = :completed_at,
                    duration_seconds = :duration_seconds
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':status' => $data['status'],
                ':accuracy' => $data['accuracy'] ?? null,
                ':precision_score' => $data['precision_score'] ?? null,
                ':recall_score' => $data['recall_score'] ?? null,
                ':f1_score' => $data['f1_score'] ?? null,
                ':loss' => $data['loss'] ?? null,
                ':training_log' => $data['training_log'] ?? null,
                ':completed_at' => $data['completed_at'] ?? null,
                ':duration_seconds' => $data['duration_seconds'] ?? null
            ]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Save prediction
     */
    public function savePrediction($modelId, $inputData, $predictionResult, $confidence = null, $predictionTime = null) {
        try {
            $sql = "INSERT INTO ml_predictions 
                    (model_id, input_data, prediction_result, confidence, prediction_time, created_by) 
                    VALUES (:model_id, :input_data, :prediction_result, :confidence, :prediction_time, :created_by)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':model_id' => $modelId,
                ':input_data' => json_encode($inputData),
                ':prediction_result' => json_encode($predictionResult),
                ':confidence' => $confidence,
                ':prediction_time' => $predictionTime,
                ':created_by' => $_SESSION['user_id'] ?? 1
            ]);
            
            return ['success' => true, 'id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get model statistics
     */
    public function getModelStatistics() {
        try {
            $stats = [];
            
            // Total models
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM ml_models");
            $stats['total_models'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Active models
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM ml_models WHERE status = 'active'");
            $stats['active_models'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Training sessions
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM ml_training_sessions");
            $stats['training_sessions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Predictions
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM ml_predictions");
            $stats['predictions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return [
                'total_models' => 0,
                'active_models' => 0,
                'training_sessions' => 0,
                'predictions' => 0
            ];
        }
    }
    
    /**
     * Get recent training sessions
     */
    public function getRecentTrainingSessions($limit = 10) {
        try {
            $sql = "SELECT ts.*, m.name as model_name 
                    FROM ml_training_sessions ts 
                    JOIN ml_models m ON ts.model_id = m.id 
                    ORDER BY ts.started_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting recent training sessions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get model performance metrics
     */
    public function getModelPerformanceMetrics($modelId, $limit = 100) {
        try {
            $sql = "SELECT * FROM ml_performance_metrics 
                    WHERE model_id = :model_id 
                    ORDER BY recorded_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':model_id', $modelId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting performance metrics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save performance metric
     */
    public function savePerformanceMetric($modelId, $metricType, $metricValue, $trainingSessionId = null) {
        try {
            $sql = "INSERT INTO ml_performance_metrics 
                    (model_id, training_session_id, metric_type, metric_value) 
                    VALUES (:model_id, :training_session_id, :metric_type, :metric_value)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':model_id' => $modelId,
                ':training_session_id' => $trainingSessionId,
                ':metric_type' => $metricType,
                ':metric_value' => $metricValue
            ]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
} 