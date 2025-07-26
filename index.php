<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/modules/helpers/auth_helper.php';
require_login();
$pageTitle = 'Panel główny AI SERVICE NETWORK MANAGEMENT SYSTEM';
ob_start();
?>
<div class="container">
  <div class="row justify-content-center mt-5">
    <div class="col-md-8">
      <div class="slms-card p-5 text-center">
        <h1 class="display-5 slms-accent mb-3">Witamy w panelu głównym AI SERVICE NETWORK MANAGEMENT SYSTEM</h1>
        <p class="lead mb-5">Zarządzaj swoją siecią, klientami i usługami w jednym miejscu</p>
        
        <div class="row g-4">
            <div class="col-md-3">
                <a href="<?= base_url('modules/clients.php') ?>" class="btn slms-btn-accent w-100 py-3">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    <strong>Klienci</strong><br>
                    <small>Zarządzaj klientami</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= base_url('modules/devices.php') ?>" class="btn slms-btn-accent w-100 py-3">
                    <i class="bi bi-pc-display fs-1 d-block mb-2"></i>
                    <strong>Urządzenia</strong><br>
                    <small>Monitoruj urządzenia</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= base_url('modules/invoices.php') ?>" class="btn slms-btn-accent w-100 py-3">
                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                    <strong>Faktury</strong><br>
                    <small>Zarządzaj fakturami</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= base_url('modules/networks.php') ?>" class="btn slms-btn-accent w-100 py-3">
                    <i class="bi bi-hdd-network fs-1 d-block mb-2"></i>
                    <strong>Sieci</strong><br>
                    <small>Konfiguruj sieci</small>
                </a>
            </div>
        </div>
        
        <div class="row g-4 mt-4">
            <div class="col-md-3">
                <a href="<?= base_url('modules/services.php') ?>" class="btn slms-btn-accent w-100 py-3">
                    <i class="bi bi-gear fs-1 d-block mb-2"></i>
                    <strong>Usługi</strong><br>
                    <small>Zarządzaj usługami</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= base_url('modules/payments.php') ?>" class="btn slms-btn-accent w-100 py-3">
                    <i class="bi bi-credit-card fs-1 d-block mb-2"></i>
                    <strong>Płatności</strong><br>
                    <small>Śledź płatności</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= base_url('modules/users.php') ?>" class="btn slms-btn-accent w-100 py-3">
                    <i class="bi bi-person-badge fs-1 d-block mb-2"></i>
                    <strong>Użytkownicy</strong><br>
                    <small>Zarządzaj użytkownikami</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= base_url('admin_menu.php') ?>" class="btn slms-btn-accent w-100 py-3">
                    <i class="bi bi-tools fs-1 d-block mb-2"></i>
                    <strong>Administracja</strong><br>
                    <small>Ustawienia systemu</small>
                </a>
            </div>
        </div>
        
        <div class="row g-4 mt-4">
            <div class="col-md-3">
                <a href="<?= base_url('modules/dhcp_clients.php') ?>" class="btn btn-info w-100 py-3">
                    <i class="bi bi-wifi fs-1 d-block mb-2"></i>
                    <strong>DHCP Klienci</strong><br>
                    <small>Zobacz klientów DHCP</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= base_url('modules/import_dhcp_clients_improved.php') ?>" class="btn btn-success w-100 py-3">
                    <i class="bi bi-upload fs-1 d-block mb-2"></i>
                    <strong>Import DHCP</strong><br>
                    <small>Zaimportuj klientów DHCP</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= base_url('modules/import_dhcp_networks_improved.php') ?>" class="btn btn-warning w-100 py-3">
                    <i class="bi bi-diagram-3 fs-1 d-block mb-2"></i>
                    <strong>Import Sieci</strong><br>
                    <small>Zaimportuj sieci DHCP</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?= base_url('modules/manual.php') ?>" class="btn btn-secondary w-100 py-3">
                    <i class="bi bi-book fs-1 d-block mb-2"></i>
                    <strong>Podręcznik</strong><br>
                    <small>Dokumentacja systemu</small>
                </a>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once 'partials/layout.php';
?> 