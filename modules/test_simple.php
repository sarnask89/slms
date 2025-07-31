<?php
require_once 'module_loader.php';


// Simple test module
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Test Module</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="container mt-4">
        <h1>Simple Test Module</h1>
        <p>This is a simple test module to verify the system works.</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-secondary">
                    <div class="card-body">
                        <h5 class="card-title">Database Test</h5>
                        <?php
                        try {
                            $pdo = get_pdo();
                            $stmt = $pdo->query("SELECT COUNT(*) as count FROM devices");
                            $deviceCount = $stmt->fetch()['count'];
                            echo "<p>✅ Database connection successful!</p>";
                            echo "<p>Total devices: {$deviceCount}</p>";
                        } catch (Exception $e) {
                            echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card bg-secondary">
                    <div class="card-body">
                        <h5 class="card-title">Function Test</h5>
                        <p>Base URL: <?php echo base_url(); ?></p>
                        <p>Current URL: <?php echo current_url(); ?></p>
                        <p>Asset URL: <?php echo asset_url('style.css'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="../admin_menu_enhanced.php" class="btn btn-primary">Back to Admin Menu</a>
        </div>
    </div>
</body>
</html> 