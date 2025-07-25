<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/modules/helpers/auth_helper.php';

// Require login and admin access
require_login();
require_admin();

$pdo = get_pdo();

// Handle actions: add, edit, delete, enable/disable, move, etc.
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$message = '';

// Add new menu item
if ($action === 'add' && isset($_POST['label'])) {
    $url = $_POST['url'] ?? null;
    if ($url && $url[0] !== '/') {
        $url = '/' . $url;
    }
    $stmt = $pdo->prepare("INSERT INTO menu_items (label, url, type, script, parent_id, position, enabled, options) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['label'],
        $url,
        $_POST['type'] ?? 'link',
        $_POST['script'] ?? null,
        $_POST['parent_id'] ?: null,
        $_POST['position'] ?? 0,
        isset($_POST['enabled']) ? 1 : 0,
        $_POST['options'] ?: null
    ]);
    $message = 'Element menu dodany.';
}
// Edit menu item
if ($action === 'edit' && isset($_POST['id'])) {
    $url = $_POST['url'] ?? null;
    if ($url && $url[0] !== '/') {
        $url = '/' . $url;
    }
    $stmt = $pdo->prepare("UPDATE menu_items SET label=?, url=?, type=?, script=?, parent_id=?, position=?, enabled=?, options=? WHERE id=?");
    $stmt->execute([
        $_POST['label'],
        $url,
        $_POST['type'] ?? 'link',
        $_POST['script'] ?? null,
        $_POST['parent_id'] ?: null,
        $_POST['position'] ?? 0,
        isset($_POST['enabled']) ? 1 : 0,
        $_POST['options'] ?: null,
        $_POST['id']
    ]);
    $message = 'Element menu zaktualizowany.';
}
// Delete menu item
if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $message = 'Element menu usunięty.';
}
// Enable/disable
if (($action === 'enable' || $action === 'disable') && isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE menu_items SET enabled=? WHERE id=?");
    $stmt->execute([$action === 'enable' ? 1 : 0, $_GET['id']]);
    $message = 'Element menu '.($action === 'enable' ? 'włączony' : 'wyłączony').'.';
}
// Move up/down
if (($action === 'moveup' || $action === 'movedown') && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT parent_id, position FROM menu_items WHERE id=?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item) {
        $parent_id = $item['parent_id'];
        $pos = $item['position'];
        $cmp = $action === 'moveup' ? '<' : '>';
        $order = $action === 'moveup' ? 'DESC' : 'ASC';
        $stmt2 = $pdo->prepare("SELECT id, position FROM menu_items WHERE parent_id <=> ? AND position $cmp ? ORDER BY position $order LIMIT 1");
        $stmt2->execute([$parent_id, $pos]);
        $swap = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($swap) {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE menu_items SET position=? WHERE id=?")->execute([$swap['position'], $id]);
            $pdo->prepare("UPDATE menu_items SET position=? WHERE id=?")->execute([$pos, $swap['id']]);
            $pdo->commit();
            $message = 'Element menu przeniesiony.';
        }
    }
}
// Fetch all menu items for display
$stmt = $pdo->prepare("SELECT * FROM menu_items ORDER BY parent_id, position ASC");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Build id=>item and parent=>[children] for tree
$menu = [];
$children = [];
foreach ($items as $item) {
    $menu[$item['id']] = $item;
    $children[$item['parent_id']][] = $item['id'];
}
function render_menu_admin($parent_id, $menu, $children, $level = 0) {
    if (empty($children[$parent_id])) return;
    foreach ($children[$parent_id] as $id) {
        $item = $menu[$id];
        echo '<tr>';
        echo '<td>'.str_repeat('&mdash; ', $level).htmlspecialchars($item['label']).'</td>';
        echo '<td>'.htmlspecialchars($item['url']).'</td>';
        echo '<td>'.htmlspecialchars($item['type']).'</td>';
        echo '<td>'.($item['enabled'] ? 'Tak' : 'Nie').'</td>';
        echo '<td>'.htmlspecialchars($item['options']).'</td>';
        echo '<td>';
        echo '<a href="?action=moveup&id='.$id.'" class="btn btn-sm btn-secondary">↑</a> ';
        echo '<a href="?action=movedown&id='.$id.'" class="btn btn-sm btn-secondary">↓</a> ';
        echo '<a href="?action=editform&id='.$id.'" class="btn btn-sm btn-primary">Edytuj</a> ';
        echo '<a href="?action=delete&id='.$id.'" class="btn btn-sm btn-danger" onclick="return confirm(\'Usunąć ten element?\')">Usuń</a> ';
        if ($item['enabled']) {
            echo '<a href="?action=disable&id='.$id.'" class="btn btn-sm btn-warning">Wyłącz</a>';
        } else {
            echo '<a href="?action=enable&id='.$id.'" class="btn btn-sm btn-success">Włącz</a>';
        }
        echo '</td>';
        echo '</tr>';
        render_menu_admin($id, $menu, $children, $level+1);
    }
}
// For edit form
$edit_item = null;
if ($action === 'editform' && isset($_GET['id'])) {
    $edit_item = $menu[$_GET['id']] ?? null;
}
// For parent dropdown
function parent_options($menu, $current_id = null, $parent_id = null, $level = 0) {
    foreach ($menu as $id => $item) {
        if ($id == $current_id) continue;
        echo '<option value="'.htmlspecialchars($id).'"'.($parent_id == $id ? ' selected' : '').'>';
        echo str_repeat('&mdash; ', $level).htmlspecialchars($item['label']);
        echo '</option>';
    }
}

// User statistics functions
function get_user_count() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function get_active_user_count() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function get_admin_count() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function get_last_login_count() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

$fg_message = '';
if (isset($_POST['fg_action']) && ($_POST['fg_action'] === 'save' || $_POST['fg_action'] === 'drop')) {
    $fg_template = $_POST['file_generator_template'] ?? '';
    $fg_path = $_POST['file_generator_path'] ?? '';
    if ($_POST['fg_action'] === 'save' && $fg_template && $fg_path) {
        $devices = $pdo->query('SELECT * FROM devices')->fetchAll(PDO::FETCH_ASSOC);
        $full_content = '';
        foreach ($devices as $dev) {
            $content = $fg_template;
            foreach ($dev as $k => $v) {
                $content = str_replace('{$'.$k.'}', $v, $content);
            }
            $full_content .= $content . "\n";
        }
        if (!preg_match('#^/#', $fg_path)) $fg_path = __DIR__ . '/../' . $fg_path;
        file_put_contents($fg_path, $full_content);
        $fg_message = "Wygenerowano 1 plik z " . count($devices) . " wpisami urządzeń.";
    } elseif ($_POST['fg_action'] === 'drop' && $_POST['file_generator_path']) {
        if (!preg_match('#^/#', $_POST['file_generator_path'])) {
            $filename = __DIR__ . '/../' . $_POST['file_generator_path'];
        } else {
            $filename = $_POST['file_generator_path'];
        }
        if (file_exists($filename)) {
            unlink($filename);
            $fg_message = "Usunięto wygenerowany plik.";
        } else {
            $fg_message = "Nie znaleziono pliku do usunięcia.";
        }
    }
}

// Get current page to determine which section should be expanded
$current_page = basename($_SERVER['SCRIPT_NAME']);
$current_module = isset($_GET['module']) ? $_GET['module'] : '';

// Define which sections should be expanded based on current page
// Set to empty array to have all sections collapsed by default
$expanded_sections = [];

// Uncomment the lines below if you want specific sections to auto-expand based on current page
/*
// SNMP/Monitoring section
if (in_array($current_page, ['network_monitoring_enhanced.php', 'discover_snmp_mndp.php', 'mndp_monitor.php'])) {
    $expanded_sections[] = 'snmp_monitoring';
}

// DHCP Import section
if (in_array($current_page, ['import_dhcp_clients_improved.php', 'import_dhcp_networks_improved.php'])) {
    $expanded_sections[] = 'dhcp_import';
}

// Device Management section
if (in_array($current_page, ['add_device.php', 'edit_device.php', 'client_devices.php'])) {
    $expanded_sections[] = 'device_management';
}

// Client Management section
if (in_array($current_page, ['add_client.php', 'edit_client.php'])) {
    $expanded_sections[] = 'client_management';
}

// Network Management section
if (in_array($current_page, ['networks.php', 'add_network.php'])) {
    $expanded_sections[] = 'network_management';
}

// Internet Packages section
if (in_array($current_page, ['add_internet_package.php', 'internet_packages.php'])) {
    $expanded_sections[] = 'internet_packages';
}

// System section
if (in_array($current_page, ['sql_console.php', 'system_status.php', 'backup.php'])) {
    $expanded_sections[] = 'system';
}
*/

$pageTitle = 'Administracja';
ob_start();
?>

<style>
.admin-section {
    margin-bottom: 10px;
}

.admin-section-header {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px 15px;
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s;
}

.admin-section-header:hover {
    background: #e9ecef;
}

.admin-section-header.expanded {
    background: #007bff;
    color: white;
    border-color: #0056b3;
}

.admin-section-content {
    display: none;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 5px 5px;
    padding: 15px;
    background: white;
}

.admin-section-content.expanded {
    display: block;
}

.admin-section-icon {
    margin-right: 8px;
}

.admin-section-toggle {
    float: right;
    transition: transform 0.2s;
}

.admin-section-header.expanded .admin-section-toggle {
    transform: rotate(180deg);
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="bi bi-gear"></i> Administracja Systemu
            </h1>
            
            <!-- SNMP i Monitoring Sieci z Cacti -->
            <div class="admin-section" id="snmp_monitoring">
                <div class="admin-section-header <?= in_array('snmp_monitoring', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('snmp_monitoring')">
                    <i class="bi bi-activity admin-section-icon"></i>
                    <strong>SNMP i Monitoring Sieci (Cacti)</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('snmp_monitoring', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="modules/network_monitoring_enhanced.php" class="btn btn-warning w-100">
                                <i class="bi bi-activity"></i> SNMP Monitoring
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="modules/snmp_graph.php" class="btn btn-success w-100">
                                <i class="bi bi-graph-up"></i> SNMP Graphing
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="modules/interface_monitoring.php" class="btn btn-info w-100">
                                <i class="bi bi-hdd-network"></i> Interface Monitoring
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="modules/queue_monitoring.php" class="btn btn-primary w-100">
                                <i class="bi bi-list-ol"></i> Queue Monitoring
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="modules/discover_snmp_mndp.php" class="btn btn-secondary w-100">
                                <i class="bi bi-search"></i> SNMP/MNDP Discovery
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="modules/mndp_monitor.php" class="btn btn-dark w-100">
                                <i class="bi bi-broadcast"></i> MNDP Monitor
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="create_snmp_graph_table.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-database"></i> Create SNMP Tables
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="modules/snmp_graph_poll.php" class="btn btn-outline-success w-100">
                                <i class="bi bi-arrow-clockwise"></i> Poll SNMP Data
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="modules/cacti_integration.php" class="btn btn-danger w-100">
                                <i class="bi bi-graph-up"></i> Cacti Integration
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="modules/test_cacti_integration.php" class="btn btn-outline-danger w-100">
                                <i class="bi bi-gear"></i> Test Cacti
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import DHCP -->
            <div class="admin-section" id="dhcp_import">
                <div class="admin-section-header <?= in_array('dhcp_import', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('dhcp_import')">
                    <i class="bi bi-download admin-section-icon"></i>
                    <strong>Import DHCP</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('dhcp_import', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="modules/import_dhcp_clients_improved.php" class="btn btn-success w-100">
                                <i class="bi bi-people"></i> Import Klientów DHCP
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="modules/import_dhcp_networks_improved.php" class="btn btn-info w-100">
                                <i class="bi bi-diagram-3"></i> Import Sieci DHCP
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zarządzanie Urządzeniami -->
            <div class="admin-section" id="device_management">
                <div class="admin-section-header <?= in_array('device_management', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('device_management')">
                    <i class="bi bi-hdd-network admin-section-icon"></i>
                    <strong>Zarządzanie Urządzeniami</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('device_management', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <a href="modules/add_device.php" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> Dodaj Urządzenie
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="modules/client_devices.php" class="btn btn-info w-100">
                                <i class="bi bi-list"></i> Lista Urządzeń
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="modules/edit_device.php" class="btn btn-warning w-100">
                                <i class="bi bi-pencil"></i> Edytuj Urządzenie
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zarządzanie Klientami -->
            <div class="admin-section" id="client_management">
                <div class="admin-section-header <?= in_array('client_management', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('client_management')">
                    <i class="bi bi-people-fill admin-section-icon"></i>
                    <strong>Zarządzanie Klientami</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('client_management', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="modules/add_client.php" class="btn btn-success w-100">
                                <i class="bi bi-person-plus"></i> Dodaj Klienta
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="modules/edit_client.php" class="btn btn-warning w-100">
                                <i class="bi bi-person-gear"></i> Edytuj Klienta
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zarządzanie Sieciami -->
            <div class="admin-section" id="network_management">
                <div class="admin-section-header <?= in_array('network_management', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('network_management')">
                    <i class="bi bi-diagram-3-fill admin-section-icon"></i>
                    <strong>Zarządzanie Sieciami</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('network_management', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="modules/networks.php" class="btn btn-info w-100">
                                <i class="bi bi-list"></i> Lista Sieci
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="modules/add_network.php" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> Dodaj Sieć
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pakiety Internetowe -->
            <div class="admin-section" id="internet_packages">
                <div class="admin-section-header <?= in_array('internet_packages', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('internet_packages')">
                    <i class="bi bi-wifi admin-section-icon"></i>
                    <strong>Pakiety Internetowe</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('internet_packages', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="modules/add_internet_package.php" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> Dodaj Pakiet
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="modules/internet_packages.php" class="btn btn-info w-100">
                                <i class="bi bi-list"></i> Lista Pakietów
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Editor -->
            <div class="admin-section" id="dashboard_editor">
                <div class="admin-section-header <?= in_array('dashboard_editor', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('dashboard_editor')">
                    <i class="bi bi-layout-text-window admin-section-icon"></i>
                    <strong>Dashboard Editor</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('dashboard_editor', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="modules/dashboard_editor.php" class="btn btn-primary w-100">
                                <i class="bi bi-palette"></i> Edit Dashboard
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="modules/dashboard_preview.php" class="btn btn-info w-100">
                                <i class="bi bi-eye"></i> Preview Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-gear"></i> Dashboard Configuration</h6>
                                </div>
                                <div class="card-body">
                                    <form action="modules/dashboard_editor.php" method="POST">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Cacti Content</h6>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="cacti_devices" id="cacti_devices" checked>
                                                    <label class="form-check-label" for="cacti_devices">
                                                        Device List
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="cacti_graphs" id="cacti_graphs" checked>
                                                    <label class="form-check-label" for="cacti_graphs">
                                                        Network Graphs
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="cacti_status" id="cacti_status" checked>
                                                    <label class="form-check-label" for="cacti_status">
                                                        System Status
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>SNMP Content</h6>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="snmp_monitoring" id="snmp_monitoring" checked>
                                                    <label class="form-check-label" for="snmp_monitoring">
                                                        SNMP Monitoring
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="snmp_graphs" id="snmp_graphs" checked>
                                                    <label class="form-check-label" for="snmp_graphs">
                                                        SNMP Graphs
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="snmp_alerts" id="snmp_alerts" checked>
                                                    <label class="form-check-label" for="snmp_alerts">
                                                        SNMP Alerts
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="bi bi-save"></i> Save Dashboard Configuration
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme Compositor -->
            <div class="admin-section" id="theme_compositor">
                <div class="admin-section-header <?= in_array('theme_compositor', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('theme_compositor')">
                    <i class="bi bi-palette-fill admin-section-icon"></i>
                    <strong>Theme Compositor</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('theme_compositor', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="modules/theme_compositor.php" class="btn btn-primary w-100">
                                <i class="bi bi-palette"></i> Theme Editor
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="modules/theme_preview.php" class="btn btn-info w-100">
                                <i class="bi bi-eye"></i> Preview Theme
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-gear"></i> Quick Theme Settings</h6>
                                </div>
                                <div class="card-body">
                                    <form action="modules/theme_compositor.php" method="POST">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h6>Layout Type</h6>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="layout_type" id="layout_top" value="top" checked>
                                                    <label class="form-check-label" for="layout_top">
                                                        Top Menu (Horizontal)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="layout_type" id="layout_left" value="left">
                                                    <label class="form-check-label" for="layout_left">
                                                        Left Menu (Vertical)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="layout_type" id="layout_sidebar" value="sidebar">
                                                    <label class="form-check-label" for="layout_sidebar">
                                                        Sidebar Layout
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Menu Style</h6>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="menu_style" id="menu_tree" value="tree" checked>
                                                    <label class="form-check-label" for="menu_tree">
                                                        Tree Menu
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="menu_style" id="menu_dropdown" value="dropdown">
                                                    <label class="form-check-label" for="menu_dropdown">
                                                        Dropdown Menu
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="menu_style" id="menu_accordion" value="accordion">
                                                    <label class="form-check-label" for="menu_accordion">
                                                        Accordion Menu
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="menu_style" id="menu_tabs" value="tabs">
                                                    <label class="form-check-label" for="menu_tabs">
                                                        Tab Menu
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Color Scheme</h6>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="color_scheme" id="color_light" value="light" checked>
                                                    <label class="form-check-label" for="color_light">
                                                        Light Theme
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="color_scheme" id="color_dark" value="dark">
                                                    <label class="form-check-label" for="color_dark">
                                                        Dark Theme
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="color_scheme" id="color_blue" value="blue">
                                                    <label class="form-check-label" for="color_blue">
                                                        Blue Theme
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="color_scheme" id="color_green" value="green">
                                                    <label class="form-check-label" for="color_green">
                                                        Green Theme
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="bi bi-save"></i> Save Theme Configuration
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Editor -->
            <div class="admin-section" id="menu_editor">
                <div class="admin-section-header <?= in_array('menu_editor', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('menu_editor')">
                    <i class="bi bi-list-nested admin-section-icon"></i>
                    <strong>Menu Editor</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('menu_editor', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="modules/menu_editor.php" class="btn btn-primary w-100">
                                <i class="bi bi-list-check"></i> Menu Manager
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="modules/menu_preview.php" class="btn btn-info w-100">
                                <i class="bi bi-eye"></i> Preview Menu
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-gear"></i> Quick Menu Settings</h6>
                                </div>
                                <div class="card-body">
                                    <form action="modules/menu_editor.php" method="POST">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h6>Menu Structure</h6>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="show_snmp" id="show_snmp" checked>
                                                    <label class="form-check-label" for="show_snmp">
                                                        SNMP & Monitoring
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="show_dhcp" id="show_dhcp" checked>
                                                    <label class="form-check-label" for="show_dhcp">
                                                        DHCP Import
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="show_devices" id="show_devices" checked>
                                                    <label class="form-check-label" for="show_devices">
                                                        Device Management
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="show_clients" id="show_clients" checked>
                                                    <label class="form-check-label" for="show_clients">
                                                        Client Management
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Menu Behavior</h6>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="menu_behavior" id="menu_expanded" value="expanded" checked>
                                                    <label class="form-check-label" for="menu_expanded">
                                                        Always Expanded
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="menu_behavior" id="menu_collapsed" value="collapsed">
                                                    <label class="form-check-label" for="menu_collapsed">
                                                        Collapsed by Default
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="menu_behavior" id="menu_remember" value="remember">
                                                    <label class="form-check-label" for="menu_remember">
                                                        Remember State
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Menu Style</h6>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="menu_style" id="menu_style_tree" value="tree" checked>
                                                    <label class="form-check-label" for="menu_style_tree">
                                                        Tree Structure
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="menu_style" id="menu_style_flat" value="flat">
                                                    <label class="form-check-label" for="menu_style_flat">
                                                        Flat List
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="menu_style" id="menu_style_accordion" value="accordion">
                                                    <label class="form-check-label" for="menu_style_accordion">
                                                        Accordion
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="bi bi-save"></i> Save Menu Configuration
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Management -->
            <div class="admin-section" id="user_management">
                <div class="admin-section-header <?= in_array('user_management', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('user_management')">
                    <i class="bi bi-people-fill admin-section-icon"></i>
                    <strong>User Management</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('user_management', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <a href="modules/user_management.php" class="btn btn-primary w-100">
                                <i class="bi bi-people"></i> Manage Users
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="modules/access_level_manager.php" class="btn btn-success w-100">
                                <i class="bi bi-shield-lock"></i> Access Levels
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="modules/activity_log.php" class="btn btn-info w-100">
                                <i class="bi bi-clock-history"></i> Activity Log
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-shield"></i> Security Overview</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-primary"><?php echo get_user_count(); ?></h4>
                                            <small class="text-muted">Total Users</small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-success"><?php echo get_active_user_count(); ?></h4>
                                            <small class="text-muted">Active Users</small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-warning"><?php echo get_admin_count(); ?></h4>
                                            <small class="text-muted">Administrators</small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-info"><?php echo get_last_login_count(); ?></h4>
                                            <small class="text-muted">Recent Logins</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System -->
            <div class="admin-section" id="system">
                <div class="admin-section-header <?= in_array('system', $expanded_sections) ? 'expanded' : '' ?>" 
                     onclick="toggleSection('system')">
                    <i class="bi bi-gear-fill admin-section-icon"></i>
                    <strong>System</strong>
                    <i class="bi bi-chevron-down admin-section-toggle"></i>
                </div>
                <div class="admin-section-content <?= in_array('system', $expanded_sections) ? 'expanded' : '' ?>">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <a href="sql_console.php" class="btn btn-secondary w-100">
                                <i class="bi bi-terminal"></i> Konsola SQL
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="modules/system_status.php" class="btn btn-info w-100">
                                <i class="bi bi-speedometer2"></i> Status Systemu
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="modules/backup.php" class="btn btn-warning w-100">
                                <i class="bi bi-download"></i> Backup
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSection(sectionId) {
    const header = document.querySelector(`#${sectionId} .admin-section-header`);
    const content = document.querySelector(`#${sectionId} .admin-section-content`);
    
    // Toggle the expanded state
    const isExpanded = header.classList.contains('expanded');
    
    if (isExpanded) {
        header.classList.remove('expanded');
        content.classList.remove('expanded');
    } else {
        header.classList.add('expanded');
        content.classList.add('expanded');
    }
}

// Auto-expand section based on current page
document.addEventListener('DOMContentLoaded', function() {
    // The PHP code above already sets the expanded state based on current page
    // This is just for any additional JavaScript functionality
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/partials/layout.php';
?> 