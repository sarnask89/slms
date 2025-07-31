<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mndp_enhanced.php';

$pageTitle = 'MNDP Monitor';
$pdo = get_pdo();
$discovered = [];
$errors = [];

// Handle MNDP discovery
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_mndp'])) {
    try {
        $mndp = new MNDPEnhanced();
        $discovered = $mndp->discover(10); // 10 second timeout
        
        // Save to database
        foreach ($discovered as $device) {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO discovered_devices 
                (ip_address, sys_name, sys_descr, method, discovered_at) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $device['ip'],
                $device['identity'] ?: $device['platform'],
                "MNDP: {$device['platform']} {$device['version_info']} (MAC: {$device['mac_address']})",
                'MNDP',
                $device['discovered_at']
            ]);
        }
        
        $success = "MNDP discovery completed! Found " . count($discovered) . " devices.";
        
    } catch (Exception $e) {
        $errors[] = "MNDP discovery failed: " . $e->getMessage();
    }
}

// Get recent discoveries from database
$stmt = $pdo->query("
    SELECT * FROM discovered_devices 
    WHERE method = 'MNDP' 
    ORDER BY discovered_at DESC 
    LIMIT 50
");
$recent_discoveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-broadcast"></i> MNDP Monitor (Mikrotik Neighbour Discovery Protocol)
                    </h5>
                </div>
                <div class="card-body">
                    <!-- MNDP Discovery Form -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Start MNDP Discovery</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                MNDP listens for Mikrotik devices broadcasting their presence on UDP port 5678.
                                This will scan for Mikrotik routers and switches in your network.
                            </p>
                            <form method="post">
                                <button type="submit" name="start_mndp" class="btn btn-primary">
                                    <i class="bi bi-broadcast"></i> Start MNDP Discovery (10s)
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Results -->
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h6><i class="bi bi-exclamation-triangle"></i> Errors:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Recent Discoveries -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Recent MNDP Discoveries (<?= count($recent_discoveries) ?> devices)</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_discoveries)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>IP Address</th>
                                                <th>Device Name</th>
                                                <th>Description</th>
                                                <th>Discovered</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_discoveries as $device): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($device['ip_address']) ?></td>
                                                    <td><?= htmlspecialchars($device['sys_name']) ?></td>
                                                    <td><?= htmlspecialchars($device['sys_descr']) ?></td>
                                                    <td><?= htmlspecialchars($device['discovered_at']) ?></td>
                                                    <td>
                                                        <a href="network_monitoring_enhanced.php?device_ip=<?= urlencode($device['ip_address']) ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-activity"></i> Monitor
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No MNDP devices discovered yet. Run discovery to find Mikrotik devices.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?> 