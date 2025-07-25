<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers/layout_helper.php';

$pdo = get_pdo();
$layout_settings = get_layout_settings($pdo);

// Generate dynamic CSS
$dynamic_css = generate_layout_css($layout_settings);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Navigation</title>
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
            background: var(--lms-primary);
            color: white;
        }
        
        .top-navbar {
            height: 60px;
            width: 100%;
            display: flex;
            align-items: center;
            padding: 0 20px;
            background: var(--lms-primary);
            color: white;
        }
        
        .top-navbar input {
            background: rgba(255,255,255,0.1) !important;
            border: 1px solid rgba(255,255,255,0.2) !important;
            color: white !important;
        }
        
        .top-navbar input::placeholder {
            color: rgba(255,255,255,0.7) !important;
        }
        
        .top-navbar .btn-outline-light {
            border-color: rgba(255,255,255,0.3) !important;
        }
        
        .top-navbar .btn-outline-light:hover {
            background: rgba(255,255,255,0.1) !important;
        }
        
        .dropdown-menu {
            background: white;
            color: #333;
        }
        
        .dropdown-item {
            color: #333;
        }
        
        .dropdown-item:hover {
            background: var(--lms-primary);
            color: white;
        }
    </style>
</head>
<body>
    <div class="top-navbar">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="fw-bold">sLMS System</div>
            <div class="d-flex align-items-center">
                <?php if ($layout_settings['show_search']): ?>
                    <form class="d-flex me-3" action="<?= base_url('modules/search.php') ?>" method="get" role="search">
                        <input type="text" class="form-control form-control-sm me-2" name="query" placeholder="Wyszukaj..." style="width: 200px;">
                        <button class="btn btn-sm btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
                    </form>
                <?php endif; ?>
                
                <?php if ($layout_settings['show_user_menu']): ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>Użytkownik
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="navigateToPage('<?= base_url('modules/profile.php') ?>')">
                                <i class="bi bi-person me-2"></i>Profil
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="navigateToPage('<?= base_url('modules/settings.php') ?>')">
                                <i class="bi bi-gear me-2"></i>Ustawienia
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="navigateToPage('<?= base_url('logout.php') ?>')">
                                <i class="bi bi-box-arrow-right me-2"></i>Wyloguj
                            </a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
        
        // Handle search form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const query = formData.get('query');
                if (query) {
                    navigateToPage('<?= base_url('modules/search.php') ?>?query=' + encodeURIComponent(query));
                }
            });
        });
        
        // Handle keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'k':
                        e.preventDefault();
                        const searchInput = document.querySelector('input[name="query"]');
                        if (searchInput) {
                            searchInput.focus();
                        }
                        break;
                    case 'u':
                        e.preventDefault();
                        navigateToPage('<?= base_url('modules/profile.php') ?>');
                        break;
                }
            }
        });
    </script>
</body>
</html> 