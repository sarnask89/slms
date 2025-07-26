<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Frame Layout System';

$pdo = get_pdo();
$layout_settings = get_layout_settings($pdo);

// Handle frame navigation
$target_frame = $_GET['frame'] ?? 'content';
$target_url = $_GET['url'] ?? 'index.php';

// Set up frame URLs
$navbar_url = base_url('modules/frame_navbar.php');
$content_url = base_url($target_url);

ob_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - AI SERVICE NETWORK MANAGEMENT SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/style.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100vh;
        }
        
        .frame-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar-frame {
            width: 100%;
            height: 60px;
            border: none;
            background: var(--lms-primary);
        }
        
        .sidebar-frame {
            width: 250px;
            height: calc(100vh - 60px);
            border: none;
            background: var(--lms-primary);
        }
        
        .content-frame {
            flex: 1;
            height: calc(100vh - 60px);
            border: none;
            background: var(--lms-background);
        }
        
        .top-layout {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        .left-layout {
            display: flex;
            height: 100vh;
        }
        
        .both-layout {
            display: flex;
            height: 100vh;
        }
        
        .both-layout .top-navbar-frame {
            width: 100%;
            height: 60px;
            border: none;
            background: var(--lms-primary);
        }
        
        .both-layout .main-content {
            display: flex;
            flex: 1;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--lms-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .frame-loading {
            position: relative;
        }
        
        .frame-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--lms-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="frame-container" id="frameContainer">
        <?php if ($layout_settings['menu_position'] === 'left'): ?>
            <!-- Left Sidebar Layout -->
            <div class="left-layout">
                <iframe 
                    src="<?= $navbar_url ?>" 
                    class="sidebar-frame" 
                    id="navbarFrame"
                    onload="frameLoaded('navbar')"
                    title="Navigation Menu">
                </iframe>
                <iframe 
                    src="<?= $content_url ?>" 
                    class="content-frame" 
                    id="contentFrame"
                    onload="frameLoaded('content')"
                    title="Main Content">
                </iframe>
            </div>
        <?php elseif ($layout_settings['menu_position'] === 'both'): ?>
            <!-- Both Top and Sidebar Layout -->
            <div class="both-layout">
                <iframe 
                    src="<?= base_url('modules/frame_top_navbar.php') ?>" 
                    class="top-navbar-frame" 
                    id="topNavbarFrame"
                    onload="frameLoaded('topNavbar')"
                    title="Top Navigation">
                </iframe>
                <div class="main-content">
                    <iframe 
                        src="<?= $navbar_url ?>" 
                        class="sidebar-frame" 
                        id="navbarFrame"
                        onload="frameLoaded('navbar')"
                        title="Sidebar Navigation">
                    </iframe>
                    <iframe 
                        src="<?= $content_url ?>" 
                        class="content-frame" 
                        id="contentFrame"
                        onload="frameLoaded('content')"
                        title="Main Content">
                    </iframe>
                </div>
            </div>
        <?php else: ?>
            <!-- Top Menu Layout -->
            <div class="top-layout">
                <iframe 
                    src="<?= $navbar_url ?>" 
                    class="navbar-frame" 
                    id="navbarFrame"
                    onload="frameLoaded('navbar')"
                    title="Top Navigation">
                </iframe>
                <iframe 
                    src="<?= $content_url ?>" 
                    class="content-frame" 
                    id="contentFrame"
                    onload="frameLoaded('content')"
                    title="Main Content">
                </iframe>
            </div>
        <?php endif; ?>
    </div>

    <script>
        let loadedFrames = 0;
        const totalFrames = <?= $layout_settings['menu_position'] === 'both' ? 3 : 2 ?>;
        
        function frameLoaded(frameName) {
            loadedFrames++;
            console.log(`Frame loaded: ${frameName} (${loadedFrames}/${totalFrames})`);
            
            if (loadedFrames >= totalFrames) {
                // All frames loaded, hide loading overlay
                setTimeout(() => {
                    document.getElementById('loadingOverlay').style.opacity = '0';
                    setTimeout(() => {
                        document.getElementById('loadingOverlay').style.display = 'none';
                    }, 300);
                }, 500);
            }
        }
        
        // Handle navigation from navbar frame
        function navigateTo(url, frame = 'content') {
            const contentFrame = document.getElementById('contentFrame');
            if (contentFrame) {
                contentFrame.src = url;
                // Show loading indicator
                contentFrame.classList.add('frame-loading');
                contentFrame.onload = function() {
                    contentFrame.classList.remove('frame-loading');
                };
            }
        }
        
        // Expose navigation function globally
        window.navigateTo = navigateTo;
        
        // Handle iframe communication
        window.addEventListener('message', function(event) {
            if (event.data.type === 'navigate') {
                navigateTo(event.data.url, event.data.frame);
            }
        });
        
        // Auto-hide loading overlay after timeout
        setTimeout(() => {
            if (document.getElementById('loadingOverlay').style.display !== 'none') {
                document.getElementById('loadingOverlay').style.opacity = '0';
                setTimeout(() => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                }, 300);
            }
        }, 10000);
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?> 