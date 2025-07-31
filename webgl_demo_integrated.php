<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLMS v2.0 - WebGL Integrated Network Control Console</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SLMS WebGL Interface -->
    <script src="webgl_interface.js"></script>
    
    <style>
        :root {
            --console-bg: #0a0a0a;
            --panel-bg: #1a1a1a;
            --accent-blue: #00d4ff;
            --accent-green: #00ff88;
            --accent-orange: #ff6b35;
            --accent-purple: #8b5cf6;
            --accent-red: #ff4757;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --border-glow: #00d4ff;
            --shadow-deep: rgba(0, 212, 255, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--console-bg);
            color: var(--text-primary);
            font-family: 'Courier New', monospace;
            overflow: hidden;
            height: 100vh;
            perspective: 1000px;
        }

        /* Futuristic Console Layout */
        .console-container {
            display: grid;
            grid-template-areas: 
                "header header header"
                "sidebar main controls"
                "footer footer footer";
            grid-template-rows: 80px 1fr 60px;
            grid-template-columns: 350px 1fr 400px;
            height: 100vh;
            gap: 2px;
            background: linear-gradient(45deg, #000, #1a1a1a);
        }

        /* Header Panel */
        .console-header {
            grid-area: header;
            background: linear-gradient(90deg, var(--panel-bg), #2a2a2a);
            border-bottom: 2px solid var(--accent-blue);
            box-shadow: 0 4px 20px var(--shadow-deep);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: relative;
            overflow: hidden;
        }

        .console-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-blue), transparent);
            animation: scan-line 3s linear infinite;
        }

        @keyframes scan-line {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--accent-blue);
            text-shadow: 0 0 10px var(--accent-blue);
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent-green);
            box-shadow: 0 0 8px var(--accent-green);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Sidebar Menu */
        .console-sidebar {
            grid-area: sidebar;
            background: var(--panel-bg);
            border-right: 2px solid var(--accent-blue);
            box-shadow: 4px 0 20px var(--shadow-deep);
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .menu-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .menu-title {
            font-size: 28px;
            font-weight: bold;
            color: var(--accent-blue);
            text-shadow: 0 0 10px var(--accent-blue);
            margin-bottom: 5px;
        }

        .menu-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            text-shadow: 0 0 5px var(--text-secondary);
        }

        .primary-navigation {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .menu-section {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border: 1px solid var(--accent-blue);
            border-radius: 8px;
            padding: 15px;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.6),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .menu-section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent-blue);
            margin-bottom: 10px;
            text-align: center;
            text-shadow: 0 0 8px var(--accent-blue);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 8px 12px;
            margin-bottom: 5px;
            background: linear-gradient(145deg, #1a1a1a, #0a0a0a);
            border: 1px solid var(--accent-purple);
            border-radius: 4px;
            color: var(--text-primary);
            text-decoration: none;
            font-size: 11px;
            text-align: left;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .menu-item:hover {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border-color: var(--accent-green);
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                0 0 10px rgba(0, 255, 136, 0.3);
            transform: translateY(-1px);
        }

        .menu-item:hover::before {
            left: 100%;
        }

        .menu-item.active {
            background: linear-gradient(145deg, #3a3a3a, #2a2a2a);
            border-color: var(--accent-green);
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.3),
                0 0 15px rgba(0, 255, 136, 0.5);
        }

        /* Main Viewport */
        .console-main {
            grid-area: main;
            background: #000;
            position: relative;
            overflow: hidden;
        }

        #webgl-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        /* Viewport Border */
        .console-main::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 2px solid var(--accent-blue);
            border-radius: 8px;
            pointer-events: none;
            box-shadow: 
                inset 0 0 20px var(--shadow-deep),
                0 0 20px var(--shadow-deep);
            z-index: 1;
        }

        /* Controls Panel */
        .console-controls {
            grid-area: controls;
            background: var(--panel-bg);
            border-left: 2px solid var(--accent-blue);
            box-shadow: -4px 0 20px var(--shadow-deep);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
        }

        /* Control Panels */
        .control-panel {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border: 2px solid var(--accent-purple);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.1),
                0 0 20px rgba(139, 92, 246, 0.3);
        }

        .control-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent-purple);
            margin-bottom: 15px;
            text-align: center;
            text-shadow: 0 0 8px var(--accent-purple);
        }

        /* Statistics Display */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .stat-item {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border: 1px solid var(--accent-blue);
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.6),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-green);
            text-shadow: 0 0 8px var(--accent-green);
        }

        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            margin-top: 5px;
        }

        /* 3D Buttons */
        .btn-3d {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border: 2px solid var(--accent-blue);
            border-radius: 8px;
            padding: 12px 20px;
            color: var(--text-primary);
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            transform: perspective(500px) rotateX(15deg);
        }

        .btn-3d::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 212, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-3d:hover {
            transform: perspective(500px) rotateX(0deg) translateY(-2px);
            box-shadow: 
                0 6px 12px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                0 0 15px var(--shadow-deep);
            border-color: var(--accent-green);
        }

        .btn-3d:hover::before {
            left: 100%;
        }

        .btn-3d:active {
            transform: perspective(500px) rotateX(15deg) translateY(1px);
            box-shadow: 
                0 2px 4px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        /* Footer */
        .console-footer {
            grid-area: footer;
            background: linear-gradient(90deg, var(--panel-bg), #2a2a2a);
            border-top: 2px solid var(--accent-blue);
            box-shadow: 0 -4px 20px var(--shadow-deep);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        /* Loading Screen */
        #loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--console-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .loading-content {
            text-align: center;
            color: var(--accent-blue);
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid var(--accent-blue);
            border-top: 3px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Data Tables Styling */
        .data-container {
            background: var(--panel-bg);
            border: 1px solid var(--accent-blue);
            border-radius: 8px;
            margin: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px var(--shadow-deep);
        }

        .data-header {
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .data-header h3 {
            margin: 0;
            color: var(--text-primary);
            font-size: 18px;
            font-weight: bold;
        }

        .table-responsive {
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--panel-bg);
        }

        .data-table th {
            background: var(--console-bg);
            color: var(--accent-blue);
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid var(--accent-blue);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #333;
            color: var(--text-secondary);
        }

        .data-table tr:hover {
            background: rgba(0, 212, 255, 0.1);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-badge.active {
            background: var(--accent-green);
            color: #000;
        }

        .status-badge.inactive {
            background: var(--accent-red);
            color: #fff;
        }

        .status-badge.suspended {
            background: var(--accent-orange);
            color: #fff;
        }

        .status-badge.online {
            background: var(--accent-green);
            color: #000;
        }

        .status-badge.offline {
            background: var(--accent-red);
            color: #fff;
        }

        .status-badge.maintenance {
            background: var(--accent-orange);
            color: #fff;
        }

        .type-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .type-badge.router {
            background: var(--accent-blue);
            color: #fff;
        }

        .type-badge.switch {
            background: var(--accent-purple);
            color: #fff;
        }

        .type-badge.server {
            background: var(--accent-green);
            color: #000;
        }

        .type-badge.access_point {
            background: var(--accent-orange);
            color: #fff;
        }

        .type-badge.firewall {
            background: var(--accent-red);
            color: #fff;
        }

        .type-badge.other {
            background: #666;
            color: #fff;
        }

        .btn-small {
            background: var(--accent-blue);
            color: var(--text-primary);
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 2px;
            font-size: 12px;
        }

        .btn-small:hover {
            background: var(--accent-purple);
            transform: translateY(-1px);
        }

        .data-footer {
            background: var(--console-bg);
            padding: 10px 20px;
            border-top: 1px solid #333;
            color: var(--text-secondary);
            font-size: 12px;
        }

        .no-data {
            text-align: center;
            padding: 50px;
            color: var(--text-secondary);
            font-size: 16px;
        }

        /* Universal Search Styling */
        .search-container {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.9);
            border: 2px solid var(--accent-blue);
            border-radius: 8px;
            padding: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.8);
        }

        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-box input[type="text"] {
            flex: 1;
            background: var(--panel-bg);
            border: 1px solid var(--accent-blue);
            border-radius: 6px;
            padding: 10px 15px;
            color: var(--text-primary);
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-box input[type="text"]:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 15px rgba(139, 92, 246, 0.5);
        }

        .search-box select {
            background: var(--panel-bg);
            border: 1px solid var(--accent-blue);
            border-radius: 6px;
            padding: 10px;
            color: var(--text-primary);
            font-size: 14px;
            outline: none;
            min-width: 120px;
        }

        .search-box button {
            background: var(--accent-blue);
            color: var(--text-primary);
            border: none;
            border-radius: 6px;
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-box button:hover {
            background: var(--accent-purple);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        }

        .search-results {
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
            background: var(--panel-bg);
            border: 1px solid var(--accent-blue);
            border-radius: 6px;
            display: none;
        }

        .search-results.active {
            display: block;
        }

        .search-result-item {
            padding: 10px 15px;
            border-bottom: 1px solid #333;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-result-item:hover {
            background: rgba(0, 212, 255, 0.1);
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-title {
            font-weight: bold;
            color: var(--accent-blue);
            margin-bottom: 5px;
        }

        .search-result-details {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .search-result-module {
            display: inline-block;
            background: var(--accent-purple);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            margin-left: 10px;
        }

        .search-highlight {
            background: rgba(0, 255, 136, 0.2) !important;
            border-left: 4px solid var(--accent-green) !important;
            animation: searchPulse 2s ease-in-out;
        }

        @keyframes searchPulse {
            0% { background: rgba(0, 255, 136, 0.2); }
            50% { background: rgba(0, 255, 136, 0.4); }
            100% { background: rgba(0, 255, 136, 0.2); }
        }

        /* Enhanced Menu Styles */
        .menu-header {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border-bottom: 2px solid var(--accent-blue);
            padding: 20px;
            text-align: center;
        }

        .menu-title {
            font-size: 18px;
            font-weight: bold;
            color: var(--accent-blue);
            margin-bottom: 5px;
        }

        .menu-subtitle {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .primary-navigation {
            padding: 20px;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }

        .menu-section {
            margin-bottom: 25px;
        }

        .menu-section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent-purple);
            margin-bottom: 10px;
            padding: 8px 12px;
            background: rgba(139, 92, 246, 0.1);
            border-left: 3px solid var(--accent-purple);
            border-radius: 4px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            margin-bottom: 5px;
            position: relative;
            overflow: hidden;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 212, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .menu-item:hover {
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border-left: 3px solid var(--accent-green);
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.2);
        }

        .menu-item:hover::before {
            left: 100%;
        }

        .menu-item.active {
            background: linear-gradient(145deg, #1a1a1a, #0a0a0a);
            border-left: 3px solid var(--accent-blue);
            color: var(--accent-blue);
        }

        .menu-item i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .utility-navigation {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(145deg, #1a1a1a, #0a0a0a);
            border-top: 1px solid var(--accent-blue);
            padding: 15px 20px;
            display: flex;
            justify-content: space-around;
        }

        .utility-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 11px;
            transition: all 0.3s ease;
        }

        .utility-item:hover {
            color: var(--accent-blue);
            transform: translateY(-2px);
        }

        .utility-item i {
            font-size: 18px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .console-container {
                grid-template-columns: 300px 1fr 350px;
            }
        }

        @media (max-width: 768px) {
            .console-container {
                grid-template-areas: 
                    "header"
                    "main"
                    "sidebar"
                    "controls"
                    "footer";
                grid-template-rows: 60px 1fr 200px 300px 40px;
                grid-template-columns: 1fr;
            }
            
            .data-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .table-responsive {
                max-height: 400px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <h3>Initializing SLMS WebGL Console...</h3>
            <p>Loading network modules and 3D visualization</p>
        </div>
    </div>

    <!-- Main Console Container -->
    <div class="console-container">
        <!-- Header -->
        <header class="console-header">
            <div class="header-title">SLMS v2.0 - WebGL Console</div>
            <div class="header-status">
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span>SYSTEM ONLINE</span>
                </div>
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span>WEBGL ACTIVE</span>
                </div>
            </div>
        </header>

        <!-- Sidebar Menu -->
        <aside class="console-sidebar">
            <!-- Enhanced Menu with Research-Based Design -->
            <div class="menu-header">
                <div class="menu-title">🔧 SLMS Console</div>
                <div class="menu-subtitle">Network Management System</div>
            </div>

            <!-- Primary Navigation Menu -->
            <nav class="primary-navigation">
                <!-- Dashboard & Overview -->
                <div class="menu-section">
                    <div class="menu-section-title">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard & Overview
                    </div>
                    <a href="#" class="menu-item" data-module="dashboard">
                        <i class="bi bi-graph-up"></i>
                        Network Dashboard
                    </a>
                    <a href="#" class="menu-item" data-module="analytics">
                        <i class="bi bi-bar-chart"></i>
                        Analytics & Reports
                    </a>
                    <a href="#" class="menu-item" data-module="monitoring">
                        <i class="bi bi-eye"></i>
                        Real-time Monitoring
                    </a>
                    <a href="#" class="menu-item" data-module="alerts">
                        <i class="bi bi-exclamation-triangle"></i>
                        System Alerts
                    </a>
                </div>

                <!-- Client Management -->
                <div class="menu-section">
                    <div class="menu-section-title">
                        <i class="bi bi-people"></i>
                        Client Management
                    </div>
                    <a href="#" class="menu-item" data-module="clients">
                        <i class="bi bi-person"></i>
                        Client Directory
                    </a>
                    <a href="#" class="menu-item" data-module="add_client">
                        <i class="bi bi-person-plus"></i>
                        Add New Client
                    </a>
                    <a href="#" class="menu-item" data-module="client_services">
                        <i class="bi bi-gear"></i>
                        Client Services
                    </a>
                    <a href="#" class="menu-item" data-module="billing">
                        <i class="bi bi-credit-card"></i>
                        Billing & Invoices
                    </a>
                    <a href="#" class="menu-item" data-module="support">
                        <i class="bi bi-headset"></i>
                        Support Tickets
                    </a>
                </div>

                <!-- Device Management -->
                <div class="menu-section">
                    <div class="menu-section-title">
                        <i class="bi bi-hdd-network"></i>
                        Device Management
                    </div>
                    <a href="#" class="menu-item" data-module="devices">
                        <i class="bi bi-hdd"></i>
                        All Devices
                    </a>
                    <a href="#" class="menu-item" data-module="client_devices">
                        <i class="bi bi-laptop"></i>
                        Client Devices
                    </a>
                    <a href="#" class="menu-item" data-module="core_devices">
                        <i class="bi bi-server"></i>
                        Core Devices
                    </a>
                    <a href="#" class="menu-item" data-module="device_categories">
                        <i class="bi bi-tags"></i>
                        Device Categories
                    </a>
                    <a href="#" class="menu-item" data-module="add_device">
                        <i class="bi bi-plus-circle"></i>
                        Add New Device
                    </a>
                </div>

                <!-- Network Infrastructure -->
                <div class="menu-section">
                    <div class="menu-section-title">
                        <i class="bi bi-diagram-3"></i>
                        Network Infrastructure
                    </div>
                    <a href="#" class="menu-item" data-module="networks">
                        <i class="bi bi-wifi"></i>
                        Network Overview
                    </a>
                    <a href="#" class="menu-item" data-module="network_segments">
                        <i class="bi bi-grid-3x3"></i>
                        Network Segments
                    </a>
                    <a href="#" class="menu-item" data-module="vlans">
                        <i class="bi bi-layers"></i>
                        VLAN Management
                    </a>
                    <a href="#" class="menu-item" data-module="ip_ranges">
                        <i class="bi bi-123"></i>
                        IP Range Management
                    </a>
                    <a href="#" class="menu-item" data-module="routing">
                        <i class="bi bi-arrow-left-right"></i>
                        Routing Tables
                    </a>
                </div>

                <!-- Integration Tools -->
                <div class="menu-section">
                    <div class="menu-section-title">
                        <i class="bi bi-plug"></i>
                        Integration Tools
                    </div>
                    <a href="#" class="menu-item" data-module="mikrotik">
                        <i class="bi bi-router"></i>
                        MikroTik Integration
                    </a>
                    <a href="#" class="menu-item" data-module="dhcp">
                        <i class="bi bi-hdd-rack"></i>
                        DHCP Management
                    </a>
                    <a href="#" class="menu-item" data-module="snmp">
                        <i class="bi bi-activity"></i>
                        SNMP Monitoring
                    </a>
                    <a href="#" class="menu-item" data-module="cacti">
                        <i class="bi bi-graph-up-arrow"></i>
                        Cacti Integration
                    </a>
                    <a href="#" class="menu-item" data-module="mndp">
                        <i class="bi bi-search"></i>
                        MNDP Discovery
                    </a>
                </div>

                <!-- Scanning & Discovery -->
                <div class="menu-section">
                    <div class="menu-section-title">
                        <i class="bi bi-search"></i>
                        Scanning & Discovery
                    </div>
                    <a href="#" class="menu-item" data-module="scan_jobs">
                        <i class="bi bi-list-task"></i>
                        Scan Jobs
                    </a>
                    <a href="#" class="menu-item" data-module="add_scan_job">
                        <i class="bi bi-plus-square"></i>
                        Add Scan Job
                    </a>
                    <a href="#" class="menu-item" data-module="network_discovery">
                        <i class="bi bi-radar"></i>
                        Network Discovery
                    </a>
                    <a href="#" class="menu-item" data-module="port_scanner">
                        <i class="bi bi-door-open"></i>
                        Port Scanner
                    </a>
                    <a href="#" class="menu-item" data-module="vulnerability_scan">
                        <i class="bi bi-shield-exclamation"></i>
                        Vulnerability Scan
                    </a>
                </div>

                <!-- System Administration -->
                <div class="menu-section">
                    <div class="menu-section-title">
                        <i class="bi bi-gear"></i>
                        System Administration
                    </div>
                    <a href="#" class="menu-item" data-module="users">
                        <i class="bi bi-person-badge"></i>
                        User Management
                    </a>
                    <a href="#" class="menu-item" data-module="access_control">
                        <i class="bi bi-lock"></i>
                        Access Control
                    </a>
                    <a href="#" class="menu-item" data-module="activity_logs">
                        <i class="bi bi-journal-text"></i>
                        Activity Logs
                    </a>
                    <a href="#" class="menu-item" data-module="system_config">
                        <i class="bi bi-sliders"></i>
                        System Configuration
                    </a>
                    <a href="#" class="menu-item" data-module="backup_restore">
                        <i class="bi bi-cloud-arrow-up"></i>
                        Backup & Restore
                    </a>
                </div>

                <!-- Development Tools -->
                <div class="menu-section">
                    <div class="menu-section-title">
                        <i class="bi bi-code-slash"></i>
                        Development Tools
                    </div>
                    <a href="#" class="menu-item" data-module="sql_console">
                        <i class="bi bi-terminal"></i>
                        SQL Console
                    </a>
                    <a href="#" class="menu-item" data-module="debug_tools">
                        <i class="bi bi-bug"></i>
                        Debug Tools
                    </a>
                    <a href="#" class="menu-item" data-module="api_docs">
                        <i class="bi bi-file-text"></i>
                        API Documentation
                    </a>
                    <a href="#" class="menu-item" data-module="test_suite">
                        <i class="bi bi-check-circle"></i>
                        Test Suite
                    </a>
                    <a href="#" class="menu-item" data-module="webgl_scanner">
                        <i class="bi bi-search-heart"></i>
                        WebGL Function Scanner
                    </a>
                </div>

                <!-- Data Management -->
                <div class="menu-section">
                    <div class="menu-section-title">
                        <i class="bi bi-database"></i>
                        Data Management
                    </div>
                    <a href="#" class="menu-item" data-module="data_import">
                        <i class="bi bi-upload"></i>
                        Data Import
                    </a>
                    <a href="#" class="menu-item" data-module="data_export">
                        <i class="bi bi-download"></i>
                        Data Export
                    </a>
                    <a href="#" class="menu-item" data-module="data_backup">
                        <i class="bi bi-archive"></i>
                        Data Backup
                    </a>
                    <a href="#" class="menu-item" data-module="data_cleanup">
                        <i class="bi bi-trash"></i>
                        Data Cleanup
                    </a>
                </div>
            </nav>

            <!-- Utility Navigation -->
            <div class="utility-navigation">
                <a href="#" class="utility-item" onclick="showHelp()">
                    <i class="bi bi-question-circle"></i>
                    Help
                </a>
                <a href="#" class="utility-item" onclick="showSettings()">
                    <i class="bi bi-gear"></i>
                    Settings
                </a>
                <a href="#" class="utility-item" onclick="showAbout()">
                    <i class="bi bi-info-circle"></i>
                    About
                </a>
            </div>
        </aside>

        <!-- Main Viewport -->
        <main class="console-main">
            <!-- Universal Search Bar -->
            <div class="search-container">
                <div class="search-box">
                    <input type="text" id="universal-search" placeholder="🔍 Search across all modules..." />
                    <select id="search-module">
                        <option value="all">All Modules</option>
                        <option value="clients">Clients</option>
                        <option value="devices">Devices</option>
                        <option value="client_devices">Client Devices</option>
                        <option value="core_devices">Core Devices</option>
                        <option value="mikrotik">Mikrotik</option>
                        <option value="networks">Networks</option>
                        <option value="vlans">VLANs</option>
                        <option value="ip_ranges">IP Ranges</option>
                        <option value="network_segments">Network Segments</option>
                        <option value="dhcp">DHCP</option>
                        <option value="snmp">SNMP</option>
                        <option value="scan_jobs">Scan Jobs</option>
                        <option value="device_categories">Device Categories</option>
                        <option value="users">Users</option>
                        <option value="services">Services</option>
                        <option value="alerts">Alerts</option>
                    </select>
                    <button id="search-btn" onclick="performSearch()">Search</button>
                    <button id="clear-search-btn" onclick="clearSearch()">Clear</button>
                </div>
                <div id="search-results" class="search-results"></div>
            </div>
            
            <div id="webgl-container">
                <canvas id="webgl-canvas" width="800" height="600"></canvas>
            </div>
        </main>

        <!-- Controls Panel -->
        <aside class="console-controls">
            <!-- System Statistics -->
            <div class="control-panel">
                <div class="control-title">System Statistics</div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value" id="client-count">0</div>
                        <div class="stat-label">Active Clients</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="device-count">0</div>
                        <div class="stat-label">Monitored Devices</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="network-count">0</div>
                        <div class="stat-label">Active Networks</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="alert-count">0</div>
                        <div class="stat-label">Active Alerts</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="control-panel">
                <div class="control-title">Quick Actions</div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button class="btn-3d" onclick="addNewClient()">Add New Client</button>
                    <button class="btn-3d" onclick="addNewDevice()">Add New Device</button>
                    <button class="btn-3d" onclick="addNewCoreDevice()">Add Core Device</button>
                    <button class="btn-3d" onclick="addNewScanJob()">Add Scan Job</button>
                    <button class="btn-3d" onclick="searchNetworks()">Search Networks</button>
                    <button class="btn-3d" onclick="generateReport()">Generate Report</button>
                    <button class="btn-3d" onclick="refreshData()">Refresh Data</button>
                </div>
            </div>

            <!-- Module Controls -->
            <div class="control-panel">
                <div class="control-title">Module Controls</div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button class="btn-3d" onclick="toggleWebGL()">Toggle WebGL View</button>
                    <button class="btn-3d" onclick="resetView()">Reset View</button>
                    <button class="btn-3d" onclick="exportData()">Export Data</button>
                    <button class="btn-3d" onclick="systemStatus()">System Status</button>
                </div>
            </div>

            <!-- Information Panel -->
            <div class="control-panel">
                <div class="control-title">Information</div>
                <div id="info-panel" style="font-size: 11px; line-height: 1.4;">
                    <p><strong>Status:</strong> <span id="current-status">Initializing...</span></p>
                    <p><strong>Module:</strong> <span id="current-module">None</span></p>
                    <p><strong>Last Update:</strong> <span id="last-update">Never</span></p>
                    <p><strong>WebGL Version:</strong> <span id="webgl-version">Unknown</span></p>
                </div>
            </div>
        </aside>

        <!-- Footer -->
        <footer class="console-footer">
            <div>SLMS v2.0 - WebGL Integrated Console</div>
            <div id="footer-status">System Ready</div>
            <div id="current-time"></div>
        </footer>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    
    <script>
        // Initialize the application
        // Global variables for WebGL
        let webglScene, webglRenderer, webglCamera, animationId;
        let stats = {
            clients: 0,
            devices: 0,
            networks: 0,
            alerts: 0
        };

        // Initialize the SLMS WebGL Interface
        let slmsInterface;

        // Enhanced Menu Functionality
        function initializeEnhancedMenu() {
            // Add event listeners for all menu items
            document.querySelectorAll('.menu-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const moduleName = this.getAttribute('data-module');
                    handleMenuClick(moduleName);
                });
            });

            // Add event listeners for utility items
            document.querySelectorAll('.utility-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const action = this.getAttribute('onclick');
                    if (action) {
                        eval(action.replace('onclick="', '').replace('"', ''));
                    }
                });
            });
        }

        // Handle menu item clicks with enhanced functionality
        function handleMenuClick(moduleName) {
            console.log(`Loading module: ${moduleName}`);
            
            // Update active menu item
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`[data-module="${moduleName}"]`)?.classList.add('active');

            // Update current module display
            document.getElementById('current-module').textContent = moduleName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            document.getElementById('current-status').textContent = 'Loading module...';

            // Load module using SLMS interface if available
            if (slmsInterface && typeof slmsInterface.loadModule === 'function') {
                slmsInterface.loadModule(moduleName);
            } else {
                // Fallback to basic module loading
                loadModule(moduleName);
            }

            // Update breadcrumbs
            updateBreadcrumbs(moduleName);
        }

        // Update breadcrumbs navigation
        function updateBreadcrumbs(moduleName) {
            const breadcrumbContainer = document.getElementById('breadcrumb-container');
            if (!breadcrumbContainer) return;

            const breadcrumbs = ['Home'];
            
            // Add module-specific breadcrumbs
            switch(moduleName) {
                case 'clients':
                case 'add_client':
                case 'client_services':
                case 'billing':
                case 'support':
                    breadcrumbs.push('Client Management');
                    break;
                case 'devices':
                case 'client_devices':
                case 'core_devices':
                case 'device_categories':
                case 'add_device':
                    breadcrumbs.push('Device Management');
                    break;
                case 'networks':
                case 'network_segments':
                case 'vlans':
                case 'ip_ranges':
                case 'routing':
                    breadcrumbs.push('Network Infrastructure');
                    break;
                case 'mikrotik':
                case 'dhcp':
                case 'snmp':
                case 'cacti':
                case 'mndp':
                    breadcrumbs.push('Integration Tools');
                    break;
                case 'scan_jobs':
                case 'add_scan_job':
                case 'network_discovery':
                case 'port_scanner':
                case 'vulnerability_scan':
                    breadcrumbs.push('Scanning & Discovery');
                    break;
                case 'users':
                case 'access_control':
                case 'activity_logs':
                case 'system_config':
                case 'backup_restore':
                    breadcrumbs.push('System Administration');
                    break;
                case 'sql_console':
                case 'debug_tools':
                case 'api_docs':
                case 'test_suite':
                case 'webgl_scanner':
                    breadcrumbs.push('Development Tools');
                    break;
                case 'data_import':
                case 'data_export':
                case 'data_backup':
                case 'data_cleanup':
                    breadcrumbs.push('Data Management');
                    break;
            }
            
            breadcrumbs.push(moduleName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));

            breadcrumbContainer.innerHTML = breadcrumbs.map((crumb, index) => 
                `<span class="breadcrumb-item">${crumb}</span>${index < breadcrumbs.length - 1 ? ' > ' : ''}`
            ).join('');
        }

        // Initialize WebGL
        function initializeWebGL() {
            const canvas = document.getElementById('webgl-canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            
            if (!gl) {
                console.error('WebGL not supported');
                document.getElementById('webgl-version').textContent = 'Not Supported';
                return;
            }

            // Get WebGL version
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            const renderer = debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : 'Unknown';
            document.getElementById('webgl-version').textContent = renderer;

            // Initialize Three.js
            webglScene = new THREE.Scene();
            webglScene.background = new THREE.Color(0x000000);

            webglCamera = new THREE.PerspectiveCamera(75, canvas.width / canvas.height, 0.1, 1000);
            webglCamera.position.z = 5;

            webglRenderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true });
            webglRenderer.setSize(canvas.width, canvas.height);
            webglRenderer.setPixelRatio(window.devicePixelRatio);

            // Add ambient light
            const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
            webglScene.add(ambientLight);

            // Add directional light
            const directionalLight = new THREE.DirectionalLight(0x00d4ff, 1);
            directionalLight.position.set(5, 5, 5);
            webglScene.add(directionalLight);

            // Create initial network visualization
            createNetworkVisualization();

            // Start animation loop
            animate();
        }

        // Create network visualization
        function createNetworkVisualization() {
            // Clear existing objects
            while(webglScene.children.length > 0) { 
                webglScene.remove(webglScene.children[0]); 
            }

            // Add lights back
            const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
            webglScene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0x00d4ff, 1);
            directionalLight.position.set(5, 5, 5);
            webglScene.add(directionalLight);

            // Create network nodes (representing devices)
            const geometry = new THREE.SphereGeometry(0.1, 32, 32);
            const material = new THREE.MeshPhongMaterial({ color: 0x00d4ff });

            for (let i = 0; i < 10; i++) {
                const sphere = new THREE.Mesh(geometry, material);
                sphere.position.x = (Math.random() - 0.5) * 10;
                sphere.position.y = (Math.random() - 0.5) * 10;
                sphere.position.z = (Math.random() - 0.5) * 10;
                webglScene.add(sphere);
            }

            // Create connection lines
            const lineMaterial = new THREE.LineBasicMaterial({ color: 0x00ff88 });
            for (let i = 0; i < 5; i++) {
                const points = [];
                points.push(new THREE.Vector3(
                    (Math.random() - 0.5) * 10,
                    (Math.random() - 0.5) * 10,
                    (Math.random() - 0.5) * 10
                ));
                points.push(new THREE.Vector3(
                    (Math.random() - 0.5) * 10,
                    (Math.random() - 0.5) * 10,
                    (Math.random() - 0.5) * 10
                ));

                const lineGeometry = new THREE.BufferGeometry().setFromPoints(points);
                const line = new THREE.Line(lineGeometry, lineMaterial);
                webglScene.add(line);
            }
        }

        // Animation loop
        function animate() {
            animationId = requestAnimationFrame(animate);
            
            // Rotate the scene
            webglScene.rotation.y += 0.005;
            
            webglRenderer.render(webglScene, webglCamera);
        }

        // Initialize event listeners
        function initializeEventListeners() {
            // Menu item clicks
            document.querySelectorAll('.menu-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const module = this.getAttribute('data-module');
                    loadModule(module);
                });
            });

            // Window resize
            window.addEventListener('resize', function() {
                const canvas = document.getElementById('webgl-canvas');
                const container = document.getElementById('webgl-container');
                canvas.width = container.clientWidth;
                canvas.height = container.clientHeight;
                
                if (webglCamera && webglRenderer) {
                    webglCamera.aspect = canvas.width / canvas.height;
                    webglCamera.updateProjectionMatrix();
                    webglRenderer.setSize(canvas.width, canvas.height);
                }
            });
        }

        // Load module
        function loadModule(moduleName) {
            // Update active menu item
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`[data-module="${moduleName}"]`).classList.add('active');

            // Update current module
            currentModule = moduleName;
            document.getElementById('current-module').textContent = moduleName.charAt(0).toUpperCase() + moduleName.slice(1);
            document.getElementById('current-status').textContent = 'Loading module...';

            // Simulate module loading
            setTimeout(() => {
                document.getElementById('current-status').textContent = 'Module loaded';
                updateLastUpdate();
                
                // Update visualization based on module
                updateVisualizationForModule(moduleName);
            }, 1000);
        }

        // Update visualization for specific module
        function updateVisualizationForModule(moduleName) {
            if (!webglScene) return;

            // Clear existing visualization
            createNetworkVisualization();

            // Add module-specific elements
            switch(moduleName) {
                case 'clients':
                    addClientVisualization();
                    break;
                case 'devices':
                    addDeviceVisualization();
                    break;
                case 'networks':
                    addNetworkVisualization();
                    break;
                case 'invoices':
                    addInvoiceVisualization();
                    break;
                case 'dashboard':
                    addDashboardVisualization();
                    break;
                default:
                    // Default network visualization
                    break;
            }
        }

        // Add client visualization
        function addClientVisualization() {
            const geometry = new THREE.BoxGeometry(0.2, 0.2, 0.2);
            const material = new THREE.MeshPhongMaterial({ color: 0x00ff88 });
            
            for (let i = 0; i < 5; i++) {
                const cube = new THREE.Mesh(geometry, material);
                cube.position.x = (Math.random() - 0.5) * 8;
                cube.position.y = (Math.random() - 0.5) * 8;
                cube.position.z = (Math.random() - 0.5) * 8;
                webglScene.add(cube);
            }
        }

        // Add device visualization
        function addDeviceVisualization() {
            const geometry = new THREE.CylinderGeometry(0.1, 0.1, 0.3, 8);
            const material = new THREE.MeshPhongMaterial({ color: 0xff6b35 });
            
            for (let i = 0; i < 8; i++) {
                const cylinder = new THREE.Mesh(geometry, material);
                cylinder.position.x = (Math.random() - 0.5) * 8;
                cylinder.position.y = (Math.random() - 0.5) * 8;
                cylinder.position.z = (Math.random() - 0.5) * 8;
                webglScene.add(cylinder);
            }
        }

        // Add network visualization
        function addNetworkVisualization() {
            const geometry = new THREE.TorusGeometry(2, 0.1, 16, 100);
            const material = new THREE.MeshPhongMaterial({ color: 0x8b5cf6 });
            const torus = new THREE.Mesh(geometry, material);
            webglScene.add(torus);
        }

        // Add invoice visualization
        function addInvoiceVisualization() {
            const geometry = new THREE.PlaneGeometry(0.3, 0.2);
            const material = new THREE.MeshPhongMaterial({ color: 0xff4757 });
            
            for (let i = 0; i < 6; i++) {
                const plane = new THREE.Mesh(geometry, material);
                plane.position.x = (Math.random() - 0.5) * 8;
                plane.position.y = (Math.random() - 0.5) * 8;
                plane.position.z = (Math.random() - 0.5) * 8;
                plane.rotation.x = Math.random() * Math.PI;
                plane.rotation.y = Math.random() * Math.PI;
                webglScene.add(plane);
            }
        }

        // Add dashboard visualization
        function addDashboardVisualization() {
            // Create a more complex visualization with multiple elements
            const geometries = [
                new THREE.SphereGeometry(0.15, 32, 32),
                new THREE.BoxGeometry(0.2, 0.2, 0.2),
                new THREE.CylinderGeometry(0.1, 0.1, 0.3, 8)
            ];
            
            const materials = [
                new THREE.MeshPhongMaterial({ color: 0x00d4ff }),
                new THREE.MeshPhongMaterial({ color: 0x00ff88 }),
                new THREE.MeshPhongMaterial({ color: 0xff6b35 })
            ];
            
            for (let i = 0; i < 12; i++) {
                const geometry = geometries[i % geometries.length];
                const material = materials[i % materials.length];
                const mesh = new THREE.Mesh(geometry, material);
                mesh.position.x = (Math.random() - 0.5) * 10;
                mesh.position.y = (Math.random() - 0.5) * 10;
                mesh.position.z = (Math.random() - 0.5) * 10;
                webglScene.add(mesh);
            }
        }

        // Update system statistics
        function updateSystemStats() {
            // Simulate real-time data updates
            stats.clients = Math.floor(Math.random() * 100) + 50;
            stats.devices = Math.floor(Math.random() * 200) + 100;
            stats.networks = Math.floor(Math.random() * 20) + 10;
            stats.alerts = Math.floor(Math.random() * 10);

            document.getElementById('client-count').textContent = stats.clients;
            document.getElementById('device-count').textContent = stats.devices;
            document.getElementById('network-count').textContent = stats.networks;
            document.getElementById('alert-count').textContent = stats.alerts;

            // Update every 5 seconds
            setTimeout(updateSystemStats, 5000);
        }

        // Quick action functions
        function addNewClient() {
            if (slmsInterface) {
                slmsInterface.addNewClient();
            } else {
                alert('Interface not initialized');
            }
        }

        function addNewDevice() {
            if (slmsInterface) {
                slmsInterface.addNewDevice();
            } else {
                alert('Interface not initialized');
            }
        }

        function generateReport() {
            if (slmsInterface) {
                slmsInterface.generateReport();
            } else {
                alert('Interface not initialized');
            }
        }

        function refreshData() {
            if (slmsInterface) {
                slmsInterface.refreshData();
            } else {
                updateSystemStats();
                document.getElementById('current-status').textContent = 'Data refreshed';
                updateLastUpdate();
            }
        }

        function toggleWebGL() {
            if (slmsInterface) {
                slmsInterface.toggleWebGL();
            } else {
                const canvas = document.getElementById('webgl-canvas');
                if (canvas.style.display === 'none') {
                    canvas.style.display = 'block';
                    animate();
                } else {
                    canvas.style.display = 'none';
                    if (animationId) {
                        cancelAnimationFrame(animationId);
                    }
                }
            }
        }

        function resetView() {
            if (slmsInterface) {
                slmsInterface.resetView();
            } else {
                if (webglCamera) {
                    webglCamera.position.set(0, 0, 5);
                    webglCamera.lookAt(0, 0, 0);
                }
                if (webglScene) {
                    webglScene.rotation.set(0, 0, 0);
                }
            }
        }

        function exportData() {
            if (slmsInterface) {
                slmsInterface.exportData();
            } else {
                alert('Export Data functionality would be implemented here');
            }
        }

        function systemStatus() {
            if (slmsInterface) {
                slmsInterface.systemStatus();
            } else {
                alert('System Status: All systems operational');
            }
        }

        // Global functions for table actions
        function editClient(clientId) {
            if (slmsInterface) {
                slmsInterface.editClient(clientId);
            } else {
                alert('Interface not initialized');
            }
        }

        function deleteClient(clientId) {
            if (slmsInterface) {
                slmsInterface.deleteClient(clientId);
            } else {
                alert('Interface not initialized');
            }
        }

        function editDevice(deviceId) {
            if (slmsInterface) {
                slmsInterface.editDevice(deviceId);
            } else {
                alert('Interface not initialized');
            }
        }

        function deleteDevice(deviceId) {
            if (slmsInterface) {
                slmsInterface.deleteDevice(deviceId);
            } else {
                alert('Interface not initialized');
            }
        }

        function pingDevice(deviceId) {
            if (slmsInterface) {
                slmsInterface.pingDevice(deviceId);
            } else {
                alert('Interface not initialized');
            }
        }

        // Universal Search Functions
        async function performSearch() {
            const searchTerm = document.getElementById('universal-search').value.trim();
            const searchModule = document.getElementById('search-module').value;
            
            if (!searchTerm) {
                alert('Please enter a search term');
                return;
            }

            if (slmsInterface) {
                await slmsInterface.performUniversalSearch(searchTerm, searchModule);
            } else {
                alert('Interface not initialized');
            }
        }

        function clearSearch() {
            document.getElementById('universal-search').value = '';
            document.getElementById('search-results').innerHTML = '';
            document.getElementById('search-results').classList.remove('active');
            
            // Restore original view
            if (slmsInterface && slmsInterface.currentModule) {
                slmsInterface.displayModuleData(slmsInterface.currentModule);
            }
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+F or Cmd+F for search focus
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('universal-search').focus();
            }
            
            // Enter key in search box
            if (e.key === 'Enter' && document.activeElement.id === 'universal-search') {
                performSearch();
            }
            
            // Escape key to clear search
            if (e.key === 'Escape') {
                clearSearch();
            }
        });

        // Navigate to search result
        function navigateToSearchResult(moduleName, itemId) {
            // Load the module first
            if (slmsInterface) {
                slmsInterface.loadModule(moduleName);
                
                // Highlight the specific item (we'll implement this later)
                setTimeout(() => {
                    highlightSearchResult(moduleName, itemId);
                }, 500);
            }
            
            // Hide search results
            document.getElementById('search-results').classList.remove('active');
        }

        function highlightSearchResult(moduleName, itemId) {
            // Find and highlight the specific row in the table
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            tableRows.forEach(row => {
                row.classList.remove('search-highlight');
                const firstCell = row.querySelector('td');
                if (firstCell && firstCell.textContent.trim() == itemId) {
                    row.classList.add('search-highlight');
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }

        // Utility functions
        function hideLoading() {
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
            }, 2000);
        }

        function startClock() {
            function updateClock() {
                const now = new Date();
                document.getElementById('current-time').textContent = now.toLocaleTimeString();
            }
            updateClock();
            setInterval(updateClock, 1000);
        }

        function updateLastUpdate() {
            const now = new Date();
            document.getElementById('last-update').textContent = now.toLocaleTimeString();
        }

        // Enhanced module loading with specific functionality
        function loadModule(moduleName) {
            // Update active menu item
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`[data-module="${moduleName}"]`)?.classList.add('active');

            // Update current module
            currentModule = moduleName;
            document.getElementById('current-module').textContent = moduleName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            document.getElementById('current-status').textContent = 'Loading module...';

            // Module-specific loading logic
            switch(moduleName) {
                case 'webgl_scanner':
                    // Open WebGL function scanner
                    window.open('webgl_function_scanner.html', '_blank');
                    document.getElementById('current-status').textContent = 'WebGL Scanner opened';
                    break;
                    
                case 'api_docs':
                    // Open API documentation
                    window.open('api_documentation.html', '_blank');
                    document.getElementById('current-status').textContent = 'API Documentation opened';
                    break;
                    
                case 'sql_console':
                    // Show SQL console interface
                    showSQLConsole();
                    break;
                    
                case 'debug_tools':
                    // Show debug tools
                    showDebugTools();
                    break;
                    
                case 'test_suite':
                    // Run test suite
                    runTestSuite();
                    break;
                    
                default:
                    // Standard module loading
                    setTimeout(() => {
                        document.getElementById('current-status').textContent = 'Module loaded';
                        updateLastUpdate();
                        updateVisualizationForModule(moduleName);
                    }, 1000);
                    break;
            }
        }

        // Additional utility functions
        function showSQLConsole() {
            const sqlContent = `
                <div class="sql-console">
                    <h3>SQL Console</h3>
                    <textarea id="sql-query" placeholder="Enter SQL query..."></textarea>
                    <button onclick="executeSQL()">Execute</button>
                    <div id="sql-results"></div>
                </div>
            `;
            
            const mainContainer = document.getElementById('webgl-container');
            if (mainContainer) {
                mainContainer.innerHTML = sqlContent;
            }
        }

        function showDebugTools() {
            const debugContent = `
                <div class="debug-tools">
                    <h3>Debug Tools</h3>
                    <button onclick="debugWebGL()">Debug WebGL</button>
                    <button onclick="debugNetwork()">Debug Network</button>
                    <button onclick="debugDatabase()">Debug Database</button>
                    <div id="debug-output"></div>
                </div>
            `;
            
            const mainContainer = document.getElementById('webgl-container');
            if (mainContainer) {
                mainContainer.innerHTML = debugContent;
            }
        }

        function runTestSuite() {
            const testContent = `
                <div class="test-suite">
                    <h3>Test Suite</h3>
                    <button onclick="runWebGLTests()">WebGL Tests</button>
                    <button onclick="runAPITests()">API Tests</button>
                    <button onclick="runDatabaseTests()">Database Tests</button>
                    <div id="test-results"></div>
                </div>
            `;
            
            const mainContainer = document.getElementById('webgl-container');
            if (mainContainer) {
                mainContainer.innerHTML = testContent;
            }
        }

        // Initialize enhanced menu when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the SLMS WebGL Interface
            slmsInterface = new SLMSWebGLInterface();
            console.log('SLMS WebGL Interface initialized');
            
            // Initialize enhanced menu
            initializeEnhancedMenu();
            
            // Initialize other components
            initializeEventListeners();
            updateSystemStats();
            
            // Initialize Advanced 3D Visualization
            initializeAdvanced3DVisualization();
            
            // Fallback: Hide loading screen after 5 seconds regardless
            setTimeout(() => {
                const loadingScreen = document.getElementById('loading');
                if (loadingScreen && loadingScreen.style.display !== 'none') {
                    loadingScreen.style.display = 'none';
                    console.log('Loading screen hidden by fallback timeout');
                }
            }, 5000);
        });
        
        // Advanced 3D Visualization Integration
        function initializeAdvanced3DVisualization() {
            if (typeof THREE !== 'undefined' && slmsInterface) {
                // Initialize 3D visualization system
                slmsInterface.initialize3DVisualization();
                
                // Add 3D controls to the interface
                add3DVisualizationControls();
                
                console.log('Advanced 3D visualization initialized based on WebGL Fundamentals research');
            } else {
                console.warn('Three.js not available or SLMS interface not initialized');
            }
            
            // Hide loading screen after 3D visualization is complete
            setTimeout(() => {
                const loadingScreen = document.getElementById('loading');
                if (loadingScreen) {
                    loadingScreen.style.display = 'none';
                    console.log('Loading screen hidden - interface ready');
                }
            }, 1000);
        }
        
        function add3DVisualizationControls() {
            // Add 3D controls to the main interface
            const controlsContainer = document.querySelector('.console-controls');
            if (controlsContainer) {
                const threeDControls = document.createElement('div');
                threeDControls.className = 'control-panel';
                threeDControls.innerHTML = `
                    <h4>🎨 Advanced 3D Visualization</h4>
                    <div class="control-group">
                        <label>3D Model:</label>
                        <select id="3d-model-select" onchange="change3DModel(this.value)">
                            <option value="cube">📦 Cube</option>
                            <option value="sphere">🌐 Sphere</option>
                            <option value="cylinder">🔲 Cylinder</option>
                            <option value="torus">🍩 Torus</option>
                            <option value="icosphere">⚙️ Icosphere</option>
                            <option value="network">🌐 Network</option>
                        </select>
                    </div>
                    <div class="control-group">
                        <label>Lighting Preset:</label>
                        <select id="lighting-preset-select" onchange="changeLightingPreset(this.value)">
                            <option value="directional">Directional</option>
                            <option value="point">Point</option>
                            <option value="spot">Spot</option>
                            <option value="ambient">Ambient</option>
                            <option value="phong">Phong</option>
                            <option value="pbr">PBR</option>
                        </select>
                    </div>
                    <div class="control-group">
                        <label>Material Type:</label>
                        <select id="material-type-select" onchange="changeMaterialType(this.value)">
                            <option value="phong">Phong</option>
                            <option value="standard">Standard (PBR)</option>
                            <option value="basic">Basic</option>
                            <option value="lambert">Lambert</option>
                            <option value="toon">Toon</option>
                        </select>
                    </div>
                    <div class="control-group">
                        <button onclick="toggle3DAnimation()" class="btn btn-sm btn-primary">▶️ Play/Pause</button>
                        <button onclick="reset3DCamera()" class="btn btn-sm btn-secondary">🔄 Reset</button>
                        <button onclick="toggle3DWireframe()" class="btn btn-sm btn-warning">🔲 Wireframe</button>
                        <button onclick="toggle3DShadows()" class="btn btn-sm btn-info">👁️ Shadows</button>
                    </div>
                    <div class="control-group">
                        <label>Rotation Speed:</label>
                        <input type="range" id="rotation-speed" min="0" max="2" value="0.5" step="0.1" onchange="changeRotationSpeed(this.value)">
                    </div>
                    <div class="performance-stats" id="3d-performance-stats">
                        <small>FPS: <span id="3d-fps">60</span> | Draw Calls: <span id="3d-draw-calls">0</span> | Triangles: <span id="3d-triangles">0</span></small>
                    </div>
                `;
                controlsContainer.appendChild(threeDControls);
                
                // Start performance monitoring
                start3DPerformanceMonitoring();
            }
        }
        
        // 3D Visualization Control Functions
        function change3DModel(modelType) {
            if (slmsInterface && slmsInterface.load3DModel) {
                slmsInterface.load3DModel(modelType);
                update3DPerformanceStats();
            }
        }
        
        function changeLightingPreset(preset) {
            if (slmsInterface && slmsInterface.setLightingPreset) {
                slmsInterface.setLightingPreset(preset);
                update3DPerformanceStats();
            }
        }
        
        function changeMaterialType(materialType) {
            if (slmsInterface) {
                slmsInterface.visualization3D.materialType = materialType;
                if (slmsInterface.load3DModel) {
                    // Reload current model with new material
                    const currentModel = slmsInterface.visualization3D.currentModel;
                    if (currentModel) {
                        const modelType = Object.keys(slmsInterface.modelLibrary).find(key => 
                            slmsInterface.modelLibrary[key].type === currentModel.geometry.type
                        ) || 'cube';
                        slmsInterface.load3DModel(modelType);
                    }
                }
                update3DPerformanceStats();
            }
        }
        
        function toggle3DAnimation() {
            if (slmsInterface && slmsInterface.visualization3D) {
                if (slmsInterface.visualization3D.isAnimating) {
                    slmsInterface.stop3DAnimation();
                } else {
                    slmsInterface.start3DAnimation();
                }
            }
        }
        
        function reset3DCamera() {
            if (slmsInterface && slmsInterface.reset3DCamera) {
                slmsInterface.reset3DCamera();
            }
        }
        
        function toggle3DWireframe() {
            if (slmsInterface && slmsInterface.toggle3DWireframe) {
                slmsInterface.toggle3DWireframe();
            }
        }
        
        function toggle3DShadows() {
            if (slmsInterface && slmsInterface.toggle3DShadows) {
                slmsInterface.toggle3DShadows();
            }
        }
        
        function changeRotationSpeed(speed) {
            if (slmsInterface && slmsInterface.visualization3D) {
                slmsInterface.visualization3D.rotationSpeed = parseFloat(speed);
            }
        }
        
        function start3DPerformanceMonitoring() {
            setInterval(() => {
                update3DPerformanceStats();
            }, 1000);
        }
        
        function update3DPerformanceStats() {
            if (slmsInterface && slmsInterface.get3DPerformanceReport) {
                const report = slmsInterface.get3DPerformanceReport();
                const fpsElement = document.getElementById('3d-fps');
                const drawCallsElement = document.getElementById('3d-draw-calls');
                const trianglesElement = document.getElementById('3d-triangles');
                
                if (fpsElement) fpsElement.textContent = report.fps || 0;
                if (drawCallsElement) drawCallsElement.textContent = report.drawCalls || 0;
                if (trianglesElement) trianglesElement.textContent = report.triangles || 0;
            }
        }
    </script>
</body>
</html>