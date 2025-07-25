<?php
// partials/layout.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/helpers/database_helper.php';
require_once __DIR__ . '/../modules/helpers/auth_helper.php';

$pdo = get_pdo();
$layout_settings = get_layout_settings($pdo);
$footer_text = get_footer_text($pdo);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'sLMS') ?></title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= base_url('assets/style.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/tooltip-system.css') ?>" rel="stylesheet">
  
  <style>
    :root {
      --dark-bg: #1a1a1a;
      --dark-sidebar: #2d2d2d;
      --dark-card: #333333;
      --dark-border: #404040;
      --dark-text: #ffffff;
      --dark-text-muted: #b0b0b0;
      --dark-hover: #404040;
      --dark-active: #007bff;
      --dark-accent: #ffc107;
      --dark-danger: #dc3545;
      --dark-success: #28a745;
      --dark-warning: #ffc107;
      --dark-info: #17a2b8;
      --dark-font: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      background-color: var(--dark-bg);
      color: var(--dark-text);
      font-family: var(--dark-font);
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }
    
    /* Dark Sidebar */
    .dark-sidebar {
      background: var(--dark-sidebar);
      min-height: 100vh;
      width: 280px;
      position: fixed;
      left: 0;
      top: 0;
      z-index: 1000;
      border-right: 1px solid var(--dark-border);
      overflow-y: auto;
    }
    
    .dark-sidebar .logo-section {
      padding: 20px;
      border-bottom: 1px solid var(--dark-border);
      text-align: center;
    }
    
    .dark-sidebar .logo-section .logo {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--dark-accent);
      text-decoration: none;
    }
    
    .dark-sidebar .logo-section .logo-icon {
      font-size: 2rem;
      margin-right: 10px;
      background: linear-gradient(45deg, var(--dark-accent), #ff6b35);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    
    /* Navigation Menu */
    .dark-sidebar .nav-menu {
      padding: 20px 0;
    }
    
    .dark-sidebar .nav-item {
      margin-bottom: 5px;
    }
    
    .dark-sidebar .nav-link {
      color: var(--dark-text-muted);
      padding: 12px 25px;
      display: flex;
      align-items: center;
      text-decoration: none;
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
    }
    
    .dark-sidebar .nav-link:hover {
      background-color: var(--dark-hover);
      color: var(--dark-text);
      border-left-color: var(--dark-accent);
    }
    
    .dark-sidebar .nav-link.active {
      background-color: var(--dark-hover);
      color: var(--dark-text);
      border-left-color: var(--dark-active);
    }
    
    .dark-sidebar .nav-link i {
      margin-right: 12px;
      width: 20px;
      text-align: center;
    }
    
    /* Submenu */
    .dark-sidebar .submenu {
      background: rgba(0, 0, 0, 0.2);
      margin-left: 20px;
      border-left: 1px solid var(--dark-border);
    }
    
    .dark-sidebar .submenu .nav-link {
      padding: 8px 25px;
      font-size: 0.9rem;
    }
    
    .dark-sidebar .submenu .nav-link.active {
      background-color: var(--dark-active);
      color: white;
    }
    
    /* Main Content */
    .dark-main-content {
      margin-left: 280px;
      padding: 20px;
      min-height: 100vh;
      background: var(--dark-bg);
    }
    
    /* Cards */
    .dark-card {
      background: var(--dark-card);
      border: 1px solid var(--dark-border);
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    
    .dark-card .card-header {
      background: var(--dark-sidebar);
      border-bottom: 1px solid var(--dark-border);
      color: var(--dark-text);
      font-weight: 600;
    }
    
    .dark-card .card-body {
      color: var(--dark-text);
    }
    
    /* Tables */
    .dark-table {
      background: var(--dark-card);
      color: var(--dark-text);
    }
    
    .dark-table th {
      background: var(--dark-sidebar);
      border-color: var(--dark-border);
      color: var(--dark-text);
    }
    
    .dark-table td {
      border-color: var(--dark-border);
      color: var(--dark-text-muted);
    }
    
    .dark-table tbody tr:hover {
      background-color: var(--dark-hover);
    }
    
    /* Buttons */
    .btn-dark-custom {
      background: var(--dark-sidebar);
      border: 1px solid var(--dark-border);
      color: var(--dark-text);
    }
    
    .btn-dark-custom:hover {
      background: var(--dark-hover);
      border-color: var(--dark-accent);
      color: var(--dark-text);
    }
    
    .btn-primary-custom {
      background: var(--dark-active);
      border-color: var(--dark-active);
      color: white;
    }
    
    .btn-primary-custom:hover {
      background: #0056b3;
      border-color: #0056b3;
      color: white;
    }
    
    /* Forms */
    .form-control-dark {
      background: var(--dark-card);
      border: 1px solid var(--dark-border);
      color: var(--dark-text);
    }
    
    .form-control-dark:focus {
      background: var(--dark-card);
      border-color: var(--dark-active);
      color: var(--dark-text);
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    /* User Profile Section */
    .user-profile {
      position: absolute;
      bottom: 20px;
      left: 20px;
      right: 20px;
      padding: 15px;
      background: var(--dark-card);
      border-radius: 8px;
      border: 1px solid var(--dark-border);
    }
    
    .user-profile .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--dark-accent);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--dark-bg);
      font-weight: bold;
      margin-right: 10px;
    }
    
    .user-profile .user-info {
      flex: 1;
    }
    
    .user-profile .user-name {
      color: var(--dark-text);
      font-weight: 600;
      margin: 0;
    }
    
    .user-profile .user-role {
      color: var(--dark-text-muted);
      font-size: 0.8rem;
      margin: 0;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .dark-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }
      
      .dark-sidebar.show {
        transform: translateX(0);
      }
      
      .dark-main-content {
        margin-left: 0;
      }
      
      .sidebar-toggle {
        display: block;
      }
    }
    
    /* Scrollbar */
    .dark-sidebar::-webkit-scrollbar {
      width: 6px;
    }
    
    .dark-sidebar::-webkit-scrollbar-track {
      background: var(--dark-sidebar);
    }
    
    .dark-sidebar::-webkit-scrollbar-thumb {
      background: var(--dark-border);
      border-radius: 3px;
    }
    
    .dark-sidebar::-webkit-scrollbar-thumb:hover {
      background: var(--dark-hover);
    }
    
    /* Page Header */
    .page-header {
      background: var(--dark-card);
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid var(--dark-border);
    }
    
    .page-header h1 {
      color: var(--dark-text);
      margin: 0;
      font-size: 1.8rem;
      font-weight: 600;
    }
    
    /* Status Indicators */
    .status-indicator {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 8px;
    }
    
    .status-up {
      background-color: var(--dark-success);
    }
    
    .status-down {
      background-color: var(--dark-danger);
    }
    
    .status-warning {
      background-color: var(--dark-warning);
    }
  </style>
  
  <?= $extraHead ?? '' ?>
</head>
<body>
  <!-- Dark Sidebar -->
  <nav class="dark-sidebar">
    <!-- Logo Section -->
    <div class="logo-section">
      <a href="<?= base_url('index.php') ?>" class="logo">
        <i class="bi bi-hdd-network logo-icon"></i>
        sLMS System
      </a>
    </div>
    
    <!-- Navigation Menu -->
    <div class="nav-menu">
      <?php
      $menu_items = get_menu_items_from_database();
      if (!empty($menu_items)) {
        foreach ($menu_items as $item) {
          $has_children = !empty($item['children']);
          $is_active = false;
          $item_id = 'menu-' . $item['id'];
          
          // Check if current page matches this item
          if (strpos($_SERVER['PHP_SELF'], $item['url']) !== false) {
            $is_active = true;
          }
          
          // Check if current page is in this item's children
          foreach ($item['children'] ?? [] as $child) {
            if (strpos($_SERVER['PHP_SELF'], $child['url']) !== false) {
              $is_active = true;
              break;
            }
          }
          
          echo '<div class="nav-item">';
          if ($has_children) {
            // Item with children - create dropdown
            echo '<a class="nav-link ' . ($is_active ? 'active' : '') . '" href="#" onclick="toggleSubmenu(\'' . $item_id . '\')">';
            echo '<i class="bi ' . ($item['icon'] ?? 'bi-circle') . '"></i>';
            echo htmlspecialchars($item['label']);
            echo '<i class="bi bi-chevron-down ms-auto" id="chevron-' . $item_id . '"></i>';
          echo '</a>';
          
            echo '<div class="submenu" id="' . $item_id . '" style="display: none;">';
            foreach ($item['children'] as $child) {
              $child_active = (strpos($_SERVER['PHP_SELF'], $child['url']) !== false) ? 'active' : '';
              $icon = $child['icon'] ?? 'bi-circle';
              echo '<a class="nav-link ' . $child_active . '" href="' . base_url($child['url']) . '">';
              echo '<i class="bi ' . $icon . '"></i>';
              echo htmlspecialchars($child['label']);
              echo '</a>';
            }
            echo '</div>';
          } else {
            // Direct link for items without children
            echo '<a class="nav-link ' . ($is_active ? 'active' : '') . '" href="' . base_url($item['url']) . '">';
            echo '<i class="bi ' . ($item['icon'] ?? 'bi-circle') . '"></i>';
            echo htmlspecialchars($item['label']);
            echo '</a>';
          }
          echo '</div>';
        }
      } else {
        // Fallback to default menu
        echo '<div class="nav-item">';
        echo '<a class="nav-link" href="' . base_url('index.php') . '">';
        echo '<i class="bi bi-house"></i>Dashboard';
        echo '</a>';
        echo '</div>';
        
        echo '<div class="nav-item">';
        echo '<a class="nav-link" href="#" onclick="toggleSubmenu(\'story-management\')">';
        echo '<i class="bi bi-file-text"></i>Story Management';
        echo '<i class="bi bi-chevron-down ms-auto" id="chevron-story-management"></i>';
        echo '</a>';
        echo '<div class="submenu" id="story-management" style="display: none;">';
        echo '<a class="nav-link" href="' . base_url('modules/clients.php') . '"><i class="bi bi-people"></i>View Clients</a>';
        echo '<a class="nav-link" href="' . base_url('modules/devices.php') . '"><i class="bi bi-pc-display"></i>View Devices</a>';
        echo '<a class="nav-link" href="' . base_url('modules/networks.php') . '"><i class="bi bi-hdd-network"></i>View Networks</a>';
        echo '<a class="nav-link" href="' . base_url('modules/services.php') . '"><i class="bi bi-gear"></i>View Services</a>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="nav-item">';
        echo '<a class="nav-link" href="#" onclick="toggleSubmenu(\'priority-management\')">';
        echo '<i class="bi bi-target"></i>Priority Management';
        echo '<i class="bi bi-chevron-down ms-auto" id="chevron-priority-management"></i>';
        echo '</a>';
        echo '<div class="submenu" id="priority-management" style="display: none;">';
        echo '<a class="nav-link" href="' . base_url('modules/network_monitoring.php') . '"><i class="bi bi-graph-up"></i>Network Monitoring</a>';
        echo '<a class="nav-link" href="' . base_url('modules/cacti_integration.php') . '"><i class="bi bi-speedometer2"></i>Cacti Integration</a>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="nav-item">';
        echo '<a class="nav-link" href="#" onclick="toggleSubmenu(\'tools\')">';
        echo '<i class="bi bi-tools"></i>Tools';
        echo '<i class="bi bi-chevron-down ms-auto" id="chevron-tools"></i>';
        echo '</a>';
        echo '<div class="submenu" id="tools" style="display: none;">';
        echo '<a class="nav-link" href="' . base_url('modules/system_status.php') . '"><i class="bi bi-activity"></i>System Status</a>';
        echo '<a class="nav-link" href="' . base_url('modules/error_monitor.php') . '"><i class="bi bi-exclamation-triangle"></i>Error Monitor</a>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="nav-item">';
        echo '<a class="nav-link" href="#" onclick="toggleSubmenu(\'user-management\')">';
        echo '<i class="bi bi-people-fill"></i>User Management';
        echo '<i class="bi bi-chevron-down ms-auto" id="chevron-user-management"></i>';
        echo '</a>';
        echo '<div class="submenu" id="user-management" style="display: none;">';
        echo '<a class="nav-link" href="' . base_url('modules/users.php') . '"><i class="bi bi-person"></i>View Users</a>';
        echo '<a class="nav-link" href="' . base_url('modules/add_user.php') . '"><i class="bi bi-person-plus"></i>Add User</a>';
        echo '</div>';
        echo '</div>';
      }
      ?>
    </div>
    
    <!-- User Profile Section -->
    <div class="user-profile">
      <div class="d-flex align-items-center">
        <div class="user-avatar">
          <?php
          $user_info = get_current_user_info();
          $initials = substr($user_info['username'] ?? 'AD', 0, 2);
          echo strtoupper($initials);
          ?>
        </div>
        <div class="user-info">
          <p class="user-name"><?= htmlspecialchars($user_info['username'] ?? 'Admin') ?></p>
          <p class="user-role"><?= htmlspecialchars($user_info['role'] ?? 'Administrator') ?></p>
        </div>
      </div>
      <div class="mt-2">
        <a href="<?= base_url('modules/logout.php') ?>" class="btn btn-sm btn-dark-custom w-100">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="dark-main-content">
    <!-- Page Header -->
    <div class="page-header">
      <h1><?= htmlspecialchars($pageTitle ?? 'sLMS Dashboard') ?></h1>
    </div>
    
    <!-- Content -->
    <div class="container-fluid mt-4">
        <?php if (!empty($content)): ?>
            <?= $content ?>
        <?php else: ?>
            <div class="alert alert-info">No content to display. Please check if the module is correctly generating content.</div>
        <?php endif; ?>
    </div>
  </main>

  <!-- Mobile Sidebar Toggle -->
  <button class="btn btn-primary-custom sidebar-toggle d-md-none" 
          style="position: fixed; top: 10px; left: 10px; z-index: 1001; display: none;"
          onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
  </button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url('assets/multiselect.js') ?>"></script>
  <script src="<?= base_url('assets/tooltip-system.js') ?>"></script>
  
  <script>
  function toggleSubmenu(menuId) {
    const submenu = document.getElementById(menuId);
    const chevron = document.getElementById('chevron-' + menuId);
    
    if (submenu.style.display === 'none') {
      submenu.style.display = 'block';
      if (chevron) {
        chevron.className = 'bi bi-chevron-up ms-auto';
      }
    } else {
      submenu.style.display = 'none';
      if (chevron) {
        chevron.className = 'bi bi-chevron-down ms-auto';
      }
    }
  }
  
  function toggleSidebar() {
    const sidebar = document.querySelector('.dark-sidebar');
    sidebar.classList.toggle('show');
  }
  
  // Auto-expand submenu if current page is in it
  document.addEventListener('DOMContentLoaded', function() {
    const activeLinks = document.querySelectorAll('.nav-link.active');
    activeLinks.forEach(link => {
      const submenu = link.closest('.submenu');
      if (submenu) {
        submenu.style.display = 'block';
        const parentLink = submenu.previousElementSibling;
        if (parentLink) {
          parentLink.classList.add('active');
          const chevron = parentLink.querySelector('.bi-chevron-down');
          if (chevron) {
            chevron.className = 'bi bi-chevron-up ms-auto';
          }
        }
      }
    });
  });
  
  // Show mobile toggle on small screens
  function checkScreenSize() {
    const toggle = document.querySelector('.sidebar-toggle');
    if (window.innerWidth <= 768) {
      toggle.style.display = 'block';
    } else {
      toggle.style.display = 'none';
      document.querySelector('.dark-sidebar').classList.remove('show');
    }
  }
  
  window.addEventListener('resize', checkScreenSize);
  checkScreenSize();
  </script>
  
  <?= $extraScripts ?? '' ?>
</body>
</html> 