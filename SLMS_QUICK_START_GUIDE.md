# sLMS Quick Start Guide

## 🚀 Get Started in 5 Minutes

### 1. System Requirements
- Linux server (Debian/Ubuntu recommended)
- PHP 8.0+, MySQL 5.7+, Apache 2.4+
- 4GB RAM minimum, 8GB recommended

### 2. Quick Installation
```bash
# Install dependencies
sudo apt update && sudo apt install apache2 mysql-server php8.2 php8.2-mysql php8.2-curl php8.2-json -y

# Copy sLMS files
sudo cp -r /home/sarna/tmpwww/ml_system_modules_tmp/* /var/www/html/

# Set permissions
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
sudo chmod -R 777 /var/www/html/cache/ /var/www/html/logs/ /var/www/html/uploads/

# Create database
sudo mysql -u root -p -e "CREATE DATABASE slmsdb; CREATE USER 'slms'@'localhost' IDENTIFIED BY 'password'; GRANT ALL ON slmsdb.* TO 'slms'@'localhost';"

# Import schema
mysql -u slms -p slmsdb < /var/www/html/sql/slms_full_schema.sql
mysql -u slms -p slmsdb < /var/www/html/modules/ml_sql_schema.sql
```

### 3. Configuration
Edit `/var/www/html/config.php`:
```php
$db_host = 'localhost';
$db_name = 'slmsdb';
$db_user = 'slms';
$db_pass = 'your_password';
```

### 4. Access the System
- Open browser: `http://your-server-ip/`
- Default login: `admin` / `admin123`
- Change password immediately!

## 🎯 Core Features Overview

### Network Management
- **Devices**: Add MikroTik, Cisco, and other network devices
- **DHCP**: Automatic DHCP lease management
- **Monitoring**: Real-time network monitoring
- **Configuration**: Bridge/NAT and interface management

### Client Management
- **Clients**: Add and manage client information
- **Services**: Internet packages, TV packages, custom services
- **Billing**: Invoice generation and payment tracking
- **Devices**: Assign devices to clients

### Machine Learning
- **Models**: Create and train ML models
- **Predictions**: Real-time network analysis
- **Monitoring**: Performance tracking and drift detection
- **Automation**: Automated retraining and optimization

### User Management
- **Users**: Create and manage user accounts
- **Permissions**: Role-based access control
- **Security**: Secure authentication and session management
- **Audit**: Complete activity logging

## 🔧 Essential Configuration

### Database Setup
```sql
-- Create main tables
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    type VARCHAR(50),
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active'
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255),
    role ENUM('admin', 'manager', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/slms_error.log
    CustomLog ${APACHE_LOG_DIR}/slms_access.log combined
</VirtualHost>
```

## 📊 Quick Dashboard Setup

### Essential Widgets
1. **System Status**: Overall system health
2. **Active Clients**: Number of active clients
3. **Device Health**: Network device status
4. **Recent Activity**: Latest system activities
5. **ML Predictions**: Recent ML model predictions

### Quick Actions
- Add new client
- Add new device
- Create ML model
- Generate report
- System backup

## 🔌 API Quick Reference

### Authentication
```bash
# Login and get session
curl -X POST http://your-domain/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'
```

### Common Endpoints
```bash
# Get all clients
curl http://your-domain/api/clients

# Get all devices
curl http://your-domain/api/devices

# Create new client
curl -X POST http://your-domain/api/clients \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com"}'

# Make ML prediction
curl -X POST http://your-domain/api/ml/predict \
  -H "Content-Type: application/json" \
  -d '{"model_id":1,"input_data":{"feature1":100,"feature2":50}}'
```

## 🐛 Quick Troubleshooting

### Common Issues

#### Can't Access Web Interface
```bash
# Check Apache status
sudo systemctl status apache2

# Check permissions
sudo chown -R www-data:www-data /var/www/html/

# Check error logs
sudo tail -f /var/log/apache2/error.log
```

#### Database Connection Error
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u slms -p -e "SELECT 1;"

# Check credentials in config.php
```

#### Permission Denied
```bash
# Fix file permissions
sudo chmod -R 755 /var/www/html/
sudo chmod -R 777 /var/www/html/cache/
sudo chmod -R 777 /var/www/html/logs/
sudo chmod -R 777 /var/www/html/uploads/
```

### Performance Issues
```bash
# Enable OPcache
sudo apt install php8.2-opcache
sudo systemctl restart apache2

# Check PHP memory limit
php -r "echo ini_get('memory_limit');"

# Optimize MySQL
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

## 📈 Basic Monitoring Setup

### System Health Check
```bash
# Create health check script
cat > /var/www/html/health_check.php << 'EOF'
<?php
$checks = [
    'database' => function() {
        try {
            include 'config.php';
            $pdo = get_pdo();
            $pdo->query('SELECT 1');
            return true;
        } catch (Exception $e) {
            return false;
        }
    },
    'filesystem' => function() {
        return is_writable('/var/www/html/cache/') && 
               is_writable('/var/www/html/logs/');
    },
    'apache' => function() {
        return file_get_contents('http://localhost/') !== false;
    }
];

$results = [];
foreach ($checks as $name => $check) {
    $results[$name] = $check();
}

header('Content-Type: application/json');
echo json_encode($results);
EOF
```

### Automated Backup
```bash
# Create backup script
cat > /home/sarna/backup_slms.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/backup/slms"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u slms -p slmsdb > $BACKUP_DIR/db_backup_$DATE.sql

# File backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/html/

# Clean old backups (keep 7 days)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
EOF

chmod +x /home/sarna/backup_slms.sh

# Add to crontab (daily backup at 2 AM)
echo "0 2 * * * /home/sarna/backup_slms.sh" | crontab -
```

## 🎯 Next Steps

### 1. Add Your First Client
1. Go to Clients → Add Client
2. Fill in client information
3. Assign services and devices
4. Generate first invoice

### 2. Add Network Devices
1. Go to Devices → Add Device
2. Enter device details (IP, credentials)
3. Test connectivity
4. Configure monitoring

### 3. Set Up ML Models
1. Go to ML System → Model Manager
2. Create new model
3. Configure training data
4. Train and deploy model

### 4. Configure Monitoring
1. Set up SNMP on devices
2. Configure monitoring intervals
3. Set up alerts and notifications
4. Create custom dashboards

## 📞 Support

### Quick Help
- **Documentation**: `/docs/` directory
- **Logs**: `/logs/` directory
- **API**: `/api/` endpoints
- **Health Check**: `/health_check.php`

### Contact
- **Email**: support@slms.com
- **Documentation**: https://docs.slms.com
- **GitHub**: https://github.com/slms/slms

---

**Version**: 2.0  
**Last Updated**: 2024  
**For full documentation**: See SLMS_COMPREHENSIVE_README.md 