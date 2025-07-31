<?php
// Temporary Authentication Disabled
// This file temporarily disables password authentication for maintenance

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    // User is already logged in, redirect to main page
    header('Location: ../index.php');
    exit;
}

// Handle any existing logout requests
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI SERVICE NETWORK MANAGEMENT SYSTEM - Authentication Temporarily Disabled</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .maintenance-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .maintenance-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .status {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .admin-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .back-link {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .back-link:hover {
            background: #0056b3;
        }
        .timestamp {
            font-size: 12px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">🔒</div>
        <h1>Authentication Temporarily Disabled</h1>
        
        <div class="status">
            <strong>Status:</strong> Password authentication is currently disabled for maintenance
        </div>
        
        <div class="message">
            <p>We are currently performing system maintenance and have temporarily disabled password authentication for security purposes.</p>
            <p>This is a precautionary measure to ensure system security during maintenance operations.</p>
        </div>
        
        <div class="admin-info">
            <strong>For Administrators:</strong><br>
            To re-enable authentication, edit the file:<br>
            <code>/var/www/html/slms/modules/login.php</code><br>
            And restore the original login functionality.
        </div>
        
        <a href="../index.php" class="back-link">Return to Main Page</a>
        
        <div class="timestamp">
            Disabled on: <?php echo date('Y-m-d H:i:s'); ?><br>
            Server: <?php echo $_SERVER['SERVER_NAME'] ?? 'sLMS'; ?>
        </div>
    </div>
</body>
</html> 