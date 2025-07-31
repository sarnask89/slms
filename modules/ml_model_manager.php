<?php
/**
 * ML Model Manager - Main Module
 * Comprehensive Machine Learning Model Management System
 * 
 * Features:
 * - Model CRUD operations
 * - Training pipeline management
 * - Prediction engine
 * - Performance monitoring
 * - Model versioning
 * - Automated retraining
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'module_loader.php';

require_once __DIR__ . '/ml_database_manager.php';
require_once __DIR__ . '/ml_training_engine.php';
require_once __DIR__ . '/ml_prediction_engine.php';
require_once __DIR__ . '/ml_performance_monitor.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$pdo = get_pdo();
$ml_db = new MLDatabaseManager($pdo);
$training_engine = new MLTrainingEngine($pdo);
$prediction_engine = new MLPredictionEngine($pdo);
$performance_monitor = new MLPerformanceMonitor($pdo);

$models = [];
$errors = [];
$success_messages = [];
$debug_info = [];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Create new model
    if (isset($_POST['create_model'])) {
        $model_data = [
            'name' => $_POST['model_name'] ?? '',
            'type' => $_POST['model_type'] ?? '',
            'description' => $_POST['description'] ?? '',
            'algorithm' => $_POST['algorithm'] ?? '',
            'parameters' => $_POST['parameters'] ?? '{}',
            'status' => 'draft'
        ];
        
        $result = $ml_db->createModel($model_data);
        if ($result['success']) {
            $success_messages[] = "✅ Model '{$model_data['name']}' created successfully";
        } else {
            $errors[] = "❌ Failed to create model: " . $result['error'];
        }
    }
    
    // Train model
    if (isset($_POST['train_model'])) {
        $model_id = $_POST['model_id'] ?? null;
        if ($model_id) {
            $result = $training_engine->trainModel($model_id);
            if ($result['success']) {
                $success_messages[] = "✅ Model training started successfully";
                $success_messages[] = "Training ID: " . $result['training_id'];
            } else {
                $errors[] = "❌ Training failed: " . $result['error'];
            }
        }
    }
    
    // Make prediction
    if (isset($_POST['make_prediction'])) {
        $model_id = $_POST['model_id'] ?? null;
        $input_data = $_POST['input_data'] ?? '{}';
        
        if ($model_id) {
            $result = $prediction_engine->predict($model_id, $input_data);
            if ($result['success']) {
                $success_messages[] = "✅ Prediction completed";
                $success_messages[] = "Result: " . json_encode($result['prediction']);
            } else {
                $errors[] = "❌ Prediction failed: " . $result['error'];
            }
        }
    }
    
    // Delete model
    if (isset($_POST['delete_model'])) {
        $model_id = $_POST['model_id'] ?? null;
        if ($model_id) {
            $result = $ml_db->deleteModel($model_id);
            if ($result['success']) {
                $success_messages[] = "✅ Model deleted successfully";
            } else {
                $errors[] = "❌ Failed to delete model: " . $result['error'];
            }
        }
    }
    
    // Update model
    if (isset($_POST['update_model'])) {
        $model_id = $_POST['model_id'] ?? null;
        $update_data = [
            'name' => $_POST['model_name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'parameters' => $_POST['parameters'] ?? '{}'
        ];
        
        if ($model_id) {
            $result = $ml_db->updateModel($model_id, $update_data);
            if ($result['success']) {
                $success_messages[] = "✅ Model updated successfully";
            } else {
                $errors[] = "❌ Failed to update model: " . $result['error'];
            }
        }
    }
}

// Get all models
$models = $ml_db->getAllModels();

// Get model statistics
$stats = $ml_db->getModelStatistics();

// Get recent training sessions
$recent_trainings = $training_engine->getRecentTrainings(5);

// Get performance metrics
$performance_metrics = $performance_monitor->getOverallMetrics();

include '../partials/layout.php';
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-cpu"></i> ML Model Manager
          </h5>
          <div>
            <span class="badge bg-primary"><?= $stats['total_models'] ?> models</span>
            <span class="badge bg-success"><?= $stats['active_models'] ?> active</span>
            <span class="badge bg-info"><?= $stats['training_sessions'] ?> trainings</span>
            <span class="badge bg-warning"><?= $stats['predictions'] ?> predictions</span>
          </div>
        </div>
        <div class="card-body">
          
          <!-- Success Messages -->
          <?php if (!empty($success_messages)): ?>
            <div class="alert alert-success">
              <?php foreach ($success_messages as $message): ?>
                <div><?= htmlspecialchars($message) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <!-- Errors -->
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <!-- Performance Overview -->
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="card bg-primary text-white">
                <div class="card-body text-center">
                  <h4><?= number_format($performance_metrics['avg_accuracy'], 2) ?>%</h4>
                  <small>Average Accuracy</small>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-success text-white">
                <div class="card-body text-center">
                  <h4><?= number_format($performance_metrics['avg_precision'], 2) ?>%</h4>
                  <small>Average Precision</small>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-info text-white">
                <div class="card-body text-center">
                  <h4><?= number_format($performance_metrics['avg_recall'], 2) ?>%</h4>
                  <small>Average Recall</small>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-warning text-white">
                <div class="card-body text-center">
                  <h4><?= number_format($performance_metrics['avg_f1_score'], 2) ?>%</h4>
                  <small>Average F1-Score</small>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Create New Model -->
          <div class="card mb-4">
            <div class="card-header">
              <h6 class="mb-0">Create New Model</h6>
            </div>
            <div class="card-body">
              <form method="post" class="row g-3">
                <div class="col-md-3">
                  <label for="model_name" class="form-label">Model Name</label>
                  <input type="text" name="model_name" id="model_name" class="form-control" required>
                </div>
                <div class="col-md-2">
                  <label for="model_type" class="form-label">Type</label>
                  <select name="model_type" id="model_type" class="form-select" required>
                    <option value="">Select type...</option>
                    <option value="classification">Classification</option>
                    <option value="regression">Regression</option>
                    <option value="clustering">Clustering</option>
                    <option value="anomaly_detection">Anomaly Detection</option>
                    <option value="recommendation">Recommendation</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label for="algorithm" class="form-label">Algorithm</label>
                  <select name="algorithm" id="algorithm" class="form-select" required>
                    <option value="">Select algorithm...</option>
                    <option value="random_forest">Random Forest</option>
                    <option value="xgboost">XGBoost</option>
                    <option value="neural_network">Neural Network</option>
                    <option value="svm">SVM</option>
                    <option value="knn">K-Nearest Neighbors</option>
                    <option value="linear_regression">Linear Regression</option>
                    <option value="logistic_regression">Logistic Regression</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="description" class="form-label">Description</label>
                  <input type="text" name="description" id="description" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" name="create_model" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Model
                  </button>
                </div>
              </form>
            </div>
          </div>
          
          <!-- Models List -->
          <div class="card mb-4">
            <div class="card-header">
              <h6 class="mb-0">ML Models</h6>
            </div>
            <div class="card-body">
              <?php if (!empty($models)): ?>
                <div class="table-responsive">
                  <table class="table table-striped" id="models-table">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Algorithm</th>
                        <th>Status</th>
                        <th>Accuracy</th>
                        <th>Last Training</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($models as $model): ?>
                        <tr>
                          <td>
                            <strong><?= htmlspecialchars($model['name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($model['description']) ?></small>
                          </td>
                          <td>
                            <span class="badge bg-info"><?= htmlspecialchars($model['type']) ?></span>
                          </td>
                          <td><?= htmlspecialchars($model['algorithm']) ?></td>
                          <td>
                            <?php
                            $status_class = $model['status'] === 'active' ? 'success' : 
                                          ($model['status'] === 'training' ? 'warning' : 'secondary');
                            ?>
                            <span class="badge bg-<?= $status_class ?>"><?= htmlspecialchars($model['status']) ?></span>
                          </td>
                          <td>
                            <?php if ($model['accuracy']): ?>
                              <span class="badge bg-success"><?= number_format($model['accuracy'], 2) ?>%</span>
                            <?php else: ?>
                              <span class="badge bg-secondary">Not trained</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if ($model['last_training']): ?>
                              <?= date('Y-m-d H:i', strtotime($model['last_training'])) ?>
                            <?php else: ?>
                              Never
                            <?php endif; ?>
                          </td>
                          <td>
                            <div class="btn-group btn-group-sm">
                              <button type="button" class="btn btn-outline-primary" 
                                      onclick="showModelDetails(<?= $model['id'] ?>)">
                                <i class="bi bi-eye"></i>
                              </button>
                              <button type="button" class="btn btn-outline-success" 
                                      onclick="showTrainingForm(<?= $model['id'] ?>)">
                                <i class="bi bi-play-circle"></i>
                              </button>
                              <button type="button" class="btn btn-outline-info" 
                                      onclick="showPredictionForm(<?= $model['id'] ?>)">
                                <i class="bi bi-graph-up"></i>
                              </button>
                              <button type="button" class="btn btn-outline-warning" 
                                      onclick="showEditForm(<?= $model['id'] ?>)">
                                <i class="bi bi-pencil"></i>
                              </button>
                              <button type="button" class="btn btn-outline-danger" 
                                      onclick="deleteModel(<?= $model['id'] ?>)">
                                <i class="bi bi-trash"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="alert alert-info">
                  <i class="bi bi-info-circle"></i> No ML models found. Create your first model above.
                </div>
              <?php endif; ?>
            </div>
          </div>
          
          <!-- Recent Training Sessions -->
          <?php if (!empty($recent_trainings)): ?>
            <div class="card mb-4">
              <div class="card-header">
                <h6 class="mb-0">Recent Training Sessions</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>Model</th>
                        <th>Status</th>
                        <th>Accuracy</th>
                        <th>Duration</th>
                        <th>Started</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($recent_trainings as $training): ?>
                        <tr>
                          <td><?= htmlspecialchars($training['model_name']) ?></td>
                          <td>
                            <span class="badge bg-<?= $training['status'] === 'completed' ? 'success' : 'warning' ?>">
                              <?= htmlspecialchars($training['status']) ?>
                            </span>
                          </td>
                          <td>
                            <?php if ($training['accuracy']): ?>
                              <?= number_format($training['accuracy'], 2) ?>%
                            <?php else: ?>
                              -
                            <?php endif; ?>
                          </td>
                          <td><?= $training['duration'] ?? '-' ?></td>
                          <td><?= date('Y-m-d H:i', strtotime($training['started_at'])) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php endif; ?>
          
          <!-- Quick Actions -->
          <div class="row mt-4">
            <div class="col-md-6">
              <a href="ml_training_history.php" class="btn btn-secondary">
                <i class="bi bi-clock-history"></i> Training History
              </a>
              <a href="ml_performance_analytics.php" class="btn btn-info">
                <i class="bi bi-graph-up"></i> Performance Analytics
              </a>
            </div>
            <div class="col-md-6">
              <a href="ml_data_manager.php" class="btn btn-success">
                <i class="bi bi-database"></i> Data Manager
              </a>
              <a href="ml_automation.php" class="btn btn-warning">
                <i class="bi bi-robot"></i> Automation
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modals for actions -->
<!-- Model Details Modal -->
<div class="modal fade" id="modelDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Model Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modelDetailsContent">
        <!-- Content will be loaded here -->
      </div>
    </div>
  </div>
</div>

<!-- Training Modal -->
<div class="modal fade" id="trainingModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Train Model</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="post" id="trainingForm">
          <input type="hidden" name="model_id" id="training_model_id">
          <div class="mb-3">
            <label for="training_data" class="form-label">Training Data Source</label>
            <select name="training_data" id="training_data" class="form-select">
              <option value="auto">Auto-select best dataset</option>
              <option value="custom">Custom dataset</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="validation_split" class="form-label">Validation Split</label>
            <input type="range" name="validation_split" id="validation_split" 
                   class="form-range" min="0.1" max="0.5" step="0.05" value="0.2">
            <div class="form-text">Split: <span id="split_value">20%</span></div>
          </div>
          <div class="mb-3">
            <label for="epochs" class="form-label">Training Epochs</label>
            <input type="number" name="epochs" id="epochs" class="form-control" 
                   min="1" max="1000" value="100">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="trainingForm" name="train_model" class="btn btn-primary">
          <i class="bi bi-play-circle"></i> Start Training
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Prediction Modal -->
<div class="modal fade" id="predictionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Make Prediction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="post" id="predictionForm">
          <input type="hidden" name="model_id" id="prediction_model_id">
          <div class="mb-3">
            <label for="input_data" class="form-label">Input Data (JSON)</label>
            <textarea name="input_data" id="input_data" class="form-control" 
                      rows="5" placeholder='{"feature1": 1.0, "feature2": 2.0}'></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="predictionForm" name="make_prediction" class="btn btn-success">
          <i class="bi bi-graph-up"></i> Predict
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// JavaScript for interactive features
$(document).ready(function() {
    // Validation split slider
    $('#validation_split').on('input', function() {
        $('#split_value').text(Math.round($(this).val() * 100) + '%');
    });
    
    // Search functionality for models table
    if ($('#models-table').length) {
        $('#models-table').before(`
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="model-search" class="form-control" placeholder="Search models...">
                </div>
                <div class="col-md-6">
                    <button id="clear-search" class="btn btn-secondary">Clear Search</button>
                </div>
            </div>
        `);
        
        $('#model-search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#models-table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        $('#clear-search').click(function() {
            $('#model-search').val('');
            $('#models-table tbody tr').show();
        });
    }
});

// Modal functions
function showModelDetails(modelId) {
    // Load model details via AJAX
    $.get('ml_ajax.php?action=get_model_details&id=' + modelId, function(data) {
        $('#modelDetailsContent').html(data);
        $('#modelDetailsModal').modal('show');
    });
}

function showTrainingForm(modelId) {
    $('#training_model_id').val(modelId);
    $('#trainingModal').modal('show');
}

function showPredictionForm(modelId) {
    $('#prediction_model_id').val(modelId);
    $('#predictionModal').modal('show');
}

function showEditForm(modelId) {
    // Load edit form via AJAX
    $.get('ml_ajax.php?action=get_edit_form&id=' + modelId, function(data) {
        $('#modelDetailsContent').html(data);
        $('#modelDetailsModal').modal('show');
    });
}

function deleteModel(modelId) {
    if (confirm('Are you sure you want to delete this model? This action cannot be undone.')) {
        var form = $('<form method="post"></form>');
        form.append('<input type="hidden" name="model_id" value="' + modelId + '">');
        form.append('<input type="hidden" name="delete_model" value="1">');
        $('body').append(form);
        form.submit();
    }
}
</script> 