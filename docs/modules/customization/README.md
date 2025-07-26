# 🎨 Customization Modules

## Overview
The Customization modules provide powerful tools for personalizing the AI SERVICE NETWORK MANAGEMENT SYSTEM interface, including theme editing, dashboard customization, menu configuration, layout management, and widget development.

---

## 📋 Available Modules

### 1. **Theme Editor Module** (`theme_compositor.php`)
Advanced visual theme customization with live preview.

#### Features
- ✅ Live theme preview
- ✅ Color scheme editor
- ✅ Typography settings
- ✅ Component styling
- ✅ Dark mode support
- ✅ Theme import/export

#### Installation
```bash
# Create themes table
CREATE TABLE themes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    author VARCHAR(100),
    version VARCHAR(20),
    is_active BOOLEAN DEFAULT FALSE,
    is_default BOOLEAN DEFAULT FALSE,
    settings JSON,
    css_variables TEXT,
    custom_css TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
);

CREATE TABLE theme_presets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    theme_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    preset_data JSON,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (theme_id) REFERENCES themes(id)
);
```

#### Configuration
```php
// config/themes.php
return [
    'allow_custom_css' => true,
    'css_sanitization' => true,
    'max_custom_css_size' => 102400, // 100KB
    'cache_compiled_css' => true,
    'minify_css' => true,
    'color_variables' => [
        '--primary-color' => '#007bff',
        '--secondary-color' => '#6c757d',
        '--success-color' => '#28a745',
        '--danger-color' => '#dc3545',
        '--warning-color' => '#ffc107',
        '--info-color' => '#17a2b8',
        '--light-color' => '#f8f9fa',
        '--dark-color' => '#343a40'
    ],
    'typography' => [
        '--font-family-base' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto',
        '--font-size-base' => '1rem',
        '--line-height-base' => '1.5',
        '--heading-font-family' => 'inherit'
    ]
];
```

#### Theme Creation
```php
// Create new theme
$themeId = createTheme([
    'name' => 'Modern Blue',
    'description' => 'A modern blue theme with rounded corners',
    'author' => 'Design Team',
    'settings' => [
        'colors' => [
            'primary' => '#0066cc',
            'secondary' => '#6c757d',
            'background' => '#f5f7fa',
            'text' => '#333333'
        ],
        'borders' => [
            'radius' => '8px',
            'width' => '1px',
            'style' => 'solid'
        ],
        'shadows' => [
            'box-shadow' => '0 2px 4px rgba(0,0,0,0.1)'
        ]
    ]
]);

// Apply theme
applyTheme($themeId);

// Create theme preset
createThemePreset($themeId, [
    'name' => 'Dark Mode',
    'preset_data' => [
        'colors' => [
            'background' => '#1a1a1a',
            'text' => '#ffffff'
        ]
    ]
]);
```

---

### 2. **Dashboard Editor Module** (`dashboard_editor.php`)
Drag-and-drop dashboard builder with widget management.

#### Features
- ✅ Drag-and-drop interface
- ✅ Responsive grid system
- ✅ Widget library
- ✅ Real-time data binding
- ✅ Dashboard templates
- ✅ User-specific dashboards

#### Dashboard Structure
```php
// Create dashboard layout
$dashboardId = createDashboard([
    'name' => 'Operations Dashboard',
    'layout' => 'grid',
    'columns' => 12,
    'rows' => 'auto',
    'widgets' => [
        [
            'type' => 'chart',
            'position' => ['x' => 0, 'y' => 0, 'w' => 8, 'h' => 4],
            'config' => [
                'title' => 'Network Traffic',
                'chart_type' => 'line',
                'data_source' => 'bandwidth_monitor',
                'refresh_interval' => 60
            ]
        ],
        [
            'type' => 'stats',
            'position' => ['x' => 8, 'y' => 0, 'w' => 4, 'h' => 2],
            'config' => [
                'title' => 'Active Clients',
                'metric' => 'active_clients_count',
                'icon' => 'users',
                'color' => 'primary'
            ]
        ]
    ]
]);

// Save user dashboard preference
saveUserDashboard($userId, $dashboardId);
```

#### Widget Development
```php
// Register custom widget
registerWidget([
    'type' => 'custom_metric',
    'name' => 'Custom Metric Display',
    'description' => 'Display any custom metric',
    'category' => 'statistics',
    'default_size' => ['w' => 3, 'h' => 2],
    'configurable_options' => [
        'title' => ['type' => 'text', 'required' => true],
        'metric_source' => ['type' => 'select', 'options' => 'available_metrics'],
        'display_format' => ['type' => 'select', 'options' => ['number', 'percentage', 'currency']],
        'color_scheme' => ['type' => 'color_picker']
    ],
    'render_callback' => 'renderCustomMetricWidget'
]);

// Widget rendering function
function renderCustomMetricWidget($config) {
    $value = getMetricValue($config['metric_source']);
    return [
        'template' => 'widgets/custom_metric',
        'data' => [
            'title' => $config['title'],
            'value' => formatValue($value, $config['display_format']),
            'color' => $config['color_scheme']
        ]
    ];
}
```

---

### 3. **Menu Editor Module** (`menu_editor.php`)
Visual menu structure editor with drag-and-drop functionality.

#### Features
- ✅ Hierarchical menu management
- ✅ Drag-and-drop reordering
- ✅ Icon picker
- ✅ Permission-based visibility
- ✅ Custom menu items
- ✅ Multi-language support

#### Menu Configuration
```bash
# Create menu tables
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT DEFAULT NULL,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255),
    icon VARCHAR(50),
    badge_text VARCHAR(20),
    badge_color VARCHAR(20),
    position INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    is_divider BOOLEAN DEFAULT FALSE,
    required_permission VARCHAR(100),
    target VARCHAR(20) DEFAULT '_self',
    custom_class VARCHAR(100),
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    INDEX idx_parent (parent_id),
    INDEX idx_position (position)
);
```

#### Menu Management
```php
// Create menu item
$menuItemId = createMenuItem([
    'title' => 'Network Monitoring',
    'url' => '/monitoring/network',
    'icon' => 'bi-diagram-3',
    'parent_id' => null,
    'position' => 3,
    'required_permission' => 'view_monitoring'
]);

// Add submenu
$submenuId = createMenuItem([
    'title' => 'SNMP Devices',
    'url' => '/monitoring/snmp',
    'icon' => 'bi-hdd-network',
    'parent_id' => $menuItemId,
    'position' => 1
]);

// Reorder menu items
reorderMenuItems([
    ['id' => 1, 'position' => 0],
    ['id' => 3, 'position' => 1],
    ['id' => 2, 'position' => 2]
]);

// Add dynamic badge
setMenuBadge($menuItemId, [
    'text' => getAlertCount(),
    'color' => 'danger',
    'update_interval' => 60
]);
```

---

### 4. **Layout Manager Module** (`layout_manager.php`)
Page layout configuration and template management.

#### Features
- ✅ Multiple layout templates
- ✅ Responsive breakpoints
- ✅ Header/footer customization
- ✅ Sidebar configuration
- ✅ Content area management
- ✅ Layout inheritance

#### Layout Configuration
```php
// config/layouts.php
return [
    'default_layout' => 'standard',
    'available_layouts' => [
        'standard' => [
            'name' => 'Standard Layout',
            'regions' => ['header', 'sidebar', 'content', 'footer'],
            'sidebar_position' => 'left',
            'sidebar_collapsible' => true
        ],
        'wide' => [
            'name' => 'Wide Layout',
            'regions' => ['header', 'content', 'footer'],
            'max_width' => '100%'
        ],
        'dashboard' => [
            'name' => 'Dashboard Layout',
            'regions' => ['top-nav', 'widgets', 'footer'],
            'grid_system' => true
        ]
    ],
    'breakpoints' => [
        'mobile' => 576,
        'tablet' => 768,
        'desktop' => 1024,
        'wide' => 1440
    ]
];
```

#### Layout Implementation
```php
// Create custom layout
$layoutId = createLayout([
    'name' => 'Customer Portal',
    'template' => 'portal',
    'regions' => [
        'top-bar' => ['height' => '60px', 'sticky' => true],
        'main-content' => ['padding' => '20px'],
        'right-panel' => ['width' => '300px', 'collapsible' => true]
    ],
    'responsive_behavior' => [
        'mobile' => ['hide_regions' => ['right-panel']],
        'tablet' => ['stack_regions' => true]
    ]
]);

// Apply layout to pages
applyLayoutToPages($layoutId, [
    'customer/*',
    'portal/*'
]);

// Set user layout preference
setUserLayoutPreference($userId, 'dashboard', $layoutId);
```

---

### 5. **Column Configuration Module** (`column_config.php`)
Table column customization and visibility management.

#### Features
- ✅ Column visibility toggle
- ✅ Column reordering
- ✅ Column width adjustment
- ✅ Sort preference saving
- ✅ Filter persistence
- ✅ Export configuration

#### Column Management
```php
// Save column configuration
saveColumnConfig('clients_table', $userId, [
    'visible_columns' => ['id', 'name', 'email', 'status', 'created_at'],
    'column_order' => ['id', 'name', 'email', 'status', 'created_at'],
    'column_widths' => [
        'id' => '80px',
        'name' => '200px',
        'email' => '250px',
        'status' => '120px',
        'created_at' => '150px'
    ],
    'sort_by' => 'name',
    'sort_direction' => 'asc',
    'filters' => [
        'status' => 'active'
    ]
]);

// Get user's column preferences
$config = getColumnConfig('clients_table', $userId);

// Apply column configuration to query
$query = applyColumnConfig($query, $config);
```

---

### 6. **Widget System**
Extensible widget framework for custom functionality.

#### Widget Types
```php
// Chart widget
registerChartWidget([
    'id' => 'revenue_chart',
    'title' => 'Revenue Overview',
    'data_source' => 'financial_api',
    'chart_options' => [
        'type' => 'bar',
        'stacked' => true,
        'colors' => ['#28a745', '#17a2b8'],
        'legend' => true
    ]
]);

// Stats widget
registerStatsWidget([
    'id' => 'system_stats',
    'metrics' => [
        ['label' => 'Total Clients', 'value' => 'client_count', 'icon' => 'users'],
        ['label' => 'Active Services', 'value' => 'service_count', 'icon' => 'wifi'],
        ['label' => 'Monthly Revenue', 'value' => 'monthly_revenue', 'format' => 'currency'],
        ['label' => 'Uptime', 'value' => 'system_uptime', 'format' => 'percentage']
    ]
]);

// Map widget
registerMapWidget([
    'id' => 'network_map',
    'title' => 'Network Coverage',
    'data_source' => 'device_locations',
    'map_options' => [
        'center' => [52.2297, 21.0122], // Warsaw
        'zoom' => 10,
        'markers' => 'dynamic',
        'clustering' => true
    ]
]);
```

---

## 🎨 Advanced Customization

### CSS Framework Integration
```php
// Integrate custom CSS framework
integrateFramework([
    'name' => 'custom-framework',
    'css_files' => [
        'assets/css/framework.min.css',
        'assets/css/components.min.css'
    ],
    'js_files' => [
        'assets/js/framework.min.js'
    ],
    'init_callback' => 'initCustomFramework'
]);

// Override Bootstrap classes
overrideFrameworkClasses([
    'btn' => 'custom-button',
    'card' => 'custom-panel',
    'alert' => 'custom-notification'
]);
```

### Theme Variables
```css
/* Custom CSS variables */
:root {
    /* Colors */
    --primary-rgb: 0, 123, 255;
    --primary-color: rgb(var(--primary-rgb));
    --primary-hover: rgba(var(--primary-rgb), 0.8);
    
    /* Spacing */
    --spacing-unit: 0.25rem;
    --spacing-xs: calc(var(--spacing-unit) * 2);
    --spacing-sm: calc(var(--spacing-unit) * 3);
    --spacing-md: calc(var(--spacing-unit) * 4);
    --spacing-lg: calc(var(--spacing-unit) * 6);
    --spacing-xl: calc(var(--spacing-unit) * 8);
    
    /* Typography */
    --font-weight-light: 300;
    --font-weight-normal: 400;
    --font-weight-medium: 500;
    --font-weight-bold: 700;
    
    /* Animations */
    --transition-base: all 0.3s ease-in-out;
    --animation-fade-in: fadeIn 0.3s ease-in;
}
```

### JavaScript Hooks
```javascript
// Register customization hooks
registerHook('theme.beforeApply', function(theme) {
    console.log('Applying theme:', theme.name);
    // Custom logic before theme application
});

registerHook('dashboard.widgetAdded', function(widget) {
    // Initialize custom widget behavior
    if (widget.type === 'custom') {
        initializeCustomWidget(widget);
    }
});

registerHook('menu.itemClicked', function(menuItem) {
    // Track menu usage
    trackEvent('menu_click', {
        item: menuItem.title,
        url: menuItem.url
    });
});
```

---

## 🔧 Performance Optimization

### CSS Optimization
```php
// Compile and minify custom CSS
$compiledCSS = compileCustomCSS([
    'theme_id' => $themeId,
    'minify' => true,
    'autoprefixer' => true,
    'remove_unused' => true
]);

// Cache compiled CSS
cacheCompiledCSS($themeId, $compiledCSS, 86400); // 24 hours

// Generate critical CSS
$criticalCSS = generateCriticalCSS([
    'pages' => ['dashboard', 'login', 'clients'],
    'viewport' => '1440x900'
]);
```

### Widget Performance
```php
// Lazy load widgets
enableWidgetLazyLoading([
    'viewport_margin' => '200px',
    'load_placeholder' => true
]);

// Cache widget data
cacheWidgetData('revenue_chart', $data, 300); // 5 minutes

// Batch widget updates
batchUpdateWidgets([
    'update_interval' => 60,
    'stagger_updates' => true
]);
```

---

## 🚨 Troubleshooting

### Common Issues

#### 1. Theme Not Applying
```php
// Clear theme cache
clearThemeCache($themeId);

// Rebuild CSS
rebuildThemeCSS($themeId);

// Check theme conflicts
$conflicts = checkThemeConflicts($themeId);
```

#### 2. Widget Loading Issues
```javascript
// Debug widget loading
window.widgetDebug = true;

// Check widget dependencies
checkWidgetDependencies('custom_widget');

// Reinitialize widgets
reinitializeWidgets();
```

#### 3. Menu Display Problems
```sql
-- Check menu integrity
SELECT mi1.*, mi2.title as parent_title
FROM menu_items mi1
LEFT JOIN menu_items mi2 ON mi1.parent_id = mi2.id
WHERE mi1.is_active = 1
ORDER BY mi1.parent_id, mi1.position;

-- Fix menu positions
UPDATE menu_items 
SET position = (@row_number:=@row_number + 1) - 1
WHERE parent_id = ? AND (@row_number:=0) = 0
ORDER BY position;
```

---

## 🔗 Related Modules
- [User Preferences](../user-guide/preferences.md)
- [Permission System](../authentication/permissions.md)
- [Asset Management](../administration/assets.md)
- [Mobile Responsiveness](../mobile/responsive-design.md)

---

**Module Version**: 2.5.0  
**Last Updated**: January 2025  
**Maintainer**: UI/UX Team