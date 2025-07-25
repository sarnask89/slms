<?php
/**
 * Optimization Launcher
 * Simple interface to run various optimization tools
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create logs directory if it doesn't exist
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Network Monitoring System - Optimization Tools</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 { 
            text-align: center; 
            color: #333; 
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .tool-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .tool-card:hover {
            border-color: #007bff;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        .tool-card h3 {
            color: #007bff;
            margin-top: 0;
            font-size: 1.3em;
        }
        .tool-card p {
            color: #666;
            line-height: 1.6;
        }
        .tool-card .icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1em;
            margin: 5px;
        }
        .btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.4);
        }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .status {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        .status.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status.warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .quick-actions {
            background: #e9ecef;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .quick-actions h3 {
            margin-top: 0;
            color: #495057;
        }
        .system-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .system-info h4 {
            margin-top: 0;
            color: #007bff;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .info-item {
            background: white;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
            font-size: 0.9em;
        }
        .info-value {
            color: #007bff;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Network Monitoring System - Optimization Tools</h1>
        
        <!-- System Information -->
        <div class="system-info">
            <h4>📊 System Information</h4>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">PHP Version</div>
                    <div class="info-value"><?php echo phpversion(); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Memory Limit</div>
                    <div class="info-value"><?php echo ini_get('memory_limit'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Max Execution Time</div>
                    <div class="info-value"><?php echo ini_get('max_execution_time'); ?>s</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Current Memory Usage</div>
                    <div class="info-value"><?php echo round(memory_get_usage(true) / 1024 / 1024, 2); ?> MB</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3>⚡ Quick Actions</h3>
            <a href="comprehensive_optimization.php" class="btn btn-success">🚀 Run Complete Optimization</a>
            <a href="system_health_checker.php" class="btn btn-warning">🏥 System Health Check</a>
            <a href="debug_optimization_tool.php" class="btn">🔍 Debug Analysis</a>
            <a href="performance_optimizer.php" class="btn">⚡ Performance Optimizer</a>
        </div>

        <!-- Individual Tools -->
        <div class="tools-grid">
            <div class="tool-card" onclick="window.location.href='comprehensive_optimization.php'">
                <div class="icon">🚀</div>
                <h3>Comprehensive Optimization</h3>
                <p>Run all optimization tools in sequence for a complete system analysis and optimization.</p>
                <a href="comprehensive_optimization.php" class="btn">Run Complete Optimization</a>
            </div>

            <div class="tool-card" onclick="window.location.href='system_health_checker.php'">
                <div class="icon">🏥</div>
                <h3>System Health Check</h3>
                <p>Comprehensive health monitoring for system resources, database, network services, and security.</p>
                <a href="system_health_checker.php" class="btn">Run Health Check</a>
            </div>

            <div class="tool-card" onclick="window.location.href='debug_optimization_tool.php'">
                <div class="icon">🔍</div>
                <h3>Debug Analysis</h3>
                <p>Environment check, database connectivity, file system, Redis, and SNMP functionality analysis.</p>
                <a href="debug_optimization_tool.php" class="btn">Run Debug Tool</a>
            </div>

            <div class="tool-card" onclick="window.location.href='performance_optimizer.php'">
                <div class="icon">⚡</div>
                <h3>Performance Optimizer</h3>
                <p>Database optimization, caching implementation, SNMP polling optimization, and memory management.</p>
                <a href="performance_optimizer.php" class="btn">Run Performance Optimizer</a>
            </div>

            <div class="tool-card" onclick="window.location.href='error_monitor.php'">
                <div class="icon">📊</div>
                <h3>Error Monitor</h3>
                <p>Error tracking, logging system, performance monitoring, and alert generation.</p>
                <a href="error_monitor.php" class="btn">View Error Monitor</a>
            </div>

            <div class="tool-card">
                <div class="icon">📁</div>
                <h3>Log Management</h3>
                <p>View and manage log files, clean old entries, and monitor log statistics.</p>
                <a href="logs/" class="btn">View Logs</a>
            </div>
        </div>

        <!-- Status Check -->
        <div class="system-info">
            <h4>🔍 Quick Status Check</h4>
            <?php
            // Check if required directories exist
            $directories = ['logs', 'modules', 'assets', 'partials'];
            $missingDirs = [];
            foreach ($directories as $dir) {
                if (!is_dir($dir)) {
                    $missingDirs[] = $dir;
                }
            }
            
            if (empty($missingDirs)) {
                echo "<div class='status success'>✅ All required directories exist</div>";
            } else {
                echo "<div class='status warning'>⚠️ Missing directories: " . implode(', ', $missingDirs) . "</div>";
            }
            
            // Check if config file exists
            if (file_exists('config.php')) {
                echo "<div class='status success'>✅ Configuration file found</div>";
            } else {
                echo "<div class='status error'>❌ Configuration file missing</div>";
            }
            
            // Check PHP extensions
            $requiredExtensions = ['mysqli', 'curl', 'json'];
            $missingExtensions = [];
            foreach ($requiredExtensions as $ext) {
                if (!extension_loaded($ext)) {
                    $missingExtensions[] = $ext;
                }
            }
            
            if (empty($missingExtensions)) {
                echo "<div class='status success'>✅ All required PHP extensions loaded</div>";
            } else {
                echo "<div class='status warning'>⚠️ Missing PHP extensions: " . implode(', ', $missingExtensions) . "</div>";
            }
            ?>
        </div>

        <!-- Instructions -->
        <div class="system-info">
            <h4>📋 Usage Instructions</h4>
            <ol>
                <li><strong>Start with Comprehensive Optimization:</strong> This will run all tools and give you a complete overview.</li>
                <li><strong>Check System Health:</strong> Monitor your system resources and identify potential issues.</li>
                <li><strong>Run Performance Optimizer:</strong> Optimize database queries, implement caching, and improve performance.</li>
                <li><strong>Monitor Errors:</strong> Set up error tracking and monitoring for ongoing maintenance.</li>
                <li><strong>Regular Maintenance:</strong> Run these tools regularly to keep your system optimized.</li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 30px; color: #666;">
            <p><em>Network Monitoring System Optimization Tools</em></p>
            <p><small>Generated on: <?php echo date('Y-m-d H:i:s'); ?></small></p>
        </div>
    </div>

    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers for tool cards
            const toolCards = document.querySelectorAll('.tool-card');
            toolCards.forEach(card => {
                card.addEventListener('click', function() {
                    const link = this.querySelector('a');
                    if (link) {
                        window.location.href = link.href;
                    }
                });
            });
        });
    </script>
</body>
</html>