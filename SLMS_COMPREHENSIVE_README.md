# sLMS (Service Level Management System) - Comprehensive Documentation

## 🏗️ System Overview

sLMS is a comprehensive network management and service delivery platform that combines traditional network administration with advanced machine learning capabilities. The system provides end-to-end management of network infrastructure, client services, billing, and predictive analytics.

## 🚀 Core Features

### 1. Network Management
- **Device Management**: Complete lifecycle management of network devices (MikroTik, Cisco, etc.)
- **DHCP Management**: Automated DHCP lease and network management
- **Network Monitoring**: Real-time monitoring with SNMP and custom protocols
- **Bridge/NAT Configuration**: Advanced network configuration and optimization
- **Interface Monitoring**: Detailed interface statistics and performance tracking

### 2. Client Management
- **Client Database**: Comprehensive client information and service tracking
- **Device Assignment**: Link clients to network devices and services
- **Service Management**: Internet packages, TV packages, and custom services
- **Billing Integration**: Invoice generation and payment tracking

### 3. Machine Learning System
- **Predictive Analytics**: Network traffic prediction and anomaly detection
- **Performance Optimization**: ML-driven capacity planning and resource optimization
- **Model Management**: Complete ML model lifecycle management
- **Real-time Predictions**: Live network analysis and threat detection

### 4. User Management & Security
- **Role-based Access Control**: Granular permissions and access levels
- **User Profiles**: Comprehensive user management and profile customization
- **Authentication System**: Secure login and session management
- **Audit Logging**: Complete system activity tracking

### 5. System Administration
- **Dashboard Management**: Customizable dashboards and layouts
- **System Monitoring**: Health checks and performance monitoring
- **Configuration Management**: Centralized system configuration
- **Backup & Recovery**: Automated backup and restore capabilities

## 📋 System Requirements

### Minimum Requirements
- **OS**: Linux (Debian/Ubuntu recommended)
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.2 or higher
- **Apache**: 2.4 or higher
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 20GB minimum, 50GB recommended

### Recommended Requirements
- **OS**: Debian 12 or Ubuntu 22.04 LTS
- **PHP**: 8.2 with OPcache enabled
- **MySQL**: 8.0 or MariaDB 10.11
- **Apache**: 2.4 with mod_rewrite
- **RAM**: 16GB or higher
- **Storage**: SSD with 100GB+ available space

## 🛠️ Installation Guide

### 1. System Preparation
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install apache2 mysql-server php8.2 php8.2-mysql php8.2-curl php8.2-json php8.2-mbstring php8.2-xml php8.2-zip php8.2-opcache php8.2-snmp -y

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

### 2. Database Setup
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
CREATE DATABASE slmsdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'slms'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON slmsdb.* TO 'slms'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Application Installation
```bash
# Clone or copy sLMS files
sudo cp -r /home/sarna/tmpwww/ml_system_modules_tmp/* /var/www/html/

# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
sudo chmod -R 777 /var/www/html/cache/
sudo chmod -R 777 /var/www/html/logs/
sudo chmod -R 777 /var/www/html/uploads/
```

### 4. Database Schema Import
```bash
# Import main schema
mysql -u slms -p slmsdb < /var/www/html/sql/slms_full_schema.sql

# Import ML system schema
mysql -u slms -p slmsdb < /var/www/html/modules/ml_sql_schema.sql
```

### 5. Configuration
```bash
# Edit configuration file
sudo nano /var/www/html/config.php

# Update database credentials and system settings
```

## 🏗️ System Architecture

### Directory Structure
```
/var/www/html/
├── api/                          # API endpoints
├── assets/                       # Static assets (CSS, JS, images)
├── cache/                        # System cache
├── config.php                    # Main configuration
├── docs/                         # Documentation
├── home/                         # User home directories
├── logs/                         # System logs
├── modules/                      # Core modules
│   ├── clients.php              # Client management
│   ├── devices.php              # Device management
│   ├── networks.php             # Network management
│   ├── users.php                # User management
│   ├── ml_model_manager.php     # ML system
│   └── ...                      # Other modules
├── partials/                     # Reusable UI components
├── sql/                          # Database schemas
├── themes/                       # UI themes
└── uploads/                      # File uploads
```

### Database Schema Overview
- **Core Tables**: clients, devices, networks, users, services
- **ML Tables**: ml_models, ml_predictions, ml_training_sessions
- **System Tables**: access_levels, permissions, audit_logs
- **Configuration Tables**: system_config, module_config

## 🎯 Module Documentation

### 1. Client Management (`modules/clients.php`)
**Purpose**: Manage client information, services, and billing
**Features**:
- Add/edit/delete clients
- Assign services and devices
- Track billing and payments
- Generate reports

**Key Functions**:
```php
// Add new client
addClient($name, $email, $phone, $address, $package_id);

// Update client information
updateClient($client_id, $data);

// Get client details
getClient($client_id);

// List all clients
getAllClients($filters = []);
```

### 2. Device Management (`modules/devices.php`)
**Purpose**: Manage network devices and their configurations
**Features**:
- Device discovery and inventory
- Configuration management
- Performance monitoring
- SNMP integration

**Key Functions**:
```php
// Add new device
addDevice($name, $ip, $type, $credentials);

// Check device connectivity
checkDevice($device_id);

// Get device statistics
getDeviceStats($device_id);

// Update device configuration
updateDeviceConfig($device_id, $config);
```

### 3. Network Management (`modules/networks.php`)
**Purpose**: Manage network infrastructure and configurations
**Features**:
- Network topology management
- DHCP configuration
- VLAN management
- Traffic monitoring

**Key Functions**:
```php
// Create network
createNetwork($name, $subnet, $gateway);

// Configure DHCP
configureDHCP($network_id, $config);

// Monitor traffic
getTrafficStats($network_id);

// Update network settings
updateNetwork($network_id, $settings);
```

### 4. ML Model Manager (`modules/ml_model_manager.php`)
**Purpose**: Manage machine learning models and predictions
**Features**:
- Model creation and training
- Real-time predictions
- Performance monitoring
- Automated retraining

**Key Functions**:
```php
// Create new model
createModel($name, $type, $parameters);

// Train model
trainModel($model_id, $training_data);

// Make prediction
predict($model_id, $input_data);

// Get model performance
getModelPerformance($model_id);
```

### 5. User Management (`modules/users.php`)
**Purpose**: Manage system users and access control
**Features**:
- User account management
- Role-based permissions
- Access level control
- Audit logging

**Key Functions**:
```php
// Create user
createUser($username, $email, $role);

// Update permissions
updateUserPermissions($user_id, $permissions);

// Check access
checkAccess($user_id, $resource);

// Log activity
logActivity($user_id, $action, $details);
```

## 🔌 API Reference

### Authentication
All API endpoints require authentication via session or API key.

### Core Endpoints

#### Client Management
```
GET    /api/clients              # List all clients
POST   /api/clients              # Create new client
GET    /api/clients/{id}         # Get client details
PUT    /api/clients/{id}         # Update client
DELETE /api/clients/{id}         # Delete client
```

#### Device Management
```
GET    /api/devices              # List all devices
POST   /api/devices              # Add new device
GET    /api/devices/{id}         # Get device details
PUT    /api/devices/{id}         # Update device
DELETE /api/devices/{id}         # Delete device
GET    /api/devices/{id}/status  # Get device status
```

#### Network Management
```
GET    /api/networks             # List all networks
POST   /api/networks             # Create network
GET    /api/networks/{id}        # Get network details
PUT    /api/networks/{id}        # Update network
DELETE /api/networks/{id}        # Delete network
GET    /api/networks/{id}/dhcp   # Get DHCP info
```

#### ML System
```
GET    /api/ml/models            # List all models
POST   /api/ml/models            # Create model
GET    /api/ml/models/{id}       # Get model details
POST   /api/ml/models/{id}/train # Train model
POST   /api/ml/models/{id}/predict # Make prediction
GET    /api/ml/predictions       # Get predictions
```

### Response Format
```json
{
    "success": true,
    "data": {...},
    "message": "Operation completed successfully",
    "timestamp": "2024-01-01T00:00:00Z"
}
```

## 🎨 User Interface

### Dashboard Features
- **Overview Widgets**: System status, active clients, device health
- **Quick Actions**: Common tasks accessible from dashboard
- **Real-time Monitoring**: Live network and system metrics
- **Customizable Layout**: Drag-and-drop widget arrangement

### Navigation Structure
```
Dashboard
├── Clients
│   ├── All Clients
│   ├── Add Client
│   ├── Client Reports
│   └── Billing
├── Devices
│   ├── All Devices
│   ├── Add Device
│   ├── Device Monitoring
│   └── Configuration
├── Networks
│   ├── Network Overview
│   ├── DHCP Management
│   ├── Traffic Analysis
│   └── Configuration
├── ML System
│   ├── Model Manager
│   ├── Training Jobs
│   ├── Predictions
│   └── Performance
├── Users
│   ├── User Management
│   ├── Access Control
│   ├── Audit Logs
│   └── Permissions
└── System
    ├── Configuration
    ├── Backup & Restore
    ├── System Health
    └── Logs
```

## 🔧 Configuration

### Main Configuration (`config.php`)
```php
<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'slmsdb';
$db_user = 'slms';
$db_pass = 'your_password';

// System settings
$system_name = 'sLMS';
$system_url = 'http://your-domain.com';
$timezone = 'UTC';

// ML system settings
$ml_enabled = true;
$ml_max_training_jobs = 5;
$ml_prediction_batch_size = 100;

// Security settings
$session_timeout = 3600;
$max_login_attempts = 5;
$password_min_length = 8;
?>
```

### Module Configuration
Each module can have its own configuration file:
- `modules/ml_config.php` - ML system settings
- `modules/network_config.php` - Network settings
- `modules/security_config.php` - Security settings

## 🔍 Monitoring and Maintenance

### System Health Monitoring
- **Database Performance**: Query optimization and connection monitoring
- **Application Performance**: Response time and resource usage
- **Network Monitoring**: Device connectivity and traffic analysis
- **Security Monitoring**: Access logs and threat detection

### Backup Strategy
```bash
# Database backup
mysqldump -u slms -p slmsdb > backup_$(date +%Y%m%d_%H%M%S).sql

# File backup
tar -czf sLMS_backup_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/html/

# Automated backup script
#!/bin/bash
BACKUP_DIR="/backup/slms"
DATE=$(date +%Y%m%d_%H%M%S)

# Database backup
mysqldump -u slms -p slmsdb > $BACKUP_DIR/db_backup_$DATE.sql

# File backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/html/

# Clean old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

### Performance Optimization
- **OPcache Configuration**: PHP bytecode caching
- **Database Indexing**: Optimized queries and indexes
- **Caching Strategy**: Application-level caching
- **CDN Integration**: Static asset delivery optimization

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Issues
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u slms -p -e "SELECT 1;"

# Check MySQL logs
sudo tail -f /var/log/mysql/error.log
```

#### Apache Configuration Issues
```bash
# Check Apache status
sudo systemctl status apache2

# Test configuration
sudo apache2ctl configtest

# Check error logs
sudo tail -f /var/log/apache2/error.log
```

#### PHP Issues
```bash
# Check PHP version
php -v

# Check PHP modules
php -m

# Test PHP configuration
php -r "phpinfo();"
```

#### Permission Issues
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
sudo chmod -R 777 /var/www/html/cache/
sudo chmod -R 777 /var/www/html/logs/
sudo chmod -R 777 /var/www/html/uploads/
```

### Debug Mode
Enable debug mode in `config.php`:
```php
$debug_mode = true;
$log_level = 'DEBUG';
```

## 🔒 Security Considerations

### Access Control
- **Role-based Permissions**: Granular access control
- **Session Management**: Secure session handling
- **Input Validation**: SQL injection and XSS prevention
- **CSRF Protection**: Cross-site request forgery protection

### Data Protection
- **Encryption**: Sensitive data encryption
- **Backup Security**: Encrypted backups
- **Audit Logging**: Complete activity tracking
- **Data Retention**: Configurable data retention policies

### Network Security
- **HTTPS**: SSL/TLS encryption
- **Firewall Configuration**: Network access control
- **VPN Access**: Secure remote access
- **Intrusion Detection**: Security monitoring

## 📊 Reporting and Analytics

### Built-in Reports
- **Client Reports**: Service usage, billing, device assignments
- **Network Reports**: Traffic analysis, performance metrics
- **System Reports**: Health status, resource usage
- **ML Reports**: Model performance, prediction accuracy

### Custom Reporting
- **Report Builder**: Drag-and-drop report creation
- **Data Export**: CSV, PDF, Excel export capabilities
- **Scheduled Reports**: Automated report generation
- **Dashboard Widgets**: Customizable analytics widgets

## 🔮 Future Enhancements

### Planned Features
- **Mobile Application**: Native mobile app for field operations
- **API Enhancements**: GraphQL API and webhook support
- **Advanced Analytics**: Business intelligence and data visualization
- **Cloud Integration**: Multi-cloud deployment support
- **IoT Integration**: Internet of Things device management
- **AI/ML Enhancements**: Advanced machine learning capabilities

### Technology Roadmap
- **Microservices Architecture**: Service-oriented design
- **Container Deployment**: Docker and Kubernetes support
- **Real-time Processing**: Event-driven architecture
- **Scalability Improvements**: Horizontal scaling capabilities

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create feature branch
3. Make changes with proper testing
4. Submit pull request
5. Code review and merge

### Coding Standards
- **PHP**: PSR-12 coding standards
- **JavaScript**: ESLint configuration
- **CSS**: BEM methodology
- **Documentation**: Comprehensive inline documentation

### Testing
- **Unit Tests**: PHPUnit for backend testing
- **Integration Tests**: API and database testing
- **UI Tests**: Selenium for frontend testing
- **Performance Tests**: Load testing and optimization

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

### Documentation
- **User Manual**: Complete user guide
- **API Documentation**: RESTful API reference
- **Developer Guide**: Technical documentation
- **Video Tutorials**: Step-by-step guides

### Community Support
- **GitHub Issues**: Bug reports and feature requests
- **Discussion Forum**: Community support
- **Email Support**: Direct support contact
- **Professional Services**: Custom development and consulting

### Contact Information
- **Email**: support@slms.com
- **Website**: https://slms.com
- **Documentation**: https://docs.slms.com
- **GitHub**: https://github.com/slms/slms

---

**Version**: 2.0  
**Last Updated**: 2024  
**Author**: sLMS Development Team  
**License**: MIT License 