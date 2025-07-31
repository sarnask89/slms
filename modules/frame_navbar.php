<?php
require_once 'module_loader.php';

require_once __DIR__ . '/helpers/layout_helper.php';

$pdo = get_pdo();
$layout_settings = get_layout_settings($pdo);

// Get menu items from database
try {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE enabled = 1 AND parent_id IS NULL ORDER BY position ASC");
    $stmt->execute();
    $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($menu_items)) {
        // Fallback to default menu items if database is empty
        $menu_items = [
            ['label' => 'Panel główny', 'url' => 'index.php'],
            ['label' => 'Klienci', 'url' => 'modules/clients.php'],
            ['label' => 'Urządzenia klienckie', 'url' => 'modules/devices.php'],
            ['label' => 'Urządzenia szkieletowe', 'url' => 'modules/skeleton_devices.php'],
            ['label' => 'Sieci', 'url' => 'modules/networks.php'],
            ['label' => 'Usługi', 'url' => 'modules/services.php'],
            ['label' => 'Taryfy', 'url' => 'modules/tariffs.php'],
            ['label' => 'Telewizja', 'url' => 'modules/tv_packages.php'],
            ['label' => 'Internet', 'url' => 'modules/internet_packages.php'],
            ['label' => 'Faktury', 'url' => 'modules/invoices.php'],
            ['label' => 'Płatności', 'url' => 'modules/payments.php'],
            ['label' => 'Użytkownicy', 'url' => 'modules/users.php'],
            ['label' => 'Zapisz/Przeładuj', 'url' => 'modules/save_reload.php'],
            ['label' => 'Administracja', 'url' => 'admin_menu.php']
        ];
    }
} catch (PDOException $e) {
    // Fallback to default menu items if table doesn't exist
    $menu_items = [
        ['label' => 'Panel główny', 'url' => 'index.php'],
        ['label' => 'Klienci', 'url' => 'modules/clients.php'],
        ['label' => 'Urządzenia klienckie', 'url' => 'modules/devices.php'],
        ['label' => 'Urządzenia szkieletowe', 'url' => 'modules/skeleton_devices.php'],
        ['label' => 'Sieci', 'url' => 'modules/networks.php'],
        ['label' => 'Usługi', 'url' => 'modules/services.php'],
        ['label' => 'Taryfy', 'url' => 'modules/tariffs.php'],
        ['label' => 'Telewizja', 'url' => 'modules/tv_packages.php'],
        ['label' => 'Internet', 'url' => 'modules/internet_packages.php'],
        ['label' => 'Faktury', 'url' => 'modules/invoices.php'],
        ['label' => 'Płatności', 'url' => 'modules/payments.php'],
        ['label' => 'Użytkownicy', 'url' => 'modules/users.php'],
        ['label' => 'Zapisz/Przeładuj', 'url' => 'modules/save_reload.php'],
        ['label' => 'Administracja', 'url' => 'admin_menu.php']
    ];
}

// Generate dynamic CSS
$dynamic_css = generate_layout_css($layout_settings);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/style.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        <?= $dynamic_css ?>
        
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100vh;
        }
        
        .navbar {
            height: 100vh;
            width: 100%;
            margin: 0;
            border-radius: 0;
        }
        
        .navbar-nav {
            height: calc(100vh - 80px);
            overflow-y: auto;
            margin: 0;
            padding: 0;
        }
        
        .nav-link {
            border-radius: 0;
            margin: 0;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            transform: translateX(5px);
            background: rgba(255,255,255,0.1);
        }
        
        .navbar-brand {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 0;
            color: white !important;
            font-weight: bold;
            text-align: center;
        }
        
        /* Hide scrollbar but keep functionality */
        .navbar-nav::-webkit-scrollbar {
            width: 6px;
        }
        
        .navbar-nav::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .navbar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        
        .navbar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }
    </style>
</head>
<body>
    <?php if ($layout_settings['menu_position'] === 'left' || $layout_settings['menu_position'] === 'both'): ?>
        <!-- Sidebar Navigation -->
        <nav class="navbar navbar-expand-lg">
            <div class="navbar-brand">sLMS System</div>
            <div class="navbar-nav">
                <?php foreach ($menu_items as $item): ?>
                    <?php
                    $url = $item['url'] ?? $item['script'] ?? '#';
                    $label = $item['label'];
                    
                    // Add emojis to database menu items
                    $label = add_emoji_to_menu_label($label);
                    ?>
                    <a class="nav-link" href="#" onclick="navigateToPage('<?= base_url($url) ?>')">
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>
    <?php else: ?>
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand lms-accent fw-bold" href="#" onclick="navigateToPage('<?= base_url('index.php') ?>')">LMS</a>
                <div class="navbar-nav">
                    <?php 
                    // Show only first 8 items for top menu
                    $top_menu_items = array_slice($menu_items, 0, 8);
                    foreach ($top_menu_items as $item): 
                    ?>
                        <?php
                        $url = $item['url'] ?? $item['script'] ?? '#';
                        $label = $item['label'];
                        
                        // Add emojis to database menu items
                        $label = add_emoji_to_menu_label($label);
                        ?>
                        <a class="nav-link" href="#" onclick="navigateToPage('<?= base_url($url) ?>')">
                            <?= htmlspecialchars($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <form class="d-flex ms-auto" action="<?= base_url('modules/search.php') ?>" method="get" role="search">
                    <input class="form-control me-2" type="search" name="query" placeholder="Szukaj..." aria-label="Szukaj">
                    <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </nav>
    <?php endif; ?>

    <script>
        function navigateToPage(url) {
            // Send message to parent window to navigate
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    type: 'navigate',
                    url: url,
                    frame: 'content'
                }, '*');
            } else {
                // Fallback for direct access
                window.location.href = url;
            }
        }
        
        // Handle keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        navigateToPage('<?= base_url('index.php') ?>');
                        break;
                    case '2':
                        e.preventDefault();
                        navigateToPage('<?= base_url('modules/clients.php') ?>');
                        break;
                    case '3':
                        e.preventDefault();
                        navigateToPage('<?= base_url('modules/devices.php') ?>');
                        break;
                }
            }
        });
        
        // Add active state to current page
        const currentUrl = window.location.href;
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(currentUrl)) {
                link.classList.add('active');
                link.style.background = 'rgba(255,255,255,0.2)';
            }
        });
    </script>
</body>
</html> 