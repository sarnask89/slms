<?php
/**
 * ML Prediction Engine
 * Handles model predictions and inference operations
 */

class MLPredictionEngine {
    private $pdo;
    private $db_manager;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->db_manager = new MLDatabaseManager($pdo);
    }
    
    /**
     * Make a prediction using a trained model
     */
    public function predict($modelId, $inputData) {
        try {
            $startTime = microtime(true);
            
            // Get model details
            $model = $this->db_manager->getModel($modelId);
            if (!$model) {
                return ['success' => false, 'error' => 'Model not found'];
            }
            
            // Check if model is active
            if ($model['status'] !== 'active') {
                return ['success' => false, 'error' => 'Model is not active. Current status: ' . $model['status']];
            }
            
            // Validate input data
            $validationResult = $this->validateInputData($inputData, $model);
            if (!$validationResult['valid']) {
                return ['success' => false, 'error' => 'Invalid input data: ' . $validationResult['error']];
            }
            
            // Preprocess input data
            $processedData = $this->preprocessData($inputData, $model);
            
            // Make prediction
            $predictionResult = $this->executePrediction($model, $processedData);
            
            $endTime = microtime(true);
            $predictionTime = $endTime - $startTime;
            
            // Calculate confidence (simulated)
            $confidence = $this->calculateConfidence($predictionResult, $model);
            
            // Save prediction to database
            $saveResult = $this->db_manager->savePrediction(
                $modelId, 
                $inputData, 
                $predictionResult, 
                $confidence, 
                $predictionTime
            );
            
            if (!$saveResult['success']) {
                error_log("Failed to save prediction: " . $saveResult['error']);
            }
            
            return [
                'success' => true,
                'prediction' => $predictionResult,
                'confidence' => $confidence,
                'prediction_time' => $predictionTime,
                'model_info' => [
                    'name' => $model['name'],
                    'type' => $model['type'],
                    'algorithm' => $model['algorithm'],
                    'accuracy' => $model['accuracy']
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Validate input data against model requirements
     */
    private function validateInputData($inputData, $model) {
        try {
            // Parse input data if it's a string
            if (is_string($inputData)) {
                $inputData = json_decode($inputData, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return ['valid' => false, 'error' => 'Invalid JSON format'];
                }
            }
            
            if (!is_array($inputData)) {
                return ['valid' => false, 'error' => 'Input data must be an array or JSON object'];
            }
            
            // Check for required features based on model type
            $requiredFeatures = $this->getRequiredFeatures($model);
            
            foreach ($requiredFeatures as $feature) {
                if (!array_key_exists($feature, $inputData)) {
                    return ['valid' => false, 'error' => "Missing required feature: {$feature}"];
                }
            }
            
            // Validate data types and ranges
            foreach ($inputData as $key => $value) {
                if (!is_numeric($value)) {
                    return ['valid' => false, 'error' => "Feature {$key} must be numeric"];
                }
            }
            
            return ['valid' => true];
            
        } catch (Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get required features for a model
     */
    private function getRequiredFeatures($model) {
        // This would typically be stored in the model metadata
        // For now, return common features based on model type
        switch ($model['type']) {
            case 'classification':
                return ['feature1', 'feature2', 'feature3'];
            case 'regression':
                return ['feature1', 'feature2', 'feature3', 'feature4'];
            case 'clustering':
                return ['feature1', 'feature2'];
            case 'anomaly_detection':
                return ['feature1', 'feature2', 'feature3'];
            default:
                return ['feature1', 'feature2', 'feature3'];
        }
    }
    
    /**
     * Preprocess input data for prediction
     */
    private function preprocessData($inputData, $model) {
        // Normalize data
        $processedData = [];
        
        foreach ($inputData as $key => $value) {
            // Convert to float
            $processedData[$key] = (float) $value;
            
            // Apply basic normalization (min-max scaling)
            // In a real implementation, this would use stored normalization parameters
            $processedData[$key] = ($processedData[$key] - 0) / (100 - 0); // Assuming range 0-100
        }
        
        return $processedData;
    }
    
    /**
     * Execute the actual prediction
     * This is a simplified implementation - in production, this would load and use actual ML models
     */
    private function executePrediction($model, $processedData) {
        try {
            // Simulate prediction based on model type and algorithm
            switch ($model['type']) {
                case 'classification':
                    return $this->simulateClassification($model, $processedData);
                    
                case 'regression':
                    return $this->simulateRegression($model, $processedData);
                    
                case 'clustering':
                    return $this->simulateClustering($model, $processedData);
                    
                case 'anomaly_detection':
                    return $this->simulateAnomalyDetection($model, $processedData);
                    
                default:
                    return $this->simulateGenericPrediction($model, $processedData);
            }
            
        } catch (Exception $e) {
            throw new Exception("Prediction failed: " . $e->getMessage());
        }
    }
    
    /**
     * Simulate classification prediction
     */
    private function simulateClassification($model, $processedData) {
        $features = array_values($processedData);
        $sum = array_sum($features);
        
        // Simple classification logic
        if ($sum > 2.0) {
            $predicted_class = 'high';
            $probability = 0.85;
        } elseif ($sum > 1.0) {
            $predicted_class = 'medium';
            $probability = 0.75;
        } else {
            $predicted_class = 'low';
            $probability = 0.90;
        }
        
        return [
            'predicted_class' => $predicted_class,
            'probability' => $probability,
            'class_probabilities' => [
                'low' => $sum <= 1.0 ? 0.90 : 0.05,
                'medium' => $sum > 1.0 && $sum <= 2.0 ? 0.75 : 0.10,
                'high' => $sum > 2.0 ? 0.85 : 0.05
            ]
        ];
    }
    
    /**
     * Simulate regression prediction
     */
    private function simulateRegression($model, $processedData) {
        $features = array_values($processedData);
        $sum = array_sum($features);
        
        // Simple regression: predict a value based on feature sum
        $predicted_value = $sum * 10 + rand(-5, 5);
        
        return [
            'predicted_value' => round($predicted_value, 2),
            'confidence_interval' => [
                'lower' => round($predicted_value - 2, 2),
                'upper' => round($predicted_value + 2, 2)
            ]
        ];
    }
    
    /**
     * Simulate clustering prediction
     */
    private function simulateClustering($model, $processedData) {
        $features = array_values($processedData);
        $sum = array_sum($features);
        
        // Simple clustering logic
        if ($sum > 1.5) {
            $cluster = 'cluster_1';
        } elseif ($sum > 0.5) {
            $cluster = 'cluster_2';
        } else {
            $cluster = 'cluster_3';
        }
        
        return [
            'cluster' => $cluster,
            'cluster_center_distance' => rand(10, 50) / 100,
            'cluster_similarity' => rand(70, 95) / 100
        ];
    }
    
    /**
     * Simulate anomaly detection
     */
    private function simulateAnomalyDetection($model, $processedData) {
        $features = array_values($processedData);
        $sum = array_sum($features);
        
        // Simple anomaly detection logic
        $is_anomaly = $sum > 2.5 || $sum < 0.1;
        $anomaly_score = $is_anomaly ? rand(70, 95) / 100 : rand(5, 30) / 100;
        
        return [
            'is_anomaly' => $is_anomaly,
            'anomaly_score' => $anomaly_score,
            'normal_score' => 1 - $anomaly_score
        ];
    }
    
    /**
     * Simulate generic prediction
     */
    private function simulateGenericPrediction($model, $processedData) {
        $features = array_values($processedData);
        $sum = array_sum($features);
        
        return [
            'prediction' => round($sum * 100, 2),
            'confidence' => rand(70, 95) / 100,
            'features_used' => count($features)
        ];
    }
    
    /**
     * Calculate prediction confidence
     */
    private function calculateConfidence($predictionResult, $model) {
        // Base confidence on model accuracy
        $baseConfidence = $model['accuracy'] ?? 0.8;
        
        // Adjust based on prediction type
        if (isset($predictionResult['probability'])) {
            return $predictionResult['probability'];
        } elseif (isset($predictionResult['confidence'])) {
            return $predictionResult['confidence'];
        } else {
            return $baseConfidence;
        }
    }
    
    /**
     * Batch prediction for multiple inputs
     */
    public function batchPredict($modelId, $inputDataArray) {
        $results = [];
        
        foreach ($inputDataArray as $index => $inputData) {
            $result = $this->predict($modelId, $inputData);
            $results[] = [
                'index' => $index,
                'input' => $inputData,
                'result' => $result
            ];
        }
        
        return [
            'success' => true,
            'predictions' => $results,
            'total_predictions' => count($results)
        ];
    }
    
    /**
     * Get prediction history for a model
     */
    public function getPredictionHistory($modelId, $limit = 100) {
        try {
            $sql = "SELECT * FROM ml_predictions 
                    WHERE model_id = :model_id 
                    ORDER BY created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':model_id', $modelId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting prediction history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get prediction statistics
     */
    public function getPredictionStatistics($modelId = null) {
        try {
            $stats = [];
            
            if ($modelId) {
                $sql = "SELECT 
                        COUNT(*) as total_predictions,
                        AVG(confidence) as avg_confidence,
                        AVG(prediction_time) as avg_prediction_time,
                        MIN(created_at) as first_prediction,
                        MAX(created_at) as last_prediction
                        FROM ml_predictions 
                        WHERE model_id = :model_id";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':model_id' => $modelId]);
            } else {
                $sql = "SELECT 
                        COUNT(*) as total_predictions,
                        AVG(confidence) as avg_confidence,
                        AVG(prediction_time) as avg_prediction_time,
                        MIN(created_at) as first_prediction,
                        MAX(created_at) as last_prediction
                        FROM ml_predictions";
                
                $stmt = $this->pdo->query($sql);
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_predictions' => $result['total_predictions'] ?? 0,
                'avg_confidence' => round(($result['avg_confidence'] ?? 0) * 100, 2),
                'avg_prediction_time' => round($result['avg_prediction_time'] ?? 0, 4),
                'first_prediction' => $result['first_prediction'] ?? null,
                'last_prediction' => $result['last_prediction'] ?? null
            ];
            
        } catch (PDOException $e) {
            error_log("Error getting prediction statistics: " . $e->getMessage());
            return [
                'total_predictions' => 0,
                'avg_confidence' => 0,
                'avg_prediction_time' => 0,
                'first_prediction' => null,
                'last_prediction' => null
            ];
        }
    }
} 