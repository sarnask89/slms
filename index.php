<?php
require_once 'config.php';

// Check database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbStatus = "Connected";
} catch(PDOException $e) {
    $dbStatus = "Error: " . $e->getMessage();
}

// Get system statistics
$stats = [];
try {
    // Device count
    $stmt = $pdo->query("SELECT COUNT(*) FROM devices");
    $stats['devices'] = $stmt->fetchColumn();
    
    // Client count
    $stmt = $pdo->query("SELECT COUNT(*) FROM clients");
    $stats['clients'] = $stmt->fetchColumn();
    
    // Network count
    $stmt = $pdo->query("SELECT COUNT(*) FROM networks");
    $stats['networks'] = $stmt->fetchColumn();
    
    // User count
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['users'] = $stmt->fetchColumn();
} catch(PDOException $e) {
    $stats = ['devices' => 0, 'clients' => 0, 'networks' => 0, 'users' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLMS v1.2.0 - Advanced Network Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-bg: #0a0a0a;
            --secondary-bg: #1a1a1a;
            --accent-blue: #00d4ff;
            --accent-green: #00ff88;
            --accent-orange: #ff6b35;
            --accent-purple: #8b5cf6;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --glow-blue: rgba(0, 212, 255, 0.6);
            --glow-green: rgba(0, 255, 136, 0.6);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--primary-bg), var(--secondary-bg));
            color: var(--text-primary);
            font-family: 'Courier New', monospace;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header */
        .hero-header {
            background: rgba(26, 26, 26, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--accent-blue);
            padding: 40px 0;
            text-align: center;
            box-shadow: 0 0 30px var(--glow-blue);
        }

        .hero-title {
            font-size: 48px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: var(--accent-blue);
            text-shadow: 0 0 20px var(--accent-blue);
            margin-bottom: 10px;
        }

        .hero-subtitle {
            font-size: 18px;
            color: var(--text-secondary);
            margin-bottom: 30px;
        }

        /* Main Content */
        .main-content {
            padding: 60px 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Menu Cards */
        .menu-section {
            margin-bottom: 60px;
        }

        .section-title {
            font-size: 32px;
            font-weight: bold;
            color: var(--accent-green);
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 0 0 15px var(--accent-green);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .menu-card {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(15px);
            border: 2px solid var(--accent-blue);
            border-radius: 20px;
            padding: 30px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px var(--glow-blue);
            border-color: var(--accent-green);
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 212, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .menu-card:hover::before {
            left: 100%;
        }

        .card-icon {
            font-size: 48px;
            color: var(--accent-blue);
            margin-bottom: 20px;
            text-align: center;
        }

        .card-title {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-primary);
            margin-bottom: 15px;
            text-align: center;
        }

        .card-description {
            color: var(--text-secondary);
            text-align: center;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .card-features {
            list-style: none;
            padding: 0;
            margin-bottom: 25px;
        }

        .card-features li {
            color: var(--text-secondary);
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }

        .card-features li::before {
            content: '▶';
            color: var(--accent-green);
            position: absolute;
            left: 0;
        }

        .card-button {
            width: 100%;
            background: linear-gradient(145deg, var(--accent-blue), #0099cc);
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card-button:hover {
            background: linear-gradient(145deg, var(--accent-green), #00cc66);
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.3);
        }

        /* Stats Section */
        .stats-section {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(15px);
            border: 2px solid var(--accent-green);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 0 30px var(--glow-green);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: var(--accent-green);
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 16px;
        }

        /* Footer */
        .footer {
            background: rgba(26, 26, 26, 0.9);
            backdrop-filter: blur(10px);
            border-top: 2px solid var(--accent-blue);
            padding: 30px 0;
            text-align: center;
            margin-top: 60px;
        }

        .footer-text {
            color: var(--text-secondary);
            margin-bottom: 10px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .footer-link {
            color: var(--accent-blue);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: var(--accent-green);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 32px;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Hero Header -->
    <header class="hero-header">
<div class="container">
            <h1 class="hero-title">SLMS v1.2.0</h1>
            <p class="hero-subtitle">Advanced Network Management System with 3D Visualization</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- System Statistics -->
        <div class="stats-section">
            <h2 class="section-title">📊 System Statistics</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['devices']; ?></div>
                    <div class="stat-label">Devices</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['clients']; ?></div>
                    <div class="stat-label">Clients</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['networks']; ?></div>
                    <div class="stat-label">Networks</div>
            </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['users']; ?></div>
                    <div class="stat-label">Users</div>
            </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $dbStatus; ?></div>
                    <div class="stat-label">Database</div>
            </div>
            </div>
        </div>
        
        <!-- 3D Menu Systems -->
        <section class="menu-section">
            <h2 class="section-title">🎮 3D Menu Systems</h2>
            <div class="menu-grid">
                <div class="menu-card" onclick="window.location.href='3d_menu_framework.php'">
                    <div class="card-icon">🎯</div>
                    <h3 class="card-title">3D Menu Framework</h3>
                    <p class="card-description">Advanced 3D menu system with immersive Three.js environment and interactive 3D buttons.</p>
                    <ul class="card-features">
                        <li>Three.js 3D environment</li>
                        <li>Advanced 3D button system</li>
                        <li>Particle effects and animations</li>
                        <li>Responsive design</li>
                        <li>Keyboard navigation</li>
                    </ul>
                    <button class="card-button">Launch 3D Framework</button>
                </div>

                <div class="menu-card" onclick="window.location.href='admin_menu_3d_enhanced.php'">
                    <div class="card-icon">⚡</div>
                    <h3 class="card-title">Enhanced Admin Menu</h3>
                    <p class="card-description">Complete admin interface with 3D buttons, system statistics, and modern design.</p>
                    <ul class="card-features">
                        <li>3D button interactions</li>
                        <li>Real-time system stats</li>
                        <li>Module navigation</li>
                        <li>Database integration</li>
                        <li>Mobile responsive</li>
                    </ul>
                    <button class="card-button">Launch Admin Menu</button>
                </div>

                <div class="menu-card" onclick="window.location.href='3d_console_menu.php'">
                    <div class="card-icon">🖥️</div>
                    <h3 class="card-title">3D Console Menu</h3>
                    <p class="card-description">Professional console-style interface with beautiful textured buttons and clean design.</p>
                    <ul class="card-features">
                        <li>Console-style interface</li>
                        <li>Beautiful textured buttons</li>
                        <li>Static 3D background</li>
                        <li>Real-time status panels</li>
                        <li>Professional appearance</li>
                    </ul>
                    <button class="card-button">Launch Console Menu</button>
                </div>

                <div class="menu-card" onclick="window.location.href='webgl_database_integration_clean.php'">
                    <div class="card-icon">🗄️</div>
                    <h3 class="card-title">WebGL Database Integration</h3>
                    <p class="card-description">Direct database integration with WebGL using clean API endpoints for real-time 3D visualization.</p>
                    <ul class="card-features">
                        <li>Clean API integration</li>
                        <li>Direct database access</li>
                        <li>Real-time synchronization</li>
                        <li>No header conflicts</li>
                        <li>Enhanced 3D visualization</li>
                    </ul>
                    <button class="card-button">Launch Database Integration</button>
                </div>

                <div class="menu-card" onclick="window.location.href='webgl_database_integration_enhanced.php'">
                    <div class="card-icon">🚀</div>
                    <h3 class="card-title">Enhanced WebGL Database</h3>
                    <p class="card-description">Advanced SQLite WASM integration with full client-side database operations and real-time sync.</p>
                    <ul class="card-features">
                        <li>Full SQLite WASM integration</li>
                        <li>Client-side database operations</li>
                        <li>Real-time bidirectional sync</li>
                        <li>Performance monitoring</li>
                        <li>Advanced 3D visualization</li>
                    </ul>
                    <button class="card-button">Launch Enhanced Integration</button>
                </div>

                <div class="menu-card" onclick="window.location.href='webgl_database_demo.php'">
                    <div class="card-icon">🎮</div>
                    <h3 class="card-title">WebGL Database Demo</h3>
                    <p class="card-description">Interactive demonstration of WebGL database integration with real-time 3D network visualization.</p>
                    <ul class="card-features">
                        <li>Interactive 3D visualization</li>
                        <li>Real-time API testing</li>
                        <li>Device simulation</li>
                        <li>Performance metrics</li>
                        <li>Data export functionality</li>
                    </ul>
                    <button class="card-button">Launch Demo</button>
                </div>

                <div class="menu-card" onclick="window.location.href='rotating_3d_menu.php'">
                    <div class="card-icon">🔄</div>
                    <h3 class="card-title">Rotating 3D Menu</h3>
                    <p class="card-description">Dynamic rotating 3D menu with interactive menu items and smooth animations.</p>
                    <ul class="card-features">
                        <li>Rotating menu items</li>
                        <li>Interactive 3D objects</li>
                        <li>Particle effects</li>
                        <li>Real-time controls</li>
                        <li>Multiple styles</li>
                    </ul>
                    <button class="card-button">Launch Rotating Menu</button>
                </div>

                <div class="menu-card" onclick="window.location.href='rotating_3d_menu_advanced.php'">
                    <div class="card-icon">⭐</div>
                    <h3 class="card-title">Advanced Rotating Menu</h3>
                    <p class="card-description">Premium rotating 3D menu with glow effects, multiple styles, and advanced controls.</p>
                    <ul class="card-features">
                        <li>Glow effects</li>
                        <li>5 different styles</li>
                        <li>Advanced particle system</li>
                        <li>FPS counter</li>
                        <li>Real-time adjustments</li>
                    </ul>
                    <button class="card-button">Launch Advanced Menu</button>
                </div>
            </div>
        </section>

        <!-- Core Systems -->
        <section class="menu-section">
            <h2 class="section-title">🔧 Core Systems</h2>
            <div class="menu-grid">
                <div class="menu-card" onclick="window.location.href='admin_menu_enhanced.php'">
                    <div class="card-icon">🛠️</div>
                    <h3 class="card-title">Classic Admin Menu</h3>
                    <p class="card-description">Traditional admin interface with enhanced styling and improved navigation.</p>
                    <ul class="card-features">
                        <li>Enhanced styling</li>
                        <li>Module management</li>
                        <li>System overview</li>
                        <li>Quick access</li>
                    </ul>
                    <button class="card-button">Launch Classic Menu</button>
                </div>

                <div class="menu-card" onclick="window.location.href='webgl_demo.php'">
                    <div class="card-icon">🌐</div>
                    <h3 class="card-title">WebGL 3D Console</h3>
                    <p class="card-description">Interactive 3D network visualization with real-time data and advanced controls.</p>
                    <ul class="card-features">
                        <li>3D network visualization</li>
                        <li>Real-time data updates</li>
                        <li>Interactive controls</li>
                        <li>Research algorithms</li>
                    </ul>
                    <button class="card-button">Launch 3D Console</button>
                </div>

                <div class="menu-card" onclick="window.location.href='modules/'">
                    <div class="card-icon">📁</div>
                    <h3 class="card-title">Module Directory</h3>
                    <p class="card-description">Access all 137+ modules for comprehensive network management and monitoring.</p>
                    <ul class="card-features">
                        <li>137+ modules available</li>
                        <li>Device management</li>
                        <li>Network monitoring</li>
                        <li>Client management</li>
                    </ul>
                    <button class="card-button">Browse Modules</button>
            </div>

                <div class="menu-card" onclick="window.location.href='integrity_check.php'">
                    <div class="card-icon">🔍</div>
                    <h3 class="card-title">System Integrity Check</h3>
                    <p class="card-description">Comprehensive system diagnostics and health monitoring.</p>
                    <ul class="card-features">
                        <li>Database integrity</li>
                        <li>File system check</li>
                        <li>WebGL validation</li>
                        <li>Performance metrics</li>
                    </ul>
                    <button class="card-button">Run Integrity Check</button>
            </div>
            </div>
        </section>

        <!-- Quick Access -->
        <section class="menu-section">
            <h2 class="section-title">⚡ Quick Access</h2>
            <div class="menu-grid">
                <div class="menu-card" onclick="window.location.href='modules/devices.php'">
                    <div class="card-icon">🖥️</div>
                    <h3 class="card-title">Device Management</h3>
                    <p class="card-description">Manage network devices, monitor status, and configure settings.</p>
                    <button class="card-button">Manage Devices</button>
                </div>

                <div class="menu-card" onclick="window.location.href='modules/networks.php'">
                    <div class="card-icon">🌐</div>
                    <h3 class="card-title">Network Management</h3>
                    <p class="card-description">Configure networks, monitor traffic, and manage connections.</p>
                    <button class="card-button">Manage Networks</button>
        </div>
        
                <div class="menu-card" onclick="window.location.href='modules/clients.php'">
                    <div class="card-icon">👥</div>
                    <h3 class="card-title">Client Management</h3>
                    <p class="card-description">Manage client accounts, permissions, and access control.</p>
                    <button class="card-button">Manage Clients</button>
            </div>

                <div class="menu-card" onclick="window.location.href='modules/network_monitor.php'">
                    <div class="card-icon">📊</div>
                    <h3 class="card-title">Network Monitoring</h3>
                    <p class="card-description">Real-time network monitoring and performance analytics.</p>
                    <button class="card-button">Monitor Network</button>
            </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="footer-text">SLMS v1.2.0 - Advanced Network Management System</p>
            <div class="footer-links">
                <a href="3D_MENU_IMPLEMENTATION_GUIDE.md" class="footer-link">3D Menu Guide</a>
                <a href="TEST_AND_DEBUG_REPORT.md" class="footer-link">System Report</a>
                <a href="modules/help.php" class="footer-link">Help</a>
                <a href="modules/settings.php" class="footer-link">Settings</a>
            </div>
        </div>
    </footer>

    <script>
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', (event) => {
            switch(event.key) {
                case '1':
                    window.location.href = '3d_menu_framework.php';
                    break;
                case '2':
                    window.location.href = 'admin_menu_3d_enhanced.php';
                    break;
                case '3':
                    window.location.href = 'webgl_demo.php';
                    break;
                case '4':
                    window.location.href = 'modules/';
                    break;
                case '5':
                    window.location.href = 'integrity_check.php';
                    break;
            }
        });

        // Add loading animation
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html> 