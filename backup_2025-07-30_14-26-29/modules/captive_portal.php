<?php
/**
 * Captive Portal Module
 * Walled Garden Portal with Authentication Popup
 * For VLAN-based guest networks
 */

if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { if (php_sapi_name() !== "cli" && session_status() === PHP_SESSION_NONE) { session_start(); } }

// Configuration
$config = [
    'portal_title' => 'Welcome to Our Network',
    'portal_subtitle' => 'Please login to access the internet',
    'company_name' => 'Your ISP Name',
    'logo_url' => '/assets/images/logo.png',
    'allowed_domains' => [
        'google.com',
        'gmail.com',
        'yahoo.com',
        'hotmail.com',
        'outlook.com',
        'facebook.com',
        'twitter.com',
        'linkedin.com',
        'github.com',
        'stackoverflow.com'
    ],
    'session_timeout' => 3600, // 1 hour
    'max_attempts' => 3,
    'lockout_time' => 900 // 15 minutes
];

// Check if user is already authenticated
function isAuthenticated() {
    return isset($_SESSION['captive_portal_authenticated']) && 
           $_SESSION['captive_portal_authenticated'] === true &&
           (time() - $_SESSION['captive_portal_login_time']) < $GLOBALS['config']['session_timeout'];
}

// Check if user is locked out
function isLockedOut() {
    if (!isset($_SESSION['captive_portal_attempts'])) {
        return false;
    }
    
    $attempts = $_SESSION['captive_portal_attempts'];
    $lastAttempt = isset($_SESSION['captive_portal_last_attempt']) ? $_SESSION['captive_portal_last_attempt'] : 0;
    
    if ($attempts >= $GLOBALS['config']['max_attempts'] && 
        (time() - $lastAttempt) < $GLOBALS['config']['lockout_time']) {
        return true;
    }
    
    return false;
}

// Handle login attempt
function handleLogin($username, $password) {
    // Reset attempts if lockout time has passed
    if (isset($_SESSION['captive_portal_last_attempt']) && 
        (time() - $_SESSION['captive_portal_last_attempt']) >= $GLOBALS['config']['lockout_time']) {
        $_SESSION['captive_portal_attempts'] = 0;
    }
    
    // Check credentials (replace with your authentication logic)
    if (authenticateUser($username, $password)) {
        $_SESSION['captive_portal_authenticated'] = true;
        $_SESSION['captive_portal_login_time'] = time();
        $_SESSION['captive_portal_username'] = $username;
        $_SESSION['captive_portal_attempts'] = 0;
        return ['success' => true, 'message' => 'Login successful'];
    } else {
        $_SESSION['captive_portal_attempts'] = isset($_SESSION['captive_portal_attempts']) ? 
            $_SESSION['captive_portal_attempts'] + 1 : 1;
        $_SESSION['captive_portal_last_attempt'] = time();
        
        $remainingAttempts = $GLOBALS['config']['max_attempts'] - $_SESSION['captive_portal_attempts'];
        
        if ($remainingAttempts <= 0) {
            return ['success' => false, 'message' => 'Account locked. Please try again in 15 minutes.'];
        } else {
            return ['success' => false, 'message' => "Invalid credentials. {$remainingAttempts} attempts remaining."];
        }
    }
}

// Authenticate user (replace with your database logic)
function authenticateUser($username, $password) {
    // Example authentication - replace with your actual logic
    $validUsers = [
        'guest' => 'guest123',
        'admin' => 'admin123',
        'user' => 'user123'
    ];
    
    return isset($validUsers[$username]) && $validUsers[$username] === $password;
}

// Check if domain is allowed in walled garden
function isAllowedDomain($domain) {
    return in_array($domain, $GLOBALS['config']['allowed_domains']);
}

// Handle logout
function handleLogout() {
    unset($_SESSION['captive_portal_authenticated']);
    unset($_SESSION['captive_portal_login_time']);
    unset($_SESSION['captive_portal_username']);
    session_destroy();
}

// Process form submissions
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                $result = handleLogin($_POST['username'], $_POST['password']);
                break;
            case 'logout':
                handleLogout();
                $result = ['success' => true, 'message' => 'Logged out successfully'];
                break;
        }
    }
}

// Check if this is an API request
if (isset($_GET['api']) && $_GET['api'] === 'check_auth') {
    header('Content-Type: application/json');
    echo json_encode([
        'authenticated' => isAuthenticated(),
        'locked_out' => isLockedOut(),
        'remaining_time' => isAuthenticated() ? 
            $GLOBALS['config']['session_timeout'] - (time() - $_SESSION['captive_portal_login_time']) : 0
    ]);
    exit;
}

// If user is authenticated, redirect to original destination or allow access
if (isAuthenticated()) {
    $redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'http://www.google.com';
    if (!empty($redirect_url)) {
        header("Location: $redirect_url");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['portal_title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .portal-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .portal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        
        .portal-body {
            padding: 3rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .walled-garden-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .allowed-domains {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .domain-badge {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }
        
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .popup-content {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-online { background: #28a745; }
        .status-offline { background: #dc3545; }
    </style>
</head>
<body>
    <!-- Popup Overlay for Authentication -->
    <div id="authPopup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <div class="loading-spinner"></div>
            <h4>Authentication Required</h4>
            <p>Please wait while we verify your credentials...</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="portal-container">
                    <!-- Header -->
                    <div class="portal-header">
                        <div class="mb-3">
                            <i class="fas fa-wifi fa-3x mb-3"></i>
                            <h2><?php echo $config['portal_title']; ?></h2>
                            <p class="mb-0"><?php echo $config['portal_subtitle']; ?></p>
                        </div>
                        <div class="d-flex justify-content-center align-items-center">
                            <span class="status-indicator status-online"></span>
                            <small>Network Status: Online</small>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="portal-body">
                        <?php if (isset($result)): ?>
                            <div class="alert alert-<?php echo $result['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show">
                                <i class="fas fa-<?php echo $result['success'] ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                                <?php echo $result['message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isLockedOut()): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-lock"></i>
                                <strong>Account Locked</strong><br>
                                Too many failed login attempts. Please try again in 15 minutes.
                            </div>
                        <?php else: ?>
                            <!-- Login Form -->
                            <form method="POST" id="loginForm">
                                <input type="hidden" name="action" value="login">
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user"></i> Username
                                    </label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           placeholder="Enter your username" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock"></i> Password
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter your password" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Login to Network
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <!-- Walled Garden Information -->
                        <div class="walled-garden-info">
                            <h5><i class="fas fa-shield-alt"></i> Walled Garden Access</h5>
                            <p class="mb-2">The following services are available without authentication:</p>
                            <div class="allowed-domains">
                                <?php foreach ($config['allowed_domains'] as $domain): ?>
                                    <span class="domain-badge"><?php echo $domain; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Demo Credentials -->
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>Demo Credentials:</strong><br>
                                Username: guest | Password: guest123<br>
                                Username: admin | Password: admin123
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-redirect detection
        function checkAuthStatus() {
            fetch('?api=check_auth')
                .then(response => response.json())
                .then(data => {
                    if (data.authenticated) {
                        // User is authenticated, redirect to original destination
                        const redirectUrl = new URLSearchParams(window.location.search).get('redirect');
                        if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else {
                            window.location.href = 'http://www.google.com';
                        }
                    }
                })
                .catch(error => console.error('Auth check failed:', error));
        }

        // Check auth status every 30 seconds
        setInterval(checkAuthStatus, 30000);

        // Show popup on form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            document.getElementById('authPopup').style.display = 'flex';
        });

        // Auto-hide popup after 3 seconds (simulating auth process)
        setTimeout(() => {
            document.getElementById('authPopup').style.display = 'none';
        }, 3000);

        // Prevent access to external sites without authentication
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' && e.target.href) {
                const url = new URL(e.target.href);
                const domain = url.hostname.replace('www.', '');
                
                // Check if domain is in allowed list
                const allowedDomains = <?php echo json_encode($config['allowed_domains']); ?>;
                if (!allowedDomains.includes(domain)) {
                    e.preventDefault();
                    alert('Please login to access this website.');
                }
            }
        });
    </script>
</body>
</html> 