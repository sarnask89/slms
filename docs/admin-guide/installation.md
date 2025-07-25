# 🚀 Installation Guide

## Overview

This guide provides step-by-step instructions for installing and configuring the sLMS (Service and License Management System) on your server.

## 📋 Prerequisites

### System Requirements

- **Operating System**: Linux (Debian/Ubuntu recommended), Windows Server, or macOS
- **PHP**: Version 8.0 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: Minimum 2GB RAM (4GB recommended)
- **Storage**: Minimum 10GB free space
- **Network**: Internet connection for updates and external integrations

### Required PHP Extensions

```bash
# Core extensions
php-pdo
php-pdo-mysql
php-json
php-curl
php-mbstring
php-xml
php-zip

# Optional but recommended
php-snmp
php-gd
php-opcache
php-redis
```

### Server Software

- **Apache** with mod_rewrite enabled
- **MySQL/MariaDB** server
- **Composer** (for dependency management)
- **Git** (for version control)

## 🔧 Installation Steps

### Step 1: Server Preparation

#### Update System Packages

```bash
# Debian/Ubuntu
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo yum update -y

# Install required packages
sudo apt install apache2 mysql-server php php-mysql php-curl php-json php-mbstring php-xml php-zip php-snmp git composer -y
```

#### Configure Apache

```bash
# Enable required modules
sudo a2enmod rewrite
sudo a2enmod ssl

# Restart Apache
sudo systemctl restart apache2
```

#### Configure MySQL

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE slms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'slms_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON slms.* TO 'slms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 2: Download and Extract sLMS

#### Clone Repository

```bash
# Navigate to web directory
cd /var/www/html

# Clone the repository
sudo git clone https://github.com/sarnask8/slms
sudo chown -R www-data:www-data slms
sudo chmod -R 755 slms
```

#### Alternative: Download ZIP

```bash
# Download and extract
wget https://github.com/your-org/slms/archive/main.zip
unzip main.zip
mv slms-main slms
sudo chown -R www-data:www-data slms
sudo chmod -R 755 slms
```

### Step 3: Configure sLMS

#### Create Configuration File

```bash
cd /var/www/html/slms
cp config.example.php config.php
```

#### Edit Configuration

```php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'slms');
define('DB_USER', 'slms_user');
define('DB_PASS', 'your_secure_password');

// Application settings
define('APP_NAME', 'sLMS');
define('APP_URL', 'http://your-domain.com');
define('APP_ENV', 'production'); // or 'development'

// Security settings
define('SECRET_KEY', 'your-secret-key-here');
define('SESSION_TIMEOUT', 3600);

// File upload settings
define('UPLOAD_MAX_SIZE', 10485760); // 10MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Email settings
define('SMTP_HOST', 'smtp.your-domain.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@your-domain.com');
define('SMTP_PASS', 'your-smtp-password');
define('SMTP_SECURE', 'tls');

// Monitoring settings
define('SNMP_COMMUNITY', 'public');
define('SNMP_TIMEOUT', 1000000);
define('SNMP_RETRIES', 3);

// Cacti integration
define('CACTI_URL', 'http://cacti.your-domain.com');
define('CACTI_USER', 'admin');
define('CACTI_PASS', 'your-cacti-password');
?>
```

### Step 4: Database Setup

#### Run Database Initialization

```bash
cd /var/www/html/slms
php lms_db_init.php
```

This script will:
- Create all necessary database tables
- Insert default data
- Set up initial user accounts
- Configure access levels

#### Verify Database Setup

```bash
# Check database tables
mysql -u slms_user -p slms -e "SHOW TABLES;"

# Check default users
mysql -u slms_user -p slms -e "SELECT username, role FROM users;"
```

### Step 5: Web Server Configuration

#### Apache Virtual Host

Create `/etc/apache2/sites-available/slms.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/html/slms
    
    <Directory /var/www/html/slms>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/slms_error.log
    CustomLog ${APACHE_LOG_DIR}/slms_access.log combined
</VirtualHost>
```

#### Enable Site

```bash
sudo a2ensite slms.conf
sudo systemctl reload apache2
```

#### Nginx Configuration (Alternative)

Create `/etc/nginx/sites-available/slms`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/html/slms;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### Step 6: Security Configuration

#### Set File Permissions

```bash
# Set proper permissions
sudo find /var/www/html/slms -type f -exec chmod 644 {} \;
sudo find /var/www/html/slms -type d -exec chmod 755 {} \;

# Make specific directories writable
sudo chmod -R 775 /var/www/html/slms/logs
sudo chmod -R 775 /var/www/html/slms/uploads
sudo chmod -R 775 /var/www/html/slms/cache

# Set ownership
sudo chown -R www-data:www-data /var/www/html/slms
```

#### Configure Firewall

```bash
# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow SSH (if not already allowed)
sudo ufw allow ssh

# Enable firewall
sudo ufw enable
```

#### SSL Certificate (Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Obtain SSL certificate
sudo certbot --apache -d your-domain.com -d www.your-domain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### Step 7: Initial Setup

#### Access the Application

1. Open your web browser
2. Navigate to `http://your-domain.com`
3. You should see the sLMS login page

#### Default Credentials

- **Username**: `admin`
- **Password**: `admin123`

⚠️ **Important**: Change the default password immediately after first login!

#### Complete Initial Setup

1. **Change Admin Password**
   - Go to User Profile
   - Click "Change Password"
   - Set a strong password

2. **Configure System Settings**
   - Navigate to System Administration
   - Configure basic settings
   - Set up email notifications

3. **Create Initial Data**
   - Add your first client
   - Configure network settings
   - Set up service packages

## 🔍 Post-Installation Verification

### System Health Check

```bash
cd /var/www/html/slms
php system_health_checker.php
```

### Database Connection Test

```bash
php -r "
require_once 'config.php';
try {
    \$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    echo 'Database connection: SUCCESS\n';
} catch (PDOException \$e) {
    echo 'Database connection: FAILED - ' . \$e->getMessage() . '\n';
}
"
```

### Web Server Test

```bash
# Test Apache configuration
sudo apache2ctl configtest

# Check Apache status
sudo systemctl status apache2

# Test PHP
php -v
```

## 🚨 Troubleshooting

### Common Issues

#### Database Connection Failed

```bash
# Check MySQL service
sudo systemctl status mysql

# Check database credentials
mysql -u slms_user -p -e "SELECT 1;"

# Check database exists
mysql -u root -p -e "SHOW DATABASES;"
```

#### Permission Denied

```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/html/slms
sudo chmod -R 755 /var/www/html/slms
sudo chmod -R 775 /var/www/html/slms/logs
```

#### Apache 500 Error

```bash
# Check Apache error logs
sudo tail -f /var/log/apache2/error.log

# Check PHP error logs
sudo tail -f /var/log/php8.0-fpm.log
```

#### SSL Certificate Issues

```bash
# Check certificate status
sudo certbot certificates

# Renew certificate manually
sudo certbot renew --dry-run
```

### Performance Optimization

#### Enable OPcache

```bash
# Install OPcache
sudo apt install php-opcache

# Configure OPcache
sudo nano /etc/php/8.0/apache2/conf.d/10-opcache.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

#### Configure MySQL

```bash
# Edit MySQL configuration
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 64M
query_cache_type = 1
```

## 📊 Monitoring Setup

### System Monitoring

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs

# Set up log rotation
sudo nano /etc/logrotate.d/slms
```

### Backup Configuration

```bash
# Create backup script
sudo nano /usr/local/bin/slms-backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/slms"
DB_NAME="slms"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u slms_user -p$DB_PASS $DB_NAME > $BACKUP_DIR/slms_db_$DATE.sql

# File backup
tar -czf $BACKUP_DIR/slms_files_$DATE.tar.gz /var/www/html/slms

# Clean old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

## 🔄 Updates and Maintenance

### Update Process

```bash
# Backup before update
sudo /usr/local/bin/slms-backup.sh

# Update application
cd /var/www/html/slms
sudo git pull origin main

# Update database schema
php lms_db_init.php

# Clear cache
sudo rm -rf cache/*
```

### Regular Maintenance

```bash
# Daily tasks
sudo crontab -e
```

Add these lines:
```cron
# Daily backup
0 2 * * * /usr/local/bin/slms-backup.sh

# Log rotation
0 3 * * * /usr/sbin/logrotate /etc/logrotate.d/slms

# Database optimization
0 4 * * 0 mysql -u slms_user -p$DB_PASS -e "OPTIMIZE TABLE slms.*;"
```

## 📞 Support

### Getting Help

- **Documentation**: Check the documentation section in sLMS
- **Logs**: Review system logs for error details
- **Community**: Join the sLMS community forum
- **Support**: Contact technical support with detailed error information

### Useful Commands

```bash
# Check system status
php system_status.php

# Test all modules
php run_all_scripts.php

# Optimize system
php run_optimization.php

# Check for errors
php error_monitor.php
```

---

**Last Updated**: July 20, 2025  
**Version**: sLMS v1.0 Installation Guide  
**Status**: ✅ **Active** 