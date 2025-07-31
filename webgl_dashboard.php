<?php
/**
 * WebGL Dashboard Integration
 * Provides quick access to 3D network visualization
 */

require_once "modules/config.php";

// Get network statistics for dashboard
function get_dashboard_stats() {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_devices,
                SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) as offline_devices
            FROM devices
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ["total_devices" => 0, "online_devices" => 0, "offline_devices" => 0];
    }
}

$stats = get_dashboard_stats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebGL Dashboard - SLMS v1.1.0</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/style.css" rel="stylesheet">
    
    <style>
        .dashboard-card {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
            border: 1px solid #333;
        }
        
        .webgl-preview {
            width: 100%;
            height: 300px;
            background: #1a1a1a;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00ff00;
            font-size: 1.2em;
        }
    </style>
</head>
<body class="dark-theme">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-cube"></i>
                    WebGL Dashboard - 3D Network Visualization
                </h1>
            </div>
        </div>
        
        <div class="row">
            <!-- Statistics -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h5><i class="bi bi-graph-up"></i> Network Statistics</h5>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4"><?php echo $stats["total_devices"]; ?></div>
                            <small>Total Devices</small>
                        </div>
                        <div class="col-6">
                            <div class="h4"><?php echo $stats["online_devices"]; ?></div>
                            <small>Online</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-md-9">
                <div class="dashboard-card">
                    <h5><i class="bi bi-lightning"></i> Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="webgl_demo.php" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-play-circle"></i> Launch 3D Viewer
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="webgl_demo_fallback.php" class="btn btn-secondary w-100 mb-2">
                                <i class="bi bi-arrow-down-circle"></i> 2D Fallback
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="modules/webgl_network_viewer.php" class="btn btn-info w-100 mb-2">
                                <i class="bi bi-gear"></i> API Interface
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="admin_menu_enhanced.php" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-house"></i> Main Menu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- WebGL Preview -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5><i class="bi bi-cube"></i> 3D Network Preview</h5>
                    <div class="webgl-preview">
                        <div class="text-center">
                            <i class="bi bi-cube" style="font-size: 3em; margin-bottom: 10px;"></i>
                            <br>
                            <strong>3D Network Visualization</strong>
                            <br>
                            <small>Click "Launch 3D Viewer" to experience immersive network monitoring</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>