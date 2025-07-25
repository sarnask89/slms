<?php
// partials/navbar.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/helpers/auth_helper.php';
require_once __DIR__ . '/../modules/helpers/database_helper.php';

// Require login for all pages
require_login();

// Get menu items using the helper function
$menu_items = get_menu_items_from_database();

function render_menu($parent_id, $menu_items, $level = 0) {
    foreach ($menu_items as $item) {
        if ($item['parent_id'] == $parent_id) {
            $has_children = !empty($item['children']);
            
            if ($has_children) {
                echo '<li class="nav-item dropdown">';
                echo '<a class="nav-link dropdown-toggle" href="#" id="dropdown'.$item['id'].'" role="button" data-bs-toggle="dropdown" aria-expanded="false">'.htmlspecialchars($item['label']).'</a>';
                echo '<ul class="dropdown-menu">';
                render_menu($item['id'], $item['children'], $level + 1);
                echo '</ul>';
                echo '</li>';
            } else {
                // Generate proper URL
                $url = '';
                if ($item['type'] === 'link' && !empty($item['url'])) {
                    $url = base_url($item['url']);
                } elseif ($item['type'] === 'script' && !empty($item['script'])) {
                    $url = base_url($item['script']);
                } else {
                    $url = '#';
                }
                
                echo '<li class="nav-item">';
                echo '<a class="nav-link" href="'.htmlspecialchars($url).'">'.htmlspecialchars($item['label']).'</a>';
                echo '</li>';
            }
        }
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand lms-accent fw-bold" href="<?= base_url('index.php') ?>">LMS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav">
        <?php render_menu(null, $menu_items); ?>
      </ul>
      <form class="d-flex me-3" action="<?= base_url('modules/search.php') ?>" method="get" role="search">
        <input class="form-control me-2" type="search" name="query" placeholder="Szukaj klientów/urządzeń..." aria-label="Szukaj">
        <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
      </form>
      
      <!-- User Menu -->
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> 
            <?php 
            $currentUser = get_current_user_info();
            if ($currentUser && is_array($currentUser)) {
                echo htmlspecialchars($currentUser['username']); 
                $role = $currentUser['role'] ?? 'user';
            } else {
                echo 'Guest';
                $role = 'guest';
            }
            ?>
            <span class="badge bg-<?php 
              echo $role === 'admin' ? 'danger' : 
                  ($role === 'manager' ? 'warning' : 
                  ($role === 'guest' ? 'secondary' : 'primary')); 
            ?> ms-1"><?php echo ucfirst($role); ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><h6 class="dropdown-header">Menu użytkownika</h6></li>
            <li><a class="dropdown-item" href="<?= base_url('modules/profile.php') ?>"><i class="bi bi-person"></i> Profil</a></li>
            <?php if (is_admin()): ?>
              <li><a class="dropdown-item" href="<?= base_url('admin_menu.php') ?>"><i class="bi bi-gear"></i> Panel administracyjny</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= base_url('modules/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Wyloguj</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav> 