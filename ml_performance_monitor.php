<?php
/**
 * ML Performance Monitor
 * Tracks and analyzes model performance metrics
 */

class MLPerformanceMonitor {
    private $pdo;
    private $db_manager;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->db_manager = new MLDatabaseManager($pdo);
    }
    
    /**
     * Get overall performance metrics
     */
    public function getOverallMetrics() {
        try {
            $metrics = [];
            
            // Get average accuracy across all models
            $stmt = $this->pdo->query("SELECT AVG(accuracy) as avg_accuracy FROM ml_models WHERE status = 'active'");
            $metrics['avg_accuracy'] = round(($stmt->fetch(PDO::FETCH_ASSOC)['avg_accuracy'] ?? 0) * 100, 2);
            
            // Get average precision
            $stmt = $this->pdo->query("SELECT AVG(precision_score) as avg_precision FROM ml_models WHERE status = 'active'");
            $metrics['avg_precision'] = round(($stmt->fetch(PDO::FETCH_ASSOC)['avg_precision'] ?? 0) * 100, 2);
            
            // Get average recall
            $stmt = $this->pdo->query("SELECT AVG(recall_score) as avg_recall FROM ml_models WHERE status = 'active'");
            $metrics['avg_recall'] = round(($stmt->fetch(PDO::FETCH_ASSOC)['avg_recall'] ?? 0) * 100, 2);
            
            // Get average F1 score
            $stmt = $this->pdo->query("SELECT AVG(f1_score) as avg_f1_score FROM ml_models WHERE status = 'active'");
            $metrics['avg_f1_score'] = round(($stmt->fetch(PDO::FETCH_ASSOC)['avg_f1_score'] ?? 0) * 100, 2);
            
            // Get total predictions
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_predictions FROM ml_predictions");
            $metrics['total_predictions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_predictions'];
            
            // Get average prediction confidence
            $stmt = $this->pdo->query("SELECT AVG(confidence) as avg_confidence FROM ml_predictions");
            $metrics['avg_confidence'] = round(($stmt->fetch(PDO::FETCH_ASSOC)['avg_confidence'] ?? 0) * 100, 2);
            
            // Get average prediction time
            $stmt = $this->pdo->query("SELECT AVG(prediction_time) as avg_prediction_time FROM ml_predictions");
            $metrics['avg_prediction_time'] = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_prediction_time'] ?? 0, 4);
            
            return $metrics;
            
        } catch (PDOException $e) {
            error_log("Error getting overall metrics: " . $e->getMessage());
            return [
                'avg_accuracy' => 0,
                'avg_precision' => 0,
                'avg_recall' => 0,
                'avg_f1_score' => 0,
                'total_predictions' => 0,
                'avg_confidence' => 0,
                'avg_prediction_time' => 0
            ];
        }
    }
    
    /**
     * Get performance metrics for a specific model
     */
    public function getModelMetrics($modelId) {
        try {
            $metrics = [];
            
            // Get model details
            $model = $this->db_manager->getModel($modelId);
            if (!$model) {
                return null;
            }
            
            $metrics['model_info'] = $model;
            
            // Get training history
            $stmt = $this->pdo->prepare("
                SELECT * FROM ml_training_sessions 
                WHERE model_id = :model_id 
                ORDER BY started_at DESC 
                LIMIT 10
            ");
            $stmt->execute([':model_id' => $modelId]);
            $metrics['training_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get prediction history
            $stmt = $this->pdo->prepare("
                SELECT * FROM ml_predictions 
                WHERE model_id = :model_id 
                ORDER BY created_at DESC 
                LIMIT 100
            ");
            $stmt->execute([':model_id' => $modelId]);
            $metrics['prediction_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get performance trends
            $metrics['performance_trends'] = $this->getPerformanceTrends($modelId);
            
            // Get accuracy over time
            $metrics['accuracy_timeline'] = $this->getAccuracyTimeline($modelId);
            
            return $metrics;
            
        } catch (PDOException $e) {
            error_log("Error getting model metrics: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get performance trends for a model
     */
    private function getPerformanceTrends($modelId) {
        try {
            $trends = [];
            
            // Get accuracy trend over time
            $stmt = $this->pdo->prepare("
                SELECT 
                    DATE(created_at) as date,
                    AVG(confidence) as avg_confidence,
                    COUNT(*) as prediction_count
                FROM ml_predictions 
                WHERE model_id = :model_id 
                GROUP BY DATE(created_at)
                ORDER BY date DESC
                LIMIT 30
            ");
            $stmt->execute([':model_id' => $modelId]);
            $trends['confidence_trend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get training performance trend
            $stmt = $this->pdo->prepare("
                SELECT 
                    accuracy,
                    precision_score,
                    recall_score,
                    f1_score,
                    started_at
                FROM ml_training_sessions 
                WHERE model_id = :model_id AND status = 'completed'
                ORDER BY started_at DESC
                LIMIT 10
            ");
            $stmt->execute([':model_id' => $modelId]);
            $trends['training_performance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $trends;
            
        } catch (PDOException $e) {
            error_log("Error getting performance trends: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get accuracy timeline for a model
     */
    private function getAccuracyTimeline($modelId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    accuracy,
                    started_at,
                    completed_at
                FROM ml_training_sessions 
                WHERE model_id = :model_id AND status = 'completed'
                ORDER BY started_at ASC
            ");
            $stmt->execute([':model_id' => $modelId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting accuracy timeline: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculate model drift detection
     */
    public function detectModelDrift($modelId) {
        try {
            $driftInfo = [];
            
            // Get recent predictions
            $stmt = $this->pdo->prepare("
                SELECT confidence, created_at
                FROM ml_predictions 
                WHERE model_id = :model_id 
                ORDER BY created_at DESC 
                LIMIT 1000
            ");
            $stmt->execute([':model_id' => $modelId]);
            $recentPredictions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($recentPredictions) < 50) {
                return ['drift_detected' => false, 'reason' => 'Insufficient data for drift detection'];
            }
            
            // Split data into two periods
            $midPoint = count($recentPredictions) / 2;
            $recentPeriod = array_slice($recentPredictions, 0, $midPoint);
            $olderPeriod = array_slice($recentPredictions, $midPoint);
            
            // Calculate average confidence for each period
            $recentAvg = array_sum(array_column($recentPeriod, 'confidence')) / count($recentPeriod);
            $olderAvg = array_sum(array_column($olderPeriod, 'confidence')) / count($olderPeriod);
            
            // Calculate drift threshold (5% decrease in confidence)
            $driftThreshold = 0.05;
            $confidenceDrift = $olderAvg - $recentAvg;
            
            $driftInfo['drift_detected'] = $confidenceDrift > $driftThreshold;
            $driftInfo['confidence_drift'] = round($confidenceDrift * 100, 2);
            $driftInfo['recent_avg_confidence'] = round($recentAvg * 100, 2);
            $driftInfo['older_avg_confidence'] = round($olderAvg * 100, 2);
            $driftInfo['drift_threshold'] = round($driftThreshold * 100, 2);
            
            if ($driftInfo['drift_detected']) {
                $driftInfo['recommendation'] = 'Consider retraining the model due to performance degradation';
            } else {
                $driftInfo['recommendation'] = 'Model performance is stable';
            }
            
            return $driftInfo;
            
        } catch (PDOException $e) {
            error_log("Error detecting model drift: " . $e->getMessage());
            return ['drift_detected' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generate performance report
     */
    public function generatePerformanceReport($modelId = null, $startDate = null, $endDate = null) {
        try {
            $report = [];
            
            if ($modelId) {
                // Single model report
                $report['model_metrics'] = $this->getModelMetrics($modelId);
                $report['drift_analysis'] = $this->detectModelDrift($modelId);
            } else {
                // Overall system report
                $report['overall_metrics'] = $this->getOverallMetrics();
                $report['top_performing_models'] = $this->getTopPerformingModels();
                $report['system_health'] = $this->getSystemHealth();
            }
            
            // Add date range if specified
            if ($startDate && $endDate) {
                $report['date_range'] = [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ];
            }
            
            $report['generated_at'] = date('Y-m-d H:i:s');
            
            return $report;
            
        } catch (Exception $e) {
            error_log("Error generating performance report: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Get top performing models
     */
    private function getTopPerformingModels() {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    id, name, type, algorithm, accuracy, 
                    (SELECT COUNT(*) FROM ml_predictions WHERE model_id = ml_models.id) as prediction_count
                FROM ml_models 
                WHERE status = 'active' 
                ORDER BY accuracy DESC 
                LIMIT 10
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting top performing models: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get system health metrics
     */
    private function getSystemHealth() {
        try {
            $health = [];
            
            // Model status distribution
            $stmt = $this->pdo->query("
                SELECT status, COUNT(*) as count 
                FROM ml_models 
                GROUP BY status
            ");
            $health['model_status_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Training success rate
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                    COUNT(*) as total
                FROM ml_training_sessions
            ");
            $trainingStats = $stmt->fetch(PDO::FETCH_ASSOC);
            $health['training_success_rate'] = $trainingStats['total'] > 0 ? 
                round(($trainingStats['successful'] / $trainingStats['total']) * 100, 2) : 0;
            
            // Average prediction response time
            $stmt = $this->pdo->query("SELECT AVG(prediction_time) as avg_response_time FROM ml_predictions");
            $health['avg_response_time'] = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_response_time'] ?? 0, 4);
            
            // System uptime (simulated)
            $health['system_uptime'] = 99.9; // Simulated uptime percentage
            
            return $health;
            
        } catch (PDOException $e) {
            error_log("Error getting system health: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Set up performance alerts
     */
    public function setupPerformanceAlert($modelId, $metric, $threshold, $condition = 'below') {
        try {
            $sql = "INSERT INTO ml_performance_alerts 
                    (model_id, metric, threshold, alert_condition, is_active, created_at) 
                    VALUES (:model_id, :metric, :threshold, :condition, 1, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':model_id' => $modelId,
                ':metric' => $metric,
                ':threshold' => $threshold,
                ':condition' => $condition
            ]);
            
            return ['success' => true, 'alert_id' => $this->pdo->lastInsertId()];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Check performance alerts
     */
    public function checkPerformanceAlerts() {
        try {
            $alerts = [];
            
            // Get active alerts
            $stmt = $this->pdo->query("
                SELECT * FROM ml_performance_alerts 
                WHERE is_active = 1
            ");
            $activeAlerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($activeAlerts as $alert) {
                $currentValue = $this->getCurrentMetricValue($alert['model_id'], $alert['metric']);
                
                $triggered = false;
                if ($alert['alert_condition'] === 'below' && $currentValue < $alert['threshold']) {
                    $triggered = true;
                } elseif ($alert['alert_condition'] === 'above' && $currentValue > $alert['threshold']) {
                    $triggered = true;
                }
                
                if ($triggered) {
                    $alerts[] = [
                        'alert_id' => $alert['id'],
                        'model_id' => $alert['model_id'],
                        'metric' => $alert['metric'],
                        'threshold' => $alert['threshold'],
                        'current_value' => $currentValue,
                        'condition' => $alert['alert_condition'],
                        'message' => "Performance alert: {$alert['metric']} is {$alert['alert_condition']} threshold ({$alert['threshold']}). Current value: {$currentValue}"
                    ];
                }
            }
            
            return $alerts;
            
        } catch (PDOException $e) {
            error_log("Error checking performance alerts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get current metric value for a model
     */
    private function getCurrentMetricValue($modelId, $metric) {
        try {
            switch ($metric) {
                case 'accuracy':
                    $stmt = $this->pdo->prepare("SELECT accuracy FROM ml_models WHERE id = :model_id");
                    break;
                case 'confidence':
                    $stmt = $this->pdo->prepare("
                        SELECT AVG(confidence) as avg_confidence 
                        FROM ml_predictions 
                        WHERE model_id = :model_id 
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    ");
                    break;
                default:
                    return 0;
            }
            
            $stmt->execute([':model_id' => $modelId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result[array_keys($result)[0]] ?? 0;
            
        } catch (PDOException $e) {
            error_log("Error getting current metric value: " . $e->getMessage());
            return 0;
        }
    }
} 