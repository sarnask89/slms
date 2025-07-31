<?php
/**
 * Enhanced Admin Menu with WebGL Integration
 * SLMS v1.1.0 - Unified Navigation System
 */

require_once "modules/config.php";

// Get system statistics
try {
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM devices");
    $deviceCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients");
    $clientCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM network_connections");
    $connectionCount = $stmt->fetch()['count'];
    
} catch (Exception $e) {
    $deviceCount = 0;
    $userCount = 0;
    $clientCount = 0;
    $connectionCount = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLMS v1.1.0 - AI Service Network Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/style.css" rel="stylesheet">
    
    <style>
        .webgl-highlight {
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
            border: 1px solid #00ff00;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .feature-card {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
            border: 1px solid #333;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            border-color: #00ff00;
            transform: translateY(-2px);
        }
        
        .webgl-badge {
            background: linear-gradient(45deg, #00ff00, #008800);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 5px;
        }
    </style>
</head>
<body class="dark-theme">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-diagram-3"></i>
                    SLMS v1.1.0 - AI Service Network Management System
                    <span class="webgl-badge">WebGL Enhanced</span>
                </h1>
            </div>
        </div>
        
        <!-- WebGL Integration Highlight -->
        <div class="webgl-highlight">
            <div class="row">
                <div class="col-md-8">
                    <h4><i class="bi bi-cube"></i> New: 3D Network Visualization</h4>
                    <p>Experience your network infrastructure in immersive 3D with real-time monitoring, Cacti integration, and SNMP data visualization.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="webgl_demo.php" class="btn btn-success">
                        <i class="bi bi-play-circle"></i> Launch 3D Viewer
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- System Statistics -->
            <div class="col-md-3">
                <div class="feature-card">
                    <h5><i class="bi bi-graph-up"></i> System Statistics</h5>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4"><?php echo $deviceCount; ?></div>
                            <small>Total Devices</small>
                        </div>
                        <div class="col-6">
                            <div class="h4"><?php echo $userCount; ?></div>
                            <small>Total Users</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-md-9">
                <div class="feature-card">
                    <h5><i class="bi bi-lightning"></i> Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="modules/devices.php" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-hdd-network"></i> Manage Devices
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="modules/invoices.php" class="btn btn-info w-100 mb-2">
                                <i class="bi bi-receipt"></i> Invoice Management
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="modules/devices.php" class="btn btn-warning w-100 mb-2">
                                <i class="bi bi-graph-up"></i> Device Management
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="webgl_demo.php" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-cube"></i> 3D Viewer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Module Categories -->
        <div class="row">
            <!-- Network Management -->
            <div class="col-md-4">
                <div class="feature-card">
                    <h5><i class="bi bi-diagram-3"></i> Network Management</h5>
                    <ul class="list-unstyled">
                        <li><a href="modules/devices.php"><i class="bi bi-hdd"></i> Device Management</a></li>
                        <li><a href="modules/invoices.php"><i class="bi bi-receipt"></i> Invoice Management</a></li>
                        <li><a href="modules/clients.php"><i class="bi bi-people"></i> Client Management</a></li>
                        <li><a href="modules/users.php"><i class="bi bi-person"></i> User Management</a></li>
                        <li><a href="webgl_demo.php"><i class="bi bi-cube"></i> 3D Network Viewer <span class="webgl-badge">NEW</span></a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Monitoring & Integration -->
            <div class="col-md-4">
                <div class="feature-card">
                    <h5><i class="bi bi-graph-up"></i> Monitoring & Integration</h5>
                    <ul class="list-unstyled">
                        <li><a href="modules/invoices.php"><i class="bi bi-receipt"></i> Invoice Management</a></li>
                        <li><a href="modules/tariffs.php"><i class="bi bi-currency-dollar"></i> Tariff Management</a></li>
                        <li><a href="modules/payments.php"><i class="bi bi-credit-card"></i> Payment Management</a></li>
                        <li><a href="modules/test_simple.php"><i class="bi bi-activity"></i> Test Module</a></li>
                        <li><a href="modules/services.php"><i class="bi bi-gear"></i> Service Management</a></li>
                        <li><a href="modules/monitoring_dashboard.php"><i class="bi bi-speedometer2"></i> Monitoring Dashboard</a></li>
                        <li><a href="modules/activity_log.php"><i class="bi bi-journal-text"></i> Activity Log</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- System Administration -->
            <div class="col-md-4">
                <div class="feature-card">
                    <h5><i class="bi bi-gear"></i> System Administration</h5>
                    <ul class="list-unstyled">
                        <li><a href="modules/user_management.php"><i class="bi bi-people"></i> User Management</a></li>
                        <li><a href="modules/access_level_manager.php"><i class="bi bi-shield-check"></i> Access Control</a></li>
                        <li><a href="modules/system_status.php"><i class="bi bi-info-circle"></i> System Status</a></li>
                        <li><a href="modules/menu_editor.php"><i class="bi bi-list"></i> Menu Editor</a></li>
                        <li><a href="modules/theme_compositor.php"><i class="bi bi-palette"></i> Theme Composer</a></li>
                        <li><a href="modules/snmp_monitoring_simple.php"><i class="bi bi-cpu"></i> SNMP Monitoring</a></li>
                        <li><a href="modules/cacti_integration_simple.php"><i class="bi bi-graph-up"></i> Cacti Integration</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <p class="text-muted">
                    SLMS v1.1.0 - Enhanced with WebGL 3D Visualization | 
                    <a href="docs/WEBGL_INTEGRATION_PLAN.md">Integration Documentation</a>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>