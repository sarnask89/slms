# 🔐 Authentication & Security Modules

## Overview
The Authentication & Security modules provide comprehensive user authentication, authorization, and security features for the AI SERVICE NETWORK MANAGEMENT SYSTEM.

---

## 📋 Available Modules

### 1. **Login Module** (`login.php`)
Advanced authentication system with session management and security features.

#### Features
- ✅ Secure password authentication
- ✅ Session management with auto-timeout
- ✅ Remember me functionality
- ✅ Brute force protection
- ✅ CSRF token validation
- ✅ SSL/TLS support

#### Installation
```bash
# No additional installation required - core module
# Ensure database tables exist
php modules/setup_auth_tables.php
```

#### Configuration
```php
// config/auth.php
return [
    'session_timeout' => 3600,        // 1 hour
    'remember_me_duration' => 604800, // 7 days
    'max_login_attempts' => 5,
    'lockout_duration' => 900,        // 15 minutes
    'password_min_length' => 8,
    'require_ssl' => true
];
```

#### Usage
```php
// Direct access
https://yourdomain.com/modules/login.php

// Programmatic login
require_once 'modules/helpers/auth_helper.php';
if (authenticate($username, $password)) {
    // User logged in
}
```

---

### 2. **User Management Module** (`user_management.php`)
Complete user administration system with role-based access control.

#### Features
- ✅ User CRUD operations
- ✅ Role assignment (Admin, Manager, User, Viewer)
- ✅ Password policies
- ✅ Account activation/deactivation
- ✅ Bulk user import
- ✅ User search and filtering

#### Installation
```bash
# Create user tables
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'manager', 'user', 'viewer') DEFAULT 'user',
    status ENUM('active', 'inactive', 'locked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);
```

#### Configuration
```php
// config/users.php
return [
    'default_role' => 'user',
    'auto_activate' => false,
    'email_verification' => true,
    'password_complexity' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true
    ]
];
```

#### API Reference
```php
// Create user
createUser($userData);

// Update user
updateUser($userId, $userData);

// Delete user
deleteUser($userId);

// Get user by ID
getUser($userId);

// List users with pagination
getUsers($page, $perPage, $filters);
```

---

### 3. **Access Level Manager** (`access_level_manager.php`)
Granular permission management system for fine-tuned access control.

#### Features
- ✅ Module-level permissions
- ✅ Custom permission groups
- ✅ Permission inheritance
- ✅ Dynamic permission checking
- ✅ Permission templates
- ✅ Audit trail

#### Installation
```bash
# Create permissions tables
php modules/create_permissions_tables.php

# Import default permissions
php modules/import_default_permissions.php
```

#### Configuration
```php
// config/permissions.php
return [
    'cache_permissions' => true,
    'cache_duration' => 3600,
    'default_permissions' => [
        'viewer' => ['view_dashboard', 'view_reports'],
        'user' => ['view_dashboard', 'view_reports', 'manage_own_data'],
        'manager' => ['all_user_permissions', 'manage_clients', 'manage_devices'],
        'admin' => ['*'] // All permissions
    ]
];
```

#### Usage Example
```php
// Check permission
if (hasPermission('manage_users')) {
    // Allow access
}

// Check multiple permissions
if (hasAnyPermission(['view_reports', 'manage_reports'])) {
    // Allow access
}

// Assign permission to role
assignPermissionToRole('manage_devices', 'manager');
```

---

### 4. **Activity Log Module** (`activity_log.php`)
Comprehensive logging system for all user activities and system events.

#### Features
- ✅ Automatic activity tracking
- ✅ Custom event logging
- ✅ Search and filtering
- ✅ Export capabilities
- ✅ Real-time monitoring
- ✅ Configurable retention

#### Installation
```bash
# Create activity log table
CREATE TABLE activity_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100),
    module VARCHAR(50),
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);
```

#### Configuration
```php
// config/activity_log.php
return [
    'enabled' => true,
    'log_level' => 'info', // debug, info, warning, error
    'retention_days' => 90,
    'excluded_actions' => ['heartbeat', 'check_status'],
    'anonymize_ip' => false,
    'real_time_monitoring' => true
];
```

#### Usage
```php
// Log custom activity
logActivity('user_login', [
    'username' => $username,
    'ip' => $_SERVER['REMOTE_ADDR']
]);

// Query logs
$logs = getActivityLogs([
    'user_id' => 123,
    'action' => 'user_login',
    'date_from' => '2025-01-01',
    'date_to' => '2025-01-31'
]);
```

---

### 5. **Session Management** (`helpers/auth_helper.php`)
Advanced session handling with security features.

#### Features
- ✅ Secure session storage
- ✅ Session fingerprinting
- ✅ Concurrent session control
- ✅ Session timeout management
- ✅ Remember me tokens
- ✅ Session hijacking protection

#### Configuration
```php
// config/session.php
return [
    'handler' => 'database', // file, database, redis
    'lifetime' => 120, // minutes
    'expire_on_close' => false,
    'encrypt' => true,
    'regenerate_id' => true,
    'same_site' => 'lax',
    'secure' => true, // HTTPS only
    'http_only' => true
];
```

#### Advanced Usage
```php
// Start secure session
startSecureSession();

// Check session validity
if (isSessionValid()) {
    // Continue
}

// Destroy all user sessions
destroyAllUserSessions($userId);

// Get active sessions
$sessions = getActiveSessions($userId);
```

---

## 🔒 Security Best Practices

### Password Security
```php
// Password hashing
$hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

// Password verification
if (password_verify($password, $hashedPassword)) {
    // Password correct
}

// Force password change
forcePasswordChange($userId);
```

### Two-Factor Authentication (Optional)
```php
// Enable 2FA
enable2FA($userId);

// Generate QR code
$qrCode = generate2FAQRCode($userId);

// Verify 2FA token
if (verify2FAToken($userId, $token)) {
    // Token valid
}
```

### IP Whitelisting
```php
// config/security.php
return [
    'ip_whitelist_enabled' => true,
    'allowed_ips' => [
        '192.168.1.0/24',
        '10.0.0.0/8'
    ],
    'whitelist_bypass_roles' => ['admin']
];
```

---

## 🚨 Troubleshooting

### Common Issues

#### 1. Login Loop
**Problem**: User keeps getting redirected to login page
```bash
# Check session directory permissions
ls -la /var/lib/php/sessions/

# Fix permissions
sudo chown -R www-data:www-data /var/lib/php/sessions/
```

#### 2. Session Timeout Too Short
```php
// Increase session timeout in php.ini
session.gc_maxlifetime = 3600
```

#### 3. CSRF Token Mismatch
```php
// Regenerate CSRF token
session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

---

## 📊 Performance Optimization

### Database Indexes
```sql
-- Optimize user queries
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_status ON users(status);

-- Optimize session queries
CREATE INDEX idx_session_user ON sessions(user_id);
CREATE INDEX idx_session_expire ON sessions(expires_at);
```

### Caching
```php
// Enable user caching
$cache->set("user_{$userId}", $userData, 3600);

// Cache permissions
$cache->set("permissions_{$roleId}", $permissions, 86400);
```

---

## 🔄 Migration Guide

### From Basic Auth to Advanced
```bash
# 1. Backup existing users
mysqldump -u root -p slmsdb users > users_backup.sql

# 2. Run migration script
php modules/migrate_auth_system.php

# 3. Update configuration
cp config/auth.example.php config/auth.php

# 4. Test authentication
php modules/test_auth.php
```

---

## 📈 Monitoring

### Key Metrics
- Failed login attempts
- Average session duration
- Concurrent users
- Permission check performance
- Password reset requests

### Alerts
```php
// Set up alerts
setAlert('failed_login_threshold', 10, 'email');
setAlert('concurrent_sessions_max', 1000, 'slack');
```

---

## 🔗 Related Modules
- [User Profile Module](../user-guide/user-profile.md)
- [API Authentication](../api-integration/api-auth.md)
- [LDAP Integration](../integration/ldap.md)

---

**Module Version**: 2.0.0  
**Last Updated**: January 2025  
**Maintainer**: Security Team