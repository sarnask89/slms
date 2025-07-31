<?php
require_once 'module_loader.php';

$pageTitle = 'SLMS Modules Directory';
$pdo = get_pdo();

// Get available modules
$available_modules = get_available_modules();

// Get system statistics
$stats = get_system_statistics();

ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="lms-card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="lms-accent">📁 SLMS Modules Directory</h2>
                    <a href="<?= base_url('admin_menu_enhanced.php') ?>" class="btn lms-btn-accent">
                        <i class="bi bi-arrow-left"></i> Back to Admin Menu
                    </a>
                </div>

                <!-- System Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= $stats['total_devices'] ?></h5>
                                <p class="card-text">Total Devices</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= $stats['online_devices'] ?></h5>
                                <p class="card-text">Online Devices</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= $stats['total_clients'] ?></h5>
                                <p class="card-text">Total Clients</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= count($available_modules) ?></h5>
                                <p class="card-text">Available Modules</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Module Categories -->
                <div class="row">
                    <!-- Device Management -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-hdd-network"></i> Device Management
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if (in_array('devices', $available_modules)): ?>
                                        <a href="devices.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-pc-display"></i> Devices
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('add_device', $available_modules)): ?>
                                        <a href="add_device.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-plus-circle"></i> Add Device
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('check_device', $available_modules)): ?>
                                        <a href="check_device.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-search"></i> Check Device
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Client Management -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-people"></i> Client Management
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if (in_array('clients', $available_modules)): ?>
                                        <a href="clients.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-person-lines-fill"></i> Clients
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('add_client', $available_modules)): ?>
                                        <a href="add_client.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-person-plus"></i> Add Client
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('client_details', $available_modules)): ?>
                                        <a href="client_details.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-person-badge"></i> Client Details
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Network Management -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-wifi"></i> Network Management
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if (in_array('networks', $available_modules)): ?>
                                        <a href="networks.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-diagram-3"></i> Networks
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('network_monitor', $available_modules)): ?>
                                        <a href="network_monitor.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-graph-up"></i> Network Monitor
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('dhcp_clients', $available_modules)): ?>
                                        <a href="dhcp_clients.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-router"></i> DHCP Clients
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Administration -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-warning text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-gear"></i> System Administration
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if (in_array('users', $available_modules)): ?>
                                        <a href="users.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-person-gear"></i> Users
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('settings', $available_modules)): ?>
                                        <a href="settings.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-sliders"></i> Settings
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('logs', $available_modules)): ?>
                                        <a href="logs.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-journal-text"></i> System Logs
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reports & Analytics -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-graph-up"></i> Reports & Analytics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if (in_array('reports', $available_modules)): ?>
                                        <a href="reports.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-file-earmark-text"></i> Reports
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('analytics', $available_modules)): ?>
                                        <a href="analytics.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-bar-chart"></i> Analytics
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('dashboard', $available_modules)): ?>
                                        <a href="dashboard.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-speedometer2"></i> Dashboard
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- All Modules List -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-dark text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-list"></i> All Modules
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($available_modules as $module): ?>
                                        <a href="<?= $module ?>.php" class="list-group-item list-group-item-action">
                                            <i class="bi bi-box"></i> <?= ucfirst(str_replace('_', ' ', $module)) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php';
?> 