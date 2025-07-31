<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers/layout_helper.php';

$pdo = get_pdo();
$layout_settings = get_layout_settings($pdo);

// Get the target content URL
$content_url = $_GET['url'] ?? 'index.php';
$page_title = $_GET['title'] ?? 'sLMS System';

// Generate dynamic CSS
$dynamic_css = generate_layout_css($layout_settings);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/style.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        <?= $dynamic_css ?>
        
        body {
            margin: 0;
            padding: 20px;
            background: var(--lms-background);
            color: var(--lms-text);
            font-family: var(--lms-font-family);
            font-size: var(--lms-font-size);
        }
        
        .content-container {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .breadcrumb-container {
            margin-bottom: 20px;
            padding: 10px 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .table-header {
            padding: 20px;
            border-bottom: 1px solid var(--lms-border);
            background: var(--lms-primary);
            color: white;
        }
        
        .table-content {
            padding: 20px;
        }
        
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--lms-primary);
        }
        
        .error-message {
            text-align: center;
            padding: 40px;
            color: #dc3545;
        }
        
        .refresh-button {
            margin-top: 10px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .table-header,
            .table-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="content-container">
        <?php if ($layout_settings['show_breadcrumbs']): ?>
            <nav aria-label="breadcrumb" class="breadcrumb-container">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="#" onclick="navigateToPage('<?= base_url('index.php') ?>')">Strona główna</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= htmlspecialchars($page_title) ?>
                    </li>
                </ol>
            </nav>
        <?php endif; ?>
        
        <div class="table-container">
            <div class="table-header">
                <h4 class="mb-0">
                    <i class="bi bi-table me-2"></i>
                    <?= htmlspecialchars($page_title) ?>
                </h4>
            </div>
            
            <div class="table-content" id="contentArea">
                <div class="loading-spinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Ładowanie...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load content dynamically
        function loadContent() {
            const contentArea = document.getElementById('contentArea');
            const contentUrl = '<?= base_url($content_url) ?>';
            
            // Show loading spinner
            contentArea.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Ładowanie...</span>
                    </div>
                </div>
            `;
            
            // Load content via AJAX
            fetch(contentUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    // Extract the main content from the response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Try to find the main content
                    let content = doc.querySelector('main') || 
                                 doc.querySelector('.container') || 
                                 doc.querySelector('body');
                    
                    if (content) {
                        contentArea.innerHTML = content.innerHTML;
                    } else {
                        contentArea.innerHTML = html;
                    }
                    
                    // Initialize any Bootstrap components
                    if (window.bootstrap) {
                        // Reinitialize tooltips
                        const tooltips = contentArea.querySelectorAll('[data-bs-toggle="tooltip"]');
                        tooltips.forEach(tooltip => {
                            new bootstrap.Tooltip(tooltip);
                        });
                        
                        // Reinitialize popovers
                        const popovers = contentArea.querySelectorAll('[data-bs-toggle="popover"]');
                        popovers.forEach(popover => {
                            new bootstrap.Popover(popover);
                        });
                    }
                    
                    // Handle internal links to work with frame system
                    const links = contentArea.querySelectorAll('a[href]');
                    links.forEach(link => {
                        const href = link.getAttribute('href');
                        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                            link.addEventListener('click', function(e) {
                                e.preventDefault();
                                navigateToPage(href);
                            });
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading content:', error);
                    contentArea.innerHTML = `
                        <div class="error-message">
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i>
                            <h5 class="mt-3">Błąd ładowania zawartości</h5>
                            <p class="text-muted">Nie udało się załadować zawartości strony.</p>
                            <button class="btn btn-primary refresh-button" onclick="loadContent()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Odśwież
                            </button>
                        </div>
                    `;
                });
        }
        
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
        
        // Load content when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadContent();
        });
        
        // Handle refresh requests from parent
        window.addEventListener('message', function(event) {
            if (event.data.type === 'refresh') {
                loadContent();
            }
        });
        
        // Auto-refresh every 5 minutes for dynamic content
        setInterval(loadContent, 300000);
        
        // Handle keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'r':
                        e.preventDefault();
                        loadContent();
                        break;
                    case 'f':
                        e.preventDefault();
                        // Focus search if available
                        const searchInput = document.querySelector('input[type="search"], input[name="query"]');
                        if (searchInput) {
                            searchInput.focus();
                        }
                        break;
                }
            }
        });
    </script>
</body>
</html> 