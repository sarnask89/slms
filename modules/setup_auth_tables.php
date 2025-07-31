<?php
require_once 'module_loader.php';


$pdo = get_pdo();
$message = '';

try {
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        role ENUM('admin', 'manager', 'user', 'viewer') DEFAULT 'user',
        is_active BOOLEAN DEFAULT TRUE,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Create user_permissions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_permissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        module VARCHAR(50) NOT NULL,
        permission ENUM('read', 'write', 'admin') DEFAULT 'read',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_module (user_id, module)
    )");
    
    // Create user_activity_log table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_activity_log (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Check if any users exist
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $userCount = $stmt->fetch()['user_count'];
    
    if ($userCount == 0) {
        // Create default admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $adminPassword, 'System Administrator', 'admin@slms.local', 'admin', 1]);
        
        $adminId = $pdo->lastInsertId();
        
        // Add default permissions for admin
        $modules = [
            'dashboard', 'devices', 'clients', 'networks', 'services', 'invoices', 
            'payments', 'users', 'admin', 'dhcp', 'snmp', 'system', 'reports'
        ];
        
        foreach ($modules as $module) {
            $stmt = $pdo->prepare("INSERT INTO user_permissions (user_id, module, permission) VALUES (?, ?, ?)");
            $stmt->execute([$adminId, $module, 'admin']);
        }
        
        $message = 'Tabele uwierzytelniania zostały utworzone. Domyślny administrator: admin / admin123';
    } else {
        $message = 'Tabele uwierzytelniania już istnieją.';
    }
    
} catch (Exception $e) {
    $message = 'Błąd podczas tworzenia tabel: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfiguracja uwierzytelniania - sLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="bi bi-shield-check"></i> Konfiguracja uwierzytelniania
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Przejdź do logowania
                            </a>
                            <a href="../index.php" class="btn btn-secondary">
                                <i class="bi bi-house"></i> Strona główna
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