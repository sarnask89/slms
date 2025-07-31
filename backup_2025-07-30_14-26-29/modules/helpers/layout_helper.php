<?php
/**
 * Layout Helper Functions
 * Provides functions to work with dynamic layout configurations
 */

/**
 * Get layout settings from database
 */
function get_layout_settings($pdo) {
    $default_layout = [
        'menu_position' => 'top',
        'menu_style' => 'horizontal',
        'sidebar_width' => '250px',
        'header_height' => '60px',
        'color_scheme' => 'default',
        'font_size' => 'medium',
        'show_breadcrumbs' => true,
        'show_search' => true,
        'show_user_menu' => true,
        'footer_text' => '© ' . date('Y') . ' sLMS System'
    ];
    
    try {
        // Check if table exists first
        $stmt = $pdo->query("SHOW TABLES LIKE 'layout_settings'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("SELECT setting_value FROM layout_settings WHERE setting_key = 'main_layout'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $saved_layout = json_decode($result['setting_value'], true);
                if ($saved_layout) {
                    return array_merge($default_layout, $saved_layout);
                }
            }
        }
    } catch (PDOException $e) {
        // Table might not exist yet
    }
    
    return $default_layout;
}

/**
 * Generate dynamic CSS based on layout settings
 */
function generate_layout_css($layout_settings) {
    $css = '';
    
    // Color schemes
    $color_schemes = [
        'default' => [
            'primary' => '#2563eb',
            'primary_hover' => '#1d4ed8',
            'background' => '#f8fafc',
            'text' => '#374151',
            'border' => '#d1d5db'
        ],
        'dark' => [
            'primary' => '#1f2937',
            'primary_hover' => '#111827',
            'background' => '#111827',
            'text' => '#f9fafb',
            'border' => '#374151'
        ],
        'light' => [
            'primary' => '#6b7280',
            'primary_hover' => '#4b5563',
            'background' => '#ffffff',
            'text' => '#1f2937',
            'border' => '#e5e7eb'
        ],
        'green' => [
            'primary' => '#059669',
            'primary_hover' => '#047857',
            'background' => '#f0fdf4',
            'text' => '#374151',
            'border' => '#bbf7d0'
        ],
        'purple' => [
            'primary' => '#7c3aed',
            'primary_hover' => '#6d28d9',
            'background' => '#faf5ff',
            'text' => '#374151',
            'border' => '#ddd6fe'
        ],
        'orange' => [
            'primary' => '#ea580c',
            'primary_hover' => '#c2410c',
            'background' => '#fff7ed',
            'text' => '#374151',
            'border' => '#fed7aa'
        ]
    ];
    
    $colors = $color_schemes[$layout_settings['color_scheme']] ?? $color_schemes['default'];
    
    // Font sizes
    $font_sizes = [
        'small' => '0.875rem',
        'medium' => '1rem',
        'large' => '1.125rem'
    ];
    
    $font_size = $font_sizes[$layout_settings['font_size']] ?? $font_sizes['medium'];
    
    // Base CSS
    $css .= "
    :root {
        --lms-primary: {$colors['primary']};
        --lms-primary-hover: {$colors['primary_hover']};
        --lms-background: {$colors['background']};
        --lms-text: {$colors['text']};
        --lms-border: {$colors['border']};
        --lms-font-size: {$font_size};
        --lms-sidebar-width: {$layout_settings['sidebar_width']};
        --lms-header-height: {$layout_settings['header_height']};
    }
    
    body {
        background: var(--lms-background);
        color: var(--lms-text);
        font-size: var(--lms-font-size);
    }
    
    .lms-btn-accent {
        background: var(--lms-primary);
        color: white;
        border: none;
    }
    
    .lms-btn-accent:hover {
        background: var(--lms-primary-hover);
    }
    
    .lms-accent {
        color: var(--lms-primary);
    }
    ";
    
         // Menu position specific CSS
     if ($layout_settings['menu_position'] === 'left') {
         $css .= "
         body {
             padding-left: var(--lms-sidebar-width);
             padding-top: 0;
         }
         
         .navbar {
             position: fixed;
             left: 0;
             top: 0;
             width: var(--lms-sidebar-width);
             height: 100vh;
             background: var(--lms-primary);
             color: white;
             z-index: 1000;
             overflow-y: auto;
             display: flex;
             flex-direction: column;
         }
         
         .navbar-nav {
             flex-direction: column !important;
             width: 100%;
             margin: 0;
             padding: 0;
         }
         
         .navbar-nav .nav-link {
             padding: 12px 20px;
             border-bottom: 1px solid rgba(255,255,255,0.1);
             color: white !important;
             text-decoration: none;
             display: block;
             width: 100%;
         }
         
         .navbar-nav .nav-link:hover {
             background: var(--lms-primary-hover);
             color: white !important;
         }
         
         .navbar-brand {
             padding: 20px;
             border-bottom: 1px solid rgba(255,255,255,0.2);
             margin-bottom: 0;
             color: white !important;
             font-weight: bold;
         }
         
         main {
             margin-left: 0;
             padding: 20px;
         }
         
         .navbar-toggler {
             display: none !important;
         }
         
         .navbar-collapse {
             flex-basis: auto !important;
         }
         ";
         } elseif ($layout_settings['menu_position'] === 'both') {
         $css .= "
         body {
             padding-left: var(--lms-sidebar-width);
             padding-top: var(--lms-header-height);
         }
         
         .navbar {
             position: fixed;
             left: 0;
             top: 0;
             width: var(--lms-sidebar-width);
             height: 100vh;
             background: var(--lms-primary);
             color: white;
             z-index: 1000;
             overflow-y: auto;
             display: flex;
             flex-direction: column;
         }
         
         .top-navbar {
             position: fixed;
             top: 0;
             left: var(--lms-sidebar-width);
             right: 0;
             height: var(--lms-header-height);
             background: var(--lms-primary);
             color: white;
             z-index: 999;
             display: flex;
             align-items: center;
             padding: 0 20px;
         }
         
         .navbar-nav {
             flex-direction: column !important;
             width: 100%;
             margin: 0;
             padding: 0;
         }
         
         .navbar-nav .nav-link {
             padding: 12px 20px;
             border-bottom: 1px solid rgba(255,255,255,0.1);
             color: white !important;
             text-decoration: none;
             display: block;
             width: 100%;
         }
         
         .navbar-nav .nav-link:hover {
             background: var(--lms-primary-hover);
             color: white !important;
         }
         
         .navbar-brand {
             padding: 20px;
             border-bottom: 1px solid rgba(255,255,255,0.2);
             margin-bottom: 0;
             color: white !important;
             font-weight: bold;
         }
         
         main {
             margin-left: 0;
             padding: 20px;
         }
         
         .navbar-toggler {
             display: none !important;
         }
         
         .navbar-collapse {
             flex-basis: auto !important;
         }
         ";
    } else {
        // Top menu (default)
        $css .= "
        body {
            padding-top: var(--lms-header-height);
        }
        
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--lms-header-height);
            background: var(--lms-primary);
            color: white;
            z-index: 1000;
        }
        
        .navbar-nav {
            flex-direction: row;
        }
        
        .navbar-nav .nav-link {
            color: white;
            padding: 0 15px;
        }
        
        .navbar-nav .nav-link:hover {
            background: var(--lms-primary-hover);
        }
        
        main {
            margin-top: 0;
            padding: 20px;
        }
        ";
    }
    
    // Hide elements based on settings
    if (!$layout_settings['show_breadcrumbs']) {
        $css .= ".breadcrumb { display: none !important; }";
    }
    
    if (!$layout_settings['show_search']) {
        $css .= ".search-container { display: none !important; }";
    }
    
    if (!$layout_settings['show_user_menu']) {
        $css .= ".user-menu { display: none !important; }";
    }
    
    return $css;
}

/**
 * Check if layout settings table exists
 */
function layout_settings_table_exists($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'layout_settings'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get footer text from layout settings
 */
function get_footer_text($pdo) {
    $layout_settings = get_layout_settings($pdo);
    return $layout_settings['footer_text'] ?? '© ' . date('Y') . ' sLMS System';
}

/**
 * Add emojis to menu labels based on keywords
 */
function add_emoji_to_menu_label($label) {
    $label_lower = strtolower($label);
    
    $emoji_map = [
        'panel główny' => '🏠',
        'strona główna' => '🏠',
        'klienci' => '👥',
        'urządzenia' => '🖥️',
        'urządzenia klienckie' => '🖥️',
        'urządzenia szkieletowe' => '📱',
        'sieci' => '🌐',
        'usługi' => '🔧',
        'taryfy' => '💰',
        'telewizja' => '📺',
        'internet' => '📦',
        'pakiety internetowe' => '📦',
        'pakiety tv' => '📺',
        'faktury' => '🧾',
        'płatności' => '💳',
        'użytkownicy' => '👤',
        'wyszukiwanie' => '🔍',
        'mikrotik' => '⚙️',
        'api' => '⚙️',
        'sprawdzanie' => '🔍',
        'proste sprawdzanie' => '✅',
        'administracja' => '⚙️',
        'admin' => '⚙️',
        'zarządzanie układem' => '🎨',
        'zarządzanie kolumnami' => '📊',
        'zapisz' => '💾',
        'przeładuj' => '🔄',
        'dhcp' => '📡',
        'klienci dhcp' => '📡'
    ];
    
    foreach ($emoji_map as $keyword => $emoji) {
        if (strpos($label_lower, $keyword) !== false) {
            return $emoji . ' ' . $label;
        }
    }
    
    // Default emoji for unknown items
    return '📋 ' . $label;
}

/**
 * Generate navbar HTML based on layout settings
 */
function generate_navbar_html($pdo, $layout_settings) {
    $menu_position = $layout_settings['menu_position'];
    
    if ($menu_position === 'left' || $menu_position === 'both') {
        // Sidebar navigation
        return generate_sidebar_navbar($pdo, $layout_settings);
    } else {
        // Top navigation
        return generate_top_navbar($pdo, $layout_settings);
    }
}

/**
 * Generate sidebar navbar HTML
 */
function generate_sidebar_navbar($pdo, $layout_settings) {
    try {
        // Try to get menu items from database
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE enabled = 1 AND parent_id IS NULL ORDER BY position ASC");
        $stmt->execute();
        $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($menu_items)) {
            // Fallback to default menu items if database is empty
            $menu_items = [
                ['label' => '🏠 Strona główna', 'url' => 'index.php'],
                ['label' => '👥 Klienci', 'url' => 'modules/clients.php'],
                ['label' => '🖥️ Urządzenia', 'url' => 'modules/devices.php'],
                ['label' => '📱 Urządzenia szkieletowe', 'url' => 'modules/skeleton_devices.php'],
                ['label' => '🔗 Urządzenia klientów', 'url' => 'modules/client_devices.php'],
                ['label' => '🌐 Sieci', 'url' => 'modules/networks.php'],
                ['label' => '📡 Klienci DHCP', 'url' => 'modules/dhcp_clients.php'],
                ['label' => '📦 Pakiety internetowe', 'url' => 'modules/internet_packages.php'],
                ['label' => '📺 Pakiety TV', 'url' => 'modules/tv_packages.php'],
                ['label' => '🔧 Usługi', 'url' => 'modules/services.php'],
                ['label' => '💰 Taryfy', 'url' => 'modules/tariffs.php'],
                ['label' => '💳 Płatności', 'url' => 'modules/payments.php'],
                ['label' => '🧾 Faktury', 'url' => 'modules/invoices.php'],
                ['label' => '👤 Użytkownicy', 'url' => 'modules/users.php'],
                ['label' => '🔍 Wyszukiwanie', 'url' => 'modules/search.php'],
                ['label' => '⚙️ MikroTik API', 'url' => 'modules/mikrotik_api.php'],
                ['label' => '🔍 Sprawdzanie urządzeń', 'url' => 'modules/check_device.php'],
                ['label' => '✅ Proste sprawdzanie', 'url' => 'modules/simple_check.php'],
                ['label' => '⚙️ Admin', 'url' => 'admin_menu.php'],
                ['label' => '🎨 Zarządzanie układem', 'url' => 'modules/layout_manager.php'],
                ['label' => '📊 Zarządzanie kolumnami', 'url' => 'modules/column_config.php']
            ];
        }
    } catch (PDOException $e) {
        // Fallback to default menu items if table doesn't exist
        $menu_items = [
            ['label' => '🏠 Strona główna', 'url' => 'index.php'],
            ['label' => '👥 Klienci', 'url' => 'modules/clients.php'],
            ['label' => '🖥️ Urządzenia', 'url' => 'modules/devices.php'],
            ['label' => '📱 Urządzenia szkieletowe', 'url' => 'modules/skeleton_devices.php'],
            ['label' => '🔗 Urządzenia klientów', 'url' => 'modules/client_devices.php'],
            ['label' => '🌐 Sieci', 'url' => 'modules/networks.php'],
            ['label' => '📡 Klienci DHCP', 'url' => 'modules/dhcp_clients.php'],
            ['label' => '📦 Pakiety internetowe', 'url' => 'modules/internet_packages.php'],
            ['label' => '📺 Pakiety TV', 'url' => 'modules/tv_packages.php'],
            ['label' => '🔧 Usługi', 'url' => 'modules/services.php'],
            ['label' => '💰 Taryfy', 'url' => 'modules/tariffs.php'],
            ['label' => '💳 Płatności', 'url' => 'modules/payments.php'],
            ['label' => '🧾 Faktury', 'url' => 'modules/invoices.php'],
            ['label' => '👤 Użytkownicy', 'url' => 'modules/users.php'],
            ['label' => '🔍 Wyszukiwanie', 'url' => 'modules/search.php'],
            ['label' => '⚙️ MikroTik API', 'url' => 'modules/mikrotik_api.php'],
            ['label' => '🔍 Sprawdzanie urządzeń', 'url' => 'modules/check_device.php'],
            ['label' => '✅ Proste sprawdzanie', 'url' => 'modules/simple_check.php'],
            ['label' => '⚙️ Admin', 'url' => 'admin_menu.php'],
            ['label' => '🎨 Zarządzanie układem', 'url' => 'modules/layout_manager.php'],
            ['label' => '📊 Zarządzanie kolumnami', 'url' => 'modules/column_config.php']
        ];
    }
    
    $html = '<nav class="navbar navbar-expand-lg">';
    $html .= '<div class="navbar-brand">sLMS System</div>';
    $html .= '<div class="navbar-nav">';
    
    foreach ($menu_items as $item) {
        $url = $item['url'] ?? $item['script'] ?? '#';
        $label = $item['label'];
        
        // Add emojis to database menu items if they don't have them
        if (!preg_match('/^[🏠👥🖥️📱🔗🌐📡📦📺🔧💰💳🧾👤🔍⚙️✅🎨📊]/', $label)) {
            $label = add_emoji_to_menu_label($label);
        }
        
        $html .= '<a class="nav-link" href="' . base_url($url) . '">' . htmlspecialchars($label) . '</a>';
    }
    
    $html .= '</div>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Generate top navbar HTML
 */
function generate_top_navbar($pdo, $layout_settings) {
    try {
        // Try to get menu items from database
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE enabled = 1 AND parent_id IS NULL ORDER BY position ASC LIMIT 8");
        $stmt->execute();
        $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($menu_items)) {
            // Fallback to default menu items if database is empty
            $menu_items = [
                ['label' => 'Panel główny', 'url' => 'index.php'],
                ['label' => 'Klienci', 'url' => 'modules/clients.php'],
                ['label' => 'Urządzenia klienckie', 'url' => 'modules/devices.php'],
                ['label' => 'Sieci', 'url' => 'modules/networks.php'],
                ['label' => 'Internet', 'url' => 'modules/internet_packages.php'],
                ['label' => 'Usługi', 'url' => 'modules/services.php'],
                ['label' => 'Płatności', 'url' => 'modules/payments.php'],
                ['label' => 'Administracja', 'url' => 'admin_menu.php']
            ];
        }
    } catch (PDOException $e) {
        // Fallback to default menu items if table doesn't exist
        $menu_items = [
            ['label' => 'Panel główny', 'url' => 'index.php'],
            ['label' => 'Klienci', 'url' => 'modules/clients.php'],
            ['label' => 'Urządzenia klienckie', 'url' => 'modules/devices.php'],
            ['label' => 'Sieci', 'url' => 'modules/networks.php'],
            ['label' => 'Internet', 'url' => 'modules/internet_packages.php'],
            ['label' => 'Usługi', 'url' => 'modules/services.php'],
            ['label' => 'Płatności', 'url' => 'modules/payments.php'],
            ['label' => 'Administracja', 'url' => 'admin_menu.php']
        ];
    }
    
    $html = '<nav class="navbar navbar-expand-lg">';
    $html .= '<div class="container-fluid">';
    $html .= '<div class="navbar-brand">sLMS System</div>';
    $html .= '<div class="navbar-nav">';
    
    foreach ($menu_items as $item) {
        $url = $item['url'] ?? $item['script'] ?? '#';
        $label = $item['label'];
        
        // Add emojis to database menu items if they don't have them
        if (!preg_match('/^[🏠👥🖥️📱🔗🌐📡📦📺🔧💰💳🧾👤🔍⚙️✅🎨📊]/', $label)) {
            $label = add_emoji_to_menu_label($label);
        }
        
        $html .= '<a class="nav-link" href="' . base_url($url) . '">' . htmlspecialchars($label) . '</a>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</nav>';
    
    return $html;
}
?> 