<?php
/**
 * Column Helper Functions
 * Provides functions to work with dynamic column configurations
 */

/**
 * Get column configurations for a specific module
 */
function get_column_configs($pdo, $module_name) {
    $stmt = $pdo->prepare("SELECT * FROM column_configs WHERE module_name = ? AND is_searchable = 1 ORDER BY sort_order ASC");
    $stmt->execute([$module_name]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Generate multiselect HTML for a module
 */
function generate_multiselect_html($pdo, $module_name, $container_id = null) {
    $configs = get_column_configs($pdo, $module_name);
    
    if (empty($configs)) {
        return ''; // Return empty if no configurations
    }
    
    $container_id = $container_id ?: "qs-{$module_name}-properties";
    $title = "Kliknij tutaj, aby wybrać, które właściwości powinny być używane podczas wyszukiwania";
    
    $html = '<div class="lms-ui-multiselect-container tiny lms-ui-quicksearch-property-selection lms-ui-multiselect-selection-group">';
    $html .= '<div class="lms-ui-multiselect-launcher" title="' . htmlspecialchars($title) . '" tabindex="0">';
    $html .= '<i class="fa-fw lms-ui-icon-customisation"></i>';
    $html .= '</div>';
    $html .= '<div class="lms-ui-multiselect-popup lms-ui-popup" style="display: none;">';
    $html .= '<input type="checkbox" class="lms-ui-multiselect-label-workaround">';
    $html .= '<div class="lms-ui-multiselect-popup-titlebar">';
    $html .= '<div class="lms-ui-multiselect-popup-title">Wybierz opcje</div>';
    $html .= '<i class="lms-ui-icon-hide close-button"></i>';
    $html .= '</div>';
    $html .= '<ul class="lms-ui-multiselect-popup-list">';
    
    foreach ($configs as $config) {
        $checked = $config['is_visible'] ? 'checked="checked"' : '';
        $selected_class = $config['is_visible'] ? 'selected' : '';
        $html .= '<li class="visible ' . $selected_class . '">';
        $html .= '<input type="checkbox" value="' . htmlspecialchars($config['field_name']) . '" class="visible" ' . $checked . '>';
        $html .= '<span class="">' . htmlspecialchars($config['field_label']) . '</span>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '<div class="lms-ui-multiselect-popup-checkall">';
    $html .= '<input type="checkbox" class="checkall" value="1" checked="checked">';
    $html .= '<span>zaznacz wszystkie</span>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<select id="' . htmlspecialchars($container_id) . '" class="lms-ui-quicksearch-property-selection lms-ui-multiselect-selection-group" multiple="multiple" title="' . htmlspecialchars($title) . '">';
    
    foreach ($configs as $config) {
        $selected = $config['is_visible'] ? 'selected="selected"' : '';
        $html .= '<option value="' . htmlspecialchars($config['field_name']) . '" ' . $selected . '>';
        $html .= htmlspecialchars($config['field_label']);
        $html .= '</option>';
    }
    
    $html .= '</select>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Get visible columns for a module
 */
function get_visible_columns($pdo, $module_name) {
    $stmt = $pdo->prepare("SELECT * FROM column_configs WHERE module_name = ? AND is_visible = 1 ORDER BY sort_order ASC");
    $stmt->execute([$module_name]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Generate table headers for a module
 */
function generate_table_headers($pdo, $module_name, $additional_columns = []) {
    $visible_columns = get_visible_columns($pdo, $module_name);
    
    $headers = [];
    foreach ($visible_columns as $column) {
        $headers[] = '<th>' . htmlspecialchars($column['field_label']) . '</th>';
    }
    
    // Add additional columns (like actions)
    foreach ($additional_columns as $column) {
        $headers[] = '<th>' . htmlspecialchars($column) . '</th>';
    }
    
    return $headers;
}

/**
 * Generate table row data for a record
 */
function generate_table_row($pdo, $module_name, $record, $additional_cells = []) {
    $visible_columns = get_visible_columns($pdo, $module_name);
    
    $cells = [];
    foreach ($visible_columns as $column) {
        $field_name = $column['field_name'];
        $field_type = $column['field_type'];
        $value = $record[$field_name] ?? '';
        
        // Format based on field type
        switch ($field_type) {
            case 'email':
                $cells[] = '<td><a href="mailto:' . htmlspecialchars($value) . '">' . htmlspecialchars($value) . '</a></td>';
                break;
            case 'textarea':
                $cells[] = '<td>' . htmlspecialchars(substr($value, 0, 100)) . (strlen($value) > 100 ? '...' : '') . '</td>';
                break;
            case 'date':
                $cells[] = '<td>' . htmlspecialchars($value ? date('Y-m-d', strtotime($value)) : '') . '</td>';
                break;
            default:
                $cells[] = '<td>' . htmlspecialchars($value) . '</td>';
        }
    }
    
    // Add additional cells (like action buttons)
    foreach ($additional_cells as $cell) {
        $cells[] = '<td>' . $cell . '</td>';
    }
    
    return $cells;
}

/**
 * Check if column configs table exists
 */
function column_configs_table_exists($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'column_configs'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Initialize default column configurations for a module
 */
function initialize_module_columns($pdo, $module_name, $default_configs) {
    // Check if module already has configurations
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM column_configs WHERE module_name = ?");
    $stmt->execute([$module_name]);
    if ($stmt->fetchColumn() > 0) {
        return false; // Already initialized
    }
    
    // Insert default configurations
    $stmt = $pdo->prepare("INSERT INTO column_configs (module_name, field_name, field_label, field_type, is_visible, is_searchable, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($default_configs as $config) {
        $stmt->execute($config);
    }
    
    return true;
}
?> 