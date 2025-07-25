<?php
/**
 * Bridge NAT Captive Portal Web Interface
 * Simple web interface for testing bridge-based traffic control
 */

require_once 'modules/bridge_nat_controller.php';

$controller = new BridgeNATController(true); // Mock mode for testing
$message = '';
$stats = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'initialize':
                $result = $controller->initializeBridgeSystem();
                $message = $result['success'] ? 'Bridge system initialized successfully' : 'Error: ' . $result['error'];
                break;
                
            case 'connect':
                $mac = $_POST['mac_address'] ?? '00:11:22:33:44:55';
                $role = $_POST['user_role'] ?? 'guest';
                $result = $controller->processBridgeConnection($mac, null, $role);
                $message = $result['success'] ? "Connection established for $mac ($role)" : 'Error: ' . $result['error'];
                break;
                
            case 'authenticate':
                $mac = $_POST['mac_address'] ?? '00:11:22:33:44:55';
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $result = $controller->handlePortalAuthentication($mac, $username, $password);
                $message = $result['success'] ? "Authentication successful for $username" : 'Error: ' . $result['error'];
                break;
                
            case 'cleanup':
                $cleaned = $controller->cleanupExpiredAccess();
                $message = "Cleaned up $cleaned expired access records";
                break;
        }
    }
}

// Get statistics
if (isset($_GET['stats'])) {
    $stats = $controller->getBridgeStats();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bridge NAT Captive Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .header p { opacity: 0.9; font-size: 1.1em; }
        .content { padding: 30px; }
        .section { 
            margin-bottom: 30px; 
            padding: 20px; 
            border: 1px solid #e1e8ed; 
            border-radius: 8px;
            background: #f8f9fa;
        }
        .section h2 { 
            color: #2c3e50; 
            margin-bottom: 15px; 
            border-bottom: 2px solid #3498db; 
            padding-bottom: 10px;
        }
        .form-group { margin-bottom: 15px; }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 600; 
            color: #2c3e50;
        }
        input, select { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #e1e8ed; 
            border-radius: 6px; 
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus { 
            outline: none; 
            border-color: #3498db; 
        }
        button { 
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        button:hover { 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        .btn-danger { 
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        .btn-success { 
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }
        .message { 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px;
            font-weight: 600;
        }
        .message.success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .message.error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #3498db;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .demo-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌉 Bridge NAT Portal</h1>
            <p>Traffic Control System for Bridged Interfaces</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="section">
                <h2>🚀 System Control</h2>
                <div class="demo-actions">
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="action" value="initialize">
                        <button type="submit">Initialize Bridge System</button>
                    </form>
                    
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="action" value="cleanup">
                        <button type="submit" class="btn-danger">Cleanup Expired Access</button>
                    </form>
                    
                    <a href="?stats=1" class="btn-success" style="text-decoration: none; display: inline-block; text-align: center; line-height: 44px;">
                        Get Statistics
                    </a>
                </div>
            </div>

            <div class="section">
                <h2>🔗 Connection Management</h2>
                <form method="post">
                    <input type="hidden" name="action" value="connect">
                    <div class="form-group">
                        <label for="mac_address">MAC Address:</label>
                        <input type="text" id="mac_address" name="mac_address" value="00:11:22:33:44:55" required>
                    </div>
                    <div class="form-group">
                        <label for="user_role">User Role:</label>
                        <select id="user_role" name="user_role">
                            <option value="guest">Guest</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit">Create Bridge Connection</button>
                </form>
            </div>

            <div class="section">
                <h2>🔐 Authentication</h2>
                <form method="post">
                    <input type="hidden" name="action" value="authenticate">
                    <div class="form-group">
                        <label for="auth_mac">MAC Address:</label>
                        <input type="text" id="auth_mac" name="mac_address" value="00:11:22:33:44:55" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" placeholder="Enter username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                    </div>
                    <button type="submit">Authenticate User</button>
                </form>
            </div>

            <?php if ($stats): ?>
            <div class="section">
                <h2>📊 System Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?= $stats['total_access'] ?></div>
                        <div class="stat-label">Total Access</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $stats['active_access'] ?></div>
                        <div class="stat-label">Active Access</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $stats['expired_access'] ?></div>
                        <div class="stat-label">Expired Access</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $stats['bridge_rules']['filters'] ?></div>
                        <div class="stat-label">Bridge Filters</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $stats['bridge_rules']['nat'] ?></div>
                        <div class="stat-label">NAT Rules</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $stats['bridge_rules']['mangle'] ?></div>
                        <div class="stat-label">Mangle Rules</div>
                    </div>
                </div>
                
                <?php if (!empty($stats['users_by_role'])): ?>
                <h3 style="margin-top: 20px; color: #2c3e50;">Users by Role:</h3>
                <div class="stats-grid">
                    <?php foreach ($stats['users_by_role'] as $role => $count): ?>
                    <div class="stat-card">
                        <div class="stat-number"><?= $count ?></div>
                        <div class="stat-label"><?= ucfirst($role) ?> Users</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="section">
                <h2>📋 Quick Test Commands</h2>
                <p style="margin-bottom: 15px; color: #7f8c8d;">
                    Use these curl commands to test the API endpoints:
                </p>
                <div style="background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 0.9em;">
                    <div style="margin-bottom: 10px;">
                        <strong>Initialize:</strong><br>
                        curl -X POST "http://localhost/bridge_portal.php" -d "action=initialize"
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Connect:</strong><br>
                        curl -X POST "http://localhost/bridge_portal.php" -d "action=connect&mac_address=00:11:22:33:44:55&user_role=guest"
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Authenticate:</strong><br>
                        curl -X POST "http://localhost/bridge_portal.php" -d "action=authenticate&mac_address=00:11:22:33:44:55&username=test&password=test"
                    </div>
                    <div>
                        <strong>Statistics:</strong><br>
                        curl "http://localhost/bridge_portal.php?stats=1"
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh statistics every 30 seconds
        if (window.location.search.includes('stats=1')) {
            setTimeout(() => {
                window.location.reload();
            }, 30000);
        }
    </script>
</body>
</html> 