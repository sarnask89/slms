<?php
/**
 * Test Cacti Integration
 * This file tests the Cacti API integration functionality
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/cacti_api.php';

$pageTitle = 'Test Cacti Integration';

// Test results
$tests = [];

// Test 1: Check if Cacti API class exists
$tests['Cacti API Class'] = class_exists('CactiAPI') ? 'PASS' : 'FAIL';

// Test 2: Check if helper functions exist
$tests['cacti_add_device function'] = function_exists('cacti_add_device') ? 'PASS' : 'FAIL';
$tests['cacti_get_device_data function'] = function_exists('cacti_get_device_data') ? 'PASS' : 'FAIL';
$tests['cacti_check_status function'] = function_exists('cacti_check_status') ? 'PASS' : 'FAIL';

// Test 3: Test Cacti API connection
try {
    $cacti_api = new CactiAPI();
    $status_result = $cacti_api->getStatus();
    $tests['Cacti API Connection'] = $status_result['success'] ? 'PASS' : 'FAIL';
    $tests['Cacti API Error'] = $status_result['success'] ? 'None' : $status_result['error'];
} catch (Exception $e) {
    $tests['Cacti API Connection'] = 'FAIL';
    $tests['Cacti API Error'] = $e->getMessage();
}

// Test 4: Test SNMP functionality
$tests['SNMP Extension'] = extension_loaded('snmp') ? 'PASS' : 'FAIL';
$tests['snmpget function'] = function_exists('snmpget') ? 'PASS' : 'FAIL';

// Test 5: Test database connection
try {
    $pdo = get_pdo();
    $tests['Database Connection'] = 'PASS';
} catch (Exception $e) {
    $tests['Database Connection'] = 'FAIL';
    $tests['Database Error'] = $e->getMessage();
}

// Test 6: Test Cacti integration file
$tests['Cacti Integration File'] = file_exists(__DIR__ . '/cacti_integration.php') ? 'PASS' : 'FAIL';

ob_start();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - sLMS</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../partials/layout.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-gear"></i> Test Cacti Integration
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="cacti_integration.php" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-graph-up"></i> Cacti Integration
                            </a>
                            <a href="../admin_menu.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-list"></i> Admin Menu
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-check-circle"></i> Integration Test Results
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Test</th>
                                        <th>Status</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tests as $test_name => $result): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($test_name); ?></td>
                                            <td>
                                                <?php if ($result === 'PASS'): ?>
                                                    <span class="badge bg-success">PASS</span>
                                                <?php elseif ($result === 'FAIL'): ?>
                                                    <span class="badge bg-danger">FAIL</span>
                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo htmlspecialchars($result); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($tests[$test_name . ' Error'])): ?>
                                                    <small class="text-danger"><?php echo htmlspecialchars($tests[$test_name . ' Error']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Summary -->
                        <div class="mt-4">
                            <?php
                            $pass_count = count(array_filter($tests, function($result) { return $result === 'PASS'; }));
                            $total_count = count($tests);
                            $success_rate = ($pass_count / $total_count) * 100;
                            ?>
                            <div class="alert <?php echo $success_rate >= 80 ? 'alert-success' : ($success_rate >= 50 ? 'alert-warning' : 'alert-danger'); ?>">
                                <h6>Test Summary:</h6>
                                <p class="mb-0">
                                    <strong><?php echo $pass_count; ?></strong> out of <strong><?php echo $total_count; ?></strong> tests passed 
                                    (<strong><?php echo round($success_rate, 1); ?>%</strong> success rate)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightbulb"></i> Recommendations
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php if ($tests['Cacti API Connection'] === 'FAIL'): ?>
                                <li class="text-danger">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    <strong>Cacti Connection Failed:</strong> Make sure Cacti is running and accessible at the configured URL.
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($tests['SNMP Extension'] === 'FAIL'): ?>
                                <li class="text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    <strong>SNMP Extension Missing:</strong> Install PHP SNMP extension: <code>sudo apt-get install php-snmp</code>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($tests['Database Connection'] === 'FAIL'): ?>
                                <li class="text-danger">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    <strong>Database Connection Failed:</strong> Check your database configuration in config.php
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($success_rate >= 80): ?>
                                <li class="text-success">
                                    <i class="bi bi-check-circle"></i> 
                                    <strong>Integration Ready:</strong> Cacti integration is working properly!
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?> 