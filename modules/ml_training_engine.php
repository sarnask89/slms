<?php
/**
 * ML Training Engine
 * Handles model training operations and pipeline management
 */

class MLTrainingEngine {
    private $pdo;
    private $db_manager;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->db_manager = new MLDatabaseManager($pdo);
    }
    
    /**
     * Train a model
     */
    public function trainModel($modelId, $trainingData = null) {
        try {
            // Get model details
            $model = $this->db_manager->getModel($modelId);
            if (!$model) {
                return ['success' => false, 'error' => 'Model not found'];
            }
            
            // Create training session
            $sessionData = [
                'training_data_source' => $trainingData ?? 'auto',
                'validation_split' => 0.2,
                'epochs' => 100,
                'batch_size' => 32,
                'learning_rate' => 0.001
            ];
            
            $sessionResult = $this->db_manager->createTrainingSession($modelId, $sessionData);
            if (!$sessionResult['success']) {
                return $sessionResult;
            }
            
            $trainingId = $sessionResult['id'];
            
            // Update model status to training
            $this->db_manager->updateModel($modelId, ['status' => 'training']);
            
            // Start training process (this would typically be done in a background job)
            $trainingResult = $this->executeTraining($model, $trainingId, $sessionData);
            
            if ($trainingResult['success']) {
                // Update model with training results
                $this->db_manager->updateModel($modelId, [
                    'status' => 'active',
                    'accuracy' => $trainingResult['accuracy'],
                    'precision_score' => $trainingResult['precision'],
                    'recall_score' => $trainingResult['recall'],
                    'f1_score' => $trainingResult['f1_score'],
                    'last_training' => date('Y-m-d H:i:s')
                ]);
                
                // Save performance metrics
                $this->db_manager->savePerformanceMetric($modelId, 'accuracy', $trainingResult['accuracy'], $trainingId);
                $this->db_manager->savePerformanceMetric($modelId, 'precision', $trainingResult['precision'], $trainingId);
                $this->db_manager->savePerformanceMetric($modelId, 'recall', $trainingResult['recall'], $trainingId);
                $this->db_manager->savePerformanceMetric($modelId, 'f1_score', $trainingResult['f1_score'], $trainingId);
                
                return [
                    'success' => true,
                    'training_id' => $trainingId,
                    'accuracy' => $trainingResult['accuracy'],
                    'message' => 'Training completed successfully'
                ];
            } else {
                // Update model status to error
                $this->db_manager->updateModel($modelId, ['status' => 'error']);
                
                return [
                    'success' => false,
                    'error' => $trainingResult['error'],
                    'training_id' => $trainingId
                ];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Execute the actual training process
     * This is a simplified implementation - in production, this would use actual ML libraries
     */
    private function executeTraining($model, $trainingId, $sessionData) {
        try {
            // Simulate training process
            $startTime = microtime(true);
            
            // Update training session status to running
            $this->db_manager->updateTrainingSession($trainingId, ['status' => 'running']);
            
            // Simulate training epochs
            for ($epoch = 1; $epoch <= $sessionData['epochs']; $epoch++) {
                // Simulate training progress
                $progress = ($epoch / $sessionData['epochs']) * 100;
                
                // Update training log
                $logEntry = "Epoch {$epoch}/{$sessionData['epochs']} - Progress: " . round($progress, 2) . "%\n";
                $this->db_manager->updateTrainingSession($trainingId, [
                    'training_log' => $logEntry,
                    'status' => 'running'
                ]);
                
                // Simulate training time
                usleep(100000); // 0.1 second per epoch
            }
            
            // Simulate training results
            $accuracy = rand(7500, 9500) / 100; // 75% to 95%
            $precision = rand(7000, 9000) / 100;
            $recall = rand(7000, 9000) / 100;
            $f1_score = ($precision + $recall) / 2;
            
            $endTime = microtime(true);
            $duration = $endTime - $startTime;
            
            // Update training session with results
            $this->db_manager->updateTrainingSession($trainingId, [
                'status' => 'completed',
                'accuracy' => $accuracy,
                'precision_score' => $precision,
                'recall_score' => $recall,
                'f1_score' => $f1_score,
                'completed_at' => date('Y-m-d H:i:s'),
                'duration_seconds' => round($duration, 2)
            ]);
            
            return [
                'success' => true,
                'accuracy' => $accuracy,
                'precision' => $precision,
                'recall' => $recall,
                'f1_score' => $f1_score,
                'duration' => round($duration, 2)
            ];
            
        } catch (Exception $e) {
            // Update training session status to failed
            $this->db_manager->updateTrainingSession($trainingId, [
                'status' => 'failed',
                'training_log' => 'Training failed: ' . $e->getMessage()
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get recent training sessions
     */
    public function getRecentTrainings($limit = 5) {
        return $this->db_manager->getRecentTrainingSessions($limit);
    }
    
    /**
     * Get training session details
     */
    public function getTrainingSession($id) {
        try {
            $sql = "SELECT ts.*, m.name as model_name, m.algorithm 
                    FROM ml_training_sessions ts 
                    JOIN ml_models m ON ts.model_id = m.id 
                    WHERE ts.id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting training session: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cancel training session
     */
    public function cancelTraining($trainingId) {
        try {
            $result = $this->db_manager->updateTrainingSession($trainingId, [
                'status' => 'cancelled',
                'completed_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result['success']) {
                // Get model ID and update model status
                $session = $this->getTrainingSession($trainingId);
                if ($session) {
                    $this->db_manager->updateModel($session['model_id'], ['status' => 'draft']);
                }
            }
            
            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Retrain model with new data
     */
    public function retrainModel($modelId, $newData = null) {
        // First, archive current model version
        $this->archiveModelVersion($modelId);
        
        // Then train new model
        return $this->trainModel($modelId, $newData);
    }
    
    /**
     * Archive current model version
     */
    private function archiveModelVersion($modelId) {
        try {
            $model = $this->db_manager->getModel($modelId);
            if (!$model) {
                return false;
            }
            
            // Create version record
            $sql = "INSERT INTO ml_model_versions 
                    (model_id, version_number, model_file_path, accuracy, is_active, created_by) 
                    VALUES (:model_id, :version_number, :model_file_path, :accuracy, :is_active, :created_by)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':model_id' => $modelId,
                ':version_number' => 'v' . date('YmdHis'),
                ':model_file_path' => $model['model_file_path'] ?? '',
                ':accuracy' => $model['accuracy'],
                ':is_active' => false,
                ':created_by' => $_SESSION['user_id'] ?? 1
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error archiving model version: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get training statistics
     */
    public function getTrainingStatistics() {
        try {
            $stats = [];
            
            // Total training sessions
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM ml_training_sessions");
            $stats['total_sessions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Successful trainings
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM ml_training_sessions WHERE status = 'completed'");
            $stats['successful_trainings'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Failed trainings
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM ml_training_sessions WHERE status = 'failed'");
            $stats['failed_trainings'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Average training duration
            $stmt = $this->pdo->query("SELECT AVG(duration_seconds) as avg_duration FROM ml_training_sessions WHERE status = 'completed'");
            $stats['avg_duration'] = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_duration'] ?? 0, 2);
            
            // Average accuracy
            $stmt = $this->pdo->query("SELECT AVG(accuracy) as avg_accuracy FROM ml_training_sessions WHERE status = 'completed'");
            $stats['avg_accuracy'] = round(($stmt->fetch(PDO::FETCH_ASSOC)['avg_accuracy'] ?? 0) * 100, 2);
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting training statistics: " . $e->getMessage());
            return [
                'total_sessions' => 0,
                'successful_trainings' => 0,
                'failed_trainings' => 0,
                'avg_duration' => 0,
                'avg_accuracy' => 0
            ];
        }
    }
} 