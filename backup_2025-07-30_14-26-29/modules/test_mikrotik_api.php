<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mikrotik_rest_api_v7.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$pdo = get_pdo();
$test_results = [];
$errors = [];
$success_messages = [];
$debug_info = [];

function runTest($name, $callback) {
    try {
        $start = microtime(true);
        $result = $callback();
        $duration = round((microtime(true) - $start) * 1000, 2); // in milliseconds
        
        return [
            'name' => $name,
            'status' => 'success',
            'duration' => $duration,
            'result' => $result
        ];
    } catch (Exception $e) {
        return [
            'name' => $name,
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['run_tests'])) {
        $device_id = $_POST['device_id'] ?? null;
        if ($device_id) {
            $stmt = $pdo->prepare("SELECT * FROM skeleton_devices WHERE id = ?");
            $stmt->execute([$device_id]);
            $device = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($device) {
                try {
                    $api = new MikroTikRestAPIv7(
                        $device['ip_address'],
                        $device['api_username'] ?? 'admin',
                        $device['api_password'] ?? '',
                        $device['api_port'] ?? 443,
                        $device['api_ssl'] ?? true
                    );
                    
                    // Test 1: Connection Test
                    $test_results[] = runTest('Connection Test', function() use ($api) {
                        $result = $api->testConnection();
                        if (!$result['success']) {
                            throw new Exception("Connection failed");
                        }
                        return "Connection successful";
                    });
                    
                    // Test 2: System Resources
                    $test_results[] = runTest('System Resources', function() use ($api) {
                        $resources = $api->getSystemResources();
                        if (!$resources) {
                            throw new Exception("Failed to get system resources");
                        }
                        return [
                            'board' => $resources['board-name'] ?? 'N/A',
                            'version' => $resources['version'] ?? 'N/A',
                            'cpu' => $resources['cpu-load'] ?? 'N/A'
                        ];
                    });
                    
                    // Test 3: System Health
                    $test_results[] = runTest('System Health', function() use ($api) {
                        $health = $api->getSystemHealth();
                        if (!$health) {
                            throw new Exception("Failed to get system health");
                        }
                        return $health;
                    });
                    
                    // Test 4: Interfaces
                    $test_results[] = runTest('Interfaces', function() use ($api) {
                        $interfaces = $api->getInterfaces();
                        if (!$interfaces) {
                            throw new Exception("Failed to get interfaces");
                        }
                        return count($interfaces) . " interfaces found";
                    });
                    
                    // Test 5: DHCP Info
                    $test_results[] = runTest('DHCP Information', function() use ($api) {
                        $dhcp = $api->getDhcpInfo();
                        if (!$dhcp) {
                            throw new Exception("Failed to get DHCP information");
                        }
                        return [
                            'leases' => count($dhcp['leases'] ?? []),
                            'networks' => count($dhcp['networks'] ?? []),
                            'servers' => count($dhcp['servers'] ?? [])
                        ];
                    });
                    
                    // Test 6: DNS Settings
                    $test_results[] = runTest('DNS Settings', function() use ($api) {
                        $dns = $api->getDnsSettings();
                        if (!$dns) {
                            throw new Exception("Failed to get DNS settings");
                        }
                        return $dns;
                    });
                    
                    // Test 7: Queue Information
                    $test_results[] = runTest('Queue Information', function() use ($api) {
                        $queues = $api->getQueues();
                        if (!$queues) {
                            throw new Exception("Failed to get queue information");
                        }
                        return count($queues) . " queues found";
                    });
                    
                    $success_messages[] = "✅ All API tests completed";
                    
                } catch (Exception $e) {
                    $errors[] = "❌ Error during tests: " . $e->getMessage();
                }
            }
        }
    }
}

// Get skeleton devices
$stmt = $pdo->prepare("SELECT * FROM skeleton_devices WHERE type = 'mikrotik' ORDER BY name");
$stmt->execute();
$skeleton_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Start output buffering
ob_start();

// Set page title
$pageTitle = 'Test MikroTik API';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Test MikroTik API</h1>
    
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (!empty($success_messages)): ?>
        <?php foreach ($success_messages as $message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-server me-1"></i>
            Select Device for API Testing
        </div>
        <div class="card-body">
            <?php if (empty($skeleton_devices)): ?>
                <div class="alert alert-info">
                    No MikroTik devices found. Please <a href="add_skeleton_device.php">add a device</a> first.
                </div>
            <?php else: ?>
                <form method="post" class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <select name="device_id" class="form-select">
                                <?php foreach ($skeleton_devices as $device): ?>
                                    <option value="<?php echo htmlspecialchars($device['id']); ?>">
                                        <?php echo htmlspecialchars($device['name']); ?> (<?php echo htmlspecialchars($device['ip_address']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" name="run_tests" class="btn btn-primary">
                                Run API Tests
                            </button>
                            <a href="add_skeleton_device.php" class="btn btn-success ms-2">
                                Add New Device
                            </a>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
            
            <?php if (!empty($test_results)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Test</th>
                                <th>Status</th>
                                <th>Duration</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($test_results as $test): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($test['name']); ?></td>
                                    <td>
                                        <?php if ($test['status'] === 'success'): ?>
                                            <span class="badge bg-success">Success</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Error</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($test['duration'])): ?>
                                            <?php echo htmlspecialchars($test['duration']); ?> ms
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($test['status'] === 'success'): ?>
                                            <?php 
                                            if (is_array($test['result'])) {
                                                echo '<pre class="mb-0">' . htmlspecialchars(json_encode($test['result'], JSON_PRETTY_PRINT)) . '</pre>';
                                            } else {
                                                echo htmlspecialchars($test['result']);
                                            }
                                            ?>
                                        <?php else: ?>
                                            <span class="text-danger"><?php echo htmlspecialchars($test['error']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout template
include __DIR__ . '/../partials/layout.php';
?>
