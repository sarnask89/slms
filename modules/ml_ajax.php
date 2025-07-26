<?php
/**
 * ML System AJAX Handler
 * Handles AJAX requests for the ML system
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/ml_database_manager.php';
require_once __DIR__ . '/ml_training_engine.php';
require_once __DIR__ . '/ml_prediction_engine.php';
require_once __DIR__ . '/ml_performance_monitor.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$pdo = get_pdo();
$ml_db = new MLDatabaseManager($pdo);
$training_engine = new MLTrainingEngine($pdo);
$prediction_engine = new MLPredictionEngine($pdo);
$performance_monitor = new MLPerformanceMonitor($pdo);

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Set content type to JSON
header('Content-Type: application/json');

try {
    switch ($action) {
        case 'get_model_details':
            $modelId = $_GET['id'] ?? null;
            if (!$modelId) {
                throw new Exception('Model ID is required');
            }
            
            $model = $ml_db->getModel($modelId);
            if (!$model) {
                throw new Exception('Model not found');
            }
            
            $metrics = $performance_monitor->getModelMetrics($modelId);
            $driftAnalysis = $performance_monitor->detectModelDrift($modelId);
            
            $html = generateModelDetailsHTML($model, $metrics, $driftAnalysis);
            echo json_encode(['success' => true, 'html' => $html]);
            break;
            
        case 'get_edit_form':
            $modelId = $_GET['id'] ?? null;
            if (!$modelId) {
                throw new Exception('Model ID is required');
            }
            
            $model = $ml_db->getModel($modelId);
            if (!$model) {
                throw new Exception('Model not found');
            }
            
            $html = generateEditFormHTML($model);
            echo json_encode(['success' => true, 'html' => $html]);
            break;
            
        case 'train_model':
            $modelId = $_POST['model_id'] ?? null;
            if (!$modelId) {
                throw new Exception('Model ID is required');
            }
            
            $result = $training_engine->trainModel($modelId);
            echo json_encode($result);
            break;
            
        case 'make_prediction':
            $modelId = $_POST['model_id'] ?? null;
            $inputData = $_POST['input_data'] ?? null;
            
            if (!$modelId || !$inputData) {
                throw new Exception('Model ID and input data are required');
            }
            
            $result = $prediction_engine->predict($modelId, $inputData);
            echo json_encode($result);
            break;
            
        case 'get_training_status':
            $trainingId = $_GET['training_id'] ?? null;
            if (!$trainingId) {
                throw new Exception('Training ID is required');
            }
            
            $session = $training_engine->getTrainingSession($trainingId);
            if (!$session) {
                throw new Exception('Training session not found');
            }
            
            echo json_encode(['success' => true, 'session' => $session]);
            break;
            
        case 'cancel_training':
            $trainingId = $_POST['training_id'] ?? null;
            if (!$trainingId) {
                throw new Exception('Training ID is required');
            }
            
            $result = $training_engine->cancelTraining($trainingId);
            echo json_encode($result);
            break;
            
        case 'get_performance_report':
            $modelId = $_GET['model_id'] ?? null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            
            $report = $performance_monitor->generatePerformanceReport($modelId, $startDate, $endDate);
            echo json_encode(['success' => true, 'report' => $report]);
            break;
            
        case 'get_prediction_history':
            $modelId = $_GET['model_id'] ?? null;
            $limit = $_GET['limit'] ?? 100;
            
            if (!$modelId) {
                throw new Exception('Model ID is required');
            }
            
            $history = $prediction_engine->getPredictionHistory($modelId, $limit);
            echo json_encode(['success' => true, 'history' => $history]);
            break;
            
        case 'get_model_statistics':
            $stats = $ml_db->getModelStatistics();
            echo json_encode(['success' => true, 'statistics' => $stats]);
            break;
            
        case 'get_training_statistics':
            $stats = $training_engine->getTrainingStatistics();
            echo json_encode(['success' => true, 'statistics' => $stats]);
            break;
            
        case 'get_prediction_statistics':
            $modelId = $_GET['model_id'] ?? null;
            $stats = $prediction_engine->getPredictionStatistics($modelId);
            echo json_encode(['success' => true, 'statistics' => $stats]);
            break;
            
        case 'check_performance_alerts':
            $alerts = $performance_monitor->checkPerformanceAlerts();
            echo json_encode(['success' => true, 'alerts' => $alerts]);
            break;
            
        case 'setup_performance_alert':
            $modelId = $_POST['model_id'] ?? null;
            $metric = $_POST['metric'] ?? null;
            $threshold = $_POST['threshold'] ?? null;
            $condition = $_POST['condition'] ?? 'below';
            
            if (!$modelId || !$metric || !$threshold) {
                throw new Exception('Model ID, metric, and threshold are required');
            }
            
            $result = $performance_monitor->setupPerformanceAlert($modelId, $metric, $threshold, $condition);
            echo json_encode($result);
            break;
            
        case 'batch_predict':
            $modelId = $_POST['model_id'] ?? null;
            $inputDataArray = $_POST['input_data_array'] ?? null;
            
            if (!$modelId || !$inputDataArray) {
                throw new Exception('Model ID and input data array are required');
            }
            
            $result = $prediction_engine->batchPredict($modelId, $inputDataArray);
            echo json_encode($result);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Generate HTML for model details modal
 */
function generateModelDetailsHTML($model, $metrics, $driftAnalysis) {
    $html = '<div class="row">';
    
    // Model Information
    $html .= '<div class="col-md-6">';
    $html .= '<h6>Model Information</h6>';
    $html .= '<table class="table table-sm">';
    $html .= '<tr><td><strong>Name:</strong></td><td>' . htmlspecialchars($model['name']) . '</td></tr>';
    $html .= '<tr><td><strong>Type:</strong></td><td>' . htmlspecialchars($model['type']) . '</td></tr>';
    $html .= '<tr><td><strong>Algorithm:</strong></td><td>' . htmlspecialchars($model['algorithm']) . '</td></tr>';
    $html .= '<tr><td><strong>Status:</strong></td><td><span class="badge bg-' . ($model['status'] === 'active' ? 'success' : 'secondary') . '">' . htmlspecialchars($model['status']) . '</span></td></tr>';
    $html .= '<tr><td><strong>Accuracy:</strong></td><td>' . ($model['accuracy'] ? number_format($model['accuracy'] * 100, 2) . '%' : 'Not trained') . '</td></tr>';
    $html .= '<tr><td><strong>Created:</strong></td><td>' . date('Y-m-d H:i', strtotime($model['created_at'])) . '</td></tr>';
    $html .= '<tr><td><strong>Last Training:</strong></td><td>' . ($model['last_training'] ? date('Y-m-d H:i', strtotime($model['last_training'])) : 'Never') . '</td></tr>';
    $html .= '</table>';
    $html .= '</div>';
    
    // Performance Metrics
    $html .= '<div class="col-md-6">';
    $html .= '<h6>Performance Metrics</h6>';
    if ($metrics && isset($metrics['model_info'])) {
        $html .= '<table class="table table-sm">';
        $html .= '<tr><td><strong>Precision:</strong></td><td>' . ($model['precision_score'] ? number_format($model['precision_score'] * 100, 2) . '%' : 'N/A') . '</td></tr>';
        $html .= '<tr><td><strong>Recall:</strong></td><td>' . ($model['recall_score'] ? number_format($model['recall_score'] * 100, 2) . '%' : 'N/A') . '</td></tr>';
        $html .= '<tr><td><strong>F1 Score:</strong></td><td>' . ($model['f1_score'] ? number_format($model['f1_score'] * 100, 2) . '%' : 'N/A') . '</td></tr>';
        $html .= '</table>';
    } else {
        $html .= '<p class="text-muted">No performance data available</p>';
    }
    $html .= '</div>';
    
    $html .= '</div>';
    
    // Drift Analysis
    if ($driftAnalysis && !isset($driftAnalysis['error'])) {
        $html .= '<div class="row mt-3">';
        $html .= '<div class="col-12">';
        $html .= '<h6>Drift Analysis</h6>';
        $html .= '<div class="alert alert-' . ($driftAnalysis['drift_detected'] ? 'warning' : 'info') . '">';
        $html .= '<strong>' . ($driftAnalysis['drift_detected'] ? 'Drift Detected' : 'No Drift Detected') . '</strong><br>';
        $html .= 'Confidence Drift: ' . $driftAnalysis['confidence_drift'] . '%<br>';
        $html .= 'Recent Avg Confidence: ' . $driftAnalysis['recent_avg_confidence'] . '%<br>';
        $html .= 'Older Avg Confidence: ' . $driftAnalysis['older_avg_confidence'] . '%<br>';
        $html .= '<strong>Recommendation:</strong> ' . $driftAnalysis['recommendation'];
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // Training History
    if ($metrics && isset($metrics['training_history']) && !empty($metrics['training_history'])) {
        $html .= '<div class="row mt-3">';
        $html .= '<div class="col-12">';
        $html .= '<h6>Recent Training Sessions</h6>';
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-sm">';
        $html .= '<thead><tr><th>Date</th><th>Status</th><th>Accuracy</th><th>Duration</th></tr></thead>';
        $html .= '<tbody>';
        
        foreach (array_slice($metrics['training_history'], 0, 5) as $session) {
            $html .= '<tr>';
            $html .= '<td>' . date('Y-m-d H:i', strtotime($session['started_at'])) . '</td>';
            $html .= '<td><span class="badge bg-' . ($session['status'] === 'completed' ? 'success' : 'warning') . '">' . htmlspecialchars($session['status']) . '</span></td>';
            $html .= '<td>' . ($session['accuracy'] ? number_format($session['accuracy'] * 100, 2) . '%' : 'N/A') . '</td>';
            $html .= '<td>' . ($session['duration_seconds'] ? round($session['duration_seconds'], 2) . 's' : 'N/A') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    return $html;
}

/**
 * Generate HTML for edit form modal
 */
function generateEditFormHTML($model) {
    $html = '<form method="post" id="editModelForm">';
    $html .= '<input type="hidden" name="model_id" value="' . $model['id'] . '">';
    
    $html .= '<div class="mb-3">';
    $html .= '<label for="edit_model_name" class="form-label">Model Name</label>';
    $html .= '<input type="text" name="model_name" id="edit_model_name" class="form-control" value="' . htmlspecialchars($model['name']) . '" required>';
    $html .= '</div>';
    
    $html .= '<div class="mb-3">';
    $html .= '<label for="edit_description" class="form-label">Description</label>';
    $html .= '<textarea name="description" id="edit_description" class="form-control" rows="3">' . htmlspecialchars($model['description']) . '</textarea>';
    $html .= '</div>';
    
    $html .= '<div class="mb-3">';
    $html .= '<label for="edit_parameters" class="form-label">Parameters (JSON)</label>';
    $html .= '<textarea name="parameters" id="edit_parameters" class="form-control" rows="5">' . htmlspecialchars($model['parameters']) . '</textarea>';
    $html .= '</div>';
    
    $html .= '<div class="d-flex justify-content-end">';
    $html .= '<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>';
    $html .= '<button type="submit" name="update_model" class="btn btn-primary">Update Model</button>';
    $html .= '</div>';
    
    $html .= '</form>';
    
    return $html;
} 