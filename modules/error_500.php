<?php
$pageTitle = '500 - Internal Server Error';
require_once 'module_loader.php';

require_once 'helpers/auth_helper.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - sLMS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('assets/style.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <i class="bi bi-exclamation-octagon text-danger" style="font-size: 4rem;"></i>
                        <h1 class="mt-3 text-danger">500</h1>
                        <h2 class="text-muted">Internal Server Error</h2>
                        <p class="lead">Something went wrong on our end.</p>
                        <p class="text-muted">We're working to fix the problem. Please try again later.</p>
                        <div class="mt-4">
                            <a href="<?= base_url('index.php') ?>" class="btn btn-primary me-2">
                                <i class="bi bi-house"></i> Go Home
                            </a>
                            <a href="javascript:location.reload()" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Try Again
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 