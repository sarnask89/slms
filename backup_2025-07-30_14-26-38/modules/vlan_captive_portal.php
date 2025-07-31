<?php
/**
 * VLAN Captive Portal Management
 * Manages VLAN interfaces and captive portal settings
 */

if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { session_start(); } }

// Database connection (replace with your actual connection)
function getDatabaseConnection() {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=slms", "username", "password");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

// VLAN Configuration
class VLANCaptivePortal {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDatabaseConnection();
    }
    
    // Get all VLANs with captive portal settings
    public function getVLANs() {
        if (!$this->pdo) return [];
        
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    v.id,
                    v.vlan_id,
                    v.name,
                    v.description,
                    v.network_address,
                    v.gateway,
                    v.captive_portal_enabled,
                    v.captive_portal_url,
                    v.walled_garden_domains,
                    v.session_timeout,
                    v.max_bandwidth,
                    v.status,
                    COUNT(c.id) as active_connections
                FROM vlans v
                LEFT JOIN captive_portal_sessions c ON v.id = c.vlan_id AND c.active = 1
                GROUP BY v.id
                ORDER BY v.vlan_id
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Create new VLAN with captive portal
    public function createVLAN($data) {
        if (!$this->pdo) return false;
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO vlans (
                    vlan_id, name, description, network_address, gateway,
                    captive_portal_enabled, captive_portal_url, walled_garden_domains,
                    session_timeout, max_bandwidth, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $data['vlan_id'],
                $data['name'],
                $data['description'],
                $data['network_address'],
                $data['gateway'],
                $data['captive_portal_enabled'] ? 1 : 0,
                $data['captive_portal_url'],
                json_encode($data['walled_garden_domains']),
                $data['session_timeout'],
                $data['max_bandwidth'],
                'active'
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Update VLAN settings
    public function updateVLAN($id, $data) {
        if (!$this->pdo) return false;
        
        try {
            $stmt = $this->pdo->prepare("
                UPDATE vlans SET
                    name = ?, description = ?, network_address = ?, gateway = ?,
                    captive_portal_enabled = ?, captive_portal_url = ?, walled_garden_domains = ?,
                    session_timeout = ?, max_bandwidth = ?, status = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $data['name'],
                $data['description'],
                $data['network_address'],
                $data['gateway'],
                $data['captive_portal_enabled'] ? 1 : 0,
                $data['captive_portal_url'],
                json_encode($data['walled_garden_domains']),
                $data['session_timeout'],
                $data['max_bandwidth'],
                $data['status'],
                $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Get active sessions for a VLAN
    public function getActiveSessions($vlanId) {
        if (!$this->pdo) return [];
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.id,
                    c.mac_address,
                    c.ip_address,
                    c.username,
                    c.login_time,
                    c.last_activity,
                    c.bytes_in,
                    c.bytes_out,
                    c.status
                FROM captive_portal_sessions c
                WHERE c.vlan_id = ? AND c.active = 1
                ORDER BY c.login_time DESC
            ");
            $stmt->execute([$vlanId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Disconnect user session
    public function disconnectSession($sessionId) {
        if (!$this->pdo) return false;
        
        try {
            $stmt = $this->pdo->prepare("
                UPDATE captive_portal_sessions 
                SET active = 0, logout_time = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$sessionId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Get VLAN statistics
    public function getVLANStats($vlanId) {
        if (!$this->pdo) return [];
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_sessions,
                    COUNT(CASE WHEN active = 1 THEN 1 END) as active_sessions,
                    SUM(bytes_in) as total_bytes_in,
                    SUM(bytes_out) as total_bytes_out,
                    AVG(TIMESTAMPDIFF(MINUTE, login_time, COALESCE(logout_time, NOW()))) as avg_session_duration
                FROM captive_portal_sessions 
                WHERE vlan_id = ?
            ");
            $stmt->execute([$vlanId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Initialize the VLAN manager
$vlanManager = new VLANCaptivePortal();

// Handle form submissions
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_vlan':
                $result = $vlanManager->createVLAN($_POST);
                break;
            case 'update_vlan':
                $result = $vlanManager->updateVLAN($_POST['id'], $_POST);
                break;
            case 'disconnect_session':
                $result = $vlanManager->disconnectSession($_POST['session_id']);
                break;
        }
    }
}

// Get data for display
$vlans = $vlanManager->getVLANs();
$selectedVLAN = isset($_GET['vlan_id']) ? $_GET['vlan_id'] : null;
$activeSessions = $selectedVLAN ? $vlanManager->getActiveSessions($selectedVLAN) : [];
$vlanStats = $selectedVLAN ? $vlanManager->getVLANStats($selectedVLAN) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VLAN Captive Portal Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .vlan-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .vlan-card:hover {
            transform: translateY(-5px);
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        
        .session-row {
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            padding: 1rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#">
                                <i class="fas fa-network-wired"></i> VLAN Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#">
                                <i class="fas fa-shield-alt"></i> Captive Portal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#">
                                <i class="fas fa-users"></i> Active Sessions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#">
                                <i class="fas fa-chart-bar"></i> Statistics
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-network-wired"></i> VLAN Captive Portal Management
                    </h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVLANModal">
                        <i class="fas fa-plus"></i> Create VLAN
                    </button>
                </div>

                <!-- VLAN Overview -->
                <div class="row mb-4">
                    <?php foreach ($vlans as $vlan): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card vlan-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">VLAN <?php echo $vlan['vlan_id']; ?></h5>
                                    <span class="status-badge status-<?php echo $vlan['status']; ?>">
                                        <?php echo ucfirst($vlan['status']); ?>
                                    </span>
                                </div>
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo $vlan['name']; ?></h6>
                                <p class="card-text small"><?php echo $vlan['description']; ?></p>
                                
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-muted">Network</small><br>
                                        <strong><?php echo $vlan['network_address']; ?></strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Active Users</small><br>
                                        <strong><?php echo $vlan['active_connections']; ?></strong>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               <?php echo $vlan['captive_portal_enabled'] ? 'checked' : ''; ?>
                                               onchange="toggleCaptivePortal(<?php echo $vlan['id']; ?>, this.checked)">
                                        <label class="form-check-label small">Captive Portal</label>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <a href="?vlan_id=<?php echo $vlan['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-cog"></i> Manage
                                    </a>
                                    <a href="<?php echo $vlan['captive_portal_url']; ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-external-link-alt"></i> Portal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- VLAN Details -->
                <?php if ($selectedVLAN): ?>
                <div class="row">
                    <div class="col-12">
                        <h3>VLAN Details</h3>
                        
                        <!-- Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <h4><?php echo $vlanStats['active_sessions'] ?? 0; ?></h4>
                                    <small>Active Sessions</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <h4><?php echo number_format(($vlanStats['total_bytes_in'] ?? 0) / 1024 / 1024, 2); ?> MB</h4>
                                    <small>Data In</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <h4><?php echo number_format(($vlanStats['total_bytes_out'] ?? 0) / 1024 / 1024, 2); ?> MB</h4>
                                    <small>Data Out</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <h4><?php echo round($vlanStats['avg_session_duration'] ?? 0); ?> min</h4>
                                    <small>Avg Session</small>
                                </div>
                            </div>
                        </div>

                        <!-- Active Sessions -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-users"></i> Active Sessions</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($activeSessions)): ?>
                                    <p class="text-muted">No active sessions</p>
                                <?php else: ?>
                                    <?php foreach ($activeSessions as $session): ?>
                                    <div class="session-row">
                                        <div class="row align-items-center">
                                            <div class="col-md-3">
                                                <strong><?php echo $session['username']; ?></strong><br>
                                                <small class="text-muted"><?php echo $session['ip_address']; ?></small>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">MAC Address</small><br>
                                                <code><?php echo $session['mac_address']; ?></code>
                                            </div>
                                            <div class="col-md-2">
                                                <small class="text-muted">Login Time</small><br>
                                                <?php echo date('H:i', strtotime($session['login_time'])); ?>
                                            </div>
                                            <div class="col-md-2">
                                                <small class="text-muted">Data Usage</small><br>
                                                <?php echo number_format($session['bytes_in'] / 1024 / 1024, 1); ?> MB
                                            </div>
                                            <div class="col-md-2">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="disconnect_session">
                                                    <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Disconnect this user?')">
                                                        <i class="fas fa-times"></i> Disconnect
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create VLAN Modal -->
    <div class="modal fade" id="createVLANModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New VLAN</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_vlan">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">VLAN ID</label>
                                    <input type="number" class="form-control" name="vlan_id" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Network Address</label>
                                    <input type="text" class="form-control" name="network_address" 
                                           placeholder="192.168.1.0/24" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Gateway</label>
                                    <input type="text" class="form-control" name="gateway" 
                                           placeholder="192.168.1.1" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="captive_portal_enabled" id="captivePortalEnabled">
                            <label class="form-check-label" for="captivePortalEnabled">
                                Enable Captive Portal
                            </label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Captive Portal URL</label>
                            <input type="url" class="form-control" name="captive_portal_url" 
                                   placeholder="http://portal.example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Walled Garden Domains (comma-separated)</label>
                            <input type="text" class="form-control" name="walled_garden_domains" 
                                   placeholder="google.com,facebook.com,gmail.com">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Session Timeout (minutes)</label>
                                    <input type="number" class="form-control" name="session_timeout" value="60">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max Bandwidth (Mbps)</label>
                                    <input type="number" class="form-control" name="max_bandwidth" value="10">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create VLAN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCaptivePortal(vlanId, enabled) {
            // AJAX call to toggle captive portal
            fetch('api/toggle_captive_portal.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    vlan_id: vlanId,
                    enabled: enabled
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('Captive portal ' + (enabled ? 'enabled' : 'disabled') + ' successfully');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating captive portal settings');
            });
        }
    </script>
</body>
</html> 