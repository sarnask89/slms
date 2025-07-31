-- ML System Database Schema
-- This file contains all the SQL tables needed for the ML system

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

-- ML Performance Alerts table
CREATE TABLE IF NOT EXISTS ml_performance_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT NOT NULL,
    metric VARCHAR(50) NOT NULL,
    threshold DECIMAL(10,6) NOT NULL,
    alert_condition ENUM('above', 'below') DEFAULT 'below',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE,
    INDEX idx_model_id (model_id),
    INDEX idx_is_active (is_active)
);

-- ML Feature Store table
CREATE TABLE IF NOT EXISTS ml_feature_store (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    feature_type ENUM('numerical', 'categorical', 'text', 'datetime') NOT NULL,
    data_type VARCHAR(50) NOT NULL,
    default_value VARCHAR(255) NULL,
    is_required BOOLEAN DEFAULT FALSE,
    validation_rules JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_feature_type (feature_type),
    INDEX idx_is_required (is_required)
);

-- ML Model Features table (many-to-many relationship)
CREATE TABLE IF NOT EXISTS ml_model_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT NOT NULL,
    feature_id INT NOT NULL,
    feature_order INT DEFAULT 0,
    is_used BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE,
    FOREIGN KEY (feature_id) REFERENCES ml_feature_store(id) ON DELETE CASCADE,
    UNIQUE KEY unique_model_feature (model_id, feature_id),
    INDEX idx_model_id (model_id),
    INDEX idx_feature_id (feature_id)
);

-- ML Data Pipelines table
CREATE TABLE IF NOT EXISTS ml_data_pipelines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    pipeline_type ENUM('data_ingestion', 'feature_engineering', 'data_cleaning', 'model_training') NOT NULL,
    configuration JSON NOT NULL,
    status ENUM('active', 'inactive', 'error') DEFAULT 'active',
    last_executed TIMESTAMP NULL,
    execution_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_pipeline_type (pipeline_type),
    INDEX idx_status (status)
);

-- ML Experiment Tracking table
CREATE TABLE IF NOT EXISTS ml_experiments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    model_id INT NULL,
    experiment_config JSON NOT NULL,
    status ENUM('running', 'completed', 'failed', 'cancelled') DEFAULT 'running',
    results JSON NULL,
    metrics JSON NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    created_by INT,
    FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE SET NULL,
    INDEX idx_model_id (model_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
);

-- ML Model Deployment table
CREATE TABLE IF NOT EXISTS ml_model_deployments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT NOT NULL,
    version_id INT NULL,
    environment ENUM('development', 'staging', 'production') NOT NULL,
    deployment_status ENUM('pending', 'deploying', 'active', 'failed', 'rolled_back') DEFAULT 'pending',
    deployment_config JSON,
    endpoint_url VARCHAR(500) NULL,
    health_check_url VARCHAR(500) NULL,
    deployed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE,
    FOREIGN KEY (version_id) REFERENCES ml_model_versions(id) ON DELETE SET NULL,
    INDEX idx_model_id (model_id),
    INDEX idx_environment (environment),
    INDEX idx_deployment_status (deployment_status)
);

-- ML Model Monitoring table
CREATE TABLE IF NOT EXISTS ml_model_monitoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT NOT NULL,
    deployment_id INT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,6) NOT NULL,
    threshold_min DECIMAL(10,6) NULL,
    threshold_max DECIMAL(10,6) NULL,
    is_alert BOOLEAN DEFAULT FALSE,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE,
    FOREIGN KEY (deployment_id) REFERENCES ml_model_deployments(id) ON DELETE SET NULL,
    INDEX idx_model_id (model_id),
    INDEX idx_metric_name (metric_name),
    INDEX idx_recorded_at (recorded_at),
    INDEX idx_is_alert (is_alert)
);

-- Insert sample data for testing
INSERT INTO ml_models (name, type, algorithm, description, status, created_by) VALUES
('Customer Churn Predictor', 'classification', 'random_forest', 'Predicts customer churn based on usage patterns', 'draft', 1),
('Sales Forecast Model', 'regression', 'xgboost', 'Forecasts sales based on historical data and market conditions', 'draft', 1),
('Network Anomaly Detector', 'anomaly_detection', 'isolation_forest', 'Detects network anomalies in real-time', 'draft', 1),
('Product Recommendation Engine', 'recommendation', 'collaborative_filtering', 'Recommends products based on user behavior', 'draft', 1);

-- Insert sample features
INSERT INTO ml_feature_store (name, description, feature_type, data_type, is_required, validation_rules) VALUES
('feature1', 'First numerical feature', 'numerical', 'float', TRUE, '{"min": 0, "max": 100}'),
('feature2', 'Second numerical feature', 'numerical', 'float', TRUE, '{"min": 0, "max": 100}'),
('feature3', 'Third numerical feature', 'numerical', 'float', TRUE, '{"min": 0, "max": 100}'),
('feature4', 'Fourth numerical feature', 'numerical', 'float', FALSE, '{"min": 0, "max": 100}'),
('category', 'Categorical feature', 'categorical', 'string', FALSE, '{"allowed_values": ["A", "B", "C"]}');

-- Link features to models
INSERT INTO ml_model_features (model_id, feature_id, feature_order) VALUES
(1, 1, 1), (1, 2, 2), (1, 3, 3), (1, 5, 4),
(2, 1, 1), (2, 2, 2), (2, 3, 3), (2, 4, 4),
(3, 1, 1), (3, 2, 2), (3, 3, 3),
(4, 1, 1), (4, 2, 2), (4, 3, 3), (4, 5, 4); 